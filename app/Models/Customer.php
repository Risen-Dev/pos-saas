<?php

// app/Models/Customer.php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Customer extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'points',
        'is_member',
        'total_debt', // Pastikan ini ada jika Anda menambahkannya di migrasi
        'loyalty_card_token',
        'loyalty_member_code',
    ];

    protected $casts = [
        'is_member' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function loyaltyPointTransactions()
    {
        return $this->hasMany(LoyaltyPointTransaction::class);
    }

    public function loyaltyStampTransactions()
    {
        return $this->hasMany(LoyaltyStampTransaction::class);
    }

    public function ensureLoyaltyCard(): self
    {
        if (! $this->is_member) {
            $this->forceFill(['is_member' => true]);
        }

        if (! $this->loyalty_card_token) {
            $this->loyalty_card_token = Str::random(64);
        }

        if (! $this->loyalty_member_code) {
            do {
                $code = 'GP-'.Str::upper(Str::random(8));
            } while (static::withoutGlobalScopes()->where('loyalty_member_code', $code)->exists());

            $this->loyalty_member_code = $code;
        }

        $this->save();

        return $this;
    }
}
