(function() {
"use strict";
/**
 * erp-utils.ts — General-Purpose Typed Utility Functions
 *
 * Pure utility functions with no side effects, available for import.
 * Consolidates DOM helpers (821+ files), translation helpers (326+ files),
 * type guards, AJAX helpers, and PDF helpers (30+ files).
 *
 * @module core/erp-utils
 */
/* ======================================================================== *
 *  DOM Helpers                                                              *
 * ======================================================================== */
/**
 * Typed `document.querySelector`. Returns `null` when not found.
 *
 * ```ts
 * const btn = qs<HTMLButtonElement>("#submit-btn");
 * ```
 */
export function qs(selector, root = document) {
    return root.querySelector(selector);
}
/**
 * Typed `document.querySelectorAll` as an array.
 */
export function qsa(selector, root = document) {
    return Array.from(root.querySelectorAll(selector));
}
/**
 * Typed `document.getElementById`.
 */
export function byId(id) {
    return document.getElementById(id);
}
/**
 * Creates a typed element with optional attributes.
 *
 * ```ts
 * const div = createEl("div", { className: "wrapper", id: "root" });
 * ```
 */
export function createEl(tag, attrs) {
    const el = document.createElement(tag);
    if (attrs) {
        Object.entries(attrs).forEach(([key, val]) => {
            if (val !== undefined && val !== null) {
                el[key] = val;
            }
        });
    }
    return el;
}
/**
 * Sets multiple attributes on an element in one call.
 */
export function setAttrs(el, map) {
    Object.entries(map).forEach(([k, v]) => el.setAttribute(k, v));
}
/**
 * Look up a translation key, optionally scoped to a locale.
 *
 * Falls back to the key itself if no translation is found.
 *
 * ```ts
 * const label = t("invoice", "total_due"); // window.translations.invoice.total_due
 * ```
 */
export function t(namespace, key, lang) {
    const w = window.translations;
    if (!w)
        return key;
    const ns = lang ? w[lang] : w[namespace];
    if (!ns || typeof ns !== "object")
        return key;
    return lang ? (ns[key] ?? key) : (ns[key] ?? key);
}
/**
 * Merges a translations map into `window.translations`.
 * This pattern appeared in 204+ lang route files.
 *
 * ```ts
 * mergeTranslations({ en: { greeting: "Hello" }, fr: { greeting: "Bonjour" } });
 * ```
 */
export function mergeTranslations(map) {
    const w = window;
    if (!w.translations || typeof w.translations !== "object") {
        w.translations = {};
    }
    const t = w.translations;
    Object.keys(map).forEach((lang) => {
        t[lang] = Object.assign({}, t[lang] || {}, map[lang]);
    });
}
/**
 * Returns the current app language string.
 */
export function getLang() {
    const w = window;
    const appLang = w.__APP_LANG__;
    if (typeof appLang === "string")
        return appLang;
    return (document.documentElement.lang ||
        document.querySelector('meta[name="app-locale"]')
            ?.content ||
        "en");
}
/* ======================================================================== *
 *  Type Guards & Checks                                                     *
 * ======================================================================== */
/** Returns `true` for finite numbers (not `NaN`, not `Infinity`). */
export function isNumber(v) {
    return typeof v === "number" && Number.isFinite(v);
}
/** Returns `true` for integer numbers. */
export function isInt(v) {
    return typeof v === "number" && Number.isInteger(v);
}
/** Returns `true` for non-null plain objects. */
export function isObject(v) {
    return v !== null && typeof v === "object" && !Array.isArray(v);
}
/** Returns `true` for `null` or `undefined`. */
export function isNil(v) {
    return v === null || v === undefined;
}
/**
 * Non-null assertion helper. Throws if the value is `null` or `undefined`.
 *
 * ```ts
 * const el = nonNull(byId("my-el"), "Element #my-el missing");
 * ```
 */
export function nonNull(value, message = "Unexpected null/undefined") {
    if (value === null || value === undefined) {
        throw new Error(message);
    }
    return value;
}
/* ======================================================================== *
 *  Functional Helpers                                                       *
 * ======================================================================== */
/**
 * Returns a debounced version of `fn`. Trailing-edge by default.
 */
export function debounce(fn, ms) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), ms);
    };
}
/** No-operation function. */
export function noop() {
    /* intentionally empty */
}
/**
 * Safe `JSON.parse` that returns `null` on failure instead of throwing.
 */
export function safeJsonParse(str) {
    try {
        return JSON.parse(str);
    }
    catch {
        return null;
    }
}
/* ======================================================================== *
 *  AJAX Helpers                                                             *
 * ======================================================================== */
/**
 * Reads the CSRF token from `<meta name="csrf-token">`.
 */
export function getCsrf() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta?.content ?? "";
}
/**
 * Typed `fetch` wrapper for POST requests with CSRF token.
 */
export async function postAjax(url, data) {
    const res = await fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": getCsrf(),
            Accept: "application/json",
        },
        body: JSON.stringify(data),
    });
    if (!res.ok)
        throw new Error(`POST ${url} returned ${res.status}`);
    return (await res.json());
}
/**
 * Typed `fetch` wrapper for DELETE requests with CSRF token.
 */
export async function deleteAjax(url, data) {
    const res = await fetch(url, {
        method: "DELETE",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": getCsrf(),
            Accept: "application/json",
        },
        body: data ? JSON.stringify(data) : undefined,
    });
    if (!res.ok)
        throw new Error(`DELETE ${url} returned ${res.status}`);
    return (await res.json());
}
const DEFAULT_PDF_OPTS = {
    margin: 0,
    filename: "document.pdf",
    image: { type: "jpeg", quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { unit: "in", format: "a4", orientation: "portrait" },
};
/**
 * Saves the given HTML element as a PDF using `html2pdf`.
 * Falls back gracefully if `html2pdf` is not loaded.
 */
export function saveAsPDF(el, opts) {
    const h2p = window.html2pdf;
    if (!h2p) {
        console.warn("[erp-utils] html2pdf is not loaded.");
        return;
    }
    const merged = { ...DEFAULT_PDF_OPTS, ...opts };
    h2p(el).set(merged).save();
}
/**
 * Opens the browser print dialog for a specific area of the page.
 * Used by ~33 route files.
 */
export function printArea(areaId) {
    const el = document.getElementById(areaId);
    if (!el) {
        console.warn(`[erp-utils] printArea: #${areaId} not found.`);
        return;
    }
    const printWin = window.open("", "", "width=800,height=600");
    if (!printWin)
        return;
    printWin.document.write(`
    <html><head><title>Print</title>
    <link rel="stylesheet" href="${document.querySelector('link[rel="stylesheet"]')?.href ??
        ""}">
    </head><body>${el.innerHTML}</body></html>
  `);
    printWin.document.close();
    printWin.focus();
    printWin.print();
    printWin.close();
}
})();