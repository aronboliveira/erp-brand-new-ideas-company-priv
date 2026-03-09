<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC, SettingsConstants as SC};
use App\Enums\{CountryName, DEICategory, EvaluationStatus, JobLevel, Visibility, WorkContractType, WorkPresence, WorkShift};
use App\Models\Job;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Symfony\Component\Console\Output\ConsoleOutput;

class JobSeeder extends Seeder
{
	private ConsoleOutput $out;

	// private const SECONDS_LIMIT = 3 * 10 ** 2;
	private const SECONDS_LIMIT = 32;
	public function run(): void
	{
		$this->out = new ConsoleOutput();
		$clock = microtime(true);
		$faker = FakerFactory::create();

		$jobCats = $this->readJobCategories();
		if (!$jobCats) {
			$this->out->writeln('<error>[JobSeeder] No job categories found. Aborting.</error>');
			return;
		}

		$annIds = $this->readIds(DC::TABLE_ANC);
		$depIds = $this->readIds(DC::TABLE_DEPARTMENTS);
		$desIds = $this->readIds(DC::TABLE_DESIGNS);
		$tmplIds = $this->readIds(DC::TABLE_EMAIL_TEMPLATES);
		$docsIds = $this->readIds(DC::TABLE_DOCS);
		$formIds = $this->readIds(DC::TABLE_FORM_BUILD);
		$projIds = $this->readIds(DC::TABLE_PROJECTS);
		$msIds = $this->readIds(DC::TABLE_MSS);
		$goalIds = $this->readIds(DC::TABLE_GL);
		$userIds = $this->readIds(DC::TABLE_USERS);
		$branches = $this->readBranchesMinimal();
		$countJobCats = count($jobCats);
		$countBranches = count($branches);
		if (!$branches) {
			$this->out->writeln('<error>[JobSeeder] No branches found. Aborting.</error>');
			Log::warning('JobSeeder aborted: no branches found');
			return;
		}
		$brBranches = array_values(array_filter($branches, function (array $b): bool {
			$c = $b['country'] ?? null;
			if (!is_string($c) || trim($c) === '') return false;
			$e = CountryName::normalize($c);
			if (!$e) return false;
			return strtoupper($e->value) === 'BR' || $e === CountryName::Brazil;
		}));
		$rawTotal = 0;
		foreach ($jobCats as $cat)
			$rawTotal += random_int(1, 16);
		$hardCap = 256;
		$targetTotal = min($countJobCats * 4, $this->toNextMultipleOf64($rawTotal));
		$targetTotalFirst = max($countJobCats * 2, (int) floor($targetTotal * 0.1));
		$targetTotalSecond = $targetTotal - $targetTotalFirst;
		$targetBrazilFirst = (int) floor($targetTotalFirst * 0.8);
		$targetBrazilSecond = (int) floor($targetTotalSecond * 0.6);

		$annTarget = 0;
		if ($annIds) {
			$annTarget = (int) floor(count($annIds) * 0.5);
			if ($annTarget > $targetTotal) $annTarget = $targetTotal;
		}

		$createdFirst = 0;
		$createdSecond = 0;
		$createdBrazilFirst = 0;
		$createdBrazilSecond = 0;
		$withAnn = 0;
		$maxRetryTolerance = 32;

		$catIdx = 0;
		$catCount = count($jobCats);

		Log::warning("Job categories count={$countJobCats}, Branches count={$countBranches}, Brazil branches count=" . count($brBranches) . ", Target total first={$targetTotalFirst}, Target total second={$targetTotalSecond}, Target Brazil First={$targetBrazilFirst}, Target Brazil Second={$targetBrazilSecond}, Announcement IDs count=" . count($annIds) . ", Announcement target={$annTarget}");
		$targetResult = min($targetTotal, $hardCap);
		while ($createdFirst < $targetTotalFirst && $maxRetryTolerance > 0) {
			$maxRetryTolerance--;
			$cat = $jobCats[$catIdx % $catCount];
			$catIdx++;
			$iterations = random_int(1, 32);
			for ($i = 0; $i < $iterations && $createdFirst < $targetTotalFirst; $i++) {

				if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? self::SECONDS_LIMIT : 6 * 10 ** 2)) {
					Log::warning(self::class . ' seeding time limit reached, stopping early');
					return;
				}
				$hardCap--;
				if (!$hardCap) break;
				$maxRetryTolerance = 128;
				$needBrazil = $createdBrazilFirst < $targetBrazilFirst;

				$branch = $this->pickBranch($branches, $brBranches, $needBrazil);
				$branchId = $branch['id'];

				$payload = [];

				$payload['title'] = $this->buildUniqueTitle($faker, $cat['id'] ?? null);

				$payload['category'] = $cat['id'] ?? null;
				$payload['branch'] = $branchId;

				$payload['company'] = $this->pickNullableId($userIds, 0.65);

				$payload['country'] = $this->pickCountryForJob($branch, $needBrazil);
				$payload['state'] = $this->pickNullableString($branch['state'] ?? null, 0.65);
				$payload['city'] = $this->pickNullableString($branch['city'] ?? null, 0.65);
				$payload['address'] = $this->pickNullableString($branch['address'] ?? null, 0.45);

				$payload['department'] = $this->pickNullableId($depIds, 0.6);
				$payload['designation'] = $this->pickNullableId($desIds, 0.6);

				$payload['level'] = $this->pickEnumFromAcceptedOrAny(
					JobLevel::class,
					$this->parseAcceptedList($cat['accepted_levels_raw'] ?? null)
				);

				$payload[PJC::COL_PRS_TP] = $this->pickEnumFromAcceptedOrAny(
					WorkPresence::class,
					$this->parseAcceptedList($cat['accepted_presence_raw'] ?? null)
				);

				$payload[PJC::COL_CTC_TP] = $this->pickEnumFromAcceptedOrAny(
					WorkContractType::class,
					$this->parseAcceptedList($cat['accepted_contract_types_raw'] ?? null)
				);

				$payload[PJC::COL_SHFT_TP] = $this->pickEnumFromAcceptedOrAny(
					WorkShift::class,
					$this->parseAcceptedList($cat['accepted_shifts_raw'] ?? null)
				);

				$payload[AC::COL_EXP_SLR] = $this->pickNullableDecimal(2500, 28000, 0.75);
				$payload[AC::COL_EXP_SLR_CURR] = SC::DEF_SITE_CURRENCY_ID;

				$payload[AC::COL_RCT_ID] = $this->pickNullableId($userIds, 0.55);
				$payload[AC::COL_MNG_ID] = $this->pickNullableId($userIds, 0.55);

				$payload[AC::COL_RCT_NM] = $faker->boolean(65) ? $faker->name() : null;
				$payload[AC::COL_RCT_EML] = $faker->boolean(55) ? strtolower($faker->safeEmail()) : null;
				$payload[AC::COL_RCT_PST] = $faker->boolean(55) ? $faker->jobTitle() : null;

				$payload[AC::COL_MNG_NM] = $faker->boolean(65) ? $faker->name() : null;
				$payload[AC::COL_MNG_EML] = $faker->boolean(55) ? strtolower($faker->safeEmail()) : null;
				$payload[AC::COL_MNG_PST] = $faker->boolean(55) ? $faker->jobTitle() : null;

				$payload[AC::COL_RCV_EML] = $faker->boolean(45) ? strtolower($faker->safeEmail()) : null;
				$payload[AC::COL_RCV_TMP] = $this->pickNullableId($tmplIds, 0.35);
				$payload[AC::COL_RES_EML] = $faker->boolean(35) ? strtolower($faker->safeEmail()) : null;
				$payload[AC::COL_RSP_TMP] = $this->pickNullableId($tmplIds, 0.35);

				$payload[AC::COL_MIN_EXP_Y] = random_int(0, 16);

				$payload['description'] = $faker->boolean(75) ? $faker->paragraphs(random_int(2, 6), true) : null;
				$payload['requirement'] = $faker->boolean(70) ? $faker->paragraphs(random_int(2, 5), true) : null;

				$payload[AC::COL_RQ_DOC] = $this->pickNullableId($docsIds, 0.15);
				$payload['skill'] = $faker->boolean(65) ? $faker->paragraphs(random_int(1, 3), true) : null;
				$payload[AC::COL_SKL_DOC] = $this->pickNullableId($docsIds, 0.2);

				$payload[AC::COL_MIN_SKL_MTCH] = $this->pickNullableFloat(55.0, 95.0, 0.8);
				$payload[AC::COL_MAX_NTC_PD] = random_int(0, 180);

				$payload[AC::COL_RQ_WK_AUTH] = $faker->boolean(25);
				$payload[AC::COL_RQ_VS] = $faker->boolean(18);
				$payload[AC::COL_RQ_RLC] = $faker->boolean(22);
				$payload[AC::COL_RLC_PV] = $faker->boolean(18);
				$payload[AC::COL_MAX_RLC_DAYS] = $faker->boolean(35) ? random_int(10, 120) : null;

				$payload[AC::COL_RQ_BKG_CK] = $faker->boolean(20);
				$payload[AC::COL_RQ_CV_LT] = $faker->boolean(28);
				$payload[AC::COL_RQ_PRTF] = $faker->boolean(26);

				$payload[AC::COL_MAX_AP] = random_int(50, 1500);
				$payload[AC::COL_CUR_AP] = $faker->boolean(75) ? random_int(0, 180) : null;

				$payload[AC::COL_IS_DEI] = $faker->boolean(20);
				$payload[AC::COL_VW_CT] = $faker->boolean(80) ? random_int(0, 5000) : null;

				$payload[AC::COL_LST_AP_AT] = $faker->boolean(45) ? $faker->dateTimeBetween('-120 days', 'now') : null;

				$payload['position'] = $faker->boolean(55) ? random_int(1, 40) : null;

				$payload[PJC::COL_S_DT] = $faker->boolean(40) ? $faker->dateTimeBetween('-30 days', '+30 days')->format('Y-m-d') : null;
				$payload[PJC::COL_E_DT] = $faker->boolean(35) ? $faker->dateTimeBetween('+30 days', '+240 days')->format('Y-m-d') : null;

				$payload['status'] = $faker->boolean(80) ? EvaluationStatus::Active->value : EvaluationStatus::Draft->value;
				$payload['visibility'] = $faker->boolean(70) ? Visibility::Public->value : Visibility::Draft->value;

				$payload[AC::COL_CT_QT] = $faker->boolean(20) ? (string) random_int(1, 50) . ',' . (string) random_int(51, 120) : null;

				$payload[AC::COL_FM_ID] = $this->pickNullableId($formIds, 0.2);

				$payload['project'] = $this->pickNullableId($projIds, 0.18);
				$payload['milestone'] = $this->pickNullableId($msIds, 0.15);
				$payload['goal'] = $this->pickNullableId($goalIds, 0.15);

				$payload['benefits'] = $faker->boolean(55) ? $this->buildBenefits($faker) : null;
				$payload['incentives'] = $faker->boolean(35) ? $this->buildIncentives($faker) : null;

				$payload['stages'] = $faker->boolean(55) ? $this->buildStages($faker) : null;

				$payload[AC::COL_RQ_CRT] = $faker->boolean(28) ? $this->buildSimpleStringList($faker, 1, 4) : null;
				$payload[AC::COL_PRF_CRT] = $faker->boolean(22) ? $this->buildSimpleStringList($faker, 1, 4) : null;

				$payload[AC::COL_RQ_SKL] = $faker->boolean(55) ? $this->buildSimpleStringList($faker, 3, 10) : null;
				$payload[AC::COL_PRF_SKL] = $faker->boolean(40) ? $this->buildSimpleStringList($faker, 2, 8) : null;

				$payload[AC::COL_RQ_LG] = $faker->boolean(40) ? $this->buildLanguages($faker, 1, 3) : null;
				$payload[AC::COL_PRF_LG] = $faker->boolean(35) ? $this->buildLanguages($faker, 1, 3) : null;

				$payload[AC::COL_ACP_WK_AUTH] = $faker->boolean(25) ? $this->buildWorkAuthList($faker) : null;

				$payload[AC::COL_ALW_CTR] = $faker->boolean(40) ? $this->buildAllowedCountries($faker, $needBrazil) : null;
				$payload[AC::COL_ALW_ST] = $faker->boolean(35) ? $this->buildAllowedStatesMap($faker, $payload[AC::COL_ALW_CTR] ?? null) : null;

				$payload[AC::COL_DEI_CTG] = $faker->boolean(18) ? $this->pickEnumValue(DEICategory::class) : null;
				$payload[AC::COL_DEI_CRT] = $faker->boolean(18) ? $this->buildSimpleStringList($faker, 1, 5) : null;
				$payload[AC::COL_DEI_DOCS] = $faker->boolean(15) ? $this->buildSimpleStringList($faker, 1, 4) : null;

				$payload['attachments'] = $faker->boolean(15) ? $this->buildAttachments($faker, $docsIds) : null;
				$payload['tags'] = $faker->boolean(40) ? $this->buildTags($faker) : null;

				$payload['platforms'] = $faker->boolean(55) ? $this->buildPlatforms($faker) : null;
				$payload['metadata'] = $faker->boolean(35) ? $this->buildMetadata($faker) : null;

				$payload[AC::COL_HRD_ID] = null;
				$payload['hired'] = null;
				$payload['applicant'] = null;
				$payload[AC::COL_APL_ID] = null;

				if ($annIds && $withAnn < $annTarget) {
					$payload['announcement'] = $this->pickNullableId($annIds, 1.0);
					$withAnn++;
				} else {
					$payload['announcement'] = $this->pickNullableId($annIds, 0.35);
					if ($payload['announcement'] !== null) $withAnn++;
				}

				$this->out->writeln(
					'[JobSeeder] (' . $createdFirst . '/' . $targetResult . ') Creating job: cat=' . ($payload['category'] ?? 'null')
						. ' branch=' . ($payload['branch'] ?? 'null')
						. ' country=' . (is_scalar($payload['country'] ?? null) ? (string) $payload['country'] : 'null')
						. ' title="' . ($payload['title'] ?? '') . '"'
				);

				$job = new Job();
				foreach ($payload as $k => $v) {
					$job->setAttribute($k, $v);
				}
				$job->save();

				$createdFirst++;
				$cc = $job->getAttribute('country');
				$ccNorm = $cc instanceof CountryName ? $cc : CountryName::normalize(is_scalar($cc) ? (string) $cc : null);
				if ($ccNorm === CountryName::Brazil) $createdBrazilFirst++;
			}
		}
		while ($createdSecond < $targetTotalSecond && $maxRetryTolerance > 0) {
			$hardCap--;
			if (!$hardCap) break;
			$maxRetryTolerance--;
			$cat = $jobCats[$catIdx % $catCount];
			$catIdx++;
			$iterations = random_int(1, 32);
			for ($i = 0; $i < $iterations && $createdSecond < $targetTotalSecond; $i++) {
				$maxRetryTolerance = 128;
				$needBrazil = $createdBrazilSecond < $targetBrazilSecond;

				$branch = $this->pickBranch($branches, $brBranches, $needBrazil);
				$branchId = $branch['id'];

				$payload = [];

				$payload['title'] = $this->buildUniqueTitle($faker, $cat['id'] ?? null);

				$payload['category'] = $cat['id'] ?? null;
				$payload['branch'] = $branchId;

				$payload['company'] = $this->pickNullableId($userIds, 0.65);

				$payload['country'] = $this->pickCountryForJob($branch, $needBrazil);
				$payload['state'] = $this->pickNullableString($branch['state'] ?? null, 0.65);
				$payload['city'] = $this->pickNullableString($branch['city'] ?? null, 0.65);
				$payload['address'] = $this->pickNullableString($branch['address'] ?? null, 0.45);

				$payload['department'] = $this->pickNullableId($depIds, 0.6);
				$payload['designation'] = $this->pickNullableId($desIds, 0.6);

				$payload['level'] = $this->pickEnumFromAcceptedOrAny(
					JobLevel::class,
					$this->parseAcceptedList($cat['accepted_levels_raw'] ?? null)
				);

				$payload[PJC::COL_PRS_TP] = $this->pickEnumFromAcceptedOrAny(
					WorkPresence::class,
					$this->parseAcceptedList($cat['accepted_presence_raw'] ?? null)
				);

				$payload[PJC::COL_CTC_TP] = $this->pickEnumFromAcceptedOrAny(
					WorkContractType::class,
					$this->parseAcceptedList($cat['accepted_contract_types_raw'] ?? null)
				);

				$payload[PJC::COL_SHFT_TP] = $this->pickEnumFromAcceptedOrAny(
					WorkShift::class,
					$this->parseAcceptedList($cat['accepted_shifts_raw'] ?? null)
				);

				$payload[AC::COL_EXP_SLR] = $this->pickNullableDecimal(2500, 28000, 0.75);
				$payload[AC::COL_EXP_SLR_CURR] = SC::DEF_SITE_CURRENCY_ID;

				$payload[AC::COL_RCT_ID] = $this->pickNullableId($userIds, 0.55);
				$payload[AC::COL_MNG_ID] = $this->pickNullableId($userIds, 0.55);

				$payload[AC::COL_RCT_NM] = $faker->boolean(65) ? $faker->name() : null;
				$payload[AC::COL_RCT_EML] = $faker->boolean(55) ? strtolower($faker->safeEmail()) : null;
				$payload[AC::COL_RCT_PST] = $faker->boolean(55) ? $faker->jobTitle() : null;

				$payload[AC::COL_MNG_NM] = $faker->boolean(65) ? $faker->name() : null;
				$payload[AC::COL_MNG_EML] = $faker->boolean(55) ? strtolower($faker->safeEmail()) : null;
				$payload[AC::COL_MNG_PST] = $faker->boolean(55) ? $faker->jobTitle() : null;

				$payload[AC::COL_RCV_EML] = $faker->boolean(45) ? strtolower($faker->safeEmail()) : null;
				$payload[AC::COL_RCV_TMP] = $this->pickNullableId($tmplIds, 0.35);
				$payload[AC::COL_RES_EML] = $faker->boolean(35) ? strtolower($faker->safeEmail()) : null;
				$payload[AC::COL_RSP_TMP] = $this->pickNullableId($tmplIds, 0.35);

				$payload[AC::COL_MIN_EXP_Y] = random_int(0, 16);

				$payload['description'] = $faker->boolean(75) ? $faker->paragraphs(random_int(2, 6), true) : null;
				$payload['requirement'] = $faker->boolean(70) ? $faker->paragraphs(random_int(2, 5), true) : null;

				$payload[AC::COL_RQ_DOC] = $this->pickNullableId($docsIds, 0.15);
				$payload['skill'] = $faker->boolean(65) ? $faker->paragraphs(random_int(1, 3), true) : null;
				$payload[AC::COL_SKL_DOC] = $this->pickNullableId($docsIds, 0.2);

				$payload[AC::COL_MIN_SKL_MTCH] = $this->pickNullableFloat(55.0, 95.0, 0.8);
				$payload[AC::COL_MAX_NTC_PD] = random_int(0, 180);

				$payload[AC::COL_RQ_WK_AUTH] = $faker->boolean(25);
				$payload[AC::COL_RQ_VS] = $faker->boolean(18);
				$payload[AC::COL_RQ_RLC] = $faker->boolean(22);
				$payload[AC::COL_RLC_PV] = $faker->boolean(18);
				$payload[AC::COL_MAX_RLC_DAYS] = $faker->boolean(35) ? random_int(10, 120) : null;

				$payload[AC::COL_RQ_BKG_CK] = $faker->boolean(20);
				$payload[AC::COL_RQ_CV_LT] = $faker->boolean(28);
				$payload[AC::COL_RQ_PRTF] = $faker->boolean(26);

				$payload[AC::COL_MAX_AP] = random_int(50, 1500);
				$payload[AC::COL_CUR_AP] = $faker->boolean(75) ? random_int(0, 180) : null;

				$payload[AC::COL_IS_DEI] = $faker->boolean(20);
				$payload[AC::COL_VW_CT] = $faker->boolean(80) ? random_int(0, 5000) : null;

				$payload[AC::COL_LST_AP_AT] = $faker->boolean(45) ? $faker->dateTimeBetween('-120 days', 'now') : null;

				$payload['position'] = $faker->boolean(55) ? random_int(1, 40) : null;

				$payload[PJC::COL_S_DT] = $faker->boolean(40) ? $faker->dateTimeBetween('-30 days', '+30 days')->format('Y-m-d') : null;
				$payload[PJC::COL_E_DT] = $faker->boolean(35) ? $faker->dateTimeBetween('+30 days', '+240 days')->format('Y-m-d') : null;

				$payload['status'] = $faker->boolean(80) ? EvaluationStatus::Active->value : EvaluationStatus::Draft->value;
				$payload['visibility'] = $faker->boolean(70) ? Visibility::Public->value : Visibility::Draft->value;

				$payload[AC::COL_CT_QT] = $faker->boolean(20) ? (string) random_int(1, 50) . ',' . (string) random_int(51, 120) : null;

				$payload[AC::COL_FM_ID] = $this->pickNullableId($formIds, 0.2);

				$payload['project'] = $this->pickNullableId($projIds, 0.18);
				$payload['milestone'] = $this->pickNullableId($msIds, 0.15);
				$payload['goal'] = $this->pickNullableId($goalIds, 0.15);

				$payload['benefits'] = $faker->boolean(55) ? $this->buildBenefits($faker) : null;
				$payload['incentives'] = $faker->boolean(35) ? $this->buildIncentives($faker) : null;

				$payload['stages'] = $faker->boolean(55) ? $this->buildStages($faker) : null;

				$payload[AC::COL_RQ_CRT] = $faker->boolean(28) ? $this->buildSimpleStringList($faker, 1, 4) : null;
				$payload[AC::COL_PRF_CRT] = $faker->boolean(22) ? $this->buildSimpleStringList($faker, 1, 4) : null;

				$payload[AC::COL_RQ_SKL] = $faker->boolean(55) ? $this->buildSimpleStringList($faker, 3, 10) : null;
				$payload[AC::COL_PRF_SKL] = $faker->boolean(40) ? $this->buildSimpleStringList($faker, 2, 8) : null;

				$payload[AC::COL_RQ_LG] = $faker->boolean(40) ? $this->buildLanguages($faker, 1, 3) : null;
				$payload[AC::COL_PRF_LG] = $faker->boolean(35) ? $this->buildLanguages($faker, 1, 3) : null;

				$payload[AC::COL_ACP_WK_AUTH] = $faker->boolean(25) ? $this->buildWorkAuthList($faker) : null;

				$payload[AC::COL_ALW_CTR] = $faker->boolean(40) ? $this->buildAllowedCountries($faker, $needBrazil) : null;
				$payload[AC::COL_ALW_ST] = $faker->boolean(35) ? $this->buildAllowedStatesMap($faker, $payload[AC::COL_ALW_CTR] ?? null) : null;

				$payload[AC::COL_DEI_CTG] = $faker->boolean(18) ? $this->pickEnumValue(DEICategory::class) : null;
				$payload[AC::COL_DEI_CRT] = $faker->boolean(18) ? $this->buildSimpleStringList($faker, 1, 5) : null;
				$payload[AC::COL_DEI_DOCS] = $faker->boolean(15) ? $this->buildSimpleStringList($faker, 1, 4) : null;

				$payload['attachments'] = $faker->boolean(15) ? $this->buildAttachments($faker, $docsIds) : null;
				$payload['tags'] = $faker->boolean(40) ? $this->buildTags($faker) : null;

				$payload['platforms'] = $faker->boolean(55) ? $this->buildPlatforms($faker) : null;
				$payload['metadata'] = $faker->boolean(35) ? $this->buildMetadata($faker) : null;

				$payload[AC::COL_HRD_ID] = null;
				$payload['hired'] = null;
				$payload['applicant'] = null;
				$payload[AC::COL_APL_ID] = null;

				if ($annIds && $withAnn < $annTarget) {
					$payload['announcement'] = $this->pickNullableId($annIds, 1.0);
					$withAnn++;
				} else {
					$payload['announcement'] = $this->pickNullableId($annIds, 0.35);
					if ($payload['announcement'] !== null) $withAnn++;
				}

				$this->out->writeln(
					'[JobSeeder] (' . ($createdFirst + $createdSecond) . '/' . $targetResult . ') Creating job: cat=' . ($payload['category'] ?? 'null')
						. ' branch=' . ($payload['branch'] ?? 'null')
						. ' country=' . (is_scalar($payload['country'] ?? null) ? (string) $payload['country'] : 'null')
						. ' title="' . ($payload['title'] ?? '') . '"'
				);

				$job = new Job();
				foreach ($payload as $k => $v) {
					$job->setAttribute($k, $v);
				}
				$job->save();

				$createdSecond++;
				$cc = $job->getAttribute('country');
				$ccNorm = $cc instanceof CountryName ? $cc : CountryName::normalize(is_scalar($cc) ? (string) $cc : null);
				if ($ccNorm === CountryName::Brazil) $createdBrazilSecond++;
			}
		}
		$this->out->writeln(
			'<info>[JobSeeder] Done. created_first=' . $createdFirst
				. ' brazil_first=' . $createdBrazilFirst
				. ' + second=' . $createdSecond
				. ' brazil_second=' . $createdBrazilSecond
				. ' with_announcement=' . $withAnn
				. ' target_total_first=' . $targetTotalFirst
				. ' target_total_second=' . $targetTotalSecond
				. ' target_brazil_first=' . $targetBrazilFirst
				. ' target_brazil_second=' . $targetBrazilSecond
				. ' ann_target=' . $annTarget
				. '</info>'
		);
	}

	private function readIds(string $table): array
	{
		try {
			$rows = DB::select('select id from ' . $table);
			$out = [];
			foreach ($rows as $r) {
				$id = $r->id ?? null;
				if (is_scalar($id) && trim((string) $id) !== '') $out[] = (string) $id;
			}
			return $out;
		} catch (\Throwable $e) {
			return [];
		}
	}

	private function readBranchesMinimal(): array
	{
		try {
			$rows = DB::select('select id, country, state, city, address, email from ' . DC::TABLE_BRANCHES);
			$out = [];
			foreach ($rows as $r) {
				$id = $r->id ?? null;
				if (!is_scalar($id) || trim((string) $id) === '') continue;
				$out[] = [
					'id' => (string) $id,
					'country' => is_scalar($r->country ?? null) ? (string) $r->country : null,
					'state' => is_scalar($r->state ?? null) ? (string) $r->state : null,
					'city' => is_scalar($r->city ?? null) ? (string) $r->city : null,
					'address' => is_scalar($r->address ?? null) ? (string) $r->address : null,
					'email' => is_scalar($r->email ?? null) ? (string) $r->email : null,
				];
			}
			return $out;
		} catch (\Throwable $e) {
			return [];
		}
	}

	private function readJobCategories(): array
	{
		try {
			$cols = [
				'id',
				PJC::COL_ACP_LVLS . ' as accepted_levels_raw',
				PJC::COL_ACP_PRS . ' as accepted_presence_raw',
				PJC::COL_ACP_CTC_TP . ' as accepted_contract_types_raw',
				PJC::COL_ACP_SHFT . ' as accepted_shifts_raw',
			];

			$sql = 'select ' . implode(', ', $cols) . ' from ' . DC::TABLE_JOB_CATS;
			$rows = DB::select($sql);

			$out = [];
			foreach ($rows as $r) {
				$id = $r->id ?? null;
				if (!is_scalar($id) || trim((string) $id) === '') continue;

				$out[] = [
					'id' => (string) $id,
					'accepted_levels_raw' => $r->accepted_levels_raw ?? null,
					'accepted_presence_raw' => $r->accepted_presence_raw ?? null,
					'accepted_contract_types_raw' => $r->accepted_contract_types_raw ?? null,
					'accepted_shifts_raw' => $r->accepted_shifts_raw ?? null,
				];
			}

			return $out;
		} catch (\Throwable $e) {
			return [];
		}
	}

	private function toNextMultipleOf64(int $n): int
	{
		if ($n <= 0) return 64;
		$r = $n % 64;
		if ($r === 0) return $n;
		return $n + (64 - $r);
	}

	private function pickBranch(array $all, array $br, bool $needBrazil): array
	{
		if ($needBrazil && $br) return $br[array_rand($br)];
		return $all[array_rand($all)];
	}

	private function pickNullableId(array $ids, float $prob = 0.5): ?string
	{
		if (!$ids) return null;
		$roll = random_int(1, 1000) / 1000;
		if ($roll > $prob) return null;
		return $ids[array_rand($ids)];
	}

	private function pickNullableString(?string $preferred, float $prob = 0.5): ?string
	{
		$roll = random_int(1, 1000) / 1000;
		if ($roll > $prob) return null;
		if (is_string($preferred) && trim($preferred) !== '') return $preferred;
		return null;
	}

	private function pickNullableDecimal(int $min, int $max, float $prob = 0.6): ?string
	{
		$roll = random_int(1, 1000) / 1000;
		if ($roll > $prob) return null;
		$v = random_int($min, $max) + (random_int(0, 99) / 100);
		return number_format($v, 2, '.', '');
	}

	private function pickNullableFloat(float $min, float $max, float $prob = 0.6): ?float
	{
		$roll = random_int(1, 1000) / 1000;
		if ($roll > $prob) return null;
		$v = $min + (lcg_value() * ($max - $min));
		return round($v, 2);
	}

	private function pickCountryForJob(array $branch, bool $needBrazil): ?string
	{
		$bc = $branch['country'] ?? null;
		if ($needBrazil) return CountryName::Brazil->value;

		if (is_string($bc) && trim($bc) !== '') {
			$e = CountryName::normalize($bc);
			if ($e) return $e->value;
		}

		$all = array_values(array_map(fn($e) => $e->value, CountryName::cases()));
		return $all ? $all[array_rand($all)] : null;
	}

	private function parseAcceptedList(mixed $raw): ?array
	{
		if ($raw === null) return null;
		if (is_array($raw)) return $raw;

		if (is_scalar($raw)) {
			$s = trim((string) $raw);
			if ($s === '') return null;
			$decoded = json_decode($s, true);
			if (is_array($decoded)) return $decoded;
		}

		return null;
	}

	private function pickEnumFromAcceptedOrAny(string $enumClass, ?array $accepted): ?string
	{
		$cases = $enumClass::cases();
		$values = array_values(array_map(fn($e) => $e->value, $cases));

		if ($accepted && is_array($accepted)) {
			$acc = [];
			foreach ($accepted as $v) {
				if (!is_scalar($v)) continue;
				$sv = trim((string) $v);
				if ($sv === '') continue;
				if (in_array($sv, $values, true)) $acc[] = $sv;
			}
			if ($acc) return $acc[array_rand($acc)];
		}

		return $values ? $values[array_rand($values)] : null;
	}

	private function pickEnumValue(string $enumClass): ?string
	{
		$cases = $enumClass::cases();
		if (!$cases) return null;
		return $cases[array_rand($cases)]->value;
	}

	private function buildUniqueTitle($faker, ?string $categoryId): string
	{
		$attempt = 0;
		$maxAttempts = 25;

		while ($attempt < $maxAttempts) {
			$attempt++;
			$title = $faker->jobTitle();
			$title = trim((string) $title);
			if ($title === '') continue;

			$exists = DB::select('select 1 as x from ' . DC::TABLE_JOBS . ' where title = ? limit 1', [$title]);
			if (!$exists) return $title;
		}

		return 'Job ' . strtoupper(substr((string) $faker->uuid(), 0, 8));
	}

	private function buildBenefits($faker): array
	{
		$pool = [
			'health_insurance',
			'meal_voucher',
			'transport_voucher',
			'dental_insurance',
			'gympass',
			'remote_work',
			'education_support',
			'childcare_assistance',
			'life_insurance',
			'paid_time_off',
		];

		$n = random_int(2, 6);
		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$out[] = $pool[array_rand($pool)];
		}

		return array_values(array_unique($out));
	}

	private function buildIncentives($faker): array
	{
		$pool = [
			'performance_bonus',
			'annual_bonus',
			'stock_options',
			'referral_bonus',
			'profit_sharing',
			'signing_bonus',
		];

		$n = random_int(1, 3);
		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$out[] = $pool[array_rand($pool)];
		}

		return array_values(array_unique($out));
	}

	private function buildStages($faker): array
	{
		$pool = [
			'sourcing',
			'application',
			'screening',
			'phone_screen',
			'assessment',
			'interview',
			'technical',
			'behavioral',
			'final_interview',
			'offer_sent',
		];

		$n = random_int(4, 8);
		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$out[] = $pool[array_rand($pool)];
		}

		return array_values(array_unique($out));
	}

	private function buildSimpleStringList($faker, int $min, int $max): array
	{
		$n = random_int($min, $max);
		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$w = $faker->word();
			$out[] = strtolower(trim((string) $w));
		}
		return array_values(array_unique($out));
	}

	private function buildLanguages($faker, int $min, int $max): array
	{
		$pool = ['english', 'portuguese', 'spanish', 'french', 'german', 'mandarin', 'italian'];
		$n = random_int($min, $max);
		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$out[] = $pool[array_rand($pool)];
		}
		return array_values(array_unique($out));
	}

	private function buildWorkAuthList($faker): array
	{
		$pool = ['citizen', 'work_permit', 'student_visa', 'permanent_resident', 'temporary_resident'];
		$n = random_int(1, 3);
		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$out[] = $pool[array_rand($pool)];
		}
		return array_values(array_unique($out));
	}

	private function buildAllowedCountries($faker, bool $needBrazil): array
	{
		$all = array_values(array_map(fn($e) => $e->value, CountryName::cases()));
		if (!$all) return [CountryName::Brazil->value];

		$out = [];
		if ($needBrazil) $out[] = CountryName::Brazil->value;

		$n = random_int(1, 3);
		for ($i = 0; $i < $n; $i++) {
			$out[] = $all[array_rand($all)];
		}

		return array_values(array_unique($out));
	}

	private function buildAllowedStatesMap($faker, mixed $allowedCountries): ?array
	{
		if (!is_array($allowedCountries) || !$allowedCountries) return null;

		$out = [];
		$maxCountries = min(2, count($allowedCountries));
		$picked = [];

		for ($i = 0; $i < $maxCountries; $i++) {
			$cc = $allowedCountries[array_rand($allowedCountries)];
			if (!is_scalar($cc)) continue;
			$cc = strtoupper(trim((string) $cc));
			if ($cc === '' || in_array($cc, $picked, true)) continue;
			$picked[] = $cc;

			$states = [];
			$n = random_int(1, 4);
			for ($j = 0; $j < $n; $j++) {
				$states[] = strtolower($faker->stateAbbr());
			}

			$out[$cc] = array_values(array_unique($states));
		}

		return $out ?: null;
	}

	private function buildAttachments($faker, array $docIds): array
	{
		$n = random_int(1, 4);
		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$out[] = [
				'id' => $docIds ? $docIds[array_rand($docIds)] : null,
				'name' => $faker->words(random_int(2, 5), true),
			];
		}
		return $out;
	}

	private function buildTags($faker): array
	{
		$pool = ['tech', 'finance', 'design', 'sales', 'support', 'management', 'internship', 'remote', 'hybrid'];
		$n = random_int(2, 6);
		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$out[] = $pool[array_rand($pool)];
		}
		return array_values(array_unique($out));
	}

	private function buildPlatforms($faker): array
	{
		$pool = ['linkedin', 'indeed', 'company_website', 'referral', 'glassdoor'];
		$n = random_int(1, 3);
		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$out[] = $pool[array_rand($pool)];
		}
		return array_values(array_unique($out));
	}

	private function buildMetadata($faker): array
	{
		return [
			'seed' => true,
			'priority' => random_int(1, 5),
			'batch' => 'jobs_' . date('Ymd_His'),
		];
	}
}
