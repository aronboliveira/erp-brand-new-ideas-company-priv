<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	BillsConstants as BC,
	CompaniesConstants as CC,
	DatabaseConstants as DC
};
use App\Enums\PaymentPatternType;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;

class CouponSeeder extends Seeder
{
	private const CODE_LENGTH = 10;
	private const MAX_COUPONS_PER_USER = 8;
	private const MIN_COUPONS_PER_USER = 0;

	public function run(): void
	{
		if (!Schema::hasTable(DC::TABLE_COUPONS) || !Schema::hasTable(DC::TABLE_USERS)) {
			$this->command?->warn('Required tables missing. Aborting CouponSeeder.');
			return;
		}

		// Get user count using raw query for performance
		$userCount = DB::selectOne('SELECT COUNT(*) as count FROM ' . DC::TABLE_USERS)->count;

		if ($userCount === 0) {
			$this->command?->warn('No users found. Aborting CouponSeeder.');
			return;
		}

		// Max coupons = 50% of users
		$maxCoupons = (int) ceil($userCount * 0.5);

		$this->command?->info("Creating up to {$maxCoupons} coupons for {$userCount} users...");

		// Get eligible user IDs (raw query for performance)
		$userIds = DB::select('SELECT id FROM ' . DC::TABLE_USERS . ' LIMIT 5000');
		$userIds = array_column($userIds, 'id');

		if (empty($userIds)) {
			$this->command?->warn('No user IDs retrieved. Aborting.');
			return;
		}

		// Get product and category IDs if tables exist (raw queries)
		$productIds = $this->getIds(DC::TABLE_PROD_SERVS, 1000);
		$categoryIds = $this->getIds(DC::TABLE_PROD_SERV_CATS, 500);

		$now = Carbon::now();
		$existingCodes = DB::table(DC::TABLE_COUPONS)
			->pluck('code')
			->flip()
			->all();

		$created = 0;

		for ($i = 0; $i < $maxCoupons; $i++) {
			try {
				// Generate unique code
				$code = $this->generateUniqueCode($existingCodes);

				// Random discount type and value
				$isPct = fake()->boolean(45);
				$discType = $isPct ? PaymentPatternType::Percentage : PaymentPatternType::Fixed;
				$discount = $isPct
					? fake()->randomFloat(2, 5.0, 35.0)
					: fake()->randomFloat(2, 10.0, 250.0);

				// Date ranges
				$validFrom = $now->copy()->subDays(fake()->numberBetween(0, 120));
				$validTo = fake()->boolean(75)
					? $validFrom->copy()->addDays(fake()->numberBetween(15, 120))
					: null;

				// Sample arrays (keeping nulls for nullable columns)
				$excProducts = $this->sampleIds($productIds, fake()->numberBetween(0, 5));
				$excCategories = $this->sampleIds($categoryIds, fake()->numberBetween(0, 4));
				$aplCategories = $this->sampleIds($categoryIds, fake()->numberBetween(0, 4));
				$excRoles = $this->sampleRoles(fake()->numberBetween(0, 3));

				$givenBy = fake()->boolean(80) ? fake()->randomElement($userIds) : null;
				$givenBySt = $givenBy ? "user {$givenBy}" : "system";
				(new \Symfony\Component\Console\Output\ConsoleOutput)->writeln("Creating Coupon: code={$code}, givenBy={$givenBySt}");
				Coupon::create([
					'code' => $code,
					'name' => fake()->boolean(70) ? fake()->words(fake()->numberBetween(2, 4), true) : null,
					'discount' => $discount,
					BC::COL_DSC_TP => $discType,
					CC::COL_GIVEN_BY => $givenBy,
					BC::COL_MIN_UNLCK => fake()->boolean(60) ? fake()->randomFloat(2, 50, 500) : null,
					BC::COL_MAX_DSC => fake()->boolean(50) ? fake()->randomFloat(2, 100, 800) : null,
					'limit' => fake()->numberBetween(1, 5),
					'description' => fake()->boolean(40) ? fake()->sentence(10) : null,
					AC::COL_IA => fake()->boolean(85),
					BC::COL_VLD_FRM => $validFrom,
					BC::COL_VLD_TO => $validTo,
					BC::COL_DT_LMT_TO_USER => fake()->boolean(25)
						? $now->copy()->addDays(fake()->numberBetween(7, 60))
						: null,
					'stackable' => fake()->boolean(70),
					BC::COL_MUST_BE_VRF => fake()->boolean(30),
					BC::COL_MIN_PRV_ORD => fake()->numberBetween(0, 2),
					BC::COL_MAX_PRV_ORD => fake()->numberBetween(0, 5),
					BC::COL_CAN_BE_GIFT => fake()->boolean(30),
					BC::COL_EXC_RLS => $excRoles,
					BC::COL_EXC_PRD => $excProducts,
					BC::COL_EXC_CAT => $excCategories,
					BC::COL_APL_CAT => $aplCategories,
					'rules' => $this->generateRules(),
				]);

				$created++;

				if ($created % 100 === 0) {
					$this->command?->info("Created {$created}/{$maxCoupons} coupons...");
				}
			} catch (\Exception $e) {
				Log::warning('CouponSeeder failed: ' . $e->getMessage());
				continue;
			}
		}

		$this->command?->info("CouponSeeder completed: {$created} coupons created.");
	}

	private function getIds(string $table, int $limit): array
	{
		if (!Schema::hasTable($table)) {
			return [];
		}

		$results = DB::select("SELECT id FROM {$table} LIMIT {$limit}");
		return array_column($results, 'id');
	}

	private function generateUniqueCode(array &$existing): string
	{
		do {
			$code = strtoupper(Str::random(self::CODE_LENGTH));
		} while (
			isset($existing[$code]) ||
			Coupon::where('code', $code)->exists()
		);

		$existing[$code] = true;
		return $code;
	}

	private function sampleIds(array $ids, int $count): ?array
	{
		if (empty($ids) || $count === 0) {
			return null;
		}

		$count = min($count, count($ids));
		$sampled = fake()->randomElements($ids, $count);

		return array_values($sampled);
	}

	private function sampleRoles(int $count): ?array
	{
		if ($count === 0) {
			return null;
		}

		$roles = ['customer', 'company', 'employee', 'vendor', 'client'];
		$count = min($count, count($roles));

		return array_values(fake()->randomElements($roles, $count));
	}

	private function generateRules(): ?array
	{
		if (!fake()->boolean(50)) {
			return null;
		}

		$rules = [];

		if (fake()->boolean(60)) {
			$min = fake()->randomFloat(2, 50, 200);
			$rules['min_amount'] = $min;
			$rules['max_amount'] = $min + fake()->randomFloat(2, 100, 500);
		}

		if (fake()->boolean(40)) {
			$rules['allowed_payment_methods'] = fake()->randomElements(
				['credit_card', 'debit_card', 'pix', 'bank_transfer'],
				fake()->numberBetween(1, 3)
			);
		}

		return empty($rules) ? null : $rules;
	}
}
