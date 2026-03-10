<?php

use App\Config\Constants\{DatabaseConstants as DC, SettingsConstants as SC};
use App\Models\{Utility, ProductServiceUnit};
use Illuminate\Support\Facades\Log;
use App\Helpers\TemplateHelper;

if (!function_exists('e')) {function e($v)
    {
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');}
}

$pos           ??= null;
$customer      ??= null;
$settings      ??= [];
$settings_data ??= [];
$customFields  ??= [];
$meta_title    ??= '';
$meta_desc     ??= '';
$themeCSS      ??= '';
$color         ??= '#4b4b4b';
$font_color    ??= '#000000';
$img           ??= '';
$preview       ??= null;
$posPayment    ??= null;

if (trim((string)$themeCSS) === '') {$themeCSS = ":root { --theme-color: {$color}; --white: #ffffff; --black: #000000; }";
}

try {$docLang = str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DC::DEFAULT_LANG);} catch (\InvalidArgumentException $e) {Log::warning('DocLang InvalidArgument: ' . get_class($e) . ' | ' . $e->getMessage());
    $docLang = DC::DEFAULT_LANG;} catch (\Exception $e) {Log::error('DocLang Exception: ' . get_class($e) . ' | ' . $e->getMessage());
    $docLang = DC::DEFAULT_LANG;} catch (\Throwable $e) {Log::critical('DocLang Throwable: ' . get_class($e) . ' | ' . $e->getMessage());
    $docLang = DC::DEFAULT_LANG;}

if (empty($pos)) {echo TemplateHelper::getNoDataHtml('pos', $docLang);
    return;}

try {$settings_data = Utility::settingsById(data_get($pos, 'created_by'));} catch (\InvalidArgumentException $e) {Log::warning('settingsById InvalidArgument: ' . $e->getMessage());
    $settings_data = [];} catch (\Exception $e) {Log::error('settingsById Exception: ' . $e->getMessage());
    $settings_data = [];} catch (\Throwable $e) {Log::critical('settingsById Throwable: ' . $e->getMessage());
    $settings_data = [];}

try {$dir = (data_get($settings_data, SC::RTL) === 'on') ? 'rtl' : '';} catch (\InvalidArgumentException $e) {Log::warning('RTL InvalidArgument: ' . $e->getMessage());
    $dir = '';} catch (\Exception $e) {Log::error('RTL Exception: ' . $e->getMessage());
    $dir = '';} catch (\Throwable $e) {Log::critical('RTL Throwable: ' . $e->getMessage());
    $dir = '';}

try {$posNumber = Utility::posNumberFormat($settings, data_get($pos, 'pos_id')) ?: __('Could not find POS number');} catch (\InvalidArgumentException $e) {Log::warning('posNumber InvalidArgument: ' . $e->getMessage());
    $posNumber = __('Could not find POS number');} catch (\Exception $e) {Log::error('posNumber Exception: ' . $e->getMessage());
    $posNumber = __('Could not find POS number');} catch (\Throwable $e) {Log::critical('posNumber Throwable: ' . $e->getMessage());
    $posNumber = __('Could not find POS number');}

try {$issueDate = Utility::dateFormat($settings, data_get($pos, 'issue_date')) ?: __('Failed to get issue date');} catch (\InvalidArgumentException $e) {Log::warning('issueDate InvalidArgument: ' . $e->getMessage());
    $issueDate = __('Failed to get issue date');} catch (\Exception $e) {Log::error('issueDate Exception: ' . $e->getMessage());
    $issueDate = __('Failed to get issue date');} catch (\Throwable $e) {Log::critical('issueDate Throwable: ' . $e->getMessage());
    $issueDate = __('Failed to get issue date');}
?>
<!DOCTYPE html>
<html lang="<?= e($docLang) ?>" dir="<?= e($dir) ?>">

