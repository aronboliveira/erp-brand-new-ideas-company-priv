@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Invoice, Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Receivable Reports') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Receivable Reports') }}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ 'assets/js/routes/reports/receivables/lang/pdf.js' }}"></script>
    <script async src="{{ 'assets/js/routes/reports/receivables/pdf.js' }}"></script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    {{-- <div class="float-end me-2">
        {{ Form::open(['route' => ['receivables.export']]) }}
        <input type="hidden" name="start_date" class="start_date">
        <input type="hidden" name="end_date" class="end_date">
        <input type="hidden" name="report" class="report">
        <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Export') }}"
            data-original-title="{{ __('Export') }}"><i class="ti ti-file-export"></i></button>
        {{ Form::close() }}
    </div> --}}
    {{-- <div class="float-end me-2">
        <a href="{{ route(ViewsConstants::RPT . '.balance.sheet', 'vertical') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
            title="{{ __('Vertical View') }}" data-original-title="{{ __('Vertical View') }}"><i
                class="ti ti-separator-horizontal"></i></a>
    </div> --}}
    <div class="float-end">
        @php
            $rcvPrintBase = VW::RPT.'.receivables.print';
            $rcvPrintKebab = Str::kebab($rcvPrintBase);
            $rcvPrintResolved = Route::has($rcvPrintBase) ? $rcvPrintBase : (Route::has($rcvPrintKebab) ? $rcvPrintKebab : null);
            $actionRoute = $rcvPrintResolved ? [$rcvPrintResolved] : ['#'];
            $actionUrl = $rcvPrintResolved ? route($rcvPrintResolved) : '#';
            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
            $guardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'print_receivables_route_unavailable') ?? 'Print receivables route is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        {{ Form::open(['route' => $actionRoute, 'method' => 'POST', 'id' => 'receivables-print', 'data-url' => $actionUrl, 'data-guard-msg' => $guardMsg, 'data-sv-localized' => 'true']) }}
            <input type="hidden" name="start_date" class="start_date">
            <input type="hidden" name="end_date" class="end_date">
            <input type="hidden" name="report" class="report">
            <button type="submit" class="{{ VC::BT_SM_PM }}" data-bs-toggle="tooltip" title="{{ __('Print') }}" data-original-title="{{ __('Print') }}">
                <i class="ti ti-printer"></i>
            </button>
        {{ Form::close() }}
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/receivables/print.js') }}" defer></script>
        @endpush
    </div>
    <div class="float-end me-2" id="filter">
        <button id="filter" class="{{ VC::BT_SM_PM }}"><i class="ti ti-filter"></i></button>
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::MT4 }}">
        <div class="{{ VC::RW }} justify-content-center">
            <div class="{{ VC::CM12 }}">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="{{ VC::CD }}" id="show_filter" style="display:none;">
                        <div class="card-body">
                            @php
                                $receivablesBase    = ViewsConstants::RPT.'.receivables';
                                $receivablesKebab   = Str::kebab($receivablesBase);
                                $receivablesResolved= Route::has($receivablesBase) ? $receivablesBase : (Route::has($receivablesKebab) ? $receivablesKebab : null);
                                $actionRoute        = $receivablesResolved ? [$receivablesResolved] : ['#'];
                                $actionUrl          = $receivablesResolved ? route($receivablesResolved) : '#';
                                $langValue          = isset($lang) ? $lang : Utility::fetchUserLang();
                                $applyGuardMsg      = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'apply_receivables_route_unavailable') ?? 'Apply receivables route is unavailable. Please contact technical support or your domain administrator.';
                                $resetGuardMsg      = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'reset_receivables_route_unavailable') ?? 'Reset receivables route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            {{ Form::open(['route' => $actionRoute, 'method' => 'GET', 'id' => 'report_bill_summary', 'data-url' => $actionUrl, 'data-guard-msg' => $applyGuardMsg, 'data-sv-localized' => 'true']) }}
                                <div class="{{ VC::R_ALC_JCE }}">
                                    <div class="col-xl-10">
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::CL_XL3 }}">
                                                <div class="btn-box"></div>
                                            </div>
                                            <div class="{{ VC::CL_XL3 }}">
                                                <div class="btn-box"></div>
                                            </div>
                                            <div class="{{ VC::CL_XL3 }}">
                                                <div class="btn-box">
                                                    {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                                                    {{ Form::date('start_date', $filter['startDateRange'], ['class' => 'startDate ' . VC::FM_CT]) }}
                                                </div>
                                            </div>
                                            <div class="{{ VC::CL_XL3 }}">
                                                <div class="btn-box">
                                                    {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                                                    {{ Form::date('end_date', $filter['endDateRange'], ['class' => 'endDate ' . VC::FM_CT]) }}
                                                </div>
                                            </div>
                                            <input type="hidden" name="report" class="report">
                                        </div>
                                    </div>
                                    <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::C_AT }}">
                                                <a id="apply-receivables-index"
                                                href="#"
                                                class="{{ VC::BT_SM_PM }}"
                                                data-form-id="report_bill_summary"
                                                data-guard-msg="{{ $applyGuardMsg }}"
                                                data-sv-localized="true"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Apply') }}"
                                                data-original-title="{{ __('apply') }}">
                                                    <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                                </a>
                                                <a id="reset-receivables-index"
                                                href="{{ $actionUrl }}"
                                                class="{{ VC::BT_SM_DG }}"
                                                data-url="{{ $actionUrl }}"
                                                data-guard-msg="{{ $resetGuardMsg }}"
                                                data-sv-localized="true"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Reset') }}"
                                                data-original-title="{{ __('Reset') }}">
                                                    <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {{ Form::close() }}
                            @push(StacksConstants::ADM_SCR_PG)
                                <script src="{{ asset('assets/js/routes/reports/receivables/apply.js') }}" defer></script>
                                <script src="{{ asset('assets/js/routes/reports/receivables/reset.js') }}" defer></script>
                            @endpush
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12" id="invoice-container">
            <div class="card">
                @php
                    $tabs = [
                        ['id' => 'customer_balance',  'label' => __('Customer Balance')],
                        ['id' => 'receivable_summary','label' => __('Receivable Summary')],
                        ['id' => 'receivable_details','label' => __('Receivable Details')],
                        ['id' => 'aging_summary',     'label' => __('Aging Summary')],
                        ['id' => 'aging_details',     'label' => __('Aging Details')],
                    ];
                    $activeTab = request('tab', 'customer_balance');
                @endphp
                <div class="card-header">
                    <div class="{{ VC::DFL }} {{ VC::JCB }} w-100">
                        <ul class="{{ VC::NAV_PL }} {{ VC::MB3 }}" id="pills-tab" role="tablist">
                            @foreach($tabs as $t)
                                <li class="{{ VC::NV_IT }}">
                                    <a  id="tab-{{ $t['id'] }}"
                                        class="{{ VC::NV_LK }} {{ $activeTab === $t['id'] ? 'active' : '' }}"
                                        data-bs-toggle="pill"
                                        href="#{{ $t['id'] }}"
                                        role="tab"
                                        aria-controls="{{ $t['id'] }}"
                                        aria-selected="{{ $activeTab === $t['id'] ? 'true' : 'false' }}">
                                        {{ $t['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @push(StacksConstants::ADM_SCR_PG)
                    <script>
                        (function () {
                            const list = document.getElementById('pills-tab');
                            if (!list) return;
                            const links = Array.from(list.querySelectorAll('a[data-bs-toggle="pill"]'));
                            const ids   = links.map(a => a.getAttribute('href')?.replace('#','')).filter(Boolean);
                            const url   = new URL(window.location.href);
                            const hash  = (window.location.hash || '').replace('#','');
                            const qTab  = url.searchParams.get('tab');
                            const init  = ids.includes(hash) ? hash : (ids.includes(qTab) ? qTab : ids[0]);
                            if (init) {
                                const el = list.querySelector(`a[href="#${init}"]`);
                                if (el && !el.classList.contains('active')) {
                                    el.click();
                                }
                                url.searchParams.set('tab', init);
                                history.replaceState(null, '', url.toString().split('#')[0] + '#' + init);
                            }
                            links.forEach(a => {
                                a.addEventListener('shown.bs.tab', function (ev) {
                                    const id = this.getAttribute('href').replace('#','');
                                    const u  = new URL(window.location.href);
                                    u.searchParams.set('tab', id);
                                    history.replaceState(null, '', u.toString().split('#')[0] + '#' + id);
                                });
                            });
                        })();
                    </script>
                @endpush
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="tab-content" id="myTabContent2">
                                @php
                                    $statusClasses = [
                                        0 => 'bg-secondary',
                                        1 => 'bg-warning',
                                        2 => 'bg-danger',
                                        3 => 'bg-info',
                                        4 => 'bg-primary',
                                    ];
                                @endphp
                                <div class="tab-pane fade show active" id="customer_balance" role="tabpanel" aria-labelledby="receivable-tab1">
                                    <table class="{{ VC::TB }} table-flush" id="report-customer-balance">
                                        <thead>
                                        <tr>
                                            <th width="33%">{{ __('Customer Name') }}</th>
                                            <th width="33%">{{ __('Invoice Balance') }}</th>
                                            <th width="33%">{{ __('Available Credits') }}</th>
                                            <th class="text-end">{{ __('Balance') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $grouped = collect($receivableCustomers ?? [])->groupBy('name')->map(function ($rows, $name) {
                                                $price      = (float) $rows->sum('price');
                                                $totalTax   = (float) $rows->sum('total_tax');
                                                $paid       = (float) $rows->sum(fn($r) => $r['pay_price'] ?? 0);
                                                $credits    = (float) $rows->sum('credit_price');

                                                return [
                                                    'name'            => $name,
                                                    'invoice_balance' => $price + $totalTax - $paid,
                                                    'credits'         => $credits,
                                                ];
                                            });

                                            $grandTotal = 0.0;
                                        @endphp

                                        @forelse ($grouped as $row)
                                            @php
                                                $balance    = $row['invoice_balance'] - $row['credits'];
                                                $grandTotal += $balance;
                                            @endphp
                                            <tr>
                                                <td>{{ $row['name'] }}</td>
                                                <td>{{ $user?->priceFormat($row['invoice_balance']) }}</td>
                                                <td>{{ $user?->priceFormat($row['credits'] ?: 0) }}</td>
                                                <td class="text-end">{{ $user?->priceFormat($balance) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted">{{ __('No data found') }}</td></tr>
                                        @endforelse

                                        @if(($receivableCustomers ?? []) !== [])
                                            <tr>
                                                <th>{{ __('Total') }}</th>
                                                <td></td>
                                                <td></td>
                                                <th class="text-end">{{ $user?->priceFormat($grandTotal) }}</th>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="receivable_summary" role="tabpanel" aria-labelledby="receivable-tab2">
                                    <table class="{{ VC::TB }} table-flush" id="report-receivable-summary">
                                        <thead>
                                        <tr>
                                            <th>{{ __('Customer Name') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Transaction') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Transaction Type') }}</th>
                                            <th>{{ __('Total') }}</th>
                                            <th>{{ __('Balance') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $rows = $receivableSummaries ?? [];
                                            usort($rows, fn($a,$b) => strtotime($b['issue_date']) <=> strtotime($a['issue_date']));
                                            $totalBalance = 0.0;
                                            $totalAmount  = 0.0;
                                        @endphp

                                        @forelse ($rows as $r)
                                            @php
                                                $isInvoice          = !empty($r['invoice']);
                                                $receivableAmount   = $isInvoice ? ($r['price'] + $r['total_tax']) : -$r['price'];
                                                $paid               = (float) ($r['pay_price'] ?? 0);
                                                $balance            = $receivableAmount - $paid;

                                                $totalBalance      += $balance;
                                                $totalAmount       += $receivableAmount;

                                                $bgClass = $statusClasses[$r['status']] ?? null;
                                            @endphp
                                            <tr>
                                                <td>{{ $r['name'] }}</td>
                                                <td>{{ $r['issue_date'] }}</td>

                                                <td>
                                                    @if ($isInvoice)
                                                        {{ $user?->invoiceNumberFormat($r['invoice']) }}
                                                    @else
                                                        {{ __('Credit Note') }}
                                                    @endif
                                                </td>

                                                <td>
                                                    @if($bgClass)
                                                        <span class="status_badge {{ VC::BDG }} {{ $bgClass }} p-2 {{ VC::PX3 }} rounded">
                                                            {{ __(Invoice::$statuses[$r['status']] ?? '-') }}
                                                        </span>
                                                    @else
                                                        <span class="p-2 {{ VC::PX3 }}">-</span>
                                                    @endif
                                                </td>

                                                <td>{{ $isInvoice ? __('Invoice') : __('Credit Note') }}</td>
                                                <td>{{ $user?->priceFormat($receivableAmount) }}</td>
                                                <td>{{ $user?->priceFormat($balance) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="text-center text-muted">{{ __('No data found') }}</td></tr>
                                        @endforelse

                                        @if(($receivableSummaries ?? []) !== [])
                                            <tr>
                                                <th>{{ __('Total') }}</th>
                                                <th></th><th></th><th></th><th></th>
                                                <th>{{ $user?->priceFormat($totalAmount) }}</th>
                                                <th>{{ $user?->priceFormat($totalBalance) }}</th>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="receivable_details" role="tabpanel" aria-labelledby="receivable-tab3">
                                    <table class="{{ VC::TB }} table-flush" id="report-receivable-details">
                                        <thead>
                                        <tr>
                                            <th>{{ __('Customer Name') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Transaction') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Transaction Type') }}</th>
                                            <th>{{ __('Item Name') }}</th>
                                            <th>{{ __('Quantity Ordered') }}</th>
                                            <th>{{ __('Item Price') }}</th>
                                            <th>{{ __('Total') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $rows = $receivableDetails ?? [];
                                            usort($rows, fn($a,$b) => strtotime($b['issue_date']) <=> strtotime($a['issue_date']));
                                            $grandTotal = 0.0;
                                            $grandQty   = 0;
                                        @endphp

                                        @forelse ($rows as $row)
                                            @php
                                                $isInvoice         = !empty($row['invoice']);
                                                $unitPrice         = $isInvoice ? $row['price'] : -$row['price'];
                                                $qty               = $isInvoice ? ($row['quantity'] ?? 0) : 0;
                                                $lineTotal         = $isInvoice ? ($unitPrice * $qty) : -$row['price'];
                                                $grandTotal       += $lineTotal;
                                                $grandQty         += $qty;
                                                $bgClass           = $statusClasses[$row['status']] ?? null;
                                            @endphp
                                            <tr>
                                                <td>{{ $row['name'] }}</td>
                                                <td>{{ $row['issue_date'] }}</td>
                                                <td>
                                                    @if ($isInvoice)
                                                        {{ $user?->invoiceNumberFormat($row['invoice']) }}
                                                    @else
                                                        {{ __('Credit Note') }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($bgClass)
                                                        <span class="status_badge {{ VC::BDG }} {{ $bgClass }} p-2 {{ VC::PX3 }} rounded">
                                                            {{ __(Invoice::$statuses[$row['status']] ?? '-') }}
                                                        </span>
                                                    @else
                                                        <span class="p-2 {{ VC::PX3 }}">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ $isInvoice ? __('Invoice') : __('Credit Note') }}</td>
                                                <td>{{ $row['product_name'] }}</td>
                                                <td>{{ $qty }}</td>
                                                <td>{{ $user?->priceFormat($unitPrice) }}</td>
                                                <td>{{ $user?->priceFormat($lineTotal) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="9" class="text-center text-muted">{{ __('No data found') }}</td></tr>
                                        @endforelse

                                        @if(!empty($rows))
                                            <tr>
                                                <th>{{ __('Total') }}</th>
                                                <th colspan="5"></th>
                                                <th>{{ $grandQty }}</th>
                                                <th></th>
                                                <th>{{ $user?->priceFormat($grandTotal) }}</th>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="aging_summary" role="tabpanel" aria-labelledby="receivable-tab4">
                                    <table class="{{ VC::TB }} table-flush" id="report-aging-summary">
                                        <thead>
                                        <tr>
                                            <th>{{ __('Customer Name') }}</th>
                                            <th>{{ __('Current') }}</th>
                                            <th>{{ __('1-15 DAYS') }}</th>
                                            <th>{{ __('16-30 DAYS') }}</th>
                                            <th>{{ __('31-45 DAYS') }}</th>
                                            <th>{{ __('> 45 DAYS') }}</th>
                                            <th>{{ __('Total') }}</th>
                                        </tr>
                                        </thead>
                                        @php
                                            $summaries   = $agingSummaries ?? [];
                                            $totCurr     = 0.0; $tot15 = 0.0; $tot30 = 0.0; $tot45 = 0.0; $totMore45 = 0.0; $totAll = 0.0;
                                        @endphp
                                        <tbody>
                                        @forelse($summaries as $customer => $s)
                                            <tr>
                                                <td>{{ $customer }}</td>
                                                <td>{{ $user?->priceFormat($s['current']) }}</td>
                                                <td>{{ $user?->priceFormat($s['1_15_days']) }}</td>
                                                <td>{{ $user?->priceFormat($s['16_30_days']) }}</td>
                                                <td>{{ $user?->priceFormat($s['31_45_days']) }}</td>
                                                <td>{{ $user?->priceFormat($s['greater_than_45_days']) }}</td>
                                                <td>{{ $user?->priceFormat($s['total_due']) }}</td>
                                            </tr>
                                            @php
                                                $totCurr   += $s['current'];
                                                $tot15     += $s['1_15_days'];
                                                $tot30     += $s['16_30_days'];
                                                $tot45     += $s['31_45_days'];
                                                $totMore45 += $s['greater_than_45_days'];
                                                $totAll    += $s['total_due'];
                                            @endphp
                                        @empty
                                            <tr><td colspan="7" class="text-center text-muted">{{ __('No data found') }}</td></tr>
                                        @endforelse

                                        @if(!empty($summaries))
                                            <tr>
                                                <th>{{ __('Total') }}</th>
                                                <th>{{ $user?->priceFormat($totCurr) }}</th>
                                                <th>{{ $user?->priceFormat($tot15) }}</th>
                                                <th>{{ $user?->priceFormat($tot30) }}</th>
                                                <th>{{ $user?->priceFormat($tot45) }}</th>
                                                <th>{{ $user?->priceFormat($totMore45) }}</th>
                                                <th>{{ $user?->priceFormat($totAll) }}</th>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="aging_details" role="tabpanel" aria-labelledby="receivable-tab5">
                                    <table class="{{ VC::TB }} table-flush" id="report-aging-details">
                                        <thead>
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Transaction') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Customer Name') }}</th>
                                            <th>{{ __('Age') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Balance Due') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $bucketTotals = [
                                                'current'   => ['amt' => 0.0, 'due' => 0.0, 'rows' => $currents ?? []],
                                                '1_15'      => ['amt' => 0.0, 'due' => 0.0, 'rows' => $days1to15 ?? []],
                                                '16_30'     => ['amt' => 0.0, 'due' => 0.0, 'rows' => $days16to30 ?? []],
                                                '31_45'     => ['amt' => 0.0, 'due' => 0.0, 'rows' => $days31to45 ?? []],
                                                '45_plus'   => ['amt' => 0.0, 'due' => 0.0, 'rows' => $moreThan45 ?? []],
                                            ];
                                            $labels = [
                                                '45_plus' => __('> 45 Days'),
                                                '31_45'   => __('31 to 45 Days'),
                                                '16_30'   => __('16 to 30 Days'),
                                                '1_15'    => __('1 to 15 Days'),
                                                'current' => __('Current'),
                                            ];
                                        @endphp

                                        @foreach (['45_plus','31_45','16_30','1_15','current'] as $key)
                                            @php $bucket = $bucketTotals[$key]; @endphp
                                            @if(!empty($bucket['rows']))
                                                <tr class="table-light">
                                                    <th colspan="8">{{ $labels[$key] }}</th>
                                                </tr>
                                            @endif

                                            @foreach ($bucket['rows'] as $r)
                                                @php
                                                    $bucketTotals[$key]['amt'] += $r['total_price'];
                                                    $bucketTotals[$key]['due'] += $r['balance_due'];
                                                    $bgClass = $statusClasses[$r['status']] ?? null;
                                                @endphp
                                                <tr>
                                                    <td>{{ $r['due_date'] }}</td>
                                                    <td>{{ $user?->invoiceNumberFormat($r['invoice_id']) }}</td>
                                                    <td>{{ __('Invoice') }}</td>
                                                    <td>
                                                        @if($bgClass)
                                                            <span class="status_badge {{ VC::BDG }} {{ $bgClass }} p-2 {{ VC::PX3 }} rounded">
                                                                {{ __(Invoice::$statuses[$r['status']] ?? '-') }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $r['name'] }}</td>
                                                    <td>{{ $key === 'current' ? '-' : ($r['age'] . ' ' . __('Days')) }}</td>
                                                    <td>{{ $user?->priceFormat($r['total_price']) }}</td>
                                                    <td>{{ $user?->priceFormat($r['balance_due']) }}</td>
                                                </tr>
                                            @endforeach

                                            @if(!empty($bucket['rows']))
                                                <tr>
                                                    <th colspan="6"></th>
                                                    <th>{{ $user?->priceFormat($bucketTotals[$key]['amt']) }}</th>
                                                    <th>{{ $user?->priceFormat($bucketTotals[$key]['due']) }}</th>
                                                </tr>
                                            @endif
                                        @endforeach

                                        @php
                                            $grandAmt = array_sum(array_column($bucketTotals,'amt'));
                                            $grandDue = array_sum(array_column($bucketTotals,'due'));
                                        @endphp

                                        @if($grandAmt || $grandDue)
                                            <tr>
                                                <th>{{ __('Total') }}</th>
                                                <th colspan="5"></th>
                                                <th>{{ $user?->priceFormat($grandAmt) }}</th>
                                                <th>{{ $user?->priceFormat($grandDue) }}</th>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
