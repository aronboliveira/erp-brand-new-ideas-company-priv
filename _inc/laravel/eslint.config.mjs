import js from "@eslint/js";
import tsPlugin from "@typescript-eslint/eslint-plugin";
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
  FileReader: "readonly",
  DOMParser: "readonly",
  MouseEvent: "readonly",
  Storage: "readonly",
  /* Project-level globals injected by other scripts */
  taskCheckbox: "readonly",
  common_bind: "readonly",
  commonLoader: "readonly",
  /* Third-party plugin globals */
  Slider: "readonly",
  IMask: "readonly",
  Datepicker: "readonly",
  DateRangePicker: "readonly",
  notifier: "readonly",
  tns: "readonly",
  introJs: "readonly",
  VanillaTree: "readonly",
  Bouncer: "readonly",
  define: "readonly",
  feather: "readonly",
  PerfectScrollbar: "readonly",
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

/**
 * Disable every @typescript-eslint/* rule that appears in inline comments
 * across the JS codebase.  The TS plugin is NOT loaded for plain JS, so
 * ESLint reports "Definition for rule '…' was not found" as an error.
 * Setting them to "off" silences those phantom errors.
 */
const tsRuleOverrides = {
  "@typescript-eslint/explicit-function-return-type": "off",
  "@typescript-eslint/no-unused-vars": "off",
  "@typescript-eslint/no-unsafe-member-access": "off",
  "@typescript-eslint/no-unsafe-call": "off",
  "@typescript-eslint/no-unsafe-assignment": "off",
  "@typescript-eslint/prefer-nullish-coalescing": "off",
  "@typescript-eslint/no-unsafe-argument": "off",
  "@typescript-eslint/no-misused-promises": "off",
  "@typescript-eslint/no-explicit-any": "off",
  "@typescript-eslint/restrict-plus-operands": "off",
  "@typescript-eslint/prefer-for-of": "off",
  "@typescript-eslint/no-base-to-string": "off",
  "@typescript-eslint/no-unsafe-return": "off",
  "@typescript-eslint/restrict-template-expressions": "off",
  "@typescript-eslint/no-floating-promises": "off",
};

export default [
  /* ── base recommended rules ──────────────────────────────────────── */
  js.configs.recommended,

  /* ── suppress phantom @typescript-eslint/* inline directives ──── */
  {
    files: ["**/*.js", "**/*.cjs", "**/*.mjs"],
    plugins: { "@typescript-eslint": tsPlugin },
    rules: tsRuleOverrides,
  },

  /* ── core singleton files ────────────────────────────────────────── */
  {
    files: ["public/assets/js/core/*.js"],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "script",
      globals: coreGlobals,
    },
    rules: {
      ...tsRuleOverrides,
      "no-unused-vars": [
        "warn",
        {
          argsIgnorePattern: "^_",
          varsIgnorePattern: "^_",
          caughtErrorsIgnorePattern: "^_",
        },
      ],
      "no-undef": "error",
      "no-redeclare": "off",
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
      ecmaVersion: 2022,
      sourceType: "script",
      globals: browserGlobals,
    },
    rules: {
      ...tsRuleOverrides,
      "no-unused-vars": [
        "warn",
        {
          argsIgnorePattern: "^_",
          varsIgnorePattern: "^_",
          caughtErrorsIgnorePattern: "^_",
        },
      ],
      "no-undef": "error",
      "no-redeclare": "off",
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
      ...tsRuleOverrides,
      "no-unused-vars": "warn",
      "no-undef": "error",
      "no-redeclare": "off",
      "prefer-const": "warn",
    },
  },

  /* ── page scripts and generic helpers (browser IIFE) ──────────── */
  {
    files: ["public/assets/js/pages/**/*.js", "public/assets/js/generic/**/*.js", "public/assets/js/dash.js"],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "script",
      globals: browserGlobals,
    },
    rules: {
      ...tsRuleOverrides,
      "no-unused-vars": [
        "warn",
        {
          argsIgnorePattern: "^_",
          varsIgnorePattern: "^_",
          caughtErrorsIgnorePattern: "^_",
        },
      ],
      "no-undef": "error",
      "no-redeclare": "off",
      "no-console": ["warn", { allow: ["error", "warn"] }],
    },
  },

  /* ── Node.js config files at project root ─────────────────────── */
  {
    files: ["*.cjs", "*.config.js", "*.config.cjs", "public/assets/js/**/*.cjs", "scripts/**/*.cjs"],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "commonjs",
      globals: {
        process: "readonly",
        console: "readonly",
        __dirname: "readonly",
        __filename: "readonly",
        require: "readonly",
        module: "writable",
        exports: "writable",
      },
    },
    rules: {
      ...tsRuleOverrides,
      "no-unused-vars": "warn",
      "no-undef": "error",
      "no-redeclare": "off",
    },
  },

  /* ── test helper scripts (.cjs, Node/CommonJS) ────────────────────── */
  {
    files: ["tests/**/*.cjs"],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "commonjs",
      globals: {
        ...browserGlobals,
        /* Node.js built-ins */
        process: "readonly",
        console: "readonly",
        Buffer: "readonly",
        __dirname: "readonly",
        __filename: "readonly",
        require: "readonly",
        module: "writable",
        exports: "writable",
        global: "readonly",
        setTimeout: "readonly",
        setInterval: "readonly",
        clearTimeout: "readonly",
        clearInterval: "readonly",
        URL: "readonly",
        URLSearchParams: "readonly",
        /* Web / runtime globals available in Node ≥ 11 */
        WebAssembly: "readonly",
        TextEncoder: "readonly",
        TextDecoder: "readonly",
        /* Jest globals */
        describe: "readonly",
        test: "readonly",
        it: "readonly",
        expect: "readonly",
        beforeAll: "readonly",
        afterAll: "readonly",
        beforeEach: "readonly",
        afterEach: "readonly",
        jest: "readonly",
      },
    },
    rules: {
      ...tsRuleOverrides,
      "no-unused-vars": [
        "warn",
        {
          argsIgnorePattern: "^_",
          varsIgnorePattern: "^_",
          caughtErrorsIgnorePattern: "^_|^e$",
        },
      ],
      "no-undef": "error",
      "no-empty": ["warn", { allowEmptyCatch: true }],
      "no-console": "off",
    },
  },

  /* ── route JS module files (ES import/export) ──────────────────── */
  {
    files: ["public/assets/js/routes/**/shared/*.js"],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "module",
      globals: browserGlobals,
    },
    rules: {
      ...tsRuleOverrides,
      "no-unused-vars": "warn",
      "no-undef": "error",
      "no-redeclare": "off",
    },
  },

  /* ── global ignores ───────────────────────────────────────────────── */
  {
    ignores: [
      "node_modules/**",
      "vendor/**",
      ".backup/**",
      ".venv/**",
      "ts/**",
      "Modules/**",
      "utils/**",
      "public/assets/js/core/erp-bootstrap.min.js",
      "public/assets/js/plugins/**",
      "public/assets/js/vendor-all.js",
      "public/assets/js/jquery.repeater.min.js",
      "public/assets/js/wow.min.js",
      "public/js/**",
      "public/Modules/**",
      "public/css/**",
      "storage/**",
      "bootstrap/cache/**",
      "tests/e2e/**",
      "tests/frontend/**",
      "tests/Feature/security/roleplay/**",
      "scripts/**",
      /* Minified / third-party page scripts */
      "public/assets/js/pages/wow.min.js",
      "public/assets/js/pages/form-validation.js",
      "public/assets/js/pages/form-masking-custom.js",
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
