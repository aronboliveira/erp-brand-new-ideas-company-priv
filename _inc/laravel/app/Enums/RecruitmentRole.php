<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum RecruitmentRole: string
{
	case Interviewer = 'interviewer';
	case Manager = 'manager';
	case Proctor = 'proctor';
	case Assistant = 'assistant';
	case OnboardingSpecialist = 'onboarding_specialist';
	case Candidate = 'candidate';
	case Representant = 'representant';
	case Other = 'other';

	public static function normalize(?string $value): self
	{
		if ($value === null)
			return self::Other;
		$v = strtolower(trim($value));
		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;

		$map = [
			// Interviewer
			'interviewer'       => self::Interviewer,
			'entrevistador'     => self::Interviewer,
			'entrevistadora'    => self::Interviewer,
			'interview'         => self::Interviewer,
			'entrevista'        => self::Interviewer,
			'recruiter'         => self::Interviewer,
			'recrutador'        => self::Interviewer,
			'recrutadora'       => self::Interviewer,

			// Manager
			'manager'           => self::Manager,
			'gerente'           => self::Manager,
			'director'          => self::Manager,
			'diretor'           => self::Manager,
			'head'              => self::Manager,
			'chefe'             => self::Manager,
			'líder'             => self::Manager,
			'lider'             => self::Manager,
			'supervisor'        => self::Manager,

			// Proctor
			'proctor'           => self::Proctor,
			'fiscal'            => self::Proctor,
			'supervisor de exame' => self::Proctor,
			'exam supervisor'   => self::Proctor,
			'test administrator' => self::Proctor,
			'administrador de teste' => self::Proctor,

			// Assistant
			'assistant'         => self::Assistant,
			'assistente'        => self::Assistant,
			'auxiliar'          => self::Assistant,
			'helper'            => self::Assistant,
			'ajudante'          => self::Assistant,
			'support'           => self::Assistant,

			// Onboarding Specialist
			'onboarding_specialist' => self::OnboardingSpecialist,
			'onboarding specialist' => self::OnboardingSpecialist,
			'specialist'        => self::OnboardingSpecialist,
			'especialista'      => self::OnboardingSpecialist,
			'onboarding'        => self::OnboardingSpecialist,
			'integration'       => self::OnboardingSpecialist,
			'integração'        => self::OnboardingSpecialist,
			'integraccion'      => self::OnboardingSpecialist,

			// Candidate
			'candidate'         => self::Candidate,
			'candidato'         => self::Candidate,
			'candidata'         => self::Candidate,
			'applicant'         => self::Candidate,
			'applicante'        => self::Candidate,
			'aplicante'         => self::Candidate,
			'postulante'        => self::Candidate,
			'aspirante'         => self::Candidate,

			// Representant
			'representant'      => self::Representant,
			'representante'     => self::Representant,
			'representative'    => self::Representant,
			'rep'               => self::Representant,
			'represent'         => self::Representant,

			// Other
			'other'             => self::Other,
			'outro'             => self::Other,
			'otro'              => self::Other,
			'misc'              => self::Other,
			'miscellaneous'     => self::Other,
		];

		return $map[$v] ?? self::Other;
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
	}

	public function label(): string
	{
		return match ($this) {
			self::Interviewer          => 'Interviewer',
			self::Manager              => 'Manager',
			self::Proctor              => 'Proctor',
			self::Assistant            => 'Assistant',
			self::OnboardingSpecialist => 'Onboarding Specialist',
			self::Candidate            => 'Candidate',
			self::Representant         => 'Representant',
			self::Other                => 'Other',
		};
	}

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

	public static function labelsPtBr(): array
	{
		return [
			self::Interviewer->value          => 'Entrevistador',
			self::Manager->value              => 'Gerente',
			self::Proctor->value              => 'Fiscal',
			self::Assistant->value            => 'Assistente',
			self::OnboardingSpecialist->value => 'Especialista em Integração',
			self::Candidate->value            => 'Candidato',
			self::Representant->value         => 'Representante',
			self::Other->value                => 'Outro',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Interviewer->value          => 'Interviewer',
			self::Manager->value              => 'Manager',
			self::Proctor->value              => 'Proctor',
			self::Assistant->value            => 'Assistant',
			self::OnboardingSpecialist->value => 'Onboarding Specialist',
			self::Candidate->value            => 'Candidate',
			self::Representant->value         => 'Representant',
			self::Other->value                => 'Other',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Interviewer->value          => 'Entrevistador',
			self::Manager->value              => 'Gerente',
			self::Proctor->value              => 'Supervisor',
			self::Assistant->value            => 'Asistente',
			self::OnboardingSpecialist->value => 'Especialista en Integración',
			self::Candidate->value            => 'Candidato',
			self::Representant->value         => 'Representante',
			self::Other->value                => 'Otro',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Interviewer->value          => 'مقابل',
			self::Manager->value              => 'مدير',
			self::Proctor->value              => 'مشرف',
			self::Assistant->value            => 'مساعد',
			self::OnboardingSpecialist->value => 'أخصائي التكامل',
			self::Candidate->value            => 'مرشح',
			self::Representant->value         => 'مندوب',
			self::Other->value                => 'آخر',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Interviewer->value          => 'Interviewer',
			self::Manager->value              => 'Manager',
			self::Proctor->value              => 'Eksamenstilsyn',
			self::Assistant->value            => 'Assistent',
			self::OnboardingSpecialist->value => 'Onboarding Specialist',
			self::Candidate->value            => 'Kandidat',
			self::Representant->value         => 'Repræsentant',
			self::Other->value                => 'Andet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Interviewer->value          => 'Interviewer',
			self::Manager->value              => 'Manager',
			self::Proctor->value              => 'Aufsicht',
			self::Assistant->value            => 'Assistent',
			self::OnboardingSpecialist->value => 'Onboarding-Spezialist',
			self::Candidate->value            => 'Kandidat',
			self::Representant->value         => 'Vertreter',
			self::Other->value                => 'Andere',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Interviewer->value          => 'Intervieweur',
			self::Manager->value              => 'Manager',
			self::Proctor->value              => 'Surveillant',
			self::Assistant->value            => 'Assistant',
			self::OnboardingSpecialist->value => 'Spécialiste d\'Intégration',
			self::Candidate->value            => 'Candidat',
			self::Representant->value         => 'Représentant',
			self::Other->value                => 'Autre',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Interviewer->value          => 'ריאיון',
			self::Manager->value              => 'מנהל',
			self::Proctor->value              => 'משגיח',
			self::Assistant->value            => 'עוזר',
			self::OnboardingSpecialist->value => 'מומחה אינטגרציה',
			self::Candidate->value            => 'מועמד',
			self::Representant->value         => 'נציג',
			self::Other->value                => 'אחר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Interviewer->value          => 'Intervistatore',
			self::Manager->value              => 'Manager',
			self::Proctor->value              => 'Supervisore',
			self::Assistant->value            => 'Assistente',
			self::OnboardingSpecialist->value => 'Specialista di Integrazione',
			self::Candidate->value            => 'Candidato',
			self::Representant->value         => 'Rappresentante',
			self::Other->value                => 'Altro',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Interviewer->value          => '面接官',
			self::Manager->value              => 'マネージャー',
			self::Proctor->value              => '監督者',
			self::Assistant->value            => 'アシスタント',
			self::OnboardingSpecialist->value => 'オン・ボーディング・スペシャリスト',
			self::Candidate->value            => '候補者',
			self::Representant->value         => '代表者',
			self::Other->value                => 'その他',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Interviewer->value          => 'Interviewer',
			self::Manager->value              => 'Manager',
			self::Proctor->value              => 'Toezichthouder',
			self::Assistant->value            => 'Assistent',
			self::OnboardingSpecialist->value => 'Onboarding Specialist',
			self::Candidate->value            => 'Kandidaat',
			self::Representant->value         => 'Vertegenwoordiger',
			self::Other->value                => 'Anders',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Interviewer->value          => 'Ankieter',
			self::Manager->value              => 'Menedżer',
			self::Proctor->value              => 'Nadzorca',
			self::Assistant->value            => 'Asystent',
			self::OnboardingSpecialist->value => 'Specjalista ds. Integracji',
			self::Candidate->value            => 'Kandydat',
			self::Representant->value         => 'Przedstawiciel',
			self::Other->value                => 'Inne',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Interviewer->value          => 'Интервьюер',
			self::Manager->value              => 'Менеджер',
			self::Proctor->value              => 'Наблюдатель',
			self::Assistant->value            => 'Ассистент',
			self::OnboardingSpecialist->value => 'Специалист по адаптации',
			self::Candidate->value            => 'Кандидат',
			self::Representant->value         => 'Представитель',
			self::Other->value                => 'Другое',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Interviewer->value          => 'Mülakatçı',
			self::Manager->value              => 'Yönetici',
			self::Proctor->value              => 'Gözetmen',
			self::Assistant->value            => 'Asistan',
			self::OnboardingSpecialist->value => 'Oryantasyon Uzmanı',
			self::Candidate->value            => 'Aday',
			self::Representant->value         => 'Temsilci',
			self::Other->value                => 'Diğer',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Interviewer->value          => '面试官',
			self::Manager->value              => '经理',
			self::Proctor->value              => '监考人',
			self::Assistant->value            => '助理',
			self::OnboardingSpecialist->value => '入职专员',
			self::Candidate->value            => '候选人',
			self::Representant->value         => '代表',
			self::Other->value                => '其他',
		];
	}

	// Helper methods for business logic
	public function isRecruitmentTeam(): bool
	{
		return match ($this) {
			self::Interviewer, self::Manager, self::Proctor, self::Assistant,
			self::OnboardingSpecialist, self::Representant => true,
			default => false,
		};
	}

	public function isCandidate(): bool
	{
		return $this === self::Candidate;
	}

	public function isHiringManager(): bool
	{
		return $this === self::Manager;
	}

	public function canInterview(): bool
	{
		return match ($this) {
			self::Interviewer, self::Manager, self::Representant => true,
			default => false,
		};
	}

	public function canEvaluate(): bool
	{
		return match ($this) {
			self::Interviewer, self::Manager, self::Proctor => true,
			default => false,
		};
	}

	public function getPermissionLevel(): int
	{
		return match ($this) {
			self::Manager              => 4,
			self::Interviewer          => 3,
			self::Proctor              => 3,
			self::Representant         => 3,
			self::OnboardingSpecialist => 2,
			self::Assistant            => 1,
			self::Candidate            => 0,
			self::Other                => 0,
		};
	}
}
