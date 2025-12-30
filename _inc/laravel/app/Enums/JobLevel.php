<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum JobLevel: string
{
	case Internship = 'internship';
	case EntryLevel = 'entry_level';
	case Junior = 'junior';
	case Associate = 'associate';
	case MidLevel = 'mid_level';
	case Senior = 'senior';
	case Lead = 'lead';
	case Staff = 'staff';
	case Principal = 'principal';
	case Manager = 'manager';
	case SeniorManager = 'senior_manager';
	case Director = 'director';
	case SeniorDirector = 'senior_director';
	case VicePresident = 'vice_president';
	case SeniorVicePresident = 'senior_vice_president';
	case ExecutiveVicePresident = 'executive_vice_president';
	case Architect = 'architect';
	case SeniorArchitect = 'senior_architect';
	case PrincipalArchitect = 'principal_architect';
	case Chief = 'chief';
	case ChiefOfficer = 'chief_officer';
	case ChiefExecutiveOfficer = 'chief_executive_officer';
	case ChiefTechnologyOfficer = 'chief_technology_officer';
	case ChiefFinancialOfficer = 'chief_financial_officer';
	case ChiefOperatingOfficer = 'chief_operating_officer';
	case ChiefMarketingOfficer = 'chief_marketing_officer';
	case ChiefInformationOfficer = 'chief_information_officer';
	case President = 'president';
	case Partner = 'partner';
	case SeniorPartner = 'senior_partner';
	case BoardMember = 'board_member';
	case Chairman = 'chairman';
	case Founder = 'founder';
	case CoFounder = 'co_founder';
	case Owner = 'owner';
	case Contractor = 'contractor';
	case Freelancer = 'freelancer';
	case Consultant = 'consultant';
	case SeniorConsultant = 'senior_consultant';

	/**
	 * Normalize input to JobLevel
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
			// Internship/Trainee
			'internship', 'intern', 'estagio', 'estagiario' => self::Internship,
			'entrylevel', 'entrylevel', 'entry', 'iniciante' => self::EntryLevel,

			// Junior levels
			'junior', 'jr', 'assistant' => self::Junior,
			'associate', 'associado', 'asociado' => self::Associate,

			// Mid to Senior
			'midlevel', 'mid', 'intermediate', 'intermedio' => self::MidLevel,
			'senior', 'sr', 'sen', 'experiente' => self::Senior,
			'lead', 'lider', 'leader' => self::Lead,
			'staff', 'especialista' => self::Staff,
			'principal', 'especialistasenior' => self::Principal,

			// Management
			'manager', 'gerente', 'gestor' => self::Manager,
			'seniormanager', 'gerentesenior' => self::SeniorManager,
			'director', 'diretor', 'direccion' => self::Director,
			'seniordirector', 'diretorsenior' => self::SeniorDirector,

			// Executive
			'vicepresident', 'vp', 'vicepresidente' => self::VicePresident,
			'seniorvicepresident', 'svp', 'vicepresidentesenior' => self::SeniorVicePresident,
			'executivevicepresident', 'evp', 'vicepresidenteexecutivo' => self::ExecutiveVicePresident,

			// Architecture/Technical Leadership
			'architect', 'arquiteto', 'arquitecto' => self::Architect,
			'seniorarchitect', 'arquitetosenior' => self::SeniorArchitect,
			'principalarchitect', 'arquitetoprincipal' => self::PrincipalArchitect,

			// C-Level
			'chief', 'chefe', 'jefe' => self::Chief,
			'chiefofficer', 'co' => self::ChiefOfficer,
			'ceo', 'chiefexecutiveofficer', 'diretorgeral', 'directorgeneral' => self::ChiefExecutiveOfficer,
			'cto', 'chieftechnologyofficer', 'diretorcto', 'directortecnologia' => self::ChiefTechnologyOfficer,
			'cfo', 'chieffinancialofficer', 'diretorfinanceiro', 'directorfinanciero' => self::ChiefFinancialOfficer,
			'coo', 'chiefoperatingofficer', 'diretoroperacoes', 'directoroperaciones' => self::ChiefOperatingOfficer,
			'cmo', 'chiefmarketingofficer', 'diretorMarketing', 'directormarketing' => self::ChiefMarketingOfficer,
			'cio', 'chiefinformationofficer', 'diretorTI', 'directorTI' => self::ChiefInformationOfficer,

			// Top Leadership
			'president', 'presidente' => self::President,
			'partner', 'socio', 'parceiro' => self::Partner,
			'seniorpartner', 'sociosenior' => self::SeniorPartner,
			'boardmember', 'conselheiro', 'miembroconsejo' => self::BoardMember,
			'chairman', 'presidenteconselho', 'presidenteconsejo' => self::Chairman,

			// Ownership/Founder
			'founder', 'fundador', 'fundadora' => self::Founder,
			'cofounder', 'cofundador', 'cofundadora' => self::CoFounder,
			'owner', 'dono', 'proprietario', 'propietario' => self::Owner,

			// Contract/Freelance
			'contractor', 'contratado', 'contratista' => self::Contractor,
			'freelancer', 'freelance', 'autonomo', 'autonomo' => self::Freelancer,

			// Consulting
			'consultant', 'consultor', 'asesor' => self::Consultant,
			'seniorconsultant', 'consultorsenior' => self::SeniorConsultant,

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
	 * Get label for this job level in specified language
	 */
	public function label($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$labels = self::labels($lang);
		return $labels[$this->value] ?? ucfirst(str_replace('_', ' ', $this->value));
	}

	/**
	 * Get the hierarchy level for sorting (higher = more senior)
	 */
	public function getHierarchyLevel(): int
	{
		return match ($this) {
			// Level 1-3: Entry/Junior
			self::Internship => 1,
			self::EntryLevel => 2,
			self::Junior => 3,

			// Level 4-6: Mid-Level
			self::Associate => 4,
			self::MidLevel => 5,
			self::Senior => 6,

			// Level 7-9: Senior/Lead
			self::Lead => 7,
			self::Staff => 8,
			self::Principal => 9,
			self::Architect => 9,

			// Level 10-12: Management
			self::Manager => 10,
			self::SeniorManager => 11,
			self::Director => 12,

			// Level 13-15: Senior Management
			self::SeniorDirector => 13,
			self::VicePresident => 14,
			self::SeniorVicePresident => 15,

			// Level 16-18: Executive
			self::ExecutiveVicePresident => 16,
			self::SeniorArchitect => 17,
			self::PrincipalArchitect => 18,

			// Level 19-21: C-Level
			self::Chief => 19,
			self::ChiefOfficer => 20,
			self::ChiefInformationOfficer => 21,
			self::ChiefMarketingOfficer => 21,
			self::ChiefOperatingOfficer => 22,
			self::ChiefFinancialOfficer => 23,
			self::ChiefTechnologyOfficer => 24,
			self::ChiefExecutiveOfficer => 25,

			// Level 26-28: Top Leadership
			self::President => 26,
			self::Partner => 27,
			self::SeniorPartner => 28,

			// Level 29-31: Board/Ownership
			self::BoardMember => 29,
			self::Chairman => 30,
			self::Founder, self::CoFounder, self::Owner => 31,

			// Contract/Freelance (variable based on experience)
			self::Contractor => 6, // Usually Senior level
			self::Freelancer => 6, // Usually Senior level
			self::Consultant => 8, // Usually Staff level
			self::SeniorConsultant => 12, // Usually Director level
		};
	}

	/**
	 * Get color for UI display
	 */
	public function getColor(): string
	{
		$level = $this->getHierarchyLevel();

		if ($level <= 3) {
			return '#6b7280'; // Gray for junior
		} elseif ($level <= 6) {
			return '#10b981'; // Green for mid-level
		} elseif ($level <= 12) {
			return '#3b82f6'; // Blue for senior/management
		} elseif ($level <= 18) {
			return '#8b5cf6'; // Purple for executive
		} else {
			return '#ef4444'; // Red for C-level/ownership
		}
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match (true) {
			// Junior/Entry
			$this === self::Internship => 'user-graduate',
			in_array($this, [self::EntryLevel, self::Junior, self::Associate]) => 'user',

			// Mid/Senior Individual Contributor
			in_array($this, [self::MidLevel, self::Senior, self::Lead, self::Staff, self::Principal]) => 'user-tie',

			// Technical Leadership
			in_array($this, [self::Architect, self::SeniorArchitect, self::PrincipalArchitect]) => 'cogs',

			// Management
			in_array($this, [self::Manager, self::SeniorManager]) => 'briefcase',

			// Director/Executive
			in_array($this, [self::Director, self::SeniorDirector, self::VicePresident, self::SeniorVicePresident, self::ExecutiveVicePresident]) => 'user-tie',

			// C-Level
			str_contains($this->value, 'chief') => 'crown',

			// Top Leadership
			in_array($this, [self::President, self::Partner, self::SeniorPartner, self::BoardMember, self::Chairman]) => 'user-ninja',

			// Ownership
			in_array($this, [self::Founder, self::CoFounder, self::Owner]) => 'user-astronaut',

			// Contract/Freelance
			in_array($this, [self::Contractor, self::Freelancer, self::Consultant, self::SeniorConsultant]) => 'user-clock',

			default => 'user',
		};
	}

	/**
	 * Check if this is an executive level position
	 */
	public function isExecutive(): bool
	{
		return $this->getHierarchyLevel() >= 16;
	}

	/**
	 * Check if this is a management position
	 */
	public function isManagement(): bool
	{
		return $this->getHierarchyLevel() >= 10;
	}

	/**
	 * Check if this is an individual contributor position
	 */
	public function isIndividualContributor(): bool
	{
		return $this->getHierarchyLevel() <= 9 && !in_array($this, [
			self::Contractor,
			self::Freelancer,
			self::Consultant,
			self::SeniorConsultant,
		]);
	}

	/**
	 * Check if this is a contract/freelance position
	 */
	public function isContractor(): bool
	{
		return in_array($this, [
			self::Contractor,
			self::Freelancer,
			self::Consultant,
			self::SeniorConsultant,
		]);
	}

	/**
	 * Get all C-level positions
	 */
	public static function getCLevelPositions(): array
	{
		return [
			self::Chief,
			self::ChiefOfficer,
			self::ChiefExecutiveOfficer,
			self::ChiefTechnologyOfficer,
			self::ChiefFinancialOfficer,
			self::ChiefOperatingOfficer,
			self::ChiefMarketingOfficer,
			self::ChiefInformationOfficer,
		];
	}

	/**
	 * Get all technical/engineering positions
	 */
	public static function getTechnicalPositions(): array
	{
		return [
			self::Junior,
			self::Associate,
			self::MidLevel,
			self::Senior,
			self::Lead,
			self::Staff,
			self::Principal,
			self::Architect,
			self::SeniorArchitect,
			self::PrincipalArchitect,
			self::ChiefTechnologyOfficer,
		];
	}

	/**
	 * Get simplified categories for filtering
	 */
	public function getCategory(): string
	{
		return match (true) {
			$this->isExecutive() => 'executive',
			$this->isManagement() => 'management',
			$this->isContractor() => 'contractor',
			default => 'individual',
		};
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			self::Internship->value => 'Internship',
			self::EntryLevel->value => 'Entry Level',
			self::Junior->value => 'Junior',
			self::Associate->value => 'Associate',
			self::MidLevel->value => 'Mid Level',
			self::Senior->value => 'Senior',
			self::Lead->value => 'Lead',
			self::Staff->value => 'Staff',
			self::Principal->value => 'Principal',
			self::Manager->value => 'Manager',
			self::SeniorManager->value => 'Senior Manager',
			self::Director->value => 'Director',
			self::SeniorDirector->value => 'Senior Director',
			self::VicePresident->value => 'Vice President',
			self::SeniorVicePresident->value => 'Senior Vice President',
			self::ExecutiveVicePresident->value => 'Executive Vice President',
			self::Architect->value => 'Architect',
			self::SeniorArchitect->value => 'Senior Architect',
			self::PrincipalArchitect->value => 'Principal Architect',
			self::Chief->value => 'Chief',
			self::ChiefOfficer->value => 'Chief Officer',
			self::ChiefExecutiveOfficer->value => 'Chief Executive Officer',
			self::ChiefTechnologyOfficer->value => 'Chief Technology Officer',
			self::ChiefFinancialOfficer->value => 'Chief Financial Officer',
			self::ChiefOperatingOfficer->value => 'Chief Operating Officer',
			self::ChiefMarketingOfficer->value => 'Chief Marketing Officer',
			self::ChiefInformationOfficer->value => 'Chief Information Officer',
			self::President->value => 'President',
			self::Partner->value => 'Partner',
			self::SeniorPartner->value => 'Senior Partner',
			self::BoardMember->value => 'Board Member',
			self::Chairman->value => 'Chairman',
			self::Founder->value => 'Founder',
			self::CoFounder->value => 'Co-Founder',
			self::Owner->value => 'Owner',
			self::Contractor->value => 'Contractor',
			self::Freelancer->value => 'Freelancer',
			self::Consultant->value => 'Consultant',
			self::SeniorConsultant->value => 'Senior Consultant',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			self::Internship->value => 'Estágio',
			self::EntryLevel->value => 'Nível Inicial',
			self::Junior->value => 'Júnior',
			self::Associate->value => 'Associado',
			self::MidLevel->value => 'Pleno',
			self::Senior->value => 'Sênior',
			self::Lead->value => 'Líder',
			self::Staff->value => 'Especialista',
			self::Principal->value => 'Principal',
			self::Manager->value => 'Gerente',
			self::SeniorManager->value => 'Gerente Sênior',
			self::Director->value => 'Diretor',
			self::SeniorDirector->value => 'Diretor Sênior',
			self::VicePresident->value => 'Vice-Presidente',
			self::SeniorVicePresident->value => 'Vice-Presidente Sênior',
			self::ExecutiveVicePresident->value => 'Vice-Presidente Executivo',
			self::Architect->value => 'Arquiteto',
			self::SeniorArchitect->value => 'Arquiteto Sênior',
			self::PrincipalArchitect->value => 'Arquiteto Principal',
			self::Chief->value => 'Chefe',
			self::ChiefOfficer->value => 'Diretor Executivo',
			self::ChiefExecutiveOfficer->value => 'Diretor Geral',
			self::ChiefTechnologyOfficer->value => 'Diretor de Tecnologia',
			self::ChiefFinancialOfficer->value => 'Diretor Financeiro',
			self::ChiefOperatingOfficer->value => 'Diretor de Operações',
			self::ChiefMarketingOfficer->value => 'Diretor de Marketing',
			self::ChiefInformationOfficer->value => 'Diretor de TI',
			self::President->value => 'Presidente',
			self::Partner->value => 'Sócio',
			self::SeniorPartner->value => 'Sócio Sênior',
			self::BoardMember->value => 'Conselheiro',
			self::Chairman->value => 'Presidente do Conselho',
			self::Founder->value => 'Fundador',
			self::CoFounder->value => 'Co-Fundador',
			self::Owner->value => 'Proprietário',
			self::Contractor->value => 'Contratado',
			self::Freelancer->value => 'Freelancer',
			self::Consultant->value => 'Consultor',
			self::SeniorConsultant->value => 'Consultor Sênior',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			self::Internship->value => 'Pasantía',
			self::EntryLevel->value => 'Nivel Inicial',
			self::Junior->value => 'Junior',
			self::Associate->value => 'Asociado',
			self::MidLevel->value => 'Medio',
			self::Senior->value => 'Senior',
			self::Lead->value => 'Líder',
			self::Staff->value => 'Especialista',
			self::Principal->value => 'Principal',
			self::Manager->value => 'Gerente',
			self::SeniorManager->value => 'Gerente Senior',
			self::Director->value => 'Director',
			self::SeniorDirector->value => 'Director Senior',
			self::VicePresident->value => 'Vicepresidente',
			self::SeniorVicePresident->value => 'Vicepresidente Senior',
			self::ExecutiveVicePresident->value => 'Vicepresidente Ejecutivo',
			self::Architect->value => 'Arquitecto',
			self::SeniorArchitect->value => 'Arquitecto Senior',
			self::PrincipalArchitect->value => 'Arquitecto Principal',
			self::Chief->value => 'Jefe',
			self::ChiefOfficer->value => 'Director Ejecutivo',
			self::ChiefExecutiveOfficer->value => 'Director General',
			self::ChiefTechnologyOfficer->value => 'Director de Tecnología',
			self::ChiefFinancialOfficer->value => 'Director Financiero',
			self::ChiefOperatingOfficer->value => 'Director de Operaciones',
			self::ChiefMarketingOfficer->value => 'Director de Marketing',
			self::ChiefInformationOfficer->value => 'Director de TI',
			self::President->value => 'Presidente',
			self::Partner->value => 'Socio',
			self::SeniorPartner->value => 'Socio Senior',
			self::BoardMember->value => 'Miembro del Consejo',
			self::Chairman->value => 'Presidente del Consejo',
			self::Founder->value => 'Fundador',
			self::CoFounder->value => 'Co-Fundador',
			self::Owner->value => 'Propietario',
			self::Contractor->value => 'Contratista',
			self::Freelancer->value => 'Freelance',
			self::Consultant->value => 'Consultor',
			self::SeniorConsultant->value => 'Consultor Senior',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			self::Internship->value => 'Praktikum',
			self::EntryLevel->value => 'Einsteiger',
			self::Junior->value => 'Junior',
			self::Associate->value => 'Mitarbeiter',
			self::MidLevel->value => 'Mittelstufe',
			self::Senior->value => 'Senior',
			self::Lead->value => 'Leiter',
			self::Staff->value => 'Mitarbeiter',
			self::Principal->value => 'Principal',
			self::Manager->value => 'Manager',
			self::SeniorManager->value => 'Senior Manager',
			self::Director->value => 'Direktor',
			self::SeniorDirector->value => 'Senior Direktor',
			self::VicePresident->value => 'Vizepräsident',
			self::SeniorVicePresident->value => 'Senior Vizepräsident',
			self::ExecutiveVicePresident->value => 'Executive Vizepräsident',
			self::Architect->value => 'Architekt',
			self::SeniorArchitect->value => 'Senior Architekt',
			self::PrincipalArchitect->value => 'Principal Architekt',
			self::Chief->value => 'Chef',
			self::ChiefOfficer->value => 'Leitender Angestellter',
			self::ChiefExecutiveOfficer->value => 'Geschäftsführer',
			self::ChiefTechnologyOfficer->value => 'Technischer Direktor',
			self::ChiefFinancialOfficer->value => 'Finanzvorstand',
			self::ChiefOperatingOfficer->value => 'Betriebsleiter',
			self::ChiefMarketingOfficer->value => 'Marketingvorstand',
			self::ChiefInformationOfficer->value => 'IT-Direktor',
			self::President->value => 'Präsident',
			self::Partner->value => 'Partner',
			self::SeniorPartner->value => 'Senior Partner',
			self::BoardMember->value => 'Vorstandsmitglied',
			self::Chairman->value => 'Vorsitzender',
			self::Founder->value => 'Gründer',
			self::CoFounder->value => 'Mitgründer',
			self::Owner->value => 'Inhaber',
			self::Contractor->value => 'Vertragsarbeiter',
			self::Freelancer->value => 'Freiberufler',
			self::Consultant->value => 'Berater',
			self::SeniorConsultant->value => 'Senior Berater',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			self::Internship->value => 'Stage',
			self::EntryLevel->value => 'Débutant',
			self::Junior->value => 'Junior',
			self::Associate->value => 'Associé',
			self::MidLevel->value => 'Intermédiaire',
			self::Senior->value => 'Senior',
			self::Lead->value => 'Responsable',
			self::Staff->value => 'Spécialiste',
			self::Principal->value => 'Principal',
			self::Manager->value => 'Manager',
			self::SeniorManager->value => 'Manager Senior',
			self::Director->value => 'Directeur',
			self::SeniorDirector->value => 'Directeur Senior',
			self::VicePresident->value => 'Vice-Président',
			self::SeniorVicePresident->value => 'Vice-Président Senior',
			self::ExecutiveVicePresident->value => 'Vice-Président Exécutif',
			self::Architect->value => 'Architecte',
			self::SeniorArchitect->value => 'Architecte Senior',
			self::PrincipalArchitect->value => 'Architecte Principal',
			self::Chief->value => 'Chef',
			self::ChiefOfficer->value => 'Cadre Dirigeant',
			self::ChiefExecutiveOfficer->value => 'Directeur Général',
			self::ChiefTechnologyOfficer->value => 'Directeur Technique',
			self::ChiefFinancialOfficer->value => 'Directeur Financier',
			self::ChiefOperatingOfficer->value => 'Directeur des Opérations',
			self::ChiefMarketingOfficer->value => 'Directeur Marketing',
			self::ChiefInformationOfficer->value => 'Directeur Informatique',
			self::President->value => 'Président',
			self::Partner->value => 'Associé',
			self::SeniorPartner->value => 'Associé Senior',
			self::BoardMember->value => 'Membre du Conseil',
			self::Chairman->value => 'Président du Conseil',
			self::Founder->value => 'Fondateur',
			self::CoFounder->value => 'Co-Fondateur',
			self::Owner->value => 'Propriétaire',
			self::Contractor->value => 'Contractuel',
			self::Freelancer->value => 'Freelance',
			self::Consultant->value => 'Consultant',
			self::SeniorConsultant->value => 'Consultant Senior',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			self::Internship->value => 'Tirocinio',
			self::EntryLevel->value => 'Livello Base',
			self::Junior->value => 'Junior',
			self::Associate->value => 'Associato',
			self::MidLevel->value => 'Intermedio',
			self::Senior->value => 'Senior',
			self::Lead->value => 'Responsabile',
			self::Staff->value => 'Specialista',
			self::Principal->value => 'Principale',
			self::Manager->value => 'Manager',
			self::SeniorManager->value => 'Manager Senior',
			self::Director->value => 'Direttore',
			self::SeniorDirector->value => 'Direttore Senior',
			self::VicePresident->value => 'Vice Presidente',
			self::SeniorVicePresident->value => 'Vice Presidente Senior',
			self::ExecutiveVicePresident->value => 'Vice Presidente Esecutivo',
			self::Architect->value => 'Architetto',
			self::SeniorArchitect->value => 'Architetto Senior',
			self::PrincipalArchitect->value => 'Architetto Principale',
			self::Chief->value => 'Capo',
			self::ChiefOfficer->value => 'Dirigente',
			self::ChiefExecutiveOfficer->value => 'Amministratore Delegato',
			self::ChiefTechnologyOfficer->value => 'Direttore Tecnologico',
			self::ChiefFinancialOfficer->value => 'Direttore Finanziario',
			self::ChiefOperatingOfficer->value => 'Direttore Operativo',
			self::ChiefMarketingOfficer->value => 'Direttore Marketing',
			self::ChiefInformationOfficer->value => 'Direttore Informatico',
			self::President->value => 'Presidente',
			self::Partner->value => 'Socio',
			self::SeniorPartner->value => 'Socio Senior',
			self::BoardMember->value => 'Membro del Consiglio',
			self::Chairman->value => 'Presidente del Consiglio',
			self::Founder->value => 'Fondatore',
			self::CoFounder->value => 'Co-Fondatore',
			self::Owner->value => 'Proprietario',
			self::Contractor->value => 'Contrattista',
			self::Freelancer->value => 'Freelance',
			self::Consultant->value => 'Consulente',
			self::SeniorConsultant->value => 'Consulente Senior',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			self::Internship->value => 'インターンシップ',
			self::EntryLevel->value => 'エントリーレベル',
			self::Junior->value => 'ジュニア',
			self::Associate->value => 'アソシエイト',
			self::MidLevel->value => 'ミッドレベル',
			self::Senior->value => 'シニア',
			self::Lead->value => 'リード',
			self::Staff->value => 'スタッフ',
			self::Principal->value => 'プリンシパル',
			self::Manager->value => 'マネージャー',
			self::SeniorManager->value => 'シニアマネージャー',
			self::Director->value => 'ディレクター',
			self::SeniorDirector->value => 'シニアディレクター',
			self::VicePresident->value => 'バイスプレジデント',
			self::SeniorVicePresident->value => 'シニアバイスプレジデント',
			self::ExecutiveVicePresident->value => 'エグゼクティブバイスプレジデント',
			self::Architect->value => 'アーキテクト',
			self::SeniorArchitect->value => 'シニアアーキテクト',
			self::PrincipalArchitect->value => 'プリンシパルアーキテクト',
			self::Chief->value => 'チーフ',
			self::ChiefOfficer->value => '最高責任者',
			self::ChiefExecutiveOfficer->value => '最高経営責任者',
			self::ChiefTechnologyOfficer->value => '最高技術責任者',
			self::ChiefFinancialOfficer->value => '最高財務責任者',
			self::ChiefOperatingOfficer->value => '最高執行責任者',
			self::ChiefMarketingOfficer->value => '最高販売責任者',
			self::ChiefInformationOfficer->value => '最高情報責任者',
			self::President->value => '社長',
			self::Partner->value => 'パートナー',
			self::SeniorPartner->value => 'シニアパートナー',
			self::BoardMember->value => '取締役',
			self::Chairman->value => '会長',
			self::Founder->value => '創業者',
			self::CoFounder->value => '共同創業者',
			self::Owner->value => 'オーナー',
			self::Contractor->value => '契約社員',
			self::Freelancer->value => 'フリーランサー',
			self::Consultant->value => 'コンサルタント',
			self::SeniorConsultant->value => 'シニアコンサルタント',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			self::Internship->value => 'Stage',
			self::EntryLevel->value => 'Beginniveau',
			self::Junior->value => 'Junior',
			self::Associate->value => 'Medewerker',
			self::MidLevel->value => 'Middenniveau',
			self::Senior->value => 'Senior',
			self::Lead->value => 'Leider',
			self::Staff->value => 'Medewerker',
			self::Principal->value => 'Hoofd',
			self::Manager->value => 'Manager',
			self::SeniorManager->value => 'Senior Manager',
			self::Director->value => 'Directeur',
			self::SeniorDirector->value => 'Senior Directeur',
			self::VicePresident->value => 'Vicepresident',
			self::SeniorVicePresident->value => 'Senior Vicepresident',
			self::ExecutiveVicePresident->value => 'Uitvoerend Vicepresident',
			self::Architect->value => 'Architect',
			self::SeniorArchitect->value => 'Senior Architect',
			self::PrincipalArchitect->value => 'Hoofdarchitect',
			self::Chief->value => 'Chef',
			self::ChiefOfficer->value => 'Hoofd',
			self::ChiefExecutiveOfficer->value => 'Algemeen Directeur',
			self::ChiefTechnologyOfficer->value => 'Hoofd Technologie',
			self::ChiefFinancialOfficer->value => 'Financieel Directeur',
			self::ChiefOperatingOfficer->value => 'Hoofd Operaties',
			self::ChiefMarketingOfficer->value => 'Hoofd Marketing',
			self::ChiefInformationOfficer->value => 'Hoofd Informatie',
			self::President->value => 'President',
			self::Partner->value => 'Partner',
			self::SeniorPartner->value => 'Senior Partner',
			self::BoardMember->value => 'Bestuurslid',
			self::Chairman->value => 'Voorzitter',
			self::Founder->value => 'Oprichter',
			self::CoFounder->value => 'Mede-oprichter',
			self::Owner->value => 'Eigenaar',
			self::Contractor->value => 'Contractant',
			self::Freelancer->value => 'Freelancer',
			self::Consultant->value => 'Consultant',
			self::SeniorConsultant->value => 'Senior Consultant',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			self::Internship->value => 'Staż',
			self::EntryLevel->value => 'Poziom Początkowy',
			self::Junior->value => 'Junior',
			self::Associate->value => 'Asystent',
			self::MidLevel->value => 'Pośredni',
			self::Senior->value => 'Senior',
			self::Lead->value => 'Lider',
			self::Staff->value => 'Specjalista',
			self::Principal->value => 'Główny',
			self::Manager->value => 'Kierownik',
			self::SeniorManager->value => 'Starszy Kierownik',
			self::Director->value => 'Dyrektor',
			self::SeniorDirector->value => 'Starszy Dyrektor',
			self::VicePresident->value => 'Wiceprezes',
			self::SeniorVicePresident->value => 'Starszy Wiceprezes',
			self::ExecutiveVicePresident->value => 'Wiceprezes Wykonawczy',
			self::Architect->value => 'Architekt',
			self::SeniorArchitect->value => 'Starszy Architekt',
			self::PrincipalArchitect->value => 'Główny Architekt',
			self::Chief->value => 'Szef',
			self::ChiefOfficer->value => 'Kierownik',
			self::ChiefExecutiveOfficer->value => 'Dyrektor Generalny',
			self::ChiefTechnologyOfficer->value => 'Dyrektor Techniczny',
			self::ChiefFinancialOfficer->value => 'Dyrektor Finansowy',
			self::ChiefOperatingOfficer->value => 'Dyrektor Operacyjny',
			self::ChiefMarketingOfficer->value => 'Dyrektor Marketingu',
			self::ChiefInformationOfficer->value => 'Dyrektor IT',
			self::President->value => 'Prezes',
			self::Partner->value => 'Partner',
			self::SeniorPartner->value => 'Starszy Partner',
			self::BoardMember->value => 'Członek Zarządu',
			self::Chairman->value => 'Przewodniczący',
			self::Founder->value => 'Założyciel',
			self::CoFounder->value => 'Współzałożyciel',
			self::Owner->value => 'Właściciel',
			self::Contractor->value => 'Kontraktor',
			self::Freelancer->value => 'Wolny Zawód',
			self::Consultant->value => 'Konsultant',
			self::SeniorConsultant->value => 'Starszy Konsultant',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			self::Internship->value => 'Стажировка',
			self::EntryLevel->value => 'Начальный уровень',
			self::Junior->value => 'Младший',
			self::Associate->value => 'Ассистент',
			self::MidLevel->value => 'Средний уровень',
			self::Senior->value => 'Старший',
			self::Lead->value => 'Ведущий',
			self::Staff->value => 'Специалист',
			self::Principal->value => 'Главный',
			self::Manager->value => 'Менеджер',
			self::SeniorManager->value => 'Старший менеджер',
			self::Director->value => 'Директор',
			self::SeniorDirector->value => 'Старший директор',
			self::VicePresident->value => 'Вице-президент',
			self::SeniorVicePresident->value => 'Старший вице-президент',
			self::ExecutiveVicePresident->value => 'Исполнительный вице-президент',
			self::Architect->value => 'Архитектор',
			self::SeniorArchitect->value => 'Старший архитектор',
			self::PrincipalArchitect->value => 'Главный архитектор',
			self::Chief->value => 'Руководитель',
			self::ChiefOfficer->value => 'Руководитель',
			self::ChiefExecutiveOfficer->value => 'Генеральный директор',
			self::ChiefTechnologyOfficer->value => 'Технический директор',
			self::ChiefFinancialOfficer->value => 'Финансовый директор',
			self::ChiefOperatingOfficer->value => 'Операционный директор',
			self::ChiefMarketingOfficer->value => 'Директор по маркетингу',
			self::ChiefInformationOfficer->value => 'Директор по ИТ',
			self::President->value => 'Президент',
			self::Partner->value => 'Партнер',
			self::SeniorPartner->value => 'Старший партнер',
			self::BoardMember->value => 'Член правления',
			self::Chairman->value => 'Председатель',
			self::Founder->value => 'Основатель',
			self::CoFounder->value => 'Сооснователь',
			self::Owner->value => 'Владелец',
			self::Contractor->value => 'Подрядчик',
			self::Freelancer->value => 'Фрилансер',
			self::Consultant->value => 'Консультант',
			self::SeniorConsultant->value => 'Старший консультант',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			self::Internship->value => 'Stajyer',
			self::EntryLevel->value => 'Başlangıç Seviyesi',
			self::Junior->value => 'Junior',
			self::Associate->value => 'Asistan',
			self::MidLevel->value => 'Orta Seviye',
			self::Senior->value => 'Senior',
			self::Lead->value => 'Lider',
			self::Staff->value => 'Uzman',
			self::Principal->value => 'Baş',
			self::Manager->value => 'Müdür',
			self::SeniorManager->value => 'Kıdemli Müdür',
			self::Director->value => 'Direktör',
			self::SeniorDirector->value => 'Kıdemli Direktör',
			self::VicePresident->value => 'Başkan Yardımcısı',
			self::SeniorVicePresident->value => 'Kıdemli Başkan Yardımcısı',
			self::ExecutiveVicePresident->value => 'İcra Kurulu Başkan Yardımcısı',
			self::Architect->value => 'Mimar',
			self::SeniorArchitect->value => 'Kıdemli Mimar',
			self::PrincipalArchitect->value => 'Baş Mimar',
			self::Chief->value => 'Şef',
			self::ChiefOfficer->value => 'Başkan',
			self::ChiefExecutiveOfficer->value => 'Genel Müdür',
			self::ChiefTechnologyOfficer->value => 'Teknoloji Müdürü',
			self::ChiefFinancialOfficer->value => 'Finans Müdürü',
			self::ChiefOperatingOfficer->value => 'Operasyon Müdürü',
			self::ChiefMarketingOfficer->value => 'Pazarlama Müdürü',
			self::ChiefInformationOfficer->value => 'Bilişim Müdürü',
			self::President->value => 'Başkan',
			self::Partner->value => 'Ortak',
			self::SeniorPartner->value => 'Kıdemli Ortak',
			self::BoardMember->value => 'Yönetim Kurulu Üyesi',
			self::Chairman->value => 'Yönetim Kurulu Başkanı',
			self::Founder->value => 'Kurucu',
			self::CoFounder->value => 'Kurucu Ortak',
			self::Owner->value => 'Sahip',
			self::Contractor->value => 'Müteahhit',
			self::Freelancer->value => 'Freelancer',
			self::Consultant->value => 'Danışman',
			self::SeniorConsultant->value => 'Kıdemli Danışman',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			self::Internship->value => 'تدريب',
			self::EntryLevel->value => 'مستوى مبتدئ',
			self::Junior->value => 'مبتدئ',
			self::Associate->value => 'مساعد',
			self::MidLevel->value => 'مستوى متوسط',
			self::Senior->value => 'كبير',
			self::Lead->value => 'قائد',
			self::Staff->value => 'موظف',
			self::Principal->value => 'رئيسي',
			self::Manager->value => 'مدير',
			self::SeniorManager->value => 'مدير كبير',
			self::Director->value => 'مدير',
			self::SeniorDirector->value => 'مدير كبير',
			self::VicePresident->value => 'نائب الرئيس',
			self::SeniorVicePresident->value => 'نائب الرئيس الكبير',
			self::ExecutiveVicePresident->value => 'نائب الرئيس التنفيذي',
			self::Architect->value => 'مهندس معماري',
			self::SeniorArchitect->value => 'مهندس معماري كبير',
			self::PrincipalArchitect->value => 'المهندس المعماري الرئيسي',
			self::Chief->value => 'رئيس',
			self::ChiefOfficer->value => 'ضابط كبير',
			self::ChiefExecutiveOfficer->value => 'الرئيس التنفيذي',
			self::ChiefTechnologyOfficer->value => 'الرئيس التقني',
			self::ChiefFinancialOfficer->value => 'الرئيس المالي',
			self::ChiefOperatingOfficer->value => 'الرئيس التنفيذي للعمليات',
			self::ChiefMarketingOfficer->value => 'الرئيس التنفيذي للتسويق',
			self::ChiefInformationOfficer->value => 'الرئيس التنفيذي للمعلومات',
			self::President->value => 'رئيس',
			self::Partner->value => 'شريك',
			self::SeniorPartner->value => 'شريك كبير',
			self::BoardMember->value => 'عضو مجلس الإدارة',
			self::Chairman->value => 'رئيس مجلس الإدارة',
			self::Founder->value => 'مؤسس',
			self::CoFounder->value => 'مؤسس مشارك',
			self::Owner->value => 'مالك',
			self::Contractor->value => 'مقاول',
			self::Freelancer->value => 'مستقل',
			self::Consultant->value => 'مستشار',
			self::SeniorConsultant->value => 'مستشار كبير',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			self::Internship->value => 'התמחות',
			self::EntryLevel->value => 'רמת כניסה',
			self::Junior->value => 'זוטר',
			self::Associate->value => 'עוזר',
			self::MidLevel->value => 'בינוני',
			self::Senior->value => 'בכיר',
			self::Lead->value => 'מוביל',
			self::Staff->value => 'צוות',
			self::Principal->value => 'ראשי',
			self::Manager->value => 'מנהל',
			self::SeniorManager->value => 'מנהל בכיר',
			self::Director->value => 'מנהל',
			self::SeniorDirector->value => 'מנהל בכיר',
			self::VicePresident->value => 'סגן נשיא',
			self::SeniorVicePresident->value => 'סגן נשיא בכיר',
			self::ExecutiveVicePresident->value => 'סגן נשיא בכיר',
			self::Architect->value => 'ארכיטקט',
			self::SeniorArchitect->value => 'ארכיטקט בכיר',
			self::PrincipalArchitect->value => 'ארכיטקט ראשי',
			self::Chief->value => 'ראש',
			self::ChiefOfficer->value => 'קצין ראשי',
			self::ChiefExecutiveOfficer->value => 'מנכ"ל',
			self::ChiefTechnologyOfficer->value => 'סמנכ"ל טכנולוגיה',
			self::ChiefFinancialOfficer->value => 'סמנכ"ל כספים',
			self::ChiefOperatingOfficer->value => 'סמנכ"ל תפעול',
			self::ChiefMarketingOfficer->value => 'סמנכ"ל שיווק',
			self::ChiefInformationOfficer->value => 'סמנכ"ל מידע',
			self::President->value => 'נשיא',
			self::Partner->value => 'שותף',
			self::SeniorPartner->value => 'שותף בכיר',
			self::BoardMember->value => 'חבר דירקטוריון',
			self::Chairman->value => 'יו"ר',
			self::Founder->value => 'מייסד',
			self::CoFounder->value => 'מייסד שותף',
			self::Owner->value => 'בעלים',
			self::Contractor->value => 'קבלן',
			self::Freelancer->value => 'פרילנסר',
			self::Consultant->value => 'יועץ',
			self::SeniorConsultant->value => 'יועץ בכיר',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			self::Internship->value => 'Praktik',
			self::EntryLevel->value => 'Begynder',
			self::Junior->value => 'Junior',
			self::Associate->value => 'Medarbejder',
			self::MidLevel->value => 'Mellemniveau',
			self::Senior->value => 'Senior',
			self::Lead->value => 'Leder',
			self::Staff->value => 'Medarbejder',
			self::Principal->value => 'Hoved',
			self::Manager->value => 'Manager',
			self::SeniorManager->value => 'Senior Manager',
			self::Director->value => 'Direktør',
			self::SeniorDirector->value => 'Senior Direktør',
			self::VicePresident->value => 'Vicepræsident',
			self::SeniorVicePresident->value => 'Senior Vicepræsident',
			self::ExecutiveVicePresident->value => 'Executive Vicepræsident',
			self::Architect->value => 'Arkitekt',
			self::SeniorArchitect->value => 'Senior Arkitekt',
			self::PrincipalArchitect->value => 'Hovedarkitekt',
			self::Chief->value => 'Chef',
			self::ChiefOfficer->value => 'Chef',
			self::ChiefExecutiveOfficer->value => 'Administrerende Direktør',
			self::ChiefTechnologyOfficer->value => 'Teknologi Direktør',
			self::ChiefFinancialOfficer->value => 'Finansdirektør',
			self::ChiefOperatingOfficer->value => 'Driftsdirektør',
			self::ChiefMarketingOfficer->value => 'Marketingdirektør',
			self::ChiefInformationOfficer->value => 'IT-Direktør',
			self::President->value => 'Præsident',
			self::Partner->value => 'Partner',
			self::SeniorPartner->value => 'Senior Partner',
			self::BoardMember->value => 'Bestyrelsesmedlem',
			self::Chairman->value => 'Formand',
			self::Founder->value => 'Grundlægger',
			self::CoFounder->value => 'Medgrundlægger',
			self::Owner->value => 'Ejer',
			self::Contractor->value => 'Entrepreneur',
			self::Freelancer->value => 'Freelancer',
			self::Consultant->value => 'Konsulent',
			self::SeniorConsultant->value => 'Senior Konsulent',
		];
	}

	// Chinese Labels
	public static function labelsZh(): array
	{
		return [
			self::Internship->value => '实习生',
			self::EntryLevel->value => '初级',
			self::Junior->value => '初级',
			self::Associate->value => '助理',
			self::MidLevel->value => '中级',
			self::Senior->value => '高级',
			self::Lead->value => '组长',
			self::Staff->value => '员工',
			self::Principal->value => '首席',
			self::Manager->value => '经理',
			self::SeniorManager->value => '高级经理',
			self::Director->value => '总监',
			self::SeniorDirector->value => '高级总监',
			self::VicePresident->value => '副总裁',
			self::SeniorVicePresident->value => '高级副总裁',
			self::ExecutiveVicePresident->value => '执行副总裁',
			self::Architect->value => '架构师',
			self::SeniorArchitect->value => '高级架构师',
			self::PrincipalArchitect->value => '首席架构师',
			self::Chief->value => '首席',
			self::ChiefOfficer->value => '首席官',
			self::ChiefExecutiveOfficer->value => '首席执行官',
			self::ChiefTechnologyOfficer->value => '首席技术官',
			self::ChiefFinancialOfficer->value => '首席财务官',
			self::ChiefOperatingOfficer->value => '首席运营官',
			self::ChiefMarketingOfficer->value => '首席营销官',
			self::ChiefInformationOfficer->value => '首席信息官',
			self::President->value => '总裁',
			self::Partner->value => '合伙人',
			self::SeniorPartner->value => '高级合伙人',
			self::BoardMember->value => '董事会成员',
			self::Chairman->value => '董事长',
			self::Founder->value => '创始人',
			self::CoFounder->value => '联合创始人',
			self::Owner->value => '所有者',
			self::Contractor->value => '承包商',
			self::Freelancer->value => '自由职业者',
			self::Consultant->value => '顾问',
			self::SeniorConsultant->value => '高级顾问',
		];
	}
}
