<?php
# Template 1
use App\Config\Constants\{DatabaseConstants, SettingsConstants, ViewsConstants};
use App\Models\Utility;
use App\Models\ProductServiceUnit;
use Illuminate\Support\Facades\{Crypt, Log, Route};
use Milon\Barcode\DNS2D;
use App\Helpers\TemplateHelper;

if (!function_exists('e')) {
    function e($v)
    {
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

$bill          ??= null;
$vendor        ??= null;
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
    $docLang = $lang ?? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG);
} catch (\Throwable $e) {
    Log::error('DocLang: ' . get_class($e) . ' | ' . $e->getMessage());
    $docLang = DatabaseConstants::DEFAULT_LANG;
}

if (empty($bill)) {
    echo TemplateHelper::getNoDataHtml('bill', $docLang);
    return;
}

try {
    $settings_data = Utility::settingsById(data_get($bill, DatabaseConstants::COL_TABLE_CREATOR));
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
    $billNumber = Utility::billNumberFormat($settings, data_get($bill, 'bill_id')) ?: __('Could not find Bill Identifier');
} catch (\Throwable $e) {
    Log::error('billNumber: ' . $e->getMessage());
    $billNumber = __('Could not find Bill Identifier');
}

try {
    $billDate = Utility::dateFormat($settings, data_get($bill, 'issue_date')) ?: __('Failed to get bill date');
} catch (\Throwable $e) {
    Log::error('billDate: ' . $e->getMessage());
    $billDate = __('Failed to get bill date');
}

try {
    $dueDate = Utility::dateFormat($settings, data_get($bill, 'due_date')) ?: __('Failed to get due date');
} catch (\Throwable $e) {
    Log::error('dueDate: ' . $e->getMessage());
    $dueDate = __('Failed to get due date');
}

$qrHtml = '';
try {
    $rid = data_get($bill, 'bill_id');
    $enc = $rid ? Crypt::encrypt($rid) : null;
    $routeName = ViewsConstants::BIL . '.link.copy';
    $url = ($enc && Route::has($routeName)) ? route($routeName, $enc) : '#';
    $qrHtml = (new \Milon\Barcode\DNS2D)->getBarcodeHTML($url, 'QRCODE', 2, 2);
} catch (\Throwable $e) {
    Log::error('QR: ' . get_class($e) . ' | ' . $e->getMessage());
    $qrHtml = '<div></div>';
}

$totalQty         = (string)(data_get($bill, 'totalQuantity') ?? '0');
$totalRate        = '0';
$totalDiscount    = '0';
$totalTaxPrice    = '0';
$subTotal         = '0';
$grandTotalRow    = '0';
$paidTotal        = '0';
$debitNoteTotal   = '0';
$dueAmount        = '0';

try {
    $totalRate     = Utility::priceFormat($settings, data_get($bill, 'totalRate', 0)) ?: '0';
} catch (\Throwable $e) {
}
try {
    $totalDiscount = Utility::priceFormat($settings, data_get($bill, 'totalDiscount', 0)) ?: '0';
} catch (\Throwable $e) {
}
try {
    $totalTaxPrice = Utility::priceFormat($settings, data_get($bill, 'totalTaxPrice', 0)) ?: '0';
} catch (\Throwable $e) {
}
try {
    $subTotal      = Utility::priceFormat($settings, method_exists($bill, 'getSubTotal') ? $bill->getSubTotal() : 0) ?: '0';
} catch (\Throwable $e) {
}
try {
    $grandTotalRow  = Utility::priceFormat($settings, (method_exists($bill, 'getSubTotal') ? $bill->getSubTotal() : 0) - (method_exists($bill, 'getTotalDiscount') ? $bill->getTotalDiscount() : 0) + (method_exists($bill, 'getTotalTax') ? $bill->getTotalTax() : 0)) ?: '0';
} catch (\Throwable $e) {
}
try {
    $paidTotal      = Utility::priceFormat($settings, (method_exists($bill, 'getTotal') ? $bill->getTotal() : 0) - (method_exists($bill, 'getDue') ? $bill->getDue() : 0) - (method_exists($bill, 'billTotalDebitNote') ? $bill->billTotalDebitNote() : 0)) ?: '0';
} catch (\Throwable $e) {
}
try {
    $debitNoteTotal = Utility::priceFormat($settings, method_exists($bill, 'billTotalDebitNote') ? $bill->billTotalDebitNote() : 0) ?: '0';
} catch (\Throwable $e) {
}
try {
    $dueAmount      = Utility::priceFormat($settings, method_exists($bill, 'getDue') ? $bill->getDue() : 0) ?: '0';
} catch (\Throwable $e) {
}
?>
<!DOCTYPE html>
<html lang="<?= e($docLang) ?>" dir="<?= e($dir) ?>">

