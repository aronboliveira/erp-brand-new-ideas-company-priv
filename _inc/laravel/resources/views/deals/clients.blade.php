@php
    use Collective\Html\FormFacade as Form;
    use App\MOdels\Utility;
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC};
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
    $routeKey       = ViewsConstants::DL . '.clients.update';
    $kebabRouteKey  = Str::kebab($routeKey);
    $hasRoute       = Route::has($routeKey);
    $hasKebab       = Route::has($kebabRouteKey);
    $updateRouteName = $hasRoute
        ? $routeKey
        : ($hasKebab ? $kebabRouteKey : null);
    $updateRouteUrl = $updateRouteName
        ? route($updateRouteName, $deal->id)
        : '#';
    $updateGuardMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::DL,
        'clients_update_route_unavailable'
    ) ?? 'Update deals with clients route is unavailable. Please contact technical support or your domain administrator.';
@endphp
{!! Form::model($deal, [
    'route'  => $updateRouteName
        ? [$updateRouteName, $deal->id]
        : $updateRouteUrl,
    'method' => 'PUT',
    'id'     => 'update-clients-form-' . $deal->id,
    'data-url'       => $updateRouteUrl,
    'data-guard-msg' => $updateGuardMsg
]) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('clients', __('Clients'), ['class' => VC::FM_LB]) }}
                {{ Form::select(
                    'clients[]',
                    $clients,
                    false,
                    [
                        'class'    => VC::FM_CT . ' select2',
                        'id'       => 'choices-multiple1',
                        'multiple' => '',
                        'required' => 'required'
                    ]
                ) }}
            </div>
        </div>
    </div>
    <div class="{{ VC::DFL }} modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            {{ __('Cancel') }}
        </button>
        <button type="submit" class="{{ VC::BT_PRM }}">
            {{ __('Create') }}
        </button>
    </div>
{{ Form::close() }}
@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const form = document.getElementById('update-clients-form-{{ $deal->id }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', e => {
                try {
                    const url = form.getAttribute('data-url') || '#';
                    if (url !== '#') return;
                    e.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                    const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                    let container = document.getElementById('toast-container');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'toast-container';
                        document.body.appendChild(container);
                    }
                    if (bs) {
                        const toast = document.createElement('div');
                        toast.className = 'toast';
                        toast.setAttribute('role','alert');
                        toast.setAttribute('aria-live','assertive');
                        toast.setAttribute('aria-atomic','true');
                        const body = document.createElement('div');
                        body.className = 'toast-body';
                        body.textContent = msg;
                        toast.appendChild(body);
                        container.appendChild(toast);
                        bootstrap.Toast.getOrCreateInstance(toast).show();
                    } else {
                        alert(msg);
                    }
                    form.setAttribute('data-failed-route', 'true');
                } catch (error) {}
            });
        })();
    </script>
@endpush
