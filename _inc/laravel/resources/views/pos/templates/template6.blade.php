<?php
# Template 6
use App\Config\Constants\{DatabaseConstants, SettingsConstants};
use App\Models\Utility;
use Illuminate\Support\Facades\Log;

if (!function_exists('e')) {
    function e($v)
    {
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
    }
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
$posPayment    ??= (object)['amount' => 0, 'discount' => 0];

if (trim((string)$themeCSS) === '') {
    $themeCSS = ":root { --theme-color: {$color}; --white: #ffffff; --black: #000000; }";
}

try {
    $lang = Utility::fetchUserLang();
} catch (\Throwable $e) {
    Log::error('fetchUserLang: ' . get_class($e) . ' | ' . $e->getMessage());
    $lang = null;
}

try {
    $docLang ??= $lang ?? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG);
} catch (\Throwable $e) {
    Log::error('DocLang: ' . get_class($e) . ' | ' . $e->getMessage());
    $docLang = DatabaseConstants::DEFAULT_LANG;
}

if (empty($pos)) {
    echo '<!DOCTYPE html><html lang="' . e($docLang) . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>POS</title></head><body><div class="alert alert-warning">{{ __('No POS data available.') }}</div></body></html>';
    return;
}

try {
    $settings_data = Utility::settingsById(data_get($pos, 'created_by'));
} catch (\Throwable $e) {
    Log::error('settingsById: ' . $e->getMessage());
    $settings_data = [];
}

try {
    $dir = (data_get($settings_data, SettingsConstants::RTL) === 'on') ? 'rtl' : '';
} catch (\Throwable $e) {
    Log::error('RTL: ' . $e->getMessage());
    $dir = '';
}

try {
    $posNumber = Utility::posNumberFormat($settings, data_get($pos, 'pos_id')) ?: __('Could not find POS number');
} catch (\Throwable $e) {
    Log::error('posNumber: ' . $e->getMessage());
    $posNumber = __('Could not find POS number');
}

try {
    $issueDate = Utility::dateFormat($settings, data_get($pos, 'issue_date')) ?: __('Failed to get issue date');
} catch (\Throwable $e) {
    Log::error('issueDate: ' . $e->getMessage());
    $issueDate = __('Failed to get issue date');
}
?>
<!DOCTYPE html>
<html lang="<?= e($docLang) ?>" dir="<?= e($dir) ?>">

