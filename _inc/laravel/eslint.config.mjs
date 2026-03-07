import js from "@eslint/js";
import { fileURLToPath } from "url";
import path from "path";

const _dirname = path.dirname(fileURLToPath(import.meta.url));
void _dirname; // available for compat use if needed

/** DOM type globals (for instanceof checks / JSDoc annotations) */
const domTypes = {
  Node: "readonly",
  Element: "readonly",
  HTMLElement: "readonly",
  HTMLFormElement: "readonly",
  HTMLInputElement: "readonly",
  NodeList: "readonly",
  Event: "readonly",
  CustomEvent: "readonly",
  Response: "readonly",
  DOMParser: "readonly",
  Range: "readonly",
  DocumentFragment: "readonly",
};

/** Standard browser globals shared by the IIFE route files. */
const browserGlobals = {
  ...domTypes,
  window: "readonly",
  globalThis: "readonly",
  document: "readonly",
  navigator: "readonly",
  location: "readonly",
  history: "readonly",
  sessionStorage: "readonly",
  localStorage: "readonly",
  alert: "readonly",
  confirm: "readonly",
  prompt: "readonly",
  console: "readonly",
  setTimeout: "readonly",
  clearTimeout: "readonly",
  setInterval: "readonly",
  clearInterval: "readonly",
  requestAnimationFrame: "readonly",
  cancelAnimationFrame: "readonly",
  MutationObserver: "readonly",
  IntersectionObserver: "readonly",
  ResizeObserver: "readonly",
  fetch: "readonly",
  FormData: "readonly",
  URL: "readonly",
  URLSearchParams: "readonly",
  Promise: "readonly",
  btoa: "readonly",
  atob: "readonly",
  /* CommonJS shim — used in erp-guard.js typeof-guard export */
  module: "readonly",
  exports: "readonly",
  /* ERP singletons injected by erp-bootstrap (consumed by route scripts) */
  ERPBootstrap: "readonly",
  ERPGuard: "readonly",
  ERPUtils: "readonly",
  RouteGuard: "readonly",
  ContractHelpers: "readonly",
  /* Third-party globals */
  jQuery: "readonly",
  $: "readonly",
  bootstrap: "readonly",
  Dropzone: "readonly",
  Swal: "readonly",
  translations: "readonly",
  axios: "readonly",
  Sortable: "readonly",
  FullCalendar: "readonly",
  ZoomMtg: "readonly",
  Choices: "readonly",
  html2pdf: "readonly",
  dragula: "readonly",
  toastr: "readonly",
  toast: "readonly",
  show_toastr: "readonly",
  hasRouteGuard: "readonly",
  JsSearchBox: "readonly",
  ensureDragula: "readonly",
  changeItem: "readonly",
  XMLHttpRequest: "readonly",
  getComputedStyle: "readonly",
  CSS: "readonly",
  /* Project-level globals injected by other scripts */
  taskCheckbox: "readonly",
  common_bind: "readonly",
  commonLoader: "readonly",
  safeSethtmlContent: "readonly",
};

/**
 * Globals for the CORE singleton files themselves.
 * These files DEFINE ERPGuard / ERPUtils / ERPBootstrap, so we must NOT
 * list them as external globals — doing so triggers no-redeclare.
 */
const coreGlobals = (function () {
  const g = { ...browserGlobals };
  delete g.ERPGuard;
  delete g.ERPUtils;
  delete g.ERPBootstrap;
  delete g.RouteGuard;
  return g;
})();

export default [
  /* ── base recommended rules ──────────────────────────────────────── */
  js.configs.recommended,

  /* ── core singleton files ────────────────────────────────────────── */
  {
    files: ["public/assets/js/core/*.js"],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "script",
      globals: coreGlobals,
    },
    rules: {
      "no-unused-vars": [
        "warn",
        {
          argsIgnorePattern: "^_",
          varsIgnorePattern: "^_",
          caughtErrorsIgnorePattern: "^_",
        },
      ],
      "no-undef": "error",
      "no-var": "error",
      "prefer-const": "warn",
      eqeqeq: ["warn", "always", { null: "ignore" }],
      "no-console": "off",
    },
  },

  /* ── route JS files (IIFE, browser, Blade-embedded literals OK) ──── */
  {
    files: ["public/assets/js/routes/**/*.js"],
    languageOptions: {
      ecmaVersion: 2020,
      sourceType: "script",
      globals: browserGlobals,
    },
    rules: {
      "no-unused-vars": [
        "warn",
        {
          argsIgnorePattern: "^_",
          varsIgnorePattern: "^_",
          caughtErrorsIgnorePattern: "^_",
        },
      ],
      "no-undef": "error",
      "no-var": "error",
      "prefer-const": "warn",
      eqeqeq: ["warn", "always", { null: "ignore" }],
      "no-console": ["warn", { allow: ["error", "warn"] }],
      "no-empty": ["warn", { allowEmptyCatch: true }],
    },
  },

  /* ── resources/js (Laravel Mix / Vite entry points) ─────────────── */
  {
    files: ["resources/js/**/*.js"],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "module",
      globals: browserGlobals,
    },
    rules: {
      "no-unused-vars": "warn",
      "no-undef": "error",
      "prefer-const": "warn",
    },
  },

  /* ── global ignores ───────────────────────────────────────────────── */
  {
    ignores: [
      "node_modules/**",
      "vendor/**",
      "frontend/**",
      "Modules/**",
      /* TypeScript migration folder - has its own eslint config */
      "ts/**",
      ".venv/**",
      "tailwind.config.js",
      "webpack.mix.js",
      "webpack.mix.cjs",
      "babel.config.cjs",
      "jest.config.cjs",
      "playwright.config.cjs",
      "tests/**",
      "utils/**",
      "public/assets/js/core/erp-bootstrap.min.js",
      "public/assets/js/plugins/**",
      "public/assets/js/pages/**",
      "public/assets/js/dash.js",
      "public/assets/js/generic/**",
      "public/assets/js/*.min.js",
      "public/assets/js/vendor-all.js",
      "public/assets/js/jquery*",
      "public/css/**",
      "public/js/**",
      "public/Modules/**",
      "storage/**",
      "bootstrap/cache/**",
      "tests/e2e/**",
      "tests/frontend/**",
      /* Files that contain Blade-template-escaped characters or
         server-rendered data and are only valid post-compilation: */
      "public/assets/js/routes/chartOfAccounts/date.js",
      "public/assets/js/routes/expenses/editSelect.js",
      "public/assets/js/routes/installer/dismiss.js",
      "public/assets/js/routes/invoices/customers/lang/view.js",
      "public/assets/js/routes/landingPage/menubar/edit.js",
      "public/assets/js/routes/leads/lang/convert.js",
      "public/assets/js/routes/leads/lang/show.js",
      "public/assets/js/routes/leaves/types/create.js",
      "public/assets/js/routes/leaves/types/delete.js",
      "public/assets/js/routes/meetings/delete.js",
      "public/assets/js/routes/meetings/edit.js",
      "public/assets/js/routes/pos/lang/view.js",
      "public/assets/js/routes/reports/balances/horizontal/index/index.js",
      "public/assets/js/routes/users/apply.js",
    ],
  },
];
