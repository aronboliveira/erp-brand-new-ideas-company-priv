/**
 * @file erp-bootstrap.js
 * @description Singleton bootstrap loader — ensures all ERP core singletons
 *              are instantiated and available on `window` before any route
 *              script executes.
 *
 * Load order (in layout): erp-guard.js → erp-utils.js → erp-bootstrap.js
 *
 * This script is designed to be minified and inlined or loaded synchronously
 * in the main layout so that all downstream `<script defer>` files can safely
 * assume `window.ERPGuard` and `window.ERPUtils` exist.
 *
 * @version 1.0.0
 * @license MIT
 */
(function (w) {
  "use strict";

  /* ── Registry ─────────────────────────────────────────────────── */

  /**
   * Central registry of singletons the ERP system depends on.
   * Each entry maps a `window` property name to a factory function
   * that creates / returns the singleton when it is missing.
   *
   * @type {Array<{key: string, factory: function(): *}>}
   */
  const registry = [
    {
      key: "ERPGuard",
      factory: function () {
        // ERPGuard uses a static getInstance pattern
        if (w.ERPGuard && w.ERPGuard.getInstance) {
          return w.ERPGuard.getInstance();
        }
        return null;
      },
    },
    {
      key: "ERPUtils",
      factory: function () {
        if (w.ERPUtils) {
          return new w.ERPUtils();
        }
        return null;
      },
    },
  ];

  /* ── Boot function ────────────────────────────────────────────── */

  /**
   * Iterate over the registry and ensure each singleton exists on `window`.
   * If a singleton is missing, attempt to create it via its factory.
   *
   * @returns {string[]} Names of singletons that could NOT be resolved
   *                     (empty array = all OK)
   */
  function ensureSingletons() {
    const missing = [];

    for (let i = 0; i < registry.length; i++) {
      const entry = registry[i];

      if (w[entry.key]) continue; // already present

      try {
        const instance = entry.factory();
        if (instance) {
          w[entry.key] = instance;
        } else {
          missing.push(entry.key);
        }
      } catch (err) {
        missing.push(entry.key);
        if (w.console && w.console.warn) {
          w.console.warn(
            "[ERPBootstrap] Failed to instantiate " + entry.key + ":",
            err,
          );
        }
      }
    }

    return missing;
  }

  /* ── Public helper for downstream scripts ─────────────────────── */

  /**
   * Called at the top of every route script IIFE to guarantee that the
   * singletons it depends on are available.
   *
   * Usage:
   *   const { guard, utils } = window.ERPBootstrap.require('ERPGuard', 'ERPUtils');
   *
   * If any requested singleton is still unavailable after a boot attempt,
   * the missing names are logged and `null` is returned for that slot.
   *
   * @param {...string} names - window property names to require
   * @returns {Object} hash of { guard: window.ERPGuard, utils: window.ERPUtils, … }
   */
  function require(/* ...names */) {
    // Boot first (idempotent — fast path if already booted)
    ensureSingletons();

    const result = {};
    const keys = Array.prototype.slice.call(arguments);

    /* Canonical short aliases */
    const aliases = {
      ERPGuard: "guard",
      ERPUtils: "utils",
    };

    for (let i = 0; i < keys.length; i++) {
      const key = keys[i];
      const alias = aliases[key] || key;
      result[alias] = w[key] || null;

      if (!result[alias] && w.console && w.console.warn) {
        w.console.warn(
          '[ERPBootstrap] Singleton "' + key + '" is unavailable.',
        );
      }
    }

    return result;
  }

  /* ── Expose ───────────────────────────────────────────────────── */

  w.ERPBootstrap = {
    /** @type {function(): string[]} */
    ensureSingletons: ensureSingletons,
    /** @type {function(...string): Object} */
    require: require,
    /**
     * Register a custom singleton at runtime.
     * @param {string} key - window property name
     * @param {function(): *} factory - Factory that returns the instance
     */
    register: function (key, factory) {
      if (typeof key !== "string" || typeof factory !== "function") return;

      // Avoid duplicates
      for (let i = 0; i < registry.length; i++) {
        if (registry[i].key === key) return;
      }

      registry.push({ key: key, factory: factory });
    },
  };

  /* ── Initial boot ─────────────────────────────────────────────── */

  const missing = ensureSingletons();

  if (missing.length > 0 && w.console && w.console.warn) {
    w.console.warn(
      "[ERPBootstrap] Missing singletons after initial boot:",
      missing.join(", "),
    );
  }
})(typeof window !== "undefined" ? window : globalThis);
