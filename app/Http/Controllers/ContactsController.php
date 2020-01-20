<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contact;

class ContactsController extends Controller
{
    public function store()
    {
        // $data là dữ liệu đã đc validate
        $data = request()->validate([
            'name' => 'required',
            'email' => 'required',
            'birthday' => 'required',
            'company' => 'required',
        ]);

        Contact::create($data);
    }
}
