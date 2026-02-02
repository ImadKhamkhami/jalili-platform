<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = [
        'context',
        'land_id',
        'apartment_id',
        'shop_id',
        'amount',
        'commission_date',
        'broker_name',
        'notes',
    ];

    public function land()
    {
        return $this->belongsTo(LandPlot::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

        public function project()
    {
        return match ($this->context) {
            'land'      => $this->land?->project(),
            'apartment' => $this->apartment?->building?->project(),
            'shop'      => $this->shop?->building?->project(),
            default     => null,
        };
    }
}

