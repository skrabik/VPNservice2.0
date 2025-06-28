<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Plan extends Model
{
    use AsSource, Filterable, HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'price',
        'stars',
        'period',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
