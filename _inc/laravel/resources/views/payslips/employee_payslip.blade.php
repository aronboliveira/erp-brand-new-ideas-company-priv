@extends(ExtendingLayoutsConstants::ADM)
@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants
    };
    use App\Config\Constants\ViewClassNamesConstants as VC;
    use App\Config\Constants\ViewsConstants as VW;
    use App\Models\{Payslip, Utility};
    use Illuminate\Support\Facades\{Auth,Route};
    use Illuminate\Support\Collection;

    $user = Auth::user();
    $lang = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
    $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);
    $canDateFormat = is_object($user) && is_callable([$user,'dateFormat']);

    $dashBase = 'dashboard';
    $dashUrl = Route::has($dashBase) ? route($dashBase) : '#';
    $dashGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang,'generics','dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $showEmpBase = VW::PY_SLP.'.showemployee';
    $showEmpGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::PY_SLP ?? 'payslip','show_employee_route_unavailable') : 'Payslip employee details route is unavailable. Please contact technical support or your domain administrator.') ?? __('Payslip employee details route is unavailable. Please contact technical support or your domain administrator.');

    $pdfBase = VW::PY_SLP.'.pdf';
    $pdfGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::PY_SLP ?? 'payslip','pdf_route_unavailable') : 'Payslip PDF route is unavailable. Please contact technical support or your domain administrator.') ?? __('Payslip PDF route is unavailable. Please contact technical support or your domain administrator.');

    $payslips = (is_array($payslip ?? null) && count($payslip ?? [])) ? $payslip : (($payslip ?? null) instanceof Collection && $payslip->isNotEmpty() ? $payslip : []);
    $canStaticEmployee = is_callable([Payslip::class,'employee']);
@endphp
@section(YieldingConstants::ADM_PG_TTL){{ __('Payslip') }}@endsection
@section(YieldingConstants::ADM_CTT)
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
                        <div class="card">
                            <div class="card-header"><div class="d-flex justify-content-between w-100"><h4>{{ __('Employee Salary') }}</h4></div></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0 datatable">
                                        <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Payroll Month') }}</th>
                                            <th>{{ __('Salary') }}</th>
                                            <th>{{ __('Net Salary') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th width="200px">{{ __('Action') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(Utility::isFilled($payslips) ?? [])
                                            @foreach($payslips as $row)
                                                @php
                                                    $empId = data_get($row,'employee_id');
                                                    $rowId = data_get($row,'id');
                                                    $emp = $canStaticEmployee ? (Payslip::employee($empId) ?? null) : null;
                                                    $empName = data_get($emp,'name',__('No employee name available'));
                                                    $month = data_get($row,'salary_month',__('No payroll month available'));
                                                    $basic = data_get($row,'basic_salary',__('No salary available'));
                                                    $net = data_get($row,'net_payble',__('No net salary available'));
                                                    $paid = (int) data_get($row,'status',0) === 1;
                                                    $showEmpUrl = Route::has($showEmpBase) ? route($showEmpBase,$rowId) : '#';
                                                    $pdfUrl = Route::has($pdfBase) ? route($pdfBase,[$empId,$month]) : '#';
                                                @endphp
                                                <tr>
                                                    <td>{{ $empName }}</td>
                                                    <td>{{ $month }}</td>
                                                    <td>{{ $basic }}</td>
                                                    <td>{{ $net }}</td>
                                                    <td>@if($paid)<div class="badge badge-success"><span class="text-white">{{ __('Paid') }}</span></div>@else<div class="badge badge-danger"><span class="text-white">{{ __('Unpaid') }}</span></div>@endif</td>
                                                    <td>
                                                        <a href="{{ $showEmpUrl }}" data-url="{{ $showEmpUrl }}" data-guard-msg="{{ $showEmpGuard }}" data-ajax-popup="true" class="btn btn-sm btn-warning btn-round btn-icon route-guard" data-bs-toggle="tooltip" title="{{ __('View Employee Detail') }}" data-title="{{ __('View Employee Detail') }}">{{ __('View') }}</a>
                                                        <a href="{{ $pdfUrl }}" data-url="{{ $pdfUrl }}" data-guard-msg="{{ $pdfGuard }}" data-size="md-pdf" data-ajax-popup="true" class="btn btn-sm btn-info btn-round btn-icon route-guard" data-bs-toggle="tooltip" title="{{ __('Payslip') }}" data-title="{{ __('Payslip') }}">{{ __('Payslip') }}</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr><td colspan="6" class="text-center">{{ __('No payslips available') }}</td></tr>
                                        @endif
                                        </tbody>
                                    </table>
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
    <script>window.PAYSLIP_I18N={routeUnavailable:"{{ __('Requested route is unavailable. Please contact technical support or your domain administrator.') }}"};</script>
    <script defer src="{{ asset('assets/js/routes/payroll/payslips/employees/index.js') }}"></script>
@endpush
