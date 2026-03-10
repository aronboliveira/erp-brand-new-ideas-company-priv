<?php
# Template 6
use App\Config\Constants\{DatabaseConstants as DC, SettingsConstants as SC, ViewsConstants as VW};
use App\Models\Utility;
use Illuminate\Support\Facades\{Crypt, Log, Route};
use Milon\Barcode\DNS2D;
use App\Helpers\TemplateHelper;

if (!function_exists('e')) {function e($v)
    {
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');}
}

$bill           ??= null;
$vendor         ??= null;
$settings       ??= [];
$settings_data  ??= [];
$customFields   ??= [];
$meta_title     ??= '';
$meta_desc      ??= '';
$themeCSS       ??= '';
$color          ??= '#4b4b4b';
$font_color     ??= '#000000';
$img            ??= '';
$preview        ??= null;

$lang = Utility::fetchUserLang();
try {$docLang ??= $lang ?? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DC::DEFAULT_LANG);} catch (\InvalidArgumentException $e) {Log::warning('DocLang: ' . $e->getMessage());
    $docLang = DC::DEFAULT_LANG;} catch (\Exception $e) {Log::error('DocLang: ' . $e->getMessage());
    $docLang = DC::DEFAULT_LANG;} catch (\Throwable $e) {Log::critical('DocLang: ' . $e->getMessage());
    $docLang = DC::DEFAULT_LANG;}

if (trim((string)$themeCSS) === '') {$themeCSS = ":root { --theme-color: {$color}; --white: #ffffff; --black: #000000; }";
}

if (empty($bill)) {echo TemplateHelper::getNoDataHtml('bill', $docLang);
    return;}

try {$settings_data = Utility::settingsById(data_get($bill, DC::COL_TABLE_CREATOR));} catch (\InvalidArgumentException $e) {Log::warning('settingsById: ' . $e->getMessage());
    $settings_data = [];} catch (\Exception $e) {Log::error('settingsById: ' . $e->getMessage());
    $settings_data = [];} catch (\Throwable $e) {Log::critical('settingsById: ' . $e->getMessage());
    $settings_data = [];}

$dir = (data_get($settings_data, SC::RTL) === 'on') ? 'rtl' : '';

try {$billNumber = Utility::billNumberFormat($settings, data_get($bill, 'bill_id')) ?: __('Could not find Bill Identifier');} catch (\InvalidArgumentException $e) {Log::warning('billNumber: ' . $e->getMessage());
    $billNumber = __('Could not find Bill Identifier');} catch (\Exception $e) {Log::error('billNumber: ' . $e->getMessage());
    $billNumber = __('Could not find Bill Identifier');} catch (\Throwable $e) {Log::critical('billNumber: ' . $e->getMessage());
    $billNumber = __('Could not find Bill Identifier');}
try {$billDate = Utility::dateFormat($settings, data_get($bill, 'issue_date')) ?: __('Failed to get bill date');} catch (\InvalidArgumentException $e) {Log::warning('billDate: ' . $e->getMessage());
    $billDate = __('Failed to get bill date');} catch (\Exception $e) {Log::error('billDate: ' . $e->getMessage());
    $billDate = __('Failed to get bill date');} catch (\Throwable $e) {Log::critical('billDate: ' . $e->getMessage());
    $billDate = __('Failed to get bill date');}
try {$dueDate = Utility::dateFormat($settings, data_get($bill, 'due_date')) ?: __('Failed to get due date');} catch (\InvalidArgumentException $e) {Log::warning('dueDate: ' . $e->getMessage());
    $dueDate = __('Failed to get due date');} catch (\Exception $e) {Log::error('dueDate: ' . $e->getMessage());
    $dueDate = __('Failed to get due date');} catch (\Throwable $e) {Log::critical('dueDate: ' . $e->getMessage());
    $dueDate = __('Failed to get due date');}

