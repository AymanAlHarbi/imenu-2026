<?php

namespace App\Scopes;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $company_id = session('company_id', null);
        $restaurant_id = session('restaurant_id', null);

        $company_or_restaurant_id = $company_id ?? $restaurant_id;


        if($company_or_restaurant_id) {
            if (Schema::hasColumn($model->getTable(), 'company_id')) {
                $builder->where(function ($query) use ($company_or_restaurant_id) {
                    $query->where('company_id', $company_or_restaurant_id)
                          ->orWhereNull('company_id');
                });
            }

            if (Schema::hasColumn($model->getTable(), 'restaurant_id')) {
                $builder->where(function ($query) use ($company_or_restaurant_id) {
                    $query->where('restaurant_id', $company_or_restaurant_id)
                          ->orWhereNull('restaurant_id');
                });
            }
        }
    }
}
