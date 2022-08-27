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
        return $this->show($request->company->socialNetwork);
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

            // return company social network if exists
            if ($request->company->socialNetwork) {
                return $this->show($request->company->socialNetwork);
            }

            // generate profile
            $key = (new AyrshareController())->createProfile($request->company->name);

            // store profile
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
    public function show(AyrshareProfile $ayrshareProfile, $message = 'Click on the link to connect social network', $code = 200)
    {
        // generate profile jwt token
        $token = (new AyrshareController())->generateToken($ayrshareProfile->identity);

        return response()->json([
            'status' => true,
            'data' => [
                'link' => "{$token['url']}&redirect="
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
