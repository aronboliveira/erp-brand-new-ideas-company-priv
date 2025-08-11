@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC
    };

    $lang       = Utility::fetchUserLang();
    $routeName  = ViewsConstants::COA . '.store';
    $storeRoute = Route::has($routeName)
        ? route($routeName)
        : (Route::has(Str::kebab($routeName))
            ? route(Str::kebab($routeName))
            : '#');
    $formId     = 'chartOfAccountsCreateForm';
    $guardMsg   = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::COA,
        'chart_of_account_store_route_unavailable'
    ) ?? 'Chart of Account store route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Collective\Html\FormFacade::open([
    'route'          => $storeRoute,
    'method'         => 'POST',
    'id'             => $formId,
    'data-url'       => $storeRoute,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Collective\Html\FormFacade::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Collective\Html\FormFacade::text('name', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Collective\Html\FormFacade::label('code', __('Code'), ['class' => VC::FM_LB]) }}
                {{ Collective\Html\FormFacade::number('code', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Collective\Html\FormFacade::label('type', __('Account'), ['class' => VC::FM_LB]) }}
                {{ Collective\Html\FormFacade::select('type', $types, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Collective\Html\FormFacade::label('sub_type', __('Type'), ['class' => VC::FM_LB]) }}
                <select name="sub_type" id="sub_type" class="{{ VC::FM_CT_SL }}" required></select>
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Collective\Html\FormFacade::label('is_enabled', __('Is Enabled'), ['class' => VC::FM_LB]) }}
                <div class="form-check form-switch">
                    <input type="checkbox" name="is_enabled" id="is_enabled" class="form-check-input" checked>
                    <label for="is_enabled" class="form-check-label"></label>
                </div>
            </div>
            <div class="form-group col-md-12">
                {{ Collective\Html\FormFacade::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Collective\Html\FormFacade::textarea('description', null, ['class' => VC::FM_CT, 'rows' => 2]) }}
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
