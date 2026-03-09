<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{HasAuditFields, NormalizesAddresses, UsesUuids};
use Illuminate\Database\Eloquent\{Casts\Attribute, Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo};
use Illuminate\Support\Facades\{Cache, Log};

class Trainer extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesAddresses;

    public const COL_FIRSTNAME = 'firstname';
    public const COL_LASTNAME  = 'lastname';

    protected $table = DC::TABLE_TRAINERS;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    protected $fillable = [
        UC::COL_USER_ID,
        'branch',
        UC::COL_EMP_ID,
        self::COL_FIRSTNAME,
        self::COL_LASTNAME,
        'contact',
        'email',
        'address',
        'presentation',
        'expertise',
        'registration',
        'qualifications',
        'certificates',
    ];

    protected $casts = [
        'qualifications' => 'array',
        'certificates'   => 'array',
    ];

    protected $with = ['branch', 'employee', 'user', 'registrationDocument'];

    protected $appends = ['full_name', 'is_external', 'certificates_count'];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $m->normalizeCoreFields();
                $m->resolveLegacyIdentityChain($m);
                $m->normalizeCoreFields();
                $m->normalizeJsonFields();
            } catch (\Throwable $e) {
                Log::error(self::class . ' saving failed', [
                    'trainer_id' => $m->getKey(),
                    'error'      => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * Mantém a cadeia de fallback (User como fonte primária, Employee como secundária),
     * com correções mínimas para não violar FKs e respeitar getAttribute/setAttribute.
     */
    protected function resolveLegacyIdentityChain(self $m): void
    {
        try {
            $userId = $m->getAttribute(UC::COL_USER_ID);
            $empId = $m->getAttribute(UC::COL_EMP_ID);
            $fullName = trim((string) $m->getAttribute(self::COL_FIRSTNAME) . ' ' . (string) $m->getAttribute(self::COL_LASTNAME));
            $contact = $m->getAttribute('contact');
            $email = $m->getAttribute('email');

            $trainerAsEmployee = null;
            $trainerAsUser = null;

            if (!empty($empId))
                $trainerAsEmployee = Employee::query()->find($empId);

            if (!$trainerAsEmployee && !empty($userId))
                $trainerAsEmployee = Employee::query()->where(UC::COL_USER_ID, $userId)->first();

            if (!$trainerAsEmployee)
                $trainerAsEmployee = Employee::query()
                    ->where(function ($query) use ($fullName, $contact, $email) {
                        if (!empty($email))
                            $query->orWhere('email', $email);
                        if (!empty($contact))
                            $query->orWhere('phone', $contact);
                        if (!empty($fullName))
                            $query->orWhere('name', $fullName);
                    })
                    ->first();

            if (!empty($userId))
                $trainerAsUser = User::query()->find($userId);

            if (!$trainerAsUser) {
                $accessorName = $m->getAttribute('name');
                $trainerAsUser = User::query()
                    ->where(function ($query) use ($accessorName, $email, $contact) {
                        if (!empty($email))
                            $query->orWhere('email', $email);
                        if (!empty($contact))
                            $query->orWhere('phone', $contact);
                        if (!empty($accessorName))
                            $query->orWhere('name', $accessorName);
                    })
                    ->first();
            }

            if ($trainerAsEmployee && empty($m->getAttribute(UC::COL_EMP_ID)))
                $m->employee()->associate($trainerAsEmployee);

            if ($trainerAsUser) {
                if (empty($m->getAttribute(UC::COL_USER_ID)))
                    $m->user()->associate($trainerAsUser);

                if (!$m->getAttribute(self::COL_FIRSTNAME) || !$m->getAttribute(self::COL_LASTNAME)) {
                    $userName = $trainerAsUser->getAttribute('name') ?? '';
                    $nameParts = explode(' ', trim((string) $userName));
                    $firstName = $nameParts[0] ?? '';
                    $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

                    if (!$m->getAttribute(self::COL_FIRSTNAME) && $firstName !== '')
                        $m->setAttribute(self::COL_FIRSTNAME, $firstName);

                    if (!$m->getAttribute(self::COL_LASTNAME) && $lastName !== '')
                        $m->setAttribute(self::COL_LASTNAME, $lastName);
                }

                foreach (['phone' => 'contact', 'email' => 'email'] as $userCol => $modelCol) {
                    $userVal = $trainerAsUser->getAttribute($userCol) ?? null;
                    $curVal = $m->getAttribute($modelCol) ?? null;

                    if ($userVal !== null && ($curVal === null || $curVal === ''))
                        $m->setAttribute($modelCol, $userVal);
                }
            }

            if ($trainerAsEmployee) {
                if (!$m->getAttribute(self::COL_FIRSTNAME) || !$m->getAttribute(self::COL_LASTNAME)) {
                    $empName = $trainerAsEmployee->getAttribute('name') ?? '';
                    $nameParts = explode(' ', trim((string) $empName));
                    $firstName = $nameParts[0] ?? '';
                    $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

                    if (!$m->getAttribute(self::COL_FIRSTNAME) && $firstName !== '')
                        $m->setAttribute(self::COL_FIRSTNAME, $firstName);

                    if (!$m->getAttribute(self::COL_LASTNAME) && $lastName !== '')
                        $m->setAttribute(self::COL_LASTNAME, $lastName);
                }

                foreach (['phone' => 'contact', 'email' => 'email', 'address' => 'address'] as $empCol => $modelCol) {
                    $empVal = $trainerAsEmployee->getAttribute($empCol) ?? null;
                    $modelVal = $m->getAttribute($modelCol) ?? null;

                    if ($empVal !== null && ($modelVal === null || $modelVal === ''))
                        $m->setAttribute($modelCol, $empVal);
                }
            }
        } catch (\Throwable $e) {
            Log::warning(self::class . ' legacy identity chain failed', [
                'trainer_id' => $m->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }


    protected function normalizeCoreFields(): void
    {
        $ownerId = $this->getKey() ?? '';

        $email = $this->getAttribute('email');
        $isNormalizeEmailCallable = is_callable([self::class, 'normalizeEmail']);
        $isNormalizeEmailCallable && $this->setAttribute('email', static::normalizeEmail(is_string($email) ? $email : null, 'trainer.email', $ownerId));
        $phone = $this->getAttribute('contact');
        $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
        $isNormalizePhoneCallable && $this->setAttribute('contact', static::normalizePhone(is_string($phone) ? $phone : null, 'trainer.contact', $ownerId));

        $first = $this->getAttribute(self::COL_FIRSTNAME);
        if (is_string($first)) $this->setAttribute(self::COL_FIRSTNAME, trim($first));

        $last = $this->getAttribute(self::COL_LASTNAME);
        if (is_string($last)) $this->setAttribute(self::COL_LASTNAME, trim($last));

        $addr = $this->getAttribute('address');
        if (is_string($addr)) $this->setAttribute('address', trim($addr));
    }

    protected function normalizeJsonFields(): void
    {
        $this->setAttribute('qualifications', static::normalizeArrayField($this->getAttribute('qualifications')));
        $this->setAttribute('certificates', static::normalizeArrayField($this->getAttribute('certificates')));
    }

    // Accessor apenas para suportar getAttribute('name') no bloco legado (não precisa estar em $appends)
    public function name(): Attribute
    {
        return Attribute::make(
            get: fn(): string => trim(
                (string) ($this->getAttribute(self::COL_FIRSTNAME) ?? '') . ' ' . (string) ($this->getAttribute(self::COL_LASTNAME) ?? '')
            )
        );
    }

    public function fullName(): Attribute
    {
        return Attribute::make(get: fn(): string => (string) $this->getAttribute('name'));
    }

    public function isExternal(): Attribute
    {
        return Attribute::make(get: function (): bool {
            $u = (string) ($this->getAttribute(UC::COL_USER_ID) ?? '');
            $e = (string) ($this->getAttribute(UC::COL_EMP_ID) ?? '');
            return $u === '' && $e === '';
        });
    }

    public function certificatesCount(): Attribute
    {
        return Attribute::make(get: fn(): int => $this->getCachedCertificatesCount());
    }

    public function getCachedCertificatesCount(int $ttlSeconds = 300): int
    {
        $id = (string) ($this->getKey() ?? '');
        if ($id === '') return 0;

        try {
            return (int) Cache::remember('trainer:' . $id . ':certificates_count', $ttlSeconds, function (): int {
                $list = static::normalizeArrayField($this->getAttribute('certificates'));
                return count($list);
            });
        } catch (\Throwable $e) {
            Log::debug(self::class . ' cache unavailable', ['trainer_id' => (string) ($this->getKey() ?? ''), 'error' => $e->getMessage()]);
            return count(static::normalizeArrayField($this->getAttribute('certificates')));
        }
    }

    // Relations
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID, 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function registrationDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'registration', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_UPDATER, 'id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Branch, $this> */
    public function branches(): BelongsTo
    {
        return $this->branch();
    }
}
