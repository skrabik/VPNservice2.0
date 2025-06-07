<?php

namespace App\Telegram\Commands;

use App\Models\Customer;
use Telegram\Bot\Objects\Update;

abstract class BaseCommand
{
    protected Update $update;

    protected Customer $customer;

    protected array $params;

    public function __construct(Update $update, Customer $customer, array $params)
    {
        $this->update = $update;
        $this->customer = $customer;
        $this->params = $params;
    }

    abstract public function handle(): void;
}
