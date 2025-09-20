@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YD
    };
    use App\Models\{Utility,Payslip,Employee};
    use Illuminate\Support\Facades\{Auth,Route};
    use Illuminate\Support\Collection;

    $user = Auth::user();
    $lang = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
    $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);
    $canPrice = is_object($user) && is_callable([$user,'priceFormat']);
    $canDate = is_object($user) && is_callable([$user,'dateFormat']);
    $canEmpIdFmt = is_object($user) && is_callable([$user,'employeeIdFormat']);

    $dashBase = 'dashboard';
    $dashUrl = Route::has($dashBase) ? route($dashBase) : '#';
    $dashGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang,'generics','dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $p = ($payslip ?? null) instanceof Payslip ? $payslip : null;
    $emp = $p ? (data_get($p,'employees') ?? null) : null;
    $empCode = $canEmpIdFmt ? ($emp && data_get($emp,'employee_id') ? $user->employeeIdFormat(data_get($emp,'employee_id')) : __('No employee code available')) : __('Failed to format employee code');
    $basicSalary = $canPrice ? ($p && data_get($p,'basic_salary') !== null ? $user->priceFormat(data_get($p,'basic_salary')) : __('No salary available')) : __('Failed to format salary');
    $salaryMonth = $canDate ? ($p && data_get($p,'salary_month') ? $user->dateFormat(data_get($p,'salary_month')) : __('No payroll month available')) : __('Failed to format date');

    $allowances = $p && data_get($p,'allowance') ? json_decode(data_get($p,'allowance')) : [];
    $commissions = $p && data_get($p,'commission') ? json_decode(data_get($p,'commission')) : [];
    $loans = $p && data_get($p,'loan') ? json_decode(data_get($p,'loan')) : [];
    $deductions = $p && data_get($p,'saturation_deduction') ? json_decode(data_get($p,'saturation_deduction')) : [];
    $overtimes = $p && data_get($p,'overtime') ? json_decode(data_get($p,'overtime')) : [];

    $fmt = function($amount){ return is_numeric($amount) ? $amount : 0; };
    $price = function($val) use($canPrice,$user){ return $canPrice ? $user->priceFormat($val) : __('Failed to format amount'); };
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YD::ADM_PG_TTL){{ __('Payslip') }}@endsection
@section(YD::ADM_CTT)
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __('Employee Salary') }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ $dashUrl }}" data-url="{{ $dashUrl }}" data-guard-msg="{{ $dashGuard }}" data-sv-localized="true">{{ __('Dashboard') }}</a></div>
                    <div class="breadcrumb-item">{{ __('Employee Salary') }}</div>
                </div>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-none card-box">
                            <div class="col-md-12">
                                <div class="card bg-none card-box">
                                    <form>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <h5 class="emp-title mb-0">{{ __('Employee Detail') }}</h5>
                                                <h5 class="emp-title black-text">{{ $empCode }}</h5>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <h5 class="emp-title mb-0">{{ __('Basic Salary') }}</h5>
                                                <h5 class="emp-title black-text">{{ $basicSalary }}</h5>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <h5 class="emp-title mb-0">{{ __('Payroll Month') }}</h5>
                                                <h5 class="emp-title black-text">{{ $salaryMonth }}</h5>
                                            </div>
                                            <div class="col-lg-12 our-system">
                                                <div class="row">
                                                    <ul class="nav nav-tabs my-4">
                                                        <li><a data-toggle="tab" href="#allowance" class="active">{{ __('Allowance') }}</a></li>
                                                        <li><a data-toggle="tab" href="#commission">{{ __('Commission') }}</a></li>
                                                        <li><a data-toggle="tab" href="#loan">{{ __('Loan') }}</a></li>
                                                        <li><a data-toggle="tab" href="#deduction">{{ __('Saturation Deduction') }}</a></li>
                                                        <li><a data-toggle="tab" href="#payment">{{ __('Other Payment') }}</a></li>
                                                        <li><a data-toggle="tab" href="#overtime">{{ __('Overtime') }}</a></li>
                                                    </ul>
                                                    <div class="tab-content">
                                                        <div id="allowance" class="tab-pane in active">
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="card bg-none mb-0">
                                                                        <div class="table-responsive">
                                                                            @php $al = (is_array($allowances) && count($allowances)) || ($allowances instanceof Collection && $allowances->isNotEmpty()); @endphp
                                                                            <table class="table align-items-center">
                                                                                <thead><tr><th>{{ __('Title') }}</th><th>{{ __('Type') }}</th><th>{{ __('Amount') }}</th></tr></thead>
                                                                                <tbody class="list">
                                                                                @if($al)
                                                                                    @foreach($allowances as $a)
                                                                                        @php
                                                                                            $title = data_get($a,'title',__('No title available'));
                                                                                            $type = data_get($a,'type',__('No type available'));
                                                                                            $eid = data_get($a,'employee_id');
                                                                                            $amt = $fmt(data_get($a,'amount'));
                                                                                            $empRow = $eid ? (Employee::find($eid) ?? null) : null;
                                                                                            $base = $fmt(data_get($empRow,'salary'));
                                                                                            $pctVal = $base ? ($amt * $base / 100) : 0;
                                                                                        @endphp
                                                                                        <tr>
                                                                                            <td>{{ $title }}</td>
                                                                                            <td>{{ ucfirst(is_string($type)?$type:__('unknown')) }}</td>
                                                                                            @if($type !== 'percentage')
                                                                                                <td>{{ $price($amt) }}</td>
                                                                                            @else
                                                                                                <td>{{ $amt }}% ({{ $price($pctVal) }})</td>
                                                                                            @endif
                                                                                        </tr>
                                                                                    @endforeach
                                                                                @else
                                                                                    <tr><td colspan="3" class="text-center">{{ __('No allowances available') }}</td></tr>
                                                                                @endif
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="commission" class="tab-pane">
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="card bg-none mb-0">
                                                                        <div class="table-responsive">
                                                                            @php $cm = (is_array($commissions) && count($commissions)) || ($commissions instanceof Collection && $commissions->isNotEmpty()); @endphp
                                                                            <table class="table align-items-center">
                                                                                <thead><tr><th>{{ __('Title') }}</th><th>{{ __('Type') }}</th><th>{{ __('Amount') }}</th></tr></thead>
                                                                                <tbody class="list">
                                                                                @if($cm)
                                                                                    @foreach($commissions as $c)
                                                                                        @php
                                                                                            $title = data_get($c,'title',__('No title available'));
                                                                                            $type = data_get($c,'type',__('No type available'));
                                                                                            $eid = data_get($c,'employee_id');
                                                                                            $amt = $fmt(data_get($c,'amount'));
                                                                                            $empRow = $eid ? (Employee::find($eid) ?? null) : null;
                                                                                            $base = $fmt(data_get($empRow,'salary'));
                                                                                            $pctVal = $base ? ($amt * $base / 100) : 0;
                                                                                        @endphp
                                                                                        <tr>
                                                                                            <td>{{ $title }}</td>
                                                                                            <td>{{ ucfirst(is_string($type)?$type:__('unknown')) }}</td>
                                                                                            @if($type !== 'percentage')
                                                                                                <td>{{ $price($amt) }}</td>
                                                                                            @else
                                                                                                <td>{{ $amt }}% ({{ $price($pctVal) }})</td>
                                                                                            @endif
                                                                                        </tr>
                                                                                    @endforeach
                                                                                @else
                                                                                    <tr><td colspan="3" class="text-center">{{ __('No commissions available') }}</td></tr>
                                                                                @endif
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="loan" class="tab-pane">
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="card bg-none mb-0">
                                                                        <div class="table-responsive">
                                                                            @php $ln = (is_array($loans) && count($loans)) || ($loans instanceof Collection && $loans->isNotEmpty()); @endphp
                                                                            <table class="table align-items-center">
                                                                                <thead><tr><th>{{ __('Title') }}</th><th>{{ __('Type') }}</th><th>{{ __('Amount') }}</th></tr></thead>
                                                                                <tbody class="list">
                                                                                @if($ln)
                                                                                    @foreach($loans as $l)
                                                                                        @php
                                                                                            $title = data_get($l,'title',__('No title available'));
                                                                                            $type = data_get($l,'type',__('No type available'));
                                                                                            $eid = data_get($l,'employee_id');
                                                                                            $amt = $fmt(data_get($l,'amount'));
                                                                                            $empRow = $eid ? (Employee::find($eid) ?? null) : null;
                                                                                            $base = $fmt(data_get($empRow,'salary'));
                                                                                            $pctVal = $base ? ($amt * $base / 100) : 0;
                                                                                        @endphp
                                                                                        <tr>
                                                                                            <td>{{ $title }}</td>
                                                                                            <td>{{ ucfirst(is_string($type)?$type:__('unknown')) }}</td>
                                                                                            @if($type !== 'percentage')
                                                                                                <td>{{ $price($amt) }}</td>
                                                                                            @else
                                                                                                <td>{{ $amt }}% ({{ $price($pctVal) }})</td>
                                                                                            @endif
                                                                                        </tr>
                                                                                    @endforeach
                                                                                @else
                                                                                    <tr><td colspan="3" class="text-center">{{ __('No loans available') }}</td></tr>
                                                                                @endif
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="deduction" class="tab-pane">
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="card bg-none mb-0">
                                                                        <div class="table-responsive">
                                                                            @php $dd = (is_array($deductions) && count($deductions)) || ($deductions instanceof Collection && $deductions->isNotEmpty()); @endphp
                                                                            <table class="table align-items-center">
                                                                                <thead><tr><th>{{ __('Title') }}</th><th>{{ __('Type') }}</th><th>{{ __('Amount') }}</th></tr></thead>
                                                                                <tbody class="list">
                                                                                @if($dd)
                                                                                    @foreach($deductions as $d)
                                                                                        @php
                                                                                            $title = data_get($d,'title',__('No title available'));
                                                                                            $type = data_get($d,'type',__('No type available'));
                                                                                            $eid = data_get($d,'employee_id');
                                                                                            $amt = $fmt(data_get($d,'amount'));
                                                                                            $empRow = $eid ? (Employee::find($eid) ?? null) : null;
                                                                                            $base = $fmt(data_get($empRow,'salary'));
                                                                                            $pctVal = $base ? ($amt * $base / 100) : 0;
                                                                                        @endphp
                                                                                        <tr>
                                                                                            <td>{{ $title }}</td>
                                                                                            <td>{{ ucfirst(is_string($type)?$type:__('unknown')) }}</td>
                                                                                            @if($type !== 'percentage')
                                                                                                <td>{{ $price($amt) }}</td>
                                                                                            @else
                                                                                                <td>{{ $amt }}% ({{ $price($pctVal) }})</td>
                                                                                            @endif
                                                                                        </tr>
                                                                                    @endforeach
                                                                                @else
                                                                                    <tr><td colspan="3" class="text-center">{{ __('No deductions available') }}</td></tr>
                                                                                @endif
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="payment" class="tab-pane">
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="card bg-none mb-0">
                                                                        <div class="table-responsive">
                                                                            @php $op = (is_array($other_payments ?? []) && count($other_payments ?? [])) || (($other_payments ?? null) instanceof Collection && $other_payments->isNotEmpty()); @endphp
                                                                            @php $other = $op ? $other_payments : ( ($p && data_get($p,'other_payment')) ? json_decode(data_get($p,'other_payment')) : [] ); @endphp
                                                                            @php $opHas = (is_array($other) && count($other)) || ($other instanceof Collection && $other->isNotEmpty()); @endphp
                                                                            <table class="table align-items-center">
                                                                                <thead><tr><th>{{ __('Title') }}</th><th>{{ __('Type') }}</th><th>{{ __('Amount') }}</th></tr></thead>
                                                                                <tbody class="list">
                                                                                @if($opHas)
                                                                                    @foreach($other as $pay)
                                                                                        @php
                                                                                            $title = data_get($pay,'title',__('No title available'));
                                                                                            $type = data_get($pay,'type',__('No type available'));
                                                                                            $eid = data_get($pay,'employee_id');
                                                                                            $amt = $fmt(data_get($pay,'amount'));
                                                                                            $empRow = $eid ? (Employee::find($eid) ?? null) : null;
                                                                                            $base = $fmt(data_get($empRow,'salary'));
                                                                                            $pctVal = $base ? ($amt * $base / 100) : 0;
                                                                                        @endphp
                                                                                        <tr>
                                                                                            <td>{{ $title }}</td>
                                                                                            <td>{{ ucfirst(is_string($type)?$type:__('unknown')) }}</td>
                                                                                            @if($type !== 'percentage')
                                                                                                <td>{{ $price($amt) }}</td>
                                                                                            @else
                                                                                                <td>{{ $amt }}% ({{ $price($pctVal) }})</td>
                                                                                            @endif
                                                                                        </tr>
                                                                                    @endforeach
                                                                                @else
                                                                                    <tr><td colspan="3" class="text-center">{{ __('No other payments available') }}</td></tr>
                                                                                @endif
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="overtime" class="tab-pane">
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="card bg-none mb-0">
                                                                        <div class="table-responsive">
                                                                            @php $ot = (is_array($overtimes) && count($overtimes)) || ($overtimes instanceof Collection && $overtimes->isNotEmpty()); @endphp
                                                                            <table class="table align-items-center">
                                                                                <thead><tr><th>{{ __('Title') }}</th><th>{{ __('Amount') }}</th></tr></thead>
                                                                                <tbody class="list">
                                                                                @if($ot)
                                                                                    @foreach($overtimes as $o)
                                                                                        @php
                                                                                            $title = data_get($o,'title',__('No title available'));
                                                                                            $rate = $fmt(data_get($o,'rate'));
                                                                                        @endphp
                                                                                        <tr>
                                                                                            <td>{{ $title }}</td>
                                                                                            <td>{{ $price($rate) }}</td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                @else
                                                                                    <tr><td colspan="2" class="text-center">{{ __('No overtimes available') }}</td></tr>
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
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-4 py-3">
                                    <h5 class="emp-title mb-0">{{ __('Net Salary') }}</h5>
                                    <h5 class="emp-title black-text">{{ $canPrice ? ($p && data_get($p,'net_payble') !== null ? $user->priceFormat(data_get($p,'net_payble')) : __('No net salary available')) : __('Failed to format amount') }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script>window.PAYSLIP_SHOW_I18N={routeUnavailable:"{{ __('Requested route is unavailable. Please contact technical support or your domain administrator.') }}"};</script>
    <script defer src="{{ asset('assets/js/routes/payroll/payslips/show.js') }}"></script>
@endpush
