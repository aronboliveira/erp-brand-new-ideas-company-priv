<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};

class LeadActivityLog extends Model
{
    use HasFactory;
    use UsesUuids;
    protected $fillable = [
        'user_id', 'lead_id', 'log_type', 'remark'
    ];
    private const ICONS = [
        'Move'              => 'ti-arrows-maximize',
        'Add Product'       => 'ti-layout-grid-add',
        'Upload File'       => 'ti-cloud-upload',
        'Update Sources'    => 'ti-brand-open-source',
        'Create Lead Call'  => 'ti-phone-plus',
        'Create Lead Email' => 'ti-mail'
    ];
    private static ?string $userData = null;

    public function getLeadRemark(): string
    {
        if (self::$userData === null)
            self::$userData = $this->fetchGetLeadRemark();
        return self::$userData;
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function logIcon(): string
    {
        return self::ICONS[$this->log_type] ?? '';
    }

    public function fetchGetLeadRemark(): string
    {
        $data = json_decode($this->remark, true) ?? [];
        $name = $this->user?->name ?? '';
        return match ($this->log_type) {
            'Upload File'       => $name . ' ' . __('Upload new file') . ' <b>' . $data['file_name'] . '</b>',
            'Add Product'       => $name . ' ' . __('Add new Products') . ' <b>' . $data['title'] . '</b>',
            'Update Sources'    => $name . ' ' . __('Update Sources'),
            'Create Lead Call'  => $name . ' ' . __('Create new Lead Call'),
            'Create Lead Email' => $name . ' ' . __('Create new Lead Email'),
            'Move'              => $name . ' ' . __('Moved the deal') . ' <b>' . $data['title'] . '</b> ' .
                __('from') . ' ' . __(
                    ucwords($data['old_status'])
                ) . ' ' . __('to') . ' ' . __(
                    ucwords($data['new_status'])
                ),
            default             => $this->remark
        };
    }
}
