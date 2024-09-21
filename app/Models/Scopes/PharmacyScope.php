<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PharmacyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (filament()->auth()->check()) {
            if (filament()->auth()->user()->is_admin) {
                $builder->where('user_id', filament()->auth()->id());
            } else {
                $builder->where('user_id', filament()->auth()->user()->user_id);
            }
        }
    }
}
