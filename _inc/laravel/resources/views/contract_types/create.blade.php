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

    $lang           = Utility::fetchUserLang();
    $storeRoute     = Route::has(ViewsConstants::CTC_TP)
        ? route(ViewsConstants::CTC_TP)
        : (Route::has(Str::kebab(ViewsConstants::CTC_TP))
            ? route(Str::kebab(ViewsConstants::CTC_TP))
            : '#');
    $formId         = 'contract-type-store-form';
    $storeMsg       = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CTC_TP,
        'contract_type_store_route_unavailable'
    ) ?? 'Contract Type store route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::open([
    'url'            => $storeRoute,
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $storeRoute,
    'data-guard-msg' => $storeMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }}">
                {{ Form::label('name', __('Name')) }}
                {{ Form::text('name', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
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
                  const action = form.getAttribute('action');
                  const url    = form.getAttribute('data-url');
                  if ((action && action !== '#') || (url && url !== '#')) return;
                  event.preventDefault();
                  const msg            = form.getAttribute('data-guard-msg') ?? '# ERROR';
                  const bootstrapLink  = document.querySelector('link[href*="bootstrap"]');
                  let container        = document.getElementById('toast-container');
                  if (!container) {
                      container        = document.createElement('div');
                      container.id     = 'toast-container';
                      document.body.appendChild(container);
                  }
                  if (bootstrapLink && window.bootstrap) {
                      const toastEl       = document.createElement('div');
                      toastEl.className   = 'toast';
                      toastEl.setAttribute('role', 'alert');
                      toastEl.setAttribute('aria-live', 'assertive');
                      toastEl.setAttribute('aria-atomic', 'true');
                      const body          = document.createElement('div');
                      body.className      = 'toast-body';
                      body.textContent    = msg;
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
