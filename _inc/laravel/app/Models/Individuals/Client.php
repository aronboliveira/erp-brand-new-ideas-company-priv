<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{HasAuditFields, NormalizesAddresses, UsesUuids};
use Illuminate\Database\Eloquent\{Casts\Attribute, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo};
use Illuminate\Support\Facades\{Log};

class Client extends Model
{
	use UsesUuids, HasAuditFields, NormalizesAddresses;

	protected $table = DC::TABLE_CLIENTS;

	protected $guarded = ['id', DC::COL_TABLE_CREATOR];

	protected $fillable = [
		UC::COL_NM,
		UC::COL_EM,
		UC::COL_EM_V_AT,
		UC::COL_PW,
		UC::COL_LG,
		UC::COL_IA,
		UC::COL_USER_ID,
		UC::COL_TEL,
		UC::COL_ADR,
		UC::COL_IU,
		UC::COL_AV,
		UC::COL_MSG_CL,
		UC::COL_DEL_STT,
		DC::COL_TABLE_UPDATER,
	];

	protected $casts = [
		UC::COL_PW => 'hashed',
		UC::COL_EM_V_AT => 'datetime',
		UC::COL_IA => 'integer',
		UC::COL_IU => 'boolean',
		UC::COL_DEL_STT => 'integer',
	];

	protected $hidden = [UC::COL_PW];

	protected static function booted(): void
	{
		static::saving(function (self $m): void {
			try {
				$m->ensureDefaults();
				$m->normalizeCoreFields();
				$m->enforceClientUserType();
			} catch (\Throwable $e) {
				Log::error(self::class . ' saving failed', [
					'client_id' => (string) ($m->getKey() ?? ''),
					'error' => $e->getMessage(),
				]);
				throw $e;
			}
		});
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
	}

	protected function email(): Attribute
	{
		return Attribute::make(
			get: fn(mixed $value): ?string => static::normalizeEmail(is_string($value) ? $value : null, 'client.email', (string) ($this->getKey() ?? '')),
			set: fn(mixed $value): ?string => static::normalizeEmail(is_string($value) ? $value : null, 'client.email', (string) ($this->getKey() ?? '')),
		);
	}

	protected function phone(): Attribute
	{
		return Attribute::make(
			get: fn(mixed $value): ?string => static::normalizePhone(is_string($value) ? $value : null, 'client.phone', (string) ($this->getKey() ?? ''), false),
			set: fn(mixed $value): ?string => static::normalizePhone(is_string($value) ? $value : null, 'client.phone', (string) ($this->getKey() ?? ''), false),
		);
	}


	private function ensureDefaults(): void
	{
		$lang = trim((string) ($this->getAttribute(UC::COL_LG) ?? ''));
		if ($lang === '') $this->setAttribute(UC::COL_LG, DC::DEFAULT_LANG);

		$ia = $this->getAttribute(UC::COL_IA);
		if (!is_numeric($ia)) $this->setAttribute(UC::COL_IA, 1);

		$del = $this->getAttribute(UC::COL_DEL_STT);
		if (!is_numeric($del)) $this->setAttribute(UC::COL_DEL_STT, 1);

		$av = trim((string) ($this->getAttribute(UC::COL_AV) ?? ''));
		if ($av === '') {
			try {
				$this->setAttribute(UC::COL_AV, (string) config('chatify.user_avatar.default'));
			} catch (\Throwable) {
				$this->setAttribute(UC::COL_AV, '');
			}
		}

		$msg = trim((string) ($this->getAttribute(UC::COL_MSG_CL) ?? ''));
		if ($msg === '') $this->setAttribute(UC::COL_MSG_CL, '#2180f3');
	}

	private function normalizeCoreFields(): void
	{
		$ownerId = (string) ($this->getKey() ?? '');

		$name = $this->getAttribute(UC::COL_NM);
		if (is_string($name)) {
			$t = trim($name);
			$this->setAttribute(UC::COL_NM, $t === '' ? null : mb_substr($t, 0, 254));
		}

		// email / phone via NormalizesAddresses (também reforçado pelos Attribute casts acima)
		$email = $this->getAttribute(UC::COL_EM);
		$this->setAttribute(UC::COL_EM, static::normalizeEmail(is_string($email) ? $email : null, 'client.email', $ownerId));

		$tel = $this->getAttribute(UC::COL_TEL);
		$this->setAttribute(UC::COL_TEL, static::normalizePhone(is_string($tel) ? $tel : null, 'client.phone', $ownerId, false));

		$adr = $this->getAttribute(UC::COL_ADR);
		if (is_string($adr)) {
			$t = trim($adr);
			$this->setAttribute(UC::COL_ADR, $t === '' ? null : $t);
		}
	}

	/**
	 * Se user_id não for nulo, deve apontar para users.type === 'client'.
	 * Caso não seja, o vínculo é invalidado (null) e logado.
	 */
	private function enforceClientUserType(): void
	{
		$uid = trim((string) ($this->getAttribute(UC::COL_USER_ID) ?? ''));
		if ($uid === '') {
			$this->setAttribute(UC::COL_USER_ID, null);
			return;
		}

		try {
			$u = User::query()->select(['id', 'type'])->where('id', $uid)->first();

			if (!$u) {
				Log::warning(self::class . ' user_id not found, nulling', [
					'client_id' => (string) ($this->getKey() ?? ''),
					'user_id' => $uid,
				]);
				$this->setAttribute(UC::COL_USER_ID, null);
				return;
			}

			$type = strtolower(trim((string) ($u->getAttribute('type') ?? '')));
			if ($type !== 'client') {
				Log::warning(self::class . ' user_id is not client type, nulling', [
					'client_id' => (string) ($this->getKey() ?? ''),
					'user_id' => $uid,
					'user_type' => $type,
				]);
				$this->setAttribute(UC::COL_USER_ID, null);
			}
		} catch (\Throwable $e) {
			Log::error(self::class . ' failed enforcing user type', [
				'client_id' => (string) ($this->getKey() ?? ''),
				'user_id' => $uid,
				'error' => $e->getMessage(),
			]);
		}
	}
}
