@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);
        $billCreateRoute      = Route::has(ViewsConstants::BIL . '.create')
            ? route(ViewsConstants::BIL . '.create', 0)
            : '#';
        $billCreateBtnId      = 'bill-create-btn';
        $billCreateGuardMsg   = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::BIL,
            'bill_create_route_unavailable'
        ) ?? 'Bill create route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('bills/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Bills')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/bills/lang/copy.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/bills/copy.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            try {
                $exportRoute      = Route::has(ViewsConstants::BIL . '.export')
                    ? route(ViewsConstants::BIL . '.export')
                    : '#';
                $exportBtnId      = 'bill-export-btn';
                $exportGuardMsg   = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::BIL,
                    'bill_export_route_unavailable'
                ) ?? 'Bill export route is unavailable. Please contact technical support or your domain administrator.';
            } catch (\Throwable $e) {
                \Log::error('bills/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <a
            id="{{ $exportBtnId }}"
            href="{{ $exportRoute }}"
            data-url="{{ $exportRoute }}"
            data-guard-msg="{{ base64_encode($exportGuardMsg) }}"
            class="{{ VC::BT_SM_PM }}"
            data-bs-toggle="tooltip"
            title="{{ __('Export') }}"
        >
            <i class="{{ VC::TI_EXP }}"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer src="{{ asset('assets/js/routes/bills/export.js') }}"></script>
        @endpush
        @can('create bill')
            @php
                try {
                    $billCreateRoute      = Route::has(ViewsConstants::BIL . '.create')
                        ? route(ViewsConstants::BIL . '.create', 0)
                        : '#';
                    $billCreateBtnId      = 'bill-create-btn';
                    $billCreateGuardMsg   = Utility::fetchLinkMessage(
                        $lang,
                        ViewsConstants::BIL,
                        'bill_create_route_unavailable'
                    ) ?? 'Bill create route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('bills/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a
                id="{{ $billCreateBtnId }}"
                href="{{ $billCreateRoute }}"
                data-url="{{ $billCreateRoute }}"
                data-guard-msg="{{ base64_encode($billCreateGuardMsg) }}"
                class="{{ VC::BT_SM_PM }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/routes/bills/create.js') }}"></script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::MT2 }}" id="multiCollapseExample1">
                <div class="card">
                    <div class="{{ VC::CD_BD }}">
                        @php
                            try {
                                $resolvedIndexName = Route::has(ViewsConstants::BIL . '.index') ? (ViewsConstants::BIL . '.index') : (Route::has(Str::kebab(ViewsConstants::BIL . '.index')) ? Str::kebab(ViewsConstants::BIL . '.index') : null);
                                $frmSubmitUrl = $resolvedIndexName ? route($resolvedIndexName) : '#';
                                $frmSubmitFormId = 'frm_submit';
                                $frmSubmitMsg = Utility::fetchLinkMessage($lang, ViewsConstants::BIL, 'bill_index_route_unavailable') ?? 'Bill index route is unavailable. Please contact technical support or your domain administrator.';
                                $statusArray = (is_array($status ?? null)) ? $status : ((($status ?? null) instanceof \Illuminate\Support\Collection && ($status ?? collect())->isNotEmpty()) ? ($status ?? collect())->toArray() : []);
                                $statusOptions = ['' => __('Select Status')] + $statusArray;
                            } catch (\Throwable $e) {
                                \Log::error('bills/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        {{ Form::open([
                            'url'            => $frmSubmitUrl,
                            'method'         => 'GET',
                            'id'             => $frmSubmitFormId,
                            'data-url'       => $frmSubmitUrl,
                            'data-guard-msg' => $frmSubmitMsg,
                        ]) }}
                            <div class="{{ VC::R_ALC_JCE }}">
                                <div class="{{ VC::CXL10 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C3 }}"></div>
                                        <div class="{{ VC::C3 }}"></div>
                                        <div class="{{ VC::CL_XLG4 }} month">
                                            <div class="btn-box">
                                                {{ Form::label('bill_date', __('Bill Date'), ['class' => VC::FM_LB]) }}
                                                {{ Form::text('bill_date', request('bill_date'), ['class' => VC::FM_CT . ' month-btn', 'id' => 'pc-daterangepicker-1', 'readonly' => true]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('status', __('Status'), ['class' => VC::FM_LB]) }}
                                                {{ Form::select('status', $statusOptions, request('status'), ['class' => VC::FM_CT_SL]) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT_FEND }}">
                                    <div class="{{ VC::DFL_JCB }}">
                                        <a href="#" class="{{ VC::BT_SM_PM }}" onclick="document.getElementById('frm_submit').submit(); return false;" data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                            <span class="btn-inner--icon">
                                                <i class="{{ VC::TI_SRC }}"></i>
                                            </span>
                                        </a>
                                        @php
                                            try {
                                                $resetUrl = $frmSubmitUrl;
                                                $resetBtnId = 'bill-reset-btn';
                                                $resetGuardMsg = $frmSubmitMsg;
                                            } catch (\Throwable $e) {
                                                \Log::error('bills/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <a id="{{ $resetBtnId }}" href="{{ $resetUrl }}" data-url="{{ $resetUrl }}" data-guard-msg="{{ base64_encode($resetGuardMsg) }}" class="{{ VC::BT_SM_DG }}" data-bs-toggle="tooltip" title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon">
                                                <i class="{{ VC::TI_TRS_OFF }}"></i>
                                            </span>
                                        </a>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer src="{{ asset('assets/js/routes/bills/resetBtn.js') }}"></script>
                                        @endpush
                                    </div>
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        try {
            $billList = (is_array($bills ?? null) && count($bills ?? [])) || (($bills ?? null) instanceof \Illuminate\Support\Collection && ($bills ?? collect())->isNotEmpty()) ? $bills : [];
            $hasActionCol = Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill');
            $statusClasses = [0 => 'bg-secondary', 1 => 'bg-warning', 2 => 'bg-danger', 3 => 'bg-info', 4 => 'bg-primary'];
        } catch (\Throwable $e) {
            \Log::error('bills/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp

    <div class="row">
        <div class="{{ VC::CM12 }}">
            <div class="card">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Bill') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Bill Date') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if($hasActionCol)
                                        <th width="10%">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($billList as $bill)
                                    @php
                                        try {
                                            $resolvedShowName = Route::has(ViewsConstants::BIL . '.show') ? (ViewsConstants::BIL . '.show') : (Route::has(Str::kebab(ViewsConstants::BIL . '.show')) ? Str::kebab(ViewsConstants::BIL . '.show') : null);
                                            $billShowRoute = $resolvedShowName ? route($resolvedShowName, Crypt::encrypt($bill->id)) : '#';
                                            $billShowLinkId = 'bill-show-' . ($bill->id ?? 'unknown');
                                            $billShowMsg = Utility::fetchLinkMessage($lang, ViewsConstants::BIL, 'bill_show_route_unavailable') ?? 'Bill view route is unavailable. Please contact technical support or your domain administrator.';
                                            $billNumberText = (is_object($user ?? null) && method_exists($user, 'billNumberFormat')) ? (string) ($user->billNumberFormat($bill->bill_id ?? null) ?? '') : (string) ($bill->bill_id ?? '');
                                            $billNumberText = $billNumberText !== '' ? $billNumberText : __('No Bill Identifier available');
                                            $billDateText = (is_object($user ?? null) && method_exists($user, 'dateFormat')) ? (string) ($user->dateFormat($bill->bill_date ?? null) ?? '') : (string) ($bill->bill_date ?? '');
                                            $billDateText = $billDateText !== '' ? $billDateText : __('No bill date available');
                                            $dueDateText = (is_object($user ?? null) && method_exists($user, 'dateFormat')) ? (string) ($user->dateFormat($bill->due_date ?? null) ?? '') : (string) ($bill->due_date ?? '');
                                            $dueDateText = $dueDateText !== '' ? $dueDateText : __('No due date available');
                                            $statusIndex = is_numeric($bill->status ?? null) ? (int) $bill->status : -1;
                                            $statusMap = [];
                                            if (class_exists('\App\Models\Bill') && property_exists('\App\Models\Bill', 'statuses')) {
                                                $statusMap = \App\Models\Bill::$statuses;
                                            } elseif (class_exists('\App\Models\Invoice') && property_exists('\App\Models\Invoice', 'statuses')) {
                                                $statusMap = \App\Models\Invoice::$statuses;
                                            }
                                            $statusLabel = $statusMap[$statusIndex] ?? __('Unknown');
                                            $badgeClass = $statusClasses[$statusIndex] ?? 'bg-secondary';
                                        } catch (\Throwable $e) {
                                            \Log::error('bills/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <tr>
                                        <td class="Id">
                                            <a id="{{ $billShowLinkId }}" href="{{ $billShowRoute }}" class="{{ VC::BT_OUTPM }}" data-url="{{ $billShowRoute }}" data-guard-msg="{{ base64_encode($billShowMsg) }}">
                                                {{ $billNumberText }}
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        const link = document.getElementById("{{ $billShowLinkId }}");
                                                        if (!link || link.getAttribute("data-listener-active") === "true") return;
                                                        link.setAttribute("data-listener-active", "true");
                                                        link.addEventListener("click", event => {
                                                            try {
                                                            const href = link.getAttribute("href");
                                                            const url = link.getAttribute("data-url");
                                                            if ((href && href !== "#") || (url && url !== "#")) return;
                                                            event.preventDefault();
                                                            const msg = link.getAttribute("data-guard-msg") ?? "# ERROR";
                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                            link.setAttribute("data-failed-route", "true");
                                                            } catch (e) {}
                                                        });
                                                    })();
                                                </script>
                                            @endpush
                                        </td>
                                        <td>{{ $bill->category->name ?? __('No category available') }}</td>
                                        <td>{{ $billDateText }}</td>
                                        <td>{{ $dueDateText }}</td>
                                        <td>
                                            <span class="status_badge {{ VC::BDG }} {{ $badgeClass }} p-2 {{ VC::PX3 }} rounded">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        @if($hasActionCol)
                                            <td class="Action">
                                                <span>
                                                    @can('duplicate bill')
                                                        @php
                                                            try {
                                                                $resolvedDuplicateName = Route::has(ViewsConstants::BIL . '.duplicate') ? (ViewsConstants::BIL . '.duplicate') : (Route::has(Str::kebab(ViewsConstants::BIL . '.duplicate')) ? Str::kebab(ViewsConstants::BIL . '.duplicate') : null);
                                                                $duplicateUrl = $resolvedDuplicateName ? route($resolvedDuplicateName, $bill->id) : '#';
                                                                $duplicateBtnId = 'duplicate-btn-' . ($bill->id ?? 'unknown');
                                                                $duplicateFormId = 'duplicate-form-' . ($bill->id ?? 'unknown');
                                                                $duplicateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::BIL, 'bill_duplicate_route_unavailable') ?? 'Bill duplicate route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('bills/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            {!! Form::open(['url' => $duplicateUrl, 'method' => 'get', 'id' => $duplicateFormId, 'data-url' => $duplicateUrl, 'data-guard-msg' => $duplicateGuardMsg]) !!}
                                                                <a id="{{ $duplicateBtnId }}" href="#" class="{{ VC::BT_SM_CT_PR }}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('{{ $duplicateFormId }}').submit();">
                                                                    <i class="{{ VC::TI_COPY_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const btn = document.getElementById("{{ $duplicateBtnId }}");
                                                                    if (!btn || btn.getAttribute("data-listener-active") === "true") return;
                                                                    btn.setAttribute("data-listener-active", "true");
                                                                    btn.addEventListener("click", event => {
                                                                        try {
                                                                        const href = btn.getAttribute("href");
                                                                        const url = btn.getAttribute("data-url");
                                                                        if ((href && href !== "#") || (url && url !== "#")) return;
                                                                        event.preventDefault();
                                                                        const msg = btn.getAttribute("data-guard-msg") ?? "# ERROR";
                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                        btn.setAttribute("data-failed-route", "true");
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endcan
                                                    @can('show bill')
                                                        <div class="{{ VC::ACT_BTN_INF }}">
                                                            <a id="{{ $billShowLinkId }}" href="{{ $billShowRoute }}" data-url="{{ $billShowRoute }}" data-guard-msg="{{ base64_encode($billShowMsg) }}" class="{{ VC::BT_SM_CT }}" data-bs-toggle="tooltip" title="{{ __('Show') }}">
                                                                <i class="{{ VC::TI_EYE_WT }}"></i>
                                                            </a>
                                                        </div>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const btn = document.getElementById("{{ $billShowLinkId }}");
                                                                    if (!btn || btn.getAttribute("data-listener-active") === "true") return;
                                                                    btn.setAttribute("data-listener-active", "true");
                                                                    btn.addEventListener("click", event => {
                                                                        try {
                                                                        const href = btn.getAttribute("href");
                                                                        const url = btn.getAttribute("data-url");
                                                                        if ((href && href !== "#") || (url && url !== "#")) return;
                                                                        event.preventDefault();
                                                                        const msg = btn.getAttribute("data-guard-msg") ?? "# ERROR";
                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                        btn.setAttribute("data-failed-route", "true");
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endcan
                                                    @can('edit bill')
                                                        @php
                                                            try {
                                                                $resolvedEditName = Route::has(ViewsConstants::BIL . '.edit') ? (ViewsConstants::BIL . '.edit') : (Route::has(Str::kebab(ViewsConstants::BIL . '.edit')) ? Str::kebab(ViewsConstants::BIL . '.edit') : null);
                                                                $billEditRoute = $resolvedEditName ? route($resolvedEditName, Crypt::encrypt($bill->id)) : '#';
                                                                $billEditBtnId = 'bill-edit-btn-' . ($bill->id ?? 'unknown');
                                                                $billEditGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::BIL, 'bill_edit_route_unavailable') ?? 'Bill edit route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('bills/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a id="{{ $billEditBtnId }}" href="{{ $billEditRoute }}" data-url="{{ $billEditRoute }}" data-guard-msg="{{ base64_encode($billEditGuardMsg) }}" class="{{ VC::BT_SM_CT }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const btn = document.getElementById("{{ $billEditBtnId }}");
                                                                    if (!btn || btn.getAttribute("data-listener-active") === "true") return;
                                                                    btn.setAttribute("data-listener-active", "true");
                                                                    btn.addEventListener("click", event => {
                                                                        try {
                                                                        const href = btn.getAttribute("href");
                                                                        const url = btn.getAttribute("data-url");
                                                                        if ((href && href !== "#") || (url && url !== "#")) return;
                                                                        event.preventDefault();
                                                                        const msg = btn.getAttribute("data-guard-msg") ?? "# ERROR";
                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                        btn.setAttribute("data-failed-route", "true");
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endcan
                                                    @can('delete bill')
                                                        @php
                                                            try {
                                                                $resolvedDestroyName = Route::has(ViewsConstants::BIL . '.destroy') ? (ViewsConstants::BIL . '.destroy') : (Route::has(Str::kebab(ViewsConstants::BIL . '.destroy')) ? Str::kebab(ViewsConstants::BIL . '.destroy') : null);
                                                                $destroyUrl = $resolvedDestroyName ? route($resolvedDestroyName, $bill->id) : '#';
                                                                $destroyBtnId = 'bill-delete-btn-' . ($bill->id ?? 'unknown');
                                                                $destroyFormId = 'delete-form-' . ($bill->id ?? 'unknown');
                                                                $destroyGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::BIL, 'bill_destroy_route_unavailable') ?? 'Bill destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('bills/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            {!! Form::open(['url' => $destroyUrl, 'method' => 'DELETE', 'id' => $destroyFormId]) !!}
                                                                <a id="{{ $destroyBtnId }}" href="#" class="{{ VC::BT_SM_CT_PR }}" data-url="{{ $destroyUrl }}" data-guard-msg="{{ base64_encode($destroyGuardMsg) }}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('{{ $destroyFormId }}').submit();">
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const btn = document.getElementById("{{ $destroyBtnId }}");
                                                                    if (!btn || btn.getAttribute("data-listener-active") === "true") return;
                                                                    btn.setAttribute("data-listener-active", "true");
                                                                    btn.addEventListener("click", event => {
                                                                        try {
                                                                        const href = btn.getAttribute("href");
                                                                        const url = btn.getAttribute("data-url");
                                                                        if ((href && href !== "#") || (url && url !== "#")) return;
                                                                        event.preventDefault();
                                                                        const msg = btn.getAttribute("data-guard-msg") ?? "# ERROR";
                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                        btn.setAttribute("data-failed-route", "true");
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endcan
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    @php
                                        $colspan = $hasActionCol ? 6 : 5;
@endphp
                                    <tr class="{{ VC::TXCT }}">
                                        <td colspan="{{ $colspan }}">{{ __('No Data Found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
