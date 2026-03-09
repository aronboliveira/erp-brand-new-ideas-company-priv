<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum EmailTemplateType: string
{
	// Core email communications (from NotificationTemplateType)
	case Email           = 'email';

		// CRM-specific emails
	case LeadAssignment  = 'lead_assignment';
	case DealStageChange = 'deal_stage_change';
	case ContactFollowUp = 'contact_follow_up';
	case MeetingReminder = 'meeting_reminder';
	case TaskAssignment  = 'task_assignment';
	case TicketUpdate    = 'ticket_update';
	case TicketEscalation = 'ticket_escalation';

		// E-commerce emails
	case OrderConfirmation   = 'order_confirmation';
	case OrderShipped        = 'order_shipped';
	case OrderDelivered      = 'order_delivered';
	case OrderCancelled      = 'order_cancelled';
	case BackInStock         = 'back_in_stock';
	case PriceDrop          = 'price_drop';
	case AbandonedCart      = 'abandoned_cart';
	case ReviewRequest      = 'review_request';
	case WishlistReminder   = 'wishlist_reminder';

		// Financial & operational emails
	case InvoiceDue          = 'invoice_due';
	case InvoiceOverdue      = 'invoice_overdue';
	case PaymentReceived     = 'payment_received';
	case PaymentFailed       = 'payment_failed';
	case SubscriptionRenewal = 'subscription_renewal';
	case SubscriptionExpiry  = 'subscription_expiry';
	case PurchaseOrder       = 'purchase_order';
	case InventoryLow        = 'inventory_low';
	case InventoryOut        = 'inventory_out';

		// System & user management emails
	case UserInvitation      = 'user_invitation';
	case PasswordReset       = 'password_reset';
	case TwoFactor           = 'two_factor';
	case SystemAlert         = 'system_alert';
	case Maintenance         = 'maintenance';
	case BackupComplete      = 'backup_complete';
	case ReportReady         = 'report_ready';
	case Welcome             = 'welcome';

		// Marketing & campaign emails
	case Newsletter          = 'newsletter';
	case Promotional         = 'promotional';
	case EventInvitation     = 'event_invitation';
	case SurveyRequest       = 'survey_request';
	case FeedbackRequest     = 'feedback_request';

		// Customer lifecycle emails
	case Anniversary         = 'anniversary';
	case LoyaltyReward       = 'loyalty_reward';
	case ReferralBonus       = 'referral_bonus';
	case UpsellOpportunity   = 'upsell_opportunity';
	case CrossSell           = 'cross_sell';

		// Workflow & approval emails
	case WorkflowTrigger     = 'workflow_trigger';
	case WorkflowComplete    = 'workflow_complete';
	case ApprovalRequest     = 'approval_request';
	case ApprovalGranted     = 'approval_granted';
	case ApprovalDenied      = 'approval_denied';

		// Specialized email-only templates
	case EmailVerification   = 'email_verification';
	case WelcomeSeries       = 'welcome_series';
	case CartAbandonment     = 'cart_abandonment';
	case ReEngagement        = 're_engagement';
	case Winback             = 'winback';
	case PostPurchase        = 'post_purchase';
	case Onboarding          = 'onboarding';
	case Offboarding         = 'offboarding';
	case ContractRenewal     = 'contract_renewal';
	case QuoteFollowUp       = 'quote_follow_up';
	case ProposalSent        = 'proposal_sent';
	case ServiceReminder     = 'service_reminder';
	case AppointmentConfirm  = 'appointment_confirm';
	case AppointmentReminder = 'appointment_reminder';
	case WebinarInvite       = 'webinar_invite';
	case WebinarReminder     = 'webinar_reminder';
	case WebinarFollowUp     = 'webinar_follow_up';
	case DownloadConfirm     = 'download_confirm';
	case TrialExpiry         = 'trial_expiry';
	case CreditLimit         = 'credit_limit';
	case Statement           = 'statement';
	case AnnualReport        = 'annual_report';
	case QuarterlyReview     = 'quarterly_review';
	case PerformanceReview   = 'performance_review';

		// Integration notifications (email versions)
	case SlackNotification   = 'slack_notification';
	case TeamsNotification   = 'teams_notification';
	case ZapierWebhook       = 'zapier_webhook';

		// Other
	case Other               = 'other';

	public static function normalize(string|null|self $value = null): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Email;

		$normalizedValue = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($value ?? '')));
		return match ($normalizedValue) {
			// Core email
			'email', 'mail', 'e-mail' => self::Email,

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

			// Financial & operational
			'invoicedue', 'invoice_due' => self::InvoiceDue,
			'invoiceoverdue', 'invoice_overdue' => self::InvoiceOverdue,
			'paymentreceived', 'payment_received' => self::PaymentReceived,
			'paymentfailed', 'payment_failed' => self::PaymentFailed,
			'subscriptionrenewal', 'subscription_renewal' => self::SubscriptionRenewal,
			'subscriptionexpiry', 'subscription_expiry' => self::SubscriptionExpiry,
			'purchaseorder', 'purchase_order' => self::PurchaseOrder,
			'inventorylow', 'inventory_low' => self::InventoryLow,
			'inventoryout', 'inventory_out' => self::InventoryOut,

			// System & user management
			'userinvitation', 'user_invitation' => self::UserInvitation,
			'passwordreset', 'password_reset' => self::PasswordReset,
			'twofactor', 'two_factor', '2fa' => self::TwoFactor,
			'systemalert', 'system_alert' => self::SystemAlert,
			'maintenance' => self::Maintenance,
			'backupcomplete', 'backup_complete' => self::BackupComplete,
			'reportready', 'report_ready' => self::ReportReady,
			'welcome' => self::Welcome,

			// Marketing & campaign
			'newsletter' => self::Newsletter,
			'promotional' => self::Promotional,
			'eventinvitation', 'event_invitation' => self::EventInvitation,
			'surveyrequest', 'survey_request' => self::SurveyRequest,
			'feedbackrequest', 'feedback_request' => self::FeedbackRequest,

			// Customer lifecycle
			'anniversary' => self::Anniversary,
			'loyaltyreward', 'loyalty_reward' => self::LoyaltyReward,
			'referralbonus', 'referral_bonus' => self::ReferralBonus,
			'upsellopportunity', 'upsell_opportunity' => self::UpsellOpportunity,
			'crosssell', 'cross_sell' => self::CrossSell,

			// Workflow & approval
			'workflowtrigger', 'workflow_trigger' => self::WorkflowTrigger,
			'workflowcomplete', 'workflow_complete' => self::WorkflowComplete,
			'approvalrequest', 'approval_request' => self::ApprovalRequest,
			'approvalgranted', 'approval_granted' => self::ApprovalGranted,
			'approvaldenied', 'approval_denied' => self::ApprovalDenied,

			// Specialized email-only
			'emailverification', 'email_verification', 'verifyemail' => self::EmailVerification,
			'welcomeseries', 'welcome_series' => self::WelcomeSeries,
			'cartabandonment', 'cart_abandonment' => self::CartAbandonment,
			'reengagement', 're_engagement', 'reengagementcampaign' => self::ReEngagement,
			'winback', 'win_back', 'winbackcampaign' => self::Winback,
			'postpurchase', 'post_purchase' => self::PostPurchase,
			'onboarding' => self::Onboarding,
			'offboarding' => self::Offboarding,
			'contractrenewal', 'contract_renewal' => self::ContractRenewal,
			'quotefollowup', 'quote_follow_up' => self::QuoteFollowUp,
			'proposalsent', 'proposal_sent' => self::ProposalSent,
			'servicereminder', 'service_reminder' => self::ServiceReminder,
			'appointmentconfirm', 'appointment_confirm' => self::AppointmentConfirm,
			'appointmentreminder', 'appointment_reminder' => self::AppointmentReminder,
			'webinarinvite', 'webinar_invite' => self::WebinarInvite,
			'webinarreminder', 'webinar_reminder' => self::WebinarReminder,
			'webinarfollowup', 'webinar_follow_up' => self::WebinarFollowUp,
			'downloadconfirm', 'download_confirm' => self::DownloadConfirm,
			'trialexpiry', 'trial_expiry' => self::TrialExpiry,
			'creditlimit', 'credit_limit' => self::CreditLimit,
			'statement' => self::Statement,
			'annualreport', 'annual_report' => self::AnnualReport,
			'quarterlyreview', 'quarterly_review' => self::QuarterlyReview,
			'performancereview', 'performance_review' => self::PerformanceReview,

			// Integration notifications
			'slacknotification', 'slack_notification' => self::SlackNotification,
			'teamsnotification', 'teams_notification' => self::TeamsNotification,
			'zapierwebhook', 'zapier_webhook' => self::ZapierWebhook,

			// Other
			'other', 'custom', 'unknown' => self::Other,

			default => self::Email,
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

	public static function labelsEn(): array
	{
		return [
			self::Email->value           => 'Email',
			self::LeadAssignment->value  => 'Lead Assignment',
			self::DealStageChange->value => 'Deal Stage Change',
			self::ContactFollowUp->value => 'Contact Follow-up',
			self::MeetingReminder->value => 'Meeting Reminder',
			self::TaskAssignment->value  => 'Task Assignment',
			self::TicketUpdate->value    => 'Ticket Update',
			self::TicketEscalation->value => 'Ticket Escalation',
			self::OrderConfirmation->value => 'Order Confirmation',
			self::OrderShipped->value    => 'Order Shipped',
			self::OrderDelivered->value  => 'Order Delivered',
			self::OrderCancelled->value  => 'Order Cancelled',
			self::BackInStock->value     => 'Back In Stock',
			self::PriceDrop->value       => 'Price Drop Alert',
			self::AbandonedCart->value   => 'Abandoned Cart',
			self::ReviewRequest->value   => 'Review Request',
			self::WishlistReminder->value => 'Wishlist Reminder',
			self::InvoiceDue->value      => 'Invoice Due',
			self::InvoiceOverdue->value  => 'Invoice Overdue',
			self::PaymentReceived->value => 'Payment Received',
			self::PaymentFailed->value   => 'Payment Failed',
			self::SubscriptionRenewal->value => 'Subscription Renewal',
			self::SubscriptionExpiry->value => 'Subscription Expiry',
			self::PurchaseOrder->value   => 'Purchase Order',
			self::InventoryLow->value    => 'Low Inventory Alert',
			self::InventoryOut->value    => 'Out of Stock Alert',
			self::UserInvitation->value  => 'User Invitation',
			self::PasswordReset->value   => 'Password Reset',
			self::TwoFactor->value       => 'Two-Factor Authentication',
			self::SystemAlert->value     => 'System Alert',
			self::Maintenance->value     => 'Maintenance Notice',
			self::BackupComplete->value  => 'Backup Complete',
			self::ReportReady->value     => 'Report Ready',
			self::Welcome->value         => 'Welcome Email',
			self::Newsletter->value      => 'Newsletter',
			self::Promotional->value     => 'Promotional',
			self::EventInvitation->value => 'Event Invitation',
			self::SurveyRequest->value   => 'Survey Request',
			self::FeedbackRequest->value => 'Feedback Request',
			self::Anniversary->value     => 'Anniversary',
			self::LoyaltyReward->value   => 'Loyalty Reward',
			self::ReferralBonus->value   => 'Referral Bonus',
			self::UpsellOpportunity->value => 'Upsell Opportunity',
			self::CrossSell->value       => 'Cross-sell',
			self::WorkflowTrigger->value => 'Workflow Trigger',
			self::WorkflowComplete->value => 'Workflow Complete',
			self::ApprovalRequest->value => 'Approval Request',
			self::ApprovalGranted->value => 'Approval Granted',
			self::ApprovalDenied->value  => 'Approval Denied',
			self::EmailVerification->value => 'Email Verification',
			self::WelcomeSeries->value   => 'Welcome Series',
			self::CartAbandonment->value => 'Cart Abandonment Series',
			self::ReEngagement->value    => 'Re-engagement Campaign',
			self::Winback->value         => 'Winback Campaign',
			self::PostPurchase->value    => 'Post-Purchase Follow-up',
			self::Onboarding->value      => 'Onboarding Series',
			self::Offboarding->value     => 'Offboarding',
			self::ContractRenewal->value => 'Contract Renewal Reminder',
			self::QuoteFollowUp->value   => 'Quote Follow-up',
			self::ProposalSent->value    => 'Proposal Sent',
			self::ServiceReminder->value => 'Service Reminder',
			self::AppointmentConfirm->value => 'Appointment Confirmation',
			self::AppointmentReminder->value => 'Appointment Reminder',
			self::WebinarInvite->value   => 'Webinar Invitation',
			self::WebinarReminder->value => 'Webinar Reminder',
			self::WebinarFollowUp->value => 'Webinar Follow-up',
			self::DownloadConfirm->value => 'Download Confirmation',
			self::TrialExpiry->value     => 'Trial Expiry Notice',
			self::CreditLimit->value     => 'Credit Limit Alert',
			self::Statement->value       => 'Account Statement',
			self::AnnualReport->value    => 'Annual Report',
			self::QuarterlyReview->value => 'Quarterly Review',
			self::PerformanceReview->value => 'Performance Review',
			self::SlackNotification->value => 'Slack Notification',
			self::TeamsNotification->value => 'Teams Notification',
			self::ZapierWebhook->value   => 'Zapier Webhook',
			self::Other->value           => 'Other',
		];
	}

	public static function labelsPtBr(): array
	{
		return [
			self::Email->value           => 'E-mail',
			self::LeadAssignment->value  => 'Atribuição de Lead',
			self::DealStageChange->value => 'Mudança de Estágio da Oportunidade',
			self::ContactFollowUp->value => 'Follow-up de Contato',
			self::MeetingReminder->value => 'Lembrete de Reunião',
			self::TaskAssignment->value  => 'Atribuição de Tarefa',
			self::TicketUpdate->value    => 'Atualização de Ticket',
			self::TicketEscalation->value => 'Escalonamento de Ticket',
			self::OrderConfirmation->value => 'Confirmação de Pedido',
			self::OrderShipped->value    => 'Pedido Enviado',
			self::OrderDelivered->value  => 'Pedido Entregue',
			self::OrderCancelled->value  => 'Pedido Cancelado',
			self::BackInStock->value     => 'Produto Disponível Novamente',
			self::PriceDrop->value       => 'Alerta de Queda de Preço',
			self::AbandonedCart->value   => 'Carrinho Abandonado',
			self::ReviewRequest->value   => 'Solicitação de Avaliação',
			self::WishlistReminder->value => 'Lembrete de Lista de Desejos',
			self::InvoiceDue->value      => 'Fatura Vencendo',
			self::InvoiceOverdue->value  => 'Fatura Vencida',
			self::PaymentReceived->value => 'Pagamento Recebido',
			self::PaymentFailed->value   => 'Pagamento Falhou',
			self::SubscriptionRenewal->value => 'Renovação de Assinatura',
			self::SubscriptionExpiry->value => 'Expiração de Assinatura',
			self::PurchaseOrder->value   => 'Ordem de Compra',
			self::InventoryLow->value    => 'Alerta de Estoque Baixo',
			self::InventoryOut->value    => 'Alerta de Estoque Esgotado',
			self::UserInvitation->value  => 'Convite de Usuário',
			self::PasswordReset->value   => 'Redefinição de Senha',
			self::TwoFactor->value       => 'Autenticação de Dois Fatores',
			self::SystemAlert->value     => 'Alerta do Sistema',
			self::Maintenance->value     => 'Aviso de Manutenção',
			self::BackupComplete->value  => 'Backup Concluído',
			self::ReportReady->value     => 'Relatório Pronto',
			self::Welcome->value         => 'E-mail de Boas-vindas',
			self::Newsletter->value      => 'Newsletter',
			self::Promotional->value     => 'Promocional',
			self::EventInvitation->value => 'Convite para Evento',
			self::SurveyRequest->value   => 'Solicitação de Pesquisa',
			self::FeedbackRequest->value => 'Solicitação de Feedback',
			self::Anniversary->value     => 'Aniversário',
			self::LoyaltyReward->value   => 'Recompensa de Fidelidade',
			self::ReferralBonus->value   => 'Bônus de Indicação',
			self::UpsellOpportunity->value => 'Oportunidade de Venda Adicional',
			self::CrossSell->value       => 'Venda Cruzada',
			self::WorkflowTrigger->value => 'Gatilho de Fluxo de Trabalho',
			self::WorkflowComplete->value => 'Fluxo de Trabalho Concluído',
			self::ApprovalRequest->value => 'Solicitação de Aprovação',
			self::ApprovalGranted->value => 'Aprovação Concedida',
			self::ApprovalDenied->value  => 'Aprovação Negada',
			self::EmailVerification->value => 'Verificação de E-mail',
			self::WelcomeSeries->value   => 'Série de Boas-vindas',
			self::CartAbandonment->value => 'Série de Carrinho Abandonado',
			self::ReEngagement->value    => 'Campanha de Reengajamento',
			self::Winback->value         => 'Campanha de Reconquista',
			self::PostPurchase->value    => 'Follow-up Pós-compra',
			self::Onboarding->value      => 'Série de Integração',
			self::Offboarding->value     => 'Desligamento',
			self::ContractRenewal->value => 'Lembrete de Renovação de Contrato',
			self::QuoteFollowUp->value   => 'Follow-up de Orçamento',
			self::ProposalSent->value    => 'Proposta Enviada',
			self::ServiceReminder->value => 'Lembrete de Serviço',
			self::AppointmentConfirm->value => 'Confirmação de Compromisso',
			self::AppointmentReminder->value => 'Lembrete de Compromisso',
			self::WebinarInvite->value   => 'Convite para Webinar',
			self::WebinarReminder->value => 'Lembrete de Webinar',
			self::WebinarFollowUp->value => 'Follow-up de Webinar',
			self::DownloadConfirm->value => 'Confirmação de Download',
			self::TrialExpiry->value     => 'Aviso de Expiração de Trial',
			self::CreditLimit->value     => 'Alerta de Limite de Crédito',
			self::Statement->value       => 'Extrato de Conta',
			self::AnnualReport->value    => 'Relatório Anual',
			self::QuarterlyReview->value => 'Revisão Trimestral',
			self::PerformanceReview->value => 'Revisão de Desempenho',
			self::SlackNotification->value => 'Notificação do Slack',
			self::TeamsNotification->value => 'Notificação do Teams',
			self::ZapierWebhook->value   => 'Webhook do Zapier',
			self::Other->value           => 'Outro',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			self::Email->value           => 'Correo Electrónico',
			self::LeadAssignment->value  => 'Asignación de Lead',
			self::DealStageChange->value => 'Cambio de Etapa de Oportunidad',
			self::ContactFollowUp->value => 'Seguimiento de Contacto',
			self::MeetingReminder->value => 'Recordatorio de Reunión',
			self::TaskAssignment->value  => 'Asignación de Tarea',
			self::TicketUpdate->value    => 'Actualización de Ticket',
			self::TicketEscalation->value => 'Escalado de Ticket',
			self::OrderConfirmation->value => 'Confirmación de Pedido',
			self::OrderShipped->value    => 'Pedido Enviado',
			self::OrderDelivered->value  => 'Pedido Entregado',
			self::OrderCancelled->value  => 'Pedido Cancelado',
			self::BackInStock->value     => 'Producto Disponible Nuevamente',
			self::PriceDrop->value       => 'Alerta de Caída de Precio',
			self::AbandonedCart->value   => 'Carrito Abandonado',
			self::ReviewRequest->value   => 'Solicitud de Reseña',
			self::WishlistReminder->value => 'Recordatorio de Lista de Deseos',
			self::InvoiceDue->value      => 'Factura por Vencer',
			self::InvoiceOverdue->value  => 'Factura Vencida',
			self::PaymentReceived->value => 'Pago Recibido',
			self::PaymentFailed->value   => 'Pago Fallido',
			self::SubscriptionRenewal->value => 'Renovación de Suscripción',
			self::SubscriptionExpiry->value => 'Vencimiento de Suscripción',
			self::PurchaseOrder->value   => 'Orden de Compra',
			self::InventoryLow->value    => 'Alerta de Inventario Bajo',
			self::InventoryOut->value    => 'Alerta de Sin Stock',
			self::UserInvitation->value  => 'Invitación de Usuario',
			self::PasswordReset->value   => 'Restablecimiento de Contraseña',
			self::TwoFactor->value       => 'Autenticación de Dos Factores',
			self::SystemAlert->value     => 'Alerta del Sistema',
			self::Maintenance->value     => 'Aviso de Mantenimiento',
			self::BackupComplete->value  => 'Copia de Seguridad Completa',
			self::ReportReady->value     => 'Informe Listo',
			self::Welcome->value         => 'Correo de Bienvenida',
			self::Newsletter->value      => 'Boletín',
			self::Promotional->value     => 'Promocional',
			self::EventInvitation->value => 'Invitación a Evento',
			self::SurveyRequest->value   => 'Solicitud de Encuesta',
			self::FeedbackRequest->value => 'Solicitud de Comentarios',
			self::Anniversary->value     => 'Aniversario',
			self::LoyaltyReward->value   => 'Recompensa de Fidelidad',
			self::ReferralBonus->value   => 'Bono de Referido',
			self::UpsellOpportunity->value => 'Oportunidad de Venta Adicional',
			self::CrossSell->value       => 'Venta Cruzada',
			self::WorkflowTrigger->value => 'Disparador de Flujo de Trabajo',
			self::WorkflowComplete->value => 'Flujo de Trabajo Completado',
			self::ApprovalRequest->value => 'Solicitud de Aprobación',
			self::ApprovalGranted->value => 'Aprobación Concedida',
			self::ApprovalDenied->value  => 'Aprobación Denegada',
			self::EmailVerification->value => 'Verificación de Correo',
			self::WelcomeSeries->value   => 'Serie de Bienvenida',
			self::CartAbandonment->value => 'Serie de Carrito Abandonado',
			self::ReEngagement->value    => 'Campaña de Reenganche',
			self::Winback->value         => 'Campaña de Recuperación',
			self::PostPurchase->value    => 'Seguimiento Post-compra',
			self::Onboarding->value      => 'Serie de Incorporación',
			self::Offboarding->value     => 'Desvinculación',
			self::ContractRenewal->value => 'Recordatorio de Renovación de Contrato',
			self::QuoteFollowUp->value   => 'Seguimiento de Cotización',
			self::ProposalSent->value    => 'Propuesta Enviada',
			self::ServiceReminder->value => 'Recordatorio de Servicio',
			self::AppointmentConfirm->value => 'Confirmación de Cita',
			self::AppointmentReminder->value => 'Recordatorio de Cita',
			self::WebinarInvite->value   => 'Invitación a Webinar',
			self::WebinarReminder->value => 'Recordatorio de Webinar',
			self::WebinarFollowUp->value => 'Seguimiento de Webinar',
			self::DownloadConfirm->value => 'Confirmación de Descarga',
			self::TrialExpiry->value     => 'Aviso de Vencimiento de Prueba',
			self::CreditLimit->value     => 'Alerta de Límite de Crédito',
			self::Statement->value       => 'Estado de Cuenta',
			self::AnnualReport->value    => 'Informe Anual',
			self::QuarterlyReview->value => 'Revisión Trimestral',
			self::PerformanceReview->value => 'Revisión de Desempeño',
			self::SlackNotification->value => 'Notificación de Slack',
			self::TeamsNotification->value => 'Notificación de Teams',
			self::ZapierWebhook->value   => 'Webhook de Zapier',
			self::Other->value           => 'Otro',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			self::Email->value           => 'البريد الإلكتروني',
			self::LeadAssignment->value  => 'تعيين العميل المحتمل',
			self::DealStageChange->value => 'تغيير مرحلة الصفقة',
			self::ContactFollowUp->value => 'متابعة الاتصال',
			self::MeetingReminder->value => 'تذكير بالاجتماع',
			self::TaskAssignment->value  => 'تعيين المهمة',
			self::TicketUpdate->value    => 'تحديث التذكرة',
			self::TicketEscalation->value => 'تصعيد التذكرة',
			self::OrderConfirmation->value => 'تأكيد الطلب',
			self::OrderShipped->value    => 'تم شحن الطلب',
			self::OrderDelivered->value  => 'تم تسليم الطلب',
			self::OrderCancelled->value  => 'تم إلغاء الطلب',
			self::BackInStock->value     => 'المنتج متوفر مرة أخرى',
			self::PriceDrop->value       => 'تنبيه انخفاض السعر',
			self::AbandonedCart->value   => 'سلة التسوق المهجورة',
			self::ReviewRequest->value   => 'طلب تقييم',
			self::WishlistReminder->value => 'تذكير بقائمة الرغبات',
			self::InvoiceDue->value      => 'فاتورة مستحقة',
			self::InvoiceOverdue->value  => 'فاتورة متأخرة',
			self::PaymentReceived->value => 'تم استلام الدفع',
			self::PaymentFailed->value   => 'فشل الدفع',
			self::SubscriptionRenewal->value => 'تجديد الاشتراك',
			self::SubscriptionExpiry->value => 'انتهاء الاشتراك',
			self::PurchaseOrder->value   => 'أمر الشراء',
			self::InventoryLow->value    => 'تنبيه مخزون منخفض',
			self::InventoryOut->value    => 'تنبيه نفاذ المخزون',
			self::UserInvitation->value  => 'دعوة مستخدم',
			self::PasswordReset->value   => 'إعادة تعيين كلمة المرور',
			self::TwoFactor->value       => 'المصادقة الثنائية',
			self::SystemAlert->value     => 'تنبيه النظام',
			self::Maintenance->value     => 'إشعار الصيانة',
			self::BackupComplete->value  => 'اكتمال النسخ الاحتياطي',
			self::ReportReady->value     => 'التقرير جاهز',
			self::Welcome->value         => 'رسالة ترحيب',
			self::Newsletter->value      => 'النشرة الإخبارية',
			self::Promotional->value     => 'ترويجي',
			self::EventInvitation->value => 'دعوة حدث',
			self::SurveyRequest->value   => 'طلب استطلاع',
			self::FeedbackRequest->value => 'طلب تعليقات',
			self::Anniversary->value     => 'ذكرى',
			self::LoyaltyReward->value   => 'مكافأة الولاء',
			self::ReferralBonus->value   => 'مكافأة الإحالة',
			self::UpsellOpportunity->value => 'فرصة بيع إضافي',
			self::CrossSell->value       => 'بيع عرضي',
			self::WorkflowTrigger->value => 'تشغيل سير العمل',
			self::WorkflowComplete->value => 'اكتمال سير العمل',
			self::ApprovalRequest->value => 'طلب موافقة',
			self::ApprovalGranted->value => 'تمت الموافقة',
			self::ApprovalDenied->value  => 'تم رفض الموافقة',
			self::EmailVerification->value => 'التحقق من البريد الإلكتروني',
			self::WelcomeSeries->value   => 'سلسلة الترحيب',
			self::CartAbandonment->value => 'سلسلة سلة التسوق المهجورة',
			self::ReEngagement->value    => 'حملة إعادة المشاركة',
			self::Winback->value         => 'حملة استعادة العملاء',
			self::PostPurchase->value    => 'متابعة ما بعد الشراء',
			self::Onboarding->value      => 'سلسلة الانضمام',
			self::Offboarding->value     => 'إنهاء الخدمة',
			self::ContractRenewal->value => 'تذكير تجديد العقد',
			self::QuoteFollowUp->value   => 'متابعة عرض السعر',
			self::ProposalSent->value    => 'تم إرسال العرض',
			self::ServiceReminder->value => 'تذكير الخدمة',
			self::AppointmentConfirm->value => 'تأكيد الموعد',
			self::AppointmentReminder->value => 'تذكير الموعد',
			self::WebinarInvite->value   => 'دعوة ويبنار',
			self::WebinarReminder->value => 'تذكير ويبنار',
			self::WebinarFollowUp->value => 'متابعة ويبنار',
			self::DownloadConfirm->value => 'تأكيد التنزيل',
			self::TrialExpiry->value     => 'إشعار انتهاء التجربة',
			self::CreditLimit->value     => 'تنبيه حد الائتمان',
			self::Statement->value       => 'كشف حساب',
			self::AnnualReport->value    => 'التقرير السنوي',
			self::QuarterlyReview->value => 'المراجعة الربع سنوية',
			self::PerformanceReview->value => 'مراجعة الأداء',
			self::SlackNotification->value => 'إشعار Slack',
			self::TeamsNotification->value => 'إشعار Teams',
			self::ZapierWebhook->value   => 'Webhook من Zapier',
			self::Other->value           => 'أخرى',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			self::Email->value           => 'E-mail',
			self::LeadAssignment->value  => 'Lead-tildeling',
			self::DealStageChange->value => 'Aftalestadieændring',
			self::ContactFollowUp->value => 'Kontaktopfølgning',
			self::MeetingReminder->value => 'Mødepåmindelse',
			self::TaskAssignment->value  => 'Opgavetildeling',
			self::TicketUpdate->value    => 'Ticketopdatering',
			self::TicketEscalation->value => 'Ticketeskalering',
			self::OrderConfirmation->value => 'Ordrebekræftelse',
			self::OrderShipped->value    => 'Ordre afsendt',
			self::OrderDelivered->value  => 'Ordre leveret',
			self::OrderCancelled->value  => 'Ordre annulleret',
			self::BackInStock->value     => 'På lager igen',
			self::PriceDrop->value       => 'Prisfaldsadvarsel',
			self::AbandonedCart->value   => 'Forladt indkøbskurv',
			self::ReviewRequest->value   => 'Anmeldelsesanmodning',
			self::WishlistReminder->value => 'Ønskelistepåmindelse',
			self::InvoiceDue->value      => 'Faktura forfalder',
			self::InvoiceOverdue->value  => 'Forfalden faktura',
			self::PaymentReceived->value => 'Betaling modtaget',
			self::PaymentFailed->value   => 'Betaling fejlede',
			self::SubscriptionRenewal->value => 'Abonnementfornyelse',
			self::SubscriptionExpiry->value => 'Abonnementudløb',
			self::PurchaseOrder->value   => 'Indkøbsordre',
			self::InventoryLow->value    => 'Lav beholdningsadvarsel',
			self::InventoryOut->value    => 'Udsolgt advarsel',
			self::UserInvitation->value  => 'Brugerinvitation',
			self::PasswordReset->value   => 'Nulstil adgangskode',
			self::TwoFactor->value       => 'Tofaktor-godkendelse',
			self::SystemAlert->value     => 'Systemadvarsel',
			self::Maintenance->value     => 'Vedligeholdelsesmeddelelse',
			self::BackupComplete->value  => 'Sikkerhedskopi fuldført',
			self::ReportReady->value     => 'Rapport klar',
			self::Welcome->value         => 'Velkomst-e-mail',
			self::Newsletter->value      => 'Nyhedsbrev',
			self::Promotional->value     => 'Promovering',
			self::EventInvitation->value => 'Begivenhedsinvitation',
			self::SurveyRequest->value   => 'Spørgeskemaanmodning',
			self::FeedbackRequest->value => 'Feedbackanmodning',
			self::Anniversary->value     => 'Årsdag',
			self::LoyaltyReward->value   => 'Loyalitetsbelønning',
			self::ReferralBonus->value   => 'Henvisningsbonus',
			self::UpsellOpportunity->value => 'Opsalgmulighed',
			self::CrossSell->value       => 'Krydssalg',
			self::WorkflowTrigger->value => 'Workflow-udløser',
			self::WorkflowComplete->value => 'Workflow fuldført',
			self::ApprovalRequest->value => 'Godkendelsesanmodning',
			self::ApprovalGranted->value => 'Godkendelse givet',
			self::ApprovalDenied->value  => 'Godkendelse afvist',
			self::EmailVerification->value => 'E-mail-bekræftelse',
			self::WelcomeSeries->value   => 'Velkomstserie',
			self::CartAbandonment->value => 'Forladt indkøbskurv serie',
			self::ReEngagement->value    => 'Genengagementskampagne',
			self::Winback->value         => 'Genvindingskampagne',
			self::PostPurchase->value    => 'Opfølgning efter køb',
			self::Onboarding->value      => 'Onboarding-serie',
			self::Offboarding->value     => 'Offboarding',
			self::ContractRenewal->value => 'Kontraktfornyelsespåmindelse',
			self::QuoteFollowUp->value   => 'Tilbudsopfølgning',
			self::ProposalSent->value    => 'Forslag sendt',
			self::ServiceReminder->value => 'Servicepåmindelse',
			self::AppointmentConfirm->value => 'Aftalebekræftelse',
			self::AppointmentReminder->value => 'Aftalepåmindelse',
			self::WebinarInvite->value   => 'Webinar invitation',
			self::WebinarReminder->value => 'Webinar påmindelse',
			self::WebinarFollowUp->value => 'Webinar opfølgning',
			self::DownloadConfirm->value => 'Download bekræftelse',
			self::TrialExpiry->value     => 'Prøveversion udløbsmeddelelse',
			self::CreditLimit->value     => 'Kreditgrænse advarsel',
			self::Statement->value       => 'Kontoudtog',
			self::AnnualReport->value    => 'Årsrapport',
			self::QuarterlyReview->value => 'Kvartalsvis gennemgang',
			self::PerformanceReview->value => 'Performancegennemgang',
			self::SlackNotification->value => 'Slack notifikation',
			self::TeamsNotification->value => 'Teams notifikation',
			self::ZapierWebhook->value   => 'Zapier Webhook',
			self::Other->value           => 'Andet',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			self::Email->value           => 'E-Mail',
			self::LeadAssignment->value  => 'Lead-Zuweisung',
			self::DealStageChange->value => 'Deal-Phasenänderung',
			self::ContactFollowUp->value => 'Kontakt-Nachverfolgung',
			self::MeetingReminder->value => 'Besprechungserinnerung',
			self::TaskAssignment->value  => 'Aufgabenzuweisung',
			self::TicketUpdate->value    => 'Ticket-Aktualisierung',
			self::TicketEscalation->value => 'Ticket-Eskalation',
			self::OrderConfirmation->value => 'Bestellbestätigung',
			self::OrderShipped->value    => 'Bestellung versendet',
			self::OrderDelivered->value  => 'Bestellung geliefert',
			self::OrderCancelled->value  => 'Bestellung storniert',
			self::BackInStock->value     => 'Wieder auf Lager',
			self::PriceDrop->value       => 'Preissenkungsalarm',
			self::AbandonedCart->value   => 'Verlassener Warenkorb',
			self::ReviewRequest->value   => 'Bewertungsanfrage',
			self::WishlistReminder->value => 'Wunschliste-Erinnerung',
			self::InvoiceDue->value      => 'Rechnung fällig',
			self::InvoiceOverdue->value  => 'Überfällige Rechnung',
			self::PaymentReceived->value => 'Zahlung erhalten',
			self::PaymentFailed->value   => 'Zahlung fehlgeschlagen',
			self::SubscriptionRenewal->value => 'Abonnementverlängerung',
			self::SubscriptionExpiry->value => 'Abonnementablauf',
			self::PurchaseOrder->value   => 'Bestellung',
			self::InventoryLow->value    => 'Niedriger Lagerbestand Alarm',
			self::InventoryOut->value    => 'Ausverkauft Alarm',
			self::UserInvitation->value  => 'Benutzereinladung',
			self::PasswordReset->value   => 'Passwort zurücksetzen',
			self::TwoFactor->value       => 'Zwei-Faktor-Authentifizierung',
			self::SystemAlert->value     => 'Systemwarnung',
			self::Maintenance->value     => 'Wartungsmitteilung',
			self::BackupComplete->value  => 'Backup abgeschlossen',
			self::ReportReady->value     => 'Bericht bereit',
			self::Welcome->value         => 'Willkommens-E-Mail',
			self::Newsletter->value      => 'Newsletter',
			self::Promotional->value     => 'Werbeaktion',
			self::EventInvitation->value => 'Veranstaltungseinladung',
			self::SurveyRequest->value   => 'Umfrageanfrage',
			self::FeedbackRequest->value => 'Feedback-Anfrage',
			self::Anniversary->value     => 'Jubiläum',
			self::LoyaltyReward->value   => 'Treueprämie',
			self::ReferralBonus->value   => 'Empfehlungsbonus',
			self::UpsellOpportunity->value => 'Upsell-Möglichkeit',
			self::CrossSell->value       => 'Cross-Selling',
			self::WorkflowTrigger->value => 'Workflow-Trigger',
			self::WorkflowComplete->value => 'Workflow abgeschlossen',
			self::ApprovalRequest->value => 'Genehmigungsanfrage',
			self::ApprovalGranted->value => 'Genehmigung erteilt',
			self::ApprovalDenied->value  => 'Genehmigung abgelehnt',
			self::EmailVerification->value => 'E-Mail-Bestätigung',
			self::WelcomeSeries->value   => 'Willkommensserie',
			self::CartAbandonment->value => 'Verlassener Warenkorb Serie',
			self::ReEngagement->value    => 'Re-Engagement-Kampagne',
			self::Winback->value         => 'Winback-Kampagne',
			self::PostPurchase->value    => 'Nachkauf-Follow-up',
			self::Onboarding->value      => 'Onboarding-Serie',
			self::Offboarding->value     => 'Offboarding',
			self::ContractRenewal->value => 'Vertragsverlängerungserinnerung',
			self::QuoteFollowUp->value   => 'Angebotsnachverfolgung',
			self::ProposalSent->value    => 'Angebot gesendet',
			self::ServiceReminder->value => 'Servicerinnerung',
			self::AppointmentConfirm->value => 'Terminbestätigung',
			self::AppointmentReminder->value => 'Terminerinnerung',
			self::WebinarInvite->value   => 'Webinar-Einladung',
			self::WebinarReminder->value => 'Webinar-Erinnerung',
			self::WebinarFollowUp->value => 'Webinar-Nachverfolgung',
			self::DownloadConfirm->value => 'Download-Bestätigung',
			self::TrialExpiry->value     => 'Testversion Ablaufmitteilung',
			self::CreditLimit->value     => 'Kreditlimit Alarm',
			self::Statement->value       => 'Kontoauszug',
			self::AnnualReport->value    => 'Jahresbericht',
			self::QuarterlyReview->value => 'Quartalsbericht',
			self::PerformanceReview->value => 'Leistungsbewertung',
			self::SlackNotification->value => 'Slack-Benachrichtigung',
			self::TeamsNotification->value => 'Teams-Benachrichtigung',
			self::ZapierWebhook->value   => 'Zapier-Webhook',
			self::Other->value           => 'Andere',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			self::Email->value           => 'E-mail',
			self::LeadAssignment->value  => 'Affectation de prospect',
			self::DealStageChange->value => 'Changement d\'étape d\'affaire',
			self::ContactFollowUp->value => 'Suivi de contact',
			self::MeetingReminder->value => 'Rappel de réunion',
			self::TaskAssignment->value  => 'Affectation de tâche',
			self::TicketUpdate->value    => 'Mise à jour de ticket',
			self::TicketEscalation->value => 'Escalade de ticket',
			self::OrderConfirmation->value => 'Confirmation de commande',
			self::OrderShipped->value    => 'Commande expédiée',
			self::OrderDelivered->value  => 'Commande livrée',
			self::OrderCancelled->value  => 'Commande annulée',
			self::BackInStock->value     => 'De nouveau en stock',
			self::PriceDrop->value       => 'Alerte baisse de prix',
			self::AbandonedCart->value   => 'Panier abandonné',
			self::ReviewRequest->value   => 'Demande d\'avis',
			self::WishlistReminder->value => 'Rappel de liste de souhaits',
			self::InvoiceDue->value      => 'Facture due',
			self::InvoiceOverdue->value  => 'Facture en retard',
			self::PaymentReceived->value => 'Paiement reçu',
			self::PaymentFailed->value   => 'Paiement échoué',
			self::SubscriptionRenewal->value => 'Renouvellement d\'abonnement',
			self::SubscriptionExpiry->value => 'Expiration d\'abonnement',
			self::PurchaseOrder->value   => 'Bon de commande',
			self::InventoryLow->value    => 'Alerte stock faible',
			self::InventoryOut->value    => 'Alerte rupture de stock',
			self::UserInvitation->value  => 'Invitation utilisateur',
			self::PasswordReset->value   => 'Réinitialisation du mot de passe',
			self::TwoFactor->value       => 'Authentification à deux facteurs',
			self::SystemAlert->value     => 'Alerte système',
			self::Maintenance->value     => 'Avis de maintenance',
			self::BackupComplete->value  => 'Sauvegarde terminée',
			self::ReportReady->value     => 'Rapport prêt',
			self::Welcome->value         => 'E-mail de bienvenue',
			self::Newsletter->value      => 'Newsletter',
			self::Promotional->value     => 'Promotionnel',
			self::EventInvitation->value => 'Invitation à un événement',
			self::SurveyRequest->value   => 'Demande d\'enquête',
			self::FeedbackRequest->value => 'Demande de retour',
			self::Anniversary->value     => 'Anniversaire',
			self::LoyaltyReward->value   => 'Récompense de fidélité',
			self::ReferralBonus->value   => 'Bonus de parrainage',
			self::UpsellOpportunity->value => 'Opportunité de vente incitative',
			self::CrossSell->value       => 'Vente croisée',
			self::WorkflowTrigger->value => 'Déclencheur de workflow',
			self::WorkflowComplete->value => 'Workflow terminé',
			self::ApprovalRequest->value => 'Demande d\'approbation',
			self::ApprovalGranted->value => 'Approbation accordée',
			self::ApprovalDenied->value  => 'Approbation refusée',
			self::EmailVerification->value => 'Vérification d\'e-mail',
			self::WelcomeSeries->value   => 'Série de bienvenue',
			self::CartAbandonment->value => 'Série panier abandonné',
			self::ReEngagement->value    => 'Campagne de réengagement',
			self::Winback->value         => 'Campagne de reconquête',
			self::PostPurchase->value    => 'Suivi post-achat',
			self::Onboarding->value      => 'Série d\'intégration',
			self::Offboarding->value     => 'Offboarding',
			self::ContractRenewal->value => 'Rappel de renouvellement de contrat',
			self::QuoteFollowUp->value   => 'Suivi de devis',
			self::ProposalSent->value    => 'Proposition envoyée',
			self::ServiceReminder->value => 'Rappel de service',
			self::AppointmentConfirm->value => 'Confirmation de rendez-vous',
			self::AppointmentReminder->value => 'Rappel de rendez-vous',
			self::WebinarInvite->value   => 'Invitation webinar',
			self::WebinarReminder->value => 'Rappel webinar',
			self::WebinarFollowUp->value => 'Suivi webinar',
			self::DownloadConfirm->value => 'Confirmation de téléchargement',
			self::TrialExpiry->value     => 'Avis d\'expiration d\'essai',
			self::CreditLimit->value     => 'Alerte limite de crédit',
			self::Statement->value       => 'Relevé de compte',
			self::AnnualReport->value    => 'Rapport annuel',
			self::QuarterlyReview->value => 'Examen trimestriel',
			self::PerformanceReview->value => 'Évaluation des performances',
			self::SlackNotification->value => 'Notification Slack',
			self::TeamsNotification->value => 'Notification Teams',
			self::ZapierWebhook->value   => 'Webhook Zapier',
			self::Other->value           => 'Autre',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			self::Email->value           => 'אימייל',
			self::LeadAssignment->value  => 'הקצאת ליד',
			self::DealStageChange->value => 'שינוי שלב עסקה',
			self::ContactFollowUp->value => 'מעקב אחר קשר',
			self::MeetingReminder->value => 'תזכורת לפגישה',
			self::TaskAssignment->value  => 'הקצאת משימה',
			self::TicketUpdate->value    => 'עדכון כרטיס',
			self::TicketEscalation->value => 'הסלמת כרטיס',
			self::OrderConfirmation->value => 'אישור הזמנה',
			self::OrderShipped->value    => 'הזמנה נשלחה',
			self::OrderDelivered->value  => 'הזמנה נמסרה',
			self::OrderCancelled->value  => 'הזמנה בוטלה',
			self::BackInStock->value     => 'חזר למלאי',
			self::PriceDrop->value       => 'התראת ירידת מחיר',
			self::AbandonedCart->value   => 'עגלה נעזבה',
			self::ReviewRequest->value   => 'בקשה לחוות דעת',
			self::WishlistReminder->value => 'תזכורת לרשימת משאלות',
			self::InvoiceDue->value      => 'חשבונית להגיע',
			self::InvoiceOverdue->value  => 'חשבונית באיחור',
			self::PaymentReceived->value => 'תשלום התקבל',
			self::PaymentFailed->value   => 'תשלום נכשל',
			self::SubscriptionRenewal->value => 'חידוש מנוי',
			self::SubscriptionExpiry->value => 'תפוגת מנוי',
			self::PurchaseOrder->value   => 'הזמנת רכש',
			self::InventoryLow->value    => 'התראת מלאי נמוך',
			self::InventoryOut->value    => 'התראת אזל מהמלאי',
			self::UserInvitation->value  => 'הזמנת משתמש',
			self::PasswordReset->value   => 'איפוס סיסמה',
			self::TwoFactor->value       => 'אימות דו-שלבי',
			self::SystemAlert->value     => 'התראת מערכת',
			self::Maintenance->value     => 'הודעת תחזוקה',
			self::BackupComplete->value  => 'גיבוי הושלם',
			self::ReportReady->value     => 'דוח מוכן',
			self::Welcome->value         => 'אימייל ברכה',
			self::Newsletter->value      => 'עלון',
			self::Promotional->value     => 'קידומי',
			self::EventInvitation->value => 'הזמנה לאירוע',
			self::SurveyRequest->value   => 'בקשת סקר',
			self::FeedbackRequest->value => 'בקשת משוב',
			self::Anniversary->value     => 'יום שנה',
			self::LoyaltyReward->value   => 'פרס נאמנות',
			self::ReferralBonus->value   => 'בונוס המלצה',
			self::UpsellOpportunity->value => 'הזדמנות למכירה נוספת',
			self::CrossSell->value       => 'מכירה צולבת',
			self::WorkflowTrigger->value => 'הפעלת זרימת עבודה',
			self::WorkflowComplete->value => 'זרימת עבודה הושלמה',
			self::ApprovalRequest->value => 'בקשת אישור',
			self::ApprovalGranted->value => 'אישור ניתן',
			self::ApprovalDenied->value  => 'אישור נדחה',
			self::EmailVerification->value => 'אימות אימייל',
			self::WelcomeSeries->value   => 'סדרת ברכה',
			self::CartAbandonment->value => 'סדרת עגלה נעזבה',
			self::ReEngagement->value    => 'קמפיין המעורבות מחדש',
			self::Winback->value         => 'קמפיין השבה',
			self::PostPurchase->value    => 'מעקב אחרי רכישה',
			self::Onboarding->value      => 'סדרת קבלה',
			self::Offboarding->value     => 'סיום עבודה',
			self::ContractRenewal->value => 'תזכורת חידוש חוזה',
			self::QuoteFollowUp->value   => 'מעקב אחר הצעת מחיר',
			self::ProposalSent->value    => 'הצעה נשלחה',
			self::ServiceReminder->value => 'תזכורת שירות',
			self::AppointmentConfirm->value => 'אישור תור',
			self::AppointmentReminder->value => 'תזכורת תור',
			self::WebinarInvite->value   => 'הזמנה לוובינר',
			self::WebinarReminder->value => 'תזכורת לוובינר',
			self::WebinarFollowUp->value => 'מעקב וובינר',
			self::DownloadConfirm->value => 'אישור הורדה',
			self::TrialExpiry->value     => 'הודעת תום תקופת ניסיון',
			self::CreditLimit->value     => 'התראת מגבלת אשראי',
			self::Statement->value       => 'דוח חשבון',
			self::AnnualReport->value    => 'דוח שנתי',
			self::QuarterlyReview->value => 'ביקורת רבעונית',
			self::PerformanceReview->value => 'סקירת ביצועים',
			self::SlackNotification->value => 'התראת Slack',
			self::TeamsNotification->value => 'התראת Teams',
			self::ZapierWebhook->value   => 'Webhook של Zapier',
			self::Other->value           => 'אחר',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			self::Email->value           => 'Email',
			self::LeadAssignment->value  => 'Assegnazione Lead',
			self::DealStageChange->value => 'Cambio Stadio Affare',
			self::ContactFollowUp->value => 'Follow-up Contatto',
			self::MeetingReminder->value => 'Promemoria Riunione',
			self::TaskAssignment->value  => 'Assegnazione Attività',
			self::TicketUpdate->value    => 'Aggiornamento Ticket',
			self::TicketEscalation->value => 'Escalation Ticket',
			self::OrderConfirmation->value => 'Conferma Ordine',
			self::OrderShipped->value    => 'Ordine Spedito',
			self::OrderDelivered->value  => 'Ordine Consegnato',
			self::OrderCancelled->value  => 'Ordine Annullato',
			self::BackInStock->value     => 'Disponibile di Nuovo',
			self::PriceDrop->value       => 'Allarme Calo Prezzo',
			self::AbandonedCart->value   => 'Carrello Abbandonato',
			self::ReviewRequest->value   => 'Richiesta Recensione',
			self::WishlistReminder->value => 'Promemoria Lista Desideri',
			self::InvoiceDue->value      => 'Fattura Scadente',
			self::InvoiceOverdue->value  => 'Fattura Scaduta',
			self::PaymentReceived->value => 'Pagamento Ricevuto',
			self::PaymentFailed->value   => 'Pagamento Fallito',
			self::SubscriptionRenewal->value => 'Rinnovo Abbonamento',
			self::SubscriptionExpiry->value => 'Scadenza Abbonamento',
			self::PurchaseOrder->value   => 'Ordine d\'Acquisto',
			self::InventoryLow->value    => 'Allarme Scorte Basse',
			self::InventoryOut->value    => 'Allarme Esaurito',
			self::UserInvitation->value  => 'Invito Utente',
			self::PasswordReset->value   => 'Reset Password',
			self::TwoFactor->value       => 'Autenticazione a Due Fattori',
			self::SystemAlert->value     => 'Allerta Sistema',
			self::Maintenance->value     => 'Avviso Manutenzione',
			self::BackupComplete->value  => 'Backup Completato',
			self::ReportReady->value     => 'Report Pronto',
			self::Welcome->value         => 'Email di Benvenuto',
			self::Newsletter->value      => 'Newsletter',
			self::Promotional->value     => 'Promozionale',
			self::EventInvitation->value => 'Invito Evento',
			self::SurveyRequest->value   => 'Richiesta Sondaggio',
			self::FeedbackRequest->value => 'Richiesta Feedback',
			self::Anniversary->value     => 'Anniversario',
			self::LoyaltyReward->value   => 'Ricompensa Fedeltà',
			self::ReferralBonus->value   => 'Bonus Referral',
			self::UpsellOpportunity->value => 'Opportunità Upsell',
			self::CrossSell->value       => 'Cross-selling',
			self::WorkflowTrigger->value => 'Trigger Workflow',
			self::WorkflowComplete->value => 'Workflow Completato',
			self::ApprovalRequest->value => 'Richiesta Approvazione',
			self::ApprovalGranted->value => 'Approvazione Concessa',
			self::ApprovalDenied->value  => 'Approvazione Negata',
			self::EmailVerification->value => 'Verifica Email',
			self::WelcomeSeries->value   => 'Serie di Benvenuto',
			self::CartAbandonment->value => 'Serie Carrello Abbandonato',
			self::ReEngagement->value    => 'Campagna di Ri-coinvolgimento',
			self::Winback->value         => 'Campagna di Riconquista',
			self::PostPurchase->value    => 'Follow-up Post-acquisto',
			self::Onboarding->value      => 'Serie di Onboarding',
			self::Offboarding->value     => 'Offboarding',
			self::ContractRenewal->value => 'Promemoria Rinnovo Contratto',
			self::QuoteFollowUp->value   => 'Follow-up Preventivo',
			self::ProposalSent->value    => 'Proposta Inviata',
			self::ServiceReminder->value => 'Promemoria Servizio',
			self::AppointmentConfirm->value => 'Conferma Appuntamento',
			self::AppointmentReminder->value => 'Promemoria Appuntamento',
			self::WebinarInvite->value   => 'Invito Webinar',
			self::WebinarReminder->value => 'Promemoria Webinar',
			self::WebinarFollowUp->value => 'Follow-up Webinar',
			self::DownloadConfirm->value => 'Conferma Download',
			self::TrialExpiry->value     => 'Avviso Scadenza Trial',
			self::CreditLimit->value     => 'Allarme Limite di Credito',
			self::Statement->value       => 'Estratto Conto',
			self::AnnualReport->value    => 'Rapporto Annuale',
			self::QuarterlyReview->value => 'Revisione Trimestrale',
			self::PerformanceReview->value => 'Valutazione delle Prestazioni',
			self::SlackNotification->value => 'Notifica Slack',
			self::TeamsNotification->value => 'Notifica Teams',
			self::ZapierWebhook->value   => 'Webhook Zapier',
			self::Other->value           => 'Altro',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			self::Email->value           => 'メール',
			self::LeadAssignment->value  => 'リード割り当て',
			self::DealStageChange->value => '商談ステージ変更',
			self::ContactFollowUp->value => 'コンタクトフォローアップ',
			self::MeetingReminder->value => '会議リマインダー',
			self::TaskAssignment->value  => 'タスク割り当て',
			self::TicketUpdate->value    => 'チケット更新',
			self::TicketEscalation->value => 'チケットエスカレーション',
			self::OrderConfirmation->value => '注文確認',
			self::OrderShipped->value    => '注文発送済み',
			self::OrderDelivered->value  => '注文配達済み',
			self::OrderCancelled->value  => '注文キャンセル',
			self::BackInStock->value     => '再入荷',
			self::PriceDrop->value       => '価格低下アラート',
			self::AbandonedCart->value   => '放棄されたカート',
			self::ReviewRequest->value   => 'レビュー依頼',
			self::WishlistReminder->value => 'ウィッシュリストリマインダー',
			self::InvoiceDue->value      => '請求書期日',
			self::InvoiceOverdue->value  => '延滞請求書',
			self::PaymentReceived->value => '支払い受領',
			self::PaymentFailed->value   => '支払い失敗',
			self::SubscriptionRenewal->value => 'サブスクリプション更新',
			self::SubscriptionExpiry->value => 'サブスクリプション期限切れ',
			self::PurchaseOrder->value   => '発注書',
			self::InventoryLow->value    => '在庫不足アラート',
			self::InventoryOut->value    => '在庫切れアラート',
			self::UserInvitation->value  => 'ユーザー招待',
			self::PasswordReset->value   => 'パスワードリセット',
			self::TwoFactor->value       => '二要素認証',
			self::SystemAlert->value     => 'システムアラート',
			self::Maintenance->value     => 'メンテナンス通知',
			self::BackupComplete->value  => 'バックアップ完了',
			self::ReportReady->value     => 'レポート準備完了',
			self::Welcome->value         => 'ウェルカムメール',
			self::Newsletter->value      => 'ニュースレター',
			self::Promotional->value     => 'プロモーション',
			self::EventInvitation->value => 'イベント招待',
			self::SurveyRequest->value   => 'アンケート依頼',
			self::FeedbackRequest->value => 'フィードバック依頼',
			self::Anniversary->value     => '記念日',
			self::LoyaltyReward->value   => 'ロイヤリティ報酬',
			self::ReferralBonus->value   => '紹介ボーナス',
			self::UpsellOpportunity->value => 'アップセル機会',
			self::CrossSell->value       => 'クロスセル',
			self::WorkflowTrigger->value => 'ワークフロートリガー',
			self::WorkflowComplete->value => 'ワークフロー完了',
			self::ApprovalRequest->value => '承認依頼',
			self::ApprovalGranted->value => '承認済み',
			self::ApprovalDenied->value  => '承認却下',
			self::EmailVerification->value => 'メール確認',
			self::WelcomeSeries->value   => 'ウェルカムシリーズ',
			self::CartAbandonment->value => '放棄カートシリーズ',
			self::ReEngagement->value    => '再エンゲージメントキャンペーン',
			self::Winback->value         => '顧客回復キャンペーン',
			self::PostPurchase->value    => '購入後フォローアップ',
			self::Onboarding->value      => 'オンボーディングシリーズ',
			self::Offboarding->value     => 'オフボーディング',
			self::ContractRenewal->value => '契約更新リマインダー',
			self::QuoteFollowUp->value   => '見積もりフォローアップ',
			self::ProposalSent->value    => '提案書送信',
			self::ServiceReminder->value => 'サービスリマインダー',
			self::AppointmentConfirm->value => '予約確認',
			self::AppointmentReminder->value => '予約リマインダー',
			self::WebinarInvite->value   => 'ウェビナー招待',
			self::WebinarReminder->value => 'ウェビナーリマインダー',
			self::WebinarFollowUp->value => 'ウェビナーフォローアップ',
			self::DownloadConfirm->value => 'ダウンロード確認',
			self::TrialExpiry->value     => 'トライアル期限切れ通知',
			self::CreditLimit->value     => '与信限度アラート',
			self::Statement->value       => '口座明細書',
			self::AnnualReport->value    => '年次報告書',
			self::QuarterlyReview->value => '四半期レビュー',
			self::PerformanceReview->value => 'パフォーマンスレビュー',
			self::SlackNotification->value => 'Slack通知',
			self::TeamsNotification->value => 'Teams通知',
			self::ZapierWebhook->value   => 'Zapier Webhook',
			self::Other->value           => 'その他',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			self::Email->value           => 'E-mail',
			self::LeadAssignment->value  => 'Leadtoewijzing',
			self::DealStageChange->value => 'Dealstadiumwijziging',
			self::ContactFollowUp->value => 'Contactfollow-up',
			self::MeetingReminder->value => 'Afspraakherinnering',
			self::TaskAssignment->value  => 'Taaktoewijzing',
			self::TicketUpdate->value    => 'Ticketupdate',
			self::TicketEscalation->value => 'Ticketescalatie',
			self::OrderConfirmation->value => 'Bestelbevestiging',
			self::OrderShipped->value    => 'Bestelling verzonden',
			self::OrderDelivered->value  => 'Bestelling geleverd',
			self::OrderCancelled->value  => 'Bestelling geannuleerd',
			self::BackInStock->value     => 'Weer op voorraad',
			self::PriceDrop->value       => 'Prijsdalingswaarschuwing',
			self::AbandonedCart->value   => 'Verlaten winkelwagen',
			self::ReviewRequest->value   => 'Recensieverzoek',
			self::WishlistReminder->value => 'Verlanglijstherinnering',
			self::InvoiceDue->value      => 'Factuur vervalt',
			self::InvoiceOverdue->value  => 'Achterstallige factuur',
			self::PaymentReceived->value => 'Betaling ontvangen',
			self::PaymentFailed->value   => 'Betaling mislukt',
			self::SubscriptionRenewal->value => 'Abonnementverlenging',
			self::SubscriptionExpiry->value => 'Abonnementverloop',
			self::PurchaseOrder->value   => 'Inkooporder',
			self::InventoryLow->value    => 'Lage voorraadwaarschuwing',
			self::InventoryOut->value    => 'Uitverkocht waarschuwing',
			self::UserInvitation->value  => 'Uitnodiging gebruiker',
			self::PasswordReset->value   => 'Wachtwoord resetten',
			self::TwoFactor->value       => 'Tweefactorauthenticatie',
			self::SystemAlert->value     => 'Systeemwaarschuwing',
			self::Maintenance->value     => 'Onderhoudsbericht',
			self::BackupComplete->value  => 'Back-up voltooid',
			self::ReportReady->value     => 'Rapport gereed',
			self::Welcome->value         => 'Welkomst-e-mail',
			self::Newsletter->value      => 'Nieuwsbrief',
			self::Promotional->value     => 'Promotioneel',
			self::EventInvitation->value => 'Evenementuitnodiging',
			self::SurveyRequest->value   => 'Enquêteverzoek',
			self::FeedbackRequest->value => 'Feedbackverzoek',
			self::Anniversary->value     => 'Verjaardag',
			self::LoyaltyReward->value   => 'Loyaliteitsbeloning',
			self::ReferralBonus->value   => 'Verwijzingsbonus',
			self::UpsellOpportunity->value => 'Upselkans',
			self::CrossSell->value       => 'Cross-selling',
			self::WorkflowTrigger->value => 'Workflowtrigger',
			self::WorkflowComplete->value => 'Workflow voltooid',
			self::ApprovalRequest->value => 'Goedkeuringsverzoek',
			self::ApprovalGranted->value => 'Goedkeuring verleend',
			self::ApprovalDenied->value  => 'Goedkeuring geweigerd',
			self::EmailVerification->value => 'E-mailverificatie',
			self::WelcomeSeries->value   => 'Welkomstserie',
			self::CartAbandonment->value => 'Verlaten winkelwagen serie',
			self::ReEngagement->value    => 'Re-engagementcampagne',
			self::Winback->value         => 'Winbackcampagne',
			self::PostPurchase->value    => 'Na-aankoop follow-up',
			self::Onboarding->value      => 'Onboardingserie',
			self::Offboarding->value     => 'Offboarding',
			self::ContractRenewal->value => 'Contractverlengingherinnering',
			self::QuoteFollowUp->value   => 'Offertefollow-up',
			self::ProposalSent->value    => 'Voorstel verzonden',
			self::ServiceReminder->value => 'Serviceherinnering',
			self::AppointmentConfirm->value => 'Afspraakbevestiging',
			self::AppointmentReminder->value => 'Afspraakherinnering',
			self::WebinarInvite->value   => 'Webinaruitnodiging',
			self::WebinarReminder->value => 'Webinarherinnering',
			self::WebinarFollowUp->value => 'Webinarfollow-up',
			self::DownloadConfirm->value => 'Downloadbevestiging',
			self::TrialExpiry->value     => 'Proefperiodevervalbericht',
			self::CreditLimit->value     => 'Kredietlimietwaarschuwing',
			self::Statement->value       => 'Accountafschrift',
			self::AnnualReport->value    => 'Jaarverslag',
			self::QuarterlyReview->value => 'Kwartaaloverzicht',
			self::PerformanceReview->value => 'Prestatiebeoordeling',
			self::SlackNotification->value => 'Slack-melding',
			self::TeamsNotification->value => 'Teams-melding',
			self::ZapierWebhook->value   => 'Zapier Webhook',
			self::Other->value           => 'Anders',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			self::Email->value           => 'E-mail',
			self::LeadAssignment->value  => 'Przypisanie leada',
			self::DealStageChange->value => 'Zmiana etapu transakcji',
			self::ContactFollowUp->value => 'Kontakt follow-up',
			self::MeetingReminder->value => 'Przypomnienie o spotkaniu',
			self::TaskAssignment->value  => 'Przypisanie zadania',
			self::TicketUpdate->value    => 'Aktualizacja zgłoszenia',
			self::TicketEscalation->value => 'Eskalacja zgłoszenia',
			self::OrderConfirmation->value => 'Potwierdzenie zamówienia',
			self::OrderShipped->value    => 'Zamówienie wysłane',
			self::OrderDelivered->value  => 'Zamówienie dostarczone',
			self::OrderCancelled->value  => 'Zamówienie anulowane',
			self::BackInStock->value     => 'Dostępne ponownie',
			self::PriceDrop->value       => 'Alert spadku ceny',
			self::AbandonedCart->value   => 'Porzucony koszyk',
			self::ReviewRequest->value   => 'Prośba o recenzję',
			self::WishlistReminder->value => 'Przypomnienie z listy życzeń',
			self::InvoiceDue->value      => 'Faktura do zapłaty',
			self::InvoiceOverdue->value  => 'Faktura przeterminowana',
			self::PaymentReceived->value => 'Płatność otrzymana',
			self::PaymentFailed->value   => 'Płatność nieudana',
			self::SubscriptionRenewal->value => 'Odnowienie subskrypcji',
			self::SubscriptionExpiry->value => 'Wygaśnięcie subskrypcji',
			self::PurchaseOrder->value   => 'Zamówienie zakupu',
			self::InventoryLow->value    => 'Alert niskiego stanu',
			self::InventoryOut->value    => 'Alert wyprzedania',
			self::UserInvitation->value  => 'Zaproszenie użytkownika',
			self::PasswordReset->value   => 'Resetowanie hasła',
			self::TwoFactor->value       => 'Uwierzytelnianie dwuetapowe',
			self::SystemAlert->value     => 'Alert systemowy',
			self::Maintenance->value     => 'Powiadomienie o konserwacji',
			self::BackupComplete->value  => 'Kopia zapasowa ukończona',
			self::ReportReady->value     => 'Raport gotowy',
			self::Welcome->value         => 'E-mail powitalny',
			self::Newsletter->value      => 'Newsletter',
			self::Promotional->value     => 'Promocyjny',
			self::EventInvitation->value => 'Zaproszenie na wydarzenie',
			self::SurveyRequest->value   => 'Prośba o ankietę',
			self::FeedbackRequest->value => 'Prośba o feedback',
			self::Anniversary->value     => 'Rocznica',
			self::LoyaltyReward->value   => 'Nagroda lojalnościowa',
			self::ReferralBonus->value   => 'Bonus poleceniowy',
			self::UpsellOpportunity->value => 'Okazja do upsell',
			self::CrossSell->value       => 'Cross-selling',
			self::WorkflowTrigger->value => 'Wyzwalacz workflow',
			self::WorkflowComplete->value => 'Workflow ukończony',
			self::ApprovalRequest->value => 'Prośba o zatwierdzenie',
			self::ApprovalGranted->value => 'Zatwierdzenie przyznane',
			self::ApprovalDenied->value  => 'Zatwierdzenie odmówione',
			self::EmailVerification->value => 'Weryfikacja e-mail',
			self::WelcomeSeries->value   => 'Seria powitalna',
			self::CartAbandonment->value => 'Seria porzuconego koszyka',
			self::ReEngagement->value    => 'Kampania re-engagement',
			self::Winback->value         => 'Kampania winback',
			self::PostPurchase->value    => 'Follow-up po zakupie',
			self::Onboarding->value      => 'Seria onboardingowa',
			self::Offboarding->value     => 'Offboarding',
			self::ContractRenewal->value => 'Przypomnienie o odnowieniu umowy',
			self::QuoteFollowUp->value   => 'Follow-up oferty',
			self::ProposalSent->value    => 'Propozycja wysłana',
			self::ServiceReminder->value => 'Przypomnienie o serwisie',
			self::AppointmentConfirm->value => 'Potwierdzenie wizyty',
			self::AppointmentReminder->value => 'Przypomnienie o wizycie',
			self::WebinarInvite->value   => 'Zaproszenie na webinar',
			self::WebinarReminder->value => 'Przypomnienie o webinarze',
			self::WebinarFollowUp->value => 'Follow-up webinaru',
			self::DownloadConfirm->value => 'Potwierdzenie pobrania',
			self::TrialExpiry->value     => 'Powiadomienie o wygaśnięciu trial',
			self::CreditLimit->value     => 'Alert limitu kredytowego',
			self::Statement->value       => 'Wyciąg z konta',
			self::AnnualReport->value    => 'Raport roczny',
			self::QuarterlyReview->value => 'Przegląd kwartalny',
			self::PerformanceReview->value => 'Przegląd wydajności',
			self::SlackNotification->value => 'Powiadomienie Slack',
			self::TeamsNotification->value => 'Powiadomienie Teams',
			self::ZapierWebhook->value   => 'Webhook Zapier',
			self::Other->value           => 'Inny',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			self::Email->value           => 'Электронная почта',
			self::LeadAssignment->value  => 'Назначение лида',
			self::DealStageChange->value => 'Изменение стадии сделки',
			self::ContactFollowUp->value => 'Контакта follow-up',
			self::MeetingReminder->value => 'Напоминание о встрече',
			self::TaskAssignment->value  => 'Назначение задачи',
			self::TicketUpdate->value    => 'Обновление тикета',
			self::TicketEscalation->value => 'Эскалация тикета',
			self::OrderConfirmation->value => 'Подтверждение заказа',
			self::OrderShipped->value    => 'Заказ отправлен',
			self::OrderDelivered->value  => 'Заказ доставлен',
			self::OrderCancelled->value  => 'Заказ отменен',
			self::BackInStock->value     => 'Снова в наличии',
			self::PriceDrop->value       => 'Оповещение о снижении цены',
			self::AbandonedCart->value   => 'Брошенная корзина',
			self::ReviewRequest->value   => 'Запрос отзыва',
			self::WishlistReminder->value => 'Напоминание списка желаний',
			self::InvoiceDue->value      => 'Счет к оплате',
			self::InvoiceOverdue->value  => 'Просроченный счет',
			self::PaymentReceived->value => 'Платеж получен',
			self::PaymentFailed->value   => 'Платеж не прошел',
			self::SubscriptionRenewal->value => 'Продление подписки',
			self::SubscriptionExpiry->value => 'Истечение подписки',
			self::PurchaseOrder->value   => 'Заказ на покупку',
			self::InventoryLow->value    => 'Оповещение низкого запаса',
			self::InventoryOut->value    => 'Оповещение отсутствия на складе',
			self::UserInvitation->value  => 'Приглашение пользователя',
			self::PasswordReset->value   => 'Сброс пароля',
			self::TwoFactor->value       => 'Двухфакторная аутентификация',
			self::SystemAlert->value     => 'Системное предупреждение',
			self::Maintenance->value     => 'Уведомление о техническом обслуживании',
			self::BackupComplete->value  => 'Резервное копирование завершено',
			self::ReportReady->value     => 'Отчет готов',
			self::Welcome->value         => 'Приветственное письмо',
			self::Newsletter->value      => 'Рассылка',
			self::Promotional->value     => 'Промо',
			self::EventInvitation->value => 'Приглашение на мероприятие',
			self::SurveyRequest->value   => 'Запрос опроса',
			self::FeedbackRequest->value => 'Запрос обратной связи',
			self::Anniversary->value     => 'Годовщина',
			self::LoyaltyReward->value   => 'Вознаграждение лояльности',
			self::ReferralBonus->value   => 'Реферальный бонус',
			self::UpsellOpportunity->value => 'Возможность апселлинга',
			self::CrossSell->value       => 'Кросс-продажи',
			self::WorkflowTrigger->value => 'Триггер workflow',
			self::WorkflowComplete->value => 'Workflow завершен',
			self::ApprovalRequest->value => 'Запрос на одобрение',
			self::ApprovalGranted->value => 'Одобрение предоставлено',
			self::ApprovalDenied->value  => 'Одобрение отклонено',
			self::EmailVerification->value => 'Подтверждение электронной почты',
			self::WelcomeSeries->value   => 'Приветственная серия',
			self::CartAbandonment->value => 'Серия брошенной корзины',
			self::ReEngagement->value    => 'Кампания повторного вовлечения',
			self::Winback->value         => 'Кампания возврата клиентов',
			self::PostPurchase->value    => 'Последующее наблюдение после покупки',
			self::Onboarding->value      => 'Серия адаптации',
			self::Offboarding->value     => 'Offboarding',
			self::ContractRenewal->value => 'Напоминание о продлении контракта',
			self::QuoteFollowUp->value   => 'Последующее наблюдение по предложению',
			self::ProposalSent->value    => 'Предложение отправлено',
			self::ServiceReminder->value => 'Напоминание об обслуживании',
			self::AppointmentConfirm->value => 'Подтверждение встречи',
			self::AppointmentReminder->value => 'Напоминание о встрече',
			self::WebinarInvite->value   => 'Приглашение на вебинар',
			self::WebinarReminder->value => 'Напоминание о вебинаре',
			self::WebinarFollowUp->value => 'Последующее наблюдение вебинара',
			self::DownloadConfirm->value => 'Подтверждение загрузки',
			self::TrialExpiry->value     => 'Уведомление об окончании пробного периода',
			self::CreditLimit->value     => 'Оповещение о кредитном лимите',
			self::Statement->value       => 'Выписка по счету',
			self::AnnualReport->value    => 'Годовой отчет',
			self::QuarterlyReview->value => 'Квартальный обзор',
			self::PerformanceReview->value => 'Обзор эффективности',
			self::SlackNotification->value => 'Уведомление Slack',
			self::TeamsNotification->value => 'Уведомление Teams',
			self::ZapierWebhook->value   => 'Webhook Zapier',
			self::Other->value           => 'Другое',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			self::Email->value           => 'E-posta',
			self::LeadAssignment->value  => 'Lead Ataması',
			self::DealStageChange->value => 'Deal Aşama Değişikliği',
			self::ContactFollowUp->value => 'İletişim Takibi',
			self::MeetingReminder->value => 'Toplantı Hatırlatıcısı',
			self::TaskAssignment->value  => 'Görev Ataması',
			self::TicketUpdate->value    => 'Ticket Güncellemesi',
			self::TicketEscalation->value => 'Ticket Eskalasyonu',
			self::OrderConfirmation->value => 'Sipariş Onayı',
			self::OrderShipped->value    => 'Sipariş Gönderildi',
			self::OrderDelivered->value  => 'Sipariş Teslim Edildi',
			self::OrderCancelled->value  => 'Sipariş İptal Edildi',
			self::BackInStock->value     => 'Tekrar Stokta',
			self::PriceDrop->value       => 'Fiyat Düşüşü Uyarısı',
			self::AbandonedCart->value   => 'Terk Edilen Sepet',
			self::ReviewRequest->value   => 'Değerlendirme İsteği',
			self::WishlistReminder->value => 'İstek Listesi Hatırlatıcısı',
			self::InvoiceDue->value      => 'Fatura Vadesi',
			self::InvoiceOverdue->value  => 'Gecikmiş Fatura',
			self::PaymentReceived->value => 'Ödeme Alındı',
			self::PaymentFailed->value   => 'Ödeme Başarısız',
			self::SubscriptionRenewal->value => 'Abonelik Yenileme',
			self::SubscriptionExpiry->value => 'Abonelik Süresi Dolması',
			self::PurchaseOrder->value   => 'Satın Alma Siparişi',
			self::InventoryLow->value    => 'Düşük Stok Uyarısı',
			self::InventoryOut->value    => 'Stokta Yok Uyarısı',
			self::UserInvitation->value  => 'Kullanıcı Daveti',
			self::PasswordReset->value   => 'Şifre Sıfırlama',
			self::TwoFactor->value       => 'İki Faktörlü Doğrulama',
			self::SystemAlert->value     => 'Sistem Uyarısı',
			self::Maintenance->value     => 'Bakım Bildirimi',
			self::BackupComplete->value  => 'Yedekleme Tamamlandı',
			self::ReportReady->value     => 'Rapor Hazır',
			self::Welcome->value         => 'Hoş Geldiniz E-postası',
			self::Newsletter->value      => 'Bülten',
			self::Promotional->value     => 'Promosyon',
			self::EventInvitation->value => 'Etkinlik Daveti',
			self::SurveyRequest->value   => 'Anket İsteği',
			self::FeedbackRequest->value => 'Geri Bildirim İsteği',
			self::Anniversary->value     => 'Yıl Dönümü',
			self::LoyaltyReward->value   => 'Sadakat Ödülü',
			self::ReferralBonus->value   => 'Referans Bonusu',
			self::UpsellOpportunity->value => 'Üst Satış Fırsatı',
			self::CrossSell->value       => 'Çapraz Satış',
			self::WorkflowTrigger->value => 'İş Akışı Tetikleyicisi',
			self::WorkflowComplete->value => 'İş Akışı Tamamlandı',
			self::ApprovalRequest->value => 'Onay İsteği',
			self::ApprovalGranted->value => 'Onay Verildi',
			self::ApprovalDenied->value  => 'Onay Reddedildi',
			self::EmailVerification->value => 'E-posta Doğrulama',
			self::WelcomeSeries->value   => 'Hoş Geldiniz Serisi',
			self::CartAbandonment->value => 'Terk Edilen Sepet Serisi',
			self::ReEngagement->value    => 'Yeniden Bağlanma Kampanyası',
			self::Winback->value         => 'Müşteri Kazanma Kampanyası',
			self::PostPurchase->value    => 'Satın Sonrası Takip',
			self::Onboarding->value      => 'Oryantasyon Serisi',
			self::Offboarding->value     => 'Offboarding',
			self::ContractRenewal->value => 'Sözleşme Yenileme Hatırlatıcısı',
			self::QuoteFollowUp->value   => 'Teklif Takibi',
			self::ProposalSent->value    => 'Teklif Gönderildi',
			self::ServiceReminder->value => 'Servis Hatırlatıcısı',
			self::AppointmentConfirm->value => 'Randevu Onayı',
			self::AppointmentReminder->value => 'Randevu Hatırlatıcısı',
			self::WebinarInvite->value   => 'Webinar Daveti',
			self::WebinarReminder->value => 'Webinar Hatırlatıcısı',
			self::WebinarFollowUp->value => 'Webinar Takibi',
			self::DownloadConfirm->value => 'İndirme Onayı',
			self::TrialExpiry->value     => 'Deneme Süresi Sona Erme Bildirimi',
			self::CreditLimit->value     => 'Kredi Limiti Uyarısı',
			self::Statement->value       => 'Hesap Ekstresi',
			self::AnnualReport->value    => 'Yıllık Rapor',
			self::QuarterlyReview->value => 'Çeyrek Değerlendirme',
			self::PerformanceReview->value => 'Performans Değerlendirmesi',
			self::SlackNotification->value => 'Slack Bildirimi',
			self::TeamsNotification->value => 'Teams Bildirimi',
			self::ZapierWebhook->value   => 'Zapier Webhook',
			self::Other->value           => 'Diğer',
		];
	}

	// Chinese (Simplified) Labels
	public static function labelsZh(): array
	{
		return [
			self::Email->value           => '电子邮件',
			self::LeadAssignment->value  => '线索分配',
			self::DealStageChange->value => '交易阶段变更',
			self::ContactFollowUp->value => '联系人跟进',
			self::MeetingReminder->value => '会议提醒',
			self::TaskAssignment->value  => '任务分配',
			self::TicketUpdate->value    => '工单更新',
			self::TicketEscalation->value => '工单升级',
			self::OrderConfirmation->value => '订单确认',
			self::OrderShipped->value    => '订单已发货',
			self::OrderDelivered->value  => '订单已送达',
			self::OrderCancelled->value  => '订单已取消',
			self::BackInStock->value     => '恢复库存',
			self::PriceDrop->value       => '价格下降提醒',
			self::AbandonedCart->value   => '放弃的购物车',
			self::ReviewRequest->value   => '评价请求',
			self::WishlistReminder->value => '愿望清单提醒',
			self::InvoiceDue->value      => '发票到期',
			self::InvoiceOverdue->value  => '逾期发票',
			self::PaymentReceived->value => '付款已收到',
			self::PaymentFailed->value   => '付款失败',
			self::SubscriptionRenewal->value => '订阅续订',
			self::SubscriptionExpiry->value => '订阅到期',
			self::PurchaseOrder->value   => '采购订单',
			self::InventoryLow->value    => '库存不足提醒',
			self::InventoryOut->value    => '缺货提醒',
			self::UserInvitation->value  => '用户邀请',
			self::PasswordReset->value   => '密码重置',
			self::TwoFactor->value       => '双重认证',
			self::SystemAlert->value     => '系统警报',
			self::Maintenance->value     => '维护通知',
			self::BackupComplete->value  => '备份完成',
			self::ReportReady->value     => '报告就绪',
			self::Welcome->value         => '欢迎邮件',
			self::Newsletter->value      => '新闻通讯',
			self::Promotional->value     => '促销',
			self::EventInvitation->value => '活动邀请',
			self::SurveyRequest->value   => '调查请求',
			self::FeedbackRequest->value => '反馈请求',
			self::Anniversary->value     => '周年纪念',
			self::LoyaltyReward->value   => '忠诚度奖励',
			self::ReferralBonus->value   => '推荐奖金',
			self::UpsellOpportunity->value => '追加销售机会',
			self::CrossSell->value       => '交叉销售',
			self::WorkflowTrigger->value => '工作流触发器',
			self::WorkflowComplete->value => '工作流完成',
			self::ApprovalRequest->value => '审批请求',
			self::ApprovalGranted->value => '已批准',
			self::ApprovalDenied->value  => '已拒绝',
			self::EmailVerification->value => '电子邮件验证',
			self::WelcomeSeries->value   => '欢迎系列',
			self::CartAbandonment->value => '放弃购物车系列',
			self::ReEngagement->value    => '重新参与活动',
			self::Winback->value         => '赢回活动',
			self::PostPurchase->value    => '购买后跟进',
			self::Onboarding->value      => '入职系列',
			self::Offboarding->value     => '离职流程',
			self::ContractRenewal->value => '合同续签提醒',
			self::QuoteFollowUp->value   => '报价跟进',
			self::ProposalSent->value    => '提案已发送',
			self::ServiceReminder->value => '服务提醒',
			self::AppointmentConfirm->value => '预约确认',
			self::AppointmentReminder->value => '预约提醒',
			self::WebinarInvite->value   => '网络研讨会邀请',
			self::WebinarReminder->value => '网络研讨会提醒',
			self::WebinarFollowUp->value => '网络研讨会跟进',
			self::DownloadConfirm->value => '下载确认',
			self::TrialExpiry->value     => '试用期到期通知',
			self::CreditLimit->value     => '信用额度提醒',
			self::Statement->value       => '账户对账单',
			self::AnnualReport->value    => '年度报告',
			self::QuarterlyReview->value => '季度审查',
			self::PerformanceReview->value => '绩效评估',
			self::SlackNotification->value => 'Slack 通知',
			self::TeamsNotification->value => 'Teams 通知',
			self::ZapierWebhook->value   => 'Zapier Webhook',
			self::Other->value           => '其他',
		];
	}

	/**
	 * Get the AppModuleType associated with this email template
	 */
	public function getModule(): AppModuleType
	{
		return match ($this) {
			// Financial module
			self::InvoiceDue, self::InvoiceOverdue, self::PaymentReceived,
			self::PaymentFailed, self::SubscriptionRenewal, self::SubscriptionExpiry,
			self::PurchaseOrder, self::CreditLimit, self::Statement,
			self::AnnualReport, self::QuarterlyReview => AppModuleType::Financial,

			// Sales module
			self::OrderConfirmation, self::OrderShipped, self::OrderDelivered,
			self::OrderCancelled, self::PriceDrop, self::AbandonedCart,
			self::ReviewRequest, self::WishlistReminder, self::BackInStock,
			self::UpsellOpportunity, self::CrossSell => AppModuleType::Sales,

			// CRM module
			self::LeadAssignment, self::DealStageChange, self::ContactFollowUp,
			self::MeetingReminder, self::QuoteFollowUp, self::ProposalSent,
			self::AppointmentConfirm, self::AppointmentReminder => AppModuleType::CRM,

			// Support module
			self::TicketUpdate, self::TicketEscalation, self::ServiceReminder => AppModuleType::Support,

			// HRM module
			self::UserInvitation, self::PasswordReset, self::TwoFactor,
			self::Onboarding, self::Offboarding, self::PerformanceReview => AppModuleType::HRM,

			// Projects module
			self::TaskAssignment, self::WorkflowTrigger, self::WorkflowComplete,
			self::ApprovalRequest, self::ApprovalGranted, self::ApprovalDenied => AppModuleType::Projects,

			// Inventory module
			self::InventoryLow, self::InventoryOut => AppModuleType::Inventory,

			// Marketing module
			self::Newsletter, self::Promotional, self::EventInvitation,
			self::SurveyRequest, self::FeedbackRequest, self::Welcome,
			self::Anniversary, self::LoyaltyReward, self::ReferralBonus,
			self::EmailVerification, self::WelcomeSeries, self::CartAbandonment,
			self::ReEngagement, self::Winback, self::PostPurchase,
			self::WebinarInvite, self::WebinarReminder, self::WebinarFollowUp => AppModuleType::Marketing,

			// User module
			self::SystemAlert, self::Maintenance, self::BackupComplete,
			self::ReportReady, self::TrialExpiry => AppModuleType::User,

			// Infrastructure module
			self::SlackNotification, self::TeamsNotification, self::ZapierWebhook => AppModuleType::Infrastructure,

			// Customer module
			self::DownloadConfirm => AppModuleType::Customer,

			// Product module
			self::ContractRenewal => AppModuleType::Product,

			// Default
			default => AppModuleType::Other,
		};
	}

	/**
	 * Get the corresponding NotificationTemplateType (if applicable)
	 */
	public function getNotificationType(): ?NotificationTemplateType
	{
		return match ($this) {
			// Direct matches
			self::LeadAssignment => NotificationTemplateType::LeadAssignment,
			self::DealStageChange => NotificationTemplateType::DealStageChange,
			self::ContactFollowUp => NotificationTemplateType::ContactFollowUp,
			self::MeetingReminder => NotificationTemplateType::MeetingReminder,
			self::TaskAssignment => NotificationTemplateType::TaskAssignment,
			self::TicketUpdate => NotificationTemplateType::TicketUpdate,
			self::TicketEscalation => NotificationTemplateType::TicketEscalation,
			self::OrderConfirmation => NotificationTemplateType::OrderConfirmation,
			self::OrderShipped => NotificationTemplateType::OrderShipped,
			self::OrderDelivered => NotificationTemplateType::OrderDelivered,
			self::OrderCancelled => NotificationTemplateType::OrderCancelled,
			self::BackInStock => NotificationTemplateType::BackInStock,
			self::PriceDrop => NotificationTemplateType::PriceDrop,
			self::AbandonedCart => NotificationTemplateType::AbandonedCart,
			self::ReviewRequest => NotificationTemplateType::ReviewRequest,
			self::WishlistReminder => NotificationTemplateType::WishlistReminder,
			self::InvoiceDue => NotificationTemplateType::InvoiceDue,
			self::InvoiceOverdue => NotificationTemplateType::InvoiceOverdue,
			self::PaymentReceived => NotificationTemplateType::PaymentReceived,
			self::PaymentFailed => NotificationTemplateType::PaymentFailed,
			self::SubscriptionRenewal => NotificationTemplateType::SubscriptionRenewal,
			self::SubscriptionExpiry => NotificationTemplateType::SubscriptionExpiry,
			self::PurchaseOrder => NotificationTemplateType::PurchaseOrder,
			self::InventoryLow => NotificationTemplateType::InventoryLow,
			self::InventoryOut => NotificationTemplateType::InventoryOut,
			self::UserInvitation => NotificationTemplateType::UserInvitation,
			self::PasswordReset => NotificationTemplateType::PasswordReset,
			self::TwoFactor => NotificationTemplateType::TwoFactor,
			self::SystemAlert => NotificationTemplateType::SystemAlert,
			self::Maintenance => NotificationTemplateType::Maintenance,
			self::BackupComplete => NotificationTemplateType::BackupComplete,
			self::ReportReady => NotificationTemplateType::ReportReady,
			self::Welcome => NotificationTemplateType::Welcome,
			self::Anniversary => NotificationTemplateType::Anniversary,
			self::LoyaltyReward => NotificationTemplateType::LoyaltyReward,
			self::ReferralBonus => NotificationTemplateType::ReferralBonus,
			self::UpsellOpportunity => NotificationTemplateType::UpsellOpportunity,
			self::CrossSell => NotificationTemplateType::CrossSell,
			self::WorkflowTrigger => NotificationTemplateType::WorkflowTrigger,
			self::WorkflowComplete => NotificationTemplateType::WorkflowComplete,
			self::ApprovalRequest => NotificationTemplateType::ApprovalRequest,
			self::ApprovalGranted => NotificationTemplateType::ApprovalGranted,
			self::ApprovalDenied => NotificationTemplateType::ApprovalDenied,
			self::Newsletter => NotificationTemplateType::Newsletter,
			self::Promotional => NotificationTemplateType::Promotional,
			self::EventInvitation => NotificationTemplateType::EventInvitation,
			self::SurveyRequest => NotificationTemplateType::SurveyRequest,
			self::FeedbackRequest => NotificationTemplateType::FeedbackRequest,

			// Email-only templates have no direct notification equivalent
			default => null,
		};
	}

	/**
	 * Check if this template is transactional (vs marketing)
	 */
	public function isTransactional(): bool
	{
		return match ($this) {
			self::EmailVerification, self::PasswordReset, self::TwoFactor,
			self::UserInvitation, self::OrderConfirmation, self::OrderShipped,
			self::OrderDelivered, self::OrderCancelled, self::InvoiceDue,
			self::InvoiceOverdue, self::PaymentReceived, self::PaymentFailed,
			self::SubscriptionRenewal, self::SubscriptionExpiry, self::PurchaseOrder,
			self::InventoryLow, self::InventoryOut, self::TicketUpdate,
			self::TicketEscalation, self::TaskAssignment, self::LeadAssignment,
			self::DealStageChange, self::ContactFollowUp, self::MeetingReminder,
			self::SystemAlert, self::Maintenance, self::BackupComplete,
			self::ReportReady, self::WorkflowTrigger, self::WorkflowComplete,
			self::ApprovalRequest, self::ApprovalGranted, self::ApprovalDenied,
			self::AppointmentConfirm, self::AppointmentReminder, self::DownloadConfirm,
			self::TrialExpiry, self::CreditLimit, self::Statement,
			self::PerformanceReview, self::Onboarding, self::Offboarding,
			self::ContractRenewal, self::QuoteFollowUp, self::ProposalSent,
			self::ServiceReminder, self::SlackNotification, self::TeamsNotification,
			self::ZapierWebhook => true,

			default => false,
		};
	}

	/**
	 * Check if this template is marketing/broadcast
	 */
	public function isMarketing(): bool
	{
		return match ($this) {
			self::Newsletter, self::Promotional, self::EventInvitation,
			self::SurveyRequest, self::FeedbackRequest, self::Welcome,
			self::Anniversary, self::LoyaltyReward, self::ReferralBonus,
			self::WelcomeSeries, self::CartAbandonment, self::ReEngagement,
			self::Winback, self::PostPurchase, self::WebinarInvite,
			self::WebinarReminder, self::WebinarFollowUp, self::PriceDrop,
			self::BackInStock, self::ReviewRequest, self::WishlistReminder,
			self::UpsellOpportunity, self::CrossSell => true,

			default => false,
		};
	}

	/**
	 * Check if this template is automated/triggered
	 */
	public function isAutomated(): bool
	{
		return match ($this) {
			self::AbandonedCart, self::WelcomeSeries, self::CartAbandonment,
			self::ReEngagement, self::Winback, self::PostPurchase,
			self::Onboarding, self::InvoiceDue, self::InvoiceOverdue,
			self::SubscriptionRenewal, self::SubscriptionExpiry, self::TrialExpiry,
			self::ContractRenewal, self::ServiceReminder, self::Anniversary,
			self::WorkflowTrigger, self::WorkflowComplete => true,

			default => false,
		};
	}

	/**
	 * Check if this template requires personalization
	 */
	public function requiresPersonalization(): bool
	{
		return match ($this) {
			self::UserInvitation, self::Welcome, self::Anniversary,
			self::LoyaltyReward, self::ReferralBonus, self::UpsellOpportunity,
			self::CrossSell, self::Onboarding, self::PerformanceReview,
			self::ProposalSent, self::QuoteFollowUp, self::ContractRenewal,
			self::Statement, self::AnnualReport, self::QuarterlyReview => true,

			default => false,
		};
	}

	/**
	 * Get recommended sending frequency
	 */
	public function getFrequency(): string
	{
		return match ($this) {
			self::Newsletter => 'weekly',
			self::Promotional => 'monthly',
			self::EventInvitation => 'as_needed',
			self::SurveyRequest => 'quarterly',
			self::FeedbackRequest => 'post_transaction',
			self::WelcomeSeries => 'immediate_sequence',
			self::CartAbandonment => 'triggered_sequence',
			self::ReEngagement => 'quarterly',
			self::Winback => 'semi_annual',
			self::PostPurchase => 'post_transaction',
			self::Onboarding => 'immediate_sequence',
			self::InvoiceDue => 'monthly',
			self::InvoiceOverdue => 'escalating',
			self::SubscriptionRenewal => 'monthly',
			self::SubscriptionExpiry => 'pre_expiry',
			self::TrialExpiry => 'pre_expiry',
			self::ContractRenewal => 'quarterly',
			self::ServiceReminder => 'scheduled',
			self::AppointmentReminder => 'pre_appointment',
			self::WebinarReminder => 'pre_event',
			self::WebinarFollowUp => 'post_event',
			self::AnnualReport => 'annual',
			self::QuarterlyReview => 'quarterly',
			self::PerformanceReview => 'biannual',

			default => 'as_needed',
		};
	}

	/**
	 * Get typical engagement metrics for this template type
	 */
	public function getExpectedMetrics(): array
	{
		if ($this->isTransactional())
			return ['open_rate' => '50-70%', 'click_rate' => '10-20%'];
		return match ($this) {
			self::Newsletter => ['open_rate' => '20-30%', 'click_rate' => '2-5%'],
			self::Promotional => ['open_rate' => '15-25%', 'click_rate' => '3-7%'],
			self::WelcomeSeries => ['open_rate' => '50-70%', 'click_rate' => '15-30%'],
			self::AbandonedCart => ['open_rate' => '45-65%', 'click_rate' => '20-40%'],
			self::InvoiceOverdue => ['open_rate' => '70-85%', 'click_rate' => '25-40%'],

			default => ['open_rate' => '30-40%', 'click_rate' => '5-15%'],
		};
	}

	/**
	 * Get recommended email client compatibility notes
	 */
	public function getClientCompatibility(): array
	{
		if ($this->isTransactional())
			return [
				'gmail' => 'Excellent',
				'outlook' => 'Good',
				'apple_mail' => 'Excellent',
				'yahoo' => 'Good',
				'mobile' => 'Excellent',
			];
		return match ($this) {
			self::Newsletter, self::Promotional, self::EventInvitation => [
				'gmail' => 'Good',
				'outlook' => 'Good',
				'apple_mail' => 'Excellent',
				'yahoo' => 'Good',
				'mobile' => 'Excellent',
			],
			default => [
				'gmail' => 'Good',
				'outlook' => 'Good',
				'apple_mail' => 'Good',
				'yahoo' => 'Good',
				'mobile' => 'Good',
			],
		};
	}

	/**
	 * Check if template is GDPR sensitive
	 */
	public function isGdprSensitive(): bool
	{
		return match ($this) {
			self::PasswordReset, self::TwoFactor, self::UserInvitation,
			self::PaymentFailed, self::CreditLimit, self::Statement,
			self::AnnualReport, self::QuarterlyReview, self::PerformanceReview => true,

			default => false,
		};
	}

	/**
	 * Get suggested A/B test variations for this template type
	 */
	public function getAbTestSuggestions(): array
	{
		return match ($this) {
			self::Newsletter => ['subject_line', 'preheader', 'cta_text', 'layout'],
			self::Promotional => ['subject_line', 'discount_amount', 'urgency', 'imagery'],
			self::AbandonedCart => ['subject_line', 'reminder_count', 'offer_type', 'urgency'],
			self::WelcomeSeries => ['sequence_length', 'content_type', 'personalization_level'],
			self::InvoiceOverdue => ['subject_line', 'tone', 'payment_options', 'urgency'],

			default => ['subject_line', 'preheader'],
		};
	}

	/**
	 * Get category for grouping templates in UI
	 */
	public function getCategory(): string
	{
		return match ($this) {
			self::LeadAssignment, self::DealStageChange, self::ContactFollowUp,
			self::MeetingReminder, self::TaskAssignment, self::TicketUpdate,
			self::TicketEscalation, self::QuoteFollowUp, self::ProposalSent,
			self::AppointmentConfirm, self::AppointmentReminder => 'crm',

			self::OrderConfirmation, self::OrderShipped, self::OrderDelivered,
			self::OrderCancelled, self::BackInStock, self::PriceDrop,
			self::AbandonedCart, self::ReviewRequest, self::WishlistReminder,
			self::UpsellOpportunity, self::CrossSell => 'ecommerce',

			self::InvoiceDue, self::InvoiceOverdue, self::PaymentReceived,
			self::PaymentFailed, self::SubscriptionRenewal, self::SubscriptionExpiry,
			self::PurchaseOrder, self::CreditLimit, self::Statement,
			self::AnnualReport, self::QuarterlyReview => 'financial',

			self::Newsletter, self::Promotional, self::EventInvitation,
			self::SurveyRequest, self::FeedbackRequest, self::Welcome,
			self::Anniversary, self::LoyaltyReward, self::ReferralBonus,
			self::EmailVerification, self::WelcomeSeries, self::CartAbandonment,
			self::ReEngagement, self::Winback, self::PostPurchase,
			self::WebinarInvite, self::WebinarReminder, self::WebinarFollowUp => 'marketing',

			self::UserInvitation, self::PasswordReset, self::TwoFactor,
			self::SystemAlert, self::Maintenance, self::BackupComplete,
			self::ReportReady, self::Onboarding, self::Offboarding,
			self::PerformanceReview, self::TrialExpiry => 'system',

			self::WorkflowTrigger, self::WorkflowComplete, self::ApprovalRequest,
			self::ApprovalGranted, self::ApprovalDenied => 'workflow',

			self::InventoryLow, self::InventoryOut, self::ServiceReminder,
			self::ContractRenewal => 'operations',

			self::SlackNotification, self::TeamsNotification, self::ZapierWebhook => 'integrations',

			self::DownloadConfirm => 'downloads',

			default => 'general',
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			self::Newsletter => 'newspaper',
			self::Promotional => 'megaphone',
			self::EventInvitation => 'calendar',
			self::Welcome => 'hand-wave',
			self::InvoiceDue => 'file-invoice-dollar',
			self::InvoiceOverdue => 'alert-triangle',
			self::OrderConfirmation => 'shopping-cart',
			self::PasswordReset => 'lock',
			self::TwoFactor => 'shield',
			self::UserInvitation => 'user-plus',
			self::LeadAssignment => 'target',
			self::TaskAssignment => 'clipboard-list',
			self::MeetingReminder => 'clock',
			self::AppointmentConfirm => 'calendar-check',
			self::WebinarInvite => 'video',
			self::DownloadConfirm => 'download',
			self::PerformanceReview => 'star',
			self::AnnualReport => 'chart-bar',
			self::Newsletter => 'mail',

			default => 'mail',
		};
	}
}
