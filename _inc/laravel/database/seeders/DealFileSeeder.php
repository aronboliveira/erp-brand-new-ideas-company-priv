<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;

use App\Models\Deal as Dl;
use App\Models\DealFile as Df;

final class DealFileSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$dealIds = Dl::query()->pluck('id')->all();
			if (!$dealIds) {
				Log::notice('No deals found. Skipping deal_files seeding.');
				return;
			}

			$exts = array_unique(array_merge(
				['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'rtf', 'odt', 'md', 'json', 'xml', 'yaml', 'yml', 'toml', 'ini', 'conf', 'cfg', 'properties', 'env', 'service', 'plist'],
				['png', 'jpg', 'jpeg', 'webp', 'svg'],
				['zip', 'tar', 'gz', 'bz2', '7z', 'tar.gz', 'tar.bz2'],
				['sh', 'bash', 'zsh', 'ps1', 'py', 'js', 'ts', 'php', 'rb', 'pl', 'lua', 'go'],
				['log', 'err', 'out'],
				['sql', 'sqlite', 'sqlite3', 'db', 'dump', 'bak'],
				['pem', 'crt', 'key', 'csr'],
				['mp4', 'mkv', 'avi', 'mov', 'webm', 'm4v', 'mpeg', 'mpg']
			));

			foreach ($dealIds as $dealId) {
				$count = random_int(1, 6);

				for ($i = 0; $i < $count; $i++) {
					do $fileId = Str::uuid()->toString();
					while (Df::where('id', $fileId)->exists());

					$base = Str::slug($faker->words(random_int(1, 4), true), '-');
					if ($base === '') $base = 'file';

					$suffix   = substr(Str::uuid()->toString(), 0, 8);
					$ext      = $faker->randomElement($exts);

					$fileName = substr($base, 0, 160) . '-' . $suffix . '.' . $ext; // * reduz colisões e respeita limite típico de 255
					$filePath = 'uploads/deals/' . $dealId . '/' . $fileName;

					$ts = $faker->dateTimeBetween('-60 days', 'now');

					$df = new Df();
					$df->id         = $fileId;
					$df->deal_id    = $dealId;
					$df->file_name  = $fileName;
					$df->file_path  = $filePath;
					$df->{DC::TABLE_CREATOR} = $systemUserId;
					$df->setAttribute(DC::TABLE_UPDATER, null);
					$df->setAttribute('created_at', $ts);
					$df->setAttribute('updated_at', $ts);
					$df->save();
				}
			}
		}, 3);
	}
}
