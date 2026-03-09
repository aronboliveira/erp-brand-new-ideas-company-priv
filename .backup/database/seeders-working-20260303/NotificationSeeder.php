<?php

namespace Database\Seeders;

use App\Config\Constants\{
    DatabaseConstants,
    NotificationsConstants,
    SeedersTemplating,
    UsersConstants
};
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{DB, Log, Notification};
use Symfony\Component\Console\Output\ConsoleOutput;

class NotificationSeeder extends Seeder
{
    private const ENTITY = 'notification';
    public function run(): void
    {
        try {
            Notification::fake();
            $now = now();
            $notifications = SeedersTemplating::NOTIFICATIONS_DICT;
            $defaultTemplate = SeedersTemplating::NOTIFICATIONS_DEFAULT_TEMPLATES;
            $templateRows = [];
            $creatorId = DB::table(DatabaseConstants::TABLE_USERS)->value('id');
            if (!$creatorId) {
                $creatorId = DatabaseConstants::DEFAULT_UUID;
                DB::table(DatabaseConstants::TABLE_USERS)->insert([
                    'id'                => $creatorId,
                    UsersConstants::COL_NM              => 'System',
                    UsersConstants::COL_EM             => 'system@yourapp.test',
                    UsersConstants::COL_PW          => bcrypt(Str::random(32)),
                    UsersConstants::COL_EM_V_AT => $now,
                    UsersConstants::COL_C_AT        => $now,
                    UsersConstants::COL_U_AT        => $now,
                ]);
            }
            $output = new ConsoleOutput();
            $output->writeln('');
            $output->writeln('<question>------------- ### Notifications Seeding ### ------------- </question>');
            $output->writeln('<comment> Seeding Notifications Rows with ' . static::class . '</comment>');
            $uuids = [];
            $stopSeedingNotifications = false;
            foreach ($notifications as $slug => $name) {
                if ($stopSeedingNotifications) break;
                $acc = 0;
                $notificationId = '';
                do {
                    if ($acc > 100_000) {
                        Log::warning(SeedersTemplating::SCAPE_MSG);
                        $stopSeedingNotifications = true;
                        break;
                    }
                    $notificationId = (string) Str::uuid();
                    $acc += 1;
                } while (in_array($notificationId, $uuids, true));
                $templateRows[] = [
                    'id'         => $notificationId,
                    NotificationsConstants::COL_NM       => $name,
                    NotificationsConstants::COL_R_SL       => $slug,
                    NotificationsConstants::COL_R_TP       => UsersConstants::COL_EM,
                    DatabaseConstants::COL_TABLE_CREATOR => $creatorId,
                    NotificationsConstants::COL_C_AT => $now,
                    NotificationsConstants::COL_U_AT => $now,
                ];
            }
            DB::table(DatabaseConstants::TABLE_NOTIFICATION_TEMPLATES)->insert($templateRows);
            $idsBySlug = DB::table(DatabaseConstants::TABLE_NOTIFICATION_TEMPLATES)
                ->whereIn(NotificationsConstants::COL_R_SL, array_keys($notifications))
                ->pluck('id', NotificationsConstants::COL_R_SL)
                ->all();
            $output->writeln('<info>                            Done creating Notification Templates!</info>');
            $output->writeln('<comment> Seeding Notifications Templates with ' . static::class . '</comment>');
            $langRows = [];
            $stopSeedingRows = false;
            foreach ($notifications as $slug => $_) {
                if ($stopSeedingRows) break;
                $parentId = $idsBySlug[$slug];
                $variables = $defaultTemplate[self::ENTITY][$slug][NotificationsConstants::COL_TEMPL_VARS];
                foreach ($defaultTemplate[self::ENTITY][$slug][NotificationsConstants::COL_TEMPL_LG] as $lang => $content) {
                    $acc = 0;
                    $templateId = '';
                    do {
                        if ($acc > 100_000) {
                            Log::warning(SeedersTemplating::SCAPE_MSG);
                            $stopSeedingRows = true;
                            break;
                        }
                        $templateId = (string) Str::uuid();
                        $acc += 1;
                    } while (in_array($templateId, $uuids, true));
                    $langRows[] = [
                        'id'         => $templateId,
                        NotificationsConstants::COL_TEMPL_PR  => $parentId,
                        NotificationsConstants::COL_TEMPL_LG       => $lang,
                        NotificationsConstants::COL_TEMPL_VARS  => $variables,
                        NotificationsConstants::COL_TEMPL_CT    => $content,
                        DatabaseConstants::COL_TABLE_CREATOR => $creatorId,
                        NotificationsConstants::COL_C_AT => $now,
                        NotificationsConstants::COL_U_AT => $now,
                    ];
                }
            }
            foreach (array_chunk($langRows, 200) as $batch)
                DB::table(DatabaseConstants::TABLE_NOTIFICATION_TEMPLATE_LANGS)
                    ->insert($batch);
            $output->writeln('<info>                                 Done creating Notifications!</info>');
            $output->writeln('<question>----------- End of Notifications Seeding ------------</question>');
            $output->writeln('');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
