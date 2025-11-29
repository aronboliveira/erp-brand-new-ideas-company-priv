<?php

namespace Database\Seeders;

use App\Config\Constants\{
    ActivitiesConstants,
    BanksConstants as BKC,
    ChartsConstants as CHTC,
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    ProjectsConstants,
    SeedersTemplating as SDT,
    SettingsConstants as ST,
    UsersConstants as UC
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
    Utility as U
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
            $superAdmin = $settled[PMC::SA];
            $admin = $settled[PMC::ADM];
            $accountant = $settled[PMC::ACT];
            $company = $settled[PMC::CPN];
            $clientUser = $settled[PMC::CL . 'User'];
            $project = $settled['project'];
            $uuids = $settled['uuids'];
            $users = [$superAdmin, $admin, $accountant, $company, $clientUser];
            $utilityTasks = [];
            $employeePairs = [
                [$accountant->id ?? DC::DEFAULT_UUID, $company->id   ?? DC::DEFAULT_UUID],
                [$superAdmin->id ?? DC::DEFAULT_UUID, $company->id  ?? DC::DEFAULT_UUID],
                [$admin->id      ?? DC::DEFAULT_UUID, $company->id  ?? DC::DEFAULT_UUID],
                [$company->id    ?? DC::DEFAULT_UUID, $superAdmin->id ?? DC::DEFAULT_UUID],
            ];
            foreach ($employeePairs as $args)
                $utilityTasks[] = [U::EMP_DTLS, $args];
            foreach ([$admin, $accountant, $company, $superAdmin] as $u)
                $utilityTasks[] = [U::COA_TP_DT, [$u->id ?? DC::DEFAULT_UUID]];
            foreach ([$accountant, $admin, $company, $superAdmin] as $u)
                $utilityTasks[] = [U::COA_DATA, [$u]];
            foreach ([$accountant, $admin, $company, $superAdmin] as $u)
                $utilityTasks[] = [U::PPL_LD_DL_STG, [$u->id ?? DC::DEFAULT_UUID]];
            foreach ([$company, $superAdmin] as $u)
                $utilityTasks[] = [
                    U::PRJ_TSK_STGS,
                    [
                        $project->id ?? DC::DEFAULT_PIPELINE,
                        $u->id      ?? DC::DEFAULT_UUID,
                    ]
                ];
            foreach ([$accountant, $admin, $company, $superAdmin] as $u)
                $utilityTasks[] = ['labels', [$u->id ?? DC::DEFAULT_UUID]];
            foreach ([$company, $superAdmin] as $u)
                $utilityTasks[] = ['sources', [$u->id ?? DC::DEFAULT_UUID]];
            foreach ([$admin, $company, $superAdmin] as $u)
                $utilityTasks[] = [U::JB_STG, [$u->id ?? DC::DEFAULT_UUID]];
            Log::debug("Dinamalically calling the utility methods to populate project-related tables...");
            if ($project instanceof Project) Log::debug("Project {$project->id} is a project instance");
            foreach ($utilityTasks as [$method, $args]) {
                if ($method === U::PRJ_TSK_STGS && !$project?->id) {
                    Log::error("Failed to call user function for Project Task Stages");
                    continue;
                };
                $allNull = array_reduce(
                    $args,
                    fn(bool $carry, mixed $item): bool => $carry && is_null($item),
                    true
                );
                if (!$allNull)
                    call_user_func_array([U::class, $method], $args);
            };
            $uniqueLanguages = [];
            foreach ($users as $user) {
                $lang = $user?->lang ?? DC::DEFAULT_LANG;
                $id = $user?->id ?? DC::DEFAULT_UUID;
                if (!isset($uniqueLanguages[$lang]))
                    $uniqueLanguages[$lang] = $id;
            };
            foreach ($uniqueLanguages as $lang => $id) {
                $friendlyLangName = U::langList()[is_string($lang) ? $lang : (string) $lang];
                Log::debug("Loading language resources for {$friendlyLangName}, id {$id}");
                U::languageCreate($id);
            }
            foreach ($users as $user) {
                if (!($user instanceof User)) continue;
                $user?->userDefaultData();
                $user?->defaultEmail($user?->id ?? DC::DEFAULT_UUID);
                $user?->userDefaultWarehouse();
                GeneratedOfferLetter::defaultOfferLetter($user?->id ?? DC::DEFAULT_UUID);
                ExperienceCertificate::defaultExpCertificate($user?->id ?? DC::DEFAULT_UUID);
                JoiningLetter::defaultJoiningLetter($user?->id ?? DC::DEFAULT_UUID);
                Noc::defaultNocCertificate($user?->id ?? DC::DEFAULT_UUID);
            }
            $disks          = [ST::LC, ST::WSB, ST::S3];
            $validationExts = ST::FMTS_UP_DEF;
            $maxUploadSize  = (int) ST::MAX_U_SIZE_DEF;
            $settingsToInsert = [];
            foreach ($disks as $disk) {
                $primarySettingsId = '';
                $primarySettingsAcc = 0;
                do {
                    if ($primarySettingsAcc > 100_000) {
                        Log::warning(SDT::SCAPE_MSG);
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
                        Log::warning(SDT::SCAPE_MSG);
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
                    DC::TABLE_CREATOR => DC::DEFAULT_UUID,
                ] + self::timestamps();
                $settingsToInsert[] = [
                    'id'         => $secondarySettingsId,
                    'name'       => "{$disk}_max_upload_size",
                    'value'      => $maxUploadSize,
                    DC::TABLE_CREATOR => DC::DEFAULT_UUID,
                ] + self::timestamps();
            }
            DB::table(DC::TABLE_SETTINGS)
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
            UC::COL_C_AT => $createdAt ?? $now,
            UC::COL_U_AT => $updatedAt ?? $now,
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
        $arrPermissions = SDT::PERMISSIONS;
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
                $p[UC::COL_C_AT] = $now;
                $p[UC::COL_U_AT] = $now;
                if (!isset($p[$gn]) || !$p[$gn]) $p[$gn] = 'web';
            }
            $arrPermissions = collect(array_map(
                'unserialize',
                array_unique(
                    array_map('serialize', $arrPermissions)
                )
            ))
                ->unique(fn($r) => ($r[$gn] ?? 'web') . '|' . $r['name'])
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
                    Log::warning(SDT::SCAPE_MSG);
                    break;
                }
                $superAdminId = (string) Str::uuid();
                $saAcc += 1;
            } while (in_array($superAdminId, $uuids, true));
            $uuids[] = $superAdminId;
            $superAdminRole = Role::create(
                [
                    'id'   => $superAdminId,
                    'name' => PMC::SA,
                    DC::TABLE_CREATOR => DC::DEFAULT_UUID,
                ]
            );
            $superAdmin = User::create(
                [
                    UC::COL_NM => $faker->name,
                    UC::COL_EM => 'suporte@prestech.com.br', // TODO CHANGE AFTER TESTING
                    UC::COL_PW => Hash::make('123456789qwe.*'), // TODO CHANGE AFTER TESTING
                    UC::COL_TP => PMC::SA,
                    UC::COL_LG => DC::DEFAULT_LANG,
                    UC::COL_AV =>  $faker->imageUrl(200, 200, 'people'),
                    UC::COL_EM_V_AT => now()->toDateTimeString(),
                    UC::COL_DPL => DC::DEFAULT_PIPELINE,
                    UC::COL_PL => DC::DEFAULT_PLAN,
                    DC::TABLE_CREATOR => DC::DEFAULT_UUID,
                ]
            );
            $superAdmin->assignRole($superAdminRole);
            foreach (array_column(SDT::SA_PERMS, 'name') as $saPerm)
                $superAdminRole->givePermissionTo($saPerm);
            Log::debug('Super Admin created with permissions: ' . json_encode($superAdminRole->getAllPermissions()->pluck('name')));
            try {
                $pipeline = Pipeline::create([
                    ProjectsConstants::COL_PPL_NM       => 'Default Pipeline',
                    ActivitiesConstants::COL_OD         => 0,
                    DC::TABLE_CREATOR    => DC::DEFAULT_UUID,
                ]);
                if ($superAdmin instanceof User)
                    $saPipeline = Pipeline::create([
                        ProjectsConstants::COL_PPL_NM       => 'Default Super Admin Pipeline',
                        ActivitiesConstants::COL_OD         => 0,
                        DC::TABLE_CREATOR    => $superAdmin->id,
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
                    Log::warning(SDT::SCAPE_MSG);
                    break;
                }
                $adminId = (string) Str::uuid();
                $saAcc += 1;
            } while (in_array($adminId, $uuids, true));
            $uuids[] = $adminId;
            $adminRole = Role::create(
                [
                    'id'   => $adminId,
                    'name' => PMC::ADM,
                    DC::TABLE_CREATOR => DC::DEFAULT_UUID,
                ]
            );
            foreach (array_column(SDT::SA_PERMS, 'name') as $saPerm)
                $adminRole->givePermissionTo($saPerm);
            $adminPassword = $faker->password(12, 20);
            $admin = User::create(
                [
                    UC::COL_NM                    => $faker->name,
                    UC::COL_EM                   => $faker->unique()->safeEmail,
                    UC::COL_PW                => bcrypt($adminPassword),
                    UC::COL_TP => PMC::ADM,
                    UC::COL_LG => DC::DEFAULT_LANG,
                    UC::COL_AV                  => $faker->imageUrl(200, 200, 'people'),
                    UC::COL_EM_V_AT => now()->toDateTimeString(),
                    UC::COL_DPL => $pipeline?->id ?? DC::DEFAULT_PIPELINE,
                    UC::COL_PL => PlansTableSeeder::$planId ?? DC::DEFAULT_PLAN,
                    DC::TABLE_CREATOR => DC::DEFAULT_UUID,
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
                    Log::warning(SDT::SCAPE_MSG);
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
                    DC::TABLE_CREATOR => DC::DEFAULT_UUID,
                ]
            );
            foreach (array_column(SDT::COMPANY_PERMS, 'name') as $cpPerm)
                $companyRole->givePermissionTo($cpPerm);
            $companyPassword = $faker->password(12, 20);
            $company = User::create(
                [
                    UC::COL_NM                    => $faker->name,
                    UC::COL_EM                   => $faker->unique()->safeEmail,
                    UC::COL_PW                => bcrypt($companyPassword),
                    UC::COL_TP => PMC::CPN,
                    UC::COL_LG => DC::DEFAULT_LANG,
                    UC::COL_AV                  => $faker->imageUrl(200, 200, 'people'),
                    UC::COL_EM_V_AT => now()->toDateTimeString(),
                    UC::COL_DPL => $pipeline?->id ?? DC::DEFAULT_PIPELINE,
                    UC::COL_PL => PlansTableSeeder::$planId ?? DC::DEFAULT_PLAN,
                    DC::TABLE_CREATOR => DC::DEFAULT_UUID,
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
                        Log::warning(SDT::SCAPE_MSG);
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
                        DC::TABLE_CREATOR => $company?->id ?? DC::DEFAULT_UUID,
                    ]
                );
                foreach (array_column(SDT::ACCOUNTANT_PERMS, 'name') as $acPerm)
                    $accountantRole->givePermissionTo($acPerm);
                $accPassword = $faker->password(12, 20);
                $accountant = User::create(
                    [
                        UC::COL_NM                    => $faker->name,
                        UC::COL_EM                   => $faker->unique()->safeEmail,
                        UC::COL_PW                => bcrypt($accPassword),
                        UC::COL_TP => PMC::ACT,
                        UC::COL_LG => DC::DEFAULT_LANG,
                        UC::COL_AV                  => $faker->imageUrl(200, 200, 'people'),
                        UC::COL_EM_V_AT => now()->toDateTimeString(),
                        UC::COL_DPL => $pipeline?->id ?? DC::DEFAULT_PIPELINE,
                        UC::COL_PL => PlansTableSeeder::$planId ?? DC::DEFAULT_PLAN,
                        DC::TABLE_CREATOR => $company?->id ?? DC::DEFAULT_UUID,
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
                $output->writeln('<comment>Creating bank account related models...</comment>');
                $usedUuids = is_array($uuids ?? null) ? $uuids : [];
                $ids = [
                    'baId'     => null,
                    'coaId'    => null,
                    'coaTpId'  => null,
                    'coaSbTpId' => null,
                ];
                foreach (array_keys($ids) as $key) {
                    $acc = 0;
                    do {
                        if ($acc > 100_000) {
                            Log::warning('Safe-guard hit while generating UUIDs for bank account mocks.');
                            break;
                        }
                        $uuid = (string) Str::uuid();
                        $acc += 1;
                    } while (in_array($uuid, $usedUuids, true));

                    $ids[$key]  = $uuid;
                    $usedUuids[] = $uuid;
                }

                $baId    = $ids['baId'];
                $coaId   = $ids['coaId'];
                $coaTpId = $ids['coaTpId'];
                $coaSbTpId = $ids['coaSbTpId'];

                $typeAttributes = [
                    'X' => [
                        'type'   => 'timestamp',
                        'unit'   => 'MM',     // mês
                        'values' => [],
                    ],
                    'Y' => [
                        'type'   => 'value',
                        'unit'   => null,
                        'values' => [],
                    ],
                ];

                $output->writeln('<comment>Creating chart of account type instance...</comment>');
                $type = ChartOfAccountType::create([
                    'id'              => $coaTpId,
                    CHTC::COL_CD      => 'ADMIN-TYPE-CHART',
                    'category'        => CHTC::AST,
                    'description'     => 'Admin chart type created for bootstrap and tests.',
                    'rules'           => $typeAttributes,
                    'units'           => $typeAttributes,
                    CHTC::COL_NM      => 'admin-type-chart',
                    DC::TABLE_CREATOR => $company?->id ?? DC::DEFAULT_UUID,
                ]);

                $output->writeln('<comment>Creating chart of account subtype instance...</comment>');
                $subType = ChartOfAccountSubType::create([
                    'id'              => $coaSbTpId,
                    CHTC::COL_CD      => 'ADMIN-SUBTYPE-CHART',
                    CHTC::COL_NM      => 'admin-subtype-chart',
                    CHTC::COL_TP      => $type->id,
                    'description'     => 'Admin chart subtype created for bootstrap and tests.',
                    'rules'           => [], // deixa o modelo aplicar defaults
                    DC::TABLE_CREATOR => $company?->id ?? DC::DEFAULT_UUID,
                ]);

                $output->writeln('<comment>Creating chart of account instance...</comment>');
                ChartOfAccount::create([
                    'id'              => $coaId,
                    CHTC::COL_CD      => 'ADMIN-CHART',
                    CHTC::COL_NM      => 'admin-chart',
                    CHTC::COL_TP      => $type->id,
                    CHTC::COL_SUBTP   => $subType->id,
                    DC::TABLE_CREATOR => $company?->id ?? DC::DEFAULT_UUID,
                ]);

                $output->writeln('<comment>Creating bank account instance...</comment>');
                $output->writeln('<comment> Using Chart of Account ID: ' . $coaId . ' for Bank Account </comment>');
                $output->writeln('<comment> Using Bank Account ID: ' . $baId . ' and Account Number: ' . $baAcc . '</comment>');
                $output->writeln('<comment>' . ($company?->id ?? DC::DEFAULT_UUID) . '</comment>');

                BankAccount::create([
                    'id'              => $baId,
                    BKC::COL_HNM      => 'cash',
                    BKC::COL_NM       => 'Nova Prestech Teste',
                    BKC::COL_ACC_N    => $baAcc,
                    BKC::COL_OB       => '0.00',
                    BKC::COL_CT       => '+55 21 9000-000',
                    BKC::COL_ADR      => 'Rua Francisco Manuel, 99A — Benfica, Rio de Janeiro, RJ, Brasil',
                    BKC::COL_COA      => $coaId,
                    DC::TABLE_CREATOR => $company?->id ?? DC::DEFAULT_UUID,
                ]);
                $doneTime = now()->toDateTimeString();
                $output->writeln('<info>                     Done creating bank accounts at ' . $doneTime . ' !</info>');
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
                        Log::warning(SDT::SCAPE_MSG);
                        break;
                    }
                    $clientId = (string) Str::uuid();
                    $clientAcc += 1;
                } while (in_array($clientId, $uuids, true));
                $uuids[] = $clientId;
                $clientRole = Role::create(
                    [
                        'id'   => $clientId,
                        'name' => PMC::CL,
                        DC::TABLE_CREATOR => $company?->id ?? DC::DEFAULT_UUID,
                    ]
                );
                foreach (array_column(SDT::CLIENT_PERMS, 'name') as $clPerm)
                    $clientRole->givePermissionTo($clPerm);
                $clientPassword = $faker->password(12, 20);
                $clientUser = User::create(
                    [
                        UC::COL_NM                    => $faker->name,
                        UC::COL_EM                   => $faker->unique()->safeEmail,
                        UC::COL_PW                => bcrypt($clientPassword),
                        UC::COL_TP => PMC::CL,
                        UC::COL_LG => DC::DEFAULT_LANG,
                        UC::COL_AV                  => $faker->imageUrl(200, 200, 'people'),
                        UC::COL_EM_V_AT => now()->toDateTimeString(),
                        UC::COL_DPL => $pipeline?->id ?? DC::DEFAULT_PIPELINE,
                        UC::COL_PL => PlansTableSeeder::$planId ?? DC::DEFAULT_PLAN,
                        DC::TABLE_CREATOR => $company?->id ?? DC::DEFAULT_UUID,
                    ]
                );
                $clientUser->assignRole($clientRole);
                $doneTime = now()->toDateTimeString();
                $output->writeln('<info>                                Done creating clients at '
                    . $doneTime . ' !</info>');
                if ($clientUser instanceof User) {
                    $client = Client::create([
                        UC::COL_NM        => $clientUser->{UC::COL_NM},
                        UC::COL_EM        => $clientUser->{UC::COL_EM},
                        UC::COL_EM_V_AT   => $clientUser->{UC::COL_EM_V_AT},
                        UC::COL_PW        => $clientUser->{UC::COL_PW},
                        UC::COL_LG        => $clientUser->{UC::COL_LG},
                        UC::COL_IA        => 1,
                        UC::COL_USER_ID   => $clientUser->id,
                        UC::COL_TEL       => $faker->phoneNumber,
                        UC::COL_ADR       => $faker->address,
                        UC::COL_IU        => true,
                        UC::COL_AV        => $clientUser->{UC::COL_AV},
                        UC::COL_MSG_CL    => '#2180f3',
                        UC::COL_DEL_STT   => 1,
                        DC::TABLE_CREATOR => $company?->id ?? DC::DEFAULT_UUID,
                    ]);
                    $stageTemplate = ProjectStages::create([
                        ProjectsConstants::COL_NM         => 'Default Stage Set',
                        ProjectsConstants::COL_CL         => 'primary',
                        ActivitiesConstants::COL_OD       => 0,
                        DC::TABLE_CREATOR  => $company?->id ?? DC::DEFAULT_UUID,
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
                        ProjectsConstants::COL_COPYLINK     => url('/') . U::generateRandomBase64Path(),
                        ProjectsConstants::COL_TAGS         => json_encode(['demo', 'seed']),
                        DC::TABLE_CREATOR    => $company?->id ?? DC::DEFAULT_UUID,
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
                    Log::warning(SDT::SCAPE_MSG);
                    break;
                }
                $customerId = (string) Str::uuid();
                $customerAcc += 1;
            } while (in_array($customerId, $uuids, true));
            $uuids[] = $customerId;
            $customerRole = Role::create(
                [
                    'id'   => $customerId,
                    'name' => PMC::CT,
                    DC::TABLE_CREATOR => DC::DEFAULT_UUID,
                ]
            );
            foreach (array_column(SDT::CUSTOMER_PERMS, 'name') as $ctPerm)
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
                    Log::warning(SDT::SCAPE_MSG);
                    break;
                }
                $vendorId = (string) Str::uuid();
                $vendorAcc += 1;
            } while (in_array($vendorId, $uuids, true));
            $uuids[] = $vendorId;
            $vendorRole = Role::create(
                [
                    'id'   => $vendorId,
                    'name' => PMC::VD,
                    DC::TABLE_CREATOR => DC::DEFAULT_UUID,
                ]
            );
            foreach (array_column(SDT::VENDOR_PERMS, 'name') as $vdPerm)
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
            PMC::SA => $superAdmin,
            PMC::ADM => $admin,
            PMC::ACT => $accountant,
            PMC::CPN => $company,
            PMC::CL . 'User' => $clientUser,
            PMC::CL => $client,
            'project' => $project,
            'pipeline' => $pipeline,
            'uuids' => $uuids
        ];
    }
}
