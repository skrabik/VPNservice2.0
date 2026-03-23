<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CustomerCabinetAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('customer.register'));

        $response->assertOk();
    }

    public function test_new_customer_can_register_for_web_cabinet(): void
    {
        $response = $this->post(route('customer.register.store'), [
            'first_name' => 'Roman',
            'last_name' => 'Tester',
            'email' => 'roman@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->assertAuthenticated('customer');
        $response->assertRedirect(route('customer.dashboard'));
        $this->assertDatabaseHas('customers', [
            'email' => 'roman@example.com',
            'first_name' => 'Roman',
        ]);
    }

    public function test_existing_telegram_customer_can_complete_registration_via_signed_link(): void
    {
        $customer = Customer::query()->create([
            'first_name' => 'Telegram',
            'telegram_id' => '123456789',
        ]);

        $claimUrl = URL::temporarySignedRoute(
            'customer.claim.create',
            now()->addHour(),
            ['customer' => $customer]
        );

        $response = $this->post($claimUrl, [
            'first_name' => 'Roman',
            'last_name' => 'Claimed',
            'email' => 'claimed@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->assertAuthenticated('customer');
        $response->assertRedirect(route('customer.dashboard'));

        $customer->refresh();

        $this->assertSame('claimed@example.com', $customer->email);
        $this->assertTrue(Hash::check('Password123!', $customer->password));
    }
}
