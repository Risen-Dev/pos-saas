<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * Properti properti yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id', // <--- Ditambahkan di sini
        'user_id',
        'start_time',
        'end_time',
        'cash_start',
        'cash_expected',
        'cash_actual',
        'cash_difference',
        'status',
        'notes',
    ];

    /**
     * Relasi ke model User / Kasir (Opsional)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
