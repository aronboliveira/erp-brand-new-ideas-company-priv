<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	BanksConstants as BKC,
	BillsConstants as BC,
	DatabaseConstants as DC,
	SettingsConstants as SC
};
use App\Models\{
	ChartOfAccount,
	ProductService,
	ProductServiceCategory,
	ProductServiceUnit,
	Tax
};
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class ProductServiceSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function (): void {
			$creator = $this->ensureSystemUser();

			$faker = \Faker\Factory::create('pt_BR');
			$tz    = 'America/Sao_Paulo';

			// Mapa de categorias code => id (exige que ProductServiceCategorySeeder já tenha rodado)
			$catIds = $this->categoryIdsByCode([
				'SWDEV',
				'ITINFRA',
				'HELPDESK',
				'RETAIL',
				'WEBDEV',
				'MOBDEV',
				'BACKEND',
				'FRONTEND',
				'AI',
				'ML',
				'DATA_ANAL',
				'DATA_SCI',
				'INFOSEC',
				'CYBERSEC',
				'CLOUD',
				'DEVOPS',
				'SRE',
				'NETWORKS',
				'TELECOM',
				'SYSADMIN',
				'DB',
				'VIRTUAL',
				'CONTAINERS',
				'AUTOMATION',
				'HARDWARE',
				'SERVERS',
				'STORAGE',
				'PHY_NET',
				'PERIPHERALS',
				'IT_EQP',
				'SOFT_LIC',
				'BUS_SOFT',
				'OS',
				'DEV_TOOLS',
				'QA',
				'SW_ARCH',
				'IT_PM',
				'IT_CONS',
				'IT_TRAIN',
				'TECH_SUP',
				'FIELD_SVC',
				'MONITORING',
				'BACKUP',
				'SUSTAIN',
				'GRAPH_DES',
				'UIUX',
				'MOTION',
				'VIDEO_EDIT',
				'AUDIO_PROD',
				'PHOTO',
				'ILLUSTRATION',
				'DIGITAL_MKT',
				'SOCIAL_MEDIA',
				'SEO',
				'PPC',
				'ECOMMERCE',
				'DIGITAL_CONT',
				'COPYWRITING',
				'BRANDING',
				'WEB_DESIGN',
				'ANIMATION',
				'VR_AR',
				'GAME_DESIGN',
				'3D_MODEL',
				'DIGITAL_ART',
				'CONTENT_PROD',
				'COMM_MGMT',
				'DIGITAL_ANAL',
				'DIGITAL_STRAT',
				'RECRUIT',
				'TRAINING',
				'DP',
				'BENEFITS',
				'COMPENSATION',
				'ORG_CLIMATE',
				'LABOR_COMP',
				'OCC_HEALTH',
				'WORK_SAFETY',
				'TALENT_MGMT',
				'ORG_DEV',
				'STRAT_HR',
				'CAREER_PLAN',
				'PERF_REVIEW',
				'ONBOARDING',
				'OFFBOARDING',
				'PAYROLL',
				'HR_CONS',
				'CORP_EVENTS',
				'UNIFORMS',
				'BUS_CONS',
				'STRAT_PLAN',
				'MARKET_ANAL',
				'PROJ_MGMT',
				'PROC_MGMT',
				'INNOVATION',
				'DIGITAL_TRANS',
				'ACCOUNTING',
				'AUDIT',
				'CONTROLLER',
				'TREASURY',
				'CORP_FIN',
				'FIN_ANAL',
				'FIN_PLAN',
				'BUDGET',
				'COSTING',
				'INVESTMENTS',
				'CREDIT',
				'FOREX',
				'FOREIGN_TRADE',
				'TAX',
				'FISCAL',
				'LEGALIZATION',
				'CERTIFICATIONS',
				'QUALITY',
				'COMPLIANCE',
				'RISK',
				'M_A',
				'DUE_DILIG',
				'RESTRUCT',
				'ERP',
				'CRM',
				'BI',
				'BPO_FIN',
				'TAX_CONS',
				'FIN_ADV',
				'CREDIT_ANAL',
				'CREDIT_RECOV',
				'TAX_PLAN',
				'INT_CONTROL',
				'INT_AUDIT',
				'MGMT_REPORTS',
				'FP_A',
				'MGMT_ACC',
				'DIRECT_SALES',
				'INDIRECT_SALES',
				'CORP_SALES',
				'RETAIL_SALES',
				'WHOLESALE',
				'PRESALES',
				'POSTSALES',
				'CUST_SUCCESS',
				'ACCOUNT_MGMT',
				'BIZ_DEV',
				'INSIDE_SALES',
				'FIELD_SALES',
				'CHANNEL_SALES',
				'KEY_ACCOUNTS',
				'SALES_OPS',
				'SALES_ENABLE',
				'PROSPECTING',
				'NEGOTIATION',
				'CLOSING',
				'RELATIONSHIP',
				'GEN_ADMIN',
				'FACILITIES',
				'RECEPTION',
				'TELEPHONY',
				'MAIL',
				'ARCHIVE',
				'PROTOCOL',
				'SUPPLIES',
				'WAREHOUSE',
				'TRANSPORT',
				'FLEET',
				'BUILD_MAINT',
				'CLEANING',
				'GARDENING',
				'PATRIM_SEC',
				'ACCESS_CTRL',
				'PANTRY',
				'INT_EVENTS',
				'TRAVEL',
				'GEN_SERVICES',
				'OFFICE',
				'STATIONERY',
				'OFFICE_SUP',
				'FILING',
				'ORGANIZATION',
				'INT_COMM',
				'SERVICE_DESK',
				'PROCESSING',
				'TYPING',
				'CHECKING',
				'STOREKEEPER',
				'ADMIN_ASSIST',
				'SECRETARIAL',
				'EXEC_ASSIST',
				'OFFICE_BOY',
				'COMM_CLEAN',
				'IND_CLEAN',
				'RES_CLEAN',
				'SANITIZATION',
				'DISINFECTION',
				'GLASS_CLEAN',
				'UPHOLSTERY',
				'CARPET_CLEAN',
				'CLEAN_PROD',
				'CLEAN_EQUIP',
				'CLEAN_PPE',
				'WASTE_COLLECT',
				'WASTE_DISPOSAL',
				'CUSTODIAL',
				'CONSERVATION',
				'MED_CONSULT',
				'EXAMS',
				'LAB',
				'IMAGING',
				'PHYSIOTHERAPY',
				'NUTRITION',
				'PSYCHOLOGY',
				'DENTISTRY',
				'PHARMACY',
				'NURSING',
				'FIRST_AID',
				'CHECKUP',
				'VACCINATION',
				'WORK_GYM',
				'MEDICINES',
				'MED_EQUIP',
				'INSTRUMENTS',
				'HOSP_SUPPLIES',
				'HOSP_PPE',
				'LABOR_LAW',
				'CIVIL_LAW',
				'BUSINESS_LAW',
				'TAX_LAW',
				'CONTRACT_LAW',
				'REAL_ESTATE_LAW',
				'IP_LAW',
				'COMPLIANCE',
				'LGPD',
				'LITIGATION',
				'LEGAL_CONS',
				'LEGAL_ADV',
				'LEGAL_DD',
				'TRADEMARK',
				'PATENTS',
				'COPYRIGHTS',
				'CERTIFICATES',
				'AUTHENTICATIONS',
				'POWERS_ATTORNEY',
				'DOC_LEGAL',
				'RESTAURANT',
				'CATERING',
				'COFFEE_BREAK',
				'FOOD',
				'BEVERAGES',
				'CONFECTIONERY',
				'BAKERY',
				'BUFFET',
				'FOOD_PROD',
				'INGREDIENTS',
				'KITCHEN_TOOLS',
				'KITCHEN_EQUIP',
				'REST_FURN',
				'DISPOSABLES',
				'DRINKS',
				'TABLE_SERVICE',
				'INCOME_MISC',
				'OPER_EXP',
				'ASSETS',
				'LIABIL',
				'EQUITY',
				'COGS',
				'OTHER'
			]);

			// Catálogo-base (serviços + itens) com category-code e unidade principal
			$catalog = [
				// name                                   unit      primaryCat   sellPrice  costPrice
				['Consultoria de TI',                    'hour',    'ITINFRA',   220.0000, 140.0000],
				['Suporte Help Desk',                    'ticket',  'HELPDESK',   59.9000,  25.0000],
				['Visita Técnica On-site',               'visit',   'ITINFRA',   280.0000, 120.0000],
				['Administração de Servidores',          'month',   'ITINFRA',  2400.0000, 900.0000],
				['Gestão de Backups',                    'month',   'ITINFRA',   720.0000, 300.0000],
				['Monitoramento 24x7',                   'month',   'ITINFRA',  1390.0000, 450.0000],
				['Implantação de Firewall',              'service', 'ITINFRA',  3800.0000, 900.0000],
				['Auditoria de Segurança',               'service', 'ITINFRA',  4900.0000, 1200.0000],
				['Treinamento Corporativo',              'session', 'SWDEV',     690.0000, 250.0000],
				['Desenvolvimento sob Demanda',          'hour',    'SWDEV',     240.0000,  95.0000],
				['Gestão de Patches e Atualizações',     'month',   'ITINFRA',   790.0000, 300.0000],
				['Hardening de Servidores',              'service', 'ITINFRA',  4300.0000, 1100.0000],
				['Migração de E-mail Corporativo',       'service', 'ITINFRA',  2900.0000, 900.0000],
				['Projeto Next.js (Sprint)',             'sprint',  'SWDEV',    9800.0000, 2800.0000],
				['Integração API / Webhook',             'service', 'SWDEV',    1700.0000, 600.0000],
				['Plano VIP Help Desk',                  'month',   'HELPDESK', 1690.0000, 600.0000],
				['Resposta a Incidentes',                'service', 'ITINFRA',  5400.0000, 1400.0000],
				['Assessoria LGPD',                      'service', 'ITINFRA',  3990.0000, 1200.0000],
				['Planejamento de Capacidade',           'service', 'ITINFRA',  2500.0000, 800.0000],

				// Revenda / produtos
				['Notebook Empresarial i5',              'item',    'RETAIL',   4350.0000, 3200.0000],
				['Servidor Rack 2U',                     'item',    'RETAIL',  18990.0000, 13500.0000],
				['Switch Gerenciável 24p',               'item',    'RETAIL',   1790.0000, 1200.0000],
				['Switch PoE 48p',                       'item',    'RETAIL',   8290.0000, 6000.0000],
				['AP Wi-Fi Corporativo',                 'item',    'RETAIL',    890.0000,  620.0000],
				['Impressora Laser A4',                  'item',    'RETAIL',   1290.0000,  900.0000],
				['SSD NVMe 1TB',                         'item',    'RETAIL',    520.0000,  380.0000],
				['Licença Software Pro',                 'license', 'RETAIL',    990.0000,  550.0000],
				['Licença Software Std',                 'license', 'RETAIL',    590.0000,  320.0000],
				['Licença por Assento',                  'seat',    'RETAIL',     49.9000,   19.9000],
				['Firewall UTM Appliance',               'item',    'RETAIL',  11990.0000,  8700.0000],
				['Assinatura Antispam (usuário)',        'seat',    'RETAIL',      9.9000,    3.9000],
				['Backup em Nuvem (100 GB)',             'GB',      'RETAIL',      2.9000,    0.9000],
			];

			// 2 hard-coded products per category (512 total)
			$categoryProducts = [
				// Technology categories
				['Desenvolvimento Web Corporativo', 'project', 'WEBDEV', 15000.0000, 8000.0000],
				['Manutenção de Site', 'hour', 'WEBDEV', 180.0000, 90.0000],
				['App Mobile iOS', 'project', 'MOBDEV', 25000.0000, 12000.0000],
				['App Mobile Android', 'project', 'MOBDEV', 22000.0000, 11000.0000],
				['API RESTful', 'service', 'BACKEND', 8000.0000, 3500.0000],
				['Microserviços', 'service', 'BACKEND', 12000.0000, 6000.0000],
				['Interface React', 'project', 'FRONTEND', 10000.0000, 4500.0000],
				['Componentes Vue.js', 'project', 'FRONTEND', 8000.0000, 3500.0000],
				['Chatbot com IA', 'service', 'AI', 15000.0000, 7000.0000],
				['Análise Preditiva', 'service', 'AI', 20000.0000, 9000.0000],
				['Modelo de Machine Learning', 'service', 'ML', 18000.0000, 8000.0000],
				['Treinamento de Modelo', 'service', 'ML', 12000.0000, 5000.0000],
				['Dashboard Analítico', 'project', 'DATA_ANAL', 9000.0000, 4000.0000],
				['Relatórios Personalizados', 'service', 'DATA_ANAL', 5000.0000, 2000.0000],
				['Data Warehouse', 'project', 'DATA_SCI', 30000.0000, 15000.0000],
				['Limpeza de Dados', 'service', 'DATA_SCI', 8000.0000, 3000.0000],
				['Auditoria de Segurança', 'service', 'INFOSEC', 15000.0000, 6000.0000],
				['Pentest', 'service', 'INFOSEC', 20000.0000, 8000.0000],
				['Proteção contra Ransomware', 'service', 'CYBERSEC', 12000.0000, 5000.0000],
				['Monitoramento de Ameaças', 'month', 'CYBERSEC', 2000.0000, 800.0000],

				// Digital Creative categories
				['Logo Corporativo', 'project', 'GRAPH_DES', 3000.0000, 1200.0000],
				['Manual de Marca', 'project', 'GRAPH_DES', 5000.0000, 2000.0000],
				['Prototipagem UI/UX', 'project', 'UIUX', 8000.0000, 3500.0000],
				['Testes de Usabilidade', 'service', 'UIUX', 4000.0000, 1500.0000],
				['Animação Corporativa', 'minute', 'MOTION', 500.0000, 200.0000],
				['Motion Graphics', 'second', 'MOTION', 50.0000, 20.0000],
				['Edição de Vídeo Promocional', 'minute', 'VIDEO_EDIT', 800.0000, 300.0000],
				['Correção de Cor', 'hour', 'VIDEO_EDIT', 200.0000, 80.0000],
				['Trilha Sonora Original', 'minute', 'AUDIO_PROD', 1000.0000, 400.0000],
				['Mixagem e Masterização', 'track', 'AUDIO_PROD', 500.0000, 200.0000],

				// HR categories
				['Recrutamento Especializado', 'position', 'RECRUIT', 8000.0000, 3000.0000],
				['Triagem de CVs', 'cv', 'RECRUIT', 50.0000, 20.0000],
				['Treinamento em Liderança', 'participant', 'TRAINING', 500.0000, 200.0000],
				['Workshop de Habilidades', 'session', 'TRAINING', 3000.0000, 1200.0000],
				['Processamento de Folha', 'employee', 'DP', 100.0000, 40.0000],
				['Rescisão Trabalhista', 'process', 'DP', 500.0000, 200.0000],

				// Business & Finance categories
				['Consultoria Estratégica', 'month', 'BUS_CONS', 15000.0000, 6000.0000],
				['Plano de Negócios', 'project', 'BUS_CONS', 10000.0000, 4000.0000],
				['Planejamento Estratégico', 'project', 'STRAT_PLAN', 20000.0000, 8000.0000],
				['Análise SWOT', 'service', 'STRAT_PLAN', 5000.0000, 2000.0000],
				['Pesquisa de Mercado', 'study', 'MARKET_ANAL', 15000.0000, 6000.0000],
				['Análise de Concorrência', 'report', 'MARKET_ANAL', 8000.0000, 3000.0000],

				// Sales categories
				['Venda Consultiva', 'deal', 'DIRECT_SALES', 5000.0000, 2000.0000],
				['Apresentação Comercial', 'presentation', 'DIRECT_SALES', 3000.0000, 1200.0000],
				['Gestão de Canal', 'channel', 'CHANNEL_SALES', 10000.0000, 4000.0000],
				['Treinamento de Revendedores', 'participant', 'CHANNEL_SALES', 500.0000, 200.0000],

				// Administration categories
				['Gestão de Facilities', 'month', 'FACILITIES', 10000.0000, 4000.0000],
				['Manutenção Predial', 'month', 'FACILITIES', 5000.0000, 2000.0000],
				['Recepcionista', 'month', 'RECEPTION', 3000.0000, 1200.0000],
				['Atendimento Telefônico', 'month', 'RECEPTION', 2000.0000, 800.0000],

				// Clerks categories
				['Papel A4 75g', 'ream', 'OFFICE', 25.0000, 15.0000],
				['Caneta Esferográfica Azul', 'unit', 'OFFICE', 2.5000, 1.0000],
				['Pastas Suspensas', 'unit', 'STATIONERY', 8.0000, 3.0000],
				['Clips Metálicos', 'box', 'STATIONERY', 5.0000, 2.0000],

				// Cleaning categories
				['Detergente Líquido 5L', 'unit', 'CLEAN_PROD', 25.0000, 12.0000],
				['Desinfetante 2L', 'unit', 'CLEAN_PROD', 18.0000, 9.0000],
				['Limpeza de Escritório', 'm²', 'COMM_CLEAN', 15.0000, 6.0000],
				['Limpeza de Vidros', 'm²', 'GLASS_CLEAN', 20.0000, 8.0000],

				// Healthcare categories
				['Consulta Médica Ocupacional', 'consultation', 'MED_CONSULT', 300.0000, 120.0000],
				['Exame Admissional', 'exam', 'MED_CONSULT', 200.0000, 80.0000],
				['Exames Laboratoriais', 'package', 'EXAMS', 400.0000, 160.0000],
				['Coleta de Sangue', 'collection', 'EXAMS', 50.0000, 20.0000],

				// Legal categories
				['Contrato Social', 'contract', 'BUSINESS_LAW', 2000.0000, 800.0000],
				['Alteração Contratual', 'amendment', 'BUSINESS_LAW', 1500.0000, 600.0000],
				['Auditoria Trabalhista', 'audit', 'LABOR_LAW', 8000.0000, 3000.0000],
				['Defesa em Processo', 'lawsuit', 'LABOR_LAW', 5000.0000, 2000.0000],

				// Gastronomy categories
				['Coffee Break Executivo', 'person', 'COFFEE_BREAK', 45.0000, 20.0000],
				['Coffee Break Premium', 'person', 'COFFEE_BREAK', 65.0000, 30.0000],
				['Buffet Corporativo', 'person', 'BUFFET', 80.0000, 35.0000],
				['Serviço de Mesa', 'person', 'TABLE_SERVICE', 30.0000, 12.0000],

				// Financial categories
				['Consultoria Contábil', 'month', 'ACCOUNTING', 3000.0000, 1200.0000],
				['Declaração de IRPF', 'declaration', 'ACCOUNTING', 500.0000, 200.0000],
				['Auditoria Externa', 'audit', 'AUDIT', 15000.0000, 6000.0000],
				['Relatório de Auditoria', 'report', 'AUDIT', 5000.0000, 2000.0000],

				// Generic categories
				['Receita Diversa', 'transaction', 'INCOME_MISC', 1000.0000, 0.0000],
				['Despesa Operacional', 'expense', 'OPER_EXP', 500.0000, 500.0000],
				['Ativo Imobilizado', 'asset', 'ASSETS', 10000.0000, 10000.0000],
				['Passivo Circulante', 'liability', 'LIABIL', 5000.0000, 5000.0000],
			];

			// Add all category products to catalog
			$catalog = array_merge($catalog, $categoryProducts);

			// Generate additional products with Faker up to 2056
			$faker = \Faker\Factory::create('pt_BR');
			$allCategoryCodes = array_keys($catIds);
			$units = ['unit', 'hour', 'day', 'month', 'year', 'kg', 'g', 'l', 'ml', 'm²', 'm³', 'package', 'set', 'pair', 'dozen'];

			$currentCount = count($catalog);
			// while ($currentCount < 2056) {
			while ($currentCount < 4) {
				$categoryCode = $faker->randomElement($allCategoryCodes);
				$unit = $faker->randomElement($units);

				// Generate realistic prices based on unit
				$basePrice = match ($unit) {
					'hour' => $faker->randomFloat(4, 100, 500),
					'day' => $faker->randomFloat(4, 800, 3000),
					'month' => $faker->randomFloat(4, 2000, 10000),
					'year' => $faker->randomFloat(4, 20000, 100000),
					'kg' => $faker->randomFloat(4, 10, 200),
					'l' => $faker->randomFloat(4, 5, 100),
					'm²' => $faker->randomFloat(4, 50, 500),
					'package' => $faker->randomFloat(4, 100, 1000),
					default => $faker->randomFloat(4, 1, 1000),
				};

				$sellPrice = $basePrice * $faker->randomFloat(4, 1, 3);
				$costPrice = $sellPrice * $faker->randomFloat(4, 0.3, 0.8);

				// Generate product name
				$productTypes = ['Premium', 'Standard', 'Enterprise', 'Profissional', 'Básico', 'Avançado'];
				$productSuffixes = ['Package', 'Service', 'Solution', 'System', 'Suite', 'License', 'Subscription'];

				$name = $faker->words($faker->numberBetween(2, 5), true) . ' ' .
					$faker->randomElement($productTypes) . ' ' .
					$faker->randomElement($productSuffixes);

				$catalog[] = [
					$name,
					$unit,
					$categoryCode,
					round($sellPrice, 4),
					round($costPrice, 4)
				];

				$currentCount++;
			}

			// Ensure exactly 2056 products
			// $catalog = array_slice($catalog, 0, 2056);
			$catalog = array_slice($catalog, 0, 4);

			// echo "Total de produtos no catálogo: " . count($catalog) . "\n";
			// echo "Primeiros 5 produtos:\n";
			// for ($i = 0; $i < 5; $i++) {
			// 	echo ($i + 1) . ". " . $catalog[$i][0] . " (" . $catalog[$i][2] . ")\n";
			// }
			// echo "\nÚltimos 5 produtos:\n";
			// for ($i = 2051; $i < 2056; $i++) {
			// 	echo ($i + 1) . ". " . $catalog[$i][0] . " (" . $catalog[$i][2] . ")\n";
			// }


			// Unidades aceitas por tipo
			$unitSets = [
				'service' => ['service', 'hour', 'session', 'sprint', 'day', 'month', 'visit', 'ticket'],
				'infra'   => ['hour', 'service', 'month', 'visit', 'ticket'],
				'dev'     => ['hour', 'session', 'sprint', 'service', 'month'],
				'retail'  => ['item', 'license', 'seat', 'GB', 'meter', 'point', 'other'],
			];

			// Auxiliares opcionais (se não existirem, ficam nulos — sem quebrar)
			$anyTaxId   = Tax::query()->value('id');
			$anySaleCoa = ChartOfAccount::query()->value('id');
			$anyExpCoa  = ChartOfAccount::query()->inRandomOrder()->value('id');

			$created = 0;
			$updated = 0;

			foreach ($catalog as [$name, $mainUnit, $catCode, $sell, $cost]) {
				try {
					$primaryCat = $catIds[$catCode] ?? null;
					if (!$primaryCat) {
						// Se por algum motivo a categoria não existir, ignora o item com log (defensivo)
						Log::warning("ProductServiceSeeder: categoria ausente para {$name} ({$catCode}), item ignorado.");
						continue;
					}

					$sku  = strtoupper(Str::slug(mb_substr($name, 0, 24), '-')) . '-' . Str::upper(Str::random(6));
					$from = now($tz)->subDays(random_int(0, 120));
					$until = (clone $from)->addDays(random_int(200, 900));

					$isRetail = in_array($mainUnit, ['item', 'license', 'seat', 'GB', 'meter', 'point'], true);
					$tags     = $isRetail ? ['revenda', 'estoque', 'hardware'] : ['serviço', 'SLA', 'projeto'];

					// accepted units
					$accepted = $isRetail ? $unitSets['retail'] : (str_contains(mb_strtolower($name), 'desenvolvimento') || str_contains(mb_strtolower($name), 'next.js')
						? $unitSets['dev'] : $unitSets['infra']);

					// Currencies aceitas (sempre inclui a default)
					$curr = ['BRL'];
					if ($isRetail && random_int(0, 1)) $curr[] = 'USD';

					// Tentativa de vincular a uma ProductServiceUnit coerente (por nome ou unidade)
					$unitId = ProductServiceUnit::query()
						->where('name', $name)->value('id');

					if (!$unitId) {
						$unitId = ProductServiceUnit::query()
							->where(AC::COL_MUNIT, $mainUnit)
							->inRandomOrder()
							->value('id');
					}

					$imgName = Str::slug($name) . '.png';
					$payload = [
						'name'                 => $name,
						'sku'                  => $sku,
						BC::COL_SL_PRC         => $this->money4($sell),
						BC::COL_PC_PRC         => $this->money4($cost),
						BC::COL_AC_CUR         => $curr,
						BC::COL_AC_MUNITS      => $accepted,
						'description'          => $faker->sentence(random_int(10, 20)),
						'attributes'           => [
							'tax_included'     => (bool) random_int(0, 1),
							'warranty_months'  => $isRetail ? [6, 12, 24][array_rand([6, 12, 24])] : null,
							'bundle'           => $isRetail && random_int(0, 1) ? $faker->word() : null,
							'service_level'    => !$isRetail ? [4, 8, 24][array_rand([4, 8, 24])] : null,
						],
						'tags'                 => $tags,
						DC::COL_PRO_IMG        => "images/products/{$imgName}",
						'icon'                 => $isRetail ? 'lucide-cpu' : 'lucide-server-cog',
						'quantity'             => $isRetail ? (float) random_int(5, 80) : 0.0,
						BC::COL_TAX_ID         => $anyTaxId,      // se não houver taxa, o model manterá null sem quebrar
						BC::COL_CAT_ID         => $primaryCat,
						'categories'           => [['id' => $primaryCat]],
						DC::COL_RL_CAT         => $this->relatedCats($primaryCat, $catIds),
						BC::COL_UNIT_ID        => $unitId,        // se não existir, model normaliza para null
						BC::COL_UNITS_SOLD     => $isRetail ? random_int(0, 500) : 0,
						BC::COL_UNITS_CNC      => $isRetail ? random_int(0, 30) : 0,
						BC::COL_UNITS_RTRN     => $isRetail ? random_int(0, 15) : 0,
						'type'                 => '0',            // campo livre legado (0..9)
						BKC::COL_SL_COA        => $anySaleCoa,
						BKC::COL_EXP_COA       => $anyExpCoa,
						AC::COL_AV_FROM        => $from,
						AC::COL_AV_UNTIL       => $until,
						AC::COL_IA             => true,
						BC::COL_ON_SALE        => (bool) random_int(0, 1),
						BC::COL_IS_LK          => false,
						BC::COL_IS_TRS         => false,
						DC::COL_TABLE_CREATOR      => $creator,
						DC::COL_TABLE_UPDATER      => $creator,
					];

					// Limpa nulls residuais de attributes
					$payload['attributes'] = array_filter(
						$payload['attributes'],
						fn($v) => $v !== null
					);
					// (new \Symfony\Component\Console\Output\ConsoleOutput
					// )->writeln("Criando Produto/Serviço: {$payload['name']}, SKU: {$payload['sku']}");
					$existing = ProductService::query()->where('name', $name)->first();
					if ($existing) {
						$existing->fill($payload)->save();
						$updated++;
					} else {
						ProductService::create($payload);
						$created++;
					}
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}

			Log::info("ProductServiceSeeder: created={$created}, updated={$updated}");
		});
	}

	private function money4(float $v): float
	{
		return (float) number_format($v, 4, '.', '');
	}

	/**
	 * Retorna mapa code => id para categorias existentes.
	 * Lança log se alguma estiver ausente (sem interromper a transação).
	 */
	private function categoryIdsByCode(array $codes): array
	{
		$rows = ProductServiceCategory::query()
			->whereIn('code', $codes)
			->get(['id', 'code'])
			->all();

		$map = [];
		foreach ($rows as $row) {
			$map[$row->code] = $row->id;
		}

		foreach ($codes as $code) {
			if (!isset($map[$code])) {
				Log::warning("ProductServiceSeeder: categoria com code={$code} não localizada.");
			}
		}
		return $map;
	}

	/**
	 * Gera pequenas relações extras para DC::COL_RL_CAT (sem repetir a primária).
	 */
	private function relatedCats(string $primaryId, array $all): array
	{
		$ids = array_values(array_unique(array_diff($all, [$primaryId])));
		shuffle($ids);
		$pick = array_slice($ids, 0, random_int(0, 2)); // 0..2 relações adicionais

		return array_map(fn($id) => ['id' => $id], $pick);
	}
}
