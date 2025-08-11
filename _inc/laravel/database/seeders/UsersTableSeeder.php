<?php

namespace Database\Seeders;

use App\Config\Constants\{
    ActivitiesConstants,
    BanksConstants,
    ChartsConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants,
    SeedersTemplating,
    SettingsConstants,
    UsersConstants
};
use App\Models\{
    BankAccount,
    ChartOfAccount,
    ChartOfAccountType,
    ChartOfAccountSubType,
    Client,
    ExperienceCertificate,
    GeneratedOfferLetter,
    JoiningLetter,
    Noc,
    Permission,
    Pipeline,
    Project,
    ProjectStages,
    Role,
    User,
    Utility
};
use Carbon\Carbon;
use Database\Seeders\PlansTableSeeder;
use Illuminate\{
    Database\QueryException,
    Database\Seeder,
    Support\Str
};
use Illuminate\Support\Facades\{DB, Hash, Log};
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Symfony\Component\Console\Output\ConsoleOutput;

class UsersTableSeeder extends Seeder
{
    protected const STANDARD_PERMISSIONS = ['manage', 'create', 'edit', 'delete'];
    public function run(): void
    {
        try {
            $settled = self::_hardcoded_permissions();
            $superAdmin = $settled[PermissionsConstants::SA];
            $admin = $settled[PermissionsConstants::ADM];
            $accountant = $settled[PermissionsConstants::ACT];
            $company = $settled[PermissionsConstants::CPN];
            $clientUser = $settled[PermissionsConstants::CL . 'User'];
            $project = $settled['project'];
            $uuids = $settled['uuids'];
            $users = [$superAdmin, $admin, $accountant, $company, $clientUser];
            $utilityTasks = [];
            $employeePairs = [
                [$accountant->id ?? DatabaseConstants::DEFAULT_UUID, $company->id   ?? DatabaseConstants::DEFAULT_UUID],
                [$superAdmin->id ?? DatabaseConstants::DEFAULT_UUID, $company->id  ?? DatabaseConstants::DEFAULT_UUID],
                [$admin->id      ?? DatabaseConstants::DEFAULT_UUID, $company->id  ?? DatabaseConstants::DEFAULT_UUID],
                [$company->id    ?? DatabaseConstants::DEFAULT_UUID, $superAdmin->id ?? DatabaseConstants::DEFAULT_UUID],
            ];
            foreach ($employeePairs as $args)
                $utilityTasks[] = [Utility::EMP_DTLS, $args];
            foreach ([$admin, $accountant, $company, $superAdmin] as $u)
                $utilityTasks[] = [Utility::COA_TP_DT, [$u->id ?? DatabaseConstants::DEFAULT_UUID]];
            foreach ([$accountant, $admin, $company, $superAdmin] as $u)
                $utilityTasks[] = [Utility::COA_DATA, [$u]];
            foreach ([$accountant, $admin, $company, $superAdmin] as $u)
                $utilityTasks[] = [Utility::PPL_LD_DL_STG, [$u->id ?? DatabaseConstants::DEFAULT_UUID]];
            foreach ([$company, $superAdmin] as $u)
                $utilityTasks[] = [
                    Utility::PRJ_TSK_STGS,
                    [
                        $project->id ?? DatabaseConstants::DEFAULT_PIPELINE,
                        $u->id      ?? DatabaseConstants::DEFAULT_UUID,
                    ]
                ];
            foreach ([$accountant, $admin, $company, $superAdmin] as $u)
                $utilityTasks[] = ['labels', [$u->id ?? DatabaseConstants::DEFAULT_UUID]];
            foreach ([$company, $superAdmin] as $u)
                $utilityTasks[] = ['sources', [$u->id ?? DatabaseConstants::DEFAULT_UUID]];
            foreach ([$admin, $company, $superAdmin] as $u)
                $utilityTasks[] = [Utility::JB_STG, [$u->id ?? DatabaseConstants::DEFAULT_UUID]];
            Log::debug("Dinamalically calling the utility methods to populate project-related tables...");
            if ($project instanceof Project) Log::debug("Project {$project->id} is a project instance");
            foreach ($utilityTasks as [$method, $args]) {
                if ($method === Utility::PRJ_TSK_STGS && !$project?->id) {
                    Log::error("Failed to call user function for Project Task Stages");
                    continue;
                };
                $allNull = array_reduce(
                    $args,
                    fn (bool $carry, mixed $item): bool => $carry && is_null($item),
                    true
                );
                if (!$allNull)
                    call_user_func_array([Utility::class, $method], $args);
            };
            $uniqueLanguages = [];
            foreach ($users as $user) {
                $lang = $user?->lang ?? DatabaseConstants::DEFAULT_LANG;
                $id = $user?->id ?? DatabaseConstants::DEFAULT_UUID;
                if (!isset($uniqueLanguages[$lang]))
                    $uniqueLanguages[$lang] = $id;
            };
            foreach ($uniqueLanguages as $lang => $id) {
                $friendlyLangName = Utility::langList()[is_string($lang) ? $lang : (string) $lang];
                Log::debug("Loading language resources for {$friendlyLangName}, id {$id}");
                Utility::languageCreate($id);
            }
            foreach ($users as $user) {
                if (!($user instanceof User)) continue;
                $user?->userDefaultData();
                $user?->defaultEmail($user?->id ?? DatabaseConstants::DEFAULT_UUID);
                $user?->userDefaultWarehouse();
                GeneratedOfferLetter::defaultOfferLetter($user?->id ?? DatabaseConstants::DEFAULT_UUID);
                ExperienceCertificate::defaultExpCertificate($user?->id ?? DatabaseConstants::DEFAULT_UUID);
                JoiningLetter::defaultJoiningLetter($user?->id ?? DatabaseConstants::DEFAULT_UUID);
                Noc::defaultNocCertificate($user?->id ?? DatabaseConstants::DEFAULT_UUID);
            }
            $disks          = [SettingsConstants::LC, SettingsConstants::WSB, SettingsConstants::S3];
            $validationExts = SettingsConstants::FMTS_UP_DEF;
            $maxUploadSize  = (int) SettingsConstants::MAX_U_SIZE_DEF;
            $settingsToInsert = [];
            foreach ($disks as $disk) {
                $primarySettingsId = '';
                $primarySettingsAcc = 0;
                do {
                    if ($primarySettingsAcc > 100_000) {
                        Log::warning(SeedersTemplating::SCAPE_MSG);
                        break;
                    }
                    $primarySettingsId = (string) Str::uuid();
                    $primarySettingsAcc += 1;
                } while (in_array($primarySettingsId, $uuids, true));
                $uuids[] = $primarySettingsId;
                $secondarySettingsId = '';
                $secondarySettingsAcc = 0;
                do {
                    if ($secondarySettingsAcc > 100_000) {
                        Log::warning(SeedersTemplating::SCAPE_MSG);
                        break;
                    }
                    $secondarySettingsId = (string) Str::uuid();
                    $secondarySettingsAcc += 1;
                } while (in_array($secondarySettingsId, $uuids, true));
                $uuids[] = $secondarySettingsId;
                $settingsToInsert[] = [
                    'id'         => $primarySettingsId,
                    'name'       => "{$disk}_storage_validation",
                    'value'      => $validationExts,
                    DatabaseConstants::TABLE_CREATOR => DatabaseConstants::DEFAULT_UUID,
                ] + self::timestamps();
                $settingsToInsert[] = [
                    'id'         => $secondarySettingsId,
                    'name'       => "{$disk}_max_upload_size",
                    'value'      => $maxUploadSize,
                    DatabaseConstants::TABLE_CREATOR => DatabaseConstants::DEFAULT_UUID,
                ] + self::timestamps();
            }
            DB::table(DatabaseConstants::TABLE_SETTINGS)
                ->insert(
                    collect($settingsToInsert)
                        ->unique('name')
                        ->values()
                        ->all()
                );
        } catch (\Exception $e) {
            Log::error('Failed to seed users tables ' . $e->getMessage());
        }
    }
    protected static function variatePerm(string $base): array
    {
        if (!preg_match('/[a-z0-9]##[a-z0-9]/i', $base)) return [$base];
        $variants = [];
        foreach ([' ', '-', '_'] as $sep) $variants[] = str_replace('##', $sep, $base);
        return $variants;
    }
    protected static function timestamps(?Carbon $createdAt = null, ?Carbon $updatedAt = null): array
    {
        $now = Carbon::now();
        return [
            UsersConstants::COL_C_AT => $createdAt ?? $now,
            UsersConstants::COL_U_AT => $updatedAt ?? $now,
        ];
    }
    /**
     *
     *  @param  string   $suffix        e.g. 'invoice'  or  'payment##invoice'
     *  @param  array    $addPerms      extra actions to *add*  (e.g. ['show'])
     *  @param  array    $removePerms   actions to *remove*     (e.g. ['delete'])
     *  @param  string   $guardName     usually 'web'
     *  @param  array    $extraData     anything else to merge (defaults to timestamps)
     *
     *  @return array[]                 each element ready for bulk‑insert
     */
    protected static function defaultedPermissions(
        string $suffix             = '#REFERENCE_ERROR',
        array  $addPerms           = [],
        array  $removePerms        = [],
        string $guardName          = 'web',
        array  $extraData          = []
    ): array {
        $actions = array_merge(self::STANDARD_PERMISSIONS, $addPerms);
        if ($removePerms)
            $actions = array_diff($actions, $removePerms);
        $meta = array_merge(
            ['guard_name' => $guardName],
            self::timestamps(),
            $extraData
        );
        $rows = [];
        foreach ($actions as $action)
            foreach (self::variatePerm("{$action}##{$suffix}") as $name)
                $rows[] = array_merge($meta, ['name' => $name]);
        return $rows;
    }
    protected static function assignPermissions(Role $role, array $permissions): void
    {
        foreach ($permissions as $permData)
            $role->givePermissionTo(Permission::firstOrCreate(
                [
                    'name' => $permData['name'],
                    'guard_name' => $permData['guard_name'] ?? 'web'
                ],
                $permData
            ));
    }
    /**
     * Get's the procedures for creating the entities through hardcoded procedures instead of looping
     *
     * @return array<string, ?User|Client|Project|Pipeline|string[]>
     */
    private static function _hardcoded_permissions(): array
    {
        $arrPermissions = SeedersTemplating::PERMISSIONS;
        $now = now()->toDateTimeString();
        $gn = 'guard_name';
        $output = new ConsoleOutput();
        $superAdmin = null;
        $admin = null;
        $accountant = null;
        $clientUser = null;
        $client = null;
        $company = null;
        $pipeline = null;
        $faker = \Faker\Factory::create();
        $uuids = [];
        $output->writeln('');
        $output->writeln('<question>------------- ### Users Seeding ### ------------- </question>');
        try {
            $output->writeln('<comment> Seeding Permissions with ' . static::class . '</comment>');
            foreach ($arrPermissions as &$p) {
                $p[UsersConstants::COL_C_AT] = $now;
                $p[UsersConstants::COL_U_AT] = $now;
                if (!isset($p[$gn]) || !$p[$gn]) $p[$gn] = 'web';
            }
            $arrPermissions = collect(array_map(
                'unserialize',
                array_unique(
                    array_map('serialize', $arrPermissions)
                )
            ))
                ->unique(fn ($r) => ($r[$gn] ?? 'web') . '|' . $r['name'])
                ->values()
                ->all();
            foreach ($arrPermissions as $p) {
                $output->writeln('Seeding ' . $p['name'] ?? 'undefined permission' . ' ...');
                Permission::firstOrCreate($p);
            }
            $doneTime = now()->toDateTimeString();
            $output->writeln('<info>                            Done creating permissions at '
                . $doneTime . ' !</info>');
        } catch (QueryException $e) {
            $msg = 'Database error while seeding permissions: ' . $e->getMessage();
            Log::error($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        } catch (\Throwable $e) {
            $msg = 'Unexpected error while seeding permissions: ' . $e->getMessage();
            Log::critical($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        }
        try {
            $output->writeln('<comment> Seeding Super Admins with ' . static::class . '</comment>');
            $superAdminId = '';
            $saAcc = 0;
            do {
                if ($saAcc > 100_000) {
                    Log::warning(SeedersTemplating::SCAPE_MSG);
                    break;
                }
                $superAdminId = (string) Str::uuid();
                $saAcc += 1;
            } while (in_array($superAdminId, $uuids, true));
            $uuids[] = $superAdminId;
            $superAdminRole = Role::create(
                [
                    'id'   => $superAdminId,
                    'name' => PermissionsConstants::SA,
                    DatabaseConstants::TABLE_CREATOR => DatabaseConstants::DEFAULT_UUID,
                ]
            );
            $superAdmin = User::create(
                [
                    UsersConstants::COL_NM => $faker->name,
                    UsersConstants::COL_EM => 'suporte@prestech.com.br', // TODO CHANGE AFTER TESTING
                    UsersConstants::COL_PW => Hash::make('123456789qwe.*'), // TODO CHANGE AFTER TESTING
                    UsersConstants::COL_TP => PermissionsConstants::SA,
                    UsersConstants::COL_LG => DatabaseConstants::DEFAULT_LANG,
                    UsersConstants::COL_AV =>  $faker->imageUrl(200, 200, 'people'),
                    UsersConstants::COL_EM_V_AT => now()->toDateTimeString(),
                    UsersConstants::COL_DPL => DatabaseConstants::DEFAULT_PIPELINE,
                    UsersConstants::COL_PL => PlansTableSeeder::$planId ?? DatabaseConstants::DEFAULT_PLAN,
                    DatabaseConstants::TABLE_CREATOR => DatabaseConstants::DEFAULT_UUID,
                ]
            );
            $superAdmin->assignRole($superAdminRole);
            foreach (array_column(SeedersTemplating::SA_PERMS, 'name') as $saPerm)
                $superAdminRole->givePermissionTo($saPerm);
            Log::debug('Super Admin created with permissions: ' . json_encode($superAdminRole->getAllPermissions()->pluck('name')));
            try {
                $pipeline = Pipeline::create([
                    ProjectsConstants::COL_PPL_NM       => 'Default Pipeline',
                    ActivitiesConstants::COL_OD         => 0,
                    DatabaseConstants::TABLE_CREATOR    => $superAdmin?->id ?? DatabaseConstants::DEFAULT_UUID,
                ]);
            } catch (QueryException $e) {
                $msg = 'Database error while seeding permissions: ' . $e->getMessage();
                Log::error($msg, ['exception' => $e]);
                $output->writeln("<error>$msg</error>");
            } catch (\Throwable $e) {
                $msg = 'Unexpected error while seeding permissions: ' . $e->getMessage();
                Log::critical($msg, ['exception' => $e]);
                $output->writeln("<error>$msg</error>");
            }
            $doneTime = now()->toDateTimeString();
            $output->writeln('<info>                              Done creating super admins at '
                . $doneTime . ' !</info>');
        } catch (PermissionDoesNotExist $e) {
            $msg = 'Tried to assign a nonexistent permission to Super-Admin: ' . $e->getMessage();
            Log::warning($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        } catch (QueryException $e) {
            $msg = 'Database error while creating Super-Admin: ' . $e->getMessage();
            Log::error($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        } catch (\Throwable $e) {
            $msg = 'Unexpected error while creating Super-Admin: ' . $e->getMessage();
            Log::critical($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        }
        try {
            $output->writeln('<comment> Seeding Admins with ' . static::class . '</comment>');
            $adminId = '';
            $saAcc = 0;
            do {
                if ($saAcc > 100_000) {
                    Log::warning(SeedersTemplating::SCAPE_MSG);
                    break;
                }
                $adminId = (string) Str::uuid();
                $saAcc += 1;
            } while (in_array($adminId, $uuids, true));
            $uuids[] = $adminId;
            $adminRole = Role::create(
                [
                    'id'   => $adminId,
                    'name' => PermissionsConstants::ADM,
                    DatabaseConstants::TABLE_CREATOR => DatabaseConstants::DEFAULT_UUID,
                ]
            );
            foreach (array_column(SeedersTemplating::SA_PERMS, 'name') as $saPerm)
                $adminRole->givePermissionTo($saPerm);
            $adminPassword = $faker->password(12, 20);
            $admin = User::create(
                [
                    UsersConstants::COL_NM                    => $faker->name,
                    UsersConstants::COL_EM                   => $faker->unique()->safeEmail,
                    UsersConstants::COL_PW                => bcrypt($adminPassword),
                    UsersConstants::COL_TP => PermissionsConstants::ADM,
                    UsersConstants::COL_LG => DatabaseConstants::DEFAULT_LANG,
                    UsersConstants::COL_AV                  => $faker->imageUrl(200, 200, 'people'),
                    UsersConstants::COL_EM_V_AT => now()->toDateTimeString(),
                    UsersConstants::COL_DPL => $pipeline?->id ?? DatabaseConstants::DEFAULT_PIPELINE,
                    UsersConstants::COL_PL => PlansTableSeeder::$planId ?? DatabaseConstants::DEFAULT_PLAN,
                    DatabaseConstants::TABLE_CREATOR => DatabaseConstants::DEFAULT_UUID,
                ]
            );
            $admin->assignRole($adminRole);
            $doneTime = now()->toDateTimeString();
            $output->writeln('<info>                              Done creating admins at '
                . $doneTime . ' !</info>');
        } catch (PermissionDoesNotExist $e) {
            $msg = 'Tried to assign a nonexistent permission to Admin: ' . $e->getMessage();
            Log::warning($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        } catch (QueryException $e) {
            $msg = 'Database error while creating Admin: ' . $e->getMessage();
            Log::error($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        } catch (\Throwable $e) {
            $msg = 'Unexpected error while creating Admin: ' . $e->getMessage();
            Log::critical($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        }
        try {
            $companyId = '';
            $output->writeln('<comment> Seeding Companies with ' . static::class . '</comment>');
            $companyAcc = 0;
            do {
                if ($companyAcc > 100_000) {
                    Log::warning(SeedersTemplating::SCAPE_MSG);
                    break;
                }
                $companyId = (string) Str::uuid();
                $companyAcc += 1;
            } while (in_array($companyId, $uuids, true));
            $uuids[] = $companyId;
            $companyRole = Role::create(
                [
                    'id'   => $companyId,
                    'name' => 'company',
                    DatabaseConstants::TABLE_CREATOR => DatabaseConstants::DEFAULT_UUID,
                ]
            );
            foreach (array_column(SeedersTemplating::COMPANY_PERMS, 'name') as $cpPerm)
                $companyRole->givePermissionTo($cpPerm);
            $companyPassword = $faker->password(12, 20);
            $company = User::create(
                [
                    UsersConstants::COL_NM                    => $faker->name,
                    UsersConstants::COL_EM                   => $faker->unique()->safeEmail,
                    UsersConstants::COL_PW                => bcrypt($companyPassword),
                    UsersConstants::COL_TP => PermissionsConstants::CPN,
                    UsersConstants::COL_LG => DatabaseConstants::DEFAULT_LANG,
                    UsersConstants::COL_AV                  => $faker->imageUrl(200, 200, 'people'),
                    UsersConstants::COL_EM_V_AT => now()->toDateTimeString(),
                    UsersConstants::COL_DPL => $pipeline?->id ?? DatabaseConstants::DEFAULT_PIPELINE,
                    UsersConstants::COL_PL => PlansTableSeeder::$planId ?? DatabaseConstants::DEFAULT_PLAN,
                    DatabaseConstants::TABLE_CREATOR => DatabaseConstants::DEFAULT_UUID,
                ]
            );
            $company?->assignRole($companyRole);
            $doneTime = now()->toDateTimeString();
            $output->writeln('<info>                            Done creating companies at '
                . $doneTime . ' !</info>');
            try {
                $output->writeln('<comment> Seeding Accountant with ' . static::class . '</comment>');
                $accountantId = '';
                $accountantAcc = 0;
                do {
                    if ($accountantAcc > 100_000) {
                        Log::warning(SeedersTemplating::SCAPE_MSG);
                        break;
                    }
                    $accountantId = (string) Str::uuid();
                    $accountantAcc += 1;
                } while (in_array($accountantId, $uuids, true));
                $uuids[] = $accountantId;
                $accountantRole = Role::create(
                    [
                        'id'   => $accountantId,
                        'name' => 'accountant',
                        DatabaseConstants::TABLE_CREATOR => $company?->id ?? DatabaseConstants::DEFAULT_UUID,
                    ]
                );
                foreach (array_column(SeedersTemplating::ACCOUNTANT_PERMS, 'name') as $acPerm)
                    $accountantRole->givePermissionTo($acPerm);
                $accPassword = $faker->password(12, 20);
                $accountant = User::create(
                    [
                        UsersConstants::COL_NM                    => $faker->name,
                        UsersConstants::COL_EM                   => $faker->unique()->safeEmail,
                        UsersConstants::COL_PW                => bcrypt($accPassword),
                        UsersConstants::COL_TP => PermissionsConstants::ACT,
                        UsersConstants::COL_LG => DatabaseConstants::DEFAULT_LANG,
                        UsersConstants::COL_AV                  => $faker->imageUrl(200, 200, 'people'),
                        UsersConstants::COL_EM_V_AT => now()->toDateTimeString(),
                        UsersConstants::COL_DPL => $pipeline?->id ?? DatabaseConstants::DEFAULT_PIPELINE,
                        UsersConstants::COL_PL => PlansTableSeeder::$planId ?? DatabaseConstants::DEFAULT_PLAN,
                        DatabaseConstants::TABLE_CREATOR => $company?->id ?? DatabaseConstants::DEFAULT_UUID,
                    ]
                );
                $accountant->assignRole($accountantRole);
                $doneTime = now()->toDateTimeString();
                $output->writeln('<info>                      Done creating accountants at '
                    . $doneTime . ' !</info>');
            } catch (PermissionDoesNotExist $e) {
                $msg = 'Tried to assign a nonexistent permission to Accountant role: ' . $e->getMessage();
                Log::warning($msg, ['exception' => $e]);
                $output->writeln("<error>$msg</error>");
            } catch (QueryException $e) {
                $msg = 'Database error while creating Accountant role: ' . $e->getMessage();
                Log::error($msg, ['exception' => $e]);
                $output->writeln("<error>$msg</error>");
            } catch (\Throwable $e) {
                $msg = 'Unexpected error while creating Accountant role: ' . $e->getMessage();
                Log::critical($msg, ['exception' => $e]);
                $output->writeln("<error>$msg</error>");
            }
            try {
                $baAcc = 0;
                $output->writeln('<comment>Creating bank account instance...</comment>');
                $ids = ['baId', 'coaId', 'coaTpId', 'coaSbTpId'];
                foreach ($ids as $varName) {
                    $$varName = '';
                    $acc = 0;
                    do {
                        if (str_starts_with('ba', $varName)) $baAcc += 1;
                        if ($acc > 100_000) {
                            Log::warning(SeedersTemplating::SCAPE_MSG);
                            break;
                        }
                        $$varName = (string) Str::uuid();
                        $acc += 1;
                    } while (in_array($$varName, $uuids, true));
                }
                ChartOfAccountType::create(
                    [
                        'id' => $coaTpId,
                        ChartsConstants::COL_NM => 'admin-type-chart',
                        DatabaseConstants::TABLE_CREATOR => $company?->id ?? DatabaseConstants::DEFAULT_UUID

                    ]
                );
                ChartOfAccountSubType::create(
                    [
                        'id' => $coaSbTpId,
                        ChartsConstants::COL_NM => 'admin-subtype-chart',
                        ChartsConstants::COL_TP => $coaTpId,
                        DatabaseConstants::TABLE_CREATOR => $company?->id ?? DatabaseConstants::DEFAULT_UUID

                    ]
                );
                ChartOfAccount::create(
                    [
                        'id' => $coaId,
                        ChartsConstants::COL_NM => 'admin-chart',
                        ChartsConstants::COL_TP => $coaTpId,
                        ChartsConstants::COL_SUBTP => $coaSbTpId,
                        DatabaseConstants::TABLE_CREATOR => $company?->id ?? DatabaseConstants::DEFAULT_UUID
                    ],
                );
                BankAccount::create(
                    [
                        'id' => $baId,
                        BanksConstants::COL_HNM => 'cash',
                        BanksConstants::COL_NM => 'Nova Prestech Teste',
                        BanksConstants::COL_ACC_N => $baAcc + 1,
                        BanksConstants::COL_OB => 'R$0.00',
                        BanksConstants::COL_CT => '+55 21 9000-000',
                        BanksConstants::COL_ADR => 'Rua Francisco Manuel, 99A — Benfica, Rio de Janeiro, RJ, Brasil',
                        BanksConstants::COL_COA => $coaId,
                        DatabaseConstants::TABLE_CREATOR => $company?->id ?? DatabaseConstants::DEFAULT_UUID,
                    ]
                );
                $doneTime = now()->toDateTimeString();
                $output->writeln('<info>                     Done creating bank accounts at '
                    . $doneTime . ' !</info>');
            } catch (QueryException $e) {
                $msg = 'Database error while creating bank account: ' . $e->getMessage();
                Log::error($msg, ['exception' => $e]);
                $output->writeln("<error>$msg</error>");
            } catch (\Throwable $e) {
                $msg = 'Unexpected error while creating bank account: ' . $e->getMessage();
                Log::critical($msg, ['exception' => $e]);
                $output->writeln("<error>$msg</error>");
            }
            try {
                $output->writeln('<comment> Seeding Clients with ' . static::class . '</comment>');
                $clientId = '';
                $clientAcc = 0;
                do {
                    if ($clientAcc > 100_000) {
                        Log::warning(SeedersTemplating::SCAPE_MSG);
                        break;
                    }
                    $clientId = (string) Str::uuid();
                    $clientAcc += 1;
                } while (in_array($clientId, $uuids, true));
                $uuids[] = $clientId;
                $clientRole = Role::create(
                    [
                        'id'   => $clientId,
                        'name' => PermissionsConstants::CL,
                        DatabaseConstants::TABLE_CREATOR => $company?->id ?? DatabaseConstants::DEFAULT_UUID,
                    ]
                );
                foreach (array_column(SeedersTemplating::CLIENT_PERMS, 'name') as $clPerm)
                    $clientRole->givePermissionTo($clPerm);
                $clientPassword = $faker->password(12, 20);
                $clientUser = User::create(
                    [
                        UsersConstants::COL_NM                    => $faker->name,
                        UsersConstants::COL_EM                   => $faker->unique()->safeEmail,
                        UsersConstants::COL_PW                => bcrypt($clientPassword),
                        UsersConstants::COL_TP => PermissionsConstants::CL,
                        UsersConstants::COL_LG => DatabaseConstants::DEFAULT_LANG,
                        UsersConstants::COL_AV                  => $faker->imageUrl(200, 200, 'people'),
                        UsersConstants::COL_EM_V_AT => now()->toDateTimeString(),
                        UsersConstants::COL_DPL => $pipeline?->id ?? DatabaseConstants::DEFAULT_PIPELINE,
                        UsersConstants::COL_PL => PlansTableSeeder::$planId ?? DatabaseConstants::DEFAULT_PLAN,
                        DatabaseConstants::TABLE_CREATOR => $company?->id ?? DatabaseConstants::DEFAULT_UUID,
                    ]
                );
                $clientUser->assignRole($clientRole);
                $doneTime = now()->toDateTimeString();
                $output->writeln('<info>                                Done creating clients at '
                    . $doneTime . ' !</info>');
                if ($clientUser instanceof User) {
                    $client = Client::create([
                        UsersConstants::COL_NM        => $clientUser->{UsersConstants::COL_NM},
                        UsersConstants::COL_EM        => $clientUser->{UsersConstants::COL_EM},
                        UsersConstants::COL_EM_V_AT   => $clientUser->{UsersConstants::COL_EM_V_AT},
                        UsersConstants::COL_PW        => $clientUser->{UsersConstants::COL_PW},
                        UsersConstants::COL_LG        => $clientUser->{UsersConstants::COL_LG},
                        UsersConstants::COL_IA        => 1,
                        UsersConstants::COL_USER_ID   => $clientUser->id,
                        UsersConstants::COL_TEL       => $faker->phoneNumber,
                        UsersConstants::COL_ADR       => $faker->address,
                        UsersConstants::COL_IU        => true,
                        UsersConstants::COL_AV        => $clientUser->{UsersConstants::COL_AV},
                        UsersConstants::COL_MSG_CL    => '#2180f3',
                        UsersConstants::COL_DEL_STT   => 1,
                        DatabaseConstants::TABLE_CREATOR => $company?->id ?? DatabaseConstants::DEFAULT_UUID,
                    ]);
                    $stageTemplate = ProjectStages::create([
                        ProjectsConstants::COL_NM         => 'Default Stage Set',
                        ProjectsConstants::COL_CL         => 'primary',
                        ActivitiesConstants::COL_OD       => 0,
                        DatabaseConstants::TABLE_CREATOR  => $company?->id ?? DatabaseConstants::DEFAULT_UUID,
                    ]);
                    $projectPassword = $faker->password(12, 20);
                    $project = Project::create([
                        ProjectsConstants::COL_NM           => "Demo Project — {$faker->name}",
                        ProjectsConstants::COL_S_DT         => now()->toDateString(),
                        ProjectsConstants::COL_E_DT         => now()->addMonth()->toDateString(),
                        ProjectsConstants::COL_CLIENT_ID    => $client->id,
                        ProjectsConstants::COL_STAGE_ID     => $stageTemplate->id,
                        ProjectsConstants::COL_DESCRIPTION  => 'This is a demo project.',
                        ProjectsConstants::COL_STATUS       => ProjectsConstants::STT_INP_K,
                        ProjectsConstants::COL_BUDGET       => 25_000,
                        ProjectsConstants::COL_E_HRS        => '160',
                        ProjectsConstants::COL_PASSWORD     => bcrypt($projectPassword),
                        ProjectsConstants::COL_COPYLINK     => url('/') . Utility::generateRandomBase64Path(),
                        ProjectsConstants::COL_TAGS         => json_encode(['demo', 'seed']),
                        DatabaseConstants::TABLE_CREATOR    => $company?->id ?? DatabaseConstants::DEFAULT_UUID,
                    ]);
                };
            } catch (PermissionDoesNotExist $e) {
                $msg = 'Tried to assign a nonexistent permission to Client role: ' . $e->getMessage();
                Log::warning($msg, ['exception' => $e]);
                $output->writeln("<error>$msg</error>");
            } catch (QueryException $e) {
                $msg = 'Database error while creating Client role: ' . $e->getMessage();
                Log::error($msg, ['exception' => $e]);
                $output->writeln("<error>$msg</error>");
            } catch (\Throwable $e) {
                $msg = 'Unexpected error while creating Client role: ' . $e->getMessage();
                Log::critical($msg, ['exception' => $e]);
                $output->writeln("<error>$msg</error>");
            }
        } catch (PermissionDoesNotExist $e) {
            $msg = 'Tried to assign a nonexistent permission to Company role: ' . $e->getMessage();
            Log::warning($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        } catch (QueryException $e) {
            $msg = 'Database error while creating Company role: ' . $e->getMessage();
            Log::error($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        } catch (\Throwable $e) {
            $msg = 'Unexpected error while creating Company role: ' . $e->getMessage();
            Log::critical($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        }
        try {
            $output->writeln('<comment> Seeding Customers with ' . static::class . '</comment>');
            $customerId = '';
            $customerAcc = 0;
            do {
                if ($customerAcc > 100_000) {
                    Log::warning(SeedersTemplating::SCAPE_MSG);
                    break;
                }
                $customerId = (string) Str::uuid();
                $customerAcc += 1;
            } while (in_array($customerId, $uuids, true));
            $uuids[] = $customerId;
            $customerRole = Role::create(
                [
                    'id'   => $customerId,
                    'name' => PermissionsConstants::CT,
                    DatabaseConstants::TABLE_CREATOR => DatabaseConstants::DEFAULT_UUID,
                ]
            );
            foreach (array_column(SeedersTemplating::CUSTOMER_PERMS, 'name') as $ctPerm)
                $customerRole->givePermissionTo($ctPerm);
            $doneTime = now()->toDateTimeString();
            $output->writeln('<info>                          Done creating customers at '
                . $doneTime . ' !</info>');
        } catch (PermissionDoesNotExist $e) {
            $msg = 'Tried to assign a nonexistent permission to Customer role: ' . $e->getMessage();
            Log::warning($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        } catch (QueryException $e) {
            $msg = 'Database error while creating Customer role: ' . $e->getMessage();
            Log::error($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        } catch (\Throwable $e) {
            $msg = 'Unexpected error while creating Customer role: ' . $e->getMessage();
            Log::critical($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        }
        try {
            $output->writeln('<comment> Seeding Vendors with ' . static::class . '</comment>');
            $vendorAcc = 0;
            $vendorId = '';
            do {
                if ($vendorAcc > 100_000) {
                    Log::warning(SeedersTemplating::SCAPE_MSG);
                    break;
                }
                $vendorId = (string) Str::uuid();
                $vendorAcc += 1;
            } while (in_array($vendorId, $uuids, true));
            $uuids[] = $vendorId;
            $vendorRole = Role::create(
                [
                    'id'   => $vendorId,
                    'name' => PermissionsConstants::VD,
                    DatabaseConstants::TABLE_CREATOR => DatabaseConstants::DEFAULT_UUID,
                ]
            );
            foreach (array_column(SeedersTemplating::VENDOR_PERMS, 'name') as $vdPerm)
                $vendorRole->givePermissionTo($vdPerm);
            $doneTime = now()->toDateTimeString();
            $output->writeln('<info>                             Done creating vendors at '
                . $doneTime . ' !</info>');
        } catch (PermissionDoesNotExist $e) {
            $msg = 'Tried to assign a nonexistent permission to Vendor role: ' . $e->getMessage();
            Log::warning($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        } catch (QueryException $e) {
            $msg = 'Database error while creating Vendor role: ' . $e->getMessage();
            Log::error($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        } catch (\Throwable $e) {
            $msg = 'Unexpected error while creating Vendor role: ' . $e->getMessage();
            Log::critical($msg, ['exception' => $e]);
            $output->writeln("<error>$msg</error>");
        }
        $output->writeln('<question>----------- End of Users Seeding ------------</question>');
        $output->writeln('');
        return [
            PermissionsConstants::SA => $superAdmin,
            PermissionsConstants::ADM => $admin,
            PermissionsConstants::ACT => $accountant,
            PermissionsConstants::CPN => $company,
            PermissionsConstants::CL . 'User' => $clientUser,
            PermissionsConstants::CL => $client,
            'project' => $project,
            'pipeline' => $pipeline,
            'uuids' => $uuids
        ];
    }
}
