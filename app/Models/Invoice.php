<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'activation_applied_at', 'duration_days',
        'tenant_id',
        'subscription_id',
        'plan_id',
        'invoice_number',
        'amount',
        'status',
        'payment_method',
        'snap_token',
        'paid_at',
        'due_date',
    ];

    protected $casts = ['activation_applied_at' => 'datetime', 'paid_at' => 'datetime', 'duration_days' => 'integer'];

    /**
     * Relasi ke Plan / Paket yang dibeli
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Relasi ke Subscription (Hanya terisi saat invoice sudah LUNAS)
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Relasi ke Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
