@php
$user ??= null;
	$lang ??= 'en';
	$profilePath ??= '';
	$row ??= VC::RW;
	$card ??= VC::CD;
	$btnPrimary ??= VC::BT_SM_PM;
	$btnDanger ??= VC::ACT_BTN_DNG_2;
	$flexBetween ??= VC::DFL_JCB;
	$avatarSm ??= VC::AV_CC_SM;
	$tableCls ??= VC::TB;
	try {
		$user = Auth::user();
		$lang = Utility::fetchUserLang(user: $user) ?? 'en';
		$profilePath = Utility::getFile('uploads/avatar/') ?? '';
	} catch (\Error $e) {
		Log::error('Error in assets/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in assets/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in assets/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Assets') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Assets') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can(PermissionsConstants::CRT_AST)
            @php
                try {
                    $accAstCreateBase = ViewsConstants::ACC_AST.'.create';
                    $accAstCreateKebab = Str::kebab($accAstCreateBase);
                    $accAstCreateResolved = Route::has($accAstCreateBase) ? $accAstCreateBase : (Route::has($accAstCreateKebab) ? $accAstCreateKebab : null);
                    $accAstCreateUrl = $accAstCreateResolved ? route($accAstCreateResolved) : '#';
                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                    $accAstCreateGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::ACC_AST, 'create_account_asset_route_unavailable') ?? 'Create account asset route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('assets/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
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
                data-guard-msg="{{ base64_encode($accAstCreateGuardMsg) }}"
                data-sv-localized="true"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
                <script defer src="{{ asset('assets/js/routes/accountAssets/create.js') }}"></script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ $row }}">
        <div class="{{ VC::CM12 }}">
            <div class="{{ $card }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
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
                                                    $__users = (is_object($asset) && method_exists($asset,'employees')) ? ($asset->employees ?? collect()) : collect();
@endphp
                                                @forelse($__users as $usr)
                                                    <a href="#" class="avatar {{ $avatarSm ?? '' }}">
                                                        <img alt="{{ data_get($usr,'name') ?: __('No user name available') }}"
                                                            src="{{ (!empty(data_get($usr,'avatar')) && !empty($profilePath ?? null)) ? ($profilePath.'/'.data_get($usr,'avatar')) : asset('/storage/uploads/avatar/avatar.png') }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ data_get($usr,'name') ?: __('No user name available') }}">
                                                    </a>
                                                @empty
                                                    <span class="{{ VC::TXT_MT }}">{{ __('No users available') }}</span>
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
                                                        try {
                                                            $accAstEditBase = ViewsConstants::ACC_AST.'.edit';
                                                            $accAstEditKebab = Str::kebab($accAstEditBase);
                                                            $accAstEditResolved = Route::has($accAstEditBase) ? $accAstEditBase : (Route::has($accAstEditKebab) ? $accAstEditKebab : null);
                                                            $assetIdValue = data_get($asset,'id','0');
                                                            $accAstEncryptedId = $assetIdValue ? Crypt::encrypt($assetIdValue) : null;
                                                            $accAstEditUrl = ($accAstEditResolved && $accAstEncryptedId) ? route($accAstEditResolved, $accAstEncryptedId) : '#';
                                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                            $accAstEditGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::ACC_AST, 'edit_account_asset_route_unavailable') ?? 'Edit account asset route is unavailable. Please contact technical support or your domain administrator.';
                                                            $accAstEditAnchorId = 'account-asset-edit-'.$assetIdValue;
                                                        } catch (\Throwable $e) {
                                                            \Log::error('assets/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
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
                                                        data-guard-msg="{{ base64_encode($accAstEditGuardMsg) }}"
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
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
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
                                                        try {
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
                                                        } catch (\Throwable $e) {
                                                            \Log::error('assets/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
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
                                                            data-guard-msg="{{ base64_encode($accAstDeleteGuardMsg) }}"
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
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
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
                                        <td colspan="7" class="{{ VC::TXCT_MT }}">{{ __('No assets available') }}</td>
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
