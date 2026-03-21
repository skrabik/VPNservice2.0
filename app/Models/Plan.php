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

    public static function resolveOrCreateDefaultMonthlyPlan(): self
    {
        $plan = self::query()
            ->where('active', true)
            ->where('period', 30)
            ->orderBy('id')
            ->first();

        if ($plan) {
            return $plan;
        }

        $templatePlan = self::query()
            ->where('active', true)
            ->orderBy('id')
            ->first();

        $plan = new self;
        $plan->title = 'Подписка на 30 дней';
        $plan->slug = 'monthly-30-days';
        $plan->description = 'Автоматически созданный план подписки на 30 дней';
        $plan->price = (float) ($templatePlan?->price ?? 299);
        $plan->stars = (int) ($templatePlan?->stars ?? max(1, (int) round((float) ($templatePlan?->price ?? 299))));
        $plan->period = 30;
        $plan->active = true;
        $plan->save();

        return $plan;
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
