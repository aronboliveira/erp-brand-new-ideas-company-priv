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
        { argsIgnorePattern: "^_", varsIgnorePattern: "^_|^\\$|^jQuery" },
      ],
      // strictNullChecks is now ON — enforce these rules
      "@typescript-eslint/prefer-nullish-coalescing": "error",
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
      // Relaxed: Boolean expressions - allow truthy object checks for defensive coding
      "@typescript-eslint/strict-boolean-expressions": [
        "warn", // Downgrade from error to allow gradual fixing
        {
          allowAny: true, // migration: untyped JS values flow as `any`
          allowNullableObject: true, // `if (el)` where el?: HTMLElement
          allowNullableBoolean: true, // `if (flag)` where flag?: boolean
          allowNullableString: true, // Relaxed: allow `if (str)` for nullable strings
          allowNullableNumber: true, // Relaxed: allow `if (num)` for nullable numbers
          allowNullableEnum: true, // Relaxed
          allowString: true, // Relaxed: `if (str)` for string values
          allowNumber: true, // Relaxed: `if (num)` for number values
        },
      ],
      // Relaxed: Allow always-true defensive checks
      "@typescript-eslint/no-unnecessary-condition": [
        "warn", // Downgrade from error to allow gradual review
        { allowConstantLoopConditions: true },
      ],
      "@typescript-eslint/restrict-plus-operands": "warn",
      "@typescript-eslint/restrict-template-expressions": "warn",

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
