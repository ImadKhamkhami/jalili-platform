<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Building extends Model
{
    use HasFactory;

    protected $fillable = ['project_id','name','floors_count'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }


    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }

    public function shops()
    {
        return $this->hasMany(Shop::class);
    }

    public function parkings()
    {
        return $this->hasMany(Parking::class);
    }
}
