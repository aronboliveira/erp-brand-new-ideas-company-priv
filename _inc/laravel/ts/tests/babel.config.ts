/**
 * @file babel.config.ts — Babel config mirror for ts/tests
 * @description The ts/ subpackage uses ts-jest directly, so Babel is not
 *              strictly needed. This file exists solely for mirror completeness.
 */
export default {
  presets: [
    ["@babel/preset-env", { targets: { node: "current" } }],
    "@babel/preset-typescript",
  ],
};
