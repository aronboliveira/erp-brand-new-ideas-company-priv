/**
 * @file jest.frontend.config.ts — Jest config for ts/tests/frontend/
 * @description Mirrors tests/frontend/jest.frontend.config.cjs for the TS subpackage.
 */
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default {
  rootDir: path.resolve(__dirname, "../.."),
  roots: ["<rootDir>/tests/frontend/js", "<rootDir>/src/tests/frontend"],
  testEnvironment: "jsdom",
  testMatch: [
    "<rootDir>/tests/frontend/js/**/*.test.ts",
    "<rootDir>/src/tests/frontend/**/*.test.ts",
  ],
  transform: {
    "^.+\\.tsx?$": [
      "ts-jest",
      { tsconfig: "<rootDir>/tsconfig.json", useESM: true },
    ],
  },
  extensionsToTreatAsEsm: [".ts", ".tsx"],
  moduleFileExtensions: ["ts", "tsx", "js", "json", "node"],
  moduleNameMapper: {
    "^@/(.*)$": "<rootDir>/src/$1",
    "^@public/(.*)$": "<rootDir>/src/public/$1",
    "^@resources/(.*)$": "<rootDir>/src/resources/$1",
    "^@tests/(.*)$": "<rootDir>/src/tests/$1",
  },
  testPathIgnorePatterns: [
    "/node_modules/",
    "<rootDir>/src/tests/frontend/js/coverage/",
    "<rootDir>/src/tests/frontend/js/playwright-report/",
  ],
  verbose: true,
  clearMocks: true,
};
