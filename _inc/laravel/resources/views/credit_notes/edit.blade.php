@php
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC
    };

    $lang           = Utility::fetchUserLang();
    $routeName      = ViewsConstants::INV . '.edit.credit.note';
    $editRoute      = Route::has($routeName)
        ? route($routeName, [$creditNote->invoice, $creditNote->id])
        : '#';
    $formId         = 'credit_note_edit_form_' . $creditNote->id;
    $guardMsg       = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::INV,
        'edit_credit_note_route_unavailable'
    ) ?? 'Edit credit note route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::model($creditNote, [
    'route'          => [$editRoute],
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $editRoute,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            @foreach($fields as $f)
                <div class="{{ VC::FM_G }} col-md-{{ $f['cols'] }}">
                    {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}
                    @php $attrs = $f['attrs']; @endphp
                    @if($f['type'] === 'textarea')
                        {{ Form::textarea($f['name'], null, $attrs) }}
                    @else
                        {{ Form::{$f['type']}($f['name'], null, $attrs) }}
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
    </div>
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
{{ Form::close() }}