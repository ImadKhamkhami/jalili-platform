<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApartmentParking extends Model
{
    protected $fillable = ['apartment_id', 'parking_number', 'price'];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}

