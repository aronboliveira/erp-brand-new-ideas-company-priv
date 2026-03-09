@php
    try {
$user      = Auth::user();
        $lang      = Utility::fetchUserLang(user: $user);
        $settings  = Utility::settings();

        $dashUrl   = Route::has('dashboard') ? route('dashboard') : '#';
        $expIdxBase = VW::PRJ_EXP . '.index';
        $expIdxKebab = Str::kebab($expIdxBase);
        $expIdxResolved = Route::has($expIdxBase) ? $expIdxBase : (Route::has($expIdxKebab) ? $expIdxKebab : null);
        $expIdxUrl = $expIdxResolved ? route($expIdxResolved) : '#';

        $hasPriceFmt   = method_exists($user, 'priceFormat');
        $hasDateFmt    = method_exists($user, 'dateFormat');
        $hasExpNumFmt  = method_exists($user, 'expenseNumberFormat');

        $hasAccTotal   = method_exists($expense ?? null, 'getAccountTotal');
        $hasSubTotal   = method_exists($expense ?? null, 'getSubTotal');
        $hasTotDisc    = method_exists($expense ?? null, 'getTotalDiscount');
        $hasTotal      = method_exists($expense ?? null, 'getTotal');
        $hasDue        = method_exists($expense ?? null, 'getDue');
        $hasDebitNotes = method_exists($expense ?? null, 'billTotalDebitNote');

        $expenseNumber = $hasExpNumFmt
            ? ($user?->expenseNumberFormat(data_get($expense ?? null, 'bill_id')) ?? __('Failed to get expense number'))
            : __('Failed to format expense number');

        $paymentDate = $hasDateFmt
            ? ($user?->dateFormat(data_get($expense ?? null, 'bill_date')) ?? __('Failed to get payment date'))
            : __('Failed to format date');

        $statusIdx = (int) data_get($expense ?? null, 'status', -1);
        $statusLbl = data_get(Bill::$statuses ?? [], $statusIdx, __('No status available'));

        $itemsIsList = (is_array($items ?? null) && count($items ?? []) > 0) || (($items ?? null) instanceof Collection && $items->isNotEmpty());

        $totalQuantity   = 0;
        $totalRate       = 0;
        $totalTaxPrice   = 0;
        $totalDiscount   = 0;
        $taxesData       = [];
    } catch (\Throwable $e) {
        \Log::error('expenses/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(EL::ADM)

@section(ST::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/expenses/lang/view.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/expenses/ship.js') }}"></script>
@endsection

@section(YW::ADM_PG_TTL)
    {{ __('Expense Detail') }}
@endsection

@section(YW::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ $dashUrl }}" {{ $dashUrl === '#' ? 'aria-disabled=true' : '' }}>{{ __('Dashboard') }}</a>
    </li>
    <li class="{{ VC::BCI }}">
        <a href="{{ $expIdxUrl }}">{{ __('Expense') }}</a>
    </li>
    <li class="{{ VC::BCI }}">{{ $expenseNumber }}</li>
@endsection

@section(YW::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD }}">
                    <div class="invoice">
                        <div class="invoice-print">
                            <div class="{{ VC::RW }} invoice-title mt-2">
                                <div class="{{ VC::C12 }} {{ VC::CL6 }}">
                                    <h4>{{ __('Expense') }}</h4>
                                </div>
                                <div class="{{ VC::C12 }} {{ VC::CL6 }} text-end">
                                    <h4 class="invoice-number">{{ $expenseNumber }}</h4>
                                </div>
                                <div class="{{ VC::C12 }}"><hr></div>
                            </div>

                            <div class="{{ VC::RW }}">
                                @php
 $uType = (string) data_get($expense ?? null, 'user_type', 'vendor');
@endphp

                                @if($uType === 'employee')
                                    <div class="{{ VC::CL5 ?? 'col-5' }}">
                                        <small class="font-style">
                                            <strong>{{ __('Employee Detail') }} :</strong><br>
                                            @php
                                                $empName  = data_get($user ?? null, 'name');
                                                $empEmail = data_get($user ?? null, 'email');
@endphp
                                            @if($empName || $empEmail)
                                                {{ $empName ?? __('Name unavailable') }}<br>
                                                {{ $empEmail ?? __('Email unavailable') }}<br>
                                            @else
                                                {{ __('No employee details available') }}
                                            @endif
                                        </small>
                                    </div>
                                @elseif($uType === 'customer')
                                    <div class="{{ VC::CL5 ?? 'col-5' }}">
                                        <small class="font-style">
                                            <strong>{{ __('Billed To') }} :</strong><br>
                                            @php
                                                try {
                                                    $bName = data_get($user ?? null, 'billing_name');
                                                    $bAddr = data_get($user ?? null, 'billing_address');
                                                    $bCity = data_get($user ?? null, 'billing_city');
                                                    $bState= data_get($user ?? null, 'billing_state');
                                                    $bZip  = data_get($user ?? null, 'billing_zip');
                                                    $bCountry = data_get($user ?? null, 'billing_country');
                                                    $bPhone = data_get($user ?? null, 'billing_phone');
                                                    $taxSwitch = data_get($settings ?? [], 'vat_gst_number_switch', 'off') === 'on';
                                                    $taxNum = data_get($user ?? null, 'tax_number');
                                                } catch (\Throwable $e) {
                                                    \Log::error('expenses/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            @if($bName || $bAddr || $bCity || $bState || $bZip || $bCountry || $bPhone || ($taxSwitch && $taxNum))
                                                {{ $bName ?? __('Billing name unavailable') }}<br>
                                                {{ $bAddr ?? __('Billing address unavailable') }}<br>
                                                {{ $bCity ? $bCity : __('City unavailable') }}{{ $bCity && $bState ? ', ' : '' }}{{ $bState ?? '' }}{{ $bZip ? '-' . $bZip : '' }}<br>
                                                {{ $bCountry ?? __('Country unavailable') }}<br>
                                                {{ $bPhone ?? __('Phone unavailable') }}<br>
                                                @if($taxSwitch)
                                                    <strong>{{ __('Tax Number') }}:</strong> {{ $taxNum ?? __('Unavailable') }}
                                                @endif
                                            @else
                                                {{ __('No billing details available') }}
                                            @endif
                                        </small>
                                    </div>
                                    @if(Utility::getValByName('shipping_display') == 'on')
                                        <div class="{{ VC::CL4 ?? 'col-4' }}">
                                            <small>
                                                <strong>{{ __('Shipped To') }} :</strong><br>
                                                @php
                                                    try {
                                                        $sName = data_get($user ?? null, 'shipping_name');
                                                        $sAddr = data_get($user ?? null, 'shipping_address');
                                                        $sCity = data_get($user ?? null, 'shipping_city');
                                                        $sState= data_get($user ?? null, 'shipping_state');
                                                        $sZip  = data_get($user ?? null, 'shipping_zip');
                                                        $sCountry = data_get($user ?? null, 'shipping_country');
                                                        $sPhone = data_get($user ?? null, 'shipping_phone');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('expenses/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                @if($sName || $sAddr || $sCity || $sState || $sZip || $sCountry || $sPhone)
                                                    {{ $sName ?? __('Shipping name unavailable') }}<br>
                                                    {{ $sAddr ?? __('Shipping address unavailable') }}<br>
                                                    {{ $sCity ? $sCity : __('City unavailable') }}{{ $sCity && $sState ? ', ' : '' }}{{ $sState ?? '' }}{{ $sZip ? '-' . $sZip : '' }}<br>
                                                    {{ $sCountry ?? __('Country unavailable') }}<br>
                                                    {{ $sPhone ?? __('Phone unavailable') }}<br>
                                                @else
                                                    {{ __('No shipping details available') }}
                                                @endif
                                            </small>
                                        </div>
                                    @endif
                                @else
                                    <div class="{{ VC::CL5 ?? 'col-5' }}">
                                        <small class="font-style">
                                            <strong>{{ __('Billed To') }} :</strong><br>
                                            @php
                                                try {
                                                    $bName = data_get($user ?? null, 'billing_name');
                                                    $bAddr = data_get($user ?? null, 'billing_address');
                                                    $bCity = data_get($user ?? null, 'billing_city');
                                                    $bState= data_get($user ?? null, 'billing_state');
                                                    $bZip  = data_get($user ?? null, 'billing_zip');
                                                    $bCountry = data_get($user ?? null, 'billing_country');
                                                    $bPhone = data_get($user ?? null, 'billing_phone');
                                                    $taxSwitch = data_get($settings ?? [], 'vat_gst_number_switch', 'off') === 'on';
                                                    $taxNum = data_get($user ?? null, 'tax_number');
                                                } catch (\Throwable $e) {
                                                    \Log::error('expenses/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            @if($bName || $bAddr || $bCity || $bState || $bZip || $bCountry || $bPhone || ($taxSwitch && $taxNum))
                                                {{ $bName ?? __('Billing name unavailable') }}<br>
                                                {{ $bAddr ?? __('Billing address unavailable') }}<br>
                                                {{ $bCity ? $bCity : __('City unavailable') }}{{ $bCity && $bState ? ', ' : '' }}{{ $bState ?? '' }}{{ $bZip ? '-' . $bZip : '' }}<br>
                                                {{ $bCountry ?? __('Country unavailable') }}<br>
                                                {{ $bPhone ?? __('Phone unavailable') }}<br>
                                                @if($taxSwitch)
                                                    <strong>{{ __('Tax Number') }}:</strong> {{ $taxNum ?? __('Unavailable') }}
                                                @endif
                                            @else
                                                {{ __('No billing details available') }}
                                            @endif
                                        </small>
                                    </div>
                                    @if(Utility::getValByName('shipping_display') == 'on')
                                        <div class="{{ VC::CL4 ?? 'col-4' }}">
                                            <small>
                                                <strong>{{ __('Shipped To') }} :</strong><br>
                                                @php
                                                    try {
                                                        $sName = data_get($user ?? null, 'shipping_name');
                                                        $sAddr = data_get($user ?? null, 'shipping_address');
                                                        $sCity = data_get($user ?? null, 'shipping_city');
                                                        $sState= data_get($user ?? null, 'shipping_state');
                                                        $sZip  = data_get($user ?? null, 'shipping_zip');
                                                        $sCountry = data_get($user ?? null, 'shipping_country');
                                                        $sPhone = data_get($user ?? null, 'shipping_phone');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('expenses/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                @if($sName || $sAddr || $sCity || $sState || $sZip || $sCountry || $sPhone)
                                                    {{ $sName ?? __('Shipping name unavailable') }}<br>
                                                    {{ $sAddr ?? __('Shipping address unavailable') }}<br>
                                                    {{ $sCity ? $sCity : __('City unavailable') }}{{ $sCity && $sState ? ', ' : '' }}{{ $sState ?? '' }}{{ $sZip ? '-' . $sZip : '' }}<br>
                                                    {{ $sCountry ?? __('Country unavailable') }}<br>
                                                    {{ $sPhone ?? __('Phone unavailable') }}<br>
                                                @else
                                                    {{ __('No shipping details available') }}
                                                @endif
                                            </small>
                                        </div>
                                    @endif
                                @endif

                                <div class="{{ VC::CL ?? 'col' }}">
                                    <small>
                                        <strong>{{ __('Payment Date') }} :</strong><br>
                                        {{ $paymentDate }}<br><br>
                                    </small>
                                </div>
                            </div>

                            <div class="{{ VC::RW }} mt-3">
                                <div class="{{ VC::CL ?? 'col' }}">
                                    <small>
                                        <strong>{{ __('Status') }} : </strong><br>
                                        <span class="badge {{ VC::BG_P }} p-2 {{ VC::PX3 }} rounded">{{ __($statusLbl) }}</span>
                                    </small>
                                </div>
                            </div>

                            <div class="{{ VC::RW }} mt-4">
                                <div class="{{ VC::CM12 }}">
                                    <div class="font-bold {{ VC::MB2 }}">{{ __('Product Summary') }}</div>
                                    <small class="{{ VC::MB2 }} {{ VC::DBL }}">{{ __('All items here cannot be deleted.') }}</small>

                                    <div class="{{ VC::TB_RSP }} {{ VC::MT3 }}">
                                        <table class="{{ VC::TB_MB0 }} table-striped">
                                            <tr>
                                                <th class="{{ VC::TX_DK }}" data-width="40">#</th>
                                                <th class="{{ VC::TX_DK }}">{{ __('Product') }}</th>
                                                <th class="{{ VC::TX_DK }}">{{ __('Quantity') }}</th>
                                                <th class="{{ VC::TX_DK }}">{{ __('Rate') }}</th>
                                                <th class="{{ VC::TX_DK }}">{{ __('Discount') }}</th>
                                                <th class="{{ VC::TX_DK }}">{{ __('Tax') }}</th>
                                                <th class="{{ VC::TX_DK }}">{{ __('Chart Of Account') }}</th>
                                                <th class="{{ VC::TX_DK }}">{{ __('Account Amount') }}</th>
                                                <th class="{{ VC::TX_DK }}">{{ __('Description') }}</th>
                                                <th class="{{ VC::TX_END }} {{ VC::TX_DK }}" width="12%">{{ __('Price') }}<br>
                                                    <small class="{{ VC::TX_DNG }} font-weight-bold">{{ __('after tax & discount') }}</small>
                                                </th>
                                                <th></th>
                                            </tr>

                                            @if($itemsIsList)
                                                @foreach($items as $key => $item)
                                                    @php
                                                        try {
                                                            $lineHasProduct = !empty(data_get($item, 'product_id'));
                                                            $lineQty   = (float) data_get($item, 'quantity', 0);
                                                            $lineRate  = (float) data_get($item, 'price', 0);
                                                            $lineDisc  = (float) data_get($item, 'discount', 0);
                                                            $totalQuantity += $lineQty;
                                                            $totalRate     += $lineRate;
                                                            $totalDiscount += $lineDisc;

                                                            $taxList = [];
                                                            if (!empty(data_get($item, 'tax')) && method_exists(Utility::class, 'tax')) {
                                                                $taxList = Utility::tax($item->tax);
                                                            }

                                                            $taxRowsHtml = '';
                                                            $lineTaxTotal = 0.0;
                                                            if (!empty($taxList)) {
                                                                foreach ($taxList as $tx) {
                                                                    $txName = data_get($tx, 'name', __('Tax'));
                                                                    $txRate = (float) data_get($tx, 'rate', 0);
                                                                    $txAmount = method_exists(Utility::class, 'taxRate')
                                                                        ? (float) Utility::taxRate($txRate, $lineRate, $lineQty, $lineDisc)
                                                                        : 0.0;
                                                                    $lineTaxTotal += $txAmount;
                                                                    $totalTaxPrice += $txAmount;
                                                                    $taxesData[$txName] = ($taxesData[$txName] ?? 0) + $txAmount;
                                                                    $txAmountFmt = $hasPriceFmt ? ($user?->priceFormat($txAmount) ?? __('Failed to format price')) : __('Failed to format price');
                                                                    $taxRowsHtml .= '<tr><td>' . e($txName) . ' (' . e($txRate) . '%)</td><td>' . e($txAmountFmt) . '</td></tr>';
                                                                }
                                                            }
                                                            $unitLabel = '-';
                                                            if ($lineHasProduct) {
                                                                $productModel = method_exists($item, 'product') ? $item->product() : null;
                                                                $unitId = data_get($productModel, 'unit_id');
                                                                $unitNameModel = $unitId ? ProductServiceUnit::find($unitId) : null;
                                                                $unitLabel = data_get($unitNameModel, 'name', __('Unit unavailable'));
                                                            }
                                                            $accountModel = ChartOfAccount::find(data_get($item, 'chart_account_id'));
                                                            $accountName  = data_get($accountModel, 'name', __('Account unavailable'));
                                                            $amountAccount = (float) data_get($item, 'amount', 0);

                                                            $rateFmt     = $hasPriceFmt ? ($user?->priceFormat($lineRate) ?? __('Failed to format price')) : __('Failed to format price');
                                                            $discFmt     = $hasPriceFmt ? ($user?->priceFormat($lineDisc) ?? __('Failed to format price')) : __('Failed to format price');
                                                            $acctFmt     = $hasPriceFmt ? ($user?->priceFormat($amountAccount) ?? __('Failed to format price')) : __('Failed to format price');

                                                            $lineTotalVal = $lineHasProduct
                                                                ? (($lineRate * $lineQty) - $lineDisc + $lineTaxTotal)
                                                                : $amountAccount;
                                                            $lineTotalFmt = $hasPriceFmt ? ($user?->priceFormat($lineTotalVal) ?? __('Failed to format price')) : __('Failed to format price');
                                                        } catch (\Throwable $e) {
                                                            \Log::error('expenses/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp

                                                    @if($lineHasProduct)
                                                        <tr>
                                                            <td>{{ $key + 1 }}</td>
                                                            <td>{{ $unitLabel }}</td>
                                                            <td>{{ $lineQty }} {{ $unitLabel !== '-' ? '(' . $unitLabel . ')' : '' }}</td>
                                                            <td>{{ $rateFmt }}</td>
                                                            <td>{{ $discFmt }}</td>
                                                            <td>
                                                                @if(!empty($taxList))
                                                                    <table>{!! $taxRowsHtml !!}</table>
                                                                @else
                                                                    {{ __('No taxes applied') }}
                                                                @endif
                                                            </td>
                                                            <td>{{ $accountName }}</td>
                                                            <td>{{ $acctFmt }}</td>
                                                            <td>{{ data_get($item, 'description', __('No description')) }}</td>
                                                            <td class="{{ VC::TX_END }}">{{ $lineTotalFmt }}</td>
                                                            <td></td>
                                                        </tr>
                                                    @else
                                                        <tr>
                                                            <td>{{ $key + 1 }}</td>
                                                            <td>-</td>
                                                            <td>-</td>
                                                            <td>-</td>
                                                            <td>-</td>
                                                            <td>-</td>
                                                            <td>{{ $accountName }}</td>
                                                            <td>{{ $acctFmt }}</td>
                                                            <td>{{ __('No description') }}</td>
                                                            <td class="{{ VC::TX_END }}">{{ $acctFmt }}</td>
                                                            <td></td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="11" class="{{ VC::TXCT }}">{{ __('No items found for this expense.') }}</td>
                                                </tr>
                                            @endif

                                            @php
                                                try {
                                                    $totalQtyOut    = $totalQuantity;
                                                    $totalRateOut   = $hasPriceFmt ? ($user?->priceFormat($totalRate) ?? __('Failed to format price')) : __('Failed to format price');
                                                    $totalDiscOut   = $hasPriceFmt ? ($user?->priceFormat($totalDiscount) ?? __('Failed to format price')) : __('Failed to format price');
                                                    $totalTaxOut    = $hasPriceFmt ? ($user?->priceFormat($totalTaxPrice) ?? __('Failed to format price')) : __('Failed to format price');

                                                    $accTotalVal    = $hasAccTotal ? $expense->getAccountTotal() : null;
                                                    $accTotalOut    = is_numeric($accTotalVal)
                                                        ? ($hasPriceFmt ? ($user?->priceFormat($accTotalVal) ?? __('Failed to format price')) : __('Failed to format price'))
                                                        : __('Failed to calculate account total');

                                                    $subTotalVal    = $hasSubTotal ? $expense->getSubTotal() : null;
                                                    $subTotalOut    = is_numeric($subTotalVal)
                                                        ? ($hasPriceFmt ? ($user?->priceFormat($subTotalVal) ?? __('Failed to format price')) : __('Failed to format price'))
                                                        : __('Failed to calculate subtotal');

                                                    $totDiscVal     = $hasTotDisc ? $expense->getTotalDiscount() : null;
                                                    $totDiscOut     = is_numeric($totDiscVal)
                                                        ? ($hasPriceFmt ? ($user?->priceFormat($totDiscVal) ?? __('Failed to format price')) : __('Failed to format price'))
                                                        : __('Failed to calculate discount');

                                                    $grandTotalVal  = $hasTotal ? $expense->getTotal() : null;
                                                    $grandTotalOut  = is_numeric($grandTotalVal)
                                                        ? ($hasPriceFmt ? ($user?->priceFormat($grandTotalVal) ?? __('Failed to format price')) : __('Failed to format price'))
                                                        : __('Failed to calculate total');

                                                    $paidValCalcOk  = $hasTotal && $hasDue && $hasDebitNotes;
                                                    $paidVal        = $paidValCalcOk ? (($expense->getTotal() - $expense->getDue()) - $expense->billTotalDebitNote()) : null;
                                                    $paidOut        = is_numeric($paidVal)
                                                        ? ($hasPriceFmt ? ($user?->priceFormat($paidVal) ?? __('Failed to format price')) : __('Failed to format price'))
                                                        : __('Failed to calculate paid amount');
                                                } catch (\Throwable $e) {
                                                    \Log::error('expenses/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp

                                            <tfoot>
                                            <tr>
                                                <td></td>
                                                <td><b>{{ __('Total') }}</b></td>
                                                <td><b>{{ $totalQtyOut }}</b></td>
                                                <td><b>{{ $totalRateOut }}</b></td>
                                                <td><b>{{ $totalDiscOut }}</b></td>
                                                <td><b>{{ $totalTaxOut }}</b></td>
                                                <td></td>
                                                <td><b>{{ $accTotalOut }}</b></td>
                                            </tr>
                                            <tr>
                                                <td colspan="8"></td>
                                                <td class="{{ VC::TX_END }}"><b>{{ __('Sub Total') }}</b></td>
                                                <td class="{{ VC::TX_END }}">{{ $subTotalOut }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="8"></td>
                                                <td class="{{ VC::TX_END }}"><b>{{ __('Discount') }}</b></td>
                                                <td class="{{ VC::TX_END }}">{{ $totDiscOut }}</td>
                                            </tr>

                                            @if(!empty($taxesData))
                                                @foreach($taxesData as $taxName => $taxPrice)
                                                    @php
                                                        $txOut = $hasPriceFmt ? ($user?->priceFormat($taxPrice) ?? __('Failed to format price')) : __('Failed to format price');
@endphp
                                                    <tr>
                                                        <td colspan="8"></td>
                                                        <td class="{{ VC::TX_END }}"><b>{{ $taxName }}</b></td>
                                                        <td class="{{ VC::TX_END }}">{{ $txOut }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif

                                            <tr>
                                                <td colspan="8"></td>
                                                <td class="blue-text {{ VC::TX_END }}"><b>{{ __('Total') }}</b></td>
                                                <td class="blue-text {{ VC::TX_END }}">{{ $grandTotalOut }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="8"></td>
                                                <td class="{{ VC::TX_END }}"><b>{{ __('Paid') }}</b></td>
                                                <td class="{{ VC::TX_END }}">{{ $paidOut }}</td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
