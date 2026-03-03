<?php
# Template 5
use App\Config\Constants\{DatabaseConstants, SettingsConstants, ViewsConstants};
use App\Models\{ProductServiceUnit, Utility};
use Illuminate\Support\{Str};
use Illuminate\Support\Facades\{Crypt, Log, Route};
use Milon\Barcode\DNS2D;

$lang = Utility::fetchUserLang();
if (isset($pos) && !empty($pos)) {
    if (!function_exists('e')) {
        function e($v)
        {
            return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
        }
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
    $posNumber ??= '';
    $issueDate ??= '';
    $qrValue ??= '#';
    $qrHtml ??= '';
    $subtotalFmt ??= '0';
    $discountFmt ??= '-';
    $totalFmt ??= '0';
    try {
        $docLang ??= $lang ?? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG);
    } catch (\Throwable $e) {
        Log::error('DocLang: ' . get_class($e) . ' "' . $e->getMessage() . '" file=' . __FILE__ . ' line=' . __LINE__);
        $docLang ??= DatabaseConstants::DEFAULT_LANG;
    }
    try {
        $settings_data ??= Utility::settingsById(data_get($pos, 'created_by'));
    } catch (\Throwable $e) {
        Log::error('SettingsById: ' . get_class($e) . ' "' . $e->getMessage() . '" file=' . __FILE__ . ' line=' . __LINE__);
        $settings_data ??= [];
    }
    try {
        $dir = (data_get($settings_data, SettingsConstants::RTL) === 'on') ? 'rtl' : '';
    } catch (\Throwable $e) {
        Log::error('RTL: ' . get_class($e) . ' "' . $e->getMessage() . '" file=' . __FILE__ . ' line=' . __LINE__);
        $dir = '';
    }
    $themeCSS = $themeCSS !== '' ? $themeCSS : (":root { --theme-color: " . $color . "; --white: #ffffff; --black: #000000; }");
    try {
        $posNumber = Utility::posNumberFormat($settings, data_get($pos, 'pos_id')) ?: __('Could not find POS number');
    } catch (\Throwable $e) {
        Log::error('PosNumber: ' . get_class($e) . ' "' . $e->getMessage() . '" file=' . __FILE__ . ' line=' . __LINE__);
        $posNumber = __('Could not find POS number');
    }
    try {
        $issueDate = Utility::dateFormat($settings, data_get($pos, 'issue_date')) ?: __('Failed to get issue date');
    } catch (\Throwable $e) {
        Log::error('IssueDate: ' . get_class($e) . ' "' . $e->getMessage() . '" file=' . __FILE__ . ' line=' . __LINE__);
        $issueDate = __('Failed to get issue date');
    }
    try {
        $base = (function () {
            try {
                return ViewsConstants::POS . '.link.copy';
            } catch (\Throwable $e) {
                return 'pos.link.copy';
            }
        })();
        $kebab = Str::kebab($base);
        $resolved = Route::has($base) ? $base : (Route::has($kebab) ? $kebab : null);
        $pid = data_get($pos, 'pos_id');
        $enc = $pid ? Crypt::encrypt($pid) : null;
        $qrValue = ($resolved && $enc) ? route($resolved, $enc) : '#';
        if ($qrValue === '#') {
            Log::error('QR route unavailable or param missing | route=' . ($resolved ?? 'null') . ' | file=' . __FILE__ . ' | line=' . __LINE__);
        }
    } catch (\Throwable $e) {
        Log::error('QRRoute: ' . get_class($e) . ' "' . $e->getMessage() . '" file=' . __FILE__ . ' line=' . __LINE__);
        $qrValue = '#';
    }
    try {
        $amt = (float) data_get($posPayment, 'amount', 0);
        $disc = (float) (data_get($posPayment, 'discount', 0) ?? 0);
        $subtotalFmt = Utility::priceFormat($settings, $amt) ?: '0';
        $discountFmt = $disc > 0 ? (Utility::priceFormat($settings, $disc) ?: '0') : '-';
        $totalFmt = Utility::priceFormat($settings, $amt - $disc) ?: '0';
    } catch (\Throwable $e) {
        Log::error('Totals: ' . get_class($e) . ' "' . $e->getMessage() . '" file=' . __FILE__ . ' line=' . __LINE__);
        $subtotalFmt = '0';
        $discountFmt = '-';
        $totalFmt = '0';
    }
?>
    <!DOCTYPE html>
    <html lang="<?= e($docLang) ?>" dir="<?= e($dir) ?>">

    <head>
        <?php try {
            echo view('fragments.std', ['meta_title' => $meta_title, 'meta_desc' => $meta_desc, 'meta_vp' => ''])->render();
        } catch (\Throwable $e) {
            Log::error('Meta view: ' . get_class($e) . ' "' . $e->getMessage() . '" file=' . __FILE__ . ' line=' . __LINE__);
        } ?>
        <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
        <style>
            <?php echo $themeCSS; ?>
        </style>
        <style type="text/css">
            body {
                font-family: 'Lato', sans-serif;
                -webkit-font-smoothing: antialiased;
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

            .pos-preview-main {
                max-width: 700px;
                width: 100%;
                margin: 0 auto;
                background: #fff;
                box-shadow: 0 0 10px #ddd;
            }

            .pos-logo {
                max-width: 200px;
                width: 100%;
            }

            .pos-header table td {
                padding: 15px 30px;
            }

            .text-right {
                text-align: right;
            }

            .no-space tr td {
                padding: 0;
                white-space: nowrap;
            }

            .vertical-align-top td {
                vertical-align: top;
            }

            .view-qrcode {
                max-width: 139px;
                height: 139px;
                width: 100%;
                margin-left: auto;
                margin-top: 15px;
                background: var(--white);
                padding: 13px;
                border-radius: 10px;
            }

            .view-qrcode img {
                width: 100%;
                height: 100%;
            }

            .pos-body {
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

            .pos-summary td,
            .pos-summary th {
                font-size: 13px;
                font-weight: 600;
            }

            .total-table td:last-of-type {
                width: 146px;
            }

            .pos-footer {
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

            .pos-summary p {
                margin-bottom: 0;
            }
        </style>
        <?php if (data_get($settings_data, SettingsConstants::RTL) === 'on'): ?>
            <link rel="stylesheet" href="<?= e(asset('css/bootstrap-rtl.css')) ?>">
        <?php endif; ?>
    </head>

    <body>
        <div class="pos-preview-main" id="boxes">
            <div class="pos-header" style="background: <?= e($color) ?>; color: <?= e($font_color) ?>">
                <table>
                    <tbody>
                        <tr>
                            <td><img class="pos-logo" src="<?= e($img) ?>" alt=""></td>
                            <td class="text-right">
                                <h3 style="text-transform:uppercase;font-size:40px;font-weight:bold;"><?= e(__('POS')) ?></h3>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="vertical-align-top">
                    <tbody>
                        <tr>
                            <td>
                                <p>
                                    <?= !empty($settings['company_name']) ? e($settings['company_name']) : e(__('No company name available')) ?><br>
                                    <?= !empty($settings['mail_from_address']) ? e($settings['mail_from_address']) : e(__('No email available')) ?><br><br><br>
                                    <?= !empty($settings['company_address']) ? e($settings['company_address']) : e(__('No address available')) ?>
                                    <?= !empty($settings['company_city']) ? '<br>' . e($settings['company_city']) . ', ' : __('No company city available') ?>
                                    <?= !empty($settings['company_state']) ? e($settings['company_state']) : __('No company state available') ?>
                                    <?= !empty($settings['company_zipcode']) ? ' - ' . e($settings['company_zipcode']) : __('No company zipcode available') ?>
                                    <?= !empty($settings['company_country']) ? '<br>' . e($settings['company_country']) : __('No company country available') ?>
                                    <?= !empty($settings['company_telephone']) ? e($settings['company_telephone']) : __('No company telephone available') ?><br>
                                    <?php if (!empty($settings['registration_number'])): ?>
                                        <?= e(__('Registration Number')) ?> : <?= e($settings['registration_number']) ?><br>
                                    <?php endif; ?>
                                    <?php if (data_get($settings, 'vat_gst_number_switch') === 'on' && !empty($settings['tax_type']) && !empty($settings['vat_number'])): ?>
                                        <?= e($settings['tax_type'] . ' ' . __('Number')) ?> : <?= e($settings['vat_number']) ?> <br>
                                    <?php endif; ?>
                                </p>
                            </td>
                            <td>
                                <table class="no-space" style="width:45%;margin-left:auto;">
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
                                                    <td><?= e(data_get($field, 'name') ?? __('No name available')) ?> :</td>
                                                    <td><?= e(data_get($pos->customField, $field->id) ?? '-') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <tr>
                                            <td colspan="2">
                                                <div class="view-qrcode">
                                                    <?php try {
                                                        $qrHtml = (new \Milon\Barcode\DNS2D)->getBarcodeHTML($qrValue, 'QRCODE', 2, 2);
                                                        echo $qrHtml;
                                                    } catch (\Throwable $e) {
                                                        Log::error('QR HTML: ' . get_class($e) . ' "' . $e->getMessage() . '" file=' . __FILE__ . ' line=' . __LINE__);
                                                        echo '<div></div>';
                                                    } ?>
                                                </div>
                                            </td>
                                        </tr>
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
                        <?php if (is_iterable(data_get($pos, 'itemData')) && count($pos->itemData) > 0): ?>
                            <?php foreach ($pos->itemData as $item): ?>
                                <?php
                                $unitLabel = '';
                                try {
                                    $unit = ProductServiceUnit::find(data_get($item, 'unit'));
                                    $unitLabel = data_get($unit, 'name', '');
                                } catch (\Throwable $e) {
                                    Log::error('Unit: ' . get_class($e) . ' "' . $e->getMessage() . '" file=' . __FILE__ . ' line=' . __LINE__);
                                }
                                $price = (float) data_get($item, 'price', 0);
                                $qty   = (float) data_get($item, 'quantity', 0);
                                $totalTaxPrice = 0.0;
                                ?>
                                <tr>
                                    <td><?= e(data_get($item, 'name', __('No item name available'))) ?></td>
                                    <td><?= e((string)$qty) . ($unitLabel ? ' (' . e($unitLabel) . ')' : '') ?></td>
                                    <td><?php try {
                                            echo e(Utility::priceFormat($settings, $price));
                                        } catch (\Throwable $e) {
                                            Log::error('ItemPrice: ' . get_class($e) . ' "' . $e->getMessage() . '" file=' . __FILE__ . ' line=' . __LINE__);
                                            echo '0';
                                        } ?></td>
                                    <td>
                                        <?php if (!empty(data_get($item, 'itemTax'))): ?>
                                            <?php foreach ((array) $item->itemTax as $taxes): ?>
                                                <?php
                                                $rateStr = (string) data_get($taxes, 'rate', '0');
                                                $res = str_ireplace(['%'], ' ', $rateStr);
                                                try {
                                                    $taxP = Utility::taxRate($res, $price, $qty);
                                                } catch (\Throwable $e) {
                                                    $taxP = 0;
                                                    Log::error('TaxRate: ' . get_class($e) . ' "' . $e->getMessage() . '" file=' . __FILE__ . ' line=' . __LINE__);
                                                }
                                                $totalTaxPrice += (float) $taxP;
                                                ?>
                                                <span><?= e(data_get($taxes, 'name', 'Tax')) ?></span> <span>(<?= e($rateStr) ?>)</span><br>
                                            <?php endforeach; ?>
                                            <?php else: ?>-<?php endif; ?>
                                    </td>
                                    <td><?= Utility::priceFormat($settings, $totalTaxPrice) ?></td>
                                    <td><?= Utility::priceFormat($settings, ($price * $qty) + $totalTaxPrice) ?></td>
                                </tr>
                                <?php if (!empty(data_get($item, 'description'))): ?>
                                    <tr class="border-0 itm-description">
                                        <td colspan="6"><?= e(data_get($item, 'description')) ?></td>
                                    </tr>
                                <?php endif; ?>
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
                                        <td><?= e($subtotalFmt) ?></td>
                                    </tr>
                                    <tr>
                                        <td><?= e(__('Discount')) ?>:</td>
                                        <td><?= is_string($discountFmt) ? e($discountFmt) : e((string)$discountFmt) ?></td>
                                    </tr>
                                    <tr>
                                        <td><?= e(__('Total')) ?>:</td>
                                        <td><?= e($totalFmt) ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <div class="pos-footer">
                    <b><?= e(data_get($settings, 'footer_title', __('No footer title available'))) ?></b> <br>
                    <?php try {
                        echo (string) data_get($settings, 'footer_notes', '');
                    } catch (\Throwable $e) {
                        Log::error('Footer: ' . get_class($e) . ' "' . $e->getMessage() . '" file=' . __FILE__ . ' line=' . __LINE__);
                    } ?>
                </div>
            </div>
        </div>
        <?php if (!isset($preview)): ?>
            <?php
            try {
                $scriptView = (function () {
                    try {
                        return ViewsConstants::POS . '.script';
                    } catch (\Throwable $e) {
                        return 'pos.script';
                    }
                })();
                echo view($scriptView)->render();
            } catch (\Throwable $e) {
                Log::error('Script include: ' . get_class($e) . ' "' . $e->getMessage() . '" file=' . __FILE__ . ' line=' . __LINE__);
            }
            ?>
        <?php endif; ?>
    </body>

    </html>
<?php
} else {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head><body><div class="alert alert-warning">No POS data available.</div></body></html>';
}
