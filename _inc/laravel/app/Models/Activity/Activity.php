<?php

namespace App\Models;

use App\Config\Constants\{UsersConstants, PermissionsConstants};
use App\Traits\{LogsIcons, UsesUuids};
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use LogsIcons, UsesUuids;
    public static function getActivity(string $moduleType, string|int $moduleId): array
    {
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
                $result = [UsersConstants::COL_NM => $employee->first_name .
                    ' ' . $employee->last_name];
        }
        return $result;
    }
}
