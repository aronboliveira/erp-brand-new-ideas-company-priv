<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum WorkContractType: string
{
	// Brazilian Contract Types
	case CLT = 'clt';
	case PJ = 'pj';
	case MEI = 'mei';
	case Freelancer = 'freelancer';
	case Society = 'society';
	case Cooperado = 'cooperado';
	case Estagiario = 'estagiario';
	case MenorAprendiz = 'menor_aprendiz';
	case Autonomo = 'autonomo';
	case Temporario = 'temporario';
	case Intermitente = 'intermitente';
	case TrabalhoRemoto = 'trabalho_remoto';
	case TrabalhoHibrido = 'trabalho_hibrido';
	case Terceirizado = 'terceirizado';

		// International Contract Types
	case FullTime = 'full_time';
	case PartTime = 'part_time';
	case Permanent = 'permanent';
	case FixedTerm = 'fixed_term';
	case Temporary = 'temporary';
	case Contract = 'contract';
	case Casual = 'casual';
	case Seasonal = 'seasonal';
	case ZeroHours = 'zero_hours';
	case Intern = 'intern';
	case Apprentice = 'apprentice';
	case Trainee = 'trainee';
	case Volunteer = 'volunteer';
	case Consultant = 'consultant';
	case Contractor = 'contractor';
	case SelfEmployed = 'self_employed';
	case Agency = 'agency';
	case Gig = 'gig';
	case Remote = 'remote';
	case Hybrid = 'hybrid';
	case OnCall = 'on_call';
	case ProjectBased = 'project_based';
	case CommissionOnly = 'commission_only';
	case Probationary = 'probationary';
	case Executive = 'executive';
	case Union = 'union';
	case Government = 'government';
	case Military = 'military';

	/**
	 * Normalize input to WorkContractType
	 */
	public static function normalize(string|int|null|self $value = null): ?self
	{
		if ($value instanceof self) {
			return $value;
		}

		if ($value === null) {
			return null;
		}

		$normalizedValue = preg_replace('/[^a-z0-9]/', '', strtolower(trim((string) $value)));
		return match ($normalizedValue) {
			// Brazilian types
			'clt', 'regimeclt', 'consolidacaoleistrabalho', 'celetista' => self::CLT,
			'pj', 'pessoajuridica', 'pessoajurídica', 'empresario', 'empresário' => self::PJ,
			'mei', 'microempreendedorindividual', 'microempreendedor' => self::MEI,
			'freelancer', 'freela', 'freelance', 'autonomo', 'autônomo' => self::Freelancer,
			'society', 'sociedade', 'socio', 'sócio', 'parceiro' => self::Society,
			'cooperado', 'cooperativa', 'cooperative' => self::Cooperado,
			'estagiario', 'estagiário', 'estagio', 'estágio', 'intern' => self::Estagiario,
			'menoraprendiz', 'jovemaprendiz', 'aprendiz' => self::MenorAprendiz,
			'temporario', 'temporário', 'temporary' => self::Temporario,
			'intermitente', 'intermittent' => self::Intermitente,
			'trabalhoremoto', 'remoto', 'remote' => self::TrabalhoRemoto,
			'trabalhoibrido', 'hibrido', 'híbrido', 'hybrid' => self::TrabalhoHibrido,
			'terceirizado', 'terceirization', 'outsourced' => self::Terceirizado,

			// International types
			'fulltime', 'fulltime', 'efetivo', 'permanente' => self::FullTime,
			'parttime', 'parttime', 'meioperiodo', 'medioperiodo' => self::PartTime,
			'permanent', 'permanente', 'indefinido' => self::Permanent,
			'fixedterm', 'fixedterm', 'prazoeterminado', 'determinado' => self::FixedTerm,
			'temporary', 'temporario', 'temporária' => self::Temporary,
			'contract', 'contrato', 'agreement' => self::Contract,
			'casual', 'casual', 'eventual', 'ocasional' => self::Casual,
			'seasonal', 'sazonal', 'estacional' => self::Seasonal,
			'zerohours', 'zerohours', 'horaszero' => self::ZeroHours,
			'intern', 'estagiario', 'pasantia' => self::Intern,
			'apprentice', 'aprendiz', 'aprendizaje' => self::Apprentice,
			'trainee', 'treinee', 'treinamento' => self::Trainee,
			'volunteer', 'voluntario', 'voluntário' => self::Volunteer,
			'consultant', 'consultor', 'asesor' => self::Consultant,
			'contractor', 'contratista', 'empreiteiro' => self::Contractor,
			'selfemployed', 'selfemployed', 'autonomo', 'autónomo' => self::SelfEmployed,
			'agency', 'agencia', 'agência' => self::Agency,
			'gig', 'gig', 'bico', 'freela' => self::Gig,
			'remote', 'remoto', 'teletrabalho' => self::Remote,
			'hybrid', 'hibrido', 'mixto' => self::Hybrid,
			'oncall', 'oncall', 'disponivel', 'disponible' => self::OnCall,
			'projectbased', 'projectbased', 'projeto', 'proyecto' => self::ProjectBased,
			'commissiononly', 'commissiononly', 'comissao', 'comisión' => self::CommissionOnly,
			'probationary', 'probationary', 'experiencia', 'prueba' => self::Probationary,
			'executive', 'executivo', 'ejecutivo' => self::Executive,
			'union', 'sindical', 'sindicalizado' => self::Union,
			'government', 'governo', 'gubernamental' => self::Government,
			'military', 'militar', 'fuerzasarmadas' => self::Military,

			default => null,
		};
	}

	/**
	 * Get labels in specified language
	 */
	public static function labels($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$lang = preg_replace('/_/', '-', strtolower(trim($lang ?? '')));
		return match ($lang) {
			'pt-br', 'pt' => self::labelsPtBr(),
			'es', 'es-es' => self::labelsEs(),
			'ar', 'ar-sa' => self::labelsAr(),
			'da', 'da-dk' => self::labelsDa(),
			'de', 'de-de' => self::labelsDe(),
			'fr', 'fr-fr' => self::labelsFr(),
			'he', 'he-il' => self::labelsHe(),
			'it', 'it-it' => self::labelsIt(),
			'ja', 'ja-jp' => self::labelsJa(),
			'nl', 'nl-nl' => self::labelsNl(),
			'pl', 'pl-pl' => self::labelsPl(),
			'ru', 'ru-ru' => self::labelsRu(),
			'tr', 'tr-tr' => self::labelsTr(),
			'zh', 'zh-cn' => self::labelsZh(),
			default => self::labelsEn(),
		};
	}

	/**
	 * Get label for this contract type in specified language
	 */
	public function label($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$labels = self::labels($lang);
		return $labels[$this->value] ?? ucfirst(str_replace('_', ' ', $this->value));
	}

	/**
	 * Get color for UI display
	 */
	public function getColor(): string
	{
		return match ($this) {
			// Brazilian standard employment - green
			self::CLT, self::Permanent, self::FullTime => '#10b981',

			// Brazilian freelance/contract - blue
			self::PJ, self::MEI, self::Freelancer, self::Consultant, self::Contractor, self::SelfEmployed => '#3b82f6',

			// Brazilian partnership/society - purple
			self::Society, self::Cooperado => '#8b5cf6',

			// Temporary/seasonal - amber
			self::Temporario, self::Intermitente, self::Temporary, self::Seasonal, self::Casual => '#f59e0b',

			// Training/apprentice - cyan
			self::Estagiario, self::MenorAprendiz, self::Intern, self::Apprentice, self::Trainee => '#06b6d4',

			// Remote/hybrid - indigo
			self::TrabalhoRemoto, self::TrabalhoHibrido, self::Remote, self::Hybrid => '#6366f1',

			// Special/other - gray
			self::Volunteer, self::ZeroHours, self::OnCall, self::CommissionOnly => '#6b7280',

			// Default - gray
			default => '#9ca3af',
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match (true) {
			// Brazilian employment
			$this === self::CLT => 'file-contract',
			$this === self::PJ => 'building',
			$this === self::MEI => 'user-tie',
			$this === self::Freelancer => 'user-clock',

			// Standard employment
			in_array($this, [self::FullTime, self::Permanent, self::PartTime]) => 'briefcase',

			// Contract/temporary
			in_array($this, [self::Contract, self::FixedTerm, self::Temporary, self::Temporario]) => 'file-signature',

			// Partnership
			in_array($this, [self::Society, self::Cooperado]) => 'handshake',

			// Training
			in_array($this, [self::Estagiario, self::Intern, self::Apprentice, self::Trainee, self::MenorAprendiz]) => 'user-graduate',

			// Remote/hybrid
			in_array($this, [self::Remote, self::TrabalhoRemoto, self::Hybrid, self::TrabalhoHibrido]) => 'laptop-house',

			// Volunteer/special
			in_array($this, [self::Volunteer, self::CommissionOnly, self::ZeroHours, self::OnCall]) => 'heart',

			// Default
			default => 'file-alt',
		};
	}

	/**
	 * Check if this is a formal employment contract (with labor rights)
	 */
	public function isFormalEmployment(): bool
	{
		return in_array($this, [
			self::CLT,
			self::Permanent,
			self::FullTime,
			self::PartTime,
			self::Government,
			self::Military,
			self::Union,
		]);
	}

	/**
	 * Check if this is a freelance/contractor arrangement
	 */
	public function isFreelance(): bool
	{
		return in_array($this, [
			self::PJ,
			self::MEI,
			self::Freelancer,
			self::Consultant,
			self::Contractor,
			self::SelfEmployed,
			self::Gig,
			self::Autonomo,
		]);
	}

	/**
	 * Check if this is a partnership arrangement
	 */
	public function isPartnership(): bool
	{
		return in_array($this, [
			self::Society,
			self::Cooperado,
		]);
	}

	/**
	 * Check if this is a temporary contract
	 */
	public function isTemporary(): bool
	{
		return in_array($this, [
			self::Temporario,
			self::Intermitente,
			self::Temporary,
			self::FixedTerm,
			self::Seasonal,
			self::Casual,
			self::ProjectBased,
		]);
	}

	/**
	 * Check if this is a training/internship contract
	 */
	public function isTraining(): bool
	{
		return in_array($this, [
			self::Estagiario,
			self::MenorAprendiz,
			self::Intern,
			self::Apprentice,
			self::Trainee,
		]);
	}

	/**
	 * Check if this contract includes remote/hybrid work
	 */
	public function isRemoteOrHybrid(): bool
	{
		return in_array($this, [
			self::Remote,
			self::TrabalhoRemoto,
			self::Hybrid,
			self::TrabalhoHibrido,
		]);
	}

	/**
	 * Get Brazilian-specific contract types
	 */
	public static function getBrazilianTypes(): array
	{
		return [
			self::CLT,
			self::PJ,
			self::MEI,
			self::Freelancer,
			self::Society,
			self::Cooperado,
			self::Estagiario,
			self::MenorAprendiz,
			self::Autonomo,
			self::Temporario,
			self::Intermitente,
			self::TrabalhoRemoto,
			self::TrabalhoHibrido,
			self::Terceirizado,
		];
	}

	/**
	 * Get international contract types
	 */
	public static function getInternationalTypes(): array
	{
		return [
			self::FullTime,
			self::PartTime,
			self::Permanent,
			self::FixedTerm,
			self::Temporary,
			self::Contract,
			self::Casual,
			self::Seasonal,
			self::ZeroHours,
			self::Intern,
			self::Apprentice,
			self::Trainee,
			self::Volunteer,
			self::Consultant,
			self::Contractor,
			self::SelfEmployed,
			self::Agency,
			self::Gig,
			self::Remote,
			self::Hybrid,
			self::OnCall,
			self::ProjectBased,
			self::CommissionOnly,
			self::Probationary,
			self::Executive,
			self::Union,
			self::Government,
			self::Military,
		];
	}

	/**
	 * Get the category of contract
	 */
	public function getCategory(): string
	{
		if ($this->isFormalEmployment()) {
			return 'formal';
		} elseif ($this->isFreelance()) {
			return 'freelance';
		} elseif ($this->isPartnership()) {
			return 'partnership';
		} elseif ($this->isTemporary()) {
			return 'temporary';
		} elseif ($this->isTraining()) {
			return 'training';
		} else {
			return 'other';
		}
	}

	/**
	 * Check if this contract type is valid in Brazil
	 */
	public function isValidInBrazil(): bool
	{
		return in_array($this, self::getBrazilianTypes());
	}

	/**
	 * Get social security/benefits info based on contract type
	 */
	public function getSocialSecurityInfo(): string
	{
		return match ($this) {
			self::CLT => 'INSS, FGTS, 13th salary, vacation pay',
			self::PJ => 'Optional INSS, no FGTS',
			self::MEI => 'Simplified INSS, limited benefits',
			self::Estagiario => 'No INSS required, may have accident insurance',
			self::MenorAprendiz => 'INSS, FGTS, reduced working hours',
			self::Freelancer => 'Optional INSS as MEI or individual',
			self::Society => 'Partner contributions, no formal employment benefits',
			self::Cooperado => 'Cooperative contributions, no formal employment',
			default => 'Varies by country and specific contract',
		};
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			// Brazilian
			self::CLT->value => 'CLT (Formal Employment)',
			self::PJ->value => 'PJ (Legal Entity)',
			self::MEI->value => 'MEI (Individual Microentrepreneur)',
			self::Freelancer->value => 'Freelancer',
			self::Society->value => 'Society/Partnership',
			self::Cooperado->value => 'Cooperative Member',
			self::Estagiario->value => 'Intern/Trainee',
			self::MenorAprendiz->value => 'Young Apprentice',
			self::Autonomo->value => 'Self-Employed',
			self::Temporario->value => 'Temporary',
			self::Intermitente->value => 'Intermittent',
			self::TrabalhoRemoto->value => 'Remote Work',
			self::TrabalhoHibrido->value => 'Hybrid Work',
			self::Terceirizado->value => 'Outsourced',

			// International
			self::FullTime->value => 'Full-Time',
			self::PartTime->value => 'Part-Time',
			self::Permanent->value => 'Permanent',
			self::FixedTerm->value => 'Fixed-Term',
			self::Temporary->value => 'Temporary',
			self::Contract->value => 'Contract',
			self::Casual->value => 'Casual',
			self::Seasonal->value => 'Seasonal',
			self::ZeroHours->value => 'Zero-Hours',
			self::Intern->value => 'Intern',
			self::Apprentice->value => 'Apprentice',
			self::Trainee->value => 'Trainee',
			self::Volunteer->value => 'Volunteer',
			self::Consultant->value => 'Consultant',
			self::Contractor->value => 'Contractor',
			self::SelfEmployed->value => 'Self-Employed',
			self::Agency->value => 'Agency',
			self::Gig->value => 'Gig Worker',
			self::Remote->value => 'Remote',
			self::Hybrid->value => 'Hybrid',
			self::OnCall->value => 'On-Call',
			self::ProjectBased->value => 'Project-Based',
			self::CommissionOnly->value => 'Commission Only',
			self::Probationary->value => 'Probationary',
			self::Executive->value => 'Executive',
			self::Union->value => 'Union',
			self::Government->value => 'Government',
			self::Military->value => 'Military',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			// Brazilian
			self::CLT->value => 'CLT (Regime Consolidado)',
			self::PJ->value => 'PJ (Pessoa Jurídica)',
			self::MEI->value => 'MEI (Microempreendedor Individual)',
			self::Freelancer->value => 'Freelancer',
			self::Society->value => 'Sociedade/Parceria',
			self::Cooperado->value => 'Cooperado',
			self::Estagiario->value => 'Estagiário',
			self::MenorAprendiz->value => 'Menor Aprendiz',
			self::Autonomo->value => 'Autônomo',
			self::Temporario->value => 'Temporário',
			self::Intermitente->value => 'Intermitente',
			self::TrabalhoRemoto->value => 'Trabalho Remoto',
			self::TrabalhoHibrido->value => 'Trabalho Híbrido',
			self::Terceirizado->value => 'Terceirizado',

			// International
			self::FullTime->value => 'Tempo Integral',
			self::PartTime->value => 'Meio Período',
			self::Permanent->value => 'Efetivo',
			self::FixedTerm->value => 'Prazo Determinado',
			self::Temporary->value => 'Temporário',
			self::Contract->value => 'Contrato',
			self::Casual->value => 'Eventual',
			self::Seasonal->value => 'Sazonal',
			self::ZeroHours->value => 'Horas Zero',
			self::Intern->value => 'Estagiário',
			self::Apprentice->value => 'Aprendiz',
			self::Trainee->value => 'Trainee',
			self::Volunteer->value => 'Voluntário',
			self::Consultant->value => 'Consultor',
			self::Contractor->value => 'Contratado',
			self::SelfEmployed->value => 'Autônomo',
			self::Agency->value => 'Agência',
			self::Gig->value => 'Gig Worker',
			self::Remote->value => 'Remoto',
			self::Hybrid->value => 'Híbrido',
			self::OnCall->value => 'Sob Demanda',
			self::ProjectBased->value => 'Por Projeto',
			self::CommissionOnly->value => 'Somente Comissão',
			self::Probationary->value => 'Período de Experiência',
			self::Executive->value => 'Executivo',
			self::Union->value => 'Sindicalizado',
			self::Government->value => 'Governo',
			self::Military->value => 'Militar',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			// Brazilian
			self::CLT->value => 'CLT (Empleo Formal)',
			self::PJ->value => 'PJ (Persona Jurídica)',
			self::MEI->value => 'MEI (Microemprendedor Individual)',
			self::Freelancer->value => 'Freelancer',
			self::Society->value => 'Sociedad/Asociación',
			self::Cooperado->value => 'Cooperado',
			self::Estagiario->value => 'Pasante',
			self::MenorAprendiz->value => 'Aprendiz Joven',
			self::Autonomo->value => 'Autónomo',
			self::Temporario->value => 'Temporal',
			self::Intermitente->value => 'Intermitente',
			self::TrabalhoRemoto->value => 'Trabajo Remoto',
			self::TrabalhoHibrido->value => 'Trabajo Híbrido',
			self::Terceirizado->value => 'Subcontratado',

			// International
			self::FullTime->value => 'Tiempo Completo',
			self::PartTime->value => 'Medio Tiempo',
			self::Permanent->value => 'Permanente',
			self::FixedTerm->value => 'Plazo Fijo',
			self::Temporary->value => 'Temporal',
			self::Contract->value => 'Contrato',
			self::Casual->value => 'Eventual',
			self::Seasonal->value => 'Estacional',
			self::ZeroHours->value => 'Horas Cero',
			self::Intern->value => 'Pasante',
			self::Apprentice->value => 'Aprendiz',
			self::Trainee->value => 'Trainee',
			self::Volunteer->value => 'Voluntario',
			self::Consultant->value => 'Consultor',
			self::Contractor->value => 'Contratista',
			self::SelfEmployed->value => 'Autónomo',
			self::Agency->value => 'Agencia',
			self::Gig->value => 'Trabajador Gig',
			self::Remote->value => 'Remoto',
			self::Hybrid->value => 'Híbrido',
			self::OnCall->value => 'Disponible',
			self::ProjectBased->value => 'Por Proyecto',
			self::CommissionOnly->value => 'Solo Comisión',
			self::Probationary->value => 'Período de Prueba',
			self::Executive->value => 'Ejecutivo',
			self::Union->value => 'Sindicalizado',
			self::Government->value => 'Gubernamental',
			self::Military->value => 'Militar',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			// Brazilian
			self::CLT->value => 'CLT (Formale Anstellung)',
			self::PJ->value => 'PJ (Juristische Person)',
			self::MEI->value => 'MEI (Einzelunternehmer)',
			self::Freelancer->value => 'Freelancer',
			self::Society->value => 'Gesellschaft/Partnerschaft',
			self::Cooperado->value => 'Genossenschaftsmitglied',
			self::Estagiario->value => 'Praktikant',
			self::MenorAprendiz->value => 'Jugendlicher Auszubildender',
			self::Autonomo->value => 'Selbständig',
			self::Temporario->value => 'Zeitlich befristet',
			self::Intermitente->value => 'Intermittierend',
			self::TrabalhoRemoto->value => 'Remote-Arbeit',
			self::TrabalhoHibrido->value => 'Hybrid-Arbeit',
			self::Terceirizado->value => 'Ausgelagert',

			// International
			self::FullTime->value => 'Vollzeit',
			self::PartTime->value => 'Teilzeit',
			self::Permanent->value => 'Unbefristet',
			self::FixedTerm->value => 'Befristet',
			self::Temporary->value => 'Zeitarbeit',
			self::Contract->value => 'Vertrag',
			self::Casual->value => 'Gelegentlich',
			self::Seasonal->value => 'Saisonal',
			self::ZeroHours->value => 'Null-Stunden',
			self::Intern->value => 'Praktikant',
			self::Apprentice->value => 'Auszubildender',
			self::Trainee->value => 'Trainee',
			self::Volunteer->value => 'Freiwillig',
			self::Consultant->value => 'Berater',
			self::Contractor->value => 'Vertragsarbeiter',
			self::SelfEmployed->value => 'Selbständig',
			self::Agency->value => 'Agentur',
			self::Gig->value => 'Gig-Arbeiter',
			self::Remote->value => 'Remote',
			self::Hybrid->value => 'Hybrid',
			self::OnCall->value => 'Bereitschaft',
			self::ProjectBased->value => 'Projektbasiert',
			self::CommissionOnly->value => 'Nur Provision',
			self::Probationary->value => 'Probezeit',
			self::Executive->value => 'Führungskraft',
			self::Union->value => 'Gewerkschaft',
			self::Government->value => 'Regierung',
			self::Military->value => 'Militär',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			// Brazilian
			self::CLT->value => 'CLT (Emploi Formel)',
			self::PJ->value => 'PJ (Personne Morale)',
			self::MEI->value => 'MEI (Micro-entrepreneur)',
			self::Freelancer->value => 'Freelance',
			self::Society->value => 'Société/Partenariat',
			self::Cooperado->value => 'Coopérateur',
			self::Estagiario->value => 'Stagiaire',
			self::MenorAprendiz->value => 'Jeune Apprenti',
			self::Autonomo->value => 'Indépendant',
			self::Temporario->value => 'Temporaire',
			self::Intermitente->value => 'Intermittent',
			self::TrabalhoRemoto->value => 'Télétravail',
			self::TrabalhoHibrido->value => 'Travail Hybride',
			self::Terceirizado->value => 'Externalisé',

			// International
			self::FullTime->value => 'Temps Plein',
			self::PartTime->value => 'Temps Partiel',
			self::Permanent->value => 'Permanent',
			self::FixedTerm->value => 'Durée Déterminée',
			self::Temporary->value => 'Temporaire',
			self::Contract->value => 'Contrat',
			self::Casual->value => 'Occasionnel',
			self::Seasonal->value => 'Saisonnier',
			self::ZeroHours->value => 'Zéro Heure',
			self::Intern->value => 'Stagiaire',
			self::Apprentice->value => 'Apprenti',
			self::Trainee->value => 'Trainee',
			self::Volunteer->value => 'Bénévole',
			self::Consultant->value => 'Consultant',
			self::Contractor->value => 'Contractant',
			self::SelfEmployed->value => 'Indépendant',
			self::Agency->value => 'Agence',
			self::Gig->value => 'Travailleur Gig',
			self::Remote->value => 'Télétravail',
			self::Hybrid->value => 'Hybride',
			self::OnCall->value => 'Disponible',
			self::ProjectBased->value => 'Par Projet',
			self::CommissionOnly->value => 'Commission Seule',
			self::Probationary->value => 'Période d\'Essai',
			self::Executive->value => 'Cadre',
			self::Union->value => 'Syndiqué',
			self::Government->value => 'Gouvernement',
			self::Military->value => 'Militaire',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			// Brazilian
			self::CLT->value => 'CLT (Impiego Formale)',
			self::PJ->value => 'PJ (Persona Giuridica)',
			self::MEI->value => 'MEI (Microimprenditore Individuale)',
			self::Freelancer->value => 'Freelance',
			self::Society->value => 'Società/Associazione',
			self::Cooperado->value => 'Cooperative',
			self::Estagiario->value => 'Tirocinante',
			self::MenorAprendiz->value => 'Giovane Apprendista',
			self::Autonomo->value => 'Autonomo',
			self::Temporario->value => 'Temporaneo',
			self::Intermitente->value => 'Intermittente',
			self::TrabalhoRemoto->value => 'Lavoro Remoto',
			self::TrabalhoHibrido->value => 'Lavoro Ibrido',
			self::Terceirizado->value => 'In Outsourcing',

			// International
			self::FullTime->value => 'Tempo Pieno',
			self::PartTime->value => 'Part-Time',
			self::Permanent->value => 'Permanente',
			self::FixedTerm->value => 'Tempo Determinato',
			self::Temporary->value => 'Temporaneo',
			self::Contract->value => 'Contratto',
			self::Casual->value => 'Occasionale',
			self::Seasonal->value => 'Stagionale',
			self::ZeroHours->value => 'Zero Ore',
			self::Intern->value => 'Tirocinante',
			self::Apprentice->value => 'Apprendista',
			self::Trainee->value => 'Trainee',
			self::Volunteer->value => 'Volontario',
			self::Consultant->value => 'Consulente',
			self::Contractor->value => 'Contraente',
			self::SelfEmployed->value => 'Autonomo',
			self::Agency->value => 'Agenzia',
			self::Gig->value => 'Lavoratore Gig',
			self::Remote->value => 'Remoto',
			self::Hybrid->value => 'Ibrido',
			self::OnCall->value => 'Disponibile',
			self::ProjectBased->value => 'A Progetto',
			self::CommissionOnly->value => 'Solo Commissione',
			self::Probationary->value => 'Periodo di Prova',
			self::Executive->value => 'Dirigente',
			self::Union->value => 'Sindacalizzato',
			self::Government->value => 'Governo',
			self::Military->value => 'Militare',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			// Brazilian
			self::CLT->value => 'CLT (Formeel Werk)',
			self::PJ->value => 'PJ (Rechtspersoon)',
			self::MEI->value => 'MEI (ZZP\'er)',
			self::Freelancer->value => 'Freelancer',
			self::Society->value => 'Vennootschap/Partnerschap',
			self::Cooperado->value => 'Coöperatielid',
			self::Estagiario->value => 'Stagiair',
			self::MenorAprendiz->value => 'Jonge Leerling',
			self::Autonomo->value => 'Zelfstandige',
			self::Temporario->value => 'Tijdelijk',
			self::Intermitente->value => 'Intermittent',
			self::TrabalhoRemoto->value => 'Remote Werk',
			self::TrabalhoHibrido->value => 'Hybride Werk',
			self::Terceirizado->value => 'Uitbesteed',

			// International
			self::FullTime->value => 'Fulltime',
			self::PartTime->value => 'Parttime',
			self::Permanent->value => 'Vast',
			self::FixedTerm->value => 'Bepaalde Tijd',
			self::Temporary->value => 'Tijdelijk',
			self::Contract->value => 'Contract',
			self::Casual->value => 'Incidenteel',
			self::Seasonal->value => 'Seizoensgebonden',
			self::ZeroHours->value => 'Nul Uren',
			self::Intern->value => 'Stagiair',
			self::Apprentice->value => 'Leerling',
			self::Trainee->value => 'Trainee',
			self::Volunteer->value => 'Vrijwilliger',
			self::Consultant->value => 'Consultant',
			self::Contractor->value => 'Contractant',
			self::SelfEmployed->value => 'Zelfstandige',
			self::Agency->value => 'Uitzendbureau',
			self::Gig->value => 'Gig Worker',
			self::Remote->value => 'Remote',
			self::Hybrid->value => 'Hybride',
			self::OnCall->value => 'Beschikbaar',
			self::ProjectBased->value => 'Projectbasis',
			self::CommissionOnly->value => 'Alleen Commissie',
			self::Probationary->value => 'Proeftijd',
			self::Executive->value => 'Directeur',
			self::Union->value => 'Vakbond',
			self::Government->value => 'Overheid',
			self::Military->value => 'Militair',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			// Brazilian
			self::CLT->value => 'CLT (Formalne Zatrudnienie)',
			self::PJ->value => 'PJ (Osoba Prawna)',
			self::MEI->value => 'MEI (Mikroprzedsiębiorca)',
			self::Freelancer->value => 'Freelancer',
			self::Society->value => 'Spółka/Partnerstwo',
			self::Cooperado->value => 'Członek Spółdzielni',
			self::Estagiario->value => 'Stażysta',
			self::MenorAprendiz->value => 'Młody Uczeń',
			self::Autonomo->value => 'Samozatrudniony',
			self::Temporario->value => 'Tymczasowy',
			self::Intermitente->value => 'Intermittent',
			self::TrabalhoRemoto->value => 'Praca Zdalna',
			self::TrabalhoHibrido->value => 'Praca Hybrydowa',
			self::Terceirizado->value => 'Zlecone',

			// International
			self::FullTime->value => 'Pełny Etat',
			self::PartTime->value => 'Część Etatu',
			self::Permanent->value => 'Stałe',
			self::FixedTerm->value => 'Określony Czas',
			self::Temporary->value => 'Tymczasowe',
			self::Contract->value => 'Umowa',
			self::Casual->value => 'Okazjonalne',
			self::Seasonal->value => 'Sezonowe',
			self::ZeroHours->value => 'Zero Godzin',
			self::Intern->value => 'Stażysta',
			self::Apprentice->value => 'Uczeń',
			self::Trainee->value => 'Praktykant',
			self::Volunteer->value => 'Wolontariusz',
			self::Consultant->value => 'Konsultant',
			self::Contractor->value => 'Kontrahent',
			self::SelfEmployed->value => 'Samozatrudniony',
			self::Agency->value => 'Agencja',
			self::Gig->value => 'Gig Worker',
			self::Remote->value => 'Zdalnie',
			self::Hybrid->value => 'Hybrydowo',
			self::OnCall->value => 'Dostępny',
			self::ProjectBased->value => 'Projektowe',
			self::CommissionOnly->value => 'Tylko Prowizja',
			self::Probationary->value => 'Okres Próbny',
			self::Executive->value => 'Kierownicze',
			self::Union->value => 'Związek Zawodowy',
			self::Government->value => 'Rząd',
			self::Military->value => 'Wojsko',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			// Brazilian
			self::CLT->value => 'CLT (Официальная Работа)',
			self::PJ->value => 'PJ (Юридическое Лицо)',
			self::MEI->value => 'MEI (Индивидуальный Предприниматель)',
			self::Freelancer->value => 'Фрилансер',
			self::Society->value => 'Общество/Партнерство',
			self::Cooperado->value => 'Член Кооператива',
			self::Estagiario->value => 'Стажер',
			self::MenorAprendiz->value => 'Молодой Ученик',
			self::Autonomo->value => 'Самозанятый',
			self::Temporario->value => 'Временный',
			self::Intermitente->value => 'Периодический',
			self::TrabalhoRemoto->value => 'Удаленная Работа',
			self::TrabalhoHibrido->value => 'Гибридная Работа',
			self::Terceirizado->value => 'Аутсорсинг',

			// International
			self::FullTime->value => 'Полный Рабочий День',
			self::PartTime->value => 'Неполный Рабочий День',
			self::Permanent->value => 'Постоянный',
			self::FixedTerm->value => 'Срочный',
			self::Temporary->value => 'Временный',
			self::Contract->value => 'Контракт',
			self::Casual->value => 'Случайный',
			self::Seasonal->value => 'Сезонный',
			self::ZeroHours->value => 'Нулевые Часы',
			self::Intern->value => 'Стажер',
			self::Apprentice->value => 'Ученик',
			self::Trainee->value => 'Стажер',
			self::Volunteer->value => 'Волонтер',
			self::Consultant->value => 'Консультант',
			self::Contractor->value => 'Подрядчик',
			self::SelfEmployed->value => 'Самозанятый',
			self::Agency->value => 'Агентство',
			self::Gig->value => 'Гиг Работник',
			self::Remote->value => 'Удаленно',
			self::Hybrid->value => 'Гибрид',
			self::OnCall->value => 'Дежурный',
			self::ProjectBased->value => 'Проектная',
			self::CommissionOnly->value => 'Только Комиссия',
			self::Probationary->value => 'Испытательный Срок',
			self::Executive->value => 'Руководящий',
			self::Union->value => 'Профсоюз',
			self::Government->value => 'Правительство',
			self::Military->value => 'Военные',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			// Brazilian
			self::CLT->value => 'CLT (Resmi İstihdam)',
			self::PJ->value => 'PJ (Tüzel Kişi)',
			self::MEI->value => 'MEI (Mikro Girişimci)',
			self::Freelancer->value => 'Freelancer',
			self::Society->value => 'Şirket/Ortaklık',
			self::Cooperado->value => 'Kooperatif Üyesi',
			self::Estagiario->value => 'Stajyer',
			self::MenorAprendiz->value => 'Genç Çırak',
			self::Autonomo->value => 'Serbest Çalışan',
			self::Temporario->value => 'Geçici',
			self::Intermitente->value => 'Aralıklı',
			self::TrabalhoRemoto->value => 'Uzaktan Çalışma',
			self::TrabalhoHibrido->value => 'Hibrit Çalışma',
			self::Terceirizado->value => 'Dış Kaynak',

			// International
			self::FullTime->value => 'Tam Zamanlı',
			self::PartTime->value => 'Yarı Zamanlı',
			self::Permanent->value => 'Sürekli',
			self::FixedTerm->value => 'Belirli Süreli',
			self::Temporary->value => 'Geçici',
			self::Contract->value => 'Sözleşme',
			self::Casual->value => 'Gündelik',
			self::Seasonal->value => 'Mevsimlik',
			self::ZeroHours->value => 'Sıfır Saat',
			self::Intern->value => 'Stajyer',
			self::Apprentice->value => 'Çırak',
			self::Trainee->value => 'Trainee',
			self::Volunteer->value => 'Gönüllü',
			self::Consultant->value => 'Danışman',
			self::Contractor->value => 'Müteahhit',
			self::SelfEmployed->value => 'Serbest Çalışan',
			self::Agency->value => 'Ajans',
			self::Gig->value => 'Gig İşçi',
			self::Remote->value => 'Uzaktan',
			self::Hybrid->value => 'Hibrit',
			self::OnCall->value => 'Nöbetçi',
			self::ProjectBased->value => 'Proje Bazlı',
			self::CommissionOnly->value => 'Sadece Komisyon',
			self::Probationary->value => 'Deneme Süresi',
			self::Executive->value => 'Yönetici',
			self::Union->value => 'Sendika',
			self::Government->value => 'Devlet',
			self::Military->value => 'Askeri',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			// Brazilian
			self::CLT->value => 'CLT (توظيف رسمي)',
			self::PJ->value => 'PJ (كيان قانوني)',
			self::MEI->value => 'MEI (صاحب عمل فردي)',
			self::Freelancer->value => 'مستقل',
			self::Society->value => 'شراكة/مجتمع',
			self::Cooperado->value => 'عضو تعاوني',
			self::Estagiario->value => 'متدرب',
			self::MenorAprendiz->value => 'متعلم صغير',
			self::Autonomo->value => 'يعمل لحسابه الخاص',
			self::Temporario->value => 'مؤقت',
			self::Intermitente->value => 'متقطع',
			self::TrabalhoRemoto->value => 'عمل عن بعد',
			self::TrabalhoHibrido->value => 'عمل هجين',
			self::Terceirizado->value => 'مصدر خارجي',

			// International
			self::FullTime->value => 'دوام كامل',
			self::PartTime->value => 'دوام جزئي',
			self::Permanent->value => 'دائم',
			self::FixedTerm->value => 'مدة محددة',
			self::Temporary->value => 'مؤقت',
			self::Contract->value => 'عقد',
			self::Casual->value => 'عرضي',
			self::Seasonal->value => 'موسمي',
			self::ZeroHours->value => 'ساعات صفرية',
			self::Intern->value => 'متدرب',
			self::Apprentice->value => 'متدرب',
			self::Trainee->value => 'متدرب',
			self::Volunteer->value => 'متطوع',
			self::Consultant->value => 'مستشار',
			self::Contractor->value => 'مقاول',
			self::SelfEmployed->value => 'يعمل لحسابه الخاص',
			self::Agency->value => 'وكالة',
			self::Gig->value => 'عامل جيج',
			self::Remote->value => 'عن بعد',
			self::Hybrid->value => 'هجين',
			self::OnCall->value => 'على الطلب',
			self::ProjectBased->value => 'قائم على المشروع',
			self::CommissionOnly->value => 'عمولة فقط',
			self::Probationary->value => 'فترة تجريبية',
			self::Executive->value => 'تنفيذي',
			self::Union->value => 'نقابة',
			self::Government->value => 'حكومة',
			self::Military->value => 'عسكري',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			// Brazilian
			self::CLT->value => 'CLT (תעסוקה רשמית)',
			self::PJ->value => 'PJ (ישות משפטית)',
			self::MEI->value => 'MEI (יזם מיקרו)',
			self::Freelancer->value => 'פרילנסר',
			self::Society->value => 'שותפות/חברה',
			self::Cooperado->value => 'חבר קואופרטיב',
			self::Estagiario->value => 'סטודנט',
			self::MenorAprendiz->value => 'שוליה צעיר',
			self::Autonomo->value => 'עצמאי',
			self::Temporario->value => 'זמני',
			self::Intermitente->value => 'מקוטע',
			self::TrabalhoRemoto->value => 'עבודה מרחוק',
			self::TrabalhoHibrido->value => 'עבודה היברידית',
			self::Terceirizado->value => 'מיקור חוץ',

			// International
			self::FullTime->value => 'משרה מלאה',
			self::PartTime->value => 'משרה חלקית',
			self::Permanent->value => 'קבוע',
			self::FixedTerm->value => 'מוגבל בזמן',
			self::Temporary->value => 'זמני',
			self::Contract->value => 'חוזה',
			self::Casual->value => 'מזדמן',
			self::Seasonal->value => 'עונתי',
			self::ZeroHours->value => 'אפס שעות',
			self::Intern->value => 'סטודנט',
			self::Apprentice->value => 'שוליה',
			self::Trainee->value => 'מתלמד',
			self::Volunteer->value => 'מתנדב',
			self::Consultant->value => 'יועץ',
			self::Contractor->value => 'קבלן',
			self::SelfEmployed->value => 'עצמאי',
			self::Agency->value => 'סוכנות',
			self::Gig->value => 'עובד ג\'יג',
			self::Remote->value => 'מרחוק',
			self::Hybrid->value => 'היברידי',
			self::OnCall->value => 'זמין',
			self::ProjectBased->value => 'מבוסס פרויקט',
			self::CommissionOnly->value => 'עמלה בלבד',
			self::Probationary->value => 'תקופת ניסיון',
			self::Executive->value => 'הנהלה',
			self::Union->value => 'איגוד מקצועי',
			self::Government->value => 'ממשלתי',
			self::Military->value => 'צבאי',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			// Brazilian
			self::CLT->value => 'CLT (正式雇用)',
			self::PJ->value => 'PJ (法人)',
			self::MEI->value => 'MEI (個人事業主)',
			self::Freelancer->value => 'フリーランス',
			self::Society->value => 'パートナーシップ',
			self::Cooperado->value => '協同組合員',
			self::Estagiario->value => 'インターン',
			self::MenorAprendiz->value => '若年見習い',
			self::Autonomo->value => '自営業',
			self::Temporario->value => '一時的',
			self::Intermitente->value => '断続的',
			self::TrabalhoRemoto->value => 'リモートワーク',
			self::TrabalhoHibrido->value => 'ハイブリッドワーク',
			self::Terceirizado->value => 'アウトソーシング',

			// International
			self::FullTime->value => 'フルタイム',
			self::PartTime->value => 'パートタイム',
			self::Permanent->value => '永久',
			self::FixedTerm->value => '有期',
			self::Temporary->value => '一時的',
			self::Contract->value => '契約',
			self::Casual->value => '臨時',
			self::Seasonal->value => '季節的',
			self::ZeroHours->value => 'ゼロ時間',
			self::Intern->value => 'インターン',
			self::Apprentice->value => '見習い',
			self::Trainee->value => '研修生',
			self::Volunteer->value => 'ボランティア',
			self::Consultant->value => 'コンサルタント',
			self::Contractor->value => '請負業者',
			self::SelfEmployed->value => '自営業',
			self::Agency->value => 'エージェンシー',
			self::Gig->value => 'ギグワーカー',
			self::Remote->value => 'リモート',
			self::Hybrid->value => 'ハイブリッド',
			self::OnCall->value => 'オンコール',
			self::ProjectBased->value => 'プロジェクトベース',
			self::CommissionOnly->value => 'コミッションのみ',
			self::Probationary->value => '試用期間',
			self::Executive->value => 'エグゼクティブ',
			self::Union->value => '組合',
			self::Government->value => '政府',
			self::Military->value => '軍事',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			// Brazilian
			self::CLT->value => 'CLT (Formelt Arbejde)',
			self::PJ->value => 'PJ (Juridisk Enhed)',
			self::MEI->value => 'MEI (Mikroiværksætter)',
			self::Freelancer->value => 'Freelancer',
			self::Society->value => 'Selskab/Partnerskab',
			self::Cooperado->value => 'Andelshaver',
			self::Estagiario->value => 'Praktikant',
			self::MenorAprendiz->value => 'Ung Lærling',
			self::Autonomo->value => 'Selvstændig',
			self::Temporario->value => 'Midlertidig',
			self::Intermitente->value => 'Intermittent',
			self::TrabalhoRemoto->value => 'Fjernarbejde',
			self::TrabalhoHibrido->value => 'Hybrid Arbejde',
			self::Terceirizado->value => 'Udlejet',

			// International
			self::FullTime->value => 'Fuldtid',
			self::PartTime->value => 'Deltid',
			self::Permanent->value => 'Fastansat',
			self::FixedTerm->value => 'Tidsbegrænset',
			self::Temporary->value => 'Midlertidig',
			self::Contract->value => 'Kontrakt',
			self::Casual->value => 'Lejlighedsvis',
			self::Seasonal->value => 'Sæson',
			self::ZeroHours->value => 'Nul Timer',
			self::Intern->value => 'Praktikant',
			self::Apprentice->value => 'Lærling',
			self::Trainee->value => 'Trainee',
			self::Volunteer->value => 'Frivillig',
			self::Consultant->value => 'Konsulent',
			self::Contractor->value => 'Entrepreneur',
			self::SelfEmployed->value => 'Selvstændig',
			self::Agency->value => 'Bureau',
			self::Gig->value => 'Gig Arbejder',
			self::Remote->value => 'Fjern',
			self::Hybrid->value => 'Hybrid',
			self::OnCall->value => 'Tilkaldbar',
			self::ProjectBased->value => 'Projektbaseret',
			self::CommissionOnly->value => 'Kun Provision',
			self::Probationary->value => 'Prøveperiode',
			self::Executive->value => 'Direktion',
			self::Union->value => 'Fagforening',
			self::Government->value => 'Regering',
			self::Military->value => 'Militær',
		];
	}

	// Chinese Labels
	public static function labelsZh(): array
	{
		return [
			// Brazilian
			self::CLT->value => 'CLT (正式雇佣)',
			self::PJ->value => 'PJ (法人实体)',
			self::MEI->value => 'MEI (个体经营者)',
			self::Freelancer->value => '自由职业者',
			self::Society->value => '合伙/公司',
			self::Cooperado->value => '合作社成员',
			self::Estagiario->value => '实习生',
			self::MenorAprendiz->value => '青年学徒',
			self::Autonomo->value => '自雇',
			self::Temporario->value => '临时',
			self::Intermitente->value => '间歇性',
			self::TrabalhoRemoto->value => '远程工作',
			self::TrabalhoHibrido->value => '混合工作',
			self::Terceirizado->value => '外包',

			// International
			self::FullTime->value => '全职',
			self::PartTime->value => '兼职',
			self::Permanent->value => '永久',
			self::FixedTerm->value => '固定期限',
			self::Temporary->value => '临时',
			self::Contract->value => '合同',
			self::Casual->value => '临时工',
			self::Seasonal->value => '季节性',
			self::ZeroHours->value => '零工时',
			self::Intern->value => '实习生',
			self::Apprentice->value => '学徒',
			self::Trainee->value => '培训生',
			self::Volunteer->value => '志愿者',
			self::Consultant->value => '顾问',
			self::Contractor->value => '承包商',
			self::SelfEmployed->value => '自雇',
			self::Agency->value => '代理',
			self::Gig->value => '零工',
			self::Remote->value => '远程',
			self::Hybrid->value => '混合',
			self::OnCall->value => '待命',
			self::ProjectBased->value => '项目制',
			self::CommissionOnly->value => '仅佣金',
			self::Probationary->value => '试用期',
			self::Executive->value => '高管',
			self::Union->value => '工会',
			self::Government->value => '政府',
			self::Military->value => '军事',
		];
	}
}