<head>
    <?php try {
        echo view('fragments.std', ['meta_title' => $meta_title, 'meta_desc' => $meta_desc])->render();
    } catch (\Throwable $e) {
        Log::error('Meta view: ' . $e->getMessage());
    } ?>
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

        .bill-preview-main {
            max-width: 700px;
            width: 100%;
            margin: 0 auto;
            background: #ffff;
            box-shadow: 0 0 10px #ddd;
        }

        .bill-logo {
            max-width: 200px;
            width: 100%;
        }

        .bill-header table td {
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

        .bill-body {
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

        .bill-summary td,
        .bill-summary th {
            font-size: 13px;
            font-weight: 600;
        }

        .total-table td:last-of-type {
            width: 146px;
        }

        .bill-footer {
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
    </style>
    <?php if (data_get($settings_data, SettingsConstants::RTL) === 'on'): ?>
        <link rel="stylesheet" href="<?= e(asset('css/bootstrap-rtl.css')) ?>">
    <?php endif; ?>
</head>

<body>
    <div class="bill-preview-main" id="boxes">
        <div class="bill-header" style="background: <?= e($color) ?>; color: <?= e($font_color) ?>">
            <table>
                <tbody>
                    <tr>
                        <td><img class="bill-logo" src="<?= e($img) ?>" alt=""></td>
                        <td class="text-right">
                            <h3 style="text-transform:uppercase;font-size:40px;font-weight:bold;"><?= e(__('BILL')) ?></h3>
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
                                <?= !empty($settings['company_city']) ? '<br>' . e($settings['company_city']) . ', ' : e(__('No company city available')) ?>
                                <?= !empty($settings['company_state']) ? e($settings['company_state']) : e(__('No company state available')) ?>
                                <?= !empty($settings['company_zipcode']) ? ' - ' . e($settings['company_zipcode']) : e(__('No company zipcode available')) ?>
                                <?= !empty($settings['company_country']) ? '<br>' . e($settings['company_country']) : e(__('No company country available')) ?>
                                <?= !empty($settings['company_telephone']) ? e($settings['company_telephone']) : e(__('No company telephone available')) ?><br>
                                <?php
                                $reg = (string)($settings['registration_number'] ?? '');
                                echo $reg !== '' ? e(__('Registration Number')) . ' : ' . e($reg) . ' <br>' : e(__('No registration number available')) . ' <br>';
                                if (data_get($settings, 'vat_gst_number_switch') === 'on') {
                                    $taxType = (string)($settings['tax_type'] ?? '');
                                    $vatNum  = (string)($settings['vat_number'] ?? '');
                                    echo ($taxType !== '' && $vatNum !== '')
                                        ? e($taxType . ' ' . __('Number')) . ' : ' . e($vatNum) . ' <br>'
                                        : e(__('No VAT/GST number available')) . ' <br>';
                                }
                                ?>
                            </p>
                        </td>
                        <td>
                            <table class="no-space" style="width:45%;margin-left:auto;">
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
                                    <tr>
                                        <td colspan="2">
                                            <div class="view-qrcode"><?= $qrHtml ?></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="bill-body">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <strong style="margin-bottom:10px;display:block;"><?= e(__('Bill To')) ?>:</strong>
                            <p>
                                <?= e(data_get($vendor, 'billing_name', __('No name for billing available.'))) ?><br>
                                <?= e(data_get($vendor, 'billing_address', __('No address for billing available.'))) ?><br>
                                <?= e(data_get($vendor, 'billing_city', __('No city for billing available.'))) ?><?= !empty(data_get($vendor, 'billing_city')) ? ', ' : '' ?><br>
                                <?= e(data_get($vendor, 'billing_state', __('No state for billing available.'))) ?><?= !empty(data_get($vendor, 'billing_state')) ? ', ' : '' ?>, <?= e(data_get($vendor, 'billing_zip', __('No zip for billing available.'))) ?><br>
                                <?= e(data_get($vendor, 'billing_country', __('No country for billing available.'))) ?><br>
                                <?= e(data_get($vendor, 'billing_phone', __('No phone for billing available'))) ?><br>
                            </p>
                        </td>
                        <?php if (data_get($settings, 'shipping_display') === 'on'): ?>
                            <td class="text-right">
                                <strong style="margin-bottom:10px;display:block;"><?= e(__('Ship To')) ?>:</strong>
                                <p>
                                    <?= e(data_get($vendor, 'shipping_name', __('No name for shipping available.'))) ?><br>
                                    <?= e(data_get($vendor, 'shipping_address', __('No address for shipping available.'))) ?><br>
                                    <?= e(data_get($vendor, 'shipping_city', __('No city for shipping available.'))) ?><?= !empty(data_get($vendor, 'shipping_city')) ? ', ' : '' ?><br>
                                    <?= e(data_get($vendor, 'shipping_state', __('No state for shipping available.'))) ?><?= !empty(data_get($vendor, 'shipping_state')) ? ', ' : '' ?>, <?= e(data_get($vendor, 'shipping_zip', __('No zip for shipping available.'))) ?><br>
                                    <?= e(data_get($vendor, 'shipping_country', __('No country for shipping available.'))) ?><br>
                                    <?= e(data_get($vendor, 'shipping_phone', __('No phone for shipping available.'))) ?><br>
                                </p>
                            </td>
                        <?php endif; ?>
                    </tr>
                </tbody>
            </table>

            <table class="add-border bill-summary" style="margin-top:30px;">
                <thead style="background: <?= e($color) ?>; color: <?= e($font_color) ?>">
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
                    <?php if (isset($bill->itemData) && is_iterable($bill->itemData) && count($bill->itemData) > 0): ?>
                        <?php foreach ($bill->itemData as $item): ?>
                            <?php
                            $price = (float) data_get($item, 'price', 0);
                            $qty   = (float) data_get($item, 'quantity', 0);
                            $disc  = (float) data_get($item, 'discount', 0);
                            $itemtax = 0.0;
                            $unitLabel = '';
                            try {
                                $unit = ProductServiceUnit::find(data_get($item, 'unit'));
                                $unitLabel = data_get($unit, 'name', '');
                            } catch (\Throwable $e) {
                                Log::error('Unit find: ' . $e->getMessage());
                            }
                            ?>
                            <tr>
                                <td><?= e(data_get($item, 'name', '')) ?></td>
                                <td><?= e((string)$qty) . ($unitLabel ? ' (' . e($unitLabel) . ')' : '') ?></td>
                                <td><?php try {
                                        echo e(Utility::priceFormat($settings, $price));
                                    } catch (\Throwable $e) {
                                        Log::error('Rate fmt: ' . $e->getMessage());
                                        echo '0';
                                    } ?></td>
                                <td><?php try {
                                        echo $disc != 0.0 ? e(Utility::priceFormat($settings, $disc)) : '-';
                                    } catch (\Throwable $e) {
                                        Log::error('Disc fmt: ' . $e->getMessage());
                                        echo '-';
                                    } ?></td>
                                <td>
                                    <?php if (!empty(data_get($item, 'itemTax'))): ?>
                                        <?php foreach ((array) data_get($item, 'itemTax', []) as $taxes): ?>
                                            <?php $itemtax += (float) data_get($taxes, 'tax_price', 0); ?>
                                            <p><?= e((data_get($taxes, 'name', 'Tax')) . ' (' . (data_get($taxes, 'rate', '0')) . ') ' . (data_get($taxes, 'price', '0'))) ?></p>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span>-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    try {
                                        echo e(Utility::priceFormat($settings, $price * $qty - $disc + $itemtax));
                                    } catch (\Throwable $e) {
                                        Log::error('Line total: ' . $e->getMessage());
                                        echo '0';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php if (!empty(data_get($item, 'description'))): ?>
                                <tr class="border-0 itm-description">
                                    <td colspan="6"><?= e(data_get($item, 'description', '')) ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td><?= e(__('Total')) ?></td>
                        <td><?= e($totalQty) ?></td>
                        <td><?= e($totalRate) ?></td>
                        <td><?= e($totalDiscount) ?></td>
                        <td><?= e($totalTaxPrice) ?></td>
                        <td><?= e($subTotal) ?></td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                        <td colspan="2" class="sub-total">
                            <table class="total-table">
                                <tr>
                                    <td><?= e(__('Subtotal')) ?>:</td>
                                    <td><?= e($subTotal) ?></td>
                                </tr>
                                <?php if (method_exists($bill, 'getTotalDiscount') && $bill->getTotalDiscount()): ?>
                                    <tr>
                                        <td><?= e(__('Discount')) ?>:</td>
                                        <td><?php try {
                                                echo e(Utility::priceFormat($settings, $bill->getTotalDiscount()));
                                            } catch (\Throwable $e) {
                                                Log::error('Total discount: ' . $e->getMessage());
                                                echo '0';
                                            } ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!empty($bill->taxesData)): ?>
                                    <?php foreach ($bill->taxesData as $taxName => $taxPrice): ?>
                                        <tr>
                                            <td><?= e($taxName) . ' :' ?></td>
                                            <td><?php try {
                                                    echo e(Utility::priceFormat($settings, $taxPrice));
                                                } catch (\Throwable $e) {
                                                    Log::error('Tax row: ' . $e->getMessage());
                                                    echo '0';
                                                } ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <tr>
                                    <td><?= e(__('Total')) ?>:</td>
                                    <td><?= e($grandTotalRow) ?></td>
                                </tr>
                                <tr>
                                    <td><?= e(__('Paid')) ?>:</td>
                                    <td><?= e($paidTotal) ?></td>
                                </tr>
                                <tr>
                                    <td><?= e(__('Debit Note')) ?>:</td>
                                    <td><?= e($debitNoteTotal) ?></td>
                                </tr>
                                <tr>
                                    <td><?= e(__('Due Amount')) ?>:</td>
                                    <td><?= e($dueAmount) ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="bill-footer">
                <b><?= e(data_get($settings, 'footer_title', __('No footer title available'))) ?></b> <br>
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
            echo view(ViewsConstants::BIL . '.script')->render();
        } catch (\Throwable $e) {
            Log::error('Script include: ' . $e->getMessage());
        } ?>
    <?php endif; ?>
</body>

</html>