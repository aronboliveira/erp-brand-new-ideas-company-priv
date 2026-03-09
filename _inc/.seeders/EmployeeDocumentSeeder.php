<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\TemplatesConstants as TC;
use App\Config\Constants\UsersConstants as UC;
use App\Enums\{DocumentKind, MimeType, UserType};
use App\Models\{Document as Doc, Employee as Emp, EmployeeDocument as EDoc, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class EmployeeDocumentSeeder extends Seeder
{
	// private const SECONDS_LIMIT = 2 * 10 ** 2;
	private const SECONDS_LIMIT = 32;
	public function run(): void
	{
		$faker = fake('pt_BR');
		DB::transaction(function () use ($faker) {
			$clock = microtime(true);

			$employeeIds = Emp::query()->pluck('id')->all();
			$documentIds = Doc::query()->pluck('id')->all();
			$userIds     = User::query()->pluck('id')->all();

			if (!$employeeIds || !$documentIds) {
				Log::notice('EmployeeDocumentSeeder: sem employees ou documents; seeding ignorado.');
				return;
			}

			// pool de extensões comuns (Linux/Mac/servidor + imagens + vídeo)
			$extPool = [
				'pdf',
				'txt',
				'log',
				'csv',
				'tsv',
				'json',
				'yaml',
				'yml',
				'xml',
				'conf',
				'ini',
				'env',
				'sh',
				'bash',
				'ps1',
				'php',
				'py',
				'rb',
				'sql',
				'xlsx',
				'xls',
				'docx',
				'odt',
				'pptx',
				'odp',
				'jpg',
				'jpeg',
				'png',
				'svg',
				'webp',
				'tiff',
				'bmp',
				'mp4',
				'mkv',
				'mov',
				'webm',
				'avi',
			];

			// ordem de papéis conforme ROLES_ORDER do AbstractDocument
			$rolesOrder = [
				UserType::SuperAdmin->value,
				UserType::Admin->value,
				UserType::Company->value,
				UserType::Accountant->value,
				UserType::Vendor->value,
				UserType::Customer->value,
				UserType::Client->value,
			];

			$makePermissionRules = function (): string {
				// parte de '7776444' e degrada aleatoriamente (sempre 7 dígitos)
				$base = [7, 7, 7, 6, 4, 4, 4];
				foreach ($base as $i => $d) {
					$dec = random_int(0, 3);
					$base[$i] = max(0, $d - $dec);
				}
				return implode('', array_map('strval', $base));
			};

			$makeCsvFromPool = function (array $pool, int $min, int $max): ?string {
				if (!$pool) return null;
				$n = random_int($min, $max);
				if ($n === 0) return null;
				return collect($pool)->shuffle()->take($n)->implode(',');
			};
			foreach ($employeeIds as $empId) {
				$take = 1;
				$pickedDocs = collect($documentIds)->shuffle()->take($take)->all();
				foreach ($pickedDocs as $docId) {
					if ((microtime(true) - $clock) > self::SECONDS_LIMIT) {
						Log::warning(self::class . ' seeding time limit reached, stopping early');
						return;
					}
					try {
						// evita duplicidade employee_id + document_id
						$exists = EDoc::query()
							->where(UC::COL_EMP_ID, $empId)
							->where(TC::COL_DC_ID, $docId)
							->exists();
						if ($exists) {
							continue;
						}

						// id com do/while para garantir unicidade
						do {
							$id = (string) Str::uuid();
						} while (EDoc::where('id', $id)->exists());

						$ext  = $faker->randomElement($extPool);
						$mime = MimeType::fromExtension($ext)?->value ?? 'application/octet-stream';
						$kind = DocumentKind::fromExtension($ext)?->value ?? 'unknown';

						// viewers/editors/executors opcionais
						$viewers   = $makeCsvFromPool($userIds, 0, 4);
						$editors   = $makeCsvFromPool($userIds, 0, 2);
						$executors = $makeCsvFromPool($userIds, 0, 1);

						(new \Symfony\Component\Console\Output\ConsoleOutput
						)->writeln("Criando Documento {$kind} [{$mime}] para Funcionário: {$empId} - {$docId}");
						$m = new EDoc();
						$m->id                       = $id;
						$m->{UC::COL_EMP_ID}         = $empId;
						$m->{TC::COL_DC_ID}          = $docId;
						$m->{TC::COL_DC_V}           = $faker->bothify(strtoupper('??#####-###'));
						$m->file_path                = '/storage/docs/' . $id . '.' . $ext;
						$m->extension                = $ext;
						// ? gravar como string (evita problemas caso o cast enum não esteja aplicado aqui)
						$m->mime_type                = $mime;
						$m->type                     = $kind;
						$m->size                     = (string) random_int(2_048, 12_582_912); // 2KB..12MB
						$m->description              = $faker->optional()->sentence();
						$m->notes                    = $faker->optional()->sentence();
						$m->expiration_date          = $faker->optional(0.25)->dateTimeBetween('now', '+2 years');
						$m->last_accessed            = $faker->optional(0.5)->dateTimeBetween('-3 months', 'now');
						$m->permission_rules         = $makePermissionRules();
						$m->viewers                  = $viewers;
						$m->editors                  = $editors;
						$m->executors                = $executors;
						$m->{DC::COL_TABLE_CREATOR}      = DC::DEFAULT_UUID;
						$m->setAttribute(DC::COL_TABLE_UPDATER, null);

						$m->save();
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
						continue;
					}
				}
			}
			$cap = 1600;
			foreach ($employeeIds as $empId) {
				if (!$cap || $cap <= 0)
					break;
				$cap--;
				$take = random_int(1, 3);
				$pickedDocs = collect($documentIds)->shuffle()->take($take)->all();

				foreach ($pickedDocs as $docId) {
					try {
						if ((microtime(true) - $clock) > self::SECONDS_LIMIT) {
							Log::warning(self::class . ' seeding time limit reached, stopping early');
							return;
						}
						// evita duplicidade employee_id + document_id
						$exists = EDoc::query()
							->where(UC::COL_EMP_ID, $empId)
							->where(TC::COL_DC_ID, $docId)
							->exists();
						if ($exists) {
							continue;
						}

						// id com do/while para garantir unicidade
						do {
							$id = (string) Str::uuid();
						} while (EDoc::where('id', $id)->exists());

						$ext  = $faker->randomElement($extPool);
						$mime = MimeType::fromExtension($ext)?->value ?? 'application/octet-stream';
						$kind = DocumentKind::fromExtension($ext)?->value ?? 'unknown';

						// viewers/editors/executors opcionais
						$viewers   = $makeCsvFromPool($userIds, 0, 4);
						$editors   = $makeCsvFromPool($userIds, 0, 2);
						$executors = $makeCsvFromPool($userIds, 0, 1);

						(new \Symfony\Component\Console\Output\ConsoleOutput
						)->writeln("Criando Documento {$kind} [{$mime}] para Funcionário: {$empId} - {$docId}");
						$m = new EDoc();
						$m->id                       = $id;
						$m->{UC::COL_EMP_ID}         = $empId;
						$m->{TC::COL_DC_ID}          = $docId;
						$m->{TC::COL_DC_V}           = $faker->bothify(strtoupper('??#####-###'));
						$m->file_path                = '/storage/docs/' . $id . '.' . $ext;
						$m->extension                = $ext;
						// ? gravar como string (evita problemas caso o cast enum não esteja aplicado aqui)
						$m->mime_type                = $mime;
						$m->type                     = $kind;
						$m->size                     = (string) random_int(2_048, 12_582_912); // 2KB..12MB
						$m->description              = $faker->optional()->sentence();
						$m->notes                    = $faker->optional()->sentence();
						$m->expiration_date          = $faker->optional(0.25)->dateTimeBetween('now', '+2 years');
						$m->last_accessed            = $faker->optional(0.5)->dateTimeBetween('-3 months', 'now');
						$m->permission_rules         = $makePermissionRules();
						$m->viewers                  = $viewers;
						$m->editors                  = $editors;
						$m->executors                = $executors;
						$m->{DC::COL_TABLE_CREATOR}      = DC::DEFAULT_UUID;
						$m->setAttribute(DC::COL_TABLE_UPDATER, null);

						$m->save();
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
						continue;
					}
				}
			}
		}, 3);
	}
}
