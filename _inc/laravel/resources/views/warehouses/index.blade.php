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
    use Illuminate\Support\Facades\{Gate, Route};
    use Illuminate\Support\{Collection, Str};
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{__('Warehouse')}}
@endsection

@push(StacksConstants::ADM_SCR_PG)
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Warehouse')}}</li>
@endsection

@php
    use App\Config\Constants\{
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Illuminate\Support\{Facades\Route, Str};

    $lang = Utility::fetchUserLang();
@endphp

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            $createBase  = VW::WRH . '.create';
            $createKebab = Str::kebab($createBase);
            $createName  = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
            $createUrl   = $createName ? route($createName) : '#';
            $createGuard = Utility::fetchLinkMessage($lang, VW::WRH, 'create_warehouse_route_unavailable')
                ?? 'Create warehouse route is unavailable. Please contact technical support or your domain administrator.';
            $createId    = 'warehouse-create-link';
        @endphp
        <a id="{{ $createId }}"
           href="{{ $createUrl }}"
           data-url="{{ $createUrl }}"
           data-ajax-popup="true"
           data-bs-toggle="tooltip"
           title="{{ __('Create') }}"
           data-title="{{ __('Create Warehouse') }}"
           data-guard-msg="{{ $createGuard }}"
           data-sv-localized="true"
           class="{{ VC::BT_SM_PM }}">
            <i class="{{ VC::TI_PLS }}"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer src="{{ asset('assets/js/routes/warehouses/create.js') }}"></script>
        @endpush
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="col-xl-12">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Address') }}</th>
                                    <th>{{ __('City') }}</th>
                                    <th>{{ __('Zip Code') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse((($warehouses ?? null) instanceof Collection || is_array($warehouses ?? null)) ? $warehouses : [] as $warehouse)
                                    @php
                                        $wid = (string) data_get($warehouse, 'id', '');
                                    @endphp
                                    <tr class="font-style">
                                        <td>{{ (string) (data_get($warehouse,'name') ?: __('No name available')) }}</td>
                                        <td>{{ (string) (data_get($warehouse,'address') ?: __('No address available')) }}</td>
                                        <td>{{ (string) (data_get($warehouse,'city') ?: __('No city available')) }}</td>
                                        <td>{{ (string) (data_get($warehouse,'city_zip') ?: __('No zip code available')) }}</td>

                                        @if(Gate::check('show warehouse') || Gate::check('edit warehouse') || Gate::check('delete warehouse'))
                                            <td class="Action">
                                                @can('show warehouse')
                                                    @php
                                                        $showBase  = VW::WRH . '.show';
                                                        $showKebab = Str::kebab($showBase);
                                                        $showName  = Route::has($showBase) ? $showBase : (Route::has($showKebab) ? $showKebab : null);
                                                        $showUrl   = ($showName && $wid !== '') ? route($showName, [$wid]) : '#';
                                                        $showGuard = Utility::fetchLinkMessage($lang, VW::WRH, 'show_warehouse_route_unavailable')
                                                            ?? 'Show warehouse route is unavailable. Please contact technical support or your domain administrator.';
                                                        $showId    = 'warehouse-show-link-' . $wid;
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_WRN }}">
                                                        <a id="{{ $showId }}"
                                                           href="{{ $showUrl }}"
                                                           data-url="{{ $showUrl }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('View') }}"
                                                           data-guard-msg="{{ $showGuard }}"
                                                           data-sv-localized="true"
                                                           class="{{ VC::BT_SM_FL_CT }}">
                                                            <i class="{{ VC::TI_EYE_WT }}"></i>
                                                        </a>
                                                    </div>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script>
                                                            (() => {
                                                                try {
                                                                    const a = document.getElementById('{{ $showId }}');
                                                                    if (!a || a.getAttribute('data-listener-active') === 'true') return;
                                                                    a.setAttribute('data-listener-active', 'true');
                                                                    const url = a.getAttribute('data-url') ?? '#';
                                                                    if ((a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') a.setAttribute('href', url);
                                                                    a.addEventListener('click', (e) => {
                                                                        try {
                                                                            const href = a.getAttribute('href') ?? '#';
                                                                            if (href && href !== '#') return;
                                                                            e.preventDefault();
                                                                            const msg = a.getAttribute('data-guard-msg') ?? 'Show warehouse route is unavailable. Please contact technical support or your domain administrator.';
                                                                            let c = document.getElementById('toast-container');
                                                                            if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                            const ok = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                                                                            if (ok) {
                                                                                const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                                                                const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                                                                t.appendChild(b); c.appendChild(t);
                                                                                try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                                            } else { alert(msg); }
                                                                            a.setAttribute('data-failed-route','true');
                                                                        } catch {}
                                                                    });
                                                                } catch {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                @endcan
                                                @can('edit warehouse')
                                                    @php
                                                        $editBase  = VW::WRH . '.edit';
                                                        $editKebab = Str::kebab($editBase);
                                                        $editName  = Route::has($editBase) ? $editBase : (Route::has($editKebab) ? $editKebab : null);
                                                        $editUrl   = ($editName && $wid !== '') ? route($editName, [$wid]) : '#';
                                                        $editGuard = Utility::fetchLinkMessage($lang, VW::WRH, 'edit_warehouse_route_unavailable')
                                                            ?? 'Edit warehouse route is unavailable. Please contact technical support or your domain administrator.';
                                                        $editId    = 'warehouse-edit-link-' . $wid;
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_INF }}">
                                                        <a id="{{ $editId }}"
                                                           href="{{ $editUrl }}"
                                                           data-url="{{ $editUrl }}"
                                                           data-ajax-popup="true"
                                                           data-size="lg"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Edit') }}"
                                                           data-title="{{ __('Edit Warehouse') }}"
                                                           data-guard-msg="{{ $editGuard }}"
                                                           data-sv-localized="true"
                                                           class="{{ VC::BT_SM_CT }}">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script>
                                                            (() => {
                                                                try {
                                                                    const a = document.getElementById('{{ $editId }}');
                                                                    if (!a || a.getAttribute('data-listener-active') === 'true') return;
                                                                    a.setAttribute('data-listener-active', 'true');
                                                                    const url = a.getAttribute('data-url') ?? '#';
                                                                    if ((a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') a.setAttribute('href', url);
                                                                    a.addEventListener('click', (e) => {
                                                                        try {
                                                                            const href = a.getAttribute('href') ?? '#';
                                                                            if (href && href !== '#') return;
                                                                            e.preventDefault();
                                                                            const msg = a.getAttribute('data-guard-msg') ?? 'Edit warehouse route is unavailable. Please contact technical support or your domain administrator.';
                                                                            let c = document.getElementById('toast-container');
                                                                            if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                            const ok = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                                                                            if (ok) {
                                                                                const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                                                                const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                                                                t.appendChild(b); c.appendChild(t);
                                                                                try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                                            } else { alert(msg); }
                                                                            a.setAttribute('data-failed-route','true');
                                                                        } catch {}
                                                                    });
                                                                } catch {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                @endcan
                                                @can('delete warehouse')
                                                    @php
                                                        $delBase   = VW::WRH . '.destroy';
                                                        $delKebab  = Str::kebab($delBase);
                                                        $delName   = Route::has($delBase) ? $delBase : (Route::has($delKebab) ? $delKebab : null);
                                                        $delUrl    = ($delName && $wid !== '') ? route($delName, [$wid]) : '#';
                                                        $delGuard  = Utility::fetchLinkMessage($lang, VW::WRH, 'delete_warehouse_route_unavailable')
                                                            ?? 'Delete warehouse route is unavailable. Please contact technical support or your domain administrator.';
                                                        $formId    = 'delete-form-' . $wid;
                                                        $btnId     = 'delete-trigger-' . $wid;
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open([
                                                            'method'               => 'DELETE',
                                                            'url'                  => $delUrl,
                                                            'id'                   => $formId,
                                                            'data-resolved-action' => $delUrl,
                                                            'data-guard-msg'       => $delGuard,
                                                            'data-sv-localized'    => 'true',
                                                        ]) !!}
                                                            <a id="{{ $btnId }}"
                                                               href="#"
                                                               class="{{ VC::BT_SM_CT_PR }}"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Delete') }}"
                                                               data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                               data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script>
                                                            (() => {
                                                                try {
                                                                    const f = document.getElementById('{{ $formId }}');
                                                                    const t = document.getElementById('{{ $btnId }}');
                                                                    if (!f || !t || f.getAttribute('data-listener-active') === 'true') return;
                                                                    f.setAttribute('data-listener-active', 'true');

                                                                    const resolved = f.getAttribute('data-resolved-action') || '#';
                                                                    if ((f.getAttribute('action') === '#' || !f.getAttribute('action')) && resolved !== '#') {
                                                                        f.setAttribute('action', resolved);
                                                                    }

                                                                    t.addEventListener('click', (e) => {
                                                                        try { f.submit(); } catch { e.preventDefault(); }
                                                                    });

                                                                    f.addEventListener('submit', (e) => {
                                                                        try {
                                                                            const action = f.getAttribute('action') || '#';
                                                                            if (action && action !== '#') return;
                                                                            e.preventDefault();

                                                                            const msg = f.getAttribute('data-guard-msg') || 'Delete warehouse route is unavailable. Please contact technical support or your domain administrator.';
                                                                            let c = document.getElementById('toast-container');
                                                                            if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                            const ok = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                                                                            if (ok) {
                                                                                const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                                                                const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                                                                t.appendChild(b); c.appendChild(t);
                                                                                try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                                            } else { alert(msg); }
                                                                            f.setAttribute('data-failed-route','true');
                                                                        } catch {}
                                                                    });
                                                                } catch {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">{{ __('No warehouses available') }}</td>
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
