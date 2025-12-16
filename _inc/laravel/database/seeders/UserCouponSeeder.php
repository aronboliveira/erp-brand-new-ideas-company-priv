<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Models\UserCoupon;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};

final class UserCouponSeeder extends Seeder
{
	private const MAX_COUPONS_PER_USER = 8;
	private const MIN_COUPONS_PER_USER = 0;

	public function run(): void
	{
		if (!Schema::hasTable(DC::TABLE_USR_CPNS)) {
			$this->command?->warn('Table user_coupons missing. Aborting.');
			return;
		}

		// Get all users with raw query for performance
		$users = DB::select('SELECT id FROM ' . DC::TABLE_USERS . ' LIMIT 10000');
		if (empty($users)) {
			$this->command?->warn('No users found. Aborting UserCouponSeeder.');
			return;
		}

		$userIds = array_column($users, 'id');

		// Get all coupons with their limits
		$coupons = DB::select('SELECT id, `limit` FROM ' . DC::TABLE_COUPONS . ' LIMIT 5000');
		if (empty($coupons)) {
			$this->command?->warn('No coupons found. Aborting UserCouponSeeder.');
			return;
		}

		$couponData = [];
		foreach ($coupons as $c) {
			$couponData[$c->id] = (int) ($c->limit ?? 1);
		}
		$couponIds = array_keys($couponData);

		// Get orders by user (raw query for performance)
		$ordersByUser = [];
		if (Schema::hasTable(DC::TABLE_ORDERS)) {
			$orders = DB::select(
				'SELECT id, ' . UC::COL_USER_ID . ' FROM ' . DC::TABLE_ORDERS .
					(Schema::hasColumn(DC::TABLE_ORDERS, 'deleted_at') ? ' WHERE deleted_at IS NULL' : '') .
					' LIMIT 20000'
			);

			foreach ($orders as $order) {
				$uid = $order->{UC::COL_USER_ID} ?? null;
				if ($uid) {
					$ordersByUser[$uid][] = $order->id;
				}
			}
		}

		// Track existing combinations to avoid duplicates
		$existing = DB::select('SELECT user, coupon, `order` FROM ' . DC::TABLE_USR_CPNS);
		$existingKeys = [];
		foreach ($existing as $row) {
			$key = $this->makeKey($row->user, $row->coupon, $row->order);
			$existingKeys[$key] = true;
		}

		// Track usage per coupon and per user-coupon pair
		$globalUsage = [];
		$userUsage = [];
		foreach ($existing as $row) {
			$cid = $row->coupon;
			$uid = $row->user;
			$globalUsage[$cid] = ($globalUsage[$cid] ?? 0) + 1;
			$userUsage["{$uid}|{$cid}"] = ($userUsage["{$uid}|{$cid}"] ?? 0) + 1;
		}

		$this->command?->info('Distributing coupons to users...');

		$created = 0;
		$skipped = 0;
		$now = Carbon::now();

		foreach ($userIds as $userId) {
			// Calculate how many coupons this user gets using quadratic distribution
			$couponsForUser = $this->calculateCouponsForUser();

			if ($couponsForUser === 0) {
				continue;
			}

			// Shuffle coupons for random selection
			$shuffledCoupons = $couponIds;
			shuffle($shuffledCoupons);

			$assignedToUser = 0;

			foreach ($shuffledCoupons as $couponId) {
				if ($assignedToUser >= $couponsForUser) {
					break;
				}

				$limit = $couponData[$couponId];

				// Check global limit
				if ($limit > 0 && ($globalUsage[$couponId] ?? 0) >= $limit) {
					continue;
				}

				// Check user-specific limit
				if ($limit > 0 && ($userUsage["{$userId}|{$couponId}"] ?? 0) >= $limit) {
					continue;
				}

				// Optionally attach to an order
				$orderId = null;
				if (
					!empty($ordersByUser[$userId]) &&
					fake()->boolean(55)
				) {
					$orderId = fake()->randomElement($ordersByUser[$userId]);
				}

				$key = $this->makeKey($userId, $couponId, $orderId);
				if (isset($existingKeys[$key])) {
					$skipped++;
					continue;
				}

				try {
					$createdAt = $now->copy()->subDays(fake()->numberBetween(0, 30));
					(new \Symfony\Component\Console\Output\ConsoleOutput)->writeln("Creating UserCoupon: user={$userId}, coupon={$couponId}, order={$orderId}");
					UserCoupon::create([
						'user' => $userId,
						'coupon' => $couponId,
						'order' => $orderId,
						'created_at' => $createdAt,
						'updated_at' => $createdAt->copy()->addMinutes(fake()->numberBetween(0, 1440)),
						DC::COL_TABLE_CREATOR => fake()->boolean(70) ? fake()->randomElement($userIds) : null,
						DC::COL_TABLE_UPDATER => fake()->boolean(40) ? fake()->randomElement($userIds) : null,
					]);

					$existingKeys[$key] = true;
					$globalUsage[$couponId] = ($globalUsage[$couponId] ?? 0) + 1;
					$userUsage["{$userId}|{$couponId}"] = ($userUsage["{$userId}|{$couponId}"] ?? 0) + 1;

					$assignedToUser++;
					$created++;

					if ($created % 500 === 0) {
						$this->command?->info("Created {$created} user-coupon links...");
					}
				} catch (\Exception $e) {
					Log::warning('UserCouponSeeder failed: ' . $e->getMessage());
					$skipped++;
					continue;
				}
			}
		}

		$this->command?->info("UserCouponSeeder completed: {$created} links created, {$skipped} skipped.");
	}

	private function calculateCouponsForUser(): int
	{
		$range = self::MAX_COUPONS_PER_USER - self::MIN_COUPONS_PER_USER + 1;
		$quadraticRange = $range ** 2;

		// Use quadratic distribution favoring lower numbers
		$random = random_int(1, $quadraticRange);
		$coupons = self::MAX_COUPONS_PER_USER - ((int) sqrt($random) - 1);

		return max(self::MIN_COUPONS_PER_USER, min(self::MAX_COUPONS_PER_USER, $coupons));
	}

	private function makeKey(string $userId, string $couponId, ?string $orderId): string
	{
		return $userId . '|' . $couponId . '|' . ($orderId ?? 'NULL');
	}
}
