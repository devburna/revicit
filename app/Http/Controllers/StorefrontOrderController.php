<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\StorefrontOrderStatus;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\StoreStorefrontOrderHistoryRequest;
use App\Http\Requests\StoreStorefrontOrderRequest;
use App\Http\Requests\UpdateStorefrontOrderRequest;
use App\Models\Payment;
use App\Models\Storefront;
use App\Models\StorefrontCustomer;
use App\Models\StorefrontOrder;
use App\Models\StorefrontProduct;
use App\Notifications\StorefrontOrderInvoice;
use App\Notifications\StorefrontOrderNotification;
use App\Notifications\StorefrontOrderStatus as NotificationsStorefrontOrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StorefrontOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $storefrontOrders = StorefrontOrder::withTrashed()->whereHas('product', function ($product) use ($request) {
            $product->whereStorefrontId($request->storefront->id);
        })->with(['product', 'customer', 'history'])->paginate(20);

        return response()->json([
            'data' => $storefrontOrders,
            'message' => 'success',
            'status' => true
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Http\Requests\StoreStorefrontOrderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function create(StoreStorefrontOrderRequest $request)
    {
        try {
            $db = DB::transaction(function () use ($request) {
                // find transaction
                if (Payment::whereIdentity($request->data['reference'])->first()) {
                    abort(422, 'Duplicate transaction.');
                }

                // find storefront
                if (!$storefront = Storefront::whereDomain($request->data['metadata']['custom_field']['merchant'])->first()) {
                    abort(422, 'Store not found.');
                }

                // verify if order
                if ($request->data['metadata']['custom_field']['type'] !== 'order') {
                    abort(422, 'Type not supported.');
                }

                // get delivery address
                $delivery_address = $request->data['metadata']['custom_field']['delivery_address'];

                // get cart items
                $cart_items = $request->data['metadata']['custom_field']['cart'];

                // create customer
                if (!$_customer = StorefrontCustomer::whereStorefrontId($request->data['customer']['email'])->whereEmail($request->data['customer']['email'])->first()) {
                    $_customer = StorefrontCustomer::create([
                        'storefront_id' => $storefront->id,
                        'first_name' => $request->data['customer']['first_name'],
                        'last_name' => $request->data['customer']['last_name'],
                        'email' => $request->data['customer']['email'],
                        'phone' => $request->data['customer']['phone']
                    ]);
                }

                // set total price
                $total_price = [];

                // set orders
                $orders = [];

                foreach ($cart_items as $item) {
                    try {
                        // validate product
                        $product = StorefrontProduct::find($item['product']);

                        // create order
                        $order['storefront_customer_id'] = $_customer->id;
                        $order['storefront_product_id'] = $product->id;
                        $order['reference'] = $request->data['reference'];
                        $order['quantity'] = $item['quantity'];
                        $order['price'] = $item['price'];
                        $order['total_price'] = $item['quantity'] * $item['price'];
                        $order['quantity'] = $product->sale_price ?? $product->regular_price;
                        $order['status'] = StorefrontOrderStatus::RECEIVED();
                        $storefrontOrder = $this->store(new StoreStorefrontOrderRequest(array_merge($order, $delivery_address)));

                        // create history
                        $history['storefront_order_id'] = $storefrontOrder->id;
                        $history['status'] = $storefrontOrder->status;
                        $history['comment'] = "We've received your order";
                        $history['meta'] = json_encode($storefrontOrder);
                        (new StorefrontOrderHistoryController())->store(new StoreStorefrontOrderHistoryRequest($history));

                        array_push($total_price, $order['total_price']);

                        array_push($orders, $storefrontOrder);
                    } catch (\Throwable $th) {
                        abort(422, $th->getMessage());
                    }
                }

                // verify amount
                if (array_sum($total_price) != $request->data['amount']) {
                    abort(422, "Amount is invalid. {$request->data['amount']} - " . array_sum($total_price));
                }

                // create payment
                $payment['company_wallet_id'] = $storefront->company->wallet->id;
                $payment['identity'] = $request->data['reference'];
                $payment['amount'] = $request->data['amount'];
                $payment['currency'] = $request->data['currency'];
                $payment['narration'] = "New order for {$storefront->name} (Reference: {$request->data['reference']})";
                $payment['type'] = PaymentType::CREDIT();
                $payment['status'] = PaymentStatus::SUCCESSFUL();
                $payment['meta'] = json_encode([
                    'orders' => $orders,
                    'transaction' => $request->all(),
                ]);
                $payment = (new PaymentController())->store(new StorePaymentRequest($payment));

                $storefrontOrder->payment = $payment;

                return $storefrontOrder;
            });


            // notify storefront
            $db->product->storefront->notify(new StorefrontOrderNotification($db));

            // notify customer
            $db->customer->notify(new StorefrontOrderInvoice($db));
        } catch (\Throwable $th) {
            abort(422, $th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStorefrontOrderRequest  $request
     */
    public function store(StoreStorefrontOrderRequest $request)
    {
        return StorefrontOrder::create($request->only([
            'storefront_product_id',
            'storefront_customer_id',
            'reference',
            'quantity',
            'price',
            'total_price',
            'address',
            'city',
            'state',
            'country',
            'notes',
            'status'
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StorefrontOrder  $storefrontOrder
     * @return \Illuminate\Http\Response
     */
    public function show(StorefrontOrder $storefrontOrder, $message = 'success', $code = 200)
    {
        // add product to data
        $storefrontOrder->product;

        // add customer to data
        $storefrontOrder->customer;


        // add history to data
        $storefrontOrder->history;

        return response()->json([
            'status' => true,
            'data' => $storefrontOrder,
            'message' => $message
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStorefrontOrderRequest  $request
     * @param  \App\Models\StorefrontOrder  $storefrontOrder
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStorefrontOrderRequest $request, StorefrontOrder $storefrontOrder)
    {
        // set comment
        $comment = match ($request->status) {
            'processing' => 'Your order is currently being processed.',
            'out-for-delivery' => 'Your order has been sent out for delivery.',
            'delivered' => 'Your order has been delivered.',
            default => 'Your order has been cancelled, kindly reach out to support for more information.',
        };

        if ($request->has('status') && $storefrontOrder->status !== $request->status) {

            // create history
            $history['storefront_order_id'] = $storefrontOrder->id;
            $history['status'] = $request->status;
            $history['comment'] = $comment;
            $history['meta'] = json_encode($storefrontOrder);
            $history = (new StorefrontOrderHistoryController())->store(new StoreStorefrontOrderHistoryRequest($history));

            // add order to data
            $history->order;

            // notify customer
            $storefrontOrder->customer->notify(new NotificationsStorefrontOrderStatus($history));
        }

        // update
        $storefrontOrder->update($request->only([
            'status'
        ]));

        return $this->show($storefrontOrder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StorefrontOrder  $storefrontOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(StorefrontOrder $storefrontOrder)
    {
        //
    }
}
