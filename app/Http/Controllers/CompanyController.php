<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\StoreCompanyWalletRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $companies = $request->user()->companies()->paginate(20);

        return response()->json([
            'data' => $companies,
            'message' => 'success',
            'status' => true
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCompanyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompanyRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $request['user_id'] = $request->user()->id;

            $company =  Company::create($request->only([
                'user_id',
                'name',
                'address',
                'email',
                'phone',
                'website',
                'description',
                'logo_url'
            ]));

            // create company wallet
            $request['company_id'] = $company->id;
            (new CompanyWalletController())->store((new StoreCompanyWalletRequest($request->all())));

            return $this->show($company, null, 201);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company, $message = 'success', $code = 200)
    {
        $company->qr_code_data = url("/company/{$company->id}");

        return response()->json([
            'data' => $company,
            'message' => $message,
            'status' => true
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCompanyRequest  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $company->update($request->only([
            'address',
            'email',
            'phone',
            'website',
            'description',
        ]));

        return $this->show($company);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        if ($company->trashed()) {
            $company->restore();
        } else {
            $company->delete();
        }

        return $this->show($company);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\ImageUploadRequest  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function logo(Company $company, ImageUploadRequest $request)
    {
        // upload to cloudinary
        $request['logo_url'] = (new UploadApi())->upload($request->image->path(), [
            'folder' => config('app.name') . '/companies/',
            'public_id' => $company->id,
            'overwrite' => true,
            'resource_type' => 'image'
        ])['secure_url'];

        $company->update($request->only(['logo_url']));

        return $this->show($company);
    }
}
