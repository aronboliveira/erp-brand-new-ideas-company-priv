<?php

namespace App\Enums;

use BackedEnum;
use App\Config\Constants\DatabaseConstants;

enum PurchaseStatus: string
{
	case Draft            = 'draft';
	case Pending          = 'pending';
	case Confirmed        = 'confirmed';
	case Processing       = 'processing';
	case Unpaid					  = 'unpaid';
	case PartiallyPaid    = 'partially_paid';
	case Paid             = 'paid';
	case Shipped          = 'shipped';
	case PartiallyShipped = 'partially_shipped';
	case Delivered        = 'delivered';
	case Completed        = 'completed';
	case OnHold           = 'on_hold';
	case Cancelled        = 'cancelled';
	case Refunded         = 'refunded';
	case PartiallyRefunded = 'partially_refunded';
	case Returned         = 'returned';
	case Failed           = 'failed';
	case Backordered      = 'backordered';
	case PreOrder         = 'pre_order';
	case AwaitingPayment  = 'awaiting_payment';
	case AwaitingFulfillment = 'awaiting_fulfillment';
	case AwaitingShipment = 'awaiting_shipment';
	case AwaitingPickup   = 'awaiting_pickup';
	case Undefined        = 'undefined';

	public static function normalize(null|string|BackedEnum $v): self
	{
		if ($v instanceof self) return $v;
		if ($v === null) return self::Undefined;

		$k = strtolower(trim((string) $v));

		$aliases = [
			// Draft aliases
			'new'              => 'draft',
			'quote'            => 'draft',
			'estimate'         => 'draft',
			'proposal'         => 'draft',

			// Pending aliases
			'waiting'          => 'pending',
			'order_received'   => 'pending',
			'order_placed'     => 'pending',

			// Confirmed aliases
			'accepted'         => 'confirmed',
			'approved'         => 'confirmed',
			'validated'        => 'confirmed',
			'acknowledged'     => 'confirmed',

			// Processing aliases
			'in_progress'      => 'processing',
			'preparing'        => 'processing',
			'packing'          => 'processing',
			'order_processing' => 'processing',

			// Payment status aliases
			'unpaid'           => 'unpaid',
			'payment_pending'  => 'awaiting_payment',
			'partial'          => 'partially_paid',
			'partial_payment'  => 'partially_paid',
			'settled'          => 'paid',
			'payment_received' => 'paid',
			'fully_paid'       => 'paid',
			'cleared'          => 'paid',

			// Shipping status aliases
			'awaiting_dispatch' => 'awaiting_shipment',
			'ready_to_ship'    => 'awaiting_shipment',
			'dispatched'       => 'shipped',
			'in_transit'       => 'shipped',
			'sent'             => 'shipped',
			'partial_delivery' => 'partially_shipped',
			'received'         => 'delivered',
			'fulfilled'        => 'completed',
			'closed'           => 'completed',

			// Hold/issue aliases
			'hold'             => 'on_hold',
			'paused'           => 'on_hold',
			'suspended'        => 'on_hold',
			'payment_issue'    => 'on_hold',
			'address_issue'    => 'on_hold',
			'review'           => 'on_hold',

			// Cancellation aliases
			'canceled'         => 'cancelled',
			'void'             => 'cancelled',
			'voided'           => 'cancelled',
			'abandoned'        => 'cancelled',

			// Refund aliases
			'refund'           => 'refunded',
			'reimbursed'       => 'refunded',
			'chargeback'       => 'refunded',
			'disputed'         => 'refunded',

			// Return aliases
			'return'           => 'returned',
			'goods_returned'   => 'returned',
			'customer_return'  => 'returned',

			// Failure aliases
			'error'            => 'failed',
			'declined'         => 'failed',
			'rejected'         => 'failed',
			'payment_failed'   => 'failed',

			// Backorder aliases
			'out_of_stock'     => 'backordered',
			'awaiting_stock'   => 'backordered',
			'stock_issue'      => 'backordered',

			// Pre-order aliases
			'preorder'         => 'pre_order',
			'pre_sale'         => 'pre_order',
			'coming_soon'      => 'pre_order',

			// Portuguese aliases
			'rascunho'         => 'draft',
			'pendente'         => 'pending',
			'confirmado'       => 'confirmed',
			'processando'      => 'processing',
			'parcialmente_pago' => 'partially_paid',
			'pago'             => 'paid',
			'enviado'          => 'shipped',
			'parcialmente_enviado' => 'partially_shipped',
			'entregue'         => 'delivered',
			'concluido'        => 'completed',
			'em_espera'        => 'on_hold',
			'cancelado'        => 'cancelled',
			'reembolsado'      => 'refunded',
			'devolvido'        => 'returned',
			'falhou'           => 'failed',
			'backorder'        => 'backordered',
			'pre_venda'        => 'pre_order',
			'aguardando_pagamento' => 'awaiting_payment',

			// Spanish aliases
			'borrador'         => 'draft',
			'parcialmente_pagado' => 'partially_paid',
			'entregado'        => 'delivered',
			'en_espera'        => 'on_hold',
			'devuelto'         => 'returned',
			'fallido'          => 'failed',
			'pedido_anticipado' => 'backordered',
		];

		$k = $aliases[$k] ?? $k;

		return self::tryFrom($k) ?? self::Undefined;
	}

