<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Enums\{DEICategory, Gender};
use App\Traits\{HasAuditFields, NormalizesAddresses, UsesCountryRegions, UsesUuids};
use Carbon\{Carbon, CarbonImmutable};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};
use Illuminate\Support\Facades\{DB, Log, Schema};

class JobApplication extends Model
{
    // todo ParsesDocument will be implemented later
    use UsesUuids, HasAuditFields, NormalizesAddresses, UsesCountryRegions;

    protected $table = DC::TABLE_JOB_APPS;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        'created_at',
    ];

    protected $fillable = [
        'job',
        AC::COL_APL_ID,

        'name',
        'email',
        'phone',
        'source',
        'announcement',

        'country',
        'state',
        'city',
        'address',
        'zip',
        'ip',

        AC::COL_DEI_CTG,
        AC::COL_APL_AT,
        AC::COL_LST_RVW_AT,

        AC::COL_RFR_ID,
        AC::COL_RFR_NM,
        AC::COL_RFR_EML,

        AC::COL_EXP_SLR,
        AC::COL_EXP_SLR_CURR,

        AC::COL_CUR_EMP,
        AC::COL_CUR_PST,
        AC::COL_CUR_SLR,
        AC::COL_CUR_SLR_CURR,

        AC::COL_WRK_AUTH,
        AC::COL_WRK_AUTH_APV,
        AC::COL_NTC_PRD,

        AC::COL_NXT_ITV_AT,
        AC::COL_ITV_NTS,
        AC::COL_ITV_NT_ID,
        AC::COL_ITV_SCRS,

        'profile',
        AC::COL_PRF_DOC,

        'portfolio',
        'website',

        'resume',
        AC::COL_RSM_DOC,

        AC::COL_CV_LT,
        AC::COL_CV_LT_DOC,

        'dob',
        'gender',

        'experience',
        AC::COL_EXP_DOC,

        'education',
        AC::COL_ED_DOC,

        'stage',
        'order',

        'skill',
        AC::COL_SKL_DOC,

        'rating',
        AC::COL_RJC_RS,
        AC::COL_RJC_AT,

        'feedback',
        AC::COL_FDB_DOC,

        AC::COL_IS_ARC,
        AC::COL_CT_QT,
        AC::COL_CT_QT_ID,

        AC::COL_TRMS_ACPT,

        'certifications',
        'awards',
        'publications',
        'projects',
        'languages',
        'references',
        'diversity',
        'disabilities',
        AC::COL_SC_MD,
        'questions',
        'tests',
        'notes',
        'attachments',
    ];

    protected $with = [
        'jobRelation',
    ];

    protected $casts = [
        AC::COL_DEI_CTG => 'string',
        AC::COL_APL_AT => 'datetime',
        AC::COL_LST_RVW_AT => 'datetime',
        AC::COL_NXT_ITV_AT => 'datetime',
        AC::COL_RJC_AT => 'date',
        'dob' => 'date',

        AC::COL_WRK_AUTH_APV => 'boolean',
        AC::COL_TRMS_ACPT => 'boolean',

        AC::COL_EXP_SLR => 'decimal:2',
        AC::COL_CUR_SLR => 'decimal:2',

        'interviews' => 'array',
        AC::COL_ITV_SCRS => 'array',

        'certifications' => 'array',
        'awards' => 'array',
        'publications' => 'array',
        'projects' => 'array',
        'languages' => 'array',
        'references' => 'array',
        'diversity' => 'array',
        'disabilities' => 'array',
        AC::COL_SC_MD => 'array',
        'questions' => 'array',
        'tests' => 'array',
        'notes' => 'array',
        'attachments' => 'array',
    ];

    protected $appends = [
        'is_archived',
        'age_years',
    ];

    protected array $localCache = [];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            $m->normalizeCoreIdentity();
            $m->rescueGeoFromAddressTokensIfMissing();
            $m->rescueGeoFromZipIfMissing();
            $m->rescueCountryStateFromKnownCityList($m);
            $m->normalizeGeo();
            $m->normalizeDatesAndStages();
            $m->normalizeFlagsAndScalars();
            $m->normalizeDei();
            $m->normalizeReferrer();
            $m->normalizeInterviewScores();
            $m->normalizeCurrencyCodes();
            $m->processInterviews($m);
            // todo methods from ParseDocuments trait will be called here later
        });
    }

    public function jobRelation(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job', 'id');
    }

    public function jobs(): BelongsTo // * legacy, DO NOT use
    {
        return $this->jobRelation();
    }

    public function applicant(): ?BelongsTo
    {
        return $this->belongsTo(User::class, AC::COL_APL_ID, 'id');
    }

    public function announcementRelation(): ?BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement', 'id');
    }

    public function referrer(): ?BelongsTo
    {
        return $this->belongsTo(User::class, AC::COL_RFR_ID, 'id');
    }

    public function getIsArchivedAttribute(): bool
    {
        $v = $this->getAttribute(AC::COL_IS_ARC);
        if ($v === null) return false;
        $n = is_numeric($v) ? (int) $v : 0;
        return $n !== 0 && ($n % 2 !== 0);
    }

    public function getAgeYearsAttribute(): ?int
    {
        $dob = $this->getAttribute('dob');
        if (!$dob) return null;

        try {
            $d = CarbonImmutable::parse($dob)->startOfDay();
            $now = CarbonImmutable::now()->startOfDay();
            if ($d->greaterThan($now)) return null;
            return (int) $d->diffInYears($now);
        } catch (\Throwable) {
            return null;
        }
    }

    public function cacheOnce(string $key, callable $fn): mixed
    {
        if (array_key_exists($key, $this->localCache)) return $this->localCache[$key];
        return $this->localCache[$key] = $fn();
    }

    public function incrementViewSafe(string $column, int $by = 1): bool
    {
        try {
            $by = max(1, (int) $by);
            return (bool) static::query()
                ->whereKey($this->getAttribute($this->getKeyName()))
                ->update([$column => DB::raw("COALESCE({$column},0)+{$by}")]);
        } catch (\Throwable $t) {
            Log::warning(static::class . ' failed to increment counter', [
                'column' => $column,
                'by' => $by,
                'error' => $t->getMessage(),
                'file' => $t->getFile(),
                'line' => $t->getLine(),
            ]);
            return false;
        }
    }

    protected function normalizeCoreIdentity(): void
    {
        $this->setAttribute('email', static::normalizeEmail(
            is_scalar($this->getAttribute('email') ?? null) ? (string) $this->getAttribute('email') : null,
            'job_application.email',
            (string) ($this->getAttribute($this->getKeyName()) ?? '')
        ));

        $this->setAttribute('phone', static::normalizePhone(
            is_scalar($this->getAttribute('phone') ?? null) ? (string) $this->getAttribute('phone') : null,
            'job_application.phone',
            (string) ($this->getAttribute($this->getKeyName()) ?? ''),
            false
        ));

        $this->setAttribute('zip', static::normalizeZip(
            is_scalar($this->getAttribute('zip') ?? null) ? (string) $this->getAttribute('zip') : null,
            is_scalar($this->getAttribute('country') ?? null) ? (string) $this->getAttribute('country') : null,
            'job_application.zip',
            (string) ($this->getAttribute($this->getKeyName()) ?? '')
        ));
    }

    protected function normalizeDatesAndStages(): void
    {
        $now = CarbonImmutable::now();

        $appliedAt = $this->safeParseDateTime($this->getAttribute(AC::COL_APL_AT));
        if ($appliedAt instanceof CarbonImmutable && $appliedAt->greaterThan($now)) $appliedAt = $now;
        $this->setAttribute(AC::COL_APL_AT, $appliedAt?->toDateTimeString());

        $lastReviewAt = $this->safeParseDateTime($this->getAttribute(AC::COL_LST_RVW_AT));
        if ($appliedAt instanceof CarbonImmutable && $lastReviewAt instanceof CarbonImmutable && $lastReviewAt->lessThan($appliedAt))
            $lastReviewAt = $appliedAt;
        $this->setAttribute(AC::COL_LST_RVW_AT, $lastReviewAt?->toDateTimeString());

        $nextItvAt = $this->safeParseDateTime($this->getAttribute(AC::COL_NXT_ITV_AT));
        $floor = $appliedAt instanceof CarbonImmutable ? $appliedAt : null;
        if ($lastReviewAt instanceof CarbonImmutable && (!$floor || $lastReviewAt->greaterThan($floor)))
            $floor = $lastReviewAt;

        if ($nextItvAt instanceof CarbonImmutable && $floor instanceof CarbonImmutable && $nextItvAt->lessThan($floor))
            $nextItvAt = $floor;
        if ($nextItvAt instanceof CarbonImmutable && $nextItvAt->lessThan($now->startOfDay()))
            $nextItvAt = $now;
        $this->setAttribute(AC::COL_NXT_ITV_AT, $nextItvAt?->toDateTimeString());

        $rejectedAt = $this->safeParseDate($this->getAttribute(AC::COL_RJC_AT));
        if ($rejectedAt) {
            $rejectedAt = $rejectedAt instanceof CarbonImmutable
                ? Carbon::instance($rejectedAt)
                : ($rejectedAt instanceof Carbon ? $rejectedAt : null);

            if ($rejectedAt instanceof Carbon && $rejectedAt->greaterThan($now)) {
                $rejectedAt = null;
            }
        }

        if ($rejectedAt instanceof Carbon && $appliedAt instanceof CarbonImmutable && CarbonImmutable::parse($rejectedAt)->lessThan($appliedAt->startOfDay()))
            $rejectedAt = null;
        if ($rejectedAt instanceof Carbon && $lastReviewAt instanceof CarbonImmutable && CarbonImmutable::parse($rejectedAt)->lessThan($lastReviewAt->startOfDay()))
            $rejectedAt = null;

        $this->setAttribute(AC::COL_RJC_AT, $rejectedAt);

        if ($this->getAttribute(AC::COL_RJC_AT) !== null)
            $this->setAttribute(AC::COL_NXT_ITV_AT, null);

        $dob = $this->safeParseDate($this->getAttribute('dob'));
        if ($dob !== null) {
            try {
                $d = $dob instanceof CarbonImmutable
                    ? $dob
                    : CarbonImmutable::parse($dob);
                $d = $d->startOfDay();

                if ($d->greaterThan($now->startOfDay())) {
                    $dob = null;
                } else {
                    $age = (int) $d->diffInYears($now->startOfDay());
                    if ($age < 18 || $age > 124) $dob = null;
                }
            } catch (\Throwable) {
                $dob = null;
            }
        }
        $this->setAttribute('dob', $dob);

        $stage = $this->getAttribute('stage');
        $stage = is_numeric($stage) ? (int) $stage : 1;
        $this->setAttribute('stage', max(1, $stage));

        $order = $this->getAttribute('order');
        $order = is_numeric($order) ? (int) $order : 0;
        $this->setAttribute('order', max(0, $order));
    }

    protected function normalizeFlagsAndScalars(): void
    {
        $rating = $this->getAttribute('rating');
        $rating = is_numeric($rating) ? (int) $rating : 0;
        $this->setAttribute('rating', min(100, max(0, $rating)));

        $notice = $this->getAttribute(AC::COL_NTC_PRD);
        $notice = is_numeric($notice) ? (int) $notice : 30;
        $this->setAttribute(AC::COL_NTC_PRD, max(0, $notice));

        $min = $this->getAttribute(AC::COL_WRK_AUTH_APV);
        $this->setAttribute(AC::COL_WRK_AUTH_APV, (bool) $min);

        $ta = $this->getAttribute(AC::COL_TRMS_ACPT);
        $this->setAttribute(AC::COL_TRMS_ACPT, (bool) $ta);

        $g = Gender::normalize($this->getAttribute('gender'));
        $this->setAttribute('gender', ($g ?? Gender::Other)->value);
    }

    protected function normalizeDei(): void
    {
        $raw = $this->getAttribute(AC::COL_DEI_CTG);
        if ($raw === null || $raw === '') {
            $this->setAttribute(AC::COL_DEI_CTG, null);
            return;
        }

        try {
            $val = (string) $raw;
            $ok = in_array($val, array_column(DEICategory::cases(), 'value'), true);
            $this->setAttribute(AC::COL_DEI_CTG, $ok ? $val : null);
        } catch (\Throwable $t) {
            Log::warning(static::class . ' failed to normalize dei category', [
                'error' => $t->getMessage(),
                'file' => $t->getFile(),
                'line' => $t->getLine(),
            ]);
            $this->setAttribute(AC::COL_DEI_CTG, null);
        }
    }

    protected function normalizeReferrer(): void
    {
        $this->setAttribute(AC::COL_RFR_EML, static::normalizeEmail(
            is_scalar($this->getAttribute(AC::COL_RFR_EML) ?? null) ? (string) $this->getAttribute(AC::COL_RFR_EML) : null,
            'job_application.referrer_email',
            (string) ($this->getAttribute($this->getKeyName()) ?? '')
        ));
    }

    protected function normalizeInterviewScores(): void
    {
        $v = $this->getAttribute(AC::COL_ITV_SCRS);
        if ($v === null) return;

        if (!is_array($v)) {
            $this->setAttribute(AC::COL_ITV_SCRS, null);
            return;
        }

        $out = [];
        foreach ($v as $k => $score) {
            if (!is_scalar($k)) continue;
            $key = trim((string) $k);
            if ($key === '') continue;
            if (!is_numeric($score)) continue;
            $out[$key] = (int) $score;
        }

        $this->setAttribute(AC::COL_ITV_SCRS, $out ?: null);
    }

    protected function normalizeCurrencyCodes(): void
    {
        foreach ([AC::COL_EXP_SLR_CURR, AC::COL_CUR_SLR_CURR] as $col) {
            $v = $this->getAttribute($col);
            if (!is_scalar($v)) continue;
            $s = strtoupper(trim((string) $v));
            $this->setAttribute($col, $s !== '' ? $s : null);
        }
    }

    protected function safeParseDateTime(mixed $v): ?CarbonImmutable
    {
        if ($v === null || $v === '') return null;
        try {
            return CarbonImmutable::parse($v);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function safeParseDate(mixed $v): ?string
    {
        if ($v === null || $v === '') return null;
        try {
            return CarbonImmutable::parse($v)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Process interviews column and set next interview datetime
     * 
     * @param Model $m The JobApplication model instance
     * @return void
     */
    protected function processInterviews(Model $m): void
    {
        $interviewsRaw = $m->getAttribute('interviews');
        if (!$interviewsRaw)
            return;
        $validInterviewIds = $this->extractValidInterviewIds($interviewsRaw);
        if (empty($validInterviewIds))
            return;
        $interviewSchedules = $this->fetchInterviewSchedules($validInterviewIds);
        if (empty($interviewSchedules))
            return;
        $nextInterview = $this->findNextInterview($interviewSchedules);
        if ($nextInterview) {
            $m->setAttribute(AC::COL_NXT_ITV_AT, $nextInterview['datetime']);
            if (Schema::hasColumn(DC::TABLE_JOB_APPS, AC::COL_NXT_ITV))
                $m->setAttribute(AC::COL_NXT_ITV, $nextInterview['id']);
        }
    }

    /**
     * Extract and validate interview IDs from various formats
     * 
     * @param mixed $interviewsRaw Raw interviews data (JSON string or array)
     * @return array<string> Array of valid interview schedule UUIDs
     */
    protected function extractValidInterviewIds(mixed $interviewsRaw): array
    {
        if (is_string($interviewsRaw)) {
            try {
                $interviews = json_decode($interviewsRaw, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return [];
            }
        } elseif (is_array($interviewsRaw))
            $interviews = $interviewsRaw;
        else
            return [];
        if (!is_array($interviews))
            return [];
        $validIds = [];
        $candidateId = null;
        foreach ($interviews as $interview) {
            if (is_string($interview) && Utility::looksLikeUuid($interview)) {
                $validIds[] = $interview;
                continue;
            }
            if (is_array($interview)) {
                $resolvedId = $this->resolveInterviewReference($interview, $candidateId);
                if ($resolvedId)
                    $validIds[] = $resolvedId;
            }
        }
        $validIds = array_unique($validIds);
        return $this->verifyInterviewsExist($validIds);
    }

    /**
     * Resolve interview reference from associative array
     * 
     * @param array $interview Interview reference data
     * @param string|null &$candidateId Reference to candidate ID (will be set if found)
     * @return string|null Resolved interview schedule UUID
     */
    protected function resolveInterviewReference(array $interview, ?string &$candidateId = null): ?string
    {
        $id = $interview['id'] ?? null;
        if (
            $id && Utility::looksLikeUuid($id) && DB::table(DC::TABLE_ITV_SCD)
            ->where('id', $id)
            ->exists()
        )
            return $id;
        $candidate = $interview['candidate'] ?? null;
        if (!$candidate)
            return null;
        $candidateId = $candidate;
        $schedule = DB::table(DC::TABLE_ITV_SCD)
            ->where('candidate', $candidate)
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->first(['id']);
        return $schedule?->id;
    }

    /**
     * Verify interview IDs exist in database
     * 
     * @param array<string> $ids Interview schedule UUIDs to verify
     * @return array<string> Only IDs that exist in database
     */
    protected function verifyInterviewsExist(array $ids): array
    {
        if (empty($ids))
            return [];
        return DB::table(DC::TABLE_ITV_SCD)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();
    }

    /**
     * Fetch interview schedules with date and time
     * 
     * @param array<string> $ids Valid interview schedule UUIDs
     * @return array<array{id: string, candidate: string, date: string, time: string, datetime: string}>
     */
    protected function fetchInterviewSchedules(array $ids): array
    {
        if (empty($ids))
            return [];
        $schedules = DB::table(DC::TABLE_ITV_SCD)
            ->whereIn('id', $ids)
            ->select(['id', 'candidate', 'date', 'time'])
            ->get();
        $result = [];
        foreach ($schedules as $schedule) {
            $datetime = $this->buildDateTime($schedule->date, $schedule->time);
            if ($datetime)
                $result[] = [
                    'id' => $schedule->id,
                    'candidate' => $schedule->candidate,
                    'date' => $schedule->date,
                    'time' => $schedule->time,
                    'datetime' => $datetime,
                ];
        }

        return $result;
    }

    /**
     * Build a proper datetime string from date and time components
     * 
     * @param mixed $date Date component
     * @param mixed $time Time component
     * @return string|null Formatted datetime string (Y-m-d H:i:s) or null if invalid
     */
    protected function buildDateTime(mixed $date, mixed $time): ?string
    {
        if (!$date || !$time)
            return null;
        try {
            $dateCarbon = CarbonImmutable::parse($date);
            $timeCarbon = CarbonImmutable::parse($time);
            $datetime = $dateCarbon->setTime(
                $timeCarbon->hour,
                $timeCarbon->minute,
                $timeCarbon->second
            );
            return $datetime->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            Log::warning('Failed to build datetime from components', [
                'date' => $date,
                'time' => $time,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Find the next upcoming interview (closest in the future)
     * 
     * @param array<array{id: string, candidate: string, date: string, time: string, datetime: string}> $schedules
     * @return array{id: string, candidate: string, date: string, time: string, datetime: string}|null
     */
    protected function findNextInterview(array $schedules): ?array
    {
        if (empty($schedules))
            return null;
        $now = now();
        $nextInterview = null;
        $closestDiff = null;
        foreach ($schedules as $schedule) {
            try {
                $interviewDateTime = CarbonImmutable::parse($schedule['datetime']);
                if ($interviewDateTime->isFuture()) {
                    $diff = $now->diffInSeconds($interviewDateTime, false);
                    if ($closestDiff === null || $diff < $closestDiff) {
                        $closestDiff = $diff;
                        $nextInterview = $schedule;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to parse interview datetime', [
                    'schedule' => $schedule,
                    'error' => $e->getMessage()
                ]);
            }
        }
        return $nextInterview;
    }

    // protected function rescueDocumentBackedTextFields(): void
    // {
    //     $pairs = [
    //         ['profile', AC::COL_PRF_DOC],
    //         ['resume', AC::COL_RSM_DOC],
    //         [AC::COL_CV_LT, AC::COL_CV_LT_DOC],
    //         ['experience', AC::COL_EXP_DOC],
    //         ['education', AC::COL_ED_DOC],
    //         ['skill', AC::COL_SKL_DOC],
    //         ['feedback', AC::COL_FDB_DOC],
    //     ];
    //     foreach ($pairs as [$textCol, $docCol]) {
    //         try {
    //             $this->rescueTextFromLinkedDocumentIfEmpty($textCol, $docCol);
    //         } catch (\Throwable $t) {
    //             Log::warning(static::class . ' failed to rescue text field from doc', [
    //                 'text_col' => $textCol,
    //                 'doc_col' => $docCol,
    //                 'error' => $t->getMessage(),
    //             ]);
    //         }
    //     }
    // }
    // protected function rescueDocumentBackedTextFields(): void
    // {
    //     // Campos textuais “document-backed”
    //     // Regra: se o texto estiver vazio e o docId existir, tenta preencher com texto do documento.
    //     $pairs = [
    //         ['profile', AC::COL_PRF_DOC],
    //         ['resume', AC::COL_RSM_DOC],
    //         [AC::COL_CV_LT, AC::COL_CV_LT_DOC],
    //         ['experience', AC::COL_EXP_DOC],
    //         ['education', AC::COL_ED_DOC],
    //         ['skill', AC::COL_SKL_DOC],
    //         ['feedback', AC::COL_FDB_DOC],
    //     ];

    //     foreach ($pairs as [$textCol, $docCol]) {
    //         try {
    //             $this->rescueTextFromLinkedDocumentIfEmpty($textCol, $docCol);
    //         } catch (\Throwable $t) {
    //             // Nunca quebrar a persistência por parsing
    //             Log::warning(static::class . ' failed to rescue text field from doc', [
    //                 'text_col' => $textCol,
    //                 'doc_col' => $docCol,
    //                 'error' => $t->getMessage(),
    //             ]);
    //         }
    //     }
    // }
}