$qrValue = '#';
try {$base = VW::BIL . '.link.copy';
    $resolved = Route::has($base) ? $base : null;
    $id = data_get($bill, 'bill_id');
    $enc = $id ? Crypt::encrypt($id) : null;
    $qrValue = ($resolved && $enc) ? route($resolved, $enc) : '#';} catch (\InvalidArgumentException $e) {Log::warning('QR route: ' . $e->getMessage());
    $qrValue = '#';} catch (\Exception $e) {Log::error('QR route: ' . $e->getMessage());
    $qrValue = '#';} catch (\Throwable $e) {Log::critical('QR route: ' . $e->getMessage());
    $qrValue = '#';}
?>
<!DOCTYPE html>
<html lang="<?= e($docLang) ?>" dir="<?= e($dir) ?>">

<head>
    <?php
    try {echo view('fragments.std', ['meta_title' => $meta_title, 'meta_desc' => $meta_desc])->render();} catch (\InvalidArgumentException $e) {Log::warning('Meta view: ' . $e->getMessage());} catch (\Exception $e) {Log::error('Meta view: ' . $e->getMessage());} catch (\Throwable $e) {Log::critical('Meta view: ' . $e->getMessage());}
    ?>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        <?= $themeCSS ?>
    </style>
    <style type="text/css">
        body {font-family: 'Lato', sans-serif}

        p,
        li,
        ul,
        ol {margin: 0;
            padding: 0;
            list-style: none;
            line-height: 1.5}

        * {margin: 0;
            padding: 0;
            box-sizing: border-box}

        table {width: 100%;
            border-collapse: collapse}

        table tr th,
        table tr td {padding: .75rem;
            text-align: left}

        table th small {display: block;
            font-size: 12px}

        .bill-preview-main {max-width: 700px;
            width: 100%;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 0 10px #ddd}

        .bill-logo {max-width: 200px;
            width: 100%}

        .bill-header table td {padding: 15px 30px}

        .text-right {text-align: right}

        .no-space tr td {padding: 0}

        .vertical-align-top td {vertical-align: top}

        .view-qrcode {max-width: 114px;
            height: 114px;
            margin-left: auto;
            margin-top: 15px;
            background: var(--white)}

        .view-qrcode img {width: 100%;
            height: 100%}

        .bill-body {padding: 30px 25px 0}

        table.add-border tr {border-top: 1px solid var(--theme-color)}

        tfoot tr:first-of-type {border-bottom: 1px solid var(--theme-color)}

        .total-table tr:first-of-type td {padding-top: 0}

        .total-table tr:first-of-type {border-top: 0}

        .sub-total {padding-right: 0;
            padding-left: 0}

        .border-0 {border: none !important}

        .bill-summary td,
        .bill-summary th {font-size: 13px;
            font-weight: 600}

        .total-table td:last-of-type {width: 146px}

        .bill-footer {padding: 15px 20px}

        .itm-description td {padding-top: 0}

        html[dir="rtl"] table tr td,
        html[dir="rtl"] table tr th {text-align: right}

        html[dir="rtl"] .text-right {text-align: left}

        html[dir="rtl"] .view-qrcode {margin-left: 0;
            margin-right: auto}

        p:not(:last-of-type) {margin-bottom: 15px}

        .bill-summary p {margin-bottom: 0}
    </style>
    <?php if (data_get($settings_data, SC::RTL) === 'on'): ?>
        <link rel="stylesheet" href="<?= e(asset('css/bootstrap-rtl.css')) ?>">
    <?php endif; ?>
</head>

<body>
    <div class="bill-preview-main" id="boxes">
        <div class="bill-header" style="border-top: 15px solid <?= e($color) ?>">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <h3 style="text-transform:uppercase;font-size:40px;font-weight:bold;"><?= e(__('BILL')) ?></h3>
                        </td>
                        <td class="text-right"><img class="bill-logo" src="<?= e($img) ?>" alt=""></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="bill-body">
            <table class="vertical-align-top">
                <tbody>
                    <tr>
                        <td style="font-size:13px;">
                            <strong style="margin-bottom:10px;display:block;"><?= e(__('From:')) ?></strong>
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
                                if (!empty($settings['registration_number'])) {echo e(__('Registration Number')) . ' : ' . e($settings['registration_number']) . '<br>';} else {echo e(__('No registration number available')) . '<br>';}
                                if (!empty($settings['tax_type']) && !empty($settings['vat_number'])) {echo e($settings['tax_type'] . ' ' . __('Number')) . ' : ' . e($settings['vat_number']) . ' <br>';} else {echo e(__('No tax number available')) . '<br>';}
                                ?>
                            </p>
                        </td>

                        <td style="font-size:13px;">
                            <strong style="margin-bottom:10px;display:block;"><?= e(__('Bill To:')) ?></strong>
                            <p>
                                <?= e(data_get($vendor, 'billing_name', __('No name for billing available.'))) ?><br>
                                <?= e(data_get($vendor, 'billing_address', __('No address for billing available.'))) ?><br>
                                <?= e(data_get($vendor, 'billing_city', __('No city for billing available.'))) ?><?= !empty(data_get($vendor, 'billing_city')) ? ', ' : '' ?><br>
                                <?= e(data_get($vendor, 'billing_state', __('No state for billing available.'))) ?><?= !empty(data_get($vendor, 'billing_state')) ? ', ' : '' ?>,
                                <?= e(data_get($vendor, 'billing_zip', __('No zip for billing available.'))) ?><br>
                                <?= e(data_get($vendor, 'billing_country', __('No country for billing available.'))) ?><br>
                                <?= e(data_get($vendor, 'billing_phone', __('No phone for billing available'))) ?><br>
                            </p>
                        </td>

                        <td style="font-size:13px;" class="text-right">
                            <strong style="margin-bottom:10px;display:block;"><?= e(__('Ship To:')) ?></strong>
                            <p>
                                <?= e(data_get($vendor, 'shipping_name', __('No name for shipping available.'))) ?><br>
                                <?= e(data_get($vendor, 'shipping_address', __('No address for shipping available.'))) ?><br>
                                <?= e(data_get($vendor, 'shipping_city', __('No city for shipping available.'))) ?><?= !empty(data_get($vendor, 'shipping_city')) ? ', ' : '' ?><br>
                                <?= e(data_get($vendor, 'shipping_state', __('No state for shipping available.'))) ?><?= !empty(data_get($vendor, 'shipping_state')) ? ', ' : '' ?>,
                                <?= e(data_get($vendor, 'shipping_zip', __('No zip for shipping available.'))) ?><br>
                                <?= e(data_get($vendor, 'shipping_country', __('No country for shipping available.'))) ?><br>
                                <?= e(data_get($vendor, 'shipping_phone', __('No phone for shipping available.'))) ?><br>
                            </p>
                        </td>
                    </tr>

                    <tr style="border-bottom:1px solid <?= e($color) ?>">
                        <td>
                            <p>
                                <?php
                                echo !empty($settings['registration_number'])
                                    ? e(__('Registration Number')) . ' : ' . e($settings['registration_number']) . '<br>'
                                    : e(__('No registration number available')) . '<br>';
                                echo (!empty($settings['tax_type']) && !empty($settings['vat_number']))
                                    ? e($settings['tax_type'] . ' ' . __('Number')) . ' : ' . e($settings['vat_number']) . ' <br>'
                                    : e(__('No tax number available')) . '<br>';
                                ?>
                            </p>
                        </td>
                        <td colspan="2">
                            <div class="view-qrcode" style="margin-top:0;">
                                <?php
                                try {echo (new \Milon\Barcode\DNS2D)->getBarcodeHTML($qrValue, 'QRCODE', 2, 2);} catch (\InvalidArgumentException $e) {Log::warning('QR HTML: ' . $e->getMessage());
                                    echo '<div></div>';} catch (\Exception $e) {Log::error('QR HTML: ' . $e->getMessage());
                                    echo '<div></div>';} catch (\Throwable $e) {Log::critical('QR HTML: ' . $e->getMessage());
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
                                        <td class="text-right"><?= e($billNumber) ?></td>
                                    </tr>
                                    <tr>
                                        <td><?= e(__('Bill Date')) ?>:</td>
                                        <td class="text-right"><?= e($billDate) ?></td>
                                    </tr>
                                    <tr>
                                        <td><?= e(__('Due Date')) ?>:</td>
                                        <td class="text-right"><?= e($dueDate) ?></td>
                                    </tr>
                                    <?php if (!empty($customFields) && count(data_get($bill, 'customField', [])) > 0): ?>
                                        <?php foreach ($customFields as $field): ?>
                                            <tr>
                                                <td><?= e(data_get($field, 'name', '')) ?> :</td>
                                                <td><?= e(data_get($bill->customField, $field->id) ?? '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="add-border bill-summary" style="margin-top:30px;">
                <thead style="background: <?= e($color) ?>; color: <?= e($font_color) ?>">
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
                    <?php if (isset($bill->itemData) && is_iterable($bill->itemData) && count($bill->itemData) > 0): ?>
                        <?php foreach ($bill->itemData as $item): ?>
                            <?php
                            $price   = (float) data_get($item, 'price', 0);
                            $qty     = (float) data_get($item, 'quantity', 0);
                            $disc    = (float) data_get($item, 'discount', 0);
                            $itemtax = 0.0;
                            $unitLbl = '';
                            try {$unit = \App\Models\ProductServiceUnit::find(data_get($item, 'unit'));
                                $unitLbl = data_get($unit, 'name', '');} catch (\InvalidArgumentException $e) {Log::warning('Unit find: ' . $e->getMessage());} catch (\Exception $e) {Log::error('Unit find: ' . $e->getMessage());} catch (\Throwable $e) {Log::critical('Unit find: ' . $e->getMessage());}
                            ?>
                            <tr>
                                <td><?= e(data_get($item, 'name', '')) ?></td>
                                <td><?= e((string)$qty . ($unitLbl ? ' (' . $unitLbl . ')' : '')) ?></td>
                                <td><?php try {echo e(Utility::priceFormat($settings, $price));} catch (\InvalidArgumentException $e) {Log::warning('Rate fmt: ' . $e->getMessage());
                                        echo '0';} catch (\Exception $e) {Log::error('Rate fmt: ' . $e->getMessage());
                                        echo '0';} catch (\Throwable $e) {Log::critical('Rate fmt: ' . $e->getMessage());
                                        echo '0';} ?></td>
                                <td><?php try {echo $disc != 0.0 ? e(Utility::priceFormat($settings, $disc)) : '-';} catch (\InvalidArgumentException $e) {Log::warning('Disc fmt: ' . $e->getMessage());
                                        echo '-';} catch (\Exception $e) {Log::error('Disc fmt: ' . $e->getMessage());
                                        echo '-';} catch (\Throwable $e) {Log::critical('Disc fmt: ' . $e->getMessage());
                                        echo '-';} ?></td>
                                <td>
                                    <?php if (!empty(data_get($item, 'itemTax'))): ?>
                                        <?php foreach ((array)$item->itemTax as $taxes): ?>
                                            <?php $itemtax += (float) data_get($taxes, 'tax_price', 0); ?>
                                            <p><?= e((data_get($taxes, 'name') ?? 'Tax') . ' (' . (data_get($taxes, 'rate') ?? '0') . ') ' . (data_get($taxes, 'price') ?? '0')) ?></p>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span>-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    try {echo e(Utility::priceFormat($settings, $price * $qty - $disc + $itemtax));} catch (\InvalidArgumentException $e) {Log::warning('Line total: ' . $e->getMessage());
                                        echo '0';} catch (\Exception $e) {Log::error('Line total: ' . $e->getMessage());
                                        echo '0';} catch (\Throwable $e) {Log::critical('Line total: ' . $e->getMessage());
                                        echo '0';}
                                    ?>
                                </td>
                            </tr>
                            <?php if (!empty(data_get($item, 'description'))): ?>
                                <tr class="border-0 itm-description">
                                    <td colspan="6" style="border-bottom:1px solid <?= e($color) ?>"><?= e((string) data_get($item, 'description')) ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

                <tfoot>
                    <tr style="border-bottom:1px solid <?= e($color) ?>">
                        <td><?= e(__('Total')) ?></td>
                        <td><?= e((string) data_get($bill, 'totalQuantity', '0')) ?></td>
                        <td><?php try {echo e(Utility::priceFormat($settings, data_get($bill, 'totalRate', 0)));} catch (\InvalidArgumentException $e) {Log::warning('totalRate: ' . $e->getMessage());
                                echo '0';} catch (\Exception $e) {Log::error('totalRate: ' . $e->getMessage());
                                echo '0';} catch (\Throwable $e) {Log::critical('totalRate: ' . $e->getMessage());
                                echo '0';} ?></td>
                        <td><?php try {echo e(Utility::priceFormat($settings, data_get($bill, 'totalDiscount', 0)));} catch (\InvalidArgumentException $e) {Log::warning('totalDiscount: ' . $e->getMessage());
                                echo '0';} catch (\Exception $e) {Log::error('totalDiscount: ' . $e->getMessage());
                                echo '0';} catch (\Throwable $e) {Log::critical('totalDiscount: ' . $e->getMessage());
                                echo '0';} ?></td>
                        <td><?php try {echo e(Utility::priceFormat($settings, data_get($bill, 'totalTaxPrice', 0)));} catch (\InvalidArgumentException $e) {Log::warning('totalTaxPrice: ' . $e->getMessage());
                                echo '0';} catch (\Exception $e) {Log::error('totalTaxPrice: ' . $e->getMessage());
                                echo '0';} catch (\Throwable $e) {Log::critical('totalTaxPrice: ' . $e->getMessage());
                                echo '0';} ?></td>
                        <td><?php try {echo e(Utility::priceFormat($settings, method_exists($bill, 'getSubTotal') ? $bill->getSubTotal() : 0));} catch (\InvalidArgumentException $e) {Log::warning('subTotal: ' . $e->getMessage());
                                echo '0';} catch (\Exception $e) {Log::error('subTotal: ' . $e->getMessage());
                                echo '0';} catch (\Throwable $e) {Log::critical('subTotal: ' . $e->getMessage());
                                echo '0';} ?></td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                        <td colspan="2" class="sub-total">
                            <table class="total-table">
                                <tr style="border-bottom:1px solid <?= e($color) ?>">
                                    <td><?= e(__('Subtotal')) ?>:</td>
                                    <td><?php try {echo e(Utility::priceFormat($settings, method_exists($bill, 'getSubTotal') ? $bill->getSubTotal() : 0));} catch (\InvalidArgumentException $e) {Log::warning('Subtotal fmt: ' . $e->getMessage());
                                            echo '0';} catch (\Exception $e) {Log::error('Subtotal fmt: ' . $e->getMessage());
                                            echo '0';} catch (\Throwable $e) {Log::critical('Subtotal fmt: ' . $e->getMessage());
                                            echo '0';} ?></td>
                                </tr>
                                <?php if (method_exists($bill, 'getTotalDiscount') && $bill->getTotalDiscount()): ?>
                                    <tr style="border-bottom:1px solid <?= e($color) ?>">
                                        <td><?= e(__('Discount')) ?>:</td>
                                        <td><?php try {echo e(Utility::priceFormat($settings, $bill->getTotalDiscount()));} catch (\InvalidArgumentException $e) {Log::warning('Discount total: ' . $e->getMessage());
                                                echo '0';} catch (\Exception $e) {Log::error('Discount total: ' . $e->getMessage());
                                                echo '0';} catch (\Throwable $e) {Log::critical('Discount total: ' . $e->getMessage());
                                                echo '0';} ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!empty($bill->taxesData)): ?>
                                    <?php foreach ($bill->taxesData as $taxName => $taxPrice): ?>
                                        <tr style="border-bottom:1px solid <?= e($color) ?>">
                                            <td><?= e($taxName) ?> :</td>
                                            <td><?php try {echo e(Utility::priceFormat($settings, $taxPrice));} catch (\InvalidArgumentException $e) {Log::warning('Tax row: ' . $e->getMessage());
                                                    echo '0';} catch (\Exception $e) {Log::error('Tax row: ' . $e->getMessage());
                                                    echo '0';} catch (\Throwable $e) {Log::critical('Tax row: ' . $e->getMessage());
                                                    echo '0';} ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <tr style="border-bottom:1px solid <?= e($color) ?>">
                                    <td><?= e(__('Total')) ?>:</td>
                                    <td><?php
                                        try {$total = (method_exists($bill, 'getSubTotal') ? $bill->getSubTotal() : 0)
                                                - (method_exists($bill, 'getTotalDiscount') ? $bill->getTotalDiscount() : 0)
                                                + (method_exists($bill, 'getTotalTax') ? $bill->getTotalTax() : 0);
                                            echo e(Utility::priceFormat($settings, $total));} catch (\InvalidArgumentException $e) {Log::warning('Grand total: ' . $e->getMessage());
                                            echo '0';} catch (\Exception $e) {Log::error('Grand total: ' . $e->getMessage());
                                            echo '0';} catch (\Throwable $e) {Log::critical('Grand total: ' . $e->getMessage());
                                            echo '0';}
                                        ?></td>
                                </tr>
                                <tr style="border-bottom:1px solid <?= e($color) ?>">
                                    <td><?= e(__('Paid')) ?>:</td>
                                    <td><?php
                                        try {$paid = (method_exists($bill, 'getTotal') ? $bill->getTotal() : 0)
                                                - (method_exists($bill, 'getDue') ? $bill->getDue() : 0)
                                                - (method_exists($bill, 'billTotalDebitNote') ? $bill->billTotalDebitNote() : 0);
                                            echo e(Utility::priceFormat($settings, $paid));} catch (\InvalidArgumentException $e) {Log::warning('Paid: ' . $e->getMessage());
                                            echo '0';} catch (\Exception $e) {Log::error('Paid: ' . $e->getMessage());
                                            echo '0';} catch (\Throwable $e) {Log::critical('Paid: ' . $e->getMessage());
                                            echo '0';}
                                        ?></td>
                                </tr>
                                <tr style="border-bottom:1px solid <?= e($color) ?>">
                                    <td><?= e(__('Debit Note')) ?>:</td>
                                    <td><?php
                                        try {$debit = method_exists($bill, 'billTotalDebitNote') ? $bill->billTotalDebitNote() : 0;
                                            echo e(Utility::priceFormat($settings, $debit));} catch (\InvalidArgumentException $e) {Log::warning('Debit: ' . $e->getMessage());
                                            echo '0';} catch (\Exception $e) {Log::error('Debit: ' . $e->getMessage());
                                            echo '0';} catch (\Throwable $e) {Log::critical('Debit: ' . $e->getMessage());
                                            echo '0';}
                                        ?></td>
                                </tr>
                                <tr style="border-bottom:1px solid <?= e($color) ?>">
                                    <td><?= e(__('Due Amount')) ?>:</td>
                                    <td><?php
                                        try {echo e(Utility::priceFormat($settings, method_exists($bill, 'getDue') ? $bill->getDue() : 0));} catch (\InvalidArgumentException $e) {Log::warning('Due: ' . $e->getMessage());
                                            echo '0';} catch (\Exception $e) {Log::error('Due: ' . $e->getMessage());
                                            echo '0';} catch (\Throwable $e) {Log::critical('Due: ' . $e->getMessage());
                                            echo '0';}
                                        ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="bill-footer">
                <b><?= e(data_get($settings, 'footer_title', '')) ?></b><br>
                <?php try {echo (string) data_get($settings, 'footer_notes', '');} catch (\InvalidArgumentException $e) {Log::warning('Footer notes: ' . $e->getMessage());} catch (\Exception $e) {Log::error('Footer notes: ' . $e->getMessage());} catch (\Throwable $e) {Log::critical('Footer notes: ' . $e->getMessage());} ?>
            </div>
        </div>
    </div>

    <?php if (!isset($preview)): ?>
        <?php
        try {echo view(VW::BIL . '.script')->render();} catch (\InvalidArgumentException $e) {Log::warning('Script include: ' . $e->getMessage());} catch (\Exception $e) {Log::error('Script include: ' . $e->getMessage());} catch (\Throwable $e) {Log::critical('Script include: ' . $e->getMessage());}
        ?>
    <?php endif; ?>
</body>

</html>