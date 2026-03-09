@php
$user ??= null;
	$lang ??= 'en';
	try {
		$user = Auth::user();
		$lang = Utility::fetchUserLang(user: $user) ?? 'en';
	} catch (\Error $e) {
		Log::error('Error in resignations/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in resignations/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in resignations/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Resignation')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Resignation')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create resignation')
            @php
                try {
                    $rsgCreateBase = VW::RSG.'.create';
                    $rsgCreateKebab = Str::kebab($rsgCreateBase);
                    $rsgCreateResolved = Route::has($rsgCreateBase) ? $rsgCreateBase : (Route::has($rsgCreateKebab) ? $rsgCreateKebab : null);
                    $rsgCreateUrl = $rsgCreateResolved ? route($rsgCreateResolved) : '#';
                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                    $rsgCreateGuardMsg = Utility::fetchLinkMessage($langValue, VW::RSG, 'create_resignation_route_unavailable') ?? 'Create resignation route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('resignations/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a
                href="{{ $rsgCreateUrl }}"
                data-size="lg"
                data-url="{{ $rsgCreateUrl }}"
                data-ajax-popup="true"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                data-title="{{ __('Create New Resignation') }}"
                data-guard-msg="{{ base64_encode($rsgCreateGuardMsg) }}"
                data-sv-localized="true"
                class="{{ VC::BT_SM_PM }} resignation-create"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script src="{{ asset('assets/js/routes/resignations/create.js') }}" defer></script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    @php
        try {
            $canManage = Gate::check('edit resignation') || Gate::check('delete resignation');
            $isCompany = auth()?->check() && auth()->user()?->hasRole('company');
            $colspan   = ($isCompany ? 4 : 3) + ($canManage ? 1 : 0);
        } catch (\Throwable $e) {
            \Log::error('resignations/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable" id="resignation-table">
                            <thead>
                                <tr>
                                    @role('company')
                                        <th>{{ __('Employee Name') }}</th>
                                    @endrole
                                    <th>{{ __('Resignation Date') }}</th>
                                    <th>{{ __('Last Working Date') }}</th>
                                    <th>{{ __('Reason') }}</th>
                                    @if($canManage)
                                        <th class="{{ VC::TX_END }}" width="200">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @forelse ($resignations as $resignation)
                                    <tr>
                                        @role('company')
                                            <td>{{ $resignation->employee->name ?? __('Anonymous Employee') }}</td>
                                        @endrole
                                        <td>{{ $user?->dateFormat($resignation->notice_date) ?? __('Failed to date data') }}</td>
                                        <td>{{ $user?->dateFormat($resignation->resignation_date) ?? __('Failed to date data') }}</td>
                                        <td>{{ Str::limit($resignation->description, 120) }}</td>
                                        @if($canManage)
                                            <td class="{{ VC::TX_END }}">
                                                @can('edit resignation')
                                                    @php
                                                        try {
                                                            $rsgEditBase = VW::RSG.'.edit';
                                                            $rsgEditKebab = Str::kebab($rsgEditBase);
                                                            $rsgEditResolved = Route::has($rsgEditBase) ? $rsgEditBase : (Route::has($rsgEditKebab) ? $rsgEditKebab : null);
                                                            $rsgIdValue = isset($resignation) && !empty($resignation->id) ? $resignation->id : null;
                                                            $rsgEncryptedId = $rsgIdValue ? Crypt::encrypt($rsgIdValue) : null;
                                                            $rsgEditUrl = ($rsgEditResolved && $rsgEncryptedId) ? route($rsgEditResolved, $rsgEncryptedId) : '#';
                                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                            $rsgEditGuardMsg = Utility::fetchLinkMessage($langValue, VW::RSG, 'edit_resignation_route_unavailable') ?? 'Edit resignation route is unavailable. Please contact technical support or your domain administrator.';
                                                            $rsgEditAnchorId = 'resignation-edit-btn-'.($rsgIdValue ?? 'x');
                                                        } catch (\Throwable $e) {
                                                            \Log::error('resignations/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <a
                                                        id="{{ $rsgEditAnchorId }}"
                                                        href="{{ $rsgEditUrl }}"
                                                        class="{{ VC::BT_SM_CT }}"
                                                        data-size="lg"
                                                        data-url="{{ $rsgEditUrl }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Resignation') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        aria-label="{{ __('Edit Resignation') }}"
                                                        data-guard-msg="{{ base64_encode($rsgEditGuardMsg) }}"
                                                        data-sv-localized="true"
                                                    >
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                try {
                                                                    const el = document.getElementById('{{ $rsgEditAnchorId }}');
                                                                    if (!el) { return; }
                                                                    if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                    el.setAttribute('data-listener-active', 'true');
                                                                    el.addEventListener('click', (e) => {
                                                                        try {
                                                                            const href = el.getAttribute('href') ?? '#';
                                                                            const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                            if (url !== '#' && href !== '#') { return; }
                                                                            e.preventDefault();
                                                                            const msg = el.getAttribute('data-guard-msg') ?? 'Edit resignation route is unavailable. Please contact technical support or your domain administrator.';
                                                                            const RG = window.RouteGuard || {};
                                                                            (RG.showToast || (m => alert(m)))(msg);
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                        } catch (err) {}
                                                                    });
                                                                } catch (err) {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                @endcan
                                                @can('delete resignation')
                                                    @php
                                                        try {
                                                            $rsgDestroyBase = VW::RSG.'.destroy';
                                                            $rsgDestroyKebab = Str::kebab($rsgDestroyBase);
                                                            $rsgDestroyResolved = Route::has($rsgDestroyBase) ? $rsgDestroyBase : (Route::has($rsgDestroyKebab) ? $rsgDestroyKebab : null);
                                                            $rsgIdValue = isset($resignation) && !empty($resignation->id) ? $resignation->id : null;
                                                            $rsgEncryptedId = $rsgIdValue ? Crypt::encrypt($rsgIdValue) : null;
                                                            $rsgDestroyUrl = ($rsgDestroyResolved && $rsgEncryptedId) ? route($rsgDestroyResolved, $rsgEncryptedId) : '#';
                                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                            $rsgDeleteGuardMsg = Utility::fetchLinkMessage($langValue, VW::RSG, 'delete_resignation_route_unavailable') ?? 'Delete resignation route is unavailable. Please contact technical support or your domain administrator.';
                                                            $formToken = (string) Str::uuid();
                                                            $formId = 'delete-form-'.$formToken;
                                                            $anchorId = 'resignation-delete-btn-'.$formToken;
                                                            $confirmTitle = __(Utility::fetchLinkMessage($langValue, 'generics', 'are_you_sure') ?? 'Are You Sure?');
                                                            $confirmBody = __(Utility::fetchLinkMessage($langValue, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                                        } catch (\Throwable $e) {
                                                            \Log::error('resignations/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    {!! Form::open(['method' => 'DELETE', 'url' => $rsgDestroyUrl, 'id' => $formId, 'class' => 'd-inline']) !!}
                                                        <a
                                                            id="{{ $anchorId }}"
                                                            href="#"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            aria-label="{{ __('Delete Resignation') }}"
                                                            data-confirm="{{ $confirmTitle }}|{{ $confirmBody }}"
                                                            data-confirm-yes="document.getElementById('{{ $formId }}').submit();"
                                                            data-url="{{ $rsgDestroyUrl }}"
                                                            data-guard-msg="{{ base64_encode($rsgDeleteGuardMsg) }}"
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
                                                                            const msg = el.getAttribute('data-guard-msg') ?? 'Delete resignation route is unavailable. Please contact technical support or your domain administrator.';
                                                                            const RG = window.RouteGuard || {};
                                                                            (RG.showToast || (m => alert(m)))(msg);
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                            if (form) { form.setAttribute('data-failed-route', 'true'); }
                                                                        } catch (err) {}
                                                                    });
                                                                } catch (err) {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $colspan }}" class="{{ VC::TXCT_MT }} {{ VC::PY4 }}">
                                            {{ __('No resignations found.') }}
                                        </td>
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
