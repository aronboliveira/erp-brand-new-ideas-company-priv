<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, SettingsConstants as SC};
use App\Enums\{CountryName, DEICategory, Gender};
use App\Models\JobApplication;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\{Arr, Str};
use Symfony\Component\Console\Output\ConsoleOutput;

class JobApplicationSeeder extends Seeder
{
	private ConsoleOutput $out;

	private const MAX_ATTEMPTS = 32;
	// private const HARD_CAP = 2048;
	private const HARD_CAP = 2;
	// private const SECONDS_LIMIT = 6 * 10 ** 2;
	private const SECONDS_LIMIT = 32;
	public function run(): void
	{
		$this->out = new ConsoleOutput();
		$clock = microtime(true);
		$jobRows = $this->fetchJobsForApplications();
		if (!$jobRows) {
			$this->out->writeln('<error>[JobApplicationsSeeder]</error> No jobs found. Aborting.');
			return;
		}

		$userIds = $this->fetchUuidList(DC::TABLE_USERS, 'id');
		$announcementIds = $this->fetchUuidList(DC::TABLE_ANC, 'id');

		$docIds = $this->fetchUuidList(DC::TABLE_DOCS, 'id');
		$noteIds = $this->fetchUuidList(DC::TABLE_JB_AP_NTS, 'id');
		$customQuestionIds = $this->fetchUuidList(DC::TABLE_CUSTOM_QUESTIONS, 'id');
		$itvScheduleIds = $this->fetchUuidList(DC::TABLE_ITV_SCD, 'id');

		$rawTotal = 0;
		$plan = [];

		foreach ($jobRows as $jr) {
			$jobId = $jr['id'];
			$n = random_int(2, 128);
			$plan[$jobId] = [
				'count' => $n,
				'announcement' => $jr['announcement'],
				'country' => $jr['country'],
				'state' => $jr['state'],
			];
			$rawTotal += $n;
		}

		if ($rawTotal > self::HARD_CAP) {
			$this->scaleDownPlanToHardCap($plan, $rawTotal);
			$rawTotal = 0;
			foreach ($plan as $p) $rawTotal += (int) $p['count'];
		}

		$targetTotal = $this->nextMultipleOf64($rawTotal);
		$padding = $targetTotal - $rawTotal;

		$this->out->writeln('<info>[JobApplicationsSeeder]</info> Planned applications: ' . $targetTotal . ' (raw=' . $rawTotal . ', padded=' . $padding . ', cap=' . self::HARD_CAP . ')');

		$jobsList = array_keys($plan);
		while ($padding > 0 && $jobsList) {
			$jid = $jobsList[array_rand($jobsList)];
			$plan[$jid]['count'] = (int) $plan[$jid]['count'] + 1;
			$padding--;
		}

		DB::beginTransaction();
		$targetResult = min($targetTotal, self::HARD_CAP);
		try {
			$created = 0;

			foreach ($plan as $jobId => $p) {
				$count = (int) $p['count'];
				if ($count <= 0) continue;
				for ($i = 1; $i <= $count; $i++) {

					if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? self::SECONDS_LIMIT : 6 * 10 ** 2)) {
						Log::warning(self::class . ' seeding time limit reached, stopping early');
						return;
					}
					$appliedAt = $this->randomPastDateTime(120);
					$lastReviewAt = random_int(1, 100) <= 60
						? $appliedAt->addDays(random_int(0, 15))->addMinutes(random_int(0, 900))
						: null;

					if ($lastReviewAt && $lastReviewAt->lessThan($appliedAt)) $lastReviewAt = $appliedAt;

					$rejected = random_int(1, 100) <= 18;
					$rejectedAt = $rejected && $lastReviewAt
						? $lastReviewAt->addDays(random_int(0, 20))->toDateString()
						: null;

					$nextItvAt = null;
					if (!$rejected && random_int(1, 100) <= 40) {
						$floor = $lastReviewAt ?? $appliedAt;
						$nextItvAt = CarbonImmutable::now()->addDays(random_int(0, 20))->setTime(random_int(9, 18), [0, 15, 30, 45][array_rand([0, 1, 2, 3])]);
						if ($nextItvAt->lessThan($floor)) $nextItvAt = $floor;
					}

					$email = $this->buildUniqueApplicantEmailForJob($jobId);
					$phone = $this->buildUniqueApplicantPhoneForJob($jobId);

					$country = $this->pickApplicationCountry($p['country']);
					$state = $this->pickApplicationState($country, $p['state']);

					$announcement = null;
					if (is_string($p['announcement']) && trim($p['announcement']) !== '') {
						$announcement = (string) $p['announcement'];
					} elseif ($announcementIds && random_int(1, 100) <= 35) {
						$announcement = $announcementIds[array_rand($announcementIds)];
					}

					$dei = random_int(1, 100) <= 18 ? $this->pickEnumValue(DEICategory::cases()) : null;

					$rating = random_int(0, 100);
					$stage = random_int(1, 10);
					$order = random_int(0, 500);

					$isArchivedPseudoBool = (random_int(1, 100) <= 10) ? 1 : 0;
					if ($isArchivedPseudoBool === 1 && random_int(1, 100) <= 40) $isArchivedPseudoBool = 3;

					$referrerId = $userIds ? $this->maybePickId($userIds, 12) : null;

					$itvScores = null;
					if (random_int(1, 100) <= 35) {
						$itvScores = [
							'screening' => random_int(0, 10),
							'technical' => random_int(0, 10),
							'behavioral' => random_int(0, 10),
						];
					}

					$itvNoteId = $noteIds ? $this->maybePickId($noteIds, 15) : null;

					$notes = null;
					if ($noteIds && random_int(1, 100) <= 25) {
						$notes = [];
						$take = random_int(1, 3);
						for ($k = 0; $k < $take; $k++) $notes[] = $noteIds[array_rand($noteIds)];
						if ($itvNoteId !== null) $notes[] = $itvNoteId;
					} elseif ($itvNoteId !== null) {
						$notes = [$itvNoteId];
					}

					$ctQtId = $customQuestionIds ? $this->maybePickId($customQuestionIds, 10) : null;
					$ctQt = random_int(1, 100) <= 25 ? $this->makeCustomQuestionPayload() : null;

					$payload = [
						'job' => $jobId,
						AC::COL_APL_ID => $userIds ? $this->maybePickId($userIds, 20) : null,

						'name' => $this->makePersonName(),
						'email' => $email,
						'phone' => $phone,
						'source' => $this->pickSource(),
						'announcement' => $announcement,

						'country' => $country,
						'state' => $state,
						'city' => $this->makeCity($country),
						'address' => random_int(1, 100) <= 55 ? $this->makeAddress($country) : null,
						'zip' => $country === CountryName::Brazil->value ? $this->makeBrazilZip() : null,
						'ip' => random_int(1, 100) <= 70 ? $this->makeIp() : null,

						AC::COL_DEI_CTG => $dei,

						AC::COL_APL_AT => $appliedAt->toDateTimeString(),
						AC::COL_LST_RVW_AT => $lastReviewAt ? $lastReviewAt->toDateTimeString() : null,

						AC::COL_RFR_ID => $referrerId,
						AC::COL_RFR_NM => $referrerId !== null ? $this->makePersonName() : null,
						AC::COL_RFR_EML => $referrerId !== null ? $this->makeEmail('ref') : null,

						AC::COL_EXP_SLR => random_int(1, 100) <= 60 ? (string) random_int(2500, 28000) : null,
						AC::COL_EXP_SLR_CURR => SC::DEF_SITE_CURRENCY_ID,

						AC::COL_CUR_EMP => random_int(1, 100) <= 50 ? $this->makeCompanyName() : null,
						AC::COL_CUR_PST => random_int(1, 100) <= 50 ? $this->makeCurrentPosition() : null,
						AC::COL_CUR_SLR => random_int(1, 100) <= 45 ? (string) random_int(2500, 25000) : null,
						AC::COL_CUR_SLR_CURR => SC::DEF_SITE_CURRENCY_ID,

						AC::COL_WRK_AUTH => $docIds ? $this->maybePickId($docIds, 10) : null,
						AC::COL_WRK_AUTH_APV => (random_int(1, 100) <= 55),
						AC::COL_NTC_PRD => random_int(0, 120),
						'interviews' => $itvScheduleIds && random_int(1, 100) <= 30
							? (function () use ($itvScheduleIds) {
								$count = random_int(1, min(4, count($itvScheduleIds)));
								$selected = Arr::random($itvScheduleIds, $count);

								return array_map(function ($id) {
									$format = random_int(1, 100);

									// 40% - Scalar UUID (valid)
									if ($format <= 40) {
										return $id;
									}

									// 30% - Referential with valid id and candidate
									if ($format <= 70) {
										return [
											'id' => $id,
											'candidate' => $id, // Same as interview schedule candidate
										];
									}
									if ($format <= 85) {
										return [
											'id' => Str::uuid()->toString(),
											'candidate' => $id,
										];
									}
									if ($format <= 95) {
										return [
											'id' => $id,
										];
									}
									return [
										'id' => 'not-a-uuid',
										'candidate' => 'invalid-candidate',
									];
								}, $selected);
							})()
							: null,
						AC::COL_NXT_ITV_AT => $rejectedAt !== null ? null : ($nextItvAt ? $nextItvAt->toDateTimeString() : null),
						AC::COL_ITV_NTS => random_int(1, 100) <= 20 ? $this->makeInterviewNotes() : null,
						AC::COL_ITV_NT_ID => $itvNoteId,
						AC::COL_ITV_SCRS => $itvScores,

						'profile' => random_int(1, 100) <= 45 ? $this->makeProfileText() : null,
						AC::COL_PRF_DOC => $docIds ? $this->maybePickId($docIds, 8) : null,

						'portfolio' => random_int(1, 100) <= 35 ? $this->makePortfolioUrl() : null,
						'website' => random_int(1, 100) <= 25 ? $this->makeWebsiteUrl() : null,

						'resume' => random_int(1, 100) <= 65 ? $this->makeResumeText() : null,
						AC::COL_RSM_DOC => $docIds ? $this->maybePickId($docIds, 12) : null,

						AC::COL_CV_LT => random_int(1, 100) <= 25 ? $this->makeCoverLetterText() : null,
						AC::COL_CV_LT_DOC => $docIds ? $this->maybePickId($docIds, 10) : null,

						'dob' => random_int(1, 100) <= 70 ? $this->makeDob18to60()->toDateString() : null,
						'gender' => $this->pickEnumValue(Gender::cases()) ?? Gender::Other->value,

						'experience' => random_int(1, 100) <= 55 ? $this->makeExperienceText() : null,
						AC::COL_EXP_DOC => $docIds ? $this->maybePickId($docIds, 10) : null,

						'education' => random_int(1, 100) <= 45 ? $this->makeEducationText() : null,
						AC::COL_ED_DOC => $docIds ? $this->maybePickId($docIds, 10) : null,

						'stage' => $stage,
						'order' => $order,

						'skill' => random_int(1, 100) <= 55 ? $this->makeSkillText() : null,
						AC::COL_SKL_DOC => $docIds ? $this->maybePickId($docIds, 10) : null,

						'rating' => $rating,
						AC::COL_RJC_RS => $rejectedAt !== null ? $this->makeRejectionReason() : null,
						AC::COL_RJC_AT => $rejectedAt,

						'feedback' => random_int(1, 100) <= 25 ? $this->makeFeedbackText() : null,
						AC::COL_FDB_DOC => $docIds ? $this->maybePickId($docIds, 10) : null,

						AC::COL_IS_ARC => $isArchivedPseudoBool,

						AC::COL_CT_QT => $ctQt,
						AC::COL_CT_QT_ID => $ctQtId,

						AC::COL_TRMS_ACPT => (random_int(1, 100) <= 75),

						'certifications' => random_int(1, 100) <= 20 ? $this->makeCertifications() : null,
						'awards' => random_int(1, 100) <= 10 ? $this->makeAwards() : null,
						'publications' => random_int(1, 100) <= 10 ? $this->makePublications() : null,
						'projects' => random_int(1, 100) <= 25 ? $this->makeProjects() : null,
						'languages' => random_int(1, 100) <= 45 ? $this->makeLanguages() : null,
						'references' => random_int(1, 100) <= 15 ? $this->makeReferences() : null,
						'diversity' => random_int(1, 100) <= 12 ? $this->makeDiversity() : null,
						'disabilities' => random_int(1, 100) <= 8 ? $this->makeDisabilities() : null,
						AC::COL_SC_MD => random_int(1, 100) <= 30 ? $this->makeSocialMedia() : null,
						'questions' => random_int(1, 100) <= 20 ? $this->makeQuestionsArray() : null,
						'tests' => random_int(1, 100) <= 15 ? $this->makeTests() : null,
						'notes' => $notes,
						'attachments' => $docIds && random_int(1, 100) <= 15 ? $this->makeDocRefs($docIds) : null,
					];

					$this->out->writeln(
						'<comment>[JobApplicationsSeeder](' . $created . '/' . $targetResult . ')</comment> Creating application'
							. ' job=' . $jobId
							. ' email=' . $email
							. ' phone=' . $phone
							. ' country=' . (string) $country
							. ' rejected=' . ($rejectedAt ? 'yes' : 'no')
					);

					$m = new JobApplication($payload);
					$m->save();

					$created++;
					if ($created >= self::HARD_CAP) break 2;
				}
			}

