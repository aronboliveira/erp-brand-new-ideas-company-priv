(function() {
"use strict";
/**
 * Core ERP modules — barrel export.
 *
 * Import the entire core bundle:
 * ```ts
 * import { toast, qs, mergeTranslations } from "../core/index.js";
 * ```
 *
 * @module core
 */
export { bootstrap, ensureToastContainer, getCsrfToken, ensureTranslations, getSiteCurrency, initFeatherIcons, } from "./erp-bootstrap.js";
export { isGuarded, markGuarded, markFailed, guardClick, guardSubmit, resolveAction, bindGuardAll, toast, devError, } from "./erp-guard.js";
export { qs, qsa, byId, createEl, setAttrs, t, mergeTranslations, getLang, isNumber, isInt, isObject, isNil, nonNull, debounce, noop, safeJsonParse, getCsrf, postAjax, deleteAjax, saveAsPDF, printArea, } from "./erp-utils.js";
})();