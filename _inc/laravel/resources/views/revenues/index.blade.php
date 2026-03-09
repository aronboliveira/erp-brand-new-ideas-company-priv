@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
    } catch (\Throwable $e) {
        \Log::error('revenues/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Revenues')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Revenue')}}</li>
@endsection

{{--        <a class="{{ VC::BT_SM_PM }}" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
{{--            <i class="ti ti-filter"></i>--}}
{{--        </a>--}}
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create revenue')
            @php
                try {
                    $rvnCreateBase = VW::RVN.'.create';
                    $rvnCreateKebab = Str::kebab($rvnCreateBase);
                    $rvnCreateResolved = Route::has($rvnCreateBase) ? $rvnCreateBase : (Route::has($rvnCreateKebab) ? $rvnCreateKebab : null);
                    $rvnCreateUrl = $rvnCreateResolved ? route($rvnCreateResolved) : '#';
                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                    $rvnCreateGuardMsg = Utility::fetchLinkMessage($langValue, VW::RVN, 'create_revenue_route_unavailable') ?? 'Create revenue route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('revenues/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a
                href="{{ $rvnCreateUrl }}"
                data-size="lg"
                data-url="{{ $rvnCreateUrl }}"
                data-ajax-popup="true"
                data-title="{{ __('Create New Revenue') }}"
                class="{{ VC::BT_SM_PM }} revenue-create"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                data-guard-msg="{{ base64_encode($rvnCreateGuardMsg) }}"
                data-sv-localized="true"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script src="{{ asset('assets/js/routes/revenues/create.js') }}" defer></script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::MT2 }}" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        @php
                            try {
                                $rvnIndexBase = VW::RVN.'.index';
                                $rvnIndexKebab = Str::kebab($rvnIndexBase);
                                $rvnIndexResolved = Route::has($rvnIndexBase) ? $rvnIndexBase : (Route::has($rvnIndexKebab) ? $rvnIndexKebab : null);
                                $rvnIndexUrl = $rvnIndexResolved ? route($rvnIndexResolved) : '#';
                                $rvnFormId = 'revenue_form';
                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                $applyGuardMsg = Utility::fetchLinkMessage($langValue, VW::RVN, 'apply_revenue_route_unavailable') ?? 'Apply revenue route is unavailable. Please contact technical support or your domain administrator.';
                                $resetGuardMsg = Utility::fetchLinkMessage($langValue, VW::RVN, 'reset_revenue_route_unavailable') ?? 'Reset revenue route is unavailable. Please contact technical support or your domain administrator.';
                            } catch (\Throwable $e) {
                                \Log::error('revenues/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        {{ Form::open(['method' => 'GET', 'url' => $rvnIndexUrl, 'id' => $rvnFormId, 'data-url' => $rvnIndexUrl, 'data-guard-msg' => $applyGuardMsg, 'data-sv-localized' => 'true']) }}
                            <div class="{{ VC::R_ALC_JCE }}">
                                <div class="{{ VC::CXL10 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C3 }}">
                                            {{ Form::label('date', __('Date'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('date', $_GET['date'] ?? null, ['class' => 'month-btn ' . VC::FM_CT, 'id' => 'pc-daterangepicker-1', 'readonly']) }}
                                        </div>
                                        <div class="{{ VC::CL_XL3 }} month">
                                            <div class="btn-box">
                                                {{ Form::label('account', __('Account'), ['class' => VC::FM_LB]) }}
                                                {{ Form::select('account', ($account instanceof Collection ? $account->toArray() : (is_array($account) ? $account : ['' => __('No accounts available')])), $_GET['account'] ?? '', ['class' => VC::FM_CT_SL]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XL3 }} date">
                                            <div class="btn-box">
                                                {{ Form::label('customer', __('Customer'), ['class' => VC::FM_LB]) }}
                                                {{ Form::select('customer', ($customer instanceof Collection ? $customer->toArray() : (is_array($customer) ? $customer : ['' => __('No customers available')])), $_GET['customer'] ?? '', ['class' => VC::FM_CT_SL]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {{ Form::label('category', __('Category'), ['class' => VC::FM_LB]) }}
                                                {{ Form::select('category', ($category instanceof Collection ? $category->toArray() : (is_array($category) ? $category : ['' => __('No categories available')])), $_GET['category'] ?? '', ['class' => VC::FM_CT_SL]) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }}">
                                            <a href="#"
                                            class="{{ VC::BT_SM_PM }} apply-revenue"
                                            data-form-id="{{ $rvnFormId }}"
                                            data-guard-msg="{{ base64_encode($applyGuardMsg) }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a href="{{ $rvnIndexUrl }}"
                                            class="{{ VC::BT_SM_DG }} reset-revenue"
                                            data-url="{{ $rvnIndexUrl }}"
                                            data-guard-msg="{{ base64_encode($resetGuardMsg) }}"
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
                            <script src="{{ asset('assets/js/routes/revenues/apply.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/revenues/reset.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }} {{ VC::MT2 }}">
                    <h5></h5>
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Account') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Reference') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Payment Receipt') }}</th>
                                @if(Gate::check('edit revenue') || Gate::check('delete revenue'))
                                    <th width="10%">{{ __('Action') }}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @php
                            	$revenuePath = Utility::getFile('uploads/revenue') ?? '';
@endphp
                            @forelse(($revenues ?? []) as $revenue)
                                <tr class="font-style">
                                    <td>{{ $user?->dateFormat(data_get($revenue,'date')) ?? __('Failed to get date') }}</td>
                                    <td>{{ $user?->priceFormat((float)(data_get($revenue,'amount') ?? 0)) ?? __('Failed to get amount') }}</td>
                                    <td>{{ (data_get($revenue,'bankAccount.bank_name') || data_get($revenue,'bankAccount.holder_name')) ? trim((data_get($revenue,'bankAccount.bank_name','').' '.data_get($revenue,'bankAccount.holder_name',''))) : __('No account available') }}</td>
                                    <td>{{ data_get($revenue,'customer.name') ?: __('No customer available') }}</td>
                                    <td>{{ data_get($revenue,'category.name') ?: __('No category available') }}</td>
                                    <td>{{ data_get($revenue,'reference') ?: __('No reference available') }}</td>
                                    <td>{{ data_get($revenue,'description') ?: __('No description available') }}</td>
                                    <td>
                                        @php
                                        	$receipt = data_get($revenue,'add_receipt');
@endphp
                                        @if(!empty($receipt) && !empty($revenuePath))
                                            <div class="{{ VC::ACT_BTN_PRIM }}">
                                                <a class="{{ VC::BT_SM_CT }}" href="{{ $revenuePath . '/' . $receipt }}" download>
                                                    <i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }}"></i>
                                                </a>
                                            </div>
                                            @php
                                                try {
                                                    $fileName = isset($receipt) && is_string($receipt) ? $receipt : null;
                                                    $basePath = isset($revenuePath) && is_string($revenuePath) ? rtrim($revenuePath, '/\\') : null;
                                                    $downloadUrl = ($basePath && $fileName) ? ($basePath . '/' . $fileName) : '#';
                                                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                    $downloadGuardMsg = Utility::fetchLinkMessage($langValue, VW::RVN, 'download_revenue_receipt_unavailable') ?? 'Download revenue receipt route is unavailable. Please contact technical support or your domain administrator.';
                                                    $anchorId = 'revenue-receipt-download-'.($fileName ? substr(md5($fileName), 0, 8) : 'x');
                                                } catch (\Throwable $e) {
                                                    \Log::error('revenues/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <a
                                                id="{{ $anchorId }}"
                                                href="{{ $downloadUrl }}"
                                                class="action-btn bg-secondary ms-2 {{ VC::BT_SM_CT }}"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Download') }}"
                                                target="_blank"
                                                rel="noopener"
                                                data-url="{{ $downloadUrl }}"
                                                data-guard-msg="{{ base64_encode($downloadGuardMsg) }}"
                                                data-sv-localized="true"
                                            >
                                                <span class="btn-inner--icon">
                                                    <i class="ti ti-crosshair {{ VC::TXT_WT }}"></i>
                                                </span>
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        try {
                                                            const el = document.getElementById('{{ $anchorId }}');
                                                            if (!el) { return; }
                                                            if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                            el.setAttribute('data-listener-active', 'true');
                                                            el.addEventListener('click', (e) => {
                                                                try {
                                                                    const href = el.getAttribute('href') ?? '#';
                                                                    const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                    if (url !== '#' && href !== '#') { return; }
                                                                    e.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? 'Download revenue receipt route is unavailable. Please contact technical support or your domain administrator.';
                                                                    (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                } catch (err) {}
                                                            });
                                                        } catch (err) {}
                                                    })();
                                                </script>
                                            @endpush
                                        @else
                                            {{ __('No payment receipt available') }}
                                        @endif
                                    </td>
                                    @if(Gate::check('edit revenue') || Gate::check('delete revenue'))
                                        <td class="Action">
                                            <span>
                                                @can('edit revenue')
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        @php
                                                            try {
                                                                $rvnEditBase = VW::RVN.'.edit';
                                                                $rvnEditKebab = Str::kebab($rvnEditBase);
                                                                $rvnEditResolved = Route::has($rvnEditBase) ? $rvnEditBase : (Route::has($rvnEditKebab) ? $rvnEditKebab : null);
                                                                $rvnIdValue = data_get($revenue, 'id');
                                                                $rvnEncryptedId = $rvnIdValue ? Crypt::encrypt($rvnIdValue) : null;
                                                                $rvnEditUrl = ($rvnEditResolved && $rvnEncryptedId) ? route($rvnEditResolved, $rvnEncryptedId) : '#';
                                                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                $rvnEditGuardMsg = Utility::fetchLinkMessage($langValue, VW::RVN, 'edit_revenue_route_unavailable') ?? 'Edit revenue route is unavailable. Please contact technical support or your domain administrator.';
                                                                $anchorId = 'revenue-edit-btn-'.Str::uuid();
                                                            } catch (\Throwable $e) {
                                                                \Log::error('revenues/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <a
                                                            id="{{ $anchorId }}"
                                                            href="{{ $rvnEditUrl }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-ajax-popup="true"
                                                            data-size="lg"
                                                            data-title="{{ __('Edit') }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                            aria-label="{{ __('Edit Revenue') }}"
                                                            data-url="{{ $rvnEditUrl }}"
                                                            data-guard-msg="{{ base64_encode($rvnEditGuardMsg) }}"
                                                            data-sv-localized="true"
                                                        >
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    try {
                                                                        const el = document.getElementById('{{ $anchorId }}');
                                                                        if (!el) { return; }
                                                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                        el.setAttribute('data-listener-active', 'true');
                                                                        el.addEventListener('click', (e) => {
                                                                            try {
                                                                                const href = el.getAttribute('href') ?? '#';
                                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                if (url !== '#' && href !== '#') { return; }
                                                                                e.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Edit revenue route is unavailable. Please contact technical support or your domain administrator.';
                                                                                (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            } catch (err) {}
                                                                        });
                                                                    } catch (err) {}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endcan
                                                @can('delete revenue')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        @php
                                                            try {
                                                                $rvnDestroyBase = VW::RVN.'.destroy';
                                                                $rvnDestroyKebab = Str::kebab($rvnDestroyBase);
                                                                $rvnDestroyResolved = Route::has($rvnDestroyBase) ? $rvnDestroyBase : (Route::has($rvnDestroyKebab) ? $rvnDestroyKebab : null);
                                                                $rvnIdValue = data_get($revenue, 'id');
                                                                $rvnEncryptedId = $rvnIdValue ? Crypt::encrypt($rvnIdValue) : null;
                                                                $rvnDestroyUrl = ($rvnDestroyResolved && $rvnEncryptedId) ? route($rvnDestroyResolved, $rvnEncryptedId) : '#';
                                                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                $deleteGuardMsg = Utility::fetchLinkMessage($langValue, VW::RVN, 'delete_revenue_route_unavailable') ?? 'Delete revenue route is unavailable. Please contact technical support or your domain administrator.';
                                                                $confirmTitle = __(Utility::fetchLinkMessage($langValue, 'generics', 'are_you_sure') ?? 'Are You Sure?');
                                                                $confirmBody = __(Utility::fetchLinkMessage($langValue, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                                                $formId = 'delete-revenue-form-'.Str::uuid();
                                                                $anchorId = 'revenue-delete-btn-'.Str::uuid();
                                                            } catch (\Throwable $e) {
                                                                \Log::error('revenues/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        {!! Form::open(['method' => 'DELETE', 'url' => $rvnDestroyUrl, 'id' => $formId, 'class' => 'd-inline']) !!}
                                                            <a
                                                                id="{{ $anchorId }}"
                                                                href="#"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                data-original-title="{{ __('Delete') }}"
                                                                data-confirm="{{ $confirmTitle }}|{{ $confirmBody }}"
                                                                data-confirm-yes="document.getElementById('{{ $formId }}').submit();"
                                                                data-url="{{ $rvnDestroyUrl }}"
                                                                data-guard-msg="{{ base64_encode($deleteGuardMsg) }}"
                                                                data-sv-localized="true"
                                                            >
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    try {
                                                                        const el = document.getElementById('{{ $anchorId }}');
                                                                        if (!el) { return; }
                                                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                        el.setAttribute('data-listener-active', 'true');
                                                                        el.addEventListener('click', (e) => {
                                                                            try {
                                                                                const href = el.getAttribute('href') ?? '#';
                                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                const form = document.getElementById('{{ $formId }}');
                                                                                const action = form ? (form.getAttribute('action') ?? '#') : '#';
                                                                                if (url !== '#' && href !== '#' && action !== '#') { return; }
                                                                                e.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Delete revenue route is unavailable. Please contact technical support or your domain administrator.';
                                                                                (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                                if (form) { form.setAttribute('data-failed-route', 'true'); }
                                                                            } catch (err) {}
                                                                        });
                                                                    } catch (err) {}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endcan
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="{{ VC::TXCT_MT }}">{{ __('No revenues available') }}</td>
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

                                            {{--                                        @if(!empty($revenue->add_receipt))--}}
                                            {{--                                            <a href="{{asset(Storage::url('uploads/revenue')).'/'.$revenue->add_receipt}}" download="" class="{{ VC::ACT_BTN_PRIM }} {{ VC::BT_SM_CT }}" data-bs-toggle="tooltip" title="{{__('Download')}}" target="_blank"><span class="btn-inner--icon"><i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }}" ></i></span></a>--}}

                                            {{--                                            <div class="{{ VC::ACT_BTN }} bg-secondary">--}}
                                            {{--                                                <a class="{{ VC::BT_SM_CT }}" href="{{asset(Storage::url('uploads/revenue')).'/'.$revenue->add_receipt}}" target="_blank"  >--}}
                                            {{--                                                    <i class="ti ti-crosshair {{ VC::TXT_WT }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Preview') }}"></i>--}}
                                            {{--                                                </a>--}}
                                            {{--                                            </div>--}}
                                            {{--                                        @else--}}
                                            {{--                                            ---}}
                                            {{--                                        @endif--}}
