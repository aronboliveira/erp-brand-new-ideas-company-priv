@php
    try {
        $invoice = !empty($data) && isset($data['invoice_id']) ? $data['invoice_id'] : null;
        $invoice_id = \Illuminate\Support\Facades\Crypt::decrypt($invoice);
        $price = !empty($data) && isset($data['amount']) ? $data['amount'] : 999999999999999;
        $user = \Illuminate\Support\Facades\Auth::user();
    } catch (\Throwable $e) {
        \Log::error('invoices/paymentwall — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
{{-- {{ dd( $admin_payment_setting) }} --}}
<script async src="https://api.paymentwall.com/brick/build/brick-default.1.5.0.min.js"></script>
<div id="payment-form-container"></div>
<script async src="{{ asset('assets/js/routes/invoices/lang/paymentwall.js') }}"></script>
<script defer>
  (() => {
      const DATA_LISTENER_ADDED   = 'data-listener-added';
      const ERR_FB                = '# ERROR';
      const DATA_CLIENT_LOCALIZED = 'data-client-localized';
      const DATA_GUARD_MSG        = 'data-guard-msg';

      const getLocalizedMessage = (el, key) => {
          let msg = ERR_FB;
          if (el?.getAttribute('data-sv-localized') === 'true'
              || el?.getAttribute(DATA_CLIENT_LOCALIZED) === 'true') {
              msg = el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
          } else {
              let lang = (sessionStorage.getItem('erp-np-lang')
                          || document.documentElement.lang
                          || 'en')
                          .toLowerCase()
                          .replace(/_/g, '-');
              lang = lang === 'pt-br' ? lang : lang.slice(0,2);
              msg = window.translations?.[lang]?.[key]
                    || el.getAttribute(DATA_GUARD_MSG)
                    || window.translations?.['en']?.[key]
                    || ERR_FB;
              if (msg !== ERR_FB) {
                  el.setAttribute(DATA_GUARD_MSG, msg);
                  el.setAttribute(DATA_CLIENT_LOCALIZED, 'true');
              }
          }
          return msg;
      };

      const handleErrorDisplay = (el, key) => {
          const message = el
              ? getLocalizedMessage(el, key)
              : ERR_FB;
          const hasBootstrap = document.querySelector('link[href*="bootstrap"]')
                               && window.bootstrap?.Toast;
          if (hasBootstrap) {
              if (!document.querySelector('#error-toast')) {
                  const toast = document.createElement('div');
                  toast.id        = 'error-toast';
                  toast.className = 'toast align-items-center text-bg-danger border-0';
                  toast.setAttribute('role', 'alert');
                  toast.setAttribute('aria-live', 'assertive');
                  toast.setAttribute('aria-atomic', 'true');
                  toast.innerHTML = `
                      <div class="{{ VC::DFL }}">
                          <div class="toast-body">${message}</div>
                          <button type="button"
                                  class="{{ VC::BT_CL }} btn-close-white me-2 m-auto"
                                  data-bs-dismiss="toast"
                                  aria-label="Close"></button>
                      </div>`;
                  document.body.appendChild(toast);
              }
              new bootstrap.Toast(
                  document.querySelector('#error-toast')
              ).show();
          } else {
              alert(message);
          }
      };

      try {
          if (typeof $ === 'undefined') {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");
              return;
          }
          const containerId = 'payment-form-container';
          const el = document.getElementById(containerId);
          if (!el) return;

          if (typeof Brick === 'undefined') {
              throw new Error('Brick library missing');
          }

          const brick = new Brick({
              public_key: '{{ !empty($company_payment_setting['paymentwall_public_key']) ? $company_payment_setting['paymentwall_public_key'] : '' }}',
              amount:     '{{ $price }}',
              currency:   '{{ App\Models\Utility::getValByName("site_currency") }}',
              container:  containerId,
              action:     '{{ route(VW::INV.".pay.with.paymentwall",[$data["invoice_id"],"amount"=>$data["amount"]]) }}',
              form: {
                  merchant:       'Paymentwall',
                  product:        '{{ \Illuminate\Support\Facades\Auth::user()->invoiceNumberFormat($invoice_id) }}',
                  pay_button:     'Pay',
                  show_zip:       true,
                  show_cardholder:true
              }
          });

          brick.showPaymentForm(
              data => {
                  try {
                      const url = data.flag == 1
                          ? '{{ route("error.invoice.show",[1,"invoice_id"]) }}'.replace('invoice_id', data.invoice)
                          : '{{ route("error.invoice.show",[2,"invoice_id"]) }}'.replace('invoice_id', data.invoice);
                      window.location.href = url;
                  } catch {
                      handleErrorDisplay(el, 'paymentwall_unavailable');
                  }
              },
              errors => {
                  try {
                      const url = errors.flag == 1
                          ? '{{ route("error.invoice.show",[1,"invoice_id"]) }}'.replace('invoice_id', errors.invoice)
                          : '{{ route("error.invoice.show",[2,"invoice_id"]) }}'.replace('invoice_id', errors.invoice);
                      window.location.href = url;
                  } catch {
                      handleErrorDisplay(el, 'paymentwall_unavailable');
                  }
              }
          );
      } catch {
          const el = document.getElementById('payment-form-container');
          if (el && !el.hasAttribute(DATA_LISTENER_ADDED)) {
              el.addEventListener('click', () =>
                  handleErrorDisplay(el, 'paymentwall_unavailable')
              );
              el.setAttribute(DATA_LISTENER_ADDED, 'true');
              const obs = new MutationObserver((_, o) => {
                  if (!document.body.contains(el)) {
                      el.removeEventListener('click',
                          () => handleErrorDisplay(el, 'paymentwall_unavailable')
                      );
                      o.disconnect();
                  }
              });
              obs.observe(document.body, { childList: true, subtree: true });
          }
      }
  })();
</script>
