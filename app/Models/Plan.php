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
        $features = array_diff_key($this->features ?? [], array_flip(['Support 24/7 Chat', 'Account Manager Pribadi', 'Integrasi API Terbuka', '100 Transaksi / Bulan', 'CRM & Loyalitas']));
        if (in_array($this->slug, ['starter', 'growth', 'scale'], true)) {
            $features = ['Transaksi Tanpa Batas' => true, 'Pelanggan & Bon' => true, 'Transfer & QRIS Manual' => true] + $features;
        }
        if (in_array($this->slug, ['growth', 'scale'], true)) {
            $features['Loyalitas & QRIS Otomatis'] = true;
        }

        return $features;
    }

    public function getDescriptionAttribute(?string $value): ?string
    {
        return $this->slug === 'scale' ? 'Untuk UMKM dengan beberapa outlet dan tim lebih besar.' : $value;
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
