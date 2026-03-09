/**
 * Shared test-setup helpers for custom.js tests.
 *
 * custom.js is a plain browser script – not a module.  We evaluate it inside
 * the jsdom globals so that every `$`, `jQuery`, `document`, `window` etc.
 * call resolves exactly as it would in a browser tab.
 *
 * Usage in a test file:
 *   const { loadCustomJs, buildJQueryEnv } = require('../helpers/setup.cjs');
 *   beforeEach(() => buildJQueryEnv());
 *   beforeEach(() => loadCustomJs());
 */

const fs = require("fs");
const path = require("path");
const vm = require("vm");

/* ------------------------------------------------------------------ */
/*  Path to the custom.js source                                      */
/* ------------------------------------------------------------------ */
const CUSTOM_JS_PATH = path.resolve(
  __dirname,
  "../../../../../public/js/custom.js",
);

/* ------------------------------------------------------------------ */
/*  Minimal jQuery shim that satisfies custom.js at eval-time          */
/* ------------------------------------------------------------------ */
function buildJQueryEnv() {
  // Use real jQuery from the npm package (installed as dev dep)
  const jQueryFactory = require("jquery");

  // jsdom's window is the global in our test environment
  global.$ = global.jQuery = jQueryFactory;

  // custom.js does  `$(location).attr('href')` at the top-level
  // jsdom sets window.location to about:blank, which is fine, but
  // jQuery(location) needs `.attr()`, so we give location an href.
  if (!window.location.href || window.location.href === "about:blank") {
    // jsdom doesn't let you assign location.href freely, but we can
    // use history.replaceState or just let it be – the `.split('/')` will
    // return an array whose `.pop()` is "blank", which is harmless.
  }
}

/* ------------------------------------------------------------------ */
/*  Evaluate custom.js in the current jsdom global scope               */
/* ------------------------------------------------------------------ */
function loadCustomJs(extraGlobals = {}) {
  let src = fs.readFileSync(CUSTOM_JS_PATH, "utf8");

  // custom.js starts with "use strict"; which prevents function declarations
  // from being added to global scope when eval'd.  Strip it so the test
  // environment can see show_toastr, addCommas, etc. as globals.
  src = src.replace(/^\s*"use strict"\s*;?\s*/m, "");

  // Provide stubs for things custom.js calls on first parse that are
  // NOT part of jQuery core:
  const defaults = {
    // bootstrap 5 Toast
    bootstrap: global.bootstrap || {
      Toast: class Toast {
        show() {}
      },
    },
    // Choices.js  (used by select2())
    Choices:
      global.Choices ||
      class Choices {
        constructor() {}
      },
    // simpleDatatables
    simpleDatatables: global.simpleDatatables || {
      DataTable: class DataTable {
        constructor() {}
      },
    },
    // flatpickr (used by daterange())
    flatpickr:
      global.flatpickr ||
      function () {
        return {};
      },
    // Swal / SweetAlert2
    Swal: global.Swal || {
      mixin: () => ({
        fire: () => Promise.resolve({ isConfirmed: false }),
      }),
      DismissReason: { cancel: "cancel" },
    },
    // summernote (jQuery plugin)
    // -> we attach a no-op $.fn.summernote below
    // niceScroll
    // -> we attach $.fn.niceScroll below

    // Currency globals used by addCommas()
    site_currency_symbol: "$",
    site_currency_symbol_position: "pre",
  };

  const merged = { ...defaults, ...extraGlobals };
  for (const [k, v] of Object.entries(merged)) {
    global[k] = v;
  }

  // jQuery plugin stubs (no-ops)
  const noop = function () {
    return this;
  };
  if ($ && $.fn) {
    $.fn.niceScroll = $.fn.niceScroll || noop;
    $.fn.summernote = $.fn.summernote || noop;
    $.fn.tagsinput = $.fn.tagsinput || noop;
    $.fn.tooltip = $.fn.tooltip || noop;
    $.fn.scrollbar = $.fn.scrollbar || noop;
    $.fn.scrollLock = $.fn.scrollLock || noop;
    $.fn.dropdown = $.fn.dropdown || noop;
    $.fn.modal = $.fn.modal || noop;
    $.fn.searchBox = $.fn.searchBox || noop;
    $.fn.flatpickr =
      $.fn.flatpickr ||
      function () {
        return {};
      };
  }

  // Provide  `document.querySelector(x).flatpickr()`  stub
  const origQuerySelector = document.querySelector.bind(document);
  document.querySelector = function (sel) {
    const el = origQuerySelector(sel);
    if (el && !el.flatpickr) {
      el.flatpickr = function () {
        return {};
      };
    }
    return el;
  };

  // Evaluate the script in the global context
  // (0, eval)(...) is "indirect eval" – it always runs in global scope,
  // so `$`, `jQuery`, `bootstrap`, etc. are visible.
  (0, eval)(src);
}

/* ------------------------------------------------------------------ */
/*  Build minimal DOM skeleton that many functions expect               */
/* ------------------------------------------------------------------ */
function buildDomSkeleton() {
  document.body.innerHTML = `
    <meta name="csrf-token" content="test-csrf-token-123">
    <div id="liveToast" class="toast">
      <div class="toast-body"></div>
    </div>
    <div id="commonModal" class="modal">
      <div class="modal-dialog">
        <div class="modal-title"></div>
        <div class="body"></div>
      </div>
    </div>
    <div id="commonModalOver" class="modal">
      <div class="modal-dialog">
        <div class="modal-title"></div>
        <div class="modal-body"></div>
      </div>
    </div>
    <div id="check-list"></div>
    <div id="taskProgress" style="width: 0%"></div>
    <span class="custom-label"></span>
    <div id="pc-daterangepicker-1"></div>
  `;
}

module.exports = {
  loadCustomJs,
  buildJQueryEnv,
  buildDomSkeleton,
  CUSTOM_JS_PATH,
};
