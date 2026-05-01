/**
 * Type declarations for ERP Brand New Ideas Company JavaScript globals
 */

// Bootstrap types (simplified)
interface ToastOptions {
  animation?: boolean;
  autohide?: boolean;
  delay?: number;
}

interface BootstrapToast {
  show(): void;
  hide(): void;
  dispose(): void;
}

interface BootstrapToastConstructor {
  new (element: Element, options?: ToastOptions): BootstrapToast;
  getInstance(element: Element): BootstrapToast | null;
  getOrCreateInstance(element: Element): BootstrapToast;
}

interface ModalOptions {
  backdrop?: boolean | "static";
  keyboard?: boolean;
  focus?: boolean;
}

interface BootstrapModal {
  show(): void;
  hide(): void;
  toggle(): void;
  handleUpdate(): void;
  dispose(): void;
}

interface BootstrapModalConstructor {
  new (element: Element, options?: ModalOptions): BootstrapModal;
  getInstance(element: Element): BootstrapModal | null;
  getOrCreateInstance(element: Element): BootstrapModal;
}

interface Bootstrap {
  Toast: BootstrapToastConstructor;
  Modal: BootstrapModalConstructor;
}

// Toast types
type ToastType =
  | "success"
  | "error"
  | "warning"
  | "info"
  | "primary"
  | "secondary"
  | "dark"
  | "light";

interface ShowToastOptions {
  duration?: number;
  autohide?: boolean;
  title?: string;
  closable?: boolean;
}

// Modal button definition
interface ModalButton {
  text: string;
  class?: string;
  onClick?: (modal: BootstrapModal) => void;
}

// Modal options
interface ShowModalOptions {
  title?: string;
  body?: string;
  size?: "sm" | "md" | "lg" | "xl";
  closable?: boolean;
  buttons?: ModalButton[];
  onShow?: () => void;
  onHide?: () => void;
  centered?: boolean;
  scrollable?: boolean;
}

// Confirm options
interface ConfirmOptions extends ShowModalOptions {
  confirmText?: string;
  cancelText?: string;
  confirmClass?: string;
  cancelClass?: string;
}

// URL validation options
interface UrlValidationOptions {
  allowRelative?: boolean;
  requireHttps?: boolean;
  allowedHosts?: string[];
}

// Guard binding options
interface SubmitGuardOptions {
  preventDefault?: boolean;
  onError?: (error: Error) => void;
  onSuccess?: (form: HTMLFormElement) => void;
}

interface ClickGuardOptions {
  onError?: (error: Error) => void;
  onSuccess?: (element: HTMLElement) => void;
  confirmMessage?: string;
}

interface ChangeGuardOptions {
  onError?: (error: Error) => void;
  onSuccess?: (element: HTMLElement) => void;
  debounce?: number;
}

interface InteractiveErrorOptions {
  onRetry?: () => void;
  onDismiss?: () => void;
  delay?: number;
}

interface AjaxErrorOptions {
  onRetry?: () => void;
}

// ERPGuard interface
interface ERPGuard {
  getLocale(): string;
  setLocale(locale: string): ERPGuard;
  getMsg(key: string, fallback?: string): string;
  hasBootstrap(): boolean;
  hasToast(): boolean;
  hasModal(): boolean;
  showToast(
    message: string,
    type?: ToastType,
    options?: ShowToastOptions,
  ): ERPGuard;
  success(message: string, options?: ShowToastOptions): ERPGuard;
  error(message: string, options?: ShowToastOptions): ERPGuard;
  warning(message: string, options?: ShowToastOptions): ERPGuard;
  info(message: string, options?: ShowToastOptions): ERPGuard;
  showModal(options?: ShowModalOptions): BootstrapModal | null;
  confirm(
    message: string,
    onConfirm: () => void,
    onCancel?: (() => void) | null,
    options?: ConfirmOptions,
  ): BootstrapModal | null;
  scheduleError(message: string, delay?: number): ERPGuard;
  scheduleInteractiveError(
    message: string,
    options?: InteractiveErrorOptions,
  ): ERPGuard;
  isInvalidUrl(url: string, options?: UrlValidationOptions): boolean;
  bindSubmitGuard(
    form: HTMLFormElement,
    validate?: ((form: HTMLFormElement) => boolean) | null,
    options?: SubmitGuardOptions,
  ): ERPGuard;
  bindClickGuard(
    element: HTMLElement,
    validate?: ((element: HTMLElement) => boolean) | null,
    options?: ClickGuardOptions,
  ): ERPGuard;
  bindChangeGuard(
    element: HTMLElement,
    validate?: ((element: HTMLElement) => boolean) | null,
    options?: ChangeGuardOptions,
  ): ERPGuard;
  unbind(element: HTMLElement): ERPGuard;
  encodeMsg(message: string): string;
  decodeMsg(encoded: string): string;
  handleAjaxError(
    error: Error | Response | { message?: string },
    options?: AjaxErrorOptions,
  ): ERPGuard;
  destroy(): void;
}

