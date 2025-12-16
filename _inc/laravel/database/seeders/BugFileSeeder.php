<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\UsersConstants as UC;

use App\Models\Bug as Bg;
use App\Models\BugFile as Bf;
use App\Enums\UserType as UT;

final class BugFileSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$bugIds = Bg::query()->pluck('id')->all();
			if (!$bugIds) {
				Log::notice('No bugs found. Skipping bug_files seeding.');
				return;
			}

			$extDocs   = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'rtf', 'odt', 'md'];
			$extImages = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
			$extArch   = ['zip', 'tar', 'gz', 'bz2', '7z', 'tar.gz', 'tar.bz2'];
			$extCode   = ['log', 'json', 'xml', 'yml', 'yaml', 'ini', 'conf', 'cfg', 'sh', 'bash', 'zsh', 'ps1', 'py', 'js', 'ts', 'php', 'rb', 'pl', 'lua', 'go', 'sql'];
			$extVideo  = ['mp4', 'mkv', 'avi', 'mov', 'webm', 'm4v', 'mpeg', 'mpg'];
			$exts = array_unique(array_merge($extDocs, $extImages, $extArch, $extCode, $extVideo));

			$typePool = [
				UT::SuperAdmin,
				UT::Admin,
				UT::Company,
				UT::Client,
				UT::Customer,
				UT::Vendor,
				UT::Accountant,
			];

			$fmtBytes = function (int $bytes): string {
				$units = ['B', 'KB', 'MB', 'GB'];
				$i = 0;
				$v = (float)$bytes;
				while ($v >= 1024 && $i < count($units) - 1) {
					$v /= 1024;
					$i++;
				}
				return ($v >= 10 ? number_format($v, 1, ',', '') : number_format($v, 2, ',', '')) . ' ' . $units[$i];
			};

			$pickSize = function (string $ext) use ($fmtBytes, $extVideo, $extImages, $extDocs, $extCode, $extArch): string {
				if (in_array($ext, $extVideo, true)) {
					$bytes = random_int(20 * 1024 * 1024, 800 * 1024 * 1024);
				} elseif (in_array($ext, $extImages, true)) {
					$bytes = random_int(50 * 1024, 8 * 1024 * 1024);
				} elseif (in_array($ext, $extDocs, true)) {
					$bytes = random_int(20 * 1024, 5 * 1024 * 1024);
				} elseif (in_array($ext, $extArch, true)) {
					$bytes = random_int(100 * 1024, 100 * 1024 * 1024);
				} else { // code/log
					$bytes = random_int(1 * 1024, 2 * 1024 * 1024);
				}
				return $fmtBytes($bytes);
			};

			foreach ($bugIds as $bugId) {
				$count = random_int(1, 5);

				for ($i = 0; $i < $count; $i++) {
					try {
						do $bugFileId = Str::uuid()->toString();
						while (Bf::where('id', $bugFileId)->exists());

						$ext = $exts[array_rand($exts)];
						$base = Str::slug($faker->words(random_int(1, 4), true), '-');
						if ($base === '') $base = 'file';

						$suffix   = substr(Str::uuid()->toString(), 0, 8);
						$fileName = substr($base, 0, 160) . '-' . $suffix . '.' . $ext;
						(new \Symfony\Component\Console\Output\ConsoleOutput
						)->writeln("Criando Arquivo para Tarefa: {$fileName}");
						$filePath = 'uploads/bugs/' . $bugId . '/' . $fileName;
						$ts = $faker->dateTimeBetween('-45 days', 'now');

						$bf = new Bf();
						$bf->id                  = $bugFileId;
						$bf->bug_id              = $bugId;
						$bf->file                = $filePath;                 // caminho/armazenamento
						$bf->name                = $fileName;                 // nome original exibível
						$bf->extension           = mb_strtolower($ext);
						$bf->file_size           = $pickSize($ext);
						$bf->{UC::COL_U_TP}      = $typePool[array_rand($typePool)]; // enum (cast/normalização no model)
						$bf->{DC::COL_TABLE_CREATOR} = $systemUserId;
						$bf->setAttribute(DC::COL_TABLE_UPDATER, null);
						$bf->setAttribute('created_at', $ts);
						$bf->setAttribute('updated_at', $ts);
						$bf->save();
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
						continue;
					}
				}
			}
		}, 3);
	}
}
