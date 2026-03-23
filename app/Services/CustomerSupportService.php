<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\SupportTicket;

class CustomerSupportService
{
    public function createTicket(Customer $customer, string $message): SupportTicket
    {
        return SupportTicket::create([
            'customer_id' => $customer->id,
            'message' => trim($message),
        ]);
    }
}
