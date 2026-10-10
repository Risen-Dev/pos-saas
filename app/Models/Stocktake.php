<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class Stocktake extends Model { use BelongsToTenant; protected $fillable=['tenant_id','created_by','number','status','note','completed_at']; protected $casts=['completed_at'=>'datetime']; public function items(){return $this->hasMany(StocktakeItem::class);} }
