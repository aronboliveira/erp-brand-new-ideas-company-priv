@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        ViewClassNamesConstants as VC,
        StacksConstants
    };
    use Collective\Html\FormFacade as Form;

    $lang            = Utility::fetchUserLang();
    $branchStoreRoute = Route::has(ViewsConstants::BRC)
        ? route(ViewsConstants::BRC)
        : '#';
    $formId           = 'create-branch-form';
    $branchStoreMsg   = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::BRC,
        'branch_store_route_unavailable'
    ) ?? 'Branch store route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{!! Form::open([
    'url'            => $branchStoreRoute,
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $branchStoreRoute,
    'data-guard-msg' => $branchStoreMsg,
]) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::FM_G }}">
                    {!! Form::label('name', __('Name'), ['class' => VC::FM_LB]) !!}
                    {!! Form::text('name', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Branch Name')]) !!}
                    @error('name')
                        <span class="invalid-name" role="alert">
                            <strong class="{{ VC::TXT_MT }}">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
    </div>
{!! Form::close() !!}

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
