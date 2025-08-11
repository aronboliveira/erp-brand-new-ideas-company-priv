@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{
        ViewsConstants,
        ViewClassNamesConstants as VC,
        StacksConstants
    };
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;

    $lang                    = Utility::fetchUserLang();
    $bankTrfUpdateRoute      = Route::has(ViewsConstants::BNK_TRF . '.update')
        ? route(ViewsConstants::BNK_TRF . '.update', $transfer->id)
        : (Route::has(Str::kebab(ViewsConstants::BNK_TRF . '.update'))
            ? route(Str::kebab(ViewsConstants::BNK_TRF . '.update'), $transfer->id)
            : '#');
    $bankTrfFormId           = 'bank-trf-update-form-' . $transfer->id;
    $bankTrfUpdateMsg        = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::BNK_TRF,
        'bank_transfer_update_route_unavailable'
    ) ?? 'Bank transfer update route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::model($transfer, [
    'route'            => [ViewsConstants::BNK_TRF . '.update', $transfer->id],
    'method'           => 'PUT',
    'id'               => $bankTrfFormId,
    'data-url'         => $bankTrfUpdateRoute,
    'data-guard-msg'   => $bankTrfUpdateMsg,
]) }}

<div class="modal-body">
    <div class="{{ VC::RW }}">
        @foreach($fields as $f)
            <div class="{{ VC::FM_G }} {{ $f['colClass'] }}">
                {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}

                @if($f['type'] === 'select')
                    {{ Form::select($f['name'], $f['options'], null, $f['attrs']) }}
                @elseif($f['type'] === 'textarea')
                    {{ Form::textarea($f['name'], null, $f['attrs']) }}
                @else
                    {{ Form::{ $f['type'] }($f['name'], null, $f['attrs']) }}
                @endif
            </div>
        @endforeach
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
</div>

{{ Form::close() }}

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const form = document.getElementById('{{ $bankTrfFormId }}');
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
