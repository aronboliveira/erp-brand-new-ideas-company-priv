/**
 * @fileoverview TypeScript version of tests/frontend/js/helpers/mock-page-audit.cjs
 * @generated from original JavaScript - manual review recommended
 * @module mock-page-audit
 */

/* global $, jQuery */
import fs from "fs";
import path from "path";

const APP_ROOT = path.resolve(__dirname, "../../../../../.."),
  MOCKS_ROOT = path.join(APP_ROOT, "tests", "frontend", "js", "pages", "mocks"),
  ROUTES_JS_ROOT = path.join(APP_ROOT, "public", "assets", "js", "routes");

function walkFiles(dir: string, predicate: (file: string) => boolean, files: string[] = []): string[] {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      walkFiles(fullPath, predicate, files);
      continue;
    }

    if (predicate(fullPath)) files.push(fullPath);
  }

  return files;
}

function getMockHtmlFiles(): string[] {
  return walkFiles(MOCKS_ROOT, (file: string) => file.endsWith(".html")).sort();
}

function getRouteScriptFiles(): string[] {
  return walkFiles(ROUTES_JS_ROOT, (file: string) => file.endsWith(".js") && !file.endsWith(".min.js")).sort();
}

function readText(file: string): string {
  return fs.readFileSync(file, "utf8");
}

function toRepoRelative(file: string): string {
  return path.relative(APP_ROOT, file).split(path.sep).join("/");
}

function isLocalReference(ref: string | null | undefined): boolean {
  if (!ref) return false;

  return !/^(?:[a-z]+:|\/\/|#|mailto:|tel:|javascript:|data:)/i.test(ref);
}

function normaliseReference(ref: string): string {
  return ref.split("#")[0].split("?")[0];
}

function getHtmlLocalReferences(html: string) {
  const refs = [],
    regex = /(?:href|src)=["']([^"']+)["']/gi;
  let match = regex.exec(html);
  while (match) {
    if (isLocalReference(match[1])) refs.push(match[1]);
    match = regex.exec(html);
  }

  return refs;
}

function resolveLocalReference(file: string, ref: string): string | null {
  const cleaned = normaliseReference(ref);
  if (!cleaned) return null;

  return path.resolve(path.dirname(file), cleaned);
}

function collectLineMatches(files: string[], predicate: (line: string, file: string) => boolean): string[] {
  const matches: string[] = [];

  for (const file of files) {
    const lines = readText(file).split(/\r?\n/);
    lines.forEach((line: string, index: number) => {
      if (predicate(line, file)) {
        matches.push(`${toRepoRelative(file)}:${index + 1}: ${line.trim()}`.trim());
      }
    });
  }

  return matches;
}

export { APP_ROOT, MOCKS_ROOT, ROUTES_JS_ROOT, collectLineMatches, getHtmlLocalReferences, getMockHtmlFiles, getRouteScriptFiles, readText, resolveLocalReference, toRepoRelative };
