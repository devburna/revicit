<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAyrshareProfileRequest;
use App\Http\Requests\UpdateAyrshareProfileRequest;
use App\Models\AyrshareProfile;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AyrshareProfileController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Http\Requests\StoreAyrshareProfileRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function create(StoreAyrshareProfileRequest $request)
    {
        try {
            // get company info
            if (!$company = Company::find($request->company_id)) {
                throw ValidationException::withMessages(["Can not connect account at the moment."]);
            }

            // create profile key
            $key = (new AyrshareController())->createProfile($company->name);

            // create profile token with key
            $token = (new AyrshareController())->generateToken($key['profileKey']);

            // store profile
            $request['company_id'] = $company->id;
            $request['key'] = $key['profileKey'];
            $request['token'] = $token['token'];
            $ayrshareProfile = $this->store($request);

            return $this->show($ayrshareProfile);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => $th->getMessage()
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAyrshareProfileRequest  $request
     */
    public function store(StoreAyrshareProfileRequest $request)
    {
        return AyrshareProfile::create($request->only([
            'company_id',
            'key',
            'token',
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AyrshareProfile  $ayrshareProfile
     * @return \Illuminate\Http\Response
     */
    public function show(AyrshareProfile $ayrshareProfile, $message = 'Click on the link to connect social network', $code = 200)
    {
        return response()->json([
            'status' => true,
            'data' => [
                'link' => "https://profile.ayrshare.com?domain=revicit&jwt={$ayrshareProfile->token}"
            ],
            'message' => $message
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAyrshareProfileRequest  $request
     * @param  \App\Models\AyrshareProfile  $ayrshareProfile
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAyrshareProfileRequest $request, AyrshareProfile $ayrshareProfile)
    {
        return $this->show($ayrshareProfile);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AyrshareProfile  $ayrshareProfile
     * @return \Illuminate\Http\Response
     */
    public function destroy(AyrshareProfile $ayrshareProfile)
    {
        if ($ayrshareProfile->trashed()) {
            $ayrshareProfile->restore();
        } else {
            $ayrshareProfile->delete();
        }

        return $this->show($ayrshareProfile);
    }
}
