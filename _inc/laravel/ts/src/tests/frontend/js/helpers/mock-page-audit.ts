/**
 * @fileoverview TypeScript version of tests/frontend/js/helpers/mock-page-audit.cjs
 * @generated from original JavaScript - manual review recommended
 * @module mock-page-audit
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars, @typescript-eslint/no-var-requires */

/* global $, jQuery */
// @ts-check
const fs = require("fs");
const path = require("path");

const APP_ROOT = path.resolve(__dirname, "../../../..");
const MOCKS_ROOT = path.join(APP_ROOT, "tests", "frontend", "js", "pages", "mocks");
const ROUTES_JS_ROOT = path.join(APP_ROOT, "public", "assets", "js", "routes");

function walkFiles(dir, predicate, files = []) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      walkFiles(fullPath, predicate, files);
      continue;
    }

    if (predicate(fullPath)) {
      files.push(fullPath);
    }
  }

  return files;
}

function getMockHtmlFiles() {
  return walkFiles(MOCKS_ROOT, file => file.endsWith(".html")).sort();
}

function getRouteScriptFiles() {
  return walkFiles(
    ROUTES_JS_ROOT,
    file => file.endsWith(".js") && !file.endsWith(".min.js"),
  ).sort();
}

function readText(file) {
  return fs.readFileSync(file, "utf8");
}

function toRepoRelative(file) {
  return path.relative(APP_ROOT, file).split(path.sep).join("/");
}

function isLocalReference(ref) {
  if (!ref) {
    return false;
  }

  return !/^(?:[a-z]+:|\/\/|#|mailto:|tel:|javascript:|data:)/i.test(ref);
}

function normaliseReference(ref) {
  return ref.split("#")[0].split("?")[0];
}

function getHtmlLocalReferences(html) {
  const refs = [];
  const regex = /(?:href|src)=["']([^"']+)["']/gi;

  let match = regex.exec(html);
  while (match) {
    if (isLocalReference(match[1])) {
      refs.push(match[1]);
    }
    match = regex.exec(html);
  }

  return refs;
}

function resolveLocalReference(file, ref) {
  const cleaned = normaliseReference(ref);
  if (!cleaned) {
    return null;
  }

  return path.resolve(path.dirname(file), cleaned);
}

function collectLineMatches(files, predicate) {
  const matches = [];

  for (const file of files) {
    const lines = readText(file).split(/\r?\n/);
    lines.forEach((line, index) => {
      if (predicate(line, file)) {
        matches.push(
          `${toRepoRelative(file)}:${index + 1}: ${line.trim()}`.trim(),
        );
      }
    });
  }

  return matches;
}

module.exports = {
  APP_ROOT,
  MOCKS_ROOT,
  ROUTES_JS_ROOT,
  collectLineMatches,
  getHtmlLocalReferences,
  getMockHtmlFiles,
  getRouteScriptFiles,
  readText,
  resolveLocalReference,
  toRepoRelative,
};
