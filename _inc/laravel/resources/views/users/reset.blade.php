@php
    try {
$lang = Utility::fetchUserLang();

        $updBase   = VW::USR . '.password.update';
        $updKebab  = Str::kebab($updBase);
        $updName   = Route::has($updBase) ? $updBase : (Route::has($updKebab) ? $updKebab : null);
        $updUrl    = ($updName && ($user?->id)) ? route($updName, [$user?->id]) : '#';
        $updGuard  = Utility::fetchLinkMessage($lang, VW::USR, 'update_password_route_unavailable')
                     ?? 'Update user password route is unavailable. Please contact technical support or your domain administrator.';
        $formId    = 'user-password-update-form-' . ($user?->id ?? 'unknown');
    } catch (\Throwable $e) {
        \Log::error('users/reset — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{!! Form::model($user, [
    'url'                  => $updUrl,
    'method'               => 'POST',
    'id'                   => $formId,
    'data-resolved-action' => $updUrl,
    'data-guard-msg'       => $updGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::FM_G }}">
                {{ Form::label('password', __('Password'), ['class' => VC::FM_LB]) }}
                <input id="password"
                       type="password"
                       class="{{ VC::FM_CT }} @error('password') is-invalid @enderror"
                       name="password"
                       required
                       autocomplete="new-password">
                @error('password')
                    <span class="{{ VC::INV_FB }}" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="{{ VC::FM_G }}">
                {{ Form::label('password_confirmation', __('Confirm Password'), ['class' => VC::FM_LB]) }}
                <input id="password-confirm"
                       type="password"
                       class="{{ VC::FM_CT }}"
                       name="password_confirmation"
                       required
                       autocomplete="new-password">
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>

    <script defer src="{{ asset('assets/js/routes/users/profiles/resetPassword.js') }}"></script>
{!! Form::close() !!}
