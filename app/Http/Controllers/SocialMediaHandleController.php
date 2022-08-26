<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSocialMediaHandleRequest;
use App\Http\Requests\UpdateSocialMediaHandleRequest;
use App\Models\Company;
use App\Models\SocialMediaHandle;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SocialMediaHandleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $socialMediaHandles = SocialMediaHandle::where('company_id', $request->company_id)->paginate(20);

        return response()->json([
            'data' => $socialMediaHandles,
            'message' => 'success',
            'status' => true,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Http\Requests\StoreSocialMediaHandleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function create(StoreSocialMediaHandleRequest $request)
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

            // store social media handle
            $request['company_id'] = $company->id;
            $request['key'] = $key['profileKey'];
            $request['token'] = $token['token'];
            $this->store($request);

            // returns social media handle details
            return response()->json([
                'status' => true,
                'data' => [
                    'url' => $token['url']
                ],
                'message' => 'Use the link to connect social accounts.'
            ]);
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
     * @param  \App\Http\Requests\StoreSocialMediaHandleRequest  $request
     */
    public function store(StoreSocialMediaHandleRequest $request)
    {
        return SocialMediaHandle::create($request->only([
            'company_id',
            'key',
            'token',
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SocialMediaHandle  $socialMediaHandle
     * @return \Illuminate\Http\Response
     */
    public function show(SocialMediaHandle $socialMediaHandle, $message = 'success', $code = 200)
    {
        return response()->json([
            'data' => $socialMediaHandle,
            'message' => $message,
            'status' => true
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSocialMediaHandleRequest  $request
     * @param  \App\Models\SocialMediaHandle  $socialMediaHandle
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSocialMediaHandleRequest $request, SocialMediaHandle $socialMediaHandle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SocialMediaHandle  $socialMediaHandle
     * @return \Illuminate\Http\Response
     */
    public function destroy(SocialMediaHandle $socialMediaHandle)
    {
        //
    }
}
