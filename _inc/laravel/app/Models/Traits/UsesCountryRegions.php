<?php

namespace App\Traits;

use App\Config\Constants\{ActivitiesConstants as AC, BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\{
	BrazilState,
	ChinaState,
	CountryName,
	PortugalState,
	UnitedStatesState,
	ArgentinaProvince,
	BoliviaDepartment,
	ChileRegion,
	EcuadorProvince,
	ParaguayDepartment,
	PeruDepartment,
	UruguayDepartment,
	ColombiaDepartment,
	GuyanaRegion
};
use App\Services\{DTO\GeoZipResolution, GeoLookupService};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{Log, Schema};

trait UsesCountryRegions
{
	use DetectsRows, NormalizesArrays;

	/**
	 * Map ISO-3166-1 alpha-2 country code => enum class for subnational units.
	 * @var array<string, class-string>
	 */
	public const STATE_ENUMS = [
		'BR' => BrazilState::class,
		'PT' => PortugalState::class,
		'US' => UnitedStatesState::class,
		'CN' => ChinaState::class,

		'AR' => ArgentinaProvince::class,
		'BO' => BoliviaDepartment::class,
		'CL' => ChileRegion::class,
		'EC' => EcuadorProvince::class,
		'PY' => ParaguayDepartment::class,
		'PE' => PeruDepartment::class,
		'UY' => UruguayDepartment::class,
		'CO' => ColombiaDepartment::class,
		'GY' => GuyanaRegion::class,
	];

	/**
	 * @var array<string, array<string, string[]>>
	 */
	public const GEO_COUNTRY_TOKENS_I18N = [
		'pt-br' => [
			'BR' => ['BRASIL', 'REPÚBLICA FEDERATIVA DO BRASIL'],
			'PT' => ['PORTUGAL', 'REPÚBLICA PORTUGUESA'],
			'US' => ['ESTADOS UNIDOS', 'EUA', 'ESTADOS UNIDOS DA AMÉRICA'],
			'CN' => ['CHINA', 'REPÚBLICA POPULAR DA CHINA', '中国', '中國'],
			'AR' => ['ARGENTINA', 'REPÚBLICA ARGENTINA'],
			'BO' => ['BOLÍVIA', 'ESTADO PLURINACIONAL DA BOLÍVIA'],
			'CL' => ['CHILE', 'REPÚBLICA DO CHILE'],
			'EC' => ['EQUADOR', 'REPÚBLICA DO EQUADOR'],
			'PY' => ['PARAGUAI', 'REPÚBLICA DO PARAGUAI'],
			'PE' => ['PERU', 'PERÚ', 'REPÚBLICA DO PERU'],
			'UY' => ['URUGUAI', 'REPÚBLICA ORIENTAL DO URUGUAI'],
			'CO' => ['COLÔMBIA', 'COLOMBIA', 'REPÚBLICA DA COLÔMBIA'],
			'GY' => ['GUIANA', 'GUYANA', 'REPÚBLICA COOPERATIVA DA GUIANA'],
		],
		'en' => [
			'BR' => ['BRAZIL', 'FEDERATIVE REPUBLIC OF BRAZIL'],
			'PT' => ['PORTUGAL', 'PORTUGUESE REPUBLIC'],
			'US' => ['UNITED STATES', 'UNITED STATES OF AMERICA', 'USA', 'U.S.A.'],
			'CN' => ['CHINA', "PEOPLE'S REPUBLIC OF CHINA", 'PRC', '中国', '中國'],
			'AR' => ['ARGENTINA', 'ARGENTINE REPUBLIC'],
			'BO' => ['BOLIVIA', 'PLURINATIONAL STATE OF BOLIVIA'],
			'CL' => ['CHILE', 'REPUBLIC OF CHILE'],
			'EC' => ['ECUADOR', 'REPUBLIC OF ECUADOR'],
			'PY' => ['PARAGUAY', 'REPUBLIC OF PARAGUAY'],
			'PE' => ['PERU', 'REPUBLIC OF PERU'],
			'UY' => ['URUGUAY', 'EASTERN REPUBLIC OF URUGUAY'],
			'CO' => ['COLOMBIA', 'REPUBLIC OF COLOMBIA'],
			'GY' => ['GUYANA', 'CO-OPERATIVE REPUBLIC OF GUYANA', 'COOPERATIVE REPUBLIC OF GUYANA'],
		],
		'es' => [
			'BR' => ['BRASIL', 'REPÚBLICA FEDERATIVA DE BRASIL'],
			'PT' => ['PORTUGAL', 'REPÚBLICA PORTUGUESA'],
			'US' => ['ESTADOS UNIDOS', 'ESTADOS UNIDOS DE AMÉRICA', 'EE. UU.', 'EEUU'],
			'CN' => ['CHINA', 'REPÚBLICA POPULAR CHINA', '中国', '中國'],
			'AR' => ['ARGENTINA', 'REPÚBLICA ARGENTINA'],
			'BO' => ['BOLIVIA', 'ESTADO PLURINACIONAL DE BOLIVIA'],
			'CL' => ['CHILE', 'REPÚBLICA DE CHILE'],
			'EC' => ['ECUADOR', 'REPÚBLICA DEL ECUADOR'],
			'PY' => ['PARAGUAY', 'REPÚBLICA DEL PARAGUAY'],
			'PE' => ['PERÚ', 'PERU', 'REPÚBLICA DEL PERÚ'],
			'UY' => ['URUGUAY', 'REPÚBLICA ORIENTAL DEL URUGUAY'],
			'CO' => ['COLOMBIA', 'REPÚBLICA DE COLOMBIA'],
			'GY' => ['GUYANA', 'GUAYANA', 'REPÚBLICA COOPERATIVA DE GUYANA'],
		],
		'pt' => [
			'BR' => ['BRASIL'],
			'PT' => ['PORTUGAL'],
			'US' => ['ESTADOS UNIDOS', 'EUA'],
			'CN' => ['CHINA', '中国', '中國'],
			'AR' => ['ARGENTINA'],
			'BO' => ['BOLÍVIA', 'BOLIVIA'],
			'CL' => ['CHILE'],
			'EC' => ['EQUADOR'],
			'PY' => ['PARAGUAI'],
			'PE' => ['PERU', 'PERÚ'],
			'UY' => ['URUGUAI', 'URUGUAY'],
			'CO' => ['COLÔMBIA', 'COLOMBIA'],
			'GY' => ['GUIANA', 'GUYANA'],
		],
		'ar' => [
			'BR' => ['البرازيل'],
			'PT' => ['البرتغال'],
			'US' => ['الولايات المتحدة', 'الولايات المتحدة الأمريكية'],
			'CN' => ['الصين', 'جمهورية الصين الشعبية', '中国', '中國'],
			'AR' => ['الأرجنتين'],
			'BO' => ['بوليفيا'],
			'CL' => ['تشيلي'],
			'EC' => ['الإكوادور', 'الاكوادور'],
			'PY' => ['باراغواي'],
			'PE' => ['بيرو'],
			'UY' => ['أوروغواي'],
			'CO' => ['كولومبيا'],
			'GY' => ['غيانا'],
		],
		'da' => [
			'BR' => ['BRASILIEN'],
			'PT' => ['PORTUGAL'],
			'US' => ['USA', 'FORENEDE STATER', 'DE FORENEDE STATER'],
			'CN' => ['KINA', '中国', '中國'],
			'AR' => ['ARGENTINA'],
			'BO' => ['BOLIVIA'],
			'CL' => ['CHILE'],
			'EC' => ['ECUADOR'],
			'PY' => ['PARAGUAY'],
			'PE' => ['PERU'],
			'UY' => ['URUGUAY'],
			'CO' => ['COLOMBIA'],
			'GY' => ['GUYANA'],
		],
		'de' => [
			'BR' => ['BRASILIEN'],
			'PT' => ['PORTUGAL'],
			'US' => ['VEREINIGTE STAATEN', 'USA', 'VEREINIGTE STAATEN VON AMERIKA'],
			'CN' => ['CHINA', 'VOLKSREPUBLIK CHINA', '中国', '中國'],
			'AR' => ['ARGENTINIEN'],
			'BO' => ['BOLIVIEN'],
			'CL' => ['CHILE'],
			'EC' => ['ECUADOR'],
			'PY' => ['PARAGUAY'],
			'PE' => ['PERU'],
			'UY' => ['URUGUAY'],
			'CO' => ['KOLUMBIEN'],
			'GY' => ['GUYANA'],
		],
		'fr' => [
			'BR' => ['BRÉSIL', 'BRESIL'],
			'PT' => ['PORTUGAL'],
			'US' => ['ÉTATS-UNIS', 'ETATS-UNIS', 'ÉTATS-UNIS D’AMÉRIQUE', "ÉTATS-UNIS D'AMÉRIQUE", 'USA'],
			'CN' => ['CHINE', 'RÉPUBLIQUE POPULAIRE DE CHINE', 'REPUBLIQUE POPULAIRE DE CHINE', '中国', '中國'],
			'AR' => ['ARGENTINE'],
			'BO' => ['BOLIVIE'],
			'CL' => ['CHILI'],
			'EC' => ['ÉQUATEUR', 'EQUATEUR'],
			'PY' => ['PARAGUAY'],
			'PE' => ['PÉROU', 'PEROU'],
			'UY' => ['URUGUAY'],
			'CO' => ['COLOMBIE'],
			'GY' => ['GUYANA'],
		],
		'he' => [
			'BR' => ['ברזיל'],
			'PT' => ['פורטוגל'],
			'US' => ['ארצות הברית', 'ארצות-הברית'],
			'CN' => ['סין', '中国', '中國'],
			'AR' => ['ארגנטינה'],
			'BO' => ['בוליביה'],
			'CL' => ['צ׳ילה', "צ'ילה", 'צילה'],
			'EC' => ['אקוודור'],
			'PY' => ['פרגוואי'],
			'PE' => ['פרו'],
			'UY' => ['אורוגוואי'],
			'CO' => ['קולומביה'],
			'GY' => ['גיאנה'],
		],
		'it' => [
			'BR' => ['BRASILE'],
			'PT' => ['PORTOGALLO'],
			'US' => ['STATI UNITI', "STATI UNITI D'AMERICA", 'USA'],
			'CN' => ['CINA', 'REPUBBLICA POPOLARE CINESE', '中国', '中國'],
			'AR' => ['ARGENTINA'],
			'BO' => ['BOLIVIA'],
			'CL' => ['CILE'],
			'EC' => ['ECUADOR'],
			'PY' => ['PARAGUAY'],
			'PE' => ['PERÙ', 'PERU'],
			'UY' => ['URUGUAY'],
			'CO' => ['COLOMBIA'],
			'GY' => ['GUYANA'],
		],
		'ja' => [
			'BR' => ['ブラジル'],
			'PT' => ['ポルトガル'],
			'US' => ['アメリカ合衆国', '米国', 'USA'],
			'CN' => ['中国', '中華人民共和国', '中國'],
			'AR' => ['アルゼンチン'],
			'BO' => ['ボリビア'],
			'CL' => ['チリ'],
			'EC' => ['エクアドル'],
			'PY' => ['パラグアイ'],
			'PE' => ['ペルー'],
			'UY' => ['ウルグアイ'],
			'CO' => ['コロンビア'],
			'GY' => ['ガイアナ'],
		],
		'nl' => [
			'BR' => ['BRAZILIË', 'BRAZILIE'],
			'PT' => ['PORTUGAL'],
			'US' => ['VERENIGDE STATEN', 'VERENIGDE STATEN VAN AMERIKA', 'VS', 'USA'],
			'CN' => ['CHINA', 'VOLKSREPUBLIEK CHINA', '中国', '中國'],
			'AR' => ['ARGENTINIË', 'ARGENTINIE'],
			'BO' => ['BOLIVIA'],
			'CL' => ['CHILI'],
			'EC' => ['ECUADOR'],
			'PY' => ['PARAGUAY'],
			'PE' => ['PERU'],
			'UY' => ['URUGUAY'],
			'CO' => ['COLOMBIA'],
			'GY' => ['GUYANA'],
		],
		'pl' => [
			'BR' => ['BRAZYLIA'],
			'PT' => ['PORTUGALIA'],
			'US' => ['STANY ZJEDNOCZONE', 'USA', 'STANY ZJEDNOCZONE AMERYKI'],
			'CN' => ['CHINY', 'CHINA', '中国', '中國'],
			'AR' => ['ARGENTYNA'],
			'BO' => ['BOLIWIA'],
			'CL' => ['CHILE'],
			'EC' => ['EKWADOR', 'ECUADOR'],
			'PY' => ['PARAGWAJ', 'PARAGUAY'],
			'PE' => ['PERU'],
			'UY' => ['URUGWAJ', 'URUGUAY'],
			'CO' => ['KOLUMBIA', 'COLOMBIA'],
			'GY' => ['GUJANA', 'GUYANA'],
		],
		'ru' => [
			'BR' => ['БРАЗИЛИЯ'],
			'PT' => ['ПОРТУГАЛИЯ'],
			'US' => ['СОЕДИНЕННЫЕ ШТАТЫ', 'США', 'СОЕДИНЕННЫЕ ШТАТЫ АМЕРИКИ'],
			'CN' => ['КИТАЙ', 'КНР', 'КИТАЙСКАЯ НАРОДНАЯ РЕСПУБЛИКА', '中国', '中國'],
			'AR' => ['АРГЕНТИНА'],
			'BO' => ['БОЛИВИЯ'],
			'CL' => ['ЧИЛИ'],
			'EC' => ['ЭКВАДОР'],
			'PY' => ['ПАРАГВАЙ'],
			'PE' => ['ПЕРУ'],
			'UY' => ['УРУГВАЙ'],
			'CO' => ['КОЛУМБИЯ'],
			'GY' => ['ГАЙАНА'],
		],
		'tr' => [
			'BR' => ['BREZILYA'],
			'PT' => ['PORTEKİZ', 'PORTEKIZ'],
			'US' => ['ABD', 'AMERİKA BİRLEŞİK DEVLETLERİ', 'AMERIKA BIRLESIK DEVLETLERI', 'USA'],
			'CN' => ['ÇİN', 'CIN', 'ÇİN HALK CUMHURİYETİ', 'CIN HALK CUMHURIYETI', '中国', '中國'],
			'AR' => ['ARJANTİN', 'ARJANTIN'],
			'BO' => ['BOLİVYA', 'BOLIVYA'],
			'CL' => ['ŞİLİ', 'SILI'],
			'EC' => ['EKVADOR', 'ECUADOR'],
			'PY' => ['PARAGUAY'],
			'PE' => ['PERU'],
			'UY' => ['URUGUAY'],
			'CO' => ['KOLOMBİYA', 'KOLOMBIYA'],
			'GY' => ['GUYANA'],
		],
		'zh' => [
			'BR' => ['巴西'],
			'PT' => ['葡萄牙'],
			'US' => ['美国', '美國', '美利坚合众国', '美利堅合眾國'],
			'CN' => ['中国', '中國', '中华人民共和国', '中華人民共和國'],
			'AR' => ['阿根廷'],
			'BO' => ['玻利维亚', '玻利維亞'],
			'CL' => ['智利'],
			'EC' => ['厄瓜多尔', '厄瓜多爾'],
			'PY' => ['巴拉圭'],
			'PE' => ['秘鲁', '秘魯'],
			'UY' => ['乌拉圭', '烏拉圭'],
			'CO' => ['哥伦比亚', '哥倫比亞'],
			'GY' => ['圭亚那', '圭亞那'],
		],
	];

	public const CITIES_BY_STATE = [
		'BR' => [
			BrazilState::RJ->value => [
				'common' => [
					"Angra dos Reis",
					"Aperibé",
					"Araruama",
					"Areal",
					"Armação dos Búzios",
					"Arraial do Cabo",
					"Barra do Piraí",
					"Barra Mansa",
					"Belford Roxo",
					"Bom Jardim",
					"Bom Jesus do Itabapoana",
					"Cabo Frio",
					"Cachoeiras de Macacu",
					"Cambuci",
					"Campos dos Goytacazes",
					"Cantagalo",
					"Carapebus",
					"Cardoso Moreira",
					"Carmo",
					"Casimiro de Abreu",
					"Comendador Levy Gasparian",
					"Conceição de Macabu",
					"Cordeiro",
					"Duas Barras",
					"Duque de Caxias",
					"Engenheiro Paulo de Frontin",
					"Guapimirim",
					"Iguaba Grande",
					"Itaboraí",
					"Itaguaí",
					"Italva",
					"Itaocara",
					"Itaperuna",
					"Itatiaia",
					"Japeri",
					"Laje do Muriaé",
					"Macaé",
					"Macuco",
					"Magé",
					"Mangaratiba",
					"Maricá",
					"Mendes",
					"Mesquita",
					"Miguel Pereira",
					"Miracema",
					"Natividade",
					"Nilópolis",
					"Niterói",
					"Nova Friburgo",
					"Nova Iguaçu",
					"Paracambi",
					"Paraíba do Sul",
					"Paraty",
					"Paty do Alferes",
					"Petrópolis",
					"Pinheiral",
					"Piraí",
					"Porciúncula",
					"Porto Real",
					"Quatis",
					"Queimados",
					"Quissamã",
					"Resende",
					"Rio Bonito",
					"Rio Claro",
					"Rio das Flores",
					"Rio das Ostras",
					"Rio de Janeiro", // Capital
					"Santa Maria Madalena",
					"Santo Antônio de Pádua",
					"São Fidélis",
					"São Francisco de Itabapoana",
					"São Gonçalo",
					"São João da Barra",
					"São João de Meriti",
					"São José de Ubá",
					"São José do Vale do Rio Preto",
					"São Pedro da Aldeia",
					"São Sebastião do Alto",
					"Sapucaia",
					"Saquarema",
					"Seropédica",
					"Silva Jardim",
					"Sumidouro",
					"Tanguá",
					"Teresópolis",
					"Trajano de Moraes",
					"Três Rios",
					"Valença",
					"Varre-Sai",
					"Vassouras",
					"Volta Redonda",
				],
				'normalized' => [
					"angra dos reis",
					"aperibe",
					"araruama",
					"areal",
					"armacao dos buzios",
					"arraial do cabo",
					"barra do pirai",
					"barra mansa",
					"belford roxo",
					"bom jardim",
					"bom jesus do itabapoana",
					"cabo frio",
					"cachoeiras de macacu",
					"cambuci",
					"campos dos goytacazes",
					"cantagalo",
					"carapebus",
					"cardoso moreira",
					"carmo",
					"casimiro de abreu",
					"comendador levy gasparian",
					"conceicao de macabu",
					"cordeiro",
					"duas barras",
					"duque de caxias",
					"engenheiro paulo de frontin",
					"guapimirim",
					"iguaba grande",
					"itaborai",
					"itaguai",
					"italva",
					"itaocara",
					"itaperuna",
					"itatiaia",
					"japeri",
					"laje do muriae",
					"macae",
					"macuco",
					"mage",
					"mangaratiba",
					"marica",
					"mendes",
					"mesquita",
					"miguel pereira",
					"miracema",
					"natividade",
					"nilopolis",
					"niteroi",
					"nova friburgo",
					"nova iguacu",
					"paracambi",
					"paraiba do sul",
					"paraty",
					"paty do alferes",
					"petropolis",
					"pinheiral",
					"pirai",
					"porciuncula",
					"porto real",
					"quatis",
					"queimados",
					"quissama",
					"resende",
					"rio bonito",
					"rio claro",
					"rio das flores",
					"rio das ostras",
					"rio de janeiro",
					"santa maria madalena",
					"santo antonio de padua",
					"sao fidelis",
					"sao francisco de itabapoana",
					"sao goncalo",
					"sao joao da barra",
					"sao joao de meriti",
					"sao jose de uba",
					"sao jose do vale do rio preto",
					"sao pedro da aldeia",
					"sao sebastiao do alto",
					"sapucaia",
					"saquarema",
					"seropedica",
					"silva jardim",
					"sumidouro",
					"tangua",
					"teresopolis",
					"trajano de moraes",
					"tres rios",
					"valenca",
					"varre-sai",
					"vassouras",
					"volta redonda"
				],
			],
			BrazilState::SP->value => [
				'common' => [
					"Adamantina",
					"Adolfo",
					"Aguaí",
					"Águas da Prata",
					"Águas de Lindóia",
					"Águas de Santa Bárbara",
					"Águas de São Pedro",
					"Agudos",
					"Alambari",
					"Alfredo Marcondes",
					"Altair",
					"Altinópolis",
					"Alto Alegre",
					"Alumínio",
					"Álvares Florence",
					"Álvares Machado",
					"Álvaro de Carvalho",
					"Alvinlândia",
					"Americana",
					"Américo Brasiliense",
					"Américo de Campos",
					"Amparo",
					"Analândia",
					"Andradina",
					"Angatuba",
					"Anhembi",
					"Anhumas",
					"Aparecida",
					"Aparecida d'Oeste",
					"Apiaí",
					"Araçariguama",
					"Araçatuba",
					"Araçoiaba da Serra",
					"Aramina",
					"Arandu",
					"Arapeí",
					"Araraquara",
					"Araras",
					"Arco-Íris",
					"Arealva",
					"Areias",
					"Areiópolis",
					"Ariranha",
					"Artur Nogueira",
					"Arujá",
					"Aspásia",
					"Assis",
					"Atibaia",
					"Auriflama",
					"Avaí",
					"Avanhandava",
					"Avaré",
					"Bady Bassitt",
					"Balbinos",
					"Bálsamo",
					"Bananal",
					"Barão de Antonina",
					"Barbosa",
					"Bariri",
					"Barra Bonita",
					"Barra do Chapéu",
					"Barra do Turvo",
					"Barretos",
					"Barrinha",
					"Barueri",
					"Bastos",
					"Batatais",
					"Bauru",
					"Bebedouro",
					"Bento de Abreu",
					"Bernardino de Campos",
					"Bertioga",
					"Bilac",
					"Birigui",
					"Biritiba-Mirim",
					"Boa Esperança do Sul",
					"Bocaina",
					"Bofete",
					"Boituva",
					"Bom Jesus dos Perdões",
					"Bom Sucesso de Itararé",
					"Borá",
					"Boracéia",
					"Borborema",
					"Borebi",
					"Botucatu",
					"Bragança Paulista",
					"Braúna",
					"Brejo Alegre",
					"Brodowski",
					"Brotas",
					"Buri",
					"Buritama",
					"Buritizal",
					"Cabrália Paulista",
					"Cabreúva",
					"Caçapava",
					"Cachoeira Paulista",
					"Caconde",
					"Cafelândia",
					"Caiabu",
					"Caieiras",
					"Caiuá",
					"Cajamar",
					"Cajati",
					"Cajobi",
					"Cajuru",
					"Campina do Monte Alegre",
					"Campinas",
					"Campo Limpo Paulista",
					"Campos do Jordão",
					"Campos Novos Paulista",
					"Cananéia",
					"Canas",
					"Cândido Mota",
					"Cândido Rodrigues",
					"Canitar",
					"Capão Bonito",
					"Capela do Alto",
					"Capivari",
					"Caraguatatuba",
					"Carapicuíba",
					"Cardoso",
					"Casa Branca",
					"Cássia dos Coqueiros",
					"Castilho",
					"Catanduva",
					"Catiguá",
					"Cedral",
					"Cerqueira César",
					"Cerquilho",
					"Cesário Lange",
					"Charqueada",
					"Chavantes",
					"Clementina",
					"Colina",
					"Colômbia",
					"Conchal",
					"Conchas",
					"Cordeirópolis",
					"Coroados",
					"Coronel Macedo",
					"Corumbataí",
					"Cosmópolis",
					"Cosmorama",
					"Cotia",
					"Cravinhos",
					"Cristais Paulista",
					"Cruzália",
					"Cruzeiro",
					"Cubatão",
					"Cunha",
					"Descalvado",
					"Diadema",
					"Dirce Reis",
					"Divinolândia",
					"Dobrada",
					"Dois Córregos",
					"Dolcinópolis",
					"Dourado",
					"Dracena",
					"Duartina",
					"Dumont",
					"Echaporã",
					"Eldorado",
					"Elias Fausto",
					"Elisiário",
					"Embaúba",
					"Embu das Artes",
					"Embu-Guaçu",
					"Emilianópolis",
					"Engenheiro Coelho",
					"Espírito Santo do Pinhal",
					"Espírito Santo do Turvo",
					"Estiva Gerbi",
					"Estrela d'Oeste",
					"Estrela do Norte",
					"Euclides da Cunha Paulista",
					"Fartura",
					"Fernando Prestes",
					"Fernandópolis",
					"Fernão",
					"Ferraz de Vasconcelos",
					"Flora Rica",
					"Floreal",
					"Flórida Paulista",
					"Florínea",
					"Franca",
					"Francisco Morato",
					"Franco da Rocha",
					"Gabriel Monteiro",
					"Gália",
					"Garça",
					"Gastão Vidigal",
					"Gavião Peixoto",
					"General Salgado",
					"Getulina",
					"Glicério",
					"Guaiçara",
					"Guaimbê",
					"Guaíra",
					"Guapiaçu",
					"Guapiara",
					"Guará",
					"Guaraçaí",
					"Guaraci",
					"Guarani d'Oeste",
					"Guarantã",
					"Guararapes",
					"Guararema",
					"Guaratinguetá",
					"Guareí",
					"Guariba",
					"Guarujá",
					"Guarulhos",
					"Guatapará",
					"Guzolândia",
					"Herculândia",
					"Holambra",
					"Hortolândia",
					"Iacanga",
					"Iacri",
					"Iaras",
					"Ibaté",
					"Ibirá",
					"Ibirarema",
					"Ibitinga",
					"Ibiúna",
					"Icém",
					"Iepê",
					"Igaraçu do Tietê",
					"Igarapava",
					"Igaratá",
					"Iguape",
					"Ilha Comprida",
					"Ilha Solteira",
					"Ilhabela",
					"Indaiatuba",
					"Indiana",
					"Indiaporã",
					"Inúbia Paulista",
					"Ipaussu",
					"Iperó",
					"Ipeúna",
					"Ipiguá",
					"Iporanga",
					"Ipuã",
					"Iracemápolis",
					"Irapuã",
					"Irapuru",
					"Itaberá",
					"Itaí",
					"Itajobi",
					"Itaju",
					"Itanhaém",
					"Itaoca",
					"Itapecerica da Serra",
					"Itapetininga",
					"Itapeva",
					"Itapevi",
					"Itapira",
					"Itapirapuã Paulista",
					"Itápolis",
					"Itaporanga",
					"Itapuí",
					"Itapura",
					"Itaquaquecetuba",
					"Itararé",
					"Itariri",
					"Itatiba",
					"Itatinga",
					"Itirapina",
					"Itirapuã",
					"Itobi",
					"Itu",
					"Itupeva",
					"Ituverava",
					"Jaborandi",
					"Jaboticabal",
					"Jacareí",
					"Jaci",
					"Jacupiranga",
					"Jaguariúna",
					"Jales",
					"Jambeiro",
					"Jandira",
					"Jardinópolis",
					"Jarinu",
					"Jaú",
					"Jeriquara",
					"Joanópolis",
					"João Ramalho",
					"José Bonifácio",
					"Júlio Mesquita",
					"Jumirim",
					"Jundiaí",
					"Junqueirópolis",
					"Juquiá",
					"Juquitiba",
					"Lagoinha",
					"Laranjal Paulista",
					"Lavínia",
					"Lavrinhas",
					"Leme",
					"Lençóis Paulista",
					"Limeira",
					"Lindóia",
					"Lins",
					"Lorena",
					"Lourdes",
					"Louveira",
					"Lucélia",
					"Lucianópolis",
					"Luís Antônio",
					"Luiziânia",
					"Lupércio",
					"Lutécia",
					"Macatuba",
					"Macaubal",
					"Macedônia",
					"Magda",
					"Mairinque",
					"Mairiporã",
					"Manduri",
					"Marabá Paulista",
					"Maracaí",
					"Marapoama",
					"Mariápolis",
					"Marília",
					"Marinópolis",
					"Martinópolis",
					"Matão",
					"Mauá",
					"Mendonça",
					"Meridiano",
					"Mesópolis",
					"Miguelópolis",
					"Mineiros do Tietê",
					"Mira Estrela",
					"Miracatu",
					"Mirandópolis",
					"Mirante do Paranapanema",
					"Mirassol",
					"Mirassolândia",
					"Mococa",
					"Mogi das Cruzes",
					"Mogi Guaçu",
					"Moji Mirim",
					"Mombuca",
					"Monções",
					"Mongaguá",
					"Monte Alegre do Sul",
					"Monte Alto",
					"Monte Aprazível",
					"Monte Azul Paulista",
					"Monte Castelo",
					"Monte Mor",
					"Monteiro Lobato",
					"Morro Agudo",
					"Morungaba",
					"Motuca",
					"Murutinga do Sul",
					"Nantes",
					"Narandiba",
					"Natividade da Serra",
					"Nazaré Paulista",
					"Neves Paulista",
					"Nhandeara",
					"Nipoã",
					"Nova Aliança",
					"Nova Campina",
					"Nova Canaã Paulista",
					"Nova Castilho",
					"Nova Europa",
					"Nova Granada",
					"Nova Guataporanga",
					"Nova Independência",
					"Nova Luzitânia",
					"Nova Odessa",
					"Novais",
					"Novo Horizonte",
					"Nuporanga",
					"Ocauçu",
					"Óleo",
					"Olímpia",
					"Onda Verde",
					"Oriente",
					"Orindiúva",
					"Orlândia",
					"Osasco",
					"Oscar Bressane",
					"Osvaldo Cruz",
					"Ourinhos",
					"Ouro Verde",
					"Ouroeste",
					"Pacaembu",
					"Palestina",
					"Palmares Paulista",
					"Palmeira d'Oeste",
					"Palmital",
					"Panorama",
					"Paraguaçu Paulista",
					"Paraibuna",
					"Paraíso",
					"Paranapanema",
					"Paranapuã",
					"Parapuã",
					"Pardinho",
					"Pariquera-Açu",
					"Parisi",
					"Patrocínio Paulista",
					"Paulicéia",
					"Paulínia",
					"Paulistânia",
					"Paulo de Faria",
					"Pederneiras",
					"Pedra Bela",
					"Pedranópolis",
					"Pedregulho",
					"Pedreira",
					"Pedrinhas Paulista",
					"Pedro de Toledo",
					"Penápolis",
					"Pereira Barreiro",
					"Pereiras",
					"Peruíbe",
					"Piacatu",
					"Piedade",
					"Pilar do Sul",
					"Pindamonhangaba",
					"Pindorama",
					"Pinhalzinho",
					"Piquerobi",
					"Piquete",
					"Piracaia",
					"Piracicaba",
					"Piraju",
					"Pirajuí",
					"Pirangi",
					"Pirapora do Bom Jesus",
					"Pirapozinho",
					"Pirassununga",
					"Piratininga",
					"Pitangueiras",
					"Planalto",
					"Platina",
					"Poá",
					"Poloni",
					"Pompéia",
					"Pongaí",
					"Pontal",
					"Pontalinda",
					"Pontes Gestal",
					"Populina",
					"Porangaba",
					"Porto Feliz",
					"Porto Ferreira",
					"Potim",
					"Potirendaba",
					"Pracinha",
					"Pradópolis",
					"Praia Grande",
					"Pratânia",
					"Presidente Alves",
					"Presidente Bernardes",
					"Presidente Epitácio",
					"Presidente Prudente",
					"Presidente Venceslau",
					"Promissão",
					"Quadra",
					"Quatá",
					"Queiroz",
					"Queluz",
					"Quintana",
					"Rafard",
					"Rancharia",
					"Redenção da Serra",
					"Regente Feijó",
					"Reginópolis",
					"Registro",
					"Restinga",
					"Ribeira",
					"Ribeirão Bonito",
					"Ribeirão Branco",
					"Ribeirão Corrente",
					"Ribeirão do Sul",
					"Ribeirão dos Índios",
					"Ribeirão Grande",
					"Ribeirão Pires",
					"Ribeirão Preto",
					"Rifaina",
					"Rincão",
					"Rinópolis",
					"Rio Claro",
					"Rio das Pedras",
					"Rio Grande da Serra",
					"Riolândia",
					"Riversul",
					"Rosana",
					"Roseira",
					"Rubiácea",
					"Rubinéia",
					"Sabino",
					"Sagres",
					"Sales",
					"Sales Oliveira",
					"Salesópolis",
					"Salmourão",
					"Saltinho",
					"Salto",
					"Salto de Pirapora",
					"Salto Grande",
					"Sandovalina",
					"Santa Adélia",
					"Santa Albertina",
					"Santa Bárbara d'Oeste",
					"Santa Branca",
					"Santa Clara d'Oeste",
					"Santa Cruz da Conceição",
					"Santa Cruz da Esperança",
					"Santa Cruz das Palmeiras",
					"Santa Cruz do Rio Pardo",
					"Santa Ernestina",
					"Santa Fé do Sul",
					"Santa Gertrudes",
					"Santa Isabel",
					"Santa Lúcia",
					"Santa Maria da Serra",
					"Santa Mercedes",
					"Santa Rita d'Oeste",
					"Santa Rita do Passa Quatro",
					"Santa Rosa de Viterbo",
					"Santa Salete",
					"Santana da Ponte Pensa",
					"Santana de Parnaíba",
					"Santo Anastácio",
					"Santo André",
					"Santo Antônio da Alegria",
					"Santo Antônio de Posse",
					"Santo Antônio do Aracanguá",
					"Santo Antônio do Jardim",
					"Santo Antônio do Pinhal",
					"Santo Expedito",
					"Santópolis do Aguapeí",
					"Santos",
					"São Bento do Sapucaí",
					"São Bernardo do Campo",
					"São Caetano do Sul",
					"São Carlos",
					"São Francisco",
					"São João da Boa Vista",
					"São João das Duas Pontes",
					"São João de Iracema",
					"São João do Pau d'Alho",
					"São Joaquim da Barra",
					"São José da Bela Vista",
					"São José do Barreiro",
					"São José do Rio Pardo",
					"São José do Rio Preto",
					"São José dos Campos",
					"São Lourenço da Serra",
					"São Luís do Paraitinga",
					"São Manuel",
					"São Miguel Arcanjo",
					"São Paulo", // Capital
					"São Pedro",
					"São Pedro do Turvo",
					"São Roque",
					"São Sebastião",
					"São Sebastião da Grama",
					"São Simão",
					"São Vicente",
					"Sarapuí",
					"Sarutaiá",
					"Sebastianópolis do Sul",
					"Serra Azul",
					"Serra Negra",
					"Serrana",
					"Sertãozinho",
					"Sete Barras",
					"Severínia",
					"Silveiras",
					"Socorro",
					"Sorocaba",
					"Sud Mennucci",
					"Sumaré",
					"Suzano",
					"Suzanápolis",
					"Tabapuã",
					"Tabatinga",
					"Taboão da Serra",
					"Taciba",
					"Taguaí",
					"Taiaçu",
					"Taiúva",
					"Tambaú",
					"Tanabi",
					"Tapiraí",
					"Tapiratiba",
					"Taquaral",
					"Taquaritinga",
					"Taquarituba",
					"Taquarivaí",
					"Tarabai",
					"Tarumã",
					"Tatuí",
					"Taubaté",
					"Tejupá",
					"Teodoro Sampaio",
					"Terra Roxa",
					"Tietê",
					"Timburi",
					"Torre de Pedra",
					"Torrinha",
					"Trabiju",
					"Tremembé",
					"Três Fronteiras",
					"Tuiuti",
					"Tupã",
					"Tupi Paulista",
					"Turiúba",
					"Turmalina",
					"Ubarana",
					"Ubatuba",
					"Ubirajara",
					"Uchoa",
					"União Paulista",
					"Urânia",
					"Uru",
					"Urupês",
					"Valentim Gentil",
					"Valinhos",
					"Valparaíso",
					"Vargem",
					"Vargem Grande do Sul",
					"Vargem Grande Paulista",
					"Várzea Paulista",
					"Vera Cruz",
					"Vinhedo",
					"Viradouro",
					"Vista Alegre do Alto",
					"Vitória Brasil",
					"Votorantim",
					"Votuporanga",
					"Zacarias"
				],
				'normalized' => [
					"adamantina",
					"adolfo",
					"aguai",
					"aguas da prata",
					"aguas de lindoia",
					"aguas de santa barbara",
					"aguas de sao pedro",
					"agudos",
					"alambari",
					"alfredo marcondes",
					"altair",
					"altinopolis",
					"alto alegre",
					"aluminio",
					"alvares florence",
					"alvares machado",
					"alvaro de carvalho",
					"alvinlandia",
					"americana",
					"americo brasiliense",
					"americo de campos",
					"amparo",
					"analandia",
					"andradina",
					"angatuba",
					"anhembi",
					"anhumas",
					"aparecida",
					"aparecida d'oeste",
					"apiai",
					"aracariguama",
					"aracatuba",
					"aracoiaba da serra",
					"aramina",
					"arandu",
					"arapei",
					"araraquara",
					"araras",
					"arco-iris",
					"arealva",
					"areias",
					"areiopolis",
					"ariranha",
					"artur nogueira",
					"aruja",
					"aspasia",
					"assis",
					"atibaia",
					"auriflama",
					"avai",
					"avanhandava",
					"avare",
					"bady bassitt",
					"balbinos",
					"balsamo",
					"bananal",
					"barao de antonina",
					"barbosa",
					"bariri",
					"barra bonita",
					"barra do chapeu",
					"barra do turvo",
					"barretos",
					"barrinha",
					"barueri",
					"bastos",
					"batatais",
					"bauru",
					"bebedouro",
					"bento de abreu",
					"bernardino de campos",
					"bertioga",
					"bilac",
					"birigui",
					"biritiba-mirim",
					"boa esperanca do sul",
					"bocaina",
					"bofete",
					"boituva",
					"bom jesus dos perdoes",
					"bom sucesso de itarare",
					"bora",
					"boraceia",
					"borborema",
					"borebi",
					"botucatu",
					"braganca paulista",
					"brauna",
					"brejo alegre",
					"brodowski",
					"brotas",
					"buri",
					"buritama",
					"buritizal",
					"cabralia paulista",
					"cabreuva",
					"cacapava",
					"cachoeira paulista",
					"caconde",
					"cafelandia",
					"caiabu",
					"caieiras",
					"caiua",
					"cajamar",
					"cajati",
					"cajobi",
					"cajuru",
					"campina do monte alegre",
					"campinas",
					"campo limpo paulista",
					"campos do jordao",
					"campos novos paulista",
					"cananeia",
					"canas",
					"candido mota",
					"candido rodrigues",
					"canitar",
					"capao bonito",
					"capela do alto",
					"capivari",
					"caraguatatuba",
					"carapicuiba",
					"cardoso",
					"casa branca",
					"cassia dos coqueiros",
					"castilho",
					"catanduva",
					"catigua",
					"cedral",
					"cerqueira cesar",
					"cerquilho",
					"cesario lange",
					"charqueada",
					"chavantes",
					"clementina",
					"colina",
					"colombia",
					"conchal",
					"conchas",
					"cordeiropolis",
					"coroados",
					"coronel macedo",
					"corumbatai",
					"cosmopolis",
					"cosmorama",
					"cotia",
					"cravinhos",
					"cristais paulista",
					"cruzalia",
					"cruzeiro",
					"cubatao",
					"cunha",
					"descalvado",
					"diadema",
					"dirce reis",
					"divinolandia",
					"dobrada",
					"dois corregos",
					"dolcinopolis",
					"dourado",
					"dracena",
					"duartina",
					"dumont",
					"echapora",
					"eldorado",
					"elias fausto",
					"elisiario",
					"embauba",
					"embu das artes",
					"embu-guacu",
					"emilianopolis",
					"engenheiro coelho",
					"espirito santo do pinhal",
					"espirito santo do turvo",
					"estiva gerbi",
					"estrela d'oeste",
					"estrela do norte",
					"euclides da cunha paulista",
					"fartura",
					"fernando prestes",
					"fernandopolis",
					"fernao",
					"ferraz de vasconcelos",
					"flora rica",
					"floreal",
					"florida paulista",
					"florinea",
					"franca",
					"francisco morato",
					"franco da rocha",
					"gabriel monteiro",
					"galia",
					"garca",
					"gastao vidigal",
					"gaviao peixoto",
					"general salgado",
					"getulina",
					"glicerio",
					"guaicara",
					"guaimbe",
					"guaira",
					"guapiacu",
					"guapiara",
					"guara",
					"guaracai",
					"guaraci",
					"guarani d'oeste",
					"guaranta",
					"guararapes",
					"guararema",
					"guaratingueta",
					"guarei",
					"guariba",
					"guaruja",
					"guarulhos",
					"guatapara",
					"guzolandia",
					"herculandia",
					"holambra",
					"hortolandia",
					"iacanga",
					"iacri",
					"iaras",
					"ibate",
					"ibira",
					"birarema",
					"ibitinga",
					"ibiuna",
					"icem",
					"iepe",
					"igaracu do tiete",
					"igarapava",
					"igaratá",
					"iguape",
					"ilha comprida",
					"ilha solteira",
					"ilhabela",
					"indaiatuba",
					"indiana",
					"indiapora",
					"inubia paulista",
					"ipaussu",
					"ipero",
					"ipeuna",
					"ipigua",
					"iporanga",
					"ipua",
					"iracemapolis",
					"irapua",
					"irapuru",
					"itabera",
					"itai",
					"itajobi",
					"itaju",
					"itanhaem",
					"itaoca",
					"itapecerica da serra",
					"itapetininga",
					"itapeva",
					"itapevi",
					"itapira",
					"itapirapua paulista",
					"itapolis",
					"itaporanga",
					"itapui",
					"itapura",
					"itaquaquecetuba",
					"itarare",
					"itariri",
					"itatiba",
					"itatinga",
					"itirapina",
					"itirapua",
					"itobi",
					"itu",
					"itupeva",
					"ituverava",
					"jaborandi",
					"jaboticabal",
					"jacarei",
					"jaci",
					"jacupiranga",
					"jaguariuna",
					"jales",
					"jambeiro",
					"jandira",
					"jardinopolis",
					"jarinu",
					"jau",
					"jeriquara",
					"joanopolis",
					"joao ramalho",
					"jose bonifacio",
					"julio mesquita",
					"jumirim",
					"jundiai",
					"junqueiropolis",
					"juquia",
					"juquitiba",
					"lagoinha",
					"laranjal paulista",
					"lavinia",
					"lavrinhas",
					"leme",
					"lencois paulista",
					"limeira",
					"lindoia",
					"lins",
					"lorena",
					"lourdes",
					"louveira",
					"lucelia",
					"lucianopolis",
					"luis antonio",
					"luiziania",
					"lupercio",
					"lutecia",
					"macatuba",
					"macaubal",
					"macedonia",
					"magda",
					"mairinque",
					"mairipora",
					"manduri",
					"maraba paulista",
					"maracai",
					"marapoama",
					"mariapolis",
					"marilia",
					"marinopolis",
					"martinopolis",
					"matao",
					"maua",
					"mendonca",
					"meridiano",
					"mesopolis",
					"miguelopolis",
					"mineiros do tiete",
					"mira estrela",
					"miracatu",
					"mirandopolis",
					"mirante do paranapanema",
					"mirassol",
					"mirassolandia",
					"mococa",
					"mogi das cruzes",
					"mogi guacu",
					"moji mirim",
					"mombuca",
					"moncoes",
					"mongagua",
					"monte alegre do sul",
					"monte alto",
					"monte aprazivel",
					"monte azul paulista",
					"monte castelo",
					"monte mor",
					"monteiro lobato",
					"morro agudo",
					"morungaba",
					"motuca",
					"murutinga do sul",
					"nantes",
					"narandiba",
					"natividade da serra",
					"nazare paulista",
					"neves paulista",
					"nhandeara",
					"nipoa",
					"nova alianca",
					"nova campina",
					"nova canaa paulista",
					"nova castilho",
					"nova europa",
					"nova granada",
					"nova guataporanga",
					"nova independencia",
					"nova luzitania",
					"nova odessa",
					"novais",
					"novo horizonte",
					"nuporanga",
					"ocaucu",
					"oleo",
					"olimpia",
					"onda verde",
					"oriente",
					"orindiuva",
					"orlandia",
					"osasco",
					"oscar bressane",
					"osvaldo cruz",
					"ourinhos",
					"ouro verde",
					"ouroeste",
					"pacaembu",
					"palestina",
					"palmares paulista",
					"palmeira d'oeste",
					"palmital",
					"panorama",
					"paraguacu paulista",
					"paraibuna",
					"paraiso",
					"paranapanema",
					"paranapua",
					"parapua",
					"pardinho",
					"pariquera-acu",
					"parisi",
					"patrocinio paulista",
					"pauliceia",
					"paulinia",
					"paulistania",
					"paulo de faria",
					"pederneiras",
					"pedra bela",
					"pedranopolis",
					"pedregulho",
					"pedreira",
					"pedrinhas paulista",
					"pedro de toledo",
					"penapolis",
					"pereira barreiro",
					"pereirac",
					"peruibe",
					"piacatu",
					"piedade",
					"pilar do sul",
					"pindamonhangaba",
					"pindorama",
					"pinhalzinho",
					"piquerobi",
					"piquete",
					"piracaia",
					"piracicaba",
					"piraju",
					"pirajui",
					"pirangi",
					"pirapora do bom jesus",
					"pirapozinho",
					"pirassununga",
					"piratininga",
					"pitangueiras",
					"planalto",
					"platina",
					"poa",
					"poloni",
					"pompeia",
					"pongai",
					"pontal",
					"pontalinda",
					"pontes gestal",
					"populina",
					"porangaba",
					"porto feliz",
					"porto ferreira",
					"potim",
					"potirendaba",
					"pracinha",
					"pradopolis",
					"praia grande",
					"pratania",
					"presidente alves",
					"presidente bernardes",
					"presidente epitacio",
					"presidente prudente",
					"presidente venceslau",
					"promissao",
					"quadra",
					"quata",
					"queiroz",
					"queluz",
					"quintana",
					"rafard",
					"rancharia",
					"redencao da serra",
					"regente feijo",
					"reginopolis",
					"registro",
					"restinga",
					"ribeira",
					"ribeirao bonito",
					"ribeirao branco",
					"ribeirao corrente",
					"ribeirao do sul",
					"ribeirao dos indios",
					"ribeirao grande",
					"ribeirao pires",
					"ribeirao preto",
					"rifaina",
					"rincao",
					"rinopolis",
					"rio claro",
					"rio das pedras",
					"rio grande da serra",
					"riolandia",
					"riversul",
					"rosana",
					"roseira",
					"rubiacea",
					"rubineia",
					"sabino",
					"sagres",
					"sales",
					"sales oliveira",
					"salesopolis",
					"salmourao",
					"saltinho",
					"salto",
					"salto de pirapora",
					"salto grande",
					"sandovalina",
					"santa adelia",
					"santa albertina",
					"santa barbara d'oeste",
					"santa branca",
					"santa clara d'oeste",
					"santa cruz da conceicao",
					"santa cruz da esperanca",
					"santa cruz das palmeiras",
					"santa cruz do rio pardo",
					"santa ernestina",
					"santa fe do sul",
					"santa gertrudes",
					"santa isabel",
					"santa lucia",
					"santa maria da serra",
					"santa mercedes",
					"santa rita d'oeste",
					"santa rita do passa quatro",
					"santa rosa de viterbo",
					"santa salete",
					"santana da ponte pensa",
					"santana de parnaiba",
					"santo anastacio",
					"santo andre",
					"santo antonio da alegria",
					"santo antonio de posse",
					"santo antonio do aracangua",
					"santo antonio do jardim",
					"santo antonio do pinhal",
					"santo expedito",
					"santopolis do aguapei",
					"santos",
					"sao bento do sapucai",
					"sao bernardo do campo",
					"sao caetano do sul",
					"sao carlos",
					"sao francisco",
					"sao joao da boa vista",
					"sao joao das duas pontes",
					"sao joao de iracema",
					"sao joao do pau d'alho",
					"sao joaquim da barra",
					"sao jose da bela vista",
					"sao jose do barreiro",
					"sao jose do rio pardo",
					"sao jose do rio preto",
					"sao jose dos campos",
					"sao lourenco da serra",
					"sao luis do paraitinga",
					"sao manuel",
					"sao miguel arcanjo",
					"sao paulo",
					"sao pedro",
					"sao pedro do turvo",
					"sao roque",
					"sao sebastiao",
					"sao sebastiao da grama",
					"sao simao",
					"sao vicente",
					"sarapui",
					"sarutaia",
					"sebastianopolis do sul",
					"serra azul",
					"serra negra",
					"serrana",
					"sertaozinho",
					"sete barras",
					"severinia",
					"silveiras",
					"socorro",
					"sorocaba",
					"sud mennucci",
					"sumare",
					"suzano",
					"suzanapolis",
					"tabapua",
					"tabatinga",
					"taboao da serra",
					"taciba",
					"taguai",
					"taiacu",
					"taiuva",
					"tambau",
					"tanabi",
					"tapirai",
					"tapiratiba",
					"taquaral",
					"taquaritinga",
					"taquarituba",
					"taquarivai",
					"tarabai",
					"taruma",
					"tatui",
					"taubate",
					"tejupa",
					"teodoro sampaio",
					"terra roxa",
					"tiete",
					"timburi",
					"torre de pedra",
					"torrinha",
					"trabiju",
					"tremembe",
					"tres fronteiras",
					"tuiuti",
					"tupa",
					"tupi paulista",
					"turiuba",
					"turmalina",
					"ubarana",
					"ubatuba",
					"ubirajara",
					"uchoa",
					"uniao paulista",
					"urania",
					"uru",
					"urupes",
					"valentim gentil",
					"valinhos",
					"valparaiso",
					"vargem",
					"vargem grande do sul",
					"vargem grande paulista",
					"varzea paulista",
					"vera cruz",
					"vinhedo",
					"viradouro",
					"vista alegre do alto",
					"vitoria brasil",
					"votorantim",
					"votuporanga",
					"zacarias"
				],
			],
			BrazilState::MG->value => [
				'common' => [
					"Abadia dos Dourados",
					"Abaeté",
					"Abre Campo",
					"Acaiaca",
					"Açucena",
					"Água Boa",
					"Água Comprida",
					"Aguanil",
					"Águas Formosas",
					"Águas Vermelhas",
					"Aimorés",
					"Aiuruoca",
					"Alagoa",
					"Albertina",
					"Além Paraíba",
					"Alfenas",
					"Alfredo Vasconcelos",
					"Almenara",
					"Alpercata",
					"Alpinópolis",
					"Alterosa",
					"Alto Caparaó",
					"Alto Jequitibá",
					"Alto Rio Doce",
					"Alvarenga",
					"Alvinópolis",
					"Alvorada de Minas",
					"Amparo do Serra",
					"Andradas",
					"Andrelândia",
					"Angelândia",
					"Antônio Carlos",
					"Antônio Dias",
					"Antônio Prado de Minas",
					"Araçaí",
					"Aracitaba",
					"Araçuaí",
					"Araguari",
					"Arantina",
					"Araponga",
					"Araporã",
					"Arapuá",
					"Araújos",
					"Araxá",
					"Arceburgo",
					"Arcos",
					"Areado",
					"Argirita",
					"Aricanduva",
					"Arinos",
					"Astolfo Dutra",
					"Ataléia",
					"Augusto de Lima",
					"Baependi",
					"Baldim",
					"Bambuí",
					"Bandeira",
					"Bandeira do Sul",
					"Barão de Cocais",
					"Barão de Monte Alto",
					"Barbacena",
					"Barra Longa",
					"Barroso",
					"Bela Vista de Minas",
					"Belmiro Braga",
					"Belo Horizonte", // Capital
					"Belo Oriente",
					"Belo Vale",
					"Berilo",
					"Berizal",
					"Bertópolis",
					"Betim",
					"Bias Fortes",
					"Bicas",
					"Biquinhas",
					"Boa Esperança",
					"Bocaina de Minas",
					"Bocaiúva",
					"Bom Despacho",
					"Bom Jardim de Minas",
					"Bom Jesus da Penha",
					"Bom Jesus do Amparo",
					"Bom Jesus do Galho",
					"Bom Repouso",
					"Bom Sucesso",
					"Bonfim",
					"Bonfinópolis de Minas",
					"Bonito de Minas",
					"Borda da Mata",
					"Botelhos",
					"Botumirim",
					"Brás Pires",
					"Brasilândia de Minas",
					"Brasília de Minas",
					"Brasópolis",
					"Braúnas",
					"Brumadinho",
					"Bueno Brandão",
					"Buenópolis",
					"Bugre",
					"Buritis",
					"Buritizeiro",
					"Cabeceira Grande",
					"Cabo Verde",
					"Cachoeira da Prata",
					"Cachoeira de Minas",
					"Cachoeira de Pajeú",
					"Cachoeira Dourada",
					"Caetanópolis",
					"Caeté",
					"Caiana",
					"Cajuri",
					"Caldas",
					"Camacho",
					"Camanducaia",
					"Cambuí",
					"Cambuquira",
					"Campanário",
					"Campanha",
					"Campestre",
					"Campina Verde",
					"Campo Azul",
					"Campo Belo",
					"Campo do Meio",
					"Campo Florido",
					"Campos Altos",
					"Campos Gerais",
					"Cana Verde",
					"Canaã",
					"Canápolis",
					"Candeias",
					"Cantagalo",
					"Caparaó",
					"Capela Nova",
					"Capelinha",
					"Capetinga",
					"Capim Branco",
					"Capinópolis",
					"Capitão Andrade",
					"Capitão Enéas",
					"Capitólio",
					"Caputira",
					"Caraí",
					"Caranaíba",
					"Carandaí",
					"Carangola",
					"Caratinga",
					"Carbonita",
					"Careaçu",
					"Carlos Chagas",
					"Carmésia",
					"Carmo da Cachoeira",
					"Carmo da Mata",
					"Carmo de Minas",
					"Carmo do Cajuru",
					"Carmo do Paranaíba",
					"Carmo do Rio Claro",
					"Carmópolis de Minas",
					"Carneirinho",
					"Carrancas",
					"Carvalhópolis",
					"Carvalhos",
					"Casa Grande",
					"Cascalho Rico",
					"Cássia",
					"Cataguases",
					"Catas Altas",
					"Catas Altas da Noruega",
					"Catuji",
					"Catuti",
					"Caxambu",
					"Cedro do Abaeté",
					"Central de Minas",
					"Centralina",
					"Chácara",
					"Chalé",
					"Chapada do Norte",
					"Chapada Gaúcha",
					"Chiador",
					"Cipotânea",
					"Claraval",
					"Claro dos Poções",
					"Cláudio",
					"Coimbra",
					"Coluna",
					"Comendador Gomes",
					"Comercinho",
					"Conceição da Aparecida",
					"Conceição da Barra de Minas",
					"Conceição das Alagoas",
					"Conceição das Pedras",
					"Conceição de Ipanema",
					"Conceição do Mato Dentro",
					"Conceição do Pará",
					"Conceição do Rio Verde",
					"Conceição dos Ouros",
					"Cônego Marinho",
					"Confins",
					"Congonhal",
					"Congonhas",
					"Congonhas do Norte",
					"Conquista",
					"Conselheiro Lafaiete",
					"Conselheiro Pena",
					"Consolação",
					"Contagem",
					"Coqueiral",
					"Coração de Jesus",
					"Cordisburgo",
					"Cordislândia",
					"Corinto",
					"Coroaci",
					"Coromandel",
					"Coronel Fabriciano",
					"Coronel Murta",
					"Coronel Pacheco",
					"Coronel Xavier Chaves",
					"Córrego Danta",
					"Córrego do Bom Jesus",
					"Córrego Fundo",
					"Córrego Novo",
					"Couto de Magalhães de Minas",
					"Crisólita",
					"Cristais",
					"Cristália",
					"Cristiano Otoni",
					"Cristina",
					"Crucilândia",
					"Cruzeiro da Fortaleza",
					"Cruzília",
					"Cuparaque",
					"Curral de Dentro",
					"Curvelo",
					"Datas",
					"Delfim Moreira",
					"Delfinópolis",
					"Delta",
					"Descoberto",
					"Desterro de Entre Rios",
					"Desterro do Melo",
					"Diamantina",
					"Diogo de Vasconcelos",
					"Dionísio",
					"Divinésia",
					"Divino",
					"Divino das Laranjeiras",
					"Divinolândia de Minas",
					"Divinópolis",
					"Divisa Alegre",
					"Divisa Nova",
					"Divisópolis",
					"Dom Bosco",
					"Dom Cavati",
					"Dom Joaquim",
					"Dom Silvério",
					"Dom Viçoso",
					"Dona Euzébia",
					"Dores de Campos",
					"Dores de Guanhães",
					"Dores do Indaiá",
					"Dores do Turvo",
					"Doresópolis",
					"Douradoquara",
					"Durandé",
					"Elói Mendes",
					"Engenheiro Caldas",
					"Engenheiro Navarro",
					"Entre Folhas",
					"Entre Rios de Minas",
					"Ervália",
					"Esmeraldas",
					"Espera Feliz",
					"Espinosa",
					"Espírito Santo do Dourado",
					"Estiva",
					"Estrela Dalva",
					"Estrela do Indaiá",
					"Estrela do Sul",
					"Eugenópolis",
					"Ewbank da Câmara",
					"Extrema",
					"Fama",
					"Faria Lemos",
					"Felício dos Santos",
					"Felisburgo",
					"Felixlândia",
					"Fernandes Tourinho",
					"Ferros",
					"Fervedouro",
					"Florestal",
					"Formiga",
					"Formoso",
					"Fortaleza de Minas",
					"Fortuna de Minas",
					"Francisco Badaró",
					"Francisco Dumont",
					"Francisco Sá",
					"Franciscópolis",
					"Frei Gaspar",
					"Frei Inocêncio",
					"Frei Lagonegro",
					"Fronteira",
					"Fronteira dos Vales",
					"Fruta de Leite",
					"Frutal",
					"Funilândia",
					"Galiléia",
					"Gameleiras",
					"Glaucilândia",
					"Goiabeira",
					"Goianá",
					"Gonçalves",
					"Gonzaga",
					"Gouveia",
					"Governador Valadares",
					"Grão Mogol",
					"Grupiara",
					"Guanhães",
					"Guapé",
					"Guaraciaba",
					"Guaraciama",
					"Guaranésia",
					"Guarani",
					"Guarará",
					"Guarda-Mor",
					"Guaxupé",
					"Guidoval",
					"Guimarânia",
					"Guiricema",
					"Gurinhatã",
					"Heliodora",
					"Iapu",
					"Ibertioga",
					"Ibiá",
					"Ibiaí",
					"Ibiracatu",
					"Ibiraci",
					"Ibirité",
					"Ibitiúra de Minas",
					"Ibituruna",
					"Icaraí de Minas",
					"Igarapé",
					"Igaratinga",
					"Iguatama",
					"Ijaci",
					"Ilicínea",
					"Imbé de Minas",
					"Inconfidentes",
					"Indaiabira",
					"Indianópolis",
					"Ingaí",
					"Inhapim",
					"Inhaúma",
					"Inimutaba",
					"Ipaba",
					"Ipanema",
					"Ipatinga",
					"Ipiaçu",
					"Ipuiúna",
					"Iraí de Minas",
					"Itabira",
					"Itabirinha",
					"Itabirito",
					"Itacambira",
					"Itacarambi",
					"Itaguara",
					"Itaipé",
					"Itajubá",
					"Itamarandiba",
					"Itamarati de Minas",
					"Itambacuri",
					"Itambé do Mato Dentro",
					"Itamogi",
					"Itamonte",
					"Itanhandu",
					"Itanhomi",
					"Itaobim",
					"Itapagipe",
					"Itapecerica",
					"Itapeva",
					"Itatiaiuçu",
					"Itaú de Minas",
					"Itaúna",
					"Itaverava",
					"Itinga",
					"Itueta",
					"Ituiutaba",
					"Itumirim",
					"Iturama",
					"Itutinga",
					"Jaboticatubas",
					"Jacinto",
					"Jacuí",
					"Jacutinga",
					"Jaguaraçu",
					"Jaíba",
					"Jampruca",
					"Janaúba",
					"Januária",
					"Japaraíba",
					"Japonvar",
					"Jeceaba",
					"Jenipapo de Minas",
					"Jequeri",
					"Jequitaí",
					"Jequitibá",
					"Jequitinhonha",
					"Jesuânia",
					"Joaíma",
					"Joanésia",
					"João Monlevade",
					"João Pinheiro",
					"Joaquim Felício",
					"Jordânia",
					"José Gonçalves de Minas",
					"José Raydan",
					"Josenópolis",
					"Juatuba",
					"Juiz de Fora",
					"Juramento",
					"Juruaia",
					"Juvenília",
					"Ladainha",
					"Lagamar",
					"Lagoa da Prata",
					"Lagoa dos Patos",
					"Lagoa Dourada",
					"Lagoa Formosa",
					"Lagoa Grande",
					"Lagoa Santa",
					"Lajinha",
					"Lambari",
					"Lamim",
					"Laranjal",
					"Lassance",
					"Lavras",
					"Leandro Ferreira",
					"Leme do Prado",
					"Leopoldina",
					"Liberdade",
					"Lima Duarte",
					"Limeira do Oeste",
					"Lontra",
					"Luisburgo",
					"Luislândia",
					"Luminárias",
					"Luz",
					"Machacalis",
					"Machado",
					"Madre de Deus de Minas",
					"Malacacheta",
					"Mamonas",
					"Manga",
					"Manhuaçu",
					"Manhumirim",
					"Mantena",
					"Mar de Espanha",
					"Maravilhas",
					"Maria da Fé",
					"Mariana",
					"Marilac",
					"Mário Campos",
					"Maripá de Minas",
					"Marliéria",
					"Marmelópolis",
					"Martinho Campos",
					"Martins Soares",
					"Mata Verde",
					"Materlândia",
					"Mateus Leme",
					"Mathias Lobato",
					"Matias Barbosa",
					"Matias Cardoso",
					"Matipó",
					"Mato Verde",
					"Matozinhos",
					"Matutina",
					"Medeiros",
					"Medina",
					"Mendes Pimentel",
					"Mercês",
					"Mesquita",
					"Minas Novas",
					"Minduri",
					"Mirabela",
					"Miradouro",
					"Miraí",
					"Miravânia",
					"Moeda",
					"Moema",
					"Monjolos",
					"Monsenhor Paulo",
					"Montalvânia",
					"Monte Alegre de Minas",
					"Monte Azul",
					"Monte Belo",
					"Monte Carmelo",
					"Monte Formoso",
					"Monte Santo de Minas",
					"Monte Sião",
					"Montes Claros",
					"Montezuma",
					"Morada Nova de Minas",
					"Morro da Garça",
					"Morro do Pilar",
					"Munhoz",
					"Muriaé",
					"Mutum",
					"Muzambinho",
					"Nacip Raydan",
					"Nanuque",
					"Naque",
					"Natalândia",
					"Natércia",
					"Nazareno",
					"Nepomuceno",
					"Ninheira",
					"Nova Belém",
					"Nova Era",
					"Nova Lima",
					"Nova Módica",
					"Nova Ponte",
					"Nova Porteirinha",
					"Nova Resende",
					"Nova Serrana",
					"Nova União",
					"Novo Cruzeiro",
					"Novo Oriente de Minas",
					"Novorizonte",
					"Olaria",
					"Olhos-d'Água",
					"Olímpio Noronha",
					"Oliveira",
					"Oliveira Fortes",
					"Onça de Pitangui",
					"Oratórios",
					"Orizânia",
					"Ouro Branco",
					"Ouro Fino",
					"Ouro Preto",
					"Ouro Verde de Minas",
					"Padre Carvalho",
					"Padre Paraíso",
					"Pai Pedro",
					"Paineiras",
					"Pains",
					"Paiva",
					"Palma",
					"Palmópolis",
					"Papagaios",
					"Pará de Minas",
					"Paracatu",
					"Paraguaçu",
					"Paraisópolis",
					"Paraopeba",
					"Passa Quatro",
					"Passa Tempo",
					"Passa-Vinte",
					"Passabém",
					"Passos",
					"Patis",
					"Patos de Minas",
					"Patrocínio",
					"Patrocínio do Muriaé",
					"Paula Cândido",
					"Paulistas",
					"Pavão",
					"Peçanha",
					"Pedra Azul",
					"Pedra Bonita",
					"Pedra do Anta",
					"Pedra do Indaiá",
					"Pedra Dourada",
					"Pedralva",
					"Pedras de Maria da Cruz",
					"Pedrinópolis",
					"Pedro Leopoldo",
					"Pedro Teixeira",
					"Pequeri",
					"Pequi",
					"Perdigão",
					"Perdizes",
					"Perdões",
					"Periquito",
					"Pescador",
					"Piau",
					"Piedade de Caratinga",
					"Piedade de Ponte Nova",
					"Piedade do Rio Grande",
					"Piedade dos Gerais",
					"Pimenta",
					"Pingo-d'Água",
					"Pintópolis",
					"Piracema",
					"Pirajuba",
					"Piranga",
					"Piranguçu",
					"Piranguinho",
					"Pirapetinga",
					"Pirapora",
					"Piraúba",
					"Pitangui",
					"Piumhi",
					"Planura",
					"Poço Fundo",
					"Poços de Caldas",
					"Pocrane",
					"Pompéu",
					"Ponte Nova",
					"Ponto Chique",
					"Ponto dos Volantes",
					"Porteirinha",
					"Porto Firme",
					"Poté",
					"Pouso Alegre",
					"Pouso Alto",
					"Prados",
					"Prata",
					"Pratápolis",
					"Pratinha",
					"Presidente Bernardes",
					"Presidente Juscelino",
					"Presidente Kubitschek",
					"Presidente Olegário",
					"Prudente de Morais",
					"Quartel Geral",
					"Queluzito",
					"Raposos",
					"Raul Soares",
					"Recreio",
					"Reduto",
					"Resende Costa",
					"Resplendor",
					"Ressaquinha",
					"Riachinho",
					"Riacho dos Machados",
					"Ribeirão das Neves",
					"Ribeirão Vermelho",
					"Rio Acima",
					"Rio Casca",
					"Rio do Prado",
					"Rio Doce",
					"Rio Espera",
					"Rio Manso",
					"Rio Novo",
					"Rio Paranaíba",
					"Rio Pardo de Minas",
					"Rio Piracicaba",
					"Rio Pomba",
					"Rio Preto",
					"Rio Vermelho",
					"Ritápolis",
					"Rochedo de Minas",
					"Rodeiro",
					"Romaria",
					"Rosário da Limeira",
					"Rubelita",
					"Rubim",
					"Sabará",
					"Sabinópolis",
					"Sacramento",
					"Salinas",
					"Salto da Divisa",
					"Santa Bárbara",
					"Santa Bárbara do Leste",
					"Santa Bárbara do Monte Verde",
					"Santa Bárbara do Tugúrio",
					"Santa Cruz de Minas",
					"Santa Cruz de Salinas",
					"Santa Cruz do Escalvado",
					"Santa Efigênia de Minas",
					"Santa Fé de Minas",
					"Santa Helena de Minas",
					"Santa Juliana",
					"Santa Luzia",
					"Santa Margarida",
					"Santa Maria de Itabira",
					"Santa Maria do Salto",
					"Santa Maria do Suaçuí",
					"Santa Rita de Caldas",
					"Santa Rita de Ibitipoca",
					"Santa Rita de Jacutinga",
					"Santa Rita de Minas",
					"Santa Rita do Itueto",
					"Santa Rita do Sapucaí",
					"Santa Rosa da Serra",
					"Santa Vitória",
					"Santana da Vargem",
					"Santana de Cataguases",
					"Santana de Pirapama",
					"Santana do Deserto",
					"Santana do Garambéu",
					"Santana do Jacaré",
					"Santana do Manhuaçu",
					"Santana do Paraíso",
					"Santana do Riacho",
					"Santana dos Montes",
					"Santo Antônio do Amparo",
					"Santo Antônio do Aventureiro",
					"Santo Antônio do Grama",
					"Santo Antônio do Itambé",
					"Santo Antônio do Jacinto",
					"Santo Antônio do Monte",
					"Santo Antônio do Retiro",
					"Santo Antônio do Rio Abaixo",
					"Santo Hipólito",
					"Santos Dumont",
					"São Bento Abade",
					"São Brás do Suaçuí",
					"São Domingos das Dores",
					"São Domingos do Prata",
					"São Félix de Minas",
					"São Francisco",
					"São Francisco de Paula",
					"São Francisco de Sales",
					"São Francisco do Glória",
					"São Geraldo",
					"São Geraldo da Piedade",
					"São Geraldo do Baixio",
					"São Gonçalo do Abaeté",
					"São Gonçalo do Pará",
					"São Gonçalo do Rio Abaixo",
					"São Gonçalo do Rio Preto",
					"São Gonçalo do Sapucaí",
					"São Gotardo",
					"São João Batista do Glória",
					"São João da Lagoa",
					"São João da Mata",
					"São João da Ponte",
					"São João das Missões",
					"São João del Rei",
					"São João do Manhuaçu",
					"São João do Manteninha",
					"São João do Oriente",
					"São João do Pacuí",
					"São João do Paraíso",
					"São João Evangelista",
					"São João Nepomuceno",
					"São Joaquim de Bicas",
					"São José da Barra",
					"São José da Lapa",
					"São José da Safira",
					"São José da Varginha",
					"São José do Alegre",
					"São José do Divino",
					"São José do Goiabal",
					"São José do Jacuri",
					"São José do Mantimento",
					"São Lourenço",
					"São Miguel do Anta",
					"São Pedro da União",
					"São Pedro do Suaçuí",
					"São Pedro dos Ferros",
					"São Romão",
					"São Roque de Minas",
					"São Sebastião da Bela Vista",
					"São Sebastião da Vargem Alegre",
					"São Sebastião do Anta",
					"São Sebastião do Maranhão",
					"São Sebastião do Oeste",
					"São Sebastião do Paraíso",
					"São Sebastião do Rio Preto",
					"São Sebastião do Rio Verde",
					"São Thomé das Letras",
					"São Tiago",
					"São Tomás de Aquino",
					"São Vicente de Minas",
					"Sapucaí-Mirim",
					"Sardoá",
					"Sarzedo",
					"Sem-Peixe",
					"Senador Amaral",
					"Senador Cortes",
					"Senador Firmino",
					"Senador José Bento",
					"Senador Modestino Gonçalves",
					"Senhora de Oliveira",
					"Senhora do Porto",
					"Senhora dos Remédios",
					"Sericita",
					"Seritinga",
					"Serra Azul de Minas",
					"Serra da Saudade",
					"Serra do Salitre",
					"Serra dos Aimorés",
					"Serrania",
					"Serranópolis de Minas",
					"Serranos",
					"Serro",
					"Sete Lagoas",
					"Setubinha",
					"Silveirânia",
					"Silvianópolis",
					"Simão Pereira",
					"Simonésia",
					"Sobrália",
					"Soledade de Minas",
					"Tabuleiro",
					"Taiobeiras",
					"Taparuba",
					"Tapira",
					"Tapiraí",
					"Taquaraçu de Minas",
					"Tarumirim",
					"Teixeiras",
					"Teófilo Otoni",
					"Timóteo",
					"Tiradentes",
					"Tiros",
					"Tocantins",
					"Tocos do Moji",
					"Toledo",
					"Tombos",
					"Três Corações",
					"Três Marias",
					"Três Pontas",
					"Tumiritinga",
					"Tupaciguara",
					"Turmalina",
					"Turvolândia",
					"Ubá",
					"Ubaí",
					"Ubaporanga",
					"Uberaba",
					"Uberlândia",
					"Umburatiba",
					"Unaí",
					"União de Minas",
					"Uruana de Minas",
					"Urucânia",
					"Urucuia",
					"Vargem Alegre",
					"Vargem Bonita",
					"Vargem Grande do Rio Pardo",
					"Varginha",
					"Varjão de Minas",
					"Várzea da Palma",
					"Varzelândia",
					"Vazante",
					"Verdelândia",
					"Veredinha",
					"Veríssimo",
					"Vermelho Novo",
					"Vespasiano",
					"Viçosa",
					"Vieiras",
					"Virgem da Lapa",
					"Virgínia",
					"Virginópolis",
					"Virgolândia",
					"Visconde do Rio Branco",
					"Volta Grande",
					"Wenceslau Braz"
				],
				'normalized' => [
					"abadia dos dourados",
					"abaete",
					"abre campo",
					"acaiaca",
					"acucena",
					"agua boa",
					"agua comprida",
					"aguanil",
					"aguas formosas",
					"aguas vermelhas",
					"aimores",
					"aiuruoca",
					"alagoa",
					"albertina",
					"alem paraiba",
					"alfenas",
					"alfredo vasconcelos",
					"almenara",
					"alpercata",
					"alpinopolis",
					"alterosa",
					"alto caparao",
					"alto jequitiba",
					"alto rio doce",
					"alvarenga",
					"alvinopolis",
					"alvorada de minas",
					"amparo do serra",
					"andradas",
					"andrelandia",
					"angelandia",
					"antonio carlos",
					"antonio dias",
					"antonio prado de minas",
					"aracai",
					"aracitaba",
					"aracuai",
					"araguari",
					"arantina",
					"araponga",
					"arapora",
					"arapua",
					"araujos",
					"araxa",
					"arceburgo",
					"arcos",
					"areado",
					"argirita",
					"aricanduva",
					"arinos",
					"astolfo dutra",
					"ataleia",
					"augusto de lima",
					"baependi",
					"baldim",
					"bambui",
					"bandeira",
					"bandeira do sul",
					"barao de cocais",
					"barao de monte alto",
					"barbacena",
					"barra longa",
					"barroso",
					"bela vista de minas",
					"belmiro braga",
					"belo horizonte",
					"belo oriente",
					"belo vale",
					"berilo",
					"berizal",
					"bertopolis",
					"betim",
					"bias fortes",
					"bicas",
					"biquinhas",
					"boa esperanca",
					"bocaina de minas",
					"bocaiuva",
					"bom despacho",
					"bom jardim de minas",
					"bom jesus da penha",
					"bom jesus do amparo",
					"bom jesus do galho",
					"bom repouso",
					"bom sucesso",
					"bonfim",
					"bonfinopolis de minas",
					"bonito de minas",
					"borda da mata",
					"botelhos",
					"botumirim",
					"bras pires",
					"brasilandia de minas",
					"brasilia de minas",
					"brasopolis",
					"braunas",
					"brumadinho",
					"bueno brandao",
					"buenopolis",
					"bugre",
					"buritis",
					"buritizeiro",
					"cabeceira grande",
					"cabo verde",
					"cachoeira da prata",
					"cachoeira de minas",
					"cachoeira de pajeu",
					"cachoeira dourada",
					"caetanopolis",
					"caete",
					"caiana",
					"cajuri",
					"caldas",
					"camacho",
					"camanducaia",
					"cambui",
					"cambuquira",
					"campanario",
					"campanha",
					"campestre",
					"campina verde",
					"campo azul",
					"campo belo",
					"campo do meio",
					"campo florido",
					"campos altos",
					"campos gerais",
					"cana verde",
					"canaa",
					"canapolis",
					"candeias",
					"cantagalo",
					"caparao",
					"capela nova",
					"capelinha",
					"capetinga",
					"capim branco",
					"capinopolis",
					"capitao andrade",
					"capitao eneas",
					"capitolio",
					"caputira",
					"carai",
					"caranaiba",
					"carandai",
					"carangola",
					"caratinga",
					"carbonita",
					"careacu",
					"carlos chagas",
					"carmesia",
					"carmo da cachoeira",
					"carmo da mata",
					"carmo de minas",
					"carmo do cajuru",
					"carmo do paranaiba",
					"carmo do rio claro",
					"carmopolis de minas",
					"carneirinho",
					"carrancas",
					"carvalhopolis",
					"carvalhos",
					"casa grande",
					"cascalho rico",
					"cassia",
					"cataguases",
					"catas altas",
					"catas altas da noruega",
					"catuji",
					"catuti",
					"caxambu",
					"cedro do abaete",
					"central de minas",
					"centralina",
					"chacara",
					"chale",
					"chapada do norte",
					"chapada gaucha",
					"chiador",
					"cipotanea",
					"claraval",
					"claro dos pocões",
					"claudio",
					"coimbra",
					"coluna",
					"comendador gomes",
					"comercinho",
					"conceicao da aparecida",
					"conceicao da barra de minas",
					"conceicao das alagoas",
					"conceicao das pedras",
					"conceicao de ipanema",
					"conceicao do mato dentro",
					"conceicao do para",
					"conceicao do rio verde",
					"conceicao dos ouros",
					"conego marinho",
					"confins",
					"congonhal",
					"congonhas",
					"congonhas do norte",
					"conquista",
					"conselheiro lafaiete",
					"conselheiro pena",
					"consolacao",
					"contagem",
					"coqueiral",
					"coracao de jesus",
					"cordisburgo",
					"cordislandia",
					"corinto",
					"coroaci",
					"coromandel",
					"coronel fabriciano",
					"coronel murta",
					"coronel pacheco",
					"coronel xavier chaves",
					"corrego danta",
					"corrego do bom jesus",
					"corrego fundo",
					"corrego novo",
					"couto de magalhaes de minas",
					"crisolita",
					"cristais",
					"cristalia",
					"cristiano otoni",
					"cristina",
					"crucilandia",
					"cruzeiro da fortaleza",
					"cruzialia",
					"cuparaque",
					"curral de dentro",
					"curvelo",
					"datas",
					"delfim moreira",
					"delfinopolis",
					"delta",
					"descoberto",
					"desterro de entre rios",
					"desterro do melo",
					"diamantina",
					"diogo de vasconcelos",
					"dionisio",
					"divinesia",
					"divino",
					"divino das laranjeiras",
					"divinolandia de minas",
					"divinopolis",
					"divisa alegre",
					"divisa nova",
					"divisopolis",
					"dom bosco",
					"dom cavati",
					"dom joaquim",
					"dom silverio",
					"dom vicoso",
					"dona euzebia",
					"dores de campos",
					"dores de guanhaes",
					"dores do indaia",
					"dores do turvo",
					"doresopolis",
					"douradoquara",
					"durande",
					"eloi mendes",
					"engenheiro caldas",
					"engenheiro navarro",
					"entre folhas",
					"entre rios de minas",
					"ervalia",
					"esmeraldas",
					"espera feliz",
					"espinosa",
					"espirito santo do dourado",
					"estiva",
					"estrela dalva",
					"estrela do indaia",
					"estrela do sul",
					"eugenopolis",
					"ewbank da camara",
					"extrema",
					"fama",
					"faria lemos",
					"felicio dos santos",
					"felisburgo",
					"felixlandia",
					"fernandes tourinho",
					"ferros",
					"fervedouro",
					"florestal",
					"formiga",
					"formoso",
					"fortaleza de minas",
					"fortuna de minas",
					"francisco badaro",
					"francisco dumont",
					"francisco sa",
					"franciscopolis",
					"frei gaspar",
					"frei inocencio",
					"frei lagonegro",
					"fronteira",
					"fronteira dos vales",
					"fruta de leite",
					"frutal",
					"funilandia",
					"galileia",
					"gameleiras",
					"glaucilandia",
					"goiabeira",
					"goiana",
					"goncalves",
					"gonzaga",
					"gouveia",
					"governador valadares",
					"grao mogol",
					"grupiara",
					"guanhaes",
					"guape",
					"guaraciaba",
					"guaraciama",
					"guaranesia",
					"guarani",
					"guarara",
					"guarda-mor",
					"guaxupe",
					"guidoval",
					"guimarania",
					"guiricema",
					"gurinhata",
					"heliodora",
					"iapu",
					"ibertioga",
					"ibia",
					"ibiai",
					"ibiraçatu",
					"ibiraci",
					"ibirite",
					"ibitiura de minas",
					"ibituruna",
					"icarai de minas",
					"igarape",
					"igaratinga",
					"iguatama",
					"ijaci",
					"ilicinea",
					"imbe de minas",
					"inconfidentes",
					"indaiabira",
					"indianopolis",
					"ingai",
					"inhapim",
					"inhauma",
					"inimutaba",
					"ipaba",
					"ipanema",
					"ipatinga",
					"ipiacu",
					"ipuiuna",
					"irai de minas",
					"itabira",
					"itabirinha",
					"itabirito",
					"itacambira",
					"itacarambi",
					"itaguara",
					"itaipe",
					"itajuba",
					"itamarandiba",
					"itamarati de minas",
					"itambacuri",
					"itambe do mato dentro",
					"itamogi",
					"itamonte",
					"itanhandu",
					"itanhomi",
					"itaobim",
					"itapagipe",
					"itapecerica",
					"itapeva",
					"itatiaiuçu",
					"itau de minas",
					"itauna",
					"itaverava",
					"itinga",
					"itueta",
					"ituiutaba",
					"itumirim",
					"iturama",
					"itutinga",
					"jaboticatubas",
					"jacinto",
					"jacui",
					"jacutinga",
					"jaguaraçu",
					"jaiba",
					"jampruca",
					"janauba",
					"januaria",
					"japaraiba",
					"japonvar",
					"jeceaba",
					"jenipapo de minas",
					"jequeri",
					"jequitai",
					"jequitiba",
					"jequitinhonha",
					"jesuania",
					"joaima",
					"joanesia",
					"joao monlevade",
					"joao pinheiro",
					"joaquim felicio",
					"jordania",
					"jose goncalves de minas",
					"jose raydan",
					"josenopolis",
					"juatuba",
					"juiz de fora",
					"juramento",
					"juruaia",
					"juvenilia",
					"ladainha",
					"lagamar",
					"lagoa da prata",
					"lagoa dos patos",
					"lagoa dourada",
					"lagoa formosa",
					"lagoa grande",
					"lagoa santa",
					"lajinha",
					"lambari",
					"lamim",
					"laranjal",
					"lassance",
					"lavras",
					"leandro ferreira",
					"leme do prado",
					"leopoldina",
					"liberdade",
					"lima duarte",
					"limeira do oeste",
					"lontra",
					"luisburgo",
					"luislandia",
					"luminarias",
					"luz",
					"machacalis",
					"machado",
					"madre de deus de minas",
					"malacacheta",
					"mamonas",
					"manga",
					"manhuacu",
					"manhumirim",
					"mantena",
					"mar de espanha",
					"maravilhas",
					"maria da fe",
					"mariana",
					"marilac",
					"mario campos",
					"maripa de minas",
					"marlieria",
					"marmelopolis",
					"martinho campos",
					"martins soares",
					"mata verde",
					"materlandia",
					"mateus leme",
					"mathias lobato",
					"matias barbosa",
					"matias cardoso",
					"matipo",
					"mato verde",
					"matozinhos",
					"matutina",
					"medeiros",
					"medina",
					"mendes pimentel",
					"merces",
					"mesquita",
					"minas novas",
					"minduri",
					"mirabela",
					"miradouro",
					"mirai",
					"miravania",
					"moeda",
					"moema",
					"monjolos",
					"monsenhor paulo",
					"montalvania",
					"monte alegre de minas",
					"monte azul",
					"monte belo",
					"monte carmelo",
					"monte formoso",
					"monte santo de minas",
					"monte sia",
					"montes claros",
					"montezuma",
					"morada nova de minas",
					"morro da garca",
					"morro do pilar",
					"munhoz",
					"muriae",
					"mutum",
					"muzambinho",
					"nacip raydan",
					"nanuque",
					"naque",
					"natalandia",
					"natercia",
					"nazareno",
					"nepomuceno",
					"ninheira",
					"nova belem",
					"nova era",
					"nova lima",
					"nova modica",
					"nova ponte",
					"nova porteirinha",
					"nova resende",
					"nova serrana",
					"nova uniao",
					"novo cruzeiro",
					"novo oriente de minas",
					"novorizonte",
					"olaria",
					"olhos-d'agua",
					"olimpio noronha",
					"oliveira",
					"oliveira fortes",
					"onca de pitangui",
					"oratorios",
					"orizania",
					"ouro branco",
					"ouro fino",
					"ouro preto",
					"ouro verde de minas",
					"padre carvalho",
					"padre paraiso",
					"pai pedro",
					"paineiras",
					"pains",
					"paiva",
					"palma",
					"palmopolis",
					"papagaios",
					"para de minas",
					"paracatu",
					"paraguacu",
					"paraisopolis",
					"paraopeba",
					"passa quatro",
					"passa tempo",
					"passa-vinte",
					"passabem",
					"passos",
					"patis",
					"patos de minas",
					"patrocinio",
					"patrocinio do muriae",
					"paula candido",
					"paulistas",
					"pavao",
					"pecanha",
					"pedra azul",
					"pedra bonita",
					"pedra do anta",
					"pedra do indaia",
					"pedra dourada",
					"pedralva",
					"pedras de maria da cruz",
					"pedrinopolis",
					"pedro leopoldo",
					"pedro teixeira",
					"pequeri",
					"pequi",
					"perdigao",
					"perdizes",
					"perdoes",
					"periquito",
					"pescador",
					"piau",
					"piedade de caratinga",
					"piedade de ponte nova",
					"piedade do rio grande",
					"piedade dos gerais",
					"pimenta",
					"pingo-d'agua",
					"pintopolis",
					"piracema",
					"pirajuba",
					"piranga",
					"pirangucu",
					"piranguinho",
					"pirapetinga",
					"pirapora",
					"pirauba",
					"pitangui",
					"piumhi",
					"planura",
					"poco fundo",
					"pocos de caldas",
					"pocrane",
					"pompeu",
					"ponte nova",
					"ponto chique",
					"ponto dos volantes",
					"porteirinha",
					"porto firme",
					"pote",
					"pouso alegre",
					"pouso alto",
					"prados",
					"prata",
					"pratapolis",
					"pratinha",
					"presidente bernardes",
					"presidente juscelino",
					"presidente kubitschek",
					"presidente olegario",
					"prudente de morais",
					"quartel geral",
					"queluzito",
					"raposos",
					"raul soares",
					"recreio",
					"reduto",
					"resende costa",
					"resplendor",
					"ressaquinha",
					"riachinho",
					"riacho dos machados",
					"ribeirao das neves",
					"ribeirao vermelho",
					"rio acima",
					"rio casca",
					"rio do prado",
					"rio doce",
					"rio espera",
					"rio manso",
					"rio novo",
					"rio paranaiba",
					"rio pardo de minas",
					"rio piracicaba",
					"rio pomba",
					"rio preto",
					"rio vermelho",
					"ritapolis",
					"rochedo de minas",
					"rodeiro",
					"romaria",
					"rosario da limeira",
					"rubelita",
					"rubim",
					"sabara",
					"sabinopolis",
					"sacramento",
					"salinas",
					"salto da divisa",
					"santa barbara",
					"santa barbara do leste",
					"santa barbara do monte verde",
					"santa barbara do tugurio",
					"santa cruz de minas",
					"santa cruz de salinas",
					"santa cruz do escalvado",
					"santa efigenia de minas",
					"santa fe de minas",
					"santa helena de minas",
					"santa juliana",
					"santa luzia",
					"santa margarida",
					"santa maria de itabira",
					"santa maria do salto",
					"santa maria do suaçuí",
					"santa rita de caldas",
					"santa rita de ibitipoca",
					"santa rita de jacutinga",
					"santa rita de minas",
					"santa rita do itueto",
					"santa rita do sapucai",
					"santa rosa da serra",
					"santa vitoria",
					"santana da vargem",
					"santana de cataguases",
					"santana de pirapama",
					"santana do deserto",
					"santana do garambéu",
					"santana do jacare",
					"santana do manhuacu",
					"santana do paraiso",
					"santana do riacho",
					"santana dos montes",
					"santo antonio do amparo",
					"santo antonio do aventureiro",
					"santo antonio do grama",
					"santo antonio do itambe",
					"santo antonio do jacinto",
					"santo antonio do monte",
					"santo antonio do retiro",
					"santo antonio do rio abaixo",
					"santo hipolito",
					"santos dumont",
					"sao bento abade",
					"sao bras do suacui",
					"sao domingos das dores",
					"sao domingos do prata",
					"sao felix de minas",
					"sao francisco",
					"sao francisco de paula",
					"sao francisco de sales",
					"sao francisco do gloria",
					"sao geraldo",
					"sao geraldo da piedade",
					"sao geraldo do baixio",
					"sao goncalo do abaete",
					"sao goncalo do para",
					"sao goncalo do rio abaixo",
					"sao goncalo do rio preto",
					"sao goncalo do sapucai",
					"sao gotardo",
					"sao joao batista do gloria",
					"sao joao da lagoa",
					"sao joao da mata",
					"sao joao da ponte",
					"sao joao das missoes",
					"sao joao del rei",
					"sao joao do manhuacu",
					"sao joao do manteninha",
					"sao joao do oriente",
					"sao joao do pacui",
					"sao joao do paraiso",
					"sao joao evangelista",
					"sao joao nepomuceno",
					"sao joaquim de bicas",
					"sao jose da barra",
					"sao jose da lapa",
					"sao jose da safira",
					"sao jose da varginha",
					"sao jose do alegre",
					"sao jose do divino",
					"sao jose do goiabal",
					"sao jose do jacuri",
					"sao jose do mantimento",
					"sao lourenco",
					"sao miguel do anta",
					"sao pedro da uniao",
					"sao pedro do suacui",
					"sao pedro dos ferros",
					"sao romao",
					"sao roque de minas",
					"sao sebastiao da bela vista",
					"sao sebastiao da vargem alegre",
					"sao sebastiao do anta",
					"sao sebastiao do maranhao",
					"sao sebastiao do oeste",
					"sao sebastiao do paraiso",
					"sao sebastiao do rio preto",
					"sao sebastiao do rio verde",
					"sao thome das letras",
					"sao tiago",
					"sao tomas de aquino",
					"sao vicente de minas",
					"sapucai-mirim",
					"sardoa",
					"sarzedo",
					"sem-peixe",
					"senador amaral",
					"senador cortes",
					"senador firmino",
					"senador jose bento",
					"senador modestino goncalves",
					"senhora de oliveira",
					"senhora do porto",
					"senhora dos remedios",
					"sericita",
					"seritinga",
					"serra azul de minas",
					"serra da saudade",
					"serra do salitre",
					"serra dos aimores",
					"serrania",
					"serranopolis de minas",
					"serranos",
					"serro",
					"sete lagoas",
					"setubinha",
					"silveirania",
					"silvianopolis",
					"simao pereira",
					"simonesia",
					"sobralia",
					"soledade de minas",
					"tabuleiro",
					"taiobeiras",
					"taparuba",
					"tapira",
					"tapirai",
					"taquaraçu de minas",
					"tarumirim",
					"teixeiras",
					"teofilo otoni",
					"timoteo",
					"tiradentes",
					"tiros",
					"tocantins",
					"tocos do moji",
					"toledo",
					"tombos",
					"tres coracoes",
					"tres marias",
					"tres pontas",
					"tumiritinga",
					"tupaciguara",
					"turmalina",
					"turvolandia",
					"uba",
					"ubai",
					"ubaporanga",
					"uberaba",
					"uberlandia",
					"umburatiba",
					"unai",
					"uniao de minas",
					"uruana de minas",
					"urucania",
					"urucuia",
					"vargem alegre",
					"vargem bonita",
					"vargem grande do rio pardo",
					"varginha",
					"varjao de minas",
					"varzea da palma",
					"varzelandia",
					"vazante",
					"verdelandia",
					"veredinha",
					"verissimo",
					"vermelho novo",
					"vespasiano",
					"vicosa",
					"vieiras",
					"virgem da lapa",
					"virginia",
					"virginopolis",
					"virgolandia",
					"visconde do rio branco",
					"volta grande",
					"wenceslau braz"
				],
			],
		],
	];

	protected const GEO_LANG_ORDER = [
		'pt-br',
		'en',
		'es',
		'pt',
		'ar',
		'da',
		'de',
		'fr',
		'he',
		'it',
		'ja',
		'nl',
		'pl',
		'ru',
		'tr',
		'zh',
	];

	/**
	 * Tokens de rótulos (labels) encontrados comumente em endereços.
	 * Objetivo: detectar padrões como "State: CA", "Estado SP", "Cidade - Lisboa", etc.
	 *
	 * Ordem de tentativa exigida:
	 *  1) pt-br
	 *  2) en
	 *  3) es
	 *  ... demais
	 *
	 * @var array<string, array{country: string[], state: string[], city: string[]}>
	 */
	protected const GEO_LABEL_TOKENS_BY_LANG = [
		'pt-br' => [
			'country' => ['pais', 'país', 'nacao', 'nação', 'pais/regiao', 'país/região'],
			'state'   => ['estado', 'uf', 'unidade federativa', 'provincia', 'província', 'departamento', 'regiao', 'região'],
			'city'    => ['cidade', 'municipio', 'município', 'localidade', 'cidade/município'],
		],
		'en' => [
			'country' => ['country', 'nation'],
			'state'   => ['state', 'province', 'region', 'department', 'prefecture', 'county'],
			'city'    => ['city', 'town', 'municipality', 'locality'],
		],
		'es' => [
			'country' => ['pais', 'país', 'nacion', 'nación'],
			'state'   => ['estado', 'provincia', 'província', 'provincia/estado', 'departamento', 'region', 'región'],
			'city'    => ['ciudad', 'municipio', 'localidad'],
		],

		// Demais idiomas solicitados (cobertura inicial; você pode expandir conforme achar dados reais no seu dataset)
		'pt' => [
			'country' => ['pais', 'país', 'nação'],
			'state'   => ['estado', 'distrito', 'provincia', 'província', 'regiao', 'região'],
			'city'    => ['cidade', 'concelho', 'municipio', 'município', 'localidade'],
		],
		'ar' => [
			'country' => ['بلد', 'الدولة', 'دولة'],
			'state'   => ['ولاية', 'محافظة', 'إقليم'],
			'city'    => ['مدينة', 'بلدة', 'محلية'],
		],
		'da' => [
			'country' => ['land'],
			'state'   => ['stat', 'region', 'provins'],
			'city'    => ['by', 'kommune', 'lokalitet'],
		],
		'de' => [
			'country' => ['land', 'staat'],
			'state'   => ['bundesland', 'land', 'region', 'provinz'],
			'city'    => ['stadt', 'ort', 'gemeinde'],
		],
		'fr' => [
			'country' => ['pays'],
			'state'   => ['etat', 'état', 'region', 'région', 'province', 'département', 'departement'],
			'city'    => ['ville', 'commune', 'localité', 'localite'],
		],
		'he' => [
			'country' => ['מדינה'],
			'state'   => ['מחוז', 'אזור'],
			'city'    => ['עיר', 'יישוב', 'מועצה'],
		],
		'it' => [
			'country' => ['paese', 'nazione'],
			'state'   => ['stato', 'regione', 'provincia'],
			'city'    => ['citta', 'città', 'comune', 'localita', 'località'],
		],
		'ja' => [
			'country' => ['国'],
			'state'   => ['州', '県', '都', '府', '道', '地方'],
			'city'    => ['市', '町', '村', '区'],
		],
		'nl' => [
			'country' => ['land'],
			'state'   => ['staat', 'provincie', 'regio'],
			'city'    => ['stad', 'gemeente', 'plaats'],
		],
		'pl' => [
			'country' => ['kraj', 'panstwo', 'państwo'],
			'state'   => ['wojewodztwo', 'województwo', 'region', 'prowincja'],
			'city'    => ['miasto', 'gmina', 'miejscowosc', 'miejscowość'],
		],
		'ru' => [
			'country' => ['страна', 'государство'],
			'state'   => ['область', 'край', 'республика', 'регион', 'округ'],
			'city'    => ['город', 'населенный пункт', 'населённый пункт'],
		],
		'tr' => [
			'country' => ['ulke', 'ülke'],
			'state'   => ['eyalet', 'il', 'bolge', 'bölge', 'vilayet'],
			'city'    => ['sehir', 'şehir', 'ilce', 'ilçe', 'belediye'],
		],
		'zh' => [
			'country' => ['国家', '國家'],
			'state'   => ['省', '州', '自治区', '自治區', '直辖市', '直轄市', '地区', '地區'],
			'city'    => ['市', '县', '縣', '区', '區', '城', '城区', '城區'],
		],
	];

	/** @var string[] */
	protected const GEO_LABEL_LANG_ORDER = [
		'pt-br',
		'en',
		'es',
		'pt',
		'fr',
		'it',
		'ar',
		'da',
		'de',
		'he',
		'ja',
		'nl',
		'pl',
		'ru',
		'tr',
		'zh',
	];

	protected static function bootUsesCountryRegions(): void
	{
		// todo too heavy for testing, use only for production
		// static::saving(function (Model $model) {
		// 	$sets = [
		// 		['country', 'state', 'city', 'zip', 'address'],
		// 		[BC::COL_SHIP_CTR, BC::COL_SHIP_ST, BC::COL_SHIP_CTY, BC::COL_SHIP_ZIP, BC::COL_SHIP_ADR],
		// 		[BC::COL_BL_CTR, BC::COL_BL_ST, BC::COL_BL_CTY, BC::COL_BL_ZIP, BC::COL_BL_ADR],
		// 	];

		// 	foreach ($sets as $cols) {
		// 		try {
		// 			if (!is_array($cols) || count($cols) < 2) continue;
		// 			if (!method_exists($model, 'applyCountryRegionNormalization')) continue;
		// 			$model->applyCountryRegionNormalization($cols);
		// 		} catch (\Throwable $e) {
		// 			Log::warning("[" . self::class . "]: " . static::class . " failed to normalize geo columns set", [
		// 				'cols' => $cols,
		// 				'message' => $e->getMessage(),
		// 				'file' => $e->getFile(),
		// 				'line' => $e->getLine(),
		// 			]);
		// 		}
		// 	}
		// });
	}

	public function getCountriesConstraintAttribute(): ?array
	{
		$table = $this->getTable();

		$sets = [
			['countries', null],
			[BC::COL_SHIP_CTR, 'single_country'],
			[BC::COL_BL_CTR, 'single_country'],
		];

		$out = [];

		foreach ($sets as $set) {
			try {
				$col = $set[0] ?? null;
				$mode = $set[1] ?? null;

				if (!is_string($col) || $col === '' || !Schema::hasColumn($table, $col)) continue;

				$val = $this->getAttribute($col);

				if ($mode === 'single_country') {
					if (!is_scalar($val)) continue;
					$s = trim((string) $val);
					if ($s === '') continue;
					$list = [$s];
				} else {
					$list = $this->normalizeStringList($val);
					if (!$list) continue;
				}

				$codes = $this->countryCodesFromMixedList($list);
				if (!$codes) continue;

				foreach ($codes as $cc) {
					$cc = strtoupper(trim((string) $cc));
					if ($cc !== '') $out[$cc] = true;
				}
			} catch (\Throwable $e) {
				Log::warning("[" . self::class . "]: failed to resolve countries constraint from column", [
					'column' => $set[0] ?? null,
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			}
		}

		if (!$out) return null;

		$codes = array_keys($out);
		sort($codes);
		return $codes ?: null;
	}

	public function getStatesConstraintAttribute(): ?array
	{
		$table = $this->getTable();

		$rawMap = null;
		$shipPair = null;
		$billPair = null;

		try {
			if (Schema::hasColumn($table, 'states'))
				$rawMap = $this->normalizeStatesMap($this->getAttribute('states'));
		} catch (\Throwable $e) {
			Log::warning("[" . self::class . "]: failed to normalize raw states map", [
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			$rawMap = null;
		}

		try {
			if (Schema::hasColumn($table, BC::COL_SHIP_CTR) && Schema::hasColumn($table, BC::COL_SHIP_ST)) {
				$ccRaw = $this->getAttribute(BC::COL_SHIP_CTR);
				$stRaw = $this->getAttribute(BC::COL_SHIP_ST);
				$cc = is_scalar($ccRaw) ? $this->normalizeCountryToCode((string) $ccRaw) : null;
				$st = is_scalar($stRaw) ? trim((string) $stRaw) : '';
				if ($cc !== null && $st !== '') $shipPair = ['country' => $cc, 'state' => $st];
			}
		} catch (\Throwable $e) {
			Log::warning("[" . self::class . "]: failed to read shipping geo pair", [
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			$shipPair = null;
		}

		try {
			if (Schema::hasColumn($table, BC::COL_BL_CTR) && Schema::hasColumn($table, BC::COL_BL_ST)) {
				$ccRaw = $this->getAttribute(BC::COL_BL_CTR);
				$stRaw = $this->getAttribute(BC::COL_BL_ST);
				$cc = is_scalar($ccRaw) ? $this->normalizeCountryToCode((string) $ccRaw) : null;
				$st = is_scalar($stRaw) ? trim((string) $stRaw) : '';
				if ($cc !== null && $st !== '') $billPair = ['country' => $cc, 'state' => $st];
			}
		} catch (\Throwable $e) {
			Log::warning("[" . self::class . "]: failed to read billing geo pair", [
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			$billPair = null;
		}

		$hasAny = is_array($rawMap) || $shipPair !== null || $billPair !== null;
		if (!$hasAny) return null;

		$merged = is_array($rawMap) ? $rawMap : [];

		foreach ([$shipPair, $billPair] as $pair) {
			if (!$pair) continue;
			$cc = $pair['country'] ?? null;
			$st = $pair['state'] ?? null;
			if (!is_string($cc) || trim($cc) === '' || !is_string($st) || trim($st) === '') continue;
			if (!isset($merged[$cc]) || !is_array($merged[$cc])) $merged[$cc] = [];
			$merged[$cc][] = $st;
		}

		try {
			$countries = $this->getCountriesConstraintAttribute();
			$hasCountries = $countries !== null;
			$out = $this->normalizeAllowedStatesByCountry($merged, $countries ?? [], $hasCountries);
			return $out ?: null;
		} catch (\Throwable $e) {
			Log::warning("[" . self::class . "]: failed to normalize allowed states by country", [
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return null;
		}
	}

	protected function applyCountryRegionNormalization(array $cols): void
	{
		$table = $this->getTable();

		[$countryCol, $stateCol, $cityCol, $zipCol, $addressCol] = array_pad($cols, 5, null);

		if (!is_string($countryCol) || $countryCol === '') return;

		try {
			if (!Schema::hasColumn($table, $countryCol)) return;
		} catch (\Throwable) {
			return;
		}

		try {
			$rawCountry = $this->getAttribute($countryCol);
			if (!CountryName::IsIsoCoded((string) $rawCountry))
				$this->setAttribute($countryCol, CountryName::getIsoCode($rawCountry));
		} catch (\Throwable) {
		}

		$country = null;

		try {
			$country = (string) $this->getAttribute($countryCol);
		} catch (\Throwable) {
			$country = null;
		}

		if (!is_string($country) || !CountryName::IsIsoCoded($country)) return;

		$stateOk = true;

		if (is_string($stateCol) && $stateCol !== '') {
			try {
				if (Schema::hasColumn($table, $stateCol)) {
					$stateEnumClass = null;
					try {
						$stateEnumClass = $this->stateEnumClassForCountry($country);
					} catch (\Throwable) {
						$stateEnumClass = null;
					}

					$stateValue = null;
					try {
						$stateValue = $this->getAttribute($stateCol);
					} catch (\Throwable) {
						$stateValue = null;
					}

					$stateStr = is_scalar($stateValue) ? trim((string) $stateValue) : '';

					if ($stateStr === '') {
						$this->setAttribute($stateCol, null);
					} elseif (is_string($stateEnumClass) && $stateEnumClass !== '' && method_exists($stateEnumClass, 'normalize')) {
						try {
							$stateEnum = $stateEnumClass::normalize($stateStr);
							if ($stateEnum instanceof $stateEnumClass)
								$this->setAttribute($stateCol, $stateEnum instanceof \BackedEnum ? $stateEnum->value : $stateEnum);
							else
								$this->setAttribute($stateCol, null);
						} catch (\Throwable) {
							$this->setAttribute($stateCol, null);
						}
					} else {
						$this->setAttribute($stateCol, $stateStr !== '' ? $stateStr : null);
					}
				}
			} catch (\Throwable) {
				$stateOk = false;
			}
		}

		if (!is_string($cityCol) || $cityCol === '') return;

		try {
			if (!Schema::hasColumn($table, $cityCol)) return;
		} catch (\Throwable) {
			return;
		}

		$city = null;

		try {
			$city = $this->getAttribute($cityCol);
		} catch (\Throwable) {
			$city = null;
		}

		if (!is_string($city) || trim($city) === '') return;

		$replacedCity = trim(str_replace([':', '—', '–', '_', '|', '/', '\\', ',', ';', '"'], ' ', $city));
		$lowerCasedCity = strtolower($replacedCity);

		try {
			$this->setAttribute($cityCol, $replacedCity);
		} catch (\Throwable) {
			return;
		}

		if ($country !== 'BR') return;
		if (!$stateOk) return;
		if (!defined('static::CITIES_BY_STATE') || !is_array(static::CITIES_BY_STATE)) return;

		$normalizedBrState = null;

		try {
			$normalizedBrState = BrazilState::normalize((string) $this->getAttribute($stateCol));
		} catch (\Throwable) {
			$normalizedBrState = null;
		}

		if (!is_string($normalizedBrState) || !in_array($normalizedBrState, [BrazilState::RJ->value, BrazilState::SP->value, BrazilState::MG->value], true)) return;

		$stateRef = null;

		try {
			$stateRef = static::CITIES_BY_STATE['BR'][$normalizedBrState] ?? null;
		} catch (\Throwable) {
			$stateRef = null;
		}

		if (!is_array($stateRef)) return;

		$isValid = (is_array($stateRef['common'] ?? null) && in_array($replacedCity, $stateRef['common'], true))
			|| (is_array($stateRef['normalized'] ?? null) && in_array($lowerCasedCity, $stateRef['normalized'], true));

		if (!$isValid) $this->setAttribute($cityCol, null);
	}

	protected function stateEnumClassForCountry(string $countryCode): ?string
	{
		$cc = strtoupper(trim($countryCode));
		return static::STATE_ENUMS[$cc] ?? null;
	}

	protected function normalizeCountryToCode(?string $value): ?string
	{
		$v = trim((string) $value);
		if ($v === '') return null;
		$vv = strtolower($v);
		$enum = CountryName::normalize($vv);
		if ($enum instanceof CountryName) {
			return match ($enum) {
				CountryName::Brazil        => 'BR',
				CountryName::UnitedStates  => 'US',
				CountryName::Canada        => 'CA',
				CountryName::UnitedKingdom => 'GB',
				CountryName::Germany       => 'DE',
				CountryName::France        => 'FR',
				CountryName::Spain         => 'ES',
				CountryName::Portugal      => 'PT',
				CountryName::Italy         => 'IT',
				CountryName::Argentina     => 'AR',
				CountryName::Chile         => 'CL',
				CountryName::Mexico        => 'MX',
				CountryName::Japan         => 'JP',
				CountryName::China         => 'CN',
				CountryName::India         => 'IN',
				CountryName::Australia     => 'AU',
				CountryName::SouthAfrica   => 'ZA',
				CountryName::Denmark       => 'DK',
				CountryName::Netherlands   => 'NL',
				CountryName::Poland        => 'PL',
				CountryName::SaudiArabia   => 'SA',
				CountryName::Turkey        => 'TR',
				CountryName::Israel        => 'IL',
				CountryName::Russia        => 'RU',
				CountryName::Switzerland   => 'CH',
				CountryName::Belgium       => 'BE',
				CountryName::Austria       => 'AT',
				CountryName::Taiwan        => 'TW',
				CountryName::Colombia      => 'CO',
				CountryName::Peru          => 'PE',
				CountryName::Venezuela     => 'VE',
				CountryName::Norway        => 'NO',
				CountryName::Sweden        => 'SE',
				CountryName::Finland       => 'FI',
				CountryName::Greece        => 'GR',
				CountryName::CzechRepublic => 'CZ',
				CountryName::Hungary       => 'HU',
				CountryName::Romania       => 'RO',
				CountryName::Bolivia       => 'BO',
				CountryName::Ecuador       => 'EC',
				CountryName::Guyana        => 'GY',
				CountryName::Paraguay      => 'PY',
				CountryName::Suriname      => 'SR',
				CountryName::Uruguay       => 'UY',
				CountryName::FrenchGuiana  => 'GF',
			};
		}
		$ascii = strtoupper(Str::ascii($v));
		if (preg_match('/^[A-Z]{2,3}$/', $ascii)) {
			return match ($ascii) {
				'BRA', 'BR' => 'BR',
				'USA', 'US' => 'US',
				'CAN', 'CA' => 'CA',
				'GBR', 'GB', 'UK' => 'GB',
				'DEU', 'DE' => 'DE',
				'FRA', 'FR' => 'FR',
				'ESP', 'ES' => 'ES',
				'PRT', 'PT' => 'PT',
				'ITA', 'IT' => 'IT',
				'ARG', 'AR' => 'AR',
				'CHL', 'CL' => 'CL',
				'MEX', 'MX' => 'MX',
				'JPN', 'JP' => 'JP',
				'CHN', 'CN' => 'CN',
				'IND', 'IN' => 'IN',
				'AUS', 'AU' => 'AU',
				'ZAF', 'ZA' => 'ZA',
				'DNK', 'DK' => 'DK',
				'NLD', 'NL' => 'NL',
				'POL', 'PL' => 'PL',
				'SAU', 'SA' => 'SA',
				'TUR', 'TR' => 'TR',
				'ISR', 'IL' => 'IL',
				'RUS', 'RU' => 'RU',
				'CHE', 'CH' => 'CH',
				'BEL', 'BE' => 'BE',
				'AUT', 'AT' => 'AT',
				'TWN', 'TW' => 'TW',
				'COL', 'CO' => 'CO',
				'PER', 'PE' => 'PE',
				'VEN', 'VE' => 'VE',
				'NOR', 'NO' => 'NO',
				'SWE', 'SE' => 'SE',
				'FIN', 'FI' => 'FI',
				'GRC', 'GR' => 'GR',
				'CZE', 'CZ' => 'CZ',
				'HUN', 'HU' => 'HU',
				'ROU', 'RO' => 'RO',
				'BOL', 'BO' => 'BO',
				'ECU', 'EC' => 'EC',
				'GUY', 'GY' => 'GY',
				'PRY', 'PY' => 'PY',
				'SUR', 'SR' => 'SR',
				'URY', 'UY' => 'UY',
				'GUF', 'GF' => 'GF',
				default => strlen($ascii) === 2 ? $ascii : null,
			};
		}
		$tokens = strtoupper(Str::ascii($v));
		return match ($tokens) {
			'BRASIL', 'BRAZIL' => 'BR',
			'UNITED STATES', 'UNITED STATES OF AMERICA', 'ESTADOS UNIDOS' => 'US',
			'UNITED KINGDOM', 'GREAT BRITAIN', 'REINO UNIDO' => 'GB',
			'ARGENTINA' => 'AR',
			'BOLIVIA' => 'BO',
			'CHILE' => 'CL',
			'ECUADOR', 'EQUADOR' => 'EC',
			'PARAGUAY' => 'PY',
			'PERU', 'PERÚ' => 'PE',
			'URUGUAY' => 'UY',
			'COLOMBIA' => 'CO',
			'GUYANA' => 'GY',
			default => null,
		};
	}

	protected function normalizeStateForCountry(?string $value, string $countryCode, bool $preferRawIfNoEnum): ?string
	{
		$v = trim((string) $value);
		if ($v === '') return null;
		$cc = strtoupper(trim($countryCode));
		$cls = $this->stateEnumClassForCountry($cc);
		if (is_string($cls) && $cls !== '' && method_exists($cls, 'normalize')) {
			try {
				$e = $cls::normalize($v);
				if ($e instanceof \BackedEnum) {
					return (string) $e->value;
				}
			} catch (\Throwable $t) {
				//
			}
		}
		if ($preferRawIfNoEnum) {
			$raw = strtoupper(Str::ascii($v));
			return $raw !== '' ? $raw : null;
		}
		return null;
	}

	protected function detectStateFromAddress(string $address, string $countryCode, array $allowedStates): ?string
	{
		$addr = trim($address);
		if ($addr === '') return null;

		$cc = strtoupper(trim($countryCode));
		$hayAscii = strtoupper(Str::ascii($addr));
		$hayRaw = mb_strtoupper($addr);

		$cls = $this->stateEnumClassForCountry($cc);

		foreach ($allowedStates as $st) {
			$st = strtoupper(trim((string) $st));
			if ($st === '') continue;

			$tokens = [];

			// Evita falso-positivo com códigos de 1 caractere (AR/BO/EC etc.)
			// Endereços reais quase sempre vêm com o nome da província/departamento, não a letra.
			if (strlen($st) >= 2) {
				$tokens[] = $st;
			}

			$e = null;
			if ($cls && enum_exists($cls) && method_exists($cls, 'tryFrom')) {
				$e = $cls::tryFrom($st);
			}

			if ($e && method_exists($e, 'label')) {
				$tokens[] = strtoupper(Str::ascii((string) $e->label()));
			}

			// aliases úteis por país (sem “explodir” ambiguidade)
			if ($cc === 'AR' && $e instanceof \BackedEnum) {
				// CABA (evita usar "BUENOS AIRES" aqui, para não conflitar com província B)
				if ($e->value === 'C') {
					$tokens[] = 'CABA';
					$tokens[] = 'CAPITAL FEDERAL';
					$tokens[] = 'CIUDAD AUTONOMA DE BUENOS AIRES';
					$tokens[] = 'CIUDAD AUTÓNOMA DE BUENOS AIRES';
				}
				if ($e->value === 'B') {
					$tokens[] = 'BUENOS AIRES';
					$tokens[] = 'PROVINCIA DE BUENOS AIRES';
					$tokens[] = 'BS AS';
					$tokens[] = 'BS.AS.';
				}
			}

			if ($cc === 'CL' && $e && method_exists($e, 'romanNumeral')) {
				$rn = strtoupper(Str::ascii((string) $e->romanNumeral()));
				if ($rn !== '') {
					$tokens[] = $rn;
					$tokens[] = "REGION {$rn}";
					$tokens[] = "REGIÓN {$rn}";
					$tokens[] = "{$rn} REGION";
					$tokens[] = "{$rn} REGIÓN";
				}
			}

			if ($cc === 'CL' && $e && method_exists($e, 'shortName')) {
				$tokens[] = strtoupper(Str::ascii((string) $e->shortName()));
			}

			if ($cc === 'GY' && $e && method_exists($e, 'regionNumber')) {
				$n = (string) $e->regionNumber();
				if ($n !== '') {
					$tokens[] = "REGION {$n}";
					$tokens[] = "REGIÓN {$n}";
					$tokens[] = $n;
				}
			}

			if ($cc === 'CO' && $st === 'DC') {
				$tokens[] = 'BOGOTA';
				$tokens[] = 'BOGOTÁ';
				$tokens[] = 'BOGOTA D.C.';
				$tokens[] = 'BOGOTÁ D.C.';
				$tokens[] = 'DISTRITO CAPITAL';
			}

			// Match ASCII tokens
			if ($this->addressHasAnyStateToken($hayAscii, $tokens)) return $st;

			// Match China native tokens (quando houver)
			if ($cc === 'CN' && $e) {
				$zh = [];
				if (method_exists($e, 'labelZh')) $zh[] = mb_strtoupper((string) $e->labelZh());
				if (method_exists($e, 'labelFullZh')) $zh[] = mb_strtoupper((string) $e->labelFullZh());
				foreach ($zh as $t) {
					if ($t !== '' && mb_strpos($hayRaw, $t) !== false) return $st;
				}
			}
		}

		// fallback genérico (somente para países sem enum)
		foreach ($allowedStates as $st) {
			$t = strtoupper(Str::ascii(trim((string) $st)));
			if ($t === '' || strlen($t) < 2) continue;
			if (mb_strpos($hayAscii, $t) !== false) return $t;
		}

		return null;
	}

	protected function normalizeStatesMap(mixed $value): ?array
	{
		if ($value === null) return null;

		$arr = null;

		if (is_array($value)) {
			$arr = $value;
		} elseif (is_string($value) && trim($value) !== '' && ($value[0] === '{' || $value[0] === '[')) {
			$decoded = json_decode($value, true);
			$arr = is_array($decoded) ? $decoded : null;
		} elseif (is_string($value)) {
			$s = trim($value);
			if ($s !== '') {
				$tmp = [];
				foreach (preg_split('/[;|]+/u', $s, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $chunk) {
					$chunk = trim($chunk);
					if ($chunk === '') continue;
					[$k, $rest] = array_pad(preg_split('/\s*[:=]\s*/u', $chunk, 2) ?: [], 2, null);
					$k = is_string($k) ? trim($k) : '';
					$rest = is_string($rest) ? trim($rest) : '';
					if ($k === '' || $rest === '') continue;
					$tmp[$k] = preg_split('/[\s,]+/u', $rest, -1, PREG_SPLIT_NO_EMPTY) ?: [];
				}
				$arr = $tmp ?: null;
			}
		}

		if (!is_array($arr) || !$arr) return null;

		$out = [];

		foreach ($arr as $k => $v) {
			if (!is_scalar($k)) continue;

			$cc = $this->normalizeCountryToCode((string) $k);
			if ($cc === null) continue;

			$list = [];
			if (is_array($v)) {
				$list = $v;
			} elseif (is_string($v) && trim($v) !== '') {
				$list = preg_split('/[\s,]+/u', trim($v), -1, PREG_SPLIT_NO_EMPTY) ?: [];
			}

			$states = [];
			foreach ($list as $sv) {
				if (!is_scalar($sv)) continue;
				$norm = $this->normalizeStateForCountry((string) $sv, $cc, false);
				if ($norm !== null) $states[] = $norm;
			}

			$states = array_values(array_unique(array_filter($states, static fn($x) => is_string($x) && trim($x) !== '')));
			if ($states) $out[$cc] = $states;
		}

		return $out ?: null;
	}


	protected function countryCodesFromMixedList(?array $countries): array
	{
		if ($countries === null) return [];
		$out = [];
		foreach ($countries as $c) {
			$code = $this->normalizeCountryToCode((string) $c);
			if ($code !== null) $out[] = $code;
		}
		return array_values(array_unique($out));
	}

	protected function normalizeAllowedStatesByCountry(array $statesRaw, array $allowedCountries, bool $hasCountries): array
	{
		$out = [];

		foreach ($statesRaw as $countryCode => $list) {
			$cc = $this->normalizeCountryToCode((string) $countryCode);
			if ($cc === null) continue;
			if ($hasCountries && !in_array($cc, $allowedCountries, true)) continue;

			$normStates = [];
			foreach ((array) $list as $sv) {
				if (!is_scalar($sv)) continue;
				$state = $this->normalizeStateForCountry((string) $sv, $cc, false);
				if ($state === null) continue;
				$normStates[] = $state;
			}

			$normStates = array_values(array_unique(array_filter($normStates, fn($x) => is_string($x) && $x !== '')));
			if ($normStates) $out[$cc] = $normStates;
		}

		return $out;
	}

	protected function detectCountryFromAddress(string $address, array $allowedCountryCodes, bool $mustDetect): ?string
	{
		$addr = trim($address);
		if ($addr === '') return null;

		if (!$allowedCountryCodes) return $mustDetect ? null : null;

		$hayAscii = strtoupper(Str::ascii($addr));
		$hayRaw = mb_strtoupper($addr);

		foreach ($allowedCountryCodes as $ccRaw) {
			$cc = $this->normalizeCountryToCode(is_scalar($ccRaw) ? (string) $ccRaw : null);
			if ($cc === null) continue;

			foreach ($this->countryTokensForCode($cc) as $t) {
				$t = is_string($t) ? trim($t) : '';
				if ($t === '') continue;

				$hasNonAscii = (bool) preg_match('/[^\x00-\x7F]/', $t);
				if ($hasNonAscii) {
					if (mb_strpos($hayRaw, mb_strtoupper($t)) !== false) return $cc;
					continue;
				}

				$tt = strtoupper(Str::ascii($t));
				if ($tt === '') continue;

				// For 2–3 letter tokens, enforce boundaries to avoid matching inside other words.
				if (strlen($tt) <= 3) {
					$re = '/(^|[^A-Z0-9])' . preg_quote($tt, '/') . '([^A-Z0-9]|$)/u';
					if (preg_match($re, $hayAscii)) return $cc;
					continue;
				}

				if (mb_strpos($hayAscii, $tt) !== false) return $cc;
			}
		}

		return $mustDetect ? null : null;
	}

	protected function countryTokensForCode(string $cc): array
	{
		$cc = strtoupper(trim($cc));
		return match ($cc) {
			'BR' => ['BR', 'BRA', 'BRAZIL', 'BRASIL'],
			'PT' => ['PT', 'PRT', 'PORTUGAL'],
			'US' => ['US', 'USA', 'UNITED STATES', 'UNITED STATES OF AMERICA', 'ESTADOS UNIDOS'],
			'CN' => ['CN', 'CHN', 'CHINA', '中国', '中國'],

			'AR' => ['AR', 'ARG', 'ARGENTINA'],
			'BO' => ['BO', 'BOL', 'BOLIVIA'],
			'CL' => ['CL', 'CHL', 'CHILE'],
			'EC' => ['EC', 'ECU', 'ECUADOR', 'EQUADOR'],
			'PY' => ['PY', 'PRY', 'PARAGUAY'],
			'PE' => ['PE', 'PER', 'PERU', 'PERÚ'],
			'UY' => ['UY', 'URY', 'URUGUAY'],
			'CO' => ['CO', 'COL', 'COLOMBIA'],
			'GY' => ['GY', 'GUY', 'GUYANA'],

			default => [$cc],
		};
	}

	protected function addressHasAnyStateToken(string $hayAscii, array $tokens): bool
	{
		foreach ($tokens as $t) {
			$tt = strtoupper(Str::ascii(trim((string) $t)));
			if ($tt === '') continue;
			if (strlen($tt) === 1) continue;

			if (strlen($tt) <= 3) {
				$re = '/(^|[^A-Z0-9])' . preg_quote($tt, '/') . '([^A-Z0-9]|$)/';
				if (preg_match($re, $hayAscii)) return true;
				continue;
			}

			if (mb_strpos($hayAscii, $tt) !== false) return true;
		}

		return false;
	}

	protected function stateInAllowed(string $state, array $allowedStates): bool
	{
		$s = strtoupper(Str::ascii(trim($state)));
		foreach ($allowedStates as $a) {
			$aa = strtoupper(Str::ascii(trim((string) $a)));
			if ($aa !== '' && $aa === $s) return true;
		}
		return false;
	}

	protected function computeEffectiveScope(): array
	{
		return $this->cacheOnce('effective_scope', function (): array {
			$countries = null;
			$states = null;

			try {
				$countries = $this->getCountriesConstraintAttribute();
			} catch (\Throwable $e) {
				Log::warning("[" . self::class . "]: failed to compute countries constraint", [
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
				$countries = null;
			}

			try {
				$states = $this->getStatesConstraintAttribute();
			} catch (\Throwable $e) {
				Log::warning("[" . self::class . "]: failed to compute states constraint", [
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
				$states = null;
			}

			$hasCountries = $countries !== null;
			$hasStates = $states !== null;

			$allowedCountries = $countries ?? [];
			$allowedStates = $states ?? [];

			if (!$hasCountries && $hasStates) $allowedCountries = array_values(array_unique(array_keys($allowedStates)));

			$effective = [];

			if (Schema::hasColumn($this->getTable(), 'companies'))
				$effective['companies'] = $this->filterByAddressList('companies', DC::TABLE_USERS, 'address', $allowedCountries, $allowedStates, $hasCountries, $hasStates, true);

			if (Schema::hasColumn($this->getTable(), 'branches'))
				$effective['branches'] = $this->filterByAddressList('branches', DC::TABLE_BRANCHES, 'address', $allowedCountries, $allowedStates, $hasCountries, $hasStates);

			if (Schema::hasColumn($this->getTable(), 'departments'))
				$effective['departments'] = $this->filterByAddressList('departments', DC::TABLE_DEPARTMENTS, 'address', $allowedCountries, $allowedStates, $hasCountries, $hasStates);

			if (Schema::hasColumn($this->getTable(), 'vendors'))
				$effective['vendors'] = $this->filterByBillingList('vendors', DC::TABLE_VENDORS, $allowedCountries, $allowedStates, $hasCountries, $hasStates);

			if (Schema::hasColumn($this->getTable(), 'customers'))
				$effective['customers'] = $this->filterByBillingList('customers', DC::TABLE_CUSTOMERS, $allowedCountries, $allowedStates, $hasCountries, $hasStates);

			return [
				'has_countries' => $hasCountries,
				'has_states' => $hasStates,
				'allowed_countries' => $allowedCountries,
				'allowed_states' => $allowedStates,
				'effective' => $effective,
			];
		});
	}

	protected function filterByAddressList(
		string $field,
		string $table,
		string $addressColumn,
		array $allowedCountries,
		array $allowedStates,
		bool $hasCountries,
		bool $hasStates,
		bool $companyOnly = false
	): ?array {
		$list = null;

		try {
			$list = $this->normalizeStringList($this->getAttribute($field));
		} catch (\Throwable $e) {
			Log::warning("[" . self::class . "]: failed to normalize list for {$field}", [
				'class' => static::class,
				'method' => __METHOD__,
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return null;
		}

		if ($list === null) return null;
		if (!$hasCountries && !$hasStates) return $list;

		try {
			if (!Schema::hasTable($table)) {
				Log::warning("[" . self::class . "]: " . "Holiday scope: missing table {$table} for {$field}", [
					'class' => static::class,
					'method' => __METHOD__,
					'file' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? __FILE__,
					'line' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['line'] ?? __LINE__,
				]);
				return null;
			}
		} catch (\Throwable $e) {
			Log::warning("[" . self::class . "]: failed schema check for {$table}", [
				'class' => static::class,
				'method' => __METHOD__,
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return null;
		}

		$nameCol = null;
		try {
			$nameCol = $this->detectNameColumn($table);
		} catch (\Throwable $e) {
			Log::warning("[" . self::class . "]: failed to detect name column for {$table}", [
				'class' => static::class,
				'method' => __METHOD__,
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			$nameCol = null;
		}

		$select = ['id'];
		if ($nameCol !== null) $select[] = $nameCol;

		try {
			if (Schema::hasColumn($table, $addressColumn)) $select[] = $addressColumn;
		} catch (\Throwable $e) {
			Log::warning("[" . self::class . "]: failed to check address column {$addressColumn} on {$table}", [
				'class' => static::class,
				'method' => __METHOD__,
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		}

		$rows = null;
		try {
			$rows = $this->resolveRowsByIdOrNameCached($table, $list, $select, $nameCol, $companyOnly ? ['type' => 'company'] : []);
		} catch (\Throwable $e) {
			Log::warning("[" . self::class . "]: failed to resolve rows for {$field}", [
				'class' => static::class,
				'method' => __METHOD__,
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return null;
		}

		if (!$rows) return null;

		$out = [];

		foreach ($list as $token) {
			try {
				$row = $this->rowByToken($rows, $token, $nameCol);
				if (!$row) continue;

				$address = '';
				try {
					$address = Schema::hasColumn($table, $addressColumn) && is_scalar($row->{$addressColumn} ?? null)
						? (string) $row->{$addressColumn}
						: '';
				} catch (\Throwable $e) {
					$address = '';
				}

				$country = null;
				try {
					$country = $this->detectCountryFromAddress($address, $allowedCountries, $hasCountries || $hasStates);
				} catch (\Throwable $e) {
					$country = null;
				}

				if ($hasCountries && $country === null) continue;

				if ($hasStates) {
					if ($country === null) continue;
					$allowedForCountry = $allowedStates[$country] ?? null;
					if (!$allowedForCountry) continue;

					$state = null;
					try {
						$state = $this->detectStateFromAddress($address, $country, $allowedForCountry);
					} catch (\Throwable $e) {
						$state = null;
					}

					if ($state === null) continue;

					$ok = false;
					try {
						$ok = $this->stateInAllowed($state, $allowedForCountry);
					} catch (\Throwable $e) {
						$ok = false;
					}

					if (!$ok) continue;
				}

				$out[] = $token;
			} catch (\Throwable $e) {
				Log::warning("[" . self::class . "]: failed to filter token in {$field}", [
					'class' => static::class,
					'method' => __METHOD__,
					'token' => is_scalar($token) ? (string) $token : null,
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			}
		}

		$out = array_values(array_unique(array_filter($out, fn($v) => is_string($v) && trim($v) !== '')));
		return $out ?: null;
	}

	protected function filterByBillingList(
		string $field,
		string $table,
		array $allowedCountries,
		array $allowedStates,
		bool $hasCountries,
		bool $hasStates
	): ?array {
		$list = null;

		try {
			$list = $this->normalizeStringList($this->getAttribute($field));
		} catch (\Throwable $e) {
			Log::warning("[" . self::class . "]: failed to normalize list for {$field}", [
				'class' => static::class,
				'method' => __METHOD__,
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return null;
		}

		if ($list === null) return null;
		if (!$hasCountries && !$hasStates) return $list;

		try {
			if (!Schema::hasTable($table)) {
				Log::warning(
					"[" . self::class . "]: " . "Holiday scope: missing table {$table} for {$field}",
					[
						'class' => static::class,
						'method' => __METHOD__,
						'file' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? __FILE__,
						'line' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['line'] ?? __LINE__,
					]
				);
				return null;
			}
		} catch (\Throwable $e) {
			Log::warning("[" . self::class . "]: failed schema check for {$table}", [
				'class' => static::class,
				'method' => __METHOD__,
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return null;
		}

		$nameCol = null;
		try {
			$nameCol = $this->detectNameColumn($table);
		} catch (\Throwable $e) {
			Log::warning("[" . self::class . "]: failed to detect name column for {$table}", [
				'class' => static::class,
				'method' => __METHOD__,
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			$nameCol = null;
		}

		$select = ['id'];
		if ($nameCol !== null) $select[] = $nameCol;

		foreach ([BC::COL_BL_CTR, BC::COL_BL_ST, BC::COL_BL_ADR] as $col) {
			try {
				if (Schema::hasColumn($table, $col)) $select[] = $col;
			} catch (\Throwable $e) {
				//
			}
		}

		$rows = null;
		try {
			$rows = $this->resolveRowsByIdOrNameCached($table, $list, $select, $nameCol, []);
		} catch (\Throwable $e) {
			Log::warning("[" . self::class . "]: failed to resolve rows for {$field}", [
				'class' => static::class,
				'method' => __METHOD__,
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return null;
		}

		if (!$rows) return null;

		$out = [];

		foreach ($list as $token) {
			try {
				$row = $this->rowByToken($rows, $token, $nameCol);
				if (!$row) continue;

				$billingCountry = null;
				try {
					$billingCountry = Schema::hasColumn($table, BC::COL_BL_CTR) && is_scalar($row->{BC::COL_BL_CTR} ?? null)
						? (string) $row->{BC::COL_BL_CTR}
						: null;
				} catch (\Throwable $e) {
					$billingCountry = null;
				}

				$billingState = null;
				try {
					$billingState = Schema::hasColumn($table, BC::COL_BL_ST) && is_scalar($row->{BC::COL_BL_ST} ?? null)
						? (string) $row->{BC::COL_BL_ST}
						: null;
				} catch (\Throwable $e) {
					$billingState = null;
				}

				$billingAddress = '';
				try {
					$billingAddress = Schema::hasColumn($table, BC::COL_BL_ADR) && is_scalar($row->{BC::COL_BL_ADR} ?? null)
						? (string) $row->{BC::COL_BL_ADR}
						: '';
				} catch (\Throwable $e) {
					$billingAddress = '';
				}

				$country = null;
				try {
					$country = $this->normalizeCountryToCode($billingCountry);
				} catch (\Throwable $e) {
					$country = null;
				}

				if ($country === null) {
					try {
						$country = $this->detectCountryFromAddress($billingAddress, $allowedCountries, $hasCountries || $hasStates);
					} catch (\Throwable $e) {
						$country = null;
					}
				}

				if ($hasCountries && $country === null) continue;

				if ($hasStates) {
					if ($country === null) continue;
					$allowedForCountry = $allowedStates[$country] ?? null;
					if (!$allowedForCountry) continue;

					$state = null;
					try {
						$state = $this->normalizeStateForCountry($billingState, $country, true);
					} catch (\Throwable $e) {
						$state = null;
					}

					if ($state === null) {
						try {
							$state = $this->detectStateFromAddress($billingAddress, $country, $allowedForCountry);
						} catch (\Throwable $e) {
							$state = null;
						}
					}

					if ($state === null) continue;

					$ok = false;
					try {
						$ok = $this->stateInAllowed($state, $allowedForCountry);
					} catch (\Throwable $e) {
						$ok = false;
					}

					if (!$ok) continue;
				}

				$out[] = $token;
			} catch (\Throwable $e) {
				Log::warning("[" . self::class . "]: failed to filter token in {$field}", [
					'class' => static::class,
					'method' => __METHOD__,
					'token' => is_scalar($token) ? (string) $token : null,
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			}
		}

		$out = array_values(array_unique(array_filter($out, fn($v) => is_string($v) && trim($v) !== '')));
		return $out ?: null;
	}

	protected function enforceGeoScopeOnPersistedLists(): void
	{
		$effective = $this->computeEffectiveScope();

		foreach (['companies', 'branches', 'departments', 'vendors', 'customers'] as $field) {
			if (!Schema::hasColumn($this->getTable(), $field)) continue;
			$current = $this->normalizeStringList($this->getAttribute($field));
			if ($current === null) continue;
			$this->setAttribute($field, $effective['effective'][$field] ?? null);
		}

		$countriesNameOnSchema = Schema::hasColumn($this->getTable(), 'countries') ? 'countries' : (Schema::hasColumn($this->getTable(), AC::COL_ALW_CTR) ? AC::COL_ALW_CTR : null);
		if ($countriesNameOnSchema !== null && Schema::hasColumn($this->getTable(), $countriesNameOnSchema)) {
			$countries = $this->normalizeStringList($this->getAttribute($countriesNameOnSchema));
			if ($countries === null) $this->setAttribute($countriesNameOnSchema, null);
		}

		$statesNameOnSchema = Schema::hasColumn($this->getTable(), 'states') ? 'states' : (Schema::hasColumn($this->getTable(), AC::COL_ALW_ST) ? AC::COL_ALW_ST : null);
		if ($statesNameOnSchema !== null && Schema::hasColumn($this->getTable(), $statesNameOnSchema)) {
			$states = $this->normalizeStatesMap($this->getAttribute($statesNameOnSchema));
			if ($states === null) $this->setAttribute($statesNameOnSchema, null);
		}
	}

	protected function normalizeCountryStatePair(
		?string $country,
		?string $state
	): array {
		$country = is_string($country) ? trim($country) : '';
		$state   = is_string($state) ? trim($state) : '';
		$countryCode = $country !== '' ? $this->normalizeCountryToCode($country) : null;
		if ($countryCode === null) {
			if ($state === '')
				return ['country' => null, 'state' => null];
			$detected = $this->reverseDetectCountryFromState($state);
			if ($detected !== null)
				return $detected;
			return ['country' => null, 'state' => null];
		}
		$enumCls = $this->stateEnumClassForCountry($countryCode);
		if ($state === '')
			return ['country' => $countryCode, 'state' => null];
		if (is_string($enumCls) && $enumCls !== '') {
			$norm = $this->normalizeStateForCountry($state, $countryCode, false);
			return ['country' => $countryCode, 'state' => $norm];
		}
		$raw = strtoupper(Str::ascii($state));
		return ['country' => $countryCode, 'state' => ($raw !== '' ? $raw : null)];
	}

	protected function reverseDetectCountryFromState(string $state): ?array
	{
		$st = trim($state);
		if ($st === '') return null;
		foreach (array_keys(static::STATE_ENUMS) as $cc) {
			$norm = $this->normalizeStateForCountry($st, $cc, false);
			if ($norm !== null)
				return ['country' => $cc, 'state' => $norm];
		}
		return null;
	}

	protected function enforceCountryStateColumns(Model $model, string $countryCol = 'country', string $stateCol = 'state'): void
	{
		if (!Schema::hasColumn($model->getTable(), $countryCol) || !Schema::hasColumn($model->getTable(), $stateCol)) return;

		$pair = $this->normalizeCountryStatePair(
			is_scalar($model->getAttribute($countryCol) ?? null) ? (string) $model->getAttribute($countryCol) : null,
			is_scalar($model->getAttribute($stateCol) ?? null) ? (string) $model->getAttribute($stateCol) : null
		);

		$model->setAttribute($countryCol, $pair['country'] ?? null);
		$model->setAttribute($stateCol, $pair['state'] ?? null);
	}

	protected function tryResolveGeoFromZip(string $zip, ?string $country = null): ?GeoZipResolution
	{
		try {
			$cc = null;
			if (is_string($country) && trim($country) !== '' && method_exists($this, 'normalizeCountryToCode'))
				$cc = $this->normalizeCountryToCode($country);
			$svc = app(GeoLookupService::class);
			return $svc->resolveFromZip($zip, $cc);
		} catch (\Throwable) {
			return null;
		}
	}

	protected function rescueGeoFromZipIfMissing(): void
	{
		$sets = [
			['zip', 'country', 'state', 'city', 'address'],
			[BC::COL_SHIP_ZIP, BC::COL_SHIP_CTR, BC::COL_SHIP_ST, BC::COL_SHIP_CTY, BC::COL_SHIP_ADR],
			[BC::COL_BL_ZIP, BC::COL_BL_CTR, BC::COL_BL_ST, BC::COL_BL_CTY, BC::COL_BL_ADR],
		];

		foreach ($sets as $cols) {
			try {
				[$zipCol, $countryCol, $stateCol, $cityCol, $addrCol] = $cols;

				if (!Schema::hasColumn($this->getTable(), $zipCol)) continue;

				$zip = $this->getAttribute($zipCol);
				$zip = is_scalar($zip) ? trim((string) $zip) : '';
				if ($zip === '') continue;

				$hasCountry = Schema::hasColumn($this->getTable(), $countryCol);
				$hasState = Schema::hasColumn($this->getTable(), $stateCol);
				$hasCity = Schema::hasColumn($this->getTable(), $cityCol);
				if (!$hasCountry && !$hasState && !$hasCity) continue;

				$countryNow = $hasCountry && is_scalar($this->getAttribute($countryCol) ?? null) ? trim((string) $this->getAttribute($countryCol)) : '';
				$stateNow = $hasState && is_scalar($this->getAttribute($stateCol) ?? null) ? trim((string) $this->getAttribute($stateCol)) : '';
				$cityNow = $hasCity && is_scalar($this->getAttribute($cityCol) ?? null) ? trim((string) $this->getAttribute($cityCol)) : '';

				if ($countryNow !== '' && $stateNow !== '' && $cityNow !== '') continue;

				$geo = $this->tryResolveGeoFromZip($zip, $countryNow !== '' ? $countryNow : null);
				if (!$geo) continue;

				if ($countryNow === '' && $hasCountry) $this->setAttribute($countryCol, $this->normalizeCountryCodeToCase($geo->countryCode));
				if ($stateNow === '' && $hasState && $geo->state !== null) $this->setAttribute($stateCol, $geo->state);
				if ($cityNow === '' && $hasCity && $geo->city !== null) $this->setAttribute($cityCol, $geo->city);

				if (Schema::hasColumn($this->getTable(), $addrCol)) {
					$addrNow = $this->getAttribute($addrCol);
					$addrNow = is_scalar($addrNow) ? trim((string) $addrNow) : '';
					if ($addrNow === '') {
						$parts = [];
						if ($geo->street !== null && trim($geo->street) !== '') $parts[] = trim($geo->street);
						if ($geo->neighborhood !== null && trim($geo->neighborhood) !== '') $parts[] = trim($geo->neighborhood);
						if ($parts) $this->setAttribute($addrCol, implode(' - ', $parts));
					}
				}
			} catch (\Throwable) {
				continue;
			}
		}
	}

	protected function rescueGeoFromAddressTokensIfMissing(): void
	{
		$sets = [
			['address', 'country', 'state', 'city'],
			[BC::COL_SHIP_ADR, BC::COL_SHIP_CTR, BC::COL_SHIP_ST, BC::COL_SHIP_CTY],
			[BC::COL_BL_ADR, BC::COL_BL_CTR, BC::COL_BL_ST, BC::COL_BL_CTY],
		];

		foreach ($sets as $cols) {
			try {
				[$addrCol, $countryCol, $stateCol, $cityCol] = $cols;

				if (!Schema::hasColumn($this->getTable(), $addrCol)) continue;

				$address = $this->getAttribute($addrCol);
				$address = is_scalar($address) ? trim((string) $address) : '';
				if ($address === '') continue;

				$hasCountry = Schema::hasColumn($this->getTable(), $countryCol);
				$hasState = Schema::hasColumn($this->getTable(), $stateCol);
				$hasCity = Schema::hasColumn($this->getTable(), $cityCol);

				$countryNow = $hasCountry && is_scalar($this->getAttribute($countryCol) ?? null) ? trim((string) $this->getAttribute($countryCol)) : '';
				$stateNow = $hasState && is_scalar($this->getAttribute($stateCol) ?? null) ? trim((string) $this->getAttribute($stateCol)) : '';
				$cityNow = $hasCity && is_scalar($this->getAttribute($cityCol) ?? null) ? trim((string) $this->getAttribute($cityCol)) : '';

				if ($countryNow !== '' && $stateNow !== '' && $cityNow !== '') continue;

				$current = [
					'country' => $countryNow !== '' ? $countryNow : null,
					'state' => $stateNow !== '' ? $stateNow : null,
					'city' => $cityNow !== '' ? $cityNow : null,
				];

				$resolved = null;

				try {
					$resolved = $this->tryResolveGeoFromAddressTokens($address, $current);
				} catch (\Throwable) {
					$resolved = null;
				}

				if (!$resolved || !is_array($resolved)) continue;

				if ($countryNow === '' && $hasCountry && isset($resolved['country'])) {
					$v = $resolved['country'] ?? null;
					if (is_string($v) && trim($v) !== '') $this->setAttribute($countryCol, $this->normalizeCountryCodeToCase($v));
				}

				if ($stateNow === '' && $hasState && isset($resolved['state'])) {
					$v = $resolved['state'] ?? null;
					if (is_string($v) && trim($v) !== '') $this->setAttribute($stateCol, trim($v));
				}

				if ($cityNow === '' && $hasCity && isset($resolved['city'])) {
					$v = $resolved['city'] ?? null;
					if (is_string($v) && trim($v) !== '') $this->setAttribute($cityCol, trim($v));
				}
			} catch (\Throwable) {
				continue;
			}
		}
	}

	protected function tryResolveCountryFromStateToken(?string $stateToken): ?string
	{
		$stateToken = is_string($stateToken) ? trim($stateToken) : '';
		if ($stateToken === '') return null;

		$attempts = 0;
		$maxAttempts = 64;
		$needleRaw = $stateToken;
		$needle = mb_strtolower($stateToken);
		$needle = preg_replace('/\s+/', ' ', $needle);
		$needle = trim($needle);

		foreach (array_keys(self::STATE_ENUMS) as $countryCode) {
			if ($attempts++ >= $maxAttempts) break;

			$enumCls = $this->stateEnumClassForCountry((string) $countryCode);
			if (!$enumCls || !enum_exists($enumCls) || !method_exists($enumCls, 'cases')) continue;

			try {
				/** @var \BackedEnum $e */
				foreach ($enumCls::cases() as $e) {
					$v = (string) $e->value;
					if (mb_strtolower(trim($v)) === $needle) return (string) $countryCode;
					if (mb_strtolower(trim($v)) === mb_strtolower(trim($needleRaw))) return (string) $countryCode;
				}
			} catch (\Throwable) {
				//
			}

			try {
				$norm = $this->normalizeStateForCountry($needleRaw, (string) $countryCode, false);
				if ($norm !== null && is_string($norm) && trim($norm) !== '') return (string) $countryCode;
			} catch (\Throwable) {
				//
			}
		}

		return null;
	}

	protected function tryResolveGeoFromAddressTokens(string $address, array $current = []): ?array
	{
		$addr = trim($address);
		if ($addr === '') return null;

		$out = [];

		$curCountry = isset($current['country']) && is_string($current['country']) ? trim($current['country']) : null;
		$curState = isset($current['state']) && is_string($current['state']) ? trim($current['state']) : null;
		$curCity = isset($current['city']) && is_string($current['city']) ? trim($current['city']) : null;

		$curCountry = ($curCountry !== null && $curCountry !== '') ? $curCountry : null;
		$curState = ($curState !== null && $curState !== '') ? $curState : null;
		$curCity = ($curCity !== null && $curCity !== '') ? $curCity : null;

		$tokens = $this->tokenizeAddressForGeoLabels($addr);
		if (!$tokens) return null;

		$extractLabeledValue = function (string $kind) use ($tokens): ?string {
			foreach (self::GEO_LABEL_LANG_ORDER as $lang) {
				$map = self::GEO_LABEL_TOKENS_BY_LANG[$lang] ?? null;
				if (!$map || !isset($map[$kind]) || !is_array($map[$kind])) continue;
				$labels = $map[$kind];
				$val = $this->findGeoValueByLabels($tokens, $labels);
				if (is_string($val) && trim($val) !== '') return trim($val);
			}
			return null;
		};

		if ($curCountry === null) {
			$v = $extractLabeledValue('country');
			if ($v !== null) $out['country'] = $v;
		}

		if ($curState === null) {
			$v = $extractLabeledValue('state');
			if ($v !== null) $out['state'] = $v;
		}

		if ($curCity === null) {
			$v = $extractLabeledValue('city');
			if ($v !== null) $out['city'] = $v;
		}

		$country = $curCountry ?? (isset($out['country']) && is_string($out['country']) ? trim((string) $out['country']) : null);

		if ($country === null || $country === '') {
			try {
				$detected = $this->detectCountryFromAddress($addr, array_keys(self::STATE_ENUMS), false);
				if (is_string($detected) && trim($detected) !== '') {
					$country = trim($detected);
					if ($curCountry === null) $out['country'] = $country;
				}
			} catch (\Throwable) {
				//
			}
		}

		if (($country === null || $country === '') && $curState !== null) {
			try {
				$rev = $this->tryResolveCountryFromStateToken($curState);
				if (is_string($rev) && trim($rev) !== '') {
					$country = trim($rev);
					if ($curCountry === null) $out['country'] = $country;
				}
			} catch (\Throwable) {
				//
			}
		}

		try {
			$pair = $this->normalizeCountryStatePair(
				$country ?: null,
				$curState ?? (isset($out['state']) && is_string($out['state']) ? (string) $out['state'] : null)
			);

			$normCountry = isset($pair['country']) && is_string($pair['country']) && trim($pair['country']) !== '' ? trim($pair['country']) : null;
			$normState = isset($pair['state']) && is_string($pair['state']) && trim($pair['state']) !== '' ? trim($pair['state']) : null;

			if ($curCountry === null && $normCountry !== null) {
				$country = $normCountry;
				$out['country'] = $normCountry;
			}

			if ($curState === null && $normState !== null) {
				$out['state'] = $normState;
			}
		} catch (\Throwable) {
			//
		}

		$finalCountry = $country;
		$hasStateNow = $curState !== null || (isset($out['state']) && is_string($out['state']) && trim((string) $out['state']) !== '');

		if (is_string($finalCountry) && trim($finalCountry) !== '' && !$hasStateNow) {
			try {
				$cc = (string) $finalCountry;
				$enumCls = $this->stateEnumClassForCountry($cc);
				if ($enumCls && enum_exists($enumCls) && method_exists($enumCls, 'cases')) {
					$allowed = array_map(fn(\BackedEnum $e) => (string) $e->value, $enumCls::cases());
					$detState = $this->detectStateFromAddress($addr, $cc, $allowed);
					if (is_string($detState) && trim($detState) !== '' && $curState === null) {
						$out['state'] = trim($detState);
					}
				}
			} catch (\Throwable) {
				//
			}
		}

		if ($curCity === null && !isset($out['city']) && method_exists($this, 'extractCityFromAddressLoose')) {
			try {
				$maybeCity = $this->extractCityFromAddressLoose($addr);
				if (is_string($maybeCity) && trim($maybeCity) !== '') $out['city'] = trim($maybeCity);
			} catch (\Throwable) {
				//
			}
		}

		try {
			$finalCountry2 = $curCountry ?? (isset($out['country']) ? (string) $out['country'] : null);
			$finalState2 = $curState ?? (isset($out['state']) ? (string) $out['state'] : null);

			$pair = $this->normalizeCountryStatePair(
				$finalCountry2 ? trim((string) $finalCountry2) : null,
				$finalState2 ? trim((string) $finalState2) : null
			);

			$normCountry2 = isset($pair['country']) && is_string($pair['country']) && trim($pair['country']) !== '' ? trim($pair['country']) : null;
			$normState2 = isset($pair['state']) && is_string($pair['state']) && trim($pair['state']) !== '' ? trim($pair['state']) : null;

			if ($curCountry === null && $normCountry2 !== null) $out['country'] = $normCountry2;
			if ($curState === null && $normState2 !== null) $out['state'] = $normState2;
		} catch (\Throwable) {
			//
		}

		$clean = [];
		foreach (['country', 'state', 'city'] as $k) {
			$v = $out[$k] ?? null;
			if (!is_string($v)) continue;
			$v = trim($v);
			if ($v !== '') $clean[$k] = $v;
		}

		return $clean ?: null;
	}

	protected function extractCityFromAddressLoose(string $address): ?string
	{
		$s = trim($address);
		if ($s === '') return null;
		if (preg_match('/,\s*([^,]{2,64})\s*[-\/]\s*[A-Z]{2}\b/u', $s, $m)) {
			$city = trim((string) ($m[1] ?? ''));
			if ($city !== '' && mb_strlen($city) >= 2) return $city;
		}
		return null;
	}

	protected function normalizeGeo(): void
	{
		$sets = [
			['country', 'state', 'city', 'address'],
			[BC::COL_SHIP_CTR, BC::COL_SHIP_ST, BC::COL_SHIP_CTY, BC::COL_SHIP_ADR],
			[BC::COL_BL_CTR, BC::COL_BL_ST, BC::COL_BL_CTY, BC::COL_BL_ADR],
		];

		foreach ($sets as $cols) {
			try {
				[$countryCol, $stateCol, $cityCol, $addrCol] = $cols;

				if (Schema::hasColumn($this->getTable(), $countryCol) && Schema::hasColumn($this->getTable(), $stateCol)) {
					$pair = $this->normalizeCountryStatePair(
						is_scalar($this->getAttribute($countryCol) ?? null) ? (string) $this->getAttribute($countryCol) : null,
						is_scalar($this->getAttribute($stateCol) ?? null) ? (string) $this->getAttribute($stateCol) : null
					);
					$this->setAttribute($countryCol, $this->normalizeCountryCodeToCase($pair['country'] ?? null));
					$this->setAttribute($stateCol, $pair['state'] ?? null);
				}

				if (Schema::hasColumn($this->getTable(), $cityCol)) {
					$city = $this->getAttribute($cityCol);
					if (is_scalar($city)) {
						$c = trim((string) $city);
						$this->setAttribute($cityCol, $c !== '' ? $c : null);
					} elseif ($city === '') $this->setAttribute($cityCol, null);
				}

				if (Schema::hasColumn($this->getTable(), $addrCol)) {
					$addr = $this->getAttribute($addrCol);
					if (is_scalar($addr)) {
						$a = trim((string) $addr);
						$this->setAttribute($addrCol, $a !== '' ? $a : null);
					} elseif ($addr === '') $this->setAttribute($addrCol, null);
				}
			} catch (\Throwable) {
				continue;
			}
		}
	}

	/**
	 * Tokeniza um endereço em partes úteis para extração por "label: value".
	 *
	 * Exemplos suportados:
	 *  - "City: São Paulo, State: SP, Country: BR"
	 *  - "Cidade - Lisboa; País PT"
	 *  - "Estado SP - Brasil"
	 *
	 * @return string[] tokens normalizados (mantém conteúdo original, mas trim e colapsa whitespace)
	 */
	protected function tokenizeAddressForGeoLabels(string $address): array
	{
		$s = trim($address);
		if ($s === '') return [];
		$s = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $s);
		$parts = preg_split('/[,\;\|\x{2014}\x{2013}\-\/\\\\:]+|\s{2,}/u', $s) ?: [];
		$out = [];
		foreach ($parts as $p) {
			$p = trim(preg_replace('/\s+/u', ' ', (string) $p));
			if ($p !== '') $out[] = $p;
		}
		return $out;
	}

	protected function findGeoValueByLabels(array $tokens, array $labels): ?string
	{
		if (!$tokens || !$labels) return null;

		$normLabels = [];
		foreach ($labels as $l) {
			if (!is_string($l)) continue;
			$ll = trim($l);
			if ($ll === '') continue;
			$normLabels[] = [
				'ascii' => strtoupper(Str::ascii($ll)),
				'raw' => mb_strtoupper($ll),
				'raw_src' => $ll,
			];
		}

		$uniq = [];
		foreach ($normLabels as $row) {
			$k = (string) ($row['ascii'] ?? '');
			if ($k === '') continue;
			$uniq[$k] = $row;
		}
		$normLabels = array_values($uniq);
		if (!$normLabels) return null;

		$tokenCount = count($tokens);

		for ($i = 0; $i < $tokenCount; $i++) {
			try {
				$raw = (string) $tokens[$i];
				$rawTrim = trim($raw);
				if ($rawTrim === '') continue;

				$hayAscii = strtoupper(Str::ascii($rawTrim));
				$hayRawUpper = mb_strtoupper($rawTrim);

				foreach ($normLabels as $labRow) {
					$labAscii = (string) ($labRow['ascii'] ?? '');
					$labRawUpper = (string) ($labRow['raw'] ?? '');
					$labRawSrc = (string) ($labRow['raw_src'] ?? '');

					if ($labAscii === '' && $labRawUpper === '') continue;

					$hasLabel = false;

					if ($labAscii !== '') {
						$re = '/(^|[^A-Z0-9])' . preg_quote($labAscii, '/') . '([^A-Z0-9]|$)/';
						if (preg_match($re, $hayAscii)) $hasLabel = true;
					}

					if (!$hasLabel && $labRawUpper !== '' && mb_strpos($hayRawUpper, $labRawUpper) !== false) $hasLabel = true;
					if (!$hasLabel) continue;

					$tail = null;

					if ($labRawUpper !== '') {
						$pos = mb_stripos($hayRawUpper, $labRawUpper);
						if ($pos !== false) $tail = trim((string) mb_substr($rawTrim, $pos + mb_strlen($labRawUpper)));
					}

					if ($tail === null && $labAscii !== '') {
						$posA = mb_stripos($hayAscii, $labAscii);
						if ($posA !== false) $tail = trim((string) mb_substr($rawTrim, $posA + mb_strlen($labRawSrc)));
					}

					if (is_string($tail)) {
						$tail = ltrim($tail, " \t:-—–|/\\,;");
						$tail = trim($tail);
					}

					if (is_string($tail) && $tail !== '') return $this->cleanGeoLabeledValue($tail);

					if ($i + 1 < $tokenCount) {
						$next = trim((string) $tokens[$i + 1]);
						if ($next !== '') return $this->cleanGeoLabeledValue($next);
					}
				}
			} catch (\Throwable) {
				continue;
			}
		}

		return null;
	}

	protected function cleanGeoLabeledValue(string $value): string
	{
		$v = trim(preg_replace('/\s+/u', ' ', $value));
		$v = trim($v, " \t\n\r\0\x0B\"'()[]{}");
		return $v;
	}

	protected function rescueCountryStateFromKnownCityList(Model $model): void
	{
		$table = $model->getTable();
		if (!is_string($table) || $table === '' || !Schema::hasTable($table)) return;

		$data = defined('static::CITIES_BY_STATE') ? static::CITIES_BY_STATE : null;
		if (!is_array($data)) return;

		$br = $data['BR'] ?? null;
		if (!is_array($br)) return;

		$charClass = static function (string $s): string {
			$ascii = mb_strtolower(Str::ascii($s), 'UTF-8');
			return match ($ascii) {
				'a' => '[aáàâãä]',
				'e' => '[eéèêë]',
				'i' => '[iíìîï]',
				'o' => '[oóòôõö]',
				'u' => '[uúùûü]',
				'y' => '[yýÿ]',
				'c' => '[cç]',
				'n' => '[nñ]',
				default => preg_quote($s, '/'),
			};
		};

		$toLooseRegex = static function (string $name) use ($charClass): string {
			$s = trim($name);
			if ($s === '') return '';
			$parts = preg_split('/[\s_-—–\:;,\|\\\\\/]+/u', $s, -1, PREG_SPLIT_NO_EMPTY) ?: [];
			if (!$parts) return '';

			$outParts = [];
			foreach ($parts as $p) {
				$p = trim((string) $p);
				if ($p === '') continue;

				$chars = preg_split('//u', $p, -1, PREG_SPLIT_NO_EMPTY) ?: [];
				if (!$chars) continue;

				$rx = '';
				foreach ($chars as $ch) $rx .= $charClass($ch);
				$outParts[] = $rx;
			}

			if (!$outParts) return '';
			$sep = '[\s_-—–\:;,\|\\\\\/]*';
			return $sep . implode($sep, $outParts) . $sep;
		};

		$tryList = static function (?array $list, string $hay, bool $normalized) use ($toLooseRegex): ?string {
			if (!is_array($list) || !$list) return null;

			$attempt = 0;
			$limit = 25000;

			foreach ($list as $name) {
				if ($attempt++ >= $limit) return null;

				if (!is_scalar($name)) continue;
				$s = trim((string) $name);
				if ($s === '') continue;

				$candidate = $normalized
					? (preg_replace('/\s+/u', ' ', trim(mb_strtolower(Str::ascii($s), 'UTF-8'))) ?: trim(mb_strtolower(Str::ascii($s), 'UTF-8')))
					: $s;

				$rxCore = $toLooseRegex($candidate);
				if ($rxCore === '') continue;

				$rx = '/(^|[^0-9A-Z])' . $rxCore . '([^0-9A-Z]|$)/iu';
				if (preg_match($rx, $hay)) return $s;
			}

			return null;
		};

		$sets = [
			['country', 'state', Schema::hasColumn($table, 'city') ? 'city' : (Schema::hasColumn($table, 'municipality') ? 'municipality' : null)],
			[BC::COL_SHIP_CTR, BC::COL_SHIP_ST, BC::COL_SHIP_CTY],
			[BC::COL_BL_CTR, BC::COL_BL_ST, BC::COL_BL_CTY],
		];

		foreach ($sets as [$countryCol, $stateCol, $cityCol]) {
			try {
				if (!is_string($cityCol) || $cityCol === '' || !Schema::hasColumn($table, $cityCol)) continue;

				$hasCountry = Schema::hasColumn($table, $countryCol);
				$hasState = Schema::hasColumn($table, $stateCol);
				if (!$hasCountry && !$hasState) continue;

				$countryNow = $hasCountry && is_scalar($model->getAttribute($countryCol) ?? null) ? trim((string) $model->getAttribute($countryCol)) : '';
				$stateNow = $hasState && is_scalar($model->getAttribute($stateCol) ?? null) ? trim((string) $model->getAttribute($stateCol)) : '';

				$needsCountry = $hasCountry && $countryNow === '';
				$needsState = $hasState && $stateNow === '';
				if (!$needsCountry && !$needsState) continue;

				$cityRaw = is_scalar($model->getAttribute($cityCol) ?? null) ? trim((string) $model->getAttribute($cityCol)) : '';
				if ($cityRaw === '') continue;

				$normCity = mb_strtolower(Str::ascii($cityRaw), 'UTF-8');
				$normCity = preg_replace('/\s+/u', ' ', trim($normCity)) ?: trim($normCity);

				foreach (['RJ', 'SP', 'MG'] as $st) {
					$bucket = $br[$st] ?? null;
					if (!is_array($bucket)) continue;

					$common = $bucket['common'] ?? null;
					$normalized = $bucket['normalized'] ?? null;

					$hit = $tryList($common, $cityRaw, false);
					if ($hit === null) $hit = $tryList($normalized, $normCity, true);
					if ($hit === null) continue;

					if ($needsCountry) $model->setAttribute($countryCol, CountryName::Brazil->value);
					if ($needsState) $model->setAttribute($stateCol, $st);
					break 2;
				}
			} catch (\Throwable) {
				continue;
			}
		}
	}

	protected function pickStateCodesForCountry(?string $country): array
	{
		try {
			$cc = $this->normalizeCountryToCode($country);
			if ($cc === null) return [];
			$cls = $this->stateEnumClassForCountry($cc);
			if (!is_string($cls) || $cls === '' || !enum_exists($cls) || !is_subclass_of($cls, \BackedEnum::class)) return [];
			$codes = array_map(static fn(\BackedEnum $e): string => (string) $e->value, $cls::cases());
			$codes = array_values(array_unique(array_filter($codes, static fn($v): bool => is_string($v) && $v !== '')));
			return $codes;
		} catch (\Throwable) {
			return [];
		}
	}

	private function normalizeCountryCodeToCase(?string $countryCode): ?string
	{
		try {
			if (!is_string($countryCode)) return null;
			$countryNormalized = CountryName::normalize($countryCode);
			$countryNormalized = $countryNormalized instanceof CountryName ? (string) $countryNormalized->value : trim($countryCode);
			return $countryNormalized !== '' ? $countryNormalized : null;
		} catch (\Throwable) {
			return null;
		}
	}
}
