<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, MessagesConstants as MC, SupportsConstants as SC};
use App\Models\SupportReply;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Symfony\Component\Console\Output\ConsoleOutput;

class SupportReplySeeder extends Seeder
{
	private ConsoleOutput $out;

	private const SECONDS_LIMIT = 6 * 10 ** 2;
	public function run(): void
	{
		$clock = microtime(true);
		$this->out = new ConsoleOutput();

		$cap = 3200;

		$supportRows = $this->fetchSupportsForSeeding();
		$supportCount = count($supportRows);

		if ($supportCount === 0) {
			$this->out->writeln('<comment>[SupportReplySeeder]</comment> No supports found.');
			return;
		}

		$eligibleReplierIds = $this->fetchEligibleReplierUserIds();
		if (count($eligibleReplierIds) === 0) {
			$this->out->writeln('<comment>[SupportReplySeeder]</comment> No eligible repliers found; falling back to all users.');
			$eligibleReplierIds = $this->fetchAnyUserIds();
		}

		$targetSupports = (int) floor($supportCount * 0.75);
		if ($targetSupports <= 0) $targetSupports = 1;

		$plannedRepliesPerSupport = [];
		$rawTotal = 0;

		foreach ($supportRows as $row) {
			if (count($plannedRepliesPerSupport) >= $targetSupports) break;

			$n = random_int(1, 8);
			$plannedRepliesPerSupport[] = [$row, $n];
			$rawTotal += $n;
		}

		$targetTotal = $rawTotal;
		if ($targetTotal > $cap) $targetTotal = $cap;

		$rem = $targetTotal % 64;
		if ($rem !== 0) {
			$up = $targetTotal + (64 - $rem);
			$targetTotal = $up <= $cap ? $up : ($targetTotal - $rem);
		}

		if ($targetTotal <= 0) {
			$this->out->writeln('<comment>[SupportReplySeeder]</comment> Target total is 0 after adjustment.');
			return;
		}

		$this->out->writeln(
			'<info>[SupportReplySeeder]</info> supports=' . $supportCount
				. ' target_supports=' . $targetSupports
				. ' raw_total=' . $rawTotal
				. ' target_total=' . $targetTotal
				. ' (cap=' . $cap . ')'
		);

		$created = 0;

		foreach ($plannedRepliesPerSupport as [$support, $planned]) {
			if ($created >= $targetTotal) break;
			if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
				$this->out->writeln('<comment>[SupportReplySeeder]</comment> Seeding time limit reached, stopping early.');
				return;
			}

			$remaining = $targetTotal - $created;
			$toCreate = $planned > $remaining ? $remaining : $planned;
			if ($toCreate <= 0) continue;

			for ($i = 0; $i < $toCreate; $i++) {
				if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
					$this->out->writeln('<comment>[SupportReplySeeder]</comment> Seeding time limit reached, stopping early.');
					return;
				}
				$attrs = $this->buildReplyAttributes($support, $eligibleReplierIds);

				$this->out->writeln(
					'<comment>[SupportReplySeeder]</comment> create reply'
						. ' support=' . ($attrs[SC::COL_SPT_ID] ?? '#NO_SUPPORT')
						. ' user=' . ($attrs['user'] ?? '#NO_USER')
						. ' sent_at=' . ($attrs[MC::COL_SNT_AT] ? '1' : '0')
						. ' is_read=' . (((bool) ($attrs[MC::COL_IS_RD] ?? false)) ? '1' : '0')
						. ' attachment=' . (($attrs['attachment'] ?? null) ? '1' : '0')
				);

				try {
					SupportReply::create($attrs);
					$created++;
				} catch (\Throwable $e) {
					Log::error(static::class . ' failed creating support reply', [
						'file' => $e->getFile(),
						'line' => $e->getLine(),
						'error' => $e->getMessage(),
						'support_id' => $attrs[SC::COL_SPT_ID] ?? null,
						'user_id' => $attrs['user'] ?? null,
					]);
				}

				if ($created >= $targetTotal) break;
			}
		}

		$this->out->writeln('<info>[SupportReplySeeder]</info> created=' . $created . ' (target=' . $targetTotal . ')');
	}

	private function fetchSupportsForSeeding(): array
	{
		try {
			$conn = DB::connection();
			$driver = $conn->getDriverName();
			$rand = $driver === 'pgsql' ? 'RANDOM()' : 'RAND()';

			return DB::table(DC::TABLE_SUPPORTS)
				->select([
					'id',
					'user',
					'task',
					'email',
					'notification',
					'bug',
				])
				->orderByRaw($rand)
				->limit(50000)
				->get()
				->all();
		} catch (\Throwable $e) {
			Log::error(static::class . ' failed fetching supports', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	private function fetchEligibleReplierUserIds(): array
	{
		try {
			$authorized = ['admin', 'super_admin', 'company'];

			$ids = DB::table(DC::TABLE_USERS)
				->select('id')
				->whereIn('type', $authorized)
				->pluck('id')
				->all();

			return array_values(array_unique(array_map('strval', $ids)));
		} catch (\Throwable $e) {
			Log::error(static::class . ' failed fetching eligible repliers', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	private function fetchAnyUserIds(): array
	{
		try {
			$ids = DB::table(DC::TABLE_USERS)
				->select('id')
				->limit(5000)
				->pluck('id')
				->all();

			return array_values(array_unique(array_map('strval', $ids)));
		} catch (\Throwable $e) {
			Log::error(static::class . ' failed fetching fallback users', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	private function buildReplyAttributes(object $support, array $eligibleReplierIds): array
	{
		$supportId = (string) ($support->id ?? '');
		$requesterId = (string) ($support->user ?? '');

		$userId = $this->pickRandomId($eligibleReplierIds, 20) ?: $requesterId;

		$isDraft = random_int(1, 100) <= 25;
		$sentAt = $isDraft ? null : now()->subMinutes(random_int(0, 60 * 24 * 15));
		$isRead = $isDraft ? false : (random_int(1, 100) <= 55);
		$readAt = $isRead ? now()->subMinutes(random_int(0, 60 * 24 * 10)) : null;

		$attachments = $this->makeOtherAttachments();

		$attrs = [
			'code' => null,
			SC::COL_SPT_ID => $supportId ?: null,
			'user' => $userId ?: null,
			'description' => $this->fakeText(240),
			MC::COL_SNT_AT => $sentAt,
			MC::COL_IS_RD => $isRead,
			MC::COL_RD_AT => $readAt,

			'email' => $this->maybeUuid((string) ($support->email ?? '')),
			'notification' => $this->maybeUuid((string) ($support->notification ?? '')),
			'task' => $this->maybeUuid((string) ($support->task ?? '')),
			'form' => null,
			SC::COL_FORM_RSP => null,
			'log' => null,

			'attachment' => $this->makePrimaryAttachment(),
			SC::COL_OTHER_ATTACHMENTS => $attachments,
		];

		return $attrs;
	}

	private function pickRandomId(array $ids, int $attemptLimit = 12): ?string
	{
		$n = count($ids);
		if ($n === 0) return null;

		$attempts = 0;
		do {
			$attempts++;
			$idx = random_int(0, $n - 1);
			$v = $ids[$idx] ?? null;
			$s = is_scalar($v) ? trim((string) $v) : '';
			if ($s !== '') return $s;
		} while ($attempts < $attemptLimit);

		return null;
	}

	private function maybeUuid(string $value): ?string
	{
		$v = trim($value);
		return $v !== '' ? $v : null;
	}

	private function makePrimaryAttachment(): ?string
	{
		$r = random_int(1, 100);
		if ($r <= 70) return null;

		if ($r <= 80) return 'https://' . parse_url((string) config('app.url'), PHP_URL_HOST) . '/mock/support-reply/' . random_int(1000, 9999) . '.pdf';

		if ($r <= 90) return 'storage/mock/support-reply/' . random_int(1000, 9999) . '.png';

		return null;
	}

	private function makeOtherAttachments(): ?array
	{
		$r = random_int(1, 100);
		if ($r <= 75) return null;

		$qty = random_int(1, 4);
		$out = [];

		$attempts = 0;
		while (count($out) < $qty && $attempts < 20) {
			$attempts++;
			$v = $this->makePrimaryAttachment();
			if ($v === null) continue;
			$out[] = $v;
		}

		$out = array_values(array_unique($out));
		return $out ?: null;
	}

	private function fakeText(int $maxLen = 200): string
	{
		$parts = [
			'Update',
			'Investigating',
			'Applied fix',
			'Need confirmation',
			'Waiting on logs',
			'Reproduced issue',
			'Deployed patch',
			'Please verify',
			'Escalated internally',
			'Work in progress',
		];

		$s = $parts[array_rand($parts)] . ': '
			. 'ref=' . random_int(100000, 999999)
			. ' note=' . $parts[array_rand($parts)];

		return mb_strlen($s) > $maxLen ? mb_substr($s, 0, $maxLen) : $s;
	}
}
