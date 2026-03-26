<?php

namespace Tests\Feature\Customer;

use App\Jobs\ProcessTelegramMainBotMessage;
use App\Models\Customer;
use App\Models\CustomerAuthLink;
use App\Services\TelegramMessageService;
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

    public function test_existing_customer_can_sign_in_via_telegram_init_data(): void
    {
        config(['telegram.bots.main.token' => 'test-bot-token']);

        $customer = Customer::query()->create([
            'first_name' => 'Roman',
            'telegram_id' => '777',
        ]);

        $response = $this->post(route('customer.telegram.store'), [
            'init_data' => $this->makeTelegramInitData([
                'id' => 777,
                'first_name' => 'Roman',
                'username' => 'roman_user',
            ]),
        ]);

        $this->assertAuthenticated('customer');
        $response->assertRedirect(route('customer.dashboard'));
        $response->assertSessionHas('customer.is_telegram_mini_app', true);

        $customer->refresh();

        $this->assertSame('roman_user', $customer->telegram_username);
    }

    public function test_new_customer_can_sign_in_via_telegram_init_data_and_be_created(): void
    {
        config(['telegram.bots.main.token' => 'test-bot-token']);

        $response = $this->post(route('customer.telegram.store'), [
            'init_data' => $this->makeTelegramInitData([
                'id' => 999,
                'first_name' => 'Mini',
                'last_name' => 'App',
                'username' => 'mini_app_user',
            ]),
        ]);

        $this->assertAuthenticated('customer');
        $response->assertRedirect(route('customer.dashboard'));
        $response->assertSessionHas('customer.is_telegram_mini_app', true);
        $this->assertDatabaseHas('customers', [
            'telegram_id' => '999',
            'telegram_username' => 'mini_app_user',
            'first_name' => 'Mini',
        ]);
        $this->assertDatabaseCount('subscriptions', 1);
    }

    public function test_telegram_login_rejects_invalid_signature(): void
    {
        config(['telegram.bots.main.token' => 'test-bot-token']);

        $response = $this->from(route('customer.login'))
            ->post(route('customer.telegram.store'), [
                'init_data' => 'user=%7B%22id%22%3A1%7D&auth_date=123456&hash=invalid',
            ]);

        $response->assertRedirect(route('customer.login'));
        $response->assertSessionHasErrors('telegram');
        $this->assertGuest('customer');
    }

    public function test_customer_can_sign_in_via_one_time_browser_link_from_telegram(): void
    {
        $customer = Customer::query()->create([
            'first_name' => 'Roman',
            'email' => 'roman@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        CustomerAuthLink::query()->create([
            'customer_id' => $customer->id,
            'purpose' => CustomerAuthLink::PURPOSE_BROWSER_LOGIN,
            'token' => 'browser-login-token',
            'expires_at' => now()->addMinutes(15),
        ]);

        $response = $this->get(route('customer.telegram.browser-login', ['token' => 'browser-login-token']));

        $this->assertAuthenticated('customer');
        $response->assertRedirect(route('customer.dashboard'));
        $response->assertSessionHas('status', 'Вход через Telegram-ссылку выполнен успешно.');
        $this->assertDatabaseHas('customer_auth_links', [
            'token' => 'browser-login-token',
        ]);
        $this->assertNotNull(CustomerAuthLink::query()->where('token', 'browser-login-token')->value('used_at'));

        $this->post(route('customer.logout'));

        $secondResponse = $this->from(route('customer.login'))
            ->get(route('customer.telegram.browser-login', ['token' => 'browser-login-token']));

        $secondResponse->assertRedirect(route('customer.login'));
        $secondResponse->assertSessionHasErrors('telegram');
        $this->assertGuest('customer');
    }

    public function test_web_customer_can_link_telegram_without_creating_duplicate_customer(): void
    {
        $customer = Customer::query()->create([
            'first_name' => 'Roman',
            'email' => 'roman@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        CustomerAuthLink::query()->create([
            'customer_id' => $customer->id,
            'purpose' => CustomerAuthLink::PURPOSE_TELEGRAM_LINK,
            'token' => 'telegram-link-token',
            'expires_at' => now()->addMinutes(20),
        ]);

        $telegramMessageService = \Mockery::mock(TelegramMessageService::class);
        $telegramMessageService->shouldReceive('sendText')
            ->once()
            ->with('555777999', \Mockery::type('string'));
        $this->app->instance(TelegramMessageService::class, $telegramMessageService);

        $update = [
            'update_id' => 1001,
            'message' => [
                'message_id' => 10,
                'date' => now()->timestamp,
                'text' => '/start link_telegram-link-token',
                'from' => [
                    'id' => 555777999,
                    'is_bot' => false,
                    'first_name' => 'Roman',
                    'username' => 'roman_tg',
                ],
                'chat' => [
                    'id' => 555777999,
                    'type' => 'private',
                    'first_name' => 'Roman',
                    'username' => 'roman_tg',
                ],
            ],
        ];

        (new ProcessTelegramMainBotMessage($update))->handle();

        $customer->refresh();

        $this->assertSame('555777999', $customer->telegram_id);
        $this->assertSame('roman_tg', $customer->telegram_username);
        $this->assertDatabaseCount('customers', 1);
        $this->assertNotNull(CustomerAuthLink::query()->where('token', 'telegram-link-token')->value('used_at'));
    }

    private function makeTelegramInitData(array $user): string
    {
        $payload = [
            'auth_date' => (string) now()->timestamp,
            'query_id' => 'AAEAAAE',
            'user' => json_encode($user, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ];

        ksort($payload);

        $dataCheckString = collect($payload)
            ->map(fn ($value, $key) => $key.'='.$value)
            ->implode("\n");

        $secretKey = hash_hmac('sha256', (string) config('telegram.bots.main.token'), 'WebAppData', true);
        $payload['hash'] = hash_hmac('sha256', $dataCheckString, $secretKey);

        return http_build_query($payload);
    }
}
