<?php

namespace App\Models;

class ProjectReport extends Document
{
    public static function assignUser(string $user): string
    {
        $assignArr = explode(',', $user);
        $userNames = '';
        foreach ($assignArr as $assignId) {
            $u = User::find($assignId);
            if ($u) $userNames .= $u->name . ',';
        }
        return $userNames;
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
