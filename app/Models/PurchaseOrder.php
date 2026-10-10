<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class PurchaseOrder extends Model { use BelongsToTenant; protected $fillable=['tenant_id','supplier_id','created_by','number','status','ordered_at','expected_at','note']; protected $casts=['ordered_at'=>'date','expected_at'=>'date']; public function items(){return $this->hasMany(PurchaseOrderItem::class);} public function supplier(){return $this->belongsTo(Supplier::class);} }
