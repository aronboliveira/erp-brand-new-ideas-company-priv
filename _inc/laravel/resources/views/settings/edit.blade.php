@php
$lang ??= 'en';
	$modules ??= ['user', 'language', 'account'];
	$actions ??= [];
	$rolePermissions ??= [];
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$modules = ['user', 'language', 'account'];
		$actions = [
			'manage' => __('Manage'),
			'create' => __('Create'),
			'edit'   => __('Edit'),
			'delete' => __('Delete'),
		];
		$rolePermissions = (array)(data_get($role ?? null, 'permission') ?? []);
	} catch (\Error $e) {
		Log::error('Error in settings/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in settings/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in settings/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@extends('layouts.main')
@section('content')
<section class="section">
    <div class="section-header">
        <h1 class="d-inline">{{ __('Role') }}</h1>
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_HD }}">
                    <h4>{{ __('Update Role') }}</h4>
                </div>
                @php
                    $roleUpdateBaseName ??= '';
                    $roleUpdateKebabName ??= '';
                    $roleUpdateResolvedName ??= null;
                    $roleUpdateRouteArray ??= ['#'];
                    $roleUpdateUrl ??= '#';
                    $roleUpdateGuardMsg ??= '';
                    $roleUpdateFormId ??= 'role-update-form';
                    try {
                        $roleUpdateBaseName = ViewsConstants::RL . '.update';
                        $roleUpdateKebabName = Str::kebab($roleUpdateBaseName);
                        $roleUpdateResolvedName = Route::has($roleUpdateBaseName)
                            ? $roleUpdateBaseName
                            : (Route::has($roleUpdateKebabName) ? $roleUpdateKebabName : null);
                        $roleId = data_get($role ?? null, 'id');
                        $roleUpdateRouteArray = ($roleUpdateResolvedName && $roleId) ? [$roleUpdateResolvedName, $roleId] : ['#'];
                        $roleUpdateUrl = ($roleUpdateResolvedName && $roleId) ? (route($roleUpdateResolvedName, $roleId) ?? '#') : '#';
                        $roleUpdateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::RL, 'role_update_route_unavailable') ?? 'Role update route is unavailable. Please contact technical support or your domain administrator.';
                        $roleUpdateFormId = 'role-update-form-' . ($roleId ?: 'unknown');
                    } catch (\Error $e) {
                        Log::error('Error in settings/edit.blade.php role update @php block', [
                            'exception_class' => get_class($e),
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Exception in settings/edit.blade.php role update @php block', [
                            'exception_class' => get_class($e),
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('Throwable in settings/edit.blade.php role update @php block', [
                            'exception_class' => get_class($e),
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ]);
                    }
@endphp
                {!! Form::model($role, [
                    'route'          => $roleUpdateRouteArray,
                    'method'         => 'PUT',
                    'id'             => $roleUpdateFormId,
                    'data-url'       => $roleUpdateUrl,
                    'data-guard-msg' => $roleUpdateGuardMsg
                ]) !!}
                    @push(StacksConstants::ADM_SCR_PG)
                        <script defer>
                            (() => {
                                const form = document.getElementById('{{ $roleUpdateFormId }}');
                                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                form.setAttribute('data-listener-active', 'true');
                                form.addEventListener('submit', (e) => {
                                    try {
                                        const dataUrl = form.getAttribute('data-url') || '#';
                                        if (dataUrl !== '#') return;
                                        e.preventDefault();
                                        const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                        form.setAttribute('data-failed-route', 'true');
                                    } catch (err) {}
                                });
                            })();
                        </script>
                    @endpush
                    <div class="{{ VC::CD_BD }}">
                        <div class="{{ VC::FM_G }}">
                            {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                            {{ Form::text('name', null, ['class' => VC::FM_CT]) }}
                            @error('name')
                                <span class="invalid-name" role="alert">
                                    <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="{{ VC::FM_G }}">
                            @if(!empty($permissions))
                                <h6>{{ __('Assign Permission to Roles') }}</h6>

                                <table class="{{ VC::TB }} table-striped {{ VC::MB0 }}" id="dataTable-1">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Module') }}</th>
                                            <th>{{ __('Permissions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($modules as $module)
                                        <tr>
                                            <td>{{ ucfirst($module) }}</td>
                                            <td>
                                                @foreach($actions as $actionKey => $actionLabel)
                                                    @php
                                                        $needle = $actionKey.' '.$module;
                                                        $permId = array_search($needle, (array)$permissions, true);
@endphp
                                                    @if($permId !== false)
                                                        <div class="{{ VC::FM_CHK_IL }}">
                                                            {{ Form::checkbox(
                                                                'permissions[]',
                                                                $permId,
                                                                in_array($permId, $rolePermissions, true),
                                                                ['id' => 'permission'.$permId]
                                                            ) }}
                                                            {{ Form::label('permission'.$permId, $actionLabel) }}<br>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endif

                            @error('permissions')
                                <span class="invalid-permissions" role="alert">
                                    <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        @php
                            $roleIndexRouteBase ??= '';
                            $roleIndexRouteKebab ??= '';
                            $roleIndexResolvedName ??= null;
                            $roleIndexHref ??= '#';
                            $roleIndexGuardMsg ??= '';
                            $roleIndexCancelLinkId ??= 'role-index-cancel-link';
                            try {
                                $roleIndexRouteBase = ViewsConstants::RL . '.index';
                                $roleIndexRouteKebab = Str::kebab($roleIndexRouteBase);
                                $roleIndexResolvedName = Route::has($roleIndexRouteBase)
                                    ? $roleIndexRouteBase
                                    : (Route::has($roleIndexRouteKebab) ? $roleIndexRouteKebab : null);
                                $roleIndexHref = $roleIndexResolvedName ? (route($roleIndexResolvedName) ?? '#') : '#';
                                $roleIndexGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::RL, 'role_index_route_unavailable')
                                    ?? 'Role index route is unavailable. Please contact technical support or your domain administrator.';
                                $roleIndexCancelLinkId = 'role-index-cancel-link';
                            } catch (\Error $e) {
                                Log::error('Error in settings/edit.blade.php role index @php block', [
                                    'exception_class' => get_class($e),
                                    'message' => $e->getMessage(),
                                    'file' => $e->getFile(),
                                    'line' => $e->getLine(),
                                ]);
                            } catch (\Exception $e) {
                                Log::error('Exception in settings/edit.blade.php role index @php block', [
                                    'exception_class' => get_class($e),
                                    'message' => $e->getMessage(),
                                    'file' => $e->getFile(),
                                    'line' => $e->getLine(),
                                ]);
                            } catch (\Throwable $e) {
                                Log::error('Throwable in settings/edit.blade.php role index @php block', [
                                    'exception_class' => get_class($e),
                                    'message' => $e->getMessage(),
                                    'file' => $e->getFile(),
                                    'line' => $e->getLine(),
                                ]);
                            }
@endphp
                        <a
                            id="{{ $roleIndexCancelLinkId }}"
                            href="{{ $roleIndexHref }}"
                            data-url="{{ $roleIndexHref }}"
                            data-guard-msg="{{ base64_encode($roleIndexGuardMsg) }}"
                            class="{{ VC::BT . ' ' . VC::BT . '-danger' }}"
                        >
                            {{ __('Cancel') }}
                        </a>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/settings/roles/cancel.js') }}">
                            </script>
                        @endpush
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</section>
@endsection
