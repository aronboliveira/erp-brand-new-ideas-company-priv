@php
    try {
$lang = Utility::fetchUserLang();

        $formId    = 'pms-store-form';
        $base      = VW::PMS . '.store';
        $baseKebab = Str::kebab($base);
        $routeRes  = Route::has($base) ? $base : (Route::has($baseKebab) ? $baseKebab : null);
        $actionUrl = $routeRes ? route($routeRes) : '#';
        $guardMsg  = Utility::fetchLinkMessage($lang, VW::PMS, 'store_route_unavailable') ?? __('Permission store route is unavailable. Please contact technical support or your domain administrator.');

        $nameErr = $errors->has('name');
        $nameAttrs = [
            'id'               => 'name',
            'class'            => trim(VC::FM_CT . ' ' . ($nameErr ? 'is-invalid' : '')),
            'placeholder'      => __('Enter Permission Name'),
            'required'         => 'required',
            'aria-invalid'     => $nameErr ? 'true' : 'false',
            'aria-describedby' => $nameErr ? 'name-error' : null,
            'autocomplete'     => 'off',
        ];

        $rolesIsList = (is_array($roles ?? null) && count($roles ?? []) > 0) || (($roles ?? null) instanceof Collection && $roles->isNotEmpty());
    } catch (\Throwable $e) {
        \Log::error('permissions/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'route'               => $actionUrl,
    'method'            => 'POST',
    'id'                => $formId,
    'data-url'          => $actionUrl,
    'data-guard-msg'    => $guardMsg,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="{{ VC::FM_GCB12 }}">
            {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
            {{ Form::text('name', null, $nameAttrs) }}
            @error('name')
                <span id="name-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
            @enderror
        </div>

        <div class="{{ VC::FM_GCB12 }}">
            <h6 class="{{ VC::MB3_FW600 }}">{{ __('Assign Permission to Roles') }}</h6>
            @if($rolesIsList)
                @foreach(($roles instanceof Collection) ? $roles : collect($roles) as $role)
                    @php
                        try {
                            $roleId   = (string) data_get($role, 'id', '');
                            $roleName = (string) data_get($role, 'name', __('(Role name unavailable)'));
                            $inputId  = $roleId !== '' ? ('role'.$roleId) : ('role-x-'.$loop->index);
                        } catch (\Throwable $e) {
                            \Log::error('permissions/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    <div class="{{ VC::CST_CT_CB }}">
                        {{ Form::checkbox(
                            'roles[]',
                            $roleId !== '' ? $roleId : '0',
                            false,
                            array_filter([
                                'class' => 'custom-control-input',
                                'id'    => $inputId,
                                $roleId === '' ? 'disabled' : null,
                            ], 'strlen')
                        ) }}
                        {{ Form::label($inputId, $roleName, ['class' => VC::CST_LB]) }}
                    </div>
                @endforeach
            @else
                <p class="{{ VC::TXT_MT }}">{{ __('No roles available.') }}</p>
            @endif
            @error('roles')
                <span class="invalid-roles {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/permissions/store.js') }}"></script>
{{ Form::close() }}
