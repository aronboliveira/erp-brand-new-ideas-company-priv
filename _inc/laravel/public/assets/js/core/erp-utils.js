/**
 * @file ERPUtils - Utility Functions for ERP System
 * @description Singleton class for general utility functions (clipboard, formatting, etc.)
 * @version 2.0.0
 * @license MIT
 */

/**
 * @typedef {'success' | 'error' | 'warning' | 'info'} NotificationType
 */

/**
 * ERPUtils - Centralized utility system for common operations
 * Separates concerns from ERPGuard by handling non-alert related utilities
 * @class
 */
class ERPUtils {
  // Private static constants
  static #ATTR_CLIPBOARD_BOUND = "data-clipboard-bound";
  static #CLIPBOARD_SUCCESS_MSG = "Copied to clipboard";
  static #CLIPBOARD_ERROR_MSG = "Failed to copy to clipboard";

  /**
   * Creates the ERPUtils singleton instance
   * @constructor
   */
  constructor() {
    if (ERPUtils.instance) return ERPUtils.instance;
    ERPUtils.instance = this;
  }

  /**
   * Copy text to clipboard using modern Clipboard API
   * @public
   * @param {string} text - Text to copy
   * @param {boolean} [showNotification=true] - Whether to show notification
   * @returns {Promise<boolean>} Success status
   */
  async copyToClipboard(text, showNotification = true) {
    if (!text) return false;

    try {
      if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(text);
        if (showNotification) this.#notifyClipboardSuccess();
        return true;
      }

      // Fallback for older browsers
      return this.#fallbackCopyToClipboard(text, showNotification);
    } catch (err) {
      this.#logError("copyToClipboard", err);
      if (showNotification) this.#notifyClipboardError();
      return false;
    }
  }

  /**
   * Fallback clipboard copy for older browsers
   * @private
   * @param {string} text - Text to copy
   * @param {boolean} showNotification - Whether to show notification
   * @returns {boolean} Success status
   */
  #fallbackCopyToClipboard(text, showNotification) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-9999px";
    textArea.style.top = "-9999px";
    document.body.appendChild(textArea);

    try {
      textArea.select();
      const successful = document.execCommand("copy");
      if (showNotification) {
        successful
          ? this.#notifyClipboardSuccess()
          : this.#notifyClipboardError();
      }
      return successful;
    } catch (err) {
      this.#logError("fallbackCopyToClipboard", err);
      if (showNotification) this.#notifyClipboardError();
      return false;
    } finally {
      document.body.removeChild(textArea);
    }
  }

  /**
   * Bind clipboard copy action to element
   * @public
   * @param {string | HTMLElement} selectorOrEl - Element or selector
   * @param {string | Function} textOrGetter - Text to copy or function that returns text
   * @returns {void}
   */
  bindClipboardAction(selectorOrEl, textOrGetter) {
    const elements = this.#resolveElements(selectorOrEl);

    elements.forEach(el => {
      if (el.hasAttribute(ERPUtils.#ATTR_CLIPBOARD_BOUND)) return;

      el.setAttribute(ERPUtils.#ATTR_CLIPBOARD_BOUND, "true");

      el.addEventListener("click", async e => {
        e.preventDefault();

        try {
          const text =
            typeof textOrGetter === "function"
              ? textOrGetter(el)
              : textOrGetter || el.textContent || el.value || "";

          await this.copyToClipboard(text, true);
        } catch (err) {
          this.#logError("bindClipboardAction", err);
        }
      });
    });
  }

  /**
   * Show notification for successful clipboard operation
   * @private
   * @returns {void}
   */
  #notifyClipboardSuccess() {
    window.ERPGuard?.showToast?.(ERPUtils.#CLIPBOARD_SUCCESS_MSG, "success") ??
      alert(ERPUtils.#CLIPBOARD_SUCCESS_MSG);
  }

  /**
   * Show notification for failed clipboard operation
   * @private
   * @returns {void}
   */
  #notifyClipboardError() {
    window.ERPGuard?.showToast?.(ERPUtils.#CLIPBOARD_ERROR_MSG, "error") ??
      alert(ERPUtils.#CLIPBOARD_ERROR_MSG);
  }

  /**
   * Format number with locale-specific formatting
   * @public
   * @param {number} value - Number to format
   * @param {Object} [options={}] - Formatting options
   * @param {number} [options.decimals=2] - Decimal places
   * @param {string} [options.locale] - Locale code
   * @returns {string} Formatted number
   */
  formatNumber(value, options = {}) {
    const { decimals = 2, locale } = options;
    const userLocale = locale || window.ERPGuard?.getLocale?.() || "en";

    try {
      return new Intl.NumberFormat(userLocale, {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
      }).format(value);
    } catch (err) {
      this.#logError("formatNumber", err);
      return value.toFixed(decimals);
    }
  }

  /**
   * Format currency with locale-specific formatting
   * @public
   * @param {number} value - Amount to format
   * @param {Object} [options={}] - Formatting options
   * @param {string} [options.currency='USD'] - Currency code
   * @param {string} [options.locale] - Locale code
   * @returns {string} Formatted currency
   */
  formatCurrency(value, options = {}) {
    const { currency = "USD", locale } = options;
    const userLocale = locale || window.ERPGuard?.getLocale?.() || "en";

    try {
      return new Intl.NumberFormat(userLocale, {
        style: "currency",
        currency: currency,
      }).format(value);
    } catch (err) {
      this.#logError("formatCurrency", err);
      return `${currency} ${value.toFixed(2)}`;
    }
  }

  /**
   * Format date with locale-specific formatting
   * @public
   * @param {Date | string | number} date - Date to format
   * @param {Object} [options={}] - Formatting options
   * @param {string} [options.locale] - Locale code
   * @param {boolean} [options.includeTime=false] - Include time
   * @returns {string} Formatted date
   */
  formatDate(date, options = {}) {
    const { locale, includeTime = false } = options;
    const userLocale = locale || window.ERPGuard?.getLocale?.() || "en";
    const dateObj = date instanceof Date ? date : new Date(date);

    if (isNaN(dateObj.getTime())) return "";

    try {
      const formatOptions = includeTime
        ? { dateStyle: "medium", timeStyle: "short" }
        : { dateStyle: "medium" };

      return new Intl.DateTimeFormat(userLocale, formatOptions).format(dateObj);
    } catch (err) {
      this.#logError("formatDate", err);
      return dateObj.toLocaleDateString();
    }
  }

  /**
   * Debounce function execution
   * @public
   * @param {Function} func - Function to debounce
   * @param {number} wait - Wait time in milliseconds
   * @returns {Function} Debounced function
   */
  debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  /**
   * Throttle function execution
   * @public
   * @param {Function} func - Function to throttle
   * @param {number} limit - Time limit in milliseconds
   * @returns {Function} Throttled function
   */
  throttle(func, limit) {
    let inThrottle;
    return function executedFunction(...args) {
      if (!inThrottle) {
        func(...args);
        inThrottle = true;
        setTimeout(() => (inThrottle = false), limit);
      }
    };
  }

  /**
   * Deep clone an object
   * @public
   * @param {Object} obj - Object to clone
   * @returns {Object} Cloned object
   */
  deepClone(obj) {
    if (obj === null || typeof obj !== "object") return obj;

    try {
      return JSON.parse(JSON.stringify(obj));
    } catch (err) {
      this.#logError("deepClone", err);
      return obj;
    }
  }

  /**
   * Check if element is in viewport
   * @public
   * @param {HTMLElement} el - Element to check
   * @param {number} [offset=0] - Offset in pixels
   * @returns {boolean}
   */
  isInViewport(el, offset = 0) {
    if (!el) return false;

    try {
      const rect = el.getBoundingClientRect();
      return (
        rect.top >= -offset &&
        rect.left >= -offset &&
        rect.bottom <=
          (window.innerHeight || document.documentElement.clientHeight) +
            offset &&
        rect.right <=
          (window.innerWidth || document.documentElement.clientWidth) + offset
      );
    } catch (err) {
      this.#logError("isInViewport", err);
      return false;
    }
  }

  /**
   * Smooth scroll to element
   * @public
   * @param {string | HTMLElement} target - Target element or selector
   * @param {Object} [options={}] - Scroll options
   * @param {number} [options.offset=0] - Offset from top
   * @param {string} [options.behavior='smooth'] - Scroll behavior
   * @returns {void}
   */
  scrollToElement(target, options = {}) {
    const { offset = 0, behavior = "smooth" } = options;
    const element =
      typeof target === "string" ? document.querySelector(target) : target;

    if (!element) return;

    try {
      const elementPosition = element.getBoundingClientRect().top;
      const offsetPosition = elementPosition + window.pageYOffset - offset;

      window.scrollTo({
        top: offsetPosition,
        behavior: behavior,
      });
    } catch (err) {
      this.#logError("scrollToElement", err);
    }
  }

  /**
   * Get query parameter from URL
   * @public
   * @param {string} name - Parameter name
   * @param {string} [url=window.location.href] - URL to parse
   * @returns {string | null} Parameter value
   */
  getQueryParam(name, url = window.location.href) {
    try {
      const urlObj = new URL(url);
      return urlObj.searchParams.get(name);
    } catch (err) {
      this.#logError("getQueryParam", err);
      return null;
    }
  }

  /**
   * Set query parameter in URL
   * @public
   * @param {string} name - Parameter name
   * @param {string} value - Parameter value
   * @param {boolean} [updateHistory=true] - Whether to update browser history
   * @returns {void}
   */
  setQueryParam(name, value, updateHistory = true) {
    try {
      const url = new URL(window.location.href);
      url.searchParams.set(name, value);

      if (updateHistory) {
        window.history.pushState({}, "", url);
      } else {
        window.history.replaceState({}, "", url);
      }
    } catch (err) {
      this.#logError("setQueryParam", err);
    }
  }

  /**
   * Generate unique ID
   * @public
   * @param {string} [prefix=''] - ID prefix
   * @returns {string} Unique ID
   */
  generateId(prefix = "") {
    const timestamp = Date.now().toString(36);
    const randomPart = Math.random().toString(36).substring(2, 9);
    return `${prefix}${prefix ? "-" : ""}${timestamp}-${randomPart}`;
  }

  /**
   * Get translation from global translations object
   * Delegates to ERPGuard.getMsg for consistency
   * @public
   * @param {string} key - Translation key
   * @param {HTMLElement} [el] - Optional element for context
   * @returns {string} Translated message or fallback
   */
  getTranslation(key, el = null) {
    try {
      // Delegate to ERPGuard if available
      if (window.ERPGuard?.getMsg) {
        return window.ERPGuard.getMsg(el, key);
      }

      // Fallback: direct lookup from window.translations
      const locale = this.#getLocale();
      const translations = window.translations || {};

      return (
        translations[locale]?.[key] ||
        translations["en"]?.[key] ||
        key ||
        "# ERROR"
      );
    } catch (err) {
      this.#logError("getTranslation", err);
      return key || "# ERROR";
    }
  }

  /**
   * Alias for getTranslation (backward compatibility)
   * @public
   * @param {string} key - Translation key
   * @returns {string} Translated message
   */
  getMsg(key) {
    return this.getTranslation(key);
  }

  /**
   * Save element content as PDF using html2pdf library
   * @public
   * @param {Object} options - PDF options
   * @param {string} [options.areaSelector='#printableArea'] - CSS selector for content area
   * @param {string} [options.filenameSelector='#filename'] - CSS selector for filename input
   * @param {string} [options.defaultFilename='export'] - Default filename if not found
   * @param {string} [options.format='A4'] - PDF page format (A4, A2, letter, etc.)
   * @param {number} [options.scale=4] - html2canvas scale factor
   * @param {string} [options.msgKey='pdf_unavailable'] - Translation key for error message
   * @returns {Promise<boolean>} Success status
   */
  async saveAsPDF(options = {}) {
    const {
      areaSelector = "#printableArea",
      filenameSelector = "#filename",
      defaultFilename = "export",
      format = "A4",
      scale = 4,
      msgKey = "pdf_unavailable",
    } = options;

    const guard = window.ERPGuard;
    const fallbackMsg =
      "PDF generation is unavailable. Please contact technical support.";

    try {
      // Check if html2pdf is available
      if (typeof window.html2pdf !== "function") {
        const msg =
          this.getTranslation("plugin_unavailable") ||
          "Required plugin is unavailable.";
        guard?.showToast?.(msg, "error") || alert(msg);
        return false;
      }

      // Get printable area
      const area = document.querySelector(areaSelector);
      if (!area) {
        const msg = this.getTranslation(msgKey) || fallbackMsg;
        guard?.showToast?.(msg, "error") || alert(msg);
        return false;
      }

      // Get filename
      const filenameEl = document.querySelector(filenameSelector);
      const $ = window.jQuery;
      const filename =
        (($ ? $(filenameSelector).val() : filenameEl?.value) || "")
          .toString()
          .trim() || defaultFilename;

      // PDF options
      const pdfOptions = {
        margin: 0.3,
        filename,
        image: { type: "jpeg", quality: 1 },
        html2canvas: { scale, dpi: 72, letterRendering: true },
        jsPDF: { unit: "in", format },
      };

      // Generate PDF
      await window.html2pdf().set(pdfOptions).from(area).save();
      return true;
    } catch (err) {
      this.#logError("saveAsPDF", err);
      const msg = this.getTranslation(msgKey) || fallbackMsg;
      guard?.showToast?.(msg, "error") || alert(msg);
      return false;
    }
  }

  /**
   * Get current locale
   * @private
   * @returns {string} Locale code
   */
  #getLocale() {
    try {
      const stored =
        window.sessionStorage?.getItem("erp-np-lang") ||
        window.localStorage?.getItem("erp-np-lang") ||
        document.documentElement.lang ||
        "en";
      const normalized = stored.toLowerCase().replace(/_/g, "-");
      return normalized === "pt-br" ? "pt-br" : normalized.slice(0, 2);
    } catch {
      return "en";
    }
  }

  /**
   * Resolve elements from selector or element
   * @private
   * @param {string | HTMLElement | NodeList | HTMLElement[]} input - Input to resolve
   * @returns {HTMLElement[]} Array of elements
   */
  #resolveElements(input) {
    if (typeof input === "string")
      return Array.from(document.querySelectorAll(input));
    if (input instanceof HTMLElement) return [input];
    if (input instanceof NodeList || Array.isArray(input))
      return Array.from(input);
    return [];
  }

  /**
   * Log error to console in development
   * @private
   * @param {string} context - Error context
   * @param {Error | string} err - Error object or message
   * @returns {void}
   */
  #logError(context, err) {
    if (window.console?.error) {
      const isDev =
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1";
      if (isDev) console.error(`[ERPUtils:${context}]`, err?.message || err);
    }
  }
}

// Initialize singleton and attach to window
(() => {
  if (!window.ERPUtils) window.ERPUtils = new ERPUtils();
})();