// ERPUtils interface
interface FormatNumberOptions {
  decimals?: number;
  locale?: string;
}

interface FormatCurrencyOptions {
  currency?: string;
  locale?: string;
}

interface FormatDateOptions {
  locale?: string;
  includeTime?: boolean;
}

interface ScrollToElementOptions {
  offset?: number;
  behavior?: ScrollBehavior;
}

interface SaveAsPDFOptions {
  areaSelector?: string;
  filenameSelector?: string;
  defaultFilename?: string;
  format?: string;
  scale?: number;
  msgKey?: string;
}

interface ERPUtils {
  copyToClipboard(text: string, showNotification?: boolean): Promise<boolean>;
  bindClipboardAction(
    selectorOrEl: string | HTMLElement,
    textOrGetter: string | ((el: HTMLElement) => string),
  ): void;
  formatNumber(value: number, options?: FormatNumberOptions): string;
  formatCurrency(value: number, options?: FormatCurrencyOptions): string;
  formatDate(date: Date | string | number, options?: FormatDateOptions): string;
  debounce<T extends (...args: any[]) => any>(fn: T, delay: number): T;
  throttle<T extends (...args: any[]) => any>(fn: T, limit: number): T;
  deepClone<T>(obj: T): T;
  isInViewport(element: HTMLElement, offset?: number): boolean;
  scrollToElement(
    target: HTMLElement | string,
    options?: ScrollToElementOptions,
  ): void;
  getQueryParam(name: string, url?: string): string | null;
  setQueryParam(name: string, value: string, updateHistory?: boolean): void;
  generateId(prefix?: string): string;
  getTranslation(key: string, el?: HTMLElement | null): string;
  getMsg(key: string): string;
  saveAsPDF(options?: SaveAsPDFOptions): Promise<boolean>;
}

// RouteGuard interface (backward compatibility layer)
interface RouteGuardAnimations {
  fadeIn(element: HTMLElement | null, duration?: number): Promise<void>;
  fadeOut(element: HTMLElement | null, duration?: number): Promise<void>;
  slideDown(element: HTMLElement | null, duration?: number): Promise<void>;
  slideUp(element: HTMLElement | null, duration?: number): Promise<void>;
  addAnimation(
    element: HTMLElement | null,
    animClass: string,
    duration?: number,
  ): Promise<void>;
}

interface RouteGuard {
  // Main methods
  init(): void;
  showToast(message?: string, type?: string): void;
  scheduleInteractiveError(message: string): void;
  getMsg(el: Element | null, fallbackKey: string): string;
  attachGuard(el: Element): void;
  guardById(id: string): void;
  guardMultiple(...ids: (string | string[])[]): void;
  guardFormSubmit(
    formId: string | HTMLFormElement,
    opts?: { msgKey?: string },
  ): void;
  guardAllInContainer(containerId: string, selector?: string): void;
  guardOnChange(
    elId: string | HTMLElement,
    callback?: (e: Event, el: HTMLElement) => void,
  ): void;
  csrfToken(): string;
  ajaxPost(
    url: string,
    data: any,
    opts?: { errorMsg?: string; headers?: Record<string, string> },
  ): Promise<any>;
  isInvalidUrl(url?: string | null): boolean;
  logError(context: string, err: any): void;
  safeFetch(
    url: string,
    opts?: RequestInit,
  ): Promise<{ ok: boolean; data?: any; error?: string; status?: number }>;

  // Animation methods
  animations: RouteGuardAnimations;

  // Constants
  TOAST_CONTAINER_ID: string;
  DATA_GUARD_MSG: string;
  DATA_SV_LOCALIZED: string;
  DATA_LISTENER_ACTIVE: string;
  DATA_FAILED_ROUTE: string;
}

// Global declarations
declare global {
  interface Window {
    ERPGuard: ERPGuard;
    ERPUtils: ERPUtils;
    RouteGuard: RouteGuard;
    bootstrap: Bootstrap;
    translations: Record<string, Record<string, string>>;
  }

  var ERPGuard: ERPGuard;
  var ERPUtils: ERPUtils;
  var RouteGuard: RouteGuard;
  var bootstrap: Bootstrap;
  var translations: Record<string, Record<string, string>>;
}

export {};
