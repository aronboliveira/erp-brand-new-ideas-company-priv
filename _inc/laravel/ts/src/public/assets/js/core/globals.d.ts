/**
 * Global type declarations for the ERP core singletons.
 *
 * These types describe the hand-written OOP singletons in
 * public/assets/js/core/ (loaded by Blade layouts via <script> tags).
 *
 * The originals are NOT TypeScript-compiled — they are vanilla JS IIFEs
 * that attach themselves to `window`. This file provides IntelliSense
 * and type-checking for any TS code that references them.
 */

/* ===================================================================== */
/*  ERPGuard                                                              */
/* ===================================================================== */

type ToastType = "success" | "error" | "warning" | "info" | "primary" | "secondary" | "dark" | "light";

interface ERPGuardToastOptions {
  title?: string;
  duration?: number;
  autohide?: boolean;
  position?: string;
  icon?: string;
}

interface ERPGuardModalOptions {
  title?: string;
  body?: string;
  footer?: string;
  size?: "sm" | "md" | "lg" | "xl";
  centered?: boolean;
  scrollable?: boolean;
  backdrop?: boolean | "static";
  keyboard?: boolean;
  onShow?: () => void;
  onHide?: () => void;
  confirmText?: string;
  cancelText?: string;
  onConfirm?: () => void;
  onCancel?: () => void;
}

interface ERPGuardAjaxConfig {
  url?: string;
  method?: string;
  data?: Record<string, unknown>;
  headers?: Record<string, string>;
  onSuccess?: (data: unknown) => void;
  onError?: (error: unknown) => void;
  onComplete?: () => void;
  guardEl?: HTMLElement;
}

interface ERPGuardBindOptions {
  action?: string;
  url?: string;
  method?: string;
  confirmMessage?: string;
  successMessage?: string;
  errorMessage?: string;
  onSuccess?: (data: unknown) => void;
  onError?: (error: unknown) => void;
  redirect?: string;
}

interface ERPGuardInstance {
  /* --- Locale --- */
  getLocale(): string;
  setLocale(locale: string): void;
  getMsg(key: string, fallback?: string): string;

  /* --- Feature detection --- */
  hasBootstrap(): boolean;
  hasToast(): boolean;
  hasModal(): boolean;

  /* --- Notifications --- */
  showToast(message: string, type?: ToastType, options?: ERPGuardToastOptions): void;
  success(message?: string, options?: ERPGuardToastOptions): void;
  error(message?: string, options?: ERPGuardToastOptions): void;
  warning(message?: string, options?: ERPGuardToastOptions): void;
  info(message?: string, options?: ERPGuardToastOptions): void;

  /* --- Modals --- */
  showModal(options?: ERPGuardModalOptions): void;
  confirm(
    message: string,
    onConfirm: () => void,
    onCancel?: (() => void) | null,
    options?: ERPGuardModalOptions,
  ): void;

  /* --- Error scheduling --- */
  scheduleError(message: string, delay?: number): void;
  scheduleInteractiveError(message: string, options?: ERPGuardToastOptions): void;

  /* --- URL & CSRF --- */
  isInvalidUrl(url: string, options?: Record<string, unknown>): boolean;
  getCsrfToken(): string;
  resolveUrl(el?: Element | null, explicit?: string): string;

  /* --- AJAX --- */
  safeFetch(url: string, opts?: RequestInit): Promise<Response>;
  ajaxPost(url: string, data: unknown, opts?: RequestInit): Promise<Response>;
  ajaxDelete(url: string, opts?: RequestInit): Promise<Response>;
  guardedAjax(config?: ERPGuardAjaxConfig): void;

  /* --- Guard bindings --- */
  bindSubmitGuard(
    form: HTMLFormElement,
    validate?: ((form: HTMLFormElement) => boolean) | null,
    options?: ERPGuardBindOptions,
  ): void;
  bindClickGuard(
    element: HTMLElement,
    validate?: ((el: HTMLElement) => boolean) | null,
    options?: ERPGuardBindOptions,
  ): void;
  bindChangeGuard(
    element: HTMLElement,
    validate?: ((el: HTMLElement) => boolean) | null,
    options?: ERPGuardBindOptions,
  ): void;
  unbind(element: HTMLElement): void;

  /* --- Encoding --- */
  encodeMsg(message: string): string;
  decodeMsg(encoded: string): string;

  /* --- Error handling --- */
  handleAjaxError(error: unknown, options?: Record<string, unknown>): void;

  /* --- Lifecycle --- */
  destroy(): void;
}

interface ERPGuardStatic {
  getInstance(): ERPGuardInstance;
}

/* ===================================================================== */
/*  ERPUtils                                                              */
/* ===================================================================== */

interface ERPUtilsFormatOptions {
  locale?: string;
  minimumFractionDigits?: number;
  maximumFractionDigits?: number;
  currency?: string;
}

interface ERPUtilsDateOptions {
  locale?: string;
  format?: string;
}

interface ERPUtilsPDFOptions {
  elementId?: string;
  filename?: string;
  title?: string;
  orientation?: "portrait" | "landscape";
  format?: string;
}

interface ERPUtilsInstance {
  copyToClipboard(text: string, showNotification?: boolean): Promise<void>;
  bindClipboardAction(
    selectorOrEl: string | HTMLElement,
    textOrGetter: string | (() => string),
  ): void;
  formatNumber(value: number | string, options?: ERPUtilsFormatOptions): string;
  formatCurrency(value: number | string, options?: ERPUtilsFormatOptions): string;
  formatDate(date: string | Date, options?: ERPUtilsDateOptions): string;
  debounce<T extends (...args: unknown[]) => unknown>(func: T, wait: number): T;
  throttle<T extends (...args: unknown[]) => unknown>(func: T, limit: number): T;
  deepClone<T>(obj: T): T;
  isInViewport(el: HTMLElement, offset?: number): boolean;
  scrollToElement(target: string | HTMLElement, options?: ScrollIntoViewOptions): void;
  getQueryParam(name: string, url?: string): string | null;
  setQueryParam(name: string, value: string, updateHistory?: boolean): void;
  generateId(prefix?: string): string;
  getTranslation(key: string, el?: HTMLElement | null): string;
  getMsg(key: string): string;
  saveAsPDF(options?: ERPUtilsPDFOptions): Promise<void>;
}

interface ERPUtilsStatic {
  getInstance(): ERPUtilsInstance;
}

/* ===================================================================== */
/*  ERPBootstrap                                                          */
/* ===================================================================== */

interface ERPBootstrapAPI {
  require(name: string): unknown;
  register(name: string, factory: () => unknown): void;
  ensureSingletons(): void;
}

/* ===================================================================== */
/*  Global augmentation                                                   */
/* ===================================================================== */

declare global {
  interface Window {
    ERPGuard: ERPGuardInstance;
    ERPUtils: ERPUtilsInstance;
    ERPBootstrap: ERPBootstrapAPI;
  }

  // Also available as direct globals in IIFE context
  const ERPGuard: ERPGuardInstance;
  const ERPUtils: ERPUtilsInstance;
  const ERPBootstrap: ERPBootstrapAPI;
}

export {};
