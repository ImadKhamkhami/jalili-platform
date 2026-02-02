<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandPlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'land_number',
        'area',
        'view_type',
        'road_type',
        'status',
        'price_per_m2',
        'total_price',

        'discount',
        'image',


        'customer_name',
        'customer_id',
        'customer_phone',  
        'customer_ref_id',  
    ];

    /* ================= Relations ================= */

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_ref_id');
    }
      public function payments()
    {
        return $this->hasMany(Payment::class, 'land_id');
    }
    public function transfers()
    {
      return $this->hasMany(Transfer::class, 'unit_id')
        ->where('context', 'land')
        ->orderBy('transfer_number');
    }

    public function commissions()
{
    return $this->hasMany(Commission::class, 'land_id');
}



}
