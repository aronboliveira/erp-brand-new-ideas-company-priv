<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;

/**
 * ContentValidationSeeder
 *
 * Ensures every table backing a content-validated route contains at
 * least a few mock rows so the curl content tests report
 * CONTENT-PASS instead of "structure exists but EMPTY".
 *
 * This seeder is idempotent: it only inserts when a table has 0 rows and
 * wraps everything in individual try/catch blocks so one failure does
 * not block the rest.
 *
 * Run standalone:
 *   php artisan db:seed --class=ContentValidationSeeder
 */
class ContentValidationSeeder extends Seeder
{
    /** Minimum # of rows to create per table. */
    private const MIN_ROWS = 3;

    public function run(): void
    {
        Model::unguard();
        $this->command?->info('ContentValidationSeeder: starting…');

        // ── Ensure prerequisite user types exist ──────────────
        $this->ensureUserTypes();

        // ── Seed each table that might be empty ──────────────
        $tables = $this->tableDefinitions();

        $created = 0;
        $skipped = 0;

        foreach ($tables as $table => $builder) {
            try {
                if (!Schema::hasTable($table)) {
                    $this->command?->warn("  ✗ Table '{$table}' does not exist — skipped");
                    $skipped++;
                    continue;
                }
                $count = DB::table($table)->count();
                if ($count > 0) {
                    $skipped++;
                    continue;
                }
                $builder();
                $newCount = DB::table($table)->count();
                $this->command?->info("  ✓ {$table}: created {$newCount} rows");
                $created++;
            } catch (\Throwable $e) {
                $this->command?->error("  ✗ {$table}: " . Str::limit($e->getMessage(), 200));
                Log::warning("ContentValidationSeeder: {$table} failed", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->command?->info("ContentValidationSeeder: done ({$created} tables populated, {$skipped} skipped)");
    }

    // ──────────────────────────────────────────────────────────
    //  Prerequisite: make sure the required user types exist
    // ──────────────────────────────────────────────────────────
    private function ensureUserTypes(): void
    {
        $adminId  = DB::table('users')->where('type', 'admin')->value('id');
        $compId   = DB::table('users')->where('type', 'company')->value('id');
        $creatorId = $adminId ?? $compId ?? DB::table('users')->value('id');

        $typesToEnsure = ['client', 'customer', 'hr', 'vendor'];
        foreach ($typesToEnsure as $type) {
            if (DB::table('users')->where('type', $type)->exists()) continue;
            try {
                $id = (string) Str::uuid();
                DB::table('users')->insert([
                    'id'                => $id,
                    'name'              => "Mock {$type} User",
                    'email'             => "mock_{$type}_" . Str::random(6) . '@test.local',
                    'password'          => bcrypt('Admin@1234'),
                    'type'              => $type,
                    'lang'              => 'en',
                    'is_active'         => 1,
                    'delete_status'     => 0,
                    'created_by'        => $creatorId ?? $id,
                    'email_verified_at' => now(),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
                $this->command?->info("  ✓ Created mock '{$type}' user ({$id})");
            } catch (\Throwable $e) {
                $this->command?->warn("  ✗ Could not create '{$type}' user: " . Str::limit($e->getMessage(), 120));
            }
        }
    }

    /**
     * Returns [table_name => closure] where each closure inserts
     * MIN_ROWS of mock data.
     */
    private function tableDefinitions(): array
    {
        // Resolve common FK references once
        $adminId    = DB::table('users')->where('type', 'admin')->value('id');
        $companyId  = DB::table('users')->where('type', 'company')->value('id');
        $creatorId  = $adminId ?? $companyId ?? DB::table('users')->value('id');
        $branchId   = DB::table('branches')->value('id');
        $deptId     = DB::table('departments')->value('id');
        $desigId    = DB::table('designations')->value('id');
        $empIds     = DB::table('employees')->limit(self::MIN_ROWS)->pluck('id')->all();
        $goalTypeId = DB::table('goal_types')->value('id');
        $goalId     = DB::table('goals')->value('id');
        $planId     = DB::table('plans')->value('id');
        $customerId = DB::table('customers')->value('id');
        $clientUserId = DB::table('users')->where('type', 'client')->value('id');
        $vendorUserId = DB::table('users')->where('type', 'vendor')->value('id');

        $uuid = fn () => (string) Str::uuid();
        $now  = now();

        return [

            // ── Appraisals ─────────────────────────────────
            'appraisals' => function () use ($uuid, $now, $creatorId, $companyId, $branchId, $empIds) {
                $appraiserId = DB::table('users')->whereIn('type', ['admin', 'hr', 'customer'])->value('id') ?? $creatorId;
                foreach (array_slice($empIds, 0, self::MIN_ROWS) as $empId) {
                    DB::table('appraisals')->insert([
                        'id'                  => $uuid(),
                        'company'             => $companyId,
                        'branch'              => $branchId,
                        'employee'            => $empId,
                        'appraiser'           => $appraiserId,
                        'rating'              => (string) rand(3, 5),
                        'attendance'          => rand(60, 100),
                        'administration'      => rand(60, 100),
                        'customer_experience' => rand(60, 100),
                        'integrity'           => rand(60, 100),
                        'marketing'           => rand(60, 100),
                        'professionalism'     => rand(60, 100),
                        'appraisal_date'      => $now->subDays(rand(1, 90))->toDateString(),
                        'status'              => 'completed',
                        'remark'              => 'Auto-generated for content validation',
                        'created_by'          => $creatorId,
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ]);
                }
            },

            // ── Goal Trackings ─────────────────────────────
            'goal_trackings' => function () use ($uuid, $now, $creatorId, $companyId, $branchId, $deptId, $goalTypeId, $goalId) {
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('goal_trackings')->insert([
                        'id'                 => $uuid(),
                        'company'            => $companyId,
                        'branch'             => $branchId,
                        'department'         => $deptId,
                        'goal_type'          => $goalTypeId,
                        'goal'               => $goalId,
                        'start_date'         => $now->copy()->subMonths(3)->toDateString(),
                        'end_date'           => $now->copy()->addMonths(3)->toDateString(),
                        'subject'            => "Goal Tracking #{$i} (mock)",
                        'rating'             => (string) rand(1, 5),
                        'target_achievement' => rand(30, 100) . '%',
                        'description'        => 'Auto-generated for content validation',
                        'status'             => 'active',
                        'progress'           => rand(10, 95),
                        'priority'           => ['low', 'medium', 'high'][rand(0, 2)],
                        'created_by'         => $creatorId,
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ]);
                }
            },

            // ── Plan Requests ──────────────────────────────
            'plan_requests' => function () use ($uuid, $now, $creatorId, $planId, $clientUserId, $vendorUserId) {
                $eligibleUsers = array_filter([$clientUserId, $vendorUserId, $creatorId]);
                foreach (array_slice($eligibleUsers, 0, self::MIN_ROWS) as $userId) {
                    DB::table('plan_requests')->insert([
                        'id'         => $uuid(),
                        'user_id'    => $userId,
                        'plan_id'    => $planId,
                        'duration'   => 'monthly',
                        'notes'      => 'Auto-generated for content validation',
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            },

            // ── Supports ───────────────────────────────────
            'supports' => function () use ($uuid, $now, $creatorId, $clientUserId) {
                $senderIds = array_filter([$clientUserId, $creatorId]);
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('supports')->insert(array_filter([
                        'id'          => $uuid(),
                        'subject'     => "Support Ticket #{$i} (mock)",
                        'user'        => $senderIds[array_rand($senderIds)],
                        'priority'    => ['low', 'medium', 'high', 'critical'][rand(0, 3)],
                        'status'      => ['open', 'on_hold', 'closed'][rand(0, 2)],
                        'description' => 'Auto-generated for content validation',
                        'ticket_code' => 'MOCK-' . strtoupper(Str::random(8)),
                        'created_by'  => $creatorId,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]));
                }
            },

            // ── Assets ─────────────────────────────────────
            'assets' => function () use ($uuid, $now, $creatorId, $companyId, $empIds) {
                $types = ['Laptop', 'Monitor', 'Keyboard', 'Headset', 'Mouse'];
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('assets')->insert(array_filter([
                        'id'             => $uuid(),
                        'name'           => $types[$i % count($types)] . " #{$i}",
                        'amount'         => rand(500, 5000),
                        'purchase_date'  => $now->copy()->subDays(rand(30, 365))->toDateString(),
                        'description'    => 'Auto-generated for content validation',
                        'company_id'     => $companyId,
                        'employee_id'    => $empIds[$i % count($empIds)] ?? null,
                        'created_by'     => $creatorId,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]));
                }
            },

            // ── Bank Transfers ─────────────────────────────
            'bank_transfers' => function () use ($uuid, $now, $creatorId) {
                $bankIds = DB::table('bank_accounts')->limit(2)->pluck('id')->all();
                if (count($bankIds) < 2) return;
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('bank_transfers')->insert(array_filter([
                        'id'              => $uuid(),
                        'from_account'    => $bankIds[0],
                        'to_account'      => $bankIds[1],
                        'amount'          => rand(100, 10000),
                        'date'            => $now->copy()->subDays(rand(1, 60))->toDateString(),
                        'description'     => "Mock transfer #{$i}",
                        'created_by'      => $creatorId,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ]));
                }
            },

            // ── Budgets ────────────────────────────────────
            'budgets' => function () use ($uuid, $now, $creatorId, $companyId) {
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('budgets')->insert(array_filter([
                        'id'         => $uuid(),
                        'name'       => "Budget #{$i} (" . date('Y') . ")",
                        'from'       => $now->copy()->startOfYear()->toDateString(),
                        'to'         => $now->copy()->endOfYear()->toDateString(),
                        'period'     => 'monthly',
                        'company_id' => $companyId,
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            },

            // ── Expenses ───────────────────────────────────
            'expenses' => function () use ($uuid, $now, $creatorId, $companyId) {
                $categories = ['Office Supplies', 'Travel', 'Meals', 'Software', 'Equipment'];
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    $bankId = DB::table('bank_accounts')->inRandomOrder()->value('id');
                    DB::table('expenses')->insert(array_filter([
                        'id'             => $uuid(),
                        'amount'         => rand(50, 2000),
                        'date'           => $now->copy()->subDays(rand(1, 90))->toDateString(),
                        'category_id'    => 0,
                        'description'    => "{$categories[$i % count($categories)]} expense (mock)",
                        'account_id'     => $bankId,
                        'created_by'     => $creatorId,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]));
                }
            },

            // ── Coupons ────────────────────────────────────
            'coupons' => function () use ($uuid, $now, $creatorId) {
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('coupons')->insert(array_filter([
                        'id'             => $uuid(),
                        'name'           => "MOCK" . rand(1000, 9999),
                        'code'           => strtoupper(Str::random(10)),
                        'discount'       => rand(5, 50),
                        'limit'          => rand(10, 100),
                        'description'    => 'Auto-generated for content validation',
                        'is_active'      => 1,
                        'created_by'     => $creatorId,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]));
                }
            },

            // ── Orders ─────────────────────────────────────
            'orders' => function () use ($uuid, $now, $creatorId, $planId) {
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('orders')->insert(array_filter([
                        'id'            => $uuid(),
                        'order_id'      => 'ORD-' . strtoupper(Str::random(8)),
                        'name'          => "Mock Order #{$i}",
                        'plan_id'       => $planId,
                        'price'         => rand(10, 500),
                        'payment_type'  => 'manual',
                        'payment_status' => 'succeeded',
                        'receipt'       => null,
                        'user_id'       => $creatorId,
                        'created_by'    => $creatorId,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ]));
                }
            },

            // ── Proposals ──────────────────────────────────
            'proposals' => function () use ($uuid, $now, $creatorId, $customerId) {
                if (!$customerId) return;
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('proposals')->insert(array_filter([
                        'id'            => $uuid(),
                        'proposal_id'   => rand(1, 99999),
                        'customer_id'   => $customerId,
                        'issue_date'    => $now->copy()->subDays(rand(1, 30))->toDateString(),
                        'send_date'     => null,
                        'category_id'   => 0,
                        'status'        => rand(0, 4),
                        'discount_apply' => 0,
                        'created_by'    => $creatorId,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ]));
                }
            },

            // ── Custom Fields ──────────────────────────────
            'custom_fields' => function () use ($uuid, $now, $creatorId) {
                $modules = ['lead', 'deal', 'project', 'invoice', 'employee'];
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('custom_fields')->insert(array_filter([
                        'id'         => $uuid(),
                        'name'       => "Mock Field #{$i}",
                        'type'       => 'text',
                        'module'     => $modules[$i % count($modules)],
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            },

            // ── Form Builders ──────────────────────────────
            'form_builders' => function () use ($uuid, $now, $creatorId) {
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('form_builders')->insert(array_filter([
                        'id'           => $uuid(),
                        'name'         => "Mock Form #{$i}",
                        'module'       => 'lead',
                        'is_active'    => 1,
                        'created_by'   => $creatorId,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]));
                }
            },

            // ── Trainers ───────────────────────────────────
            'trainers' => function () use ($uuid, $now, $creatorId, $branchId) {
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('trainers')->insert(array_filter([
                        'id'           => $uuid(),
                        'branch'       => $branchId,
                        'firstname'    => "Trainer{$i}",
                        'lastname'     => 'Mock',
                        'contact'      => '1234567890',
                        'email'        => "trainer_mock_{$i}_" . Str::random(4) . "@test.local",
                        'expertise'    => 'General',
                        'created_by'   => $creatorId,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]));
                }
            },

            // ── Transfers ──────────────────────────────────
            'transfers' => function () use ($uuid, $now, $creatorId, $empIds, $branchId, $deptId) {
                if (empty($empIds)) return;
                $branches = DB::table('branches')->limit(2)->pluck('id')->all();
                for ($i = 0; $i < min(self::MIN_ROWS, count($empIds)); $i++) {
                    DB::table('transfers')->insert(array_filter([
                        'id'             => $uuid(),
                        'employee_id'    => $empIds[$i],
                        'branch_id'      => $branches[0] ?? $branchId,
                        'department_id'  => $deptId,
                        'transfer_date'  => $now->copy()->subDays(rand(1, 60))->toDateString(),
                        'description'    => "Mock transfer #{$i}",
                        'created_by'     => $creatorId,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]));
                }
            },

            // ── Travels ────────────────────────────────────
            'travels' => function () use ($uuid, $now, $creatorId, $empIds) {
                if (empty($empIds)) return;
                for ($i = 0; $i < min(self::MIN_ROWS, count($empIds)); $i++) {
                    DB::table('travels')->insert(array_filter([
                        'id'             => $uuid(),
                        'employee_id'    => $empIds[$i],
                        'start_date'     => $now->copy()->addDays(rand(1, 30))->toDateString(),
                        'end_date'       => $now->copy()->addDays(rand(31, 60))->toDateString(),
                        'purpose_of_visit' => "Mock business trip #{$i}",
                        'place_of_visit' => "City #{$i}",
                        'description'    => 'Auto-generated for content validation',
                        'status'         => 'approved',
                        'created_by'     => $creatorId,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]));
                }
            },

            // ── Promotions ─────────────────────────────────
            'promotions' => function () use ($uuid, $now, $creatorId, $empIds, $desigId) {
                if (empty($empIds) || !$desigId) return;
                for ($i = 0; $i < min(self::MIN_ROWS, count($empIds)); $i++) {
                    DB::table('promotions')->insert(array_filter([
                        'id'              => $uuid(),
                        'employee_id'     => $empIds[$i],
                        'designation_id'  => $desigId,
                        'promotion_title' => "Mock promotion #{$i}",
                        'promotion_date'  => $now->copy()->subDays(rand(1, 180))->toDateString(),
                        'description'     => 'Content validation mock',
                        'created_by'      => $creatorId,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ]));
                }
            },

            // ── Resignations ────────────────────────────────
            'resignations' => function () use ($uuid, $now, $creatorId, $empIds) {
                if (empty($empIds)) return;
                $existing = DB::table('resignations')->pluck('employee_id')->all();
                $available = array_diff($empIds, $existing);
                foreach (array_slice(array_values($available), 0, self::MIN_ROWS) as $empId) {
                    DB::table('resignations')->insert(array_filter([
                        'id'               => $uuid(),
                        'employee_id'      => $empId,
                        'notice_date'      => $now->copy()->subDays(rand(30, 90))->toDateString(),
                        'resignation_date' => $now->copy()->subDays(rand(1, 29))->toDateString(),
                        'description'      => 'Content validation mock',
                        'created_by'       => $creatorId,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ]));
                }
            },

            // ── Set Salaries ────────────────────────────────
            'set_salaries' => function () use ($uuid, $now, $creatorId, $empIds) {
                if (empty($empIds)) return;
                $existing = DB::table('set_salaries')->pluck('employee_id')->all();
                $available = array_diff($empIds, $existing);
                foreach (array_slice(array_values($available), 0, self::MIN_ROWS) as $empId) {
                    DB::table('set_salaries')->insert(array_filter([
                        'id'            => $uuid(),
                        'employee_id'   => $empId,
                        'net_salary'    => rand(3000, 10000),
                        'salary_type'   => 0,
                        'status'        => 1,
                        'created_by'    => $creatorId,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ]));
                }
            },

            // ── Terminations ────────────────────────────────
            'terminations' => function () use ($uuid, $now, $creatorId, $empIds) {
                if (empty($empIds)) return;
                $termTypeId = DB::table('termination_types')->value('id');
                $existing = DB::table('terminations')->pluck('employee_id')->all();
                $available = array_diff($empIds, $existing);
                foreach (array_slice(array_values($available), 0, self::MIN_ROWS) as $empId) {
                    DB::table('terminations')->insert(array_filter([
                        'id'               => $uuid(),
                        'employee_id'      => $empId,
                        'termination_type' => $termTypeId,
                        'termination_date' => $now->copy()->subDays(rand(1, 90))->toDateString(),
                        'notice_date'      => $now->copy()->subDays(rand(91, 180))->toDateString(),
                        'description'      => 'Content validation mock',
                        'created_by'       => $creatorId,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ]));
                }
            },

            // ── Awards ─────────────────────────────────────
            'awards' => function () use ($uuid, $now, $creatorId, $empIds) {
                if (empty($empIds)) return;
                $awardTypeId = DB::table('award_types')->value('id');
                for ($i = 0; $i < min(self::MIN_ROWS, count($empIds)); $i++) {
                    DB::table('awards')->insert(array_filter([
                        'id'            => $uuid(),
                        'employee_id'   => $empIds[$i],
                        'award_type'    => $awardTypeId,
                        'date'          => $now->copy()->subDays(rand(1, 180))->toDateString(),
                        'gift'          => 'Certificate & Bonus',
                        'description'   => 'Content validation mock',
                        'created_by'    => $creatorId,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ]));
                }
            },

            // ── Complaints ─────────────────────────────────
            'complaints' => function () use ($uuid, $now, $creatorId, $empIds) {
                if (count($empIds) < 2) return;
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('complaints')->insert(array_filter([
                        'id'                    => $uuid(),
                        'complaint_from'        => $empIds[0],
                        'complaint_against'     => $empIds[1],
                        'title'                 => "Mock complaint #{$i}",
                        'complaint_date'        => $now->copy()->subDays(rand(1, 60))->toDateString(),
                        'description'           => 'Content validation mock',
                        'created_by'            => $creatorId,
                        'created_at'            => $now,
                        'updated_at'            => $now,
                    ]));
                }
            },

            // ── Competencies ────────────────────────────────
            'competencies' => function () use ($uuid, $now, $creatorId) {
                $names = ['Communication', 'Leadership', 'Problem Solving'];
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('competencies')->insertOrIgnore([
                        'id'         => $uuid(),
                        'code'       => 'CMPT-' . strtoupper(\Illuminate\Support\Str::random(6)) . '-' . ($i + 1),
                        'name'       => $names[$i],
                        'type'       => 'technical',
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            },

            // ── Indicators ──────────────────────────────────
            'indicators' => function () use ($uuid, $now, $creatorId, $companyId, $branchId, $deptId, $desigId) {
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('indicators')->insert(array_filter([
                        'id'              => $uuid(),
                        'branch_id'       => $branchId,
                        'department_id'   => $deptId,
                        'designation_id'  => $desigId,
                        'company_id'      => $companyId,
                        'rating'          => rand(1, 5),
                        'overall_rating'  => rand(1, 5),
                        'added_by'        => $creatorId,
                        'created_by'      => $creatorId,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ]));
                }
            },

            // ── Leaves ──────────────────────────────────────
            'leaves' => function () use ($uuid, $now, $creatorId, $empIds) {
                if (empty($empIds)) return;
                $leaveTypeId = DB::table('leave_types')->value('id');
                if (!$leaveTypeId) return;
                for ($i = 0; $i < min(self::MIN_ROWS, count($empIds)); $i++) {
                    DB::table('leaves')->insert(array_filter([
                        'id'             => $uuid(),
                        'employee_id'    => $empIds[$i],
                        'leave_type_id'  => $leaveTypeId,
                        'applied_on'     => $now->copy()->subDays(rand(10, 30))->toDateString(),
                        'start_date'     => $now->copy()->addDays(rand(1, 15))->toDateString(),
                        'end_date'       => $now->copy()->addDays(rand(16, 20))->toDateString(),
                        'total_leave_days' => rand(1, 5),
                        'leave_reason'   => 'Content validation mock',
                        'status'         => 'approved',
                        'created_by'     => $creatorId,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]));
                }
            },

            // ── Payslips ────────────────────────────────────
            'payslips' => function () use ($uuid, $now, $creatorId, $empIds) {
                if (empty($empIds)) return;
                for ($i = 0; $i < min(self::MIN_ROWS, count($empIds)); $i++) {
                    DB::table('payslips')->insert(array_filter([
                        'id'             => $uuid(),
                        'employee_id'    => $empIds[$i],
                        'salary_month'   => $now->copy()->subMonths($i + 1)->format('Y-m'),
                        'net_payable'    => rand(3000, 8000),
                        'basic_salary'   => rand(3000, 8000),
                        'status'         => rand(0, 1),
                        'created_by'     => $creatorId,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]));
                }
            },

            // ── Clients ─────────────────────────────────────
            'clients' => function () use ($uuid, $now, $creatorId) {
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    $userId = DB::table('users')->where('type', 'client')->inRandomOrder()->value('id') ?? $creatorId;
                    DB::table('clients')->insert(array_filter([
                        'id'         => $uuid(),
                        'name'       => "Mock Client #{$i}",
                        'email'      => "mock_client_{$i}_" . Str::random(4) . "@test.local",
                        'user_id'    => $userId,
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            },

            // ── Job Categories ──────────────────────────────
            'job_categories' => function () use ($uuid, $now, $creatorId) {
                $categories = ['Engineering', 'Marketing', 'Sales', 'HR', 'Finance'];
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('job_categories')->insert(array_filter([
                        'id'         => $uuid(),
                        'title'      => $categories[$i],
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            },

            // ── Jobs ────────────────────────────────────────
            'jobs' => function () use ($uuid, $now, $creatorId, $branchId) {
                $catId = DB::table('job_categories')->value('id');
                if (!$catId) return;
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('jobs')->insert(array_filter([
                        'id'         => $uuid(),
                        'title'      => "Mock Job Position #{$i}",
                        'branch'     => $branchId,
                        'category'   => $catId,
                        'position'   => rand(1, 5),
                        'status'     => 'active',
                        'start_date' => $now->copy()->subDays(30)->toDateString(),
                        'end_date'   => $now->copy()->addDays(60)->toDateString(),
                        'skill'      => 'PHP, Laravel, MySQL',
                        'description'=> 'Content validation mock',
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            },

            // ── Job Applications ────────────────────────────
            'job_applications' => function () use ($uuid, $now, $creatorId) {
                $jobId = DB::table('jobs')->value('id');
                if (!$jobId) return;
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('job_applications')->insert(array_filter([
                        'id'         => $uuid(),
                        'job'        => $jobId,
                        'name'       => "Mock Applicant #{$i}",
                        'email'      => "applicant_mock_{$i}_" . Str::random(4) . "@test.local",
                        'phone'      => '1234567890',
                        'stage'      => DB::table('job_stages')->value('id'),
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            },

            // ── Interview Schedules ─────────────────────────
            'interview_schedules' => function () use ($uuid, $now, $creatorId) {
                $appIds = DB::table('job_applications')->limit(self::MIN_ROWS)->pluck('id')->all();
                if (empty($appIds)) return;
                foreach ($appIds as $i => $appId) {
                    DB::table('interview_schedules')->insert(array_filter([
                        'id'            => $uuid(),
                        'candidate'     => $appId,
                        'employee'      => $creatorId,
                        'date'          => $now->copy()->addDays(rand(1, 30))->toDateString(),
                        'time'          => '10:00:00',
                        'comment'       => 'Content validation mock',
                        'created_by'    => $creatorId,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ]));
                }
            },

            // ── Custom Questions ─────────────────────────────
            'custom_questions' => function () use ($uuid, $now, $creatorId) {
                $types = ['text', 'textarea', 'select'];
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('custom_questions')->insert(array_filter([
                        'id'         => $uuid(),
                        'question'   => "Mock question #{$i}?",
                        'type'       => $types[$i % count($types)],
                        'is_required' => rand(0, 1),
                        'module'     => 'recruitment',
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            },

            // ── Project Stages ──────────────────────────────
            'project_stages' => function () use ($uuid, $now, $creatorId) {
                $stages = ['Planning', 'In Progress', 'Review', 'Completed'];
                foreach (array_slice($stages, 0, self::MIN_ROWS) as $i => $stage) {
                    DB::table('project_stages')->insert(array_filter([
                        'id'         => $uuid(),
                        'name'       => $stage,
                        'order'      => $i,
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            },

            // ── Company Policies ─────────────────────────────
            'company_policies' => function () use ($uuid, $now, $creatorId, $companyId, $branchId) {
                $policies = ['Attendance Policy', 'Remote Work Policy', 'Code of Conduct'];
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('company_policies')->insert(array_filter([
                        'id'          => $uuid(),
                        'branch'      => $branchId,
                        'title'       => $policies[$i],
                        'description' => 'Content validation mock policy',
                        'company_id'  => $companyId,
                        'created_by'  => $creatorId,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]));
                }
            },

            // ── Documents ───────────────────────────────────
            'documents' => function () use ($uuid, $now, $creatorId) {
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('documents')->insert(array_filter([
                        'id'          => $uuid(),
                        'name'        => "Mock Document #{$i}",
                        'description' => 'Content validation mock',
                        'created_by'  => $creatorId,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]));
                }
            },

            // ── Labels ──────────────────────────────────────
            'labels' => function () use ($uuid, $now, $creatorId) {
                $pipelineId = DB::table('pipelines')->value('id');
                if (!$pipelineId) return;
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('labels')->insert(array_filter([
                        'id'          => $uuid(),
                        'name'        => "Mock Label #{$i}",
                        'color'       => '#' . str_pad(dechex(rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT),
                        'pipeline_id' => $pipelineId,
                        'created_by'  => $creatorId,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]));
                }
            },

            // ── Sources ─────────────────────────────────────
            'sources' => function () use ($uuid, $now, $creatorId) {
                $names = ['Website', 'Referral', 'Social Media'];
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('sources')->insert(array_filter([
                        'id'         => $uuid(),
                        'name'       => $names[$i],
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            },

            // ── Performance Types ────────────────────────────
            'performance_types' => function () use ($uuid, $now, $creatorId) {
                $names = ['Attendance', 'Teamwork', 'Technical Skill'];
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('performance_types')->insert(array_filter([
                        'id'         => $uuid(),
                        'name'       => $names[$i],
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            },

            // ── Loan Options ────────────────────────────────
            'loan_options' => function () use ($uuid, $now, $creatorId) {
                $names = ['Personal Loan', 'Housing Loan', 'Car Loan'];
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('loan_options')->insert(array_filter([
                        'id'         => $uuid(),
                        'name'       => $names[$i],
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            },

            // ── Training Types ──────────────────────────────
            'training_types' => function () use ($uuid, $now, $creatorId) {
                $names = ['On-the-Job', 'Classroom', 'Online'];
                for ($i = 0; $i < self::MIN_ROWS; $i++) {
                    DB::table('training_types')->insert(array_filter([
                        'id'         => $uuid(),
                        'name'       => $names[$i],
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            },
        ];
    }
}
