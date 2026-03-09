(function() {
"use strict";
/**
 * erp-bootstrap.ts — Application Bootstrap / Initialization Singleton
 *
 * Handles one-time page setup that currently lives inline in dash.js,
 * custom.js, and the Blade footer. Route files can assume this has
 * already run when they execute.
 *
 * @module core/erp-bootstrap
 * @see public/assets/js/dash.js
 * @see public/assets/js/custom.js
 * @see resources/views/partials/admin/footer.blade.php
 */
/* ---------- Constants --------------------------------------------------- */
/** Default toast auto-hide delay in milliseconds. */
const TOAST_DELAY = 4000;
/** CSS class for the global toast container. */
const TOAST_CONTAINER_ID = "toast-container";
/* ---------- Private state ----------------------------------------------- */
let _bootstrapped = false;
/* ---------- Toast Container --------------------------------------------- */
/**
 * Ensures the global `#toast-container` element exists in the DOM.
 * Created once; 824+ route files previously inlined this logic.
 */
export function ensureToastContainer() {
    let container = document.getElementById(TOAST_CONTAINER_ID);
    if (!container) {
        container = document.createElement("div");
        container.id = TOAST_CONTAINER_ID;
        container.className =
            "toast-container position-fixed bottom-0 end-0 p-3";
        container.style.zIndex = "1100";
        container.setAttribute("aria-live", "polite");
        container.setAttribute("aria-atomic", "true");
        document.body.appendChild(container);
    }
    return container;
}
/* ---------- CSRF -------------------------------------------------------- */
let _csrfToken = null;
/** Reads and caches `<meta name="csrf-token">`. */
export function getCsrfToken() {
    if (_csrfToken)
        return _csrfToken;
    const meta = document.querySelector('meta[name="csrf-token"]');
    _csrfToken = meta?.content ?? "";
    return _csrfToken;
}
/* ---------- Translations Init ------------------------------------------- */
/**
 * Ensures `window.translations` is an object.
 * 326+ route files check this property; we initialise it once.
 */
export function ensureTranslations() {
    if (!window.translations || typeof window.translations !== "object") {
        window.translations = {};
    }
    return window.translations;
}
/**
 * Reads `site_currency_symbol` and `site_currency_symbol_position`
 * from the inline `<script>` in the Blade footer.
 */
export function getSiteCurrency() {
    const w = window;
    return {
        symbol: w.site_currency_symbol ?? "$",
        position: w.site_currency_symbol_position ?? "pre",
    };
}
/* ---------- Feather Icons ----------------------------------------------- */
/**
 * Safely calls `feather.replace()` if the library is loaded.
 */
export function initFeatherIcons() {
    const f = window.feather;
    if (f && typeof f.replace === "function") {
        f.replace();
    }
}
/* ---------- Master Bootstrap -------------------------------------------- */
/**
 * Runs all one-time initialisations. Idempotent — safe to call multiple times.
 * Called automatically at module load on `DOMContentLoaded` or immediately
 * if the DOM is already ready.
 */
export function bootstrap() {
    if (_bootstrapped)
        return;
    _bootstrapped = true;
    ensureToastContainer();
    ensureTranslations();
    getCsrfToken();
    initFeatherIcons();
}
/* ---------- Auto-init --------------------------------------------------- */
if (typeof document !== "undefined") {
    if (document.readyState === "interactive" ||
        document.readyState === "complete") {
        bootstrap();
    }
    else {
        document.addEventListener("DOMContentLoaded", bootstrap, { once: true });
    }
}
})();