<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	EmailsConstants as EC,
	MessagesConstants as MC
};
use App\Enums\AppModuleType;
use App\Models\Email;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class EmailSeeder extends Seeder
{
	private const FROM_FRACTION = 0.10;
	private const TO_FRACTION   = 0.10;

	// provider minimum shares (sobre o TOTAL a criar)
	private const PROVIDER_MIN = [
		'gmail'     => 0.05,
		'outlook'   => 0.025,
		'protonmail' => 0.01,
		'zimbra'    => 0.01,
		'nextcloud' => 0.01,
	];

	private const PROVIDER_POOL_EXTRA = [
		'smtp',
		'imap',
		'microsoft365',
		'office365',
		'sendgrid',
		'mailgun',
		'amazon_ses',
		'custom',
		'gmail',
		'outlook',
		'protonmail',
		'zimbra',
		'nextcloud',
	];

	private const MAX_ATTEMPTS_UNIQUE = 128;
	private const MAX_ATTEMPTS_PICK   = 64;

	public function run(): void
	{
		$out = $this->out();

		if (!Schema::hasTable(DC::TABLE_EMAILS) || !Schema::hasTable(DC::TABLE_USERS)) {
			$out->writeln('<comment>[EmailSeeder]</comment> Missing required tables. Aborting.');
			return;
		}

		$eligibleUsers = $this->fetchUsersWithEmailRaw();
		if (count($eligibleUsers) < 1) {
			$out->writeln('<comment>[EmailSeeder]</comment> No users with email found. Aborting.');
			return;
		}

		$fromUsers = $this->pickFractionRows($eligibleUsers, self::FROM_FRACTION);
		$toUsers   = $this->pickFractionRows($eligibleUsers, self::TO_FRACTION);

		if (count($fromUsers) < 1 || count($toUsers) < 1) {
			$out->writeln('<comment>[EmailSeeder]</comment> from/to sampling resulted in empty sets. Aborting.');
			return;
		}

		// 2..16 por AppModuleType (ajustado para múltiplo de 64 sem ultrapassar 2..16 por tipo)
		$types = AppModuleType::cases();
		[$countsByType, $total] = $this->buildCountsMultipleOf64($types);

		// override opcional --count (regra do projeto)
		$cliCount = $this->readCountOptionOrNull();
		if ($cliCount !== null && $cliCount > 0) {
			[$countsByType, $total] = $this->buildCountsMultipleOf64($types, $cliCount);
		}

		if ($total < 64) {
			// força mínimo 64 (continua respeitando 2..16 por tipo)
			[$countsByType, $total] = $this->buildCountsMultipleOf64($types, 64);
		}

		$providerPlan = $this->buildProviderPlan($total);

		$docIds = Schema::hasTable(DC::TABLE_DOCS) ? $this->fetchDocIdsRaw() : [];
		$createdIds = [];

		$out->writeln(sprintf(
			'<info>[EmailSeeder]</info> eligible_users=%d from_pool=%d to_pool=%d total=%d',
			count($eligibleUsers),
			count($fromUsers),
			count($toUsers),
			$total
		));

		$out->writeln('<info>[EmailSeeder]</info> provider plan: ' . json_encode($this->summarizeProviders($providerPlan)));

		$created = 0;

		foreach ($types as $t) {
			$k = $t->value;
			$n = (int) ($countsByType[$k] ?? 0);
			if ($n < 1) continue;

			for ($i = 0; $i < $n; $i++) {
				$provider = $providerPlan[$created] ?? $this->randomProvider();
				$pair = $this->pickFromToPair($fromUsers, $toUsers);

				$fromId = $pair['from_id'];
				$toId   = $pair['to_id'];
				$fromEm = $pair['from_email'];
				$toEm   = $pair['to_email'];

				$isDraft = $this->chance(0.06);
				$isSpam  = !$isDraft && $this->chance(0.02);
				$isTrs   = !$isDraft && !$isSpam && $this->chance(0.03);
				$isArc   = !$isDraft && !$isSpam && !$isTrs && $this->chance(0.06);
				$isFav   = !$isDraft && !$isSpam && $this->chance(0.04);
				$isReply = !$isDraft && $this->chance(0.12);

				$sentAt = $isDraft ? null : $this->randomPastDateTime(240);
				$isRead = !$isDraft && $this->chance(0.55);

				$readAt = null;
				if ($isRead && $sentAt) {
					$readAt = (clone $sentAt)->addMinutes(random_int(1, 60 * 48));
					$now = Carbon::now('America/Sao_Paulo');
					if ($readAt->isFuture()) $readAt = $now;
					if ($readAt->lt($sentAt)) $readAt = $sentAt;
				}

				$unique = $this->generateUniqueIdentifier();

				$title = $this->makeSubjectForModule($t);
				$plain = $this->makeBodyPlain($t, $provider);
				$html  = $this->chance(0.65) ? $this->makeBodyHtml($plain) : null;

				$thread = $this->chance(0.15) ? $this->pickThreadIds($createdIds) : null;

				$cc = $this->chance(0.18) ? $this->pickEmailsList($eligibleUsers, random_int(1, 3), [$toEm, $fromEm]) : null;
				$bcc = $this->chance(0.08) ? $this->pickEmailsList($eligibleUsers, 1, [$toEm, $fromEm]) : null;

				$docId = (!empty($docIds) && $this->chance(0.12))
					? $docIds[random_int(0, count($docIds) - 1)]
					: null;

				$headers = $this->chance(0.55) ? [
					'message_id' => (string) Str::uuid(),
					'mime_version' => '1.0',
					'content_type' => $html ? 'text/html; charset=UTF-8' : 'text/plain; charset=UTF-8',
					'provider' => $provider,
				] : null;

				$attachments = $this->chance(0.20) ? [
					[
						'name' => $this->chance(0.5) ? 'invoice.pdf' : 'document.txt',
						'mime' => $this->chance(0.5) ? 'application/pdf' : 'text/plain',
						'size' => random_int(8_000, 2_500_000),
					]
				] : null;

				$mwScan = $this->chance(0.30) ? [
					'status' => $this->chance(0.97) ? 'clean' : 'suspicious',
					'engine' => 'clamav',
					'scanned_at' => Carbon::now('America/Sao_Paulo')->subMinutes(random_int(1, 60 * 24))->toIso8601String(),
				] : null;

				$metadata = $this->chance(0.35) ? [
					'ip' => '10.' . random_int(0, 255) . '.' . random_int(0, 255) . '.' . random_int(1, 254),
					'agent' => $this->chance(0.5) ? 'web' : 'worker',
					'module' => $t->value,
				] : null;

				try {
					$m = Email::create([
						EC::COL_TT        => $title,
						'provider'        => $provider,
						'description'     => $this->chance(0.35) ? 'Automated message' : null,
						'body'            => $plain,
						'html'            => $html,
						'notes'           => $this->chance(0.15) ? 'Seeded for testing.' : null,

						EC::COL_FROM      => $fromEm,
						EC::COL_FROM_ID   => $fromId,
						'to'              => $toEm,
						EC::COL_TO_ID     => $toId,

						AC::COL_IS_RPL    => $isReply,
						'thread'          => $thread,
						'cc'              => $cc,
						'bcc'             => $bcc,

						AC::COL_IS_FV     => $isFav,
						MC::COL_IS_DFT    => $isDraft,
						MC::COL_IS_TRS    => $isTrs,
						MC::COL_IS_ARC    => $isArc,
						MC::COL_IS_SPAM   => $isSpam,

						DC::COL_MW_FREE   => $this->chance(0.92),
						MC::COL_SNT_AT    => $sentAt,
						MC::COL_IS_RD     => $isRead,
						MC::COL_RD_AT     => $readAt,

						EC::COL_D_URL     => $this->chance(0.10) ? ('https://example.test/doc/' . Str::uuid()) : null,
						DC::COL_DOC_ID    => $docId,
						EC::COL_EM_KEY    => $unique,

						AC::COL_MT        => $t->value,
						AC::COL_MI        => null,

						'counter'         => $isDraft ? 0 : random_int(0, 24),
						'headers'         => $headers,
						'attachments'     => $attachments,
						'templates'       => $this->chance(0.10) ? [(string) Str::uuid()] : null,
						'variables'       => $this->chance(0.08) ? ['customer_name' => 'string', 'amount' => 'decimal'] : null,
						'settings'        => $this->chance(0.12) ? ['priority' => $this->chance(0.2) ? 'high' : 'normal'] : null,
						DC::COL_MW_SCAN   => $mwScan,
						'metadata'        => $metadata,

						DC::COL_TABLE_UPDATER => null,
					]);

					$created++;
					$createdIds[] = (string) $m->getAttribute('id');

					if ($created % 64 === 0) {
						$out->writeln(sprintf('<info>[EmailSeeder]</info> created=%d', $created));
					}
				} catch (\Throwable $e) {
					Log::debug('[EmailSeeder] create failed', [
						'module_type' => $t->value,
						'provider' => $provider,
						'error' => $e->getMessage(),
					]);
				}
			}
		}

		$out->writeln(sprintf('<info>[EmailSeeder]</info> done created=%d', $created));
	}

	private function out(): OutputInterface
	{
		try {
			if ($this->command) return $this->command->getOutput();
		} catch (\Throwable) {
		}
		return new ConsoleOutput();
	}

	private function readCountOptionOrNull(): ?int
	{
		try {
			if (
				$this->command instanceof \Illuminate\Console\Command &&
				$this->command->hasOption('count')
			) {
				$v = $this->command->option('count');
				if (is_numeric($v)) return (int) $v;
			}
		} catch (\Throwable) {
		}
		return null;
	}

	private function fetchUsersWithEmailRaw(): array
	{
		$rows = DB::select(
			"select id, email from " . DC::TABLE_USERS . " where email is not null and email != ''"
		);

		$out = [];
		foreach ($rows as $r) {
			$a = (array) $r;
			$id = is_string($a['id'] ?? null) ? trim((string) $a['id']) : '';
			$em = is_string($a['email'] ?? null) ? trim((string) $a['email']) : '';

			if ($id === '') continue;

			// Validate email and generate random one if invalid
			if ($em === '' || !preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $em)) {
				$newEmail = $this->generateRandomEmail();

				// Try to update the user row with the new email
				try {
					$updated = DB::table(DC::TABLE_USERS)
						->where('id', $id)
						->whereNotNull('id')
						->update(['email' => $newEmail]);

					if ($updated) {
						$em = $newEmail;
						Log::debug('[EmailSeeder] Updated user email', [
							'user_id' => $id,
							'new_email' => $newEmail,
						]);
					}
				} catch (\Throwable $e) {
					Log::debug('[EmailSeeder] Failed to update user email', [
						'user_id' => $id,
						'error' => $e->getMessage(),
					]);
				}

				// Use the new email regardless of update success
				$em = $newEmail;
			}

			$out[] = ['id' => $id, 'email' => $em];
		}
		return $out;
	}

	private function fetchDocIdsRaw(): array
	{
		$rows = DB::select("select id from " . DC::TABLE_DOCS);
		$out = [];
		foreach ($rows as $r) {
			$a = (array) $r;
			$id = is_string($a['id'] ?? null) ? trim((string) $a['id']) : '';
			if ($id !== '') $out[] = $id;
		}
		return $out;
	}

	private function pickFractionRows(array $rows, float $fraction): array
	{
		$fraction = max(0.0, min(1.0, $fraction));
		$n = (int) floor(count($rows) * $fraction);
		$n = max(1, $n);

		$tmp = $rows;
		shuffle($tmp);

		return array_slice($tmp, 0, min($n, count($tmp)));
	}

	/**
	 * Gera contagens 2..16 por módulo e ajusta o total para múltiplo de 64,
	 * sem ultrapassar os limites por módulo.
	 * Se $desiredTotal for informado, tenta aproximar (múltiplo de 64) respeitando limites.
	 *
	 * @return array{0: array<string,int>, 1:int}
	 */
	private function buildCountsMultipleOf64(array $types, ?int $desiredTotal = null): array
	{
		$minPer = 2;
		$maxPer = 16;

		$minTotal = $minPer * count($types);
		$maxTotal = $maxPer * count($types);

		$counts = [];
		$raw = 0;

		foreach ($types as $t) {
			$n = random_int($minPer, $maxPer);
			$counts[$t->value] = $n;
			$raw += $n;
		}

		$target = $raw;

		// define alvo (múltiplo de 64) viável
		if ($desiredTotal !== null) {
			$target = $this->nearestFeasibleMultipleOf64($desiredTotal, $minTotal, $maxTotal);
		} else {
			$target = $this->nearestFeasibleMultipleOf64($raw, $minTotal, $maxTotal);
		}

		// ajusta distribuindo delta
		$delta = $target - $raw;

		if ($delta > 0) {
			$tries = 0;
			while ($delta > 0 && $tries++ < 100_000) {
				$k = $types[random_int(0, count($types) - 1)]->value;
				if ($counts[$k] < $maxPer) {
					$counts[$k]++;
					$delta--;
				}
				if ($tries % 4096 === 0) break;
			}
		} elseif ($delta < 0) {
			$delta = abs($delta);
			$tries = 0;
			while ($delta > 0 && $tries++ < 100_000) {
				$k = $types[random_int(0, count($types) - 1)]->value;
				if ($counts[$k] > $minPer) {
					$counts[$k]--;
					$delta--;
				}
				if ($tries % 4096 === 0) break;
			}
		}

		$sum = 0;
		foreach ($counts as $v) $sum += (int) $v;

		// fallback defensivo: se algo saiu do alvo, força para múltiplo de 64 viável mais próximo.
		if ($sum % 64 !== 0) {
			$sumTarget = $this->nearestFeasibleMultipleOf64($sum, $minTotal, $maxTotal);
			$diff = $sumTarget - $sum;

			$tries = 0;
			while ($diff !== 0 && $tries++ < 100_000) {
				$k = $types[random_int(0, count($types) - 1)]->value;

				if ($diff > 0 && $counts[$k] < $maxPer) {
					$counts[$k]++;
					$diff--;
				} elseif ($diff < 0 && $counts[$k] > $minPer) {
					$counts[$k]--;
					$diff++;
				}

				if ($tries % 4096 === 0) break;
			}

			$sum = 0;
			foreach ($counts as $v) $sum += (int) $v;
		}

		return [$counts, $sum];
	}

	private function nearestFeasibleMultipleOf64(int $value, int $minTotal, int $maxTotal): int
	{
		$value = max($minTotal, min($maxTotal, $value));

		$candidates = [];
		for ($m = 64; $m <= $maxTotal; $m += 64) {
			if ($m >= $minTotal && $m <= $maxTotal) $candidates[] = $m;
		}

		if (!$candidates) return max($minTotal, min($maxTotal, $value));

		$best = $candidates[0];
		$bestDist = abs($best - $value);

		foreach ($candidates as $c) {
			$d = abs($c - $value);
			if ($d < $bestDist) {
				$best = $c;
				$bestDist = $d;
			} elseif ($d === $bestDist && $c > $best) {
				// empate: prefere maior
				$best = $c;
			}
		}

		return $best;
	}

	/** garante mínimos por provider e completa o resto */
	private function buildProviderPlan(int $total): array
	{
		$plan = [];

		$minCounts = [];
		$sumMin = 0;

		foreach (self::PROVIDER_MIN as $prov => $ratio) {
			$n = (int) ceil($total * $ratio);
			$n = max(1, $n);
			$minCounts[$prov] = $n;
			$sumMin += $n;
		}

		// se por algum motivo os mínimos estourarem, reduz proporcionalmente (defensivo)
		if ($sumMin > $total) {
			foreach ($minCounts as $prov => $n) {
				$minCounts[$prov] = max(0, (int) floor($n * ($total / $sumMin)));
			}
		}

		foreach ($minCounts as $prov => $n) {
			for ($i = 0; $i < $n; $i++) $plan[] = $prov;
		}

		while (count($plan) < $total) {
			$plan[] = $this->randomProvider();
		}

		shuffle($plan);
		return array_slice($plan, 0, $total);
	}

	private function summarizeProviders(array $plan): array
	{
		$m = [];
		foreach ($plan as $p) {
			$k = is_string($p) ? $p : 'unknown';
			$m[$k] = (int) ($m[$k] ?? 0) + 1;
		}
		ksort($m);
		return $m;
	}

	private function randomProvider(): string
	{
		return self::PROVIDER_POOL_EXTRA[random_int(0, count(self::PROVIDER_POOL_EXTRA) - 1)];
	}

	/** @return array{from_id:?string,to_id:?string,from_email:?string,to_email:?string} */
	private function pickFromToPair(array $fromUsers, array $toUsers): array
	{
		$from = $fromUsers[random_int(0, count($fromUsers) - 1)] ?? null;
		$to   = $toUsers[random_int(0, count($toUsers) - 1)] ?? null;

		$tries = 0;
		while (
			$tries++ < self::MAX_ATTEMPTS_PICK &&
			is_array($from) && is_array($to) &&
			(string)($from['id'] ?? '') !== '' &&
			(string)($to['id'] ?? '') !== '' &&
			(string)($from['id'] ?? '') === (string)($to['id'] ?? '') &&
			count($toUsers) > 1
		) {
			$to = $toUsers[random_int(0, count($toUsers) - 1)] ?? null;
		}

		$fromEmail = $this->ensureValidEmail($from['email'] ?? null, $from['id'] ?? null);
		$toEmail = $this->ensureValidEmail($to['email'] ?? null, $to['id'] ?? null);

		while ($fromEmail === $toEmail) {
			$toEmail = $this->generateRandomEmail();
		}

		return [
			'from_id'    => is_array($from) ? (string)($from['id'] ?? '') : null,
			'to_id'      => is_array($to)   ? (string)($to['id'] ?? '') : null,
			'from_email' => is_array($from) ? $fromEmail : null,
			'to_email'   => is_array($to)   ? $toEmail : null,
		];
	}

	/** @return array<string>|null */
	private function pickEmailsList(array $eligibleUsers, int $n, array $exclude = []): ?array
	{
		$excludeMap = [];
		foreach ($exclude as $e) {
			if (is_string($e) && trim($e) !== '') $excludeMap[trim($e)] = true;
		}

		$out = [];
		$tries = 0;

		while (count($out) < $n && $tries++ < 256) {
			$u = $eligibleUsers[random_int(0, count($eligibleUsers) - 1)] ?? null;
			if (!is_array($u)) continue;

			$userId = is_string($u['id'] ?? null) ? trim((string) $u['id']) : '';
			$em = is_string($u['email'] ?? null) ? trim((string) $u['email']) : '';

			// Validate email and generate random one if invalid
			if ($em === '' || !preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $em)) {
				$newEmail = $this->generateRandomEmail();

				// Try to update the user row with the new email
				if ($userId !== '') {
					try {
						$updated = DB::table(DC::TABLE_USERS)
							->where('id', $userId)
							->whereNotNull('id')
							->update(['email' => $newEmail]);

						if ($updated) {
							Log::debug('[EmailSeeder] Updated user email in pickEmailsList', [
								'user_id' => $userId,
								'new_email' => $newEmail,
							]);
							// Update the array reference for future iterations
							$u['email'] = $newEmail;
						}
					} catch (\Throwable $e) {
						Log::debug('[EmailSeeder] Failed to update user email in pickEmailsList', [
							'user_id' => $userId,
							'error' => $e->getMessage(),
						]);
					}
				}

				$em = $newEmail;
			}

			if (isset($excludeMap[$em])) continue;

			$out[] = $em;
			$out = array_values(array_unique($out));
		}

		return $out ?: null;
	}

	private function ensureValidEmail(?string $email, ?string $userId): string
	{
		$email = is_string($email) ? trim($email) : '';

		if ($email === '' || !preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $email)) {
			$newEmail = $this->generateRandomEmail();

			if (is_string($userId) && trim($userId) !== '') {
				try {
					$updated = DB::table(DC::TABLE_USERS)
						->where('id', trim($userId))
						->whereNotNull('id')
						->update(['email' => $newEmail]);

					if ($updated) {
						Log::debug('[EmailSeeder] Updated user email in ensureValidEmail', [
							'user_id' => $userId,
							'new_email' => $newEmail,
						]);
					}
				} catch (\Throwable $e) {
					Log::debug('[EmailSeeder] Failed to update user email in ensureValidEmail', [
						'user_id' => $userId,
						'error' => $e->getMessage(),
					]);
				}
			}

			return $newEmail;
		}

		return $email;
	}
	private function generateRandomEmail(): string
	{
		return fake()->userName() . '-' . Str::random(8) . '@' . fake()->domainName();
	}

	private function generateUniqueIdentifier(): string
	{
		$tries = 0;
		do {
			$tries++;
			$v = (string) Str::uuid();

			if (!$this->emailUniqueExistsRaw($v)) return $v;
		} while ($tries < self::MAX_ATTEMPTS_UNIQUE);

		// último recurso (ainda reduz chance de colisão)
		return (string) Str::uuid() . '-' . Str::random(8);
	}

	private function emailUniqueExistsRaw(string $unique): bool
	{
		try {
			$row = DB::selectOne(
				"select 1 as x from " . DC::TABLE_EMAILS . " where " . EC::COL_EM_KEY . " = ? limit 1",
				[$unique]
			);
			return $row !== null;
		} catch (\Throwable) {
			return false;
		}
	}

	private function randomPastDateTime(int $maxDaysBack): Carbon
	{
		$now = Carbon::now('America/Sao_Paulo');
		$days = random_int(0, max(1, $maxDaysBack));
		$mins = random_int(0, 60 * 23);

		return $now->copy()->subDays($days)->subMinutes($mins);
	}

	private function makeSubjectForModule(AppModuleType $t): string
	{
		$base = $t->label();
		$suffix = match (true) {
			$this->chance(0.22) => ' - Action Required',
			$this->chance(0.18) => ' - Update',
			$this->chance(0.12) => ' - Reminder',
			default => ' - Notification',
		};

		return $base . $suffix;
	}

	private function makeBodyPlain(AppModuleType $t, string $provider): string
	{
		$lines = [
			"Module: {$t->value}",
			"Provider: {$provider}",
			"Timestamp: " . Carbon::now('America/Sao_Paulo')->toIso8601String(),
		];

		if ($this->chance(0.35)) $lines[] = "Status: informational";
		if ($this->chance(0.20)) $lines[] = "Reference: " . Str::upper(Str::random(10));

		return implode("\n", $lines);
	}

	private function makeBodyHtml(string $plain): string
	{
		$escaped = htmlspecialchars($plain, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$escaped = nl2br($escaped);
		return "<html><body><pre style=\"font-family: ui-monospace, Menlo, Monaco, Consolas, monospace;\">{$escaped}</pre></body></html>";
	}

	/** @return array<string>|null */
	private function pickThreadIds(array $createdIds): ?array
	{
		if (count($createdIds) < 2) return null;

		$n = random_int(1, min(5, count($createdIds)));
		$out = [];

		$tries = 0;
		while (count($out) < $n && $tries++ < 128) {
			$id = $createdIds[random_int(0, count($createdIds) - 1)] ?? null;
			if (!is_string($id) || $id === '') continue;
			$out[] = $id;
			$out = array_values(array_unique($out));
		}

		return $out ?: null;
	}

	private function chance(float $p): bool
	{
		$p = max(0.0, min(1.0, $p));
		return (random_int(0, 10_000) / 10_000) < $p;
	}
}
