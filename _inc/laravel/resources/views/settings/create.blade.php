@php
$lang ??= 'en';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
	} catch (\Error $e) {
		Log::error('Error in settings/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in settings/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in settings/create.blade.php main @php block', [
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
                        <h4>{{ __('Create Role') }}</h4>
                    </div>
                    @php
						$roleStoreBaseName ??= '';
						$roleStoreKebabName ??= '';
						$roleStoreResolvedName ??= null;
						$roleStoreUrl ??= '#';
						$roleStoreGuardMsg ??= '';
						$roleStoreFormId ??= 'role-store-form';
						try {
							$roleStoreBaseName = ViewsConstants::RL;
							$roleStoreKebabName = Str::kebab($roleStoreBaseName);
							$roleStoreResolvedName = Route::has($roleStoreBaseName)
								? $roleStoreBaseName
								: (Route::has($roleStoreKebabName) ? $roleStoreKebabName : null);
							$roleStoreUrl = $roleStoreResolvedName ? (route($roleStoreResolvedName) ?? '#') : '#';
							$roleStoreGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::RL, 'role_store_route_unavailable')
								?? 'Role store route is unavailable. Please contact technical support or your domain administrator.';
						} catch (\Error $e) {
							Log::error('Error in settings/create.blade.php role store @php block', [
								'exception_class' => get_class($e),
								'message' => $e->getMessage(),
								'file' => $e->getFile(),
								'line' => $e->getLine(),
							]);
						} catch (\Exception $e) {
							Log::error('Exception in settings/create.blade.php role store @php block', [
								'exception_class' => get_class($e),
								'message' => $e->getMessage(),
								'file' => $e->getFile(),
								'line' => $e->getLine(),
							]);
						} catch (\Throwable $e) {
							Log::error('Throwable in settings/create.blade.php role store @php block', [
								'exception_class' => get_class($e),
								'message' => $e->getMessage(),
								'file' => $e->getFile(),
								'line' => $e->getLine(),
							]);
						}
@endphp
                    {!! Form::open([
                        'url'            => $roleStoreUrl,
                        'method'         => 'post',
                        'id'             => $roleStoreFormId,
                        'data-url'       => $roleStoreUrl,
                        'data-guard-msg' => $roleStoreGuardMsg
                    ]) !!}
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/settings/store.js') }}"></script>
                        @endpush
                        <div class="{{ VC::CD_BD }}">
                            <div class="{{ VC::FM_G }}">
                                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                                {{ Form::text('name', old('name'), [
                                    'class' => VC::FM_CT,
                                    'placeholder' => __('Enter Role Name'),
                                ]) }}
                                @error('name')
                                    <span class="invalid-name" role="alert">
                                        <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="{{ VC::FM_G }}">
                                @if (!empty($permissions))
                                    <h6>{{ __('Assign Permission to Roles') }}</h6>
                                    <table class="{{ VC::TB }} table-striped {{ VC::MB0 }}" id="dataTable-1">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Module') }}</th>
                                                <th>{{ __('Permissions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                try {
                                                    $modules = ['user', 'language', 'account'];
                                                    $actions = [
                                                        'manage' => __('Manage'),
                                                        'create' => __('Create'),
                                                        'edit'   => __('Edit'),
                                                        'delete' => __('Delete'),
                                                    ];
                                                } catch (\Throwable $e) {
                                                    \Log::error('settings/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            @foreach ($modules as $module)
                                                <tr>
                                                    <td>{{ ucfirst($module) }}</td>
                                                    <td>
                                                        @foreach ($actions as $actionKey => $actionLabel)
                                                            @php
                                                                try {
                                                                    $needle = $actionKey . ' ' . $module;
                                                                    $key = in_array($needle, (array) $permissions, true)
                                                                        ? array_search($needle, $permissions, true)
                                                                        : false;
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('settings/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp

                                                            @if ($key !== false)
                                                                <div class="{{ VC::FM_CHK_IL }}">
                                                                    {{ Form::checkbox('permissions[]', $key, false, ['id' => 'permission'.$key]) }}
                                                                    {{ Form::label('permission'.$key, $actionLabel) }}<br>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer">
                            {{ Form::submit(__('Create'), ['class' => VC::BT_PRM]) }}
                            @php
                                try {
                                    $roleIndexBaseName        = ViewsConstants::RL . '.index';
                                    $roleIndexKebabName       = Str::kebab($roleIndexBaseName);
                                    $roleIndexResolvedName    = Route::has($roleIndexBaseName) ? $roleIndexBaseName : (Route::has($roleIndexKebabName) ? $roleIndexKebabName : null);
                                    $roleIndexUrl             = $roleIndexResolvedName ? route($roleIndexResolvedName) : '#';
                                    $roleIndexGuardMsg        = Utility::fetchLinkMessage($lang, ViewsConstants::RL, 'role_index_route_unavailable') ?? 'Role index route is unavailable. Please contact technical support or your domain administrator.';
                                    $roleIndexLinkId          = 'role-index-cancel-link';
                                } catch (\Throwable $e) {
                                    \Log::error('settings/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            <a
                                id="{{ $roleIndexLinkId }}"
                                href="{{ $roleIndexUrl }}"
                                data-url="{{ $roleIndexUrl }}"
                                data-guard-msg="{{ base64_encode($roleIndexGuardMsg) }}"
                                class="{{ VC::BT . ' ' . VC::BT . '-danger' }}"
                            >
                                {{ __('Cancel') }}
                            </a>
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/settings/cancel.js') }}"></script>
                            @endpush
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </section>
@endsection
