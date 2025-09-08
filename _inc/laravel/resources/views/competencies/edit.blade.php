@php
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC
    };

    $lang         = Utility::fetchUserLang();
    $routeName    = ViewsConstants::CPT . '.update';
    $updateRoute  = Route::has($routeName)
        ? route($routeName, $competencies->id)
        : (Route::has(Str::kebab($routeName))
            ? route(Str::kebab($routeName), $competencies->id)
            : '#');
    $formId       = 'competencyUpdateForm_' . $competencies->id;
    $guardMsg     = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CPT,
        'competency_update_route_unavailable'
    ) ?? 'Competency update route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@if((is_array($competencies) && count($competencies)) || ($competencies instanceof Collection && $competencies->isNotEmpty()))
    {{ Form::model($competencies, [
        'route'          => [$updateRoute],
        'method'         => 'PUT',
        'id'             => $formId,
        'data-url'       => $updateRoute,
        'data-guard-msg' => $guardMsg,
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                        {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                    </div>
                </div>
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('type', __('Type'), ['class' => VC::FM_LB]) }}
                        {{ Form::select('type', $performance, null, ['class' => VC::FM_CT . ' select', 'required' => 'required']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input
                type="button"
                value="{{ __('Cancel') }}"
                class="{{ VC::BT_LG }}"
                data-bs-dismiss="modal"
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
    <div class="text-muted">{{ __('No competencies found') }}</div>
@endif