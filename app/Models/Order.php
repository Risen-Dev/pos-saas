<?php

// app/Models/Order.php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'payment_reference', 'payment_verified_at', 'payment_verified_by',
        'payment_attention', 'service_due_at',
        'points_awarded', 'points_redeemed', 'loyalty_discount', 'cash_tracked', 'cancellation_reason', 'cancelled_by', 'cancelled_at',
        'tenant_id',
        'shift_id',
        'checkout_key', 'request_hash', 'qr_url', 'kitchen_status', 'sold_at',
        'withdrawal_status',
        'service_status', 'assigned_user_id',
        'customer_id',
        'user_id',
        'order_type',
        'invoice_number',
        'table_number',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'payment_status',
        'payment_method',
        'paid_amount',
        'change_amount',
        'order_status',
        'note',
    ];

    protected $casts = ['sold_at' => 'datetime', 'service_due_at' => 'datetime'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
