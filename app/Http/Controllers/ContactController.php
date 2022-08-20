<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Requests\ViewCompanyRequest;
use App\Models\Company;
use App\Models\Contact;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function index(ViewCompanyRequest $request)
    {
        $contacts = Contact::where('company_id', $request->company_id)->paginate(20);

        return response()->json([
            'data' => $contacts,
            'message' => 'success',
            'status' => true,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Company  $company
     * @param  \App\Http\Requests\StoreContactRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreContactRequest $request, Company $company)
    {
        $request['company_id'] = $company->id;
        $contact = Contact::create($request->only(['company_id', 'name', 'email', 'phone']));

        return $this->show($contact, 'success', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function show(Contact $contact, $message = 'success', $code = 200)
    {
        // and related company to data
        $contact->company;

        return response()->json([
            'data' => $contact,
            'message' => $message,
            'status' => true,
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateContactRequest  $request
     * @param  \App\Models\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $contact->update($request->only(['name', 'email', 'phone']));

        return $this->show($contact, 'success', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contact $contact)
    {
        if ($contact->trashed()) {
            $contact->restore();
        } else {
            $contact->delete();
        }

        return $this->show($contact);
    }
}
