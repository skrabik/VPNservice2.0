<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const PROVIDER_TELEGRAM = 'telegram';
    public const PROVIDER_YOOKASSA = 'yookassa';

    public const METHOD_TELEGRAM_STARS = 'telegram_stars';
    public const METHOD_YOOKASSA_REDIRECT = 'yookassa_redirect';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_CANCELED = 'canceled';

    protected $fillable = [
        'customer_id',
        'subscription_id',
        'amount',
        'currency',
        'transaction_id',
        'provider',
        'payment_method',
        'status',
        'external_payment_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
