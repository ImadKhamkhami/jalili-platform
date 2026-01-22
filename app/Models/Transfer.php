<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'context',
        'unit_id',
        'from_customer_id',
        'to_customer_id',
        'transfer_number',
        'transfer_date',
        'notes',
    ];

    /* ================= العملاء ================= */

    public function fromCustomer()
    {
        return $this->belongsTo(Customer::class, 'from_customer_id');
    }

    public function toCustomer()
    {
        return $this->belongsTo(Customer::class, 'to_customer_id');
    }

    /* ================= الوحدات ================= */

    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'unit_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'unit_id');
    }

    public function land()
    {
        return $this->belongsTo(LandPlot::class, 'unit_id');
    }
    public function company()
{
    return $this->belongsTo(Company::class);
}


    /* ================= Accessors ذكية ================= */

    public function getProjectAttribute()
    {
        return match ($this->context) {
            'apartment' => $this->apartment?->building?->project,
            'shop'      => $this->shop?->building?->project,
            'land'      => $this->land?->project,
            default     => null,
        };
    }

    public function getUnitLabelAttribute()
    {
        return match ($this->context) {
            'apartment' => 'شقة ' . $this->apartment?->number,
            'shop'      => 'محل ' . $this->shop?->number,
            'land'      => 'قطعة ' . $this->land?->land_number,
            default     => '-',
        };
    }
}
