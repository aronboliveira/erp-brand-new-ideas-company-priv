<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, EmailsConstants as EC, ProjectsConstants as PJC};
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Builder, Factories\HasFactory, Model, Relations\BelongsTo};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class ProjectEmailTemplate extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesArrays;

    public const CODE_PREFIX  = 'PRJ-EM-TMP-';
    public const CODE_PATTERN = '/^PRJ-EM-TMP-[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    protected $table = DC::TABLE_PRJ_EM_TMP;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        'variables',
        'settings',
    ];

    protected $fillable = [
        'code',
        'name',
        EC::COL_TMP,
        PJC::COL_PJ_ID,
        EC::COL_IA,
        'fonts',
        'colors',
        'tags',
    ];

    protected $casts = [
        EC::COL_IA     => 'boolean',
        'fonts'        => 'array',
        'colors'       => 'array',
        'variables'    => 'array',
        'settings'     => 'array',
        'tags'         => 'array',
        PJC::COL_PJ_ID => 'string',
        EC::COL_TMP    => 'string',
    ];

    protected $with = [
        'template',
        'project',
    ];

    protected $appends = [
        'default_font',
        'color_palette',
        'template_title',
        'project_name',
    ];

    protected static array $templatePayloadCache = [];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                self::ensureValidCode($m);
                self::normalizeJsonFields($m);
                self::syncVariablesAndSettingsFromTemplate($m);
            } catch (\Throwable $e) {
                Log::error(self::class . ' saving hook failed: ' . $e->getMessage(), [
                    'id'          => (string) ($m->getAttribute('id') ?? ''),
                    'template_id'  => (string) ($m->getAttribute(EC::COL_TMP) ?? ''),
                    'project_id'   => (string) ($m->getAttribute(PJC::COL_PJ_ID) ?? ''),
                ]);
            }
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, EC::COL_TMP, 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, PJC::COL_PJ_ID, 'id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where(EC::COL_IA, true);
    }

    public function isActive(): bool
    {
        return (bool) ($this->getAttribute(EC::COL_IA) ?? false);
    }

    public function activate(): void
    {
        $this->setAttribute(EC::COL_IA, true);
    }

    public function deactivate(): void
    {
        $this->setAttribute(EC::COL_IA, false);
    }

    public function getDefaultFontAttribute(): ?string
    {
        $fonts = $this->getAttribute('fonts');
        if (!is_array($fonts) || $fonts === [])
            return null;

        $first = $fonts[0] ?? null;
        if (!is_string($first))
            return null;

        $first = trim($first);
        return $first === '' ? null : $first;
    }

    public function getColorPaletteAttribute(): array
    {
        $colors = $this->getAttribute('colors');
        return is_array($colors) ? $colors : [];
    }

    public function getTemplateTitleAttribute(): ?string
    {
        try {
            $t = $this->getRelationValue('template') ?? null;
            $title = $t?->getAttribute('title') ?? null;
            return is_string($title) && trim($title) !== '' ? $title : null;
        } catch (\Throwable $e) {
            Log::debug(self::class . ' failed reading template_title: ' . $e->getMessage(), [
                'id' => (string) ($this->getAttribute('id') ?? ''),
            ]);
            return null;
        }
    }

    public function getProjectNameAttribute(): ?string
    {
        try {
            $p = $this->getRelationValue('project') ?? null;
            $name = $p?->getAttribute('name') ?? null;
            return is_string($name) && trim($name) !== '' ? $name : null;
        } catch (\Throwable $e) {
            Log::debug(self::class . ' failed reading project_name: ' . $e->getMessage(), [
                'id' => (string) ($this->getAttribute('id') ?? ''),
            ]);
            return null;
        }
    }

    protected static function ensureValidCode(self $m): void
    {
        $raw = (string) ($m->getAttribute('code') ?? '');
        if ($raw !== '' && preg_match(self::CODE_PATTERN, $raw) === 1)
            return;

        $attemptCap = 32;
        $attempts = 0;

        do {
            $attempts++;
            $code = self::CODE_PREFIX . Str::uuid()->toString();

            $exists = false;
            try {
                $exists = (bool) (DB::table(DC::TABLE_PRJ_EM_TMP)->where('code', $code)->exists());
            } catch (\Throwable $e) {
                Log::warning(self::class . ' code existence check failed: ' . $e->getMessage(), [
                    'code' => $code,
                ]);
                $exists = false;
            }
        } while ($exists && $attempts < $attemptCap);

        if ($exists) {
            Log::warning(self::class . ' code generation attempts exhausted; leaving code null', [
                'id' => (string) ($m->getAttribute('id') ?? ''),
            ]);
            $m->setAttribute('code', null);
            return;
        }

        $m->setAttribute('code', $code);
    }

    protected static function normalizeJsonFields(self $m): void
    {
        $m->setAttribute(EC::COL_IA, (bool) ($m->getAttribute(EC::COL_IA) ?? false));

        $fonts = self::normalizeStringSet($m->getAttribute('fonts'), 12);
        $m->setAttribute('fonts', $fonts ?: null);

        $colors = self::normalizeHexColorSet($m->getAttribute('colors'), 24);
        $m->setAttribute('colors', $colors ?: null);

        $tags = self::normalizeStringSet($m->getAttribute('tags'), 24);
        $m->setAttribute('tags', $tags ?: null);
    }

    protected static function syncVariablesAndSettingsFromTemplate(self $m): void
    {
        $templateId = $m->getAttribute(EC::COL_TMP);
        if (!is_string($templateId) || trim($templateId) === '') {
            Log::warning(self::class . ' missing template_id; variables/settings set to []', [
                'id' => (string) ($m->getAttribute('id') ?? ''),
            ]);
            $m->setAttribute('variables', []);
            $m->setAttribute('settings', []);
            return;
        }

        $key = trim($templateId);

        $payload = self::$templatePayloadCache[$key] ?? null;
        if (!is_array($payload)) {
            try {
                $row = DB::table(DC::TABLE_EMAIL_TEMPLATES)
                    ->select(['variables', 'settings'])
                    ->where('id', $key)
                    ->first();

                $vars = $row?->variables ?? null;
                $stg  = $row?->settings ?? null;

                $payload = [
                    'variables' => self::normalizeArrayField($vars),
                    'settings'  => self::normalizeArrayField($stg),
                ];

                self::$templatePayloadCache[$key] = $payload;
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed fetching template variables/settings: ' . $e->getMessage(), [
                    'template_id' => $key,
                ]);
                $payload = ['variables' => [], 'settings' => []];
            }
        }

        $m->setAttribute('variables', $payload['variables'] ?? []);
        $m->setAttribute('settings', $payload['settings'] ?? []);
    }

    protected static function normalizeStringSet(mixed $value, int $limit): array
    {
        $arr = self::normalizeArrayField($value);
        $out = [];

        foreach ($arr as $v) {
            if (!is_scalar($v))
                continue;

            $s = trim((string) $v);
            if ($s === '')
                continue;

            $out[] = $s;
            if (count($out) >= $limit)
                break;
        }

        $out = array_values(array_unique($out));
        return $out;
    }

    protected static function normalizeHexColorSet(mixed $value, int $limit): array
    {
        $arr = self::normalizeArrayField($value);
        $out = [];

        foreach ($arr as $v) {
            if (!is_scalar($v))
                continue;

            $s = strtolower(trim((string) $v));
            if ($s === '')
                continue;

            if ($s[0] !== '#')
                $s = '#' . $s;

            if (preg_match('/^#[0-9a-f]{3}$/', $s) === 1) {
                $r = $s[1];
                $g = $s[2];
                $b = $s[3];
                $s = "#{$r}{$r}{$g}{$g}{$b}{$b}";
            }

            if (preg_match('/^#[0-9a-f]{6}$/', $s) !== 1)
                continue;

            $out[] = $s;
            if (count($out) >= $limit)
                break;
        }

        $out = array_values(array_unique($out));
        return $out;
    }
}
