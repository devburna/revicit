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
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request;  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // get profile
        $profile = AyrshareProfile::where('company_id', $request->company_id)->firstOrfail();

        // check policy
        if (!$request->user()->is($profile->company->user)) {
            abort(403, 'This action is unauthorized.');
        }

        return $this->show($profile);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Http\Requests\StoreAyrshareProfileRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function create(StoreAyrshareProfileRequest $request)
    {
        try {
            // c
            // get company info
            if (!$company = Company::find($request->company_id)) {
                throw ValidationException::withMessages(["Can not connect account at the moment."]);
            }

            // generate profile
            $key = (new AyrshareController())->createProfile($company->name);

            // store profile
            $request['company_id'] = $company->id;
            $request['identity'] = $key['profileKey'];
            $request['meta'] = json_encode($key);
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
            'identity',
            'meta',
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AyrshareProfile  $ayrshareProfile
     * @return \Illuminate\Http\Response
     */
    public function show(AyrshareProfile $ayrshareProfile, $message = 'Click on the link to connect social network.', $code = 200)
    {
        // generate profile jwt token
        $token = (new AyrshareController())->generateToken($ayrshareProfile->identity);

        return response()->json([
            'status' => true,
            'data' => [
                'link' => $token['url']
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
