<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StocktakeItem extends Model
{
    protected $fillable = ['stocktake_id', 'product_id', 'system_quantity', 'counted_quantity'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
