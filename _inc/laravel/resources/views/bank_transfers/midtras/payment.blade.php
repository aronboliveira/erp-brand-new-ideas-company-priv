@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{StacksConstants, ViewsConstants};
    $lang               = Utility::fetchUserLang();
    $fallbackRoute      = Route::has($data['fallback_url'])
        ? route($data['fallback_url'], $data)
        : (Route::has(Str::kebab($data['fallback_url']))
            ? route(Str::kebab($data['fallback_url']), $data)
            : '#');
    $formId             = 'submit_form';
    $fallbackGuardMsg   = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::BNK_TRF,
        'fallback_route_unavailable'
    ) ?? 'Fallback route is unavailable. Please contact technical support or your domain administrator.';
@endphp
<html>
  <head>
    @include('fragments.std', [
      'meta_title' => $meta_title,
      'meta_desc' => $meta_desc,
      'meta_vp' => ''
    ])
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <!-- @TODO: replace SET_YOUR_CLIENT_KEY_HERE with your client key -->
    <script type="text/javascript"
      src="https://app.sandbox.midtrans.com/snap/snap.js"
      data-client-key="{{ $data['midtrans_secret'] }}"></script>
    <!-- Note: replace with src="https://app.midtrans.com/snap/snap.js" for Production environment -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  </head>
  <body>
    <form
        action="{{ $fallbackRoute }}"
        id="{{ $formId }}"
        method="POST"
        data-url="{{ $fallbackRoute }}"
        data-guard-msg="{{ $fallbackGuardMsg }}"
    >
        @csrf
        <input type="hidden" name="json" id="json_callback">
    </form>
    <script async src="{{ asset('assets/js/routes/bank/transfers/lang/payment.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/bank/transfers/payment.js') }}"></script>
    <script defer>
      (() => {
        const errFb = '# ERROR';
        const dataClientLocalized = 'data-client-localized';
        const dataGuardMsg = 'data-guard-msg';
        const langSessionKey = 'erp-np-lang';
        function getLocalizedMessage(key, el) {
          let msg = errFb;
          if (el.getAttribute('data-sv-localized') === 'true'
          || el.getAttribute(dataClientLocalized) === 'true') {
            msg = el.getAttribute(dataGuardMsg) ?? errFb;
          } else {
            let lang = (window.sessionStorage.getItem(langSessionKey)
                      ?? document.documentElement.lang
                      ?? 'en').toLowerCase().replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg = window.translations?.[lang]?.[key]
              ?? el.getAttribute(dataGuardMsg)
              ?? window.translations?.['en']?.[key]
              ?? errFb;
            if (msg !== errFb) {
              el.setAttribute(dataGuardMsg, msg);
              el.setAttribute(dataClientLocalized, 'true');
            }
          }
          return msg;
        }
        function showError(message) {
          try {
            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
            let container = document.getElementById('toast-container');
            if (!container) {
              container = document.createElement('div');
              container.id = 'toast-container';
              document.body.appendChild(container);
            }
            if (bootstrapLink && window.bootstrap) {
              const toastEl = document.createElement('div');
              toastEl.className = 'toast';
              toastEl.setAttribute('role', 'alert');
              toastEl.setAttribute('aria-live', 'assertive');
              toastEl.setAttribute('aria-atomic', 'true');
              const body = document.createElement('div');
              body.className = 'toast-body';
              body.textContent = message;
              toastEl.appendChild(body);
              container.appendChild(toastEl);
              bootstrap.Toast.getOrCreateInstance(toastEl).show();
            } else {
              alert(message);
            }
          } catch {
            alert(message);
          }
        }
        let errorMessage = '';
        const onErrorPointerUp = () => {
          if (errorMessage) {
            showError(errorMessage);
            errorMessage = '';
          }
        };
        document.addEventListener('pointerup', onErrorPointerUp);
        new MutationObserver((muts, obs) => {
          muts.forEach(m => m.removedNodes.forEach(n => {
            if (n === document.documentElement) {
              document.removeEventListener('pointerup', onErrorPointerUp);
              obs.disconnect();
            }
          }));
        }).observe(document.body, { childList: true, subtree: true });
        document.addEventListener('DOMContentLoaded', () => {
          const token = '{{ $data['snap_token'] }}';
          if (!window.snap?.pay) {
            console.log('Snap SDK missing');
            showError(getLocalizedMessage('snap_sdk_unavailable', document.body));
            return;
          }
          try {
            window.snap.pay(token, {
              onSuccess: result => sendResponse(result),
              onPending: result => sendResponse(result),
              onError: result   => sendResponse(result),
              onClose: ()       => {
                errorMessage = getLocalizedMessage('payment_popup_closed', document.body);
              }
            });
          } catch {
            errorMessage = getLocalizedMessage('payment_init_failed', document.body);
          }
        });
        function sendResponse(result) {
          try {
            document.getElementById('json_callback').value = JSON.stringify(result);
            document.getElementById('submit_form')?.submit();
          } catch {
            errorMessage = getLocalizedMessage('response_submit_failed', document.body);
          }
        }
      })();
    </script>
  </body>
</html>
