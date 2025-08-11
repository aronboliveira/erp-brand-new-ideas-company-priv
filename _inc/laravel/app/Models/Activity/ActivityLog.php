<?php

namespace App\Models;

use App\Traits\{LogsIcons, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class ActivityLog extends Model
{
    use LogsIcons, UsesUuids;
    private const FILLABLE = [
        'user_id', 'project_id', 'task_id', 'deal_id', 'log_type', 'remark',
    ];
    protected $fillable = self::FILLABLE;
    private static $userData = null;
    private const ICONS = [
        'Invite User'                  => 'ti-user',
        'User Assigned to the Task'    => 'ti-user-check',
        'User Removed from the Task'   => 'ti-user-x',
        'Upload File'                  => 'ti-cloud-upload',
        'Create Milestone'             => 'ti-crop',
        'Create Bug'                   => 'ti-bug',
        'Create Task'                  => 'ti-square-plus',
        'Move Task'                    => 'ti-command',
        'Create Expense'               => 'ti-clipboard-list',
        'Move'                         => 'ti-arrows-maximize',
        'Add Product'                  => 'ti-shopping-cart-plus',
        'Update Sources'               => 'ti-brand-open-source',
        'Create Deal Call'             => 'ti-phone-plus',
        'Create Deal Email'            => 'ti-record-mail',
        'Create Invoice'               => 'ti-file-plus',
        'Add Contact'                  => 'ti-notebook',
    ];

    public function getRemark(): string
    {
        if (!self::$userData)
            self::$userData = self::fetchgetRemark();
        return self::$userData;
    }

    public function user(): HasOne
    {
        $cls = get_class($this);
        return $this->hasOne(substr($cls, 0, strrpos($cls, '\\')) . '\\' . ucfirst(__FUNCTION__), 'id', 'user_id');
    }

    public function userDetail(): HasOne
    {
        $id = 'user_id';
        $cls = get_class($this);
        return $this->hasOne(substr($cls, 0, strrpos($cls, '\\')) . '\\' . ucfirst(__FUNCTION__), $id, $id);
    }

    public function fetchGetRemark(): string
    {
        $data = json_decode($this->remark, true) ?: [];
        $name = $this->user->name ?? '';
        switch ($this->log_type) {
            case 'Invite User':
                return "{$name} " . __('has invited') . " <b>{$data['title']}</b>";
            case 'User Assigned to the Task':
                return "{$name} " . __('has assigned task') . " <b>{$data['task_name']}</b> " . __('to') . " <b>{$data['member_name']}</b>";
            case 'User Removed from the Task':
                return "{$name} " . __('has removed') . " <b>{$data['member_name']}</b> " . __('from task') . " <b>{$data['task_name']}</b>";
            case 'Upload File':
                return "{$name} " . __('uploaded new file') . " <b>{$data['file_name']}</b>";
            case 'Create Bug':
                return "{$name} " . __('created new bug') . " <b>{$data['title']}</b>";
            case 'Create Milestone':
                return "{$name} " . __('created new milestone') . " <b>{$data['title']}</b>";
            case 'Create Task':
                return "{$name} " . __('created new task') . " <b>{$data['title']}</b>";
            case 'Move Task':
                return "{$name} " . __('moved the task') . " <b>{$data['title']}</b> " . __('from') . " <b>" . __(ucwords($data['old_stage'])) . "</b> " . __('to') . " <b>" . __(ucwords($data['new_stage'])) . "</b>";
            case 'Create Expense':
                return "{$name} " . __('created new expense') . " <b>{$data['title']}</b>";
            case 'Add Product':
                return "{$name} " . __('added new products') . " <b>{$data['title']}</b>";
            case 'Update Sources':
                return "{$name} " . __('updated sources');
            case 'Create Deal Call':
                return "{$name} " . __('created new deal call');
            case 'Create Deal Email':
                return "{$name} " . __('created new deal email');
            case 'Move':
                return "{$name} " . __('moved the deal') . " <b>{$data['title']}</b> " . __('from') . " <b>" . __(ucwords($data['old_status'])) . "</b> " . __('to') . " <b>" . __(ucwords($data['new_status'])) . "</b>";
            default:
                return $this->remark;
        }
    }

    public function logIcon(): string
    {
        return self::ICONS[$this->log_type] ?? '';
    }
}
