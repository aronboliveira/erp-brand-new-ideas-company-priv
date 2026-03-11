<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC, SettingsConstants as SC};
use App\Enums\{CountryName, DEICategory, EvaluationStatus, JobLevel, Visibility, WorkContractType, WorkPresence, WorkShift};
use App\Traits\{DefinesDates, DescribesCompanyBranch, FiltersSecureAttachments, HasAuditFields, NormalizesAddresses, UsesCountryRegions, UsesUuids};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Support\{Collection, Str};
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @property mixed $created_by
 * @property array|string|null $customQuestion
 * @property array|string|null $custom_question
 * @property mixed $applicant
 * @property mixed $skill
 * @property mixed $visibility

 * @property mixed $custom
 */

class Job extends Model
{
    use HasFactory;
    use UsesUuids;
    use HasAuditFields;
    use NormalizesAddresses;
    use DescribesCompanyBranch;
    use FiltersSecureAttachments;
    use UsesCountryRegions;
    use DefinesDates;

    protected $table = DC::TABLE_JOBS;

    /**
     * Status options for dropdown selects (mirrors EvaluationStatus subset).
     */
    public static array $status = [
        'active'    => 'Active',
        'in_active' => 'In Active',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $fillable = [
        'title',
        'slug',
        'code',
        'category',
        'announcement',
        'company',
        'country',
        'state',
        'city',
        'address',
        'branch',
        'department',
        'designation',
        'level',
        PJC::COL_PRS_TP,
        PJC::COL_CTC_TP,
        PJC::COL_SHFT_TP,
        AC::COL_EXP_SLR,
        AC::COL_EXP_SLR_CURR,
        AC::COL_RCT_ID,
        AC::COL_RCT_NM,
        AC::COL_RCT_EML,
        AC::COL_RCT_PST,
        AC::COL_MNG_ID,
        AC::COL_MNG_NM,
        AC::COL_MNG_EML,
        AC::COL_MNG_PST,
        AC::COL_RCV_EML,
        AC::COL_RCV_TMP,
        AC::COL_RES_EML,
        AC::COL_RSP_TMP,
        AC::COL_MIN_EXP_Y,
        'description',
        'requirement',
        AC::COL_RQ_DOC,
        'skill',
        AC::COL_SKL_DOC,
        AC::COL_MIN_SKL_MTCH,
        AC::COL_MAX_NTC_PD,
        AC::COL_RQ_WK_AUTH,
        AC::COL_RQ_VS,
        AC::COL_RQ_RLC,
        AC::COL_RLC_PV,
        AC::COL_MAX_RLC_DAYS,
        AC::COL_RQ_BKG_CK,
        AC::COL_RQ_CV_LT,
        AC::COL_RQ_PRTF,
        AC::COL_MAX_AP,
        AC::COL_CUR_AP,
        AC::COL_IS_DEI,
        AC::COL_VW_CT,
        AC::COL_LST_AP_AT,
        'position',
        PJC::COL_S_DT,
        PJC::COL_E_DT,
        'status',
        'visibility',
        AC::COL_CT_QT,
        AC::COL_FM_ID,
        'project',
        'milestone',
        'goal',
        'benefits',
        'incentives',
        'stages',
        AC::COL_RQ_CRT,
        AC::COL_PRF_CRT,
        AC::COL_RQ_SKL,
        AC::COL_PRF_SKL,
        AC::COL_RQ_LG,
        AC::COL_PRF_LG,
        AC::COL_ACP_WK_AUTH,
        AC::COL_ALW_CTR,
        AC::COL_ALW_ST,
        AC::COL_DEI_CTG,
        AC::COL_DEI_CRT,
        AC::COL_DEI_DOCS,
        'attachments',
        'tags',
        'platforms',
        'metadata',
        AC::COL_HRD_ID,
        'hired',
        'applicant',
        AC::COL_APL_ID,
    ];

    protected $with = [
        'branchModel',
        'categoryModel',
    ];

    protected $appends = [
        'allowed_countries_constraint',
        'allowed_states_constraint',
        'effective_receiving_email',
    ];

    protected $casts = [
        'level' => JobLevel::class,
        PJC::COL_PRS_TP => WorkPresence::class,
        PJC::COL_CTC_TP => WorkContractType::class,
        PJC::COL_SHFT_TP => WorkShift::class,
        'status' => EvaluationStatus::class,
        'visibility' => Visibility::class,
        AC::COL_DEI_CTG => DEICategory::class,

        AC::COL_EXP_SLR => 'decimal:2',
        AC::COL_MIN_SKL_MTCH => 'float',

        AC::COL_LST_AP_AT => 'datetime',
        PJC::COL_S_DT => 'date',
        PJC::COL_E_DT => 'date',

        'benefits' => 'array',
        'incentives' => 'array',
        'stages' => 'array',
        AC::COL_RQ_CRT => 'array',
        AC::COL_PRF_CRT => 'array',
        AC::COL_RQ_SKL => 'array',
        AC::COL_PRF_SKL => 'array',
        AC::COL_RQ_LG => 'array',
        AC::COL_PRF_LG => 'array',
        AC::COL_ACP_WK_AUTH => 'array',
        AC::COL_ALW_CTR => 'array',
        AC::COL_ALW_ST => 'array',
        AC::COL_DEI_CRT => 'array',
        AC::COL_DEI_DOCS => 'array',
        'attachments' => 'array',
        'tags' => 'array',
        'platforms' => 'array',
        'metadata' => 'array',

        AC::COL_RQ_WK_AUTH => 'bool',
        AC::COL_RQ_VS => 'bool',
        AC::COL_RQ_RLC => 'bool',
        AC::COL_RLC_PV => 'bool',
        AC::COL_RQ_BKG_CK => 'bool',
        AC::COL_RQ_CV_LT => 'bool',
        AC::COL_RQ_PRTF => 'bool',
        AC::COL_IS_DEI => 'bool',
    ];

    protected static array $cacheLocal = [];

    protected static function booted(): void
    {
        // todo too heavy for testing, use only in production
        // static::saving(function (self $m): void {
        //     $m->applyDomainInvariants();
        // });
    }

    public function categoryModel(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'category');
    }

