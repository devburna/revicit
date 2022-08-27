<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;

class CompanyOwner
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
        if (!$request->hasHeader('x-api-key')) {
            abort(400, 'Bad request');
        };

        // find and check verify company policy
        $company = Company::find($request->header('x-api-key'));
        if (!$company || !$request->user()->is($company->user)) {
            abort(403, 'This action is aunthorized');
        }

        // add company data to request
        $request['company'] = $company;
        $request['company_id'] = $company->id;

        return $next($request);
    }
}