<head>
    <?php try {echo view('fragments.std', ['meta_title' => $meta_title, 'meta_desc' => $meta_desc, 'meta_vp' => ''])->render();} catch (\InvalidArgumentException $e) {Log::warning('Meta view InvalidArgument: ' . $e->getMessage());} catch (\Exception $e) {Log::error('Meta view Exception: ' . $e->getMessage());} catch (\Throwable $e) {Log::critical('Meta view Throwable: ' . $e->getMessage());} ?>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <?php try {echo '<link rel="stylesheet" href="' . e(asset('css/app.css')) . '">';} catch (\Throwable $e) {} ?>
    <style>
        <?= $themeCSS ?>
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

        .pos-preview-main {max-width: 700px;
            width: 100%;
            margin: 0 auto;
            background: #ffff;
            box-shadow: 0 0 10px #ddd;}

        .pos-logo {max-width: 200px;
            width: 100%;}

        .pos-header table td {padding: 15px 30px;}

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

        .pos-body {padding: 30px 25px 0;}

        table.add-border tr {border-top: 1px solid var(--theme-color);}

        tfoot tr:first-of-type {border-bottom: 1px solid var(--theme-color);}

        .total-table tr:first-of-type td {padding-top: 0;}

        .total-table tr:first-of-type {border-top: 0;}

        .sub-total {padding-right: 0;
            padding-left: 0;}

        .border-0 {border: none !important;}

        .pos-summary td,
        .pos-summary th {font-size: 13px;
            font-weight: 600;}

        .total-table td:last-of-type {width: 146px;}

        .pos-footer {padding: 15px 20px;}

        .itm-description td {padding-top: 0;}

        html[dir="rtl"] table tr td,
        html[dir="rtl"] table tr th {text-align: right;}

        html[dir="rtl"] .text-right {text-align: left;}

        html[dir="rtl"] .view-qrcode {margin-left: 0;
            margin-right: auto;}

        p:not(:last-of-type) {margin-bottom: 15px;}

        .pos-summary p {margin-bottom: 0;}
    </style>
    <?php if (data_get($settings_data, SC::RTL) === 'on'): ?>
        <link rel="stylesheet" href="<?= e(asset('css/bootstrap-rtl.css')) ?>">
    <?php endif; ?>
</head>

<body>
    <div class="pos-preview-main" id="boxes">
        <div class="pos-header">
            <table class="vertical-align-top">
                <tbody>
                    <tr>
                        <td><img class="pos-logo" src="<?= e($img) ?>" alt=""></td>
                        <td class="text-right">
                            <p>
                                <?= !empty($settings['company_name']) ? e($settings['company_name']) : e(__('No company name available')) ?><br>
                                <?= !empty($settings['mail_from_address']) ? e($settings['mail_from_address']) : e(__('No email available')) ?><br><br><br>
                                <?= !empty($settings['company_address']) ? e($settings['company_address']) : e(__('No address available')) ?>
                                <?= !empty($settings['company_city']) ? '<br>' . e($settings['company_city']) . ', ' : __('No company city available') ?>
                                <?= !empty($settings['company_state']) ? e($settings['company_state']) : __('No company state available') ?>
                                <?= !empty($settings['company_zipcode']) ? ' - ' . e($settings['company_zipcode']) : __('No company zipcode available') ?>
                                <?= !empty($settings['company_country']) ? '<br>' . e($settings['company_country']) : __('No company country available') ?>
                                <?= !empty($settings['company_telephone']) ? e($settings['company_telephone']) : __('No company telephone available') ?><br>
                                <?php
                                if (!empty($settings['registration_number'])) {echo e(__('Registration Number')) . ' : ' . e($settings['registration_number']) . ' ';}
                                echo '<br>';
                                if (data_get($settings, 'vat_gst_number_switch') === 'on' && !empty($settings['tax_type']) && !empty($settings['vat_number'])) {echo e($settings['tax_type'] . ' ' . __('Number')) . ' : ' . e($settings['vat_number']) . ' <br>';}
                                ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="vertical-align-top">
                <tbody>
                    <tr>
                        <td>
                            <h3 style="text-transform:uppercase;font-size:25px;font-weight:bold;margin-bottom:15px;"><?= e(__('POS')) ?></h3>
                            <table class="no-space">
                                <tbody>
                                    <tr>
                                        <td><?= e(__('Number')) ?>:</td>
                                        <td class="text-right"><?= e($posNumber) ?></td>
                                    </tr>
                                    <tr>
                                        <td><?= e(__('Issue Date')) ?>:</td>
                                        <td class="text-right"><?= e($issueDate) ?></td>
                                    </tr>
                                    <?php if (!empty($customFields) && count(data_get($pos, 'customField', [])) > 0): ?>
                                        <?php foreach ($customFields as $field): ?>
                                            <tr>
                                                <td><?= e(data_get($field, 'name', '')) ?> :</td>
                                                <td><?= e(data_get($pos->customField, $field->id) ?? '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pos-body">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <strong style="margin-bottom:10px;display:block;"><?= e(__('Bill To')) ?>:</strong>
                            <?php if (!empty(data_get($customer, 'billing_name'))): ?>
                                <p>
                                    <?= e(data_get($customer, 'billing_name', __('No name for billing available.'))) ?><br>
                                    <?= e(data_get($customer, 'billing_address', __('No address for billing available.'))) ?><br>
                                    <?= e(data_get($customer, 'billing_city', __('No city for billing available.'))) ?><?= !empty(data_get($customer, 'billing_city')) ? ', ' : '' ?><br>
                                    <?= e(data_get($customer, 'billing_state', __('No state for billing available.'))) ?><?= !empty(data_get($customer, 'billing_state')) ? ', ' : '' ?>,
                                    <?= e(data_get($customer, 'billing_zip', __('No zip for billing available.'))) ?><br>
                                    <?= e(data_get($customer, 'billing_country', __('No country for billing available.'))) ?><br>
                                    <?= e(data_get($customer, 'billing_phone', __('No phone for billing available'))) ?><br>
                                </p>
                                <?php else: ?>-<?php endif; ?>
                        </td>
                        <?php if (data_get($settings, 'shipping_display') === 'on'): ?>
                            <td class="text-right">
                                <strong style="margin-bottom:10px;display:block;"><?= e(__('Ship To')) ?>:</strong>
                                <?php if (!empty(data_get($customer, 'shipping_name'))): ?>
                                    <p>
                                        <?= e(data_get($customer, 'shipping_name', __('No name for shipping available.'))) ?><br>
                                        <?= e(data_get($customer, 'shipping_address', __('No address for shipping available.'))) ?><br>
                                        <?= e(data_get($customer, 'shipping_city', __('No city for shipping available.'))) ?><?= !empty(data_get($customer, 'shipping_city')) ? ', ' : '' ?><br>
                                        <?= e(data_get($customer, 'shipping_state', __('No state for shipping available.'))) ?><?= !empty(data_get($customer, 'shipping_state')) ? ', ' : '' ?>,
                                        <?= e(data_get($customer, 'shipping_zip', __('No zip for shipping available.'))) ?><br>
                                        <?= e(data_get($customer, 'shipping_country', __('No country for shipping available.'))) ?><br>
                                        <?= e(data_get($customer, 'shipping_phone', __('No phone for shipping available.'))) ?><br>
                                    </p>
                                    <?php else: ?>-<?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                </tbody>
            </table>

            <table class="add-border pos-summary" style="margin-top:30px;">
                <thead style="background: <?= e($color) ?>; color: <?= e($font_color) ?>">
                    <tr>
                        <th><?= e(__('Item')) ?></th>
                        <th><?= e(__('Quantity')) ?></th>
                        <th><?= e(__('Price')) ?></th>
                        <th><?= e(__('Tax')) ?></th>
                        <th><?= e(__('Tax Amount')) ?></th>
                        <th><?= e(__('Total')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($pos->itemData) && is_iterable($pos->itemData) && count($pos->itemData) > 0): ?>
                        <?php foreach ($pos->itemData as $item): ?>
                            <?php
                            $price = (float) data_get($item, 'price', 0);
                            $qty   = (float) data_get($item, 'quantity', 0);
                            $totalTaxPrice = 0.0;
                            $unitSuffix = '';
                            try {$unit = ProductServiceUnit::find(data_get($item, 'unit'));
                                $unitName = data_get($unit, 'name', '');
                                $unitSuffix = $unitName ? ' (' . e($unitName) . ')' : '';} catch (\InvalidArgumentException $e) {Log::warning('Unit find InvalidArgument: ' . $e->getMessage());
                                $unitSuffix = '';} catch (\Exception $e) {Log::error('Unit find Exception: ' . $e->getMessage());
                                $unitSuffix = '';} catch (\Throwable $e) {Log::critical('Unit find Throwable: ' . $e->getMessage());
                                $unitSuffix = '';}
                            ?>
                            <tr>
                                <td><?= e(data_get($item, 'name', '')) ?></td>
                                <td><?= e((string)$qty) . $unitSuffix ?></td>
                                <td><?php try {echo e(Utility::priceFormat($settings, $price));} catch (\InvalidArgumentException $e) {Log::warning('Item price fmt: ' . $e->getMessage());
                                        echo '0';} catch (\Exception $e) {Log::error('Item price fmt: ' . $e->getMessage());
                                        echo '0';} catch (\Throwable $e) {Log::critical('Item price fmt: ' . $e->getMessage());
                                        echo '0';} ?></td>
                                <td>
                                    <?php if (!empty(data_get($item, 'itemTax'))): ?>
                                        <?php foreach ((array) data_get($item, 'itemTax', []) as $taxes): ?>
                                            <?php
                                            try {$rateRaw = (string) data_get($taxes, 'rate', '0');
                                                $res     = str_ireplace(['%'], ' ', $rateRaw);
                                                $taxP    = Utility::taxRate($res, $price, $qty);
                                                $totalTaxPrice += (float) $taxP;} catch (\InvalidArgumentException $e) {Log::warning('Tax calc InvalidArgument: ' . $e->getMessage());} catch (\Exception $e) {Log::error('Tax calc Exception: ' . $e->getMessage());} catch (\Throwable $e) {Log::critical('Tax calc Throwable: ' . $e->getMessage());}
                                            ?>
                                            <span><?= e(data_get($taxes, 'name', 'Tax')) ?></span> <span>(<?= e(data_get($taxes, 'rate', '0')) ?>)</span><br>
                                        <?php endforeach; ?>
                                        <?php else: ?>-<?php endif; ?>
                                </td>
                                <td><?php try {echo e(Utility::priceFormat($settings, $totalTaxPrice));} catch (\InvalidArgumentException $e) {Log::warning('Tax amt fmt: ' . $e->getMessage());
                                        echo '0';} catch (\Exception $e) {Log::error('Tax amt fmt: ' . $e->getMessage());
                                        echo '0';} catch (\Throwable $e) {Log::critical('Tax amt fmt: ' . $e->getMessage());
                                        echo '0';} ?></td>
                                <td><?php try {echo e(Utility::priceFormat($settings, ($price * $qty) + $totalTaxPrice));} catch (\InvalidArgumentException $e) {Log::warning('Line total fmt: ' . $e->getMessage());
                                        echo '0';} catch (\Exception $e) {Log::error('Line total fmt: ' . $e->getMessage());
                                        echo '0';} catch (\Throwable $e) {Log::critical('Line total fmt: ' . $e->getMessage());
                                        echo '0';} ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4"></td>
                        <td colspan="2" class="sub-total">
                            <table class="total-table">
                                <tr>
                                    <td><?= e(__('Subtotal')) ?>:</td>
                                    <td><?php try {echo e(Utility::priceFormat($settings, (float) data_get($posPayment, 'amount', 0)));} catch (\InvalidArgumentException $e) {Log::warning('Subtotal fmt: ' . $e->getMessage());
                                            echo '0';} catch (\Exception $e) {Log::error('Subtotal fmt: ' . $e->getMessage());
                                            echo '0';} catch (\Throwable $e) {Log::critical('Subtotal fmt: ' . $e->getMessage());
                                            echo '0';} ?></td>
                                </tr>
                                <tr>
                                    <td><?= e(__('Discount')) ?>:</td>
                                    <td>
                                        <?php try {$disc = data_get($posPayment, 'discount', null);
                                            echo !empty($disc) ? e(Utility::priceFormat($settings, (float)$disc)) : '-';} catch (\InvalidArgumentException $e) {Log::warning('Discount fmt: ' . $e->getMessage());
                                            echo '-';} catch (\Exception $e) {Log::error('Discount fmt: ' . $e->getMessage());
                                            echo '-';} catch (\Throwable $e) {Log::critical('Discount fmt: ' . $e->getMessage());
                                            echo '-';} ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= e(__('Total')) ?>:</td>
                                    <td>
                                        <?php try {$amt = (float) data_get($posPayment, 'amount', 0);
                                            $disc = (float) (data_get($posPayment, 'discount', 0) ?? 0);
                                            echo e(Utility::priceFormat($settings, $amt - $disc));} catch (\InvalidArgumentException $e) {Log::warning('Total fmt: ' . $e->getMessage());
                                            echo '0';} catch (\Exception $e) {Log::error('Total fmt: ' . $e->getMessage());
                                            echo '0';} catch (\Throwable $e) {Log::critical('Total fmt: ' . $e->getMessage());
                                            echo '0';} ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="pos-footer">
                <b><?= e(data_get($settings, 'footer_title', '')) ?></b> <br>
                <?php try {echo (string) data_get($settings, 'footer_notes', '');} catch (\InvalidArgumentException $e) {Log::warning('Footer notes InvalidArgument: ' . $e->getMessage());} catch (\Exception $e) {Log::error('Footer notes Exception: ' . $e->getMessage());} catch (\Throwable $e) {Log::critical('Footer notes Throwable: ' . $e->getMessage());} ?>
            </div>
        </div>
    </div>

    <?php if (!isset($preview)): ?>
        <?php try {echo view('pos.script')->render();} catch (\InvalidArgumentException $e) {Log::warning('pos.script include InvalidArgument: ' . $e->getMessage());} catch (\Exception $e) {Log::error('pos.script include Exception: ' . $e->getMessage());} catch (\Throwable $e) {Log::critical('pos.script include Throwable: ' . $e->getMessage());} ?>
    <?php endif; ?>
</body>

</html>