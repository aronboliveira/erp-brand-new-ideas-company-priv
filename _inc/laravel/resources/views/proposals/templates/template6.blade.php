<?php
# Template 6
use App\Config\Constants\{DatabaseConstants as DC, SettingsConstants as SC, ViewsConstants as VW};
use App\Helpers\TemplateHelper;
use App\Models\{ProductServiceUnit, Utility};
use Illuminate\Support\{Str};
use Illuminate\Support\Facades\{Auth, Crypt, Log, Route};
use Milon\Barcode\DNS2D;

$user = Auth::user();
$lang = Utility::fetchUserLang(user: $user);

if (isset($proposal) && !empty($proposal)) {if (!function_exists('e')) {
        function e($v)
        {
            return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');}
    }

    $customer ??= null;
    $settings ??= [];
    $settings_data ??= [];
    $customFields ??= [];
    $meta_title ??= '';
    $meta_desc ??= '';
    $themeCSS ??= '';
    $color ??= '#ffffff';
    $font_color ??= '#000000';
    $img ??= '';
    $preview ??= null;
    $docLang ??= null;
    $dir ??= '';
    $proposalNumber ??= '';
    $issueDate ??= '';
    $qrValue ??= '#';
    $qrHtml ??= '';
    $proposalTotalQuantity ??= '0';
    $proposalTotalRate ??= '0';
    $proposalTotalDiscount ??= '0';
    $proposalTotalTaxPrice ??= '0';
    $proposalSubTotal ??= '0';
    $proposalGrandTotal ??= '0';

    try {$docLang ??= $lang ?? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DC::DEFAULT_LANG);} catch (\InvalidArgumentException $e) {Log::warning('DocLang InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $docLang ??= DC::DEFAULT_LANG;} catch (\Exception $e) {Log::error('DocLang Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $docLang ??= DC::DEFAULT_LANG;} catch (\Throwable $e) {Log::critical('DocLang Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $docLang ??= DC::DEFAULT_LANG;}

    try {$settings_data ??= Utility::settingsById(data_get($proposal, 'created_by'));} catch (\TypeError $e) {Log::error('SettingsById TypeError: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $settings_data ??= [];} catch (\InvalidArgumentException $e) {Log::warning('SettingsById InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $settings_data ??= [];} catch (\Exception $e) {Log::error('SettingsById Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $settings_data ??= [];} catch (\Throwable $e) {Log::critical('SettingsById Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $settings_data ??= [];}

    try {$dir = (data_get($settings_data, SC::RTL) === 'on') ? 'rtl' : '';} catch (\InvalidArgumentException $e) {Log::warning('RTL InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $dir = '';} catch (\Exception $e) {Log::error('RTL Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $dir = '';} catch (\Throwable $e) {Log::critical('RTL Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $dir = '';}

    try {$proposalNumber = Utility::proposalNumberFormat($settings, data_get($proposal, 'proposal_id')) ?: __('Could not find proposal number');} catch (\InvalidArgumentException $e) {Log::warning('ProposalNumber InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $proposalNumber = __('Could not find proposal number');} catch (\Exception $e) {Log::error('ProposalNumber Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $proposalNumber = __('Could not find proposal number');} catch (\Throwable $e) {Log::critical('ProposalNumber Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $proposalNumber = __('Could not find proposal number');}

    try {$issueDate = Utility::dateFormat($settings, data_get($proposal, 'issue_date')) ?: __('Failed to get issue date');} catch (\InvalidArgumentException $e) {Log::warning('IssueDate InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $issueDate = __('Failed to get issue date');} catch (\Exception $e) {Log::error('IssueDate Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $issueDate = __('Failed to get issue date');} catch (\Throwable $e) {Log::critical('IssueDate Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $issueDate = __('Failed to get issue date');}

    try {$base = VW::PPS . '.link.copy';
        $kebab = Str::kebab($base);
        $resolved = Route::has($base) ? $base : (Route::has($kebab) ? $kebab : null);
        $pid = data_get($proposal, 'proposal_id');
        $enc = $pid ? Crypt::encrypt($pid) : null;
        $qrValue = ($resolved && $enc) ? route($resolved, $enc) : '#';
        if ($qrValue === '#') {
            Log::error('QR route unavailable or param missing | route=' . ($resolved ?? 'null') . ' | file=' . __FILE__ . ' | line=' . __LINE__);}
    } catch (\InvalidArgumentException $e) {Log::error('QR Route InvalidArgumentException: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $qrValue = '#';} catch (\RuntimeException $e) {Log::error('QR Route RuntimeException: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $qrValue = '#';} catch (\InvalidArgumentException $e) {Log::warning('QR Route InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $qrValue = '#';} catch (\Exception $e) {Log::error('QR Route Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $qrValue = '#';} catch (\Throwable $e) {Log::critical('QR Route Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $qrValue = '#';}

    try {$proposalTotalQuantity = (string)(data_get($proposal, 'totalQuantity') ?? '0');
        $proposalTotalRate = Utility::priceFormat($settings, data_get($proposal, 'totalRate')) ?: '0';
        $proposalTotalDiscount = Utility::priceFormat($settings, data_get($proposal, 'totalDiscount')) ?: '0';
        $proposalTotalTaxPrice = Utility::priceFormat($settings, data_get($proposal, 'totalTaxPrice')) ?: '0';
        $proposalSubTotal = Utility::priceFormat($settings, data_get($proposal, 'getSubTotal') ? $proposal->getSubTotal() : 0) ?: '0';
        $proposalGrandTotal = Utility::priceFormat(
            $settings, (data_get($proposal, 'getSubTotal') ? $proposal->getSubTotal() : 0)
                - (data_get($proposal, 'getTotalDiscount') ? $proposal->getTotalDiscount() : 0)
                + (data_get($proposal, 'getTotalTax') ? $proposal->getTotalTax() : 0)
        ) ?: '0';} catch (\InvalidArgumentException $e) {Log::warning('Totals InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $proposalTotalQuantity = '0';
        $proposalTotalRate = '0';
        $proposalTotalDiscount = '0';
        $proposalTotalTaxPrice = '0';
        $proposalSubTotal = '0';
        $proposalGrandTotal = '0';} catch (\Exception $e) {Log::error('Totals Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $proposalTotalQuantity = '0';
        $proposalTotalRate = '0';
        $proposalTotalDiscount = '0';
        $proposalTotalTaxPrice = '0';
        $proposalSubTotal = '0';
        $proposalGrandTotal = '0';} catch (\Throwable $e) {Log::critical('Totals Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        $proposalTotalQuantity = '0';
        $proposalTotalRate = '0';
        $proposalTotalDiscount = '0';
        $proposalTotalTaxPrice = '0';
        $proposalSubTotal = '0';
        $proposalGrandTotal = '0';}
?>
    <!DOCTYPE html>
    <html lang="<?= e($docLang) ?>" dir="<?= e($dir) ?>">

    <head>
        <?php
        try {echo view('fragments.std', ['meta_title' => $meta_title, 'meta_desc' => $meta_desc, 'meta_vp' => ''])->render();} catch (\InvalidArgumentException $e) {Log::warning('Meta view InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);} catch (\Exception $e) {Log::error('Meta view Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);} catch (\Throwable $e) {Log::critical('Meta view Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);}
        ?>
        <title>{{ __('New York') }} - {{ __('Proposal') }}</title>
        <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
        <style>
            <?php echo $themeCSS; ?>
        </style>
        <style type="text/css">
            body {font-family: 'Lato', sans-serif;}

            p,
            li,
            ul,
            ol {margin: 0;
                padding: 0;
                list-style: none;
                line-height: 1.5;}

            * {margin: 0;
                padding: 0;
                box-sizing: border-box;}

            table {width: 100%;
                border-collapse: collapse;}

            table tr th {padding: .75rem;
                text-align: left;}

            table tr td {padding: .75rem;
                text-align: left;}

            table th small {display: block;
                font-size: 12px;}

            .proposal-preview-main {max-width: 700px;
                width: 100%;
                margin: 0 auto;
                background: #ffff;
                box-shadow: 0 0 10px #ddd;}

            .proposal-logo {max-width: 200px;
                width: 100%;}

            .proposal-header table td {padding: 15px 30px;}

            .text-right {text-align: right;}

            .no-space tr td {padding: 0;}

            .vertical-align-top td {vertical-align: top;}

            .view-qrcode {max-width: 114px;
                height: 114px;
                margin-left: auto;
                margin-top: 15px;
                background: var(--white);}

            .view-qrcode img {width: 100%;
                height: 100%;}

            .proposal-body {padding: 30px 25px 0;}

            table.add-border tr {border-top: 1px solid var(--theme-color);}

            tfoot tr:first-of-type {border-bottom: 1px solid var(--theme-color);}

            .total-table tr:first-of-type td {padding-top: 0;}

            .total-table tr:first-of-type {border-top: 0;}

            .sub-total {padding-right: 0;
                padding-left: 0;}

            .border-0 {border: none !important;}

            .proposal-summary td,
            .proposal-summary th {font-size: 13px;
                font-weight: 600;}

            .total-table td:last-of-type {width: 146px;}

            .proposal-footer {padding: 15px 20px;}

            .itm-description td {padding-top: 0;}

            html[dir="rtl"] table tr td,
            html[dir="rtl"] table tr th {text-align: right;}

            html[dir="rtl"] .text-right {text-align: left;}

            html[dir="rtl"] .view-qrcode {margin-left: 0;
                margin-right: auto;}

            p:not(:last-of-type) {margin-bottom: 15px;}

            .proposal-summary p {margin-bottom: 0;}
        </style>
        <?php if (data_get($settings_data, SC::RTL) === 'on'): ?>
            <link rel="stylesheet" href="<?= e(asset('css/bootstrap-rtl.css')) ?>">
        <?php endif; ?>
    </head>

    <body>
        <div class="proposal-preview-main" id="boxes">
            <div class="proposal-header" style="border-top:15px solid <?= e($color) ?>">
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <h3 style="text-transform:uppercase;font-size:40px;font-weight:bold;"><?= e(__('PROPOSAL')) ?></h3>
                            </td>
                            <td class="text-right"><img class="proposal-logo" src="<?= e($img) ?>" alt=""></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="proposal-body">
                <table class="vertical-align-top">
                    <tbody>
                        <tr>
                            <?php if (!empty($settings['company_name']) && !empty($settings['mail_from_address']) && !empty($settings['company_address'])): ?>
                                <td style="font-size:13px;">
                                    <strong style="margin-bottom:10px;display:block;"><?= e(__('From:')) ?></strong>
                                    <p>
                                        <?= e(data_get($settings, 'company_name', __('No company name available.'))) ?><br>
                                        <?= e(data_get($settings, 'mail_from_address', __('No email available.'))) ?><br><br>
                                        <?= e(data_get($settings, 'company_address', __('No address available.'))) ?>
                                        <?php $city = (string)data_get($settings, 'company_city', '');
                                        echo $city !== '' ? '<br>' . e($city) . ', ' : '<br>' . e(__('No city available.')) . ' '; ?>
                                        <?php $state = (string)data_get($settings, 'company_state', '');
                                        echo $state !== '' ? e($state) : e(__('No state available.')); ?>
                                        <?php $zip = (string)data_get($settings, 'company_zipcode', '');
                                        echo $zip !== '' ? ' - ' . e($zip) : ' - ' . e(__('No zipcode available.')); ?>
                                        <?php $country = (string)data_get($settings, 'company_country', '');
                                        echo $country !== '' ? '<br>' . e($country) : '<br>' . e(__('No country available.')); ?>
                                        <?= e(data_get($settings, 'company_telephone', __('No phone available.'))) ?><br>
                                    </p>
                                </td>
                            <?php endif; ?>
                            <td style="font-size:13px;">
                                <strong style="margin-bottom:10px;display:block;"><?= e(__('Bill To:')) ?></strong>
                                <?php if (!empty(data_get($customer, 'billing_name'))): ?>
                                    <p>
                                        <?= e(data_get($customer, 'billing_name', __('No name for billing available.'))) ?><br>
                                        <?= e(data_get($customer, 'billing_address', __('No address for billing available.'))) ?><br>
                                        <?php $bcity = (string)data_get($customer, 'billing_city', '');
                                        echo $bcity !== '' ? e($bcity) : e(__('No city for billing available.')); ?><?= $bcity !== '' ? ', ' : '' ?><br>
                                        <?php $bstate = (string)data_get($customer, 'billing_state', '');
                                        echo $bstate !== '' ? e($bstate) : e(__('No state for billing available.')); ?><?= $bstate !== '' ? ', ' : '' ?>,
                                        <?= e(data_get($customer, 'billing_zip', __('No zip for billing available.'))) ?><br>
                                        <?= e(data_get($customer, 'billing_country', __('No country for billing available.'))) ?><br>
                                        <?= e(data_get($customer, 'billing_phone', __('No phone for billing available.'))) ?><br>
                                    </p>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <?php if (data_get($settings, 'shipping_display') === 'on'): ?>
                                <td style="font-size:13px;" class="text-right">
                                    <strong style="margin-bottom:10px;display:block;"><?= e(__('Ship To:')) ?></strong>
                                    <?php if (!empty(data_get($customer, 'shipping_name'))): ?>
                                        <p>
                                            <?= e(data_get($customer, 'shipping_name', __('No name for shipping available.'))) ?><br>
                                            <?= e(data_get($customer, 'shipping_address', __('No address for shipping available.'))) ?><br>
                                            <?php $scity = (string)data_get($customer, 'shipping_city', '');
                                            echo $scity !== '' ? e($scity) : e(__('No city for shipping available.')); ?><?= $scity !== '' ? ', ' : '' ?><br>
                                            <?php $sstate = (string)data_get($customer, 'shipping_state', '');
                                            echo $sstate !== '' ? e($sstate) : e(__('No state for shipping available.')); ?><?= $sstate !== '' ? ', ' : '' ?>,
                                            <?= e(data_get($customer, 'shipping_zip', __('No zip for shipping available.'))) ?><br>
                                            <?= e(data_get($customer, 'shipping_country', __('No country for shipping available.'))) ?><br>
                                            <?= e(data_get($customer, 'shipping_phone', __('No phone for shipping available.'))) ?><br>
                                        </p>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <tr style="border-bottom:1px solid <?= e($color) ?>">
                            <td>
                                <p>
                                    <?php $reg = (string)data_get($settings, 'registration_number', '');
                                    echo $reg !== '' ? e(__('Registration Number')) . ' : ' . e($reg) : ''; ?><br>
                                    <?php if (!empty($settings['tax_type']) && !empty($settings['vat_number'])) {echo e($settings['tax_type'] . ' ' . __('Number')) . ' : ' . e($settings['vat_number']) . ' <br>';} ?>
                                </p>
                            </td>
                            <td colspan="2">
                                <div class="view-qrcode" style="margin-top:0;">
                                    <?php
                                    try {$qrHtml = (new \Milon\Barcode\DNS2D)->getBarcodeHTML($qrValue, 'QRCODE', 2, 2);
                                        echo $qrHtml;} catch (\InvalidArgumentException $e) {Log::warning('QR HTML InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                        echo '<div></div>';} catch (\Exception $e) {Log::error('QR HTML Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                        echo '<div></div>';} catch (\Throwable $e) {Log::critical('QR HTML Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                        echo '<div></div>';}
                                    ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table>
                    <tbody>
                        <tr>
                            <td>
                                <table class="no-space">
                                    <tbody>
                                        <tr>
                                            <td><?= e(__('Number')) ?>:</td>
                                            <td class="text-right"><?= e($proposalNumber) ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= e(__('Issue Date')) ?>:</td>
                                            <td class="text-right"><?= e($issueDate) ?></td>
                                        </tr>
                                        <?php if (!empty($customFields) && count(data_get($proposal, 'customField', [])) > 0): ?>
                                            <?php foreach ($customFields as $field): ?>
                                                <tr>
                                                    <td><?= e(data_get($field, 'name') ?? __('No name available')) ?> :</td>
                                                    <td><?= e(data_get($proposal->customField, $field->id) ?? '-') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table class="add-border proposal-summary" style="margin-top:30px;">
                    <thead style="background: <?= e($color) ?>;color:<?= e($font_color) ?>">
                        <tr style="border-bottom:1px solid <?= e($color) ?>">
                            <th><?= e(__('Item')) ?></th>
                            <th><?= e(__('Quantity')) ?></th>
                            <th><?= e(__('Rate')) ?></th>
                            <th><?= e(__('Discount')) ?></th>
                            <th><?= e(__('Tax')) ?> (%)</th>
                            <th><?= e(__('Price')) ?> <small><?= e(__('after tax & discount')) ?></small></th>
                        </tr>
                    </thead>
                    <tbody style="border-bottom:1px solid <?= e($color) ?>">
                        <?php if (is_iterable(data_get($proposal, 'itemData')) && count($proposal->itemData) > 0): ?>
                            <?php foreach ($proposal->itemData as $key => $item): ?>
                                <?php
                                $unitLabel = '';
                                try {$unit = ProductServiceUnit::find(data_get($item, 'unit'));
                                    $unitLabel = data_get($unit, 'name', '');} catch (\InvalidArgumentException $e) {Log::warning('Unit find InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                    $unitLabel = '';} catch (\Exception $e) {Log::error('Unit find Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                    $unitLabel = '';} catch (\Throwable $e) {Log::critical('Unit find Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                    $unitLabel = '';}
                                ?>
                                <tr>
                                    <td><?= e(data_get($item, 'name') ?? __('No item name available')) ?></td>
                                    <td><?= e((string)(data_get($item, 'quantity') ?? 0)) . ($unitLabel ? ' (' . e($unitLabel) . ')' : '') ?></td>
                                    <td><?php try {echo e(Utility::priceFormat($settings, data_get($item, 'price', 0)));} catch (\InvalidArgumentException $e) {Log::warning('Item price format InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                            echo '0';} catch (\Exception $e) {Log::error('Item price format Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                            echo '0';} catch (\Throwable $e) {Log::critical('Item price format Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                            echo '0';} ?></td>
                                    <td><?php try {$disc = (float)(data_get($item, 'discount', 0));
                                            echo $disc != 0.0 ? e(Utility::priceFormat($settings, $disc)) : '-';} catch (\InvalidArgumentException $e) {Log::warning('Item discount format InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                            echo '-';} catch (\Exception $e) {Log::error('Item discount format Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                            echo '-';} catch (\Throwable $e) {Log::critical('Item discount format Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                            echo '-';} ?></td>
                                    <?php $itemtax = 0.0; ?>
                                    <td>
                                        <?php if (!empty(data_get($item, 'itemTax'))): ?>
                                            <?php foreach ((array)$item->itemTax as $taxes): ?>
                                                <?php $itemtax += (float)data_get($taxes, 'tax_price', 0); ?>
                                                <p><?= e((data_get($taxes, 'name') ?? 'Tax') . ' (' . (data_get($taxes, 'rate') ?? '0') . ') ' . (data_get($taxes, 'price') ?? '0')) ?></p>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        try {$line = (float)(data_get($item, 'price', 0)) * (float)(data_get($item, 'quantity', 0)) - (float)(data_get($item, 'discount', 0)) + (float)$itemtax;
                                            echo e(Utility::priceFormat($settings, $line));} catch (\InvalidArgumentException $e) {Log::warning('Line total InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                            echo '0';} catch (\Exception $e) {Log::error('Line total Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                            echo '0';} catch (\Throwable $e) {Log::critical('Line total Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                            echo '0';}
                                        ?>
                                    </td>
                                </tr>
                                <?php if (!empty(data_get($item, 'description'))): ?>
                                    <tr class="border-0 itm-description">
                                        <td colspan="6" style="border-bottom:1px solid <?= e($color) ?>"><?= e(data_get($item, 'description')) ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr style="border-bottom:1px solid <?= e($color) ?>">
                            <td><?= e(__('Total')) ?></td>
                            <td><?= e($proposalTotalQuantity) ?></td>
                            <td><?= e($proposalTotalRate) ?></td>
                            <td><?= e($proposalTotalDiscount) ?></td>
                            <td><?= e($proposalTotalTaxPrice) ?></td>
                            <td><?= e($proposalSubTotal) ?></td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td colspan="2" class="sub-total">
                                <table class="total-table">
                                    <tr style="border-bottom:1px solid <?= e($color) ?>">
                                        <td><?= e(__('Subtotal')) ?>:</td>
                                        <td><?= e($proposalSubTotal) ?></td>
                                    </tr>
                                    <?php if (method_exists($proposal, 'getTotalDiscount') && $proposal->getTotalDiscount()): ?>
                                        <tr style="border-bottom:1px solid <?= e($color) ?>">
                                            <td><?= e(__('Discount')) ?>:</td>
                                            <td><?php try {echo e(Utility::priceFormat($settings, $proposal->getTotalDiscount()));} catch (\InvalidArgumentException $e) {Log::warning('Subtotal discount InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                                    echo '0';} catch (\Exception $e) {Log::error('Subtotal discount Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                                    echo '0';} catch (\Throwable $e) {Log::critical('Subtotal discount Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                                    echo '0';} ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($proposal->taxesData)): ?>
                                        <?php foreach ($proposal->taxesData as $taxName => $taxPrice): ?>
                                            <tr style="border-bottom:1px solid <?= e($color) ?>">
                                                <td><?= e($taxName) . ' :' ?></td>
                                                <td><?php try {echo e(Utility::priceFormat($settings, $taxPrice));} catch (\InvalidArgumentException $e) {Log::warning('Tax row InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                                        echo '0';} catch (\Exception $e) {Log::error('Tax row Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                                        echo '0';} catch (\Throwable $e) {Log::critical('Tax row Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                                                        echo '0';} ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <tr style="border-bottom:1px solid <?= e($color) ?>">
                                        <td><?= e(__('Total')) ?>:</td>
                                        <td><?= e($proposalGrandTotal) ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <div class="proposal-footer">
                    <b><?= e(data_get($settings, 'footer_title', __('No footer title available'))) ?></b> <br>
                    <?php try {echo (string)data_get($settings, 'footer_notes', '');} catch (\InvalidArgumentException $e) {Log::warning('Footer notes InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);} catch (\Exception $e) {Log::error('Footer notes Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);} catch (\Throwable $e) {Log::critical('Footer notes Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);} ?>
                </div>
            </div>
        </div>

        <?php if (!isset($preview)): ?>
            <?php
            try {echo view(VW::PPS . '.script')->render();} catch (\InvalidArgumentException $e) {Log::warning('Script include InvalidArgument: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);} catch (\Exception $e) {Log::error('Script include Exception: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);} catch (\Throwable $e) {Log::critical('Script include Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);}
            ?>
        <?php endif; ?>
    </body>

    </html>
<?php
} else {echo TemplateHelper::getNoDataHtml('proposal');}
