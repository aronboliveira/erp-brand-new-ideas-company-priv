<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

function permissionRequiredCustom(string $perm, \Closure $view_func): \Closure
{
    return function (Request $request, ...$args) use ($perm, $view_func) {
        if (!$request->user()->hasPermissionTo($perm)) {
            Log::warning("User {$request->user()->id} lacks permission: $perm");
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Permission Denied by Permission Requirement'
            );
        }
        return $view_func($request, ...$args);
    };
}

function emailValidation(string $email, Builder $manager, ?array $excludes = null): array
{
    $errors = [];

    if (!Validator::make(['email' => $email], ['email' => 'email'])->passes()) {
        Log::warning("Email validation for $email failed");
        $errors[] = 'Invalid email address.';
    }

    $query = $manager->where('email', $email);

    if ($excludes) {
        foreach ($excludes as $key => $value) {
            $query->where($key, '!=', $value);
        }
    }

    if ($query->exists()) {
        $errors[] = 'A lead with this email already exists.';
    }

    return $errors;
}

function userIdValidation(array $data, $manager, array $filters = []): array
{
    $errors = [];
    $uid = $data['user_id'] ?? null;

    if (!$uid) {
        $errors[] = 'Assignee is required.';
        return $errors;
    }

    try {
        $manager = ($manager === 'users') ? User::query() : $manager;

        $exists = ($manager instanceof User)
            ? $manager->whereKey($uid)->where($filters)->exists()
            : $manager->where('user_id', $uid)->where($filters)->exists();

        if (!$exists) {
            $errors[] = 'Selected user is invalid.';
        }
    } catch (\Throwable $e) {
        $errors[] = match (get_class($e)) {
            \InvalidArgumentException::class => "Assignee invalid: {$e->getMessage()}",
            default => "Validation error: {$e->getMessage()}"
        };

        Log::warning("User ID validation failed for: " . ($uid ?? 'undefined'));
    }

    return $errors;
}