<head>
    <?php try {
        echo view('fragments.std', ['meta_title' => $meta_title, 'meta_desc' => $meta_desc, 'meta_vp' => ''])->render();
    } catch (\Throwable $e) {
        Log::error('Meta view: ' . $e->getMessage());
    } ?>
    <title>New York - POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
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

        .pos-preview-main {
            max-width: 700px;
            width: 100%;
            margin: 0 auto;
            background: #ffff;
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
        <div class="pos-header" style="border-top:15px solid <?= e($color) ?>">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <h3 style="text-transform:uppercase;font-size:40px;font-weight:bold;"><?= e(__('POS')) ?></h3>
                        </td>
                        <td class="text-right"><img class="pos-logo" src="<?= e($img) ?>" alt=""></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pos-body">
            <table class="vertical-align-top">
                <tbody>
                    <tr>
                        <td style="font-size:13px;">
                            <strong style="margin-bottom:10px;display:block;"><?= e(__('From:')) ?></strong>
                            <p>
                                <?= !empty($settings['company_name']) ? e($settings['company_name']) : e(__('No company name available')) ?><br>
                                <?= !empty($settings['mail_from_address']) ? e($settings['mail_from_address']) : e(__('No email available')) ?><br><br>
                                <?= !empty($settings['company_address']) ? e($settings['company_address']) : e(__('No address available')) ?>
                                <?= !empty($settings['company_city']) ? '<br>' . e($settings['company_city']) . ', ' : e(__('No company city available')) ?>
                                <?= !empty($settings['company_state']) ? e($settings['company_state']) : e(__('No company state available')) ?>
                                <?= !empty($settings['company_zipcode']) ? ' - ' . e($settings['company_zipcode']) : e(__('No company zipcode available')) ?>
                                <?= !empty($settings['company_country']) ? '<br>' . e($settings['company_country']) : e(__('No company country available')) ?>
                                <?= !empty($settings['company_telephone']) ? e($settings['company_telephone']) : e(__('No company telephone available')) ?><br>
                            </p>
                        </td>

                        <td style="font-size:13px;">
                            <strong style="margin-bottom:10px;display:block;"><?= e(__('Bill To:')) ?></strong>
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
                            <td style="font-size:13px;" class="text-right">
                                <strong style="margin-bottom:10px;display:block;"><?= e(__('Ship To:')) ?></strong>
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

                    <tr style="border-bottom:1px solid <?= e($color) ?>">
                        <td>
                            <p>
                                <?= !empty($settings['registration_number']) ? e(__('Registration Number')) . ' : ' . e($settings['registration_number']) : e(__('No registration number available')) ?><br>
                                <?php if (data_get($settings, 'vat_gst_number_switch') === 'on'): ?>
                                    <?= (!empty($settings['tax_type']) && !empty($settings['vat_number'])) ? e($settings['tax_type'] . ' ' . __('Number')) . ' : ' . e($settings['vat_number']) . ' <br>' : e(__('No VAT/GST number available')) . ' <br>' ?>
                                <?php endif; ?>
                            </p>
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

            <table class="add-border pos-summary" style="margin-top:30px;">
                <thead style="background: <?= e($color) ?>; color: <?= e($font_color) ?>">
                    <tr style="border-bottom:1px solid <?= e($color) ?>">
                        <th><?= e(__('Item')) ?></th>
                        <th><?= e(__('Quantity')) ?></th>
                        <th><?= e(__('Price')) ?></th>
                        <th><?= e(__('Tax')) ?></th>
                        <th><?= e(__('Tax Amount')) ?></th>
                        <th><?= e(__('Total')) ?></th>
                    </tr>
                </thead>
                <tbody style="border-bottom:1px solid <?= e($color) ?>">
                    <?php if (isset($pos->itemData) && is_iterable($pos->itemData) && count($pos->itemData) > 0): ?>
                        <?php foreach ($pos->itemData as $item): ?>
                            <?php
                            $price = (float) data_get($item, 'price', 0);
                            $qty   = (float) data_get($item, 'quantity', 0);
                            $totalTaxPrice = 0.0;
                            ?>
                            <tr>
                                <td><?= e(data_get($item, 'name', '')) ?></td>
                                <td><?= e((string)$qty) ?></td>
                                <td><?php try {
                                        echo e(Utility::priceFormat($settings, $price));
                                    } catch (\Throwable $e) {
                                        Log::error('Item price: ' . $e->getMessage());
                                        echo '0';
                                    } ?></td>
                                <td>
                                    <?php if (!empty(data_get($item, 'itemTax'))): ?>
                                        <?php foreach ((array) data_get($item, 'itemTax', []) as $taxes): ?>
                                            <?php
                                            try {
                                                $rateRaw = (string) data_get($taxes, 'rate', '0');
                                                $res     = str_ireplace(['%'], ' ', $rateRaw);
                                                $taxP    = Utility::taxRate($res, $price, $qty);
                                                $totalTaxPrice += (float)$taxP;
                                            } catch (\Throwable $e) {
                                                Log::error('Tax calc: ' . $e->getMessage());
                                            }
                                            ?>
                                            <span><?= e(data_get($taxes, 'name', 'Tax')) ?></span> <span>(<?= e(data_get($taxes, 'rate', '0')) ?>)</span><br>
                                        <?php endforeach; ?>
                                        <?php else: ?>-<?php endif; ?>
                                </td>
                                <td><?php try {
                                        echo e(Utility::priceFormat($settings, $totalTaxPrice));
                                    } catch (\Throwable $e) {
                                        Log::error('Tax amt: ' . $e->getMessage());
                                        echo '0';
                                    } ?></td>
                                <td><?php try {
                                        echo e(Utility::priceFormat($settings, ($price * $qty) + $totalTaxPrice));
                                    } catch (\Throwable $e) {
                                        Log::error('Line total: ' . $e->getMessage());
                                        echo '0';
                                    } ?></td>
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
                                    <td><?php try {
                                            echo e(Utility::priceFormat($settings, (float) data_get($posPayment, 'amount', 0)));
                                        } catch (\Throwable $e) {
                                            Log::error('Subtotal: ' . $e->getMessage());
                                            echo '0';
                                        } ?></td>
                                </tr>
                                <tr>
                                    <td><?= e(__('Discount')) ?>:</td>
                                    <td>
                                        <?php try {
                                            $disc = data_get($posPayment, 'discount', null);
                                            echo !empty($disc) ? e(Utility::priceFormat($settings, (float)$disc)) : '-';
                                        } catch (\Throwable $e) {
                                            Log::error('Discount: ' . $e->getMessage());
                                            echo '-';
                                        } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= e(__('Total')) ?>:</td>
                                    <td>
                                        <?php try {
                                            $amt = (float) data_get($posPayment, 'amount', 0);
                                            $disc = (float) (data_get($posPayment, 'discount', 0) ?? 0);
                                            echo e(Utility::priceFormat($settings, $amt - $disc));
                                        } catch (\Throwable $e) {
                                            Log::error('Total: ' . $e->getMessage());
                                            echo '0';
                                        } ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="pos-footer">
                <b><?= e(data_get($settings, 'footer_title', '')) ?></b> <br>
                <?php try {
                    echo (string) data_get($settings, 'footer_notes', '');
                } catch (\Throwable $e) {
                    Log::error('Footer notes: ' . $e->getMessage());
                } ?>
            </div>
        </div>
    </div>

    <?php if (!isset($preview)): ?>
        <?php try {
            echo view('pos.script')->render();
        } catch (\Throwable $e) {
            Log::error('pos.script: ' . $e->getMessage());
        } ?>
    <?php endif; ?>
</body>

</html>