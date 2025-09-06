@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC
    };

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
                        <span class="invalid-feedback" role="alert">
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
        <script defer>
            (() => {
                const form = document.getElementById('{{ $formId }}');
                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                form.setAttribute('data-listener-active', 'true');
                form.addEventListener('submit', event => {
                    try {
                        const action = form.getAttribute('action');
                        const url    = form.getAttribute('data-url');
                        if ((action && action !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container       = document.getElementById('toast-container');
                        if (!container) {
                            container       = document.createElement('div');
                            container.id    = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl      = document.createElement('div');
                            toastEl.className  = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body         = document.createElement('div');
                            body.className     = 'toast-body';
                            body.textContent   = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        form.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            })();
        </script>
    {{ Form::close() }}
@else
    <div class="{{ VC::ALERT }} {{ VC::ALERT_DANGER }} {{ VC::MG_B0 }}" role="alert">
        {{ __('User information is unavailable. Please contact technical support or your domain administrator.') }}
    </div>
@endif