<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum LeadRole: string
{
	case Caller = 'caller';
	case Supervisor = 'supervisor';
	case Manager = 'manager';
	case Callee = 'callee';
	case Collaborator = 'collaborator';
	case Sponsor = 'sponsor';

	public static function normalize(?string $value): ?self
	{
		if ($value === null)
			return self::Collaborator;

		$v = strtolower(trim($value));

		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;

		$map = [
			// Caller
			'caller'           => self::Caller,
			'chamador'         => self::Caller,
			'chamadora'        => self::Caller,
			'call'             => self::Caller,
			'call_maker'       => self::Caller,
			'initiator'        => self::Caller,
			'iniciador'        => self::Caller,
			'iniciadora'       => self::Caller,
			'originator'       => self::Caller,
			'originador'       => self::Caller,
			'contactor'        => self::Caller,
			'contact'          => self::Caller,
			'telemarketer'     => self::Caller,
			'sales_caller'     => self::Caller,
			'vendedor_telefonico' => self::Caller,

			// Supervisor
			'supervisor'       => self::Supervisor,
			'supervisora'      => self::Supervisor,
			'supervisão'       => self::Supervisor,
			'oversee'          => self::Supervisor,
			'team_lead'        => self::Supervisor,
			'líder_de_equipe'  => self::Supervisor,
			'lider_de_equipe'  => self::Supervisor,
			'team_leader'      => self::Supervisor,
			'chefe_de_equipe'  => self::Supervisor,
			'jefe_de_equipo'   => self::Supervisor,
			'coordinator'      => self::Supervisor,
			'coordenador'      => self::Supervisor,

			// Manager
			'manager'          => self::Manager,
			'gerente'          => self::Manager,
			'gestor'           => self::Manager,
			'gestora'          => self::Manager,
			'head'             => self::Manager,
			'chefe'            => self::Manager,
			'director'         => self::Manager,
			'diretor'          => self::Manager,
			'líder'            => self::Manager,
			'lider'            => self::Manager,

			// Callee
			'callee'           => self::Callee,
			'atendido'         => self::Callee,
			'atendida'         => self::Callee,
			'called'           => self::Callee,
			'receiving'        => self::Callee,
			'recipient'        => self::Callee,
			'receptor'         => self::Callee,
			'receptora'        => self::Callee,
			'prospect'         => self::Callee,
			'prospecto'        => self::Callee,
			'lead'             => self::Callee,
			'potential_client' => self::Callee,
			'client_potencial' => self::Callee,

			// Collaborator
			'collaborator'     => self::Collaborator,
			'colaborador'      => self::Collaborator,
			'colaboradora'     => self::Collaborator,
			'partner'          => self::Collaborator,
			'parceiro'         => self::Collaborator,
			'parceira'         => self::Collaborator,
			'co_worker'        => self::Collaborator,
			'coworker'         => self::Collaborator,
			'team_member'      => self::Collaborator,
			'membro_da_equipe' => self::Collaborator,
			'colleague'        => self::Collaborator,
			'colega'           => self::Collaborator,
			'assistant'        => self::Collaborator,
			'assistente'       => self::Collaborator,

			// Sponsor
			'sponsor'          => self::Sponsor,
			'patrocinador'     => self::Sponsor,
			'patrocinadora'    => self::Sponsor,
			'financiador'      => self::Sponsor,
			'financiadora'     => self::Sponsor,
			'investor'         => self::Sponsor,
			'investidor'       => self::Sponsor,
			'backer'           => self::Sponsor,
			'apoiador'         => self::Sponsor,
			'supporter'        => self::Sponsor,
			'apoiante'         => self::Sponsor,
		];

		return $map[$v] ?? self::Caller;
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
	}

	public function label(): string
	{
		return match ($this) {
			self::Caller       => 'Caller',
			self::Supervisor   => 'Supervisor',
			self::Manager      => 'Manager',
			self::Callee       => 'Callee',
			self::Collaborator => 'Collaborator',
			self::Sponsor      => 'Sponsor',
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
			self::Caller->value       => 'Chamador',
			self::Supervisor->value   => 'Supervisor',
			self::Manager->value      => 'Gerente',
			self::Callee->value       => 'Atendido',
			self::Collaborator->value => 'Colaborador',
			self::Sponsor->value      => 'Patrocinador',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Caller->value       => 'Caller',
			self::Supervisor->value   => 'Supervisor',
			self::Manager->value      => 'Manager',
			self::Callee->value       => 'Callee',
			self::Collaborator->value => 'Collaborator',
			self::Sponsor->value      => 'Sponsor',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Caller->value       => 'Llamador',
			self::Supervisor->value   => 'Supervisor',
			self::Manager->value      => 'Gerente',
			self::Callee->value       => 'Atendido',
			self::Collaborator->value => 'Colaborador',
			self::Sponsor->value      => 'Patrocinador',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Caller->value       => 'المتصل',
			self::Supervisor->value   => 'المشرف',
			self::Manager->value      => 'المدير',
			self::Callee->value       => 'المتصل به',
			self::Collaborator->value => 'المتعاون',
			self::Sponsor->value      => 'الراعي',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Caller->value       => 'Opkalder',
			self::Supervisor->value   => 'Supervisor',
			self::Manager->value      => 'Manager',
			self::Callee->value       => 'Modtager',
			self::Collaborator->value => 'Samarbejdspartner',
			self::Sponsor->value      => 'Sponsor',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Caller->value       => 'Anrufer',
			self::Supervisor->value   => 'Aufseher',
			self::Manager->value      => 'Manager',
			self::Callee->value       => 'Angerufener',
			self::Collaborator->value => 'Mitarbeiter',
			self::Sponsor->value      => 'Sponsor',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Caller->value       => 'Appelant',
			self::Supervisor->value   => 'Superviseur',
			self::Manager->value      => 'Gestionnaire',
			self::Callee->value       => 'Appelé',
			self::Collaborator->value => 'Collaborateur',
			self::Sponsor->value      => 'Sponsor',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Caller->value       => 'מתקשר',
			self::Supervisor->value   => 'מפקח',
			self::Manager->value      => 'מנהל',
			self::Callee->value       => 'מי שמתקשר אליו',
			self::Collaborator->value => 'משתף פעולה',
			self::Sponsor->value      => 'נותן חסות',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Caller->value       => 'Chiamante',
			self::Supervisor->value   => 'Supervisore',
			self::Manager->value      => 'Manager',
			self::Callee->value       => 'Chiamato',
			self::Collaborator->value => 'Collaboratore',
			self::Sponsor->value      => 'Sponsor',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Caller->value       => '発信者',
			self::Supervisor->value   => '監督者',
			self::Manager->value      => 'マネージャー',
			self::Callee->value       => '受信者',
			self::Collaborator->value => '共同作業者',
			self::Sponsor->value      => 'スポンサー',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Caller->value       => 'Beller',
			self::Supervisor->value   => 'Supervisor',
			self::Manager->value      => 'Manager',
			self::Callee->value       => 'Ontvanger',
			self::Collaborator->value => 'Medewerker',
			self::Sponsor->value      => 'Sponsor',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Caller->value       => 'Dzwoniący',
			self::Supervisor->value   => 'Nadzorca',
			self::Manager->value      => 'Menedżer',
			self::Callee->value       => 'Odbiorca',
			self::Collaborator->value => 'Współpracownik',
			self::Sponsor->value      => 'Sponsor',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Caller->value       => 'Звонящий',
			self::Supervisor->value   => 'Супервайзер',
			self::Manager->value      => 'Менеджер',
			self::Callee->value       => 'Получатель',
			self::Collaborator->value => 'Сотрудник',
			self::Sponsor->value      => 'Спонсор',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Caller->value       => 'Arayan',
			self::Supervisor->value   => 'Gözetmen',
			self::Manager->value      => 'Yönetici',
			self::Callee->value       => 'Aranan',
			self::Collaborator->value => 'İşbirlikçi',
			self::Sponsor->value      => 'Sponsor',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Caller->value       => '呼叫者',
			self::Supervisor->value   => '主管',
			self::Manager->value      => '经理',
			self::Callee->value       => '被呼叫者',
			self::Collaborator->value => '协作者',
			self::Sponsor->value      => '赞助商',
		];
	}

	// Helper methods for business logic
	public function isInitiator(): bool
	{
		return $this === self::Caller;
	}

	public function isReceiver(): bool
	{
		return $this === self::Callee;
	}

	public function isManagement(): bool
	{
		return match ($this) {
			self::Supervisor, self::Manager => true,
			default => false,
		};
	}

	public function isSupport(): bool
	{
		return match ($this) {
			self::Collaborator, self::Sponsor => true,
			default => false,
		};
	}

	public function isExternal(): bool
	{
		return match ($this) {
			self::Callee, self::Sponsor => true,
			default => false,
		};
	}

	public function isInternal(): bool
	{
		return match ($this) {
			self::Caller, self::Supervisor, self::Manager, self::Collaborator => true,
			default => false,
		};
	}

	public function canMakeCalls(): bool
	{
		return match ($this) {
			self::Caller, self::Supervisor, self::Manager, self::Collaborator => true,
			default => false,
		};
	}

	public function canReceiveCalls(): bool
	{
		return match ($this) {
			self::Callee, self::Supervisor, self::Manager, self::Collaborator => true,
			default => false,
		};
	}

	public function getCommunicationDirection(): string
	{
		return match ($this) {
			self::Caller => 'outbound',
			self::Callee => 'inbound',
			default => 'both',
		};
	}

	public function getPermissionLevel(): int
	{
		return match ($this) {
			self::Manager      => 4,
			self::Supervisor   => 3,
			self::Collaborator => 2,
			self::Sponsor      => 2,
			self::Caller       => 1,
			self::Callee       => 0,
		};
	}
}
