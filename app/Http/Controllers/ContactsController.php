<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contact;

class ContactsController extends Controller
{
    private function validateData()
    {
        // return dữ liệu đã đc validate
        return  request()->validate([
            'name' => 'required',
            'email' => 'required|email',
            'birthday' => 'required',
            'company' => 'required',
        ]);
    }

    public function index()
    {
        // return Contact::all();
        return auth('api')->user()->contacts;
    }

    public function store()
    {
        Contact::create($this->validateData());
    }

    public function show(Contact $contact)
    {
        return $contact;
    }

    public function update(Contact $contact)
    {
        $contact->update($this->validateData());
        return $contact;
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
    }
}
