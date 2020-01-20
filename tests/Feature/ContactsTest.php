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

        $this->post('/api/contacts', ['name' => 'Len Chay Hu']);

        // Sau khi chạy lệnh POST create trên, thì trong db phải có 1 record
        $this->assertCount(1, Contact::all());
    }
}
