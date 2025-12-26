<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum ProjectRole: string
{
	case Owner = 'owner';
	case Admin = 'admin';
	case Manager = 'manager';
	case Member = 'member';
	case Contributor = 'contributor';
	case Viewer = 'viewer';
	case Guest = 'guest';
	case Reporter = 'reporter';
	case Developer = 'developer';

	/**
	 * Normalize input to ProjectRole
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
			'owner', 'proprietario', 'dono', 'propietario' => self::Owner,
			'admin', 'administrator', 'administrador', 'administrador' => self::Admin,
			'manager', 'gerente', 'gestor', 'director' => self::Manager,
			'member', 'membro', 'miembro', 'membre' => self::Member,
			'contributor', 'contribuinte', 'contribuidor', 'colaborador' => self::Contributor,
			'viewer', 'visualizador', 'espectador', 'lector' => self::Viewer,
			'guest', 'convidado', 'invitado', 'invite' => self::Guest,
			'reporter', 'relator', 'informante', 'reportero' => self::Reporter,
			'developer', 'desenvolvedor', 'desarrollador', 'developpeur' => self::Developer,
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
	 * Get label for this project role in specified language
	 */
	public function label($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$labels = self::labels($lang);
		return $labels[$this->value] ?? ucfirst(str_replace('_', ' ', $this->value));
	}

	/**
	 * Get the permission set for this role based on ActivityType
	 */
	public function getPermissions(): array
	{
		$allActivities = ActivityType::cases();
		$permissions = [];

		foreach ($allActivities as $activity) {
			if ($this->canPerform($activity)) {
				$permissions[] = $activity;
			}
		}

		return $permissions;
	}

	/**
	 * Check if this role can perform a specific activity
	 */
	public function canPerform(ActivityType $activity): bool
	{
		$activityValue = $activity->value;

		return match ($this) {
			self::Owner => true, // Owner can do everything

			self::Admin => $this->canAdminPerform($activityValue),
			self::Manager => $this->canManagerPerform($activityValue),
			self::Member => $this->canMemberPerform($activityValue),
			self::Contributor => $this->canContributorPerform($activityValue),
			self::Viewer => $this->canViewerPerform($activityValue),
			self::Guest => $this->canGuestPerform($activityValue),
			self::Reporter => $this->canReporterPerform($activityValue),
			self::Developer => $this->canDeveloperPerform($activityValue),
		};
	}

	/**
	 * Get color for UI display
	 */
	public function getColor(): string
	{
		return match ($this) {
			self::Owner => '#ef4444', // red
			self::Admin => '#8b5cf6', // violet
			self::Manager => '#3b82f6', // blue
			self::Member => '#10b981', // green
			self::Contributor => '#06b6d4', // cyan
			self::Viewer => '#6b7280', // gray
			self::Guest => '#f59e0b', // amber
			self::Reporter => '#84cc16', // lime
			self::Developer => '#ec4899', // pink
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			self::Owner => 'crown',
			self::Admin => 'shield-check',
			self::Manager => 'briefcase',
			self::Member => 'user',
			self::Contributor => 'hand',
			self::Viewer => 'eye',
			self::Guest => 'user-plus',
			self::Reporter => 'flag',
			self::Developer => 'code',
		};
	}

	/**
	 * Get role hierarchy level (higher number = more permissions)
	 */
	public function getLevel(): int
	{
		return match ($this) {
			self::Owner => 9,
			self::Admin => 8,
			self::Manager => 7,
			self::Developer => 6,
			self::Member => 5,
			self::Contributor => 4,
			self::Reporter => 3,
			self::Viewer => 2,
			self::Guest => 1,
		};
	}

	/**
	 * Check if this role can manage other users
	 */
	public function canManageUsers(): bool
	{
		return match ($this) {
			self::Owner, self::Admin, self::Manager => true,
			default => false,
		};
	}

	/**
	 * Check if this role can manage project settings
	 */
	public function canManageProject(): bool
	{
		return match ($this) {
			self::Owner, self::Admin, self::Manager => true,
			default => false,
		};
	}

	/**
	 * Permission checks for each role
	 */
	private function canAdminPerform(string $activity): bool
	{
		// Admins can do almost everything except delete the project owner
		$restricted = [
			'delete_user', // Cannot delete owner
			'move_user',   // Cannot move owner
		];

		return !in_array($activity, $restricted);
	}

	private function canManagerPerform(string $activity): bool
	{
		// Managers can manage project items but not users or system settings
		$allowedPrefixes = ['move_', 'add_', 'create_', 'update_', 'upload_', 'delete_'];
		$allowedEntities = ['task', 'project', 'file', 'milestone', 'bug', 'expense', 'invoice', 'stage'];

		$parts = explode('_', $activity, 2);
		$action = $parts[0];
		$entity = $parts[1] ?? '';

		// Special activities
		if (in_array($activity, ['invite_user', 'user_assigned_to_task', 'user_removed_from_task'])) {
			return true;
		}

		// Check if action is allowed
		foreach ($allowedPrefixes as $prefix) {
			if (str_starts_with($activity, $prefix)) {
				foreach ($allowedEntities as $entityType) {
					if (str_contains($activity, $entityType)) {
						return true;
					}
				}
			}
		}

		return false;
	}

	private function canMemberPerform(string $activity): bool
	{
		// Members can create/update their own work
		$allowedActions = ['add_', 'create_', 'update_', 'upload_'];
		$allowedEntities = ['task', 'file', 'comment', 'note', 'discussion', 'call', 'email'];

		foreach ($allowedActions as $action) {
			if (str_starts_with($activity, $action)) {
				foreach ($allowedEntities as $entity) {
					if (str_contains($activity, $entity)) {
						return true;
					}
				}
			}
		}

		return false;
	}

	private function canContributorPerform(string $activity): bool
	{
		// Contributors have limited create/update rights
		$allowed = [
			'add_task',
			'create_task',
			'update_task',
			'add_file',
			'upload_file',
			'create_file',
			'add_comment',
			'create_comment',
			'update_comment',
		];

		return in_array($activity, $allowed);
	}

	private function canViewerPerform(string $activity): bool
	{
		// Viewers can only view, no modifications
		return false;
	}

	private function canGuestPerform(string $activity): bool
	{
		// Guests have minimal permissions
		$allowed = [
			'add_comment',
			'create_comment',
		];

		return in_array($activity, $allowed);
	}

	private function canReporterPerform(string $activity): bool
	{
		// Reporters can create and update issues
		$allowedActions = ['add_', 'create_', 'update_'];
		$allowedEntities = ['task', 'bug', 'comment', 'note'];

		foreach ($allowedActions as $action) {
			if (str_starts_with($activity, $action)) {
				foreach ($allowedEntities as $entity) {
					if (str_contains($activity, $entity)) {
						return true;
					}
				}
			}
		}

		return false;
	}

	private function canDeveloperPerform(string $activity): bool
	{
		// Developers can work on code and tasks
		$allowedActions = ['add_', 'create_', 'update_', 'move_', 'delete_', 'upload_'];
		$allowedEntities = ['task', 'file', 'sources', 'project', 'bug', 'stage', 'milestone'];

		foreach ($allowedActions as $action) {
			if (str_starts_with($activity, $action)) {
				foreach ($allowedEntities as $entity) {
					if (str_contains($activity, $entity)) {
						return true;
					}
				}
			}
		}

		// Special development activities
		$special = ['invite_user', 'user_assigned_to_task', 'user_removed_from_task'];
		return in_array($activity, $special);
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			self::Owner->value => 'Owner',
			self::Admin->value => 'Admin',
			self::Manager->value => 'Manager',
			self::Member->value => 'Member',
			self::Contributor->value => 'Contributor',
			self::Viewer->value => 'Viewer',
			self::Guest->value => 'Guest',
			self::Reporter->value => 'Reporter',
			self::Developer->value => 'Developer',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			self::Owner->value => 'Proprietário',
			self::Admin->value => 'Administrador',
			self::Manager->value => 'Gerente',
			self::Member->value => 'Membro',
			self::Contributor->value => 'Contribuidor',
			self::Viewer->value => 'Visualizador',
			self::Guest->value => 'Convidado',
			self::Reporter->value => 'Relator',
			self::Developer->value => 'Desenvolvedor',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			self::Owner->value => 'Propietario',
			self::Admin->value => 'Administrador',
			self::Manager->value => 'Gerente',
			self::Member->value => 'Miembro',
			self::Contributor->value => 'Colaborador',
			self::Viewer->value => 'Espectador',
			self::Guest->value => 'Invitado',
			self::Reporter->value => 'Informante',
			self::Developer->value => 'Desarrollador',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			self::Owner->value => 'Eigentümer',
			self::Admin->value => 'Administrator',
			self::Manager->value => 'Manager',
			self::Member->value => 'Mitglied',
			self::Contributor->value => 'Mitwirkender',
			self::Viewer->value => 'Betrachter',
			self::Guest->value => 'Gast',
			self::Reporter->value => 'Berichterstatter',
			self::Developer->value => 'Entwickler',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			self::Owner->value => 'Propriétaire',
			self::Admin->value => 'Administrateur',
			self::Manager->value => 'Gestionnaire',
			self::Member->value => 'Membre',
			self::Contributor->value => 'Contributeur',
			self::Viewer->value => 'Observateur',
			self::Guest->value => 'Invité',
			self::Reporter->value => 'Rapporteur',
			self::Developer->value => 'Développeur',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			self::Owner->value => 'Proprietario',
			self::Admin->value => 'Amministratore',
			self::Manager->value => 'Manager',
			self::Member->value => 'Membro',
			self::Contributor->value => 'Collaboratore',
			self::Viewer->value => 'Visualizzatore',
			self::Guest->value => 'Ospite',
			self::Reporter->value => 'Segnalatore',
			self::Developer->value => 'Sviluppatore',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			self::Owner->value => 'Eigenaar',
			self::Admin->value => 'Beheerder',
			self::Manager->value => 'Manager',
			self::Member->value => 'Lid',
			self::Contributor->value => 'Bijdrager',
			self::Viewer->value => 'Kijker',
			self::Guest->value => 'Gast',
			self::Reporter->value => 'Rapporteur',
			self::Developer->value => 'Ontwikkelaar',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			self::Owner->value => 'Właściciel',
			self::Admin->value => 'Administrator',
			self::Manager->value => 'Kierownik',
			self::Member->value => 'Członek',
			self::Contributor->value => 'Współtwórca',
			self::Viewer->value => 'Przeglądający',
			self::Guest->value => 'Gość',
			self::Reporter->value => 'Zgłaszający',
			self::Developer->value => 'Programista',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			self::Owner->value => 'Владелец',
			self::Admin->value => 'Администратор',
			self::Manager->value => 'Менеджер',
			self::Member->value => 'Участник',
			self::Contributor->value => 'Участник проекта',
			self::Viewer->value => 'Наблюдатель',
			self::Guest->value => 'Гость',
			self::Reporter->value => 'Репортёр',
			self::Developer->value => 'Разработчик',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			self::Owner->value => 'Sahip',
			self::Admin->value => 'Yönetici',
			self::Manager->value => 'Yönetici',
			self::Member->value => 'Üye',
			self::Contributor->value => 'Katkıda Bulunan',
			self::Viewer->value => 'Görüntüleyen',
			self::Guest->value => 'Misafir',
			self::Reporter->value => 'Raporlayıcı',
			self::Developer->value => 'Geliştirici',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			self::Owner->value => 'المالك',
			self::Admin->value => 'المسؤول',
			self::Manager->value => 'المدير',
			self::Member->value => 'عضو',
			self::Contributor->value => 'المساهم',
			self::Viewer->value => 'المشاهد',
			self::Guest->value => 'ضيف',
			self::Reporter->value => 'المراسل',
			self::Developer->value => 'المطور',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			self::Owner->value => 'בעלים',
			self::Admin->value => 'מנהל',
			self::Manager->value => 'מנהל',
			self::Member->value => 'חבר',
			self::Contributor->value => 'תורם',
			self::Viewer->value => 'צופה',
			self::Guest->value => 'אורח',
			self::Reporter->value => 'מדווח',
			self::Developer->value => 'מפתח',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			self::Owner->value => '所有者',
			self::Admin->value => '管理者',
			self::Manager->value => 'マネージャー',
			self::Member->value => 'メンバー',
			self::Contributor->value => '貢献者',
			self::Viewer->value => '閲覧者',
			self::Guest->value => 'ゲスト',
			self::Reporter->value => 'レポーター',
			self::Developer->value => '開発者',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			self::Owner->value => 'Ejer',
			self::Admin->value => 'Administrator',
			self::Manager->value => 'Manager',
			self::Member->value => 'Medlem',
			self::Contributor->value => 'Bidragyder',
			self::Viewer->value => 'Fremviser',
			self::Guest->value => 'Gæst',
			self::Reporter->value => 'Reporter',
			self::Developer->value => 'Udvikler',
		];
	}

	// Chinese Labels
	public static function labelsZh(): array
	{
		return [
			self::Owner->value => '所有者',
			self::Admin->value => '管理员',
			self::Manager->value => '经理',
			self::Member->value => '成员',
			self::Contributor->value => '贡献者',
			self::Viewer->value => '查看者',
			self::Guest->value => '访客',
			self::Reporter->value => '报告者',
			self::Developer->value => '开发者',
		];
	}
}