    public function announcementModel(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement');
    }

    public function companyModel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company');
    }

    public function branchModel(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch');
    }

    public function departmentModel(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department');
    }

    public function designationModel(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation');
    }

    public function recruiterModel(): BelongsTo
    {
        return $this->belongsTo(User::class, AC::COL_RCT_ID);
    }

    public function managerModel(): BelongsTo
    {
        return $this->belongsTo(User::class, AC::COL_MNG_ID);
    }

    public function receivedTemplateModel(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, AC::COL_RCV_TMP);
    }

    public function responseTemplateModel(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, AC::COL_RSP_TMP);
    }

    public function requirementDocumentModel(): BelongsTo
    {
        return $this->belongsTo(Document::class, AC::COL_RQ_DOC);
    }

    public function skillDocumentModel(): BelongsTo
    {
        return $this->belongsTo(Document::class, AC::COL_SKL_DOC);
    }

    public function formModel(): BelongsTo
    {
        return $this->belongsTo(FormBuilder::class, AC::COL_FM_ID);
    }

    public function projectModel(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project');
    }

    public function milestoneModel(): BelongsTo
    {
        return $this->belongsTo(Milestone::class, 'milestone');
    }

    public function goalModel(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'goal');
    }

    public function hiredApplicantModel(): BelongsTo
    {
        return $this->belongsTo(User::class, AC::COL_HRD_ID);
    }

    public function applicantUserModel(): BelongsTo
    {
        return $this->belongsTo(User::class, AC::COL_APL_ID);
    }

    public function branches(): BelongsTo // * legacy, DO NOT use it
    {
        return $this->branchModel();
    }

    public function categories(): BelongsTo // * legacy, DO NOT use it
    {
        return $this->categoryModel();
    }

    public function createdBy(): HasOne
    {
        return $this
            ->hasOne(User::class, 'id', DC::COL_TABLE_CREATOR);
    }

    public function questions(): Collection
    {
        $ids = explode(',', $this->getAttribute('questions') ?? '');
        return CustomQuestion::whereIn('id', $ids)->get();
    }

    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('visibility', '!=', Visibility::Draft->value);
    }

    public function getAllowedCountriesConstraintAttribute(): ?array
    {
        return $this->cacheOnce('allowed_countries_constraint', function (): ?array {
            $raw = $this->getAttribute(AC::COL_ALW_CTR);
            if ($raw === null) return null;

            $list = is_array($raw) ? $raw : (is_string($raw) ? (json_decode($raw, true) ?: []) : []);
            if (!is_array($list)) return null;

            $out = [];
            foreach ($list as $v) {
                $e = CountryName::normalize(is_scalar($v) ? (string) $v : null);
                if ($e instanceof CountryName) $out[] = $e->value;
            }

            $out = array_values(array_unique(array_filter($out, fn($x) => is_string($x) && $x !== '')));
            return $out ?: null;
        });
    }

    public function getAllowedStatesConstraintAttribute(): ?array
    {
        return $this->cacheOnce('allowed_states_constraint', function (): ?array {
            $raw = $this->getAttribute(AC::COL_ALW_ST);
            if ($raw === null) return null;

            $map = is_array($raw) ? $raw : (is_string($raw) ? (json_decode($raw, true) ?: []) : []);
            if (!is_array($map)) return null;

            $out = [];
            foreach ($map as $k => $v) {
                if (!is_scalar($k)) continue;
                $cc = $this->normalizeCountryToCode((string) $k);
                if ($cc === null) continue;

                $list = is_array($v) ? $v : [];
                $states = [];
                foreach ($list as $sv) {
                    if (!is_scalar($sv)) continue;
                    $st = $this->normalizeStateForCountry((string) $sv, $cc, false);
                    if ($st !== null) $states[] = $st;
                }

                $states = array_values(array_unique(array_filter($states, fn($x) => is_string($x) && $x !== '')));
                if ($states) $out[$cc] = $states;
            }

            return $out ?: null;
        });
    }

    public function getEffectiveReceivingEmailAttribute(): ?string
    {
        return $this->cacheOnce('effective_receiving_email', function (): ?string {
            $v = $this->getAttribute(AC::COL_RCV_EML);
            if (is_scalar($v) && trim((string) $v) !== '')
                return static::normalizeEmail((string) $v, 'job.receiving_email', $this->getAttribute('id') ?? null);

            $fromRecruiter = $this->resolveUserEmailByUserIdColumn(AC::COL_RCT_ID);
            if ($fromRecruiter !== null) return $fromRecruiter;

            $fromManager = $this->resolveUserEmailByUserIdColumn(AC::COL_MNG_ID);
            if ($fromManager !== null) return $fromManager;

            if ($this->relationLoaded('branchModel') && $this->branchModel) {
                $branchEmail = $this->branchModel->getAttribute('email') ?? null;
                if (is_scalar($branchEmail) && trim((string) $branchEmail) !== '')
                    return static::normalizeEmail((string) $branchEmail, 'branch.email', $this->getAttribute('id') ?? null);
            }

            return null;
        });
    }


    protected function applyDomainInvariants(): void
    {
        $isCreate = !$this->exists;
        if ($isCreate)
            $this->enforceDerivedDefaultsOnCreate();
        $this->enforceRecruiterManagerIdentityHints();
        $this->enforceReceivingEmailResolution();
        $this->enforceApplicantMirror();
        $this->enforceGeoPipeline();
        $this->enforceAllowedApplicantGeo();
        $this->enforceDomainCoercions();
        $this->enforceSlugAndCodeUniqueness($this->exists);
        $this->enforceStatusFromHire();
    }

    protected function enforceGeoPipeline(): void
    {
        $geoCols = ['country', 'state', 'city', 'address', 'branch'];
        $shouldRun =
            !$this->exists || // create
            $this->isDirty($geoCols) ||
            $this->getAttribute('country') === null ||
            trim((string) $this->getAttribute('country')) === '' ||
            $this->getAttribute('state') === null ||
            trim((string) $this->getAttribute('state')) === '';
        if (!$shouldRun)
            return;
        $this->enforceGeoColumnsFromBranchIfNeeded();
        $this->rescueCountryStateFromKnownCityList($this);
        $this->normalizeGeo();
    }

    protected function enforceDerivedDefaultsOnCreate(): void
    {
        $curr = $this->getAttribute(AC::COL_EXP_SLR_CURR);
        if (!is_scalar($curr) || trim((string) $curr) === '')
            $this->setAttribute(AC::COL_EXP_SLR_CURR, SC::DEF_SITE_CURRENCY_ID);

        $level = $this->getAttribute('level');
        if ($level === null || (is_scalar($level) && trim((string) $level) === ''))
            $this->setAttribute('level', JobLevel::Staff->value);

        $st = $this->getAttribute('status');
        if ($st === null || (is_scalar($st) && trim((string) $st) === ''))
            $this->setAttribute('status', EvaluationStatus::Draft->value);

        $vis = $this->getAttribute('visibility');
        if ($vis === null || (is_scalar($vis) && trim((string) $vis) === ''))
            $this->setAttribute('visibility', Visibility::Draft->value);
    }

    protected function enforceGeoColumnsFromBranchIfNeeded(): void
    {
        $country = $this->getAttribute('country');
        $state = $this->getAttribute('state');

        $hasCountry = $country !== null && (!is_string($country) || trim($country) !== '');
        $hasState = $state !== null && (!is_string($state) || trim($state) !== '');

        if ($hasCountry && $hasState) {
            $this->enforceCountryStateColumns($this, 'country', 'state');
            return;
        }

        $branch = $this->branchModel ?: $this->resolveBranchMinimal();
        if (!$branch) {
            $this->enforceCountryStateColumns($this, 'country', 'state');
            return;
        }

        $branchCountry = is_scalar($branch->getAttribute('country') ?? null) ? (string) $branch->getAttribute('country') : null;
        $branchState = is_scalar($branch->getAttribute('state') ?? null) ? (string) $branch->getAttribute('state') : null;

        if (!$hasCountry && $branchCountry !== null && trim($branchCountry) !== '')
            $this->setAttribute('country', $branchCountry);

        if (!$hasState && $branchState !== null && trim($branchState) !== '')
            $this->setAttribute('state', $branchState);

        try {
            $this->rescueGeoFromAddressTokensIfMissing();
        } catch (\Throwable) {
            //
        }
        $this->enforceCountryStateColumns($this, 'country', 'state');
    }

    protected function enforceAllowedApplicantGeo(): void
    {
        $rawAllowedCountries = $this->getAttribute(AC::COL_ALW_CTR);
        $rawAllowedStates = $this->getAttribute(AC::COL_ALW_ST);

        if ($rawAllowedCountries === null) {
            $guess = $this->guessDefaultAllowedCountriesFromTimezone();
            if ($guess !== null) $this->setAttribute(AC::COL_ALW_CTR, $guess);
        } else {
            $this->setAttribute(AC::COL_ALW_CTR, $this->getAllowedCountriesConstraintAttribute());
        }

        if ($rawAllowedStates === null) {
            $guess = $this->guessDefaultAllowedStatesFromTimezone();
            if ($guess !== null) $this->setAttribute(AC::COL_ALW_ST, $guess);
        } else {
            $this->setAttribute(AC::COL_ALW_ST, $this->getAllowedStatesConstraintAttribute());
        }

        $singleCountry = $this->getAllowedCountriesConstraintAttribute();
        if (is_array($singleCountry) && count($singleCountry) === 1) {
            $only = (string) $singleCountry[0];
            $jobCountry = $this->normalizeCountryToCode((string) ($this->getAttribute('country') ?? ''));
            if ($jobCountry !== null && $jobCountry === $only) {
                $requiresVisa = $this->getAttribute(AC::COL_RQ_VS);
                if ($requiresVisa === true)
                    $this->setAttribute(AC::COL_RQ_VS, null);
            }
        }
    }

    protected function enforceDomainCoercions(): void
    {
        $minExp = $this->getAttribute(AC::COL_MIN_EXP_Y);
        if (is_numeric($minExp)) {
            $v = (int) $minExp;
            if ($v < 0) $v = 0;
            if ($v > 16) $v = 16;
            $this->setAttribute(AC::COL_MIN_EXP_Y, $v);
        }

        $match = $this->getAttribute(AC::COL_MIN_SKL_MTCH);
        if (is_numeric($match)) {
            $v = (float) $match;
            if ($v < 0) $v = 0;
            if ($v > 100) $v = 100;
            $this->setAttribute(AC::COL_MIN_SKL_MTCH, $v);
        }

        $relocation = $this->getAttribute(AC::COL_RQ_RLC);
        if ($relocation === false) {
            $this->setAttribute(AC::COL_RLC_PV, false);
            $this->setAttribute(AC::COL_MAX_RLC_DAYS, null);
        }

        $maxAp = $this->getAttribute(AC::COL_MAX_AP);
        if (is_numeric($maxAp)) {
            $v = (int) $maxAp;
            if ($v < 0) $v = 0;
            $this->setAttribute(AC::COL_MAX_AP, $v);
        }

        $curAp = $this->getAttribute(AC::COL_CUR_AP);
        if (is_numeric($curAp)) {
            $v = (int) $curAp;
            if ($v < 0) $v = 0;
            $this->setAttribute(AC::COL_CUR_AP, $v);
        }

        $emailCols = [
            AC::COL_RCT_EML => 'job.recruiter_email',
            AC::COL_MNG_EML => 'job.manager_email',
            AC::COL_RCV_EML => 'job.receiving_email',
            AC::COL_RES_EML => 'job.response_email',
        ];

        foreach ($emailCols as $col => $ctx) {
            $v = $this->getAttribute($col);
            if (!is_scalar($v) || trim((string) $v) === '') continue;
            $this->setAttribute($col, static::normalizeEmail((string) $v, $ctx, $this->getAttribute('id') ?? null));
        }
    }

    protected function enforceSlugAndCodeUniqueness(bool $isUpdate = false): void
    {
        $titleDirty = $this->isDirty('title');
        $slugDirty  = $this->isDirty('slug');
        $codeDirty  = $this->isDirty('code');
        $slugEmpty = trim((string) ($this->getAttribute('slug') ?? '')) === '';
        $codeEmpty = trim((string) ($this->getAttribute('code') ?? '')) === '';
        if (!$isUpdate || $titleDirty || $slugDirty || $slugEmpty || $codeDirty || $codeEmpty) {
            if (method_exists(parent::class, 'enforceSlugAndCodeUniqueness'))
                parent::enforceSlugAndCodeUniqueness($isUpdate); // @phpstan-ignore staticMethod.notFound
        }
    }

    protected function enforceStatusFromHire(): void
    {
        if ($this->exists && !$this->isDirty([AC::COL_HRD_ID, 'hired']))
            return;
        $hid = $this->getAttribute(AC::COL_HRD_ID);
        $hired = $this->getAttribute('hired');
        $hasHire = (is_scalar($hid) && trim((string) $hid) !== '')
            || (is_scalar($hired) && trim((string) $hired) !== '');
        if ($hasHire)
            $this->setAttribute('status', EvaluationStatus::Completed->value);
    }


    protected function enforceReceivingEmailResolution(): void
    {
        $effective = $this->getEffectiveReceivingEmailAttribute();
        $current = $this->getAttribute(AC::COL_RCV_EML);

        if (($current === null || trim((string) $current) === '') && $effective !== null)
            $this->setAttribute(AC::COL_RCV_EML, $effective);
    }

    protected function enforceRecruiterManagerIdentityHints(): void
    {
        $this->enforceUserNameOverrideIfIdPresent(AC::COL_RCT_ID, AC::COL_RCT_NM);
        $this->enforceUserNameOverrideIfIdPresent(AC::COL_MNG_ID, AC::COL_MNG_NM);

        $this->enforceUserEmailFillIfIdPresent(AC::COL_RCT_ID, AC::COL_RCT_EML);
        $this->enforceUserEmailFillIfIdPresent(AC::COL_MNG_ID, AC::COL_MNG_EML);
    }

    protected function enforceApplicantMirror(): void
    {
        $applicant = $this->getAttribute('applicant');
        if (is_scalar($applicant) && trim((string) $applicant) !== '')
            return;

        $hid = $this->getAttribute(AC::COL_HRD_ID);
        $hired = $this->getAttribute('hired');

        if (is_scalar($hired) && trim((string) $hired) !== '') {
            $this->setAttribute('applicant', trim((string) $hired));
            return;
        }

        if (is_scalar($hid) && trim((string) $hid) !== '') {
            $name = $this->resolveUserNameById((string) $hid);
            if ($name !== null) $this->setAttribute('applicant', $name);
        }
    }

    protected function buildUniqueSlug(string $title, string $id): string
    {
        $base = Str::of($title)->lower()->replaceMatches('/[^a-z0-9]+/i', '-')->trim('-')->substr(0, 180)->toString();
        if ($base === '') $base = 'job';

        return $this->buildUniqueSlugFromGiven($base, $id, $title);
    }

    protected function buildUniqueSlugFromGiven(string $candidate, string $id, string $title = ''): string
    {
        $maxAttempts = 25;
        $attempt = 0;
        $out = $candidate;

        while ($this->slugExists($out, $id) && $attempt < $maxAttempts) {
            $attempt++;
            $suffix = Str::lower(Str::uuid()->toString());
            $ts = (string) time();

            $prefixTitle = $title !== '' ? Str::of($title)->lower()->replaceMatches('/[^a-z0-9]+/i', '-')->trim('-')->substr(0, 80)->toString() : '';
            $out = 'job_' . ($prefixTitle !== '' ? ($prefixTitle . '_') : '') . $suffix . '_' . $ts;
            $out = Str::of($out)->replaceMatches('/[^a-z0-9_]+/i', '-')->trim('-')->substr(0, 254)->toString();
        }

        if ($this->slugExists($out, $id))
            $out = 'job_' . Str::lower(Str::uuid()->toString()) . '_' . (string) time();

        return $out;
    }

    protected function buildUniqueCode(string $id): string
    {
        $code = 'JOB-' . Str::upper(Str::uuid()->toString()) . '_' . (string) time();
        return $this->buildUniqueCodeFromGiven($code, $id);
    }

    protected function buildUniqueCodeFromGiven(string $candidate, string $id): string
    {
        $maxAttempts = 25;
        $attempt = 0;
        $out = trim($candidate) !== '' ? trim($candidate) : ('JOB-' . Str::upper(Str::uuid()->toString()) . '_' . (string) time());

        while ($this->codeExists($out, $id) && $attempt < $maxAttempts) {
            $attempt++;
            $out = 'JOB-' . Str::upper(Str::uuid()->toString()) . '_' . (string) time();
        }

        if ($this->codeExists($out, $id))
            $out = 'JOB-' . Str::upper(Str::uuid()->toString()) . '_' . (string) time();

        return $out;
    }

    protected function slugExists(string $slug, string $id): bool
    {
        if (!Schema::hasColumn($this->getTable(), 'slug')) return false;

        $q = DB::table($this->getTable())->where('slug', $slug);
        if ($id !== '') $q->where('id', '!=', $id);
        return $q->exists();
    }

    protected function codeExists(string $code, string $id): bool
    {
        if (!Schema::hasColumn($this->getTable(), 'code')) return false;

        $q = DB::table($this->getTable())->where('code', $code);
        if ($id !== '') $q->where('id', '!=', $id);
        return $q->exists();
    }

    protected function resolveBranchMinimal(): ?Model
    {
        $bid = $this->getAttribute('branch');
        if (!is_scalar($bid) || trim((string) $bid) === '') return null;
        if (!class_exists(Branch::class)) return null;

        return Branch::query()
            ->select(['id', 'country', 'state', 'email'])
            ->where('id', (string) $bid)
            ->first();
    }

    protected function guessDefaultAllowedCountriesFromTimezone(): ?array
    {
        $tz = (string) env('APP_TIMEZONE', '');
        $tz = trim($tz);
        if ($tz === '') return null;

        $cc = match (true) {
            Str::contains($tz, ['America/Sao_Paulo', 'America/São_Paulo', 'America/Belem', 'America/Fortaleza', 'America/Manaus', 'America/Recife', 'America/Bahia', 'America/Campo_Grande', 'America/Cuiaba', 'America/Porto_Velho', 'America/Boa_Vista', 'America/Rio_Branco'], true) => 'BR',
            Str::startsWith($tz, 'America/') => 'US',
            Str::startsWith($tz, 'Europe/Lisbon') => 'PT',
            Str::startsWith($tz, 'Asia/Shanghai') => 'CN',
            default => null,
        };

        if ($cc === null) return null;

        $e = CountryName::normalize($cc);
        return $e instanceof CountryName ? [$e->value] : null;
    }

    protected function guessDefaultAllowedStatesFromTimezone(): ?array
    {
        $countries = $this->guessDefaultAllowedCountriesFromTimezone();
        if (!$countries || count($countries) !== 1) return null;

        $cc = $this->normalizeCountryToCode((string) $countries[0]);
        if ($cc === null) return null;

        $cls = $this->stateEnumClassForCountry($cc);
        if (!$cls || !enum_exists($cls)) return null;

        return [$cc => array_values(array_map(fn(\BackedEnum $e) => (string) $e->value, $cls::cases()))];
    }

    protected function resolveUserEmailByUserIdColumn(string $col): ?string
    {
        $uid = $this->getAttribute($col);
        if (!is_scalar($uid) || trim((string) $uid) === '') return null;

        $email = DB::table(DC::TABLE_USERS)
            ->where('id', (string) $uid)
            ->value('email');

        if (!is_scalar($email) || trim((string) $email) === '') return null;

        return static::normalizeEmail((string) $email, 'user.email', (string) $uid);
    }

    protected function resolveUserNameById(string $userId): ?string
    {
        $name = DB::table(DC::TABLE_USERS)
            ->where('id', $userId)
            ->value('name');

        if (!is_scalar($name) || trim((string) $name) === '') return null;
        return trim((string) $name);
    }

    protected function enforceUserNameOverrideIfIdPresent(string $idCol, string $nameCol): void
    {
        $uid = $this->getAttribute($idCol);
        if (!is_scalar($uid) || trim((string) $uid) === '') return;

        $name = $this->resolveUserNameById((string) $uid);
        if ($name !== null) $this->setAttribute($nameCol, $name);
    }

    protected function enforceUserEmailFillIfIdPresent(string $idCol, string $emailCol): void
    {
        $uid = $this->getAttribute($idCol);
        if (!is_scalar($uid) || trim((string) $uid) === '') return;

        $cur = $this->getAttribute($emailCol);
        if (is_scalar($cur) && trim((string) $cur) !== '') return;

        $email = $this->resolveUserEmailByUserIdColumn($idCol);
        if ($email !== null) $this->setAttribute($emailCol, $email);
    }

    protected function cacheOnce(string $key, \Closure $cb): mixed
    {
        if (array_key_exists($key, $this->cacheLocal))
            return $this->cacheLocal[$key];
        return $this->cacheLocal[$key] = $cb();
    }

    // NOTE: created_by() alias removed — collides with 'created_by' column.
    // Use $model->createdBy (relation) or $model->getAttributes()['created_by'] (column).
}
