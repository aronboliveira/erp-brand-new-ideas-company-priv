<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Enums\{
    AppModuleType,
    IndicatorTechnicalLevel
};
use App\Traits\{
    FiltersSecureAttachments,
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\{Casts\Attribute, Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo};
use Illuminate\Support\Facades\Log;

class TrainingType extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use HasFactory;
    use NormalizesArrays;
    use FiltersSecureAttachments;

    protected $table = DC::TABLE_TRAINING_TYPES;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    protected $fillable = [
        'code',
        'name',
        'description',
        'module',
        'level',
        PJC::COL_MIN_DR,
        PJC::COL_MAX_DR,
        'rules',
        'attachments',
        'tags',
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'module' => 'string',
        'level' => 'string',
        PJC::COL_MIN_DR => 'string',
        PJC::COL_MAX_DR => 'string',
        'rules' => 'array',
        'attachments' => 'array',
        'tags' => 'array',
    ];

    protected $appends = [
        'module_enum',
        'module_label',
        'module_category',
        'module_icon',
        'module_color',

        'level_enum',
        'level_label',
        'level_icon',
        'level_color',
        'level_proficiency',

        'has_duration_rules',
    ];

    /** cache simples por request */
    private static array $cache = [
        'module_labels' => [],
        'level_labels' => [],
    ];

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class, 'training_type', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    protected function module(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value): string => AppModuleType::normalize(is_string($value) ? $value : null)->value,
            set: function (mixed $value): string {
                if ($value instanceof AppModuleType)
                    return $value->value;

                return AppModuleType::normalize(is_string($value) ? $value : null)->value;
            }
        );
    }

    protected function level(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value): string => IndicatorTechnicalLevel::normalize($value)->value,
            set: function (mixed $value): string {
                if ($value instanceof IndicatorTechnicalLevel)
                    return $value->value;

                return IndicatorTechnicalLevel::normalize($value)->value;
            }
        );
    }

    protected function rules(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value): array => self::normalizeArrayField($value),
            set: fn(mixed $value): array => self::normalizeArrayField($value),
        );
    }

    protected function attachments(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value): array => self::normalizeArrayField($value),
            set: fn(mixed $value): array => self::normalizeArrayField($value),
        );
    }

    protected function tags(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value): array => self::normalizeArrayField($value),
            set: fn(mixed $value): array => self::normalizeArrayField($value),
        );
    }

    public function getModuleEnumAttribute(): AppModuleType
    {
        return AppModuleType::normalize($this->getAttribute('module'));
    }

    public function getModuleLabelAttribute(): string
    {
        $enum = $this->getModuleEnumAttribute();
        return $enum->label();
    }

    public function getModuleCategoryAttribute(): string
    {
        return $this->getModuleEnumAttribute()->getCategory();
    }

    public function getModuleIconAttribute(): string
    {
        return $this->getModuleEnumAttribute()->getIcon();
    }

    public function getModuleColorAttribute(): string
    {
        return $this->getModuleEnumAttribute()->getColor();
    }

    public function getLevelEnumAttribute(): IndicatorTechnicalLevel
    {
        return IndicatorTechnicalLevel::normalize($this->getAttribute('level'));
    }

    public function getLevelLabelAttribute(): string
    {
        return $this->getLevelEnumAttribute()->label();
    }

    public function getLevelIconAttribute(): string
    {
        return $this->getLevelEnumAttribute()->getIcon();
    }

    public function getLevelColorAttribute(): string
    {
        return $this->getLevelEnumAttribute()->getColor();
    }

    public function getLevelProficiencyAttribute(): int
    {
        return $this->getLevelEnumAttribute()->getProficiency();
    }

    public function getHasDurationRulesAttribute(): bool
    {
        return (string) ($this->getAttribute(PJC::COL_MIN_DR) ?? '') !== ''
            || (string) ($this->getAttribute(PJC::COL_MAX_DR) ?? '') !== '';
    }

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $m->ensureDefaults();
                $m->normalizeJsonFields();
                $m->validateDurations();
            } catch (\Throwable $ex) {
                Log::error(static::class . ' saving() failed', [
                    'id' => (string) ($m->getAttribute('id') ?? ''),
                    'error' => $ex->getMessage(),
                ]);
                throw $ex;
            }
        });
    }

    private function ensureDefaults(): void
    {
        $module = trim((string) ($this->getAttribute('module') ?? ''));
        if ($module === '')
            $this->setAttribute('module', AppModuleType::Other->value);

        $level = trim((string) ($this->getAttribute('level') ?? ''));
        if ($level === '')
            $this->setAttribute('level', IndicatorTechnicalLevel::Beginner->value);
    }

    private function normalizeJsonFields(): void
    {
        $this->setAttribute('rules', self::normalizeArrayField($this->getAttribute('rules')));
        $this->setAttribute('attachments', self::normalizeArrayField($this->getAttribute('attachments')));
        $this->setAttribute('tags', self::normalizeArrayField($this->getAttribute('tags')));
    }

    private function validateDurations(): void
    {
        $min = $this->getAttribute(PJC::COL_MIN_DR);
        $max = $this->getAttribute(PJC::COL_MAX_DR);

        if ($min && $max && (string) $min > (string) $max)
            throw \Illuminate\Validation\ValidationException::withMessages([
                PJC::COL_MIN_DR => 'minimum_duration deve ser <= maximum_duration.',
            ]);
    }
}
