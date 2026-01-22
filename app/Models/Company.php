<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model {
    use HasFactory;
    protected $fillable = ['name',];
    
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    //public function projects(): HasMany { return $this->hasMany(Project::class); }

}
