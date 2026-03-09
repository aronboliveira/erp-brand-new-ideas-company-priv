@php
    try {
$lang = Utility::fetchUserLang() ?? app()->getLocale();
        $currEmailLang ??= $lang;
        $languages ??= is_callable([Utility::class,'languages']) ? Utility::languages() : [];
    } catch (\Throwable $e) {
        \Log::error('email_templates/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
    $lang ??= 'en';
    $currEmailLang ??= $lang;
    $languages ??= [];
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ !empty($emailTemplate?->name) ? $emailTemplate->name : __('No name available for template') }}
@endsection
@push(ST::ADM_CSS)
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
@endpush
@push(ST::ADM_SCR_PG)
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI_ACT }}" aria-current="page">{{ __('Email Template') }}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CL6 }}"></div>
        <div class="{{ VC::CL6 }}">
            <div class="{{ VC::TX_END }}">
                <div class="{{ VC::DFL_JCB }} drp-languages">
                    <ul class="list-unstyled {{ VC::MB0 }} m-2">
                        <li class="{{ VC::LNG_DD_IT }}">
                            <a class="{{ VC::EM_DRP_NO_ARROW }}" data-bs-toggle="dropdown"
                               href="#" role="button" aria-haspopup="false" aria-expanded="false"
                               id="dropdownLanguage">
                                <span class="email-color drp-text hide-mob {{ VC::TX_PM }} me-2">
                                    {{ !empty($LangName) ? ucfirst($LangName->full_name) : __('No name available for language') }}
                                </span>
                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                            </a>
                            <div class="{{ VC::DRP_MN_DSH_END }}" aria-labelledby="dropdownLanguage">
                                @if(Utility::isFilled($languages) && !empty($currEmailLang) && isset($currEmailLang->lang) && !empty($emailTemplate) && isset($emailTemplate->id) ?? [])
                                    @php
                                        try {
                                            $tplIdStr                = (string) data_get($emailTemplate ?? null, 'id', '');
                                            $manageBase              = VW::EMLS . '.manage.language';
                                            $manageKebab             = Str::kebab($manageBase);
                                            $manageResolved          = Route::has($manageBase) ? $manageBase : (Route::has($manageKebab) ? $manageKebab : null);
                                            $manageGuardMsg          = Utility::fetchLinkMessage($lang, VW::EMLS, 'email_template_manage_language_route_unavailable') ?? 'Manage email template language route is unavailable. Please contact technical support or your domain administrator.';
                                        } catch (\Throwable $e) {
                                            \Log::error('email_templates/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    @foreach ($languages as $code => $ln)
                                        @php
                                            try {
                                                $codeStr  = (string) $code;
                                                $href     = ($manageResolved && $tplIdStr !== '' && $codeStr !== '') ? route($manageResolved, [$tplIdStr, $codeStr]) : '#';
                                                $isActive = (string) data_get($currEmailTempLang ?? null, 'lang', '') === $codeStr;
                                                $lnkId    = 'email-template-lang-link-' . ($tplIdStr !== '' ? $tplIdStr : 'x') . '-' . ($codeStr !== '' ? $codeStr : 'xx');
                                            } catch (\Throwable $e) {
                                                \Log::error('email_templates/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <a
                                            id="{{ $lnkId }}"
                                            href="{{ $href }}"
                                            class="{{ VC::DRP_IT }} {{ $isActive ? 'text-primary' : '' }} email-template-lang-link"
                                            data-url="{{ $href }}"
                                            data-guard-msg="{{ base64_encode($manageGuardMsg) }}"
                                            data-sv-localized="true"
                                            {{ $href === '#' ? 'aria-disabled=true' : '' }}
                                        >
                                            {{ ucfirst($ln) }}
                                        </a>
                                    @endforeach
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/emailTemplates/manageLanguageSwitch.js') }}"></script>
                                    @endpush
                                @else
                                    {{ __('No languages available') }}
                                @endif
                            </div>
                        </li>
                    </ul>

                    <ul class="list-unstyled {{ VC::MB0 }} m-2">
                        <li class="{{ VC::LNG_DD_IT }}">
                            <a class="{{ VC::EM_DRP_NO_ARROW }}" data-bs-toggle="dropdown"
                               href="#" role="button" aria-haspopup="false" aria-expanded="false"
                               id="dropdownLanguage">
                                <span class="drp-text hide-mob {{ VC::TX_PM }}">
                                    {{ __('Template: ') }}{{ !empty($emailTemplate?->name) ? $emailTemplate->name : __('No name available for template') }}
                                </span>
                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                            </a>
                            <div class="{{ VC::DRP_MN_EM }}" aria-labelledby="dropdownLanguage">
                                @if(!empty($EmailTemplates) && ((is_array($EmailTemplates) && count($EmailTemplates)) || ($EmailTemplates instanceof Collection && $EmailTemplates->isNotEmpty())))
                                    @php
                                        try {
                                            $currentLang      = request()->segment(3) ?? $lang;
                                            $manageBase       = VW::EMLS . '.manage.language';
                                            $manageKebab      = Str::kebab($manageBase);
                                            $manageResolved   = Route::has($manageBase) ? $manageBase : (Route::has($manageKebab) ? $manageKebab : null);
                                            $manageGuardMsg   = Utility::fetchLinkMessage($lang, VW::EMLS, 'email_template_manage_language_route_unavailable')
                                                                ?? 'Manage email template language route is unavailable. Please contact technical support or your domain administrator.';
                                        } catch (\Throwable $e) {
                                            \Log::error('email_templates/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    @foreach ($EmailTemplates as $EmailTemplate)
                                        @php
                                            try {
                                                $tplId     = (string) ($EmailTemplate->id ?? '');
                                                $isActive  = ($EmailTemplate->name ?? '') === ($emailTemplate->name ?? '');
                                                $manageUrl = ($manageResolved && $tplId !== '')
                                                    ? route($manageResolved, [$tplId, $currentLang])
                                                    : '#';
                                                $lnkId     = 'email-template-manage-link-' . ($tplId !== '' ? $tplId : 'x');
                                            } catch (\Throwable $e) {
                                                \Log::error('email_templates/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <a
                                            id="{{ $lnkId }}"
                                            href="{{ $manageUrl }}"
                                            class="{{ VC::DRP_IT }} {{ $isActive ? 'text-primary' : '' }} email-template-manage-link"
                                            data-url="{{ $manageUrl }}"
                                            data-guard-msg="{{ base64_encode($manageGuardMsg) }}"
                                            data-sv-localized="true"
                                            {{ $manageUrl === '#' ? 'aria-disabled=true' : '' }}
                                        >
                                            {{ $EmailTemplate->name ?? __('Unnamed template') }}
                                        </a>
                                    @endforeach
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/emailTemplates/manageLanguage.js') }}"></script>
                                    @endpush
                                @else
                                    {{ __('No templates available') }}
                                @endif
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CXL12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD }}">
                    @if(!empty($currEmailTempLang) && isset($currEmailTempLang->parent_id))
                        @php
                            try {
                                $emailTplUpdateBaseRouteName    = VW::EMLS . '.update';
                                $emailTplUpdateKebabRouteName   = Str::kebab($emailTplUpdateBaseRouteName);
                                $emailTplUpdateResolvedName     = Route::has($emailTplUpdateBaseRouteName)
                                    ? $emailTplUpdateBaseRouteName
                                    : (Route::has($emailTplUpdateKebabRouteName) ? $emailTplUpdateKebabRouteName : null);
                                $emailTplParentId               = (string) data_get($currEmailTempLang, 'parent_id', '');
                                $emailTplUpdateUrl              = ($emailTplUpdateResolvedName && $emailTplParentId !== '')
                                    ? route($emailTplUpdateResolvedName, $emailTplParentId)
                                    : '#';
                                $emailTplUpdateFormId           = 'email-template-update-form-' . ($emailTplParentId !== '' ? $emailTplParentId : 'x');
                                $emailTplUpdateGuardMessage     = Utility::fetchLinkMessage($lang, VW::EMLS, 'email_template_update_route_unavailable')
                                    ?? 'Update email template route is unavailable. Please contact technical support or your domain administrator.';
                            } catch (\Throwable $e) {
                                \Log::error('email_templates/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        {{ Form::model($currEmailTempLang, [
                            'url'               => $emailTplUpdateUrl,
                            'method'            => 'PUT',
                            'id'                => $emailTplUpdateFormId,
                            'data-url'          => $emailTplUpdateUrl,
                            'data-guard-msg'    => $emailTplUpdateGuardMessage,
                            'data-sv-localized' => 'true',
                        ]) }}
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::CL12 }} {{ VC::CM12 }} {{ VC::CS12 }}">
                                    <h6 class="font-weight-bold pb-1">{{ __('Placeholders') }}</h6>
                                    <div class="{{ VC::CD }}">
                                        <div class="{{ VC::CD_BD }}">
                                            <div class="{{ VC::RW }} {{ VC::TXS }}">
                                                @if(!empty($emailTemplate) && !empty($emailTemplate->slug))
                                                    @if($emailTemplate->slug=='new_user')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Email') }} : <span class="pull-right {{ VC::TX_PM }}">{email}</span></p>
                                                            <p class="col-4">{{ __('Password') }} : <span class="pull-right {{ VC::TX_PM }}">{password}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='new_client')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Client Name') }} : <span class="pull-right {{ VC::TX_PM }}">{client_name}</span></p>
                                                            <p class="col-4">{{ __('Email') }} : <span class="pull-right {{ VC::TX_PM }}">{client_email}</span></p>
                                                            <p class="col-4">{{ __('Password') }} : <span class="pull-right {{ VC::TX_PM }}">{client_password}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='new_support_ticket')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('User Name') }} : <span class="pull-right {{ VC::TX_PM }}">{support_name}</span></p>
                                                            <p class="col-4">{{ __('Support Title') }} : <span class="pull-right {{ VC::TX_PM }}">{support_title}</span></p>
                                                            <p class="col-4">{{ __('Support Priority') }} : <span class="pull-right {{ VC::TX_PM }}">{support_priority}</span></p>
                                                            <p class="col-4">{{ __('Support End Date') }} : <span class="pull-right {{ VC::TX_PM }}">{support_end_date}</span></p>
                                                            <p class="col-4">{{ __('Support Description') }} : <span class="pull-right {{ VC::TX_PM }}">{support_description}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='new_contract')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Contract Subject') }} : <span class="pull-right {{ VC::TX_PM }}">{contract_subject}</span></p>
                                                            <p class="col-4">{{ __('Client Name') }} : <span class="pull-right {{ VC::TX_PM }}">{contract_client}</span></p>
                                                            <p class="col-4">{{ __('Contract Title') }} : <span class="pull-right {{ VC::TX_PM }}">{contract_value}</span></p>
                                                            <p class="col-4">{{ __('Contract Priority') }} : <span class="pull-right {{ VC::TX_PM }}">{contract_start_date}</span></p>
                                                            <p class="col-4">{{ __('Contract End Date') }} : <span class="pull-right {{ VC::TX_PM }}">{contract_end_date}</span></p>
                                                            <p class="col-4">{{ __('Contract Description') }} : <span class="pull-right {{ VC::TX_PM }}">{contract_description}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='lead_assigned')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('Lead Name') }} : <span class="pull-right {{ VC::TX_PM }}">{lead_name}</span></p>
                                                            <p class="col-4">{{ __('Lead Email') }} : <span class="pull-right {{ VC::TX_PM }}">{lead_email}</span></p>
                                                            <p class="col-4">{{ __('Lead Subject') }} : <span class="pull-right {{ VC::TX_PM }}">{lead_subject}</span></p>
                                                            <p class="col-4">{{ __('Lead Pipeline') }} : <span class="pull-right {{ VC::TX_PM }}">{lead_pipeline}</span></p>
                                                            <p class="col-4">{{ __('Lead Stage') }} : <span class="pull-right {{ VC::TX_PM }}">{lead_stage}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='deal_assigned')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('Deal Name') }} : <span class="pull-right {{ VC::TX_PM }}">{deal_name}</span></p>
                                                            <p class="col-4">{{ __('Deal Pipeline') }} : <span class="pull-right {{ VC::TX_PM }}">{deal_pipeline}</span></p>
                                                            <p class="col-4">{{ __('Deal Stage') }} : <span class="pull-right {{ VC::TX_PM }}">{deal_stage}</span></p>
                                                            <p class="col-4">{{ __('Deal Status') }} : <span class="pull-right {{ VC::TX_PM }}">{deal_status}</span></p>
                                                            <p class="col-4">{{ __('Deal Price') }} : <span class="pull-right {{ VC::TX_PM }}">{deal_price}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='award_sent')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Award Name') }} : <span class="pull-right {{ VC::TX_PM }}">{award_name}</span></p>
                                                            <p class="col-4">{{ __('Award Email') }} : <span class="pull-right {{ VC::TX_PM }}">{award_email}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='customer_invoice_sent')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Customer Name') }} : <span class="pull-right {{ VC::TX_PM }}">{customer_name}</span></p>
                                                            <p class="col-4">{{ __('Customer Email') }} : <span class="pull-right {{ VC::TX_PM }}">{customer_email}</span></p>
                                                            <p class="col-4">{{ __('Invoice Name') }} : <span class="pull-right {{ VC::TX_PM }}">{invoice_name}</span></p>
                                                            <p class="col-4">{{ __('Invoice Number') }} : <span class="pull-right {{ VC::TX_PM }}">{invoice_number}</span></p>
                                                            <p class="col-4">{{ __('Invoice Url') }} : <span class="pull-right {{ VC::TX_PM }}">{invoice_url}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='new_invoice_payment')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Customer Name') }} : <span class="pull-right {{ VC::TX_PM }}">{invoice_payment_name}</span></p>
                                                            <p class="col-4">{{ __('Invoice Payment') }} : <span class="pull-right {{ VC::TX_PM }}">{invoice_payment}</span></p>
                                                            <p class="col-4">{{ __('Invoice Payment Amount') }} : <span class="pull-right {{ VC::TX_PM }}">{invoice_payment_amount}</span></p>
                                                            <p class="col-4">{{ __('Invoice Payment Date') }} : <span class="pull-right {{ VC::TX_PM }}">{invoice_payment_date}</span></p>
                                                            <p class="col-4">{{ __('Invoice Payment Method') }} : <span class="pull-right {{ VC::TX_PM }}">{invoice_payment_method}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='new_payment_reminder')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Customer Name') }} : <span class="pull-right {{ VC::TX_PM }}">{customer_name}</span></p>
                                                            <p class="col-4">{{ __('Customer Email') }} : <span class="pull-right {{ VC::TX_PM }}">{customer_email}</span></p>
                                                            <p class="col-4">{{ __('Payment Reminder Name') }} : <span class="pull-right {{ VC::TX_PM }}">{payment_reminder_name}</span></p>
                                                            <p class="col-4">{{ __('Invoice Payment Number') }} : <span class="pull-right {{ VC::TX_PM }}">{invoice_payment_number}</span></p>
                                                            <p class="col-4">{{ __('Payment Due Amount') }} : <span class="pull-right {{ VC::TX_PM }}">{invoice_payment_dueAmount}</span></p>
                                                            <p class="col-4">{{ __('Payment Reminder Date') }} : <span class="pull-right {{ VC::TX_PM }}">{payment_reminder_date}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='new_bill_payment')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Payment Name') }} : <span class="pull-right {{ VC::TX_PM }}">{payment_name}</span></p>
                                                            <p class="col-4">{{ __('Payment Bill') }} : <span class="pull-right {{ VC::TX_PM }}">{payment_bill}</span></p>
                                                            <p class="col-4">{{ __('Payment Amount') }} : <span class="pull-right {{ VC::TX_PM }}">{payment_amount}</span></p>
                                                            <p class="col-4">{{ __('Payment Date') }} : <span class="pull-right {{ VC::TX_PM }}">{payment_date}</span></p>
                                                            <p class="col-4">{{ __('Payment Method') }} : <span class="pull-right {{ VC::TX_PM }}">{payment_method}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='bill_resent')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Vendor Name') }} : <span class="pull-right {{ VC::TX_PM }}">{vendor_name}</span></p>
                                                            <p class="col-4">{{ __('Vendor Email') }} : <span class="pull-right {{ VC::TX_PM }}">{vendor_email}</span></p>
                                                            <p class="col-4">{{ __('Bill Name') }} : <span class="pull-right {{ VC::TX_PM }}">{bill_name}</span></p>
                                                            <p class="col-4">{{ __('Bill Identifier') }} : <span class="pull-right {{ VC::TX_PM }}">{bill_id}</span></p>
                                                            <p class="col-4">{{ __('Bill Url') }} : <span class="pull-right {{ VC::TX_PM }}">{bill_url}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='proposal_sent')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Proposal Name') }} : <span class="pull-right {{ VC::TX_PM }}">{proposal_name}</span></p>
                                                            <p class="col-4">{{ __('Proposal Email') }} : <span class="pull-right {{ VC::TX_PM }}">{proposal_number}</span></p>
                                                            <p class="col-4">{{ __('Proposal Url') }} : <span class="pull-right {{ VC::TX_PM }}">{proposal_url}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='complaint_resent')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Complaint Name') }} : <span class="pull-right {{ VC::TX_PM }}">{complaint_name}</span></p>
                                                            <p class="col-4">{{ __('Complaint Title') }} : <span class="pull-right {{ VC::TX_PM }}">{complaint_title}</span></p>
                                                            <p class="col-4">{{ __('Complaint Against') }} : <span class="pull-right {{ VC::TX_PM }}">{complaint_against}</span></p>
                                                            <p class="col-4">{{ __('Complaint Date') }} : <span class="pull-right {{ VC::TX_PM }}">{complaint_date}</span></p>
                                                            <p class="col-4">{{ __('Complaint Date') }} : <span class="pull-right {{ VC::TX_PM }}">{complaint_description}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='leave_action_sent')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Leave Name') }} : <span class="pull-right {{ VC::TX_PM }}">{leave_name}</span></p>
                                                            <p class="col-4">{{ __('Leave Status') }} : <span class="pull-right {{ VC::TX_PM }}">{leave_status}</span></p>
                                                            <p class="col-4">{{ __('Leave Reason') }} : <span class="pull-right {{ VC::TX_PM }}">{leave_reason}</span></p>
                                                            <p class="col-4">{{ __('Leave Start Date') }} : <span class="pull-right {{ VC::TX_PM }}">{leave_start_date}</span></p>
                                                            <p class="col-4">{{ __('Leave End Date') }} : <span class="pull-right {{ VC::TX_PM }}">{leave_end_date}</span></p>
                                                            <p class="col-4">{{ __('Leave Days') }} : <span class="pull-right {{ VC::TX_PM }}">{total_leave_days}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='payslip_sent')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Employee Name') }} : <span class="pull-right {{ VC::TX_PM }}">{employee_name}</span></p>
                                                            <p class="col-4">{{ __('Employee Email') }} : <span class="pull-right {{ VC::TX_PM }}">{employee_email}</span></p>
                                                            <p class="col-4">{{ __('Payslip Name') }} : <span class="pull-right {{ VC::TX_PM }}">{payslip_name}</span></p>
                                                            <p class="col-4">{{ __('Payslip Salary Month ') }} : <span class="pull-right {{ VC::TX_PM }}">{payslip_salary_month}</span></p>
                                                            <p class="col-4">{{ __('Payslip Url') }} : <span class="pull-right {{ VC::TX_PM }}">{payslip_url}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='promotion_sent')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Employee Name') }} : <span class="pull-right {{ VC::TX_PM }}">{employee_name}</span></p>
                                                            <p class="col-4">{{ __('Designation') }} : <span class="pull-right {{ VC::TX_PM }}">{promotion_designation}</span></p>
                                                            <p class="col-4">{{ __('Promotion Title') }} : <span class="pull-right {{ VC::TX_PM }}">{promotion_title}</span></p>
                                                            <p class="col-4">{{ __('Promotion Date') }} : <span class="pull-right {{ VC::TX_PM }}">{promotion_date}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='resignation_sent')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Employee Email') }} : <span class="pull-right {{ VC::TX_PM }}">{resignation_email}</span></p>
                                                            <p class="col-4">{{ __('Employee Name') }} : <span class="pull-right {{ VC::TX_PM }}">{assign_user}</span></p>
                                                            <p class="col-4">{{ __('Last Working Date') }} : <span class="pull-right {{ VC::TX_PM }}">{resignation_date}</span></p>
                                                            <p class="col-4">{{ __('Resignation Date') }} : <span class="pull-right {{ VC::TX_PM }}">{notice_date}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='termination_sent')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Employee Name') }} : <span class="pull-right {{ VC::TX_PM }}">{termination_name}</span></p>
                                                            <p class="col-4">{{ __('Employee Email') }} : <span class="pull-right {{ VC::TX_PM }}">{termination_email}</span></p>
                                                            <p class="col-4">{{ __('Notice Date') }} : <span class="pull-right {{ VC::TX_PM }}">{notice_date}</span></p>
                                                            <p class="col-4">{{ __('Termination Date') }} : <span class="pull-right {{ VC::TX_PM }}">{termination_date}</span></p>
                                                            <p class="col-4">{{ __('Termination Type') }} : <span class="pull-right {{ VC::TX_PM }}">{termination_type}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='transfer_sent')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Employee Name') }} : <span class="pull-right {{ VC::TX_PM }}">{transfer_name}</span></p>
                                                            <p class="col-4">{{ __('Employee Email') }} : <span class="pull-right {{ VC::TX_PM }}">{transfer_email}</span></p>
                                                            <p class="col-4">{{ __('Transfer Date') }} : <span class="pull-right {{ VC::TX_PM }}">{transfer_date}</span></p>
                                                            <p class="col-4">{{ __('Transfer Department') }} : <span class="pull-right {{ VC::TX_PM }}">{transfer_department}</span></p>
                                                            <p class="col-4">{{ __('Transfer Branch') }} : <span class="pull-right {{ VC::TX_PM }}">{transfer_branch}</span></p>
                                                            <p class="col-4">{{ __('Transfer Desciption') }} : <span class="pull-right {{ VC::TX_PM }}">{transfer_description}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='trip_sent')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Employee ') }} : <span class="pull-right {{ VC::TX_PM }}">{trip_name}</span></p>
                                                            <p class="col-4">{{ __('Purpose of Trip') }} : <span class="pull-right {{ VC::TX_PM }}">{purpose_of_visit}</span></p>
                                                            <p class="col-4">{{ __('Start Date') }} : <span class="pull-right {{ VC::TX_PM }}">{start_date}</span></p>
                                                            <p class="col-4">{{ __('End Date') }} : <span class="pull-right {{ VC::TX_PM }}">{end_date}</span></p>
                                                            <p class="col-4">{{ __('Country') }} : <span class="pull-right {{ VC::TX_PM }}">{place_of_visit}</span></p>
                                                            <p class="col-4">{{ __('Description') }} : <span class="pull-right {{ VC::TX_PM }}">{trip_description}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='vendor_bill_sent')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Vendor Name') }} : <span class="pull-right {{ VC::TX_PM }}">{vendor_bill_name}</span></p>
                                                            <p class="col-4">{{ __('Bill Identifier') }} : <span class="pull-right {{ VC::TX_PM }}">{vendor_bill_id}</span></p>
                                                            <p class="col-4">{{ __('Bill Url') }} : <span class="pull-right {{ VC::TX_PM }}">{vendor_bill_url}</span></p>
                                                        </div>
                                                    @elseif($emailTemplate->slug=='warning_sent')
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('App Name') }} : <span class="pull-end {{ VC::TX_PM }}">{app_name}</span></p>
                                                            <p class="col-4">{{ __('Company Name') }} : <span class="pull-right {{ VC::TX_PM }}">{company_name}</span></p>
                                                            <p class="col-4">{{ __('App Url') }} : <span class="pull-right {{ VC::TX_PM }}">{app_url}</span></p>
                                                            <p class="col-4">{{ __('Employee Name') }} : <span class="pull-right {{ VC::TX_PM }}">{employee_warning_name}</span></p>
                                                            <p class="col-4">{{ __('Subject') }} : <span class="pull-right {{ VC::TX_PM }}">{warning_subject}</span></p>
                                                            <p class="col-4">{{ __('Description') }} : <span class="pull-right {{ VC::TX_PM }}">{warning_description}</span></p>
                                                        </div>
                                                    @else
                                                        <div class="{{ VC::RW }}">
                                                            <p class="col-4">{{ __('Unrecognized template') }} : <span class="pull-end {{ VC::TX_PM }}">{{ __('No name available.') }}</span></p>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="{{ VC::RW }}">
                                                        <p class="col-4">{{ __('Failed to load email template') }} : <span class="pull-end {{ VC::TX_PM }}">{{ __('No name available.') }}</span></p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="{{ VC::CM6 }}">
                                    {{ Form::label('subject', __('Subject'), ['class' => 'col-form-label text-dark']) }}
                                    {{ Form::text('subject', null, ['class' => VC::FM_CT . ' font-style', 'required' => 'required']) }}
                                </div>

                                <div class="{{ VC::CM6 }}">
                                    {{ Form::label('from', __('From'), ['class' => 'col-form-label text-dark']) }}
                                    {{ Form::text('from', $emailTemplate->from, ['class' => VC::FM_CT . ' font-style', 'required' => 'required']) }}
                                </div>

                                <div class="{{ VC::C12 }}">
                                    {{ Form::label('content', __('Email Message'), ['class' => 'col-form-label text-dark']) }}
                                    {{ Form::textarea('content', $currEmailTempLang->content, ['class' => 'summernote-simple', 'required' => 'required']) }}
                                </div>

                                <div class="modal-footer">
                                    {{ Form::hidden('lang', null) }}
                                    {{ Form::submit(__('Save Changes'), ['class' => VC::BT_XS_PM]) }}
                                </div>

                            </div>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/emailTemplates/update.js') }}"></script>
                            @endpush
                        {{ Form::close() }}
                    @else
                        <div>{{ __('The current email template could not be found.') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
