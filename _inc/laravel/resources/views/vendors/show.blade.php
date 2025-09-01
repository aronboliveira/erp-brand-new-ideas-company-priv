@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW
    };
    use App\Models\Utility;
    use Illuminate\Support\{Facades\Auth, Facades\Gate, Facades\Route, Facades\Crypt, Str};
    use Illuminate\Support\Collection;

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);

    $dashResolved   = Route::has('dashboard') ? 'dashboard' : (Route::has(Str::kebab('dashboard')) ? Str::kebab('dashboard') : null);
    $dashUrl        = $dashResolved ? route($dashResolved) : '#';

    $vndIndexBase   = VW::VND . '.index';
    $vndIndexKebab  = Str::kebab($vndIndexBase);
    $vndIndexName   = Route::has($vndIndexBase) ? $vndIndexBase : (Route::has($vndIndexKebab) ? $vndIndexKebab : null);
    $vndIndexUrl    = $vndIndexName ? route($vndIndexName) : '#';
    $vndIndexGuard  = Utility::fetchLinkMessage($lang, VW::VND, 'index_vendor_route_unavailable') ?? 'Vendor index route is unavailable. Please contact technical support or your domain administrator.';
    $vndIndexId     = 'vendor-index-link';

    $vId            = (string) (data_get($vendor ?? null, 'id') ?? '');
    $vName          = (string) (data_get($vendor ?? null, 'name') ?? __('No vendor name available'));
    $vEmail         = (string) (data_get($vendor ?? null, 'email') ?? __('No vendor email available'));
    $vContact       = (string) (data_get($vendor ?? null, 'contact') ?? __('No vendor contact available'));

    $bName          = (string) (data_get($vendor ?? null, 'billing_name') ?? __('No billing name available'));
    $bAddr          = (string) (data_get($vendor ?? null, 'billing_address') ?? __('No billing address available'));
    $bCity          = (string) (data_get($vendor ?? null, 'billing_city') ?? '');
    $bState         = (string) (data_get($vendor ?? null, 'billing_state') ?? '');
    $bZip           = (string) (data_get($vendor ?? null, 'billing_zip') ?? '');
    $bCountry       = (string) (data_get($vendor ?? null, 'billing_country') ?? __('No billing country available'));
    $bPhone         = (string) (data_get($vendor ?? null, 'billing_phone') ?? __('No billing phone available'));
    $bLine          = trim(collect([$bCity, $bState, $bZip])->filter(fn($v) => ($v ?? '') !== '')->implode(', '));
    $bLine          = $bLine !== '' ? $bLine : __('No billing region available');

    $sName          = (string) (data_get($vendor ?? null, 'shipping_name') ?? __('No shipping name available'));
    $sAddr          = (string) (data_get($vendor ?? null, 'shipping_address') ?? __('No shipping address available'));
    $sCity          = (string) (data_get($vendor ?? null, 'shipping_city') ?? '');
    $sState         = (string) (data_get($vendor ?? null, 'shipping_state') ?? '');
    $sZip           = (string) (data_get($vendor ?? null, 'shipping_zip') ?? '');
    $sCountry       = (string) (data_get($vendor ?? null, 'shipping_country') ?? __('No shipping country available'));
    $sPhone         = (string) (data_get($vendor ?? null, 'shipping_phone') ?? __('No shipping phone available'));
    $sLine          = trim(collect([$sCity, $sState, $sZip])->filter(fn($v) => ($v ?? '') !== '')->implode(', '));
    $sLine          = $sLine !== '' ? $sLine : __('No shipping region available');

    $billsRaw       = (is_object($vendor ?? null) && method_exists($vendor, 'vendorBill')) ? $vendor->vendorBill($vId) : [];
    $bills          = ($billsRaw instanceof Collection) ? $billsRaw : collect(is_array($billsRaw) ? $billsRaw : []);
@endphp

@extends(EL::ADM)

@push(ST::ADM_SCR_PG)
@endpush

@section(YW::ADM_PG_TTL)
    {{ __('Manage Vendor-Detail') }}
@endsection

