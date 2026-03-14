<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\{AppModuleType, FieldType};
use App\Traits\{DescribesClientField, DescribesHtmlLinkedEntity, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{DB, Schema};

/**
 * @method static void saveData(Model $model, array $customFieldData)
 * @method static Collection getData(Model $model, string $module)
 * @property mixed $created_by
 */
class CustomField extends Model
{
    use HasAuditFields, UsesUuids, DescribesClientField, DescribesHtmlLinkedEntity;

    protected $table = DC::TABLE_CUSTOM_FIELDS;

    protected $fillable = [
        ...self::CLIENT_FIELD_COLS,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'readonly'       => 'boolean',
        'required'       => 'boolean',
        'multiline'      => 'boolean',
        'multiple'       => 'boolean',
        'autocapitalize' => 'boolean',
        'autocomplete'   => 'boolean',
        'autocorrect'    => 'boolean',
        'disabled'       => 'boolean',
        'tags'           => 'array',
        'options'        => 'array',
        'optgroups'      => 'array',
        'aria'           => 'array',
        'dataset'        => 'array',
        'accepts'        => 'array',
        'selectors'      => 'array',
        'size'           => 'array',
        DC::COL_C_AT     => 'datetime',
        DC::COL_U_AT     => 'datetime',
    ];

    protected $appends = [
        'client_payload',
        'constraint_payload',
    ];

    protected const CORE_COLS = ['name', 'type', 'module', 'description', 'default'];

    /** @var array<string,string> */
    public static array $fieldTypes = [];

    /** @var array<string,string> */
    public static array $modules = [];

    protected const DEFAULT_NUMERIC_MIN = '0';
    protected const DEFAULT_NUMERIC_MAX = '9007199254740991';

    protected static function booted(): void
    {
        if (self::$fieldTypes === []) self::$fieldTypes = FieldType::labels();
        if (self::$modules === []) self::$modules = AppModuleType::labels();
    }

    public function getClientPayloadAttribute(): array
    {
        // Uses trait methods (must exist) + local columns
        $base = [];

        foreach (self::CORE_COLS as $col) {
            if (!Schema::hasColumn($this->getTable(), $col)) continue;
            $base[$col] = $this->getAttribute($col);
        }

        $client = array_merge($base, $this->getClientFieldAttributes(), $this->getHtmlLinkedAttributes(true));
        return static::normalizeClientFieldPayload($client);
    }

    public function getConstraintPayloadAttribute(): array
    {
        $out = [];
        $table = $this->getTable();

        foreach (static::clientFieldConstraintColumns() as $col) {
            if (!Schema::hasColumn($table, $col)) continue;
            $out[$col] = $this->getAttribute($col);
        }

        // Adds html-linked accessibility metadata if needed for validation layers
        foreach (['aria', 'dataset'] as $col) {
            if (!Schema::hasColumn($table, $col)) continue;
            $out[$col] = $this->getAttribute($col);
        }

        return $out;
    }

    /**
     * Business helper: map options => [value => text] for fast UI binds.
     */
    public function optionsMap(): array
    {
        $options = $this->getAttribute('options');
        if (!is_array($options)) return [];

        $out = [];
        foreach ($options as $opt) {
            if (!is_array($opt)) continue;
            $v = isset($opt['value']) ? (string) $opt['value'] : '';
            $t = isset($opt['text']) ? (string) $opt['text'] : $v;
            if ($v === '') continue;
            $out[$v] = $t;
        }
        return $out;
    }

    /**
     * Business helper: returns true if field currently behaves as “locked”.
     */
    public function isLocked(): bool
    {
        $disabled = (bool) $this->getAttribute('disabled');
        $readonly = (bool) $this->getAttribute('readonly');
        return $disabled || $readonly;
    }

    /**
     * Insert or update custom field values for a model.
     */
    public static function saveData(Model $model, array $data): void
    {
        foreach ($data as $fieldId => $value) {
            DB::insert(
                'INSERT INTO custom_field_values (id, record_id, field_id, value, created_by, created_at) '
                . 'VALUES (?, ?, ?, ?, ?, NOW()) '
                . 'ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()',
                [(string) \Illuminate\Support\Str::uuid(), $model->getKey(), $fieldId, $value, $model->getKey()]
            );
        }
    }

    /**
     * Get custom field values for a model.
     */
    public static function getData(Model $model, string $module): Collection
    {
        return DB::table('custom_field_values')
            ->select('custom_field_values.value', 'custom_field_values.id')
            ->join('custom_fields', 'custom_fields.id', '=', 'custom_field_values.field_id')
            ->where('custom_field_values.record_id', $model->getKey())
            ->where('custom_fields.module', $module)
            ->get()
            ->pluck('value', 'id');
    }
}
