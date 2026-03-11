<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Enums\EvaluationStatus;
use App\Models\{
    Branch,
    Employee,
    User
};
use App\Traits\{
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\{
    DB,
    Log,
    Schema
};
use Illuminate\Support\Carbon;
/**
 * @property \Illuminate\Support\Carbon|string|null $appraisal_date
 * @property string|null $rating
 * @property string|null $remark

 * @property mixed $branch
 * @property mixed $employee
 */

class Appraisal extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use NormalizesArrays;

    protected $table = DC::TABLE_APR;

    private const FILLABLE_FIELDS = [
        // HasRatingColumns
        'company',
        'branch',
        'employee',
        'rating',
        'attendance',
        'administration',
        PJC::COL_CST_EXP, // customer_experience
        'integrity',
        'marketing',
        'professionalism',

        // Appraisals
        PJC::COL_APR_DT,  // appraisal_date (string por enquanto)
        'status',
        'remark',
        'appraiser',
        'acknowledgers',
        'metadata',
    ];

    protected $fillable = self::FILLABLE_FIELDS;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'id'              => 'string',
        'company'         => 'string',
        'branch'          => 'string',
        'employee'        => 'string',
        'appraiser'       => 'string',

        'rating'          => 'string',
        'attendance'      => 'int',
        'administration'  => 'int',
        PJC::COL_CST_EXP  => 'int',
        'integrity'       => 'int',
        'marketing'       => 'int',
        'professionalism' => 'int',

        PJC::COL_APR_DT   => 'string',
        'status'          => EvaluationStatus::class,
        'remark'          => 'string',

        'acknowledgers'   => 'array',
        'metadata'        => 'array',

        DC::COL_C_AT      => 'datetime',
        DC::COL_U_AT      => 'datetime',
    ];

    protected $appends = [
        'appraisal_date_as_date',
        'acknowledgers_count',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            foreach (
                [
                    'attendance',
                    'administration',
                    PJC::COL_CST_EXP,
                    'integrity',
                    'marketing',
                    'professionalism',
                ] as $field
            ) {
                $value = $model->getAttribute($field);
                $int   = (int) ($value ?? 0);
                if ($int < 0)
                    $int = 0;
                elseif ($int > 10)
                    $int = 10;
                $model->setAttribute($field, $int);
            }
            $rating = $model->getAttribute('rating');
            if ($rating !== null)
                $model->setAttribute('rating', trim((string) $rating));
            $date = $model->getAttribute(PJC::COL_APR_DT);
            if ($date !== null) {
                $model->setAttribute(PJC::COL_APR_DT, trim((string) $date));
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $model->getAttribute(PJC::COL_APR_DT)))
                    $model->setAttribute(PJC::COL_APR_DT, null);
            }
            $acks = $model->getAttribute('acknowledgers');
            $model->setAttribute('acknowledgers', self::normalizeAcknowledgers($acks));
            $metadata = $model->getAttribute('metadata');
            if (!is_array($metadata)) {
                $metadata = [];
            }
            $model->setAttribute('metadata', $metadata);

            // Garante codificação JSON correta
            try {
                $model->ensureJsonAttributesAreEncoded(['acknowledgers', 'metadata']);
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed to encode JSON attributes', [
                    'id'    => $model->getAttribute('id'),
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * Normaliza a estrutura de acknowledgers.
     *
     * Regra geral:
     * - Sempre devolve array de itens padronizados:
     *   [
     *     'user_id'        => string|null,
     *     'employee_id'    => string|null,
     *     'name'           => string|null,
     *     'acknowledged_at'=> string|null (Y-m-d H:i:s),
     *     'step'           => string|null,
     *   ]
     * - Filtra entradas que não conseguimos associar a User/Employee válidos.
     */
    private static function normalizeAcknowledgers(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];

        foreach ($value as $item) {
            $normalized = self::normalizeSingleAcknowledger($item);

            if ($normalized !== null) {
                $result[] = $normalized;
            }
        }

        // Remove duplicados por (user_id, step)
        $unique = [];
        $seen   = [];

        foreach ($result as $row) {
            $key = ($row['user_id'] ?? '') . '|' . ($row['step'] ?? '');
            if ($key === '|') {
                $unique[] = $row;
                continue;
            }
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[]   = $row;
        }

        return $unique;
    }

    private static function normalizeSingleAcknowledger(mixed $item): ?array
    {
        try {
            $userId     = null;
            $employeeId = null;
            $name       = null;
            $step       = null;
            $ackAt      = null;

            // Formatos aceitos:
            // - string/int (id ou nome)
            // - array associativo

            if (is_string($item) || is_int($item)) {
                $candidate = trim((string) $item);

                if ($candidate === '') {
                    return null;
                }

                [$userId, $employeeId, $name] = self::resolveUserOrEmployee($candidate, null);
            } elseif (is_array($item)) {
                $candidateId   = isset($item['id']) ? (string) $item['id'] : null;
                $candidateUser = isset($item['user_id']) ? (string) $item['user_id'] : null;
                $candidateEmp  = isset($item['employee_id']) ? (string) $item['employee_id'] : null;
                $candidateName = isset($item['name']) ? (string) $item['name'] : null;

                $step = isset($item['step']) ? trim((string) $item['step']) : null;

                $rawAck = $item['acknowledged_at'] ?? ($item['ack_at'] ?? null);
                if ($rawAck !== null) {
                    $ackAt = self::normalizeDateTimeString((string) $rawAck);
                }

                // Tentativa de resolução por ordem de prioridade
                foreach ([$candidateUser, $candidateId, $candidateEmp, $candidateName] as $candidate) {
                    if ($candidate === null || trim($candidate) === '') {
                        continue;
                    }

                    [$userId, $employeeId, $nameResolved] = self::resolveUserOrEmployee($candidate, $candidateName);

                    if ($userId !== null || $employeeId !== null) {
                        $name = $nameResolved;
                        break;
                    }
                }
            } else {
                return null;
            }

            if ($userId === null && $employeeId === null) {
                // Sem vínculo claro com User/Employee, descartamos
                return null;
            }

            return [
                'user_id'         => $userId,
                'employee_id'     => $employeeId,
                'name'            => $name,
                'acknowledged_at' => $ackAt,
                'step'            => $step,
            ];
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to normalize acknowledger entry', [
                'entry' => $item,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Resolve um identificador (id ou nome) para User/Employee.
     *
     * Retorna [user_id|null, employee_id|null, name|null]
     */
    private static function resolveUserOrEmployee(string $candidate, ?string $fallbackName = null): array
    {
        $candidate = trim($candidate);
        if ($candidate === '') {
            return [null, null, null];
        }

        $userId     = null;
        $employeeId = null;
        $name       = $fallbackName;

        // 1. Se for UUID, tenta consultar com users.id ou employees.id/user_id
        if (Utility::looksLikeUuid($candidate)) {
            try {
                if (Schema::hasTable(DC::TABLE_USERS)) {
                    $user = DB::table(DC::TABLE_USERS)
                        ->select('id', 'name')
                        ->where('id', $candidate)
                        ->first();

                    if ($user) {
                        $userId = (string) $user->id;
                        $name   = $name ?? (isset($user->name) ? (string) $user->name : null);

                        // tenta achar employee ligado a este user_id
                        if (Schema::hasTable(DC::TABLE_EMPLOYEES)) {
                            $emp = DB::table(DC::TABLE_EMPLOYEES)
                                ->select('id')
                                ->where('user_id', $userId)
                                ->first();

                            if ($emp) {
                                $employeeId = (string) $emp->id;
                            }
                        }

                        return [$userId, $employeeId, $name];
                    }
                }

                if (Schema::hasTable(DC::TABLE_EMPLOYEES)) {
                    $emp = DB::table(DC::TABLE_EMPLOYEES)
                        ->select('id')
                        ->where('id', $candidate)
                        ->first();

                    if ($emp) {
                        $employeeId = (string) $emp->id;

                        // tenta descobrir user associado
                        if (Schema::hasTable(DC::TABLE_USERS)) {
                            $empUser = DB::table(DC::TABLE_EMPLOYEES)
                                ->select('user_id')
                                ->where('id', $employeeId)
                                ->first();

                            if ($empUser && $empUser->user_id) {
                                $userId = (string) $empUser->user_id;
                            }
                        }

                        return [$userId, $employeeId, $name];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed to resolve UUID candidate for acknowledger', [
                    'candidate' => $candidate,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        // 2. Se não é UUID, tratamos como nome e tentamos localizar em users/ employees
        try {
            if (Schema::hasTable(DC::TABLE_USERS)) {
                $user = DB::table(DC::TABLE_USERS)
                    ->select('id', 'name')
                    ->where('name', $candidate)
                    ->first();

                if ($user) {
                    $userId = (string) $user->id;
                    $name   = (string) $user->name;

                    if (Schema::hasTable(DC::TABLE_EMPLOYEES)) {
                        $emp = DB::table(DC::TABLE_EMPLOYEES)
                            ->select('id')
                            ->where('user_id', $userId)
                            ->first();

                        if ($emp) {
                            $employeeId = (string) $emp->id;
                        }
                    }

                    return [$userId, $employeeId, $name];
                }
            }

            if (Schema::hasTable(DC::TABLE_EMPLOYEES)) {
                $emp = DB::table(DC::TABLE_EMPLOYEES)
                    ->select('id', 'name')
                    ->where('name', $candidate)
                    ->first();

                if ($emp) {
                    $employeeId = (string) $emp->id;
                    $name       = isset($emp->name) ? (string) $emp->name : $candidate;

                    // tenta achar o user vinculado
                    if (Schema::hasTable(DC::TABLE_EMPLOYEES)) {
                        $empUser = DB::table(DC::TABLE_EMPLOYEES)
                            ->select('user_id')
                            ->where('id', $employeeId)
                            ->first();

                        if ($empUser && $empUser->user_id && Schema::hasTable(DC::TABLE_USERS)) {
                            $userId = (string) $empUser->user_id;
                        }
                    }

                    return [$userId, $employeeId, $name];
                }
            }
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to resolve name candidate for acknowledger', [
                'candidate' => $candidate,
                'error'     => $e->getMessage(),
            ]);
        }

        return [null, null, $name];
    }

    private static function normalizeDateTimeString(string $value): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        try {
            return Carbon::parse($trimmed)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getAppraisalDateAsDateAttribute(): ?Carbon
    {
        $raw = $this->getAttribute(PJC::COL_APR_DT);

        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }

        try {
            return Carbon::parse($raw);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getAcknowledgersCountAttribute(): int
    {
        $acks = $this->getAttribute('acknowledgers');

        return is_array($acks) ? count($acks) : 0;
    }

    public function branch(): BelongsTo
    {
        // coluna "branch" (BranchConnected com prefixed:false)
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee', 'id');
    }

    public function company(): BelongsTo
    {
        // Usuário do tipo company; FK em "company" -> users.id
        return $this->belongsTo(User::class, 'company', 'id');
    }

    public function appraiserUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appraiser', 'id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Branch, $this> */
    public function branches(): BelongsTo
    {
        return $this->branch();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Employee, $this> */
    public function employees(): BelongsTo
    {
        return $this->employee();
    }
}
