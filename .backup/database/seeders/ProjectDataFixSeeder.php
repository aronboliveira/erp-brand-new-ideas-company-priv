<?php

namespace Database\Seeders;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Enums\{EvaluationStatus, ParticipationStatus, PriorityLevel, ProjectRole};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Carbon\CarbonImmutable;

/**
 * One-shot seeder: ensures the super-admin user owns projects,
 * is linked via project_users, and each project has tasks + stages.
 *
 * Safe to re-run — skips rows that already exist.
 */
final class ProjectDataFixSeeder extends Seeder
{
    private const NEW_PROJECTS = 5;
    private const TASKS_PER_PROJECT = 6;

    public function run(): void
    {
        $faker = fake('pt_BR');
        $now   = CarbonImmutable::now();

        // ── Resolve the super-admin (Lúcia) ─────────────────────────
        $sa = DB::table(DC::TABLE_USERS)
            ->where(UC::COL_TP, 'super admin')
            ->first();

        if (!$sa) {
            $this->command?->warn('[ProjectDataFixSeeder] No super-admin user found. Aborting.');
            return;
        }

        $saId = (string) $sa->id;
        $this->command?->info("[ProjectDataFixSeeder] Super-admin: {$sa->name} ({$saId})");

        // ── Collect lookup IDs ──────────────────────────────────────
        $clientIds = DB::table(DC::TABLE_CLIENTS)->pluck('id')->all();
        $stageIds  = DB::table(DC::TABLE_PROJ_STAGES)->pluck('id')->all();
        $userIds   = DB::table(DC::TABLE_USERS)->pluck('id')->all();

        if (!$clientIds || !$stageIds) {
            $this->command?->warn('[ProjectDataFixSeeder] No clients or stages. Aborting.');
            return;
        }

        // ── 1) Reassign existing projects to super-admin ────────────
        $reassigned = DB::table(DC::TABLE_PROJECTS)
            ->where(DC::COL_TABLE_CREATOR, '!=', $saId)
            ->update([DC::COL_TABLE_CREATOR => $saId]);
        $this->command?->info("  Reassigned {$reassigned} existing projects to super-admin.");

        // ── 2) Create new projects (only if below threshold) ────────
        $statuses    = ['in_progress', 'on_hold', 'complete'];
        $projectIds  = DB::table(DC::TABLE_PROJECTS)->pluck('id')->all();
        $totalTarget = count($projectIds) + self::NEW_PROJECTS;

        $projectNames = [
            'Sistema de Gestão Financeira',
            'Portal de Atendimento ao Cliente',
            'Automação de Marketing Digital',
            'Módulo de Controle de Estoque',
            'Plataforma de Relatórios Gerenciais',
        ];

        $created = 0;
        for ($i = 0; $i < self::NEW_PROJECTS && count($projectIds) < $totalTarget; $i++) {
            // Skip if a project with this name already exists
            if (DB::table(DC::TABLE_PROJECTS)->where(PJC::COL_NM, $projectNames[$i])->exists()) {
                continue;
            }
            $pid = Str::uuid()->toString();
            $start = $faker->dateTimeBetween('-60 days', '+5 days');
            $end   = $faker->dateTimeBetween($start, '+90 days');

            DB::table(DC::TABLE_PROJECTS)->insert([
                'id'                     => $pid,
                PJC::COL_NM             => $projectNames[$i] ?? $faker->sentence(3),
                PJC::COL_S_DT           => $start->format('Y-m-d'),
                PJC::COL_E_DT           => $end->format('Y-m-d'),
                PJC::COL_CLIENT_ID      => $faker->randomElement($clientIds),
                PJC::COL_BUDGET         => $faker->numberBetween(8_000, 200_000),
                PJC::COL_STAGE_ID       => $faker->randomElement($stageIds),
                PJC::COL_DESCRIPTION    => $faker->paragraph(),
                PJC::COL_STATUS         => $faker->randomElement($statuses),
                PJC::COL_E_HRS          => (string) $faker->numberBetween(40, 500),
                PJC::COL_TAGS           => implode(',', $faker->words(3)),
                DC::COL_TABLE_CREATOR   => $saId,
                'created_at'            => $now,
                'updated_at'            => $now,
            ]);

            $projectIds[] = $pid;
            $created++;
        }

        $this->command?->info("  Created {$created} new projects (skipped duplicates).");

        // ── 3) Ensure super-admin is in project_users for ALL projects ─
        $existingLinks = DB::table(DC::TABLE_PRJ_USR)
            ->where('user_id', $saId)
            ->pluck('project_id')
            ->all();

        $missing = array_diff($projectIds, $existingLinks);
        $linked  = 0;

        foreach ($missing as $pid) {
            DB::table(DC::TABLE_PRJ_USR)->insert([
                'id'                => Str::uuid()->toString(),
                'project_id'        => $pid,
                'user_id'           => $saId,
                'is_active'         => true,
                'invited_by'        => $saId,
                'invited_at'        => $now,
                'invite_status'     => ParticipationStatus::Accepted->value,
                'invite_code'       => 'INV-' . Str::uuid()->toString() . '-' . time(),
                'joined_at'         => $now,
                'accepted_at'       => $now,
                'accepted_by'       => $saId,
                'role'              => ProjectRole::Admin->value,
                'can_write_own_files'    => true,
                'can_write_others_files' => true,
                'can_read_others_files'  => true,
                'is_project_leader'      => true,
                DC::COL_TABLE_CREATOR    => $saId,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            $linked++;
        }

        // Also link a couple of company users to projects
        $companyUsers = DB::table(DC::TABLE_USERS)
            ->where(UC::COL_TP, 'company')
            ->pluck('id')
            ->all();

        foreach ($companyUsers as $cuid) {
            $cuExisting = DB::table(DC::TABLE_PRJ_USR)
                ->where('user_id', $cuid)
                ->pluck('project_id')
                ->all();
            $cuMissing = array_diff($projectIds, $cuExisting);
            // Link to up to 3 random projects
            $cuToLink = array_slice((array) $faker->randomElements($cuMissing, min(3, count($cuMissing))), 0, 3);
            foreach ($cuToLink as $pid) {
                DB::table(DC::TABLE_PRJ_USR)->insert([
                    'id'                => Str::uuid()->toString(),
                    'project_id'        => $pid,
                    'user_id'           => $cuid,
                    'is_active'         => true,
                    'invited_by'        => $saId,
                    'invited_at'        => $now,
                    'invite_status'     => ParticipationStatus::Accepted->value,
                    'invite_code'       => 'INV-' . Str::uuid()->toString() . '-' . time(),
                    'joined_at'         => $now,
                    'accepted_at'       => $now,
                    'accepted_by'       => $saId,
                    'role'              => ProjectRole::Member->value,
                    'can_write_own_files'    => true,
                    'can_write_others_files' => false,
                    'can_read_others_files'  => true,
                    'is_project_leader'      => false,
                    DC::COL_TABLE_CREATOR    => $saId,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
                $linked++;
            }
        }

        $this->command?->info("  Linked {$linked} project_users entries.");

        // ── 4) Ensure task_stages exist for projects ────────────────
        $taskStageNames = ['A Fazer', 'Em Progresso', 'Em Revisão', 'Concluído', 'Bloqueado'];
        $tsCreated = 0;

        foreach ($projectIds as $pid) {
            $existingTs = DB::table(DC::TABLE_TSK_STGS)
                ->where('project_id', $pid)
                ->count();
            if ($existingTs >= 3) continue;

            foreach ($taskStageNames as $ord => $tsName) {
                DB::table(DC::TABLE_TSK_STGS)->insert([
                    'id'                => Str::uuid()->toString(),
                    'project_id'        => $pid,
                    'name'              => $tsName,
                    'color'             => ['#3498db', '#f39c12', '#9b59b6', '#27ae60', '#e74c3c'][$ord],
                    'order'             => $ord,
                    'status'            => $ord === 3
                        ? EvaluationStatus::Completed->value
                        : EvaluationStatus::Active->value,
                    'complete'          => $ord === 3,
                    'progress'          => $ord === 3 ? 100 : 0,
                    DC::COL_TABLE_CREATOR => $saId,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
                $tsCreated++;
            }
        }

        $this->command?->info("  Created {$tsCreated} task stages.");

        // ── 5) Create project_tasks for every project ───────────────
        $taskNames = [
            'Levantamento de requisitos',
            'Design de interface do usuário',
            'Desenvolvimento do backend API',
            'Implementação do frontend',
            'Testes unitários e integração',
            'Revisão de código e deploy',
            'Documentação técnica',
            'Configuração de CI/CD',
            'Migração de dados legados',
            'Testes de aceitação do usuário',
        ];
        $priorities = array_map(fn($c) => $c->value, PriorityLevel::cases());
        $taskStatuses = ['not_started', 'in_progress', 'completed'];
        $tasksCreated = 0;

        foreach ($projectIds as $pid) {
            $existingTasks = DB::table(DC::TABLE_PROJ_TSKS)
                ->where(PJC::COL_PJ_ID, $pid)
                ->count();
            if ($existingTasks >= self::TASKS_PER_PROJECT) continue;

            $toCreate = self::TASKS_PER_PROJECT - $existingTasks;
            // project_stage_id FK → project_stages (not task_stages)
            $projStages = DB::table(DC::TABLE_PROJ_STAGES)
                ->pluck('id')
                ->all();

            for ($t = 0; $t < $toCreate; $t++) {
                $tStart = $faker->dateTimeBetween('-30 days', '+10 days');
                $tEnd   = $faker->dateTimeBetween($tStart, '+60 days');
                $progress = $faker->randomElement([0, 10, 25, 50, 75, 100]);
                $status = match (true) {
                    $progress === 100 => 'completed',
                    $progress > 0    => 'in_progress',
                    default          => 'not_started',
                };
                $assignees = $faker->randomElements($userIds, $faker->numberBetween(1, min(3, count($userIds))));

                $taskId = Str::uuid()->toString();
                $code   = 'PRJ-TSK-' . $taskId;

                DB::table(DC::TABLE_PROJ_TSKS)->insert([
                    'id'                 => $taskId,
                    'code'               => $code,
                    PJC::COL_NM         => $taskNames[$t % count($taskNames)] . ' — ' . Str::limit($faker->sentence(2), 30),
                    PJC::COL_DESCRIPTION => $faker->paragraph(),
                    'color'              => $faker->hexColor(),
                    'responsible'        => $faker->randomElement($userIds),
                    PJC::COL_ASGN       => implode(',', $assignees),
                    'assigned_by'        => $saId,
                    'assigned_at'        => $now,
                    PJC::COL_E_HRS      => $faker->numberBetween(2, 40),
                    'actual_hrs'         => $faker->numberBetween(0, 30),
                    PJC::COL_S_DT       => $tStart->format('Y-m-d'),
                    PJC::COL_E_DT       => $tEnd->format('Y-m-d'),
                    'module_type'        => 'projects',
                    PJC::COL_PRT        => $faker->randomElement($priorities),
                    PJC::COL_PGR        => (string) $progress,
                    PJC::COL_STATUS     => $status,
                    AC::COL_OD          => $t,
                    'depth'              => 0,
                    PJC::COL_PJ_ID      => $pid,
                    PJC::COL_STAGE_ID   => $projStages ? $faker->randomElement($projStages) : null,
                    PJC::COL_IS_FV      => $faker->boolean(20),
                    PJC::COL_IS_CP      => $progress === 100,
                    'recurring'          => false,
                    DC::COL_TABLE_CREATOR => $saId,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
                $tasksCreated++;
            }
        }

        $this->command?->info("  Created {$tasksCreated} project tasks.");

        // ── 6) Seed tasks table (generic tasks board) ───────────────
        $genericTasks = DB::table(DC::TABLE_TASKS)->count();
        $genericToCreate = max(0, 10 - $genericTasks);

        for ($g = 0; $g < $genericToCreate; $g++) {
            $taskPid = $faker->randomElement($projectIds);
            DB::table(DC::TABLE_TASKS)->insert([
                'id'                   => Str::uuid()->toString(),
                'title'                => $taskNames[$g % count($taskNames)],
                'agent_or_manager'     => $sa->name,
                'date'                 => $faker->dateTimeBetween('-15 days', '+15 days')->format('Y-m-d'),
                'time'                 => $faker->time('H:i:s'),
                'description'          => $faker->paragraph(),
                'module_type'          => 'projects',
                'assign_to'            => $saId,
                'project_id'           => $taskPid,
                'priority'             => $faker->randomElement($priorities),
                DC::COL_TABLE_CREATOR  => $saId,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }

        if ($genericToCreate > 0) {
            $this->command?->info("  Created {$genericToCreate} generic tasks.");
        }

        $this->command?->info('[ProjectDataFixSeeder] Done.');
    }
}
