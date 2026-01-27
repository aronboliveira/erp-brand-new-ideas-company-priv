<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum BillReferenceType: string
{
	// Core Bill/Payment Types
	case Bill = 'bill';
	case Payment = 'payment';
	case BillCategory = 'bill_category';

		// Invoice/Statement Types
	case Invoice = 'invoice';
	case ProformaInvoice = 'proforma_invoice';
	case RecurringInvoice = 'recurring_invoice';
	case CreditInvoice = 'credit_invoice';
	case DebitInvoice = 'debit_invoice';
	case Statement = 'statement';
	case Estimate = 'estimate';
	case Quote = 'quote';
	case Proposal = 'proposal';

		// Payment-Related Types
	case AdvancePayment = 'advance_payment';
	case PartialPayment = 'partial_payment';
	case FullPayment = 'full_payment';
	case Installment = 'installment';
	case DownPayment = 'down_payment';
	case FinalPayment = 'final_payment';
	case CreditCardPayment = 'credit_card_payment';
	case BankTransfer = 'bank_transfer';
	case CashPayment = 'cash_payment';
	case DigitalPayment = 'digital_payment';

		// Adjustment Types
	case CreditNote = 'credit_note';
	case DebitNote = 'debit_note';
	case Adjustment = 'adjustment';
	case CreditAdjustment = 'credit_adjustment';
	case DebitAdjustment = 'debit_adjustment';
	case WriteOff = 'write_off';
	case WriteOn = 'write_on';

		// Discount/Tax Types
	case Discount = 'discount';
	case EarlyPaymentDiscount = 'early_payment_discount';
	case VolumeDiscount = 'volume_discount';
	case PromotionalDiscount = 'promotional_discount';
	case Tax = 'tax';
	case SalesTax = 'sales_tax';
	case VAT = 'vat';
	case GST = 'gst';
	case ServiceTax = 'service_tax';
	case WithholdingTax = 'withholding_tax';

		// Fee Types
	case Fee = 'fee';
	case ServiceFee = 'service_fee';
	case ProcessingFee = 'processing_fee';
	case TransactionFee = 'transaction_fee';
	case LateFee = 'late_fee';
	case PenaltyFee = 'penalty_fee';
	case AdministrativeFee = 'administrative_fee';
	case ConvenienceFee = 'convenience_fee';

		// Subscription Types
	case Subscription = 'subscription';
	case RecurringBilling = 'recurring_billing';
	case MembershipFee = 'membership_fee';
	case LicenseFee = 'license_fee';
	case MaintenanceFee = 'maintenance_fee';
	case SubscriptionRenewal = 'subscription_renewal';
	case SubscriptionUpgrade = 'subscription_upgrade';
	case SubscriptionDowngrade = 'subscription_downgrade';

		// Financial Document Types
	case Receipt = 'receipt';
	case Voucher = 'voucher';
	case CreditVoucher = 'credit_voucher';
	case DebitVoucher = 'debit_voucher';
	case PaymentVoucher = 'payment_voucher';
	case ReceiptVoucher = 'receipt_voucher';
	case ContraVoucher = 'contra_voucher';

		// Contract/Agreement Types
	case Contract = 'contract';
	case Agreement = 'agreement';
	case PurchaseOrder = 'purchase_order';
	case SalesOrder = 'sales_order';
	case WorkOrder = 'work_order';
	case ServiceOrder = 'service_order';

		// Delivery/Shipping Types
	case DeliveryNote = 'delivery_note';
	case PackingSlip = 'packing_slip';
	case ShippingDocument = 'shipping_document';
	case BillOfLading = 'bill_of_lading';
	case Waybill = 'waybill';

		// Miscellaneous Types
	case Refund = 'refund';
	case Chargeback = 'chargeback';
	case Reimbursement = 'reimbursement';
	case Commission = 'commission';
	case Royalty = 'royalty';
	case Dividend = 'dividend';
	case Interest = 'interest';
	case Depreciation = 'depreciation';
	case Amortization = 'amortization';

		// Other
	case Other = 'other';
	case Miscellaneous = 'miscellaneous';
	case Unclassified = 'unclassified';

	/**
	 * Normalize input to BillReferenceType
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
			// Core types
			'bill', 'invoice', 'charge', 'debit' => self::Bill,
			'payment', 'pay', 'settlement', 'clearing' => self::Payment,
			'billcategory', 'category', 'type', 'classification' => self::BillCategory,

			// Invoice/Statement
			'invoice', 'billingdocument', 'billinvoice' => self::Invoice,
			'proformainvoice', 'proforma', 'preinvoice' => self::ProformaInvoice,
			'recurringinvoice', 'recurringbill', 'repeatinginvoice' => self::RecurringInvoice,
			'creditinvoice', 'creditmemo', 'creditbill' => self::CreditInvoice,
			'debitinvoice', 'debitmemo', 'debitbill' => self::DebitInvoice,
			'statement', 'accountstatement', 'billingstatement' => self::Statement,
			'estimate', 'estimation', 'costestimate' => self::Estimate,
			'quote', 'quotation', 'pricequote' => self::Quote,
			'proposal', 'bid', 'tender' => self::Proposal,

			// Payment types
			'advancepayment', 'advance', 'prepayment', 'deposit' => self::AdvancePayment,
			'partialpayment', 'partpayment', 'installmentpayment' => self::PartialPayment,
			'fullpayment', 'completepayment', 'fullsettlement' => self::FullPayment,
			'installment', 'installmentplan', 'paymentplan' => self::Installment,
			'downpayment', 'down', 'initialpayment' => self::DownPayment,
			'finalpayment', 'final', 'balancepayment' => self::FinalPayment,
			'creditcardpayment', 'ccpayment', 'cardpayment' => self::CreditCardPayment,
			'banktransfer', 'wiretransfer', 'bankpayment' => self::BankTransfer,
			'cashpayment', 'cash', 'cashsettlement' => self::CashPayment,
			'digitalpayment', 'electronicpayment', 'onlinepayment' => self::DigitalPayment,

			// Adjustments
			'creditnote', 'credit', 'creditmemo' => self::CreditNote,
			'debitnote', 'debit', 'debitmemo' => self::DebitNote,
			'adjustment', 'adjust', 'correction' => self::Adjustment,
			'creditadjustment', 'creditcorrection' => self::CreditAdjustment,
			'debitadjustment', 'debitcorrection' => self::DebitAdjustment,
			'writeoff', 'writeoff', 'baddebt' => self::WriteOff,
			'writeon', 'writeon', 'reversalwriteoff' => self::WriteOn,

			// Discount/Tax
			'discount', 'reduction', 'deduction' => self::Discount,
			'earlypaymentdiscount', 'earlydiscount', 'promptpayment' => self::EarlyPaymentDiscount,
			'volumediscount', 'bulkdiscount', 'quantitydiscount' => self::VolumeDiscount,
			'promotionaldiscount', 'promodiscount', 'specialdiscount' => self::PromotionalDiscount,
			'tax', 'taxation', 'levy' => self::Tax,
			'salestax', 'salestaxation' => self::SalesTax,
			'vat', 'valuaddedtax', 'vatcharge' => self::VAT,
			'gst', 'goodsandservicestax' => self::GST,
			'servicetax', 'servicetaxation' => self::ServiceTax,
			'withholdingtax', 'withholding', 'taxdeducted' => self::WithholdingTax,

			// Fees
			'fee', 'chargefee', 'cost' => self::Fee,
			'servicefee', 'servicecharge' => self::ServiceFee,
			'processingfee', 'processfee', 'handlingfee' => self::ProcessingFee,
			'transactionfee', 'txnfee', 'bankfee' => self::TransactionFee,
			'latefee', 'latecharge', 'delayedpayment' => self::LateFee,
			'penaltyfee', 'penalty', 'fine' => self::PenaltyFee,
			'administrativefee', 'adminfee', 'admincharge' => self::AdministrativeFee,
			'conveniencefee', 'conveniencecharge' => self::ConvenienceFee,

			// Subscriptions
			'subscription', 'sub', 'recurringcharge' => self::Subscription,
			'recurringbilling', 'recurring', 'autobilling' => self::RecurringBilling,
			'membershipfee', 'membership', 'membershipcharge' => self::MembershipFee,
			'licensefee', 'license', 'licensingfee' => self::LicenseFee,
			'maintenancefee', 'maintenance', 'supportfee' => self::MaintenanceFee,
			'subscriptionrenewal', 'renewal', 'renewalfee' => self::SubscriptionRenewal,
			'subscriptionupgrade', 'upgrade', 'upgradefee' => self::SubscriptionUpgrade,
			'subscriptiondowngrade', 'downgrade', 'downgradefee' => self::SubscriptionDowngrade,

			// Financial Documents
			'receipt', 'paymentreceipt', 'acknowledgement' => self::Receipt,
			'voucher', 'paymentvoucher' => self::Voucher,
			'creditvoucher', 'creditcoupon' => self::CreditVoucher,
			'debitvoucher', 'debitcoupon' => self::DebitVoucher,
			'paymentvoucher', 'payvoucher' => self::PaymentVoucher,
			'receiptvoucher', 'receivingvoucher' => self::ReceiptVoucher,
			'contravoucher', 'contraentry' => self::ContraVoucher,

			// Contracts/Agreements
			'contract', 'agreementcontract' => self::Contract,
			'agreement', 'mutualagreement' => self::Agreement,
			'purchaseorder', 'po', 'buyorder' => self::PurchaseOrder,
			'salesorder', 'so', 'sellorder' => self::SalesOrder,
			'workorder', 'wo', 'joborder' => self::WorkOrder,
			'serviceorder', 'serviceagreement' => self::ServiceOrder,

			// Delivery/Shipping
			'deliverynote', 'deliveryreceipt' => self::DeliveryNote,
			'packingslip', 'packinglist' => self::PackingSlip,
			'shippingdocument', 'shippingnote' => self::ShippingDocument,
			'billoflading', 'bl', 'shippingbill' => self::BillOfLading,
			'waybill', 'consignmentnote' => self::Waybill,

			// Miscellaneous
			'refund', 'refundpayment', 'moneyback' => self::Refund,
			'chargeback', 'chargebackdispute' => self::Chargeback,
			'reimbursement', 'reimburse', 'expensereimbursement' => self::Reimbursement,
			'commission', 'brokerage', 'agentfee' => self::Commission,
			'royalty', 'royaltypayment', 'licensingroyalty' => self::Royalty,
			'dividend', 'dividendpayment', 'shareprofit' => self::Dividend,
			'interest', 'interestpayment', 'loaninterest' => self::Interest,
			'depreciation', 'depreciationcharge' => self::Depreciation,
			'amortization', 'amortizationcharge' => self::Amortization,

			// Other
			'other', 'misc', 'general' => self::Other,
			'miscellaneous', 'various', 'assorted' => self::Miscellaneous,
			'unclassified', 'uncategorized', 'notclassified' => self::Unclassified,

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
	 * Get label for this bill reference type in specified language
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
			// Core Bills - blue
			self::Bill, self::Invoice, self::ProformaInvoice,
			self::RecurringInvoice, self::Statement,
			self::Estimate, self::Quote, self::Proposal => '#3b82f6',

			// Payments - green
			self::Payment, self::AdvancePayment, self::PartialPayment,
			self::FullPayment, self::Installment, self::DownPayment,
			self::FinalPayment, self::CreditCardPayment, self::BankTransfer,
			self::CashPayment, self::DigitalPayment => '#10b981',

			// Adjustments/Notes - purple
			self::CreditNote, self::DebitNote, self::Adjustment,
			self::CreditAdjustment, self::DebitAdjustment,
			self::WriteOff, self::WriteOn => '#8b5cf6',

			// Discounts/Taxes - amber
			self::Discount, self::EarlyPaymentDiscount, self::VolumeDiscount,
			self::PromotionalDiscount => '#f59e0b',
			self::Tax, self::SalesTax, self::VAT, self::GST,
			self::ServiceTax, self::WithholdingTax => '#d97706',

			// Fees - red
			self::Fee, self::ServiceFee, self::ProcessingFee,
			self::TransactionFee, self::LateFee, self::PenaltyFee,
			self::AdministrativeFee, self::ConvenienceFee => '#ef4444',

			// Subscriptions - indigo
			self::Subscription, self::RecurringBilling, self::MembershipFee,
			self::LicenseFee, self::MaintenanceFee, self::SubscriptionRenewal,
			self::SubscriptionUpgrade, self::SubscriptionDowngrade => '#6366f1',

			// Financial Documents - teal
			self::Receipt, self::Voucher, self::CreditVoucher,
			self::DebitVoucher, self::PaymentVoucher,
			self::ReceiptVoucher, self::ContraVoucher => '#14b8a6',

			// Contracts - dark blue
			self::Contract, self::Agreement, self::PurchaseOrder,
			self::SalesOrder, self::WorkOrder, self::ServiceOrder => '#1e40af',

			// Delivery/Shipping - orange
			self::DeliveryNote, self::PackingSlip, self::ShippingDocument,
			self::BillOfLading, self::Waybill => '#f97316',

			// Miscellaneous - pink
			self::Refund, self::Chargeback, self::Reimbursement,
			self::Commission, self::Royalty, self::Dividend,
			self::Interest, self::Depreciation, self::Amortization => '#ec4899',

			// Other - gray
			self::Other, self::Miscellaneous, self::Unclassified,
			self::BillCategory => '#6b7280',

			// Credit/Debit Invoices - special colors
			self::CreditInvoice => '#10b981', // Green for credit
			self::DebitInvoice => '#ef4444',  // Red for debit

			default => '#9ca3af',
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			// Core Bills
			self::Bill, self::Invoice => 'file-invoice-dollar',
			self::Payment => 'credit-card',
			self::BillCategory => 'folder',

			// Invoice/Statement
			self::ProformaInvoice => 'file-invoice',
			self::RecurringInvoice => 'redo-alt',
			self::CreditInvoice => 'arrow-circle-up',
			self::DebitInvoice => 'arrow-circle-down',
			self::Statement => 'file-alt',
			self::Estimate => 'calculator',
			self::Quote => 'comments-dollar',
			self::Proposal => 'handshake',

			// Payment types
			self::AdvancePayment => 'arrow-circle-right',
			self::PartialPayment => 'percentage',
			self::FullPayment => 'check-circle',
			self::Installment => 'calendar-check',
			self::DownPayment => 'hand-holding-usd',
			self::FinalPayment => 'flag-checkered',
			self::CreditCardPayment => 'credit-card',
			self::BankTransfer => 'university',
			self::CashPayment => 'money-bill-wave',
			self::DigitalPayment => 'mobile-alt',

			// Adjustments
			self::CreditNote => 'plus-circle',
			self::DebitNote => 'minus-circle',
			self::Adjustment => 'exchange-alt',
			self::CreditAdjustment => 'arrow-up',
			self::DebitAdjustment => 'arrow-down',
			self::WriteOff => 'times-circle',
			self::WriteOn => 'check-circle',

			// Discount/Tax
			self::Discount => 'tags',
			self::EarlyPaymentDiscount => 'clock',
			self::VolumeDiscount => 'boxes',
			self::PromotionalDiscount => 'gift',
			self::Tax, self::SalesTax, self::VAT, self::GST => 'receipt',
			self::ServiceTax => 'concierge-bell',
			self::WithholdingTax => 'hand-holding-usd',

			// Fees
			self::Fee => 'file-invoice',
			self::ServiceFee => 'concierge-bell',
			self::ProcessingFee => 'cogs',
			self::TransactionFee => 'exchange-alt',
			self::LateFee => 'clock',
			self::PenaltyFee => 'exclamation-circle',
			self::AdministrativeFee => 'user-tie',
			self::ConvenienceFee => 'thumbs-up',

			// Subscriptions
			self::Subscription => 'sync-alt',
			self::RecurringBilling => 'calendar-check',
			self::MembershipFee => 'id-card',
			self::LicenseFee => 'certificate',
			self::MaintenanceFee => 'tools',
			self::SubscriptionRenewal => 'redo',
			self::SubscriptionUpgrade => 'arrow-up',
			self::SubscriptionDowngrade => 'arrow-down',

			// Financial Documents
			self::Receipt => 'receipt',
			self::Voucher => 'ticket-alt',
			self::CreditVoucher => 'ticket-alt',
			self::DebitVoucher => 'ticket-alt',
			self::PaymentVoucher => 'money-check',
			self::ReceiptVoucher => 'file-invoice',
			self::ContraVoucher => 'exchange-alt',

			// Contracts
			self::Contract => 'file-contract',
			self::Agreement => 'handshake',
			self::PurchaseOrder => 'shopping-cart',
			self::SalesOrder => 'chart-line',
			self::WorkOrder => 'tools',
			self::ServiceOrder => 'concierge-bell',

			// Delivery/Shipping
			self::DeliveryNote => 'truck',
			self::PackingSlip => 'box',
			self::ShippingDocument => 'clipboard-list',
			self::BillOfLading => 'ship',
			self::Waybill => 'plane',

			// Miscellaneous
			self::Refund => 'undo',
			self::Chargeback => 'ban',
			self::Reimbursement => 'reply',
			self::Commission => 'percentage',
			self::Royalty => 'crown',
			self::Dividend => 'chart-pie',
			self::Interest => 'percentage',
			self::Depreciation => 'chart-line-down',
			self::Amortization => 'calculator',

			// Other
			self::Other, self::Miscellaneous => 'ellipsis-h',
			self::Unclassified => 'question-circle',
		};
	}

	/**
	 * Check if this is a bill/invoice type
	 */
	public function isBill(): bool
	{
		return in_array($this, [
			self::Bill,
			self::Invoice,
			self::ProformaInvoice,
			self::RecurringInvoice,
			self::CreditInvoice,
			self::DebitInvoice,
			self::Statement,
			self::Estimate,
			self::Quote,
			self::Proposal,
		]);
	}

	/**
	 * Check if this is a payment type
	 */
	public function isPayment(): bool
	{
		return in_array($this, [
			self::Payment,
			self::AdvancePayment,
			self::PartialPayment,
			self::FullPayment,
			self::Installment,
			self::DownPayment,
			self::FinalPayment,
			self::CreditCardPayment,
			self::BankTransfer,
			self::CashPayment,
			self::DigitalPayment,
		]);
	}

	/**
	 * Check if this is an adjustment type
	 */
	public function isAdjustment(): bool
	{
		return in_array($this, [
			self::CreditNote,
			self::DebitNote,
			self::Adjustment,
			self::CreditAdjustment,
			self::DebitAdjustment,
			self::WriteOff,
			self::WriteOn,
		]);
	}

	/**
	 * Check if this is a fee type
	 */
	public function isFee(): bool
	{
		return in_array($this, [
			self::Fee,
			self::ServiceFee,
			self::ProcessingFee,
			self::TransactionFee,
			self::LateFee,
			self::PenaltyFee,
			self::AdministrativeFee,
			self::ConvenienceFee,
		]);
	}

	/**
	 * Check if this is a tax type
	 */
	public function isTax(): bool
	{
		return in_array($this, [
			self::Tax,
			self::SalesTax,
			self::VAT,
			self::GST,
			self::ServiceTax,
			self::WithholdingTax,
		]);
	}

	/**
	 * Check if this is a discount type
	 */
	public function isDiscount(): bool
	{
		return in_array($this, [
			self::Discount,
			self::EarlyPaymentDiscount,
			self::VolumeDiscount,
			self::PromotionalDiscount,
		]);
	}

	/**
	 * Check if this is a subscription type
	 */
	public function isSubscription(): bool
	{
		return in_array($this, [
			self::Subscription,
			self::RecurringBilling,
			self::MembershipFee,
			self::LicenseFee,
			self::MaintenanceFee,
			self::SubscriptionRenewal,
			self::SubscriptionUpgrade,
			self::SubscriptionDowngrade,
		]);
	}

	/**
	 * Check if this increases the balance (debit/charge)
	 */
	public function increasesBalance(): bool
	{
		return in_array($this, [
			self::Bill,
			self::Invoice,
			self::DebitInvoice,
			self::DebitNote,
			self::DebitAdjustment,
			self::Tax,
			self::Fee,
			self::PenaltyFee,
			self::LateFee,
		]);
	}

	/**
	 * Check if this decreases the balance (credit/payment)
	 */
	public function decreasesBalance(): bool
	{
		return in_array($this, [
			self::Payment,
			self::CreditInvoice,
			self::CreditNote,
			self::CreditAdjustment,
			self::Discount,
			self::Refund,
			self::WriteOff,
		]);
	}

	/**
	 * Check if this is a recurring type
	 */
	public function isRecurring(): bool
	{
		return in_array($this, [
			self::RecurringInvoice,
			self::RecurringBilling,
			self::Subscription,
			self::MembershipFee,
			self::LicenseFee,
			self::MaintenanceFee,
		]);
	}

	/**
	 * Get bill reference category
	 */
	public function getCategory(): string
	{
		return match ($this) {
			// Bills/Invoices
			self::Bill, self::Invoice, self::ProformaInvoice,
			self::RecurringInvoice, self::CreditInvoice,
			self::DebitInvoice, self::Statement,
			self::Estimate, self::Quote, self::Proposal => 'billing',

			// Payments
			self::Payment, self::AdvancePayment, self::PartialPayment,
			self::FullPayment, self::Installment, self::DownPayment,
			self::FinalPayment, self::CreditCardPayment, self::BankTransfer,
			self::CashPayment, self::DigitalPayment => 'payment',

			// Adjustments
			self::CreditNote, self::DebitNote, self::Adjustment,
			self::CreditAdjustment, self::DebitAdjustment,
			self::WriteOff, self::WriteOn => 'adjustment',

			// Discounts
			self::Discount, self::EarlyPaymentDiscount, self::VolumeDiscount,
			self::PromotionalDiscount => 'discount',

			// Taxes
			self::Tax, self::SalesTax, self::VAT, self::GST,
			self::ServiceTax, self::WithholdingTax => 'tax',

			// Fees
			self::Fee, self::ServiceFee, self::ProcessingFee,
			self::TransactionFee, self::LateFee, self::PenaltyFee,
			self::AdministrativeFee, self::ConvenienceFee => 'fee',

			// Subscriptions
			self::Subscription, self::RecurringBilling, self::MembershipFee,
			self::LicenseFee, self::MaintenanceFee, self::SubscriptionRenewal,
			self::SubscriptionUpgrade, self::SubscriptionDowngrade => 'subscription',

			// Financial Documents
			self::Receipt, self::Voucher, self::CreditVoucher,
			self::DebitVoucher, self::PaymentVoucher,
			self::ReceiptVoucher, self::ContraVoucher => 'document',

			// Contracts
			self::Contract, self::Agreement, self::PurchaseOrder,
			self::SalesOrder, self::WorkOrder, self::ServiceOrder => 'contract',

			// Delivery/Shipping
			self::DeliveryNote, self::PackingSlip, self::ShippingDocument,
			self::BillOfLading, self::Waybill => 'delivery',

			// Miscellaneous
			self::Refund, self::Chargeback, self::Reimbursement,
			self::Commission, self::Royalty, self::Dividend,
			self::Interest, self::Depreciation, self::Amortization => 'financial',

			// Other
			self::Other, self::Miscellaneous, self::Unclassified => 'other',

			// Category
			self::BillCategory => 'category',
		};
	}

	/**
	 * Get typical payment terms in days
	 */
	public function getPaymentTerms(): int
	{
		return match ($this) {
			// Standard commercial terms
			self::Invoice, self::Bill => 30,
			self::ProformaInvoice => 0, // Usually requires payment before delivery
			self::RecurringInvoice => 15, // Often shorter terms for subscriptions
			self::Estimate, self::Quote => 0, // Not payable, just estimates

			// Contract terms
			self::Contract, self::Agreement => 30,
			self::PurchaseOrder => 30,
			self::SalesOrder => 30,

			// Immediate payments
			self::AdvancePayment, self::DownPayment => 0,
			self::FinalPayment => 7, // Usually due upon completion

			// Subscription terms
			self::Subscription, self::RecurringBilling => 15,
			self::MembershipFee => 30,
			self::LicenseFee => 30,

			// Default
			default => 30,
		};
	}

	/**
	 * Check if this requires tax calculation
	 */
	public function requiresTax(): bool
	{
		return in_array($this, [
			self::Bill,
			self::Invoice,
			self::RecurringInvoice,
			self::ServiceFee,
			self::MembershipFee,
			self::LicenseFee,
			self::SalesOrder,
			self::Contract,
			self::Agreement,
		]);
	}

	/**
	 * Get description of the bill reference type
	 */
	public function getDescription(): string
	{
		return match ($this) {
			// Core types
			self::Bill => 'A formal request for payment for goods or services',
			self::Payment => 'Transfer of money to settle an obligation',
			self::BillCategory => 'Classification group for organizing bills',

			// Invoice/Statement
			self::Invoice => 'Itemized bill for goods or services provided',
			self::ProformaInvoice => 'Preliminary bill of sale sent to buyer in advance',
			self::RecurringInvoice => 'Regularly scheduled invoice for ongoing services',
			self::CreditInvoice => 'Invoice that reduces the amount owed (credit)',
			self::DebitInvoice => 'Invoice that increases the amount owed (debit)',
			self::Statement => 'Summary of all transactions within a specific period',
			self::Estimate => 'Approximate cost calculation before work begins',
			self::Quote => 'Formal statement of expected costs for specific work',
			self::Proposal => 'Detailed offer outlining scope, timeline, and costs',

			// Payment types
			self::AdvancePayment => 'Payment made before goods/services are delivered',
			self::PartialPayment => 'Payment covering only part of the total amount',
			self::FullPayment => 'Complete payment of the entire amount due',
			self::Installment => 'Payment made as part of a series to settle an amount',
			self::DownPayment => 'Initial payment made when an agreement is reached',
			self::FinalPayment => 'Last payment to complete settlement of an amount',
			self::CreditCardPayment => 'Payment made using a credit card',
			self::BankTransfer => 'Payment transferred electronically between bank accounts',
			self::CashPayment => 'Payment made in physical currency',
			self::DigitalPayment => 'Payment made through digital/online methods',

			// Adjustments
			self::CreditNote => 'Document that reduces the amount a customer owes',
			self::DebitNote => 'Document that increases the amount a customer owes',
			self::Adjustment => 'Modification to correct or update a bill amount',
			self::CreditAdjustment => 'Adjustment that reduces the balance',
			self::DebitAdjustment => 'Adjustment that increases the balance',
			self::WriteOff => 'Cancellation of an uncollectible debt',
			self::WriteOn => 'Reversal of a previous write-off',

			// Discount/Tax
			self::Discount => 'Reduction in the usual price',
			self::EarlyPaymentDiscount => 'Discount for paying before the due date',
			self::VolumeDiscount => 'Discount based on quantity purchased',
			self::PromotionalDiscount => 'Discount offered as part of a promotion',
			self::Tax => 'Mandatory financial charge imposed by government',
			self::SalesTax => 'Tax on the sale of goods and services',
			self::VAT => 'Value Added Tax on the value added at each production stage',
			self::GST => 'Goods and Services Tax on most goods and services',
			self::ServiceTax => 'Tax specifically on services provided',
			self::WithholdingTax => 'Tax deducted at source from payments',

			// Fees
			self::Fee => 'Payment made for professional services',
			self::ServiceFee => 'Charge for specific services provided',
			self::ProcessingFee => 'Charge for handling or processing',
			self::TransactionFee => 'Charge for completing a transaction',
			self::LateFee => 'Charge for late payment',
			self::PenaltyFee => 'Charge for violation of terms',
			self::AdministrativeFee => 'Charge for administrative services',
			self::ConvenienceFee => 'Charge for convenient payment options',

			// Subscriptions
			self::Subscription => 'Recurring payment for continued service access',
			self::RecurringBilling => 'Automated regular billing for ongoing services',
			self::MembershipFee => 'Payment for membership in an organization',
			self::LicenseFee => 'Payment for permission to use something',
			self::MaintenanceFee => 'Payment for ongoing maintenance or support',
			self::SubscriptionRenewal => 'Payment to continue a subscription',
			self::SubscriptionUpgrade => 'Payment to upgrade subscription level',
			self::SubscriptionDowngrade => 'Adjustment when downgrading subscription',

			// Financial Documents
			self::Receipt => 'Document acknowledging payment received',
			self::Voucher => 'Document serving as evidence of a transaction',
			self::CreditVoucher => 'Voucher representing a credit amount',
			self::DebitVoucher => 'Voucher representing a debit amount',
			self::PaymentVoucher => 'Document authorizing payment',
			self::ReceiptVoucher => 'Document acknowledging receipt of goods/services',
			self::ContraVoucher => 'Document for internal accounting transfers',

			// Contracts
			self::Contract => 'Legally binding agreement between parties',
			self::Agreement => 'Mutual understanding between parties',
			self::PurchaseOrder => 'Commercial document issued by a buyer',
			self::SalesOrder => 'Document confirming details of a sale',
			self::WorkOrder => 'Document authorizing and describing work',
			self::ServiceOrder => 'Document authorizing service provision',

			// Delivery/Shipping
			self::DeliveryNote => 'Document accompanying shipment of goods',
			self::PackingSlip => 'Document listing items in a shipment',
			self::ShippingDocument => 'Document related to shipping goods',
			self::BillOfLading => 'Legal document between shipper and carrier',
			self::Waybill => 'Document issued by carrier giving details',

			// Miscellaneous
			self::Refund => 'Return of payment to the payer',
			self::Chargeback => 'Return of funds to consumer from merchant',
			self::Reimbursement => 'Repayment for expenses incurred',
			self::Commission => 'Payment to agent for services',
			self::Royalty => 'Payment to owner for use of property',
			self::Dividend => 'Payment to shareholders from profits',
			self::Interest => 'Payment for use of borrowed money',
			self::Depreciation => 'Allocation of cost of assets over time',
			self::Amortization => 'Gradual reduction of debt over time',

			// Other
			self::Other => 'Other type of bill reference not specified',
			self::Miscellaneous => 'Various bill reference types',
			self::Unclassified => 'Bill reference type not yet categorized',
		};
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			// Core types
			self::Bill->value => 'Bill',
			self::Payment->value => 'Payment',
			self::BillCategory->value => 'Bill Category',

			// Invoice/Statement
			self::Invoice->value => 'Invoice',
			self::ProformaInvoice->value => 'Proforma Invoice',
			self::RecurringInvoice->value => 'Recurring Invoice',
			self::CreditInvoice->value => 'Credit Invoice',
			self::DebitInvoice->value => 'Debit Invoice',
			self::Statement->value => 'Statement',
			self::Estimate->value => 'Estimate',
			self::Quote->value => 'Quote',
			self::Proposal->value => 'Proposal',

			// Payment types
			self::AdvancePayment->value => 'Advance Payment',
			self::PartialPayment->value => 'Partial Payment',
			self::FullPayment->value => 'Full Payment',
			self::Installment->value => 'Installment',
			self::DownPayment->value => 'Down Payment',
			self::FinalPayment->value => 'Final Payment',
			self::CreditCardPayment->value => 'Credit Card Payment',
			self::BankTransfer->value => 'Bank Transfer',
			self::CashPayment->value => 'Cash Payment',
			self::DigitalPayment->value => 'Digital Payment',

			// Adjustments
			self::CreditNote->value => 'Credit Note',
			self::DebitNote->value => 'Debit Note',
			self::Adjustment->value => 'Adjustment',
			self::CreditAdjustment->value => 'Credit Adjustment',
			self::DebitAdjustment->value => 'Debit Adjustment',
			self::WriteOff->value => 'Write Off',
			self::WriteOn->value => 'Write On',

			// Discount/Tax
			self::Discount->value => 'Discount',
			self::EarlyPaymentDiscount->value => 'Early Payment Discount',
			self::VolumeDiscount->value => 'Volume Discount',
			self::PromotionalDiscount->value => 'Promotional Discount',
			self::Tax->value => 'Tax',
			self::SalesTax->value => 'Sales Tax',
			self::VAT->value => 'VAT',
			self::GST->value => 'GST',
			self::ServiceTax->value => 'Service Tax',
			self::WithholdingTax->value => 'Withholding Tax',

			// Fees
			self::Fee->value => 'Fee',
			self::ServiceFee->value => 'Service Fee',
			self::ProcessingFee->value => 'Processing Fee',
			self::TransactionFee->value => 'Transaction Fee',
			self::LateFee->value => 'Late Fee',
			self::PenaltyFee->value => 'Penalty Fee',
			self::AdministrativeFee->value => 'Administrative Fee',
			self::ConvenienceFee->value => 'Convenience Fee',

			// Subscriptions
			self::Subscription->value => 'Subscription',
			self::RecurringBilling->value => 'Recurring Billing',
			self::MembershipFee->value => 'Membership Fee',
			self::LicenseFee->value => 'License Fee',
			self::MaintenanceFee->value => 'Maintenance Fee',
			self::SubscriptionRenewal->value => 'Subscription Renewal',
			self::SubscriptionUpgrade->value => 'Subscription Upgrade',
			self::SubscriptionDowngrade->value => 'Subscription Downgrade',

			// Financial Documents
			self::Receipt->value => 'Receipt',
			self::Voucher->value => 'Voucher',
			self::CreditVoucher->value => 'Credit Voucher',
			self::DebitVoucher->value => 'Debit Voucher',
			self::PaymentVoucher->value => 'Payment Voucher',
			self::ReceiptVoucher->value => 'Receipt Voucher',
			self::ContraVoucher->value => 'Contra Voucher',

			// Contracts
			self::Contract->value => 'Contract',
			self::Agreement->value => 'Agreement',
			self::PurchaseOrder->value => 'Purchase Order',
			self::SalesOrder->value => 'Sales Order',
			self::WorkOrder->value => 'Work Order',
			self::ServiceOrder->value => 'Service Order',

			// Delivery/Shipping
			self::DeliveryNote->value => 'Delivery Note',
			self::PackingSlip->value => 'Packing Slip',
			self::ShippingDocument->value => 'Shipping Document',
			self::BillOfLading->value => 'Bill of Lading',
			self::Waybill->value => 'Waybill',

			// Miscellaneous
			self::Refund->value => 'Refund',
			self::Chargeback->value => 'Chargeback',
			self::Reimbursement->value => 'Reimbursement',
			self::Commission->value => 'Commission',
			self::Royalty->value => 'Royalty',
			self::Dividend->value => 'Dividend',
			self::Interest->value => 'Interest',
			self::Depreciation->value => 'Depreciation',
			self::Amortization->value => 'Amortization',

			// Other
			self::Other->value => 'Other',
			self::Miscellaneous->value => 'Miscellaneous',
			self::Unclassified->value => 'Unclassified',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			// Core types
			self::Bill->value => 'Fatura',
			self::Payment->value => 'Pagamento',
			self::BillCategory->value => 'Categoria de Fatura',

			// Invoice/Statement
			self::Invoice->value => 'Fatura',
			self::ProformaInvoice->value => 'Fatura Pró-forma',
			self::RecurringInvoice->value => 'Fatura Recorrente',
			self::CreditInvoice->value => 'Fatura de Crédito',
			self::DebitInvoice->value => 'Fatura de Débito',
			self::Statement->value => 'Extrato',
			self::Estimate->value => 'Orçamento',
			self::Quote->value => 'Cotação',
			self::Proposal->value => 'Proposta',

			// Payment types
			self::AdvancePayment->value => 'Pagamento Antecipado',
			self::PartialPayment->value => 'Pagamento Parcial',
			self::FullPayment->value => 'Pagamento Integral',
			self::Installment->value => 'Parcela',
			self::DownPayment->value => 'Entrada',
			self::FinalPayment->value => 'Pagamento Final',
			self::CreditCardPayment->value => 'Pagamento com Cartão',
			self::BankTransfer->value => 'Transferência Bancária',
			self::CashPayment->value => 'Pagamento em Dinheiro',
			self::DigitalPayment->value => 'Pagamento Digital',

			// Adjustments
			self::CreditNote->value => 'Nota de Crédito',
			self::DebitNote->value => 'Nota de Débito',
			self::Adjustment->value => 'Ajuste',
			self::CreditAdjustment->value => 'Ajuste de Crédito',
			self::DebitAdjustment->value => 'Ajuste de Débito',
			self::WriteOff->value => 'Baixa',
			self::WriteOn->value => 'Reversão de Baixa',

			// Discount/Tax
			self::Discount->value => 'Desconto',
			self::EarlyPaymentDiscount->value => 'Desconto por Pagamento Antecipado',
			self::VolumeDiscount->value => 'Desconto por Volume',
			self::PromotionalDiscount->value => 'Desconto Promocional',
			self::Tax->value => 'Imposto',
			self::SalesTax->value => 'Imposto sobre Vendas',
			self::VAT->value => 'IVA',
			self::GST->value => 'GST',
			self::ServiceTax->value => 'Imposto sobre Serviços',
			self::WithholdingTax->value => 'Imposto Retido na Fonte',

			// Fees
			self::Fee->value => 'Taxa',
			self::ServiceFee->value => 'Taxa de Serviço',
			self::ProcessingFee->value => 'Taxa de Processamento',
			self::TransactionFee->value => 'Taxa de Transação',
			self::LateFee->value => 'Taxa de Atraso',
			self::PenaltyFee->value => 'Taxa de Penalidade',
			self::AdministrativeFee->value => 'Taxa Administrativa',
			self::ConvenienceFee->value => 'Taxa de Conveniência',

			// Subscriptions
			self::Subscription->value => 'Assinatura',
			self::RecurringBilling->value => 'Cobrança Recorrente',
			self::MembershipFee->value => 'Taxa de Associação',
			self::LicenseFee->value => 'Taxa de Licença',
			self::MaintenanceFee->value => 'Taxa de Manutenção',
			self::SubscriptionRenewal->value => 'Renovação de Assinatura',
			self::SubscriptionUpgrade->value => 'Upgrade de Assinatura',
			self::SubscriptionDowngrade->value => 'Downgrade de Assinatura',

			// Financial Documents
			self::Receipt->value => 'Recibo',
			self::Voucher->value => 'Comprovante',
			self::CreditVoucher->value => 'Comprovante de Crédito',
			self::DebitVoucher->value => 'Comprovante de Débito',
			self::PaymentVoucher->value => 'Comprovante de Pagamento',
			self::ReceiptVoucher->value => 'Comprovante de Recebimento',
			self::ContraVoucher->value => 'Comprovante de Transferência',

			// Contracts
			self::Contract->value => 'Contrato',
			self::Agreement->value => 'Acordo',
			self::PurchaseOrder->value => 'Ordem de Compra',
			self::SalesOrder->value => 'Ordem de Venda',
			self::WorkOrder->value => 'Ordem de Serviço',
			self::ServiceOrder->value => 'Ordem de Serviço',

			// Delivery/Shipping
			self::DeliveryNote->value => 'Nota de Entrega',
			self::PackingSlip->value => 'Relação de Embalagem',
			self::ShippingDocument->value => 'Documento de Remessa',
			self::BillOfLading->value => 'Conhecimento de Embarque',
			self::Waybill->value => 'Conhecimento de Transporte',

			// Miscellaneous
			self::Refund->value => 'Reembolso',
			self::Chargeback->value => 'Estorno',
			self::Reimbursement->value => 'Reembolso',
			self::Commission->value => 'Comissão',
			self::Royalty->value => 'Royalty',
			self::Dividend->value => 'Dividendo',
			self::Interest->value => 'Juros',
			self::Depreciation->value => 'Depreciação',
			self::Amortization->value => 'Amortização',

			// Other
			self::Other->value => 'Outro',
			self::Miscellaneous->value => 'Diversos',
			self::Unclassified->value => 'Não Classificado',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			// Core types
			self::Bill->value => 'Factura',
			self::Payment->value => 'Pago',
			self::BillCategory->value => 'Categoría de Factura',

			// Invoice/Statement
			self::Invoice->value => 'Factura',
			self::ProformaInvoice->value => 'Factura Proforma',
			self::RecurringInvoice->value => 'Factura Recurrente',
			self::CreditInvoice->value => 'Factura de Crédito',
			self::DebitInvoice->value => 'Factura de Débito',
			self::Statement->value => 'Estado de Cuenta',
			self::Estimate->value => 'Presupuesto',
			self::Quote->value => 'Cotización',
			self::Proposal->value => 'Propuesta',

			// Payment types
			self::AdvancePayment->value => 'Pago Adelantado',
			self::PartialPayment->value => 'Pago Parcial',
			self::FullPayment->value => 'Pago Completo',
			self::Installment->value => 'Cuota',
			self::DownPayment->value => 'Pago Inicial',
			self::FinalPayment->value => 'Pago Final',
			self::CreditCardPayment->value => 'Pago con Tarjeta',
			self::BankTransfer->value => 'Transferencia Bancaria',
			self::CashPayment->value => 'Pago en Efectivo',
			self::DigitalPayment->value => 'Pago Digital',

			// Adjustments
			self::CreditNote->value => 'Nota de Crédito',
			self::DebitNote->value => 'Nota de Débito',
			self::Adjustment->value => 'Ajuste',
			self::CreditAdjustment->value => 'Ajuste de Crédito',
			self::DebitAdjustment->value => 'Ajuste de Débito',
			self::WriteOff->value => 'Baja',
			self::WriteOn->value => 'Reversión de Baja',

			// Discount/Tax
			self::Discount->value => 'Descuento',
			self::EarlyPaymentDiscount->value => 'Descuento por Pago Anticipado',
			self::VolumeDiscount->value => 'Descuento por Volumen',
			self::PromotionalDiscount->value => 'Descuento Promocional',
			self::Tax->value => 'Impuesto',
			self::SalesTax->value => 'Impuesto sobre Ventas',
			self::VAT->value => 'IVA',
			self::GST->value => 'GST',
			self::ServiceTax->value => 'Impuesto sobre Servicios',
			self::WithholdingTax->value => 'Impuesto Retenido',

			// Fees
			self::Fee->value => 'Tarifa',
			self::ServiceFee->value => 'Tarifa de Servicio',
			self::ProcessingFee->value => 'Tarifa de Procesamiento',
			self::TransactionFee->value => 'Tarifa de Transacción',
			self::LateFee->value => 'Tarifa por Retraso',
			self::PenaltyFee->value => 'Tarifa por Penalidad',
			self::AdministrativeFee->value => 'Tarifa Administrativa',
			self::ConvenienceFee->value => 'Tarifa de Conveniencia',

			// Subscriptions
			self::Subscription->value => 'Suscripción',
			self::RecurringBilling->value => 'Facturación Recurrente',
			self::MembershipFee->value => 'Cuota de Membresía',
			self::LicenseFee->value => 'Tarifa de Licencia',
			self::MaintenanceFee->value => 'Tarifa de Mantenimiento',
			self::SubscriptionRenewal->value => 'Renovación de Suscripción',
			self::SubscriptionUpgrade->value => 'Actualización de Suscripción',
			self::SubscriptionDowngrade->value => 'Reducción de Suscripción',

			// Financial Documents
			self::Receipt->value => 'Recibo',
			self::Voucher->value => 'Comprobante',
			self::CreditVoucher->value => 'Comprobante de Crédito',
			self::DebitVoucher->value => 'Comprobante de Débito',
			self::PaymentVoucher->value => 'Comprobante de Pago',
			self::ReceiptVoucher->value => 'Comprobante de Recepción',
			self::ContraVoucher->value => 'Comprobante de Transferencia',

			// Contracts
			self::Contract->value => 'Contrato',
			self::Agreement->value => 'Acuerdo',
			self::PurchaseOrder->value => 'Orden de Compra',
			self::SalesOrder->value => 'Orden de Venta',
			self::WorkOrder->value => 'Orden de Trabajo',
			self::ServiceOrder->value => 'Orden de Servicio',

			// Delivery/Shipping
			self::DeliveryNote->value => 'Nota de Entrega',
			self::PackingSlip->value => 'Lista de Empaque',
			self::ShippingDocument->value => 'Documento de Envío',
			self::BillOfLading->value => 'Conocimiento de Embarque',
			self::Waybill->value => 'Carta de Porte',

			// Miscellaneous
			self::Refund->value => 'Reembolso',
			self::Chargeback->value => 'Contracargo',
			self::Reimbursement->value => 'Reembolso',
			self::Commission->value => 'Comisión',
			self::Royalty->value => 'Regalía',
			self::Dividend->value => 'Dividendo',
			self::Interest->value => 'Interés',
			self::Depreciation->value => 'Depreciación',
			self::Amortization->value => 'Amortización',

			// Other
			self::Other->value => 'Otro',
			self::Miscellaneous->value => 'Misceláneo',
			self::Unclassified->value => 'No Clasificado',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			// Core types
			self::Bill->value => 'Facture',
			self::Payment->value => 'Paiement',
			self::BillCategory->value => 'Catégorie de Facture',

			// Invoice/Statement
			self::Invoice->value => 'Facture',
			self::ProformaInvoice->value => 'Facture Proforma',
			self::RecurringInvoice->value => 'Facture Récurrente',
			self::CreditInvoice->value => 'Facture de Crédit',
			self::DebitInvoice->value => 'Facture de Débit',
			self::Statement->value => 'Relevé',
			self::Estimate->value => 'Devis',
			self::Quote->value => 'Devis',
			self::Proposal->value => 'Proposition',

			// Payment types
			self::AdvancePayment->value => 'Paiement d\'Avance',
			self::PartialPayment->value => 'Paiement Partiel',
			self::FullPayment->value => 'Paiement Complet',
			self::Installment->value => 'Tranche',
			self::DownPayment->value => 'Acompte',
			self::FinalPayment->value => 'Paiement Final',
			self::CreditCardPayment->value => 'Paiement par Carte',
			self::BankTransfer->value => 'Virement Bancaire',
			self::CashPayment->value => 'Paiement en Espèces',
			self::DigitalPayment->value => 'Paiement Numérique',

			// Adjustments
			self::CreditNote->value => 'Note de Crédit',
			self::DebitNote->value => 'Note de Débit',
			self::Adjustment->value => 'Ajustement',
			self::CreditAdjustment->value => 'Ajustement de Crédit',
			self::DebitAdjustment->value => 'Ajustement de Débit',
			self::WriteOff->value => 'Radiation',
			self::WriteOn->value => 'Annulation de Radiation',

			// Discount/Tax
			self::Discount->value => 'Remise',
			self::EarlyPaymentDiscount->value => 'Remise pour Paiement Anticipé',
			self::VolumeDiscount->value => 'Remise sur Volume',
			self::PromotionalDiscount->value => 'Remise Promotionnelle',
			self::Tax->value => 'Taxe',
			self::SalesTax->value => 'Taxe sur les Ventes',
			self::VAT->value => 'TVA',
			self::GST->value => 'GST',
			self::ServiceTax->value => 'Taxe sur les Services',
			self::WithholdingTax->value => 'Taxe Retenue à la Source',

			// Fees
			self::Fee->value => 'Frais',
			self::ServiceFee->value => 'Frais de Service',
			self::ProcessingFee->value => 'Frais de Traitement',
			self::TransactionFee->value => 'Frais de Transaction',
			self::LateFee->value => 'Frais de Retard',
			self::PenaltyFee->value => 'Frais de Pénalité',
			self::AdministrativeFee->value => 'Frais Administratifs',
			self::ConvenienceFee->value => 'Frais de Convenance',

			// Subscriptions
			self::Subscription->value => 'Abonnement',
			self::RecurringBilling->value => 'Facturation Récurrente',
			self::MembershipFee->value => 'Frais d\'Adhésion',
			self::LicenseFee->value => 'Frais de Licence',
			self::MaintenanceFee->value => 'Frais de Maintenance',
			self::SubscriptionRenewal->value => 'Renouvellement d\'Abonnement',
			self::SubscriptionUpgrade->value => 'Mise à Niveau d\'Abonnement',
			self::SubscriptionDowngrade->value => 'Rétrogradation d\'Abonnement',

			// Financial Documents
			self::Receipt->value => 'Reçu',
			self::Voucher->value => 'Bon',
			self::CreditVoucher->value => 'Bon de Crédit',
			self::DebitVoucher->value => 'Bon de Débit',
			self::PaymentVoucher->value => 'Bon de Paiement',
			self::ReceiptVoucher->value => 'Bon de Réception',
			self::ContraVoucher->value => 'Bon de Transfert',

			// Contracts
			self::Contract->value => 'Contrat',
			self::Agreement->value => 'Accord',
			self::PurchaseOrder->value => 'Bon de Commande',
			self::SalesOrder->value => 'Bon de Vente',
			self::WorkOrder->value => 'Bon de Travail',
			self::ServiceOrder->value => 'Bon de Service',

			// Delivery/Shipping
			self::DeliveryNote->value => 'Bon de Livraison',
			self::PackingSlip->value => 'Bordereau d\'Expédition',
			self::ShippingDocument->value => 'Document d\'Expédition',
			self::BillOfLading->value => 'Connaissement',
			self::Waybill->value => 'Lettre de Voiture',

			// Miscellaneous
			self::Refund->value => 'Remboursement',
			self::Chargeback->value => 'Contestation',
			self::Reimbursement->value => 'Remboursement',
			self::Commission->value => 'Commission',
			self::Royalty->value => 'Redevance',
			self::Dividend->value => 'Dividende',
			self::Interest->value => 'Intérêt',
			self::Depreciation->value => 'Dépréciation',
			self::Amortization->value => 'Amortissement',

			// Other
			self::Other->value => 'Autre',
			self::Miscellaneous->value => 'Divers',
			self::Unclassified->value => 'Non Classifié',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			// Core types
			self::Bill->value => 'Rechnung',
			self::Payment->value => 'Zahlung',
			self::BillCategory->value => 'Rechnungskategorie',

			// Invoice/Statement
			self::Invoice->value => 'Rechnung',
			self::ProformaInvoice->value => 'Proforma-Rechnung',
			self::RecurringInvoice->value => 'Wiederkehrende Rechnung',
			self::CreditInvoice->value => 'Gutschrift',
			self::DebitInvoice->value => 'Lastschrift',
			self::Statement->value => 'Kontoauszug',
			self::Estimate->value => 'Kostenvoranschlag',
			self::Quote->value => 'Angebot',
			self::Proposal->value => 'Vorschlag',

			// Payment types
			self::AdvancePayment->value => 'Vorauszahlung',
			self::PartialPayment->value => 'Teilzahlung',
			self::FullPayment->value => 'Vollständige Zahlung',
			self::Installment->value => 'Ratenzahlung',
			self::DownPayment->value => 'Anzahlung',
			self::FinalPayment->value => 'Schlusszahlung',
			self::CreditCardPayment->value => 'Kreditkartenzahlung',
			self::BankTransfer->value => 'Banküberweisung',
			self::CashPayment->value => 'Barzahlung',
			self::DigitalPayment->value => 'Digitale Zahlung',

			// Adjustments
			self::CreditNote->value => 'Gutschrift',
			self::DebitNote->value => 'Lastschrift',
			self::Adjustment->value => 'Berichtigung',
			self::CreditAdjustment->value => 'Gutschriftsberichtigung',
			self::DebitAdjustment->value => 'Lastschriftsberichtigung',
			self::WriteOff->value => 'Abschreibung',
			self::WriteOn->value => 'Rückgängig Abschreibung',

			// Discount/Tax
			self::Discount->value => 'Rabatt',
			self::EarlyPaymentDiscount->value => 'Skonto',
			self::VolumeDiscount->value => 'Mengenrabatt',
			self::PromotionalDiscount->value => 'Aktionsrabatt',
			self::Tax->value => 'Steuer',
			self::SalesTax->value => 'Umsatzsteuer',
			self::VAT->value => 'Mehrwertsteuer',
			self::GST->value => 'GST',
			self::ServiceTax->value => 'Dienstleistungssteuer',
			self::WithholdingTax->value => 'Quellensteuer',

			// Fees
			self::Fee->value => 'Gebühr',
			self::ServiceFee->value => 'Servicegebühr',
			self::ProcessingFee->value => 'Bearbeitungsgebühr',
			self::TransactionFee->value => 'Transaktionsgebühr',
			self::LateFee->value => 'Verspätungsgebühr',
			self::PenaltyFee->value => 'Strafgebühr',
			self::AdministrativeFee->value => 'Verwaltungsgebühr',
			self::ConvenienceFee->value => 'Bequemlichkeitsgebühr',

			// Subscriptions
			self::Subscription->value => 'Abonnement',
			self::RecurringBilling->value => 'Wiederkehrende Abrechnung',
			self::MembershipFee->value => 'Mitgliedsbeitrag',
			self::LicenseFee->value => 'Lizenzgebühr',
			self::MaintenanceFee->value => 'Wartungsgebühr',
			self::SubscriptionRenewal->value => 'Abonnementverlängerung',
			self::SubscriptionUpgrade->value => 'Abonnement-Upgrade',
			self::SubscriptionDowngrade->value => 'Abonnement-Downgrade',

			// Financial Documents
			self::Receipt->value => 'Quittung',
			self::Voucher->value => 'Gutschein',
			self::CreditVoucher->value => 'Gutschriftsgutschein',
			self::DebitVoucher->value => 'Lastschriftsgutschein',
			self::PaymentVoucher->value => 'Zahlungsbeleg',
			self::ReceiptVoucher->value => 'Eingangsbeleg',
			self::ContraVoucher->value => 'Übertragungsbeleg',

			// Contracts
			self::Contract->value => 'Vertrag',
			self::Agreement->value => 'Vereinbarung',
			self::PurchaseOrder->value => 'Bestellung',
			self::SalesOrder->value => 'Verkaufsauftrag',
			self::WorkOrder->value => 'Arbeitsauftrag',
			self::ServiceOrder->value => 'Serviceauftrag',

			// Delivery/Shipping
			self::DeliveryNote->value => 'Lieferschein',
			self::PackingSlip->value => 'Packliste',
			self::ShippingDocument->value => 'Versanddokument',
			self::BillOfLading->value => 'Frachtbrief',
			self::Waybill->value => 'Frachtbrief',

			// Miscellaneous
			self::Refund->value => 'Rückerstattung',
			self::Chargeback->value => 'Rückbuchung',
			self::Reimbursement->value => 'Erstattung',
			self::Commission->value => 'Provision',
			self::Royalty->value => 'Lizenzgebühr',
			self::Dividend->value => 'Dividende',
			self::Interest->value => 'Zinsen',
			self::Depreciation->value => 'Abschreibung',
			self::Amortization->value => 'Amortisation',

			// Other
			self::Other->value => 'Andere',
			self::Miscellaneous->value => 'Verschiedenes',
			self::Unclassified->value => 'Nicht klassifiziert',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			// Core types
			self::Bill->value => 'Fattura',
			self::Payment->value => 'Pagamento',
			self::BillCategory->value => 'Categoria di Fattura',

			// Invoice/Statement
			self::Invoice->value => 'Fattura',
			self::ProformaInvoice->value => 'Fattura Proforma',
			self::RecurringInvoice->value => 'Fattura Ricorrente',
			self::CreditInvoice->value => 'Fattura di Credito',
			self::DebitInvoice->value => 'Fattura di Debito',
			self::Statement->value => 'Estratto Conto',
			self::Estimate->value => 'Preventivo',
			self::Quote->value => 'Preventivo',
			self::Proposal->value => 'Proposta',

			// Payment types
			self::AdvancePayment->value => 'Pagamento Anticipato',
			self::PartialPayment->value => 'Pagamento Parziale',
			self::FullPayment->value => 'Pagamento Completo',
			self::Installment->value => 'Rata',
			self::DownPayment->value => 'Acconto',
			self::FinalPayment->value => 'Pagamento Finale',
			self::CreditCardPayment->value => 'Pagamento con Carta',
			self::BankTransfer->value => 'Bonifico Bancario',
			self::CashPayment->value => 'Pagamento in Contanti',
			self::DigitalPayment->value => 'Pagamento Digitale',

			// Adjustments
			self::CreditNote->value => 'Nota di Credito',
			self::DebitNote->value => 'Nota di Debito',
			self::Adjustment->value => 'Rettifica',
			self::CreditAdjustment->value => 'Rettifica di Credito',
			self::DebitAdjustment->value => 'Rettifica di Debito',
			self::WriteOff->value => 'Storno',
			self::WriteOn->value => 'Annullamento Storno',

			// Discount/Tax
			self::Discount->value => 'Sconto',
			self::EarlyPaymentDiscount->value => 'Sconto per Pagamento Anticipato',
			self::VolumeDiscount->value => 'Sconto Volume',
			self::PromotionalDiscount->value => 'Sconto Promozionale',
			self::Tax->value => 'Tassa',
			self::SalesTax->value => 'Imposta sulle Vendite',
			self::VAT->value => 'IVA',
			self::GST->value => 'GST',
			self::ServiceTax->value => 'Imposta sui Servizi',
			self::WithholdingTax->value => 'Ritenuta alla Fonte',

			// Fees
			self::Fee->value => 'Tariffa',
			self::ServiceFee->value => 'Tariffa di Servizio',
			self::ProcessingFee->value => 'Tariffa di Elaborazione',
			self::TransactionFee->value => 'Tariffa di Transazione',
			self::LateFee->value => 'Tariffa per Ritardo',
			self::PenaltyFee->value => 'Tariffa per Penalità',
			self::AdministrativeFee->value => 'Tariffa Amministrativa',
			self::ConvenienceFee->value => 'Tariffa di Convenienza',

			// Subscriptions
			self::Subscription->value => 'Abbonamento',
			self::RecurringBilling->value => 'Fatturazione Ricorrente',
			self::MembershipFee->value => 'Quota Associativa',
			self::LicenseFee->value => 'Tariffa di Licenza',
			self::MaintenanceFee->value => 'Tariffa di Manutenzione',
			self::SubscriptionRenewal->value => 'Rinnovo Abbonamento',
			self::SubscriptionUpgrade->value => 'Aggiornamento Abbonamento',
			self::SubscriptionDowngrade->value => 'Riduzione Abbonamento',

			// Financial Documents
			self::Receipt->value => 'Ricevuta',
			self::Voucher->value => 'Buono',
			self::CreditVoucher->value => 'Buono di Credito',
			self::DebitVoucher->value => 'Buono di Debito',
			self::PaymentVoucher->value => 'Buono di Pagamento',
			self::ReceiptVoucher->value => 'Buono di Ricevimento',
			self::ContraVoucher->value => 'Buono di Trasferimento',

			// Contracts
			self::Contract->value => 'Contratto',
			self::Agreement->value => 'Accordo',
			self::PurchaseOrder->value => 'Ordine di Acquisto',
			self::SalesOrder->value => 'Ordine di Vendita',
			self::WorkOrder->value => 'Ordine di Lavoro',
			self::ServiceOrder->value => 'Ordine di Servizio',

			// Delivery/Shipping
			self::DeliveryNote->value => 'Nota di Consegna',
			self::PackingSlip->value => 'Lista di Imballaggio',
			self::ShippingDocument->value => 'Documento di Spedizione',
			self::BillOfLading->value => 'Polizza di Carico',
			self::Waybill->value => 'Lettera di Vettura',

			// Miscellaneous
			self::Refund->value => 'Rimborso',
			self::Chargeback->value => 'Contro addebito',
			self::Reimbursement->value => 'Rimborso',
			self::Commission->value => 'Commissione',
			self::Royalty->value => 'Royalty',
			self::Dividend->value => 'Dividendo',
			self::Interest->value => 'Interesse',
			self::Depreciation->value => 'Ammortamento',
			self::Amortization->value => 'Ammortamento',

			// Other
			self::Other->value => 'Altro',
			self::Miscellaneous->value => 'Varie',
			self::Unclassified->value => 'Non Classificato',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			// Core types
			self::Bill->value => 'Factuur',
			self::Payment->value => 'Betaling',
			self::BillCategory->value => 'Factuurcategorie',

			// Invoice/Statement
			self::Invoice->value => 'Factuur',
			self::ProformaInvoice->value => 'Proforma Factuur',
			self::RecurringInvoice->value => 'Terugkerende Factuur',
			self::CreditInvoice->value => 'Credit Factuur',
			self::DebitInvoice->value => 'Debet Factuur',
			self::Statement->value => 'Afschrift',
			self::Estimate->value => 'Offerte',
			self::Quote->value => 'Offerte',
			self::Proposal->value => 'Voorstel',

			// Payment types
			self::AdvancePayment->value => 'Vooruitbetaling',
			self::PartialPayment->value => 'Gedeeltelijke Betaling',
			self::FullPayment->value => 'Volledige Betaling',
			self::Installment->value => 'Termijnbetaling',
			self::DownPayment->value => 'Aanbetaling',
			self::FinalPayment->value => 'Eindbetaling',
			self::CreditCardPayment->value => 'Creditcard Betaling',
			self::BankTransfer->value => 'Bankoverschrijving',
			self::CashPayment->value => 'Contante Betaling',
			self::DigitalPayment->value => 'Digitale Betaling',

			// Adjustments
			self::CreditNote->value => 'Creditnota',
			self::DebitNote->value => 'Debetnota',
			self::Adjustment->value => 'Aanpassing',
			self::CreditAdjustment->value => 'Credit Aanpassing',
			self::DebitAdjustment->value => 'Debet Aanpassing',
			self::WriteOff->value => 'Afschrijving',
			self::WriteOn->value => 'Terugdraaien Afschrijving',

			// Discount/Tax
			self::Discount->value => 'Korting',
			self::EarlyPaymentDiscount->value => 'Korting voor Vroegtijdige Betaling',
			self::VolumeDiscount->value => 'Volume Korting',
			self::PromotionalDiscount->value => 'Promotionele Korting',
			self::Tax->value => 'Belasting',
			self::SalesTax->value => 'Omzetbelasting',
			self::VAT->value => 'BTW',
			self::GST->value => 'GST',
			self::ServiceTax->value => 'Dienstbelasting',
			self::WithholdingTax->value => 'Bronbelasting',

			// Fees
			self::Fee->value => 'Vergoeding',
			self::ServiceFee->value => 'Service Vergoeding',
			self::ProcessingFee->value => 'Verwerkingskosten',
			self::TransactionFee->value => 'Transactiekosten',
			self::LateFee->value => 'Verlate Betaling Vergoeding',
			self::PenaltyFee->value => 'Boete',
			self::AdministrativeFee->value => 'Administratiekosten',
			self::ConvenienceFee->value => 'Gemaksvergoeding',

			// Subscriptions
			self::Subscription->value => 'Abonnement',
			self::RecurringBilling->value => 'Terugkerende Facturering',
			self::MembershipFee->value => 'Lidmaatschapskosten',
			self::LicenseFee->value => 'Licentiekosten',
			self::MaintenanceFee->value => 'Onderhoudskosten',
			self::SubscriptionRenewal->value => 'Abonnementsverlenging',
			self::SubscriptionUpgrade->value => 'Abonnementsupgrade',
			self::SubscriptionDowngrade->value => 'Abonnementsdowngrade',

			// Financial Documents
			self::Receipt->value => 'Bon',
			self::Voucher->value => 'Voucher',
			self::CreditVoucher->value => 'Credit Voucher',
			self::DebitVoucher->value => 'Debet Voucher',
			self::PaymentVoucher->value => 'Betalingsbewijs',
			self::ReceiptVoucher->value => 'Ontvangstbewijs',
			self::ContraVoucher->value => 'Overboekingsbewijs',

			// Contracts
			self::Contract->value => 'Contract',
			self::Agreement->value => 'Overeenkomst',
			self::PurchaseOrder->value => 'Bestelbon',
			self::SalesOrder->value => 'Verkooporder',
			self::WorkOrder->value => 'Werkorder',
			self::ServiceOrder->value => 'Serviceorder',

			// Delivery/Shipping
			self::DeliveryNote->value => 'Leveringsbon',
			self::PackingSlip->value => 'Pakbon',
			self::ShippingDocument->value => 'Verzenddocument',
			self::BillOfLading->value => 'Vrachtbrief',
			self::Waybill->value => 'Vrachtbrief',

			// Miscellaneous
			self::Refund->value => 'Terugbetaling',
			self::Chargeback->value => 'Terugboeking',
			self::Reimbursement->value => 'Vergoeding',
			self::Commission->value => 'Commissie',
			self::Royalty->value => 'Royalty',
			self::Dividend->value => 'Dividend',
			self::Interest->value => 'Rente',
			self::Depreciation->value => 'Afschrijving',
			self::Amortization->value => 'Amortisatie',

			// Other
			self::Other->value => 'Anders',
			self::Miscellaneous->value => 'Diversen',
			self::Unclassified->value => 'Niet-geclassificeerd',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			// Core types
			self::Bill->value => 'Rachunek',
			self::Payment->value => 'Płatność',
			self::BillCategory->value => 'Kategoria Rachunku',

			// Invoice/Statement
			self::Invoice->value => 'Faktura',
			self::ProformaInvoice->value => 'Faktura Proforma',
			self::RecurringInvoice->value => 'Faktura Okresowa',
			self::CreditInvoice->value => 'Faktura Korygująca',
			self::DebitInvoice->value => 'Faktura Obciążeniowa',
			self::Statement->value => 'Wyciąg',
			self::Estimate->value => 'Wycenia',
			self::Quote->value => 'Oferta',
			self::Proposal->value => 'Propozycja',

			// Payment types
			self::AdvancePayment->value => 'Płatność Zaliczkowa',
			self::PartialPayment->value => 'Płatność Częściowa',
			self::FullPayment->value => 'Płatność Pełna',
			self::Installment->value => 'Rata',
			self::DownPayment->value => 'Zadatek',
			self::FinalPayment->value => 'Płatność Końcowa',
			self::CreditCardPayment->value => 'Płatność Kartą',
			self::BankTransfer->value => 'Przelew Bankowy',
			self::CashPayment->value => 'Płatność Gotówką',
			self::DigitalPayment->value => 'Płatność Cyfrowa',

			// Adjustments
			self::CreditNote->value => 'Nota Uznaniowa',
			self::DebitNote->value => 'Nota Obciążeniowa',
			self::Adjustment->value => 'Korekta',
			self::CreditAdjustment->value => 'Korekta Uznaniowa',
			self::DebitAdjustment->value => 'Korekta Obciążeniowa',
			self::WriteOff->value => 'Odpis',
			self::WriteOn->value => 'Anulowanie Odpisu',

			// Discount/Tax
			self::Discount->value => 'Rabat',
			self::EarlyPaymentDiscount->value => 'Rabat za Wczesną Płatność',
			self::VolumeDiscount->value => 'Rabat Ilościowy',
			self::PromotionalDiscount->value => 'Rabat Promocyjny',
			self::Tax->value => 'Podatek',
			self::SalesTax->value => 'Podatek od Sprzedaży',
			self::VAT->value => 'VAT',
			self::GST->value => 'GST',
			self::ServiceTax->value => 'Podatek od Usług',
			self::WithholdingTax->value => 'Podatek U źródła',

			// Fees
			self::Fee->value => 'Opłata',
			self::ServiceFee->value => 'Opłata Serwisowa',
			self::ProcessingFee->value => 'Opłata za Przetwarzanie',
			self::TransactionFee->value => 'Opłata Transakcyjna',
			self::LateFee->value => 'Opłata za Opóźnienie',
			self::PenaltyFee->value => 'Kara',
			self::AdministrativeFee->value => 'Opłata Administracyjna',
			self::ConvenienceFee->value => 'Opłata za Wygodę',

			// Subscriptions
			self::Subscription->value => 'Subskrypcja',
			self::RecurringBilling->value => 'Cykliczne Rozliczenie',
			self::MembershipFee->value => 'Członkostwo',
			self::LicenseFee->value => 'Opłata Licencyjna',
			self::MaintenanceFee->value => 'Opłata za Konserwację',
			self::SubscriptionRenewal->value => 'Odnowienie Subskrypcji',
			self::SubscriptionUpgrade->value => 'Ulepszenie Subskrypcji',
			self::SubscriptionDowngrade->value => 'Obniżenie Subskrypcji',

			// Financial Documents
			self::Receipt->value => 'Paragon',
			self::Voucher->value => 'Voucher',
			self::CreditVoucher->value => 'Voucher Uznaniowy',
			self::DebitVoucher->value => 'Voucher Obciążeniowy',
			self::PaymentVoucher->value => 'Dowód Płatności',
			self::ReceiptVoucher->value => 'Dowód Odbioru',
			self::ContraVoucher->value => 'Dowód Transferu',

			// Contracts
			self::Contract->value => 'Umowa',
			self::Agreement->value => 'Porozumienie',
			self::PurchaseOrder->value => 'Zamówienie',
			self::SalesOrder->value => 'Zamówienie Sprzedaży',
			self::WorkOrder->value => 'Zlecenie Pracy',
			self::ServiceOrder->value => 'Zlecenie Usługi',

			// Delivery/Shipping
			self::DeliveryNote->value => 'Dowód Dostawy',
			self::PackingSlip->value => 'Lista Pakowania',
			self::ShippingDocument->value => 'Dokument Wysyłkowy',
			self::BillOfLading->value => 'Konosament',
			self::Waybill->value => 'List Przewozowy',

			// Miscellaneous
			self::Refund->value => 'Zwrot Pieniędzy',
			self::Chargeback->value => 'Obciążenie Zwrotne',
			self::Reimbursement->value => 'Zwrot Kosztów',
			self::Commission->value => 'Prowizja',
			self::Royalty->value => 'Royalty',
			self::Dividend->value => 'Dywidenda',
			self::Interest->value => 'Odsetki',
			self::Depreciation->value => 'Amortyzacja',
			self::Amortization->value => 'Amortyzacja',

			// Other
			self::Other->value => 'Inny',
			self::Miscellaneous->value => 'Różne',
			self::Unclassified->value => 'Nieklasyfikowany',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			// Core types
			self::Bill->value => 'Счет',
			self::Payment->value => 'Платеж',
			self::BillCategory->value => 'Категория Счета',

			// Invoice/Statement
			self::Invoice->value => 'Счет-фактура',
			self::ProformaInvoice->value => 'Проформа Счет',
			self::RecurringInvoice->value => 'Повторяющийся Счет',
			self::CreditInvoice->value => 'Кредитовый Счет',
			self::DebitInvoice->value => 'Дебитовый Счет',
			self::Statement->value => 'Выписка',
			self::Estimate->value => 'Смета',
			self::Quote->value => 'Квота',
			self::Proposal->value => 'Предложение',

			// Payment types
			self::AdvancePayment->value => 'Авансовый Платеж',
			self::PartialPayment->value => 'Частичный Платеж',
			self::FullPayment->value => 'Полный Платеж',
			self::Installment->value => 'Рассрочка',
			self::DownPayment->value => 'Задаток',
			self::FinalPayment->value => 'Окончательный Платеж',
			self::CreditCardPayment->value => 'Платеж по Карте',
			self::BankTransfer->value => 'Банковский Перевод',
			self::CashPayment->value => 'Наличный Платеж',
			self::DigitalPayment->value => 'Цифровой Платеж',

			// Adjustments
			self::CreditNote->value => 'Кредитовое Уведомление',
			self::DebitNote->value => 'Дебитовое Уведомление',
			self::Adjustment->value => 'Корректировка',
			self::CreditAdjustment->value => 'Кредитовая Корректировка',
			self::DebitAdjustment->value => 'Дебитовая Корректировка',
			self::WriteOff->value => 'Списание',
			self::WriteOn->value => 'Отмена Списания',

			// Discount/Tax
			self::Discount->value => 'Скидка',
			self::EarlyPaymentDiscount->value => 'Скидка за Раннюю Оплату',
			self::VolumeDiscount->value => 'Скидка за Объем',
			self::PromotionalDiscount->value => 'Промо-Скидка',
			self::Tax->value => 'Налог',
			self::SalesTax->value => 'Налог с Продаж',
			self::VAT->value => 'НДС',
			self::GST->value => 'GST',
			self::ServiceTax->value => 'Налог на Услуги',
			self::WithholdingTax->value => 'Налог у Источника',

			// Fees
			self::Fee->value => 'Сбор',
			self::ServiceFee->value => 'Плата за Услугу',
			self::ProcessingFee->value => 'Плата за Обработку',
			self::TransactionFee->value => 'Комиссия за Транзакцию',
			self::LateFee->value => 'Штраф за Опоздание',
			self::PenaltyFee->value => 'Штрафная Санкция',
			self::AdministrativeFee->value => 'Административный Сбор',
			self::ConvenienceFee->value => 'Плата за Удобство',

			// Subscriptions
			self::Subscription->value => 'Подписка',
			self::RecurringBilling->value => 'Повторяющееся Выставление Счетов',
			self::MembershipFee->value => 'Членский Взнос',
			self::LicenseFee->value => 'Лицензионный Сбор',
			self::MaintenanceFee->value => 'Плата за Обслуживание',
			self::SubscriptionRenewal->value => 'Продление Подписки',
			self::SubscriptionUpgrade->value => 'Обновление Подписки',
			self::SubscriptionDowngrade->value => 'Понижение Подписки',

			// Financial Documents
			self::Receipt->value => 'Квитанция',
			self::Voucher->value => 'Ваучер',
			self::CreditVoucher->value => 'Кредитовый Ваучер',
			self::DebitVoucher->value => 'Дебитовый Ваучер',
			self::PaymentVoucher->value => 'Платежный Ваучер',
			self::ReceiptVoucher->value => 'Приходный Ваучер',
			self::ContraVoucher->value => 'Контра-Ваучер',

			// Contracts
			self::Contract->value => 'Контракт',
			self::Agreement->value => 'Соглашение',
			self::PurchaseOrder->value => 'Заказ на Покупку',
			self::SalesOrder->value => 'Заказ на Продажу',
			self::WorkOrder->value => 'Рабочий Заказ',
			self::ServiceOrder->value => 'Заказ на Услугу',

			// Delivery/Shipping
			self::DeliveryNote->value => 'Товарная Накладная',
			self::PackingSlip->value => 'Упаковочный Лист',
			self::ShippingDocument->value => 'Отгрузочный Документ',
			self::BillOfLading->value => 'Коносамент',
			self::Waybill->value => 'Транспортная Накладная',

			// Miscellaneous
			self::Refund->value => 'Возврат',
			self::Chargeback->value => 'Обратное Списание',
			self::Reimbursement->value => 'Возмещение',
			self::Commission->value => 'Комиссия',
			self::Royalty->value => 'Роялти',
			self::Dividend->value => 'Дивиденды',
			self::Interest->value => 'Проценты',
			self::Depreciation->value => 'Амортизация',
			self::Amortization->value => 'Амортизация',

			// Other
			self::Other->value => 'Другое',
			self::Miscellaneous->value => 'Разное',
			self::Unclassified->value => 'Не классифицировано',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			// Core types
			self::Bill->value => 'Fatura',
			self::Payment->value => 'Ödeme',
			self::BillCategory->value => 'Fatura Kategorisi',

			// Invoice/Statement
			self::Invoice->value => 'Fatura',
			self::ProformaInvoice->value => 'Proforma Fatura',
			self::RecurringInvoice->value => 'Tekrarlayan Fatura',
			self::CreditInvoice->value => 'Kredi Faturası',
			self::DebitInvoice->value => 'Borç Faturası',
			self::Statement->value => 'Ekstre',
			self::Estimate->value => 'Tahmini',
			self::Quote->value => 'Teklif',
			self::Proposal->value => 'Teklif',

			// Payment types
			self::AdvancePayment->value => 'Avans Ödemesi',
			self::PartialPayment->value => 'Kısmi Ödeme',
			self::FullPayment->value => 'Tam Ödeme',
			self::Installment->value => 'Taksit',
			self::DownPayment->value => 'Peşinat',
			self::FinalPayment->value => 'Son Ödeme',
			self::CreditCardPayment->value => 'Kredi Kartı Ödemesi',
			self::BankTransfer->value => 'Banka Havalesi',
			self::CashPayment->value => 'Nakit Ödeme',
			self::DigitalPayment->value => 'Dijital Ödeme',

			// Adjustments
			self::CreditNote->value => 'Kredi Notu',
			self::DebitNote->value => 'Borç Notu',
			self::Adjustment->value => 'Düzeltme',
			self::CreditAdjustment->value => 'Kredi Düzeltmesi',
			self::DebitAdjustment->value => 'Borç Düzeltmesi',
			self::WriteOff->value => 'Silme',
			self::WriteOn->value => 'Silme İptali',

			// Discount/Tax
			self::Discount->value => 'İndirim',
			self::EarlyPaymentDiscount->value => 'Erken Ödeme İndirimi',
			self::VolumeDiscount->value => 'Miktar İndirimi',
			self::PromotionalDiscount->value => 'Promosyon İndirimi',
			self::Tax->value => 'Vergi',
			self::SalesTax->value => 'Satış Vergisi',
			self::VAT->value => 'KDV',
			self::GST->value => 'GST',
			self::ServiceTax->value => 'Hizmet Vergisi',
			self::WithholdingTax->value => 'Stopaj Vergisi',

			// Fees
			self::Fee->value => 'Ücret',
			self::ServiceFee->value => 'Hizmet Ücreti',
			self::ProcessingFee->value => 'İşlem Ücreti',
			self::TransactionFee->value => 'İşlem Ücreti',
			self::LateFee->value => 'Gecikme Ücreti',
			self::PenaltyFee->value => 'Ceza Ücreti',
			self::AdministrativeFee->value => 'Yönetim Ücreti',
			self::ConvenienceFee->value => 'Kolaylık Ücreti',

			// Subscriptions
			self::Subscription->value => 'Abonelik',
			self::RecurringBilling->value => 'Tekrarlayan Faturalandırma',
			self::MembershipFee->value => 'Üyelik Ücreti',
			self::LicenseFee->value => 'Lisans Ücreti',
			self::MaintenanceFee->value => 'Bakım Ücreti',
			self::SubscriptionRenewal->value => 'Abonelik Yenileme',
			self::SubscriptionUpgrade->value => 'Abonelik Yükseltme',
			self::SubscriptionDowngrade->value => 'Abonelik Düşürme',

			// Financial Documents
			self::Receipt->value => 'Fiş',
			self::Voucher->value => 'Kupon',
			self::CreditVoucher->value => 'Kredi Kuponu',
			self::DebitVoucher->value => 'Borç Kuponu',
			self::PaymentVoucher->value => 'Ödeme Kuponu',
			self::ReceiptVoucher->value => 'Alındı Kuponu',
			self::ContraVoucher->value => 'Transfer Kuponu',

			// Contracts
			self::Contract->value => 'Sözleşme',
			self::Agreement->value => 'Anlaşma',
			self::PurchaseOrder->value => 'Satın Alma Siparişi',
			self::SalesOrder->value => 'Satış Siparişi',
			self::WorkOrder->value => 'İş Emri',
			self::ServiceOrder->value => 'Hizmet Siparişi',

			// Delivery/Shipping
			self::DeliveryNote->value => 'Teslimat Notu',
			self::PackingSlip->value => 'Paketleme Listesi',
			self::ShippingDocument->value => 'Sevkiyat Belgesi',
			self::BillOfLading->value => 'Konşimento',
			self::Waybill->value => 'Taşıma Belgesi',

			// Miscellaneous
			self::Refund->value => 'İade',
			self::Chargeback->value => 'Geri Tahsilat',
			self::Reimbursement->value => 'Geri Ödeme',
			self::Commission->value => 'Komisyon',
			self::Royalty->value => 'Telif Hakkı',
			self::Dividend->value => 'Temettü',
			self::Interest->value => 'Faiz',
			self::Depreciation->value => 'Amortisman',
			self::Amortization->value => 'Amortisman',

			// Other
			self::Other->value => 'Diğer',
			self::Miscellaneous->value => 'Çeşitli',
			self::Unclassified->value => 'Sınıflandırılmamış',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			// Core types
			self::Bill->value => 'فاتورة',
			self::Payment->value => 'دفع',
			self::BillCategory->value => 'فئة الفاتورة',

			// Invoice/Statement
			self::Invoice->value => 'فاتورة',
			self::ProformaInvoice->value => 'فاتورة أولية',
			self::RecurringInvoice->value => 'فاتورة متكررة',
			self::CreditInvoice->value => 'فاتورة دائنة',
			self::DebitInvoice->value => 'فاتورة مدينة',
			self::Statement->value => 'كشف حساب',
			self::Estimate->value => 'تقدير',
			self::Quote->value => 'عرض سعر',
			self::Proposal->value => 'اقتراح',

			// Payment types
			self::AdvancePayment->value => 'دفعة مقدمة',
			self::PartialPayment->value => 'دفعة جزئية',
			self::FullPayment->value => 'دفعة كاملة',
			self::Installment->value => 'قسط',
			self::DownPayment->value => 'دفعة أولية',
			self::FinalPayment->value => 'الدفعة النهائية',
			self::CreditCardPayment->value => 'دفع ببطاقة الائتمان',
			self::BankTransfer->value => 'تحويل بنكي',
			self::CashPayment->value => 'دفع نقدي',
			self::DigitalPayment->value => 'دفع رقمي',

			// Adjustments
			self::CreditNote->value => 'إشعار دائن',
			self::DebitNote->value => 'إشعار مدين',
			self::Adjustment->value => 'تعديل',
			self::CreditAdjustment->value => 'تعديل دائن',
			self::DebitAdjustment->value => 'تعديل مدين',
			self::WriteOff->value => 'شطب',
			self::WriteOn->value => 'إلغاء شطب',

			// Discount/Tax
			self::Discount->value => 'خصم',
			self::EarlyPaymentDiscount->value => 'خصم الدفع المبكر',
			self::VolumeDiscount->value => 'خصم الكمية',
			self::PromotionalDiscount->value => 'خصم ترويجي',
			self::Tax->value => 'ضريبة',
			self::SalesTax->value => 'ضريبة المبيعات',
			self::VAT->value => 'ضريبة القيمة المضافة',
			self::GST->value => 'GST',
			self::ServiceTax->value => 'ضريبة الخدمات',
			self::WithholdingTax->value => 'ضريبة الخصم',

			// Fees
			self::Fee->value => 'رسوم',
			self::ServiceFee->value => 'رسوم الخدمة',
			self::ProcessingFee->value => 'رسوم المعالجة',
			self::TransactionFee->value => 'رسوم المعاملة',
			self::LateFee->value => 'رسوم التأخير',
			self::PenaltyFee->value => 'رسوم جزائية',
			self::AdministrativeFee->value => 'رسوم إدارية',
			self::ConvenienceFee->value => 'رسوم الراحة',

			// Subscriptions
			self::Subscription->value => 'اشتراك',
			self::RecurringBilling->value => 'فوترة متكررة',
			self::MembershipFee->value => 'رسوم العضوية',
			self::LicenseFee->value => 'رسوم الترخيص',
			self::MaintenanceFee->value => 'رسوم الصيانة',
			self::SubscriptionRenewal->value => 'تجديد الاشتراك',
			self::SubscriptionUpgrade->value => 'ترقية الاشتراك',
			self::SubscriptionDowngrade->value => 'تخفيض الاشتراك',

			// Financial Documents
			self::Receipt->value => 'إيصال',
			self::Voucher->value => 'قسيمة',
			self::CreditVoucher->value => 'قسيمة دائنة',
			self::DebitVoucher->value => 'قسيمة مدينة',
			self::PaymentVoucher->value => 'قسيمة الدفع',
			self::ReceiptVoucher->value => 'قسيمة الاستلام',
			self::ContraVoucher->value => 'قسيمة التحويل',

			// Contracts
			self::Contract->value => 'عقد',
			self::Agreement->value => 'اتفاق',
			self::PurchaseOrder->value => 'أمر شراء',
			self::SalesOrder->value => 'أمر بيع',
			self::WorkOrder->value => 'أمر عمل',
			self::ServiceOrder->value => 'أمر خدمة',

			// Delivery/Shipping
			self::DeliveryNote->value => 'إشعار التسليم',
			self::PackingSlip->value => 'قائمة التعبئة',
			self::ShippingDocument->value => 'مستند الشحن',
			self::BillOfLading->value => 'سند الشحن',
			self::Waybill->value => 'إذن الشحن',

			// Miscellaneous
			self::Refund->value => 'استرداد',
			self::Chargeback->value => 'استرداد الرسوم',
			self::Reimbursement->value => 'تعويض',
			self::Commission->value => 'عمولة',
			self::Royalty->value => 'إتاوة',
			self::Dividend->value => 'أرباح',
			self::Interest->value => 'فائدة',
			self::Depreciation->value => 'إهلاك',
			self::Amortization->value => 'إطفاء',

			// Other
			self::Other->value => 'آخر',
			self::Miscellaneous->value => 'متنوع',
			self::Unclassified->value => 'غير مصنف',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			// Core types
			self::Bill->value => 'חשבון',
			self::Payment->value => 'תשלום',
			self::BillCategory->value => 'קטגוריית חשבון',

			// Invoice/Statement
			self::Invoice->value => 'חשבונית',
			self::ProformaInvoice->value => 'חשבונית פרופורמה',
			self::RecurringInvoice->value => 'חשבונית חוזרת',
			self::CreditInvoice->value => 'חשבונית זכות',
			self::DebitInvoice->value => 'חשבונית חובה',
			self::Statement->value => 'דוח',
			self::Estimate->value => 'הערכה',
			self::Quote->value => 'הצעת מחיר',
			self::Proposal->value => 'הצעה',

			// Payment types
			self::AdvancePayment->value => 'תשלום מקדמי',
			self::PartialPayment->value => 'תשלום חלקי',
			self::FullPayment->value => 'תשלום מלא',
			self::Installment->value => 'תשלום בתשלומים',
			self::DownPayment->value => 'מקדמה',
			self::FinalPayment->value => 'תשלום סופי',
			self::CreditCardPayment->value => 'תשלום בכרטיס אשראי',
			self::BankTransfer->value => 'העברה בנקאית',
			self::CashPayment->value => 'תשלום במזומן',
			self::DigitalPayment->value => 'תשלום דיגיטלי',

			// Adjustments
			self::CreditNote->value => 'אשראי',
			self::DebitNote->value => 'חיוב',
			self::Adjustment->value => 'התאמה',
			self::CreditAdjustment->value => 'התאמת אשראי',
			self::DebitAdjustment->value => 'התאמת חיוב',
			self::WriteOff->value => 'ביטול',
			self::WriteOn->value => 'ביטול ביטול',

			// Discount/Tax
			self::Discount->value => 'הנחה',
			self::EarlyPaymentDiscount->value => 'הנחה לתשלום מוקדם',
			self::VolumeDiscount->value => 'הנחת נפח',
			self::PromotionalDiscount->value => 'הנחה לקידום מכירות',
			self::Tax->value => 'מס',
			self::SalesTax->value => 'מס מכירה',
			self::VAT->value => 'מע"מ',
			self::GST->value => 'GST',
			self::ServiceTax->value => 'מס שירות',
			self::WithholdingTax->value => 'מס ניכוי',

			// Fees
			self::Fee->value => 'עמלה',
			self::ServiceFee->value => 'עמלת שירות',
			self::ProcessingFee->value => 'עמלת עיבוד',
			self::TransactionFee->value => 'עמלת עסקה',
			self::LateFee->value => 'עמלת איחור',
			self::PenaltyFee->value => 'עמלת קנס',
			self::AdministrativeFee->value => 'עמלה אדמיניסטרטיבית',
			self::ConvenienceFee->value => 'עמלת נוחות',

			// Subscriptions
			self::Subscription->value => 'מנוי',
			self::RecurringBilling->value => 'חיוב חוזר',
			self::MembershipFee->value => 'דמי חבר',
			self::LicenseFee->value => 'דמי רישיון',
			self::MaintenanceFee->value => 'דמי תחזוקה',
			self::SubscriptionRenewal->value => 'חידוש מנוי',
			self::SubscriptionUpgrade->value => 'שדרוג מנוי',
			self::SubscriptionDowngrade->value => 'הורדת דרגת מנוי',

			// Financial Documents
			self::Receipt->value => 'קבלה',
			self::Voucher->value => 'שובר',
			self::CreditVoucher->value => 'שובר אשראי',
			self::DebitVoucher->value => 'שובר חיוב',
			self::PaymentVoucher->value => 'שובר תשלום',
			self::ReceiptVoucher->value => 'שובר קבלה',
			self::ContraVoucher->value => 'שובר העברה',

			// Contracts
			self::Contract->value => 'חוזה',
			self::Agreement->value => 'הסכם',
			self::PurchaseOrder->value => 'הזמנת רכש',
			self::SalesOrder->value => 'הזמנת מכירה',
			self::WorkOrder->value => 'הזמנת עבודה',
			self::ServiceOrder->value => 'הזמנת שירות',

			// Delivery/Shipping
			self::DeliveryNote->value => 'תעודת משלוח',
			self::PackingSlip->value => 'רשימת אריזה',
			self::ShippingDocument->value => 'מסמך משלוח',
			self::BillOfLading->value => 'שטר מטען',
			self::Waybill->value => 'תעודת משלוח',

			// Miscellaneous
			self::Refund->value => 'החזר',
			self::Chargeback->value => 'החזרת חיוב',
			self::Reimbursement->value => 'החזר הוצאות',
			self::Commission->value => 'עמלה',
			self::Royalty->value => 'תמלוגים',
			self::Dividend->value => 'דיבידנד',
			self::Interest->value => 'ריבית',
			self::Depreciation->value => 'פחת',
			self::Amortization->value => 'פירעון',

			// Other
			self::Other->value => 'אחר',
			self::Miscellaneous->value => 'שונות',
			self::Unclassified->value => 'לא מסווג',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			// Core types
			self::Bill->value => '請求書',
			self::Payment->value => '支払い',
			self::BillCategory->value => '請求書カテゴリ',

			// Invoice/Statement
			self::Invoice->value => '請求書',
			self::ProformaInvoice->value => '見積請求書',
			self::RecurringInvoice->value => '定期的な請求書',
			self::CreditInvoice->value => 'クレジット請求書',
			self::DebitInvoice->value => 'デビット請求書',
			self::Statement->value => '明細書',
			self::Estimate->value => '見積もり',
			self::Quote->value => '見積書',
			self::Proposal->value => '提案',

			// Payment types
			self::AdvancePayment->value => '前払い',
			self::PartialPayment->value => '一部支払い',
			self::FullPayment->value => '全額支払い',
			self::Installment->value => '分割払い',
			self::DownPayment->value => '頭金',
			self::FinalPayment->value => '最終支払い',
			self::CreditCardPayment->value => 'クレジットカード支払い',
			self::BankTransfer->value => '銀行振込',
			self::CashPayment->value => '現金支払い',
			self::DigitalPayment->value => 'デジタル支払い',

			// Adjustments
			self::CreditNote->value => 'クレジットノート',
			self::DebitNote->value => 'デビットノート',
			self::Adjustment->value => '調整',
			self::CreditAdjustment->value => 'クレジット調整',
			self::DebitAdjustment->value => 'デビット調整',
			self::WriteOff->value => '書き込み',
			self::WriteOn->value => '書き込み解除',

			// Discount/Tax
			self::Discount->value => '割引',
			self::EarlyPaymentDiscount->value => '早期支払い割引',
			self::VolumeDiscount->value => '数量割引',
			self::PromotionalDiscount->value => 'プロモーション割引',
			self::Tax->value => '税金',
			self::SalesTax->value => '売上税',
			self::VAT->value => '付加価値税',
			self::GST->value => 'GST',
			self::ServiceTax->value => 'サービス税',
			self::WithholdingTax->value => '源泉徴収税',

			// Fees
			self::Fee->value => '手数料',
			self::ServiceFee->value => 'サービス料',
			self::ProcessingFee->value => '処理手数料',
			self::TransactionFee->value => '取引手数料',
			self::LateFee->value => '延滞料',
			self::PenaltyFee->value => '罰金',
			self::AdministrativeFee->value => '管理手数料',
			self::ConvenienceFee->value => '利便料',

			// Subscriptions
			self::Subscription->value => 'サブスクリプション',
			self::RecurringBilling->value => '定期的な請求',
			self::MembershipFee->value => '会員費',
			self::LicenseFee->value => 'ライセンス料',
			self::MaintenanceFee->value => '保守料',
			self::SubscriptionRenewal->value => 'サブスクリプション更新',
			self::SubscriptionUpgrade->value => 'サブスクリプションアップグレード',
			self::SubscriptionDowngrade->value => 'サブスクリプションダウングレード',

			// Financial Documents
			self::Receipt->value => '領収書',
			self::Voucher->value => 'バウチャー',
			self::CreditVoucher->value => 'クレジットバウチャー',
			self::DebitVoucher->value => 'デビットバウチャー',
			self::PaymentVoucher->value => '支払いバウチャー',
			self::ReceiptVoucher->value => '受領バウチャー',
			self::ContraVoucher->value => '振替バウチャー',

			// Contracts
			self::Contract->value => '契約',
			self::Agreement->value => '合意',
			self::PurchaseOrder->value => '発注書',
			self::SalesOrder->value => '販売注文書',
			self::WorkOrder->value => '作業指示書',
			self::ServiceOrder->value => 'サービス注文書',

			// Delivery/Shipping
			self::DeliveryNote->value => '配送通知書',
			self::PackingSlip->value => '梱包明細書',
			self::ShippingDocument->value => '出荷書類',
			self::BillOfLading->value => '船荷証券',
			self::Waybill->value => '運送状',

			// Miscellaneous
			self::Refund->value => '返金',
			self::Chargeback->value => 'チャージバック',
			self::Reimbursement->value => '償還',
			self::Commission->value => '手数料',
			self::Royalty->value => 'ロイヤルティ',
			self::Dividend->value => '配当',
			self::Interest->value => '利息',
			self::Depreciation->value => '減価償却',
			self::Amortization->value => '償却',

			// Other
			self::Other->value => 'その他',
			self::Miscellaneous->value => 'その他',
			self::Unclassified->value => '未分類',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			// Core types
			self::Bill->value => 'Regning',
			self::Payment->value => 'Betaling',
			self::BillCategory->value => 'Regningskategori',

			// Invoice/Statement
			self::Invoice->value => 'Faktura',
			self::ProformaInvoice->value => 'Proforma Faktura',
			self::RecurringInvoice->value => 'Gentagende Faktura',
			self::CreditInvoice->value => 'Kreditfaktura',
			self::DebitInvoice->value => 'Debitfaktura',
			self::Statement->value => 'Kontoudtog',
			self::Estimate->value => 'Overslag',
			self::Quote->value => 'Tilbud',
			self::Proposal->value => 'Forslag',

			// Payment types
			self::AdvancePayment->value => 'Forudbetaling',
			self::PartialPayment->value => 'Delvis Betaling',
			self::FullPayment->value => 'Fuld Betaling',
			self::Installment->value => 'Afsnit',
			self::DownPayment->value => 'Udbetaling',
			self::FinalPayment->value => 'Endelig Betaling',
			self::CreditCardPayment->value => 'Kreditkort Betaling',
			self::BankTransfer->value => 'Bankoverførsel',
			self::CashPayment->value => 'Kontant Betaling',
			self::DigitalPayment->value => 'Digital Betaling',

			// Adjustments
			self::CreditNote->value => 'Kreditnota',
			self::DebitNote->value => 'Debitnota',
			self::Adjustment->value => 'Justering',
			self::CreditAdjustment->value => 'Kreditjustering',
			self::DebitAdjustment->value => 'Debitjustering',
			self::WriteOff->value => 'Afskrivning',
			self::WriteOn->value => 'Annullering af Afskrivning',

			// Discount/Tax
			self::Discount->value => 'Rabat',
			self::EarlyPaymentDiscount->value => 'Rabat for Tidlig Betaling',
			self::VolumeDiscount->value => 'Mængderabat',
			self::PromotionalDiscount->value => 'Promotionsrabat',
			self::Tax->value => 'Skat',
			self::SalesTax->value => 'Moms',
			self::VAT->value => 'Moms',
			self::GST->value => 'GST',
			self::ServiceTax->value => 'Servicesskat',
			self::WithholdingTax->value => 'Kildeskat',

			// Fees
			self::Fee->value => 'Gebyr',
			self::ServiceFee->value => 'Servicegebyr',
			self::ProcessingFee->value => 'Behandlingsgebyr',
			self::TransactionFee->value => 'Transaktionsgebyr',
			self::LateFee->value => 'Forsinkelsesgebyr',
			self::PenaltyFee->value => 'Bøde',
			self::AdministrativeFee->value => 'Administrationsgebyr',
			self::ConvenienceFee->value => 'Bekvemmelighedsgebyr',

			// Subscriptions
			self::Subscription->value => 'Abonnement',
			self::RecurringBilling->value => 'Gentagende Fakturering',
			self::MembershipFee->value => 'Medlemskontingent',
			self::LicenseFee->value => 'Licensgebyr',
			self::MaintenanceFee->value => 'Vedligeholdelsesgebyr',
			self::SubscriptionRenewal->value => 'Abonnementsfornyelse',
			self::SubscriptionUpgrade->value => 'Abonnementsopgradering',
			self::SubscriptionDowngrade->value => 'Abonnementsnedgradering',

			// Financial Documents
			self::Receipt->value => 'Kvittering',
			self::Voucher->value => 'Kupon',
			self::CreditVoucher->value => 'Kreditkupon',
			self::DebitVoucher->value => 'Debitkupon',
			self::PaymentVoucher->value => 'Betalingskupon',
			self::ReceiptVoucher->value => 'Modtagelseskupon',
			self::ContraVoucher->value => 'Overførselskupon',

			// Contracts
			self::Contract->value => 'Kontrakt',
			self::Agreement->value => 'Aftale',
			self::PurchaseOrder->value => 'Indkøbsordre',
			self::SalesOrder->value => 'Salgsordre',
			self::WorkOrder->value => 'Arbejdsordre',
			self::ServiceOrder->value => 'Serviceordre',

			// Delivery/Shipping
			self::DeliveryNote->value => 'Leveringsseddel',
			self::PackingSlip->value => 'Pakkeliste',
			self::ShippingDocument->value => 'Forsendelsesdokument',
			self::BillOfLading->value => 'Konossement',
			self::Waybill->value => 'Fragtseddel',

			// Miscellaneous
			self::Refund->value => 'Refusion',
			self::Chargeback->value => 'Tilbageførsel',
			self::Reimbursement->value => 'Godtgørelse',
			self::Commission->value => 'Provision',
			self::Royalty->value => 'Royalty',
			self::Dividend->value => 'Udbytte',
			self::Interest->value => 'Rente',
			self::Depreciation->value => 'Afskrivning',
			self::Amortization->value => 'Amortisering',

			// Other
			self::Other->value => 'Andet',
			self::Miscellaneous->value => 'Diverse',
			self::Unclassified->value => 'Ikke klassificeret',
		];
	}

	// Chinese Labels
	public static function labelsZh(): array
	{
		return [
			// Core types
			self::Bill->value => '账单',
			self::Payment->value => '付款',
			self::BillCategory->value => '账单类别',

			// Invoice/Statement
			self::Invoice->value => '发票',
			self::ProformaInvoice->value => '形式发票',
			self::RecurringInvoice->value => '定期发票',
			self::CreditInvoice->value => '贷项发票',
			self::DebitInvoice->value => '借项发票',
			self::Statement->value => '对账单',
			self::Estimate->value => '估算',
			self::Quote->value => '报价',
			self::Proposal->value => '提案',

			// Payment types
			self::AdvancePayment->value => '预付款',
			self::PartialPayment->value => '部分付款',
			self::FullPayment->value => '全额付款',
			self::Installment->value => '分期付款',
			self::DownPayment->value => '首付款',
			self::FinalPayment->value => '最终付款',
			self::CreditCardPayment->value => '信用卡付款',
			self::BankTransfer->value => '银行转账',
			self::CashPayment->value => '现金付款',
			self::DigitalPayment->value => '数字付款',

			// Adjustments
			self::CreditNote->value => '贷项通知单',
			self::DebitNote->value => '借项通知单',
			self::Adjustment->value => '调整',
			self::CreditAdjustment->value => '贷项调整',
			self::DebitAdjustment->value => '借项调整',
			self::WriteOff->value => '冲销',
			self::WriteOn->value => '冲销撤销',

			// Discount/Tax
			self::Discount->value => '折扣',
			self::EarlyPaymentDiscount->value => '提前付款折扣',
			self::VolumeDiscount->value => '数量折扣',
			self::PromotionalDiscount->value => '促销折扣',
			self::Tax->value => '税',
			self::SalesTax->value => '销售税',
			self::VAT->value => '增值税',
			self::GST->value => 'GST',
			self::ServiceTax->value => '服务税',
			self::WithholdingTax->value => '预扣税',

			// Fees
			self::Fee->value => '费用',
			self::ServiceFee->value => '服务费',
			self::ProcessingFee->value => '处理费',
			self::TransactionFee->value => '交易费',
			self::LateFee->value => '滞纳金',
			self::PenaltyFee->value => '罚款',
			self::AdministrativeFee->value => '行政费',
			self::ConvenienceFee->value => '便利费',

			// Subscriptions
			self::Subscription->value => '订阅',
			self::RecurringBilling->value => '定期账单',
			self::MembershipFee->value => '会员费',
			self::LicenseFee->value => '许可费',
			self::MaintenanceFee->value => '维护费',
			self::SubscriptionRenewal->value => '订阅续订',
			self::SubscriptionUpgrade->value => '订阅升级',
			self::SubscriptionDowngrade->value => '订阅降级',

			// Financial Documents
			self::Receipt->value => '收据',
			self::Voucher->value => '凭证',
			self::CreditVoucher->value => '贷项凭证',
			self::DebitVoucher->value => '借项凭证',
			self::PaymentVoucher->value => '付款凭证',
			self::ReceiptVoucher->value => '收款凭证',
			self::ContraVoucher->value => '转账凭证',

			// Contracts
			self::Contract->value => '合同',
			self::Agreement->value => '协议',
			self::PurchaseOrder->value => '采购订单',
			self::SalesOrder->value => '销售订单',
			self::WorkOrder->value => '工作订单',
			self::ServiceOrder->value => '服务订单',

			// Delivery/Shipping
			self::DeliveryNote->value => '送货单',
			self::PackingSlip->value => '装箱单',
			self::ShippingDocument->value => '运输单据',
			self::BillOfLading->value => '提单',
			self::Waybill->value => '运单',

			// Miscellaneous
			self::Refund->value => '退款',
			self::Chargeback->value => '退单',
			self::Reimbursement->value => '报销',
			self::Commission->value => '佣金',
			self::Royalty->value => '版税',
			self::Dividend->value => '股息',
			self::Interest->value => '利息',
			self::Depreciation->value => '折旧',
			self::Amortization->value => '摊销',

			// Other
			self::Other->value => '其他',
			self::Miscellaneous->value => '杂项',
			self::Unclassified->value => '未分类',
		];
	}
}