			DB::commit();
			$this->out->writeln('<info>[JobApplicationsSeeder]</info> Created applications: ' . $created);
		} catch (\Throwable $e) {
			DB::rollBack();
			$this->out->writeln('<error>[JobApplicationsSeeder]</error> Failed: ' . $e->getMessage());
			throw $e;
		}
	}

	private function fetchJobsForApplications(): array
	{
		if (!DB::getSchemaBuilder()->hasTable(DC::TABLE_JOBS)) return [];

		$rows = DB::select('SELECT id, announcement, country, state FROM ' . DC::TABLE_JOBS);
		$out = [];
		foreach ($rows as $r) {
			$id = $r->id ?? null;
			if (!is_string($id) || trim($id) === '') continue;

			$out[] = [
				'id' => (string) $id,
				'announcement' => is_string($r->announcement ?? null) ? (string) $r->announcement : null,
				'country' => is_string($r->country ?? null) ? (string) $r->country : null,
				'state' => is_string($r->state ?? null) ? (string) $r->state : null,
			];
		}
		return $out;
	}

	private function fetchUuidList(string $table, string $col): array
	{
		if (!DB::getSchemaBuilder()->hasTable($table)) return [];
		$rows = DB::select("SELECT {$col} AS id FROM {$table}");
		$out = [];
		foreach ($rows as $r) {
			$v = $r->id ?? null;
			if (is_string($v) && trim($v) !== '') $out[] = $v;
		}
		return $out;
	}

	private function nextMultipleOf64(int $n): int
	{
		if ($n <= 0) return 64;
		$mod = $n % 64;
		if ($mod === 0) return $n;
		return $n + (64 - $mod);
	}

	private function scaleDownPlanToHardCap(array &$plan, int $rawTotal): void
	{
		$excess = $rawTotal - self::HARD_CAP;
		if ($excess <= 0) return;

		$this->out->writeln('<comment>[JobApplicationsSeeder]</comment> Scaling down plan by ' . $excess . ' to satisfy hard cap.');

		$keys = array_keys($plan);
		shuffle($keys);

		$attempt = 0;
		while ($excess > 0 && $attempt < self::MAX_ATTEMPTS * 5) {
			$attempt++;
			foreach ($keys as $k) {
				if ($excess <= 0) break;
				$c = (int) ($plan[$k]['count'] ?? 0);
				if ($c <= 2) continue;

				$dec = min($excess, max(1, (int) floor($c * 0.05)));
				$plan[$k]['count'] = max(2, $c - $dec);
				$excess -= $dec;
			}
		}

		if ($excess > 0) {
			foreach ($keys as $k) {
				if ($excess <= 0) break;
				$c = (int) ($plan[$k]['count'] ?? 0);
				if ($c <= 2) continue;
				$plan[$k]['count'] = $c - 1;
				$excess--;
			}
		}
	}

	private function maybePickId(array $ids, int $chancePct): ?string
	{
		if (!$ids) return null;
		if (random_int(1, 100) > $chancePct) return null;
		return (string) $ids[array_rand($ids)];
	}

	private function pickEnumValue(array $cases): ?string
	{
		if (!$cases) return null;
		$c = $cases[array_rand($cases)];
		return is_object($c) && property_exists($c, 'value') ? (string) $c->value : null;
	}

	private function randomPastDateTime(int $maxDaysBack): CarbonImmutable
	{
		$days = random_int(0, max(1, $maxDaysBack));
		$mins = random_int(0, 24 * 60 - 1);
		return CarbonImmutable::now()->subDays($days)->subMinutes($mins);
	}

	private function buildUniqueApplicantEmailForJob(string $jobId): string
	{
		$attempt = 0;
		while ($attempt < self::MAX_ATTEMPTS) {
			$attempt++;
			$email = 'candidate+' . Str::lower(Str::random(10)) . '@example.test';

			$exists = (bool) (DB::selectOne(
				'SELECT 1 AS x FROM ' . DC::TABLE_JOB_APPS . ' WHERE job = ? AND email = ? LIMIT 1',
				[$jobId, $email]
			)?->x ?? false);

			if (!$exists) return $email;
		}

		return 'candidate+' . Str::lower(Str::uuid()->toString()) . '@example.test';
	}

	private function buildUniqueApplicantPhoneForJob(string $jobId): string
	{
		$attempt = 0;
		while ($attempt < self::MAX_ATTEMPTS) {
			$attempt++;
			$phone = '+55' . (string) random_int(10000000000, 99999999999);

			$exists = (bool) (DB::selectOne(
				'SELECT 1 AS x FROM ' . DC::TABLE_JOB_APPS . ' WHERE job = ? AND phone = ? LIMIT 1',
				[$jobId, $phone]
			)?->x ?? false);

			if (!$exists) return $phone;
		}

		return '+55' . (string) random_int(10000000000, 99999999999);
	}

	private function pickApplicationCountry(?string $jobCountry): string
	{
		if (is_string($jobCountry) && trim($jobCountry) !== '' && random_int(1, 100) <= 70) return $jobCountry;

		$all = array_map(fn($e) => $e->value, CountryName::cases());
		$all = array_values($all);
		return $all[array_rand($all)];
	}

	private function pickApplicationState(string $country, mixed $jobState): ?string
	{
		if ($country === CountryName::Brazil->value) {
			$states = ['SP', 'RJ', 'MG', 'PR', 'SC', 'RS', 'BA', 'PE', 'CE'];
			if (is_string($jobState) && trim($jobState) !== '' && random_int(1, 100) <= 55) return trim((string) $jobState);
			return $states[array_rand($states)];
		}

		if (is_string($jobState) && trim($jobState) !== '' && random_int(1, 100) <= 40) return trim((string) $jobState);
		return null;
	}

	private function makeCity(string $countryVal): string
	{
		if ($countryVal === CountryName::Brazil->value) {
			$cities = ['São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Curitiba', 'Recife', 'Fortaleza', 'Salvador', 'Porto Alegre'];
			return $cities[array_rand($cities)];
		}

		$cities = ['Lisbon', 'Madrid', 'Bogotá', 'Buenos Aires', 'New York', 'Toronto', 'London'];
		return $cities[array_rand($cities)];
	}

	private function makeAddress(string $countryVal): string
	{
		$streetNo = random_int(10, 9999);
		$streets = ['Main St', 'Paulista Ave', 'Liberty St', 'Central Ave', 'Rua das Flores', 'Avenida Brasil'];
		$street = $streets[array_rand($streets)];
		return $streetNo . ' ' . $street;
	}

	private function makeBrazilZip(): string
	{
		$a = random_int(10000, 99999);
		$b = random_int(100, 999);
		return (string) $a . '-' . (string) $b;
	}

	private function makeIp(): string
	{
		return random_int(10, 250) . '.' . random_int(0, 255) . '.' . random_int(0, 255) . '.' . random_int(0, 255);
	}

	private function pickSource(): string
	{
		$pool = ['linkedin', 'indeed', 'company_website', 'referral', 'email_campaign', 'other'];
		return $pool[array_rand($pool)];
	}

	private function makePersonName(): string
	{
		$first = ['Ana', 'Bruno', 'Carla', 'Diego', 'Elisa', 'Felipe', 'Gabriela', 'Henrique'];
		$last = ['Silva', 'Souza', 'Oliveira', 'Santos', 'Pereira', 'Almeida', 'Costa', 'Ribeiro'];
		return $first[array_rand($first)] . ' ' . $last[array_rand($last)];
	}

	private function makeEmail(string $prefix): string
	{
		return $prefix . '+' . Str::lower(Str::random(10)) . '@example.test';
	}

	private function makeCompanyName(): string
	{
		$pool = ['Prestech', 'NovaTech', 'Alfa Systems', 'Beta Logistics', 'Gamma Labs', 'Delta Finance'];
		return $pool[array_rand($pool)];
	}

	private function makeCurrentPosition(): string
	{
		$pool = ['Developer', 'Analyst', 'Engineer', 'Coordinator', 'Consultant', 'Specialist'];
		return $pool[array_rand($pool)];
	}

	private function makeInterviewNotes(): string
	{
		return 'Interview notes: candidate demonstrated solid fundamentals, communicated clearly, and provided relevant examples. Follow-up recommended on system design and reliability practices.';
	}

	private function makeProfileText(): string
	{
		return "Summary:\n- Strong execution\n- Consistent delivery\n- Team collaboration\n";
	}

	private function makeResumeText(): string
	{
		return "Experience:\n- Company A (2y)\n- Company B (3y)\nEducation:\n- Bachelor degree\n";
	}

	private function makeCoverLetterText(): string
	{
		return 'Cover letter: I am applying for this role because my background aligns with the responsibilities and I can contribute with reliable delivery and clear communication.';
	}

	private function makeDob18to60(): CarbonImmutable
	{
		$years = random_int(18, 60);
		$days = random_int(0, 364);
		return CarbonImmutable::now()->subYears($years)->subDays($days)->startOfDay();
	}

	private function makeExperienceText(): string
	{
		return 'Experience highlights: delivery of web products, API integration, SQL reporting, and operational troubleshooting with documented procedures.';
	}

	private function makeEducationText(): string
	{
		return 'Education: undergraduate degree or equivalent professional experience; continuous learning through courses and certifications.';
	}

	private function makeSkillText(): string
	{
		$pool = ['Laravel', 'Django', 'Next.js', 'Angular', 'PostgreSQL', 'Redis', 'Docker', 'CI/CD'];
		shuffle($pool);
		return 'Skills: ' . implode(', ', array_slice($pool, 0, random_int(3, 6)));
	}

	private function makeRejectionReason(): string
	{
		$pool = [
			'Insufficient alignment with role requirements.',
			'Compensation expectations not compatible.',
			'Unavailable for required schedule.',
			'Process concluded with another candidate.',
		];
		return $pool[array_rand($pool)];
	}

	private function makeFeedbackText(): string
	{
		return 'Feedback: overall strong profile; recommended focus on deeper system design and production troubleshooting scenarios.';
	}

	private function makeCustomQuestionPayload(): string
	{
		return json_encode([
			'q1' => 'Are you legally authorized to work?',
			'a1' => 'Yes',
			'q2' => 'Preferred start date?',
			'a2' => 'Within 30 days',
		]);
	}

	private function makeCertifications(): array
	{
		$pool = ['aws_cp', 'azure_fundamentals', 'scrum_master'];
		shuffle($pool);
		return array_slice($pool, 0, random_int(1, 2));
	}

	private function makeAwards(): array
	{
		return [
			['title' => 'Employee Recognition', 'year' => (string) random_int(2018, 2025)],
		];
	}

	private function makePublications(): array
	{
		return [
			['title' => 'Technical article', 'platform' => 'medium', 'year' => (string) random_int(2018, 2025)],
		];
	}

	private function makeProjects(): array
	{
		return [
			['name' => 'Portfolio Project', 'url' => 'https://example.test/project/' . Str::lower(Str::random(8))],
		];
	}

	private function makeLanguages(): array
	{
		return [
			['language' => 'Portuguese', 'level' => 'native'],
			['language' => 'English', 'level' => 'intermediate'],
		];
	}

	private function makeReferences(): array
	{
		return [
			['name' => $this->makePersonName(), 'contact' => $this->makeEmail('ref')],
		];
	}

	private function makeDiversity(): array
	{
		return [
			'veteran_status' => 'no',
			'ethnicity' => 'not_informed',
		];
	}

	private function makeDisabilities(): array
	{
		return [
			'has_disability' => false,
		];
	}

	private function makeSocialMedia(): array
	{
		return [
			'linkedin' => 'https://linkedin.com/in/' . Str::lower(Str::random(10)),
			'github' => 'https://github.com/' . Str::lower(Str::random(10)),
		];
	}

	private function makeQuestionsArray(): array
	{
		return [
			['id' => 'q1', 'question' => 'Availability?', 'answer' => 'Immediate'],
			['id' => 'q2', 'question' => 'Preferred model?', 'answer' => 'Hybrid'],
		];
	}

	private function makeTests(): array
	{
		return [
			['key' => 'coding_test', 'result' => (string) (random_int(60, 100) / 10)],
		];
	}

	private function makeDocRefs(array $docIds): array
	{
		$count = min(3, max(1, random_int(1, 3)));
		$out = [];
		for ($i = 0; $i < $count; $i++) {
			$out[] = ['id' => (string) $docIds[array_rand($docIds)]];
		}
		return $out;
	}

	private function makePortfolioUrl(): string
	{
		$pool = ['https://github.com/', 'https://behance.net/', 'https://gitlab.com/'];
		return $pool[array_rand($pool)] . Str::lower(Str::random(10));
	}

	private function makeWebsiteUrl(): string
	{
		return 'https://example.test/' . Str::lower(Str::random(10));
	}
}
