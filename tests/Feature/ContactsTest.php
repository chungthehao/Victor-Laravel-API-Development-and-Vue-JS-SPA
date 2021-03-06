<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Contact;
use App\User;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class ContactsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    // Muốn chạy gì đó trước khi bất cứ test nào đc chạy
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    private function data()
    {
        return [
            'name' => 'Len Chay Hu',
            'email' => 'hu@1.com',
            'birthday' => '08/17/1992',
            'company' => 'ABC Company',
            'api_token' => $this->user->api_token
        ];
    }

    /** @test */
    public function a_list_of_contacts_can_be_fetched_for_the_authenticated_user()
    {
        $user = factory(User::class)->create();
        $anotherUser = factory(User::class)->create();

        $contact = factory(Contact::class)->create(['user_id' => $user->id]);
        $anotherContact = factory(Contact::class)->create(['user_id' => $anotherUser->id]);

        $response = $this->get('/api/contacts?api_token=' . $user->api_token);

        // dd(json_decode($response->getContent()));
        $response->assertJsonCount(1)->assertJson([
            'data' => [
                [
                    'data' => ['contact_id' => $contact->id],
                    'links' => ['self' => $contact->path()]
                ],
            ]
        ]); // List -> 1 mảng các obj
    }

    /** @test */
    public function an_unauthenticated_user_should_redirected_to_login()
    {
        // Chưa login mà insert -> redirect tới login page, db vẫn trống
        $response = $this->post(
            '/api/contacts', 
            array_merge(
                $this->data(),
                ['api_token' => ''] // để coi như nó chưa login
            )
        );
        $response->assertRedirect('/login');
        $this->assertCount(0, Contact::all());
    }

    /** @test */
    public function an_authenticated_user_can_add_a_contact()
    {
        $response = $this->post('/api/contacts', $this->data()); // dd(json_decode($response->getContent()));

        $contact = Contact::first();

        // Những thông tin trg db phải khớp những gì đã lưu
        $this->assertEquals('Len Chay Hu', $contact->name);
        $this->assertEquals('hu@1.com', $contact->email);
        $this->assertEquals(Carbon::parse('08/17/1992'), $contact->birthday);
        $this->assertEquals('ABC Company', $contact->company);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'data' => ['contact_id' => $contact->id],
            'links' => ['self' => $contact->path()]
        ]);
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

    /** @test */
    public function email_must_be_a_valid_email()
    {
        $response = $this->post(
            '/api/contacts', 
            array_merge(
                $this->data(),
                ['email' => 'NOT AN EMAIL']
            )
        );

        $contact = Contact::first();

        // Khi insert ko đúng định dạng email, phải báo lỗi validation
        $response->assertSessionHasErrors('email');

        // Và khi validation fail, không có record nào đc insert
        $this->assertCount(0, Contact::all());
    }

    /** @test */
    public function birthdays_are_properly_stored()
    {
        $this->withoutExceptionHandling();

        $response = $this->post(
            '/api/contacts', 
            array_merge(
                $this->data(),
                // ['birthday' => 'August 17, 1992'] // even another format
            )
        );

        $this->assertCount(1, Contact::all());

        $this->assertInstanceOf(Carbon::class, Contact::first()->birthday);

        $this->assertEquals('08-17-1992', Contact::first()->birthday->format('m-d-Y'));
    }

    /** @test */
    public function a_contact_can_be_retrieved()
    {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);

        $response = $this->get('/api/contacts/' . $contact->id . '?api_token=' . $this->user->api_token);

        // dd(json_decode($response->getContent()));
        $response->assertJson([
            'data' => [
                'contact_id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'birthday' => $contact->birthday->format('m/d/Y'),
                'company' => $contact->company,
                'last_updated' => $contact->updated_at->diffForHumans(),
            ]
        ]);
    }

    /** @test */
    public function only_the_users_contacts_can_be_retrieved()
    {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);

        $anotherUser = factory(User::class)->create();

        $response = $this->get('/api/contacts/' . $contact->id . '?api_token=' . $anotherUser->api_token);

        $response->assertStatus(403);
    }

    /** @test */
    public function a_contact_can_be_patched()
    {
        // $this->withoutExceptionHandling();

        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);

        $response = $this->patch(
            '/api/contacts/' . $contact->id, 
            $this->data()
        );

        $contact = $contact->fresh();

        // Những thông tin trg db phải khớp những gì đã lưu
        $this->assertEquals('Len Chay Hu', $contact->name);
        $this->assertEquals('hu@1.com', $contact->email);
        $this->assertEquals(Carbon::parse('08/17/1992'), $contact->birthday);
        $this->assertEquals('ABC Company', $contact->company);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'contact_id' => $contact->id
            ],
            'links' => [
                'self' => $contact->path()
            ],
        ]);
    }

    /** @test */
    public function only_the_owner_of_the_contact_can_patch_the_contact()
    {
        // $this->withoutExceptionHandling();

        $contact = factory(Contact::class)->create();
        $anotherUser = factory(User::class)->create();

        $response = $this->patch(
            '/api/contacts/' . $contact->id, 
            array_merge(
                $this->data(),
                ['api_token' => $anotherUser->api_token]
            )
        );

        $response->assertStatus(403);
    }

    /** @test */
    public function a_contact_can_be_deleted()
    {
        // $this->withoutExceptionHandling();

        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);

        $response = $this->delete('/api/contacts/' . $contact->id. '?api_token=' . $this->user->api_token);

        $this->assertCount(0, Contact::all());

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    /** @test */
    public function only_the_owner_of_the_contact_can_delete_the_contact()
    {
        $contact = factory(Contact::class)->create();
        $anotherUser = factory(User::class)->create();

        $response = $this->delete('/api/contacts/' . $contact->id. '?api_token=' . $anotherUser->api_token);

        $response->assertStatus(403);
    }

}
