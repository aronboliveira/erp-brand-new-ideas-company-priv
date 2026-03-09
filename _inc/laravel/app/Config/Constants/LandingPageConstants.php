<?php

namespace App\Config\Constants;

class LandingPageConstants
{
	// landing page
	public const COL_LPS_NM = 'name';
	public const COL_LPS_V = 'value';
	public const FAQ_STT_K = 'faq_status';
	// "{{ LandingPageConstants::FAQ_STT_K }}"
	// "{{ LPC::FAQ_STT_K }}"
	public const FAQ_TTL_K = 'faq_title';
	// "{{ LandingPageConstants::FAQ_TTL_K }}"
	// "{{ LPC::FAQ_TTL_K }}"
	public const FAQ_TTL_DEF = 'Faq';
	public const FAQ_HDG_K = 'faq_heading';
	public const FAQ_HDG_DEF = '';
	public const FAQ_DESC_K = 'faq_description';
	public const FAQ_DESC_DEF = '';
	public const FAQ_FQS_K = 'faqs';
	public const FAQ_FQS_DEF = '';
	public const TM_HDG_K = 'testimonials_heading';
	// "{{ LandingPageConstants::TM_HDG_K }}"
	// "{{ LPC::TM_HDG_K }}"
	public const TM_DESC_K = 'testimonials_description';
	// "{{ LandingPageConstants::TM_DESC_K }}"
	// "{{ LPC::TM_DESC_K }}"
	public const TM_DESC_DEF = '';
	public const TM_TTL_K = 'testimonials_title';
	public const TM_TTL_DEF = 'Testimonial';
	public const TM_STT_K = 'testimonials_status';
	public const TM_STT_DEF = 'on';
	public const TM_LONG_DESC_K = 'testimonials_long_description';
	public const TM_LONG_DESC_DEF = '';
	public const TM_TMS_K = 'testimonials';
	public const TM_TMS_DEF = '';
}
