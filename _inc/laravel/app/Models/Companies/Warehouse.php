<?php

namespace App\Models;

use App\Config\Constants\{
    CompaniesConstants as CC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Enums\Weekday;
use App\Traits\{
    ChecksLogin,
    DefinesDates,
    HasAuditFields,
    NormalizesAddresses,
    StoresManyRefJson,
    UsesCountryRegions,
    UsesUuids,
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo
};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{
    DB,
    Log
};

class Warehouse extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use HasFactory;
    use StoresManyRefJson;
    use NormalizesAddresses;
    use UsesCountryRegions;
    use ChecksLogin;
    use DefinesDates;

    protected $table = DC::TABLE_WRH;
    protected $fillable = [
        'code',
        'name',
        CC::COL_CP_ID,
        'zip',
        'country',
        'state',
        'city',
        'address',
        CC::COL_ADR_DTL,
        'notes',
        'phone',
        'email',
        CC::COL_OWN_ID,
        CC::COL_OWN_NM,
        CC::COL_IA,
        CC::COL_IS_SHP,
        CC::COL_FD_DT,
        'dimensions',
        'capacity',
        'employees',
        'supervisors',
        'managers',
        'partners',
        'sections',
        CC::COL_REACH,
        CC::COL_OP_TM,
        CC::COL_CL_TM,
        CC::COL_WK_DYS,
        UC::COL_AVG_RT,
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];
    protected $with = [
        'company',
        'owner',
        'createdBy',
    ];
    protected $casts = [
        CC::COL_IA       => 'boolean',
        CC::COL_IS_SHP   => 'boolean',
        CC::COL_FD_DT    => 'date',
        CC::COL_OP_TM    => 'datetime:H:i:s',
        CC::COL_CL_TM    => 'datetime:H:i:s',
        'dimensions'     => 'array',
        'capacity'       => 'array',
        'employees'      => 'array',
        'supervisors'    => 'array',
        'managers'       => 'array',
        'partners'       => 'array',
        'sections'       => 'array',
        CC::COL_REACH    => 'array',
        CC::COL_WK_DYS   => 'array',
        UC::COL_AVG_RT   => 'decimal:2',
    ];
    private const BASE = [
        'name'      => 'North Warehouse',
        'country'   => 'BR',
        'notes'     => 'Default mock warehouse',
        'sections'  => ['Corredor-01', 'Corredor-02', 'Doca-A'],
        'dimensions' => ['width_m' => 70, 'length_m' => 120, 'height_m' => 10],
        'capacity'  => ['pallets' => 2200, 'max_kg' => 150000],
        CC::COL_WK_DYS => ['mon', 'tue', 'wed', 'thu', 'fri'],
        CC::COL_IS_SHP => true,
        CC::COL_IA     => true,
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            $m->rescueCountryStateFromKnownCityList($m);
            $m->rescueGeoFromAddressTokensIfMissing();
            $m->rescueGeoFromZipIfMissing();
            $m->normalizeGeo();
            foreach (
                [
                    'dimensions',
                    'capacity',
                    'employees',
                    'supervisors',
                    'managers',
                    'partners',
                    'sections',
                    CC::COL_REACH,
                ] as $field
            )
                $m->setAttribute($field, static::normalizeArrayField($m->getAttribute($field) ?? null));
            $ownerId = $m->getAttribute(DC::COL_TABLE_CREATOR) ?? $m->getAttribute(CC::COL_CP_ID) ?? $m->getAttribute(CC::COL_OWN_ID) ?? null;
            $isNormalizeEmailCallable = is_callable([self::class, 'normalizeEmail']);
            $isNormalizeEmailCallable && $m->setAttribute('email', static::normalizeEmail(
                $m->getAttribute('email') ?? null,
                'warehouse',
                $ownerId
            ));
            $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
            $isNormalizePhoneCallable && $m->setAttribute('phone', static::normalizePhone(
                $m->getAttribute('phone') ?? null,
                'warehouse',
                $ownerId
            ));
            $isNormalizeZipCallable = is_callable([self::class, 'normalizeZip']);
            $isNormalizeZipCallable && $m->setAttribute('zip', static::normalizeZip(
                $m->getAttribute('zip') ?? null,
                $m->getAttribute('country') ?? null,
                'warehouse',
                $ownerId
            ));
            $ratingField = UC::COL_AVG_RT;
            if ($m->getAttribute($ratingField) !== null) {
                $val = (float) $m->getAttribute($ratingField);
                if ($val < 0.0)
                    $val = 0.0;
                elseif ($val > 5.0)
                    $val = 5.0;
                $m->setAttribute($ratingField, $val);
            }
            $m->enforceCountryStateColumns($m, 'country', 'state');
        });
    }

    /**
     * Normaliza working_days na escrita usando Weekday::normalize
     * e garante ordenação ISO (segunda–domingo).
     */
    public function setWorkingDaysAttribute(mixed $value): void
    {
        $raw   = static::normalizeArrayField($value);
        $valid = [];
        foreach ($raw as $item) {
            $weekday = Weekday::normalize(is_string($item) ? $item : (string) $item);
            if ($weekday !== null)
                $valid[$weekday->value] = true; // evitar duplicatas
        }
        $ordered = [];
        foreach (Weekday::ordered(true) as $enumDay)
            if (isset($valid[$enumDay->value]))
                $ordered[] = $enumDay->value;
        $this->attributes[CC::COL_WK_DYS] = json_encode($ordered);
    }

    /**
     * Garante que working_days lido venha sempre normalizado e ordenado.
     */
    public function getWorkingDaysAttribute(mixed $value): array
    {
        $raw = static::normalizeArrayField($value);
        $valid = [];
        foreach ($raw as $item) {
            $weekday = Weekday::normalize(is_string($item) ? $item : (string) $item);
            if ($weekday !== null)
                $valid[$weekday->value] = true;
        }
        $ordered = [];
        foreach (Weekday::ordered(true) as $enumDay)
            if (isset($valid[$enumDay->value]))
                $ordered[] = $enumDay->value;
        return $ordered;
    }

    /**
     * Relação com a "empresa" (company_id).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            CC::COL_CP_ID,
            'id'
        );
    }

    /**
     * Relação com o owner (owner_id).
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            CC::COL_OWN_ID,
            'id'
        );
    }

    /**
     * Mantida para compatibilidade: se não logado, retorna RedirectResponse,
     * senão retorna o ID numérico (ou 0).
     */
    public static function warehouseId(string $warehouseId): string|int|RedirectResponse
    {
        try {
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse)
                return $userOrRedirect;
            $user = $userOrRedirect;
            $id = DB::table((new self())->getTable())
                ->where('id', $warehouseId)
                ->where(DC::COL_TABLE_CREATOR, $user?->creatorId())
                ->value('id');
            return (int) ($id ?? 0);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed: {$e->getMessage()}");
            return 0;
        }
    }

    public function scopeActive($q)
    {
        return $q->where(CC::COL_IA, true);
    }
    public function scopeShipping($q, bool $enabled = true)
    {
        return $q->where(CC::COL_IS_SHP, $enabled);
    }
    public function scopeByStateCity($q, ?string $state = null, ?string $city = null)
    {
        if ($state !== null) $q->where('state', strtoupper(trim($state)));
        if ($city  !== null) $q->where('city', $city);
        return $q;
    }
    public function scopeOwnedBy($q, string $ownerId)
    {
        return $q->where(\App\Config\Constants\DatabaseConstants::COL_TABLE_CREATOR, $ownerId);
    }
    public function scopeCompany($q, ?string $companyId)
    {
        return $companyId ? $q->where(CC::COL_CP_ID, $companyId) : $q;
    }
    public function scopeSearch($q, string $term)
    {
        $t = '%' . trim($term) . '%';
        return $q->where(function ($qq) use ($t) {
            $qq->where('name', 'like', $t)
                ->orWhere('code', 'like', $t)
                ->orWhere('city', 'like', $t)
                ->orWhere('address', 'like', $t)
                ->orWhere('zip', 'like', $t);
        });
    }

    // Agregadores / arrays
    public function sectionsFlat(): array
    {
        return array_values(array_unique(array_map('strval', $this->sections ?? [])));
    }
    public function partnersFlat(): array
    {
        return array_values(array_unique(array_map('strval', $this->partners ?? [])));
    }
    public function supervisorsFlat(): array
    {
        return array_values(array_unique(array_map('strval', $this->supervisors ?? [])));
    }
    public function employeesFlat(): array
    {
        return array_values(array_unique(array_map('strval', $this->employees ?? [])));
    }
    public function capacitySummary(): array
    {
        $c = (array) ($this->capacity ?? []);
        return [
            'pallets' => (int)($c['pallets'] ?? 0),
            'max_kg'  => (int)($c['max_kg']  ?? 0),
            'meta'    => array_diff_key($c, array_flip(['pallets', 'max_kg'])),
        ];
    }
    public function totalPalletCapacity(): int
    {
        return (int) (($this->capacity ?? [])['pallets'] ?? 0);
    }

    // Utilidade operacional
    public function workingWindow(): array
    {
        $open  = (string)($this->{CC::COL_OP_TM} ?? '08:00:00');
        $close = (string)($this->{CC::COL_CL_TM} ?? '18:00:00');
        return ['open' => $open, 'close' => $close];
    }
    public function isOpenAt($at = null): bool
    {
        $at = $at ? \Carbon\Carbon::parse($at) : now();
        $w  = $this->workingWindow();
        $day = strtolower($at->format('D'));
        $map = ['mon' => 'mon', 'tue' => 'tue', 'wed' => 'wed', 'thu' => 'thu', 'fri' => 'fri', 'sat' => 'sat', 'sun' => 'sun'];
        $days = array_map('strtolower', (array)($this->{CC::COL_WK_DYS} ?? []));
        if (!in_array($map[$day] ?? $day, $days, true)) return false;
        $open  = \Carbon\Carbon::parse($at->format('Y-m-d') . ' ' . $w['open']);
        $close = \Carbon\Carbon::parse($at->format('Y-m-d') . ' ' . $w['close']);
        return $at->betweenIncluded($open, $close);
    }

    // Mutadores utilitários
    public function addSection(string $name): self
    {
        $arr = $this->sectionsFlat();
        $arr[] = $name;
        $this->setAttribute('sections', array_values(array_unique($arr)));
        return $this;
    }
    public function removeSection(string $name): self
    {
        $this->setAttribute('sections', array_values(array_filter($this->sectionsFlat(), fn($v) => $v !== $name)));
        return $this;
    }
    public function addWorkingDay(string $weekday): self
    {
        $raw = (array)($this->getAttribute(CC::COL_WK_DYS) ?? []);
        $raw[] = $weekday;
        $this->setAttribute(CC::COL_WK_DYS, array_values(array_unique(array_map('strtolower', $raw))));
        return $this;
    }
    public function removeWorkingDay(string $weekday): self
    {
        $raw = array_filter((array)($this->getAttribute(CC::COL_WK_DYS) ?? []), fn($v) => strtolower($v) !== strtolower($weekday));
        $this->setAttribute(CC::COL_WK_DYS, array_values(array_map('strtolower', $raw)));
        return $this;
    }

    public function toBriefArray(): array
    {
        return [
            'id'      => (string)$this->getAttribute('id'),
            'code'    => (string)($this->getAttribute('code') ?? ''),
            'name'    => (string)($this->getAttribute('name') ?? ''),
            'state'   => (string)($this->getAttribute('state') ?? ''),
            'city'    => (string)($this->getAttribute('city') ?? ''),
            'zip'     => (string)($this->getAttribute('zip') ?? ''),
            'address' => (string)($this->getAttribute('address') ?? ''),
            'phone'   => (string)($this->getAttribute('phone') ?? ''),
            'email'   => (string)($this->getAttribute('email') ?? ''),
            'shipping' => (bool)($this->getAttribute(CC::COL_IS_SHP) ?? false),
            'active'  => (bool)($this->getAttribute(CC::COL_IA) ?? false),
            'sections' => $this->sectionsFlat(),
            'capacity' => $this->capacitySummary(),
        ];
    }
}
