@php
    use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,
        PermissionsConstants, 
        ProjectsConstants,
        SettingsConstants,
        StacksConstants,
        UsersConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\{Bill, Invoice, Plan, Utility};
    use Illuminate\Support\Facades\{Auth, Log, Route};
    $user ??= Auth::user();
    $lang ??= Utility::fetchUserLang(user:$user);
    $plan ??= Plan::find(DatabaseConstants::DEFAULT_PLAN);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Dashboard')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    @if($user?->can(PermissionsConstants::SHW_ACC_DSB) || $user[UsersConstants::COL_TP] == PermissionsConstants::SA)
        @php
            if (!$user?->can(PermissionsConstants::SHW_ACC_DSB) && $user[UsersConstants::COL_TP] == PermissionsConstants::SA) 
                Log::notice(
                    'User is a super admin bypassing. Be sure this is intended or report.',
                    [
                        'user_id' => $user->id,
                        'user_type' => $user[UsersConstants::COL_TP],
                        'permission' => PermissionsConstants::SHW_ACC_DSB
                    ]
                );
        @endphp
            <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            en: {
                cash_flow_unavailable: "Failed to load cash‑flow chart.",
                incExpBarChart_unavailable: "Failed to load income/expense bar chart.",
                expenseByCategory_unavailable: "Failed to load expense‑by‑category chart.",
                incomeByCategory_unavailable: "Failed to load income‑by‑category chart.",
                limitChart_unavailable: "Failed to load storage‑limit chart."
            },
            pt: {
                cash_flow_unavailable: "Falha ao carregar o gráfico de fluxo de caixa.",
                incExpBarChart_unavailable: "Falha ao carregar o gráfico de entradas/saídas.",
                expenseByCategory_unavailable: "Falha ao carregar o gráfico de despesas por categoria.",
                incomeByCategory_unavailable: "Falha ao carregar o gráfico de receitas por categoria.",
                limitChart_unavailable: "Falha ao carregar o gráfico de limite de armazenamento."
            },
            "pt-br": {
                cash_flow_unavailable: "Falha ao carregar o gráfico de fluxo de caixa.",
                incExpBarChart_unavailable: "Falha ao carregar o gráfico de entradas/saídas.",
                expenseByCategory_unavailable: "Falha ao carregar o gráfico de despesas por categoria.",
                incomeByCategory_unavailable: "Falha ao carregar o gráfico de receitas por categoria.",
                limitChart_unavailable: "Falha ao carregar o gráfico de limite de armazenamento."
            }
            };
