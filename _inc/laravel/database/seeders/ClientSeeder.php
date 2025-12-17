<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\UsersConstants as UC;
use App\Enums\UserType;
use App\Models\Client as Cli;
use App\Models\{User, Utility};
use App\Traits\EnsuresSystemUser;

class ClientSeeder extends Seeder
{
	use EnsuresSystemUser;
	public const MIN_CLIENTS = 64;
	public function run(): void
	{
		$this->ensureSystemUser();
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$creatorId = DC::DEFAULT_UUID;
			$clientsAsUsers = User::where('type', 'client')->pluck('id')->all();
			$quantity = max(self::MIN_CLIENTS, count($clientsAsUsers));
			Log::warning(get_class($this) . ' seeding ' . $quantity . ' clients (' . count($clientsAsUsers) . ' linked to users).');
			for ($i = 0; $i < $quantity; $i++) {
				try {
					$maxAttempts = $quantity * 2;
					$attempts = 0;
					do {
						$clientName = $faker->name() . ($attempts > 10 ? " #{$i}" : '');
						$attempts++;
					} while (Cli::where(UC::COL_NM, $clientName)->exists() && $attempts < $maxAttempts);

					if ($attempts >= $maxAttempts)
						$clientName = $faker->name() . " #" . Str::uuid();
					$output = new \Symfony\Component\Console\Output\ConsoleOutput();
					$output->writeln("Creating client: {$clientName}");

					$attempts = 0;
					do {
						$clientPhone = Utility::generateBrazilianPhone();
						$attempts++;
					} while (Cli::where(UC::COL_TEL, $clientPhone)->exists() && $attempts < $maxAttempts);
					if ($attempts >= $maxAttempts)
						$clientPhone = $faker->phoneNumber();

					$attempts = 0;
					$exitAcumulator = $quantity * 64;
					do {
						$clientEmail = $faker->userName() . "_" . Str::uuid()->toString() . "@" . $faker->safeEmailDomain();
						$attempts++;
						if ($attempts >= $exitAcumulator) throw new \Exception("Too many attempts to generate unique client email. Seeding is canceled.");
					} while (Cli::where(UC::COL_EM, $clientEmail)->exists());

					$c = new Cli();
					$c->{UC::COL_NM}      = $clientName;
					$c->{UC::COL_EM}      = $clientEmail;
					$c->{UC::COL_EM_V_AT} = $faker->boolean(80) ? now() : null;
					$c->{UC::COL_PW}      = Hash::make(Str::password());
					$c->{UC::COL_LG}      = 'pt_BR';
					$c->{UC::COL_IA}      = 1;
					$c->{UC::COL_USER_ID} = $clientsAsUsers && $i < count($clientsAsUsers) ? $clientsAsUsers[$i] : null;
					$c->{UC::COL_TEL}     = $clientPhone;
					$c->{UC::COL_ADR}     = $faker->address();
					$c->{UC::COL_IU}      = $clientsAsUsers && $i < count($clientsAsUsers);
					$c->{UC::COL_AV}      = 'default.png';
					$c->{UC::COL_MSG_CL}  = $faker->hexColor();
					$c->{UC::COL_DEL_STT} = $faker->boolean(95) ? 0 : 1;
					$c->{DC::COL_TABLE_CREATOR} = $creatorId;

					$c->save();
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
