<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPendingAction extends Model
{
    public const ACTION_SUPPORT_TICKET_TYPE = 1;

    protected $fillable = [
        'customer_id',
        'action_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
