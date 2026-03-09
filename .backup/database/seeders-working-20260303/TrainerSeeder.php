<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Models\Trainer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class TrainerSeeder extends Seeder
{
	private OutputInterface $out;

	public function run(): void
	{
		$this->initOutput();

		$this->out->writeln('<info>Seeding: ' . DC::TABLE_TRAINERS . '</info>');

		$branchIds = DB::table(DC::TABLE_BRANCHES)->pluck('id')->map(fn($v) => (string) $v)->filter()->values()->all();
		if (empty($branchIds)) {
			$this->out->writeln('<error>No branches found. Cannot seed trainers because `branch` is NOT NULL.</error>');
			return;
		}

		$docIds = DB::table(DC::TABLE_DOCS)->limit(500)->pluck('id')->map(fn($v) => (string) $v)->filter()->values()->all();

		$usedUserIds = DB::table(DC::TABLE_TRAINERS)->whereNotNull(UC::COL_USER_ID)->pluck(UC::COL_USER_ID)
			->map(fn($v) => (string) $v)->filter()->values()->all();
		$usedUserSet = array_fill_keys($usedUserIds, true);

		$usedEmpIds = DB::table(DC::TABLE_TRAINERS)->whereNotNull(UC::COL_EMP_ID)->pluck(UC::COL_EMP_ID)
			->map(fn($v) => (string) $v)->filter()->values()->all();
		$usedEmpSet = array_fill_keys($usedEmpIds, true);

		$existingExternalKeys = [];
		$existingExternal = DB::table(DC::TABLE_TRAINERS)
			->whereNull(UC::COL_USER_ID)
			->whereNull(UC::COL_EMP_ID)
			->select('branch', Trainer::COL_FIRSTNAME, Trainer::COL_LASTNAME, 'email')
			->get();

		foreach ($existingExternal as $r) {
			$k = $this->externalKey((string) ($r->branch ?? ''), (string) ($r->{Trainer::COL_FIRSTNAME} ?? ''), (string) ($r->{Trainer::COL_LASTNAME} ?? ''), (string) ($r->email ?? ''));
			$existingExternalKeys[$k] = true;
		}

		$totAdmins   = (int) DB::table(DC::TABLE_USERS)->where(UC::COL_TP, 'admin')->count();
		$totVendors  = (int) DB::table(DC::TABLE_USERS)->where(UC::COL_TP, 'vendor')->count();
		$totCompanies = (int) DB::table(DC::TABLE_USERS)->where(UC::COL_TP, 'company')->count();
		$totEmps     = (int) DB::table(DC::TABLE_EMPLOYEES)->count();

		$linkedAdmins = (int) DB::table(DC::TABLE_TRAINERS)
			->join(DC::TABLE_USERS, DC::TABLE_TRAINERS . '.' . UC::COL_USER_ID, '=', DC::TABLE_USERS . '.id')
			->where(DC::TABLE_USERS . '.' . UC::COL_TP, 'admin')
			->distinct(DC::TABLE_TRAINERS . '.' . UC::COL_USER_ID)
			->count(DC::TABLE_TRAINERS . '.' . UC::COL_USER_ID);

		$linkedVendors = (int) DB::table(DC::TABLE_TRAINERS)
			->join(DC::TABLE_USERS, DC::TABLE_TRAINERS . '.' . UC::COL_USER_ID, '=', DC::TABLE_USERS . '.id')
			->where(DC::TABLE_USERS . '.' . UC::COL_TP, 'vendor')
			->distinct(DC::TABLE_TRAINERS . '.' . UC::COL_USER_ID)
			->count(DC::TABLE_TRAINERS . '.' . UC::COL_USER_ID);

		$linkedCompanies = (int) DB::table(DC::TABLE_TRAINERS)
			->join(DC::TABLE_USERS, DC::TABLE_TRAINERS . '.' . UC::COL_USER_ID, '=', DC::TABLE_USERS . '.id')
			->where(DC::TABLE_USERS . '.' . UC::COL_TP, 'company')
			->distinct(DC::TABLE_TRAINERS . '.' . UC::COL_USER_ID)
			->count(DC::TABLE_TRAINERS . '.' . UC::COL_USER_ID);

		$linkedEmployees = (int) DB::table(DC::TABLE_TRAINERS)
			->whereNotNull(UC::COL_EMP_ID)
			->distinct(UC::COL_EMP_ID)
			->count(UC::COL_EMP_ID);

		$needAdmins    = max(0, (int) ceil($totAdmins * 0.10) - $linkedAdmins);
		$needVendors   = max(0, (int) ceil($totVendors * 0.05) - $linkedVendors);
		$needCompanies = max(0, (int) ceil($totCompanies * 0.20) - $linkedCompanies);
		$needEmployees = max(0, (int) ceil($totEmps * 0.10) - $linkedEmployees);

		$countOpt = $this->getCountOption();
		$this->out->writeln('<comment>' . OutputFormatter::escape(
			"Totals: admins={$totAdmins}, vendors={$totVendors}, companies={$totCompanies}, employees={$totEmps} | " .
				"Need(min): admins={$needAdmins}, vendors={$needVendors}, companies={$needCompanies}, employees={$needEmployees}" .
				($countOpt ? " | Target(count)={$countOpt}" : '')
		) . '</comment>');

		$faker = \Faker\Factory::create('pt_BR');

		$created = 0;
		$skipped = 0;

		$selectedUserIds = [];

		$created += $this->seedUsersByType('admin', $needAdmins, $branchIds, $docIds, $faker, $usedUserSet, $usedEmpSet, $selectedUserIds, $skipped);
		$created += $this->seedUsersByType('vendor', $needVendors, $branchIds, $docIds, $faker, $usedUserSet, $usedEmpSet, $selectedUserIds, $skipped);
		$created += $this->seedUsersByType('company', $needCompanies, $branchIds, $docIds, $faker, $usedUserSet, $usedEmpSet, $selectedUserIds, $skipped);

		$needEmployees = max(0, $needEmployees - $this->countSelectedEmployeesFromUserSeed($selectedUserIds, $usedEmpSet));
		if ($needEmployees > 0) {
			$created += $this->seedEmployees($needEmployees, $branchIds, $docIds, $faker, $usedUserSet, $usedEmpSet, $selectedUserIds, $existingExternalKeys, $skipped);
		}

		if ($countOpt !== null) {
			$currentTotal = (int) DB::table(DC::TABLE_TRAINERS)->count();
			$missing = max(0, $countOpt - $currentTotal);

			if ($missing > 0) {
				$this->out->writeln('<comment>' . OutputFormatter::escape("Top-up external trainers: {$missing}") . '</comment>');
				$created += $this->seedExternal($missing, $branchIds, $docIds, $faker, $existingExternalKeys, $skipped);
			}
		}

		$this->out->writeln('<info>' . OutputFormatter::escape("Done. Created={$created}, Skipped={$skipped}") . '</info>');
	}

	private function initOutput(): void
	{
		try {
			if ($this->command instanceof \Illuminate\Console\Command) {
				$out = $this->command->getOutput();
				if ($out instanceof OutputInterface) {
					$this->out = $out;
					return;
				}
			}
		} catch (\Throwable) {
		}

		$this->out = new NullOutput();
	}

	private function getCountOption(): ?int
	{
		if (!($this->command instanceof \Illuminate\Console\Command)) return null;
		if (!$this->command->hasOption('count')) return null;

		$raw = $this->command->option('count');
		if (!is_numeric($raw)) return null;

		$n = (int) $raw;
		return $n >= 1 ? $n : null;
	}

	private function seedUsersByType(
		string $type,
		int $need,
		array $branchIds,
		array $docIds,
		\Faker\Generator $faker,
		array &$usedUserSet,
		array &$usedEmpSet,
		array &$selectedUserIds,
		int &$skipped
	): int {
		if ($need <= 0) return 0;

		$availIds = DB::table(DC::TABLE_USERS)
			->where(UC::COL_TP, $type)
			->whereNotNull('id')
			->pluck('id')
			->map(fn($v) => (string) $v)
			->filter()
			->reject(fn($id) => isset($usedUserSet[$id]))
			->values()
			->all();

		if (empty($availIds)) {
			$this->out->writeln('<comment>' . OutputFormatter::escape("No available users for type={$type} (all already linked or none exist).") . '</comment>');
			return 0;
		}

		$take = min($need, count($availIds));
		if ($take < $need) {
			$this->out->writeln('<comment>' . OutputFormatter::escape("Type={$type}: need={$need}, available={$take}. Will create only available.") . '</comment>');
		}

		$userIds = DB::table(DC::TABLE_USERS)
			->where(UC::COL_TP, $type)
			->whereNotIn('id', array_keys($usedUserSet))
			->inRandomOrder()
			->limit($take)
			->pluck('id')
			->map(fn($v) => (string) $v)
			->filter()
			->values()
			->all();

		if (empty($userIds)) return 0;

		foreach ($userIds as $id) $selectedUserIds[$id] = true;

		$users = DB::table(DC::TABLE_USERS)
			->whereIn('id', $userIds)
			->select('id', 'name', 'email', 'phone')
			->get();

		$usersById = [];
		foreach ($users as $u) $usersById[(string) $u->id] = $u;

		$emps = DB::table(DC::TABLE_EMPLOYEES)
			->whereIn(UC::COL_USER_ID, $userIds)
			->select('id', UC::COL_USER_ID, 'name', 'email', 'phone', 'address', UC::COL_BRC_ID)
			->get();

		$empByUserId = [];
		foreach ($emps as $e) {
			$uid = (string) ($e->{UC::COL_USER_ID} ?? '');
			if ($uid !== '' && !isset($empByUserId[$uid])) $empByUserId[$uid] = $e;
		}

		$created = 0;

		foreach ($userIds as $uid) {
			if (isset($usedUserSet[$uid])) continue;

			$u = $usersById[$uid] ?? null;

			$emp = $empByUserId[$uid] ?? null;
			$empId = $emp ? (string) ($emp->id ?? '') : '';
			if ($empId !== '' && isset($usedEmpSet[$empId])) $empId = '';

			$branch = null;
			if ($emp && isset($emp->{UC::COL_BRC_ID})) {
				$b = (string) ($emp->{UC::COL_BRC_ID} ?? '');
				if ($b !== '') $branch = $b;
			}
			$branch ??= $branchIds[array_rand($branchIds)];

			[$first, $last] = $this->splitName((string) ($u->name ?? ''));
			if ($first === '') $first = $faker->firstName();
			if ($last === '') $last = $faker->lastName();

			$email = is_string($u->email ?? null) && trim((string) $u->email) !== '' ? trim((string) $u->email) : $faker->unique()->safeEmail();
			$phone = is_string($u->phone ?? null) && trim((string) $u->phone) !== '' ? trim((string) $u->phone) : $faker->unique()->cellphoneNumber();

			$payload = [
				UC::COL_USER_ID => $uid,
				'branch' => $branch,
				UC::COL_EMP_ID => $empId !== '' ? $empId : null,

				Trainer::COL_FIRSTNAME => $first,
				Trainer::COL_LASTNAME  => $last,
				'email' => $email,
				'contact' => $phone,

				'address' => $faker->address(),
				'presentation' => $faker->optional(0.7)->sentence(14),
				'expertise' => $faker->optional(0.7)->sentence(10),
				'registration' => !empty($docIds) && random_int(1, 100) <= 25 ? $docIds[array_rand($docIds)] : null,
				'qualifications' => $this->randomQualifications($faker),
				'certificates' => $this->randomCertificates($faker, $docIds),
			];

			if ($payload[UC::COL_EMP_ID] !== null && $emp) {
				$expectedUserId = (string) ($emp->{UC::COL_USER_ID} ?? '');
				if ($expectedUserId !== $uid) {
					$payload[UC::COL_EMP_ID] = null;
				}
			}

			try {
				Trainer::query()->create($payload);
				$usedUserSet[$uid] = true;
				if ($payload[UC::COL_EMP_ID]) $usedEmpSet[(string) $payload[UC::COL_EMP_ID]] = true;
				$created++;

				if ($created === 1 || ($created % 50) === 0) {
					$this->out->writeln('<info>' . OutputFormatter::escape("Created trainers (user type={$type}): {$created}") . '</info>');
				}
			} catch (\Throwable $ex) {
				$skipped++;
				$this->out->writeln('<error>' . OutputFormatter::escape("Failed creating trainer for user={$uid} ({$type}): " . $ex->getMessage()) . '</error>');
			}
		}

		return $created;
	}

	private function countSelectedEmployeesFromUserSeed(array $selectedUserIds, array $usedEmpSet): int
	{
		if (empty($selectedUserIds)) return 0;

		$userIds = array_keys($selectedUserIds);

		$empIds = DB::table(DC::TABLE_EMPLOYEES)
			->whereIn(UC::COL_USER_ID, $userIds)
			->pluck('id')
			->map(fn($v) => (string) $v)
			->filter()
			->values()
			->all();

		$cnt = 0;
		foreach ($empIds as $eid) {
			if (isset($usedEmpSet[$eid])) $cnt++;
		}
		return $cnt;
	}

	private function seedEmployees(
		int $need,
		array $branchIds,
		array $docIds,
		\Faker\Generator $faker,
		array &$usedUserSet,
		array &$usedEmpSet,
		array $selectedUserIds,
		array &$existingExternalKeys,
		int &$skipped
	): int {
		if ($need <= 0) return 0;

		$blockUserIds = array_keys($selectedUserIds);

		$q = DB::table(DC::TABLE_EMPLOYEES)
			->select('id', UC::COL_USER_ID, 'name', 'email', 'phone', 'address', UC::COL_BRC_ID)
			->whereNotNull('id');

		if (!empty($blockUserIds)) {
			$q->where(function ($qq) use ($blockUserIds) {
				$qq->whereNull(UC::COL_USER_ID)->orWhereNotIn(UC::COL_USER_ID, $blockUserIds);
			});
		}

		if (!empty($usedEmpSet)) {
			$q->whereNotIn('id', array_keys($usedEmpSet));
		}

		$emps = $q->inRandomOrder()->limit($need)->get();

		if ($emps->isEmpty()) {
			$this->out->writeln('<comment>' . OutputFormatter::escape("No available employees to satisfy employee-trainer requirement (need={$need}).") . '</comment>');
			return 0;
		}

		$created = 0;

		foreach ($emps as $e) {
			$eid = (string) ($e->id ?? '');
			if ($eid === '' || isset($usedEmpSet[$eid])) continue;

			$empUserId = (string) ($e->{UC::COL_USER_ID} ?? '');
			$useUserId = ($empUserId !== '' && !isset($usedUserSet[$empUserId])) ? $empUserId : '';

			$branch = null;
			if (isset($e->{UC::COL_BRC_ID})) {
				$b = (string) ($e->{UC::COL_BRC_ID} ?? '');
				if ($b !== '') $branch = $b;
			}
			$branch ??= $branchIds[array_rand($branchIds)];

			[$first, $last] = $this->splitName((string) ($e->name ?? ''));
			if ($first === '') $first = $faker->firstName();
			if ($last === '') $last = $faker->lastName();

			$email = is_string($e->email ?? null) && trim((string) $e->email) !== '' ? trim((string) $e->email) : $faker->unique()->safeEmail();
			$phone = is_string($e->phone ?? null) && trim((string) $e->phone) !== '' ? trim((string) $e->phone) : $faker->unique()->cellphoneNumber();
			$addr  = is_string($e->address ?? null) && trim((string) $e->address) !== '' ? trim((string) $e->address) : $faker->address();

			if ($useUserId === '' && $empUserId !== '') {
				$first = $first !== '' ? $first : $faker->firstName();
				$last  = trim($last . ' ' . Str::upper(Str::random(3)));
				$email = $faker->unique()->safeEmail();
				$phone = $faker->unique()->cellphoneNumber();
			}

			$payload = [
				UC::COL_USER_ID => $useUserId !== '' ? $useUserId : null,
				'branch' => $branch,
				UC::COL_EMP_ID => $eid,

				Trainer::COL_FIRSTNAME => $first,
				Trainer::COL_LASTNAME  => $last,
				'email' => $email,
				'contact' => $phone,
				'address' => $addr,

				'presentation' => $faker->optional(0.6)->sentence(12),
				'expertise' => $faker->optional(0.6)->sentence(10),
				'registration' => !empty($docIds) && random_int(1, 100) <= 20 ? $docIds[array_rand($docIds)] : null,
				'qualifications' => $this->randomQualifications($faker),
				'certificates' => $this->randomCertificates($faker, $docIds),
			];

			try {
				Trainer::query()->create($payload);

				$usedEmpSet[$eid] = true;
				if ($payload[UC::COL_USER_ID]) $usedUserSet[(string) $payload[UC::COL_USER_ID]] = true;

				$created++;
				if ($created === 1 || ($created % 50) === 0) {
					$this->out->writeln('<info>' . OutputFormatter::escape("Created trainers (employees): {$created}") . '</info>');
				}
			} catch (\Throwable $ex) {
				$skipped++;
				$this->out->writeln('<error>' . OutputFormatter::escape("Failed creating trainer for employee={$eid}: " . $ex->getMessage()) . '</error>');
			}
		}

		return $created;
	}

	private function seedExternal(
		int $need,
		array $branchIds,
		array $docIds,
		\Faker\Generator $faker,
		array &$existingExternalKeys,
		int &$skipped
	): int {
		if ($need <= 0) return 0;

		$created = 0;
		$attempts = 0;

		while ($created < $need && $attempts < ($need * 20)) {
			$attempts++;

			$branch = $branchIds[array_rand($branchIds)];
			$first = $faker->firstName();
			$last = $faker->lastName();
			$email = $faker->unique()->safeEmail();

			$k = $this->externalKey($branch, $first, $last, $email);
			if (isset($existingExternalKeys[$k])) continue;

			$payload = [
				UC::COL_USER_ID => null,
				'branch' => $branch,
				UC::COL_EMP_ID => null,

				Trainer::COL_FIRSTNAME => $first,
				Trainer::COL_LASTNAME  => $last,
				'email' => $email,
				'contact' => $faker->unique()->cellphoneNumber(),
				'address' => $faker->address(),

				'presentation' => $faker->optional(0.7)->sentence(14),
				'expertise' => $faker->optional(0.7)->sentence(10),
				'registration' => !empty($docIds) && random_int(1, 100) <= 15 ? $docIds[array_rand($docIds)] : null,
				'qualifications' => $this->randomQualifications($faker),
				'certificates' => $this->randomCertificates($faker, $docIds),
			];

			try {
				Trainer::query()->create($payload);
				$existingExternalKeys[$k] = true;
				$created++;
			} catch (\Throwable $ex) {
				$skipped++;
				$this->out->writeln('<error>' . OutputFormatter::escape("Failed creating external trainer: " . $ex->getMessage()) . '</error>');
			}
		}

		return $created;
	}

	private function splitName(string $name): array
	{
		$n = trim(preg_replace('/\s+/', ' ', $name));
		if ($n === '') return ['', ''];

		$parts = explode(' ', $n);
		$first = $parts[0] ?? '';
		$last = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';

		return [trim($first), trim($last)];
	}

	private function randomQualifications(\Faker\Generator $faker): array
	{
		$out = [];
		$n = random_int(0, 5);
		for ($i = 0; $i < $n; $i++) {
			$out[] = $faker->sentence(4);
		}
		return $out;
	}

	private function randomCertificates(\Faker\Generator $faker, array $docIds): array
	{
		$out = [];
		$n = random_int(0, 6);

		for ($i = 0; $i < $n; $i++) {
			if (!empty($docIds) && random_int(1, 100) <= 70) {
				$out[] = $docIds[array_rand($docIds)];
			} else {
				$out[] = $faker->words(random_int(1, 3), true);
			}
		}

		return array_values(array_unique(array_filter($out, fn($v) => is_string($v) && trim($v) !== '')));
	}

	private function externalKey(string $branch, string $first, string $last, string $email): string
	{
		return hash('sha256', strtolower(trim($branch)) . '|' . strtolower(trim($first)) . '|' . strtolower(trim($last)) . '|' . strtolower(trim($email)));
	}
}
