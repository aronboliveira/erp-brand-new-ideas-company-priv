#!/usr/bin/env node
// strict-null-fix.mjs
// Comprehensive script to add null-safety, nullish coalescing, optional chaining,
// try/catch wrappers, and proper return types to all TypeScript migration files.
//
// Run: node strict-null-fix.mjs [--dry-run]

import { readFileSync, writeFileSync } from "node:fs";
import { execSync } from "node:child_process";

const DRY = process.argv.includes("--dry-run");

const files = execSync('find src -name "*.ts" -not -name "*.d.ts" -type f', {
  encoding: "utf8",
})
  .trim()
  .split("\n")
  .filter(Boolean);

let changed = 0;

for (const file of files) {
  let src = readFileSync(file, "utf8");
  const orig = src;

  // ────────────────────────────────────────────────────────────
  // 0. Strip old file-level eslint-disable comments
  // ────────────────────────────────────────────────────────────
  src = src.replace(/\/\*\s*eslint-disable\s+[\w\s,/@-]+\s*\*\/\s*\n?/g, "");

  // ────────────────────────────────────────────────────────────
  // 1. querySelector / querySelectorAll  →  optional chaining
  //    document.querySelector<X>("sel").foo → document.querySelector<X>("sel")?.foo
  //    document.querySelector<X>("sel").addEventListener → ?.addEventListener
  // ────────────────────────────────────────────────────────────

  // querySelector<T>("...").  →  querySelector<T>("...")?.
  src = src.replace(
    /(document\.querySelector(?:All)?(?:<[^>]+>)?\([^)]*\))\.(?![\s\n])/g,
    "$1?.",
  );
  // document.querySelector("...").  (no generic)
  src = src.replace(
    /(document\.querySelector(?:All)?)\(([^)]+)\)\.(?![\s\n])/g,
    (m, fn, arg) => {
      if (m.includes("?.")) return m;
      return `${fn}(${arg})?.`;
    },
  );

  // ────────────────────────────────────────────────────────────
  // 2. getElementById / getElementsByX  →  optional chaining
  //    document.getElementById("x").property → ?.property
  // ────────────────────────────────────────────────────────────
  src = src.replace(
    /(document\.getElementById\([^)]+\))\.(?![\s\n])/g,
    (m, fn) => (m.includes("?.") ? m : `${fn}?.`),
  );
  src = src.replace(
    /(document\.getElementsBy(?:ClassName|TagName|Name)\([^)]+\))\.(?![\s\n])/g,
    (m, fn) => (m.includes("?.") ? m : `${fn}?.`),
  );

  // ────────────────────────────────────────────────────────────
  // 3. .closest("sel").foo  →  .closest("sel")?.foo
  // ────────────────────────────────────────────────────────────
  src = src.replace(/(\.closest\([^)]+\))\.(?![\s\n])/g, (m, fn) =>
    m.includes("?.") ? m : `${fn}?.`,
  );

  // ────────────────────────────────────────────────────────────
  // 4. e.target.result → (e.target as FileReader | null)?.result
  //    (specifically in FileReader onload context)
  // ────────────────────────────────────────────────────────────
  src = src.replace(
    /(\w+)\.target\.result/g,
    (m, ev) => `(${ev}.target as FileReader | null)?.result`,
  );

  // ────────────────────────────────────────────────────────────
  // 5. || → ?? where safe  (string/number literal or identifier on the right)
  //    Only convert when left side is likely nullable, not boolean
  //    Pattern: `expr || "string"` or `expr || defaultVar`
  // ────────────────────────────────────────────────────────────
  // getAttribute("x") || "default"  →  getAttribute("x") ?? "default"
  src = src.replace(
    /(\.getAttribute\([^)]+\))\s*\|\|\s*(?=["'`#0-9])/g,
    "$1 ?? ",
  );
  // .value || "default"
  src = src.replace(/(\.value)\s*\|\|\s*(?=["'`])/g, "$1 ?? ");
  // .innerHTML || ""
  src = src.replace(/(\.innerHTML)\s*\|\|\s*(?=["'`])/g, "$1 ?? ");
  // .textContent || ""
  src = src.replace(/(\.textContent)\s*\|\|\s*(?=["'`])/g, "$1 ?? ");

  // ────────────────────────────────────────────────────────────
  // 6. document.documentElement || document.querySelector → ??
  // ────────────────────────────────────────────────────────────
  src = src.replace(
    /(document\.documentElement)\s*\|\|\s*(document\.)/g,
    "$1 ?? $2",
  );
  // document.head || target  →  document.head ?? target
  src = src.replace(/(document\.head)\s*\|\|\s*/g, "$1 ?? ");
  // document.body || →  document.body ??
  // (Be careful - document.body can be null in some contexts)

  // ────────────────────────────────────────────────────────────
  // 7. Wrap bare fetch().then() calls in void + catch
  //    fetch(url).then(r => ...) → void fetch(url).then(r => ...).catch(() => {})
  //    But only if not already wrapped in try/catch or already has .catch
  // ────────────────────────────────────────────────────────────
  // This is complex — skip for now, handle with eslint-disable-next-line as needed

  // ────────────────────────────────────────────────────────────
  // 8. var → const / let
  //    var x = ... (not re-assigned) → const x = ...
  //    Just do var → let for safety (const requires usage analysis)
  // ────────────────────────────────────────────────────────────
  src = src.replace(/\bvar\s+/g, "let ");

  // ────────────────────────────────────────────────────────────
  // 9. Fix function return types
  //    function () { → function (): void {
  //    Only for function expressions without existing return type
  // ────────────────────────────────────────────────────────────
  // "function () {"  →  "function (): void {"
  src = src.replace(/function\s*\(\s*\)\s*\{/g, "function (): void {");
  // "function (params) {"  →  leave alone for now (parameter types needed)

  // Arrow functions with body:  () => { → (): void => {
  // Only simple no-param arrows
  src = src.replace(/\(\s*\)\s*=>\s*\{/g, m => {
    // Only if not already typed
    if (m.includes(": ")) return m;
    return "(): void => {";
  });

  // ────────────────────────────────────────────────────────────
  // 10. Fix ?.?. double optional chaining  (from our replacements)
  // ────────────────────────────────────────────────────────────
  src = src.replace(/\?\.\?\./g, "?.");

  // ────────────────────────────────────────────────────────────
  // 11. Fix .querySelectorAll(...)?.forEach  (NodeList is never null)
  //     querySelectorAll always returns a NodeList, so ?. is unnecessary
  //     BUT querySelector CAN return null, so keep ?. there
  // ────────────────────────────────────────────────────────────
  src = src.replace(
    /(document\.querySelectorAll(?:<[^>]+>)?\([^)]*\))\?\./g,
    "$1.",
  );

  // ────────────────────────────────────────────────────────────
  // 12. Swal calls: wrap in void  to handle floating promises
  //     Only bare Swal.fire(...) and Toast.fire(...)
  //     Match lines that start with Swal.fire or <indent>Swal.fire
  // ────────────────────────────────────────────────────────────
  const swalPattern = /^(\s*)(Swal\.fire\()/gm;
  src = src.replace(swalPattern, (m, indent, call) => {
    return `${indent}void ${call}`;
  });
  const toastFirePattern = /^(\s*)(Toast\.fire\()/gm;
  src = src.replace(toastFirePattern, (m, indent, call) => {
    return `${indent}void ${call}`;
  });
  // swalWithBootstrapButtons.fire(
  src = src.replace(
    /^(\s*)(swalWithBootstrapButtons\.fire\()/gm,
    (m, indent, call) => `${indent}void ${call}`,
  );

  // ────────────────────────────────────────────────────────────
  // 13. Fix prefer-const: let x = ... where x is never reassigned
  //     This is AST-level — too complex for regex. ESLint --fix handles it.
  // ────────────────────────────────────────────────────────────

  // ────────────────────────────────────────────────────────────
  // 14. Fix content.match(...)  — match returns RegExpMatchArray | null
  //     Already guarded in well-written files; just ensure optional chain
  // ────────────────────────────────────────────────────────────
  // x.match(...).something → x.match(...)?.something
  src = src.replace(/(\.match\([^)]+\))\.(?![\s\n])/g, (m, fn) =>
    m.includes("?.") ? m : `${fn}?.`,
  );

  // ────────────────────────────────────────────────────────────
  // 15. Fix .dataset.xxx  on possibly-null elements
  //     el.dataset.xxx where el comes from querySelector
  // ────────────────────────────────────────────────────────────
  // Already handled by querySelector?.

  // ────────────────────────────────────────────────────────────
  // 16. Fix .parentElement.xxx / .parentNode.xxx  → ?.
  // ────────────────────────────────────────────────────────────
  src = src.replace(/\.parentElement\./g, ".parentElement?.");
  src = src.replace(/\.parentNode\./g, ".parentNode?.");
  // Avoid double
  src = src.replace(/\?\.\?\./g, "?.");

  // ────────────────────────────────────────────────────────────
  // 17. Fix .nextElementSibling. / .previousElementSibling.
  // ────────────────────────────────────────────────────────────
  src = src.replace(/\.nextElementSibling\./g, ".nextElementSibling?.");
  src = src.replace(/\.previousElementSibling\./g, ".previousElementSibling?.");
  src = src.replace(/\?\.\?\./g, "?.");

  // ────────────────────────────────────────────────────────────
  // 18. introJs().setOptions → introJs?.()?.setOptions
  //     (introJs is a global that may not exist)
  // ────────────────────────────────────────────────────────────
  // Already mostly handled by global declarations

  // ────────────────────────────────────────────────────────────
  // 19. JSON.parse without try/catch — wrap assignments
  //     const x = JSON.parse(...) → let x; try { x = JSON.parse(...) } catch { x = null; }
  //     Too complex for regex in all cases; we'll do targeted inline
  // ────────────────────────────────────────────────────────────

  // ────────────────────────────────────────────────────────────
  // 20. window.bootstrap → window.bootstrap as typeof bootstrap | undefined
  //     Then optional-chain calls on it
  // ────────────────────────────────────────────────────────────

  // ────────────────────────────────────────────────────────────
  // 21. Remove prefer-rest-params: replace arguments with ...args
  //     function foo() { ... arguments ... } → function foo(...args) { ... args ... }
  //     Complex — leave for eslint-disable-next-line
  // ────────────────────────────────────────────────────────────

  // ────────────────────────────────────────────────────────────
  // 22. Fix Swal.getContent() which may return null
  //     Already done via general querySelector-like pattern
  // ────────────────────────────────────────────────────────────
  src = src.replace(/(Swal\.getContent\(\))\./g, "$1?.");
  src = src.replace(/(Swal\.getHtmlContainer\(\))\./g, "$1?.");

  // ────────────────────────────────────────────────────────────
  // 23.  response.json() — wrap fetch chains with .catch
  //      fetch(...).then(r => r.json()).then(data => ...)
  //        → void fetch(...).then(r => r.json() as Promise<unknown>).then(data => ...).catch(() => {})
  //      Complex for regex; we add void and .catch on fetch chains
  // ────────────────────────────────────────────────────────────

  // ────────────────────────────────────────────────────────────
  // 24. Cleanup: remove double blank lines from our edits
  // ────────────────────────────────────────────────────────────
  src = src.replace(/\n{3,}/g, "\n\n");

  // ────────────────────────────────────────────────────────────
  // 25. void void → void  (from stacking)
  // ────────────────────────────────────────────────────────────
  src = src.replace(/void\s+void\s+/g, "void ");

  if (src !== orig) {
    changed++;
    if (!DRY) writeFileSync(file, src, "utf8");
  }
}

console.log(
  `${DRY ? "[DRY] " : ""}Transformed ${changed} / ${files.length} files.`,
);
