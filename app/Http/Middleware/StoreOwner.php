<?php

namespace App\Http\Middleware;

use App\Models\Storefront;
use Closure;
use Illuminate\Http\Request;

class StoreOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // verify request header
        if (!$request->hasHeader('x-store-key')) {
            abort(400, 'Bad request');
        };

        // find and verify store policy
        $storefront = Storefront::find($request->header('x-store-key'));
        if (!$storefront || !$request->user()->is($storefront->company->user)) {
            abort(403, 'This action is aunthorized');
        }

        // add storefront data to request
        $request['storefront'] = $storefront;
        $request['storefront_id'] = $storefront->id;

        return $next($request);
    }
}
