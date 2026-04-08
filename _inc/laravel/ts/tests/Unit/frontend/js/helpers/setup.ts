/**
 * Shared test-setup helpers for custom.js tests (TS mirror).
 *
 * custom.js is a plain browser script – not a module. We evaluate it inside
 * the jsdom globals so that every `$`, `jQuery`, `document`, `window` etc.
 * call resolves exactly as it would in a browser tab.
 *
 * Mirror of tests/Unit/frontend/js/helpers/setup.cjs
 */

import fs from "fs";
import path from "path";

/* ------------------------------------------------------------------ */
/*  Path to the custom.js source                                      */
/* ------------------------------------------------------------------ */
export const CUSTOM_JS_PATH = path.resolve(
  __dirname,
  "../../../../../../public/js/custom.js",
);

/* ------------------------------------------------------------------ */
/*  Minimal jQuery shim that satisfies custom.js at eval-time          */
/* ------------------------------------------------------------------ */
export function buildJQueryEnv(): void {
  // eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
// eslint-disable-next-line @typescript-eslint/no-require-imports
  const jQueryFactory = require("jquery");
  (globalThis as any).$ = (globalThis as any).jQuery = jQueryFactory;
}

/* ------------------------------------------------------------------ */
/*  Evaluate custom.js in the current jsdom global scope               */
/* ------------------------------------------------------------------ */
export function loadCustomJs(extraGlobals: Record<string, unknown> = {}): void {
  let src = fs.readFileSync(CUSTOM_JS_PATH, "utf8");

  src = src.replace(/^\s*"use strict"\s*;?\s*/m, "");

  const defaults: Record<string, unknown> = {
    bootstrap: (globalThis as any).bootstrap || {
      Toast: class Toast {
        show() {}
      },
    },
    Choices:
      (globalThis as any).Choices ||
      class Choices {
        constructor() {}
      },
    simpleDatatables: (globalThis as any).simpleDatatables || {
      DataTable: class DataTable {
        constructor() {}
      },
    },
    flatpickr:
      (globalThis as any).flatpickr ||
      function () {
        return {};
      },
    Swal: (globalThis as any).Swal || {
      mixin: () => ({
        fire: () => Promise.resolve({ isConfirmed: false }),
      }),
      DismissReason: { cancel: "cancel" },
    },
    site_currency_symbol: "$",
    site_currency_symbol_position: "pre",
  };

  const merged = { ...defaults, ...extraGlobals };
  for (const [k, v] of Object.entries(merged)) {
    (globalThis as any)[k] = v;
  }

  const noop = function (this: any) {
    return this;
  };
  const $ = (globalThis as any).$;
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

  const origQuerySelector = document.querySelector.bind(document);
  document.querySelector = function (sel: string) {
    const el = origQuerySelector(sel) as any;
    if (el && !el.flatpickr) {
      el.flatpickr = function () {
        return {};
      };
    }
    return el;
  } as typeof document.querySelector;

  (0, eval)(src);
}

/* ------------------------------------------------------------------ */
/*  Build minimal DOM skeleton that many functions expect               */
/* ------------------------------------------------------------------ */
export function buildDomSkeleton(): void {
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
