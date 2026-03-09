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
export function ensureToastContainer(): HTMLDivElement {
  let container = document.getElementById(
    TOAST_CONTAINER_ID
  ) as HTMLDivElement | null;
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

let _csrfToken: string | null = null;

/** Reads and caches `<meta name="csrf-token">`. */
export function getCsrfToken(): string {
  if (_csrfToken) return _csrfToken;
  const meta = document.querySelector<HTMLMetaElement>(
    'meta[name="csrf-token"]'
  );
  _csrfToken = meta?.content ?? "";
  return _csrfToken;
}

/* ---------- Translations Init ------------------------------------------- */

/**
 * Ensures `window.translations` is an object.
 * 326+ route files check this property; we initialise it once.
 */
export function ensureTranslations(): Record<string, Record<string, string>> {
  if (!window.translations || typeof window.translations !== "object") {
    (window as unknown as Record<string, unknown>).translations = {};
  }
  return window.translations as Record<string, Record<string, string>>;
}

/* ---------- Currency Globals -------------------------------------------- */

export interface SiteCurrency {
  symbol: string;
  position: "pre" | "post";
}

/**
 * Reads `site_currency_symbol` and `site_currency_symbol_position`
 * from the inline `<script>` in the Blade footer.
 */
export function getSiteCurrency(): SiteCurrency {
  const w = window as unknown as Record<string, unknown>;
  return {
    symbol: (w.site_currency_symbol as string) ?? "$",
    position: (w.site_currency_symbol_position as "pre" | "post") ?? "pre",
  };
}

/* ---------- Feather Icons ----------------------------------------------- */

/**
 * Safely calls `feather.replace()` if the library is loaded.
 */
export function initFeatherIcons(): void {
  const f = (window as unknown as Record<string, unknown>).feather as
    | { replace: () => void }
    | undefined;
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
export function bootstrap(): void {
  if (_bootstrapped) return;
  _bootstrapped = true;

  ensureToastContainer();
  ensureTranslations();
  getCsrfToken();
  initFeatherIcons();
}

/* ---------- Auto-init --------------------------------------------------- */

if (typeof document !== "undefined") {
  if (
    document.readyState === "interactive" ||
    document.readyState === "complete"
  ) {
    bootstrap();
  } else {
    document.addEventListener("DOMContentLoaded", bootstrap, { once: true });
  }
}

export {};
