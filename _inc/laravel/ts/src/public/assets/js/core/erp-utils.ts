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
export function qs<T extends Element = Element>(
  selector: string,
  root: ParentNode = document
): T | null {
  return root.querySelector<T>(selector);
}

/**
 * Typed `document.querySelectorAll` as an array.
 */
export function qsa<T extends Element = Element>(
  selector: string,
  root: ParentNode = document
): T[] {
  return Array.from(root.querySelectorAll<T>(selector));
}

/**
 * Typed `document.getElementById`.
 */
export function byId<T extends HTMLElement = HTMLElement>(
  id: string
): T | null {
  return document.getElementById(id) as T | null;
}

/**
 * Creates a typed element with optional attributes.
 *
 * ```ts
 * const div = createEl("div", { className: "wrapper", id: "root" });
 * ```
 */
export function createEl<K extends keyof HTMLElementTagNameMap>(
  tag: K,
  attrs?: Partial<HTMLElementTagNameMap[K]>
): HTMLElementTagNameMap[K] {
  const el = document.createElement(tag);
  if (attrs) {
    Object.entries(attrs).forEach(([key, val]) => {
      if (val !== undefined && val !== null) {
        (el as Record<string, unknown>)[key] = val;
      }
    });
  }
  return el;
}

/**
 * Sets multiple attributes on an element in one call.
 */
export function setAttrs(
  el: Element,
  map: Record<string, string>
): void {
  Object.entries(map).forEach(([k, v]) => el.setAttribute(k, v));
}

/* ======================================================================== *
 *  Translation Helpers                                                      *
 * ======================================================================== */

type TranslationsMap = Record<string, Record<string, string>>;

/**
 * Look up a translation key, optionally scoped to a locale.
 *
 * Falls back to the key itself if no translation is found.
 *
 * ```ts
 * const label = t("invoice", "total_due"); // window.translations.invoice.total_due
 * ```
 */
export function t(
  namespace: string,
  key: string,
  lang?: string
): string {
  const w = (window as unknown as Record<string, unknown>).translations as
    | TranslationsMap
    | undefined;
  if (!w) return key;
  const ns = lang ? (w[lang] as Record<string, string> | undefined) : w[namespace];
  if (!ns || typeof ns !== "object") return key;
  return lang ? ((ns as Record<string, string>)[key] ?? key) : ((ns as Record<string, string>)[key] ?? key);
}

/**
 * Merges a translations map into `window.translations`.
 * This pattern appeared in 204+ lang route files.
 *
 * ```ts
 * mergeTranslations({ en: { greeting: "Hello" }, fr: { greeting: "Bonjour" } });
 * ```
 */
export function mergeTranslations(
  map: TranslationsMap
): void {
  const w = window as unknown as Record<string, unknown>;
  if (!w.translations || typeof w.translations !== "object") {
    w.translations = {};
  }
  const t = w.translations as TranslationsMap;
  Object.keys(map).forEach((lang) => {
    t[lang] = Object.assign({}, t[lang] || {}, map[lang]);
  });
}

/**
 * Returns the current app language string.
 */
export function getLang(): string {
  const w = window as unknown as Record<string, unknown>;
  const appLang = w.__APP_LANG__;
  if (typeof appLang === "string") return appLang;
  return (
    document.documentElement.lang ||
    document.querySelector<HTMLMetaElement>('meta[name="app-locale"]')
      ?.content ||
    "en"
  );
}

/* ======================================================================== *
 *  Type Guards & Checks                                                     *
 * ======================================================================== */

/** Returns `true` for finite numbers (not `NaN`, not `Infinity`). */
export function isNumber(v: unknown): v is number {
  return typeof v === "number" && Number.isFinite(v);
}

/** Returns `true` for integer numbers. */
export function isInt(v: unknown): v is number {
  return typeof v === "number" && Number.isInteger(v);
}

/** Returns `true` for non-null plain objects. */
export function isObject(v: unknown): v is Record<string, unknown> {
  return v !== null && typeof v === "object" && !Array.isArray(v);
}

