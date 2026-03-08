/// <reference types="node" />
// @ts-check
/* eslint-env node */
/* global __dirname */

/**
 * Jest Configuration for TypeScript Tests
 *
 * This config handles tests for the TypeScript migration in ts/src/tests/.
 * Run with: cd ts && npm run test
 */
const path = require("path");

module.exports = {
  rootDir: __dirname,
  roots: ["<rootDir>/src/tests", "<rootDir>/tests/unit"],
  testEnvironment: "jsdom",
  testMatch: [
    "<rootDir>/src/tests/**/*.test.ts",
    "<rootDir>/src/tests/**/*.spec.ts",
    "<rootDir>/tests/unit/**/*.test.ts",
  ],
  transform: {
    "^.+\\.tsx?$": [
      "ts-jest",
      {
        tsconfig: "<rootDir>/tsconfig.json",
        useESM: true,
      },
    ],
  },
  extensionsToTreatAsEsm: [".ts", ".tsx"],
  moduleFileExtensions: ["ts", "tsx", "js", "jsx", "json", "node"],
  moduleNameMapper: {
    "^@/(.*)$": "<rootDir>/src/$1",
    "^@public/(.*)$": "<rootDir>/src/public/$1",
    "^@resources/(.*)$": "<rootDir>/src/resources/$1",
    "^@tests/(.*)$": "<rootDir>/src/tests/$1",
  },
  testPathIgnorePatterns: [
    "/node_modules/",
    "/dist/",
    "<rootDir>/src/tests/frontend/js/coverage/",
    "<rootDir>/src/tests/frontend/js/playwright-report/",
    "<rootDir>/src/tests/frontend/js/test-results/",
  ],
  collectCoverageFrom: [
    "src/**/*.ts",
    "!src/**/*.d.ts",
    "!src/types/**/*",
  ],
  coverageDirectory: "<rootDir>/coverage",
  verbose: true,
  clearMocks: true,
};
