/**
 * @file eslint.frontend.config.ts — ESLint flat config for ts/tests/frontend/
 * @description Mirrors tests/frontend/eslint.frontend.config.mjs with TS-first rules.
 */
import js from "@eslint/js";
import tsPlugin from "@typescript-eslint/eslint-plugin";
import tsParser from "@typescript-eslint/parser";
import type { Linter } from "eslint";

const sharedGlobals: Record<string, "readonly"> = {
  console: "readonly",
  process: "readonly",
  setTimeout: "readonly",
  clearTimeout: "readonly",
  URL: "readonly",
  URLSearchParams: "readonly",
  window: "readonly",
  document: "readonly",
  performance: "readonly",
  DOMParser: "readonly",
  navigator: "readonly",
  localStorage: "readonly",
  sessionStorage: "readonly",
  FormData: "readonly",
  Node: "readonly",
  Element: "readonly",
  HTMLElement: "readonly",
  HTMLInputElement: "readonly",
  PerformanceObserver: "readonly",
  PerformanceNavigationTiming: "readonly",
  PerformanceResourceTiming: "readonly",
};

const jestGlobals: Record<string, "readonly"> = {
  describe: "readonly",
  test: "readonly",
  expect: "readonly",
  jest: "readonly",
  beforeAll: "readonly",
  beforeEach: "readonly",
  afterAll: "readonly",
  afterEach: "readonly",
};

const config: Linter.Config[] = [
  js.configs.recommended,
  {
    ignores: [
      "tests/frontend/js/coverage/**",
      "tests/frontend/js/playwright-report/**",
      "tests/frontend/js/test-results/**",
    ],
  },
  {
    files: ["tests/frontend/**/*.ts", "src/tests/frontend/**/*.ts"],
    languageOptions: {
      parser: tsParser as unknown as Linter.Parser,
      ecmaVersion: 2022,
      sourceType: "module",
      parserOptions: {
        ecmaVersion: "latest",
        sourceType: "module",
      },
      globals: {
        ...sharedGlobals,
        ...jestGlobals,
      },
    },
    plugins: {
      "@typescript-eslint": tsPlugin as unknown as Record<string, unknown>,
    },
    rules: {
      "no-console": "off",
      "no-undef": "off",
      "no-unused-vars": "off",
      "no-empty": ["warn", { allowEmptyCatch: true }],
      "@typescript-eslint/no-explicit-any": "off",
      "@typescript-eslint/no-unused-vars": [
        "warn",
        {
          argsIgnorePattern: "^_",
          varsIgnorePattern: "^_",
          caughtErrorsIgnorePattern: "^_",
        },
      ],
    },
  },
];

export default config;
