<?php

namespace App\Models\Scopes;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\PermissionsConstants as PMC;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Restricts Eloquent queries to records owned by the current authenticated user
 * (WHERE created_by = auth()->id()).
 *
 * Usage — apply in model booted():
 *   static::addGlobalScope(new CreatedByScope());
 *
 * Bypass in admin contexts:
 *   Model::withoutGlobalScope(CreatedByScope::class)->get();
 *
 * Super-admins and non-authenticated contexts are automatically bypassed.
 */
class CreatedByScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (!Auth::check()) return;

        $user = Auth::user();

        // Super-admins and admins see all records; scope applies only to regular users.
        if (
            method_exists($user, 'hasRole') &&
            $user->hasRole([PMC::SA, PMC::ADM])
        ) {
            return;
        }

        $table = $model->getTable();
        $col   = defined(DC::class . '::COL_TABLE_CREATOR') ? DC::COL_TABLE_CREATOR : 'created_by';
        $builder->where("{$table}.{$col}", Auth::id());
    }
}
