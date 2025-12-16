<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    CompaniesConstants as CC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Enums\AssetType;
use App\Models\Employee;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Support\Facades\Log;

class Asset extends Model
{
    use HasAuditFields, UsesUuids, NormalizesArrays;

    protected $table = DC::TABLE_AST;

    protected $fillable = [
        'serial',
        'category',
        'type',
        'name',
        UC::COL_EMP_ID,
        CC::COL_PRC_DT,
        CC::COL_SPT_DT,
        'amount',
        'description',
        'purpose',
        'order',
        'transaction',
        BC::COL_SIGN_BY,
        BC::COL_SIGN_BY_NAME,
        'attachments',
        'metadata',
        'tags',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'attachments'     => 'array',
        'metadata'        => 'array',
        'tags'            => 'array',
        CC::COL_PRC_DT    => 'date',
        CC::COL_SPT_DT    => 'date',
        DC::COL_C_AT      => 'datetime',
        DC::COL_U_AT      => 'datetime',
    ];

    protected $appends = [
        'type_enum',
        'is_depreciable',
        'typical_lifespan_years',
    ];

    public static function booted(): void
    {
        static::saving(function (self $model): void {
            try {
                $serial = $model->getAttribute('serial');
                if ($serial !== null) {
                    $serial = strtoupper(trim((string) $serial));
                    if ($serial === '') $serial = null;
                }
                $model->setAttribute('serial', $serial);

                foreach (['category', UC::COL_EMP_ID, 'order', 'transaction', BC::COL_SIGN_BY] as $column) {
                    $value = $model->getAttribute($column);
                    if ($value === null) {
                        $model->setAttribute($column, null);
                        continue;
                    }
                    $value = trim((string) $value);
                    if ($value === '') $value = null;
                    $model->setAttribute($column, $value);
                }

                $rawType  = (string) ($model->getAttribute('type') ?? '');
                $typeEnum = AssetType::normalize($rawType !== '' ? $rawType : null);
                $model->setAttribute('type', $typeEnum->value);

                $purchase  = self::parseDate($model->getAttribute(CC::COL_PRC_DT));
                $supported = self::parseDate($model->getAttribute(CC::COL_SPT_DT));

                if ($purchase && $supported && $supported->lessThan($purchase)) {
                    $tmp      = $purchase;
                    $purchase = $supported;
                    $supported = $tmp;
                }

                $model->setAttribute(CC::COL_PRC_DT, $purchase?->toDateString());
                $model->setAttribute(CC::COL_SPT_DT, $supported?->toDateString());

                $amountRaw = $model->getAttribute('amount');
                $amount    = is_numeric($amountRaw) ? (float) $amountRaw : 0.0;
                if ($amount < 0) $amount = 0.0;
                $model->setAttribute('amount', $amount);

                foreach (['name', 'description', 'purpose', BC::COL_SIGN_BY_NAME] as $column) {
                    $value = $model->getAttribute($column);
                    if ($value === null) {
                        if ($column === 'name') $model->setAttribute($column, DC::DEFAULT_TT);
                        else $model->setAttribute($column, null);
                        continue;
                    }
                    $value = trim((string) $value);
                    if ($value === '') {
                        if ($column === 'name') $value = DC::DEFAULT_TT;
                        else $value = null;
                    }
                    $model->setAttribute($column, $value);
                }

                $attachments = self::normalizeArrayField($model->getAttribute('attachments'));
                $metadata    = self::normalizeArrayField($model->getAttribute('metadata'));
                $tags        = self::normalizeArrayField($model->getAttribute('tags'));

                $attachments = self::sanitizeAttachmentsArray($attachments);
                $tags        = self::sanitizeTagsArray($tags);

                $model->setAttribute('attachments', array_values($attachments));
                $model->setAttribute('metadata', $metadata);
                $model->setAttribute('tags', array_values($tags));

                $model->ensureJsonAttributesAreEncoded([
                    'attachments',
                    'metadata',
                    'tags',
                ]);

                $employeeId = $model->getAttribute(UC::COL_EMP_ID);

                if ($employeeId && class_exists(Employee::class)) {
                    try {
                        $employee = Employee::query()->with('user')->find($employeeId);
                        $user     = $employee?->user;

                        $type = null;
                        if ($user && isset($user->{UC::COL_TP}) && is_string($user->{UC::COL_TP}))
                            $type = strtolower(trim($user->{UC::COL_TP}));

                        $privileged = in_array($type, ['admin', 'super admin', 'super_admin', 'company'], true);

                        $signBy   = $model->getAttribute(BC::COL_SIGN_BY);
                        $signName = trim((string) ($model->getAttribute(BC::COL_SIGN_BY_NAME) ?? ''));

                        if (!$privileged) {
                            if (!$signBy || $signName === '') {
                                $message = 'Asset requires signer when employee owner is not privileged.';
                                Log::warning(static::class . ' signature missing for non-privileged employee', [
                                    'asset_id'    => $model->getAttribute('id'),
                                    'employee_id' => $employeeId,
                                    'user_type'   => $type,
                                ]);

                                if (function_exists('app') && app()->environment('production'))
                                    throw new \RuntimeException($message);
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning(static::class . ' failed to validate asset signer', [
                            'error'       => $e->getMessage(),
                            'asset_id'    => $model->getAttribute('id'),
                            'employee_id' => $employeeId,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed during saving normalization', [
                    'error' => $e->getMessage(),
                    'id'    => $model->getAttribute('id'),
                ]);
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID, 'id');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, BC::COL_SIGN_BY, 'id');
    }

    public function getTypeEnumAttribute(): AssetType
    {
        $raw = (string) ($this->getAttribute('type') ?? '');

        return AssetType::normalize($raw !== '' ? $raw : null);
    }

    public function getIsDepreciableAttribute(): bool
    {
        return $this->type_enum->isDepreciable();
    }

    public function getTypicalLifespanYearsAttribute(): int
    {
        return $this->type_enum->typicalLifespanYears();
    }

    protected static function parseDate(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) return $value->copy()->startOfDay();
        if ($value instanceof \DateTimeInterface) return Carbon::instance($value)->startOfDay();

        $string = trim((string) $value);
        if ($string === '') return null;

        try {
            return Carbon::parse($string)->startOfDay();
        } catch (\Throwable $e) {
            Log::debug(static::class . ' failed to parse date', [
                'value' => $string,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected static function sanitizeAttachmentsArray(array $attachments): array
    {
        $result = [];

        foreach ($attachments as $attachment) {
            if (!is_array($attachment)) continue;

            $path = $attachment['path'] ?? $attachment['file_path'] ?? null;
            $name = $attachment['name'] ?? $attachment['file_name'] ?? null;
            $mime = $attachment['mime'] ?? $attachment['mime_type'] ?? null;
            $size = $attachment['size'] ?? $attachment['file_size'] ?? null;

            $clean = [];

            if (is_string($path) && trim($path) !== '') $clean['path'] = trim($path);
            if (is_string($name) && trim($name) !== '') $clean['name'] = trim($name);
            if (is_string($mime) && trim($mime) !== '') $clean['mime'] = trim($mime);
            if (is_numeric($size)) $clean['size'] = (int) $size;

            if ($clean !== []) $result[] = $clean;
        }

        return $result;
    }

    protected static function sanitizeTagsArray(array $tags): array
    {
        return collect($tags)
            ->filter(fn($t) => is_string($t) && trim($t) !== '')
            ->map(fn($t) => Str::slug((string) $t, '_'))
            ->unique()
            ->values()
            ->all();
    }
}
