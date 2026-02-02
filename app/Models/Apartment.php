<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Project;

class Apartment extends Model
{
     protected $fillable = [
        'building_id',
        'number',
        'floor',
        'tranche_number',
        'area',
        'price_per_m2',
        'rooms',

        'status',
        'customer_name',
        'customer_id',
        'customer_phone',
        'customer_ref_id',

        'discount',
        'image',

        'has_parking',
        'parking_number',
        'parking_price',

        'has_terrace',
        'terrace_area',
        'terrace_type',
        'terrace_total_price',
        'total_price',
    ];

    protected $casts = [
        'area'         => 'float',
        'price_per_m2' => 'float',
        'total_price'  => 'float',
        'terrace_total_price' => 'float',
        'has_parking'  => 'boolean',
        'has_terrace'  => 'boolean',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function customer()
{
    return $this->belongsTo(Customer::class, 'customer_ref_id');
}
public function payments()
{
    return $this->hasMany(Payment::class);
}
public function transfers()
{
    return $this->hasMany(Transfer::class, 'unit_id')
        ->where('context', 'apartment')
        ->orderBy('transfer_number');
}

public function commissions()
{
    return $this->hasMany(Commission::class, 'apartment_id');
}



   



}
