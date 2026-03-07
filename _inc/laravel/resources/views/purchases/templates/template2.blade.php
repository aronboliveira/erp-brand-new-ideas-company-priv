<?php
/* @formatter:off */
// Template 2

use App\Config\Constants\{DatabaseConstants, SettingsConstants, ViewClassNamesConstants};
use App\Models\Utility;
use Illuminate\Support\Facades\{Auth, Crypt, Log, Route};
use Illuminate\Support\Str;
use Milon\Barcode\DNS2D;

$user = Auth::user();
$lang = Utility::fetchUserLang(user: $user);

if (isset($purchase) && !empty($purchase)) {

    if (!function_exists('e')) {
        function e($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
    }

    // Defaults
    $vendor ??= null;
    $settings ??= [];
    $settings_data ??= [];
    $customFields ??= [];
    $meta_title ??= '';
    $meta_desc ??= '';
    $themeCSS ??= ":root { --theme-color: {$color}; --white: #ffffff; --black: #000000; }";
    $color ??= '#ffffff';
    $font_color ??= '#000000';
    $img ??= '';
    $preview ??= null;
    $docLang ??= null;
    $dir ??= '';
    $purchaseNumber ??= '';
    $purchaseDate ??= '';
    $qrValue ??= '#';
    $qrHtml ??= '';
    $purchaseTotalQuantity ??= '0';
    $purchaseTotalRate ??= '0';
    $purchaseTotalDiscount ??= '0';
    $purchaseTotalTaxPrice ??= '0';
    $purchaseSubTotal ??= '0';
    $purchaseGrandTotal ??= '0';
    $purchasePaid ??= '0';
    $purchaseDue ??= '0';

    // Doc language (fallback to app locale or DEFAULT_LANG)
    try {
        $docLang ??= $lang ?? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG);
    } catch (\Throwable $e) {
        Log::error('DocLang Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__);
        $docLang ??= DatabaseConstants::DEFAULT_LANG;
    }

    // Settings data
    try {
        $creatorId = Utility::isFilled($purchase ?? [])
            ? ($purchase[DatabaseConstants::COL_TABLE_CREATOR] ?? null)
            : (data_get($purchase, DatabaseConstants::COL_TABLE_CREATOR) ?? data_get($purchase, 'created_by'));
        $settings_data ??= Utility::settingsById($creatorId);
    } catch (\Throwable $e) {
        Log::error('SettingsById Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__);
        $settings_data ??= [];
    }

    // Direction
    try {
        $dir = (data_get($settings_data, SettingsConstants::RTL) === 'on') ? 'rtl' : '';
    } catch (\Throwable $e) {
        Log::error('RTL Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__);
        $dir = '';
    }

    // Number & date
    try {
        $purchaseNumber = Utility::purchaseNumberFormat($settings, data_get($purchase, 'purchase_id')) ?: __('Could not find purchase number');
    } catch (\Throwable $e) {
        Log::error('PurchaseNumber Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__);
        $purchaseNumber = __('Could not find purchase number');
    }
    try {
        $purchaseDate = Utility::dateFormat($settings, data_get($purchase, 'purchase_date')) ?: __('Failed to get purchase date');
    } catch (\Throwable $e) {
        Log::error('PurchaseDate Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__);
        $purchaseDate = __('Failed to get purchase date');
    }

    // QR value
    try {
        $base = 'purchase.link.copy';
        $kebab = Str::kebab($base);
        $resolved = Route::has($base) ? $base : (Route::has($kebab) ? $kebab : null);
        $pid = data_get($purchase, 'purchase_id');
        $enc = $pid ? Crypt::encrypt($pid) : null;
        $qrValue = ($resolved && $enc) ? route($resolved, $enc) : '#';
        if ($qrValue === '#') {
            Log::error('QR route unavailable or param missing | route='.($resolved ?? 'null').' | file='.__FILE__.' | line='.__LINE__);
        }
    } catch (\Throwable $e) {
        Log::error('QR Route Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__);
        $qrValue = '#';
    }

    // Totals
    try {
        $purchaseTotalQuantity = (string)(data_get($purchase, 'totalQuantity') ?? '0');
        $purchaseTotalRate = Utility::priceFormat($settings, data_get($purchase, 'totalRate')) ?: '0';
        $purchaseTotalDiscount = Utility::priceFormat($settings, data_get($purchase, 'totalDiscount')) ?: '0';
        $purchaseTotalTaxPrice = Utility::priceFormat($settings, data_get($purchase, 'totalTaxPrice')) ?: '0';
        $purchaseSubTotal = Utility::priceFormat($settings, data_get($purchase, 'getSubTotal') ? $purchase->getSubTotal() : 0) ?: '0';
        $purchaseGrandTotal = Utility::priceFormat(
            $settings,
            (data_get($purchase, 'getSubTotal') ? $purchase->getSubTotal() : 0)
            - (data_get($purchase, 'getTotalDiscount') ? $purchase->getTotalDiscount() : 0)
            + (data_get($purchase, 'getTotalTax') ? $purchase->getTotalTax() : 0)
        ) ?: '0';
        $purchasePaid = Utility::priceFormat($settings, (method_exists($purchase, 'getTotal') ? $purchase->getTotal() : 0) - (method_exists($purchase, 'getDue') ? $purchase->getDue() : 0)) ?: '0';
        $purchaseDue  = Utility::priceFormat($settings, method_exists($purchase, 'getDue') ? $purchase->getDue() : 0) ?: '0';
    } catch (\Throwable $e) {
        Log::error('Totals Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__);
        $purchaseTotalQuantity = '0';
        $purchaseTotalRate = '0';
        $purchaseTotalDiscount = '0';
        $purchaseTotalTaxPrice = '0';
        $purchaseSubTotal = '0';
        $purchaseGrandTotal = '0';
        $purchasePaid = '0';
        $purchaseDue = '0';
    }
?>
    <!DOCTYPE html>
    <html lang="<?= e($docLang) ?>" dir="<?= e($dir) ?>">

    <head>
        <?php
    try {
        echo view('fragments.std', ['meta_title' => $meta_title, 'meta_desc' => $meta_desc, 'meta_vp' => ''])->render();
    } catch (\Throwable $e) {
        Log::error('Meta view Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__);
    }
    ?>
        <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
        <style>
            <?= $themeCSS ?>
        </style>
        <style type="text/css">
            body {
                font-family: 'Lato', sans-serif;
            }

            p,
            li,
            ul,
            ol {
                margin: 0;
                padding: 0;
                list-style: none;
                line-height: 1.5;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            table tr th {
                padding: .75rem;
                text-align: left;
            }

            table tr td {
                padding: .75rem;
                text-align: left;
            }

            table th small {
                display: block;
                font-size: 12px;
            }

            .purchase-preview-main {
                max-width: 700px;
                width: 100%;
                margin: 0 auto;
                background: #ffff;
                box-shadow: 0 0 10px #ddd;
            }

            .purchase-logo {
                max-width: 200px;
                width: 100%;
            }

            .purchase-header table td {
                padding: 15px 30px;
            }

            .text-right {
                text-align: right;
            }

            .no-space tr td {
                padding: 0;
            }

            .vertical-align-top td {
                vertical-align: top;
            }

            .view-qrcode {
                max-width: 114px;
                height: 114px;
                margin-left: auto;
                margin-top: 15px;
                background: var(--white);
            }

            .view-qrcode img {
                width: 100%;
                height: 100%;
            }

            .purchase-body {
                padding: 30px 25px 0;
            }

            table.add-border tr {
                border-top: 1px solid var(--theme-color);
            }

            tfoot tr:first-of-type {
                border-bottom: 1px solid var(--theme-color);
            }

            .total-table tr:first-of-type td {
                padding-top: 0;
            }

            .total-table tr:first-of-type {
                border-top: 0;
            }

            .sub-total {
                padding-right: 0;
                padding-left: 0;
            }

            .border-0 {
                border: none !important;
            }

            .purchase-summary td,
            .purchase-summary th {
                font-size: 13px;
                font-weight: 600;
            }

            .total-table td:last-of-type {
                width: 146px;
            }

            .purchase-footer {
                padding: 15px 20px;
            }

            .itm-description td {
                padding-top: 0;
            }

            html[dir="rtl"] table tr td,
            html[dir="rtl"] table tr th {
                text-align: right;
            }

            html[dir="rtl"] .text-right {
                text-align: left;
            }

            html[dir="rtl"] .view-qrcode {
                margin-left: 0;
                margin-right: auto;
            }

            p:not(:last-of-type) {
                margin-bottom: 15px;
            }

            .purchase-summary p {
                margin-bottom: 0;
            }
        </style>
        <?php if (data_get($settings_data, SettingsConstants::RTL) === 'on'): ?>
            <link rel="stylesheet" href="<?= e(asset('css/bootstrap-rtl.css')) ?>">
        <?php endif; ?>
    </head>

    <body>
        <div class="purchase-preview-main" id="boxes">
            <div class="purchase-header">
                <table class="vertical-align-top">
                    <tbody>
                        <tr>
                            <td><img class="purchase-logo" src="<?= e($img) ?>" alt=""></td>
                            <td class="text-right">
                                <p>
                                    <?= e(data_get($settings,'company_name', __('No company name available.'))) ?><br>
                                    <?= e(data_get($settings,'mail_from_address', __('No email available.'))) ?><br><br>
                                    <?= e(data_get($settings,'company_address', __('No address available.'))) ?>
                                    <?php $city=(string)data_get($settings,'company_city',''); echo $city!==''? '<br>'.e($city).', ' : '<br>'.e(__('No city available.')).' '; ?>
                                    <?php $state=(string)data_get($settings,'company_state',''); echo $state!==''? e($state) : e(__('No state available.')); ?>
                                    <?php $zip=(string)data_get($settings,'company_zipcode',''); echo $zip!==''? ' - '.e($zip) : ' - '.e(__('No zipcode available.')); ?>
                                    <?php $country=(string)data_get($settings,'company_country',''); echo $country!==''? '<br>'.e($country) : '<br>'.e(__('No country available.')); ?>
                                    <?= e(data_get($settings,'company_telephone', __('No phone available.'))) ?><br>
                                    <?php $reg=(string)data_get($settings,'registration_number',''); if($reg!==''){ echo e(__('Registration Number')).' : '.e($reg).' <br>'; } ?>
                                    <?php if (data_get($settings,'vat_gst_number_switch')==='on') {
                            if (!empty($settings['tax_type']) && !empty($settings['vat_number'])) {
                                echo e($settings['tax_type'].' '.__('Number')).' : '.e($settings['vat_number']).' <br>';
                            }
                        } ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table class="vertical-align-top">
                    <tbody>
                        <tr>
                            <td>
                                <h3 style="text-transform:uppercase;font-size:25px;font-weight:bold;margin-bottom:15px;"><?= e(__('PURCHASE')) ?></h3>
                                <table class="no-space">
                                    <tbody>
                                        <tr>
                                            <td><?= e(__('Number')) ?>:</td>
                                            <td class="text-right"><?= e($purchaseNumber) ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= e(__('Purchase Date')) ?>:</td>
                                            <td class="text-right"><?= e($purchaseDate) ?></td>
                                        </tr>
                                        <?php if (!empty($customFields) && count(data_get($purchase,'customField',[]))>0): ?>
                                            <?php foreach ($customFields as $field): ?>
                                                <tr>
                                                    <td><?= e(data_get($field,'name') ?? __('No name available')) ?> :</td>
                                                    <td><?= e(data_get($purchase->customField, $field->id) ?? '-') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </td>
                            <td>
                                <div class="view-qrcode">
                                    <?php
                        try {
                            $qrHtml = (new \Milon\Barcode\DNS2D)->getBarcodeHTML($qrValue, 'QRCODE', 2, 2);
                            echo $qrHtml;
                        } catch (\Throwable $e) {
                            Log::error('QR HTML Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__);
                            echo '<div></div>';
                        }
                        ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="purchase-body">
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <strong style="margin-bottom:10px;display:block;"><?= e(__('Bill To')) ?>:</strong>
                                <?php if (!empty(data_get($vendor,'billing_name'))): ?>
                                    <p>
                                        <?= e(data_get($vendor, 'billing_name', __('No name for billing available.'))) ?><br>
                                        <?= e(data_get($vendor, 'billing_address', __('No address for billing available.'))) ?><br>
                                        <?php $bcity=(string)data_get($vendor,'billing_city',''); echo $bcity!==''? e($bcity) : e(__('No city for billing available.')); ?><?= $bcity!==''?', ':'' ?><br>
                                        <?php $bstate=(string)data_get($vendor,'billing_state',''); echo $bstate!==''? e($bstate) : e(__('No state for billing available.')); ?><?= $bstate!==''?', ':'' ?>,
                                        <?= e(data_get($vendor,'billing_zip', __('No zip for billing available.'))) ?><br>
                                        <?= e(data_get($vendor,'billing_country', __('No country for billing available.'))) ?><br>
                                        <?= e(data_get($vendor,'billing_phone', __('No phone for billing available.'))) ?><br>
                                    </p>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <?php if (data_get($settings,'shipping_display')==='on'): ?>
                                <td class="text-right">
                                    <strong style="margin-bottom:10px;display:block;"><?= e(__('Ship To')) ?>:</strong>
                                    <?php if (!empty(data_get($vendor,'shipping_name'))): ?>
                                        <p>
                                            <?= e(data_get($vendor, 'shipping_name', __('No name for shipping available.'))) ?><br>
                                            <?= e(data_get($vendor, 'shipping_address', __('No address for shipping available.'))) ?><br>
                                            <?php $scity=(string)data_get($vendor,'shipping_city',''); echo $scity!==''? e($scity) : e(__('No city for shipping available.')); ?><?= $scity!==''?', ':'' ?><br>
                                            <?php $sstate=(string)data_get($vendor,'shipping_state',''); echo $sstate!==''? e($sstate) : e(__('No state for shipping available.')); ?><?= $sstate!==''?', ':'' ?>,
                                            <?= e(data_get($vendor,'shipping_zip', __('No zip for shipping available.'))) ?><br>
                                            <?= e(data_get($vendor,'shipping_country', __('No country for shipping available.'))) ?><br>
                                            <?= e(data_get($vendor,'shipping_phone', __('No phone for shipping available.'))) ?><br>
                                        </p>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    </tbody>
                </table>

                <table class="add-border purchase-summary" style="margin-top:30px;">
                    <thead style="background: <?= e($color) ?>; color: <?= e($font_color) ?>;">
                        <tr>
                            <th><?= e(__('Item')) ?></th>
                            <th><?= e(__('Quantity')) ?></th>
                            <th><?= e(__('Rate')) ?></th>
                            <th><?= e(__('Discount')) ?></th>
                            <th><?= e(__('Tax')) ?> (%)</th>
                            <th><?= e(__('Price')) ?> <small><?= e(__('after tax & discount')) ?></small></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (is_iterable(data_get($purchase,'itemData')) && count($purchase->itemData) > 0): ?>
                            <?php foreach ($purchase->itemData as $key => $item): ?>
                                <tr>
                                    <td><?= e(data_get($item,'name') ?? __('No item name available')) ?></td>
                                    <td><?= e((string)(data_get($item,'quantity') ?? 0)) ?></td>
                                    <td><?php try { echo e(Utility::priceFormat($settings, data_get($item,'price',0))); } catch (\Throwable $e) { Log::error('Item price format Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__); echo '0'; } ?></td>
                                    <td><?php try { $disc=(float)(data_get($item,'discount',0)); echo $disc!=0.0 ? e(Utility::priceFormat($settings,$disc)) : '-'; } catch (\Throwable $e) { Log::error('Item discount format Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__); echo '-'; } ?></td>
                                    <?php $itemtax = 0.0; ?>
                                    <td>
                                        <?php if (!empty(data_get($item,'itemTax'))): ?>
                                            <?php foreach ((array)$item->itemTax as $taxes): ?>
                                                <?php $itemtax += (float)data_get($taxes,'tax_price',0); ?>
                                                <p><?= e((data_get($taxes,'name') ?? 'Tax').' ('.(data_get($taxes,'rate') ?? '0').') '.(data_get($taxes,'price') ?? '0')) ?></p>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                            try {
                                $line = (float)(data_get($item,'price',0)) * (float)(data_get($item,'quantity',0)) - (float)(data_get($item,'discount',0)) + (float)$itemtax;
                                echo e(Utility::priceFormat($settings, $line));
                            } catch (\Throwable $e) {
                                Log::error('Line total Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__);
                                echo '0';
                            }
                            ?>
                                    </td>
                                </tr>
                                <?php if (!empty(data_get($item,'description'))): ?>
                                    <tr class="border-0 itm-description">
                                        <td colspan="6"><?= e((string)$item->description) ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><?= e(__('Total')) ?></td>
                            <td><?= e($purchaseTotalQuantity) ?></td>
                            <td><?= e($purchaseTotalRate) ?></td>
                            <td><?= e($purchaseTotalDiscount) ?></td>
                            <td><?= e($purchaseTotalTaxPrice) ?></td>
                            <td><?= e($purchaseSubTotal) ?></td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td colspan="2" class="sub-total">
                                <table class="total-table">
                                    <tr>
                                        <td><?= e(__('Subtotal')) ?>:</td>
                                        <td><?= e($purchaseSubTotal) ?></td>
                                    </tr>
                                    <?php if (method_exists($purchase,'getTotalDiscount') && $purchase->getTotalDiscount()): ?>
                                        <tr>
                                            <td><?= e(__('Discount')) ?>:</td>
                                            <td><?php try { echo e(Utility::priceFormat($settings, $purchase->getTotalDiscount())); } catch (\Throwable $e) { Log::error('Subtotal discount Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__); echo '0'; } ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($purchase->taxesData)): ?>
                                        <?php foreach ($purchase->taxesData as $taxName => $taxPrice): ?>
                                            <tr>
                                                <td><?= e($taxName) ?> :</td>
                                                <td><?php try { echo e(Utility::priceFormat($settings, $taxPrice)); } catch (\Throwable $e) { Log::error('Tax row Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__); echo '0'; } ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <tr>
                                        <td><?= e(__('Total')) ?>:</td>
                                        <td><?= e($purchaseGrandTotal) ?></td>
                                    </tr>
                                    <tr>
                                        <td><?= e(__('Paid')) ?>:</td>
                                        <td><?= e($purchasePaid) ?></td>
                                    </tr>
                                    <tr>
                                        <td><?= e(__('Due Amount')) ?>:</td>
                                        <td><?= e($purchaseDue) ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <div class="purchase-footer">
                    <b><?= e(data_get($settings,'footer_title', __('No footer title available'))) ?></b> <br>
                    <?php try { echo (string)data_get($settings,'footer_notes',''); } catch (\Throwable $e) { Log::error('Footer notes Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__); } ?>
                </div>
            </div>
        </div>

        <?php if (!isset($preview)): ?>
            <?php
    try {
        echo view('purchase.script')->render();
    } catch (\Throwable $e) {
        Log::error('Script include Throwable: '.get_class($e).' | "'.$e->getMessage().'" | file='.__FILE__.' | line='.__LINE__);
    }
    ?>
        <?php endif; ?>
    </body>

    </html>
<?php
} else {
    echo '<!DOCTYPE html>
<html lang="'.htmlspecialchars((string)DatabaseConstants::DEFAULT_LANG, ENT_QUOTES, 'UTF-8').'">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="alert alert-warning">{{ __('No purchase data available.') }}</div>
</body>
</html>';
}
