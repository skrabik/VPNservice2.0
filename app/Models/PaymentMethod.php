<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class PaymentMethod extends Model
{
    use AsSource, Filterable, HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
