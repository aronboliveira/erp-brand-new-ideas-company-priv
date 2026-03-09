<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, CompaniesConstants as CC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\{ApprovalType, Frequency};
use App\Models\ZoomMeeting;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class ZoomMeetingSeeder extends Seeder
{
	private ConsoleOutput $out;

	private const HARD_CAP = 8000;

	private const UNIQUE_ATTEMPT_LIMIT = 32;

	public function run(): void
	{
		$this->out = new ConsoleOutput();

		$meetingRows = $this->fetchMeetings();
		if (!$meetingRows) {
			$this->out->writeln('<comment>[ZoomMeetingSeeder]</comment> No meetings found; skipping.');
			return;
		}

		$rawTotal = (int) floor(count($meetingRows) * 0.5);
		$target = $this->roundUpTo64($rawTotal);
		if ($target > self::HARD_CAP) $target = self::HARD_CAP;

		$maxPossible = count($meetingRows);
		if ($target > $maxPossible) {
			$target = $maxPossible - ($maxPossible % 64);
			if ($target <= 0) $target = $maxPossible;
		}

		$projectIds = $this->fetchProjectIds();
		$userIds = $this->fetchUserIds();
		$clientIds = $this->fetchClientIds();

		shuffle($meetingRows);

		$types = ['instant', 'scheduled', 'recurring_fixed', 'recurring_no_fixed', 'webinar', 'personal_room'];
		$audios = ['both', 'telephony', 'voip'];
		$autoRecording = ['local', 'cloud', 'none'];
		$whoCanShare = ['host_only', 'all_participants'];

		$created = 0;
		$scanned = 0;

		foreach ($meetingRows as $row) {
			$scanned++;
			if ($created >= $target) break;

			$meetingId = (string) ($row->id ?? '');
			if ($meetingId === '') continue;

			if ($this->zoomMeetingExistsForMeeting($meetingId)) continue;

			$type = $types[$created % count($types)];
			$audio = $audios[$created % count($audios)];
			$ar = $autoRecording[$created % count($autoRecording)];
			$share = $whoCanShare[$created % count($whoCanShare)];

			$nullableChance = 0.1;

			$min = (int) ($row->min_dr ?? 15);
			$exp = (int) ($row->exp_dr ?? 30);
			$max = (int) ($row->max_dr ?? 60);
			if ($min <= 0) $min = 15;
			if ($max <= 0) $max = 60;
			if ($min > $max) [$min, $max] = [$max, $min];
			if ($exp < $min) $exp = $min;
			if ($exp > $max) $exp = $max;

			$duration = random_int($min, $max);
			if (random_int(1, 100) <= 55) $duration = $exp;

			$code = $this->uniqueCodeForZoomFromMeetingCode((string) ($row->code ?? ''), $meetingId);
			$title = $this->chanceNull($nullableChance) ? null : trim((string) ($row->title ?? ''));
			if ($title === '') $title = null;

			$startAt = $this->inferStartAtFromMeetingRow($row);

			$zoomNum = $this->zoomMeetingNumber();
			$plainPw = $this->chanceNull($nullableChance) ? null : $this->zoomPassword();

			[$startUrl, $joinUrl, $regUrl] = $this->zoomUrls($zoomNum);

			$joinFromMeeting = trim((string) ($row->url ?? ''));
			if ($joinFromMeeting !== '' && $this->isZoomUrl($joinFromMeeting)) $joinUrl = $joinFromMeeting;

			$approval = $this->approvalTypeForMock($type);
			$enc = random_int(0, 100) <= 15 ? 'e2e_encryption' : 'enhanced_encryption';
			$timezone = random_int(0, 100) <= 60 ? 'America/Sao_Paulo' : 'UTC';

			$frequency = null;
			if (in_array($type, ['recurring_fixed', 'recurring_no_fixed'], true)) {
				$freqCases = [Frequency::Weekly->value, Frequency::Biweekly->value, Frequency::Monthly->value, Frequency::Variable->value];
				$frequency = $freqCases[$created % count($freqCases)];
			}

			$hostUserId = $userIds ? $userIds[$created % count($userIds)] : null;
			$clientId = $clientIds ? $clientIds[$created % count($clientIds)] : null;
			$projectId = $projectIds && random_int(0, 100) > 25 ? $projectIds[$created % count($projectIds)] : null;

			$altHostsEnabled = random_int(0, 100) <= 18;
			$altHosts = null;
			if ($altHostsEnabled) {
				$altHosts = $this->altHostsCsv($created);
				if (trim((string) $altHosts) === '') {
					$altHostsEnabled = false;
					$altHosts = null;
				}
			}

			$participants = $this->participantsList($userIds, $created);
			$maxParticipants = random_int(20, 300);
			if ($participants && count($participants) > $maxParticipants)
				$participants = array_slice($participants, 0, $maxParticipants);

			$payload = [
				CC::COL_MT_ID => $meetingId,
				'code' => $code,
				'title' => $title,
				'password' => $plainPw,
				AC::COL_APV_TP => $approval,
				AC::COL_ENC_TP => $enc,
				'duration' => $duration,
				AC::COL_STRT_URL => $this->chanceNull($nullableChance) ? null : $startUrl,
				AC::COL_JOIN_URL => $this->chanceNull($nullableChance) ? null : $joinUrl,
				AC::COL_RGT_URL => $this->chanceNull($nullableChance) ? null : $regUrl,
				'type' => $type,
				'frequency' => $frequency,
				'timezone' => $timezone,
				PJC::COL_PJ_ID => $projectId,
				UC::COL_USER_ID => $hostUserId,
				PJC::COL_CLIENT_ID => $clientId,
				PJC::COL_S_DT => $startAt,
				'audio' => $audio,
				AC::COL_AUTO_RCD => $ar,
				AC::COL_MAX_PRT => $maxParticipants,
				'agenda' => $this->chanceNull($nullableChance) ? null : $this->agendaText($type),
				'status' => $this->mockStatus($created),
				AC::COL_MT_CHAT => random_int(0, 100) <= 85,
				AC::COL_PV_CHAT => random_int(0, 100) <= 70,
				AC::COL_SCR_SHR => random_int(0, 100) <= 88,
				AC::COL_WHO_CAN_SHR_SCR => $share,
				AC::COL_WT_ROOM => random_int(0, 100) <= 28,
				AC::COL_BRK_ROOM => random_int(0, 100) <= 12,
				AC::COL_FC_MD => random_int(0, 100) <= 8,
				AC::COL_USE_PMI => random_int(0, 100) <= 6,
				AC::COL_ALT_HST_ENB => $altHostsEnabled,
				AC::COL_ALT_HST => $altHosts,
				AC::COL_CLS_RGT_AFT_HRS => random_int(0, 100) <= 10,
				AC::COL_MUTE_UPON_ENTRY => random_int(0, 100) <= 35,
				AC::COL_CTC_NM_RQ => random_int(0, 100) <= 22,
				AC::COL_CTC_EML_RQ => random_int(0, 100) <= 22,
				AC::COL_ALW_SHR_BT => random_int(0, 100) <= 82,
				AC::COL_ALW_MT_DV => random_int(0, 100) <= 78,
				'settings' => $this->settingsArray($type, $created),
				'participants' => $participants ?: null,
				'webhooks' => $this->chanceNull($nullableChance) ? null : $this->webhooksArray($created),
				'metadata' => $this->chanceNull($nullableChance) ? null : $this->metadataArray($meetingId, $type),
			];

			$this->out->writeln(
				'<info>[ZoomMeetingSeeder]</info> creating'
					. ' meeting_id=' . $meetingId
					. ' code=' . $code
					. ' type=' . $type
					. ' approval=' . $approval
					. ' duration=' . $duration
					. ' participants=' . (is_array($participants) ? (string) count($participants) : '0')
			);

			try {
				ZoomMeeting::query()->create($payload);
				$created++;
			} catch (\Throwable $e) {
				Log::error('ZoomMeetingSeeder create failed: ' . $e->getMessage(), [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'meeting_id' => $meetingId,
					'code' => $code,
				]);
			}
		}

		$this->out->writeln(
			'<comment>[ZoomMeetingSeeder]</comment> done created=' . $created
				. ' target=' . $target
				. ' scanned_meetings=' . $scanned
		);
	}

	private function fetchMeetings(): array
	{
		try {
			return DB::select(
				'SELECT id, code, title, `date`, `time`, url, `' . PJC::COL_MIN_DR . '` AS min_dr, `' . PJC::COL_EXP_DR . '` AS exp_dr, `' . PJC::COL_MAX_DR . '` AS max_dr
				 FROM ' . DC::TABLE_MEETINGS . ' ORDER BY id DESC'
			);
		} catch (\Throwable $e) {
			Log::error('ZoomMeetingSeeder fetchMeetings failed: ' . $e->getMessage(), [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return [];
		}
	}

	private function fetchProjectIds(): array
	{
		try {
			$rows = DB::select('SELECT id FROM ' . DC::TABLE_PROJECTS . ' ORDER BY id DESC LIMIT 4096');
			return array_values(array_map(fn($r) => (string) ($r->id ?? ''), $rows));
		} catch (\Throwable $e) {
			Log::warning('ZoomMeetingSeeder fetchProjectIds failed: ' . $e->getMessage(), [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return [];
		}
	}

	private function fetchUserIds(): array
	{
		try {
			$rows = DB::select('SELECT id FROM ' . DC::TABLE_USERS . ' ORDER BY id DESC LIMIT 8192');
			return array_values(array_map(fn($r) => (string) ($r->id ?? ''), $rows));
		} catch (\Throwable $e) {
			Log::warning('ZoomMeetingSeeder fetchUserIds failed: ' . $e->getMessage(), [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return [];
		}
	}

	private function fetchClientIds(): array
	{
		try {
			$rows = DB::select(
				'SELECT id FROM ' . DC::TABLE_USERS . ' WHERE `type` IN (?, ?, ?, ?) ORDER BY id DESC LIMIT 4096',
				['client', 'customer', 'vendor', 'company']
			);
			$ids = array_values(array_map(fn($r) => (string) ($r->id ?? ''), $rows));
			return $ids ?: $this->fetchUserIds();
		} catch (\Throwable $e) {
			Log::warning('ZoomMeetingSeeder fetchClientIds fallback: ' . $e->getMessage(), [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return $this->fetchUserIds();
		}
	}

	private function zoomMeetingExistsForMeeting(string $meetingId): bool
	{
		try {
			$row = DB::selectOne(
				'SELECT 1 AS ok FROM ' . DC::TABLE_ZM_MT . ' WHERE ' . CC::COL_MT_ID . ' = ? LIMIT 1',
				[$meetingId]
			);
			return is_object($row);
		} catch (\Throwable $e) {
			Log::warning('ZoomMeetingSeeder exists check failed: ' . $e->getMessage(), [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return false;
		}
	}

	private function uniqueCodeForZoomFromMeetingCode(string $meetingCode, string $meetingId): string
	{
		$base = trim($meetingCode) !== '' ? trim($meetingCode) : ('ZM-' . Str::uuid());
		$candidate = $base;

		$attempts = 0;
		do {
			$attempts++;
			$exists = false;

			try {
				$row = DB::selectOne(
					'SELECT 1 AS ok FROM ' . DC::TABLE_ZM_MT . ' WHERE code = ? LIMIT 1',
					[$candidate]
				);
				$exists = is_object($row);
			} catch (\Throwable $e) {
				Log::warning('ZoomMeetingSeeder code exists failed: ' . $e->getMessage(), [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'candidate' => $candidate,
				]);
				$exists = false;
			}

			if (!$exists) break;

			if ($attempts >= self::UNIQUE_ATTEMPT_LIMIT) {
				$candidate = 'ZM-' . Str::uuid();
				break;
			}

			$candidate = str_starts_with($base, 'ZM-')
				? ('ZM-' . Str::uuid())
				: ($base . '-ZM-' . Str::upper(Str::random(6)));
		} while (true);

		return $candidate;
	}

	private function inferStartAtFromMeetingRow(object $row): CarbonImmutable
	{
		$d = trim((string) ($row->date ?? ''));
		$t = trim((string) ($row->time ?? ''));
		if ($d !== '' && $t !== '') {
			try {
				return CarbonImmutable::parse($d . ' ' . $t);
			} catch (\Throwable) {
			}
		}
		return CarbonImmutable::now()->addDays(random_int(1, 45))->setTime(random_int(8, 18), [0, 15, 30, 45][random_int(0, 3)]);
	}

	private function zoomMeetingNumber(): string
	{
		$len = random_int(9, 11);
		$out = '';
		for ($i = 0; $i < $len; $i++) $out .= (string) random_int(0, 9);
		return $out;
	}

	private function zoomPassword(): string
	{
		return Str::lower(Str::random(10));
	}

	private function zoomUrls(string $meetingNumber): array
	{
		$pwd = Str::random(16);
		$start = 'https://zoom.us/s/' . $meetingNumber . '?zak=' . Str::random(24);
		$join = 'https://zoom.us/j/' . $meetingNumber . '?pwd=' . $pwd;
		$reg = 'https://zoom.us/webinar/register/WN_' . Str::random(18);
		return [$start, $join, $reg];
	}

	private function isZoomUrl(string $url): bool
	{
		$parts = parse_url(trim($url));
		$host = strtolower((string) ($parts['host'] ?? ''));
		if ($host === '') return false;
		return str_ends_with($host, 'zoom.us') || str_ends_with($host, 'zoom.com');
	}

	private function approvalTypeForMock(string $type): string
	{
		if (in_array($type, ['webinar'], true)) return ApprovalType::EmailConfirmation->value;
		if (in_array($type, ['recurring_fixed', 'recurring_no_fixed'], true)) return ApprovalType::ManualApproval->value;
		return random_int(0, 100) <= 65 ? ApprovalType::NoRegistration->value : ApprovalType::AutomaticApproval->value;
	}

	private function participantsList(array $userIds, int $seed): array
	{
		if (!$userIds) return [];

		$cnt = random_int(1, 18);
		if ($seed % 6 === 0) $cnt = random_int(8, 32);

		$pick = [];
		$attempts = 0;

		while (count($pick) < $cnt && $attempts < 64) {
			$attempts++;
			$id = $userIds[($seed + $attempts) % count($userIds)] ?? null;
			if (!$id) continue;
			$pick[] = $id;
		}

		$pick = array_values(array_unique(array_values($pick)));
		return $pick;
	}

	private function altHostsCsv(int $seed): string
	{
		$cnt = random_int(1, 3);
		$out = [];

		for ($i = 0; $i < $cnt; $i++) {
			$token = Str::lower(Str::random(8));
			$out[] = $token . '@example.com';
		}

		$out = array_values(array_unique($out));
		return implode(',', $out);
	}

	private function settingsArray(string $type, int $seed): array
	{
		return [
			'host_video' => $seed % 2 === 0,
			'participant_video' => $seed % 3 === 0,
			'allow_multiple_devices' => $seed % 5 !== 0,
			'type_hint' => $type,
		];
	}

	private function webhooksArray(int $seed): array
	{
		return [
			[
				'event' => 'meeting.created',
				'url' => 'https://example.com/hooks/zoom/' . Str::lower(Str::random(10)),
				'enabled' => $seed % 2 === 0,
			],
			[
				'event' => 'meeting.ended',
				'url' => 'https://example.com/hooks/zoom/' . Str::lower(Str::random(10)),
				'enabled' => $seed % 3 === 0,
			],
		];
	}

	private function metadataArray(string $meetingId, string $type): array
	{
		return [
			'linked_meeting_id' => $meetingId,
			'type' => $type,
			'seeded' => true,
			'seed_tag' => 'mock',
		];
	}

	private function mockStatus(int $seed): string
	{
		$list = ['waiting', 'scheduled', 'started', 'ended', 'cancelled'];
		return $list[$seed % count($list)];
	}

	private function agendaText(string $type): string
	{
		return match ($type) {
			'webinar' => 'Webinar session with moderated Q&A.',
			'personal_room' => 'Personal room sync.',
			'recurring_fixed', 'recurring_no_fixed' => 'Recurring touchpoint for project alignment.',
			'instant' => 'Instant meeting for quick triage.',
			default => 'Scheduled meeting for planning and execution review.',
		};
	}

	private function chanceNull(float $chance): bool
	{
		$p = max(0, min(1, $chance));
		return random_int(0, 1000) <= (int) round($p * 1000);
	}

	private function roundUpTo64(int $n): int
	{
		if ($n <= 0) return 0;
		$rem = $n % 64;
		return $rem === 0 ? $n : ($n + (64 - $rem));
	}
}
