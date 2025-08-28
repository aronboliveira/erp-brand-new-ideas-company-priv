@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\{Collection, Str};
    $user        = Auth::user();
    $lang        = Utility::fetchUserLang(user: $user);
    $profilePath = Utility::getFile('uploads/avatar/');
    $row         = VC::RW;
    $card        = VC::CD;
    $btnPrimary  = VC::BT_SM_PM;
    $btnDanger   = VC::ACT_BTN_DNG_2;
    $flexBetween = VC::DFL_JCB;
    $avatarSm    = VC::AV_CC_SM;
    $tableCls    = VC::TB;
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Assets') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Assets') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can(PermissionsConstants::CRT_AST)
            @php
                $accAstCreateBase = ViewsConstants::ACC_AST.'.create';
                $accAstCreateKebab = Str::kebab($accAstCreateBase);
                $accAstCreateResolved = Route::has($accAstCreateBase) ? $accAstCreateBase : (Route::has($accAstCreateKebab) ? $accAstCreateKebab : null);
                $accAstCreateUrl = $accAstCreateResolved ? route($accAstCreateResolved) : '#';
                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $accAstCreateGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::ACC_AST, 'create_account_asset_route_unavailable') ?? 'Create account asset route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                href="{{ $accAstCreateUrl }}"
                data-url="{{ $accAstCreateUrl }}"
                data-size="lg"
                data-ajax-popup="true"
                data-title="{{ __('Create New Asset') }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ $btnPrimary }} account-asset-create"
                data-guard-msg="{{ $accAstCreateGuardMsg }}"
                data-sv-localized="true"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script src="{{ asset('assets/js/routes/accountAssets/create.js') }}" defer></script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ $row }}">
        <div class="col-md-12">
            <div class="{{ $card }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ $tableCls }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Users') }}</th>
                                    <th>{{ __('Purchase Date') }}</th>
                                    <th>{{ __('Supported Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse((($assets ?? null) instanceof Collection || is_array($assets ?? null)) ? $assets : [] as $asset)
                                    <tr>
                                        <td class="font-style">{{ data_get($asset,'name') ?: __('No asset name available') }}</td>
                                        <td>
                                            <div class="avatar-group">
                                                @php
                                                    $__users = (is_object($asset) && method_exists($asset,'users')) ? ($asset->users(data_get($asset,'employee_id')) ?? collect()) : collect();
                                                @endphp
                                                @forelse($__users as $usr)
                                                    <a href="#" class="avatar {{ $avatarSm ?? '' }}">
                                                        <img alt="{{ data_get($usr,'name') ?: __('No user name available') }}"
                                                            src="{{ (!empty(data_get($usr,'avatar')) && !empty($profilePath ?? null)) ? ($profilePath.'/'.data_get($usr,'avatar')) : asset('/storage/uploads/avatar/avatar.png') }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ data_get($usr,'name') ?: __('No user name available') }}">
                                                    </a>
                                                @empty
                                                    <span class="text-muted">{{ __('No users available') }}</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="font-style">{{ $user?->dateFormat(data_get($asset,'purchase_date')) ?? __('Failed to get purchase date') }}</td>
                                        <td class="font-style">{{ $user?->dateFormat(data_get($asset,'supported_date')) ?? __('Failed to get supported date') }}</td>
                                        <td class="font-style">{{ $user?->priceFormat((float)(data_get($asset,'amount') ?? 0)) ?? __('Failed to get amount') }}</td>
                                        <td class="font-style">{{ data_get($asset,'description') ?: __('No description available') }}</td>
                                        <td>
                                            <div class="{{ $flexBetween ?? '' }}">
                                                @can(PermissionsConstants::ED_AST)
                                                    @php
                                                        $accAstEditBase = ViewsConstants::ACC_AST.'.edit';
                                                        $accAstEditKebab = Str::kebab($accAstEditBase);
                                                        $accAstEditResolved = Route::has($accAstEditBase) ? $accAstEditBase : (Route::has($accAstEditKebab) ? $accAstEditKebab : null);
                                                        $assetIdValue = data_get($asset,'id','0');
                                                        $accAstEncryptedId = $assetIdValue ? Crypt::encrypt($assetIdValue) : null;
                                                        $accAstEditUrl = ($accAstEditResolved && $accAstEncryptedId) ? route($accAstEditResolved, $accAstEncryptedId) : '#';
                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                        $accAstEditGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::ACC_AST, 'edit_account_asset_route_unavailable') ?? 'Edit account asset route is unavailable. Please contact technical support or your domain administrator.';
                                                        $accAstEditAnchorId = 'account-asset-edit-'.$assetIdValue;
                                                    @endphp
                                                    <a
                                                        id="{{ $accAstEditAnchorId }}"
                                                        href="{{ $accAstEditUrl }}"
                                                        data-url="{{ $accAstEditUrl }}"
                                                        data-ajax-popup="true"
                                                        data-size="lg"
                                                        data-title="{{ __('Edit Asset') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        class="{{ $btnPrimary ?? '' }}"
                                                        data-guard-msg="{{ $accAstEditGuardMsg }}"
                                                        data-sv-localized="true"
                                                    >
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                try {
                                                                    const el = document.getElementById('{{ $accAstEditAnchorId }}');
                                                                    if (!el) { return; }
                                                                    if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                    el.setAttribute('data-listener-active', 'true');
                                                                    el.addEventListener('click', (e) => {
                                                                        try {
                                                                            const href = el.getAttribute('href') ?? '#';
                                                                            const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                            if (url !== '#' && href !== '#') { return; }
                                                                            e.preventDefault();
                                                                            const msg = el.getAttribute('data-guard-msg') ?? 'Edit account asset route is unavailable. Please contact technical support or your domain administrator.';
                                                                            const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                                                            let container = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (hasBootstrap) {
                                                                                const toast = document.createElement('div');
                                                                                toast.className = 'toast';
                                                                                toast.setAttribute('role', 'alert');
                                                                                toast.setAttribute('aria-live', 'assertive');
                                                                                toast.setAttribute('aria-atomic', 'true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toast.appendChild(body);
                                                                                container.appendChild(toast);
                                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                        } catch (err) {}
                                                                    });
                                                                } catch (err) {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                @endcan
                                                @can(PermissionsConstants::DEL_AST)
                                                    @php
                                                        $accAstDestroyBase = ViewsConstants::ACC_AST.'.destroy';
                                                        $accAstDestroyKebab = Str::kebab($accAstDestroyBase);
                                                        $accAstDestroyResolved = Route::has($accAstDestroyBase) ? $accAstDestroyBase : (Route::has($accAstDestroyKebab) ? $accAstDestroyKebab : null);
                                                        $assetIdValue = data_get($asset,'id','0');
                                                        $accAstEncryptedId = $assetIdValue ? Crypt::encrypt($assetIdValue) : null;
                                                        $accAstDestroyUrl = ($accAstDestroyResolved && $accAstEncryptedId) ? route($accAstDestroyResolved, $accAstEncryptedId) : '#';
                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                        $accAstDeleteGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::ACC_AST, 'delete_account_asset_route_unavailable') ?? 'Delete account asset route is unavailable. Please contact technical support or your domain administrator.';
                                                        $confirmTitle = __(Utility::fetchLinkMessage($langValue, 'generics', 'are_you_sure') ?? 'Are You Sure?');
                                                        $confirmBody = __(Utility::fetchLinkMessage($langValue, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                                        $accAstFormId = 'delete-account-asset-form-'.$assetIdValue;
                                                        $accAstAnchorId = 'account-asset-delete-btn-'.$assetIdValue;
                                                    @endphp
                                                    {!! Collective\Html\FormFacade::open(['method'=>'DELETE', 'url'=>$accAstDestroyUrl, 'id'=>$accAstFormId]) !!}
                                                        <a
                                                            id="{{ $accAstAnchorId }}"
                                                            href="#"
                                                            class="{{ $btnDanger ?? '' }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-confirm="{{ $confirmTitle }}|{{ $confirmBody }}"
                                                            data-confirm-yes="document.getElementById('{{ $accAstFormId }}').submit();"
                                                            data-url="{{ $accAstDestroyUrl }}"
                                                            data-guard-msg="{{ $accAstDeleteGuardMsg }}"
                                                            data-sv-localized="true"
                                                        >
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                try {
                                                                    const el = document.getElementById('{{ $accAstAnchorId }}');
                                                                    if (!el) { return; }
                                                                    if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                    el.setAttribute('data-listener-active', 'true');
                                                                    el.addEventListener('click', (e) => {
                                                                        try {
                                                                            const form = document.getElementById('{{ $accAstFormId }}');
                                                                            const action = form ? (form.getAttribute('action') ?? '#') : '#';
                                                                            const href = el.getAttribute('href') ?? '#';
                                                                            const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                            if (url !== '#' && href !== '#' && action !== '#') { return; }
                                                                            e.preventDefault();
                                                                            const msg = el.getAttribute('data-guard-msg') ?? 'Delete account asset route is unavailable. Please contact technical support or your domain administrator.';
                                                                            const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                                                            let container = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (hasBootstrap) {
                                                                                const toast = document.createElement('div');
                                                                                toast.className = 'toast';
                                                                                toast.setAttribute('role', 'alert');
                                                                                toast.setAttribute('aria-live', 'assertive');
                                                                                toast.setAttribute('aria-atomic', 'true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toast.appendChild(body);
                                                                                container.appendChild(toast);
                                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                            if (form) { form.setAttribute('data-failed-route', 'true'); }
                                                                        } catch (err) {}
                                                                    });
                                                                } catch (err) {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">{{ __('No assets available') }}</td>
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
