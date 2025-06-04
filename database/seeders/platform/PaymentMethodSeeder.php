<?php

namespace Database\Seeders\platform;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'title' => 'Credit Card',
                'slug' => 'credit_card',
                'description' => 'Оплата банковской картой',
                'active' => true,
            ],
            [
                'title' => 'PayPal',
                'slug' => 'paypal',
                'description' => 'Оплата через PayPal',
                'active' => true,
            ],
            [
                'title' => 'Crypto',
                'slug' => 'crypto',
                'description' => 'Оплата криптовалютой',
                'active' => false,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::create($method);
        }
    }
}