@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashUrl }}" {{ $dashUrl === '#' ? 'aria-disabled=true' : '' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a id="{{ $vndIndexId }}"
           href="{{ $vndIndexUrl }}"
           data-url="{{ $vndIndexUrl }}"
           data-guard-msg="{{ $vndIndexGuard }}"
           data-sv-localized="true"
           {{ $vndIndexUrl === '#' ? 'aria-disabled=true' : '' }}>
            {{ __('Vendor') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ $vName }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create bill')
            @php
                $crBase  = VW::BIL . '.create';
                $crKebab = Str::kebab($crBase);
                $crName  = Route::has($crBase) ? $crBase : (Route::has($crKebab) ? $crKebab : null);
                $crUrl   = ($crName && $vId !== '') ? route($crName, [$vId]) : '#';
                $crMsg   = Utility::fetchLinkMessage($lang, VW::BIL, 'bill_create_route_unavailable') ?? 'Create bill route is unavailable. Please contact technical support or your domain administrator.';
                $crId    = 'bill-create-link-' . $vId;
            @endphp
            <a id="{{ $crId }}"
               href="{{ $crUrl }}"
               data-url="{{ $crUrl }}"
               data-guard-msg="{{ $crMsg }}"
               data-sv-localized="true"
               class="{{ VC::BT_SM_PM }}">
                {{ __('Create Bill') }}
            </a>
            @push(ST::ADM_SCR_PG)
                <script>
                    (() => {
                        try {
                            const a = document.getElementById(@json($crId));
                            if (!a || a.getAttribute('data-listener-active') === 'true') return;
                            a.setAttribute('data-listener-active', 'true');
                            const url = a.getAttribute('data-url') ?? '#';
                            if ((a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') a.setAttribute('href', url);
                            a.addEventListener('click', (e) => {
                                const href = a.getAttribute('href') ?? '#';
                                if (href && href !== '#') return;
                                e.preventDefault();
                                const msg = a.getAttribute('data-guard-msg') || 'Create bill route is unavailable. Please contact technical support or your domain administrator.';
                                let c = document.getElementById('toast-container');
                                if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                const ok = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                                if (ok) {
                                    const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                    const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                    t.appendChild(b); c.appendChild(t);
                                    try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                } else { alert(msg); }
                                a.setAttribute('data-failed-route', 'true');
                            });
                        } catch {}
                    })();
                </script>
            @endpush
        @endcan
        @can('edit vendor')
            @php
                $edBase  = VW::VND . '.edit';
                $edKebab = Str::kebab($edBase);
                $edName  = Route::has($edBase) ? $edBase : (Route::has($edKebab) ? $edKebab : null);
                $edUrl   = ($edName && $vId !== '') ? route($edName, [$vId]) : '#';
                $edMsg   = Utility::fetchLinkMessage($lang, VW::VND, 'edit_vendor_route_unavailable') ?? 'Edit vendor route is unavailable. Please contact technical support or your domain administrator.';
                $edId    = 'vendor-edit-link-' . $vId;
            @endphp
            <a id="{{ $edId }}"
               href="{{ $edUrl }}"
               data-url="{{ $edUrl }}"
               data-size="xl"
               data-ajax-popup="true"
               data-guard-msg="{{ $edMsg }}"
               data-sv-localized="true"
               title="{{ __('Edit') }}"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PC_WT }}"></i>
            </a>
            @push(ST::ADM_SCR_PG)
                <script>
                    (() => {
                        try {
                            const a = document.getElementById(@json($edId));
                            if (!a || a.getAttribute('data-listener-active') === 'true') return;
                            a.setAttribute('data-listener-active', 'true');
                            const url = a.getAttribute('data-url') ?? '#';
                            if ((a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') a.setAttribute('href', url);
                            a.addEventListener('click', (e) => {
                                const href = a.getAttribute('href') ?? '#';
                                if (href && href !== '#') return;
                                e.preventDefault();
                                const msg = a.getAttribute('data-guard-msg') || 'Edit vendor route is unavailable. Please contact technical support or your domain administrator.';
                                let c = document.getElementById('toast-container');
                                if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                const ok = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                                if (ok) {
                                    const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                    const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                    t.appendChild(b); c.appendChild(t);
                                    try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                } else { alert(msg); }
                                a.setAttribute('data-failed-route', 'true');
                            });
                        } catch {}
                    })();
                </script>
            @endpush
        @endcan
        @can('delete vendor')
            @php
                $dvBase  = VW::VND . '.destroy';
                $dvKebab = Str::kebab($dvBase);
                $dvName  = Route::has($dvBase) ? $dvBase : (Route::has($dvKebab) ? $dvKebab : null);
                $dvUrl   = ($dvName && $vId !== '') ? route($dvName, [$vId]) : '#';
                $dvMsg   = Utility::fetchLinkMessage($lang, VW::VND, 'delete_vendor_route_unavailable') ?? 'Delete vendor route is unavailable. Please contact technical support or your domain administrator.';
                $dvForm  = 'vendor-delete-form-' . $vId;
            @endphp
            {!! Collective\Html\FormFacade::open([
                'method'               => 'DELETE',
                'url'                  => $dvUrl,
                'id'                   => $dvForm,
                'class'                => 'delete-form-btn',
                'data-resolved-action' => $dvUrl,
                'data-guard-msg'       => $dvMsg,
                'data-sv-localized'    => 'true',
            ]) !!}
                <a href="#"
                   class="{{ VC::BT_SM_DG }} bs-pass-para"
                   data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                   data-confirm-yes="document.getElementById('{{ $dvForm }}').submit();">
                    <i class="{{ VC::TI_TRS_WT }}"></i>
                </a>
            {!! Collective\Html\FormFacade::close() !!}
            @push(ST::ADM_SCR_PG)
                <script>
                    (() => {
                        try {
                            const f = document.getElementById(@json($dvForm));
                            if (!f || f.getAttribute('data-listener-active') === 'true') return;
                            f.setAttribute('data-listener-active', 'true');
                            const resolved = f.getAttribute('data-resolved-action') || '#';
                            if ((f.getAttribute('action') === '#' || !f.getAttribute('action')) && resolved !== '#') f.setAttribute('action', resolved);
                            f.addEventListener('submit', (e) => {
                                const action = f.getAttribute('action') || '#';
                                if (action && action !== '#') return;
                                e.preventDefault();
                                const msg = f.getAttribute('data-guard-msg') || 'Delete vendor route is unavailable. Please contact technical support or your domain administrator.';
                                let c = document.getElementById('toast-container');
                                if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                const ok = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                                if (ok) {
                                    const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                    const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                    t.appendChild(b); c.appendChild(t);
                                    try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                } else { alert(msg); }
                                f.setAttribute('data-failed-route', 'true');
                            });
                        } catch {}
                    })();
                </script>
            @endpush
        @endcan
    </div>
@endsection

@section(YW::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM4 }}">
            <div class="{{ VC::CD }} pb-0 customer-detail-box vendor_card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Vendor Info') }}</h5>
                    <p class="card-text">{{ $vName }}</p>
                    <p class="card-text">{{ $vEmail }}</p>
                    <p class="card-text">{{ $vContact }}</p>
                </div>
            </div>
        </div>
        <div class="{{ VC::CM4 }}">
            <div class="{{ VC::CD }} pb-0 customer-detail-box vendor_card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Billing Info') }}</h5>
                    <p class="card-text">{{ $bName }}</p>
                    <p class="card-text">{{ $bAddr }}</p>
                    <p class="card-text">{{ $bLine }}</p>
                    <p class="card-text">{{ $bCountry }}</p>
                    <p class="card-text">{{ $bPhone }}</p>
                </div>
            </div>
        </div>
        <div class="{{ VC::CM4 }}">
            <div class="{{ VC::CD }} pb-0 customer-detail-box vendor_card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Shipping Info') }}</h5>
                    <p class="card-text">{{ $sName }}</p>
                    <p class="card-text">{{ $sAddr }}</p>
                    <p class="card-text">{{ $sLine }}</p>
                    <p class="card-text">{{ $sCountry }}</p>
                    <p class="card-text">{{ $sPhone }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="{{ VC::RW }} {{ VC::MT4 }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <h5 class="{{ VC::MB4 }}">{{ __('Bills') }}</h5>
                    <div class="table-responsive">
                        <table class="{{ VC::TB }}">
                            <thead>
                                <tr>
                                    <th>{{ __('Bill') }}</th>
                                    <th>{{ __('Bill Date') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Due Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill') || Gate::check('duplicate bill'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bills as $bill)
                                    @php
                                        $bid         = (string) (data_get($bill, 'id') ?? '');
                                        $billIdCode  = (string) (data_get($bill, 'bill_id') ?? '');
                                        $billDate    = (string) (data_get($bill, 'bill_date') ?? '');
                                        $dueDate     = (string) (data_get($bill, 'due_date') ?? '');
                                        $dueAmount   = (is_object($bill) && method_exists($bill, 'getDue')) ? $bill->getDue() : null;

                                        $statusIdx   = (int) (data_get($bill, 'status') ?? 0);
                                        $statusesArr = \App\Models\Invoice::$statuses ?? [];
                                        $statusLbl   = is_array($statusesArr) && array_key_exists($statusIdx, $statusesArr) ? __($statusesArr[$statusIdx]) : __('Unknown');
                                        $colors      = ['primary','warning','danger','info','success'];
                                        $colorKey    = $colors[$statusIdx] ?? 'secondary';
                                        $numFmt      = $user?->billNumberFormat($billIdCode) ?? ($billIdCode !== '' ? $billIdCode : __('No bill number available'));
                                        $billDateFmt = $user?->dateFormat($billDate) ?? ($billDate !== '' ? $billDate : __('No bill date available'));
                                        $dueDateFmt  = $user?->dateFormat($dueDate) ?? ($dueDate !== '' ? $dueDate : __('No due date available'));
                                        $dueAmtFmt   = isset($dueAmount) ? ($user?->priceFormat($dueAmount) ?? (string) $dueAmount) : __('No due amount available');
                                    @endphp
                                    <tr>
                                        <td>
                                            <a id="{{ $showId }}"
                                               href="{{ $showUrl }}"
                                               data-url="{{ $showUrl }}"
                                               data-guard-msg="{{ $showMsg }}"
                                               data-sv-localized="true"
                                               class="{{ VC::BT_OUTPM }}">
                                                {{ $numFmt }}
                                            </a>
                                        </td>
                                        <td>{{ $billDateFmt }}</td>
                                        <td>
                                            @php $isOver = ($dueDate !== '' && $dueDate < now()->toDateString()); @endphp
                                            @if($isOver)
                                                <span class="text-danger">{{ $dueDateFmt }}</span>
                                            @else
                                                {{ $dueDateFmt }}
                                            @endif
                                        </td>
                                        <td>{{ $dueAmtFmt }}</td>
                                        <td>
                                            <span class="{{ VC::BDG }} bg-{{ $colorKey }} p-2 px-3 rounded">{{ $statusLbl }}</span>
                                        </td>
                                        @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill') || Gate::check('duplicate bill'))
                                            <td class="d-flex align-items-center">
                                                @can('duplicate bill')
                                                    @php
                                                        $dupBase     = VW::BIL . '.duplicate';
                                                        $dupKebab    = Str::kebab($dupBase);
                                                        $dupName     = Route::has($dupBase) ? $dupBase : (Route::has($dupKebab) ? $dupKebab : null);
                                                        $dupUrl      = ($dupName && $bid !== '') ? route($dupName, [$bid]) : '#';
                                                        $dupMsg      = Utility::fetchLinkMessage($lang, VW::BIL, 'bill_duplicate_route_unavailable') ?? 'Duplicate bill route is unavailable. Please contact technical support or your domain administrator.';
                                                        $dupFormId   = 'bill-duplicate-form-' . $bid;
                                                        $dupBtnId    = 'bill-duplicate-btn-' . $bid;
                                                    @endphp
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method'               => 'GET',
                                                        'url'                  => $dupUrl,
                                                        'id'                   => $dupFormId,
                                                        'data-resolved-action' => $dupUrl,
                                                        'data-guard-msg'       => $dupMsg,
                                                        'data-sv-localized'    => 'true',
                                                    ]) !!}
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                    <a id="{{ $dupBtnId }}"
                                                       href="#"
                                                       class="me-2 bs-pass-para"
                                                       data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                       data-confirm-yes="document.getElementById('{{ $dupFormId }}').submit();">
                                                        <i class="ti ti-copy text-success"></i>
                                                    </a>
                                                    @push(ST::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                try {
                                                                    const f = document.getElementById(@json($dupFormId));
                                                                    const t = document.getElementById(@json($dupBtnId));
                                                                    if (!f || !t || f.getAttribute('data-listener-active') === 'true') return;
                                                                    f.setAttribute('data-listener-active', 'true');
                                                                    const resolved = f.getAttribute('data-resolved-action') || '#';
                                                                    if ((f.getAttribute('action') === '#' || !f.getAttribute('action')) && resolved !== '#') f.setAttribute('action', resolved);
                                                                    f.addEventListener('submit', (e) => {
                                                                        const action = f.getAttribute('action') || '#';
                                                                        if (action && action !== '#') return;
                                                                        e.preventDefault();
                                                                        const msg = f.getAttribute('data-guard-msg') || 'Duplicate bill route is unavailable. Please contact technical support or your domain administrator.';
                                                                        let c = document.getElementById('toast-container');
                                                                        if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                        const ok = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                                                                        if (ok) {
                                                                            const t0 = document.createElement('div'); t0.className = 'toast'; t0.setAttribute('role','alert'); t0.setAttribute('aria-live','assertive'); t0.setAttribute('aria-atomic','true');
                                                                            const b0 = document.createElement('div'); b0.className = 'toast-body'; b0.textContent = msg;
                                                                            t0.appendChild(b0); c.appendChild(t0);
                                                                            try { window.bootstrap.Toast.getOrCreateInstance(t0).show(); } catch { alert(msg); }
                                                                        } else { alert(msg); }
                                                                        f.setAttribute('data-failed-route', 'true');
                                                                    });
                                                                } catch {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                @endcan
                                                @can('show bill')
                                                    @php
                                                        $showBase    = VW::BIL . '.show';
                                                        $showKebab   = Str::kebab($showBase);
                                                        $showName    = Route::has($showBase) ? $showBase : (Route::has($showKebab) ? $showKebab : null);
                                                        $showParam   = $bid !== '' ? Crypt::encrypt($bid) : '';
                                                        $showUrl     = ($showName && $showParam !== '') ? route($showName, [$showParam]) : '#';
                                                        $showMsg     = Utility::fetchLinkMessage($lang, VW::BIL, 'bill_show_route_unavailable') ?? 'Show bill route is unavailable. Please contact technical support or your domain administrator.';
                                                        $showId      = 'bill-show-link-' . $bid;
                                                    @endphp
                                                    @push(ST::ADM_SCR_PG)
                                                        <script>
                                                            (() => {
                                                                try {
                                                                    const a = document.getElementById(@json($showId));
                                                                    if (!a || a.getAttribute('data-listener-active') === 'true') return;
                                                                    a.setAttribute('data-listener-active', 'true');
                                                                    const url = a.getAttribute('data-url') ?? '#';
                                                                    if ((a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') a.setAttribute('href', url);
                                                                    a.addEventListener('click', (e) => {
                                                                        const href = a.getAttribute('href') ?? '#';
                                                                        if (href && href !== '#') return;
                                                                        e.preventDefault();
                                                                        const msg = a.getAttribute('data-guard-msg') || 'Show bill route is unavailable. Please contact technical support or your domain administrator.';
                                                                        let c = document.getElementById('toast-container');
                                                                        if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                        const ok = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                                                                        if (ok) {
                                                                            const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                                                            const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                                                            t.appendChild(b); c.appendChild(t);
                                                                            try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                                        } else { alert(msg); }
                                                                        a.setAttribute('data-failed-route', 'true');
                                                                    });
                                                                } catch {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                    <a id="{{ $showId }}" href="{{ $showUrl }}" data-url="{{ $showUrl }}" data-guard-msg="{{ $showMsg }}" data-sv-localized="true" class="me-2">
                                                        <i class="{{ VC::TI_EYE }} text-info"></i>
                                                    </a>
                                                @endcan
                                                @can('edit bill')
                                                    @php
                                                        $editKebab   = Str::kebab($editBase);
                                                        $editName    = Route::has($editBase) ? $editBase : (Route::has($editKebab) ? $editKebab : null);
                                                        $editParam   = $bid !== '' ? Crypt::encrypt($bid) : '';
                                                        $editUrl     = ($editName && $editParam !== '') ? route($editName, [$editParam]) : '#';
                                                        $editMsg     = Utility::fetchLinkMessage($lang, VW::BIL, 'bill_edit_route_unavailable') ?? 'Edit bill route is unavailable. Please contact technical support or your domain administrator.';
                                                        $editId      = 'bill-edit-link-' . $bid;
                                                    @endphp
                                                    @push(ST::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                try {
                                                                    const a = document.getElementById(@json($editId));
                                                                    if (!a || a.getAttribute('data-listener-active') === 'true') return;
                                                                    a.setAttribute('data-listener-active', 'true');
                                                                    const url = a.getAttribute('data-url') ?? '#';
                                                                    if ((a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') a.setAttribute('href', url);
                                                                    a.addEventListener('click', (e) => {
                                                                        const href = a.getAttribute('href') ?? '#';
                                                                        if (href && href !== '#') return;
                                                                        e.preventDefault();
                                                                        const msg = a.getAttribute('data-guard-msg') || 'Edit bill route is unavailable. Please contact technical support or your domain administrator.';
                                                                        let c = document.getElementById('toast-container');
                                                                        if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                        const ok = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                                                                        if (ok) {
                                                                            const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                                                            const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                                                            t.appendChild(b); c.appendChild(t);
                                                                            try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                                        } else { alert(msg); }
                                                                        a.setAttribute('data-failed-route', 'true');
                                                                    });
                                                                } catch {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                    <a id="{{ $editId }}" href="{{ $editUrl }}" data-url="{{ $editUrl }}" data-guard-msg="{{ $editMsg }}" data-sv-localized="true" class="me-2">
                                                        <i class="{{ VC::TI_PC }} text-primary"></i>
                                                    </a>
                                                @endcan
                                                @can('delete bill')
                                                    @php
                                                        $delBase     = VW::BIL . '.destroy';
                                                        $delKebab    = Str::kebab($delBase);
                                                        $delName     = Route::has($delBase) ? $delBase : (Route::has($delKebab) ? $delKebab : null);
                                                        $delUrl      = ($delName && $bid !== '') ? route($delName, [$bid]) : '#';
                                                        $delMsg      = Utility::fetchLinkMessage($lang, VW::BIL, 'bill_destroy_route_unavailable') ?? 'Delete bill route is unavailable. Please contact technical support or your domain administrator.';
                                                        $delFormId   = 'bill-delete-form-' . $bid;
                                                    @endphp
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method'               => 'DELETE',
                                                        'url'                  => $delUrl,
                                                        'id'                   => $delFormId,
                                                        'data-resolved-action' => $delUrl,
                                                        'data-guard-msg'       => $delMsg,
                                                        'data-sv-localized'    => 'true',
                                                    ]) !!}
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                    @push(ST::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                try {
                                                                    const f = document.getElementById(@json($delFormId));
                                                                    if (!f || f.getAttribute('data-listener-active') === 'true') return;
                                                                    f.setAttribute('data-listener-active', 'true');
                                                                    const resolved = f.getAttribute('data-resolved-action') || '#';
                                                                    if ((f.getAttribute('action') === '#' || !f.getAttribute('action')) && resolved !== '#') f.setAttribute('action', resolved);
                                                                    f.addEventListener('submit', (e) => {
                                                                        const action = f.getAttribute('action') || '#';
                                                                        if (action && action !== '#') return;
                                                                        e.preventDefault();
                                                                        const msg = f.getAttribute('data-guard-msg') || 'Delete bill route is unavailable. Please contact technical support or your domain administrator.';
                                                                        let c = document.getElementById('toast-container');
                                                                        if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                        const ok = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                                                                        if (ok) {
                                                                            const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                                                            const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                                                            t.appendChild(b); c.appendChild(t);
                                                                            try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                                        } else { alert(msg); }
                                                                        f.setAttribute('data-failed-route', 'true');
                                                                    });
                                                                } catch {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                    <a href="#"
                                                       class="bs-pass-para"
                                                       data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                       data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();">
                                                        <i class="{{ VC::TI_TRS_ALT }}"></i>
                                                    </a>
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">{{ __('No bills available') }}</td>
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

@push(ST::ADM_SCR_PG)
    <script defer>
        (() => {
            try {
                const a = document.getElementById(@json($vndIndexId));
                if (!a || a.getAttribute('data-listener-active') === 'true') return;
                a.setAttribute('data-listener-active', 'true');
                const url = a.getAttribute('data-url') ?? '#';
                if ((a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') a.setAttribute('href', url);
                a.addEventListener('click', (e) => {
                    const href = a.getAttribute('href') ?? '#';
                    if (href && href !== '#') return;
                    e.preventDefault();
                    const msg = a.getAttribute('data-guard-msg') || 'Vendor index route is unavailable. Please contact technical support or your domain administrator.';
                    let c = document.getElementById('toast-container');
                    if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                    const ok = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                    if (ok) {
                        const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                        const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                        t.appendChild(b); c.appendChild(t);
                        try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                    } else { alert(msg); }
                    a.setAttribute('data-failed-route', 'true');
                });
            } catch {}
        })();
    </script>
@endpush
