#!/usr/bin/env node
"use strict";

/**
 * rollback-ts-migration.cjs — Revert TypeScript migration changes.
 *
 * Usage:
 *   node .backup/scripts/node/20260309_014820/rollback-ts-migration.cjs [--dry-run]
 */

const fs = require("fs");
const path = require("path");

const DRY_RUN = process.argv.includes("--dry-run");

function log(msg) {
  console.log(`[rollback] ${msg}`);
}

function findWorkspace() {
  let dir = __dirname;
  for (let i = 0; i < 10; i++) {
    if (fs.existsSync(path.join(dir, "_inc"))) return dir;
    dir = path.dirname(dir);
  }
  throw new Error("Cannot find workspace root");
}

function rmDir(dirPath) {
  if (!fs.existsSync(dirPath)) return 0;
  if (DRY_RUN) {
    log(`DRY-RUN: would remove ${dirPath}`);
    return 1;
  }
  fs.rmSync(dirPath, { recursive: true, force: true });
  return 1;
}

function rmFile(filePath) {
  if (!fs.existsSync(filePath)) return 0;
  if (DRY_RUN) {
    log(`DRY-RUN: would remove ${filePath}`);
    return 1;
  }
  fs.unlinkSync(filePath);
  return 1;
}

/**
 * Recursively find files matching a pattern.
 * @param {string} dir
 * @param {RegExp} pattern
 * @returns {string[]}
 */
function findFiles(dir, pattern) {
  const results = [];
  if (!fs.existsSync(dir)) return results;
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      results.push(...findFiles(full, pattern));
    } else if (pattern.test(full)) {
      results.push(full);
    }
  }
  return results;
}

function main() {
  const workspace = findWorkspace();
  const tsDir = path.join(workspace, "_inc", "laravel", "ts");

  log(`Workspace: ${workspace}`);
  log(`TS dir:    ${tsDir}`);
  if (DRY_RUN) log("** DRY RUN — no changes will be made **");

  let removed = 0;

  // 1. dist/ and dist-iife/
  for (const d of ["dist", "dist-iife"]) {
    const target = path.join(tsDir, d);
    if (fs.existsSync(target)) {
      log(`Removing ${d}/...`);
      removed += rmDir(target);
    }
  }

  // 2. Lang TS files
  const routesDir = path.join(tsDir, "src", "public", "assets", "js", "routes");
  const langFiles = findFiles(routesDir, /[/\\]lang[/\\][^/\\]+\.ts$/);
  log(`Found ${langFiles.length} lang TS files`);
  if (!DRY_RUN) {
    langFiles.forEach(f => fs.unlinkSync(f));
    // Remove empty lang/ dirs
    findFiles(routesDir, /[/\\]lang$/).forEach(d => {
      try {
        if (fs.statSync(d).isDirectory() && fs.readdirSync(d).length === 0) {
          fs.rmdirSync(d);
        }
      } catch {
        /* ignore */
      }
    });
  }
  removed += langFiles.length;

  // 3. Core singletons
  const coreDir = path.join(tsDir, "src", "public", "assets", "js", "core");
  if (fs.existsSync(coreDir)) {
    log("Removing core TS singletons...");
    removed += rmDir(coreDir);
  }

  // 4. Integration tests
  const integDir = path.join(tsDir, "tests", "integration");
  if (fs.existsSync(integDir)) {
    log("Removing integration tests...");
    removed += rmDir(integDir);
  }

  // 5. ESM→IIFE script
  const esmScript = path.join(tsDir, "scripts", "esm-to-iife.cjs");
  if (fs.existsSync(esmScript)) {
    log("Removing ESM→IIFE script...");
    removed += rmFile(esmScript);
  }

  log("");
  log(`Rollback complete. ${removed} items removed.`);
  log("Original JS files in public/assets/js/routes/ are untouched.");
}

main();
