<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Support\Str;

/**
 * Seeds all records needed for a populated Account Dashboard:
 * - Product service categories (income + expense)
 * - Product service units
 * - Additional customers (total ≥ 5)
 * - Additional vendors (total ≥ 5)
 * - Invoices (6)
 * - Bills (5)
 * - Goals (4 with is_display=1)
 *
 * Uses raw DB::table inserts to avoid Eloquent boot/relation side-effects.
 * Idempotent — checks existing counts before inserting.
 */
class DashboardSeeder extends Seeder
{
	public function run(): void
	{
		$userRow = DB::table('users')->where('email', 'suporte@prestech.com.br')->first();
		if (!$userRow) {
			$this->command?->error('Test user suporte@prestech.com.br not found.');
			return;
		}
		// Super admin / company types use their own ID as creatorId
		$cid = in_array($userRow->type, ['super admin', 'company'], true)
			? $userRow->id
			: $userRow->created_by;
		$now = Carbon::now();
		$out = new \Symfony\Component\Console\Output\ConsoleOutput();

		// ── 1. Product Service Categories ───────────────────────────────
		$existingCats = DB::table('product_service_categories')
			->where('created_by', $cid)->count();

		if ($existingCats === 0) {
			$cats = [
				['name' => 'Consultoria',     'type' => 'income',  'color' => '2ecc71'],
				['name' => 'Licenciamento',   'type' => 'income',  'color' => '3498db'],
				['name' => 'Suporte Técnico', 'type' => 'income',  'color' => '9b59b6'],
				['name' => 'Infraestrutura',  'type' => 'expense', 'color' => 'e74c3c'],
				['name' => 'Pessoal',         'type' => 'expense', 'color' => 'e67e22'],
				['name' => 'Software',        'type' => 'expense', 'color' => 'f1c40f'],
			];
			foreach ($cats as $cat) {
				DB::table('product_service_categories')->insert([
					'id'         => Str::uuid()->toString(),
					'name'       => $cat['name'],
					'type'       => $cat['type'],
					'type_label' => ucfirst($cat['type']),
					'color'      => $cat['color'],
					'is_active'  => 1,
					'created_by' => $cid,
					'created_at' => $now,
					'updated_at' => $now,
				]);
			}
			$out->writeln("Categories seeded: " . count($cats));
		}

		// ── 2. Product Service Units ────────────────────────────────────
		$existingUnits = DB::table('product_service_units')
			->where('created_by', $cid)->count();

		if ($existingUnits === 0) {
			$units = [
				['name' => 'Hora',    'code' => 'hr'],
				['name' => 'Unidade', 'code' => 'un'],
				['name' => 'Mês',     'code' => 'mo'],
			];
			foreach ($units as $u) {
				DB::table('product_service_units')->insert([
					'id'         => Str::uuid()->toString(),
					'name'       => $u['name'],
					'code'       => $u['code'],
					'status'     => 'active',
					'created_by' => $cid,
					'created_at' => $now,
					'updated_at' => $now,
				]);
			}
			$out->writeln("Units seeded: " . count($units));
		}

		// ── 3. Additional Customers (total ≥ 5) ────────────────────────
		$existingCusts = DB::table('customers')
			->where('created_by', $cid)->count();
		$needCusts = max(0, 5 - $existingCusts);

		$customerNames = [
			'Tech Solutions Ltda',
			'Inovação Digital SA',
			'Grupo Alpha Engenharia',
			'Retail Plus Comércio',
			'Logística Express ME',
		];

		$customerIds = DB::table('customers')
			->where('created_by', $cid)->pluck('id')->toArray();

		for ($i = 0; $i < $needCusts; $i++) {
			$custId = Str::uuid()->toString();
			DB::table('customers')->insert([
				'id'         => $custId,
				'name'       => $customerNames[$existingCusts + $i] ?? "Cliente Demo " . ($existingCusts + $i + 1),
				'email'      => 'cliente' . ($existingCusts + $i + 1) . '@demo.prestech.com.br',
				'created_by' => $cid,
				'created_at' => $now,
				'updated_at' => $now,
			]);
			$customerIds[] = $custId;
		}
		if ($needCusts > 0) $out->writeln("Customers added: {$needCusts}");

		// ── 4. Additional Vendors (total ≥ 5) ──────────────────────────
		$existingVends = DB::table('vendors')
			->where('created_by', $cid)->count();
		$needVends = max(0, 5 - $existingVends);

		$vendorNames = [
			'Cloud Hosting Brasil',
			'SecureNet Cybersecurity',
			'DataCenter SP',
			'Office Supplies Pro',
			'Telecom Solutions SA',
		];

		$vendorIds = DB::table('vendors')
			->where('created_by', $cid)->pluck('id')->toArray();

		for ($i = 0; $i < $needVends; $i++) {
			$vendId = Str::uuid()->toString();
			DB::table('vendors')->insert([
				'id'         => $vendId,
				'name'       => $vendorNames[$existingVends + $i] ?? "Fornecedor Demo " . ($existingVends + $i + 1),
				'email'      => 'fornecedor' . ($existingVends + $i + 1) . '@demo.prestech.com.br',
				'created_by' => $cid,
				'created_at' => $now,
				'updated_at' => $now,
			]);
			$vendorIds[] = $vendId;
		}
		if ($needVends > 0) $out->writeln("Vendors added: {$needVends}");

		// ── 5. Invoices ─────────────────────────────────────────────────
		$existingInvs = DB::table('invoices')
			->where('created_by', $cid)->count();

		if ($existingInvs === 0 && !empty($customerIds)) {
			$catIncome = DB::table('product_service_categories')
				->where('created_by', $cid)
				->where('type', 'income')
				->pluck('id')->first();

			$invoices = [
				['desc' => 'Consultoria ERP - Fase 1',    'amount' => 15000.00, 'status' => 3],
				['desc' => 'Licença Anual Software',      'amount' => 8500.00,  'status' => 2],
				['desc' => 'Implementação módulo RH',     'amount' => 22000.00, 'status' => 3],
				['desc' => 'Treinamento equipe TI',       'amount' => 6800.00,  'status' => 1],
				['desc' => 'Suporte técnico mensal',      'amount' => 3200.00,  'status' => 3],
				['desc' => 'Migração de dados legados',   'amount' => 12500.00, 'status' => 2],
			];

			foreach ($invoices as $idx => $inv) {
				$issueDate = $now->copy()->subDays(rand(5, 60));
				DB::table('invoices')->insert([
					'id'             => Str::uuid()->toString(),
					'invoice_id'     => 'INV-' . str_pad($idx + 1, 5, '0', STR_PAD_LEFT),
					'customer_id'    => $customerIds[array_rand($customerIds)],
					'category_id'    => $catIncome,
					'amount'         => $inv['amount'],
					'discount'       => 0,
					'description'    => $inv['desc'],
					'status'         => $inv['status'],
					'payment_status' => $inv['status'] === 3 ? 'paid' : 'unpaid',
					'issue_date'     => $issueDate->format('Y-m-d'),
					'send_date'      => $issueDate->copy()->addDays(1)->format('Y-m-d'),
					'due_date'       => $issueDate->copy()->addDays(30)->format('Y-m-d'),
					'created_by'     => $cid,
					'created_at'     => $issueDate,
					'updated_at'     => $now,
				]);
			}
			$out->writeln("Invoices seeded: " . count($invoices));
		}

		// ── 6. Bills ────────────────────────────────────────────────────
		$existingBills = DB::table('bills')
			->where('created_by', $cid)->count();

		if ($existingBills === 0 && !empty($vendorIds)) {
			$catExpense = DB::table('product_service_categories')
				->where('created_by', $cid)
				->where('type', 'expense')
				->pluck('id')->first();

			$orderId = DB::table('orders')->pluck('id')->first();

			$bills = [
				['desc' => 'Hospedagem servidores cloud',   'amount' => 4500.00,  'status' => 3],
				['desc' => 'Licenças Microsoft 365',        'amount' => 2800.00,  'status' => 2],
				['desc' => 'Folha de pagamento Fev/2026',   'amount' => 35000.00, 'status' => 1],
				['desc' => 'Aluguel escritório',            'amount' => 6500.00,  'status' => 3],
				['desc' => 'Material de escritório',        'amount' => 1200.00,  'status' => 2],
			];

			foreach ($bills as $idx => $bill) {
				$billDate = $now->copy()->subDays(rand(5, 45));
				DB::table('bills')->insert([
					'id'          => Str::uuid()->toString(),
					'bill_id'     => 'BILL-' . str_pad($idx + 1, 5, '0', STR_PAD_LEFT),
					'vendor_id'   => $vendorIds[array_rand($vendorIds)],
					'category_id' => $catExpense,
					'order_id'    => $orderId,
					'amount'      => $bill['amount'],
					'discount'    => 0,
					'description' => $bill['desc'],
					'status'      => $bill['status'],
					'payment_status' => $bill['status'] === 3 ? 'paid' : 'unpaid',
					'bill_date'   => $billDate->format('Y-m-d'),
					'send_date'   => $billDate->copy()->addDays(1)->format('Y-m-d'),
					'due_date'    => $billDate->copy()->addDays(30)->format('Y-m-d'),
					'created_by'  => $cid,
					'created_at'  => $billDate,
					'updated_at'  => $now,
				]);
			}
			$out->writeln("Bills seeded: " . count($bills));
		}

		// ── 7. Goals (is_display = 1) ───────────────────────────────────
		$existingGoals = DB::table('goals')
			->where('created_by', $cid)->count();

		if ($existingGoals === 0) {
			$goals = [
				['name' => 'Faturamento Q1 2026',        'type' => 'Invoice', 'amount' => 100000.00],
				['name' => 'Redução de custos operação',  'type' => 'Bill',    'amount' => 50000.00],
				['name' => 'Meta receita recorrente',     'type' => 'Revenue', 'amount' => 80000.00],
				['name' => 'Controle de pagamentos',      'type' => 'Payment', 'amount' => 45000.00],
			];

			foreach ($goals as $goal) {
				$from = $now->copy()->startOfYear();
				$to   = $now->copy()->endOfQuarter();
				DB::table('goals')->insert([
					'id'          => Str::uuid()->toString(),
					'name'        => $goal['name'],
					'type'        => $goal['type'],
					'from'        => $from->format('Y-m-d'),
					'to'          => $to->format('Y-m-d'),
					'amount'      => $goal['amount'],
					'is_display'  => 1,
					'description' => 'Meta demonstrativa para dashboard',
					'created_by'  => $cid,
					'created_at'  => $now,
					'updated_at'  => $now,
				]);
			}
			$out->writeln("Goals seeded: " . count($goals));
		}

		$out->writeln('DashboardSeeder completed.');
	}
}
