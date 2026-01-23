<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'context',        // apartment | shop | land
        'project_id',

        // 🔥 الربط المباشر
        'apartment_id',
        'shop_id',
        'land_id',

           // ✅ أعدهما
         'building_number',
         'tranche_number',

        'payment_method', // cash | check | transfer | bill
        'amount',
        'paid_at',

        'receipt_number',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'date:Y-m-d',
    ];

    /* ===================== العلاقات ===================== */


    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function land()
    {
        return $this->belongsTo(LandPlot::class);
    }
protected static function booted()
{
    static::creating(function ($payment) {

        // إذا تم تمرير receipt_number يدويًا
        if (!empty($payment->receipt_number)) {
            return;
        }

        $year = now()->year;

        // 🔐 جلب آخر رقم بشكل آمن
        $lastNumber = self::whereNotNull('receipt_number')
            ->orderBy('id', 'desc')
            ->value('receipt_number');

        $next = 1;

        if ($lastNumber) {
            $next = intval(substr($lastNumber, -5)) + 1;
        }

        $payment->receipt_number =
            'REC-' . $year . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    });
}

    /* ===================== Helpers ===================== */

    public function getContextLabelAttribute()
    {
        return match ($this->context) {
            'apartment' => 'شقة',
            'shop'      => 'محل',
            'land'      => 'قطعة',
            default     => '-',
        };
    }

    public function getPaymentMethodLabelAttribute()
    {
        return match ($this->payment_method) {
            'cash'     => 'نقدًا',
            'check'    => 'شيك',
            'transfer' => 'تحويل',
            'bill'     => 'كمبيالة',
            default    => '-',
        };
    }
}
