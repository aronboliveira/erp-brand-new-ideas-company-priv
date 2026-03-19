@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
        $profile = asset(Storage::url('uploads/avatar/'));
    } catch (\Throwable $e) {
        \Log::error('vendors/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('js/routes/vendors/lang/copy.js') }}"></script>
    <script defer src="{{ asset('js/routes/vendors/copy.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Vendors') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Vendor')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            try {
                $vndImportBase = VW::VND.'.file.import';
                $vndImportKebab = Str::kebab($vndImportBase);
                $vndImportResolved = Route::has($vndImportBase) ? $vndImportBase : (Route::has($vndImportKebab) ? $vndImportKebab : null);
                $vndImportUrl = $vndImportResolved ? route($vndImportResolved) : '#';
                $vndExportBase = VW::VND.'.export';
                $vndExportKebab = Str::kebab($vndExportBase);
                $vndExportResolved = Route::has($vndExportBase) ? $vndExportBase : (Route::has($vndExportKebab) ? $vndExportKebab : null);
                $vndExportUrl = $vndExportResolved ? route($vndExportResolved) : '#';
                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $vndImportGuardMsg = Utility::fetchLinkMessage($langValue, VW::VND, 'import_vendor_file_route_unavailable') ?? 'Import vendor file route is unavailable. Please contact technical support or your domain administrator.';
                $vndExportGuardMsg = Utility::fetchLinkMessage($langValue, VW::VND, 'export_vendor_route_unavailable') ?? 'Export vendor route is unavailable. Please contact technical support or your domain administrator.';
                $vndImportAnchorId = 'vendor-import';
                $vndExportAnchorId = 'vendor-export';
            } catch (\Throwable $e) {
                \Log::error('vendors/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <a href="{{ $vndImportUrl }}"
        id="{{ $vndImportAnchorId }}"
        class="{{ VC::BT_SM_PM }}"
        data-url="{{ $vndImportUrl }}"
        data-ajax-popup="true"
        data-bs-toggle="tooltip"
        title="{{ __('Import') }}"
        data-guard-msg="{{ base64_encode($vndImportGuardMsg) }}"
        data-sv-localized="true">
            <i class="{{ VC::TI_IMP }}"></i>
        </a>
        <a href="{{ $vndExportUrl }}"
        id="{{ $vndExportAnchorId }}"
        class="{{ VC::BT_SM_PM }}"
        data-url="{{ $vndExportUrl }}"
        data-bs-toggle="tooltip"
        title="{{ __('Export') }}"
        data-guard-msg="{{ base64_encode($vndExportGuardMsg) }}"
        data-sv-localized="true">
            <i class="{{ VC::TI_EXP }}"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer src="{{ asset('js/routes/vendors/exportImport.js') }}"></script>
        @endpush
        @can('create vendor')
            @php
                try {
                    $vendorCreateBase = VW::VND.'.create';
                    $vendorCreateKebab = Str::kebab($vendorCreateBase);
                    $vendorCreateResolved = Route::has($vendorCreateBase) ? $vendorCreateBase : (Route::has($vendorCreateKebab) ? $vendorCreateKebab : null);
                    $vendorCreateUrl = $vendorCreateResolved ? route($vendorCreateResolved) : '#';
                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                    $vendorCreateGuardMsg = Utility::fetchLinkMessage($langValue, VW::VND, 'create_vendor_route_unavailable') ?? 'Create vendor route is unavailable. Please contact technical support or your domain administrator.';
                    $vendorCreateAnchorId = 'vendor-create-link';
                } catch (\Throwable $e) {
                    \Log::error('vendors/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a href="{{ $vendorCreateUrl }}"
            id="{{ $vendorCreateAnchorId }}"
            data-size="lg"
            data-url="{{ $vendorCreateUrl }}"
            data-ajax-popup="true"
            data-title="{{ __('Create New Vendor') }}"
            data-bs-toggle="tooltip"
            title="{{ __('Create') }}"
            class="{{ VC::BT_SM_PM }}"
            data-guard-msg="{{ base64_encode($vendorCreateGuardMsg) }}"
            data-sv-localized="true">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/routes/vendors/create.js') }}"></script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Balance') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(((is_array($vendors ?? null) && count($vendors) > 0) || (($vendors ?? null) instanceof Collection && ($vendors)->isNotEmpty())))
                                    @foreach ($vendors as $k => $Vendor)
                                        <tr class="cust_tr" id="vend_detail">
                                            <td class="Id">
                                                @can('show vendor')
                                                    @php
                                                        try {
                                                            $vendorShowBase = VW::VND.'.show';
                                                            $vendorShowKebab = Str::kebab($vendorShowBase);
                                                            $vendorIdValue = (string) data_get($Vendor,'id','');
                                                            $encryptedVendorId = $vendorIdValue !== '' ? Crypt::encrypt($vendorIdValue) : null;
                                                            $vendorShowResolved = Route::has($vendorShowBase) ? $vendorShowBase : (Route::has($vendorShowKebab) ? $vendorShowKebab : null);
                                                            $vendorShowUrl = ($vendorShowResolved && $encryptedVendorId) ? route($vendorShowResolved, $encryptedVendorId) : '#';
                                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                            $vendorShowGuardMsg = Utility::fetchLinkMessage($langValue, VW::VND, 'show_vendor_route_unavailable') ?? 'Show vendor route is unavailable. Please contact technical support or your domain administrator.';
                                                            $vendorShowAnchorId = 'vendor-show-link-'.Str::random(8);
                                                            $vendorNumberLabel = !is_null(data_get($Vendor,'vendor_id')) ? ($user?->vendorNumberFormat(data_get($Vendor,'vendor_id')) ?? __('Failed to get vendor number')) : __('No vendor number available');
                                                        } catch (\Throwable $e) {
                                                            \Log::error('vendors/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <a id="{{ $vendorShowAnchorId }}"
                                                    href="{{ $vendorShowUrl }}"
                                                    class="{{ VC::BT_OUTPM }}"
                                                    data-guard-msg="{{ base64_encode($vendorShowGuardMsg) }}"
                                                    data-sv-localized="true">
                                                        {{ $vendorNumberLabel }}
                                                    </a>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                try {
                                                                    const el = document.getElementById('{{ $vendorShowAnchorId }}');
                                                                    if (!el) { return; }
                                                                    if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                    el.setAttribute('data-listener-active','true');
                                                                    el.addEventListener('click',(e) => {
                                                                        try {
                                                                            const href = el.getAttribute('href') ?? '#';
                                                                            if (href !== '#') { return; }
                                                                            e.preventDefault();
                                                                            const msg = el.getAttribute('data-guard-msg') ?? 'Show vendor route is unavailable. Please contact technical support or your domain administrator.';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            el.setAttribute('data-failed-route','true');
                                                                        } catch (err) {}
                                                                    });
                                                                } catch (err) {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                @else
                                                    <a href="#" class="{{ VC::BT_OUTPM }}">
                                                        {{ !is_null(data_get($Vendor,'vendor_id')) ? ($user?->vendorNumberFormat(data_get($Vendor,'vendor_id')) ?? __('Failed to get vendor number')) : __('No vendor number available') }}
                                                    </a>
                                                @endcan
                                            </td>
                                            <td>{{ data_get($Vendor,'name') ?: __('No name available') }}</td>
                                            <td>{{ data_get($Vendor,'contact') ?: __('No contact available') }}</td>
                                            <td>{{ data_get($Vendor,'email') ?: __('No email available') }}</td>
                                            <td>
                                                {{ is_numeric(data_get($Vendor,'balance')) ? ($user?->priceFormat(data_get($Vendor,'balance')) ?? __('Failed to format balance')) : __('No balance available') }}
                                            </td>
                                            <td class="Action">
                                                <span>
                                                    @if ((int) data_get($Vendor,'is_active',0) === 0)
                                                        <i class="fa fa-lock" title="{{ __('Inactive') }}"></i>
                                                    @else
                                                        @can('show vendor')
                                                            @php
                                                                try {
                                                                    $vendorShowBase = VW::VND.'.show';
                                                                    $vendorShowKebab = Str::kebab($vendorShowBase);
                                                                    $vendorIdValue = (string) data_get($Vendor,'id','');
                                                                    $encryptedVendorId = $vendorIdValue !== '' ? Crypt::encrypt($vendorIdValue) : null;
                                                                    $vendorShowResolved = Route::has($vendorShowBase) ? $vendorShowBase : (Route::has($vendorShowKebab) ? $vendorShowKebab : null);
                                                                    $vendorShowUrl = ($vendorShowResolved && $encryptedVendorId) ? route($vendorShowResolved, $encryptedVendorId) : '#';
                                                                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                    $vendorShowGuardMsg = Utility::fetchLinkMessage($langValue, VW::VND, 'show_vendor_route_unavailable') ?? 'Show vendor route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $vendorShowAnchorId = 'vendor-show-btn-'.($vendorIdValue === '' ? 'x' : $vendorIdValue);
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('vendors/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            <div class="{{ VC::ACT_BTN_INF }}">
                                                                <a id="{{ $vendorShowAnchorId }}"
                                                                href="{{ $vendorShowUrl }}"
                                                                class="{{ VC::BT_SM_CT }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('View') }}"
                                                                data-url="{{ $vendorShowUrl }}"
                                                                data-guard-msg="{{ base64_encode($vendorShowGuardMsg) }}"
                                                                data-sv-localized="true">
                                                                    <i class="{{ VC::TI_EYE_WT }}"></i>
                                                                </a>
                                                            </div>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        try {
                                                                            const el = document.getElementById('{{ $vendorShowAnchorId }}');
                                                                            if (!el) { return; }
                                                                            if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                            el.setAttribute('data-listener-active','true');
                                                                            el.addEventListener('click',(e) => {
                                                                                try {
                                                                                    const href = el.getAttribute('href') ?? '#';
                                                                                    const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                    if (url !== '#' && href !== '#') { return; }
                                                                                    e.preventDefault();
                                                                                    const msg = el.getAttribute('data-guard-msg') ?? 'Show vendor route is unavailable. Please contact technical support or your domain administrator.';
                                                                                    (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                    el.setAttribute('data-failed-route','true');
                                                                                } catch (err) {}
                                                                            });
                                                                        } catch (err) {}
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        @endcan
                                                        @can('edit vendor')
                                                            @php
                                                                try {
                                                                    $vendorEditBase = VW::VND.'.edit';
                                                                    $vendorEditKebab = Str::kebab($vendorEditBase);
                                                                    $vendorIdValue = (string) data_get($Vendor,'id','');
                                                                    $vendorEditResolved = Route::has($vendorEditBase) ? $vendorEditBase : (Route::has($vendorEditKebab) ? $vendorEditKebab : null);
                                                                    $vendorEditUrl = ($vendorEditResolved && $vendorIdValue !== '') ? route($vendorEditResolved, $vendorIdValue) : '#';
                                                                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                    $vendorEditGuardMsg = Utility::fetchLinkMessage($langValue, VW::VND, 'edit_vendor_route_unavailable') ?? 'Edit vendor route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $vendorEditAnchorId = 'vendor-edit-'.($vendorIdValue === '' ? 'x' : $vendorIdValue);
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('vendors/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            <a href="{{ $vendorEditUrl }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            id="{{ $vendorEditAnchorId }}"
                                                            data-size="lg"
                                                            data-title="{{ __('Edit Vendor') }}"
                                                            data-url="{{ $vendorEditUrl }}"
                                                            data-ajax-popup="true"
                                                            title="{{ __('Edit') }}"
                                                            data-bs-toggle="tooltip"
                                                            data-original-title="{{ __('Edit') }}"
                                                            data-guard-msg="{{ base64_encode($vendorEditGuardMsg) }}"
                                                            data-sv-localized="true">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        try {
                                                                            const el = document.getElementById('{{ $vendorEditAnchorId }}');
                                                                            if (!el) { return; }
                                                                            if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                            el.setAttribute('data-listener-active','true');
                                                                            el.addEventListener('click',(e) => {
                                                                                try {
                                                                                    const href = el.getAttribute('href') ?? '#';
                                                                                    const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                    if (url !== '#' && href !== '#') { return; }
                                                                                    e.preventDefault();
                                                                                    const msg = el.getAttribute('data-guard-msg') ?? 'Edit vendor route is unavailable. Please contact technical support or your domain administrator.';
                                                                                    (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                    el.setAttribute('data-failed-route','true');
                                                                                } catch (err) {}
                                                                            });
                                                                        } catch (err) {}
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        @endcan
                                                        @can('delete vendor')
                                                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                @php
                                                                    try {
                                                                        $vndDestroyBase = VW::VND.'.destroy';
                                                                        $vndDestroyKebab = Str::kebab($vndDestroyBase);
                                                                        $vendorIdValue = (string) data_get($Vendor,'id','');
                                                                        $vndDestroyResolved = Route::has($vndDestroyBase) ? $vndDestroyBase : (Route::has($vndDestroyKebab) ? $vndDestroyKebab : null);
                                                                        $vndDestroyUrl = ($vndDestroyResolved && $vendorIdValue !== '') ? route($vndDestroyResolved, $vendorIdValue) : '#';
                                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                        $vndDestroyGuardMsg = Utility::fetchLinkMessage($langValue, VW::VND, 'delete_vendor_route_unavailable') ?? 'Delete vendor route is unavailable. Please contact technical support or your domain administrator.';
                                                                        $delFormId = 'delete-form-'.($vendorIdValue === '' ? 'x' : $vendorIdValue);
                                                                        $delAnchorId = 'delete-vendor-'.($vendorIdValue === '' ? 'x' : $vendorIdValue);
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('vendors/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'url' => $vndDestroyUrl,
                                                                    'id' => $delFormId,
                                                                    'data-url' => $vndDestroyUrl,
                                                                    'data-guard-msg' => $vndDestroyGuardMsg,
                                                                    'data-sv-localized' => 'true',
                                                                ]) !!}
                                                                    <a href="#"
                                                                    id="{{ $delAnchorId }}"
                                                                    class="{{ VC::BT_SM_CT_PR }}"
                                                                    data-bs-toggle="tooltip"
                                                                    data-original-title="{{ __('Delete') }}"
                                                                    title="{{ __('Delete') }}"
                                                                    data-confirm="{{ __(Utility::fetchLinkMessage($langValue, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($langValue, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();"
                                                                    data-form-id="{{ $delFormId }}"
                                                                    data-guard-msg="{{ base64_encode($vndDestroyGuardMsg) }}"
                                                                    data-sv-localized="true">
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            try {
                                                                                const a = document.getElementById('{{ $delAnchorId }}');
                                                                                const f = document.getElementById('{{ $delFormId }}');
                                                                                if (!a || !f) { return; }
                                                                                if (a.getAttribute('data-listener-active') === 'true') { return; }
                                                                                a.setAttribute('data-listener-active','true');
                                                                                a.addEventListener('click',(e) => {
                                                                                    try {
                                                                                        const fid = a.getAttribute('data-form-id') ?? '';
                                                                                        if (!fid) { return; }
                                                                                        const fm = document.getElementById(fid);
                                                                                        if (!fm) { return; }
                                                                                        const action = fm.getAttribute('action') ?? '#';
                                                                                        const url = fm.getAttribute('data-url') ?? action ?? '#';
                                                                                        if (url !== '#' && action !== '#') { return; }
                                                                                        e.preventDefault();
                                                                                        const msg = a.getAttribute('data-guard-msg') ?? 'Destroy vendor route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                        a.setAttribute('data-failed-route','true');
                                                                                        fm.setAttribute('data-failed-route','true');
                                                                                    } catch (err) {}
                                                                                });
                                                                                if (f.getAttribute('data-submit-guarded') !== 'true') {
                                                                                    f.setAttribute('data-submit-guarded','true');
                                                                                    f.addEventListener('submit',(e) => {
                                                                                        try {
                                                                                            const action = f.getAttribute('action') ?? '#';
                                                                                            const url = f.getAttribute('data-url') ?? action ?? '#';
                                                                                            if (url !== '#' && action !== '#') { return; }
                                                                                            e.preventDefault();
                                                                                            const msg = f.getAttribute('data-guard-msg') ?? 'Destroy vendor route is unavailable. Please contact technical support or your domain administrator.';
                                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                            f.setAttribute('data-failed-route','true');
                                                                                        } catch (err) {}
                                                                                    });
                                                                                }
                                                                            } catch (err) {}
                                                                        })();
                                                                    </script>
                                                                @endpush
                                                            </div>
                                                        @endcan
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="{{ VC::TXCT }}">{{ __('No vendors found.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
