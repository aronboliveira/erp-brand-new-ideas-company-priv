/**
 * erp-guard.ts — Route Guard & Listener Dedup Utilities (AUXILIARY)
 *
 * ⚠ NOT THE RUNTIME VERSION. The authoritative runtime singleton is the
 * hand-written OOP class in public/assets/js/core/erp-guard.js (1,672 lines),
 * loaded by Blade layouts. This file is a simplified TypeScript extraction of
 * common patterns — it does NOT replace the original.
 *
 * For global type declarations of the original, see ./globals.d.ts.
 *
 * Extracts massively duplicated guard / dedup / toast / devError
 * patterns into reusable functions.
 *
 * Duplication stats (across 1,097 route files):
 * - `const toast = (msg) => { ... }` — 601 files
 * - `data-listener-active` — 420 files
 * - `data-guard-msg` — 852 files
 * - `data-listener-bound-click` — 258 files
 * - `data-listener-bound-submit` — 175 files
 * - `data-submit-guarded` — 107 files
 * - `data-failed-route` — 660 files
 * - `data-resolved-action` — 52 files
 * - console.error on localhost — 814 files
 *
 * @module core/erp-guard
 */

import { ensureToastContainer } from "./erp-bootstrap.js";

/* ---------- Types ------------------------------------------------------- */

export interface GuardOptions {
  /** Attribute flagging that the listener is already bound. */
  boundAttr?: string;
  /** If `true`, call `event.preventDefault()`. Default `true`. */
  preventDefault?: boolean;
}

export interface ToastOptions {
  /** Bootstrap colour class suffix: `success`, `danger`, `warning`, `info`. */
  type?: "success" | "danger" | "warning" | "info";
  /** Auto-hide delay in milliseconds. Default 4000. */
  delay?: number;
}

/* ---------- Guard helpers ----------------------------------------------- */

/**
 * Returns `true` if `el` already has the dedup attribute set.
 *
 * ```ts
 * if (isGuarded(btn, "data-listener-bound-click")) return;
 * ```
 */
export function isGuarded(el: Element, attr = "data-listener-active"): boolean {
  return el.getAttribute(attr) === "true";
}

/**
 * Marks `el` with the dedup attribute.
 */
export function markGuarded(el: Element, attr = "data-listener-active"): void {
  el.setAttribute(attr, "true");
}

/**
 * Marks `el` as having a failed route (e.g. href="`#`").
 */
export function markFailed(el: Element): void {
  el.setAttribute("data-failed-route", "true");
}

/* ---------- Click Guard ------------------------------------------------- */

/**
 * Attaches a click guard on `el`. If `href` / `data-url` is `#` or empty,
 * prevents default and shows a toast with `data-guard-msg`.
 *
 * Idempotent — silently returns if already bound.
 */
export function guardClick(el: HTMLElement, opts?: GuardOptions): void {
  const attr = opts?.boundAttr ?? "data-listener-bound-click";
  if (isGuarded(el, attr)) return;
  markGuarded(el, attr);

  el.addEventListener("click", ev => {
    const href = el.getAttribute("href") ?? el.getAttribute("data-url") ?? "";
    if (href === "#" || href === "" || href === "javascript:void(0)") {
      if (opts?.preventDefault !== false) ev.preventDefault();
      const msg = el.getAttribute("data-guard-msg") ?? "This action is not available.";
      toast(msg, { type: "warning" });
      markFailed(el);
    }
  });
}

/**
 * Attaches a submit guard on a `<form>`. Prevents double-submission
 * by flagging `data-submit-guarded`.
 */
export function guardSubmit(form: HTMLFormElement, opts?: GuardOptions): void {
  const attr = opts?.boundAttr ?? "data-listener-bound-submit";
  if (isGuarded(form, attr)) return;
  markGuarded(form, attr);

  form.addEventListener("submit", () => {
    if (form.getAttribute("data-submit-guarded") === "true") return;
    form.setAttribute("data-submit-guarded", "true");
  });
}

/**
 * Resolves the action URL from `data-resolved-action` if set,
 * falling back to `form.action`.
 */
export function resolveAction(form: HTMLFormElement): string {
  return form.getAttribute("data-resolved-action") ?? form.action ?? "";
}

/* ---------- Batch Bind -------------------------------------------------- */

/**
 * Applies `guardClick` to all matching elements not yet guarded.
 *
 * ```ts
 * bindGuardAll("a[data-guard-msg]");
 * ```
 */
export function bindGuardAll(selector: string, opts?: GuardOptions): void {
  document.querySelectorAll<HTMLElement>(selector).forEach(el => guardClick(el, opts));
}

/* ---------- Toast ------------------------------------------------------- */

/**
 * Shows a Bootstrap 5 Toast notification. Falls back to `alert()`
 * if `window.bootstrap` is unavailable.
 *
 * This function was previously copy-pasted into 601 separate route files.
 */
export function toast(msg: string, opts?: ToastOptions): void {
  const type = opts?.type ?? "info";
  const delay = opts?.delay ?? 4000;

  try {
    const container = ensureToastContainer();
    const id = `toast-${Date.now()}`;
    const wrapper = document.createElement("div");
    wrapper.id = id;
    wrapper.className = `toast align-items-center text-white bg-${type} border-0`;
    wrapper.setAttribute("role", "alert");
    wrapper.setAttribute("aria-live", "assertive");
    wrapper.setAttribute("aria-atomic", "true");
    wrapper.setAttribute("data-bs-autohide", "true");
    wrapper.setAttribute("data-bs-delay", String(delay));
    wrapper.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${msg}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast" aria-label="Close"></button>
      </div>`;
    container.appendChild(wrapper);

    const bs = (window as unknown as Record<string, unknown>).bootstrap as { Toast: { getOrCreateInstance: (el: Element) => { show: () => void } } } | undefined;

    if (bs?.Toast) {
      bs.Toast.getOrCreateInstance(wrapper).show();
    } else {
      wrapper.classList.add("show");
      setTimeout(() => wrapper.remove(), delay);
    }

    // Auto-remove from DOM after dismissal
    wrapper.addEventListener("hidden.bs.toast", () => wrapper.remove(), { once: true });
  } catch {
    alert(msg); // ultimate fallback
  }
}

/* ---------- Dev Error --------------------------------------------------- */

/**
 * Logs errors only in local dev environments (localhost / 127.0.0.1).
 * The same `if (location.hostname === ...)` pattern appeared in 814 files.
 */
export function devError(context: string, err: unknown): void {
  if (typeof location !== "undefined" && (location.hostname === "localhost" || location.hostname === "127.0.0.1" || location.hostname === "0.0.0.0")) {
    console.error(`[ERP:${context}]`, err);
  }
}

export {};
