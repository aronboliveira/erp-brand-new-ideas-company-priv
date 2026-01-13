<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum ApprovalType: string
{
	// Approval types
	case NoRegistration = 'no_registration';
	case ManualApproval = 'manual_approval';
	case AutomaticApproval = 'automatic_approval';
	case EmailConfirmation = 'email_confirmation';

	/**
	 * Normalize input to ApprovalType
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
			// No registration
			'noregistration', 'none', 'disabled', 'off', 'openaccess',
			'instantaccess', 'noauth' => self::NoRegistration,

			// Manual approval
			'manualapproval', 'manual', 'adminapproval', 'requiresapproval',
			'approvalrequired', 'moderated', 'reviewrequired' => self::ManualApproval,

			// Automatic approval
			'automaticapproval', 'auto', 'instant', 'autoapprove',
			'immediate', 'instantapproval' => self::AutomaticApproval,

			// Email confirmation
			'emailconfirmation', 'emailverify', 'emailverification',
			'confirmemail', 'verifyemail', 'emailconfirm' => self::EmailConfirmation,

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
	 * Get label for this registration type in specified language
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
			self::NoRegistration => '#10b981', // Green - open access
			self::ManualApproval => '#f59e0b', // Yellow/amber - requires review
			self::AutomaticApproval => '#3b82f6', // Blue - instant access
			self::EmailConfirmation => '#8b5cf6', // Purple - requires email verification
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			self::NoRegistration => 'door-open',
			self::ManualApproval => 'user-check',
			self::AutomaticApproval => 'bolt',
			self::EmailConfirmation => 'envelope',
		};
	}

	/**
	 * Check if registration requires approval
	 */
	public function requiresApproval(): bool
	{
		return $this === self::ManualApproval;
	}

	/**
	 * Check if registration is automatic
	 */
	public function isAutomatic(): bool
	{
		return in_array($this, [
			self::NoRegistration,
			self::AutomaticApproval,
		]);
	}

	/**
	 * Check if registration requires email verification
	 */
	public function requiresEmailVerification(): bool
	{
		return $this === self::EmailConfirmation;
	}

	/**
	 * Check if user gets immediate access
	 */
	public function givesImmediateAccess(): bool
	{
		return in_array($this, [
			self::NoRegistration,
			self::AutomaticApproval,
		]);
	}

	/**
	 * Get security level (1-4, where 4 is highest security)
	 */
	public function getSecurityLevel(): int
	{
		return match ($this) {
			self::NoRegistration => 1,
			self::AutomaticApproval => 2,
			self::EmailConfirmation => 3,
			self::ManualApproval => 4,
		};
	}

	/**
	 * Get description of the registration process
	 */
	public function getDescription(): string
	{
		return match ($this) {
			self::NoRegistration => 'Users can access immediately without any registration',
			self::ManualApproval => 'Users register but must wait for admin approval',
			self::AutomaticApproval => 'Users are automatically approved after registration',
			self::EmailConfirmation => 'Users must confirm their email address before access',
		};
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			self::NoRegistration->value => 'No Registration',
			self::ManualApproval->value => 'Manual Approval',
			self::AutomaticApproval->value => 'Automatic Approval',
			self::EmailConfirmation->value => 'Email Confirmation',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			self::NoRegistration->value => 'Sem Registro',
			self::ManualApproval->value => 'Aprovação Manual',
			self::AutomaticApproval->value => 'Aprovação Automática',
			self::EmailConfirmation->value => 'Confirmação por Email',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			self::NoRegistration->value => 'Sin Registro',
			self::ManualApproval->value => 'Aprobación Manual',
			self::AutomaticApproval->value => 'Aprobación Automática',
			self::EmailConfirmation->value => 'Confirmación por Email',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			self::NoRegistration->value => 'Pas d\'Inscription',
			self::ManualApproval->value => 'Approbation Manuelle',
			self::AutomaticApproval->value => 'Approbation Automatique',
			self::EmailConfirmation->value => 'Confirmation par Email',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			self::NoRegistration->value => 'Keine Registrierung',
			self::ManualApproval->value => 'Manuelle Genehmigung',
			self::AutomaticApproval->value => 'Automatische Genehmigung',
			self::EmailConfirmation->value => 'E-Mail-Bestätigung',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			self::NoRegistration->value => 'Nessuna Registrazione',
			self::ManualApproval->value => 'Approvazione Manuale',
			self::AutomaticApproval->value => 'Approvazione Automatica',
			self::EmailConfirmation->value => 'Conferma Email',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			self::NoRegistration->value => 'Geen Registratie',
			self::ManualApproval->value => 'Handmatige Goedkeuring',
			self::AutomaticApproval->value => 'Automatische Goedkeuring',
			self::EmailConfirmation->value => 'E-mailbevestiging',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			self::NoRegistration->value => 'Brak Rejestracji',
			self::ManualApproval->value => 'Ręczna Akceptacja',
			self::AutomaticApproval->value => 'Automatyczna Akceptacja',
			self::EmailConfirmation->value => 'Potwierdzenie Email',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			self::NoRegistration->value => 'Без Регистрации',
			self::ManualApproval->value => 'Ручное Одобрение',
			self::AutomaticApproval->value => 'Автоматическое Одобрение',
			self::EmailConfirmation->value => 'Подтверждение Email',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			self::NoRegistration->value => 'Kayıt Yok',
			self::ManualApproval->value => 'Manuel Onay',
			self::AutomaticApproval->value => 'Otomatik Onay',
			self::EmailConfirmation->value => 'Email Doğrulama',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			self::NoRegistration->value => 'بدون تسجيل',
			self::ManualApproval->value => 'موافقة يدوية',
			self::AutomaticApproval->value => 'موافقة تلقائية',
			self::EmailConfirmation->value => 'تأكيد البريد الإلكتروني',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			self::NoRegistration->value => 'ללא רישום',
			self::ManualApproval->value => 'אישור ידני',
			self::AutomaticApproval->value => 'אישור אוטומטי',
			self::EmailConfirmation->value => 'אימות אימייל',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			self::NoRegistration->value => '登録なし',
			self::ManualApproval->value => '手動承認',
			self::AutomaticApproval->value => '自動承認',
			self::EmailConfirmation->value => 'メール確認',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			self::NoRegistration->value => 'Ingen Registrering',
			self::ManualApproval->value => 'Manuel Godkendelse',
			self::AutomaticApproval->value => 'Automatisk Godkendelse',
			self::EmailConfirmation->value => 'E-mail Bekræftelse',
		];
	}

	// Chinese Labels
	public static function labelsZh(): array
	{
		return [
			self::NoRegistration->value => '无需注册',
			self::ManualApproval->value => '手动批准',
			self::AutomaticApproval->value => '自动批准',
			self::EmailConfirmation->value => '邮件确认',
		];
	}
}
