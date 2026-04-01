module.exports = {
  testEnvironment: "jsdom",
  testMatch: [
    "<rootDir>/tests/Unit/frontend/js/**/*.test.cjs",
    "<rootDir>/tests/Unit/security/roleplay/**/js/*.test.cjs",
  ],
  clearMocks: true,
  restoreMocks: true,
  resetMocks: true,
  // Transform .js files using Babel to convert ESM to CommonJS
  transform: {
    "^.+\\.js$": "babel-jest",
  },
  // Don't ignore any files from transformation (needed because package.json has "type": "module")
  transformIgnorePatterns: [],
  moduleFileExtensions: ["js", "mjs", "cjs", "json"],
  moduleDirectories: ["node_modules"],
  testEnvironmentOptions: {
    customExportConditions: ["node"],
  },
  // Treat source .js files as scripts, not ES modules
  extensionsToTreatAsEsm: [],
};
