@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Crypt, Gate, Route};
    use Illuminate\Support\{Collection, Str};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YW::ADM_PG_TTL)
    {{__('Manage Expenses')}}
@endsection
@push(ST::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/expenses/lang/index.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/expenses/url.js') }}"></script>
@endpush
@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Expense')}}</li>
@endsection
@php
    $indexBase     = VW::PRJ_EXP . '.index';
    $indexKebab    = Str::kebab($indexBase);
    $indexResolved = Route::has($indexBase) ? $indexBase : (Route::has($indexKebab) ? $indexKebab : null);
    $indexUrl      = $indexResolved ? route($indexResolved) : '#';
    $indexGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ_EXP, 'index_expense_route_unavailable') ?? 'Expense index route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@section(YW::ADM_ACT_BTN)
    <div class="float-end">
        @can('create bill')
            @php
                $createBase     = VW::PRJ_EXP . '.create';
                $createKebab    = Str::kebab($createBase);
                $createResolved = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
                $createUrl      = $createResolved ? route($createResolved, 0) : '#';
                $createGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ_EXP, 'create_expense_route_unavailable') ?? 'Create expense route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                id="exp-create-btn"
                href="{{ $createUrl }}"
                data-url="{{ $createUrl }}"
                data-guard-msg="{{ $createGuardMsg }}"
                class="{{ VC::BT_SM_PM }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
            >
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section(YW::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div id="multiCollapseExample1" class="mt-2">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        {{ Form::open([
                            'url'    => $indexUrl,
                            'method' => 'GET',
                            'id'     => 'frm_submit',
                            'data-url' => $indexUrl,
                            'data-guard-msg' => $indexGuardMsg
                        ]) }}
                        <div class="{{ VC::R_FLX_ALC_JCE }}">
                            <div class="col-xl-10">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::C3 }}"></div>
                                    <div class="{{ VC::C3 }}"></div>
                                    <div class="{{ VC::CLMS4 }}">
                                        <div class="btn-box">
                                            {{ Form::label('bill_date', __('Payment Date'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('bill_date', request('bill_date'), ['class' => VC::FM_CT.' month-btn', 'id' => 'pc-daterangepicker-1', 'readonly']) }}
                                        </div>
                                    </div>
                                    <div class="{{ VC::CLMS4 }}">
                                        <div class="btn-box">
                                            {{ Form::label('category', __('Category'), ['class' => VC::FM_LB]) }}
                                            {{ Form::select('category', $category, request('category', ''), ['class' => VC::FM_CT_SL]) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto {{ VC::MT4 }}">
                                <div class="{{ VC::RW }}">
                                    <div class="col-auto">
                                        <a
                                            href="#"
                                            class="{{ VC::BT_SM_PM }}"
                                            id="exp-filter-apply"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                        >
                                            <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                        </a>
                                        <a
                                            href="{{ $indexUrl }}"
                                            data-url="{{ $indexUrl }}"
                                            data-guard-msg="{{ $indexGuardMsg }}"
                                            class="{{ VC::BT_SM_DG }}"
                                            id="exp-filter-reset"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Reset') }}"
                                        >
                                            <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                        </a>
                                    </div>
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
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{ __('Expense') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill'))
                                    <th width="10%">{{ __('Action') }}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $isExpenseNumberFormatAvailable = method_exists($user, 'expenseNumberFormat');
                                $isDateFormatAvailable          = method_exists($user, 'dateFormat');
                            @endphp
                            @if(Utility::isFilled($expenses))
                                @foreach ($expenses as $expense)
                                    @php
                                        $showBase     = VW::PRJ_EXP . '.show';
                                        $showKebab    = Str::kebab($showBase);
                                        $showResolved = Route::has($showBase) ? $showBase : (Route::has($showKebab) ? $showKebab : null);
                                        $encryptedId  = Crypt::encrypt($expense->id);
                                        $showUrl      = $showResolved ? route($showResolved, $encryptedId) : '#';
                                        $showGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ_EXP, 'show_expense_route_unavailable') ?? 'Show expense route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <tr>
                                        <td class="Id">
                                            <a
                                                href="{{ $showUrl }}"
                                                data-url="{{ $showUrl }}"
                                                data-guard-msg="{{ $showGuardMsg }}"
                                                class="{{ VC::BT_OUTPM }}"
                                            >
                                                {{ $isExpenseNumberFormatAvailable ? $user->expenseNumberFormat($expense->bill_id) : __('Failed to format expense number') }}
                                            </a>
                                        </td>
                                        <td>{{ !empty($expense->category?->name) ? $expense->category->name : __('No category name available for expense ') }}</td>
                                        <td>{{ $isDateFormatAvailable ? $user->dateFormat($expense->bill_date) : __('Failed to format date') }}</td>
                                        <td>
                                            <span class="status_badge badge bg-primary p-2 px-3 rounded">
                                                {{ __(Invoice::$statuses[$expense->status]) }}
                                            </span>
                                        </td>
                                        @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill'))
                                            <td class="Action">
                                                <span>
                                                    @can('show bill')
                                                        <div class="{{ VC::ACT_BTN_INF }}">
                                                            <a
                                                                href="{{ $showUrl }}"
                                                                data-url="{{ $showUrl }}"
                                                                data-guard-msg="{{ $showGuardMsg }}"
                                                                class="{{ VC::BT_SM_CT }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Show') }}"
                                                            >
                                                                <i class="{{ VC::TI_EYE_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('edit bill')
                                                        @php
                                                            $editBase     = VW::PRJ_EXP . '.edit';
                                                            $editKebab    = Str::kebab($editBase);
                                                            $editResolved = Route::has($editBase) ? $editBase : (Route::has($editKebab) ? $editKebab : null);
                                                            $editUrl      = $editResolved ? route($editResolved, $encryptedId) : '#';
                                                            $editGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ_EXP, 'edit_expense_route_unavailable') ?? 'Edit expense route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a
                                                                href="{{ $editUrl }}"
                                                                data-url="{{ $editUrl }}"
                                                                data-guard-msg="{{ $editGuardMsg }}"
                                                                class="{{ VC::BT_SM_CT }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Edit') }}"
                                                            >
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('delete bill')
                                                        @php
                                                            $destroyBase     = VW::PRJ_EXP . '.destroy';
                                                            $destroyKebab    = Str::kebab($destroyBase);
                                                            $destroyResolved = Route::has($destroyBase) ? $destroyBase : (Route::has($destroyKebab) ? $destroyKebab : null);
                                                            $destroyUrl      = $destroyResolved ? route($destroyResolved, $expense->id) : '#';
                                                            $destroyGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ_EXP, 'destroy_expense_route_unavailable') ?? 'Delete expense route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'url'    => $destroyUrl,
                                                                'class'  => 'delete-form-btn',
                                                                'id'     => 'delete-form-'.$expense->id,
                                                                'data-url' => $destroyUrl,
                                                                'data-guard-msg' => $destroyGuardMsg
                                                            ]) !!}
                                                            <a
                                                                href="#"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('delete-form-{{$expense->id}}').submit();"
                                                            >
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5">
                                        <div class="text-center text-muted">{{ __('No expenses found.') }}</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push(ST::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/expenses/index.js') }}"></script>
    @endpush
@endsection
