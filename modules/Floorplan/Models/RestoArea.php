<?php

namespace Modules\Floorplan\Models;

use App\Models\Company;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestoArea extends Model
{
    public $table = 'restoareas';

    protected $fillable = [
        'name', 'company_id',
    ];

    public function tables(): HasMany
    {
        return $this->hasMany(Tables::class, 'restoarea_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'restaurant_id');
    }

    protected static function booted(){
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model){
            $company_id=session('company_id',null);
            if($company_id){
                $model->company_id=$company_id;
            }
        });
    }
}
