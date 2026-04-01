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
  FileReader: "readonly",
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
  tinymce: "readonly",
  Prism: "readonly",
  SVG: "readonly",
  PerfectScrollbar: "readonly",
  flatpickr: "readonly",
  ClipboardJS: "readonly",
  feather: "readonly",
  select2: "readonly",
  summernote: "readonly",
  daterange: "readonly",
  jscolor: "readonly",
  notifier: "readonly",
  tns: "readonly",
  IMask: "readonly",
  Bouncer: "readonly",
  Datepicker: "readonly",
  DateRangePicker: "readonly",
  introJs: "readonly",
  Slider: "readonly",
  VanillaTree: "readonly",
  define: "readonly",
  SimpleBar: "readonly",
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
      "no-empty": ["warn", { allowEmptyCatch: true }],
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

  /* ── pages / dash / generic browser JS ──────────────────────────── */
  {
    files: ["public/assets/js/pages/**/*.js", "public/assets/js/dash.js", "public/assets/js/generic/**/*.js"],
    languageOptions: {
      ecmaVersion: 2022,
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
      "no-console": "off",
      "no-empty": ["warn", { allowEmptyCatch: true }],
      "no-prototype-builtins": "off",
    },
  },

  /* ── Modules LandingPage browser JS ───────────────────────────────── */
  {
    files: ["Modules/LandingPage/Resources/**/*.js"],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "script",
      globals: {
        ...browserGlobals,
        jQuery: "readonly",
        $: "readonly",
        feather: "readonly",
        bootstrap: "readonly",
        PerfectScrollbar: "readonly",
        setTimeout: "readonly",
        setInterval: "readonly",
        clearInterval: "readonly",
      },
    },
    rules: {
      "no-undef": "error",
      "no-console": "off",
      "no-unused-vars": ["warn", { argsIgnorePattern: "^_", varsIgnorePattern: "^_" }],
      "no-empty": ["warn", { allowEmptyCatch: true }],
      "no-prototype-builtins": "off",
      "no-cond-assign": "off",
      "no-useless-escape": "off",
      "no-redeclare": "off",
    },
  },

  /* ── Jest test files (.test.cjs) ──────────────────────────────────── */
  {
    files: ["tests/**/*.test.cjs", "tests/**/*.test.js", "tests/**/helpers/**/*.cjs", "tests/**/helpers/**/*.js"],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "script",
      globals: {
        ...browserGlobals,
        jest: "readonly",
        describe: "readonly",
        test: "readonly",
        it: "readonly",
        expect: "readonly",
        beforeEach: "readonly",
        afterEach: "readonly",
        beforeAll: "readonly",
        afterAll: "readonly",
        require: "readonly",
        module: "readonly",
        __dirname: "readonly",
        __filename: "readonly",
        process: "readonly",
        global: "writable",
        Buffer: "readonly",
        URL: "readonly",
        WebAssembly: "readonly",
        setTimeout: "readonly",
        setInterval: "readonly",
        clearTimeout: "readonly",
        clearInterval: "readonly",
        Storage: "readonly",
        $: "readonly",
        jQuery: "readonly",
      },
    },
    rules: {
      "no-unused-vars": ["warn", { argsIgnorePattern: "^_", varsIgnorePattern: "^_" }],
      "no-undef": "error",
      "no-console": "off",
    },
  },

  /* ── CommonJS helper/config files ───────────────────────────────── */
  {
    files: ["**/*.cjs", "tailwind.config.js"],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "script",
      globals: {
        require: "readonly",
        module: "readonly",
        exports: "readonly",
        __dirname: "readonly",
        __filename: "readonly",
        process: "readonly",
        console: "readonly",
        Buffer: "readonly",
        URL: "readonly",
        WebAssembly: "readonly",
        global: "readonly",
        setTimeout: "readonly",
        setInterval: "readonly",
        clearTimeout: "readonly",
        clearInterval: "readonly",
        window: "readonly",
      },
    },
    rules: {
      "no-undef": "error",
      "no-console": "off",
      "no-unused-vars": ["warn", { argsIgnorePattern: "^_", varsIgnorePattern: "^_" }],
      "no-empty": ["warn", { allowEmptyCatch: true }],
      "no-redeclare": "off",
    },
  },

  /* ── Node ESM utility scripts ───────────────────────────────────── */
  {
    files: ["utils/scripts/**/*.mjs", "ts/utils/scripts/**/*.mjs", "Modules/**/vite.config.js"],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "module",
      globals: {
        process: "readonly",
        console: "readonly",
        URL: "readonly",
        __dirname: "readonly",
        __filename: "readonly",
        require: "readonly",
      },
    },
    rules: {
      "no-undef": "error",
      "no-console": "off",
      "no-unused-vars": ["warn", { argsIgnorePattern: "^_", varsIgnorePattern: "^_" }],
      "no-empty": ["warn", { allowEmptyCatch: true }],
    },
  },

  /* ── global ignores ───────────────────────────────────────────────── */
  {
    ignores: [
      "node_modules/**",
      "vendor/**",
      ".backup/**",
      ".venv/**",
      "ts/dist/**",
      "ts/dist-iife/**",
      "Modules/LandingPage/Resources/assets/css/summernote/**",
      "Modules/LandingPage/Resources/assets/js/plugins/**",
      "Modules/LandingPage/Resources/assets/js/vendor-all.js",
      "Modules/LandingPage/Resources/assets/js/pages/wow.min.js",
      "public/css/summernote/**",
      "public/assets/js/plugins/**",
      "public/assets/js/vendor-all.js",
      "public/assets/js/wow.min.js",
      "public/assets/js/jquery.repeater.min.js",
      "public/assets/js/pages/wow.min.js",
      "public/assets/js/core/erp-bootstrap.min.js",
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
