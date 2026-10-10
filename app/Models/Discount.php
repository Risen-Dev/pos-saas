<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'value',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'days',
        'is_active',
    ];

    protected $casts = [
        'days' => 'array',
        'is_active' => 'boolean', // Pastikan dicast ke boolean
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'discount_product');
    }

    public function isValidNow()
    {
        // 1. Cek apakah admin mengaktifkan diskon ini
        if (! $this->is_active) {
            return false;
        }

        $now = Carbon::now();

        // 2. Validasi Tanggal (start_date s/d end_date)
        $today = $now->toDateString();
        if ($this->start_date && $today < $this->start_date) {
            return false;
        }
        if ($this->end_date && $today > $this->end_date) {
            return false;
        }

        // 3. Validasi Hari (Misal: Hanya Senin & Kamis)
        if (! empty($this->days) && is_array($this->days)) {
            if (! in_array($now->englishDayOfWeek, $this->days)) {
                return false;
            }
        }

        // 4. Validasi Jam (Misal: 08:00 - 11:00)
        // Kita gunakan format H:i:s agar perbandingan string aman (08:00:00 < 14:12:00)
        if ($this->start_time || $this->end_time) {
            $currentTime = $now->format('H:i:s');

            if ($this->start_time && $currentTime < $this->start_time) {
                return false;
            }
            if ($this->end_time && $currentTime > $this->end_time) {
                return false;
            }
        }

        return true;
    }
}
