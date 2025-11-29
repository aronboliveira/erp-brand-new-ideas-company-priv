<?php

namespace Database\Seeders;

use App\Config\Constants\{
	CompaniesConstants as CC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Models\MeetingEmployee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MeetingEmployeeSeeder extends Seeder
{
	use WithoutModelEvents;

	/**
	 * Parâmetros básicos (ajuste conforme necessidade).
	 */
	private int $minPerMeeting = 1;
	private int $maxPerMeeting = 4;

	public function run(): void
	{
		// IDs base (falha cedo se não houver dados).
		$meetingIds  = DB::table(DC::TABLE_MEETINGS)->pluck('id')->all();
		$employeeIds = DB::table(DC::TABLE_EMPLOYEES)->pluck('id')->all();

		if (empty($meetingIds) || empty($employeeIds)) {
			Log::warning(static::class . ': não há meetings e/ou employees suficientes para semear.');
			return;
		}

		// Ajusta o teto por reunião ao número de empregados disponível.
		$this->maxPerMeeting = max(1, min($this->maxPerMeeting, count($employeeIds)));

		foreach ($meetingIds as $meetingId) {
			DB::transaction(function () use ($meetingId, $employeeIds) {
				try {
					// Quantidade de participantes a gerar para esta reunião.
					$count = random_int($this->minPerMeeting, $this->maxPerMeeting);

					// Evita duplicações escolhendo um subconjunto aleatório e único.
					$picked = collect($employeeIds)->shuffle()->take($count)->values();

					// Se já existe host na reunião, não forçamos outro.
					$alreadyHasHost = MeetingEmployee::query()
						->where(CC::COL_MT_ID, $meetingId)
						->where(CC::COL_IS_HST, true)
						->exists();

					$hostAssigned = $alreadyHasHost;

					foreach ($picked as $index => $empId) {
						// Garante no máximo 1 host; se não houver, o primeiro vira host.
						$isHost = $hostAssigned ? false : true;
						$hostAssigned = $hostAssigned || $isHost;

						// firstOrCreate evita violar a UNIQUE([meeting_id, employee_id]).
						// Definimos 'id' e 'invitation_code' apenas na criação para não sobrescrever códigos existentes.
						$record = MeetingEmployee::firstOrCreate(
							[
								CC::COL_MT_ID => $meetingId,
								UC::COL_EMP_ID => $empId,
							],
							[
								'id'              => (string) Str::uuid(),       // UUID primário
								CC::COL_INV_CD    => $this->uniqueInviteCode(), // código único
								CC::COL_IS_HST    => false,                      // valores default (serão atualizados abaixo)
								CC::COL_HAS_MRC_MT => false,
								CC::COL_HAS_CMR_OFF => false,
								CC::COL_HAS_SCR_ENB => false,
								'metadata'        => [],
							]
						);

						// Atualiza flags e metadados sem tocar no invitation_code existente.
						$record->fill([
							CC::COL_IS_HST       => $isHost,
							CC::COL_HAS_MRC_MT   => (bool) random_int(0, 1),
							CC::COL_HAS_CMR_OFF  => (bool) random_int(0, 1),
							CC::COL_HAS_SCR_ENB  => (bool) random_int(0, 1) && !$isHost,
							'metadata'           => array_merge(
								is_array($record->metadata) ? $record->metadata : [],
								[
									'role'       => $isHost ? 'host' : 'attendee',
									'seeded_at'  => now()->toISOString(),
								]
							),
						])->save();
					}
				} catch (\Throwable $e) {
					Log::error(static::class . " falhou para meeting={$meetingId}: {$e->getMessage()}");
				}
			});
		}
	}

	/**
	 * Gera um código de convite único (coluna UNIQUE) com poucas tentativas.
	 * Usa UUID por robustez. Collisions são virtualmente impossíveis; ainda assim,
	 * verificamos 3 vezes por prudência.
	 */
	private function uniqueInviteCode(int $maxAttempts = 3): string
	{
		for ($i = 0; $i < $maxAttempts; $i++) {
			$code = (string) Str::uuid(); // helper oficial recomendado para gerar UUIDs
			$exists = MeetingEmployee::query()
				->where(CC::COL_INV_CD, $code)
				->exists();
			if (!$exists) {
				return $code;
			}
		}
		// Fallback extremo: adiciona sufixo randômico em caso de cenário altamente improvável.
		return (string) Str::uuid() . '-' . Str::lower(Str::random(6));
	}
}
