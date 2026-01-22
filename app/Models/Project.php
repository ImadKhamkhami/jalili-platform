<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Project extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'type', 'address', 'floors','titre_foncier',];

    // الشركة المالكة
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // العمارات
    public function buildings()
    {
        return $this->hasMany(Building::class);
    }

    // الشقق (عبر العمارات)
    public function apartments()
    {
        return $this->hasManyThrough(Apartment::class, Building::class);
    }

    // القطع الأرضية إن احتجتها لاحقًا
    public function landPlots()
    {
        return $this->hasMany(LandPlot::class);
    }
        public function shops()
    {
        return $this->hasManyThrough(
            Shop::class,       // النموذج النهائي
            Building::class,   // النموذج الوسيط
            'project_id',      // FK في جدول buildings
            'building_id',     // FK في جدول shops
            'id',              // PK في projects
            'id'               // PK في buildings
        );
    }

}


