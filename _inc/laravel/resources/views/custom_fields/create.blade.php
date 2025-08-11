@php
    use Illuminate\Support\Facades\Route;
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC
    };
    use Collective\Html\FormFacade as Form;

    $lang                    = Utility::fetchUserLang();
    $routeName               = ViewsConstants::CST_FD;
    $createUrl               = Route::has($routeName)
        ? route($routeName)
        : '#';
    $formId                  = 'custom-field-create-form';
    $guardMsg                = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CST_FD,
        'custom_field_index_route_unavailable'
    ) ?? 'Custom Field index route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::open([
    'url'            => $createUrl,
    'id'             => $formId,
    'data-url'       => $createUrl,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('name', __('Custom Field Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('type', __('Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select('type', $types, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('module', __('Module'), ['class' => VC::FM_LB]) }}
                {{ Form::select('module', $modules, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button"
               value="{{ __('Cancel') }}"
               class="{{ VC::BT_LG }}"
               data-bs-dismiss="modal">
        <input type="submit"
               value="{{ __('Create') }}"
               class="{{ VC::BT_PRM }}">
    </div>
{{ Form::close() }}

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const form = document.getElementById('{{ $formId }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', event => {
                try {
                    const url = form.getAttribute('data-url') ?? '#';
                    if (url !== '#') return;
                    event.preventDefault();
                    const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                    const bsLink        = document.querySelector('link[href*="bootstrap"]');
                    let container       = document.getElementById('toast-container');
                    if (!container) {
                        container       = document.createElement('div');
                        container.id    = 'toast-container';
                        document.body.appendChild(container);
                    }
                    if (bsLink && window.bootstrap) {
                        const toastEl      = document.createElement('div');
                        toastEl.className  = 'toast';
                        toastEl.setAttribute('role','alert');
                        toastEl.setAttribute('aria-live','assertive');
                        toastEl.setAttribute('aria-atomic','true');
                        const body         = document.createElement('div');
                        body.className     = 'toast-body';
                        body.textContent   = msg;
                        toastEl.appendChild(body);
                        container.appendChild(toastEl);
                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                    } else {
                        alert(msg);
                    }
                    form.setAttribute('data-failed-route','true');
                } catch (e) {}
            });
        })();
    </script>
@endpush
