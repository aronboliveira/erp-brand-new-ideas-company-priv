<?php

namespace App\Models;

use Illuminate\{
    Database\Eloquent\Model,
    Support\Facades\DB
};
use App\Traits\UsesUuids;

class CustomField extends Model
{
    use UsesUuids;

    protected $fillable = [
        'name',
        'type',
        'module',
        'created_by',
    ];

    public static array $fieldTypes = [
        'text'     => 'Text',
        'email'    => 'Email',
        'number'   => 'Number',
        'date'     => 'Date',
        'textarea' => 'Textarea',
    ];

    public static array $modules = [
        'user'     => 'User',
        'customer' => 'Customer',
        'vendor'   => 'Vendor',
        'product'  => 'Product',
        'proposal' => 'Proposal',
        'Invoice'  => 'Invoice',
        'Bill'     => 'Bill',
        'account'  => 'Account',
    ];

    public static function saveData(Model $obj, array $data): void
    {
        if (empty($data)) return;

        $recordId = $obj->id;
        foreach ($data as $fieldId => $value) {
            DB::insert(
                'insert into custom_field_values (`record_id`, `field_id`, `value`, `created_at`, `updated_at`)
         values (?, ?, ?, ?, ?)
         on duplicate key update `value` = values(`value`), `updated_at` = values(`updated_at`)',
                [
                    $recordId,
                    $fieldId,
                    $value,
                    now(),
                    now(),
                ]
            );
        }
    }

    public static function getData(Model $obj, string $module): \Illuminate\Support\Collection
    {
        return DB::table('custom_field_values')
            ->select(['custom_field_values.value', 'custom_fields.id'])
            ->join('custom_fields', 'custom_field_values.field_id', '=', 'custom_fields.id')
            ->where('custom_fields.module', '=', $module)
            ->where('record_id', '=', $obj->id)
            ->get()
            ->pluck('value', 'id');
    }
}
