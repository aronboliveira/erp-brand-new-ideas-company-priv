/// <reference types="node" />
// @ts-check
/* eslint-env node */
/* global __dirname */

/**
 * Jest Configuration — Core Tests
 *
 * This config covers all Jest unit/integration tests in the project.
 * Run with: npm run test:jest:core
 *
 * For frontend-specific mock page tests, see:
 *   tests/frontend/jest.frontend.config.cjs
 */
const path = require("path");

module.exports = {
  rootDir: __dirname,
  roots: ["<rootDir>/tests/frontend/js"],
  testEnvironment: "jsdom",
  testMatch: [
    "<rootDir>/tests/frontend/js/**/*.test.cjs",
    "<rootDir>/tests/frontend/js/**/*.test.js",
  ],
  setupFilesAfterSetup: undefined,
  setupFilesAfterEnv: (() => {
    try {
      require.resolve(
        path.resolve(__dirname, "tests/frontend/js/jest.setup.cjs"),
      );
      return ["<rootDir>/tests/frontend/js/jest.setup.cjs"];
    } catch {
      return [];
    }
  })(),
  testPathIgnorePatterns: [
    "/node_modules/",
    "<rootDir>/tests/frontend/js/coverage/",
    "<rootDir>/tests/frontend/js/playwright-report/",
    "<rootDir>/tests/frontend/js/test-results/",
    "<rootDir>/tests/frontend/js/e2e/",
  ],
  moduleFileExtensions: ["cjs", "js", "json"],
  transform: {},
  verbose: true,
  clearMocks: true,
};
