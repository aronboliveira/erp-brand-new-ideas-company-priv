<?php

use App\Config\Constants\{
    ExtendingLayoutsConstants,
    StacksConstants as ST,
    ViewsConstants as VW,
    ViewClassNamesConstants as VC,
    YieldingConstants,
};
use Illuminate\Support\Facades\Route;
use Illuminate\Support\{Collection, Str};
use Collective\Html\FormFacade as Form;
use App\Models\Utility;

$lang = Utility::fetchUserLang() ?? app()->getLocale();
$currEmailLang ??= $lang;
$languages ??= is_callable([Utility::class, 'languages']) ? Utility::languages() : [];
?>

<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
<?php echo e(!empty($emailTemplate?->name) ? $emailTemplate->name : __('No name available for template')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startPush(ST::ADM_CSS); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/summernote/summernote-bs4.css')); ?>">
<?php $__env->stopPush(); ?>
<?php $__env->startPush(ST::ADM_SCR_PG); ?>
<script src="<?php echo e(asset('css/summernote/summernote-bs4.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
<li class="breadcrumb-item">
    <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
        <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
        <?php echo e(__('Dashboard')); ?>

    </a>
</li>
<li class="breadcrumb-item active" aria-current="page"><?php echo e(__('Email Template')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
<div class="<?php echo e(VC::RW); ?>">
    <div class="<?php echo e(VC::CL6); ?>"></div>
    <div class="<?php echo e(VC::CL6); ?>">
        <div class="text-end">
            <div class="<?php echo e(VC::DFL_JCB); ?> drp-languages">
                <ul class="list-unstyled <?php echo e(VC::MB0); ?> m-2">
                    <li class="<?php echo e(VC::LNG_DD_IT); ?>">
                        <a class="<?php echo e(VC::EM_DRP_NO_ARROW); ?>" data-bs-toggle="dropdown"
                            href="#" role="button" aria-haspopup="false" aria-expanded="false"
                            id="dropdownLanguage">
                            <span class="email-color drp-text hide-mob text-primary me-2">
                                <?php echo e(!empty($LangName) ? ucfirst($LangName->full_name) : __('No name available for language')); ?>

                            </span>
                            <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                        </a>
                        <div class="<?php echo e(VC::DRP_MN_DSH_END); ?>" aria-labelledby="dropdownLanguage">
                            <?php if (Utility::isFilled($languages) && !empty($currEmailLang) && isset($currEmailLang->lang) && !empty($emailTemplate) && isset($emailTemplate->id) ?? []): ?>
                                <?php
                                $tplIdStr                = (string) data_get($emailTemplate ?? null, 'id', '');
                                $manageBase              = VW::EMLS . '.manage.language';
                                $manageKebab             = Str::kebab($manageBase);
                                $manageResolved          = Route::has($manageBase) ? $manageBase : (Route::has($manageKebab) ? $manageKebab : null);
                                $manageGuardMsg          = Utility::fetchLinkMessage($lang, VW::EMLS, 'email_template_manage_language_route_unavailable') ?? 'Manage email template language route is unavailable. Please contact technical support or your domain administrator.';
                                ?>
                                <?php $__currentLoopData = $languages;
                                $__env->addLoop($__currentLoopData);
                                foreach ($__currentLoopData as $code => $ln): $__env->incrementLoopIndices();
                                    $loop = $__env->getLastLoop(); ?>
                                    <?php
                                    $codeStr  = (string) $code;
                                    $href     = ($manageResolved && $tplIdStr !== '' && $codeStr !== '') ? route($manageResolved, [$tplIdStr, $codeStr]) : '#';
                                    $isActive = (string) data_get($currEmailTempLang ?? null, 'lang', '') === $codeStr;
                                    $lnkId    = 'email-template-lang-link-' . ($tplIdStr !== '' ? $tplIdStr : 'x') . '-' . ($codeStr !== '' ? $codeStr : 'xx');
                                    ?>
                                    <a
                                        id="<?php echo e($lnkId); ?>"
                                        href="<?php echo e($href); ?>"
                                        class="dropdown-item <?php echo e($isActive ? 'text-primary' : ''); ?> email-template-lang-link"
                                        data-url="<?php echo e($href); ?>"
                                        data-guard-msg="<?php echo e($manageGuardMsg); ?>"
                                        data-sv-localized="true"
                                        <?php echo e($href === '#' ? 'aria-disabled=true' : ''); ?>>
                                        <?php echo e(ucfirst($ln)); ?>

                                    </a>
                                <?php endforeach;
                                $__env->popLoop();
                                $loop = $__env->getLastLoop(); ?>
                                <?php $__env->startPush(ST::ADM_SCR_PG); ?>
                                <script defer src="<?php echo e(asset('assets/js/routes/emailTemplates/manageLanguageSwitch.js')); ?>"></script>
                                <?php $__env->stopPush(); ?>
                            <?php else: ?>
                                <?php echo e(__('No languages available')); ?>

                            <?php endif; ?>
                        </div>
                    </li>
                </ul>

                <ul class="list-unstyled <?php echo e(VC::MB0); ?> m-2">
                    <li class="<?php echo e(VC::LNG_DD_IT); ?>">
                        <a class="<?php echo e(VC::EM_DRP_NO_ARROW); ?>" data-bs-toggle="dropdown"
                            href="#" role="button" aria-haspopup="false" aria-expanded="false"
                            id="dropdownLanguage">
                            <span class="drp-text hide-mob text-primary">
                                <?php echo e(__('Template: ')); ?><?php echo e(!empty($emailTemplate?->name) ? $emailTemplate->name : __('No name available for template')); ?>

                            </span>
                            <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                        </a>
                        <div class="<?php echo e(VC::DRP_MN_EM); ?>" aria-labelledby="dropdownLanguage">
                            <?php if (!empty($EmailTemplates) && ((is_array($EmailTemplates) && count($EmailTemplates)) || ($EmailTemplates instanceof Collection && $EmailTemplates->isNotEmpty()))): ?>
                                <?php
                                $currentLang      = request()->segment(3) ?? $lang;
                                $manageBase       = VW::EMLS . '.manage.language';
                                $manageKebab      = Str::kebab($manageBase);
                                $manageResolved   = Route::has($manageBase) ? $manageBase : (Route::has($manageKebab) ? $manageKebab : null);
                                $manageGuardMsg   = Utility::fetchLinkMessage($lang, VW::EMLS, 'email_template_manage_language_route_unavailable')
                                    ?? 'Manage email template language route is unavailable. Please contact technical support or your domain administrator.';
                                ?>
                                <?php $__currentLoopData = $EmailTemplates;
                                $__env->addLoop($__currentLoopData);
                                foreach ($__currentLoopData as $EmailTemplate): $__env->incrementLoopIndices();
                                    $loop = $__env->getLastLoop(); ?>
                                    <?php
                                    $tplId     = (string) ($EmailTemplate->id ?? '');
                                    $isActive  = ($EmailTemplate->name ?? '') === ($emailTemplate->name ?? '');
                                    $manageUrl = ($manageResolved && $tplId !== '')
                                        ? route($manageResolved, [$tplId, $currentLang])
                                        : '#';
                                    $lnkId     = 'email-template-manage-link-' . ($tplId !== '' ? $tplId : 'x');
                                    ?>
                                    <a
                                        id="<?php echo e($lnkId); ?>"
                                        href="<?php echo e($manageUrl); ?>"
                                        class="dropdown-item <?php echo e($isActive ? 'text-primary' : ''); ?> email-template-manage-link"
                                        data-url="<?php echo e($manageUrl); ?>"
                                        data-guard-msg="<?php echo e($manageGuardMsg); ?>"
                                        data-sv-localized="true"
                                        <?php echo e($manageUrl === '#' ? 'aria-disabled=true' : ''); ?>>
                                        <?php echo e($EmailTemplate->name ?? __('Unnamed template')); ?>

                                    </a>
                                <?php endforeach;
                                $__env->popLoop();
                                $loop = $__env->getLastLoop(); ?>
                                <?php $__env->startPush(ST::ADM_SCR_PG); ?>
                                <script defer src="<?php echo e(asset('assets/js/routes/emailTemplates/manageLanguage.js')); ?>"></script>
                                <?php $__env->stopPush(); ?>
                            <?php else: ?>
                                <?php echo e(__('No templates available')); ?>

                            <?php endif; ?>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
<div class="<?php echo e(VC::RW); ?>">
    <div class="col-xl-12">
        <div class="<?php echo e(VC::CD); ?>">
            <div class="card-body">
                <?php if (!empty($currEmailTempLang) && isset($currEmailTempLang->parent_id)): ?>
                    <?php
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
                    ?>
                    <?php echo e(Form::model($currEmailTempLang, [
                        'url'               => $emailTplUpdateUrl,
                        'method'            => 'PUT',
                        'id'                => $emailTplUpdateFormId,
                        'data-url'          => $emailTplUpdateUrl,
                        'data-guard-msg'    => $emailTplUpdateGuardMessage,
                        'data-sv-localized' => 'true',
                    ])); ?>

                    <div class="<?php echo e(VC::RW); ?>">
                        <div class="<?php echo e(VC::CL12); ?> <?php echo e(VC::CM12); ?> <?php echo e(VC::CS12); ?>">
                            <h6 class="font-weight-bold pb-1"><?php echo e(__('Placeholders')); ?></h6>
                            <div class="<?php echo e(VC::CD); ?>">
                                <div class="card-body">
                                    <div class="<?php echo e(VC::RW); ?> <?php echo e(VC::TXS); ?>">
                                        <?php if (!empty($emailTemplate) && !empty($emailTemplate->slug)): ?>
                                            <?php if ($emailTemplate->slug == 'new_user'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Email')); ?> : <span class="pull-right text-primary">{email}</span></p>
                                                    <p class="col-4"><?php echo e(__('Password')); ?> : <span class="pull-right text-primary">{password}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'new_client'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Client Name')); ?> : <span class="pull-right text-primary">{client_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Email')); ?> : <span class="pull-right text-primary">{client_email}</span></p>
                                                    <p class="col-4"><?php echo e(__('Password')); ?> : <span class="pull-right text-primary">{client_password}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'new_support_ticket'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('User Name')); ?> : <span class="pull-right text-primary">{support_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Support Title')); ?> : <span class="pull-right text-primary">{support_title}</span></p>
                                                    <p class="col-4"><?php echo e(__('Support Priority')); ?> : <span class="pull-right text-primary">{support_priority}</span></p>
                                                    <p class="col-4"><?php echo e(__('Support End Date')); ?> : <span class="pull-right text-primary">{support_end_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Support Description')); ?> : <span class="pull-right text-primary">{support_description}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'new_contract'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Contract Subject')); ?> : <span class="pull-right text-primary">{contract_subject}</span></p>
                                                    <p class="col-4"><?php echo e(__('Client Name')); ?> : <span class="pull-right text-primary">{contract_client}</span></p>
                                                    <p class="col-4"><?php echo e(__('Contract Title')); ?> : <span class="pull-right text-primary">{contract_value}</span></p>
                                                    <p class="col-4"><?php echo e(__('Contract Priority')); ?> : <span class="pull-right text-primary">{contract_start_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Contract End Date')); ?> : <span class="pull-right text-primary">{contract_end_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Contract Description')); ?> : <span class="pull-right text-primary">{contract_description}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'lead_assigned'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('Lead Name')); ?> : <span class="pull-right text-primary">{lead_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Lead Email')); ?> : <span class="pull-right text-primary">{lead_email}</span></p>
                                                    <p class="col-4"><?php echo e(__('Lead Subject')); ?> : <span class="pull-right text-primary">{lead_subject}</span></p>
                                                    <p class="col-4"><?php echo e(__('Lead Pipeline')); ?> : <span class="pull-right text-primary">{lead_pipeline}</span></p>
                                                    <p class="col-4"><?php echo e(__('Lead Stage')); ?> : <span class="pull-right text-primary">{lead_stage}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'deal_assigned'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('Deal Name')); ?> : <span class="pull-right text-primary">{deal_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Deal Pipeline')); ?> : <span class="pull-right text-primary">{deal_pipeline}</span></p>
                                                    <p class="col-4"><?php echo e(__('Deal Stage')); ?> : <span class="pull-right text-primary">{deal_stage}</span></p>
                                                    <p class="col-4"><?php echo e(__('Deal Status')); ?> : <span class="pull-right text-primary">{deal_status}</span></p>
                                                    <p class="col-4"><?php echo e(__('Deal Price')); ?> : <span class="pull-right text-primary">{deal_price}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'award_sent'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Award Name')); ?> : <span class="pull-right text-primary">{award_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Award Email')); ?> : <span class="pull-right text-primary">{award_email}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'customer_invoice_sent'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Customer Name')); ?> : <span class="pull-right text-primary">{customer_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Customer Email')); ?> : <span class="pull-right text-primary">{customer_email}</span></p>
                                                    <p class="col-4"><?php echo e(__('Invoice Name')); ?> : <span class="pull-right text-primary">{invoice_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Invoice Number')); ?> : <span class="pull-right text-primary">{invoice_number}</span></p>
                                                    <p class="col-4"><?php echo e(__('Invoice Url')); ?> : <span class="pull-right text-primary">{invoice_url}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'new_invoice_payment'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Customer Name')); ?> : <span class="pull-right text-primary">{invoice_payment_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Invoice Payment')); ?> : <span class="pull-right text-primary">{invoice_payment}</span></p>
                                                    <p class="col-4"><?php echo e(__('Invoice Payment Amount')); ?> : <span class="pull-right text-primary">{invoice_payment_amount}</span></p>
                                                    <p class="col-4"><?php echo e(__('Invoice Payment Date')); ?> : <span class="pull-right text-primary">{invoice_payment_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Invoice Payment Method')); ?> : <span class="pull-right text-primary">{invoice_payment_method}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'new_payment_reminder'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Customer Name')); ?> : <span class="pull-right text-primary">{customer_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Customer Email')); ?> : <span class="pull-right text-primary">{customer_email}</span></p>
                                                    <p class="col-4"><?php echo e(__('Payment Reminder Name')); ?> : <span class="pull-right text-primary">{payment_reminder_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Invoice Payment Number')); ?> : <span class="pull-right text-primary">{invoice_payment_number}</span></p>
                                                    <p class="col-4"><?php echo e(__('Payment Due Amount')); ?> : <span class="pull-right text-primary">{invoice_payment_dueAmount}</span></p>
                                                    <p class="col-4"><?php echo e(__('Payment Reminder Date')); ?> : <span class="pull-right text-primary">{payment_reminder_date}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'new_bill_payment'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Payment Name')); ?> : <span class="pull-right text-primary">{payment_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Payment Bill')); ?> : <span class="pull-right text-primary">{payment_bill}</span></p>
                                                    <p class="col-4"><?php echo e(__('Payment Amount')); ?> : <span class="pull-right text-primary">{payment_amount}</span></p>
                                                    <p class="col-4"><?php echo e(__('Payment Date')); ?> : <span class="pull-right text-primary">{payment_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Payment Method')); ?> : <span class="pull-right text-primary">{payment_method}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'bill_resent'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Vendor Name')); ?> : <span class="pull-right text-primary">{vendor_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Vendor Email')); ?> : <span class="pull-right text-primary">{vendor_email}</span></p>
                                                    <p class="col-4"><?php echo e(__('Bill Name')); ?> : <span class="pull-right text-primary">{bill_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Bill Number')); ?> : <span class="pull-right text-primary">{bill_number}</span></p>
                                                    <p class="col-4"><?php echo e(__('Bill Url')); ?> : <span class="pull-right text-primary">{bill_url}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'proposal_sent'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Proposal Name')); ?> : <span class="pull-right text-primary">{proposal_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Proposal Email')); ?> : <span class="pull-right text-primary">{proposal_number}</span></p>
                                                    <p class="col-4"><?php echo e(__('Proposal Url')); ?> : <span class="pull-right text-primary">{proposal_url}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'complaint_resent'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Complaint Name')); ?> : <span class="pull-right text-primary">{complaint_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Complaint Title')); ?> : <span class="pull-right text-primary">{complaint_title}</span></p>
                                                    <p class="col-4"><?php echo e(__('Complaint Against')); ?> : <span class="pull-right text-primary">{complaint_against}</span></p>
                                                    <p class="col-4"><?php echo e(__('Complaint Date')); ?> : <span class="pull-right text-primary">{complaint_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Complaint Date')); ?> : <span class="pull-right text-primary">{complaint_description}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'leave_action_sent'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Leave Name')); ?> : <span class="pull-right text-primary">{leave_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Leave Status')); ?> : <span class="pull-right text-primary">{leave_status}</span></p>
                                                    <p class="col-4"><?php echo e(__('Leave Reason')); ?> : <span class="pull-right text-primary">{leave_reason}</span></p>
                                                    <p class="col-4"><?php echo e(__('Leave Start Date')); ?> : <span class="pull-right text-primary">{leave_start_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Leave End Date')); ?> : <span class="pull-right text-primary">{leave_end_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Leave Days')); ?> : <span class="pull-right text-primary">{total_leave_days}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'payslip_sent'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Employee Name')); ?> : <span class="pull-right text-primary">{employee_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Employee Email')); ?> : <span class="pull-right text-primary">{employee_email}</span></p>
                                                    <p class="col-4"><?php echo e(__('Payslip Name')); ?> : <span class="pull-right text-primary">{payslip_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Payslip Salary Month ')); ?> : <span class="pull-right text-primary">{payslip_salary_month}</span></p>
                                                    <p class="col-4"><?php echo e(__('Payslip Url')); ?> : <span class="pull-right text-primary">{payslip_url}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'promotion_sent'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Employee Name')); ?> : <span class="pull-right text-primary">{employee_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Designation')); ?> : <span class="pull-right text-primary">{promotion_designation}</span></p>
                                                    <p class="col-4"><?php echo e(__('Promotion Title')); ?> : <span class="pull-right text-primary">{promotion_title}</span></p>
                                                    <p class="col-4"><?php echo e(__('Promotion Date')); ?> : <span class="pull-right text-primary">{promotion_date}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'resignation_sent'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Employee Email')); ?> : <span class="pull-right text-primary">{resignation_email}</span></p>
                                                    <p class="col-4"><?php echo e(__('Employee Name')); ?> : <span class="pull-right text-primary">{assign_user}</span></p>
                                                    <p class="col-4"><?php echo e(__('Last Working Date')); ?> : <span class="pull-right text-primary">{resignation_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Resignation Date')); ?> : <span class="pull-right text-primary">{notice_date}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'termination_sent'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Employee Name')); ?> : <span class="pull-right text-primary">{termination_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Employee Email')); ?> : <span class="pull-right text-primary">{termination_email}</span></p>
                                                    <p class="col-4"><?php echo e(__('Notice Date')); ?> : <span class="pull-right text-primary">{notice_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Termination Date')); ?> : <span class="pull-right text-primary">{termination_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Termination Type')); ?> : <span class="pull-right text-primary">{termination_type}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'transfer_sent'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Employee Name')); ?> : <span class="pull-right text-primary">{transfer_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Employee Email')); ?> : <span class="pull-right text-primary">{transfer_email}</span></p>
                                                    <p class="col-4"><?php echo e(__('Transfer Date')); ?> : <span class="pull-right text-primary">{transfer_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Transfer Department')); ?> : <span class="pull-right text-primary">{transfer_department}</span></p>
                                                    <p class="col-4"><?php echo e(__('Transfer Branch')); ?> : <span class="pull-right text-primary">{transfer_branch}</span></p>
                                                    <p class="col-4"><?php echo e(__('Transfer Desciption')); ?> : <span class="pull-right text-primary">{transfer_description}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'trip_sent'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Employee ')); ?> : <span class="pull-right text-primary">{trip_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Purpose of Trip')); ?> : <span class="pull-right text-primary">{purpose_of_visit}</span></p>
                                                    <p class="col-4"><?php echo e(__('Start Date')); ?> : <span class="pull-right text-primary">{start_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('End Date')); ?> : <span class="pull-right text-primary">{end_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Country')); ?> : <span class="pull-right text-primary">{place_of_visit}</span></p>
                                                    <p class="col-4"><?php echo e(__('Description')); ?> : <span class="pull-right text-primary">{trip_description}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'vendor_bill_sent'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Vendor Name')); ?> : <span class="pull-right text-primary">{vendor_bill_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Bill Number')); ?> : <span class="pull-right text-primary">{vendor_bill_number}</span></p>
                                                    <p class="col-4"><?php echo e(__('Bill Url')); ?> : <span class="pull-right text-primary">{vendor_bill_url}</span></p>
                                                </div>
                                            <?php elseif ($emailTemplate->slug == 'warning_sent'): ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('App Name')); ?> : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('App Url')); ?> : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4"><?php echo e(__('Employee Name')); ?> : <span class="pull-right text-primary">{employee_warning_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Subject')); ?> : <span class="pull-right text-primary">{warning_subject}</span></p>
                                                    <p class="col-4"><?php echo e(__('Description')); ?> : <span class="pull-right text-primary">{warning_description}</span></p>
                                                </div>
                                            <?php else: ?>
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('Unrecognized template')); ?> : <span class="pull-end text-primary"><?php echo e(__('No name available.')); ?></span></p>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="<?php echo e(VC::RW); ?>">
                                                <p class="col-4"><?php echo e(__('Failed to load email template')); ?> : <span class="pull-end text-primary"><?php echo e(__('No name available.')); ?></span></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="<?php echo e(VC::CM6); ?>">
                            <?php echo e(Form::label('subject', __('Subject'), ['class' => 'col-form-label text-dark'])); ?>

                            <?php echo e(Form::text('subject', null, ['class' => VC::FM_CT . ' font-style', 'required' => 'required'])); ?>

                        </div>

                        <div class="<?php echo e(VC::CM6); ?>">
                            <?php echo e(Form::label('from', __('From'), ['class' => 'col-form-label text-dark'])); ?>

                            <?php echo e(Form::text('from', $emailTemplate->from, ['class' => VC::FM_CT . ' font-style', 'required' => 'required'])); ?>

                        </div>

                        <div class="<?php echo e(VC::C12); ?>">
                            <?php echo e(Form::label('content', __('Email Message'), ['class' => 'col-form-label text-dark'])); ?>

                            <?php echo e(Form::textarea('content', $currEmailTempLang->content, ['class' => 'summernote-simple', 'required' => 'required'])); ?>

                        </div>

                        <div class="modal-footer">
                            <?php echo e(Form::hidden('lang', null)); ?>

                            <?php echo e(Form::submit(__('Save Changes'), ['class' => VC::BT_XS_PM])); ?>

                        </div>

                    </div>
                    <?php $__env->startPush(ST::ADM_SCR_PG); ?>
                    <script defer src="<?php echo e(asset('assets/js/routes/emailTemplates/update.js')); ?>"></script>
                    <?php $__env->stopPush(); ?>
                    <?php echo e(Form::close()); ?>

                <?php else: ?>
                    <div><?php echo e(__('The current email template could not be found.')); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/email_templates/show.blade.php ENDPATH**/ ?>