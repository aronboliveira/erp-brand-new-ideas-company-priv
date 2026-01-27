<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\{AttachmentModuleType, EvaluationStatus, MimeType};
use App\Helpers\ErrorHandler;
use App\Traits\{DefinesDates, FiltersSecureAttachments, HasAuditFields, NormalizesArrays, PlansByHierarchy, UsesUuids};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

final class ContractAttachment extends AbstractFile
{
    use UsesUuids, HasAuditFields, DefinesDates, NormalizesArrays, FiltersSecureAttachments, PlansByHierarchy;

    protected $table = DC::TABLE_CTC_ATC;

    protected $fillable = [
        'code',
        PJC::COL_CTC_ID,
        UC::COL_USER_ID,
        PJC::COL_SBM_AT,
        PJC::COL_APV_BY,
        PJC::COL_APV_AT,
        PJC::COL_REJ_BY,
        PJC::COL_REJ_AT,
        ...self::ABSTRACT_FILE_FILLABLE,
        PJC::COL_ATC_TP,
        'files',
        'metadata'
    ];

    protected $casts = [
        PJC::COL_SBM_AT => 'datetime',
        PJC::COL_APV_AT => 'datetime',
        PJC::COL_REJ_AT => 'datetime',
        PJC::COL_ATC_TP => AttachmentModuleType::class,
        'metadata'      => 'array',
        ...self::ABSTRACT_FILE_CASTS,

    ];

    protected $with = [
        'contract',
        'user',
    ];

    protected $appends = [
        'main_file',
        'files_list',
        'is_approved',
        'is_rejected',
        ...self::ABSTRACT_FILE_APPENDS,
    ];

