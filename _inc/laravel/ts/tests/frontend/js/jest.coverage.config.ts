/**
 * @file jest.coverage.config.ts — Jest coverage config for frontend tests
 * @description Mirrors tests/frontend/js/jest.coverage.config.js (empty in source).
 *              Extend the main jest.config.cjs with coverage-specific overrides here.
 */
import type { Config } from "jest";

const config: Config = {
  collectCoverage: true,
  coverageDirectory: "<rootDir>/tests/frontend/js/coverage",
  collectCoverageFrom: [
    "src/public/assets/js/**/*.ts",
    "!src/public/assets/js/vendor-all.ts",
    "!src/**/*.d.ts",
  ],
  coverageReporters: ["text", "lcov", "clover"],
};

export default config;
