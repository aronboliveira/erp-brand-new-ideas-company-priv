@php
    use Illuminate\Support\Collection;
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        PermissionsConstants as PC,
        ViewsConstants as VW,
        YieldingConstants
    };
    use App\Config\Constants\ViewClassNamesConstants as VC;
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang    = Utility::fetchUserLang();
    $profile = Utility::getFile('uploads/avatar');

    $dashBase  = 'dashboard';
    $dashName  = Route::has($dashBase) ? $dashBase : (Route::has(Str::kebab($dashBase)) ? Str::kebab($dashBase) : null);
    $dashUrl   = $dashName ? route($dashName) : '#';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage User Log') }}
@endsection

@push(StacksConstants::ADM_SCR_PG)
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashUrl }}" {{ $dashUrl === '#' ? 'aria-disabled=true' : '' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('User Log') }}</li>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::MT2 }}" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        @php
                            $logBase   = ViewsConstants::USR . '.log';
                            $logName   = Route::has($logBase) ? $logBase : (Route::has(Str::kebab($logBase)) ? Str::kebab($logBase) : null);
                            $logUrl    = $logName ? route($logName) : '#';
                            $logGuard  = Utility::fetchLinkMessage($lang, ViewsConstants::USR, 'view_user_log_unavailable') ?? 'User logs route is unavailable. Please contact technical support or your domain administrator.';
                            $formId    = 'user_userlog';
                            $applyId   = 'userlog-apply-btn';
                            $resetId   = 'userlog-reset-link';
                        @endphp
                        {!! Form::open([
                            'url'                  => $logUrl,
                            'method'               => 'GET',
                            'id'                   => $formId,
                            'data-resolved-action' => $logUrl,
                            'data-guard-msg'       => $logGuard,
                            'data-sv-localized'    => 'true',
                        ]) !!}
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/users/apply.js') }}"></script>
                            @endpush
                            <div class="{{ VC::R_FLX_ALC_JCE }}">
                                <div class="{{ VC::CXL10 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::CL_XL3 }}"></div>
                                        <div class="{{ VC::CL_XL3 }}"></div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {!! Form::label('month', __('Month'), ['class' => VC::FM_LB]) !!}
                                                {!! Form::month('month', request('month', date('Y-m')), ['class' => 'month-btn ' . VC::FM_CT]) !!}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {!! Form::label('users', __('User'), ['class' => VC::FM_LB]) !!}
                                                {!! Form::select(
                                                    'users',
                                                    ($filteruser instanceof Collection ? $filteruser->toArray() : (is_array($filteruser ?? null) ? $filteruser : ['' => __('No users available')])),
                                                    request('users', ''),
                                                    ['class' => VC::FM_CT_SL]
                                                ) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT_FEND }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }}">
                                            <a id="{{ $applyId }}"
                                            href="#"
                                            class="{{ VC::BT_SM_PM }}"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a id="{{ $resetId }}"
                                            href="{{ $logUrl }}"
                                            data-url="{{ $logUrl }}"
                                            data-guard-msg="{{ $logGuard }}"
                                            class="{{ VC::BT_SM_DG }}"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Reset') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/users/reset.js') }}"></script>
                                            @endpush
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {!! Form::close() !!}
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
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('User Name') }}</th>
                                    <th>{{ __('Role') }}</th>
                                    <th>{{ __('Last Login') }}</th>
                                    <th>{{ __('Ip') }}</th>
                                    <th>{{ __('Country') }}</th>
                                    <th>{{ __('Device') }}</th>
                                    <th>{{ __('OS') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse((($userDetails ?? null) instanceof Collection || is_array($userDetails ?? null)) ? $userDetails : [] as $ud)
                                    @php
                                        $rowId      = (string) data_get($ud, 'id', '');
                                        $userId     = (string) data_get($ud, 'user_id', '');
                                        $detail     = (string) data_get($ud, 'Details', '');
                                        $userDetail = $detail !== '' ? (json_decode($detail) ?: (object)[]) : (object)[];
                                        $viewBase  = VW::USR . '.log.view';
                                        $viewName  = Route::has($viewBase) ? $viewBase : (Route::has(Str::kebab($viewBase)) ? Str::kebab($viewBase) : null);
                                        $viewUrl   = ($viewName && $rowId) ? route($viewName, [$rowId]) : '#';
                                        $viewGuard = Utility::fetchLinkMessage($lang, VW::USR, 'view_user_log_detail_unavailable') ?? 'View user log route is unavailable. Please contact technical support or your domain administrator.';
                                        $viewId    = 'userlog-view-link-' . $rowId;
                                    @endphp
                                    <tr>
                                        <td>{{ data_get($ud, 'user_name') ?: __('No user name available') }}</td>
                                        <td>
                                            <span class="{{ VC::BDG }} {{ VC::BG_P }} p-2 px-3 rounded status_badge me-3">
                                                {{ data_get($ud, 'user_type') ?: __('No role available') }}
                                            </span>
                                        </td>
                                        <td>{{ data_get($ud, 'date') ?: __('No last login available') }}</td>
                                        <td>{{ data_get($ud, 'ip') ?: __('No IP available') }}</td>
                                        <td>{{ data_get($userDetail, 'country') ?: __('No country available') }}</td>
                                        <td>{{ data_get($userDetail, 'device_type') ?: __('No device available') }}</td>
                                        <td>{{ data_get($userDetail, 'os_name') ?: __('No OS available') }}</td>
                                        <td class="{{ VC::DFL_JCB }}">
                                            <div class="{{ VC::ACT_BTN_WRN }}">
                                                <a id="{{ $viewId }}"
                                                   href="{{ $viewUrl }}"
                                                   data-url="{{ $viewUrl }}"
                                                   data-ajax-popup="true"
                                                   data-size="md"
                                                   class="{{ VC::BT_SM_CT }}"
                                                   data-bs-toggle="tooltip"
                                                   data-title="{{ __('View User Logs') }}"
                                                   title="{{ __('View') }}"
                                                   data-guard-msg="{{ $viewGuard }}">
                                                    <i class="{{ VC::TI_EYE_WT }}"></i>
                                                </a>
                                            </div>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        try {
                                                            const a = document.getElementById(@json($viewId));
                                                            if (!a || a.getAttribute('data-listener-active') === 'true') return;
                                                            a.setAttribute('data-listener-active', 'true');

                                                            const url = a.getAttribute('data-url') ?? '#';
                                                            if ((a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') {
                                                                a.setAttribute('href', url);
                                                            }

                                                            a.addEventListener('click', (e) => {
                                                                const href = a.getAttribute('href') ?? '#';
                                                                if (href && href !== '#') return;
                                                                e.preventDefault();

                                                                const msg = a.getAttribute('data-guard-msg') ?? 'View user log route is unavailable. Please contact technical support or your domain administrator.';
                                                                let c = document.getElementById('toast-container');
                                                                if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                const hasBS = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                                                                if (hasBS) {
                                                                    const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                                                    const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                                                    t.appendChild(b); c.appendChild(t);
                                                                    try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                                } else {
                                                                    alert(msg);
                                                                }
                                                                a.setAttribute('data-failed-route', 'true');
                                                            });
                                                        } catch {}
                                                    })();
                                                </script>
                                            @endpush
                                            @can(PC::DEL_USER)
                                                @php
                                                    $delBase   = VW::USR . '.log.destroy';
                                                    $delName   = Route::has($delBase) ? $delBase : (Route::has(Str::kebab($delBase)) ? Str::kebab($delBase) : null);
                                                    $delUrl    = ($delName && $userId) ? route($delName, [$userId]) : '#';
                                                    $delGuard  = Utility::fetchLinkMessage($lang, VW::USR, 'delete_user_log_route_unavailable') ?? 'Delete user log route is unavailable. Please contact technical support or your domain administrator.';
                                                    $delFormId = 'userlog-delete-form-' . $rowId;
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
                                                           class="{{ VC::BT_SM_CT_PR }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                           data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            try {
                                                                const f = document.getElementById(@json($delFormId));
                                                                if (!f || f.getAttribute('data-listener-active') === 'true') return;
                                                                f.setAttribute('data-listener-active', 'true');

                                                                const resolved = f.getAttribute('data-resolved-action') || '#';
                                                                if ((f.getAttribute('action') === '#' || !f.getAttribute('action')) && resolved !== '#') {
                                                                    f.setAttribute('action', resolved);
                                                                }

                                                                f.addEventListener('submit', (e) => {
                                                                    const action = f.getAttribute('action') || '#';
                                                                    if (action && action !== '#') return;
                                                                    e.preventDefault();

                                                                    const msg = f.getAttribute('data-guard-msg') || 'Delete user log route is unavailable. Please contact technical support or your domain administrator.';
                                                                    let c = document.getElementById('toast-container');
                                                                    if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                    const hasBS = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                                                                    if (hasBS) {
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
                                        </td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">{{ __('No user logs available') }}</td>
                                        </tr>
                                    @endforelse
                                </div>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
