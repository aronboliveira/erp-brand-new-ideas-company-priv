import eslint from "@eslint/js";
import tseslint from "typescript-eslint";
import globals from "globals";

/**
 * ESLint configuration for TypeScript migration
 *
 * MIGRATION PHASE: Strict type-checking rules are set to "warn" to allow
 * gradual migration from JavaScript. As files are properly typed, these
 * can be promoted to "error".
 *
 * Rule Categories:
 * - "error": Must be fixed - practical rules that improve code quality
 * - "warn": Should be fixed - strict type rules for gradual migration
 * - "off": Disabled for migration compatibility
 */
export default tseslint.config(
  {
    ignores: [
      "src/tests/**",
      "tests/**",
      "src/public/js/**",
      "src/public/assets/js/pages/**",
      "src/public/assets/js/dash.ts",
      "src/public/assets/js/vendor-all.ts",
      "src/public/Modules/**",
      "utils/**",
      "eslint.config.mjs",
      "jest.config.cjs",
      "playwright.config.ts",
      "playwright.harness.config.ts",
      "playwright-report/**",
      "test-results/**",
      "test-results.json",
    ],
  },
  eslint.configs.recommended,
  ...tseslint.configs.recommendedTypeChecked,
  ...tseslint.configs.stylisticTypeChecked,
  {
    languageOptions: {
      parserOptions: {
        project: "./tsconfig.json",
        tsconfigRootDir: import.meta.dirname,
      },
      globals: {
        ...globals.browser,
        ...globals.node,
        // Project globals
        $: "readonly",
        jQuery: "readonly",
        feather: "readonly",
        bootstrap: "readonly",
        ApexCharts: "readonly",
        flatpickr: "readonly",
        PerfectScrollbar: "readonly",
        Swal: "readonly",
        toastr: "readonly",
        DataTable: "readonly",
      },
    },
    rules: {
      // === WARNINGS: Migration phase - allow gradual fixing ===
      "no-var": "warn",
      "prefer-const": "warn",
      "no-inner-declarations": "warn",
      "@typescript-eslint/no-implied-eval": "warn",
      "no-mixed-spaces-and-tabs": "warn",
      "no-unsafe-finally": "warn",
      "no-fallthrough": "warn",
      "no-shadow-restricted-names": "warn",
      "no-control-regex": "warn",
      "no-constant-condition": "warn",
      "no-duplicate-case": "warn",

      // === WARNINGS: Migration phase - demoted from errors ===
      "@typescript-eslint/no-unused-vars": [
        "warn",
        {
          argsIgnorePattern: "^_",
          varsIgnorePattern:
            "^_|^\\$|^jQuery|^bootstrap|^feather|^SimpleBar|^dragula",
          caughtErrorsIgnorePattern: ".*",
        },
      ],
      // strictNullChecks is now ON — enforce these rules
      // Migration: Allow || for primitives where semantics are similar
      "@typescript-eslint/prefer-nullish-coalescing": [
        "warn",
        {
          ignorePrimitives: { string: true, number: true, boolean: true },
          ignoreConditionalTests: true,
          ignoreMixedLogicalExpressions: true,
        },
      ],
      "@typescript-eslint/prefer-optional-chain": "error",
      "@typescript-eslint/no-floating-promises": "warn",
      "@typescript-eslint/no-misused-promises": "warn",
      "@typescript-eslint/prefer-for-of": "warn",
      "@typescript-eslint/no-var-requires": "warn",
      "@typescript-eslint/no-base-to-string": "warn",
      "@typescript-eslint/require-await": "warn",
      "no-prototype-builtins": "warn",
      "prefer-rest-params": "warn",
      "no-cond-assign": "warn",
      "no-useless-escape": "warn",

      // === WARNINGS: Gradual migration - strict type rules ===
      "@typescript-eslint/no-unsafe-member-access": "warn",
      "@typescript-eslint/no-unsafe-call": "warn",
      "@typescript-eslint/no-unsafe-assignment": "warn",
      "@typescript-eslint/no-unsafe-argument": "warn",
      "@typescript-eslint/no-unsafe-return": "warn",
      "@typescript-eslint/no-explicit-any": "warn",
      "@typescript-eslint/explicit-function-return-type": "warn",
      // OFF: Conflicts with required DOM type assertions (querySelector returns Element)
      "@typescript-eslint/no-unnecessary-type-assertion": "off",
      // OFF: Migration phase — defensive coding patterns from JS produce
      // thousands of "always truthy" warnings that are intentional null-guards.
      // Re-enable after migration stabilizes and types are fully audited.
      "@typescript-eslint/strict-boolean-expressions": "off",
      // OFF: Migration phase — same reasoning as strict-boolean-expressions.
      // Defensive null checks from original JS are intentional safety nets.
      "@typescript-eslint/no-unnecessary-condition": "off",
      "@typescript-eslint/restrict-plus-operands": "warn",
      "@typescript-eslint/restrict-template-expressions": "warn",
      // Allow Function type during migration - will be refined later
      "@typescript-eslint/ban-types": [
        "warn",
        {
          types: {
            Function: {
              message:
                "Avoid using Function. Use specific function types instead.",
              fixWith: "(...args: unknown[]) => unknown",
            },
          },
          extendDefaults: true,
        },
      ],

      // === OFF: Disabled for migration compatibility ===
      "no-empty": "off", // Many IIFE patterns have empty catches
      "@typescript-eslint/no-empty-function": "off",
      "@typescript-eslint/unbound-method": "off", // jQuery callback patterns
      "@typescript-eslint/no-this-alias": "off", // Common JS pattern

      // === OTHER ===
      "no-console": ["warn", { allow: ["warn", "error", "info"] }],
      "@typescript-eslint/no-confusing-void-expression": "off",
    },
  },
  {
    ignores: [
      "dist/**",
      "node_modules/**",
      "**/*.d.ts",
      "**/vendor-all.ts",
      "**/site.ts",
      "**/cookieconsent.ts",
    ],
  },
);
