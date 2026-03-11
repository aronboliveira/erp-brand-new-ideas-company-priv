@php
    $user = null;
    $lang = null;
    $canAv = false;
    try {
        $user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);
        $plan ??= $user?->getPlan?->first() ?? Plan::find(DatabaseConstants::DEFAULT_PLAN);
        $canAv = $user && method_exists($user, 'can');
    } catch (\Throwable $e) {
        \Log::error('dashboard/account_dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Dashboard')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    @if($canAv && ($user?->can(PermissionsConstants::SHW_ACC_DSB) || $user[UsersConstants::COL_TP] == PermissionsConstants::SA))
        @php
            try {
                if (!$user?->can(PermissionsConstants::SHW_ACC_DSB) && $user[UsersConstants::COL_TP] == PermissionsConstants::SA)
                    Log::notice(
                        'User is a super admin bypassing. Be sure this is intended or report.',
                        [
                            'user_id' => $user->id,
                            'user_type' => $user[UsersConstants::COL_TP],
                            'permission' => PermissionsConstants::SHW_ACC_DSB
                        ]
                    );
            } catch (\Throwable $e) {
                \Log::error('dashboard/account_dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <script async src="{{ asset('assets/js/routes/dashboards/account/lang/cash.js') }}"></script>
        <script defer>
            (() => {
                const RG = window.RouteGuard || {};
                const getMsg = RG.getMsg || ((k, el) => el?.getAttribute?.('data-guard-msg') || '');
                const showError = RG.showToast || (m => { if (m) console.warn('[Dashboard]', m); });
                const renderCashFlow = () => {
                    try {
                        const el = document.querySelector("#cash-flow");
                        if (!el || !window.ApexCharts) throw new Error('cash_flow_unavailable');
                        new ApexCharts(el, {
                            series: [
                                { name: "{{__('Income')}}", data: {!! !empty($incExpLineChartData['income']) ? json_encode($incExpLineChartData['income']) : '[]' !!} },
                                { name: "{{__('Expense')}}", data: {!! !empty($incExpLineChartData['expense']) ? json_encode($incExpLineChartData['expense']) : '[]' !!} }
                            ],
                            chart: { height: 250, type: "area", dropShadow: { enabled: true, color: "#000", top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
                            dataLabels: { enabled: false },
                            stroke: { width: 2, curve: "smooth" },
                            title: { text: "", align: "left" },
                            xaxis: { categories: {!! json_encode($incExpLineChartData['day'] ?? []) !!}, title: { text: "{{ __('Date') }}" } },
                            colors: ["#6fd944", "#ff3a6e"],
                            grid: { strokeDashArray: 4 },
                            legend: { show: false },
                            yaxis: { title: { text: "{{ __('Amount') }}" } }
                        }).render();
                    } catch (e) { showError(getMsg(e.message, document.body)); }
                };
                const renderIncExpBar = () => {
                    try {
                        const el = document.querySelector("#incExpBarChart");
                        if (!el || !window.ApexCharts) throw new Error('incExpBarChart_unavailable');
                        new ApexCharts(el, {
                            chart: { height: 180, type: "bar", toolbar: { show: false } },
                            dataLabels: { enabled: false },
                            stroke: { width: 2, curve: "smooth" },
                            series: [
                                { name: "{{__('Income')}}", data: {!! !empty($incExpBarChartData['income']) ? json_encode($incExpBarChartData['income']) : '[]' !!} },
                                { name: "{{__('Expense')}}", data: {!! !empty($incExpBarChartData['expense']) ? json_encode($incExpBarChartData['expense']) : '[]' !!} }
                            ],
                            xaxis: { categories: {!! json_encode($incExpBarChartData['month'] ?? []) !!} },
                            colors: ["#3ec9d6", "#FF3A6E"],
                            fill: { type: "solid" },
                            grid: { strokeDashArray: 4 },
                            legend: { show: true, position: "top", horizontalAlign: "right" }
                        }).render();
                    } catch (e) { showError(getMsg(e.message, document.body)); }
                };
                const renderExpenseByCategory = () => {
                    try {
                        const el = document.querySelector("#expenseByCategory");
                        if (!el || !window.ApexCharts) throw new Error('expenseByCategory_unavailable');
                        new ApexCharts(el, {
                            chart: { height: 140, type: "donut" },
                            dataLabels: { enabled: false },
                            plotOptions: { pie: { donut: { size: "70%" } } },
                            series: {!! json_encode($expenseCatAmount) !!},
                            colors: {!! json_encode($expenseCategoryColor) !!},
                            labels: {!! json_encode($expenseCategory) !!},
                            legend: { show: true }
                        }).render();
                    } catch (e) { showError(getMsg(e.message, document.body)); }
                };
                const renderIncomeByCategory = () => {
                    try {
                        const el = document.querySelector("#incomeByCategory");
                        if (!el || !window.ApexCharts) throw new Error('incomeByCategory_unavailable');
                        new ApexCharts(el, {
                            chart: { height: 140, type: "donut" },
                            dataLabels: { enabled: false },
                            plotOptions: { pie: { donut: { size: "70%" } } },
                            series: {!! !empty($incomeCatAmount) ? json_encode($incomeCatAmount) : '[]' !!},
                            colors: {!! !empty($incomeCategoryColor) ? json_encode($incomeCategoryColor) : '[]' !!},
                            labels: {!! !empty($incomeCategory) ? json_encode($incomeCategory) : '[]' !!},
                            legend: { show: true }
                        }).render();
                    } catch (e) { showError(getMsg(e.message, document.body)); }
                };
                const renderLimitChart = () => {
                    try {
                        const el = document.querySelector("#limit-chart");
                        if (!el || !window.ApexCharts) throw new Error('limitChart_unavailable');
                        new ApexCharts(el, {
                            series: [{{ round($storage_limit,2) }}],
                            chart: { height: 350, type: "radialBar", offsetY: -20, sparkline: { enabled: true } },
                            plotOptions: { radialBar: { startAngle: -90, endAngle: 90, track: { background: "#e7e7e7", strokeWidth: "97%", margin: 5 }, dataLabels: { name: { show: true }, value: { offsetY: -50, fontSize: "20px" } } } },
                            grid: { padding: { top: -10 } },
                            colors: ["#6FD943"],
                            labels: ["Used"]
                        }).render();
                    } catch (e) { showError(getMsg(e.message, document.body)); }
                };
                renderCashFlow();
                renderIncExpBar();
                renderExpenseByCategory();
                renderIncomeByCategory();
                renderLimitChart();
            })();
        </script>
    @endif
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Account')}}</li>
@endsection
@push(StacksConstants::ADM_CSS)
<style>
    /* ── Dashboard metric cards gradient icons ── */
    .theme-avatar.bg-primary {
        background: linear-gradient(135deg, #6366f1 0%, #818cf8 50%, #a5b4fc 100%) !important;
    }
    .theme-avatar.bg-info {
        background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 50%, #67e8f9 100%) !important;
    }
    .theme-avatar.bg-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 50%, #fcd34d 100%) !important;
    }
    .theme-avatar.bg-danger {
        background: linear-gradient(135deg, #ef4444 0%, #f87171 50%, #fca5a5 100%) !important;
    }
    .theme-avatar {
        box-shadow: 0 4px 15px rgba(0,0,0,.15);
        transition: transform .3s cubic-bezier(.25,.46,.45,.94), box-shadow .3s ease;
    }

    /* ── Card mount animation ── */
    @keyframes dashCardMount {
        0%   { opacity: 0; transform: translateY(18px) scale(.97); }
        100% { opacity: 1; transform: translateY(0) scale(1); }
    }
    .col-lg-3.col-6 > .card {
        animation: dashCardMount .5s cubic-bezier(.25,.46,.45,.94) both;
    }
    .col-lg-3.col-6:nth-child(1) > .card { animation-delay: .05s; }
    .col-lg-3.col-6:nth-child(2) > .card { animation-delay: .12s; }
    .col-lg-3.col-6:nth-child(3) > .card { animation-delay: .19s; }
    .col-lg-3.col-6:nth-child(4) > .card { animation-delay: .26s; }

    /* ── Hover / focus lift ── */
    .col-lg-3.col-6 > .card {
        transition: transform .3s cubic-bezier(.25,.46,.45,.94), box-shadow .3s ease;
        will-change: transform;
    }
    .col-lg-3.col-6 > .card:hover,
    .col-lg-3.col-6 > .card:focus-within {
        transform: scale(1.04) translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,.12);
    }
    .col-lg-3.col-6 > .card:hover .theme-avatar,
    .col-lg-3.col-6 > .card:focus-within .theme-avatar {
        transform: scale(1.12) translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,.2);
    }
</style>
@endpush
@section('content')
    <div class="row">
        <div class="{{ VC::CS12 }}">
            <div class="row">
                @php
                    $safeCount = static function ($user, string $method): int {
                        try {
                            return (isset($user) && method_exists($user, $method)) ? $user->$method() : 0;
                        } catch (\Throwable $e) {
                            \Log::error("dashboard metric {$method}: " . $e->getMessage());
                            return 0;
                        }
                    };
                    $metrics=[
                        ['bg'=>'bg-primary','icon'=>VC::TI_USRS,'label'=>__('Customers'),'value'=> $safeCount($user, 'countCustomers')],
                        ['bg'=>'bg-info','icon'=>VC::TI_USRS,'label'=>__('Vendors'),'value'=> $safeCount($user, 'countVendors')],
                        ['bg'=>'bg-warning','icon'=>'ti ti-report-money','label'=>__('Invoices'),'value'=> $safeCount($user, 'countInvoices')],
                        ['bg'=>'bg-danger','icon'=>'ti ti-report-money','label'=>__('Bills'),'value'=> $safeCount($user, 'countBills')]
                    ];
                    $currentYear ??= (string) now()->format('Y');
                    $bankAccountDetail ??= [];
                    $latestIncome ??= [];
                    $latestExpense ??= [];
                    $recentInvoice ??= [];
                    $recentBill ??= [];
                    $asString = static function ($value, string $alias) {
                        if ($value instanceof \Carbon\Carbon || $value instanceof \DateTimeInterface) {
                            return $value->format('Y-m-d');
                        }
                        return isset($value) && is_string($value) && trim($value) !== ''
                            ? $value
                            : __('No '.$alias.' available');
                    };
                    $asClass = static function ($value, string $fallback = 'bg-secondary') {
                        return isset($value) && is_string($value) && trim($value) !== ''
                            ? $value
                            : $fallback;
                    };
                    $asNumber = static function ($value, string $alias) {
                        return isset($value) && is_numeric($value)
                            ? $value
                            : __('Could not find '.$alias);
                    };
                    $fmtDate = static function ($value) use ($user, $asString) {
                        try {
                            return isset($user) && method_exists($user, 'dateFormat')
                                ? $user->dateFormat($value ?? null)
                                : $asString($value, 'date');
                        } catch (ModelNotFoundException $e) {
                            Log::error('dateFormat model not found', [
                                'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                                'message' => $e->getMessage(),
                            ]);
                            return __('Failed to get date');
                        } catch (QueryException $e) {
                            Log::error('dateFormat query error', [
                                'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                                'message' => $e->getMessage(),
                            ]);
                            return __('Failed to get date');
                        } catch (\TypeError $e) {
                            Log::error('dateFormat type error', [
                                'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                                'message' => $e->getMessage(),
                            ]);
                            return __('Failed to get date');
                        } catch (\Throwable $e) {
                            Log::error('dateFormat error', [
                                'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                                'message' => $e->getMessage(),
                            ]);
                            return __('Failed to get date');
                        }
                    };
                    $fmtPrice = static function ($value) use ($user, $asNumber) {
                        try {
                            return isset($user) && method_exists($user, 'priceFormat')
                                ? $user->priceFormat($value ?? 0)
                                : (is_numeric($value) ? number_format((float) $value, 2, '.', ',')
                                    : $asNumber($value, 'amount'));
                        } catch (ModelNotFoundException $e) {
                            Log::error('priceFormat model not found', [
                                'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                                'message' => $e->getMessage(),
                            ]);
                            return __('Failed to get amount');
                        } catch (QueryException $e) {
                            Log::error('priceFormat query error', [
                                'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                                'message' => $e->getMessage(),
                            ]);
                            return __('Failed to get amount');
                        } catch (\TypeError $e) {
                            Log::error('priceFormat type error', [
                                'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                                'message' => $e->getMessage(),
                            ]);
                            return __('Failed to get amount');
                        } catch (\Throwable $e) {
                            Log::error('priceFormat error', [
                                'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                                'message' => $e->getMessage(),
                            ]);
                            return __('Failed to get amount');
                        }
                    };
                    $fmtInv = static function ($value) use ($user, $asString) {
                        try {
                            return isset($user) && method_exists($user, 'invoiceNumberFormat')
                                ? $user->invoiceNumberFormat($value ?? null)
                                : $asString($value, 'invoice number');
                        } catch (\Throwable $e) {
                            Log::error('invoiceNumberFormat error', [
                                'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                                'message' => $e->getMessage(),
                            ]);
                            return __('Failed to get invoice number');
                        }
                    };
                    $fmtBill = static function ($value) use ($user, $asString) {
                        try {
                            return isset($user) && method_exists($user, 'billNumberFormat')
                                ? $user->billNumberFormat($value ?? null)
                                : $asString($value, 'Bill Identifier');
                        } catch (\Throwable $e) {
                            Log::error('billNumberFormat error', [
                                'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                                'message' => $e->getMessage(),
                            ]);
                            return __('Failed to get Bill Identifier');
                        }
                    };
                    $invoiceStatusClasses ??= [
                        0 => 'bg-secondary', 1 => 'bg-warning', 2 => 'bg-danger',
                        3 => 'bg-info', 4 => 'bg-primary',
                    ];
                    $billStatusClasses ??= $invoiceStatusClasses;
                    try {
                        $metrics = is_iterable($metrics) ? $metrics : [];
                    } catch (\Throwable $e) {
                        Log::error('metrics iteration error', [
                            'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                            'message' => $e->getMessage(),
                        ]);
                        $metrics = [];
                    }
                    try {
                        $bankAccountDetail = is_iterable($bankAccountDetail) ? $bankAccountDetail : [];
                    } catch (\Throwable $e) {
                        Log::error('bankAccountDetail iteration error', [
                            'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                            'message' => $e->getMessage(),
                        ]);
                        $bankAccountDetail = [];
                    }
                    try {
                        $latestIncome = is_iterable($latestIncome) ? $latestIncome : [];
                    } catch (\Throwable $e) {
                        Log::error('latestIncome iteration error', [
                            'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                            'message' => $e->getMessage(),
                        ]);
                        $latestIncome = [];
                    }
                    try {
                        $latestExpense = is_iterable($latestExpense) ? $latestExpense : [];
                    } catch (\Throwable $e) {
                        Log::error('latestExpense iteration error', [
                            'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                            'message' => $e->getMessage(),
                        ]);
                        $latestExpense = [];
                    }
                    try {
                        $recentInvoice = is_iterable($recentInvoice) ? $recentInvoice : [];
                    } catch (\Throwable $e) {
                        Log::error('recentInvoice iteration error', [
                            'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                            'message' => $e->getMessage(),
                        ]);
                        $recentInvoice = [];
                    }
                    try {
                        $recentBill = is_iterable($recentBill) ? $recentBill : [];
                    } catch (\Throwable $e) {
                        Log::error('recentBill iteration error', [
                            'file' => __FILE__, 'line' => __LINE__, 'class' => $e::class,
                            'message' => $e->getMessage(),
                        ]);
                        $recentBill = [];
                    }
@endphp
                <div class="col-xxl-7">
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::CM12 }}">
                            <div class="{{ VC::RW }}">
                                @foreach($metrics as $m)
                                    @php
                                        try {
                                            $bg = $asClass(data_get($m, 'bg'));
                                            $icon = $asClass(data_get($m, 'icon'), 'ti ti-help');
                                            $label = $asString(data_get($m, 'label'), 'label');
                                            $value = $asNumber(data_get($m, 'value'), 'value');
                                        } catch (\Throwable $e) {
                                            \Log::error('dashboard/account_dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <div class="{{ VC::CL3 }} {{ VC::C6 }}">
                                        <div class="{{ VC::CD }}">
                                            <div class="{{ VC::CD_BD }}">
                                                <div class="theme-avatar {{ $bg }}">
                                                    <i class="{{ $icon }}"></i>
                                                </div>
                                                <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} mt-4 mb-2">
                                                    {{ __('Total') }}
                                                </p>
                                                <h6 class="{{ VC::MB3 }}">{{ $label }}</h6>
                                                <h3 class="{{ VC::MB0 }}">{{ $value }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-12">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::CD_HD }}">
                                <h5>
                                    {{ __('Income & Expense') }}
                                    <span class="{{ VC::FEND }} {{ VC::TXT_MT }}">
                                        {{ ($currentYear ?: __('Could not find year')) }}
                                    </span>
                                </h5>
                            </div>
                            <div class="{{ VC::CD_BD }}">
                                <div id="incExpBarChart"></div>
                            </div>
                        </div>
                    </div>

                    <div class="{{ VC::CM12 }}">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::CD_HD }}">
                                <h5 class="{{ VC::MT1 }} {{ VC::MB0 }}">{{ __('Account Balance') }}</h5>
                            </div>
                            <div class="{{ VC::CD_BD }}">
                                <div class="{{ VC::TB_RSP }}">
                                    <table class="{{ VC::TB }}">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Bank') }}</th>
                                                <th>{{ __('Holder Name') }}</th>
                                                <th>{{ __('Balance') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($bankAccountDetail as $account)
                                                @php
                                                    try {
                                                        $bankName = $asString(data_get($account, 'bank_name'), 'bank name');
                                                        $holder = $asString(data_get($account, 'holder_name'), 'holder name');
                                                        $balance = $fmtPrice(data_get($account, 'opening_balance'));
                                                    } catch (\Throwable $e) {
                                                        \Log::error('dashboard/account_dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <tr class="font-style">
                                                    <td>{{ $bankName }}</td>
                                                    <td>{{ $holder }}</td>
                                                    <td>{{ $balance }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4">
                                                        <div class="{{ VC::TXCT }}">
                                                            <h6>{{ __('There is no account balance') }}</h6>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-12">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::CD_HD }}">
                                <h5 class="{{ VC::MT1 }} {{ VC::MB0 }}">{{ __('Latest Income') }}</h5>
                            </div>
                            <div class="{{ VC::CD_BD }}">
                                <div class="{{ VC::TB_RSP }}">
                                    <table class="{{ VC::TB }}">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Customer') }}</th>
                                                <th>{{ __('Amount Due') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($latestIncome as $income)
                                                @php
                                                    try {
                                                        $incDate = $fmtDate(data_get($income, 'date') ?? '01/01/1970');
                                                        $incCust = $asString(data_get($income, 'customer.name') ?? '#NO_NAME',
                                                            'customer name');
                                                        $incAmt = $fmtPrice(data_get($income, 'amount') ?? '9999999999999999999');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('dashboard/account_dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <tr>
                                                    <td>{{ $incDate }}</td>
                                                    <td>{{ $incCust }}</td>
                                                    <td>{{ $incAmt }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4">
                                                        <div class="{{ VC::TXCT }}">
                                                            <h6>{{ __('There is no latest income') }}</h6>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-12">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::CD_HD }}">
                                <h5 class="{{ VC::MT1 }} {{ VC::MB0 }}">{{ __('Latest Expense') }}</h5>
                            </div>
                            <div class="{{ VC::CD_BD }}">
                                <div class="{{ VC::TB_RSP }}">
                                    <table class="{{ VC::TB }}">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Vendor') }}</th>
                                                <th>{{ __('Amount Due') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($latestExpense as $expense)
                                                @php
                                                    try {
                                                        $expDate = $fmtDate(data_get($expense, 'date'));
                                                        $expVend = $asString(data_get($expense, 'vendor.name'),
                                                            'vendor name');
                                                        $expAmt = $fmtPrice(data_get($expense, 'amount'));
                                                    } catch (\Throwable $e) {
                                                        \Log::error('dashboard/account_dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <tr>
                                                    <td>{{ $expDate }}</td>
                                                    <td>{{ $expVend }}</td>
                                                    <td>{{ $expAmt }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4">
                                                        <div class="{{ VC::TXCT }}">
                                                            <h6>{{ __('There is no latest expense') }}</h6>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-12">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::CD_HD }}">
                                <h5 class="{{ VC::MT1 }} {{ VC::MB0 }}">{{ __('Recent Invoices') }}</h5>
                            </div>
                            <div class="{{ VC::CD_BD }}">
                                <div class="{{ VC::TB_RSP }}">
                                    <table class="{{ VC::TB }}">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('Customer') }}</th>
                                                <th>{{ __('Issue Date') }}</th>
                                                <th>{{ __('Due Date') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                <th>{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentInvoice as $invoice)
                                                @php
                                                    $invNo = '—'; $invCust = '—'; $invIssue = '—'; $invDue = '—'; $invTotal = '—'; $bgClass = null; $stText = '—';
                                                    try {
                                                        $invNo = $fmtInv(data_get($invoice, 'invoice_id'));
                                                        $invCust = $asString(data_get($invoice, 'customer.name'),
                                                            'customer name');
                                                        $invIssue = $fmtDate(data_get($invoice, 'issue_date'));
                                                        $invDue = $fmtDate(data_get($invoice, 'due_date'));
                                                        $invTotal = $fmtPrice(
                                                            method_exists($invoice, 'getTotal')
                                                                ? $invoice->getTotal()
                                                                : data_get($invoice, 'total')
                                                        );
                                                        $stIdx = data_get($invoice, 'status');
                                                        $bgClass = $invoiceStatusClasses[$stIdx] ?? null;
                                                        $stText = is_array(Invoice::$statuses ?? null)
                                                            ? data_get(Invoice::$statuses, $stIdx)
                                                            : null;
                                                        $stText = $asString($stText, 'status');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('dashboard/account_dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <tr>
                                                    <td>{{ $invNo }}</td>
                                                    <td>{{ $invCust }}</td>
                                                    <td>{{ $invIssue }}</td>
                                                    <td>{{ $invDue }}</td>
                                                    <td>{{ $invTotal }}</td>
                                                    <td>
                                                        @if($bgClass)
                                                            <span class="p-2 px-3 rounded {{ VC::BDG }} {{ $bgClass }}">
                                                                {{ __($stText) }}
                                                            </span>
                                                        @else
                                                            <span class="p-2 px-3 rounded {{ VC::BDG }} bg-secondary">
                                                                {{ __('No status available') }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6">
                                                        <div class="{{ VC::TXCT }}">
                                                            <h6>{{ __('There is no recent invoice') }}</h6>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-12">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::CD_HD }}">
                                <h5 class="{{ VC::MT1 }} {{ VC::MB0 }}">{{ __('Recent Bills') }}</h5>
                            </div>
                            <div class="{{ VC::CD_BD }}">
                                <div class="{{ VC::TB_RSP }}">
                                    <table class="{{ VC::TB }}">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('Vendor') }}</th>
                                                <th>{{ __('Bill Date') }}</th>
                                                <th>{{ __('Due Date') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                <th>{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentBill as $bill)
                                                @php
                                                    try {
                                                        $blNo = $fmtBill(data_get($bill, 'bill_id'));
                                                        $blVend = $asString(data_get($bill, 'vendor.name'),
                                                            'vendor name');
                                                        $blDate = $fmtDate(data_get($bill, 'bill_date'));
                                                        $blDue = $fmtDate(data_get($bill, 'due_date'));
                                                        $blTotal = $fmtPrice(
                                                            method_exists($bill, 'getTotal')
                                                                ? $bill->getTotal()
                                                                : data_get($bill, 'total')
                                                        );
                                                        $blIdx = data_get($bill, 'status');
                                                        $bgClass = $billStatusClasses[$blIdx] ?? null;
                                                        $blText = is_array(Bill::$statuses ?? null)
                                                            ? data_get(Bill::$statuses, $blIdx)
                                                            : null;
                                                        $blText = $asString($blText, 'status');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('dashboard/account_dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <tr>
                                                    <td>{{ $blNo }}</td>
                                                    <td>{{ $blVend }}</td>
                                                    <td>{{ $blDate }}</td>
                                                    <td>{{ $blDue }}</td>
                                                    <td>{{ $blTotal }}</td>
                                                    <td>
                                                        @if($bgClass)
                                                            <span class="p-2 px-3 rounded {{ VC::BDG }} {{ $bgClass }}">
                                                                {{ __($blText) }}
                                                            </span>
                                                        @else
                                                            <span class="p-2 px-3 rounded {{ VC::BDG }} bg-secondary">
                                                                {{ __('No status available') }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6">
                                                        <div class="{{ VC::TXCT }}">
                                                            <h6>{{ __('There is no recent bill') }}</h6>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @php
                    $tiles ??= [];
                    $weeklyInvoice ??= [];
                    $monthlyInvoice ??= [];
                    $goals ??= [];
                    $storage ??= __('Could not find storage limits');
                    $decimalNumber ??= 2;
                    try {
                        $decimalNumber = (int) (Utility::getValByName('decimal_number') ?? 2);
                    } catch (ModelNotFoundException $e) {
                        Log::error('decimal number setting model not found', ['file'=>__FILE__, 'line'=>__LINE__, 'class'=>$e::class, 'message'=>$e->getMessage()]);
                    } catch (QueryException $e) {
                        Log::error('decimal number setting query error', ['file'=>__FILE__, 'line'=>__LINE__, 'class'=>$e::class, 'message'=>$e->getMessage()]);
                    } catch (\TypeError $e) {
                        Log::error('decimal number setting type error', ['file'=>__FILE__, 'line'=>__LINE__, 'class'=>$e::class, 'message'=>$e->getMessage()]);
                    } catch (\Throwable $e) {
                        Log::error('decimal number setting error', ['file'=>__FILE__, 'line'=>__LINE__, 'class'=>$e::class, 'message'=>$e->getMessage()]);
                    }

                    try {
                        $incomeToday = (isset($user) && method_exists($user, 'todayIncome')) ? $user->todayIncome() : null;
                        $expenseToday = (isset($user) && method_exists($user, 'todayExpense')) ? $user->todayExpense() : null;
                        $incomeMonth = (isset($user) && method_exists($user, 'incomeCurrentMonth')) ? $user->incomeCurrentMonth() : null;
                        $expenseMonth = (isset($user) && method_exists($user, 'expenseCurrentMonth')) ? $user->expenseCurrentMonth() : null;

                        $tiles = [
                            ['label'=>__('Income Today'),'value'=>$fmtPrice($incomeToday),'avatarBg'=>'bg-primary','icon'=>'ti-report-money','textClass'=>'text-success'],
                            ['label'=>__('Expense Today'),'value'=>$fmtPrice($expenseToday),'avatarBg'=>'bg-info','icon'=>'ti-file-invoice','textClass'=>'text-info'],
                            ['label'=>__('Income This Month'),'value'=>$fmtPrice($incomeMonth),'avatarBg'=>'bg-warning','icon'=>'ti-report-money','textClass'=>'text-warning'],
                            ['label'=>__('Expense This Month'),'value'=>$fmtPrice($expenseMonth),'avatarBg'=>'bg-danger','icon'=>'ti-file-invoice','textClass'=>'text-danger'],
                        ];
                    } catch (\Error $e) {
                        Log::error('tiles build fatal error', ['file'=>__FILE__, 'line'=>__LINE__, 'class'=>$e::class, 'message'=>$e->getMessage()]);
                        $tiles = [];
                    } catch (\Throwable $e) {
                        Log::error('tiles build error', ['file'=>__FILE__, 'line'=>__LINE__, 'class'=>$e::class, 'message'=>$e->getMessage()]);
                        $tiles = [];
                    }

                    try {
                        $weeklyInvoice = Utility::isFilled($weeklyInvoice ?? []) ? $weeklyInvoice : [];
                        $monthlyInvoice = Utility::isFilled($monthlyInvoice ?? []) ? $monthlyInvoice : [];
                    } catch (\Throwable $e) {
                        Log::error('invoice stats validation error', ['file'=>__FILE__, 'line'=>__LINE__, 'class'=>$e::class, 'message'=>$e->getMessage()]);
                        $weeklyInvoice = [];
                        $monthlyInvoice = [];
                    }

                    try {
                        if (($user ?? null) instanceof User && ($plan ?? null) instanceof Plan && isset($user->storage_limit, $plan->storage_limit)) {
                            $storage = (string) $user->storage_limit.'MB / '.(string) $plan->storage_limit.'MB';
                        } else {
                            $storage = __('Max').' '.(string) SettingsConstants::MAX_SL_LIMIT_MB.'MB';
                        }
                    } catch (\Throwable $e) {
                        Log::error('storage string build error', ['file'=>__FILE__, 'line'=>__LINE__, 'class'=>$e::class, 'message'=>$e->getMessage()]);
                        $storage = __('Could not find storage limits');
                    }
@endphp
                <div class="col-xxl-5">
                    <div class="row">
                        <div class="{{ VC::C12 }}">
                            <div class="{{ VC::CD }}">
                                <div class="{{ VC::CD_HD }}">
                                    <h5 class="{{ VC::MT1 }} {{ VC::MB0 }}">{{ __('Cashflow') }}</h5>
                                </div>
                                <div class="{{ VC::CD_BD }}">
                                    <div id="cash-flow"></div>
                                </div>
                            </div>

                            <div class="{{ VC::CD }}">
                                <div class="{{ VC::CD_HD }}">
                                    <h5 class="{{ VC::MT1 }} {{ VC::MB0 }}">{{ __('Income Vs Expense') }}</h5>
                                </div>
                                <div class="{{ VC::CD_BD }}">
                                    <div class="{{ VC::RW }}">
                                        @foreach($tiles as $tile)
                                            @php
                                                try {
                                                    $tLbl = $asString(data_get($tile, 'label'), 'label');
                                                    $tVal = $asString(data_get($tile, 'value'), 'amount');
                                                    $tBg = $asClass(data_get($tile, 'avatarBg'), 'bg-secondary');
                                                    $tIc = $asClass(data_get($tile, 'icon'), 'ti-help');
                                                    $tTx = $asClass(data_get($tile, 'textClass'), 'text-muted');
                                                } catch (\Throwable $e) {
                                                    \Log::error('dashboard/account_dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <div class="{{ VC::CM6 }} {{ VC::C6 }} {{ VC::MY2 }}">
                                                <div class="{{ VC::DFL }} align-items-start mb-2">
                                                    <div class="theme-avatar {{ $tBg }}">
                                                        <i class="ti {{ $tIc }}"></i>
                                                    </div>
                                                    <div class="{{ VC::MS2 }}">
                                                        <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ $tLbl }}</p>
                                                        <h4 class="{{ VC::MB0 }} {{ $tTx }}">{{ $tVal }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-12">
                            <div class="{{ VC::CD }}">
                                <div class="{{ VC::CD_HD }}">
                                    <h5>{{ __('Storage Limit') }}<small class="{{ VC::FEND }} {{ VC::TXT_MT }}">{{ $asString($storage, 'storage limits') }}</small></h5>
                                </div>
                                <div class="{{ VC::CD_BD }}">
                                    <div id="limit-chart"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-12">
                            <div class="{{ VC::CD }}">
                                <div class="{{ VC::CD_HD }}">
                                    <h5>{{ __('Income By Category') }}<span class="{{ VC::FEND }} {{ VC::TXT_MT }}">{{ ($currentYear ?? null) ? __('Year').' - '.$currentYear : __('Could not find year') }}</span></h5>
                                </div>
                                <div class="{{ VC::CD_BD }}">
                                    <div id="incomeByCategory"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-12">
                            <div class="{{ VC::CD }}">
                                <div class="{{ VC::CD_HD }}">
                                    <h5>{{ __('Expense By Category') }}<span class="{{ VC::FEND }} {{ VC::TXT_MT }}">{{ ($currentYear ?? null) ? __('Year').' - '.$currentYear : __('Could not find year') }}</span></h5>
                                </div>
                                <div class="{{ VC::CD_BD }}">
                                    <div id="expenseByCategory"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-12">
                            <div class="{{ VC::CD }}">
                                <div class="{{ VC::CD_BD }}">
                                    @php
                                        try {
                                            $wTotal = $fmtPrice(data_get($weeklyInvoice, 'invoiceTotal'));
                                            $wPaid = $fmtPrice(data_get($weeklyInvoice, 'invoicePaid'));
                                            $wDue = $fmtPrice(data_get($weeklyInvoice, 'invoiceDue'));
                                            $mTotal = $fmtPrice(data_get($monthlyInvoice, 'invoiceTotal'));
                                            $mPaid = $fmtPrice(data_get($monthlyInvoice, 'invoicePaid'));
                                            $mDue = $fmtPrice(data_get($monthlyInvoice, 'invoiceDue'));
                                        } catch (\Throwable $e) {
                                            \Log::error('dashboard/account_dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <ul class="{{ VC::NAV_PL }} {{ VC::MB5 }}" id="pills-tab" role="tablist">
                                        <li class="{{ VC::NV_IT }}">
                                            <a class="{{ VC::NV_LK }} active" id="pills-home-tab" data-bs-toggle="pill" href="#invoice_weekly_statistics" role="tab">{{ __('Invoices Weekly Statistics') }}</a>
                                        </li>
                                        <li class="{{ VC::NV_IT }}">
                                            <a class="{{ VC::NV_LK }}" id="pills-profile-tab" data-bs-toggle="pill" href="#invoice_monthly_statistics" role="tab">{{ __('Invoices Monthly Statistics') }}</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="pills-tabContent">
                                        <div class="{{ VC::TAB_FD_SH }} active" id="invoice_weekly_statistics" role="tabpanel">
                                            <div class="{{ VC::TB_RSP }}">
                                                <table class="{{ VC::TB_AL }} {{ VC::MB0 }}">
                                                    <tbody class="list">
                                                        <tr>
                                                            <td>
                                                                <h5 class="{{ VC::MB0 }}">{{ __('Total') }}</h5>
                                                                <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Invoice Generated') }}</p>
                                                            </td>
                                                            <td><h4 class="{{ VC::TXT_MT }}">{{ $wTotal }}</h4></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <h5 class="{{ VC::MB0 }}">{{ __('Total') }}</h5>
                                                                <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Paid') }}</p>
                                                            </td>
                                                            <td><h4 class="{{ VC::TXT_MT }}">{{ $wPaid }}</h4></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <h5 class="{{ VC::MB0 }}">{{ __('Total') }}</h5>
                                                                <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Due') }}</p>
                                                            </td>
                                                            <td><h4 class="{{ VC::TXT_MT }}">{{ $wDue }}</h4></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="invoice_monthly_statistics" role="tabpanel">
                                            <div class="{{ VC::TB_RSP }}">
                                                <table class="{{ VC::TB_AL }} {{ VC::MB0 }}">
                                                    <tbody class="list">
                                                        <tr>
                                                            <td>
                                                                <h5 class="{{ VC::MB0 }}">{{ __('Total') }}</h5>
                                                                <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Invoice Generated') }}</p>
                                                            </td>
                                                            <td><h4 class="{{ VC::TXT_MT }}">{{ $mTotal }}</h4></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <h5 class="{{ VC::MB0 }}">{{ __('Total') }}</h5>
                                                                <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Paid') }}</p>
                                                            </td>
                                                            <td><h4 class="{{ VC::TXT_MT }}">{{ $mPaid }}</h4></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <h5 class="{{ VC::MB0 }}">{{ __('Total') }}</h5>
                                                                <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Due') }}</p>
                                                            </td>
                                                            <td><h4 class="{{ VC::TXT_MT }}">{{ $mDue }}</h4></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-12">
                            <div class="{{ VC::CD }}">
                                <div class="{{ VC::CD_HD }}">
                                    <h5 class="{{ VC::MT1 }} {{ VC::MB0 }}">{{ __('Goal') }}</h5>
                                </div>
                                <div class="{{ VC::CD_BD }}">
                                    @forelse($goals as $goal)
                                        @php
                                            try {
                                                $results = method_exists($goal, 'target') ? $goal->target(data_get($goal, 'type'), data_get($goal, 'from'), data_get($goal, 'to'), data_get($goal, 'amount')) : ['total'=>0,'percentage'=>0];
                                                $total = (float) data_get($results, 'total', 0);
                                                $percentage = (float) data_get($results, 'percentage', 0);
                                                $per = number_format($percentage, $decimalNumber, '.', '');
                                            } catch (ModelNotFoundException $e) {
                                                Log::error('goal target model not found', ['file'=>__FILE__, 'line'=>__LINE__, 'class'=>$e::class, 'message'=>$e->getMessage()]);
                                                $total = 0;
                                                $per = number_format(0, $decimalNumber, '.', '');
                                            } catch (QueryException $e) {
                                                Log::error('goal target query error', ['file'=>__FILE__, 'line'=>__LINE__, 'class'=>$e::class, 'message'=>$e->getMessage()]);
                                                $total = 0;
                                                $per = number_format(0, $decimalNumber, '.', '');
                                            } catch (\TypeError $e) {
                                                Log::error('goal target type error', ['file'=>__FILE__, 'line'=>__LINE__, 'class'=>$e::class, 'message'=>$e->getMessage()]);
                                                $total = 0;
                                                $per = number_format(0, $decimalNumber, '.', '');
                                            } catch (\Throwable $e) {
                                                Log::error('goal target error', ['file'=>__FILE__, 'line'=>__LINE__, 'class'=>$e::class, 'message'=>$e->getMessage()]);
                                                $total = 0;
                                                $per = number_format(0, $decimalNumber, '.', '');
                                            }

                                            $gName = $asString(data_get($goal, 'name'), 'goal name');
                                            $gTypeIdx = data_get($goal, 'type');
                                            $gTypeLabel = is_string($gTypeIdx) ? ucfirst($gTypeIdx) : null;
                                            $gType = $asString($gTypeLabel, 'type');
                                            $gFrom = $asString(data_get($goal, 'from'), 'start date');
                                            $gTo = $asString(data_get($goal, 'to'), 'end date');
                                            $gAmount = $fmtPrice(data_get($goal, 'amount'));
                                            $gTotal = $fmtPrice($total);
                                            $perFloat = (float) $per;
@endphp
                                        <div class="{{ VC::CD }} border-success border-2 border-bottom-0 border-start-0 border-end-0">
                                            <div class="{{ VC::CD_BD }}">
                                                <div class="{{ VC::FM_CHK }}">
                                                    <label class="{{ VC::DBL }}" for="goal-{{ (string) data_get($goal, 'id', 'x') }}">
                                                        <span>
                                                            <span class="{{ VC::R_ALC }}">
                                                                <span class="col">
                                                                    <span class="{{ VC::TXT_MT }} {{ VC::TXSM }}">{{ __('Name') }}</span>
                                                                    <h6 class="text-nowrap {{ VC::MB3 }} mb-sm-0">{{ $gName }}</h6>
                                                                </span>
                                                                <span class="col">
                                                                    <span class="{{ VC::TXT_MT }} {{ VC::TXSM }}">{{ __('Type') }}</span>
                                                                    <h6 class="{{ VC::MB3 }} mb-sm-0">{{ __($gType) }}</h6>
                                                                </span>
                                                                <span class="col">
                                                                    <span class="{{ VC::TXT_MT }} {{ VC::TXSM }}">{{ __('Duration') }}</span>
                                                                    <h6 class="{{ VC::MB3 }} mb-sm-0">{{ $gFrom.' '.__('To').' '.$gTo }}</h6>
                                                                </span>
                                                                <span class="col">
                                                                    <span class="{{ VC::TXT_MT }} {{ VC::TXSM }}">{{ __('Target') }}</span>
                                                                    <h6 class="{{ VC::MB3 }} mb-sm-0">{{ $gTotal.' '.__('of').' '.$gAmount }}</h6>
                                                                </span>
                                                                <span class="col">
                                                                    <span class="{{ VC::TXT_MT }} {{ VC::TXSM }}">{{ __('Progress') }}</span>
                                                                    <h6 class="{{ VC::MB0 }}">{{ $per }}%</h6>
                                                                    <div class="{{ VC::PG }} {{ VC::MB0 }}">
                                                                        @if($perFloat <= 33)
                                                                            <div class="progress-bar bg-danger" style="width: {{ $per }}%"></div>
                                                                        @elseif($perFloat <= 66)
                                                                            <div class="progress-bar bg-warning" style="width: {{ $per }}%"></div>
                                                                        @else
                                                                            <div class="progress-bar {{ VC::BG_P }}" style="width: {{ $per }}%"></div>
                                                                        @endif
                                                                    </div>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="{{ VC::CD }} pb-0">
                                            <div class="{{ VC::CD_BD }} {{ VC::TXCT }}">
                                                <h6>{{ __('There is no goal.') }}</h6>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @php
                    $goals ??= [];
                    $decimalNumber ??= 2;
@endphp
                <div class="col-xxl-12">
                    <div class="{{ VC::CD }}">
                        <div class="{{ VC::CD_HD }}">
                            <h5>{{ __('Goal') }}</h5>
                        </div>
                        <div class="{{ VC::CD_BD }}">
                            @forelse($goals as $goal)
                                @php
                                    try {
                                        $results = method_exists($goal, 'target') ? $goal->target(data_get($goal, 'type'), data_get($goal, 'from'), data_get($goal, 'to'), data_get($goal, 'amount')) : ['total' => 0, 'percentage' => 0];
                                        $total = (float) data_get($results, 'total', 0);
                                        $percentage = (float) data_get($results, 'percentage', 0);
                                    } catch (ModelNotFoundException $e) {
                                        Log::error('goal target model not found', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                        $total = 0;
                                        $percentage = 0;
                                    } catch (QueryException $e) {
                                        Log::error('goal target query error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                        $total = 0;
                                        $percentage = 0;
                                    } catch (\TypeError $e) {
                                        Log::error('goal target type error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                        $total = 0;
                                        $percentage = 0;
                                    } catch (\Throwable $e) {
                                        Log::error('goal target error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                        $total = 0;
                                        $percentage = 0;
                                    }

                                    try {
                                        $decimals = (int) ($decimalNumber ?? Utility::getValByName('decimal_number') ?? 2);
                                    } catch (ModelNotFoundException $e) {
                                        Log::error('decimal number model not found', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                        $decimals = 2;
                                    } catch (QueryException $e) {
                                        Log::error('decimal number query error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                        $decimals = 2;
                                    } catch (\Throwable $e) {
                                        Log::error('decimal number error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                        $decimals = 2;
                                    }

                                    $per = number_format($percentage, $decimals, '.', '');
                                    $gName = $asString(data_get($goal, 'name'), 'goal name');
                                    $gTypeIdx = data_get($goal, 'type');
                                    $gTypeMap = Goal::$goalType ?? [];
                                    $gType = $asString(data_get($gTypeMap, $gTypeIdx), 'type');
                                    $gFrom = $asString(data_get($goal, 'from'), 'start date');
                                    $gTo = $asString(data_get($goal, 'to'), 'end date');
                                    $gTotal = $fmtPrice($total);
                                    $gAmount = $fmtPrice(data_get($goal, 'amount'));
                                    $perFloat = (float) $per;
@endphp
                                <div class="{{ VC::CD }} border-success border-2 border-bottom-0 border-start-0 border-end-0">
                                    <div class="{{ VC::CD_BD }}">
                                        <div class="{{ VC::FM_CHK }}">
                                            <label class="form-check-label {{ VC::DBL }}" for="goal-{{ (string) data_get($goal, 'id', 'x') }}">
                                                <span>
                                                    <span class="{{ VC::R_ALC }}">
                                                        <span class="col">
                                                            <span class="{{ VC::TXT_MT }} {{ VC::TXSM }}">{{ __('Name') }}</span>
                                                            <h6 class="text-nowrap {{ VC::MB3 }} mb-sm-0">{{ $gName }}</h6>
                                                        </span>
                                                        <span class="col">
                                                            <span class="{{ VC::TXT_MT }} {{ VC::TXSM }}">{{ __('Type') }}</span>
                                                            <h6 class="{{ VC::MB3 }} mb-sm-0">{{ __($gType) }}</h6>
                                                        </span>
                                                        <span class="col">
                                                            <span class="{{ VC::TXT_MT }} {{ VC::TXSM }}">{{ __('Duration') }}</span>
                                                            <h6 class="{{ VC::MB3 }} mb-sm-0">{{ $gFrom.' '.__('To').' '.$gTo }}</h6>
                                                        </span>
                                                        <span class="col">
                                                            <span class="{{ VC::TXT_MT }} {{ VC::TXSM }}">{{ __('Target') }}</span>
                                                            <h6 class="{{ VC::MB3 }} mb-sm-0">{{ $gTotal.' '.__('of').' '.$gAmount }}</h6>
                                                        </span>
                                                        <span class="col">
                                                            <span class="{{ VC::TXT_MT }} {{ VC::TXSM }}">{{ __('Progress') }}</span>
                                                            <h6 class="{{ VC::MB0 }} {{ VC::DBL }}">{{ $per }}%</h6>
                                                            <div class="{{ VC::PG }} {{ VC::MB0 }}">
                                                                @if($perFloat <= 33)
                                                                    <div class="progress-bar bg-danger" style="width: {{ $per }}%"></div>
                                                                @elseif($perFloat <= 66)
                                                                    <div class="progress-bar bg-warning" style="width: {{ $per }}%"></div>
                                                                @else
                                                                    <div class="progress-bar {{ VC::BG_P }}" style="width: {{ $per }}%"></div>
                                                                @endif
                                                            </div>
                                                        </span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="{{ VC::CD }} pb-0">
                                    <div class="{{ VC::CD_BD }} {{ VC::TXCT }}">
                                        <h6>{{ __('There is no goal.') }}</h6>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
