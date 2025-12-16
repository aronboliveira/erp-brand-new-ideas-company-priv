<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BillsConstants as BC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Enums\{BrazilState, UserType};
use App\Models\{
	ProductService,
	ProductServiceUnit,
	User,
	Vendor
};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Hash, Log};
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class VendorSeeder extends Seeder
{
	public const MIN_VENDORS = 32;
	public function run(): void
	{
		DB::transaction(function (): void {
			$faker = Faker::create('pt_BR');
			$seed = [
				// Infraestrutura, cloud e hospedagem
				['Alpha Infra & Cloud Solutions',        'contato@alphainfra.example',           'BR', 'SP'],
				['Beta DataCenter & Hosting',            'comercial@betadatacenter.example',     'BR', 'RJ'],
				['Gama Cloud Managed Services',          'suporte@gamacloud.example',           'BR', 'MG'],
				['Delta Edge Networking',                'vendas@deltaedge.example',             'BR', 'PR'],
				['Épsilon Secure Hosting',               'contato@epsilonhosting.example',       'BR', 'RS'],
				['Zeta MultiCloud Orchestrators',        'parcerias@zetamulticloud.example',     'BR', 'SC'],

				// Telecom, conectividade e VoIP
				['ConectaLink Telecom Empresarial',      'atendimento@conectalinktelecom.example', 'BR', 'SP'],
				['FibraMax Internet Corporativa',        'comercial@fibramaxcorp.example',       'BR', 'RJ'],
				['VoIPOffice Call Solutions',            'suporte@voipoffice.example',           'BR', 'DF'],
				['NetBridge WAN & SD-WAN',               'projetos@netbridgewan.example',        'BR', 'PR'],

				// Hardware, equipamentos e impressão
				['HardwareTech Distribuidora',           'vendas@hardwaretechdist.example',      'BR', 'MG'],
				['PrintMaster Document Solutions',       'contratos@printmasterdocs.example',    'BR', 'RS'],
				['Desk&Chair Office Supplies',           'compras@deskchairoffice.example',      'BR', 'SP'],
				['Cabos & Conexões do Brasil',           'logistica@caboseconexoes.example',     'BR', 'SC'],

				// Facilities, limpeza, segurança física
				['CleanOffice Facilities Group',         'operacoes@cleanofficefacilities.example', 'BR', 'BA'],
				['SafeGuard Segurança Patrimonial',      'contato@safeguardseguranca.example',   'BR', 'PE'],
				['CoffeeBreak Gourmet Services',         'eventos@coffeebreakgourmet.example',   'BR', 'RJ'],
				['FacilityPro Gestão Predial',           'projetos@facilitypro.example',         'BR', 'GO'],

				// Contabilidade, fiscal e BPO financeiro
				['ContábilPlus BPO & Contabilidade',     'financeiro@contabilplus.example',      'BR', 'SP'],
				['FiscalExpert Consultoria Tributária',  'consultoria@fiscalexpert.example',     'BR', 'RS'],
				['LedgerOne Serviços Contábeis',         'relacionamento@ledgerone.example',     'BR', 'MG'],
				['BPO Financeiro Prisma',                'operacoes@bpofinanceiroprisma.example', 'BR', 'PR'],

				// Banco de horas, folha, RH, benefícios
				['RH Consult Pessoas & Cultura',         'clientes@rhconsultpc.example',         'BR', 'SP'],
				['TalentBridge Recrutamento Tech',       'hunting@talentbridge.example',         'BR', 'RJ'],
				['Beneflex Gestão de Benefícios',        'contato@beneflexbeneficios.example',   'BR', 'SC'],
				['FolhaCerta Processamento de Folha',    'suporte@folhacerta.example',           'BR', 'DF'],

				// Jurídico, LGPD e compliance
				['LegalCorp Advocacia Empresarial',      'contato@legalcorpempresarial.example', 'BR', 'SP'],
				['LGPDShield Privacidade & Dados',       'projetos@lgpdshield.example',          'BR', 'RS'],
				['Compliance360 Consultoria',            'consultoria@compliance360.example',    'BR', 'PR'],

				// Treinamento, certificações e idiomas
				['TreinaTech Academy',                   'matriculas@treinatechacademy.example', 'BR', 'CE'],
				['SkillUp Cursos Corporativos',          'comercial@skillupcursos.example',      'BR', 'MG'],
				['LinguaPro Idiomas para Negócios',      'contato@linguapro.example',            'BR', 'RJ'],

				// Marketing, branding e design
				['BrandLab Estúdio Criativo',            'projetos@brandlabstudio.example',      'BR', 'SP'],
				['GrowthFlow Marketing Digital',         'midia@growthflowmarketing.example',    'BR', 'BA'],
				['PixelCraft Design & UX',               'ux@pixelcraftdesign.example',          'BR', 'SC'],
			];
			$acc = 0;
			$userNum =  User::query()->count();
			$companiesPool = User::query()
				->whereIn('type', ['vendor', 'company'])
				->get()
				->all();
			while (count($seed) < max(User::where('type', 'vendor')->count(), self::MIN_VENDORS * count(UserType::cases()))) {
				if ($acc > $userNum / count(UserType::cases())) break;
				do $candidateEmail = $companiesPool[array_rand($companiesPool)]->email ?? $faker->username() . '_' . Str::uuid() . '@' . $faker->domainName();
				while (in_array($candidateEmail, array_map(fn($e) => $e[1], $seed)));
				(new \Symfony\Component\Console\Output\ConsoleOutput
				)->writeln("Criando semente para fornecedor: " . $candidateEmail);
				$seed[] = [
					$companiesPool[array_rand($companiesPool)]->name ?? $faker->company(),
					$candidateEmail,
					array_rand(['BR', 'US', 'ZH', 'FR', 'DE', 'JP', 'AR', 'UY', 'PY', 'CL', 'CO', 'MX', 'EC', 'PT', 'ES', 'IT', 'PE', 'RU']),
					$faker->randomElement(array_map(fn($e) => $e->value, BrazilState::cases())),
				];
				$acc++;
			}

			// Tabela de ProductServices para linkar ofertas (se não houver, apenas ofertas "unregistered")
			$productIds = ProductService::query()
				->inRandomOrder()
				->limit(30)
				->pluck('id')
				->all();

			$created = 0;
			$updated = 0;

			foreach ($seed as [$name, $email, $ctr, $uf]) {
				try {
					(new \Symfony\Component\Console\Output\ConsoleOutput
					)->writeln("Criando Fornecedor: {$name}, Email: {$email}");
					$offers = $this->buildOffers($productIds);
					$mainTaxId = $this->requireAnyId(DC::TABLE_TAXES, 'tributário');
					$payload = [
						UC::COL_NM          => $name,
						UC::COL_EM          => $email,
						UC::COL_PW          => Hash::make('secret123!'),
						'contact'           => $faker->cellphoneNumber(),
						UC::COL_AV          => 'chatify.user_avatar.default',
						UC::COL_IA          => true,
						UC::COL_LG          => 'pt-br',
						'preferences'       => [
							'newsletter' => (bool) random_int(0, 1),
							'channels'   => ['email', 'phone'],
						],
						UC::COL_EM_V_AT     => $faker->optional(0.7)->dateTimeBetween('-180 days', 'now'),

						// Billing
						BC::COL_BL_NAME     => $name . ' - Financeiro',
						BC::COL_BL_EMAIL    => $email,
						BC::COL_BL_CTR      => $ctr,
						BC::COL_BL_ST       => $uf,
						BC::COL_BL_CTY      => $faker->city(),
						BC::COL_BL_TEL      => $faker->cellphoneNumber(),
						BC::COL_BL_ZIP      => $faker->postcode(),
						BC::COL_BL_ADR      => $faker->streetAddress(),
						BC::COL_BL_DTL      => $faker->secondaryAddress(),

						// Shipping
						BC::COL_SHIP_NAME   => $name . ' - Logística',
						BC::COL_SHIP_CTR    => $ctr,
						BC::COL_SHIP_ST     => $uf,
						BC::COL_SHIP_CTY    => $faker->city(),
						BC::COL_SHIP_TEL    => $faker->cellphoneNumber(),
						BC::COL_SHIP_ZIP    => $faker->postcode(),
						BC::COL_SHIP_ADR    => $faker->streetAddress(),
						BC::COL_SHIP_DTL    => $faker->secondaryAddress(),

						// Tributário e flags
						BC::COL_TX_N        => $mainTaxId, // CPF/CNPJ limpo; normalizador decidirá
						BC::COL_OT_TX_ID    => [...array_filter($this->idPool(DC::TABLE_TAXES), fn($t) => $t !== $mainTaxId)],
						BC::COL_IS_PRM      => (bool) random_int(0, 1),

						'balance'           => 0.00,
						'offers'            => $offers,

						// Auditoria
						DC::COL_TABLE_CREATOR   => null,  // se quiser atrelar a um "system user", injete aqui o ID
					];

					$found = Vendor::query()->where(UC::COL_EM, $email)->first();

					if ($found) {
						$found->fill($payload)->save();
						$updated++;
					} else {
						Vendor::create($payload);
						$created++;
					}
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}

			Log::info("VendorSeeder: created={$created}, updated={$updated}");
		});
	}

	/**
	 * Monta uma lista de ofertas mesclando registradas e não registradas.
	 * A função respeita o formato esperado por Vendor::sanitizeOffers().
	 */
	private function buildOffers(array $productIds): array
	{
		$offers = [];

		// 2–4 ofertas válidas (registradas)
		$count = $productIds ? random_int(2, 4) : 0;
		shuffle($productIds);

		for ($i = 0; $i < $count; $i++) {
			$pid = $productIds[$i] ?? null;
			if (!$pid) break;

			// Se existir, tenta pegar uma unidade ligada ao ProductService
			$unitId = ProductServiceUnit::query()
				->where(BC::COL_PRD_SV_ID, $pid)
				->inRandomOrder()
				->value('id');

			$offers[] = $unitId
				? ['id' => $pid, 'unit_id' => $unitId]
				: ['id' => $pid];
		}

		// 1–2 ofertas não registradas (servem para exercitar os métodos de classificação)
		$extra = random_int(1, 2);
		for ($j = 0; $j < $extra; $j++) {
			$offers[] = ['key' => 'OFF-' . Str::upper(Str::random(8))];
		}

		return $offers;
	}

	private function idPool(string $table): array
	{
		return array_values(array_map('strval', DB::table($table)->pluck('id')->all()));
	}

	private function requireAnyId(string $table, string $humanName): string
	{
		$id = (string) (DB::table($table)->value('id') ?? '');
		if ($id !== '') return $id;

		throw new \RuntimeException("VendorSeeder: nenhuma linha encontrada em '{$table}' para '{$humanName}'. Crie ao menos 1 registro antes de semear Vendors.");
	}
}
