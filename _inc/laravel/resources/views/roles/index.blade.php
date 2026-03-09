@php
    try {
$lang = Utility::fetchUserLang();
    } catch (\Throwable $e) {
        \Log::error('roles/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Role') }}
@endsection
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Role') }}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            try {
                $rlCreateBase = ViewsConstants::RL.'.create';
                $rlCreateKebab = Str::kebab($rlCreateBase);
                $rlCreateResolved = Route::has($rlCreateBase) ? $rlCreateBase : (Route::has($rlCreateKebab) ? $rlCreateKebab : null);
                $rlCreateUrl = $rlCreateResolved ? route($rlCreateResolved) : '#';
                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $rlCreateGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RL, 'create_role_route_unavailable') ?? 'Create role route is unavailable. Please contact technical support or your domain administrator.';
            } catch (\Throwable $e) {
                \Log::error('roles/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <a
            href="{{ $rlCreateUrl }}"
            data-size="lg"
            data-url="{{ $rlCreateUrl }}"
            data-ajax-popup="true"
            data-bs-toggle="tooltip"
            title="{{ __('Create New Role') }}"
            class="{{ VC::BT_SM_PM }} role-create"
            data-guard-msg="{{ base64_encode($rlCreateGuardMsg) }}"
            data-sv-localized="true"
        >
            <i class="{{ VC::TI_PLS }}"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/roles/create.js') }}" defer></script>
        @endpush
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CXL12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Role') }}</th>
                                    <th>{{ __('Permissions') }}</th>
                                    <th width="150">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse((($roles ?? null) instanceof Collection || is_array($roles ?? null)) ? $roles : [] as $role)
                                    @if((string)data_get($role,'name','') != 'client')
                                        <tr class="font-style">
                                            <td class="Role">{{ data_get($role,'name') ?: __('No role name available') }}</td>
                                            <td class="Permission">
                                                @php
                                                	$__perms = (is_object($role) && method_exists($role,'permissions')) ? ($role->permissions()->pluck('name') ?? collect()) : collect();
@endphp
                                                @forelse($__perms as $permissionName)
                                                    <span class="{{ VC::BDG }} rounded p-2 m-1 px-3 {{ VC::BG_P }}">{{ $permissionName ?: __('No permission name available') }}</span>
                                                @empty
                                                    <span class="{{ VC::TXT_MT }}">{{ __('No permissions available') }}</span>
                                                @endforelse
                                            </td>
                                            <td class="Action">
                                                <span>
                                                    @can(PermissionsConstants::ED_ROLE)
                                                        @php
                                                            try {
                                                                $rlEditBase = ViewsConstants::RL.'.edit';
                                                                $rlEditKebab = Str::kebab($rlEditBase);
                                                                $rlEditResolved = Route::has($rlEditBase) ? $rlEditBase : (Route::has($rlEditKebab) ? $rlEditKebab : null);
                                                                $roleIdValue = data_get($role, 'id');
                                                                $rlEncryptedId = $roleIdValue ? Crypt::encrypt($roleIdValue) : null;
                                                                $rlEditUrl = ($rlEditResolved && $rlEncryptedId) ? route($rlEditResolved, $rlEncryptedId) : '#';
                                                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                $rlEditGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RL, 'edit_role_route_unavailable') ?? 'Edit role route is unavailable. Please contact technical support or your domain administrator.';
                                                                $rlAnchorId = 'role-edit-'.($roleIdValue ? substr(md5((string) $roleIdValue), 0, 8) : 'x');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('roles/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="{{ VC::ACT_BTN_INF }}">
                                                            <a
                                                                id="{{ $rlAnchorId }}"
                                                                href="{{ $rlEditUrl }}"
                                                                class="{{ VC::BT_SM_FL_CT }}"
                                                                data-url="{{ $rlEditUrl }}"
                                                                data-ajax-popup="true"
                                                                data-size="lg"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Edit') }}"
                                                                data-title="{{ __('Role Edit') }}"
                                                                aria-label="{{ __('Edit Role') }}"
                                                                data-guard-msg="{{ base64_encode($rlEditGuardMsg) }}"
                                                                data-sv-localized="true"
                                                            >
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    try {
                                                                        const el = document.getElementById('{{ $rlAnchorId }}');
                                                                        if (!el) { return; }
                                                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                        el.setAttribute('data-listener-active', 'true');
                                                                        el.addEventListener('click', (e) => {
                                                                            try {
                                                                                const href = el.getAttribute('href') ?? '#';
                                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                if (url !== '#' && href !== '#') { return; }
                                                                                e.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Edit role route is unavailable. Please contact technical support or your domain administrator.';
                                                                                (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            } catch (err) {}
                                                                        });
                                                                    } catch (err) {}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endcan
                                                    @if (strtolower((string)data_get($role,'name','')) !== 'employee')
                                                        @can(PermissionsConstants::DEL_ROLE)
                                                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                {!! Form::open(['method' => 'DELETE','route' => [ViewsConstants::RL.'.destroy', data_get($role,'id','0')],'id' => 'delete-form-'.data_get($role,'id','0')]) !!}
                                                                    @php
                                                                        try {
                                                                            $rlDestroyBase = ViewsConstants::RL.'.destroy';
                                                                            $rlDestroyKebab = Str::kebab($rlDestroyBase);
                                                                            $rlDestroyResolved = Route::has($rlDestroyBase) ? $rlDestroyBase : (Route::has($rlDestroyKebab) ? $rlDestroyKebab : null);
                                                                            $roleIdValue = data_get($role, 'id');
                                                                            $rlEncryptedId = $roleIdValue ? Crypt::encrypt($roleIdValue) : null;
                                                                            $rlDestroyUrl = ($rlDestroyResolved && $rlEncryptedId) ? route($rlDestroyResolved, $rlEncryptedId) : '#';
                                                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                            $rlDeleteGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RL, 'delete_role_route_unavailable') ?? 'Delete role route is unavailable. Please contact technical support or your domain administrator.';
                                                                            $confirmTitle = __(Utility::fetchLinkMessage($langValue, 'generics', 'are_you_sure') ?? 'Are You Sure?');
                                                                            $confirmBody = __(Utility::fetchLinkMessage($langValue, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                                                            $formId = 'delete-form-'.($roleIdValue ?? 'x');
                                                                            $anchorId = 'role-delete-btn-'.($roleIdValue ?? 'x');
                                                                        } catch (\Throwable $e) {
                                                                            \Log::error('roles/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                        }
@endphp
                                                                    {!! Form::open(['method' => 'DELETE', 'url' => $rlDestroyUrl, 'id' => $formId]) !!}
                                                                        <a
                                                                            id="{{ $anchorId }}"
                                                                            href="#"
                                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Delete') }}"
                                                                            data-confirm="{{ $confirmTitle }}|{{ $confirmBody }}"
                                                                            data-confirm-yes="document.getElementById('{{ $formId }}').submit();"
                                                                            data-url="{{ $rlDestroyUrl }}"
                                                                            data-guard-msg="{{ base64_encode($rlDeleteGuardMsg) }}"
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
                                                                                            const msg = el.getAttribute('data-guard-msg') ?? 'Delete role route is unavailable. Please contact technical support or your domain administrator.';
                                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                            el.setAttribute('data-failed-route', 'true');
                                                                                            if (form) { form.setAttribute('data-failed-route', 'true'); }
                                                                                        } catch (err) {}
                                                                                    });
                                                                                } catch (err) {}
                                                                            })();
                                                                        </script>
                                                                    @endpush
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="3" class="{{ VC::TXCT_MT }}">{{ __('No roles available') }}</td>
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
