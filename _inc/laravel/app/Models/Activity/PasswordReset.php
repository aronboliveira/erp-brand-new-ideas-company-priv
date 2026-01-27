<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\UserType;
use App\Traits\{HasAuditFields, NormalizesAddresses};
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

final class PasswordReset extends Model
{
	use HasAuditFields, NormalizesAddresses;

	protected $table = DC::TABLE_PW_RST;

	/** Primary key is the token (string) */
	protected $primaryKey = 'token';
	public $incrementing = false;
	protected $keyType = 'string';

	protected $fillable = [
		'token',
		'email',
		PJC::COL_SBM_AT,
		DC::COL_EXP_DT,
		'source',
		'attempts',
		'ip',
		DC::COL_TABLE_CREATOR,
		DC::COL_TABLE_UPDATER,
	];

	protected $casts = [
		PJC::COL_SBM_AT => 'datetime',
		DC::COL_EXP_DT  => 'datetime',
		'attempts'      => 'integer',
	];

	protected static function booted(): void
	{
		static::saving(function (self $m): void {
			self::normalizeEmailAttribute($m, 'email');
			$m->normalizeSource();
			$m->normalizeAttempts();
			$user = $m->loadUserByEmailOrFail();
			$m->ensureSubmittedAtNotLowerThanUser($user);
			$m->ensureExpiresAtNotLowerThanUserPlus15($user);
			$m->enforceAttemptsLimit($user);
			if (!$m->exists)
				$m->enforceRollingRateLimits($user);
		});
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'email', UC::COL_EM);
	}

	private function normalizeSource(): void
	{
		$raw = $this->getAttribute('source');
		$src = is_scalar($raw) ? trim(mb_strtolower((string) $raw)) : '';
		$this->setAttribute('source', $src !== '' ? $src : null);
	}

	private function normalizeAttempts(): void
	{
		$raw = $this->getAttribute('attempts');
		$n = is_numeric($raw) ? (int) $raw : 0;
		if ($n < 0) $n = 0;
		if ($n > 255) $n = 255;
		$this->setAttribute('attempts', $n);
	}

	private function loadUserByEmailOrFail(): User
	{
		/** @var User|null $u */
		$u = User::query()
			->where(UC::COL_EM, $this->getAttribute('email'))
			->first([UC::COL_EM, UC::COL_U_TP, 'created_at']);
		if (!$u) throw new \InvalidArgumentException(static::class . ' email not found in users');
		return $u;
	}

	private function isSuperAdmin(User $u): bool
	{
		$ut = (string) ($u->getAttribute(UC::COL_U_TP) ?? '');
		return $ut === UserType::SuperAdmin->value;
	}

	private function sbmAtImmutable(): CarbonImmutable
	{
		$raw = $this->getAttribute(PJC::COL_SBM_AT);
		if ($raw instanceof \DateTimeInterface) return CarbonImmutable::instance($raw);
		$fallback = $this->getAttribute('updated_at') ?? $this->getAttribute('created_at');
		if ($fallback instanceof \DateTimeInterface) return CarbonImmutable::instance($fallback);
		return CarbonImmutable::now();
	}

	private function ensureSubmittedAtNotLowerThanUser(User $u): void
	{
		$sbmAt = $this->sbmAtImmutable();
		$uCreated = $u->getAttribute('created_at');
		if ($uCreated instanceof \DateTimeInterface) {
			$min = CarbonImmutable::instance($uCreated);
			if ($sbmAt->lessThan($min)) $sbmAt = $min;
		}
		$this->setAttribute(PJC::COL_SBM_AT, $sbmAt);
	}

	private function ensureExpiresAtNotLowerThanUserPlus15(User $u): void
	{
		$sbmAt = $this->sbmAtImmutable();
		$rawExp = $this->getAttribute(DC::COL_EXP_DT);
		$exp = $rawExp instanceof \DateTimeInterface
			? CarbonImmutable::instance($rawExp)
			: $sbmAt->addMinutes(15);
		$uCreated = $u->getAttribute('created_at');
		if ($uCreated instanceof \DateTimeInterface) {
			$min = CarbonImmutable::instance($uCreated)->addMinutes(15);
			if ($exp->lessThan($min)) $exp = $min;
		}
		$minFromSbm = $sbmAt->addMinutes(15);
		if ($exp->lessThan($minFromSbm)) $exp = $minFromSbm;
		$this->setAttribute(DC::COL_EXP_DT, $exp);
	}

	private function enforceAttemptsLimit(User $u): void
	{
		if ($this->isSuperAdmin($u)) return;
		$attempts = (int) ($this->getAttribute('attempts') ?? 0);
		if ($attempts > 16)
			throw new \RuntimeException(static::class . ' attempts exceeded max(16)');
	}

	private function enforceRollingRateLimits(User $u): void
	{
		if ($this->isSuperAdmin($u)) return;
		$email = (string) $this->getAttribute('email');
		$sbmAt = $this->sbmAtImmutable();
		$hourStart = $sbmAt->subHour();
		$dayStart  = $sbmAt->subDay();
		$qBase = DB::table($this->getTable())->where('email', $email);
		$hCount = (int) (clone $qBase)
			->where(PJC::COL_SBM_AT, '>=', $hourStart)
			->where(PJC::COL_SBM_AT, '<=', $sbmAt)
			->count();
		if ($hCount >= 2)
			throw new \RuntimeException(static::class . ' rate-limit: >2 requests within 1 hour');
		$dCount = (int) (clone $qBase)
			->where(PJC::COL_SBM_AT, '>=', $dayStart)
			->where(PJC::COL_SBM_AT, '<=', $sbmAt)
			->count();
		if ($dCount >= 5) {
			throw new \RuntimeException(static::class . ' rate-limit: >5 requests within 24 hours');
		}
	}
}
