<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Contact;

class ContactsTest extends TestCase
{
    use RefreshDatabase;

    private function data()
    {
        return [
            'name' => 'Len Chay Hu',
            'email' => 'hu@1.com',
            'birthday' => '08/17/1992',
            'company' => 'ABC Company'
        ];
    }

    /** @test */
    public function a_contact_can_be_added()
    {
        $this->post('/api/contacts', $this->data());

        $contact = Contact::first();

        // Những thông tin trg db phải khớp những gì đã lưu
        $this->assertEquals('Len Chay Hu', $contact->name);
        $this->assertEquals('hu@1.com', $contact->email);
        $this->assertEquals('08/17/1992', $contact->birthday);
        $this->assertEquals('ABC Company', $contact->company);
    }

    /** @test */
    public function fields_are_required()
    {
        collect(['name', 'email', 'birthday', 'company'])
            ->each(function ($field) {
                $response = $this->post(
                    '/api/contacts', 
                    array_merge(
                        $this->data(),
                        [$field => '']
                    )
                );
        
                $contact = Contact::first();
        
                // Khi insert ko có 'name', phải báo lỗi validation
                $response->assertSessionHasErrors($field);
        
                // Và khi validation fail, không có record nào đc insert
                $this->assertCount(0, Contact::all());
            });
    }

}
