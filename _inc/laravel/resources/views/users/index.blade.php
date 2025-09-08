@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants as PC,
        StacksConstants,
        UsersConstants as UC,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Crypt, Gate, Route, Storage};
    use Illuminate\Support\{Collection, Str};

    $profile = Utility::getFile('uploads/avatar');
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);

    $dashBase  = 'dashboard';
    $dashKebab = Str::kebab($dashBase);
    $dashName  = Route::has($dashBase) ? $dashBase : (Route::has($dashKebab) ? $dashKebab : null);
    $dashUrl   = $dashName ? route($dashName) : '#';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage User') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashUrl }}" {{ $dashUrl === '#' ? 'aria-disabled=true' : '' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('User') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @if ($user?->{UC::COL_TP} == PC::CPN || strtolower((string)($user?->{UC::COL_TP} ?? '')) == PC::HR || $user?->{UC::COL_TP} == PC::SA)
            @php
                $logsBase  = VW::USR . '.log';
                $logsKebab = Str::kebab($logsBase);
                $logsName  = Route::has($logsBase) ? $logsBase : (Route::has($logsKebab) ? $logsKebab : null);
                $logsUrl   = $logsName ? route($logsName, []) : '#';
                $logsGuard = Utility::fetchLinkMessage($lang, VW::USR, 'view_user_log_unavailable') ?? 'User logs route is unavailable. Please contact technical support or your domain administrator.';
                $logsId    = 'users-log-link';
            @endphp
            <a id="{{ $logsId }}"
               href="{{ $logsUrl }}"
               data-url="{{ $logsUrl }}"
               data-guard-msg="{{ $logsGuard }}"
               class="{{ VC::BT_SM }} {{ Request::segment(1) === VW::USR ? 'active' : '' }} {{ VC::BT_PRM }}"
               data-bs-toggle="tooltip"
               data-bs-placement="top"
               title="{{ __('User Logs History') }}">
                <i class="ti ti-user-check"></i>
            </a>
        @endif

        @can(PC::CR_USER)
            @php
                $createBase  = VW::USR . '.create';
                $createKebab = Str::kebab($createBase);
                $createName  = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
                $createUrl   = $createName ? route($createName, []) : '#';
                $createGuard = Utility::fetchLinkMessage($lang, VW::USR, 'create_user_unavailable') ?? 'Create user route is unavailable. Please contact technical support or your domain administrator.';
                $createId    = 'user-create-link';
            @endphp
            <a id="{{ $createId }}"
               href="{{ $createUrl }}"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               data-guard-msg="{{ $createGuard }}"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="col-xxl-12">
            <div class="{{ VC::RW }}">
                @forelse((($users ?? null) instanceof Collection || is_array($users ?? null)) ? $users : [] as $usr)
                    @php
                        $uid = (string) data_get($usr, 'id', '');
                        $isActive = (int) (data_get($usr, UC::COL_IA) ?? 0) === 1;
                    @endphp

                    <div class="{{ VC::CM3 }} {{ VC::MB4 }}">
                        <div class="{{ VC::CD }}">
                            <div class="card-header border-0 {{ VC::MB0 }}">
                                <div class="{{ VC::DFL_AIC_JCB }}">
                                    <h6 class="{{ VC::MB0 }}">
                                        <div class="{{ VC::BDG }} {{ VC::BG_P }} p-2 px-3 rounded">
                                            {{ data_get($usr,'type') ? ucfirst((string) data_get($usr,'type')) : __('No user type available') }}
                                        </div>
                                    </h6>
                                </div>

                                @if(Gate::check(PC::ED_USER) || Gate::check(PC::DEL_USER))
                                    <div class="card-header-right">
                                        <div class="btn-group card-option">
                                            @if($isActive)
                                                <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <div class="{{ VC::DRP_MN_EM }}">
                                                    @can(PC::ED_USER)
                                                        @php
                                                            $editBase  = VW::USR . '.edit';
                                                            $editName  = Route::has($editBase) ? $editBase : (Route::has(Str::kebab($editBase)) ? Str::kebab($editBase) : null);
                                                            $editUrl   = ($editName && $uid) ? route($editName, [$uid]) : '#';
                                                            $editGuard = Utility::fetchLinkMessage($lang, VW::USR, 'edit_user_route_unavailable') ?? 'Edit user route is unavailable. Please contact technical support or your domain administrator.';
                                                            $editId    = 'user-edit-link-' . $uid;
                                                        @endphp
                                                        <a id="{{ $editId }}"
                                                           href="{{ $editUrl }}"
                                                           data-url="{{ $editUrl }}"
                                                           data-size="lg"
                                                           data-ajax-popup="true"
                                                           class="dropdown-item"
                                                           title="{{ __('Edit User') }}"
                                                           data-guard-msg="{{ $editGuard }}">
                                                            <i class="{{ VC::TI_PC }}"></i><span>{{ __('Edit') }}</span>
                                                        </a>

                                                        @push(StacksConstants::ADM_SCR_PG)
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
                                                                            const msg = a.getAttribute('data-guard-msg') ?? 'Edit user route is unavailable. Please contact technical support or your domain administrator.';
                                                                            let c = document.getElementById('toast-container');
                                                                            if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                            const hasBS = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                                                                            if (hasBS) {
                                                                                const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                                                                const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                                                                t.appendChild(b); c.appendChild(t);
                                                                                try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                                            } else { alert(msg); }
                                                                            a.setAttribute('data-failed-route','true');
                                                                        });
                                                                    } catch {}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endcan

                                                    @can(PC::DEL_USER)
                                                        @php
                                                            $delBase   = VW::USR . '.destroy';
                                                            $delName   = Route::has($delBase) ? $delBase : (Route::has(Str::kebab($delBase)) ? Str::kebab($delBase) : null);
                                                            $delUrl    = ($delName && $uid) ? route($delName, [$uid]) : '#';
                                                            $delGuard  = Utility::fetchLinkMessage($lang, VW::USR, 'delete_user_route_unavailable') ?? 'Delete user route is unavailable. Please contact technical support or your domain administrator.';
                                                            $delFormId = 'user-delete-form-' . $uid;
                                                        @endphp
                                                        {!! Form::open([
                                                            'method'               => 'DELETE',
                                                            'url'                  => $delUrl,
                                                            'id'                   => $delFormId,
                                                            'data-resolved-action' => $delUrl,
                                                            'data-guard-msg'       => $delGuard,
                                                            'data-sv-localized'    => 'true',
                                                        ]) !!}
                                                            <a href="#!"
                                                               class="dropdown-item bs-pass-para"
                                                               data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                               data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();">
                                                                <i class="{{ VC::TI_ARC }}"></i>
                                                                <span>
                                                                    @if((int) (data_get($usr,'delete_status') ?? 0) !== 0)
                                                                        {{ __('Delete') }}
                                                                    @else
                                                                        {{ __('Restore') }}
                                                                    @endif
                                                                </span>
                                                            </a>
                                                        {!! Form::close() !!}

                                                        @push(StacksConstants::ADM_SCR_PG)
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
                                                                            const msg = f.getAttribute('data-guard-msg') || 'Delete user route is unavailable. Please contact technical support or your domain administrator.';
                                                                            let c = document.getElementById('toast-container');
                                                                            if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                            const hasBS = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                                                                            if (hasBS) {
                                                                                const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                                                                const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                                                                t.appendChild(b); c.appendChild(t);
                                                                                try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                                            } else { alert(msg); }
                                                                            f.setAttribute('data-failed-route','true');
                                                                        });
                                                                    } catch {}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endcan

                                                    @php
                                                        $resetBase  = VW::USR . '.reset';
                                                        $resetName  = Route::has($resetBase) ? $resetBase : (Route::has(Str::kebab($resetBase)) ? Str::kebab($resetBase) : null);
                                                        $encId      = $uid !== '' ? Crypt::encrypt($uid) : '';
                                                        $resetUrl   = ($resetName && $encId) ? route($resetName, [$encId]) : '#';
                                                        $resetGuard = Utility::fetchLinkMessage($lang, VW::USR, 'reset_user_password_route_unavailable') ?? 'Reset password route is unavailable. Please contact technical support or your domain administrator.';
                                                        $resetId    = 'user-reset-link-' . $uid;
                                                    @endphp
                                                    <a id="{{ $resetId }}"
                                                       href="{{ $resetUrl }}"
                                                       data-url="{{ $resetUrl }}"
                                                       data-ajax-popup="true"
                                                       data-size="md"
                                                       class="dropdown-item"
                                                       title="{{ __('Reset Password') }}"
                                                       data-guard-msg="{{ $resetGuard }}">
                                                        <i class="ti ti-adjustments"></i><span>{{ __('Reset Password') }}</span>
                                                    </a>

                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                try {
                                                                    const a = document.getElementById(@json($resetId));
                                                                    if (!a || a.getAttribute('data-listener-active') === 'true') return;
                                                                    a.setAttribute('data-listener-active', 'true');
                                                                    const url = a.getAttribute('data-url') ?? '#';
                                                                    if ((a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') a.setAttribute('href', url);
                                                                    a.addEventListener('click', (e) => {
                                                                        const href = a.getAttribute('href') ?? '#';
                                                                        if (href && href !== '#') return;
                                                                        e.preventDefault();
                                                                        const msg = a.getAttribute('data-guard-msg') ?? 'Reset password route is unavailable. Please contact technical support or your domain administrator.';
                                                                        let c = document.getElementById('toast-container');
                                                                        if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                        const hasBS = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                                                                        if (hasBS) {
                                                                            const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                                                            const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                                                            t.appendChild(b); c.appendChild(t);
                                                                            try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                                        } else { alert(msg); }
                                                                        a.setAttribute('data-failed-route','true');
                                                                    });
                                                                } catch {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                </div>
                                            @else
                                                <a href="#" class="action-item text-lg"><i class="ti ti-lock"></i></a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="card-body full-card text-center">
                                <div class="img-fluid {{ VC::AV_CC }} card-avatar">
                                    <img src="{{ data_get($usr,'avatar') ? asset(Storage::url('uploads/avatar/'.data_get($usr,'avatar'))) : asset(Storage::url('uploads/avatar/avatar.png')) }}" class="img-user wid-80 round-img {{ VC::AV_CC }}">
                                </div>
                                <h4 class="{{ VC::MT3 }} text-primary">{{ data_get($usr,'name') ?: __('No name available') }}</h4>
                                @if((int) (data_get($usr,'delete_status') ?? 0) === 0)
                                    <h5 class="office-time {{ VC::MB0 }}">{{ __('Soft Deleted') }}</h5>
                                @endif
                                <small class="text-primary">{{ data_get($usr,'email') ?: __('No email available') }}</small>
                                <p></p>
                                <div class="text-center" data-bs-toggle="tooltip" title="{{ __('Last Login') }}">
                                    {{ data_get($usr,'last_login_at') ?: __('No last login available') }}
                                </div>

                                @if((string) (data_get($usr,UC::COL_TP) ?? '') === PC::SA)
                                    @php
                                        $upgBase   = VW::PLN . '.upgrade';
                                        $upgName   = Route::has($upgBase) ? $upgBase : (Route::has(Str::kebab($upgBase)) ? Str::kebab($upgBase) : null);
                                        $upgUrl    = ($upgName && $uid) ? route($upgName, [$uid]) : '#';
                                        $upgGuard  = Utility::fetchLinkMessage($lang, VW::PLN, 'upgrade_plan_route_unavailable') ?? 'Upgrade plan route is unavailable. Please contact technical support or your domain administrator.';
                                        $upgId     = 'plan-upgrade-link-' . $uid;
                                    @endphp

                                    <div class="{{ VC::MT4 }}">
                                        <div class="{{ VC::R_FLX_ALC_JCE }}">
                                            <div class="col-6 text-center">
                                                <span class="d-block font-bold {{ VC::MB0 }}">{{ data_get($usr,'currentPlan.name') ?: __('No plan name available') }}</span>
                                            </div>
                                            <div class="col-6 text-center">
                                                <a id="{{ $upgId }}"
                                                   href="{{ $upgUrl }}"
                                                   data-url="{{ $upgUrl }}"
                                                   data-size="lg"
                                                   data-ajax-popup="true"
                                                   class="{{ VC::BT_OUTPM }}"
                                                   data-guard-msg="{{ $upgGuard }}">
                                                    {{ __('Upgrade Plan') }}
                                                </a>
                                            </div>
                                            <div class="{{ VC::C12 }}"><hr class="{{ VC::MY3 }}"></div>
                                            <div class="{{ VC::C12 }} text-center">
                                                <span class="text-dark {{ VC::TXS }}">
                                                    {{ __('Plan Expired : ') }}
                                                    {{ data_get($usr,'plan_expire_date') ? ($usr?->dateFormat(data_get($usr,'plan_expire_date')) ?? __('Failed to format date')) : __('Lifetime') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script>
                                            (() => {
                                                try {
                                                    const a = document.getElementById(@json($upgId));
                                                    if (!a || a.getAttribute('data-listener-active') === 'true') return;
                                                    a.setAttribute('data-listener-active', 'true');
                                                    const url = a.getAttribute('data-url') ?? '#';
                                                    if ((a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') a.setAttribute('href', url);
                                                    a.addEventListener('click', (e) => {
                                                        const href = a.getAttribute('href') ?? '#';
                                                        if (href && href !== '#') return;
                                                        e.preventDefault();
                                                        const msg = a.getAttribute('data-guard-msg') ?? 'Upgrade plan route is unavailable. Please contact technical support or your domain administrator.';
                                                        let c = document.getElementById('toast-container');
                                                        if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                        const hasBS = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                                                        if (hasBS) {
                                                            const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                                            const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                                            t.appendChild(b); c.appendChild(t);
                                                            try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                        } else { alert(msg); }
                                                        a.setAttribute('data-failed-route','true');
                                                    });
                                                } catch {}
                                            })();
                                        </script>
                                    @endpush
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="{{ VC::CM12 }}"><p class="text-center text-muted">{{ __('No users available') }}</p></div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/users/index.js') }}"></script>
@endpush
