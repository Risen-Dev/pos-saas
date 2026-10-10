<?php

// app/Models/StockMovement.php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'material_id',
        'user_id',
        'supplier_id', // new
        'purchase_price', // new
        'type',
        'quantity',
        'before_stock',
        'after_stock',
        'note',
        'reference_type',
        'reference_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // protected static function booted()
    // {
    //     static::addGlobalScope('tenant', function ($builder) {
    //         if (auth()->check()) {
    //             $builder->where('tenant_id', auth()->user()->tenant_id);
    //         }
    //     });
    // }

    // Helper untuk label warna di view nanti
    public function getTypeLabelAttribute()
    {
        return [
            'stock_in' => 'Masuk',
            'stock_out' => 'Keluar',
            'sales' => 'Penjualan',
            'adjustment' => 'Penyesuaian',
            'return' => 'Retur',
        ][$this->type];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function getItemNameAttribute()
    {
        return $this->product_id ? $this->product->product_name : $this->material->name;
    }
}
