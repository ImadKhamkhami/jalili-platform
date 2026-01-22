<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Unit extends Model {
    protected $fillable = ['project_id','buyer_id','code','subtype','floor','area','price','status','notes'];
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function buyer(): BelongsTo { return $this->belongsTo(Buyer::class); }
    public function contract(): HasOne { return $this->hasOne(Contract::class); }
}

