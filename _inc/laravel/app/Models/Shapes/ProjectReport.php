<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\{HasFactory};
use Illuminate\Support\Facades\{Log};

class ProjectReport extends Document
{
    use HasFactory;

    public static function assignUser(string $user): string
    {
        try {
            $assignArr = explode(',', $user);
            $userNames = '';
            foreach ($assignArr as $assignId) {
                $u = User::find($assignId);
                if ($u) $userNames .= $u->name . ',';
            }
            return $userNames;
        } catch (\Throwable $e) {
            Log::error(static::class . '::assignUser — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return '';
        }
    }

    public static function milestone(string|int $id): string
    {
        $m = Milestone::find($id);
        return $m ? $m->title : '';
    }

    public static function status(string|int $id): string
    {
        $s = TaskStage::find($id);
        return $s ? $s->name : '';
    }
}
