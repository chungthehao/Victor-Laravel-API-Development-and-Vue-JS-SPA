<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Contact;

class ContactsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_contact_can_be_added()
    {
        // Không có dòng này thì Laravel sẽ handle exception nên mình ko bị lỗi báo endpoint ko tồn tại
        $this->withoutExceptionHandling();

        $this->post('/api/contacts', [
            'name' => 'Len Chay Hu',
            'email' => 'hu@1.com',
            'birthday' => '08/17/1992',
            'company' => 'ABC Company'
        ]);

        $contact = Contact::first();

        // Những thông tin trg db phải khớp những gì đã lưu
        $this->assertEquals('Len Chay Hu', $contact->name);
        $this->assertEquals('hu@1.com', $contact->email);
        $this->assertEquals('08/17/1992', $contact->birthday);
        $this->assertEquals('ABC Company', $contact->company);
    }

    /** @test */
    public function a_name_is_required()
    {
        // $this->withoutExceptionHandling(); // Comment vì validation fail thì Laravel quăng exception: The given data was invalid. -> nó có validate cho mình, để nó handle exception để trả response là validation fail rồi cho mình (expect). -> Khi đó test sẽ xanh

        $response = $this->post('/api/contacts', [
            'email' => 'hu@1.com',
            'birthday' => '08/17/1992',
            'company' => 'ABC Company'
        ]);

        $contact = Contact::first();

        // Khi insert ko có 'name', phải báo lỗi validation
        $response->assertSessionHasErrors('name');

        // Và khi validation fail, không có record nào đc insert
        $this->assertCount(0, Contact::all());
    }

    /** @test */
    public function email_is_required()
    {
        // $this->withoutExceptionHandling(); // Comment vì validation fail thì Laravel quăng exception: The given data was invalid. -> nó có validate cho mình, để nó handle exception để trả response là validation fail rồi cho mình (expect). -> Khi đó test sẽ xanh

        $response = $this->post('/api/contacts', [
            'name' => 'Hao Chung',
            'birthday' => '08/17/1992',
            'company' => 'ABC Company'
        ]);

        $contact = Contact::first();

        // Khi insert ko có 'name', phải báo lỗi validation
        $response->assertSessionHasErrors('email');

        // Và khi validation fail, không có record nào đc insert
        $this->assertCount(0, Contact::all());
    }
}
