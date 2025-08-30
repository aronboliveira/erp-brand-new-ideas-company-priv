<?php
# Template 9
use App\Config\Constants\{DatabaseConstants, SettingsConstants, ViewsConstants};
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

if (!function_exists('e')) {
    function e($v)
    {
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
    }
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

if (trim((string)$themeCSS) === '') {
    $themeCSS = ":root { --theme-color: {$color}; --white: #ffffff; --black: #000000; }";
}

try {
    $lang = \App\Models\Utility::fetchUserLang();
} catch (\Throwable $e) {
    $lang = null;
}

try {
    $docLang ??= $lang ?? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG);
} catch (\Throwable $e) {
    Log::error('DocLang Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '"');
    $docLang = DatabaseConstants::DEFAULT_LANG;
}

if (empty($bill)) {
    echo '<!DOCTYPE html><html lang="' . e($docLang) . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>BILL</title></head><body><div class="alert alert-warning">No BILL data available.</div></body></html>';
    return;
}

try {
    $settings_data = \App\Models\Utility::settingsById(data_get($bill, DatabaseConstants::TABLE_CREATOR));
} catch (\Throwable $e) {
    Log::error('settingsById Throwable: ' . $e->getMessage());
    $settings_data = [];
}

try {
    $dir = (data_get($settings_data, SettingsConstants::RTL) === 'on') ? 'rtl' : '';
} catch (\Throwable $e) {
    Log::error('RTL Throwable: ' . $e->getMessage());
    $dir = '';
}

?>
<!DOCTYPE html>
<html lang="<?= e($docLang) ?>" dir="<?= e($dir) ?>">

<head>
    <?php
    try {
        echo view('fragments.std', ['meta_title' => $meta_title, 'meta_desc' => $meta_desc, 'meta_vp' => ''])->render();
    } catch (\Throwable $e) {
        Log::error('Meta view Throwable: ' . $e->getMessage());
    }
    ?>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        <?= $themeCSS ?>
    </style>
    <style type="text/css">
        body {
            font-family: 'Lato', sans-serif
        }

        p,
        li,
        ul,
        ol {
            margin: 0;
            padding: 0;
            list-style: none;
            line-height: 1.5
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        table tr th {
            padding: .75rem;
            text-align: left
        }

        table tr td {
            padding: .75rem;
            text-align: left
        }

        table th small {
            display: block;
            font-size: 12px
        }

        .bill-preview-main {
            max-width: 700px;
            width: 100%;
            margin: 0 auto;
            background: #ffff;
            box-shadow: 0 0 10px #ddd
        }

        .bill-logo {
            max-width: 200px;
            width: 100%
        }

        .bill-header table td {
            padding: 15px 30px
        }

        .text-right {
            text-align: right
        }

        .no-space tr td {
            padding: 0
        }

        .vertical-align-top td {
            vertical-align: top
        }

        .view-qrcode {
            max-width: 114px;
            height: 114px;
            margin-left: auto;
            margin-top: 15px;
            background: var(--white)
        }

        .view-qrcode img {
            width: 100%;
            height: 100%
        }

        .bill-body {
            padding: 30px 25px 0
        }

        table.add-border tr {
            border-top: 1px solid var(--theme-color)
        }

        tfoot tr:first-of-type {
            border-bottom: 1px solid var(--theme-color)
        }

        .total-table tr:first-of-type td {
            padding-top: 0
        }

        .total-table tr:first-of-type {
            border-top: 0
        }

        .sub-total {
            padding-right: 0;
            padding-left: 0
        }

        .border-0 {
            border: none !important
        }

        .bill-summary td,
        .bill-summary th {
            font-size: 13px;
            font-weight: 600
        }

        .total-table td:last-of-type {
            width: 146px
        }

        .bill-footer {
            padding: 15px 20px
        }

        .itm-description td {
            padding-top: 0
        }

        html[dir="rtl"] table tr td,
        html[dir="rtl"] table tr th {
            text-align: right
        }

        html[dir="rtl"] .text-right {
            text-align: left
        }

        html[dir="rtl"] .view-qrcode {
            margin-left: 0;
            margin-right: auto
        }

        p:not(:last-of-type) {
            margin-bottom: 15px
        }

        .bill-summary p {
            margin-bottom: 0
        }

        .bill-footer h6 {
            font-size: 45px;
            line-height: 1.2em;
            font-weight: 400;
            margin-top: 15px;
            color: var(--theme-color)
        }
    </style>
    <?php if (data_get($settings_data, SettingsConstants::RTL) === 'on'): ?>
        <link rel="stylesheet" href="<?= e(asset('css/bootstrap-rtl.css')) ?>">
    <?php endif; ?>
</head>

<body>
    <div class="bill-preview-main" id="boxes" style="border-right:40px solid var(--theme-color);">
        <div class="bill-header">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <h3 style="text-transform:uppercase;font-size:40px;font-weight:bold;color:var(--theme-color);margin-bottom:10px;"><?= e(__('BILL')) ?></h3>
                            <table class="no-space" style="width:70%;">
                                <tbody>
                                    <tr>
                                        <td><?= e(__('Number')) ?>:</td>
                                        <td class="text-right"><?php try {
                                                                    echo e(\App\Models\Utility::billNumberFormat($settings, data_get($bill, 'bill_id')));
                                                                } catch (\Throwable $e) {
                                                                    Log::error('Bill num fmt: ' . $e->getMessage());
                                                                    echo e(__('Unavailable'));
                                                                } ?></td>
                                    </tr>
                                    <tr>
                                        <td><?= e(__('Bill Date')) ?>:</td>
                                        <td class="text-right"><?php try {
                                                                    echo e(\App\Models\Utility::dateFormat($settings, data_get($bill, 'issue_date')));
                                                                } catch (\Throwable $e) {
                                                                    Log::error('Issue date fmt: ' . $e->getMessage());
                                                                    echo e(__('Unavailable'));
                                                                } ?></td>
                                    </tr>
                                    <tr>
                                        <td><?= e(__('Due Date')) ?>:</td>
                                        <td class="text-right"><?php try {
                                                                    echo e(\App\Models\Utility::dateFormat($settings, data_get($bill, 'due_date')));
                                                                } catch (\Throwable $e) {
                                                                    Log::error('Due date fmt: ' . $e->getMessage());
                                                                    echo e(__('Unavailable'));
                                                                } ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td class="text-right">
                            <img class="bill-logo" src="<?= e($img) ?>" alt="">
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
                                <?php
                                if (!empty($settings['registration_number'])) {
                                    echo e(__('Registration Number')) . ' : ' . e($settings['registration_number']) . ' ';
                                }
                                echo '<br>';
                                if (data_get($settings, 'vat_gst_number_switch') === 'on' && !empty($settings['tax_type']) && !empty($settings['vat_number'])) {
                                    echo e($settings['tax_type'] . ' ' . __('Number')) . ' : ' . e($settings['vat_number']) . ' <br>';
                                }
                                ?>
                            </p>
                        </td>
                        <td>
                            <table class="no-space">
                                <tbody>
                                    <tr>
                                        <td colspan="2">
                                            <div class="view-qrcode" style="margin-top:0;">
                                                <?php try {
                                                    echo (string) \Milon\Barcode\DNS2D::getBarcodeHTML(route(ViewsConstants::BIL . '.link.copy', Crypt::encrypt(data_get($bill, 'bill_id'))), "QRCODE", 2, 2);
                                                } catch (\Throwable $e) {
                                                    Log::error('QR gen Throwable: ' . $e->getMessage());
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

            <table>
                <tbody>
                    <tr>
                        <td>
                            <strong style="margin-bottom:10px;display:block;"><?= e(__('Bill To')) ?>:</strong>
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
                        <?php if (data_get($settings, 'shipping_display') === 'on'): ?>
                            <td class="text-right">
                                <strong style="margin-bottom:10px;display:block;"><?= e(__('Ship To')) ?>:</strong>
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
                        <?php endif; ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="bill-body" style="padding-right:0;">
            <table class="add-border bill-summary">
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
                        <?php foreach ($bill->itemData as $key => $item): ?>
                            <?php
                            $itemtax = 0.0;
                            $qty = (float) data_get($item, 'quantity', 0);
                            $price = (float) data_get($item, 'price', 0);
                            $disc = (float) data_get($item, 'discount', 0);
                            ?>
                            <tr>
                                <td><?= e(data_get($item, 'name', '')) ?></td>
                                <td><?php
                                    try {
                                        $unitName = \App\Models\ProductServiceUnit::find(data_get($item, 'unit'));
                                        echo e((string)$qty . ' (' . (string) data_get($unitName, 'name', '') . ')');
                                    } catch (\Throwable $e) {
                                        Log::error('Unit find Throwable: ' . $e->getMessage());
                                        echo e((string)$qty);
                                    }
                                    ?></td>
                                <td><?php try {
                                        echo e(\App\Models\Utility::priceFormat($settings, $price));
                                    } catch (\Throwable $e) {
                                        Log::error('Rate fmt: ' . $e->getMessage());
                                        echo '0';
                                    } ?></td>
                                <td><?= $disc != 0 ? e(\App\Models\Utility::priceFormat($settings, $disc)) : '-' ?></td>
                                <td>
                                    <?php if (!empty(data_get($item, 'itemTax'))): ?>
                                        <?php foreach ((array) $item->itemTax as $taxes): ?>
                                            <?php $itemtax += (float) data_get($taxes, 'tax_price', 0); ?>
                                            <p><?= e(data_get($taxes, 'name', 'Tax')) ?> (<?= e(data_get($taxes, 'rate', '0')) ?>) <?= e(data_get($taxes, 'price', '0')) ?></p>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span>-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php try {
                                        echo e(\App\Models\Utility::priceFormat($settings, ($price * $qty) - $disc + $itemtax));
                                    } catch (\Throwable $e) {
                                        Log::error('Line total fmt: ' . $e->getMessage());
                                        echo '0';
                                    } ?></td>
                            </tr>
                            <?php if (!empty(data_get($item, 'description'))): ?>
                                <tr class="border-0 itm-description">
                                    <td colspan="6" style="border-bottom:1px solid <?= e($color) ?>"> <?= e((string) $item->description) ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td><?= e(__('Total')) ?></td>
                        <td><?= e((string) data_get($bill, 'totalQuantity', 0)) ?></td>
                        <td><?php try {
                                echo e(\App\Models\Utility::priceFormat($settings, data_get($bill, 'totalRate', 0)));
                            } catch (\Throwable $e) {
                                Log::error('totalRate fmt: ' . $e->getMessage());
                                echo '0';
                            } ?></td>
                        <td><?php try {
                                echo e(\App\Models\Utility::priceFormat($settings, data_get($bill, 'totalDiscount', 0)));
                            } catch (\Throwable $e) {
                                Log::error('totalDiscount fmt: ' . $e->getMessage());
                                echo '0';
                            } ?></td>
                        <td><?php try {
                                echo e(\App\Models\Utility::priceFormat($settings, data_get($bill, 'totalTaxPrice', 0)));
                            } catch (\Throwable $e) {
                                Log::error('totalTaxPrice fmt: ' . $e->getMessage());
                                echo '0';
                            } ?></td>
                        <td><?php try {
                                echo e(\App\Models\Utility::priceFormat($settings, $bill->getSubTotal()));
                            } catch (\Throwable $e) {
                                Log::error('subtotal fmt: ' . $e->getMessage());
                                echo '0';
                            } ?></td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                        <td colspan="2" class="sub-total">
                            <table class="total-table">
                                <tr>
                                    <td><?= e(__('Subtotal')) ?>:</td>
                                    <td><?php try {
                                            echo e(\App\Models\Utility::priceFormat($settings, $bill->getSubTotal()));
                                        } catch (\Throwable $e) {
                                            Log::error('subTotal fmt: ' . $e->getMessage());
                                            echo '0';
                                        } ?></td>
                                </tr>
                                <?php if ($bill->getTotalDiscount()): ?>
                                    <tr>
                                        <td><?= e(__('Discount')) ?>:</td>
                                        <td><?php try {
                                                echo e(\App\Models\Utility::priceFormat($settings, $bill->getTotalDiscount()));
                                            } catch (\Throwable $e) {
                                                Log::error('totDisc fmt: ' . $e->getMessage());
                                                echo '0';
                                            } ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!empty($bill->taxesData)): ?>
                                    <?php foreach ($bill->taxesData as $taxName => $taxPrice): ?>
                                        <tr>
                                            <td><?= e($taxName) ?> :</td>
                                            <td><?php try {
                                                    echo e(\App\Models\Utility::priceFormat($settings, $taxPrice));
                                                } catch (\Throwable $e) {
                                                    Log::error('tax row fmt: ' . $e->getMessage());
                                                    echo '0';
                                                } ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <tr>
                                    <td><?= e(__('Total')) ?>:</td>
                                    <td><?php try {
                                            echo e(\App\Models\Utility::priceFormat($settings, $bill->getSubTotal() - $bill->getTotalDiscount() + $bill->getTotalTax()));
                                        } catch (\Throwable $e) {
                                            Log::error('grand total fmt: ' . $e->getMessage());
                                            echo '0';
                                        } ?></td>
                                </tr>
                                <tr>
                                    <td><?= e(__('Paid')) ?>:</td>
                                    <td><?php try {
                                            echo e(\App\Models\Utility::priceFormat($settings, ($bill->getTotal() - $bill->getDue()) - ($bill->billTotalDebitNote())));
                                        } catch (\Throwable $e) {
                                            Log::error('paid fmt: ' . $e->getMessage());
                                            echo '0';
                                        } ?></td>
                                </tr>
                                <tr>
                                    <td><?= e(__('Debit Note')) ?>:</td>
                                    <td><?php try {
                                            echo e(\App\Models\Utility::priceFormat($settings, $bill->billTotalDebitNote()));
                                        } catch (\Throwable $e) {
                                            Log::error('debit note fmt: ' . $e->getMessage());
                                            echo '0';
                                        } ?></td>
                                </tr>
                                <tr>
                                    <td><?= e(__('Due Amount')) ?>:</td>
                                    <td><?php try {
                                            echo e(\App\Models\Utility::priceFormat($settings, $bill->getDue()));
                                        } catch (\Throwable $e) {
                                            Log::error('due fmt: ' . $e->getMessage());
                                            echo '0';
                                        } ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <div class="bill-footer">
                <b><?= e(data_get($settings, 'footer_title', '')) ?></b> <br>
                <?php try {
                    echo (string) data_get($settings, 'footer_notes', '');
                } catch (\Throwable $e) {
                    Log::error('Footer notes Throwable: ' . $e->getMessage());
                } ?>
            </div>
        </div>
    </div>

    <?php if (!isset($preview)): ?>
        <?php try {
            echo view(ViewsConstants::BIL . '.script')->render();
        } catch (\Throwable $e) {
            Log::error('BIL script include Throwable: ' . $e->getMessage());
        } ?>
    <?php endif; ?>
</body>

</html>