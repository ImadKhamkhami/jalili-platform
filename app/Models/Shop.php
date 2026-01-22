<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
protected $fillable = [
    'building_id',
    'number',
    'tranche_number',

    'area',
    'price_per_m2',

    // 🪜 Mezzanine (مثل terrasse)
    'has_mezzanine',
    'mezzanine_area',
    'mezzanine_price_per_m2',
    'mezzanine_total_price',

    'discount',
    'total_price',

    'facade',

    'image',
    'status',

    // 👤 Customer
    'customer_ref_id',
    'customer_name',
    'customer_id',
    'customer_phone',
];
protected $casts = [
    'has_mezzanine' => 'boolean',

    'area'                    => 'float',
    'price_per_m2'            => 'float',

    'mezzanine_area'          => 'float',
    'mezzanine_price_per_m2'  => 'float',
    'mezzanine_total_price'   => 'float',

    'discount'     => 'float',
    'total_price'  => 'float',
];


    /* ================= Relations ================= */

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_ref_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function payments()
{
    return $this->hasMany(Payment::class);
}

public function transfers()
{
    return $this->hasMany(Transfer::class, 'unit_id')
        ->where('context', 'shop')
        ->orderBy('transfer_number');
}

    /* ================= Accessors ================= */


}
