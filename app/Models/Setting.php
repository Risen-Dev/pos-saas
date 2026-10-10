<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use BelongsToTenant, HasFactory;

    // Tambahkan baris ini
    protected $fillable = [
        'tenant_id',
        'key',
        'value',
    ];

    // Jika kamu ingin menghubungkan kembali ke model Tenant
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
