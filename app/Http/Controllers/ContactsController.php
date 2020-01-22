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
            // 'user_id' => 'required'
        ]);
    }

    public function index()
    {
        // return Contact::all();
        return auth('api')->user()->contacts;
    }

    public function store()
    {
        auth('api')->user()->contacts()->create($this->validateData());
    }

    public function show(Contact $contact)
    {
        if (request()->user()->isNot($contact->user)) {
            return response()->json([], 403);
        }
        return $contact;
    }

    public function update(Contact $contact)
    {
        if (request()->user()->isNot($contact->user)) {
            return response()->json([], 403);
        }
        $contact->update($this->validateData());
        return $contact;
    }

    public function destroy(Contact $contact)
    {
        if (request()->user()->isNot($contact->user)) {
            return response()->json([], 403);
        }
        $contact->delete();
    }
}