    protected static array $contractAttachmentErrors = [];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (self $m): void {
            try {
                $m->ensureCodeIsPresentAndUnique();
                $m->normalizeAttachmentType();
                $m->normalizeFilesList();
                $m->importApprovalRejectionFromContractIfApplicable();
                $m->resolveApprovalRejectionWinner();
                if (method_exists($m, 'ensureJsonAttributesAreEncoded'))
                    $m->ensureJsonAttributesAreEncoded(['metadata']);
            } catch (\Throwable $e) {
                ErrorHandler::evaluateExistenceToLogChannel(
                    'contract_attachment_errors',
                    candidate: [
                        'message' => 'saving hook failed on ContractAttachment model',
                        'context' => [
                            'id' => $m->getAttribute('id'),
                            'error' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]
                    ]
                );
            }
        });
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, PJC::COL_CTC_ID, 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_APV_BY, 'id');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_REJ_BY, 'id');
    }

    public function getMainFileAttribute(): ?string
    {
        return $this->getMainFileIdentifier();
    }

    public function getFilesListAttribute(): array
    {
        return $this->parseFilesToArray($this->getAttribute('files'));
    }

    public function getIsApprovedAttribute(): bool
    {
        $by = $this->getAttribute(PJC::COL_APV_BY);
        return is_string($by) ? trim($by) !== '' : !empty($by);
    }

    public function getIsRejectedAttribute(): bool
    {
        $by = $this->getAttribute(PJC::COL_REJ_BY);
        return is_string($by) ? trim($by) !== '' : !empty($by);
    }

    protected function ensureCodeIsPresentAndUnique(): void
    {
        $raw = $this->getAttribute('code');
        $code = is_string($raw) ? trim($raw) : '';

        $isValid = false;
        if ($code !== '' && str_starts_with($code, 'CTC-ATC-')) {
            $uuidPart = substr($code, 8);
            $isValid = $uuidPart !== false
                && Str::isUuid($uuidPart)
                && preg_match('/^CTC\-ATC\-[0-9a-fA-F\-]{36}$/', $code) === 1;
        }

        try {
            if (
                $isValid && !DB::table($this->getTable())
                    ->where('code', $code)
                    ->where('id', '!=', (string) ($this->getAttribute('id') ?? ''))
                    ->exists()
            ) return;
        } catch (\Throwable $e) {
            Log::notice(static::class . ' failed checking code uniqueness', [
                'id' => $this->getAttribute('id'),
                'code' => $code,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        $attempts = 0;
        $max = 250;
        do {
            $attempts++;
            $candidate = 'CTC-ATC-' . strtoupper((string) Str::uuid());

            try {
                $exists = DB::table($this->getTable())
                    ->where('code', $candidate)
                    ->where('id', '!=', (string) ($this->getAttribute('id') ?? ''))
                    ->exists();
                if (!$exists) break;
            } catch (\Throwable $e) {
                Log::notice(static::class . ' failed checking candidate code existence', [
                    'id' => $this->getAttribute('id'),
                    'candidate' => $candidate,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                break;
            }
        } while ($attempts < $max);

        if ($attempts >= $max)
            Log::warning(static::class . ' code generation hit attempt limit', [
                'id' => $this->getAttribute('id'),
                'max_attempts' => $max,
            ]);

        $this->setAttribute('code', $candidate ?? ('CTC-ATC-' . strtoupper((string) Str::uuid())));
    }

    protected function normalizeAttachmentType(): void
    {
        $key = PJC::COL_ATC_TP;
        $raw = $this->getAttribute($key);

        $norm = $raw instanceof AttachmentModuleType
            ? $raw
            : (AttachmentModuleType::normalize(is_scalar($raw) ? (string) $raw : null) ?? AttachmentModuleType::Other);

        $this->setAttribute($key, $norm->value);
    }

    protected function ensureSubmissionTimestamp(): void
    {
        $userId = $this->getAttribute(UC::COL_USER_ID);
        $hasUser = is_string($userId) ? trim($userId) !== '' : !empty($userId);

        if (!$hasUser) return;

        $sbmAt = $this->getAttribute(PJC::COL_SBM_AT);
        if (empty($sbmAt)) $this->setAttribute(PJC::COL_SBM_AT, now());
    }

    protected function importDecisionFromContractIfNeeded(): void
    {
        $ctcId = $this->getAttribute(PJC::COL_CTC_ID);
        if (!is_string($ctcId) || trim($ctcId) === '') return;

        $sql = 'select status, '
            . PJC::COL_APV_BY . ' as apv_by, ' . PJC::COL_APV_AT . ' as apv_at, '
            . PJC::COL_REJ_BY . ' as rej_by, ' . PJC::COL_REJ_AT . ' as rej_at '
            . 'from ' . DC::TABLE_CONTRACTS . ' where id = ? limit 1';

        try {
            $row = DB::selectOne($sql, [$ctcId]);
        } catch (\Throwable $e) {
            Log::debug(static::class . ' failed reading contract for import', [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'error' => $e->getMessage(),
                'contract_id' => $ctcId,
            ]);
            return;
        }

        if (!$row) return;

        $status = EvaluationStatus::normalize($row->status ?? null);

        $apvStatuses = [EvaluationStatus::Accept, EvaluationStatus::Active];
        $rejStatuses = [EvaluationStatus::Suspended, EvaluationStatus::Cancelled, EvaluationStatus::Expired, EvaluationStatus::Decline];

        if (in_array($status, $apvStatuses, true)) {
            if (empty($this->getAttribute(PJC::COL_APV_BY)) && !empty($row->apv_by))
                $this->setAttribute(PJC::COL_APV_BY, $row->apv_by);
            if (empty($this->getAttribute(PJC::COL_APV_AT)) && !empty($row->apv_at))
                $this->setAttribute(PJC::COL_APV_AT, $row->apv_at);
        }

        if (in_array($status, $rejStatuses, true)) {
            if (empty($this->getAttribute(PJC::COL_REJ_BY)) && !empty($row->rej_by))
                $this->setAttribute(PJC::COL_REJ_BY, $row->rej_by);
            if (empty($this->getAttribute(PJC::COL_REJ_AT)) && !empty($row->rej_at))
                $this->setAttribute(PJC::COL_REJ_AT, $row->rej_at);
        }
    }

    protected function normalizeApprovalRejectionWinner(): void
    {
        $apvBy = $this->getAttribute(PJC::COL_APV_BY);
        $rejBy = $this->getAttribute(PJC::COL_REJ_BY);

        $hasApvBy = is_string($apvBy) ? trim($apvBy) !== '' : !empty($apvBy);
        $hasRejBy = is_string($rejBy) ? trim($rejBy) !== '' : !empty($rejBy);

        if (!$hasApvBy) $this->setAttribute(PJC::COL_APV_AT, null);
        if (!$hasRejBy) $this->setAttribute(PJC::COL_REJ_AT, null);

        if ($hasApvBy && empty($this->getAttribute(PJC::COL_APV_AT)))
            $this->setAttribute(PJC::COL_APV_AT, now());

        if ($hasRejBy && empty($this->getAttribute(PJC::COL_REJ_AT)))
            $this->setAttribute(PJC::COL_REJ_AT, now());

        if (!$hasApvBy || !$hasRejBy) return;

        $apvAt = $this->getAttribute(PJC::COL_APV_AT);
        $rejAt = $this->getAttribute(PJC::COL_REJ_AT);

        $apvTs = $apvAt ? strtotime((string) $apvAt) : null;
        $rejTs = $rejAt ? strtotime((string) $rejAt) : null;

        $rejWins =
            ($apvTs === null && $rejTs === null) ||
            ($apvTs === null && $rejTs !== null) ||
            ($apvTs !== null && $rejTs !== null && $rejTs >= $apvTs);

        if ($rejWins) {
            $this->setAttribute(PJC::COL_APV_BY, null);
            $this->setAttribute(PJC::COL_APV_AT, null);
            return;
        }

        $this->setAttribute(PJC::COL_REJ_BY, null);
        $this->setAttribute(PJC::COL_REJ_AT, null);
    }

    protected function normalizeFilesList(): void
    {
        $raw = $this->getAttribute('files');

        $list = [];
        if (is_string($raw)) {
            $parts = explode(',', $raw);
            foreach ($parts as $p) {
                $s = trim((string) $p);
                if ($s === '') continue;
                if (!in_array($s, $list, true)) $list[] = $s;
            }
        } elseif (is_array($raw)) {
            foreach ($raw as $p) {
                if (!is_scalar($p)) continue;
                $s = trim((string) $p);
                if ($s === '') continue;
                if (!in_array($s, $list, true)) $list[] = $s;
            }
        }

        $url = $this->getAttribute('url');
        $filePath = $this->getAttribute(DC::COL_FL_PT);

        $main = null;
        if (is_string($url) && trim($url) !== '') $main = trim($url);
        elseif (is_string($filePath) && trim($filePath) !== '') $main = trim($filePath);

        if ($main !== null) {
            $next = [];
            $next[] = $main;
            foreach ($list as $v)
                if ($v !== $main) $next[] = $v;
            $list = $next;
        }

        $this->setAttribute('files', $list ? implode(',', $list) : null);
    }

    protected function parseFilesToArray(mixed $value): array
    {
        if ($value === null) return [];
        if (is_array($value)) return array_values($value);

        $s = is_string($value) ? trim($value) : (string) $value;
        if ($s === '') return [];

        return array_values(array_filter(array_map('trim', explode(',', $s)), fn($v) => $v !== ''));
    }

    protected function getMainFileIdentifier(): ?string
    {
        $url = $this->getAttribute('url');
        if (is_string($url) && trim($url) !== '') return trim($url);

        $path = $this->getAttribute(DC::COL_FL_PT);
        if (is_string($path) && trim($path) !== '') return trim($path);

        return null;
    }

    protected function ensureFileNameIfMissing(): void
    {
        $name = $this->getAttribute('name');
        if (is_string($name) && trim($name) !== '') return;

        $this->setAttribute('name', 'FILE_' . strtoupper((string) Str::uuid()) . '_' . now()->format('YmdHis'));
    }

    protected function importApprovalRejectionFromContractIfApplicable(): void
    {
        $ctcIdKey = PJC::COL_CTC_ID;
        $contractId = $this->getAttribute($ctcIdKey);

        if (!is_string($contractId) || trim($contractId) === '' || !Str::isUuid($contractId))
            return;

        $contractsTable = DC::TABLE_CONTRACTS;

        try {
            $row = DB::table($contractsTable)
                ->where('id', $contractId)
                ->first([
                    'status',
                    PJC::COL_APV_BY,
                    PJC::COL_APV_AT,
                    PJC::COL_REJ_BY,
                    PJC::COL_REJ_AT,
                ]);

            if (!$row) return;

            $status = EvaluationStatus::normalize($row->status ?? null);

            $acceptLike = [
                EvaluationStatus::Accept->value,
                EvaluationStatus::Active->value,
            ];

            $rejectLike = [
                EvaluationStatus::Suspended->value,
                EvaluationStatus::Cancelled->value,
                EvaluationStatus::Expired->value,
                EvaluationStatus::Decline->value,
            ];

            $changes = [];

            if (in_array($status->value, $acceptLike, true)) {
                $apvByKey = PJC::COL_APV_BY;
                $apvAtKey = PJC::COL_APV_AT;

                if (empty($this->getAttribute($apvByKey)) && !empty($row->{$apvByKey} ?? null)) {
                    $this->setAttribute($apvByKey, (string) $row->{$apvByKey});
                    $changes['imported_apv_by'] = (string) $row->{$apvByKey};
                }

                if (empty($this->getAttribute($apvAtKey)) && !empty($row->{$apvAtKey} ?? null)) {
                    $this->setAttribute($apvAtKey, $row->{$apvAtKey});
                    $changes['imported_apv_at'] = (string) $row->{$apvAtKey};
                }
            } elseif (in_array($status->value, $rejectLike, true)) {
                $rejByKey = PJC::COL_REJ_BY;
                $rejAtKey = PJC::COL_REJ_AT;

                if (empty($this->getAttribute($rejByKey)) && !empty($row->{$rejByKey} ?? null)) {
                    $this->setAttribute($rejByKey, (string) $row->{$rejByKey});
                    $changes['imported_rej_by'] = (string) $row->{$rejByKey};
                }

                if (empty($this->getAttribute($rejAtKey)) && !empty($row->{$rejAtKey} ?? null)) {
                    $this->setAttribute($rejAtKey, $row->{$rejAtKey});
                    $changes['imported_rej_at'] = (string) $row->{$rejAtKey};
                }
            }

            if ($changes)
                $this->pushAutomaticEditHistory('import_from_contract_decision', [
                    'contract_id' => $contractId,
                    'contract_status' => $status->value,
                    ...$changes,
                ]);
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed importing decision from contract', [
                'id' => $this->getAttribute('id'),
                'contract_id' => $contractId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    protected function pushAutomaticEditHistory(string $event, array $payload = []): void
    {
        try {
            $meta = self::normalizeArrayField($this->getAttribute('metadata'));
            $hist = $meta['automatic_edit_history'] ?? [];
            if (!is_array($hist)) $hist = [];

            $hist[] = array_merge([
                'at'    => now()->toIso8601String(),
                'event' => $event,
                'id'    => (string) ($this->getAttribute('id') ?? ''),
            ], $payload);

            $meta['automatic_edit_history'] = array_values($hist);
            $this->setAttribute('metadata', $meta);
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to push automatic_edit_history', [
                'id'    => $this->getAttribute('id'),
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
        }
    }

    protected function resolveApprovalRejectionWinner(): void
    {
        try {
            $apvBy = $this->getAttribute(PJC::COL_APV_BY);
            $apvAt = $this->toImmutableSafe($this->getAttribute(PJC::COL_APV_AT));
            $rejBy = $this->getAttribute(PJC::COL_REJ_BY);
            $rejAt = $this->toImmutableSafe($this->getAttribute(PJC::COL_REJ_AT));
            $apvByOk = is_string($apvBy) ? trim($apvBy) !== '' : !empty($apvBy);
            $rejByOk = is_string($rejBy) ? trim($rejBy) !== '' : !empty($rejBy);
            $hasApv = $apvByOk || (bool) $apvAt;
            $hasRej = $rejByOk || (bool) $rejAt;
            if (!$hasApv && !$hasRej) return;
            if ($hasApv && !$hasRej) {
                $this->setAttribute(PJC::COL_REJ_BY, null);
                $this->setAttribute(PJC::COL_REJ_AT, null);
                return;
            }
            if ($hasRej && !$hasApv) {
                $this->setAttribute(PJC::COL_APV_BY, null);
                $this->setAttribute(PJC::COL_APV_AT, null);
                return;
            }
            $winner = 'reject';
            switch (true) {
                case $apvAt && $rejAt:
                    $winner = $apvAt->greaterThan($rejAt) ? 'approve' : 'reject';
                    if ($apvAt->equalTo($rejAt)) $winner = 'reject';
                    break;
                case $apvAt && !$rejAt:
                    $winner = 'approve';
                    break;
                case !$apvAt && $rejAt:
                    $winner = 'reject';
                    break;
                default:
                    $winner = 'reject';
            }
            if ($winner === 'reject') {
                $this->setAttribute(PJC::COL_APV_BY, null);
                $this->setAttribute(PJC::COL_APV_AT, null);
                return;
            }
            $this->setAttribute(PJC::COL_REJ_BY, null);
            $this->setAttribute(PJC::COL_REJ_AT, null);
        } catch (\Throwable $e) {
            ErrorHandler::evaluateExistenceToLogChannel(
                'contract_attachment_errors',
                candidate: [
                    'message' => 'failed to resolve approval/rejection winner',
                    'context' => [
                        'id' => $this->getAttribute('id'),
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]
                ]
            );
        }
    }

    protected function toImmutableSafe(mixed $date): ?\Carbon\CarbonImmutable
    {
        try {
            if ($date instanceof \Carbon\CarbonImmutable) return $date;
            if ($date instanceof \Carbon\Carbon) return \Carbon\CarbonImmutable::instance($date);
            if (is_string($date) && trim($date) !== '') return \Carbon\CarbonImmutable::parse($date);
            return null;
        } catch (\Throwable $e) {
            Log::debug(static::class . ' failed to parse date for status inference', [
                'id'    => $this->getAttribute('id'),
                'raw'   => $date,
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
            return null;
        }
    }
}
