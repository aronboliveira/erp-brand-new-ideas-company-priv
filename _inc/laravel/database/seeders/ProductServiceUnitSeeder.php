<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	BillsConstants as BC,
	SettingsConstants as SC
};
use App\Enums\ProductStatus;
use App\Models\ProductService;
use App\Models\ProductServiceUnit;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductServiceUnitSeeder extends Seeder
{
	use EnsuresSystemUser;

	// Parâmetro fixo (sem env)
	private const ORPHANS = 6; // units without product linkage

	public function run(): void
	{
		$this->ensureSystemUser();
		// Reproducibility
		fake()->seed(20251128);

		// Tunables
		$perProductMin = 1;
		$perProductMax = 3;
		$orphans       = self::ORPHANS;

		DB::beginTransaction();
		try {
			// Preload DB state (including soft-deleted) to avoid unique collisions
			$existingCodes = ProductServiceUnit::withTrashed()->pluck('code')->filter()->map(fn($v) => (string) $v)->all();
			$existingNames = ProductServiceUnit::withTrashed()->pluck('name')->filter()->map(fn($v) => (string) $v)->all();

			$usedCode = array_fill_keys($existingCodes, true);
			$usedName = array_fill_keys($existingNames, true);

			// ---- Stable fixtures (idempotent) --------------------------------
			$fixtures = [
				[
					'code'                    => 'PSU-STD-ITEM',
					'name'                    => 'Standard Item Unit',
					'status'                  => ProductStatus::Active->value,
					AC::COL_MUNIT             => 'item',
					BC::COL_PRC_IDX           => 1,
					BC::COL_BS_PRC            => 100.0000,
					'discount'                => 5.0000,
					BC::COL_CUR_ID            => strtoupper(substr(SC::DEF_SITE_CURRENCY_ID, 0, 3)),
					'attributes'              => ['pack' => 'std', 'warranty_months' => 12],
					'notes'                   => 'Stable fixture (standard item).',
					BC::COL_PRD_SV_ID         => null,
				],
				[
					'code'                    => 'PSU-HOUR-SVC',
					'name'                    => 'Service By Hour',
					'status'                  => ProductStatus::Active->value,
					AC::COL_MUNIT             => 'hour',
					BC::COL_PRC_IDX           => 1,
					BC::COL_BS_PRC            => 250.0000,
					'discount'                => 0.0000,
					BC::COL_CUR_ID            => 'BRL',
					'attributes'              => ['billing' => 'time_based'],
					'notes'                   => 'Stable fixture (hourly service).',
					BC::COL_PRD_SV_ID         => null,
				],
			];

			foreach ($fixtures as $fx) {
				ProductServiceUnit::query()->updateOrCreate(['code' => $fx['code']], $fx);
				$usedCode[$fx['code']] = true;
				$usedName[$fx['name']] = true;
			}

			// ---- Helper closures ---------------------------------------------
			$unitsList = ['item', 'unit', 'box', 'package', 'hour', 'session', 'license', 'service'];
			$currencies = ['BRL', 'USD', 'EUR'];

			$uniqueCode = function (?string $hint = null) use (&$usedCode): string {
				$prefix = 'PSU-';
				$seed   = $hint ? Str::of($hint)->upper()->replace([' ', '/', '\\', '.', ','], '-')->substr(0, 8) : null;

				for ($i = 0; $i < 5; $i++) {
					$candidate = $prefix . ($seed ?: Str::upper(fake()->bothify('??'))) . '-' . Str::upper(fake()->bothify('###'));
					if (!isset($usedCode[$candidate]) && !ProductServiceUnit::withTrashed()->where('code', $candidate)->exists()) {
						$usedCode[$candidate] = true;
						return $candidate;
					}
				}

				do {
					$candidate = $prefix . Str::upper(fake()->bothify('??-####'));
				} while (isset($usedCode[$candidate]) || ProductServiceUnit::withTrashed()->where('code', $candidate)->exists());
				$usedCode[$candidate] = true;
				return $candidate;
			};

			$uniqueName = function (string $base) use (&$usedName): string {
				$try = $base;
				$suffix = 1;
				while (isset($usedName[$try]) || ProductServiceUnit::withTrashed()->where('name', $try)->exists()) {
					$try = $base . ' #' . $suffix;
					$suffix++;
				}
				$usedName[$try] = true;
				return $try;
			};

			$randStatus = fn(): string => fake()->randomElement([
				ProductStatus::Active->value,
				ProductStatus::Paused->value,
				ProductStatus::Inactive->value,
			]);

			$mkAttributes = function (): array {
				return fake()->randomElements([
					['color' => fake()->safeColorName()],
					['size' => fake()->randomElement(['S', 'M', 'L', 'XL'])],
					['region' => fake()->randomElement(['NA', 'EU', 'LATAM'])],
					['warranty_months' => fake()->numberBetween(0, 36)],
					['pack' => fake()->randomElement(['std', 'pro', 'ent'])],
				], fake()->numberBetween(1, 3));
			};

			// ---- Per-product units -------------------------------------------
			$products = ProductService::query()->select(['id', 'name'])->get();
			foreach ($products as $product) {
				$n = fake()->numberBetween($perProductMin, $perProductMax);
				for ($i = 1; $i <= $n; $i++) {
					$unitLabel  = fake()->randomElement($unitsList);
					$hint       = $product->name ? Str::slug($product->name) : null;
					$code       = $uniqueCode($hint);
					$baseName   = ($product->name ?: 'Product') . ' — ' . Str::title($unitLabel);
					$name       = $uniqueName($baseName);

					$price      = fake()->randomFloat(4, 10, 2500);
					$discount   = fake()->boolean(35) ? min($price, fake()->randomFloat(4, 1, $price * 0.3)) : 0.0;

					ProductServiceUnit::query()->create([
						'product_service_id'      => $product->id,
						'name'                    => $name,
						'code'                    => $code,
						'status'                  => $randStatus(),
						AC::COL_MUNIT             => $unitLabel,
						BC::COL_PRC_IDX           => $i,
						BC::COL_BS_PRC            => $price,
						'discount'                => $discount,
						BC::COL_CUR_ID            => fake()->randomElement($currencies),
						'attributes'              => $mkAttributes(),
						'notes'                   => fake()->optional(0.3)->sentence(10),
					]);
				}
			}

			// ---- Orphan units (no product linked) -----------------------------
			for ($j = 0; $j < $orphans; $j++) {
				$unitLabel = fake()->randomElement($unitsList);
				$code      = $uniqueCode('orphan');
				$name      = $uniqueName('Generic Unit — ' . Str::title($unitLabel));

				$price     = fake()->randomFloat(4, 5, 800);
				$discount  = fake()->boolean(25) ? min($price, fake()->randomFloat(4, 1, $price * 0.25)) : 0.0;

				ProductServiceUnit::query()->create([
					'product_service_id'      => null,
					'name'                    => $name,
					'code'                    => $code,
					'status'                  => $randStatus(),
					AC::COL_MUNIT             => $unitLabel,
					BC::COL_PRC_IDX           => 1,
					BC::COL_BS_PRC            => $price,
					'discount'                => $discount,
					BC::COL_CUR_ID            => fake()->randomElement(['BRL', 'USD', 'EUR']),
					'attributes'              => $mkAttributes(),
					'notes'                   => fake()->optional(0.3)->sentence(10),
				]);
			}

			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			Log::error(self::class . ' failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
			throw $e;
		}
	}
}
