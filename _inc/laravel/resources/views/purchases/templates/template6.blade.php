<?php
# Template 6
use App\Config\Constants\{DatabaseConstants, SettingsConstants};
use App\Models\Utility;
use Illuminate\Support\{Str};
use Illuminate\Support\Facades\{Auth, Crypt, Log, Route};
use Milon\Barcode\DNS2D;

if (!function_exists('e')) {
    function e($v)
    {
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

$user          = Auth::user();
$lang          = null;
try {
    $lang = Utility::fetchUserLang(user: $user);
} catch (\Throwable $e) {
    $lang = null;
}

$purchase       ??= null;
$vendor         ??= null;
$settings       ??= [];
$settings_data  ??= [];
$customFields   ??= [];
$meta_title     ??= '';
$meta_desc      ??= '';
$themeCSS       ??= (function () use (&$color) {
    $color ??= '#4b4b4b';
    return ":root { --theme-color: {$color}; --white: #ffffff; --black: #000000; }";
})();
$color          ??= '#4b4b4b';
$font_color     ??= '#000000';
$img            ??= '';
$preview        ??= null;

$docLang = DatabaseConstants::DEFAULT_LANG;
try {
    $docLang = str_replace('_', '-', is_string($lang) ? $lang : (is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG));
} catch (\Throwable $e) {
    Log::error('DocLang Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
}

if (!isset($purchase) || empty($purchase)) {
    echo '<!DOCTYPE html>
    <html lang="' . e($docLang) . '">
    <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>New York - purchase</title></head>
    <body><div class="alert alert-warning">No purchase data available.</div></body></html>';
    return;
}

try {
    $settings_data = Utility::settingsById(data_get($purchase, DatabaseConstants::COL_TABLE_CREATOR));
} catch (\Throwable $e) {
    Log::error('settingsById Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
    $settings_data = [];
}

$dir = '';
try {
    $dir = (data_get($settings_data, SettingsConstants::RTL) === 'on') ? 'rtl' : '';
} catch (\Throwable $e) {
    Log::error('RTL Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
}

try {
    $purchaseNumber = Utility::purchaseNumberFormat($settings, data_get($purchase, 'purchase_id')) ?: __('Could not find purchase number');
} catch (\Throwable $e) {
    Log::error('purchaseNumber Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
    $purchaseNumber = __('Could not find purchase number');
}
try {
    $purchaseDate = Utility::dateFormat($settings, data_get($purchase, 'purchase_date')) ?: __('Failed to get purchase date');
} catch (\Throwable $e) {
    Log::error('purchaseDate Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
    $purchaseDate = __('Failed to get purchase date');
}

$qrValue = '#';
try {
    $base = 'purchase.link.copy';
    $resolved = Route::has($base) ? $base : (Route::has(Str::kebab($base)) ? Str::kebab($base) : null);
    $pid = data_get($purchase, 'purchase_id');
    $enc = $pid ? Crypt::encrypt($pid) : null;
    $qrValue = ($resolved && $enc) ? route($resolved, $enc) : '#';
    if ($qrValue === '#') Log::warning('QR route unavailable or param missing | route=' . ($resolved ?? 'null'));
} catch (\Throwable $e) {
    Log::error('QR Route Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
    $qrValue = '#';
}

$purchaseTotalQuantity = (string)(data_get($purchase, 'totalQuantity') ?? '0');
try {
    $purchaseTotalRate      = Utility::priceFormat($settings, data_get($purchase, 'totalRate', 0));
} catch (\Throwable $e) {
    Log::error('totalRate fmt: ' . $e->getMessage());
    $purchaseTotalRate = '0';
}
try {
    $purchaseTotalDiscount  = Utility::priceFormat($settings, data_get($purchase, 'totalDiscount', 0));
} catch (\Throwable $e) {
    Log::error('totalDiscount fmt: ' . $e->getMessage());
    $purchaseTotalDiscount = '0';
}
try {
    $purchaseTotalTaxPrice  = Utility::priceFormat($settings, data_get($purchase, 'totalTaxPrice', 0));
} catch (\Throwable $e) {
    Log::error('totalTaxPrice fmt: ' . $e->getMessage());
    $purchaseTotalTaxPrice = '0';
}
try {
    $purchaseSubTotal       = Utility::priceFormat($settings, method_exists($purchase, 'getSubTotal') ? $purchase->getSubTotal() : 0);
} catch (\Throwable $e) {
    Log::error('subTotal fmt: ' . $e->getMessage());
    $purchaseSubTotal = '0';
}
try {
    $grand = (method_exists($purchase, 'getSubTotal') ? $purchase->getSubTotal() : 0)
        - (method_exists($purchase, 'getTotalDiscount') ? $purchase->getTotalDiscount() : 0)
        + (method_exists($purchase, 'getTotalTax') ? $purchase->getTotalTax() : 0);
    $purchaseGrandTotal = Utility::priceFormat($settings, $grand);
} catch (\Throwable $e) {
    Log::error('grandTotal fmt: ' . $e->getMessage());
    $purchaseGrandTotal = '0';
}
try {
    $paidAmt = Utility::priceFormat($settings, (method_exists($purchase, 'getTotal') ? $purchase->getTotal() : 0) - (method_exists($purchase, 'getDue') ? $purchase->getDue() : 0));
} catch (\Throwable $e) {
    Log::error('paid fmt: ' . $e->getMessage());
    $paidAmt = '0';
}
try {
    $dueAmt  = Utility::priceFormat($settings, method_exists($purchase, 'getDue') ? $purchase->getDue() : 0);
} catch (\Throwable $e) {
    Log::error('due fmt: ' . $e->getMessage());
    $dueAmt  = '0';
}
?>
<!DOCTYPE html>
<html lang="<?= e($docLang) ?>" dir="<?= e($dir) ?>">

<head>
    <?php
    try {
        echo view('fragments.std', ['meta_title' => $meta_title, 'meta_desc' => $meta_desc, 'meta_vp' => ''])->render();
    } catch (\Throwable $e) {
        Log::error('Meta view Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
    }
    ?>
    <title>New York - purchase</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        <?php echo $themeCSS; ?>
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
            white-space: nowrap;
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
        <div class="purchase-header" style="border-top: 15px solid <?= e($color) ?>;">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <h3 style="text-transform: uppercase; font-size: 40px; font-weight: bold;"><?= e(__('PURCHASE')) ?></h3>
                        </td>
                        <td class="text-right">
                            <img class="purchase-logo" src="<?= e($img) ?>" alt="">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="purchase-body">
            <table class="vertical-align-top">
                <tbody>
                    <tr>
                        <?php if (!empty($settings['company_name']) && !empty($settings['mail_from_address']) && !empty($settings['company_address'])): ?>
                            <td style="font-size: 13px;">
                                <strong style="margin-bottom:10px;display:block;"><?= e(__('From:')) ?></strong>
                                <p>
                                    <?= e($settings['company_name']) ?><br>
                                    <?= e($settings['mail_from_address']) ?><br><br>
                                    <?= e($settings['company_address']) ?>
                                    <?php if (!empty($settings['company_city'])): ?>
                                        <br><?= e($settings['company_city']) ?>,
                                    <?php endif; ?>
                                    <?php if (!empty($settings['company_state'])): ?>
                                        <?= e($settings['company_state']) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($settings['company_zipcode'])): ?>
                                        - <?= e($settings['company_zipcode']) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($settings['company_country'])): ?>
                                        <br><?= e($settings['company_country']) ?>
                                    <?php endif; ?>
                                    <?= e(data_get($settings, 'company_telephone', '')) ?><br>
                                    <?php
                                    if (!empty($settings['registration_number'])) {
                                        echo e(__('Registration Number')) . ' : ' . e($settings['registration_number']) . ' <br>';
                                    }
                                    if (data_get($settings, 'vat_gst_number_switch') === 'on') {
                                        if (!empty($settings['tax_type']) && !empty($settings['vat_number'])) {
                                            echo e($settings['tax_type'] . ' ' . __('Number')) . ' : ' . e($settings['vat_number']) . ' <br>';
                                        }
                                    }
                                    ?>
                                </p>
                            </td>
                        <?php endif; ?>

                        <td style="font-size: 13px;">
                            <strong style="margin-bottom:10px;display:block;"><?= e(__('Bill To:')) ?></strong>
                            <?php if (!empty(data_get($vendor, 'billing_name'))): ?>
                                <p>
                                    <?= e(data_get($vendor, 'billing_name', '')) ?><br>
                                    <?= e(data_get($vendor, 'billing_address', '')) ?><br>
                                    <?= e(data_get($vendor, 'billing_city', '')) ?><?= !empty(data_get($vendor, 'billing_city')) ? ', ' : '' ?><br>
                                    <?= e(data_get($vendor, 'billing_state', '')) ?><?= !empty(data_get($vendor, 'billing_state')) ? ', ' : '' ?>,
                                    <?= e(data_get($vendor, 'billing_zip', '')) ?><br>
                                    <?= e(data_get($vendor, 'billing_country', '')) ?><br>
                                    <?= e(data_get($vendor, 'billing_phone', '')) ?><br>
                                </p>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                        <?php if (data_get($settings, 'shipping_display') === 'on'): ?>
                            <td style="font-size: 13px;" class="text-right">
                                <strong style="margin-bottom:10px;display:block;"><?= e(__('Ship To:')) ?></strong>
                                <?php if (!empty(data_get($vendor, 'shipping_name'))): ?>
                                    <p>
                                        <?= e(data_get($vendor, 'shipping_name', '')) ?><br>
                                        <?= e(data_get($vendor, 'shipping_address', '')) ?><br>
                                        <?= e(data_get($vendor, 'shipping_city', '')) ?><?= !empty(data_get($vendor, 'shipping_city')) ? ', ' : '' ?><br>
                                        <?= e(data_get($vendor, 'shipping_state', '')) ?><?= !empty(data_get($vendor, 'shipping_state')) ? ', ' : '' ?>,
                                        <?= e(data_get($vendor, 'shipping_zip', '')) ?><br>
                                        <?= e(data_get($vendor, 'shipping_country', '')) ?><br>
                                        <?= e(data_get($vendor, 'shipping_phone', '')) ?><br>
                                    </p>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>

                    <tr style="border-bottom:1px solid <?= e($color) ?>;">
                        <td>
                            <p>
                                <?php if (!empty($settings['registration_number'])): ?>
                                    <?= e(__('Registration Number')) ?> : <?= e($settings['registration_number']) ?><br>
                                <?php endif; ?>
                                <?php if (!empty($settings['tax_type']) && !empty($settings['vat_number'])): ?>
                                    <?= e($settings['tax_type'] . ' ' . __('Number')) ?> : <?= e($settings['vat_number']) ?> <br>
                                <?php endif; ?>
                            </p>
                        </td>
                        <td colspan="2">
                            <div class="view-qrcode" style="margin-top:0;">
                                <?php
                                try {
                                    echo (new \Milon\Barcode\DNS2D)->getBarcodeHTML($qrValue, 'QRCODE', 2, 2);
                                } catch (\Throwable $e) {
                                    Log::error('QR HTML Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '"');
                                    echo '<div></div>';
                                }
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
                                        <td class="text-right"><?= e($purchaseNumber) ?></td>
                                    </tr>
                                    <tr>
                                        <td><?= e(__('Purchase Date')) ?>:</td>
                                        <td class="text-right"><?= e($purchaseDate) ?></td>
                                    </tr>
                                    <?php if (!empty($customFields) && count(data_get($purchase, 'customField', [])) > 0): ?>
                                        <?php foreach ($customFields as $field): ?>
                                            <tr>
                                                <td><?= e(data_get($field, 'name', __('Field'))) ?> :</td>
                                                <td><?= e(data_get($purchase->customField, $field->id) ?? '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="add-border purchase-summary" style="margin-top:30px;">
                <thead style="background: <?= e($color) ?>; color: <?= e($font_color) ?>">
                    <tr style="border-bottom:1px solid <?= e($color) ?>;">
                        <th><?= e(__('Item')) ?></th>
                        <th><?= e(__('Quantity')) ?></th>
                        <th><?= e(__('Rate')) ?></th>
                        <th><?= e(__('Discount')) ?></th>
                        <th><?= e(__('Tax')) ?> (%)</th>
                        <th><?= e(__('Price')) ?> <small><?= e(__('after tax & discount')) ?></small></th>
                    </tr>
                </thead>
                <tbody style="border-bottom:1px solid <?= e($color) ?>;">
                    <?php if (isset($purchase->itemData) && is_iterable($purchase->itemData) && count($purchase->itemData) > 0): ?>
                        <?php foreach ($purchase->itemData as $key => $item): ?>
                            <?php $itemtax = 0.0; ?>
                            <tr>
                                <td><?= e(data_get($item, 'name', '')) ?></td>
                                <td><?= e((string)(data_get($item, 'quantity', 0))) ?></td>
                                <td><?php try {
                                        echo e(Utility::priceFormat($settings, data_get($item, 'price', 0)));
                                    } catch (\Throwable $e) {
                                        Log::error('item price fmt: ' . $e->getMessage());
                                        echo '0';
                                    } ?></td>
                                <td><?php try {
                                        $disc = (float)data_get($item, 'discount', 0);
                                        echo $disc != 0.0 ? e(Utility::priceFormat($settings, $disc)) : '-';
                                    } catch (\Throwable $e) {
                                        Log::error('item disc fmt: ' . $e->getMessage());
                                        echo '-';
                                    } ?></td>
                                <td>
                                    <?php if (!empty(data_get($item, 'itemTax'))): ?>
                                        <?php foreach ((array)$item->itemTax as $taxes): ?>
                                            <?php $itemtax += (float)data_get($taxes, 'tax_price', 0); ?>
                                            <p><?= e((data_get($taxes, 'name', 'Tax')) . ' (' . (data_get($taxes, 'rate', '0')) . ') ' . (data_get($taxes, 'price', '0'))) ?></p>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span>-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    try {
                                        $line = (float)(data_get($item, 'price', 0)) * (float)(data_get($item, 'quantity', 0))
                                            - (float)(data_get($item, 'discount', 0)) + (float)$itemtax;
                                        echo e(Utility::priceFormat($settings, $line));
                                    } catch (\Throwable $e) {
                                        Log::error('line total fmt: ' . $e->getMessage());
                                        echo '0';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php if (!empty(data_get($item, 'description'))): ?>
                                <tr class="border-0 itm-description">
                                    <td colspan="6" style="border-bottom:1px solid <?= e($color) ?>;"><?= e(data_get($item, 'description')) ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr style="border-bottom:1px solid <?= e($color) ?>;">
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
                                <tr style="border-bottom:1px solid <?= e($color) ?>;">
                                    <td><?= e(__('Subtotal')) ?>:</td>
                                    <td><?= e($purchaseSubTotal) ?></td>
                                </tr>
                                <?php if (method_exists($purchase, 'getTotalDiscount') && $purchase->getTotalDiscount()): ?>
                                    <tr style="border-bottom:1px solid <?= e($color) ?>;">
                                        <td><?= e(__('Discount')) ?>:</td>
                                        <td><?php try {
                                                echo e(Utility::priceFormat($settings, $purchase->getTotalDiscount()));
                                            } catch (\Throwable $e) {
                                                Log::error('tot disc fmt: ' . $e->getMessage());
                                                echo '0';
                                            } ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!empty($purchase->taxesData)): ?>
                                    <?php foreach ($purchase->taxesData as $taxName => $taxPrice): ?>
                                        <tr style="border-bottom:1px solid <?= e($color) ?>;">
                                            <td><?= e($taxName) ?> :</td>
                                            <td><?php try {
                                                    echo e(Utility::priceFormat($settings, $taxPrice));
                                                } catch (\Throwable $e) {
                                                    Log::error('tax row fmt: ' . $e->getMessage());
                                                    echo '0';
                                                } ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <tr style="border-bottom:1px solid <?= e($color) ?>;">
                                    <td><?= e(__('Total')) ?>:</td>
                                    <td><?= e($purchaseGrandTotal) ?></td>
                                </tr>
                                <tr style="border-bottom:1px solid <?= e($color) ?>;">
                                    <td><?= e(__('Paid')) ?>:</td>
                                    <td><?= e($paidAmt) ?></td>
                                </tr>
                                <tr style="border-bottom:1px solid <?= e($color) ?>;">
                                    <td><?= e(__('Due Amount')) ?>:</td>
                                    <td><?= e($dueAmt) ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="purchase-footer">
                <b><?= e(data_get($settings, 'footer_title', '')) ?></b> <br>
                <?php try {
                    echo (string)data_get($settings, 'footer_notes', '');
                } catch (\Throwable $e) {
                    Log::error('footer notes Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
                } ?>
            </div>
        </div>
    </div>

    <?php if (!isset($preview)): ?>
        <?php
        try {
            echo view('purchase.script')->render();
        } catch (\Throwable $e) {
            Log::error('script include Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        }
        ?>
    <?php endif; ?>
</body>

</html>