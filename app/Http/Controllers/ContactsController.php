<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contact;
use App\Http\Resources\ContactResource;

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
        $this->authorize('viewAny', Contact::class); // Contact::class, vậy nó mới biết đang dùng policy của model nào
        return ContactResource::collection(auth('api')->user()->contacts);
    }

    public function store()
    {
        $this->authorize('create', Contact::class); // Contact::class, vậy nó mới biết đang dùng policy của model nào
        auth('api')->user()->contacts()->create($this->validateData());
    }

    public function show(Contact $contact)
    {
        $this->authorize('view', $contact);
        return new ContactResource($contact);
    }

    public function update(Contact $contact)
    {
        $this->authorize('update', $contact);
        $contact->update($this->validateData());
        return $contact;
    }

    public function destroy(Contact $contact)
    {
        $this->authorize('delete', $contact);
        $contact->delete();
    }
}
