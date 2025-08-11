@php
    use Illuminate\Support\Facades\Route;
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC
    };
    use Collective\Html\FormFacade as Form;

    $lang               = Utility::fetchUserLang();
    $importRouteName    = ViewsConstants::CST . '.import';
    $importUrl          = Route::has($importRouteName)
        ? route($importRouteName)
        : '#';
    $formId             = 'customer-csv-import-form';
    $guardMsg           = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CST,
        'customers_import_route_unavailable'
    ) ?? 'Customer CSV import route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::open([
    'route'            => $importUrl,
    'method'         => 'post',
    'enctype'        => 'multipart/form-data',
    'id'             => $formId,
    'data-url'       => $importUrl,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }} mb-6">
                {{ Form::label('file', __('Download sample customer CSV file'), ['class' => VC::FM_LB]) }}
                <a href="{{ asset(Storage::url('uploads/sample/sample-customer.csv')) }}" class="{{ VC::BT_SM_PM }}">
                    <i class="{{ VC::TI_DWN }}"></i> {{ __('Download') }}
                </a>
            </div>

            <div class="{{ VC::C12 }}">
                {{ Form::label('file', __('Select CSV File'), ['class' => VC::FM_LB]) }}
                <div class="choose-file {{ VC::FM_G }}">
                    <label for="file" class="{{ VC::FM_LB }}">
                        <input
                            type="file"
                            class="{{ VC::FM_CT }}"
                            name="file"
                            id="file"
                            data-filename="upload_file"
                            required
                        >
                    </label>
                    <p class="upload_file"></p>
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
            value="{{ __('Upload') }}"
            class="{{ VC::BT_PRM }}"
        >
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
