<?php

namespace Database\Seeders;

use App\Models\{BankAccount, Customer, Payment, Revenue, User, Vendor};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

/**
 * Seeds mock data for the Account Statement report.
 *
 * Usage:
 *   php artisan db:seed --class=AccountStatementSeeder
 */
class AccountStatementSeeder extends Seeder
{
	public function run(): void
	{
		$user = User::where('email', 'suporte@prestech.com.br')->first();

		if (!$user) {
			$this->command?->error('Test user suporte@prestech.com.br not found.');
			return;
		}

		$creatorId = $user->creatorId();

		// ── Customer ────────────────────────────────────────────────
		$customer = Customer::firstOrCreate(
			['email' => 'cliente.demo@prestech.com.br', 'created_by' => $creatorId],
			[
				'name'       => 'Cliente Demo Ltda',
				'contact'    => '+55 11 98888-0001',
				'is_active'  => true,
				'lang'       => 'pt-br',
				'created_by' => $creatorId,
			]
		);

		// ── Vendor ─────────────────────────────────────────────────────
		$vendor = Vendor::firstOrCreate(
			['email' => 'fornecedor.demo@prestech.com.br', 'created_by' => $creatorId],
			[
				'name'       => 'Fornecedor Demo SA',
				'contact'    => '+55 11 97777-0001',
				'is_active'  => true,
				'lang'       => 'pt-br',
				'created_by' => $creatorId,
			]
		);

		$this->command?->info("Customer: {$customer->id}, Vendor: {$vendor->id}");

		// ── Bank Account ───────────────────────────────────────────────
		$bank = BankAccount::firstOrCreate(
			['holder_name' => 'Nova Prestech Ltda', 'created_by' => $creatorId],
			[
				'account_number' => '00012345-6',
				'bank_name'      => 'Banco do Brasil',
				'bank_address'   => 'Brasília, DF',
				'holder_address' => 'São Paulo, SP',
				'contact_number' => '+55 11 99999-0001',
				'opening_balance' => 50000.00,
				'current_balance' => 50000.00,
				'is_active'       => true,
				'created_by'      => $creatorId,
			]
		);

		$cashAccount = BankAccount::firstOrCreate(
			['holder_name' => 'Cash', 'created_by' => $creatorId],
			[
				'account_number'  => 'CASH-001',
				'bank_name'       => 'Cash',
				'opening_balance' => 10000.00,
				'current_balance' => 10000.00,
				'is_active'       => true,
				'created_by'      => $creatorId,
			]
		);

		$this->command?->info("Bank accounts ready (IDs: {$bank->id}, {$cashAccount->id})");

		// ── Revenues ───────────────────────────────────────────────────
		$revenueData = [
			['amount' => 12500.00, 'description' => 'Cloud infrastructure consulting – Jan',  'date' => now()->subMonths(4)->startOfMonth()->addDays(4)],
			['amount' => 8750.00,  'description' => 'Cybersecurity audit – Feb',              'date' => now()->subMonths(3)->startOfMonth()->addDays(10)],
			['amount' => 15000.00, 'description' => 'ERP implementation phase 1 – Mar',       'date' => now()->subMonths(2)->startOfMonth()->addDays(7)],
			['amount' => 6200.00,  'description' => 'Network monitoring subscription – Mar',  'date' => now()->subMonths(2)->startOfMonth()->addDays(18)],
			['amount' => 9800.00,  'description' => 'IT helpdesk support contract – Apr',     'date' => now()->subMonths(1)->startOfMonth()->addDays(3)],
			['amount' => 22000.00, 'description' => 'Server migration project – Apr',         'date' => now()->subMonths(1)->startOfMonth()->addDays(15)],
			['amount' => 4500.00,  'description' => 'SSL certificate renewals – May',         'date' => now()->startOfMonth()->addDays(1)],
			['amount' => 18000.00, 'description' => 'Data backup & recovery solution – May',  'date' => now()->startOfMonth()->addDays(9)],
		];

		$created = 0;
		foreach ($revenueData as $rev) {
			$exists = DB::table('revenues')
				->where('description', $rev['description'])
				->where('created_by', $creatorId)
				->exists();
			if ($exists) continue;

			DB::table('revenues')->insert([
				'id'          => Str::uuid()->toString(),
				'date'        => $rev['date'],
				'amount'      => $rev['amount'],
				'account_id'  => $bank->id,
				'customer_id' => $customer->id,
				'category_id' => null,
				'description' => $rev['description'],
				'reference'   => 'SEED-REV-' . str_pad(++$created, 3, '0', STR_PAD_LEFT),
				'status'      => 'approved',
				'created_by'  => $creatorId,
				'created_at'  => now(),
				'updated_at'  => now(),
			]);
		}

		$this->command?->info("Revenues seeded: {$created}");

		// ── Payments ───────────────────────────────────────────────────
		$paymentData = [
			['amount' => 3200.00,  'description' => 'AWS hosting fees – Jan',       'date' => now()->subMonths(4)->startOfMonth()->addDays(8)],
			['amount' => 1800.00,  'description' => 'Software licenses – Feb',      'date' => now()->subMonths(3)->startOfMonth()->addDays(5)],
			['amount' => 4500.00,  'description' => 'Staff salaries – Mar',         'date' => now()->subMonths(2)->startOfMonth()->addDays(1)],
			['amount' => 950.00,   'description' => 'Office supplies – Mar',        'date' => now()->subMonths(2)->startOfMonth()->addDays(20)],
			['amount' => 2750.00,  'description' => 'Azure cloud subscription – Apr', 'date' => now()->subMonths(1)->startOfMonth()->addDays(6)],
			['amount' => 1200.00,  'description' => 'Marketing campaign – Apr',     'date' => now()->subMonths(1)->startOfMonth()->addDays(22)],
			['amount' => 5600.00,  'description' => 'Equipment purchase – May',     'date' => now()->startOfMonth()->addDays(3)],
		];

		$created = 0;
		foreach ($paymentData as $pay) {
			$exists = DB::table('payments')
				->where('description', $pay['description'])
				->where('created_by', $creatorId)
				->exists();
			if ($exists) continue;

			DB::table('payments')->insert([
				'id'               => Str::uuid()->toString(),
				'date'             => $pay['date'],
				'amount'           => $pay['amount'],
				'account_id'       => $cashAccount->id,
				'vendor_id'        => $vendor->id,
				'category_id'      => null,
				'chart_account_id' => null,
				'description'      => $pay['description'],
				'reference'        => 'SEED-PAY-' . str_pad(++$created, 3, '0', STR_PAD_LEFT),
				'status'           => 'approved',
				'created_by'       => $creatorId,
				'created_at'       => now(),
				'updated_at'       => now(),
			]);
		}

		$this->command?->info("Payments seeded: {$created}");
		Log::info('AccountStatementSeeder completed', ['creator_id' => $creatorId]);
	}
}
