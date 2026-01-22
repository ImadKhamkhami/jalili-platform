<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'national_id',
        'phone',
        'address',
    ];

    /*
    |--------------------------------------------------------------------------
    | العلاقات
    |--------------------------------------------------------------------------
    */

    // 🏢 الشقق التي يملكها الزبون
public function apartments()
{
    return $this->hasMany(Apartment::class, 'customer_ref_id');
}

public function shops()
{
    return $this->hasMany(Shop::class, 'customer_ref_id');
}

public function lands()
{
    return $this->hasMany(LandPlot::class, 'customer_ref_id');
}

}
