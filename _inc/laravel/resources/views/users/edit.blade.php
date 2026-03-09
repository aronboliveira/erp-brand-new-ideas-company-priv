@php
$userAuth ??= null;
	$lang ??= 'en';
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateName ??= null;
	$updateUrl ??= '#';
	$updateGuard ??= '';
	$formId ??= 'user-update-form';
	$fields ??= [];
	try {
		$userAuth = Auth::user();
		$lang = Utility::fetchUserLang(user: $userAuth) ?? 'en';
		$updateBase = VW::USR . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateName = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$userId = data_get($user ?? null, 'id', '');
		$updateUrl = ($updateName && $userId) ? (route($updateName, [$userId]) ?? '#') : '#';
		$updateGuard = Utility::fetchLinkMessage($lang, VW::USR, 'update_user_route_unavailable')
			?? 'Update user route is unavailable. Please contact technical support or your domain administrator.';
		$fields = [
			['name' => 'name',  'type' => 'text', 'label' => __('Name'),  'cols' => 6, 'attrs' => ['class' => 'form-control font-style', 'placeholder' => __('Enter User Name')],  'error' => 'name'],
			['name' => 'email', 'type' => 'text', 'label' => __('Email'), 'cols' => 6, 'attrs' => ['class' => 'form-control', 'placeholder' => __('Enter User Email')],      'error' => 'email'],
		];
	} catch (\Error $e) {
		Log::error('Error in users/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in users/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in users/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{!! Form::model($user, [
    'url'                  => $updateUrl,
    'method'               => 'PUT',
    'id'                   => $formId,
    'data-resolved-action' => $updateUrl,
    'data-guard-msg'       => $updateGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            @foreach ($fields as $f)
                @php
                    try {
                        $cols  = (int) ($f['cols'] ?? 6);
                        $type  = $f['type'] ?? 'text';
                        $name  = $f['name'] ?? '';
                        $label = $f['label'] ?? '';
                        $attrs = $f['attrs'] ?? [];
                        $extra = trim(preg_replace('/\bform-control\b/', '', $attrs['class'] ?? ''));
                        $attrs['class'] = trim(VC::FM_CT . ($extra ? ' ' . $extra : ''));
                    } catch (\Throwable $e) {
                        \Log::error('users/edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <div class="col-md-{{ $cols }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label($name, $label, ['class' => VC::FM_LB]) }}
                        @switch($type)
                            @case('password')
                                {{ Form::password($name, $attrs) }}
                                @break
                            @case('text')
                            @default
                                {{ Form::text($name, null, $attrs) }}
                                @break
                        @endswitch
                        @error($f['error'])
                            <small class="{{ VC::TX_DNG }}" role="alert"><strong>{{ $message }}</strong></small>
                        @enderror
                    </div>
                </div>
            @endforeach

            @if($userAuth?->{UsersConstants::COL_TP} != PermissionsConstants::SA)
                <div class="{{ VC::FM_G }} {{ VC::CM12 }}">
                    {{ Form::label('role', __('User Role'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('role', $roles ?? [], $user?->roles, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                    @error('role')
                        <small class="{{ VC::TX_DNG }}" role="alert"><strong>{{ $message }}</strong></small>
                    @enderror
                </div>
            @endif

            @if(!empty($customFields) && !$customFields->isEmpty())
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::TAB_FD_SH }}" id="tab-2" role="tabpanel">
                        @include(VW::CST_FD . '.formBuilder')
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
    </div>

    <script defer src="{{ asset('assets/js/routes/users/update.js') }}"></script>
{!! Form::close() !!}
