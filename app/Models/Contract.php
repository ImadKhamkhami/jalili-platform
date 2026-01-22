<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model {
    protected $fillable = ['buyer_id','unit_id','contract_date','total_amount','paid_amount','remaining_amount','status','notes'];
    public function buyer(): BelongsTo { return $this->belongsTo(Buyer::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
}
