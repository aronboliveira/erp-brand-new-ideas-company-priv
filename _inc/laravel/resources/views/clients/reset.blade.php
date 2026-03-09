@php
    try {
$lang          = Utility::fetchUserLang();
        $routeName     = 'client.password.update';
        $updateRoute   = Route::has($routeName)
            ? route($routeName, $user->id)
            : (Route::has(Str::kebab($routeName))
                ? route(Str::kebab($routeName), $user->id)
                : '#');
        $formId        = 'clientPasswordUpdateForm_' . $user->id;
        $guardMsg      = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::USR,
            'client_password_update_route_unavailable'
        ) ?? 'Client password update route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('clients/reset — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@if (!empty($user) && isset($user->id))
    {{ Form::model($user, [
        'route'          => [$updateRoute],
        'method'         => 'post',
        'id'             => $formId,
        'data-url'       => $updateRoute,
        'data-guard-msg' => $guardMsg,
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('password', __('Password'), ['class' => VC::FM_LB]) }}
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        class="{{ VC::FM_CT }} @error('password') is-invalid @enderror"
                    >
                    @error('password')
                        <span class="{{ VC::INV_FB }}" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('password_confirmation', __('Confirm Password'), ['class' => VC::FM_LB]) }}
                    <input
                        id="password-confirm"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="{{ VC::FM_CT }}"
                    >
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input
                type="button"
                value="{{ __('Cancel') }}"
                data-bs-dismiss="modal"
                class="{{ VC::BT_LG }}"
            >
            <input
                type="submit"
                value="{{ __('Update') }}"
                class="{{ VC::BT_PRM }}"
            >
        </div>
        <script defer>window.RouteGuard?.guardFormSubmit?.('{{ $formId }}');</script>
    {{ Form::close() }}
@else
    <div class="{{ VC::ALERT }} {{ VC::ALERT_DANGER }} {{ VC::MG_B0 }}" role="alert">
        {{ __('User information is unavailable. Please contact technical support or your domain administrator.') }}
    </div>
@endif
