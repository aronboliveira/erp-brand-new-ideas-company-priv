<?php

namespace Database\Seeders;

use App\Config\Constants\{
	DatabaseConstants as DC,
	MessagesConstants as MC,
	UsersConstants as UC
};
use App\Enums\{
	MessagingPlatform,
	NotificationTemplateType
};
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class NotificationsLateSeeder extends Seeder
{
	public function run(): void
	{
		// Sanidade da tabela de notifications
		if (!Schema::hasTable(DC::TABLE_NTF)) {
			$this->command?->warn('NotificationSeeder: tabela de notifications ausente. Seeder abortado.');
			return;
		}

		// Tipos realmente usados em notification_templates (DC::TABLE_NOTIFICATION_TEMPLATES)
		if (!Schema::hasTable(DC::TABLE_NOTIFICATION_TEMPLATES)) {
			$this->command?->warn('NotificationSeeder: tabela de notification_templates ausente. Nada a semear.');
			return;
		}

		$rawTypes = DB::table(DC::TABLE_NOTIFICATION_TEMPLATES)
			->select('type')
			->distinct()
			->pluck('type')
			->all();

		$usedTypeEnums = [];
		foreach ($rawTypes as $rawType) {
			$enum                         = NotificationTemplateType::normalize((string) $rawType);
			$usedTypeEnums[$enum->value] = $enum; // chave pelo value para garantir unicidade
		}
		$usedTypes = array_values($usedTypeEnums);

		if (empty($usedTypes)) {
			$this->command?->warn('NotificationSeeder: nenhum NotificationTemplateType encontrado em ' . DC::TABLE_NOTIFICATION_TEMPLATES . '.');
			return;
		}

		// Usuários (recipient / sender) – leitura via DB::table (raw query)
		if (!Schema::hasTable(DC::TABLE_USERS)) {
			$this->command?->warn('NotificationSeeder: tabela de usuários ausente. Seeder abortado.');
			return;
		}

		$userIds = DB::table(DC::TABLE_USERS)
			->select('id')
			->pluck('id')
			->all();

		if (empty($userIds)) {
			$this->command?->warn('NotificationSeeder: nenhum usuário em ' . DC::TABLE_USERS . '. Seeder abortado.');
			return;
		}

		$platforms = MessagingPlatform::cases();
		if (empty($platforms)) {
			$this->command?->warn('NotificationSeeder: enum MessagingPlatform sem casos. Nada a semear.');
			return;
		}

		// N: multiplicador para 64 × N
		$baseTypesCount = count($usedTypes);
		$multiplier     = $this->command instanceof \Illuminate\Console\Command
			&& $this->command->hasOption('count')
			? max(1, (int) $this->command->option('count'))
			: max(1, $baseTypesCount);

		$targetTotal = 4 * $multiplier;

		$faker   = fake();
		$created = 0;

		foreach ($usedTypes as $typeEnum) {
			if ($created >= $targetTotal) {
				break;
			}

			// Para cada type: entre 2 e 8 "blocos" de notificações
			$blocksForType = $faker->numberBetween(2, 8);

			for ($block = 0; $block < $blocksForType && $created < $targetTotal; $block++) {
				// Para cada bloco, iteramos as plataformas
				foreach ($platforms as $platformEnum) {
					if ($created >= $targetTotal) {
						break 2;
					}

					// Para cada plataforma, criar entre 2 e 8 notificações
					$perPlatformCount = $faker->numberBetween(2, 8);

					for ($i = 0; $i < $perPlatformCount && $created < $targetTotal; $i++) {
						// -------- Montagem do payload da Notification --------

						$recipientId = Arr::random($userIds);
						$senderId    = $faker->boolean(70)
							? Arr::random($userIds)
							: $recipientId; // às vezes o próprio usuário (auto-notification)

						$now    = Carbon::now();
						$sentAt = $now
							->copy()
							->subDays($faker->numberBetween(0, 30))
							->subMinutes($faker->numberBetween(0, 1440));

						$isRead = $faker->boolean(40);
						$readAt = null;
						if ($isRead) {
							$readAt = $sentAt
								->copy()
								->addMinutes($faker->numberBetween(1, 10080)); // até 7 dias depois
						}

						// attachments / metadata / tags / platforms – arrays simples, sem array_filter
						$attachments = [];
						if ($faker->boolean(35)) {
							$attachments[] = [
								'name' => $faker->lexify('attachment-????.pdf'),
								'url'  => $faker->url(),
							];
						}
						if ($faker->boolean(25)) {
							$attachments[] = [
								'name' => $faker->lexify('screenshot-????.png'),
								'url'  => $faker->url(),
							];
						}
						$attachments = array_values($attachments);

						$metadata = [
							'template_type' => $typeEnum->value,
							'platform'      => $platformEnum->value,
							'priority'      => $faker->randomElement(['low', 'normal', 'high']),
							'ref'           => (string) Str::uuid(),
						];

						$tags = [];
						if ($faker->boolean(60)) {
							$tags[] = $typeEnum->value;
						}
						if ($faker->boolean(30)) {
							$tags[] = $platformEnum->value;
						}
						if ($faker->boolean(20)) {
							$tags[] = $faker->randomElement(['system', 'billing', 'crm', 'marketing']);
						}
						$tags = array_values($tags);

						$platformsRaw = [$platformEnum->value];

						// Campo "data" é uma string JSON; aqui codificamos com cuidado,
						// deixando o Model / accessor getPayloadAttribute() fazer o decode depois.
						$dataPayload = [
							'title'           => ucfirst(str_replace('_', ' ', $typeEnum->value)),
							'message'         => sprintf(
								'Mock notification for %s via %s.',
								$typeEnum->value,
								$platformEnum->value
							),
							'template_type'   => $typeEnum->value,
							'platform'        => $platformEnum->value,
							'recipient_id'    => $recipientId,
							'sender_id'       => $senderId,
							'reference_token' => (string) Str::uuid(),
						];

						$dataJson = json_encode(
							$dataPayload,
							JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
						);

						// -------- Criação via Model (respeita casts + booted) --------
						(new \Symfony\Component\Console\Output\ConsoleOutput())->writeln("Generating notification for user {$recipientId} of type {$typeEnum->value} via {$platformEnum->value}");
						Notification::query()->create([
							UC::COL_USER_ID => $recipientId,
							'type'          => $typeEnum->value,
							'data'          => $dataJson,
							'attachments'   => $attachments,
							'metadata'      => $metadata,
							'tags'          => $tags,
							'platforms'     => $platformsRaw,
							MC::COL_SNT_AT  => $sentAt->toDateTimeString(),
							MC::COL_SNT_BY  => $senderId,
							MC::COL_IS_RD   => $isRead ? 1 : 0,
							MC::COL_RD_AT   => $readAt?->toDateTimeString(),
							// created_at / updated_at e demais audit fields:
							// serão tratados pelos traits / defaults da Model / Migration.
						]);

						$created++;
					}
				}
			}
		}

		$this->command?->info(sprintf(
			'NotificationSeeder: %d notificações criadas em %s (limite alvo 64 x %d = %d).',
			$created,
			DC::TABLE_NTF,
			$multiplier,
			$targetTotal
		));
	}
}
