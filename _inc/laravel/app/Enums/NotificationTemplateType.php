<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum NotificationTemplateType: string
{
	// Core communication types
	case Email           = 'email';
	case Sms             = 'sms';
	case Push            = 'push';
	case InApp           = 'in_app';
	case Webhook         = 'webhook';
	case Api             = 'api';

		// CRM-specific notifications
	case LeadAssignment  = 'lead_assignment';
	case DealStageChange = 'deal_stage_change';
	case ContactFollowUp = 'contact_follow_up';
	case MeetingReminder = 'meeting_reminder';
	case TaskAssignment  = 'task_assignment';
	case TicketUpdate    = 'ticket_update';
	case TicketEscalation = 'ticket_escalation';

		// E-commerce notifications
	case OrderConfirmation   = 'order_confirmation';
	case OrderShipped        = 'order_shipped';
	case OrderDelivered      = 'order_delivered';
	case OrderCancelled      = 'order_cancelled';
	case BackInStock         = 'back_in_stock';
	case PriceDrop          = 'price_drop';
	case AbandonedCart      = 'abandoned_cart';
	case ReviewRequest      = 'review_request';
	case WishlistReminder   = 'wishlist_reminder';

		// ERP operational notifications
	case InventoryLow        = 'inventory_low';
	case InventoryOut        = 'inventory_out';
	case PurchaseOrder       = 'purchase_order';
	case InvoiceDue          = 'invoice_due';
	case InvoiceOverdue      = 'invoice_overdue';
	case PaymentReceived     = 'payment_received';
	case PaymentFailed       = 'payment_failed';
	case SubscriptionRenewal = 'subscription_renewal';
	case SubscriptionExpiry  = 'subscription_expiry';

		// System & administrative notifications
	case UserInvitation      = 'user_invitation';
	case PasswordReset       = 'password_reset';
	case TwoFactor           = 'two_factor';
	case SystemAlert         = 'system_alert';
	case Maintenance         = 'maintenance';
	case BackupComplete      = 'backup_complete';
	case ReportReady         = 'report_ready';

		// Integration & third-party notifications
	case Slack               = 'slack';
	case Teams               = 'teams';
	case Discord             = 'discord';
	case Telegram            = 'telegram';
	case WhatsApp            = 'whatsapp';
	case Messenger           = 'messenger';

		// Workflow & automation notifications
	case WorkflowTrigger     = 'workflow_trigger';
	case WorkflowComplete    = 'workflow_complete';
	case ApprovalRequest     = 'approval_request';
	case ApprovalGranted     = 'approval_granted';
	case ApprovalDenied      = 'approval_denied';

		// Customer lifecycle notifications
	case Welcome             = 'welcome';
	case Anniversary         = 'anniversary';
	case LoyaltyReward       = 'loyalty_reward';
	case ReferralBonus       = 'referral_bonus';
	case UpsellOpportunity   = 'upsell_opportunity';
	case CrossSell           = 'cross_sell';

		// Marketing communications
	case Newsletter          = 'newsletter';
	case Promotional         = 'promotional';
	case EventInvitation     = 'event_invitation';
	case SurveyRequest       = 'survey_request';
	case FeedbackRequest     = 'feedback_request';

	case Other = 'other';

	public static function normalize(string|null|self $value = null): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Email;

		$normalizedValue = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($value)));
		return match ($normalizedValue) {
			// Core communication types
			'email', 'mail' => self::Email,
			'sms', 'text' => self::Sms,
			'push', 'pushnotification' => self::Push,
			'inapp', 'in_app', 'in_app_notification' => self::InApp,
			'webhook', 'web_hook' => self::Webhook,
			'api', 'apicall' => self::Api,

			// CRM-specific
			'leadassignment', 'lead_assignment' => self::LeadAssignment,
			'dealstagechange', 'deal_stage_change' => self::DealStageChange,
			'contactfollowup', 'contact_follow_up' => self::ContactFollowUp,
			'meetingreminder', 'meeting_reminder' => self::MeetingReminder,
			'taskassignment', 'task_assignment' => self::TaskAssignment,
			'ticketupdate', 'ticket_update' => self::TicketUpdate,
			'ticketescalation', 'ticket_escalation' => self::TicketEscalation,

			// E-commerce
			'orderconfirmation', 'order_confirmation' => self::OrderConfirmation,
			'ordershipped', 'order_shipped' => self::OrderShipped,
			'orderdelivered', 'order_delivered' => self::OrderDelivered,
			'ordercancelled', 'order_cancelled' => self::OrderCancelled,
			'backinstock', 'back_in_stock' => self::BackInStock,
			'pricedrop', 'price_drop' => self::PriceDrop,
			'abandonedcart', 'abandoned_cart' => self::AbandonedCart,
			'reviewrequest', 'review_request' => self::ReviewRequest,
			'wishlistreminder', 'wishlist_reminder' => self::WishlistReminder,

			// ERP operational
			'inventorylow', 'inventory_low' => self::InventoryLow,
			'inventoryout', 'inventory_out' => self::InventoryOut,
			'purchaseorder', 'purchase_order' => self::PurchaseOrder,
			'invoicedue', 'invoice_due' => self::InvoiceDue,
			'invoiceoverdue', 'invoice_overdue' => self::InvoiceOverdue,
			'paymentreceived', 'payment_received' => self::PaymentReceived,
			'paymentfailed', 'payment_failed' => self::PaymentFailed,
			'subscriptionrenewal', 'subscription_renewal' => self::SubscriptionRenewal,
			'subscriptionexpiry', 'subscription_expiry' => self::SubscriptionExpiry,

			// System & administrative
			'userinvitation', 'user_invitation' => self::UserInvitation,
			'passwordreset', 'password_reset' => self::PasswordReset,
			'twofactor', 'two_factor', '2fa' => self::TwoFactor,
			'systemalert', 'system_alert' => self::SystemAlert,
			'maintenance' => self::Maintenance,
			'backupcomplete', 'backup_complete' => self::BackupComplete,
			'reportready', 'report_ready' => self::ReportReady,

			// Integration
			'slack' => self::Slack,
			'teams', 'microsoftteams' => self::Teams,
			'discord' => self::Discord,
			'telegram' => self::Telegram,
			'whatsapp' => self::WhatsApp,
			'messenger', 'facebookmessenger' => self::Messenger,

			// Workflow
			'workflowtrigger', 'workflow_trigger' => self::WorkflowTrigger,
			'workflowcomplete', 'workflow_complete' => self::WorkflowComplete,
			'approvalrequest', 'approval_request' => self::ApprovalRequest,
			'approvalgranted', 'approval_granted' => self::ApprovalGranted,
			'approvaldenied', 'approval_denied' => self::ApprovalDenied,

			// Customer lifecycle
			'welcome' => self::Welcome,
			'anniversary' => self::Anniversary,
			'loyaltyreward', 'loyalty_reward' => self::LoyaltyReward,
			'referralbonus', 'referral_bonus' => self::ReferralBonus,
			'upsellopportunity', 'upsell_opportunity' => self::UpsellOpportunity,
			'crosssell', 'cross_sell' => self::CrossSell,

			// Marketing
			'newsletter' => self::Newsletter,
			'promotional' => self::Promotional,
			'eventinvitation', 'event_invitation' => self::EventInvitation,
			'surveyrequest', 'survey_request' => self::SurveyRequest,
			'feedbackrequest', 'feedback_request' => self::FeedbackRequest,

			default => self::Other,
		};
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
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
			// Core communication types
			self::Email->value            => 'E-mail',
			self::Sms->value              => 'SMS',
			self::Push->value             => 'Notificação Push',
			self::InApp->value            => 'No Aplicativo',
			self::Webhook->value          => 'Webhook',
			self::Api->value              => 'API',

			// CRM-specific
			self::LeadAssignment->value   => 'Atribuição de Lead',
			self::DealStageChange->value  => 'Mudança de Estágio da Oportunidade',
			self::ContactFollowUp->value  => 'Follow-up de Contato',
			self::MeetingReminder->value  => 'Lembrete de Reunião',
			self::TaskAssignment->value   => 'Atribuição de Tarefa',
			self::TicketUpdate->value     => 'Atualização de Ticket',
			self::TicketEscalation->value => 'Escalonamento de Ticket',

			// E-commerce
			self::OrderConfirmation->value => 'Confirmação de Pedido',
			self::OrderShipped->value      => 'Pedido Enviado',
			self::OrderDelivered->value    => 'Pedido Entregue',
			self::OrderCancelled->value    => 'Pedido Cancelado',
			self::BackInStock->value       => 'Produto Disponível Novamente',
			self::PriceDrop->value         => 'Queda de Preço',
			self::AbandonedCart->value     => 'Carrinho Abandonado',
			self::ReviewRequest->value     => 'Solicitação de Avaliação',
			self::WishlistReminder->value  => 'Lembrete de Lista de Desejos',

			// ERP operational
			self::InventoryLow->value      => 'Estoque Baixo',
			self::InventoryOut->value      => 'Estoque Esgotado',
			self::PurchaseOrder->value     => 'Ordem de Compra',
			self::InvoiceDue->value        => 'Fatura Vencendo',
			self::InvoiceOverdue->value    => 'Fatura Vencida',
			self::PaymentReceived->value   => 'Pagamento Recebido',
			self::PaymentFailed->value     => 'Pagamento Falhou',
			self::SubscriptionRenewal->value => 'Renovação de Assinatura',
			self::SubscriptionExpiry->value  => 'Expiração de Assinatura',

			// System & administrative
			self::UserInvitation->value    => 'Convite de Usuário',
			self::PasswordReset->value     => 'Redefinição de Senha',
			self::TwoFactor->value         => 'Autenticação de Dois Fatores',
			self::SystemAlert->value       => 'Alerta do Sistema',
			self::Maintenance->value       => 'Manutenção',
			self::BackupComplete->value    => 'Backup Concluído',
			self::ReportReady->value       => 'Relatório Pronto',

			// Integration
			self::Slack->value             => 'Slack',
			self::Teams->value             => 'Microsoft Teams',
			self::Discord->value           => 'Discord',
			self::Telegram->value          => 'Telegram',
			self::WhatsApp->value          => 'WhatsApp',
			self::Messenger->value         => 'Facebook Messenger',

			// Workflow
			self::WorkflowTrigger->value   => 'Gatilho de Fluxo de Trabalho',
			self::WorkflowComplete->value  => 'Fluxo de Trabalho Concluído',
			self::ApprovalRequest->value   => 'Solicitação de Aprovação',
			self::ApprovalGranted->value   => 'Aprovação Concedida',
			self::ApprovalDenied->value    => 'Aprovação Negada',

			// Customer lifecycle
			self::Welcome->value           => 'Boas-vindas',
			self::Anniversary->value       => 'Aniversário',
			self::LoyaltyReward->value     => 'Recompensa de Fidelidade',
			self::ReferralBonus->value     => 'Bônus de Indicação',
			self::UpsellOpportunity->value => 'Oportunidade de Venda Adicional',
			self::CrossSell->value         => 'Venda Cruzada',

			// Marketing
			self::Newsletter->value        => 'Newsletter',
			self::Promotional->value       => 'Promocional',
			self::EventInvitation->value   => 'Convite para Evento',
			self::SurveyRequest->value     => 'Solicitação de Pesquisa',
			self::FeedbackRequest->value   => 'Solicitação de Feedback',

			self::Other->value             => 'Outro',
		];
	}

	public static function labelsEn(): array
	{
		return [
			// Core communication types
			self::Email->value            => 'Email',
			self::Sms->value              => 'SMS',
			self::Push->value             => 'Push Notification',
			self::InApp->value            => 'In-App',
			self::Webhook->value          => 'Webhook',
			self::Api->value              => 'API',

			// CRM-specific
			self::LeadAssignment->value   => 'Lead Assignment',
			self::DealStageChange->value  => 'Deal Stage Change',
			self::ContactFollowUp->value  => 'Contact Follow-up',
			self::MeetingReminder->value  => 'Meeting Reminder',
			self::TaskAssignment->value   => 'Task Assignment',
			self::TicketUpdate->value     => 'Ticket Update',
			self::TicketEscalation->value => 'Ticket Escalation',

			// E-commerce
			self::OrderConfirmation->value => 'Order Confirmation',
			self::OrderShipped->value      => 'Order Shipped',
			self::OrderDelivered->value    => 'Order Delivered',
			self::OrderCancelled->value    => 'Order Cancelled',
			self::BackInStock->value       => 'Back In Stock',
			self::PriceDrop->value         => 'Price Drop Alert',
			self::AbandonedCart->value     => 'Abandoned Cart',
			self::ReviewRequest->value     => 'Review Request',
			self::WishlistReminder->value  => 'Wishlist Reminder',

			// ERP operational
			self::InventoryLow->value      => 'Low Inventory',
			self::InventoryOut->value      => 'Out of Stock',
			self::PurchaseOrder->value     => 'Purchase Order',
			self::InvoiceDue->value        => 'Invoice Due',
			self::InvoiceOverdue->value    => 'Invoice Overdue',
			self::PaymentReceived->value   => 'Payment Received',
			self::PaymentFailed->value     => 'Payment Failed',
			self::SubscriptionRenewal->value => 'Subscription Renewal',
			self::SubscriptionExpiry->value  => 'Subscription Expiry',

			// System & administrative
			self::UserInvitation->value    => 'User Invitation',
			self::PasswordReset->value     => 'Password Reset',
			self::TwoFactor->value         => 'Two-Factor Authentication',
			self::SystemAlert->value       => 'System Alert',
			self::Maintenance->value       => 'Maintenance',
			self::BackupComplete->value    => 'Backup Complete',
			self::ReportReady->value       => 'Report Ready',

			// Integration
			self::Slack->value             => 'Slack',
			self::Teams->value             => 'Microsoft Teams',
			self::Discord->value           => 'Discord',
			self::Telegram->value          => 'Telegram',
			self::WhatsApp->value          => 'WhatsApp',
			self::Messenger->value         => 'Facebook Messenger',

			// Workflow
			self::WorkflowTrigger->value   => 'Workflow Trigger',
			self::WorkflowComplete->value  => 'Workflow Complete',
			self::ApprovalRequest->value   => 'Approval Request',
			self::ApprovalGranted->value   => 'Approval Granted',
			self::ApprovalDenied->value    => 'Approval Denied',

			// Customer lifecycle
			self::Welcome->value           => 'Welcome',
			self::Anniversary->value       => 'Anniversary',
			self::LoyaltyReward->value     => 'Loyalty Reward',
			self::ReferralBonus->value     => 'Referral Bonus',
			self::UpsellOpportunity->value => 'Upsell Opportunity',
			self::CrossSell->value         => 'Cross-sell',

			// Marketing
			self::Newsletter->value        => 'Newsletter',
			self::Promotional->value       => 'Promotional',
			self::EventInvitation->value   => 'Event Invitation',
			self::SurveyRequest->value     => 'Survey Request',
			self::FeedbackRequest->value   => 'Feedback Request',
			self::Other->value             => 'Other',
		];
	}
	public static function labelsEs(): array
	{
		return [
			self::Email->value            => 'Correo Electrónico',
			self::Sms->value              => 'SMS',
			self::Push->value             => 'Notificación Push',
			self::InApp->value            => 'En la Aplicación',
			self::Webhook->value          => 'Webhook',
			self::Api->value              => 'API',
			self::Other->value            => 'Otro',
			self::LeadAssignment->value   => 'Asignación de Lead',
			self::DealStageChange->value  => 'Cambio de Etapa de Oportunidad',
			self::ContactFollowUp->value  => 'Seguimiento de Contacto',
			self::MeetingReminder->value  => 'Recordatorio de Reunión',
			self::TaskAssignment->value   => 'Asignación de Tarea',
			self::TicketUpdate->value     => 'Actualización de Ticket',
			self::TicketEscalation->value => 'Escalado de Ticket',
			self::OrderConfirmation->value => 'Confirmación de Pedido',
			self::OrderShipped->value      => 'Pedido Enviado',
			self::OrderDelivered->value    => 'Pedido Entregado',
			self::OrderCancelled->value    => 'Pedido Cancelado',
			self::BackInStock->value       => 'Producto Disponible Nuevamente',
			self::PriceDrop->value         => 'Caída de Precio',
			self::AbandonedCart->value     => 'Carrito Abandonado',
			self::ReviewRequest->value     => 'Solicitud de Reseña',
			self::WishlistReminder->value  => 'Recordatorio de Lista de Deseos',
			self::InventoryLow->value      => 'Inventario Bajo',
			self::InventoryOut->value      => 'Sin Stock',
			self::PurchaseOrder->value     => 'Orden de Compra',
			self::InvoiceDue->value        => 'Factura por Vencer',
			self::InvoiceOverdue->value    => 'Factura Vencida',
			self::PaymentReceived->value   => 'Pago Recibido',
			self::PaymentFailed->value     => 'Pago Fallido',
			self::SubscriptionRenewal->value => 'Renovación de Suscripción',
			self::SubscriptionExpiry->value  => 'Vencimiento de Suscripción',
			self::UserInvitation->value    => 'Invitación de Usuario',
			self::PasswordReset->value     => 'Restablecimiento de Contraseña',
			self::TwoFactor->value         => 'Autenticación de Dos Factores',
			self::SystemAlert->value       => 'Alerta del Sistema',
			self::Maintenance->value       => 'Mantenimiento',
			self::BackupComplete->value    => 'Copia de Seguridad Completa',
			self::ReportReady->value       => 'Informe Listo',
			self::Slack->value             => 'Slack',
			self::Teams->value             => 'Microsoft Teams',
			self::Discord->value           => 'Discord',
			self::Telegram->value          => 'Telegram',
			self::WhatsApp->value          => 'WhatsApp',
			self::Messenger->value         => 'Facebook Messenger',
			self::WorkflowTrigger->value   => 'Disparador de Flujo de Trabajo',
			self::WorkflowComplete->value  => 'Flujo de Trabajo Completado',
			self::ApprovalRequest->value   => 'Solicitud de Aprobación',
			self::ApprovalGranted->value   => 'Aprobación Concedida',
			self::ApprovalDenied->value    => 'Aprobación Denegada',
			self::Welcome->value           => 'Bienvenida',
			self::Anniversary->value       => 'Aniversario',
			self::LoyaltyReward->value     => 'Recompensa de Fidelidad',
			self::ReferralBonus->value     => 'Bono de Referido',
			self::UpsellOpportunity->value => 'Oportunidad de Venta Adicional',
			self::CrossSell->value         => 'Venta Cruzada',
			self::Newsletter->value        => 'Boletín',
			self::Promotional->value       => 'Promocional',
			self::EventInvitation->value   => 'Invitación a Evento',
			self::SurveyRequest->value     => 'Solicitud de Encuesta',
			self::FeedbackRequest->value   => 'Solicitud de Comentarios',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Email->value            => 'البريد الإلكتروني',
			self::Sms->value              => 'رسالة نصية',
			self::Push->value             => 'إشعار دفع',
			self::InApp->value            => 'داخل التطبيق',
			self::Webhook->value          => 'ويب هوك',
			self::Api->value              => 'واجهة برمجة التطبيقات',
			self::Other->value            => 'أخرى',
			self::LeadAssignment->value   => 'تعيين العميل المحتمل',
			self::DealStageChange->value  => 'تغيير مرحلة الصفقة',
			self::ContactFollowUp->value  => 'متابعة الاتصال',
			self::MeetingReminder->value  => 'تذكير بالاجتماع',
			self::TaskAssignment->value   => 'تعيين المهمة',
			self::TicketUpdate->value     => 'تحديث التذكرة',
			self::TicketEscalation->value => 'تصعيد التذكرة',
			self::OrderConfirmation->value => 'تأكيد الطلب',
			self::OrderShipped->value      => 'تم شحن الطلب',
			self::OrderDelivered->value    => 'تم تسليم الطلب',
			self::OrderCancelled->value    => 'تم إلغاء الطلب',
			self::BackInStock->value       => 'متوفر مرة أخرى',
			self::PriceDrop->value         => 'انخفاض السعر',
			self::AbandonedCart->value     => 'سلة مهجورة',
			self::ReviewRequest->value     => 'طلب تقييم',
			self::WishlistReminder->value  => 'تذكير بقائمة الرغبات',
			self::InventoryLow->value      => 'مخزون منخفض',
			self::InventoryOut->value      => 'نفذ من المخزون',
			self::PurchaseOrder->value     => 'أمر الشراء',
			self::InvoiceDue->value        => 'فاتورة مستحقة',
			self::InvoiceOverdue->value    => 'فاتورة متأخرة',
			self::PaymentReceived->value   => 'تم استلام الدفع',
			self::PaymentFailed->value     => 'فشل الدفع',
			self::SubscriptionRenewal->value => 'تجديد الاشتراك',
			self::SubscriptionExpiry->value  => 'انتهاء الاشتراك',
			self::UserInvitation->value    => 'دعوة مستخدم',
			self::PasswordReset->value     => 'إعادة تعيين كلمة المرور',
			self::TwoFactor->value         => 'المصادقة الثنائية',
			self::SystemAlert->value       => 'تنبيه النظام',
			self::Maintenance->value       => 'صيانة',
			self::BackupComplete->value    => 'اكتمال النسخ الاحتياطي',
			self::ReportReady->value       => 'التقرير جاهز',
			self::Slack->value             => 'سلاك',
			self::Teams->value             => 'مايكروسوفت تيمز',
			self::Discord->value           => 'ديسكورد',
			self::Telegram->value          => 'تيليجرام',
			self::WhatsApp->value          => 'واتساب',
			self::Messenger->value         => 'ماسنجر فيسبوك',
			self::WorkflowTrigger->value   => 'تشغيل سير العمل',
			self::WorkflowComplete->value  => 'اكتمال سير العمل',
			self::ApprovalRequest->value   => 'طلب موافقة',
			self::ApprovalGranted->value   => 'تمت الموافقة',
			self::ApprovalDenied->value    => 'تم رفض الموافقة',
			self::Welcome->value           => 'ترحيب',
			self::Anniversary->value       => 'ذكرى',
			self::LoyaltyReward->value     => 'مكافأة الولاء',
			self::ReferralBonus->value     => 'مكافأة الإحالة',
			self::UpsellOpportunity->value => 'فرصة بيع إضافي',
			self::CrossSell->value         => 'بيع عرضي',
			self::Newsletter->value        => 'النشرة الإخبارية',
			self::Promotional->value       => 'ترويجي',
			self::EventInvitation->value   => 'دعوة حدث',
			self::SurveyRequest->value     => 'طلب استطلاع',
			self::FeedbackRequest->value   => 'طلب تعليقات',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Email->value            => 'E-mail',
			self::Sms->value              => 'SMS',
			self::Push->value             => 'Push-besked',
			self::InApp->value            => 'I appen',
			self::Webhook->value          => 'Webhook',
			self::Api->value              => 'API',
			self::Other->value            => 'Andet',
			self::LeadAssignment->value   => 'Lead-tildeling',
			self::DealStageChange->value  => 'Aftalestadieændring',
			self::ContactFollowUp->value  => 'Kontaktopfølgning',
			self::MeetingReminder->value  => 'Mødepåmindelse',
			self::TaskAssignment->value   => 'Opgavetildeling',
			self::TicketUpdate->value     => 'Ticketopdatering',
			self::TicketEscalation->value => 'Ticketeskalering',
			self::OrderConfirmation->value => 'Ordrebekræftelse',
			self::OrderShipped->value      => 'Ordre afsendt',
			self::OrderDelivered->value    => 'Ordre leveret',
			self::OrderCancelled->value    => 'Ordre annulleret',
			self::BackInStock->value       => 'På lager igen',
			self::PriceDrop->value         => 'Prisfald',
			self::AbandonedCart->value     => 'Forladt indkøbskurv',
			self::ReviewRequest->value     => 'Anmeldelsesanmodning',
			self::WishlistReminder->value  => 'Ønskelistepåmindelse',
			self::InventoryLow->value      => 'Lav beholdning',
			self::InventoryOut->value      => 'Udsolgt',
			self::PurchaseOrder->value     => 'Indkøbsordre',
			self::InvoiceDue->value        => 'Faktura forfalder',
			self::InvoiceOverdue->value    => 'Forfalden faktura',
			self::PaymentReceived->value   => 'Betaling modtaget',
			self::PaymentFailed->value     => 'Betaling fejlede',
			self::SubscriptionRenewal->value => 'Abonnementfornyelse',
			self::SubscriptionExpiry->value  => 'Abonnementudløb',
			self::UserInvitation->value    => 'Brugerinvitation',
			self::PasswordReset->value     => 'Nulstil adgangskode',
			self::TwoFactor->value         => 'Tofaktor-godkendelse',
			self::SystemAlert->value       => 'Systemadvarsel',
			self::Maintenance->value       => 'Vedligeholdelse',
			self::BackupComplete->value    => 'Sikkerhedskopi fuldført',
			self::ReportReady->value       => 'Rapport klar',
			self::Slack->value             => 'Slack',
			self::Teams->value             => 'Microsoft Teams',
			self::Discord->value           => 'Discord',
			self::Telegram->value          => 'Telegram',
			self::WhatsApp->value          => 'WhatsApp',
			self::Messenger->value         => 'Facebook Messenger',
			self::WorkflowTrigger->value   => 'Workflow-udløser',
			self::WorkflowComplete->value  => 'Workflow fuldført',
			self::ApprovalRequest->value   => 'Godkendelsesanmodning',
			self::ApprovalGranted->value   => 'Godkendelse givet',
			self::ApprovalDenied->value    => 'Godkendelse afvist',
			self::Welcome->value           => 'Velkomst',
			self::Anniversary->value       => 'Årsdag',
			self::LoyaltyReward->value     => 'Loyalitetsbelønning',
			self::ReferralBonus->value     => 'Henvisningsbonus',
			self::UpsellOpportunity->value => 'Opsalgmulighed',
			self::CrossSell->value         => 'Krydssalg',
			self::Newsletter->value        => 'Nyhedsbrev',
			self::Promotional->value       => 'Promovering',
			self::EventInvitation->value   => 'Begivenhedsinvitation',
			self::SurveyRequest->value     => 'Spørgeskemaanmodning',
			self::FeedbackRequest->value   => 'Feedbackanmodning',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Email->value            => 'E-Mail',
			self::Sms->value              => 'SMS',
			self::Push->value             => 'Push-Benachrichtigung',
			self::InApp->value            => 'In-App',
			self::Webhook->value          => 'Webhook',
			self::Api->value              => 'API',
			self::Other->value            => 'Andere',
			self::LeadAssignment->value   => 'Lead-Zuweisung',
			self::DealStageChange->value  => 'Deal-Phasenänderung',
			self::ContactFollowUp->value  => 'Kontakt-Nachverfolgung',
			self::MeetingReminder->value  => 'Besprechungserinnerung',
			self::TaskAssignment->value   => 'Aufgabenzuweisung',
			self::TicketUpdate->value     => 'Ticket-Aktualisierung',
			self::TicketEscalation->value => 'Ticket-Eskalation',
			self::OrderConfirmation->value => 'Bestellbestätigung',
			self::OrderShipped->value      => 'Bestellung versendet',
			self::OrderDelivered->value    => 'Bestellung geliefert',
			self::OrderCancelled->value    => 'Bestellung storniert',
			self::BackInStock->value       => 'Wieder auf Lager',
			self::PriceDrop->value         => 'Preissenkung',
			self::AbandonedCart->value     => 'Verlassener Warenkorb',
			self::ReviewRequest->value     => 'Bewertungsanfrage',
			self::WishlistReminder->value  => 'Wunschliste-Erinnerung',
			self::InventoryLow->value      => 'Niedriger Lagerbestand',
			self::InventoryOut->value      => 'Ausverkauft',
			self::PurchaseOrder->value     => 'Bestellung',
			self::InvoiceDue->value        => 'Rechnung fällig',
			self::InvoiceOverdue->value    => 'Überfällige Rechnung',
			self::PaymentReceived->value   => 'Zahlung erhalten',
			self::PaymentFailed->value     => 'Zahlung fehlgeschlagen',
			self::SubscriptionRenewal->value => 'Abonnementverlängerung',
			self::SubscriptionExpiry->value  => 'Abonnementablauf',
			self::UserInvitation->value    => 'Benutzereinladung',
			self::PasswordReset->value     => 'Passwort zurücksetzen',
			self::TwoFactor->value         => 'Zwei-Faktor-Authentifizierung',
			self::SystemAlert->value       => 'Systemwarnung',
			self::Maintenance->value       => 'Wartung',
			self::BackupComplete->value    => 'Backup abgeschlossen',
			self::ReportReady->value       => 'Bericht bereit',
			self::Slack->value             => 'Slack',
			self::Teams->value             => 'Microsoft Teams',
			self::Discord->value           => 'Discord',
			self::Telegram->value          => 'Telegram',
			self::WhatsApp->value          => 'WhatsApp',
			self::Messenger->value         => 'Facebook Messenger',
			self::WorkflowTrigger->value   => 'Workflow-Trigger',
			self::WorkflowComplete->value  => 'Workflow abgeschlossen',
			self::ApprovalRequest->value   => 'Genehmigungsanfrage',
			self::ApprovalGranted->value   => 'Genehmigung erteilt',
			self::ApprovalDenied->value    => 'Genehmigung abgelehnt',
			self::Welcome->value           => 'Willkommen',
			self::Anniversary->value       => 'Jubiläum',
			self::LoyaltyReward->value     => 'Treueprämie',
			self::ReferralBonus->value     => 'Empfehlungsbonus',
			self::UpsellOpportunity->value => 'Upsell-Möglichkeit',
			self::CrossSell->value         => 'Cross-Selling',
			self::Newsletter->value        => 'Newsletter',
			self::Promotional->value       => 'Werbeaktion',
			self::EventInvitation->value   => 'Veranstaltungseinladung',
			self::SurveyRequest->value     => 'Umfrageanfrage',
			self::FeedbackRequest->value   => 'Feedback-Anfrage',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Email->value            => 'E-mail',
			self::Sms->value              => 'SMS',
			self::Push->value             => 'Notification Push',
			self::InApp->value            => 'Dans l\'application',
			self::Webhook->value          => 'Webhook',
			self::Api->value              => 'API',
			self::Other->value            => 'Autre',
			self::LeadAssignment->value   => 'Affectation de prospect',
			self::DealStageChange->value  => 'Changement d\'étape d\'affaire',
			self::ContactFollowUp->value  => 'Suivi de contact',
			self::MeetingReminder->value  => 'Rappel de réunion',
			self::TaskAssignment->value   => 'Affectation de tâche',
			self::TicketUpdate->value     => 'Mise à jour de ticket',
			self::TicketEscalation->value => 'Escalade de ticket',
			self::OrderConfirmation->value => 'Confirmation de commande',
			self::OrderShipped->value      => 'Commande expédiée',
			self::OrderDelivered->value    => 'Commande livrée',
			self::OrderCancelled->value    => 'Commande annulée',
			self::BackInStock->value       => 'De nouveau en stock',
			self::PriceDrop->value         => 'Baisse de prix',
			self::AbandonedCart->value     => 'Panier abandonné',
			self::ReviewRequest->value     => 'Demande d\'avis',
			self::WishlistReminder->value  => 'Rappel de liste de souhaits',
			self::InventoryLow->value      => 'Stock faible',
			self::InventoryOut->value      => 'Rupture de stock',
			self::PurchaseOrder->value     => 'Bon de commande',
			self::InvoiceDue->value        => 'Facture due',
			self::InvoiceOverdue->value    => 'Facture en retard',
			self::PaymentReceived->value   => 'Paiement reçu',
			self::PaymentFailed->value     => 'Paiement échoué',
			self::SubscriptionRenewal->value => 'Renouvellement d\'abonnement',
			self::SubscriptionExpiry->value  => 'Expiration d\'abonnement',
			self::UserInvitation->value    => 'Invitation utilisateur',
			self::PasswordReset->value     => 'Réinitialisation du mot de passe',
			self::TwoFactor->value         => 'Authentification à deux facteurs',
			self::SystemAlert->value       => 'Alerte système',
			self::Maintenance->value       => 'Maintenance',
			self::BackupComplete->value    => 'Sauvegarde terminée',
			self::ReportReady->value       => 'Rapport prêt',
			self::Slack->value             => 'Slack',
			self::Teams->value             => 'Microsoft Teams',
			self::Discord->value           => 'Discord',
			self::Telegram->value          => 'Telegram',
			self::WhatsApp->value          => 'WhatsApp',
			self::Messenger->value         => 'Facebook Messenger',
			self::WorkflowTrigger->value   => 'Déclencheur de workflow',
			self::WorkflowComplete->value  => 'Workflow terminé',
			self::ApprovalRequest->value   => 'Demande d\'approbation',
			self::ApprovalGranted->value   => 'Approbation accordée',
			self::ApprovalDenied->value    => 'Approbation refusée',
			self::Welcome->value           => 'Bienvenue',
			self::Anniversary->value       => 'Anniversaire',
			self::LoyaltyReward->value     => 'Récompense de fidélité',
			self::ReferralBonus->value     => 'Bonus de parrainage',
			self::UpsellOpportunity->value => 'Opportunité de vente incitative',
			self::CrossSell->value         => 'Vente croisée',
			self::Newsletter->value        => 'Newsletter',
			self::Promotional->value       => 'Promotionnel',
			self::EventInvitation->value   => 'Invitation à un événement',
			self::SurveyRequest->value     => 'Demande d\'enquête',
			self::FeedbackRequest->value   => 'Demande de retour',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Email->value            => 'אימייל',
			self::Sms->value              => 'SMS',
			self::Push->value             => 'התראה',
			self::InApp->value            => 'באפליקציה',
			self::Webhook->value          => 'Webhook',
			self::Api->value              => 'API',
			self::Other->value            => 'אחר',
			self::LeadAssignment->value   => 'הקצאת ליד',
			self::DealStageChange->value  => 'שינוי שלב עסקה',
			self::ContactFollowUp->value  => 'מעקב אחר קשר',
			self::MeetingReminder->value  => 'תזכורת לפגישה',
			self::TaskAssignment->value   => 'הקצאת משימה',
			self::TicketUpdate->value     => 'עדכון כרטיס',
			self::TicketEscalation->value => 'הסלמת כרטיס',
			self::OrderConfirmation->value => 'אישור הזמנה',
			self::OrderShipped->value      => 'הזמנה נשלחה',
			self::OrderDelivered->value    => 'הזמנה נמסרה',
			self::OrderCancelled->value    => 'הזמנה בוטלה',
			self::BackInStock->value       => 'חזר למלאי',
			self::PriceDrop->value         => 'ירידת מחיר',
			self::AbandonedCart->value     => 'עגלה נעזבה',
			self::ReviewRequest->value     => 'בקשה לחוות דעת',
			self::WishlistReminder->value  => 'תזכורת לרשימת משאלות',
			self::InventoryLow->value      => 'מלאי נמוך',
			self::InventoryOut->value      => 'אזל מהמלאי',
			self::PurchaseOrder->value     => 'הזמנת רכש',
			self::InvoiceDue->value        => 'חשבונית להגיע',
			self::InvoiceOverdue->value    => 'חשבונית באיחור',
			self::PaymentReceived->value   => 'תשלום התקבל',
			self::PaymentFailed->value     => 'תשלום נכשל',
			self::SubscriptionRenewal->value => 'חידוש מנוי',
			self::SubscriptionExpiry->value  => 'תפוגת מנוי',
			self::UserInvitation->value    => 'הזמנת משתמש',
			self::PasswordReset->value     => 'איפוס סיסמה',
			self::TwoFactor->value         => 'אימות דו-שלבי',
			self::SystemAlert->value       => 'התראת מערכת',
			self::Maintenance->value       => 'תחזוקה',
			self::BackupComplete->value    => 'גיבוי הושלם',
			self::ReportReady->value       => 'דוח מוכן',
			self::Slack->value             => 'סלאק',
			self::Teams->value             => 'Microsoft Teams',
			self::Discord->value           => 'Discord',
			self::Telegram->value          => 'Telegram',
			self::WhatsApp->value          => 'WhatsApp',
			self::Messenger->value         => 'Facebook Messenger',
			self::WorkflowTrigger->value   => 'הפעלת זרימת עבודה',
			self::WorkflowComplete->value  => 'זרימת עבודה הושלמה',
			self::ApprovalRequest->value   => 'בקשת אישור',
			self::ApprovalGranted->value   => 'אישור ניתן',
			self::ApprovalDenied->value    => 'אישור נדחה',
			self::Welcome->value           => 'ברוכים הבאים',
			self::Anniversary->value       => 'יום שנה',
			self::LoyaltyReward->value     => 'פרס נאמנות',
			self::ReferralBonus->value     => 'בונוס המלצה',
			self::UpsellOpportunity->value => 'הזדמנות למכירה נוספת',
			self::CrossSell->value         => 'מכירה צולבת',
			self::Newsletter->value        => 'עלון',
			self::Promotional->value       => 'קידומי',
			self::EventInvitation->value   => 'הזמנה לאירוע',
			self::SurveyRequest->value     => 'בקשת סקר',
			self::FeedbackRequest->value   => 'בקשת משוב',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Email->value            => 'Email',
			self::Sms->value              => 'SMS',
			self::Push->value             => 'Notifica Push',
			self::InApp->value            => 'In-App',
			self::Webhook->value          => 'Webhook',
			self::Api->value              => 'API',
			self::Other->value            => 'Altro',
			self::LeadAssignment->value   => 'Assegnazione Lead',
			self::DealStageChange->value  => 'Cambio Stadio Affare',
			self::ContactFollowUp->value  => 'Follow-up Contatto',
			self::MeetingReminder->value  => 'Promemoria Riunione',
			self::TaskAssignment->value   => 'Assegnazione Attività',
			self::TicketUpdate->value     => 'Aggiornamento Ticket',
			self::TicketEscalation->value => 'Escalation Ticket',
			self::OrderConfirmation->value => 'Conferma Ordine',
			self::OrderShipped->value      => 'Ordine Spedito',
			self::OrderDelivered->value    => 'Ordine Consegnato',
			self::OrderCancelled->value    => 'Ordine Annullato',
			self::BackInStock->value       => 'Disponibile di Nuovo',
			self::PriceDrop->value         => 'Calo Prezzo',
			self::AbandonedCart->value     => 'Carrello Abbandonato',
			self::ReviewRequest->value     => 'Richiesta Recensione',
			self::WishlistReminder->value  => 'Promemoria Lista Desideri',
			self::InventoryLow->value      => 'Scorte Basse',
			self::InventoryOut->value      => 'Esaurito',
			self::PurchaseOrder->value     => 'Ordine d\'Acquisto',
			self::InvoiceDue->value        => 'Fattura Scadente',
			self::InvoiceOverdue->value    => 'Fattura Scaduta',
			self::PaymentReceived->value   => 'Pagamento Ricevuto',
			self::PaymentFailed->value     => 'Pagamento Fallito',
			self::SubscriptionRenewal->value => 'Rinnovo Abbonamento',
			self::SubscriptionExpiry->value  => 'Scadenza Abbonamento',
			self::UserInvitation->value    => 'Invito Utente',
			self::PasswordReset->value     => 'Reset Password',
			self::TwoFactor->value         => 'Autenticazione a Due Fattori',
			self::SystemAlert->value       => 'Allerta Sistema',
			self::Maintenance->value       => 'Manutenzione',
			self::BackupComplete->value    => 'Backup Completato',
			self::ReportReady->value       => 'Report Pronto',
			self::Slack->value             => 'Slack',
			self::Teams->value             => 'Microsoft Teams',
			self::Discord->value           => 'Discord',
			self::Telegram->value          => 'Telegram',
			self::WhatsApp->value          => 'WhatsApp',
			self::Messenger->value         => 'Facebook Messenger',
			self::WorkflowTrigger->value   => 'Trigger Workflow',
			self::WorkflowComplete->value  => 'Workflow Completato',
			self::ApprovalRequest->value   => 'Richiesta Approvazione',
			self::ApprovalGranted->value   => 'Approvazione Concessa',
			self::ApprovalDenied->value    => 'Approvazione Negata',
			self::Welcome->value           => 'Benvenuto',
			self::Anniversary->value       => 'Anniversario',
			self::LoyaltyReward->value     => 'Ricompensa Fedeltà',
			self::ReferralBonus->value     => 'Bonus Referral',
			self::UpsellOpportunity->value => 'Opportunità Upsell',
			self::CrossSell->value         => 'Cross-selling',
			self::Newsletter->value        => 'Newsletter',
			self::Promotional->value       => 'Promozionale',
			self::EventInvitation->value   => 'Invito Evento',
			self::SurveyRequest->value     => 'Richiesta Sondaggio',
			self::FeedbackRequest->value   => 'Richiesta Feedback',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Email->value            => 'メール',
			self::Sms->value              => 'SMS',
			self::Push->value             => 'プッシュ通知',
			self::InApp->value            => 'アプリ内',
			self::Webhook->value          => 'Webhook',
			self::Api->value              => 'API',
			self::Other->value            => 'その他',
			self::LeadAssignment->value   => 'リード割り当て',
			self::DealStageChange->value  => '商談ステージ変更',
			self::ContactFollowUp->value  => 'コンタクトフォローアップ',
			self::MeetingReminder->value  => '会議リマインダー',
			self::TaskAssignment->value   => 'タスク割り当て',
			self::TicketUpdate->value     => 'チケット更新',
			self::TicketEscalation->value => 'チケットエスカレーション',
			self::OrderConfirmation->value => '注文確認',
			self::OrderShipped->value      => '注文発送済み',
			self::OrderDelivered->value    => '注文配達済み',
			self::OrderCancelled->value    => '注文キャンセル',
			self::BackInStock->value       => '再入荷',
			self::PriceDrop->value         => '価格低下',
			self::AbandonedCart->value     => '放棄されたカート',
			self::ReviewRequest->value     => 'レビュー依頼',
			self::WishlistReminder->value  => 'ウィッシュリストリマインダー',
			self::InventoryLow->value      => '在庫不足',
			self::InventoryOut->value      => '在庫切れ',
			self::PurchaseOrder->value     => '発注書',
			self::InvoiceDue->value        => '請求書期日',
			self::InvoiceOverdue->value    => '延滞請求書',
			self::PaymentReceived->value   => '支払い受領',
			self::PaymentFailed->value     => '支払い失敗',
			self::SubscriptionRenewal->value => 'サブスクリプション更新',
			self::SubscriptionExpiry->value  => 'サブスクリプション期限切れ',
			self::UserInvitation->value    => 'ユーザー招待',
			self::PasswordReset->value     => 'パスワードリセット',
			self::TwoFactor->value         => '二要素認証',
			self::SystemAlert->value       => 'システムアラート',
			self::Maintenance->value       => 'メンテナンス',
			self::BackupComplete->value    => 'バックアップ完了',
			self::ReportReady->value       => 'レポート準備完了',
			self::Slack->value             => 'Slack',
			self::Teams->value             => 'Microsoft Teams',
			self::Discord->value           => 'Discord',
			self::Telegram->value          => 'Telegram',
			self::WhatsApp->value          => 'WhatsApp',
			self::Messenger->value         => 'Facebook Messenger',
			self::WorkflowTrigger->value   => 'ワークフロートリガー',
			self::WorkflowComplete->value  => 'ワークフロー完了',
			self::ApprovalRequest->value   => '承認依頼',
			self::ApprovalGranted->value   => '承認済み',
			self::ApprovalDenied->value    => '承認却下',
			self::Welcome->value           => 'ウェルカム',
			self::Anniversary->value       => '記念日',
			self::LoyaltyReward->value     => 'ロイヤリティ報酬',
			self::ReferralBonus->value     => '紹介ボーナス',
			self::UpsellOpportunity->value => 'アップセル機会',
			self::CrossSell->value         => 'クロスセル',
			self::Newsletter->value        => 'ニュースレター',
			self::Promotional->value       => 'プロモーション',
			self::EventInvitation->value   => 'イベント招待',
			self::SurveyRequest->value     => 'アンケート依頼',
			self::FeedbackRequest->value   => 'フィードバック依頼',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Email->value            => 'E-mail',
			self::Sms->value              => 'SMS',
			self::Push->value             => 'Push-melding',
			self::InApp->value            => 'In-app',
			self::Webhook->value          => 'Webhook',
			self::Api->value              => 'API',
			self::Other->value            => 'Anders',
			self::LeadAssignment->value   => 'Leadtoewijzing',
			self::DealStageChange->value  => 'Dealstadiumwijziging',
			self::ContactFollowUp->value  => 'Contactfollow-up',
			self::MeetingReminder->value  => 'Afspraakherinnering',
			self::TaskAssignment->value   => 'Taaktoewijzing',
			self::TicketUpdate->value     => 'Ticketupdate',
			self::TicketEscalation->value => 'Ticketescalatie',
			self::OrderConfirmation->value => 'Bestelbevestiging',
			self::OrderShipped->value      => 'Bestelling verzonden',
			self::OrderDelivered->value    => 'Bestelling geleverd',
			self::OrderCancelled->value    => 'Bestelling geannuleerd',
			self::BackInStock->value       => 'Weer op voorraad',
			self::PriceDrop->value         => 'Prijsdaling',
			self::AbandonedCart->value     => 'Verlaten winkelwagen',
			self::ReviewRequest->value     => 'Recensieverzoek',
			self::WishlistReminder->value  => 'Verlanglijstherinnering',
			self::InventoryLow->value      => 'Lage voorraad',
			self::InventoryOut->value      => 'Uitverkocht',
			self::PurchaseOrder->value     => 'Inkooporder',
			self::InvoiceDue->value        => 'Factuur vervalt',
			self::InvoiceOverdue->value    => 'Achterstallige factuur',
			self::PaymentReceived->value   => 'Betaling ontvangen',
			self::PaymentFailed->value     => 'Betaling mislukt',
			self::SubscriptionRenewal->value => 'Abonnementverlenging',
			self::SubscriptionExpiry->value  => 'Abonnementverloop',
			self::UserInvitation->value    => 'Uitnodiging gebruiker',
			self::PasswordReset->value     => 'Wachtwoord resetten',
			self::TwoFactor->value         => 'Tweefactorauthenticatie',
			self::SystemAlert->value       => 'Systeemwaarschuwing',
			self::Maintenance->value       => 'Onderhoud',
			self::BackupComplete->value    => 'Back-up voltooid',
			self::ReportReady->value       => 'Rapport gereed',
			self::Slack->value             => 'Slack',
			self::Teams->value             => 'Microsoft Teams',
			self::Discord->value           => 'Discord',
			self::Telegram->value          => 'Telegram',
			self::WhatsApp->value          => 'WhatsApp',
			self::Messenger->value         => 'Facebook Messenger',
			self::WorkflowTrigger->value   => 'Workflowtrigger',
			self::WorkflowComplete->value  => 'Workflow voltooid',
			self::ApprovalRequest->value   => 'Goedkeuringsverzoek',
			self::ApprovalGranted->value   => 'Goedkeuring verleend',
			self::ApprovalDenied->value    => 'Goedkeuring geweigerd',
			self::Welcome->value           => 'Welkom',
			self::Anniversary->value       => 'Verjaardag',
			self::LoyaltyReward->value     => 'Loyaliteitsbeloning',
			self::ReferralBonus->value     => 'Verwijzingsbonus',
			self::UpsellOpportunity->value => 'Upselkans',
			self::CrossSell->value         => 'Cross-selling',
			self::Newsletter->value        => 'Nieuwsbrief',
			self::Promotional->value       => 'Promotioneel',
			self::EventInvitation->value   => 'Uitnodiging evenement',
			self::SurveyRequest->value     => 'Enquêteverzoek',
			self::FeedbackRequest->value   => 'Feedbackverzoek',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Email->value            => 'E-mail',
			self::Sms->value              => 'SMS',
			self::Push->value             => 'Powiadomienie push',
			self::InApp->value            => 'W aplikacji',
			self::Webhook->value          => 'Webhook',
			self::Api->value              => 'API',
			self::Other->value            => 'Inny',
			self::LeadAssignment->value   => 'Przypisanie leada',
			self::DealStageChange->value  => 'Zmiana etapu transakcji',
			self::ContactFollowUp->value  => 'Kontakt follow-up',
			self::MeetingReminder->value  => 'Przypomnienie o spotkaniu',
			self::TaskAssignment->value   => 'Przypisanie zadania',
			self::TicketUpdate->value     => 'Aktualizacja zgłoszenia',
			self::TicketEscalation->value => 'Eskalacja zgłoszenia',
			self::OrderConfirmation->value => 'Potwierdzenie zamówienia',
			self::OrderShipped->value      => 'Zamówienie wysłane',
			self::OrderDelivered->value    => 'Zamówienie dostarczone',
			self::OrderCancelled->value    => 'Zamówienie anulowane',
			self::BackInStock->value       => 'Dostępne ponownie',
			self::PriceDrop->value         => 'Spadek ceny',
			self::AbandonedCart->value     => 'Porzucony koszyk',
			self::ReviewRequest->value     => 'Prośba o recenzję',
			self::WishlistReminder->value  => 'Przypomnienie z listy życzeń',
			self::InventoryLow->value      => 'Niski stan magazynowy',
			self::InventoryOut->value      => 'Wyprzedane',
			self::PurchaseOrder->value     => 'Zamówienie zakupu',
			self::InvoiceDue->value        => 'Faktura do zapłaty',
			self::InvoiceOverdue->value    => 'Faktura przeterminowana',
			self::PaymentReceived->value   => 'Płatność otrzymana',
			self::PaymentFailed->value     => 'Płatność nieudana',
			self::SubscriptionRenewal->value => 'Odnowienie subskrypcji',
			self::SubscriptionExpiry->value  => 'Wygaśnięcie subskrypcji',
			self::UserInvitation->value    => 'Zaproszenie użytkownika',
			self::PasswordReset->value     => 'Resetowanie hasła',
			self::TwoFactor->value         => 'Uwierzytelnianie dwuetapowe',
			self::SystemAlert->value       => 'Alert systemowy',
			self::Maintenance->value       => 'Konserwacja',
			self::BackupComplete->value    => 'Kopia zapasowa ukończona',
			self::ReportReady->value       => 'Raport gotowy',
			self::Slack->value             => 'Slack',
			self::Teams->value             => 'Microsoft Teams',
			self::Discord->value           => 'Discord',
			self::Telegram->value          => 'Telegram',
			self::WhatsApp->value          => 'WhatsApp',
			self::Messenger->value         => 'Facebook Messenger',
			self::WorkflowTrigger->value   => 'Wyzwalacz workflow',
			self::WorkflowComplete->value  => 'Workflow ukończony',
			self::ApprovalRequest->value   => 'Prośba o zatwierdzenie',
			self::ApprovalGranted->value   => 'Zatwierdzenie przyznane',
			self::ApprovalDenied->value    => 'Zatwierdzenie odmówione',
			self::Welcome->value           => 'Powitanie',
			self::Anniversary->value       => 'Rocznica',
			self::LoyaltyReward->value     => 'Nagroda lojalnościowa',
			self::ReferralBonus->value     => 'Bonus poleceniowy',
			self::UpsellOpportunity->value => 'Okazja do upsell',
			self::CrossSell->value         => 'Cross-selling',
			self::Newsletter->value        => 'Newsletter',
			self::Promotional->value       => 'Promocyjny',
			self::EventInvitation->value   => 'Zaproszenie na wydarzenie',
			self::SurveyRequest->value     => 'Prośba o ankietę',
			self::FeedbackRequest->value   => 'Prośba o feedback',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Email->value            => 'Электронная почта',
			self::Sms->value              => 'SMS',
			self::Push->value             => 'Push-уведомление',
			self::InApp->value            => 'В приложении',
			self::Webhook->value          => 'Webhook',
			self::Api->value              => 'API',
			self::Other->value            => 'Другое',
			self::LeadAssignment->value   => 'Назначение лида',
			self::DealStageChange->value  => 'Изменение стадии сделки',
			self::ContactFollowUp->value  => 'Контакта follow-up',
			self::MeetingReminder->value  => 'Напоминание о встрече',
			self::TaskAssignment->value   => 'Назначение задачи',
			self::TicketUpdate->value     => 'Обновление тикета',
			self::TicketEscalation->value => 'Эскалация тикета',
			self::OrderConfirmation->value => 'Подтверждение заказа',
			self::OrderShipped->value      => 'Заказ отправлен',
			self::OrderDelivered->value    => 'Заказ доставлен',
			self::OrderCancelled->value    => 'Заказ отменен',
			self::BackInStock->value       => 'Снова в наличии',
			self::PriceDrop->value         => 'Снижение цены',
			self::AbandonedCart->value     => 'Брошенная корзина',
			self::ReviewRequest->value     => 'Запрос отзыва',
			self::WishlistReminder->value  => 'Напоминание списка желаний',
			self::InventoryLow->value      => 'Низкий запас',
			self::InventoryOut->value      => 'Нет в наличии',
			self::PurchaseOrder->value     => 'Заказ на покупку',
			self::InvoiceDue->value        => 'Счет к оплате',
			self::InvoiceOverdue->value    => 'Просроченный счет',
			self::PaymentReceived->value   => 'Платеж получен',
			self::PaymentFailed->value     => 'Платеж не прошел',
			self::SubscriptionRenewal->value => 'Продление подписки',
			self::SubscriptionExpiry->value  => 'Истечение подписки',
			self::UserInvitation->value    => 'Приглашение пользователя',
			self::PasswordReset->value     => 'Сброс пароля',
			self::TwoFactor->value         => 'Двухфакторная аутентификация',
			self::SystemAlert->value       => 'Системное предупреждение',
			self::Maintenance->value       => 'Обслуживание',
			self::BackupComplete->value    => 'Резервное копирование завершено',
			self::ReportReady->value       => 'Отчет готов',
			self::Slack->value             => 'Slack',
			self::Teams->value             => 'Microsoft Teams',
			self::Discord->value           => 'Discord',
			self::Telegram->value          => 'Telegram',
			self::WhatsApp->value          => 'WhatsApp',
			self::Messenger->value         => 'Facebook Messenger',
			self::WorkflowTrigger->value   => 'Триггер workflow',
			self::WorkflowComplete->value  => 'Workflow завершен',
			self::ApprovalRequest->value   => 'Запрос на одобрение',
			self::ApprovalGranted->value   => 'Одобрение предоставлено',
			self::ApprovalDenied->value    => 'Одобрение отклонено',
			self::Welcome->value           => 'Приветствие',
			self::Anniversary->value       => 'Годовщина',
			self::LoyaltyReward->value     => 'Вознаграждение лояльности',
			self::ReferralBonus->value     => 'Реферальный бонус',
			self::UpsellOpportunity->value => 'Возможность апселлинга',
			self::CrossSell->value         => 'Кросс-продажи',
			self::Newsletter->value        => 'Рассылка',
			self::Promotional->value       => 'Промо',
			self::EventInvitation->value   => 'Приглашение на мероприятие',
			self::SurveyRequest->value     => 'Запрос опроса',
			self::FeedbackRequest->value   => 'Запрос обратной связи',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Email->value            => 'E-posta',
			self::Sms->value              => 'SMS',
			self::Push->value             => 'Push Bildirimi',
			self::InApp->value            => 'Uygulama İçi',
			self::Webhook->value          => 'Webhook',
			self::Api->value              => 'API',
			self::Other->value            => 'Diğer',
			self::LeadAssignment->value   => 'Lead Ataması',
			self::DealStageChange->value  => 'Deal Aşama Değişikliği',
			self::ContactFollowUp->value  => 'İletişim Takibi',
			self::MeetingReminder->value  => 'Toplantı Hatırlatıcısı',
			self::TaskAssignment->value   => 'Görev Ataması',
			self::TicketUpdate->value     => 'Ticket Güncellemesi',
			self::TicketEscalation->value => 'Ticket Eskalasyonu',
			self::OrderConfirmation->value => 'Sipariş Onayı',
			self::OrderShipped->value      => 'Sipariş Gönderildi',
			self::OrderDelivered->value    => 'Sipariş Teslim Edildi',
			self::OrderCancelled->value    => 'Sipariş İptal Edildi',
			self::BackInStock->value       => 'Tekrar Stokta',
			self::PriceDrop->value         => 'Fiyat Düşüşü',
			self::AbandonedCart->value     => 'Terk Edilen Sepet',
			self::ReviewRequest->value     => 'Değerlendirme İsteği',
			self::WishlistReminder->value  => 'İstek Listesi Hatırlatıcısı',
			self::InventoryLow->value      => 'Düşük Stok',
			self::InventoryOut->value      => 'Stokta Yok',
			self::PurchaseOrder->value     => 'Satın Alma Siparişi',
			self::InvoiceDue->value        => 'Fatura Vadesi',
			self::InvoiceOverdue->value    => 'Gecikmiş Fatura',
			self::PaymentReceived->value   => 'Ödeme Alındı',
			self::PaymentFailed->value     => 'Ödeme Başarısız',
			self::SubscriptionRenewal->value => 'Abonelik Yenileme',
			self::SubscriptionExpiry->value  => 'Abonelik Süresi Dolması',
			self::UserInvitation->value    => 'Kullanıcı Daveti',
			self::PasswordReset->value     => 'Şifre Sıfırlama',
			self::TwoFactor->value         => 'İki Faktörlü Doğrulama',
			self::SystemAlert->value       => 'Sistem Uyarısı',
			self::Maintenance->value       => 'Bakım',
			self::BackupComplete->value    => 'Yedekleme Tamamlandı',
			self::ReportReady->value       => 'Rapor Hazır',
			self::Slack->value             => 'Slack',
			self::Teams->value             => 'Microsoft Teams',
			self::Discord->value           => 'Discord',
			self::Telegram->value          => 'Telegram',
			self::WhatsApp->value          => 'WhatsApp',
			self::Messenger->value         => 'Facebook Messenger',
			self::WorkflowTrigger->value   => 'İş Akışı Tetikleyicisi',
			self::WorkflowComplete->value  => 'İş Akışı Tamamlandı',
			self::ApprovalRequest->value   => 'Onay İsteği',
			self::ApprovalGranted->value   => 'Onay Verildi',
			self::ApprovalDenied->value    => 'Onay Reddedildi',
			self::Welcome->value           => 'Hoş Geldiniz',
			self::Anniversary->value       => 'Yıl Dönümü',
			self::LoyaltyReward->value     => 'Sadakat Ödülü',
			self::ReferralBonus->value     => 'Referans Bonusu',
			self::UpsellOpportunity->value => 'Üst Satış Fırsatı',
			self::CrossSell->value         => 'Çapraz Satış',
			self::Newsletter->value        => 'Bülten',
			self::Promotional->value       => 'Promosyon',
			self::EventInvitation->value   => 'Etkinlik Daveti',
			self::SurveyRequest->value     => 'Anket İsteği',
			self::FeedbackRequest->value   => 'Geri Bildirim İsteği',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Email->value            => '电子邮件',
			self::Sms->value              => '短信',
			self::Push->value             => '推送通知',
			self::InApp->value            => '应用内',
			self::Webhook->value          => 'Webhook',
			self::Api->value              => 'API',
			self::Other->value            => '其他',
			self::LeadAssignment->value   => '线索分配',
			self::DealStageChange->value  => '交易阶段变更',
			self::ContactFollowUp->value  => '联系人跟进',
			self::MeetingReminder->value  => '会议提醒',
			self::TaskAssignment->value   => '任务分配',
			self::TicketUpdate->value     => '工单更新',
			self::TicketEscalation->value => '工单升级',
			self::OrderConfirmation->value => '订单确认',
			self::OrderShipped->value      => '订单已发货',
			self::OrderDelivered->value    => '订单已送达',
			self::OrderCancelled->value    => '订单已取消',
			self::BackInStock->value       => '恢复库存',
			self::PriceDrop->value         => '价格下降',
			self::AbandonedCart->value     => '放弃的购物车',
			self::ReviewRequest->value     => '评价请求',
			self::WishlistReminder->value  => '愿望清单提醒',
			self::InventoryLow->value      => '库存不足',
			self::InventoryOut->value      => '缺货',
			self::PurchaseOrder->value     => '采购订单',
			self::InvoiceDue->value        => '发票到期',
			self::InvoiceOverdue->value    => '逾期发票',
			self::PaymentReceived->value   => '付款已收到',
			self::PaymentFailed->value     => '付款失败',
			self::SubscriptionRenewal->value => '订阅续订',
			self::SubscriptionExpiry->value  => '订阅到期',
			self::UserInvitation->value    => '用户邀请',
			self::PasswordReset->value     => '密码重置',
			self::TwoFactor->value         => '双重认证',
			self::SystemAlert->value       => '系统警报',
			self::Maintenance->value       => '维护',
			self::BackupComplete->value    => '备份完成',
			self::ReportReady->value       => '报告就绪',
			self::Slack->value             => 'Slack',
			self::Teams->value             => 'Microsoft Teams',
			self::Discord->value           => 'Discord',
			self::Telegram->value          => 'Telegram',
			self::WhatsApp->value          => 'WhatsApp',
			self::Messenger->value         => 'Facebook Messenger',
			self::WorkflowTrigger->value   => '工作流触发器',
			self::WorkflowComplete->value  => '工作流完成',
			self::ApprovalRequest->value   => '审批请求',
			self::ApprovalGranted->value   => '已批准',
			self::ApprovalDenied->value    => '已拒绝',
			self::Welcome->value           => '欢迎',
			self::Anniversary->value       => '周年纪念',
			self::LoyaltyReward->value     => '忠诚度奖励',
			self::ReferralBonus->value     => '推荐奖金',
			self::UpsellOpportunity->value => '追加销售机会',
			self::CrossSell->value         => '交叉销售',
			self::Newsletter->value        => '新闻通讯',
			self::Promotional->value       => '促销',
			self::EventInvitation->value   => '活动邀请',
			self::SurveyRequest->value     => '调查请求',
			self::FeedbackRequest->value   => '反馈请求',
		];
	}
}