	public static function getIndex(?string $case): int
	{
		return match ($case) {
			self::Draft->value             => 0,
			self::Pending->value           => 1,
			self::Confirmed->value         => 2,
			self::Processing->value        => 3,
			self::Unpaid->value            => 4,
			self::PartiallyPaid->value     => 5,
			self::Paid->value              => 6,
			self::Shipped->value           => 7,
			self::PartiallyShipped->value  => 8,
			self::Delivered->value         => 9,
			self::Completed->value         => 10,
			self::OnHold->value            => 11,
			self::Cancelled->value         => 12,
			self::Refunded->value          => 13,
			self::PartiallyRefunded->value => 14,
			self::Returned->value          => 15,
			self::Failed->value            => 16,
			self::Backordered->value       => 17,
			self::PreOrder->value          => 18,
			self::AwaitingPayment->value   => 19,
			self::AwaitingFulfillment->value => 20,
			self::AwaitingShipment->value  => 21,
			self::AwaitingPickup->value    => 22,
			self::Undefined->value         => 23,
			default => 23
		};
	}

	public static function getAllIndexes(): array
	{
		return array_values(
			array_map(fn($value) => self::getIndex($value), self::values())
		);
	}

	public static function values(): array
	{
		return [
			self::Draft->value,
			self::Pending->value,
			self::Confirmed->value,
			self::Processing->value,
			self::Unpaid->value,
			self::PartiallyPaid->value,
			self::Paid->value,
			self::Shipped->value,
			self::PartiallyShipped->value,
			self::Delivered->value,
			self::Completed->value,
			self::OnHold->value,
			self::Cancelled->value,
			self::Refunded->value,
			self::PartiallyRefunded->value,
			self::Returned->value,
			self::Failed->value,
			self::Backordered->value,
			self::PreOrder->value,
			self::AwaitingPayment->value,
			self::AwaitingFulfillment->value,
			self::AwaitingShipment->value,
			self::AwaitingPickup->value,
			self::Undefined->value,
		];
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
			self::Draft->value             => 'Rascunho',
			self::Pending->value           => 'Pendente',
			self::Confirmed->value         => 'Confirmado',
			self::Processing->value        => 'Processando',
			self::Unpaid->value            => 'Não Pago',
			self::PartiallyPaid->value     => 'Parcialmente Pago',
			self::Paid->value              => 'Pago',
			self::Shipped->value           => 'Enviado',
			self::PartiallyShipped->value  => 'Parcialmente Enviado',
			self::Delivered->value         => 'Entregue',
			self::Completed->value         => 'Concluído',
			self::OnHold->value            => 'Em Espera',
			self::Cancelled->value         => 'Cancelado',
			self::Refunded->value          => 'Reembolsado',
			self::PartiallyRefunded->value => 'Parcialmente Reembolsado',
			self::Returned->value          => 'Devolvido',
			self::Failed->value            => 'Falhou',
			self::Backordered->value       => 'Backorder',
			self::PreOrder->value          => 'Pré-venda',
			self::AwaitingPayment->value   => 'Aguardando Pagamento',
			self::AwaitingFulfillment->value => 'Aguardando Preparação',
			self::AwaitingShipment->value  => 'Aguardando Envio',
			self::AwaitingPickup->value    => 'Aguardando Retirada',
			self::Undefined->value         => 'Indefinido',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Draft->value             => 'Draft',
			self::Pending->value           => 'Pending',
			self::Confirmed->value         => 'Confirmed',
			self::Processing->value        => 'Processing',
			self::Unpaid->value            => 'Unpaid',
			self::PartiallyPaid->value     => 'Partially Paid',
			self::Paid->value              => 'Paid',
			self::Shipped->value           => 'Shipped',
			self::PartiallyShipped->value  => 'Partially Shipped',
			self::Delivered->value         => 'Delivered',
			self::Completed->value         => 'Completed',
			self::OnHold->value            => 'On Hold',
			self::Cancelled->value         => 'Cancelled',
			self::Refunded->value          => 'Refunded',
			self::PartiallyRefunded->value => 'Partially Refunded',
			self::Returned->value          => 'Returned',
			self::Failed->value            => 'Failed',
			self::Backordered->value       => 'Backordered',
			self::PreOrder->value          => 'Pre-order',
			self::AwaitingPayment->value   => 'Awaiting Payment',
			self::AwaitingFulfillment->value => 'Awaiting Fulfillment',
			self::AwaitingShipment->value  => 'Awaiting Shipment',
			self::AwaitingPickup->value    => 'Awaiting Pickup',
			self::Undefined->value         => 'Undefined',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Draft->value             => 'Borrador',
			self::Pending->value           => 'Pendiente',
			self::Confirmed->value         => 'Confirmado',
			self::Processing->value        => 'Procesando',
			self::Unpaid->value            => 'No Pagado',
			self::PartiallyPaid->value     => 'Parcialmente Pagado',
			self::Paid->value              => 'Pagado',
			self::Shipped->value           => 'Enviado',
			self::PartiallyShipped->value  => 'Parcialmente Enviado',
			self::Delivered->value         => 'Entregado',
			self::Completed->value         => 'Completado',
			self::OnHold->value            => 'En Espera',
			self::Cancelled->value         => 'Cancelado',
			self::Refunded->value          => 'Reembolsado',
			self::PartiallyRefunded->value => 'Parcialmente Reembolsado',
			self::Returned->value          => 'Devuelto',
			self::Failed->value            => 'Fallido',
			self::Backordered->value       => 'Pedido Anticipado',
			self::PreOrder->value          => 'Preventa',
			self::AwaitingPayment->value   => 'Esperando Pago',
			self::AwaitingFulfillment->value => 'Esperando Preparación',
			self::AwaitingShipment->value  => 'Esperando Envío',
			self::AwaitingPickup->value    => 'Esperando Recogida',
			self::Undefined->value         => 'Indefinido',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Draft->value             => 'مسودة',
			self::Pending->value           => 'قيد الانتظار',
			self::Confirmed->value         => 'مؤكد',
			self::Processing->value        => 'جاري المعالجة',
			self::Unpaid->value            => 'غير مدفوع',
			self::PartiallyPaid->value     => 'مدفوع جزئيًا',
			self::Paid->value              => 'مدفوع',
			self::Shipped->value           => 'تم الشحن',
			self::PartiallyShipped->value  => 'مشحون جزئيًا',
			self::Delivered->value         => 'تم التوصيل',
			self::Completed->value         => 'مكتمل',
			self::OnHold->value            => 'معلق',
			self::Cancelled->value         => 'ملغي',
			self::Refunded->value          => 'مسترد',
			self::PartiallyRefunded->value => 'مسترد جزئيًا',
			self::Returned->value          => 'معاد',
			self::Failed->value            => 'فشل',
			self::Backordered->value       => 'طلبية مؤجلة',
			self::PreOrder->value          => 'طلب مسبق',
			self::AwaitingPayment->value   => 'بانتظار الدفع',
			self::AwaitingFulfillment->value => 'بانتظار الإعداد',
			self::AwaitingShipment->value  => 'بانتظار الشحن',
			self::AwaitingPickup->value    => 'بانتظار الاستلام',
			self::Undefined->value         => 'غير محدد',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Draft->value             => 'Kladde',
			self::Pending->value           => 'Afventer',
			self::Confirmed->value         => 'Bekræftet',
			self::Processing->value        => 'Behandler',
			self::Unpaid->value            => 'Ikke Betalt',
			self::PartiallyPaid->value     => 'Delvist Betalt',
			self::Paid->value              => 'Betalt',
			self::Shipped->value           => 'Afsendt',
			self::PartiallyShipped->value  => 'Delvist Afsendt',
			self::Delivered->value         => 'Leveret',
			self::Completed->value         => 'Afsluttet',
			self::OnHold->value            => 'På Hold',
			self::Cancelled->value         => 'Annulleret',
			self::Refunded->value          => 'Refunderet',
			self::PartiallyRefunded->value => 'Delvist Refunderet',
			self::Returned->value          => 'Returneret',
			self::Failed->value            => 'Fejlet',
			self::Backordered->value       => 'Tilbagebestilt',
			self::PreOrder->value          => 'Forhåndsbestilling',
			self::AwaitingPayment->value   => 'Afventer Betaling',
			self::AwaitingFulfillment->value => 'Afventer Opfyldelse',
			self::AwaitingShipment->value  => 'Afventer Forsendelse',
			self::AwaitingPickup->value    => 'Afventer Afhentning',
			self::Undefined->value         => 'Udefineret',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Draft->value             => 'Entwurf',
			self::Pending->value           => 'Ausstehend',
			self::Confirmed->value         => 'Bestätigt',
			self::Processing->value        => 'In Bearbeitung',
			self::Unpaid->value            => 'Nicht Bezahlt',
			self::PartiallyPaid->value     => 'Teilweise Bezahlt',
			self::Paid->value              => 'Bezahlt',
			self::Shipped->value           => 'Versandt',
			self::PartiallyShipped->value  => 'Teilweise Versandt',
			self::Delivered->value         => 'Geliefert',
			self::Completed->value         => 'Abgeschlossen',
			self::OnHold->value            => 'Zurückgestellt',
			self::Cancelled->value         => 'Storniert',
			self::Refunded->value          => 'Erstattet',
			self::PartiallyRefunded->value => 'Teilweise Erstattet',
			self::Returned->value          => 'Zurückgegeben',
			self::Failed->value            => 'Fehlgeschlagen',
			self::Backordered->value       => 'Nachbestellt',
			self::PreOrder->value          => 'Vorbestellung',
			self::AwaitingPayment->value   => 'Zahlung Ausstehend',
			self::AwaitingFulfillment->value => 'Erfüllung Ausstehend',
			self::AwaitingShipment->value  => 'Versand Ausstehend',
			self::AwaitingPickup->value    => 'Abholung Ausstehend',
			self::Undefined->value         => 'Undefiniert',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Draft->value             => 'Brouillon',
			self::Pending->value           => 'En Attente',
			self::Confirmed->value         => 'Confirmé',
			self::Processing->value        => 'En Traitement',
			self::Unpaid->value            => 'Non Payé',
			self::PartiallyPaid->value     => 'Partiellement Payé',
			self::Paid->value              => 'Payé',
			self::Shipped->value           => 'Expédié',
			self::PartiallyShipped->value  => 'Partiellement Expédié',
			self::Delivered->value         => 'Livré',
			self::Completed->value         => 'Terminé',
			self::OnHold->value            => 'En Attente',
			self::Cancelled->value         => 'Annulé',
			self::Refunded->value          => 'Remboursé',
			self::PartiallyRefunded->value => 'Partiellement Remboursé',
			self::Returned->value          => 'Retourné',
			self::Failed->value            => 'Échoué',
			self::Backordered->value       => 'En Rupture',
			self::PreOrder->value          => 'Précommande',
			self::AwaitingPayment->value   => 'En Attente de Paiement',
			self::AwaitingFulfillment->value => 'En Attente de Préparation',
			self::AwaitingShipment->value  => 'En Attente d\'Expédition',
			self::AwaitingPickup->value    => 'En Attente de Retrait',
			self::Undefined->value         => 'Indéfini',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Draft->value             => 'טיוטה',
			self::Pending->value           => 'ממתין',
			self::Confirmed->value         => 'מאושר',
			self::Processing->value        => 'בעיבוד',
			self::Unpaid->value            => 'לא שולם',
			self::PartiallyPaid->value     => 'שולם חלקית',
			self::Paid->value              => 'שולם',
			self::Shipped->value           => 'נשלח',
			self::PartiallyShipped->value  => 'נשלח חלקית',
			self::Delivered->value         => 'נמסר',
			self::Completed->value         => 'הושלם',
			self::OnHold->value            => 'בהמתנה',
			self::Cancelled->value         => 'בוטל',
			self::Refunded->value          => 'הוחזר',
			self::PartiallyRefunded->value => 'הוחזר חלקית',
			self::Returned->value          => 'הוחזר',
			self::Failed->value            => 'נכשל',
			self::Backordered->value       => 'בהזמנה חוזרת',
			self::PreOrder->value          => 'הזמנה מוקדמת',
			self::AwaitingPayment->value   => 'ממתין לתשלום',
			self::AwaitingFulfillment->value => 'ממתין למילוי',
			self::AwaitingShipment->value  => 'ממתין למשלוח',
			self::AwaitingPickup->value    => 'ממתין לאיסוף',
			self::Undefined->value         => 'לא מוגדר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Draft->value             => 'Bozza',
			self::Pending->value           => 'In Sospeso',
			self::Confirmed->value         => 'Confermato',
			self::Processing->value        => 'In Elaborazione',
			self::Unpaid->value            => 'Non Pagato',
			self::PartiallyPaid->value     => 'Parzialmente Pagato',
			self::Paid->value              => 'Pagato',
			self::Shipped->value           => 'Spedito',
			self::PartiallyShipped->value  => 'Parzialmente Spedito',
			self::Delivered->value         => 'Consegnato',
			self::Completed->value         => 'Completato',
			self::OnHold->value            => 'In Attesa',
			self::Cancelled->value         => 'Annullato',
			self::Refunded->value          => 'Rimborsato',
			self::PartiallyRefunded->value => 'Parzialmente Rimborsato',
			self::Returned->value          => 'Restituito',
			self::Failed->value            => 'Fallito',
			self::Backordered->value       => 'In Backorder',
			self::PreOrder->value          => 'Preordine',
			self::AwaitingPayment->value   => 'In Attesa di Pagamento',
			self::AwaitingFulfillment->value => 'In Attesa di Evasione',
			self::AwaitingShipment->value  => 'In Attesa di Spedizione',
			self::AwaitingPickup->value    => 'In Attesa di Ritiro',
			self::Undefined->value         => 'Indefinito',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Draft->value             => '下書き',
			self::Pending->value           => '保留中',
			self::Confirmed->value         => '確認済み',
			self::Processing->value        => '処理中',
			self::Unpaid->value            => '未払い',
			self::PartiallyPaid->value     => '一部支払済み',
			self::Paid->value              => '支払済み',
			self::Shipped->value           => '発送済み',
			self::PartiallyShipped->value  => '一部発送済み',
			self::Delivered->value         => '配送済み',
			self::Completed->value         => '完了',
			self::OnHold->value            => '保留中',
			self::Cancelled->value         => 'キャンセル',
			self::Refunded->value          => '返金済み',
			self::PartiallyRefunded->value => '一部返金済み',
			self::Returned->value          => '返品済み',
			self::Failed->value            => '失敗',
			self::Backordered->value       => 'バックオーダー',
			self::PreOrder->value          => '予約注文',
			self::AwaitingPayment->value   => '支払い待ち',
			self::AwaitingFulfillment->value => '発送準備中',
			self::AwaitingShipment->value  => '発送待ち',
			self::AwaitingPickup->value    => '受け取り待ち',
			self::Undefined->value         => '未定義',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Draft->value             => 'Concept',
			self::Pending->value           => 'In Afwachting',
			self::Confirmed->value         => 'Bevestigd',
			self::Processing->value        => 'Verwerken',
			self::Unpaid->value            => 'Niet Betaald',
			self::PartiallyPaid->value     => 'Gedeeltelijk Betaald',
			self::Paid->value              => 'Betaald',
			self::Shipped->value           => 'Verzonden',
			self::PartiallyShipped->value  => 'Gedeeltelijk Verzonden',
			self::Delivered->value         => 'Geleverd',
			self::Completed->value         => 'Voltooid',
			self::OnHold->value            => 'In de Wacht',
			self::Cancelled->value         => 'Geannuleerd',
			self::Refunded->value          => 'Terugbetaald',
			self::PartiallyRefunded->value => 'Gedeeltelijk Terugbetaald',
			self::Returned->value          => 'Geretourneerd',
			self::Failed->value            => 'Mislukt',
			self::Backordered->value       => 'Backorder',
			self::PreOrder->value          => 'Pre-order',
			self::AwaitingPayment->value   => 'Wacht op Betaling',
			self::AwaitingFulfillment->value => 'Wacht op Verwerking',
			self::AwaitingShipment->value  => 'Wacht op Verzending',
			self::AwaitingPickup->value    => 'Wacht op Ophalen',
			self::Undefined->value         => 'Ongedefinieerd',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Draft->value             => 'Szkic',
			self::Pending->value           => 'Oczekujący',
			self::Confirmed->value         => 'Potwierdzony',
			self::Processing->value        => 'Przetwarzanie',
			self::Unpaid->value            => 'Nie Zapłacony',
			self::PartiallyPaid->value     => 'Częściowo Zapłacony',
			self::Paid->value              => 'Zapłacony',
			self::Shipped->value           => 'Wysłany',
			self::PartiallyShipped->value  => 'Częściowo Wysłany',
			self::Delivered->value         => 'Dostarczony',
			self::Completed->value         => 'Zakończony',
			self::OnHold->value            => 'Wstrzymany',
			self::Cancelled->value         => 'Anulowany',
			self::Refunded->value          => 'Zwrócony',
			self::PartiallyRefunded->value => 'Częściowo Zwrócony',
			self::Returned->value          => 'Zwrócony',
			self::Failed->value            => 'Niepowodzenie',
			self::Backordered->value       => 'Zamówienie z Oczekiwaniem',
			self::PreOrder->value          => 'Przedpremierowe Zamówienie',
			self::AwaitingPayment->value   => 'Oczekuje na Płatność',
			self::AwaitingFulfillment->value => 'Oczekuje na Realizację',
			self::AwaitingShipment->value  => 'Oczekuje na Wysyłkę',
			self::AwaitingPickup->value    => 'Oczekuje na Odbiór',
			self::Undefined->value         => 'Niezdefiniowany',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Draft->value             => 'Черновик',
			self::Pending->value           => 'В Ожидании',
			self::Confirmed->value         => 'Подтвержден',
			self::Processing->value        => 'Обработка',
			self::Unpaid->value            => 'Не Оплачен',
			self::PartiallyPaid->value     => 'Частично Оплачен',
			self::Paid->value              => 'Оплачен',
			self::Shipped->value           => 'Отправлен',
			self::PartiallyShipped->value  => 'Частично Отправлен',
			self::Delivered->value         => 'Доставлен',
			self::Completed->value         => 'Завершен',
			self::OnHold->value            => 'Приостановлен',
			self::Cancelled->value         => 'Отменен',
			self::Refunded->value          => 'Возвращен',
			self::PartiallyRefunded->value => 'Частично Возвращен',
			self::Returned->value          => 'Возвращен',
			self::Failed->value            => 'Не Удался',
			self::Backordered->value       => 'В Резервном Заказе',
			self::PreOrder->value          => 'Предзаказ',
			self::AwaitingPayment->value   => 'Ожидает Оплаты',
			self::AwaitingFulfillment->value => 'Ожидает Исполнения',
			self::AwaitingShipment->value  => 'Ожидает Отправки',
			self::AwaitingPickup->value    => 'Ожидает Получения',
			self::Undefined->value         => 'Неопределен',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Draft->value             => 'Taslak',
			self::Pending->value           => 'Beklemede',
			self::Confirmed->value         => 'Onaylandı',
			self::Processing->value        => 'İşleniyor',
			self::Unpaid->value            => 'Ödenmemiş',
			self::PartiallyPaid->value     => 'Kısmen Ödenmiş',
			self::Paid->value              => 'Ödenmiş',
			self::Shipped->value           => 'Gönderildi',
			self::PartiallyShipped->value  => 'Kısmen Gönderildi',
			self::Delivered->value         => 'Teslim Edildi',
			self::Completed->value         => 'Tamamlandı',
			self::OnHold->value            => 'Beklemede',
			self::Cancelled->value         => 'İptal Edildi',
			self::Refunded->value          => 'İade Edildi',
			self::PartiallyRefunded->value => 'Kısmen İade Edildi',
			self::Returned->value          => 'İade Edildi',
			self::Failed->value            => 'Başarısız',
			self::Backordered->value       => 'Geri Sipariş',
			self::PreOrder->value          => 'Ön Sipariş',
			self::AwaitingPayment->value   => 'Ödeme Bekleniyor',
			self::AwaitingFulfillment->value => 'Hazırlanıyor',
			self::AwaitingShipment->value  => 'Gönderim Bekleniyor',
			self::AwaitingPickup->value    => 'Teslim Alınmayı Bekliyor',
			self::Undefined->value         => 'Tanımsız',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Draft->value             => '草稿',
			self::Pending->value           => '待处理',
			self::Confirmed->value         => '已确认',
			self::Processing->value        => '处理中',
			self::Unpaid->value            => '未支付',
			self::PartiallyPaid->value     => '部分支付',
			self::Paid->value              => '已支付',
			self::Shipped->value           => '已发货',
			self::PartiallyShipped->value  => '部分发货',
			self::Delivered->value         => '已送达',
			self::Completed->value         => '已完成',
			self::OnHold->value            => '暂停中',
			self::Cancelled->value         => '已取消',
			self::Refunded->value          => '已退款',
			self::PartiallyRefunded->value => '部分退款',
			self::Returned->value          => '已退货',
			self::Failed->value            => '失败',
			self::Backordered->value       => '缺货待补',
			self::PreOrder->value          => '预售订单',
			self::AwaitingPayment->value   => '等待支付',
			self::AwaitingFulfillment->value => '等待备货',
			self::AwaitingShipment->value  => '等待发货',
			self::AwaitingPickup->value    => '等待取货',
			self::Undefined->value         => '未定义',
		];
	}
}
