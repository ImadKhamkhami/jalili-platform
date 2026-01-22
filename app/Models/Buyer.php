<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Buyer extends Model {
    protected $fillable = ['name','phone','email','national_id','address'];
    public function units(): HasMany { return $this->hasMany(Unit::class); }
    public function contracts(): HasMany { return $this->hasMany(Contract::class); }
}
