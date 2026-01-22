<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Parking extends Model
{
    use HasFactory;

    protected $fillable = ['building_id','number','price'];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function apartmentParking()
    {
        return $this->hasOne(ApartmentParking::class);
    }

    public function apartment()
    {
        return $this->hasOneThrough(
            Apartment::class,
            ApartmentParking::class,
            'parking_id',   // fk on pivot
            'id',           // pk on apartment
            'id',           // pk on parking
            'apartment_id'  // fk on pivot
        );
    }
}
