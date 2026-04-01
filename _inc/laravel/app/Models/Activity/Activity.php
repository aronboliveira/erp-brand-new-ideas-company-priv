<?php

namespace App\Models;

use App\Config\Constants\{UsersConstants, PermissionsConstants};
use App\Traits\{LogsIcons, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{Log};

class Activity extends Model
{
    use LogsIcons, UsesUuids;
    # PULL REQUEST START — Proteção contra mass assignment
    protected $guarded = ['id'];
    # PULL REQUEST END
    public static function getActivity(string $moduleType, string|int|null $moduleId): array
    {
        try {
            if ($moduleId === null) return [UsersConstants::COL_NM => '-'];
            $moduleType = strtolower($moduleType);
            $result = [UsersConstants::COL_NM => '-'];
            if (
                $moduleType === strtolower(UsersConstants::TP_CT)
                || $moduleType === strtolower(PermissionsConstants::CPN)
            ) {
                $user = User::whereKey($moduleId)
                    ->where(UsersConstants::COL_TP, $moduleType)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($user) $result = [UsersConstants::COL_NM => $user?->name];
            } elseif ($moduleType === strtolower(class_basename(Employee::class))) {
                $employee = Employee::where('id', $moduleId)->orderBy('id', 'desc')->first();
                if ($employee)
                    $result = [UsersConstants::COL_NM => $employee->name ?? '-'];
            }
            return $result;
        } catch (\Throwable $e) {
            Log::error(static::class . '::getActivity — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
    }
}
