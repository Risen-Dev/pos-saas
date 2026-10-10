<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    /**
     * Boot trait ini otomatis dipanggil Laravel saat model di-load.
     * Menambahkan filter tenant_id otomatis ke SEMUA query,
     * dan mengisi tenant_id otomatis saat create.
     */
    protected static function bootBelongsToTenant()
    {
        // Filter otomatis: setiap query ke model ini otomatis
        // ditambahkan WHERE tenant_id = tenant aktif user login
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where(
                    $builder->getModel()->getTable().'.tenant_id',
                    auth()->user()->tenant_id
                );
            }
        });

        // Auto-isi tenant_id saat record baru dibuat,
        // supaya tidak perlu diisi manual tiap kali create()
        static::creating(function ($model) {
            if (auth()->check() && empty($model->tenant_id)) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    /**
     * Helper untuk query tanpa filter tenant (khusus dipakai
     * di context admin platform / job background jika perlu).
     * Pakai HATI-HATI, hanya untuk kebutuhan internal/admin.
     */
    public function scopeWithoutTenantScope($query)
    {
        return $query->withoutGlobalScope('tenant');
    }
}
