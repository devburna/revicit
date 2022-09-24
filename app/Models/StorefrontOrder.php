<?php

namespace App\Models;

use App\Enums\StorefrontOrderStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class StorefrontOrder extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
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
        'status',
        'meta'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'storefront_product_id',
        'storefront_customer_id',
        'storefront'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => StorefrontOrderStatus::class
    ];

    protected function meta(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => json_decode($value),
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(StorefrontProduct::class, 'storefront_product_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(StorefrontCustomer::class, 'storefront_customer_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(StorefrontOrderHistory::class, 'storefront_order_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(StorefrontOrder::class, 'storefront_order_id');
    }

    public function shipping(): HasOne
    {
        return $this->hasOne(StorefrontOrderDeliveryAgent::class, 'storefront_order_id');
    }
}
