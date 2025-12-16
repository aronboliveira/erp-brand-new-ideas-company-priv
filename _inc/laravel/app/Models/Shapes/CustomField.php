<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\{AppModuleType, FieldType, MimeType};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\{Carbon, Str};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomField extends Model
{
    use HasAuditFields, UsesUuids;

    protected $table = DC::TABLE_CUSTOM_FIELDS;

    protected $fillable = [
        'name',
        'type',
        'module',
        'description',
        'tags',
        'default',
        'placeholder',
        'pattern',
        'readonly',
        'required',
        'multiline',
        'multiple',
        'autocapitalize',
        'autocomplete',
        'autocorrect',
        'disabled',
        'min',
        'max',
        'step',
        'minlength',
        'maxlength',
        'rows',
        'cols',
        'wrap',
        'spellcheck',
        'options',
        'optgroups',
        'aria',
        'dataset',
        'accepts',
        'selectors',
        'size',
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

    /** @var array<string,string> */
    public static array $fieldTypes = [];

    /** @var array<string,string> */
    public static array $modules = [];

    protected const DEFAULT_NUMERIC_MIN = '0';
    protected const DEFAULT_NUMERIC_MAX = '9007199254740991';

    protected const ALLOWED_ARIA = [
        'aria-label',
        'aria-labelledby',
        'aria-describedby',
        'aria-hidden',
        'aria-required',
        'aria-invalid',
        'aria-live',
        'aria-checked',
        'aria-selected',
        'aria-expanded',
        'aria-controls',
        'aria-current',
        'aria-valuemin',
        'aria-valuemax',
        'aria-valuenow',
        'aria-valuetext',
        'aria-level',
        'aria-orientation',
        'aria-pressed',
        'aria-readonly',
    ];

    protected static function booted(): void
    {
        if (self::$fieldTypes === []) self::$fieldTypes = FieldType::labels();
        if (self::$modules === []) self::$modules = AppModuleType::labels();

        static::saving(function (self $model): void {
            try {
                self::normalizeCoreAttributes($model);
                self::normalizeFlagsAndRanges($model);
                self::normalizeOptionsAndMetadata($model);
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed to normalize CustomField before saving', [
                    'id'    => $model->getAttribute('id'),
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    protected static function normalizeCoreAttributes(self $model): void
    {
        $name = trim((string) $model->getAttribute('name'));
        if ($name === '') $name = DC::DEFAULT_TT;
        $model->setAttribute('name', $name);

        $typeEnum = FieldType::normalize($model->getAttribute('type'));
        $model->setAttribute('type', $typeEnum->value);

        $moduleEnum = AppModuleType::normalize($model->getAttribute('module'));
        $model->setAttribute('module', $moduleEnum->value);

        $description = $model->getAttribute('description');
        if ($description !== null) {
            $description = trim((string) $description);
            if ($description === '') $description = null;
        }
        $model->setAttribute('description', $description);

        $tags = self::normalizeArrayField($model->getAttribute('tags'));
        $tags = self::sanitizeTagsArray($tags);
        $model->setAttribute('tags', $tags ?: null);

        $default = $model->getAttribute('default');
        if ($default !== null) {
            $default = trim((string) $default);
            if ($default === '') $default = null;
        }
        $model->setAttribute('default', $default);
    }

    protected static function normalizeFlagsAndRanges(self $model): void
    {
        $type = FieldType::normalize($model->getAttribute('type'));

        $isTextual  = $type->isTextual();
        $isNumeric  = $type->isNumeric();
        $isDateTime = $type->isDateTime();
        $isTextarea = $type === FieldType::Textarea;
        $isFile     = $type === FieldType::File;
        $isEmail    = $type === FieldType::Email;
        $isUrl      = $type === FieldType::Url;
        $isPassword = $type === FieldType::Password;
        $isSelect   = $type === FieldType::Select;

        // placeholder: só para tipos textuais
        $placeholder = $model->getAttribute('placeholder');
        if ($isTextual) {
            $placeholder = $placeholder !== null ? trim((string) $placeholder) : null;
            if ($placeholder === '') $placeholder = null;
        } else $placeholder = null;
        $model->setAttribute('placeholder', $placeholder);

        $pattern = $model->getAttribute('pattern');
        if ($isTextual) {
            $pattern = $pattern !== null ? trim((string) $pattern) : null;
            if ($pattern === '') $pattern = null;
            if (!$pattern) {
                $type = $model->getAttribute('type');
                if ($type === FieldType::Email->value)
                    $pattern = '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$';
                elseif ($type === FieldType::Url->value)
                    $pattern = '^(https?:\/\/)?.*(\.[a-zA-Z]{2,})(\/\S*)?$';
                elseif ($type === FieldType::Tel->value)
                    $pattern = '^\+?[0-9\s\-\(\)]{7,15}$';
                if (isset($pattern))
                    $model->setAttribute('pattern', $pattern);
            }
        } else $pattern = null;

        // required / disabled sempre boolean
        $model->setAttribute('required', self::parseBoolean($model->getAttribute('required'), false));
        $model->setAttribute('disabled', self::parseBoolean($model->getAttribute('disabled'), false));

        // readonly / multiline: só textuais
        $readonly = $isTextual ? self::parseBoolean($model->getAttribute('readonly'), false) : null;
        $model->setAttribute('readonly', $readonly);

        $multiline = $isTextual ? self::parseBoolean($model->getAttribute('multiline'), false) : null;
        $model->setAttribute('multiline', $multiline);

        // multiple: somente email, file e select
        $multipleAllowed = $isEmail || $isFile || $isSelect;
        $multiple = $multipleAllowed ? self::parseBoolean($model->getAttribute('multiple'), false) : null;
        $model->setAttribute('multiple', $multiple);

        // autocapitalize / autocorrect: textuais exceto email/url/password
        $autocapAllowed = $isTextual && !$isEmail && !$isUrl && !$isPassword;
        $autocap = $autocapAllowed ? self::parseBoolean($model->getAttribute('autocapitalize'), false) : null;
        $model->setAttribute('autocapitalize', $autocap);

        $autocomplete = $isTextual ? self::parseBoolean($model->getAttribute('autocomplete'), false) : null;
        $model->setAttribute('autocomplete', $autocomplete);

        $autocorrectAllowed = $autocapAllowed;
        $autocorrect = $autocorrectAllowed ? self::parseBoolean($model->getAttribute('autocorrect'), false) : null;
        $model->setAttribute('autocorrect', $autocorrect);

        // minlength/maxlength: apenas campos textuais
        if ($isTextual) {
            $minlength = $model->getAttribute('minlength');
            $maxlength = $model->getAttribute('maxlength');

            $min = is_numeric($minlength) && (int) $minlength >= 0 ? (int) $minlength : 0;
            $max = is_numeric($maxlength) && (int) $maxlength > 0 ? (int) $maxlength : 1024;

            if ($min > $max) {
                $tmp = $min;
                $min = $max;
                $max = $tmp;
            }

            $model->setAttribute('minlength', $min);
            $model->setAttribute('maxlength', $max);
        } else {
            $model->setAttribute('minlength', null);
            $model->setAttribute('maxlength', null);
        }

        // rows/cols/wrap: somente textarea
        if ($isTextarea) {
            $rows = $model->getAttribute('rows');
            $cols = $model->getAttribute('cols');

            $rows = is_numeric($rows) && (int) $rows > 0 ? (int) $rows : 2;
            $cols = is_numeric($cols) && (int) $cols > 0 ? (int) $cols : 20;

            $wrap = $model->getAttribute('wrap');
            $wrap = in_array($wrap, ['soft', 'hard'], true) ? $wrap : 'soft';

            $model->setAttribute('rows', $rows);
            $model->setAttribute('cols', $cols);
            $model->setAttribute('wrap', $wrap);
        } else {
            $model->setAttribute('rows', null);
            $model->setAttribute('cols', null);
            $model->setAttribute('wrap', null);
        }

        // spellcheck: apenas textuais
        if ($isTextual) {
            $spellcheck = $model->getAttribute('spellcheck');
            $spellcheck = in_array($spellcheck, ['true', 'false', 'default'], true)
                ? $spellcheck
                : 'false';
            $model->setAttribute('spellcheck', $spellcheck);
        } else $model->setAttribute('spellcheck', null);

        // min/max/step: numérico ou date/time; demais => null
        if ($isNumeric || $isDateTime) self::normalizeRangeAttributes($model, $type);
        else {
            $model->setAttribute('min', null);
            $model->setAttribute('max', null);
            $model->setAttribute('step', null);
        }
    }

    protected static function normalizeRangeAttributes(self $model, FieldType $type): void
    {
        $min  = trim((string) ($model->getAttribute('min') ?? ''));
        $max  = trim((string) ($model->getAttribute('max') ?? ''));
        $step = trim((string) ($model->getAttribute('step') ?? ''));

        if ($type->isNumeric()) {
            if ($min === '' || !is_numeric($min)) $min = self::DEFAULT_NUMERIC_MIN;
            if ($max === '' || !is_numeric($max)) $max = self::DEFAULT_NUMERIC_MAX;

            $minFloat = (float) $min;
            $maxFloat = (float) $max;

            if ($minFloat > $maxFloat) {
                $tmp     = $minFloat;
                $minFloat = $maxFloat;
                $maxFloat = $tmp;
            }

            $min = rtrim(rtrim(sprintf('%.14F', $minFloat), '0'), '.');
            $max = rtrim(rtrim(sprintf('%.14F', $maxFloat), '0'), '.');

            if ($step === '') $step = 'any';
            elseif ($step !== 'any') {
                if (!is_numeric($step) || (float) $step <= 0) $step = 'any';
                else {
                    $stepFloat = (float) $step;
                    $step = rtrim(rtrim(sprintf('%.14F', $stepFloat), '0'), '.');
                }
            }
        } else {
            [$defaultMin, $defaultMax] = self::defaultDateRange($type);

            if ($min === '' || !self::isValidDateBoundary($type, $min)) $min = $defaultMin;
            if ($max === '' || !self::isValidDateBoundary($type, $max)) $max = $defaultMax;

            if (self::compareDateBoundaries($type, $min, $max) > 0) {
                $tmp = $min;
                $min = $max;
                $max = $tmp;
            }

            // Para campos de data/tempo, step padrão = 1 unidade
            if ($step === '' || !ctype_digit($step) || (int) $step <= 0) $step = '1';
        }

        $model->setAttribute('min', $min);
        $model->setAttribute('max', $max);
        $model->setAttribute('step', $step);
    }

    protected static function defaultDateRange(FieldType $type): array
    {
        return match ($type) {
            FieldType::Time          => ['00:00', '23:59'],
            FieldType::Month         => ['1970-01', '2099-12'],
            FieldType::Week          => ['1970-W01', '2099-W52'],
            FieldType::DateTimeLocal => ['1970-01-01T00:00', '2099-12-31T23:59'],
            default                  => ['1970-01-01', '2099-12-31'],
        };
    }

    protected static function isValidDateBoundary(FieldType $type, string $value): bool
    {
        return self::toCarbonForBoundary($type, $value) instanceof Carbon;
    }

    protected static function compareDateBoundaries(FieldType $type, string $a, string $b): int
    {
        $ca = self::toCarbonForBoundary($type, $a);
        $cb = self::toCarbonForBoundary($type, $b);

        if (!$ca || !$cb) return 0;
        if ($ca->equalTo($cb)) return 0;

        return $ca->lessThan($cb) ? -1 : 1;
    }

    protected static function toCarbonForBoundary(FieldType $type, string $value): ?Carbon
    {
        try {
            return match ($type) {
                FieldType::Time          => Carbon::createFromFormat('H:i', $value),
                FieldType::Month         => Carbon::parse($value . '-01'),
                FieldType::Week          => Carbon::parse($value),
                FieldType::DateTimeLocal => Carbon::parse($value),
                default                  => Carbon::parse($value),
            };
        } catch (\Throwable) {
            return null;
        }
    }

    protected static function normalizeOptionsAndMetadata(self $model): void
    {
        $type = FieldType::normalize($model->getAttribute('type'));

        // options / optgroups: select OU tipos textuais/numericos/datetime (para datalist)
        $supportsOptions = $type === FieldType::Select
            || $type->isTextual()
            || $type->isNumeric()
            || $type->isDateTime();

        if ($supportsOptions) {
            $options   = self::normalizeArrayField($model->getAttribute('options'));
            $optgroups = self::normalizeArrayField($model->getAttribute('optgroups'));

            $options   = self::sanitizeOptionsArray($options);
            $optgroups = self::sanitizeOptgroupsArray($optgroups);

            $model->setAttribute('options', $options ?: null);
            $model->setAttribute('optgroups', $optgroups ?: null);
        } else {
            $model->setAttribute('options', null);
            $model->setAttribute('optgroups', null);
        }

        // aria: valida chaves permitidas
        $aria = self::normalizeAssocArray($model->getAttribute('aria'));
        $aria = self::sanitizeAriaArray($aria);
        $model->setAttribute('aria', $aria ?: null);

        // dataset: normaliza data-*
        $dataset = self::normalizeAssocArray($model->getAttribute('dataset'));
        $dataset = self::sanitizeDatasetArray($dataset);
        $model->setAttribute('dataset', $dataset ?: null);

        // accepts: somente file; convertido pra mimetype/ext aceitos
        if ($type === FieldType::File) {
            $accepts = self::normalizeArrayField($model->getAttribute('accepts'));
            $accepts = self::sanitizeAcceptsArray($accepts);
            $model->setAttribute('accepts', $accepts ?: null);
        } else $model->setAttribute('accepts', null);

        // selectors: lista de .class / #id válidos
        $selectors = self::normalizeArrayField($model->getAttribute('selectors'));
        $selectors = self::sanitizeSelectorsArray($selectors);
        $model->setAttribute('selectors', $selectors ?: null);

        // size: width/height com unidades válidas
        $size = self::normalizeAssocArray($model->getAttribute('size'));
        $size = self::sanitizeSizeArray($size);
        $model->setAttribute('size', $size);
    }

    /* -----------------------------------------------------------------
     |  Helpers genéricos
     | -----------------------------------------------------------------
     */

    protected static function parseBoolean(mixed $value, bool $default = false): bool
    {
        if (is_bool($value)) return $value;
        if ($value === null) return $default;

        $value = strtolower(trim((string) $value));

        return match ($value) {
            '1', 'true', 'yes', 'on'  => true,
            '0', 'false', 'no', 'off' => false,
            default                   => $default,
        };
    }

    protected static function normalizeArrayField(mixed $value): array
    {
        if (is_array($value)) return $value;
        if ($value === null) return [];

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') return [];

            try {
                $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) return $decoded;
            } catch (\Throwable) {
            }

            return [$trimmed];
        }

        return [$value];
    }

    protected static function normalizeAssocArray(mixed $value): array
    {
        $array = self::normalizeArrayField($value);
        if ($array === []) return [];

        if (self::isList($array)) {
            $assoc = [];
            foreach ($array as $item) {
                if (!is_array($item) || count($item) !== 2) continue;
                [$k, $v] = array_values($item);
                if (!is_string($k)) continue;
                $assoc[$k] = $v;
            }
            return $assoc ?: [];
        }

        return $array;
    }

    protected static function isList(array $array): bool
    {
        if ($array === []) return true;
        return array_keys($array) === range(0, count($array) - 1);
    }

    protected static function sanitizeTagsArray(array $tags): array
    {
        $clean = [];

        foreach ($tags as $tag) {
            if (!is_string($tag)) continue;
            $tag = trim($tag);
            if ($tag === '') continue;

            $clean[] = Str::slug($tag, '_');
        }

        return array_values(array_unique($clean));
    }

    protected static function sanitizeOptionsArray(array $options): array
    {
        $result = [];

        foreach ($options as $option) {
            if (!is_array($option)) {
                if (is_scalar($option)) {
                    $text = trim((string) $option);
                    if ($text === '') continue;
                    $result[] = [
                        'text'     => $text,
                        'value'    => $text,
                        'selected' => false,
                        'disabled' => false,
                        'group'    => null,
                    ];
                }
                continue;
            }

            $text  = isset($option['text']) ? trim((string) $option['text']) : '';
            $value = isset($option['value']) ? trim((string) $option['value']) : '';

            if ($text === '' && $value === '') continue;
            if ($text === '') $text = $value;
            if ($value === '') $value = $text;

            $selected = array_key_exists('selected', $option)
                ? self::parseBoolean($option['selected'], false)
                : false;

            $disabled = array_key_exists('disabled', $option)
                ? self::parseBoolean($option['disabled'], false)
                : false;

            $group = isset($option['group']) ? trim((string) $option['group']) : null;
            if ($group === '') $group = null;

            $result[] = [
                'text'     => $text,
                'value'    => $value,
                'selected' => $selected,
                'disabled' => $disabled,
                'group'    => $group,
            ];
        }

        $unique = [];
        $seen   = [];

        foreach ($result as $opt) {
            $key = $opt['value'] . '|' . ($opt['group'] ?? '');
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $unique[]   = $opt;
        }

        return $unique;
    }

    protected static function sanitizeOptgroupsArray(array $optgroups): array
    {
        $labels = [];

        foreach ($optgroups as $group) {
            if (is_array($group) && isset($group['label'])) $label = trim((string) $group['label']);
            else $label = trim((string) $group);

            if ($label === '') continue;
            $labels[] = $label;
        }

        $labels = array_values(array_unique($labels));

        return array_map(fn(string $label) => ['label' => $label], $labels);
    }

    protected static function sanitizeAriaArray(array $aria): array
    {
        $result = [];

        foreach ($aria as $key => $value) {
            if (!is_string($key)) continue;

            $attr = strtolower(trim($key));
            if (!str_starts_with($attr, 'aria-')) $attr = 'aria-' . ltrim($attr, '-');
            if (!in_array($attr, self::ALLOWED_ARIA, true)) continue;

            if ($value === null) continue;
            if (is_bool($value)) $value = $value ? 'true' : 'false';
            else {
                $value = trim((string) $value);
                if ($value === '') continue;
            }

            $result[$attr] = $value;
        }

        return $result;
    }

    protected static function sanitizeDatasetArray(array $dataset): array
    {
        $result = [];

        foreach ($dataset as $key => $value) {
            if (!is_string($key)) continue;

            $attr = strtolower(trim($key));
            if (!str_starts_with($attr, 'data-')) $attr = 'data-' . ltrim($attr, '-');

            if ($value === null) continue;
            if (is_bool($value)) $value = $value ? 'true' : 'false';
            else {
                $value = trim((string) $value);
                if ($value === '') continue;
            }

            $result[$attr] = $value;
        }

        return $result;
    }

    protected static function sanitizeAcceptsArray(array $accepts): array
    {
        $result = [];

        foreach ($accepts as $entry) {
            if (!is_string($entry)) continue;

            $entry = strtolower(trim($entry));
            if ($entry === '') continue;

            // MIME type
            if (str_contains($entry, '/')) {
                $mime = MimeType::normalize($entry);
                $result[] = $mime?->value ?? $entry;
                continue;
            }

            // Extensão
            $ext = ltrim($entry, '.');
            if ($ext === '') continue;

            $mime = MimeType::fromExtension($ext);
            if ($mime) $result[] = $mime->value;
            else $result[] = '.' . $ext;
        }

        return array_values(array_unique($result));
    }

    protected static function sanitizeSelectorsArray(array $selectors): array
    {
        $clean = [];

        foreach ($selectors as $selector) {
            if (!is_string($selector)) continue;

            $selector = trim($selector);
            if ($selector === '') continue;
            if (!preg_match('/^[#.][A-Za-z0-9_-]+$/', $selector)) continue;

            $clean[] = $selector;
        }

        return array_values(array_unique($clean));
    }

    protected static function sanitizeSizeArray(?array $size): ?array
    {
        if (!$size) return null;

        $result = [];

        foreach (['width', 'height'] as $key) {
            if (!array_key_exists($key, $size)) continue;

            $value = trim((string) $size[$key]);
            if ($value === '') continue;
            if (!preg_match('/^\d+(\.\d+)?(px|em|rem|%|vw|vh)$/', $value)) continue;

            $result[$key] = $value;
        }

        return $result === [] ? null : $result;
    }

    public static function saveData(Model $obj, array $data): void
    {
        if (empty($data)) return;

        $recordId = $obj->getAttribute('id');

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
            ->where('record_id', '=', $obj->getAttribute('id'))
            ->get()
            ->pluck('value', 'id');
    }
}