Object.keys(t).forEach(
  k =>
    (window.translations[k] = {
      ...(window.translations[k] || {}),
      ...t[k],
    })
);
         
          })();
    </script>
        <script defer>
            (() => {
                const errFb = "# ERROR";
                const dataClientLocalized = "data-client-localized";
                const dataGuardMsg = "data-guard-msg";
                
                const getLocalizedMessage = (el, key) => {
                    let msg = errFb;
                    if (
                    el.getAttribute("data-sv-localized") === "true" ||
                    el.getAttribute(dataClientLocalized) === "true"
                    ) {
                    msg = el.getAttribute(dataGuardMsg) ?? errFb;
                    } else {
                    let lang = (
                        window.sessionStorage.getItem("erp-np-lang") ||
                        document.documentElement.lang ||
                        "en"
                    )
                        .toLowerCase()
                        .replace(/_/g, "-");
                    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                    msg =
                        window.translations?.[lang]?.[key] ??
                        el.getAttribute(dataGuardMsg) ??
                        window.translations?.["en"]?.[key] ??
                        errFb;
                    if (msg !== errFb) {
                        el.setAttribute(dataGuardMsg, msg);
                        el.setAttribute(dataClientLocalized, "true");
                    }
                    }
                    return msg;
                };
                
                const showErrorUI = msg => {
                    const bsLink = document.querySelector('link[href*="bootstrap"]');
                    if (bsLink && window.bootstrap?.Toast) {
                    if (!document.querySelector("#fail-toast")) {
                        const toast = document.createElement("div");
                        toast.id = "fail-toast";
                        toast.className =
                        "toast align-items-center text-white bg-danger border-0 position-fixed bottom-0 end-0 m-3";
                        toast.setAttribute("role", "alert");
                        toast.setAttribute("aria-live", "assertive");
                        toast.setAttribute("aria-atomic", "true");
                        toast.innerHTML = `
                        <div class="d-flex">
                            <div class="toast-body">${msg}</div>
                            <button
                            type="button"
                            class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast"
                            aria-label="Close"
                            ></button>
                        </div>`;
                        document.body.append(toast);
                        new bootstrap.Toast(toast).show();
                    }
                    } else {
                    console.error(msg);
                    }
                };
                
                const renderCashFlow = () => {
                    try {
                    const el = document.querySelector("#cash-flow");
                    if (!el || !window.ApexCharts) throw new Error();
                    const options = {
                        series: [
                        { name: "{{__('Income')}}", data: {!! json_encode($incExpLineChartData['income']) !!} },
                        { name: "{{__('Expense')}}", data: {!! json_encode($incExpLineChartData['expense']) !!} }
                        ],
                        chart: {
                        height: 250,
                        type: "area",
                        dropShadow: { enabled: true, color: "#000", top: 18, left: 7, blur: 10, opacity: 0.2 },
                        toolbar: { show: false }
                        },
                        dataLabels: { enabled: false },
                        stroke: { width: 2, curve: "smooth" },
                        title: { text: "", align: "left" },
                        xaxis: { categories: {!! json_encode($incExpLineChartData['day']) !!}, title: { text: "{{ __('Date') }}" } },
                        colors: ["#6fd944", "#ff3a6e"],
                        grid: { strokeDashArray: 4 },
                        legend: { show: false },
                        yaxis: { title: { text: "{{ __('Amount') }}" } }
                    };
                    new ApexCharts(el, options).render();
                    } catch {
                    showErrorUI(getLocalizedMessage(document.body, "cash_flow_unavailable"));
                    }
                };
                
                const renderIncExpBar = () => {
                    try {
                    const el = document.querySelector("#incExpBarChart");
                    if (!el || !window.ApexCharts) throw new Error();
                    const options = {
                        chart: { height: 180, type: "bar", toolbar: { show: false } },
                        dataLabels: { enabled: false },
                        stroke: { width: 2, curve: "smooth" },
                        series: [
                        { name: "{{__('Income')}}", data: {!! json_encode($incExpBarChartData['income']) !!} },
                        { name: "{{__('Expense')}}", data: {!! json_encode($incExpBarChartData['expense']) !!} }
                        ],
                        xaxis: { categories: {!! json_encode($incExpBarChartData['month']) !!} },
                        colors: ["#3ec9d6", "#FF3A6E"],
                        fill: { type: "solid" },
                        grid: { strokeDashArray: 4 },
                        legend: { show: true, position: "top", horizontalAlign: "right" }
                    };
                    new ApexCharts(el, options).render();
                    } catch {
                    showErrorUI(getLocalizedMessage(document.body, "incExpBarChart_unavailable"));
                    }
                };
                
                const renderExpenseByCategory = () => {
                    try {
                    const el = document.querySelector("#expenseByCategory");
                    if (!el || !window.ApexCharts) throw new Error();
                    const options = {
                        chart: { height: 140, type: "donut" },
                        dataLabels: { enabled: false },
                        plotOptions: { pie: { donut: { size: "70%" } } },
                        series: {!! json_encode($expenseCatAmount) !!},
                        colors: {!! json_encode($expenseCategoryColor) !!},
                        labels: {!! json_encode($expenseCategory) !!},
                        legend: { show: true }
                    };
                    new ApexCharts(el, options).render();
                    } catch {
                    showErrorUI(getLocalizedMessage(document.body, "expenseByCategory_unavailable"));
                    }
                };
                
                const renderIncomeByCategory = () => {
                    try {
                    const el = document.querySelector("#incomeByCategory");
                    if (!el || !window.ApexCharts) throw new Error();
                    const options = {
                        chart: { height: 140, type: "donut" },
                        dataLabels: { enabled: false },
                        plotOptions: { pie: { donut: { size: "70%" } } },
                        series: {!! json_encode($incomeCatAmount) !!},
                        colors: {!! json_encode($incomeCategoryColor) !!},
                        labels: {!! json_encode($incomeCategory) !!},
                        legend: { show: true }
                    };
                    new ApexCharts(el, options).render();
                    } catch {
                    showErrorUI(getLocalizedMessage(document.body, "incomeByCategory_unavailable"));
                    }
                };
                
                const renderLimitChart = () => {
                    try {
                    const el = document.querySelector("#limit-chart");
                    if (!el || !window.ApexCharts) throw new Error();
                    const options = {
                        series: [{{ round($storage_limit,2) }}],
                        chart: { height: 350, type: "radialBar", offsetY: -20, sparkline: { enabled: true } },
                        plotOptions: {
                        radialBar: {
                            startAngle: -90,
                            endAngle: 90,
                            track: { background: "#e7e7e7", strokeWidth: "97%", margin: 5 },
                            dataLabels: { name: { show: true }, value: { offsetY: -50, fontSize: "20px" } }
                        }
                        },
                        grid: { padding: { top: -10 } },
                        colors: ["#6FD943"],
                        labels: ["Used"]
                    };
                    new ApexCharts(el, options).render();
                    } catch {
                    showErrorUI(getLocalizedMessage(document.body, "limitChart_unavailable"));
                    }
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
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Account')}}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                @php
                    $metrics=[
                        ['bg'=>'bg-primary','icon'=>ViewClassNamesConstants::TI_USRS,'label'=>__('Customers'),'value'=>$user->countCustomers()],
                        ['bg'=>'bg-info','icon'=>ViewClassNamesConstants::TI_USRS,'label'=>__('Vendors'),'value'=>$user->countVendors()],
                        ['bg'=>'bg-warning','icon'=>'ti ti-report-money','label'=>__('Invoices'),'value'=>$user->countInvoices()],
                        ['bg'=>'bg-danger','icon'=>'ti ti-report-money','label'=>__('Bills'),'value'=>$user->countBills()]
                    ];
                @endphp
                <div class="col-xxl-7">
                    <div class="{{ ViewClassNamesConstants::RW }}">
                        <div class="col-md-12">
                            <div class="{{ ViewClassNamesConstants::RW }}">
                                @foreach($metrics as $m)
                                    <div class="col-lg-3 col-6">
                                        <div class="{{ ViewClassNamesConstants::CD }}">
                                            <div class="card-body">
                                                <div class="theme-avatar {{ $m['bg'] }}">
                                                    <i class="{{ $m['icon'] }}"></i>
                                                </div>
                                                <p class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }} mt-4 mb-2">{{ __('Total') }}</p>
                                                <h6 class="{{ ViewClassNamesConstants::MB3 }}">{{ $m['label'] }}</h6>
                                                <h3 class="{{ ViewClassNamesConstants::MB0 }}">{{ $m['value'] }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-12">
                        <div class="{{ ViewClassNamesConstants::CD }}">
                            <div class="card-header">
                                <h5>{{ __('Income & Expense') }}
                                    <span class="{{ ViewClassNamesConstants::FEND }} {{ ViewClassNamesConstants::TXT_MT }}">{{ __('Current Year').' - '.$currentYear }}</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="incExpBarChart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="{{ ViewClassNamesConstants::CD }}">
                            <div class="card-header"><h5 class="{{ ViewClassNamesConstants::MT1 }} {{ ViewClassNamesConstants::MB0 }}">{{ __('Account Balance') }}</h5></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="{{ ViewClassNamesConstants::TB }}">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Bank') }}</th>
                                                <th>{{ __('Holder Name') }}</th>
                                                <th>{{ __('Balance') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($bankAccountDetail as $account)
                                                <tr class="font-style">
                                                    <td>{{ $account->bank_name }}</td>
                                                    <td>{{ $account->holder_name }}</td>
                                                    <td>{{ $user->priceFormat($account->opening_balance) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4">
                                                        <div class="text-center"><h6>{{ __('there is no account balance') }}</h6></div>
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
                        <div class="{{ ViewClassNamesConstants::CD }}">
                            <div class="card-header"><h5 class="{{ ViewClassNamesConstants::MT1 }} {{ ViewClassNamesConstants::MB0 }}">{{ __('Latest Income') }}</h5></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="{{ ViewClassNamesConstants::TB }}">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Customer') }}</th>
                                                <th>{{ __('Amount Due') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($latestIncome as $income)
                                                <tr>
                                                    <td>{{ $user->dateFormat($income->date) }}</td>
                                                    <td>{{ $income->customer->name ?? '-' }}</td>
                                                    <td>{{ $user->priceFormat($income->amount) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4"><div class="text-center"><h6>{{ __('There is no latest income') }}</h6></div></td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-12">
                        <div class="{{ ViewClassNamesConstants::CD }}">
                            <div class="card-header"><h5 class="{{ ViewClassNamesConstants::MT1 }} {{ ViewClassNamesConstants::MB0 }}">{{ __('Latest Expense') }}</h5></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="{{ ViewClassNamesConstants::TB }}">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Vendor') }}</th>
                                                <th>{{ __('Amount Due') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($latestExpense as $expense)
                                                <tr>
                                                    <td>{{ $user->dateFormat($expense->date) }}</td>
                                                    <td>{{ $expense->vendor->name ?? '-' }}</td>
                                                    <td>{{ $user->priceFormat($expense->amount) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4"><div class="text-center"><h6>{{ __('There is no latest expense') }}</h6></div></td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-12">
                        <div class="{{ ViewClassNamesConstants::CD }}">
                            <div class="card-header"><h5 class="{{ ViewClassNamesConstants::MT1 }} {{ ViewClassNamesConstants::MB0 }}">{{ __('Recent Invoices') }}</h5></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="{{ ViewClassNamesConstants::TB }}">
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
                                                <tr>
                                                    <td>{{ $user->invoiceNumberFormat($invoice->invoice_id) }}</td>
                                                    <td>{{ $invoice->customer->name ?? '' }}</td>
                                                    <td>{{ $user->dateFormat($invoice->issue_date) }}</td>
                                                    <td>{{ $user->dateFormat($invoice->due_date) }}</td>
                                                    <td>{{ $user->priceFormat($invoice->getTotal()) }}</td>
                                                    <td>
                                                        @if($invoice->status==0)
                                                            <span class="p-2 px-3 rounded {{ ViewClassNamesConstants::BDG }} bg-secondary">{{ __(Invoice::$statuses[$invoice->status]) }}</span>
                                                        @elseif($invoice->status==1)
                                                            <span class="p-2 px-3 rounded {{ ViewClassNamesConstants::BDG }} bg-warning">{{ __(Invoice::$statuses[$invoice->status]) }}</span>
                                                        @elseif($invoice->status==2)
                                                            <span class="p-2 px-3 rounded {{ ViewClassNamesConstants::BDG }} bg-danger">{{ __(Invoice::$statuses[$invoice->status]) }}</span>
                                                        @elseif($invoice->status==3)
                                                            <span class="p-2 px-3 rounded {{ ViewClassNamesConstants::BDG }} bg-info">{{ __(Invoice::$statuses[$invoice->status]) }}</span>
                                                        @elseif($invoice->status==4)
                                                            <span class="p-2 px-3 rounded {{ ViewClassNamesConstants::BDG }} bg-primary">{{ __(Invoice::$statuses[$invoice->status]) }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6"><div class="text-center"><h6>{{ __('There is no recent invoice') }}</h6></div></td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-12">
                        <div class="{{ ViewClassNamesConstants::CD }}">
                            <div class="card-header"><h5 class="{{ ViewClassNamesConstants::MT1 }} {{ ViewClassNamesConstants::MB0 }}">{{ __('Recent Bills') }}</h5></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="{{ ViewClassNamesConstants::TB }}">
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
                                                <tr>
                                                    <td>{{ $user->billNumberFormat($bill->bill_id) }}</td>
                                                    <td>{{ $bill->vendor->name ?? '' }}</td>
                                                    <td>{{ $user->dateFormat($bill->bill_date) }}</td>
                                                    <td>{{ $user->dateFormat($bill->due_date) }}</td>
                                                    <td>{{ $user->priceFormat($bill->getTotal()) }}</td>
                                                    <td>
                                                        @if($bill->status==0)
                                                            <span class="p-2 px-3 rounded {{ ViewClassNamesConstants::BDG }} bg-secondary">{{ __(Bill::$statuses[$bill->status]) }}</span>
                                                        @elseif($bill->status==1)
                                                            <span class="p-2 px-3 rounded {{ ViewClassNamesConstants::BDG }} bg-warning">{{ __(Bill::$statuses[$bill->status]) }}</span>
                                                        @elseif($bill->status==2)
                                                            <span class="p-2 px-3 rounded {{ ViewClassNamesConstants::BDG }} bg-danger">{{ __(Bill::$statuses[$bill->status]) }}</span>
                                                        @elseif($bill->status==3)
                                                            <span class="p-2 px-3 rounded {{ ViewClassNamesConstants::BDG }} bg-info">{{ __(Bill::$statuses[$bill->status]) }}</span>
                                                        @elseif($bill->status==4)
                                                            <span class="p-2 px-3 rounded {{ ViewClassNamesConstants::BDG }} bg-primary">{{ __(Bill::$statuses[$bill->status]) }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6"><div class="text-center"><h6>{{ __('There is no recent bill') }}</h6></div></td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-5">
                    <div class="row">
                        <div class="col-12">
                            <div class="{{ ViewClassNamesConstants::CD }}">
                                <div class="card-header">
                                    <h5 class="{{ ViewClassNamesConstants::MT1 }} {{ ViewClassNamesConstants::MB0 }}">{{ __('Cashflow') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div id="cash-flow"></div>
                                </div>
                            </div>
                            @php
                                $tiles=[['label'=>__('Income Today'),'value'=>$user->priceFormat($user->todayIncome()),'avatarBg'=>'bg-primary','icon'=>'ti-report-money','textClass'=>'text-success'],['label'=>__('Expense Today'),'value'=>$user->priceFormat($user->todayExpense()),'avatarBg'=>'bg-info','icon'=>'ti-file-invoice','textClass'=>'text-info'],['label'=>__('Income This Month'),'value'=>$user->priceFormat($user->incomeCurrentMonth()),'avatarBg'=>'bg-warning','icon'=>'ti-report-money','textClass'=>'text-warning'],['label'=>__('Expense This Month'),'value'=>$user->priceFormat($user->expenseCurrentMonth()),'avatarBg'=>'bg-danger','icon'=>'ti-file-invoice','textClass'=>'text-danger']];
                            @endphp
                            <div class="{{ ViewClassNamesConstants::CD }}">
                                <div class="card-header">
                                    <h5 class="{{ ViewClassNamesConstants::MT1 }} {{ ViewClassNamesConstants::MB0 }}">{{ __('Income Vs Expense') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="{{ ViewClassNamesConstants::RW }}">
                                        @foreach($tiles as $tile)
                                            <div class="col-md-6 col-6 my-2">
                                                <div class="{{ ViewClassNamesConstants::DFL }} align-items-start mb-2">
                                                    <div class="theme-avatar {{ $tile['avatarBg'] }}">
                                                        <i class="ti {{ $tile['icon'] }}"></i>
                                                    </div>
                                                    <div class="{{ ViewClassNamesConstants::MS2 }}">
                                                        <p class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }} {{ ViewClassNamesConstants::MB0 }}">{{ $tile['label'] }}</p>
                                                        <h4 class="{{ ViewClassNamesConstants::MB0 }} {{ $tile['textClass'] }}">{{ $tile['value'] }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12">
                            <div class="{{ ViewClassNamesConstants::CD }}">
                                <div class="card-header">
                                    @php
                                        $storage = ($user instanceof User && $plan instanceof Plan) 
                                            ? $user->storage_limit.'MB' . '/' . $plan->storage_limit.'MB'
                                            : (string) SettingsConstants::MAX_SL_LIMIT_MB;
                                    @endphp
                                    <h5>{{ __('Storage Limit') }}<small class="{{ ViewClassNamesConstants::FEND }} {{ ViewClassNamesConstants::TXT_MT }}">$storage</small></h5>
                                </div>
                                <div class="card-body">
                                    <div id="limit-chart"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12">
                            <div class="{{ ViewClassNamesConstants::CD }}">
                                <div class="card-header">
                                    <h5>{{ __('Income By Category') }}<span class="{{ ViewClassNamesConstants::FEND }} {{ ViewClassNamesConstants::TXT_MT }}">{{ __('Year').' - '.$currentYear }}</span></h5>
                                </div>
                                <div class="card-body">
                                    <div id="incomeByCategory"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12">
                            <div class="{{ ViewClassNamesConstants::CD }}">
                                <div class="card-header">
                                    <h5>{{ __('Expense By Category') }}<span class="{{ ViewClassNamesConstants::FEND }} {{ ViewClassNamesConstants::TXT_MT }}">{{ __('Year').' - '.$currentYear }}</span></h5>
                                </div>
                                <div class="card-body">
                                    <div id="expenseByCategory"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12">
                            <div class="{{ ViewClassNamesConstants::CD }}">
                                <div class="card-body">
                                    <ul class="nav nav-pills mb-5" id="pills-tab" role="tablist">
                                        <li class="{{ ViewClassNamesConstants::NV_IT }}">
                                            <a class="{{ ViewClassNamesConstants::NV_LK }} active" id="pills-home-tab" data-bs-toggle="pill" href="#invoice_weekly_statistics" role="tab">{{ __('Invoices Weekly Statistics') }}</a>
                                        </li>
                                        <li class="{{ ViewClassNamesConstants::NV_IT }}">
                                            <a class="{{ ViewClassNamesConstants::NV_LK }}" id="pills-profile-tab" data-bs-toggle="pill" href="#invoice_monthly_statistics" role="tab">{{ __('Invoices Monthly Statistics') }}</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="pills-tabContent">
                                        <div class="tab-pane fade show active" id="invoice_weekly_statistics" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="{{ ViewClassNamesConstants::TB_AL }} {{ ViewClassNamesConstants::MB0 }}">
                                                    <tbody class="list">
                                                        <tr>
                                                            <td>
                                                                <h5 class="{{ ViewClassNamesConstants::MB0 }}">{{ __('Total') }}</h5>
                                                                <p class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }} {{ ViewClassNamesConstants::MB0 }}">{{ __('Invoice Generated') }}</p>
                                                            </td>
                                                            <td><h4 class="text-muted">{{ $user->priceFormat($weeklyInvoice['invoiceTotal']) }}</h4></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <h5 class="{{ ViewClassNamesConstants::MB0 }}">{{ __('Total') }}</h5>
                                                                <p class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }} {{ ViewClassNamesConstants::MB0 }}">{{ __('Paid') }}</p>
                                                            </td>
                                                            <td><h4 class="text-muted">{{ $user->priceFormat($weeklyInvoice['invoicePaid']) }}</h4></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <h5 class="{{ ViewClassNamesConstants::MB0 }}">{{ __('Total') }}</h5>
                                                                <p class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }} {{ ViewClassNamesConstants::MB0 }}">{{ __('Due') }}</p>
                                                            </td>
                                                            <td><h4 class="text-muted">{{ $user->priceFormat($weeklyInvoice['invoiceDue']) }}</h4></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="invoice_monthly_statistics" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="{{ ViewClassNamesConstants::TB_AL }} {{ ViewClassNamesConstants::MB0 }}">
                                                    <tbody class="list">
                                                        <tr>
                                                            <td>
                                                                <h5 class="{{ ViewClassNamesConstants::MB0 }}">{{ __('Total') }}</h5>
                                                                <p class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }} {{ ViewClassNamesConstants::MB0 }}">{{ __('Invoice Generated') }}</p>
                                                            </td>
                                                            <td><h4 class="text-muted">{{ $user->priceFormat($monthlyInvoice['invoiceTotal']) }}</h4></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <h5 class="{{ ViewClassNamesConstants::MB0 }}">{{ __('Total') }}</h5>
                                                                <p class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }} {{ ViewClassNamesConstants::MB0 }}">{{ __('Paid') }}</p>
                                                            </td>
                                                            <td><h4 class="text-muted">{{ $user->priceFormat($monthlyInvoice['invoicePaid']) }}</h4></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <h5 class="{{ ViewClassNamesConstants::MB0 }}">{{ __('Total') }}</h5>
                                                                <p class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }} {{ ViewClassNamesConstants::MB0 }}">{{ __('Due') }}</p>
                                                            </td>
                                                            <td><h4 class="text-muted">{{ $user->priceFormat($monthlyInvoice['invoiceDue']) }}</h4></td>
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
                            <div class="{{ ViewClassNamesConstants::CD }}">
                                <div class="card-header">
                                    <h5 class="{{ ViewClassNamesConstants::MT1 }} {{ ViewClassNamesConstants::MB0 }}">{{ __('Goal') }}</h5>
                                </div>
                                <div class="card-body">
                                    @forelse($goals as $goal)
                                        @php
                                            $results    = $goal->target($goal->type,$goal->from,$goal->to,$goal->amount);
                                            $total      = $results['total'];
                                            $percentage = $results['percentage'];
                                            $per        = number_format($percentage,Utility::getValByName('decimal_number'),'.','');
                                        @endphp
                                        <div class="{{ ViewClassNamesConstants::CD }} border-success border-2 border-bottom-0 border-start-0 border-end-0">
                                            <div class="card-body">
                                                <div class="{{ ViewClassNamesConstants::FM_CHK }}">
                                                    <label class="{{ ViewClassNamesConstants::DBL }}" for="customCheckdef1">
                                                        <span>
                                                            <span class="{{ ViewClassNamesConstants::R_ALC }}">
                                                                <span class="col">
                                                                    <span class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }}">{{ __('Name') }}</span>
                                                                    <h6 class="text-nowrap {{ ViewClassNamesConstants::MB3 }} mb-sm-0">{{ $goal->name }}</h6>
                                                                </span>
                                                                <span class="col">
                                                                    <span class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }}">{{ __('Type') }}</span>
                                                                    <h6 class="{{ ViewClassNamesConstants::MB3 }} mb-sm-0">{{ __(\App\Models\Goal::$goalType[$goal->type]) }}</h6>
                                                                </span>
                                                                <span class="col">
                                                                    <span class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }}">{{ __('Duration') }}</span>
                                                                    <h6 class="{{ ViewClassNamesConstants::MB3 }} mb-sm-0">{{ $goal->from.' To '.$goal->to }}</h6>
                                                                </span>
                                                                <span class="col">
                                                                    <span class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }}">{{ __('Target') }}</span>
                                                                    <h6 class="{{ ViewClassNamesConstants::MB3 }} mb-sm-0">{{ $user->priceFormat($total).' of '.$user->priceFormat($goal->amount) }}</h6>
                                                                </span>
                                                                <span class="col">
                                                                    <span class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }}">{{ __('Progress') }}</span>
                                                                    <h6 class="{{ ViewClassNamesConstants::MB0 }}">{{ $per }}%</h6>
                                                                    <div class="{{ ViewClassNamesConstants::PG }} {{ ViewClassNamesConstants::MB0 }}">
                                                                        @if($per<=33)
                                                                            <div class="progress-bar bg-danger" style="width: {{ $per }}%"></div>
                                                                        @elseif($per<=66)
                                                                            <div class="progress-bar bg-warning" style="width: {{ $per }}%"></div>
                                                                        @else
                                                                            <div class="progress-bar bg-primary" style="width: {{ $per }}%"></div>
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
                                        <div class="{{ ViewClassNamesConstants::CD }} pb-0">
                                            <div class="card-body text-center">
                                                <h6>{{ __('There is no goal.') }}</h6>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-12">
                    <div class="{{ ViewClassNamesConstants::CD }}">
                        <div class="card-header">
                            <h5>{{ __('Goal') }}</h5>
                        </div>
                        <div class="card-body">
                            @forelse($goals as $goal)
                                @php
                                    $results    = $goal->target($goal->type, $goal->from, $goal->to, $goal->amount);
                                    $total      = $results['total'];
                                    $percentage = $results['percentage'];
                                    $per        = number_format($percentage, Utility::getValByName('decimal_number'), '.', '');
                                @endphp
                                <div class="{{ ViewClassNamesConstants::CD }} border-success border-2 border-bottom-0 border-start-0 border-end-0">
                                    <div class="card-body">
                                        <div class="{{ ViewClassNamesConstants::FM_CHK }}">
                                            <label class="form-check-label {{ ViewClassNamesConstants::DBL }}" for="customCheckdef1">
                                                <span>
                                                    <span class="{{ ViewClassNamesConstants::R_ALC }}">
                                                        <span class="col">
                                                            <span class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }}">{{ __('Name') }}</span>
                                                            <h6 class="text-nowrap {{ ViewClassNamesConstants::MB3 }} mb-sm-0">{{ $goal->name }}</h6>
                                                        </span>
                                                        <span class="col">
                                                            <span class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }}">{{ __('Type') }}</span>
                                                            <h6 class="{{ ViewClassNamesConstants::MB3 }} mb-sm-0">{{ __(\App\Models\Goal::$goalType[$goal->type]) }}</h6>
                                                        </span>
                                                        <span class="col">
                                                            <span class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }}">{{ __('Duration') }}</span>
                                                            <h6 class="{{ ViewClassNamesConstants::MB3 }} mb-sm-0">{{ $goal->from .' To '.$goal->to }}</h6>
                                                        </span>
                                                        <span class="col">
                                                            <span class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }}">{{ __('Target') }}</span>
                                                            <h6 class="{{ ViewClassNamesConstants::MB3 }} mb-sm-0">{{ $user->priceFormat($total) .' of '. $user->priceFormat($goal->amount) }}</h6>
                                                        </span>
                                                        <span class="col">
                                                            <span class="{{ ViewClassNamesConstants::TXT_MT }} {{ ViewClassNamesConstants::TXSM }}">{{ __('Progress') }}</span>
                                                            <h6 class="{{ ViewClassNamesConstants::MB0 }} {{ ViewClassNamesConstants::DBL }}">{{ $per }}%</h6>
                                                            <div class="{{ ViewClassNamesConstants::PG }} {{ ViewClassNamesConstants::MB0 }}">
                                                                @if($per <= 33)
                                                                    <div class="progress-bar bg-danger" style="width: {{ $per }}%"></div>
                                                                @elseif($per <= 66)
                                                                    <div class="progress-bar bg-warning" style="width: {{ $per }}%"></div>
                                                                @else
                                                                    <div class="progress-bar bg-primary" style="width: {{ $per }}%"></div>
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
                                <div class="{{ ViewClassNamesConstants::CD }} pb-0">
                                    <div class="card-body text-center">
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
