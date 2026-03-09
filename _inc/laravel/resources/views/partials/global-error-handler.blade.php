{{--
  Global JS Error Handler — catches unhandled errors & promise rejections.
  Renders a Bootstrap 5 toast at the top-right corner.
  Uses a data attribute on <body> to prevent duplicate binding when
  multiple layouts accidentally include this partial twice.
--}}
<div id="erpGlobalErrorToast"
     class="toast {{ VC::ALC }} text-bg-danger border-0 position-fixed"
     role="alert"
     aria-live="assertive"
     aria-atomic="true"
     style="top:1rem;right:1rem;z-index:99999;display:none;min-width:320px;max-width:480px;">
  <div class="{{ VC::DFL }}">
    <div class="toast-body" id="erpGlobalErrorToastBody">
      {{ __('An unexpected error occurred.') }}
    </div>
    <button type="button"
            class="{{ VC::BT_CL }} btn-close-white me-2 m-auto"
            data-bs-dismiss="toast"
            aria-label="{{ __('Close') }}"></button>
  </div>
</div>

<script>
(function () {
  'use strict';
  if (document.body.dataset.erpErrorHandlerBound) return;
  document.body.dataset.erpErrorHandlerBound = '1';

  var TOAST_ID   = 'erpGlobalErrorToast';
  var BODY_ID    = 'erpGlobalErrorToastBody';
  var DELAY      = 8000;
  var _bsToast   = null;

  function _show(msg) {
    var el   = document.getElementById(TOAST_ID);
    var body = document.getElementById(BODY_ID);
    if (!el || !body) return;
    body.textContent = msg || '{{ __("An unexpected error occurred.") }}';
    el.style.display = 'block';
    try {
      if (!_bsToast && typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        _bsToast = new bootstrap.Toast(el, { delay: DELAY, autohide: true });
      }
      if (_bsToast) {
        _bsToast.show();
      }
    } catch (_) {
      /* bootstrap might not be loaded yet on very early errors */
      el.style.display = 'block';
      setTimeout(function () { el.style.display = 'none'; }, DELAY);
    }
  }

  /* ── window.onerror ─────────────────────────────────── */
  window.addEventListener('error', function (ev) {
    /* Ignore browser-extension / cross-origin script noise */
    if (ev.filename && ev.filename.indexOf(window.location.origin) === -1) return;
    /* Ignore ResizeObserver loop warnings (benign) */
    if (ev.message && ev.message.indexOf('ResizeObserver') !== -1) return;
    var msg = ev.message || '{{ __("An unexpected error occurred.") }}';
    if (typeof console !== 'undefined') console.error('[ERP Error Handler]', msg, ev);
    _show(msg);
  });

  /* ── unhandled promise rejections ───────────────────── */
  window.addEventListener('unhandledrejection', function (ev) {
    var reason = ev.reason;
    var msg    = '{{ __("An unexpected error occurred.") }}';
    if (reason) {
      msg = typeof reason === 'string' ? reason
          : reason.message             ? reason.message
          : String(reason);
    }
    if (typeof console !== 'undefined') console.error('[ERP Unhandled Rejection]', msg, reason);
    _show(msg);
  });

  /* hide on toast dismiss */
  var _el = document.getElementById(TOAST_ID);
  if (_el) {
    _el.addEventListener('hidden.bs.toast', function () {
      _el.style.display = 'none';
    });
  }
})();
</script>
