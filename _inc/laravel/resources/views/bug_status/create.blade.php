@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC
    };

    $lang                   = Utility::fetchUserLang();
    $routeName              = ViewsConstants::BUG_STT;
    $bugstatusStoreRoute    = Route::has($routeName)
        ? route($routeName)
        : (Route::has(Str::kebab($routeName))
            ? route(Str::kebab($routeName))
            : '#');
    $formId                 = 'bugstatus-store-form';
    $guardMsg               = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::BUG_STT,
        'bug_status_store_route_unavailable'
    ) ?? 'Bug Status store route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Collective\Html\FormFacade::open([
    'url'            => $bugstatusStoreRoute,
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $bugstatusStoreRoute,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Collective\Html\FormFacade::label('title', __('Bug Status Title'), ['class' => VC::FM_LB]) }}
                {{ Collective\Html\FormFacade::text('title', '', [
                    'class'    => VC::FM_CT,
                    'required' => 'required',
                ]) }}
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
            value="{{ __('Create') }}"
            class="{{ VC::BT_PRM }}"
        >
    </div>
{{ Collective\Html\FormFacade::close() }}

@push(StacksConstants::ADM_SCR_PG)
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
@endpush
