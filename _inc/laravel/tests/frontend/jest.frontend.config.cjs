const path = require("path");

module.exports = {
  rootDir: path.resolve(__dirname, "../.."),
  roots: ["<rootDir>/tests/frontend/js"],
  testEnvironment: "jsdom",
  testMatch: ["<rootDir>/tests/frontend/js/**/*.test.cjs"],
  setupFilesAfterEnv: ["<rootDir>/tests/frontend/js/jest.setup.cjs"],
  testPathIgnorePatterns: [
    "/node_modules/",
    "<rootDir>/tests/frontend/js/coverage/",
    "<rootDir>/tests/frontend/js/playwright-report/",
    "<rootDir>/tests/frontend/js/test-results/",
  ],
  moduleFileExtensions: ["cjs", "js", "json"],
  transform: {},
  verbose: true,
  clearMocks: true,
};