/** Returns `true` for `null` or `undefined`. */
export function isNil(v: unknown): v is null | undefined {
  return v === null || v === undefined;
}

/**
 * Non-null assertion helper. Throws if the value is `null` or `undefined`.
 *
 * ```ts
 * const el = nonNull(byId("my-el"), "Element #my-el missing");
 * ```
 */
export function nonNull<T>(
  value: T | null | undefined,
  message = "Unexpected null/undefined"
): T {
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
export function debounce<T extends (...args: unknown[]) => void>(
  fn: T,
  ms: number
): (...args: Parameters<T>) => void {
  let timer: ReturnType<typeof setTimeout> | undefined;
  return (...args: Parameters<T>) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), ms);
  };
}

/** No-operation function. */
export function noop(): void {
  /* intentionally empty */
}

/**
 * Safe `JSON.parse` that returns `null` on failure instead of throwing.
 */
export function safeJsonParse<T = unknown>(str: string): T | null {
  try {
    return JSON.parse(str) as T;
  } catch {
    return null;
  }
}

/* ======================================================================== *
 *  AJAX Helpers                                                             *
 * ======================================================================== */

/**
 * Reads the CSRF token from `<meta name="csrf-token">`.
 */
export function getCsrf(): string {
  const meta = document.querySelector<HTMLMetaElement>(
    'meta[name="csrf-token"]'
  );
  return meta?.content ?? "";
}

/**
 * Typed `fetch` wrapper for POST requests with CSRF token.
 */
export async function postAjax<T = unknown>(
  url: string,
  data: Record<string, unknown>
): Promise<T> {
  const res = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": getCsrf(),
      Accept: "application/json",
    },
    body: JSON.stringify(data),
  });
  if (!res.ok) throw new Error(`POST ${url} returned ${res.status}`);
  return (await res.json()) as T;
}

/**
 * Typed `fetch` wrapper for DELETE requests with CSRF token.
 */
export async function deleteAjax<T = unknown>(
  url: string,
  data?: Record<string, unknown>
): Promise<T> {
  const res = await fetch(url, {
    method: "DELETE",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": getCsrf(),
      Accept: "application/json",
    },
    body: data ? JSON.stringify(data) : undefined,
  });
  if (!res.ok) throw new Error(`DELETE ${url} returned ${res.status}`);
  return (await res.json()) as T;
}

/* ======================================================================== *
 *  PDF Helpers                                                              *
 * ======================================================================== */

export interface PdfOptions {
  margin?: number;
  filename?: string;
  image?: { type: string; quality: number };
  html2canvas?: { scale: number };
  jsPDF?: { unit: string; format: string; orientation: string };
}

const DEFAULT_PDF_OPTS: PdfOptions = {
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
export function saveAsPDF(
  el: HTMLElement,
  opts?: Partial<PdfOptions>
): void {
  const h2p = (window as unknown as Record<string, unknown>).html2pdf as
    | ((el: HTMLElement) => { set: (o: PdfOptions) => { save: () => void } })
    | undefined;
  if (!h2p) {
    console.warn("[erp-utils] html2pdf is not loaded.");
    return;
  }
  const merged = { ...DEFAULT_PDF_OPTS, ...opts };
  h2p(el).set(merged as PdfOptions).save();
}

/**
 * Opens the browser print dialog for a specific area of the page.
 * Used by ~33 route files.
 */
export function printArea(areaId: string): void {
  const el = document.getElementById(areaId);
  if (!el) {
    console.warn(`[erp-utils] printArea: #${areaId} not found.`);
    return;
  }
  const printWin = window.open("", "", "width=800,height=600");
  if (!printWin) return;
  printWin.document.write(`
    <html><head><title>Print</title>
    <link rel="stylesheet" href="${
      document.querySelector<HTMLLinkElement>('link[rel="stylesheet"]')?.href ??
      ""
    }">
    </head><body>${el.innerHTML}</body></html>
  `);
  printWin.document.close();
  printWin.focus();
  printWin.print();
  printWin.close();
}

export {};
