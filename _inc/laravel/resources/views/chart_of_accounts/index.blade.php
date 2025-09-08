@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;

    $user = Auth::user();
    $lang               = Utility::fetchUserLang(user: $user);
    $indexName          = ViewsConstants::COA . '.index';
    $indexRoute         = Route::has($indexName)
        ? route($indexName)
        : (Route::has(Str::kebab($indexName))
            ? route(Str::kebab($indexName))
            : '#');
    $indexGuardMsg      = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::COA,
        'chart_of_account_index_route_unavailable'
    ) ?? 'Chart of Account index route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Chart of Accounts') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Chart of Account') }}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/chartOfAccounts/lang/date.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/chartOfAccounts/date.js') }}"></script>
@endpush

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can(PermissionsConstants::CR_COA)
            @php
                $coaCreateBase = ViewsConstants::COA.'.create';
                $coaCreateKebab = Str::kebab($coaCreateBase);
                $coaCreateResolved = Route::has($coaCreateBase) ? $coaCreateBase : (Route::has($coaCreateKebab) ? $coaCreateKebab : null);
                $coaCreateUrl = $coaCreateResolved ? route($coaCreateResolved) : '#';
                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $coaCreateGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::COA, 'create_chart_of_account_route_unavailable') ?? 'Create chart of account route is unavailable. Please contact technical support or your domain administrator.';
                $coaCreateLinkId = 'coa-create-account-link';
            @endphp
            <a id="{{ $coaCreateLinkId }}"
            href="{{ $coaCreateUrl }}"
            data-url="{{ $coaCreateUrl }}"
            data-bs-toggle="tooltip"
            title="{{ __('Create') }}"
            data-size="lg"
            data-ajax-popup="true"
            data-title="{{ __('Create New Account') }}"
            class="{{ VC::BT_SM_PM }}"
            data-guard-msg="{{ $coaCreateGuardMsg }}"
            data-sv-localized="true">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/routes/chartOfAccounts/create.js') }}"></script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    @php
        $indexUrl = !empty($indexRoute) ? $indexRoute : '#';
        $indexGuard = $indexGuardMsg ?? __('No guard message available');
        $start = data_get($filter,'startDateRange');
        $end = data_get($filter,'endDateRange');
        $groups = ((is_array($chartAccounts ?? null) && count($chartAccounts ?? [])) || (($chartAccounts ?? null) instanceof Collection && ($chartAccounts)->isNotEmpty())) ? $chartAccounts : [];
        $isPriceFormatAvailable = ($user ?? null) && method_exists($user,'priceFormat');
    @endphp
    <div class="{{ VC::RW }} justify-content-center">
        <div class="{{ VC::CM12 }}">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="{{ VC::CD }}" id="show_filter">
                    <div class="card-body">
                        {{ Form::open([
                            'url'            => $indexUrl,
                            'method'         => 'GET',
                            'id'             => 'report_bill_summary',
                            'data-url'       => $indexUrl,
                            'data-guard-msg' => $indexGuard,
                        ]) }}
                        <div class="{{ VC::R_ALC_JCE }}">
                            <div class="col-xl-10">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::CL_XL3 }}"><div class="btn-box"></div></div>
                                    <div class="{{ VC::CL_XL3 }}"><div class="btn-box"></div></div>
                                    <div class="{{ VC::CL_XL3 }}">
                                        <div class="btn-box">
                                            {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                                            {{ Form::date('start_date', $start ?? '', ['class' => VC::FM_CT]) }}
                                        </div>
                                    </div>
                                    <div class="{{ VC::CL_XL3 }}">
                                        <div class="btn-box">
                                            {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                                            {{ Form::date('end_date', $end ?? '', ['class' => VC::FM_CT]) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                <div class="{{ VC::DFL_JCB }}">
                                    <a href="#" class="{{ VC::BT_SM_PM }}" id="applyFilter" data-listener-alias="apply-filter" data-bs-toggle="tooltip" title="{{ __('Apply') }}"><span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span></a>
                                    <a href="{{ $indexUrl }}" class="{{ VC::BT_SM_DG }}" data-bs-toggle="tooltip" title="{{ __('Reset') }}"><span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span></a>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }}">
        @forelse ($groups as $type => $accounts)
            @php
                $list = ((is_array($accounts ?? null) && count($accounts ?? [])) || (($accounts ?? null) instanceof Collection && ($accounts)->isNotEmpty())) ? $accounts : [];
            @endphp
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-header">
                        <h6>{{ !empty($type) ? $type : __('No type available') }}</h6>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="{{ VC::TB }}">
                                <thead>
                                    <tr>
                                        <th width="10%">{{ __('Code') }}</th>
                                        <th width="30%">{{ __('Name') }}</th>
                                        <th width="20%">{{ __('Type') }}</th>
                                        <th width="20%">{{ __('Balance') }}</th>
                                        <th width="10%">{{ __('Status') }}</th>
                                        <th width="10%">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($list as $account)
                                        @php
                                            $accId = data_get($account,'id');
                                            $ledgerUrl = $accId ? route(ViewsConstants::RPT . '.ledger', $accId) . '?account=' . $accId : '#';
                                            $ledgerGuard = Utility::fetchLinkMessage($lang, ViewsConstants::RPT, 'ledger_route_unavailable') ?? __('No ledger route available');
                                            $balanceVal = ($accId && $start && $end) ? (float) (Utility::getAccountBalance($accId,$start,$end) ?? 0) : 0;
                                            $enabled = (bool) data_get($account,'is_enabled');
                                        @endphp
                                        <tr>
                                            <td>{{ data_get($account,'code') ?: __('No code available') }}</td>
                                            <td>
                                                <a href="#" class="{{ VC::BT_SM_CT }}" data-url="{{ $ledgerUrl }}" data-guard-msg="{{ $ledgerGuard }}" data-listener-alias="ledger-link" data-bs-toggle="tooltip" title="{{ __('Transaction Summary') }}">{{ data_get($account,'name') ?: __('No account name available') }}</a>
                                            </td>
                                            <td>{{ data_get($account,'subType.name') ?: __('No subtype name available') }}</td>
                                            <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($balanceVal) : __('Failed to format balance') }}</td>
                                            <td><span class="badge {{ $enabled ? 'bg-primary' : 'bg-danger' }} p-2 px-3 rounded">{{ $enabled ? __('Enabled') : __('Disabled') }}</span></td>
                                            <td class="Action">
                                                <div class="{{ VC::ACT_BTN_WRN }}">
                                                    <a href="#" class="{{ VC::BT_SM_CT }}" data-url="{{ $ledgerUrl }}" data-guard-msg="{{ $ledgerGuard }}" data-listener-alias="ledger-link" data-bs-toggle="tooltip" title="{{ __('Transaction Summary') }}"><i class="ti ti-wave-sine {{ VC::TXT_WT }}"></i></a>
                                                </div>
                                                @can('edit chart of account')
                                                    @php
                                                        $editName = ViewsConstants::COA . '.edit';
                                                        $editUrl = Route::has($editName) ? route($editName, $accId) : (Route::has(Str::kebab($editName)) ? route(Str::kebab($editName), $accId) : '#');
                                                        $editGuard = Utility::fetchLinkMessage($lang, ViewsConstants::COA, 'chart_of_account_edit_route_unavailable') ?? __('No edit route available');
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a href="#" class="{{ VC::BT_SM_CT }}" data-url="{{ $editUrl }}" data-guard-msg="{{ $editGuard }}" data-listener-alias="edit-account" data-ajax-popup="true" data-title="{{ __('Edit Account') }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}"><i class="{{ VC::TI_PC_WT }}"></i></a>
                                                    </div>
                                                @endcan
                                                @can(PermissionsConstants::DEL_COA)
                                                    @php
                                                        $destroyName = ViewsConstants::COA . '.destroy';
                                                        $destroyUrl = Route::has($destroyName) ? route($destroyName, $accId) : (Route::has(Str::kebab($destroyName)) ? route(Str::kebab($destroyName), $accId) : '#');
                                                        $destroyGuard = Utility::fetchLinkMessage($lang, ViewsConstants::COA, 'chart_of_account_destroy_route_unavailable') ?? __('No delete route available');
                                                        $deleteFormId = 'delete-form-' . $accId;
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open([
                                                            'method'         => 'DELETE',
                                                            'url'            => $destroyUrl,
                                                            'id'             => $deleteFormId,
                                                            'data-url'       => $destroyUrl,
                                                            'data-guard-msg' => $destroyGuard,
                                                        ]) !!}
                                                        <a href="#" class="{{ VC::BT_SM_CT_PR }}" data-listener-alias="delete-account" data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"><i class="{{ VC::TI_TRS_WT }}"></i></a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="{{ VC::TXCT }}">{{ __('No accounts available') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="{{ VC::CM12 }}"><div class="{{ VC::CD }}"><div class="card-body"><p class="{{ VC::TXCT }}">{{ __('No chart accounts available') }}</p></div></div></div>
        @endforelse
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/chartOfAccounts/edit.js') }}"></script>
@endpush
