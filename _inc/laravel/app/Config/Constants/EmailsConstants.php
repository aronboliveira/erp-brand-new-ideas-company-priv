<?php

namespace App\Config\Constants;

class EmailsConstants
{
	public const COL_TT  = 'title';
	public const COL_TMP	= 'template_id';
	public const COL_SLG 	= 'slug';
	public const COL_FROM	= 'from';
	public const COL_FROM_ID = 'from_id';
	public const COL_TO_ID = 'to_id';
	public const COL_D_URL = 'document_url';
	public const COL_EM  = 'email';
	public const COL_EM_KEY = 'email_key';
	public const COL_ATC = 'attachments';
	public const COL_IA = 'is_active';
	public const COL_PRT_ID = 'parent_id';
	public const STATUS_MAP = [
		'new_user' => 'New User',
		'new_client' => 'New Client',
		'new_support_ticket' => 'New Support Ticket',
		'lead_assigned' => 'Lead Assigned',
		'deal_assigned' => 'Deal Assigned',
		'new_award' => 'New Award',
		'customer_invoice_sent' => 'Customer Invoice Sent',
		'new_invoice_payment' => 'New Invoice Payment',
		'new_payment_reminder' => 'New Payment Reminder',
		'new_bill_payment' => 'New Bill Payment',
		'bill_resent' => 'Bill Resent',
		'proposal_sent' => 'Proposal Sent',
		'complaint_resent' => 'Complaint Resent',
		'leave_action_sent' => 'Leave Action Sent',
		'payslip_sent' => 'Payslip Sent',
		'promotion_sent' => 'Promotion Sent',
		'resignation_sent' => 'Resignation Sent',
		'termination_sent' => 'Termination Sent',
		'transfer_sent' => 'Transfer Sent',
		'trip_sent' => 'Trip Sent',
		'vendor_bill_sent' => 'Vendor Bill Sent',
		'warning_sent' => 'Warning Sent',
		'new_contract' => 'New Contract',
	];
}
