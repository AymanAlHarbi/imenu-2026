<?php

namespace Modules\Coupons\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Company as ParentCompany;

class Company extends ParentCompany
{
    public function coupons()
    {
        return $this->hasMany(Coupons::class, 'company_id', 'id')->orderBy('id', 'desc');
    }
}