<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration_days',
        'max_users',
        'max_products',
        'max_outlets',
        'features',
        'is_active',
        'is_public',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'max_users' => 'integer',
        'max_products' => 'integer',
        'max_outlets' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
    ];

    /**
     * Relasi ke Subscriptions
     */
    public function advertisedFeatures(): array
    {
        return array_diff_key($this->features ?? [], array_flip(['Support 24/7 Chat', 'Account Manager Pribadi', 'Integrasi API Terbuka']));
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
