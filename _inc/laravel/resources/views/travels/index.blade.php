@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        ViewsConstants as VW,
        YieldingConstants
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Gate, Route, URL};
    use Illuminate\Support\Str;

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);

    $dashBase   = 'dashboard';
    $dashKebab  = Str::kebab($dashBase);
    $dashName   = Route::has($dashBase) ? $dashBase : (Route::has($dashKebab) ? $dashKebab : null);
    $dashUrl    = $dashName ? route($dashName) : '#';
    $dashGuard  = Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
    $dashId     = 'dashboard-link';
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Trip') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a id="{{ $dashId }}"
           href="{{ $dashUrl }}"
           data-url="{{ $dashUrl }}"
           data-guard-msg="{{ $dashGuard }}"
           data-sv-localized="true"
           {{ $dashUrl === '#' ? 'aria-disabled=true' : '' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Trip') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    @php
        $createBase     = VW::TRV . '.create';
        $createKebab    = Str::kebab($createBase);
        $createResolved = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
        $createUrl      = $createResolved ? route($createResolved) : '#';
        $createGuard    = Utility::fetchLinkMessage($lang, VW::TRV, 'create_travel_route_unavailable') ?? 'Create travel route is unavailable. Please contact technical support or your domain administrator.';
        $createId       = 'travel-create-link';
    @endphp
    <div class="{{ VC::FEND }}">
        @can('create travel')
            <a id="{{ $createId }}"
               href="{{ $createUrl }}"
               data-size="lg"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               data-title="{{ __('Create New Trip') }}"
               data-guard-msg="{{ $createGuard }}"
               data-sv-localized="true"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            <script defer src="{{ asset('assets/js/routes/travels/create.js') }}"></script>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    @role('company')
                                        <th>{{ __('Employee Name') }}</th>
                                    @endrole
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Purpose of Trip') }}</th>
                                    <th>{{ __('Country') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    @if(Gate::check('edit travel') || Gate::check('delete travel'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach(($travels ?? []) as $travel)
                                    @php
                                        $tid = data_get($travel, 'id');
                                    @endphp
                                    <tr>
                                        @role('company')
                                            <td>{{ optional($travel->employee)->name ?? __('No employee available') }}</td>
                                        @endrole
                                        <td>{{ $user?->dateFormat($travel->start_date) ?? __('Failed to get date') }}</td>
                                        <td>{{ $user?->dateFormat($travel->end_date) ?? __('Failed to get date') }}</td>
                                        <td>{{ $travel->purpose_of_visit ?: __('No purpose available') }}</td>
                                        <td>{{ $travel->place_of_visit ?: __('No country available') }}</td>
                                        <td>{{ $travel->description ?: __('No description available') }}</td>

                                        @if(Gate::check('edit travel') || Gate::check('delete travel'))
                                            <td class="{{ VC::DFL }}">
                                                @can('edit travel')
                                                    @php
                                                        $editBase     = VW::TRV . '.edit';
                                                        $editKebab    = Str::kebab($editBase);
                                                        $editResolved = Route::has($editBase) ? $editBase : (Route::has($editKebab) ? $editKebab : null);
                                                        $editUrl      = ($editResolved && $tid) ? route($editResolved, [$tid]) : '#';
                                                        $editGuard    = Utility::fetchLinkMessage($lang, VW::TRV, 'edit_travel_route_unavailable') ?? 'Edit travel route is unavailable. Please contact technical support or your domain administrator.';
                                                        $editId       = 'travel-edit-link-' . $tid;
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a id="{{ $editId }}"
                                                           href="{{ $editUrl }}"
                                                           data-url="{{ $editUrl }}"
                                                           data-size="lg"
                                                           data-ajax-popup="true"
                                                           data-title="{{ __('Edit Trip') }}"
                                                           class="{{ VC::BT_SM_CT }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Edit') }}"
                                                           data-original-title="{{ __('Edit') }}"
                                                           data-guard-msg="{{ $editGuard }}"
                                                           data-sv-localized="true">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                try {
                                                                    const a = document.getElementById('{{ $editId }}');
                                                                    if (!a) return;
                                                                    if (a.getAttribute('data-listener-active') === 'true') return;
                                                                    a.setAttribute('data-listener-active', 'true');
                                                                    const url = a.getAttribute('data-url') || '#';
                                                                    if ((a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') {
                                                                        a.setAttribute('href', url);
                                                                    }
                                                                    a.addEventListener('click', (e) => {
                                                                        try {
                                                                            const href = a.getAttribute('href') || '#';
                                                                            if (href && href !== '#') return;
                                                                            e.preventDefault();
                                                                            const msg = a.getAttribute('data-guard-msg') || 'Edit travel route is unavailable. Please contact technical support or your domain administrator.';
                                                                            let c = document.getElementById('toast-container');
                                                                            if (!c) {
                                                                                c = document.createElement('div');
                                                                                c.id = 'toast-container';
                                                                                document.body.appendChild(c);
                                                                            }
                                                                            if (window.bootstrap && window.bootstrap.Toast) {
                                                                                const t = document.createElement('div');
                                                                                t.className = 'toast';
                                                                                t.setAttribute('role', 'alert');
                                                                                t.setAttribute('aria-live', 'assertive');
                                                                                t.setAttribute('aria-atomic', 'true');
                                                                                const b = document.createElement('div');
                                                                                b.className = 'toast-body';
                                                                                b.textContent = msg;
                                                                                t.appendChild(b);
                                                                                c.appendChild(t);
                                                                                window.bootstrap.Toast.getOrCreateInstance(t).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            a.setAttribute('data-failed-route', 'true');
                                                                        } catch {}
                                                                    });
                                                                } catch {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                @endcan

                                                @can('delete travel')
                                                    @php
                                                        $delBase      = VW::TRV . '.destroy';
                                                        $delKebab     = Str::kebab($delBase);
                                                        $delResolved  = Route::has($delBase) ? $delBase : (Route::has($delKebab) ? $delKebab : null);
                                                        $delUrl       = ($delResolved && $tid) ? route($delResolved, [$tid]) : '#';
                                                        $delGuard     = Utility::fetchLinkMessage($lang, VW::TRV, 'delete_travel_route_unavailable') ?? 'Delete travel route is unavailable. Please contact technical support or your domain administrator.';
                                                        $delFormId    = 'delete-form-' . $tid;
                                                        $delLinkId    = 'travel-delete-link-' . $tid;
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open([
                                                            'method'               => 'DELETE',
                                                            'url'                  => $delUrl,
                                                            'id'                   => $delFormId,
                                                            'data-resolved-action' => $delUrl,
                                                            'data-guard-msg'       => $delGuard,
                                                            'data-sv-localized'    => 'true',
                                                        ]) !!}
                                                            <a href="#"
                                                               id="{{ $delLinkId }}"
                                                               class="{{ VC::BT_SM_CT_PR }}"
                                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                               data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Delete') }}"
                                                               data-original-title="{{ __('Delete') }}">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                try {
                                                                    const f = document.getElementById('{{ $delFormId }}');
                                                                    const a = document.getElementById('{{ $delLinkId }}');
                                                                    if (!f || !a) return;
                                                                    if (f.getAttribute('data-listener-active') === 'true') return;
                                                                    f.setAttribute('data-listener-active', 'true');
                                                                    const resolved = f.getAttribute('data-resolved-action') || '#';
                                                                    if ((f.getAttribute('action') === '#' || !f.getAttribute('action')) && resolved !== '#') {
                                                                        f.setAttribute('action', resolved);
                                                                    }
                                                                    a.addEventListener('click', (e) => {
                                                                        try {
                                                                            const action = f.getAttribute('action') || '#';
                                                                            if (action && action !== '#') return;
                                                                            e.preventDefault();
                                                                            const msg = f.getAttribute('data-guard-msg') || 'Delete travel route is unavailable. Please contact technical support or your domain administrator.';
                                                                            let c = document.getElementById('toast-container');
                                                                            if (!c) {
                                                                                c = document.createElement('div');
                                                                                c.id = 'toast-container';
                                                                                document.body.appendChild(c);
                                                                            }
                                                                            if (window.bootstrap && window.bootstrap.Toast) {
                                                                                const t = document.createElement('div');
                                                                                t.className = 'toast';
                                                                                t.setAttribute('role', 'alert');
                                                                                t.setAttribute('aria-live', 'assertive');
                                                                                t.setAttribute('aria-atomic', 'true');
                                                                                const b = document.createElement('div');
                                                                                b.className = 'toast-body';
                                                                                b.textContent = msg;
                                                                                t.appendChild(b);
                                                                                c.appendChild(t);
                                                                                window.bootstrap.Toast.getOrCreateInstance(t).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            f.setAttribute('data-failed-route', 'true');
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
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
