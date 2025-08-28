@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Account Statement Summary')}}
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <!-- <script src="{{ asset('js/jspdf.min.js') }} "></script>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/jszip.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/pdfmake.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/vfs_fonts.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/dataTables.buttons.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/buttons.html5.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/buttons.print.min.js') }}"></script> -->
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/statements/lang/pdf.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/statements/pdf.js') }}"></script>
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Account Statement Summary')}}</li>
@endsection

{{--        <a class="{{ VC::BT_SM_PM }}" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
{{--            <i class="ti ti-filter"></i>--}}
{{--        </a>--}}
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $exportBase = VW::ACC_STT.'.export';
            $exportKebab = Str::kebab($exportBase);
            $exportResolved = Route::has($exportBase) ? $exportBase : (Route::has($exportKebab) ? $exportKebab : null);
            $exportUrl = $exportResolved ? route($exportResolved) : '#';
            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
            $exportGuardMsg = Utility::fetchLinkMessage($langValue, VW::ACC_STT, 'export_account_statements_route_unavailable') ?? 'Export account statements route is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a id="account-statements-export"
        href="{{ $exportUrl }}"
        data-url="{{ $exportUrl }}"
        data-guard-msg="{{ $exportGuardMsg }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ __('Export') }}"
        class="btn btn-sm btn-primary">
            <i class="ti ti-file-export"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/accountStatements/export.js') }}" defer></script>
        @endpush
        @php
            $downloadLabelAs = __('Download');
            $downloadGuardMsgAs = Utility::fetchLinkMessage($lang, VW::RPT, 'download_account_statements_report_unavailable') ?? 'Download function for Account Statements report is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a href="#"
        class="{{ VC::BT_SM_PM }} download-account-statements"
        data-func-name="saveAsPDF"
        data-guard-msg="{{ $downloadGuardMsgAs }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ $downloadLabelAs }}"
        aria-label="{{ $downloadLabelAs }}"
        data-original-title="{{ $downloadLabelAs }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/accountStatements/download.js') }}" defer></script>
        @endpush
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        @php
                            $acctStmtBase = ViewsConstants::RPT.'.account.statement';
                            $acctStmtKebab = Str::kebab($acctStmtBase);
                            $acctStmtResolved = Route::has($acctStmtBase) ? $acctStmtBase : (Route::has($acctStmtKebab) ? $acctStmtKebab : null);
                            $actionRoute = $acctStmtResolved ? [$acctStmtResolved] : ['#'];
                            $actionUrl = $acctStmtResolved ? route($acctStmtResolved) : '#';
                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                            $applyGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'apply_account_statement_route_unavailable') ?? 'Apply account statement route is unavailable. Please contact technical support or your domain administrator.';
                            $resetGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'reset_account_statement_route_unavailable') ?? 'Reset account statement route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        {{ Form::open(['route'=> $actionRoute,'method'=>'GET','id'=>'report_account','data-url'=>$actionUrl,'data-guard-msg'=>$applyGuardMsg,'data-sv-localized'=>'true']) }}
                            <div class="{{ VC::R_ALC_JCE }}">
                                <div class="col-xl-10">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {{ Form::label('start_month', __('Start Month'), ['class' => VC::FM_LB]) }}
                                                {{ Form::month('start_month', isset($_GET['start_month']) ? $_GET['start_month'] : date('Y-m', strtotime('-5 month')), ['class' => 'month-btn ' . VC::FM_CT]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {{ Form::label('end_month', __('End Month'), ['class' => VC::FM_LB]) }}
                                                {{ Form::month('end_month', isset($_GET['end_month']) ? $_GET['end_month'] : date('Y-m'), ['class' => 'month-btn ' . VC::FM_CT]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {{ Form::label('account', __('Account'), ['class' => VC::FM_LB]) }}
                                                {{ Form::select('account', $account, isset($_GET['account']) ? $_GET['account'] : '', ['class' => VC::FM_CT_SL]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {{ Form::label('type', __('Category'), ['class' => VC::FM_LB]) }}
                                                {{ Form::select('type', $types, isset($_GET['type']) ? $_GET['type'] : '', ['class' => VC::FM_CT_SL, 'placeholder' => __('Select Category')]) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                            <a id="apply-account-statement"
                                            href="#"
                                            class="{{ VC::BT_SM_PM }}"
                                            data-form-id="report_account"
                                            data-guard-msg="{{ $applyGuardMsg }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a id="reset-account-statement"
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
                            <script src="{{ asset('assets/js/routes/reports/accountStatements/apply.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/reports/accountStatements/reset.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="printableArea">
        <div class="{{ VC::RW }} {{ VC::MT3 }}">
            <div class="col">
                <input type="hidden"
                       id="filename"
                       value="{{ __('Account Statement') . ' ' . $filter['type'] . ' ' . __('Report of') . ' ' . $filter['startDateRange'] . ' ' . __('to') . ' ' . $filter['endDateRange'] }}">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Report') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ __('Account Statement Summary') }}</h6>
                </div>
            </div>

            @if($filter['account'] != __('All'))
                <div class="col">
                    <div class="{{ VC::CD_POS }}">
                        <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Account') }} :</h7>
                        <h6 class="{{ VC::RPT_TX_DEF }}">{{ $filter['account'] }}</h6>
                    </div>
                </div>
            @endif

            @if($filter['type'] != __('All'))
                <div class="col">
                    <div class="{{ VC::CD_POS }}">
                        <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Type') }} :</h7>
                        <h6 class="{{ VC::RPT_TX_DEF }}">{{ $filter['type'] }}</h6>
                    </div>
                </div>
            @endif

            <div class="col">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Duration') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ $filter['startDateRange'] . ' ' . __('to') . ' ' . $filter['endDateRange'] }}</h6>
                </div>
            </div>
        </div>

        @if(!empty($reportData['revenueAccounts']))
            <div class="{{ VC::RW }}">
                @foreach($reportData['revenueAccounts'] as $acc)
                    <div class="{{ VC::CL_XL3 }}">
                        <div class="{{ VC::CD_POS }}">
                            @if($acc->holder_name == 'Cash')
                                <h7 class="{{ VC::RPT_TX_GR }}">{{ $acc->holder_name }}</h7>
                            @elseif(empty($acc->holder_name))
                                <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Stripe / PayPal') }}</h7>
                            @else
                                <h7 class="{{ VC::RPT_TX_GR }}">{{ $acc->holder_name . ' - ' . $acc->bank_name }}</h7>
                            @endif
                            <h6 class="{{ VC::RPT_TX_DEF }}">{{ $user?->priceFormat($acc->total) }}</h6>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if(!empty($reportData['paymentAccounts']))
            <div class="{{ VC::RW }}">
                @foreach($reportData['paymentAccounts'] as $acc)
                    <div class="{{ VC::CL_XL3 }}">
                        <div class="{{ VC::CD_POS }}">
                            @if($acc->holder_name == 'Cash')
                                <h7 class="{{ VC::RPT_TX_GR }}">{{ $acc->holder_name }}</h7>
                            @elseif(empty($acc->holder_name))
                                <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Stripe / PayPal') }}</h7>
                            @else
                                <h7 class="{{ VC::RPT_TX_GR }}">{{ $acc->holder_name . ' - ' . $acc->bank_name }}</h7>
                            @endif
                            <h6 class="{{ VC::RPT_TX_DEF }}">{{ $user?->priceFormat($acc->total) }}</h6>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @php
        $revTotal = 0.0;
        $payTotal = 0.0;
        if (!empty($reportData['revenues'])) {
            foreach ($reportData['revenues'] as $r) { $revTotal += (float) $r->amount; }
        }
        if (!empty($reportData['payments'])) {
            foreach ($reportData['payments'] as $p) { $payTotal += (float) $p->amount; }
        }
        $netTotal = $revTotal - $payTotal;
    @endphp

    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable" id="account-statement-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th class="text-end">{{ __('Amount') }}</th>
                                    <th>{{ __('Description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $hasRows = false; @endphp

                                @if(!empty($reportData['revenues']))
                                    @foreach ($reportData['revenues'] as $revenue)
                                        @php $hasRows = true; @endphp
                                        <tr class="font-style">
                                            <td>{{ $user?->dateFormat($revenue->date) }}</td>
                                            <td class="text-end">{{ $user?->priceFormat($revenue->amount) }}</td>
                                            <td>{{ $revenue->description }}</td>
                                        </tr>
                                    @endforeach
                                @endif

                                @if(!empty($reportData['payments']))
                                    @foreach ($reportData['payments'] as $payment)
                                        @php $hasRows = true; @endphp
                                        <tr class="font-style">
                                            <td>{{ $user?->dateFormat($payment->date) }}</td>
                                            <td class="text-end">{{ $user?->priceFormat($payment->amount) ?? __('Failed to fetch user data.') }}</td>
                                            <td>{{ !empty($payment->description) ? $payment->description : __('No description.') }}</td>
                                        </tr>
                                    @endforeach
                                @endif

                                @unless($hasRows)
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">{{ __('No transactions found for the selected period.') }}</td>
                                    </tr>
                                @endunless
                            </tbody>

                            @if($hasRows)
                                <tfoot>
                                    <tr>
                                        <th class="text-end">{{ __('Total Revenue') }}</th>
                                        <th class="text-end">{{ $user?->priceFormat($revTotal) ?? __('Failed to fetch user data.') }}</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th class="text-end">{{ __('Total Payments') }}</th>
                                        <th class="text-end">{{ $user?->priceFormat($payTotal) ?? __('Failed to fetch user data.') }}</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th class="text-end">{{ __('Net Total') }}</th>
                                        <th class="text-end">{{ $user?->priceFormat($netTotal) ?? __('Failed to fetch user data.') }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
