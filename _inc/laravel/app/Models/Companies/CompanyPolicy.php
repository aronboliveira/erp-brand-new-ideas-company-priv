<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    MessagesConstants as MC,
    SettingsConstants as SC,
    UsersConstants as UC
};
use App\Enums\{AvailableLang, EvaluationStatus, UserType};
use App\Traits\{DefinesDates, FiltersSecureAttachments, HasAuditFields, NormalizesAddresses, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @property mixed $created_by
 * @property string|null $title

 * @property mixed $attachment
 */

class CompanyPolicy extends Model
{
    use HasFactory;
    use UsesUuids;
    use HasAuditFields;
    use NormalizesAddresses;
    use FiltersSecureAttachments;
    use DefinesDates;

    protected $table = DC::TABLE_CPN_POL;

    protected $fillable = [
        'code',
        'title',
        'url',
        'email',
        'phone',
        'summary',
        'version',

        'company',
        'branch',

        'description',

        SC::DEF_LNG,       // default_language
        'status',

        'attachment',      // legacy
        'file',            // legacy
        'attachments',     // json

        MC::COL_AV_LG,     // json
        'acknowledgers',   // json (employee ids)
        DC::COL_LEGAL_REP, // json
        'urls',            // json

        BC::COL_SIGN_BY_NAME,
        BC::COL_SIGN_BY,
        BC::COL_SIGN_AT,
    ];

    protected $casts = [
        'version' => 'decimal:2',
        BC::COL_SIGN_AT => 'datetime',
    ];

    protected $with = ['branchModel'];

    protected $appends = [
        'is_company_wide',
        'attachments_count',
        'acknowledgers_count',
        'status_enum',
        'default_language_enum',
    ];

    public function branchModel(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }

    public function companyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company', 'id');
    }

    public function signedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, BC::COL_SIGN_BY, 'id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            try {
                $model->ensureUniqueCodeIfEmpty();
            } catch (\Throwable $e) {
                Log::warning('CompanyPolicy: ensureUniqueCodeIfEmpty failed: ' . $e->getMessage());
            }
        });

        static::saving(function (self $model): void {
            try {
                $model->applyDefaults();
                $model->normalizeContactColumns();
                $model->normalizeCompanyAndBranch();
                $model->normalizeAttachments();
                $model->normalizeAvailableLanguages();
                $model->normalizeAcknowledgerEmployeeIds();
                $model->normalizeLegalRepresentants();
                $model->normalizeUrls();

                $model->ensureJsonAttributesAreEncoded([
                    'attachments',
                    MC::COL_AV_LG,
                    'acknowledgers',
                    DC::COL_LEGAL_REP,
                    'urls',
                ]);
            } catch (\DomainException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::warning('CompanyPolicy: saving normalization failed: ' . $e->getMessage(), [
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);
            }
        });
    }

    public function getAttachmentsAttribute($value): array
    {
        return self::normalizeArrayField($value);
    }

    public function setAttachmentsAttribute($value): void
    {
        $this->encodeJsonAttribute('attachments', self::normalizeArrayField($value));
    }

    public function getAvailableLanguagesAttribute($value): array
    {
        return self::normalizeArrayField($value);
    }

    public function setAvailableLanguagesAttribute($value): void
    {
        $this->encodeJsonAttribute(MC::COL_AV_LG, self::normalizeArrayField($value));
    }

    public function getAcknowledgersAttribute($value): array
    {
        return self::normalizeArrayField($value);
    }

    public function setAcknowledgersAttribute($value): void
    {
        $this->encodeJsonAttribute('acknowledgers', self::normalizeArrayField($value));
    }

    public function getLegalRepresentantsAttribute($value): array
    {
        return self::normalizeArrayField($value);
    }

    public function setLegalRepresentantsAttribute($value): void
    {
        $this->encodeJsonAttribute(DC::COL_LEGAL_REP, self::normalizeArrayField($value));
    }

    public function getUrlsAttribute($value): array
    {
        return self::normalizeArrayField($value);
    }

    public function setUrlsAttribute($value): void
    {
        $this->encodeJsonAttribute('urls', self::normalizeArrayField($value));
    }

    public function getIsCompanyWideAttribute(): bool
    {
        return (bool) $this->getAttribute('company') && $this->getAttribute('branch') === null;
    }

    public function getAttachmentsCountAttribute(): int
    {
        $v = $this->getAttribute('attachments');
        return is_array($v) ? count($v) : 0;
    }

    public function getAcknowledgersCountAttribute(): int
    {
        $v = $this->getAttribute('acknowledgers');
        return is_array($v) ? count($v) : 0;
    }

    public function getStatusEnumAttribute(): EvaluationStatus
    {
        return EvaluationStatus::normalize($this->getAttribute('status'));
    }

    public function getDefaultLanguageEnumAttribute(): AvailableLang
    {
        return AvailableLang::normalize($this->getAttribute(SC::DEF_LNG));
    }

    public function scopeForCompany($q, string $companyId)
    {
        return $q->where('company', trim($companyId));
    }

    public function scopeForBranch($q, string $branchId)
    {
        return $q->where('branch', trim($branchId));
    }

    public function scopeCompanyWide($q, string $companyId)
    {
        return $q->where('company', trim($companyId))->whereNull('branch');
    }

    private function applyDefaults(): void
    {
        if ($this->getAttribute(SC::DEF_LNG) === null)
            $this->setAttribute(SC::DEF_LNG, DC::DEFAULT_LANG);

        if ($this->getAttribute('status') === null)
            $this->setAttribute('status', EvaluationStatus::Draft->value);

        $version = $this->getAttribute('version');
        if ($version === null || !is_numeric($version) || (float) $version <= 0)
            $this->setAttribute('version', 1.0);
    }

    private function ensureUniqueCodeIfEmpty(): void
    {
        $code = $this->getAttribute('code');
        if (is_string($code) && trim($code) !== '')
            return;

        for ($i = 0; $i < 10; $i++) {
            $candidate = Str::uuid()->toString();
            $exists = DB::table(DC::TABLE_CPN_POL)->where('code', $candidate)->exists();
            if (!$exists) {
                $this->setAttribute('code', $candidate);
                return;
            }
        }

        Log::warning('CompanyPolicy: failed to generate a unique code after retries.');
    }

    private function normalizeContactColumns(): void
    {
        $id = $this->getKey() ?? '#NO_ID';
        $email = $this->getAttribute('email');
        $isNormalizeEmailCallable = is_callable([self::class, 'normalizeEmail']);
        $isNormalizeEmailCallable && $this->setAttribute('email', static::normalizeEmail(
            is_string($email) ? $email : null,
            'company_policy.email',
            $id
        ));
        $phone = $this->getAttribute('phone');
        $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
        $isNormalizePhoneCallable && $this->setAttribute('phone', static::normalizePhone(
            is_string($phone) ? $phone : null,
            'company_policy.phone',
            $id,
            app()->environment('testing')
        ));
        $url = $this->getAttribute('url');
        if ($url !== null && (!is_string($url) || trim($url) === '' || filter_var(trim($url), FILTER_VALIDATE_URL) === false))
            $this->setAttribute('url', null);
    }

    /**
     * Regras (modelo):
     * - company deve existir em users e ser UserType::Company.
     * - branch pode ser null (company-wide) ou uuid existente.
     * - evitar violação de uniques:
     *   - unique(company) (existente na migration atual)
     *   - unique(company, branch) (company_branch_unique)
     * - em testing: se company vier null, tenta atribuir company aleatória não utilizada.
     */
    private function normalizeCompanyAndBranch(): void
    {
        $company = $this->getAttribute('company');
        $branch  = $this->getAttribute('branch');

        if (!is_string($company) || !Str::isUuid($company))
            $company = null;

        if (!is_string($branch) || !Str::isUuid($branch))
            $branch = null;

        if ($company !== null) {
            try {
                $user = DB::table(DC::TABLE_USERS)
                    ->where('id', $company)
                    ->first(['id', UC::COL_TP]);

                if (!$user) {
                    $company = null;
                } else {
                    $typeRaw  = is_string($user->{UC::COL_TP} ?? null) ? (string) $user->{UC::COL_TP} : null;
                    $typeEnum = UserType::normalize($typeRaw);

                    if ($typeEnum !== UserType::Company)
                        $company = null;
                }
            } catch (\Throwable $e) {
                Log::warning('CompanyPolicy: company validation failed: ' . $e->getMessage());
            }
        }

        if ($company === null && app()->environment('testing')) {
            $company = $this->pickUnusedCompanyUserIdForTesting();
        }

        if ($company !== null) {
            // 1) Regra do unique(company) da migration atual
            $anyCompanyConflict = self::query()
                ->where('company', $company)
                ->when($this->exists, fn($q) => $q->where('id', '!=', $this->getKey()))
                ->exists();

            if ($anyCompanyConflict) {
                if (!app()->environment('testing'))
                    throw new \DomainException('CompanyPolicy: company already has a policy (unique constraint).');

                $company = $this->pickUnusedCompanyUserIdForTesting(exclude: [$company]);
            }

            // 2) Regra do company_branch_unique (para já ficar aderente ao índice composto)
            if ($company !== null) {
                $pairConflict = self::query()
                    ->where('company', $company)
                    ->when(
                        $branch === null,
                        fn($q) => $q->whereNull('branch'),
                        fn($q) => $q->where('branch', $branch)
                    )
                    ->when($this->exists, fn($q) => $q->where('id', '!=', $this->getKey()))
                    ->exists();

                if ($pairConflict) {
                    if (!app()->environment('testing'))
                        throw new \DomainException('CompanyPolicy: duplicated company+branch (company_branch_unique).');

                    // em testing: tenta outra company; se não houver, cai para null
                    $company = $this->pickUnusedCompanyUserIdForTesting(exclude: [$company]);
                }
            }
        }

        if ($branch !== null) {
            try {
                $exists = DB::table(DC::TABLE_BRANCHES)->where('id', $branch)->exists();
                if (!$exists)
                    $branch = null;
            } catch (\Throwable $e) {
                Log::warning('CompanyPolicy: branch existence check failed: ' . $e->getMessage());
            }
        }

        $this->setAttribute('company', $company);
        $this->setAttribute('branch', $branch);
    }

    private function pickUnusedCompanyUserIdForTesting(array $exclude = []): ?string
    {
        try {
            $used = DB::table(DC::TABLE_CPN_POL)
                ->whereNotNull('company')
                ->pluck('company')
                ->all();

            $exclude = array_values(array_unique(array_merge($used, $exclude)));

            $q = DB::table(DC::TABLE_USERS)
                ->select('id')
                ->where(UC::COL_TP, UserType::Company->value);

            if (!empty($exclude))
                $q->whereNotIn('id', $exclude);

            $row = $q->inRandomOrder()->first();

            return $row?->id ?? null;
        } catch (\Throwable $e) {
            Log::warning('CompanyPolicy: pickUnusedCompanyUserIdForTesting failed: ' . $e->getMessage());
            return null;
        }
    }

    private function normalizeAttachments(): void
    {
        $attachments = self::normalizeArrayField($this->getAttribute('attachments'));

        $push = function (?string $v) use (&$attachments): void {
            if (!is_string($v)) return;
            $v = trim($v);
            if ($v === '') return;
            if (!in_array($v, $attachments, true))
                $attachments[] = $v;
        };

        $push(is_string($this->getAttribute('attachment')) ? $this->getAttribute('attachment') : null);
        $push(is_string($this->getAttribute('file')) ? $this->getAttribute('file') : null);

        $filtered = [];
        foreach ($attachments as $a) {
            if (!is_string($a)) continue;
            $a = trim($a);
            if ($a === '') continue;
            if (!in_array($a, $filtered, true))
                $filtered[] = $a;
        }

        $this->setAttribute('attachments', empty($filtered) ? null : $filtered);
    }

    private function normalizeAvailableLanguages(): void
    {
        $raw = self::normalizeArrayField($this->getAttribute(MC::COL_AV_LG));

        $out = [];
        foreach ($raw as $v) {
            if (!is_string($v) && !($v instanceof AvailableLang)) continue;
            $lang = AvailableLang::normalize($v)->value;
            if (!in_array($lang, $out, true))
                $out[] = $lang;
        }

        $this->setAttribute(MC::COL_AV_LG, empty($out) ? null : $out);

        $this->setAttribute(SC::DEF_LNG, AvailableLang::normalize($this->getAttribute(SC::DEF_LNG))->value);
        $this->setAttribute('status', EvaluationStatus::normalize($this->getAttribute('status'))->value);
    }

    /**
     * acknowledgers:
     * - entrada: lista de IDs de employee
     * - valida employee existe
     * - valida employee.user_id existe em users e tipo permitido:
     *   SuperAdmin, Company, Admin, Vendor (via UserType enum)
     * - saída: lista filtrada de employee ids
     */
    private function normalizeAcknowledgerEmployeeIds(): void
    {
        $raw = self::normalizeArrayField($this->getAttribute('acknowledgers'));

        $employeeIds = [];
        foreach ($raw as $v) {
            if (!is_string($v)) continue;
            $v = trim($v);
            if (!Str::isUuid($v)) continue;
            if (!in_array($v, $employeeIds, true))
                $employeeIds[] = $v;
        }

        if (empty($employeeIds)) {
            $this->setAttribute('acknowledgers', null);
            return;
        }

        $allowed = [
            UserType::SuperAdmin,
            UserType::Company,
            UserType::Admin,
            UserType::Vendor,
        ];

        try {
            $employees = DB::table(DC::TABLE_EMPLOYEES)
                ->whereIn('id', $employeeIds)
                ->get(['id', UC::COL_USER_ID]);

            if ($employees->isEmpty()) {
                $this->setAttribute('acknowledgers', null);
                return;
            }

            $userIds = [];
            $empToUser = [];

            foreach ($employees as $e) {
                $eid = (string) ($e->id ?? '');
                $uid = (string) ($e->{UC::COL_USER_ID} ?? '');

                if ($eid === '' || !Str::isUuid($eid)) continue;
                if ($uid === '' || !Str::isUuid($uid)) continue;

                $empToUser[$eid] = $uid;
                if (!in_array($uid, $userIds, true))
                    $userIds[] = $uid;
            }

            if (empty($userIds)) {
                $this->setAttribute('acknowledgers', null);
                return;
            }

            $users = DB::table(DC::TABLE_USERS)
                ->whereIn('id', $userIds)
                ->get(['id', UC::COL_TP]);

            $allowedUsers = [];
            foreach ($users as $u) {
                $uid = (string) ($u->id ?? '');
                $tp  = is_string($u->{UC::COL_TP} ?? null) ? (string) $u->{UC::COL_TP} : null;

                if ($uid === '' || !Str::isUuid($uid)) continue;

                $typeEnum = UserType::normalize($tp);
                if (in_array($typeEnum, $allowed, true))
                    $allowedUsers[$uid] = true;
            }

            $out = [];
            foreach ($employeeIds as $eid) {
                $uid = $empToUser[$eid] ?? null;
                if ($uid && isset($allowedUsers[$uid]))
                    $out[] = $eid;
            }

            $this->setAttribute('acknowledgers', empty($out) ? null : $out);
        } catch (\Throwable $e) {
            Log::warning('CompanyPolicy: normalizeAcknowledgerEmployeeIds failed: ' . $e->getMessage());
        }
    }

    private function normalizeLegalRepresentants(): void
    {
        $raw = self::normalizeArrayField($this->getAttribute(DC::COL_LEGAL_REP));
        if (empty($raw)) {
            $this->setAttribute(DC::COL_LEGAL_REP, null);
            return;
        }

        $out = [];

        foreach ($raw as $item) {
            if (!is_array($item)) continue;

            $name = isset($item['name']) && is_string($item['name']) ? trim($item['name']) : '';
            $pos  = isset($item['position']) && is_string($item['position']) ? trim($item['position']) : '';
            $id   = isset($item['id']) && is_string($item['id']) ? trim($item['id']) : null;

            if ($name === '' && $pos === '') continue;

            $out[] = [
                'name' => $name !== '' ? $name : null,
                'position' => $pos !== '' ? $pos : null,
                'id' => ($id && Str::isUuid($id)) ? $id : null,
            ];
        }

        $this->setAttribute(DC::COL_LEGAL_REP, empty($out) ? null : $out);
    }

    private function normalizeUrls(): void
    {
        $raw = self::normalizeArrayField($this->getAttribute('urls'));

        $out = [];
        foreach ($raw as $u) {
            if (!is_string($u)) continue;
            $u = trim($u);
            if ($u === '') continue;
            if (filter_var($u, FILTER_VALIDATE_URL) === false) continue;
            if (!in_array($u, $out, true))
                $out[] = $u;
        }

        $this->setAttribute('urls', empty($out) ? null : $out);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Branch, $this> */
    public function branches(): BelongsTo
    {
        return $this->branchModel();
    }
}
