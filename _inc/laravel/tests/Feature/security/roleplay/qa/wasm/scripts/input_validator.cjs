#!/usr/bin/env node
// ▓ Roleplay: QA — WASM Input Validator ▓
// Validação de input via WASM para testes de segurança
// PULL REQUEST START
"use strict";

const WASM_VALIDATOR = new Uint8Array([
  0x00, 0x61, 0x73, 0x6d,
  0x01, 0x00, 0x00, 0x00,
  0x01, 0x05, 0x01, 0x60, 0x00, 0x01, 0x7f,
  0x03, 0x02, 0x01, 0x00,
  0x07, 0x0c, 0x01, 0x08, 0x76, 0x61, 0x6c, 0x69, 0x64, 0x61, 0x74, 0x65, 0x00, 0x00,
  0x0a, 0x06, 0x01, 0x04, 0x00, 0x41, 0x01, 0x0b,
]);

async function loadWasm() {
  try {
    const { instance } = await WebAssembly.instantiate(WASM_VALIDATOR);
    return instance.exports;
  } catch {
    return null;
  }
}

/**
 * Valida input contra padrões de ataque
 * @param {string} input
 * @returns {{safe: boolean, threats: string[]}}
 */
function validateInput(input) {
  if (!input || typeof input !== "string") return { safe: true, threats: [] };
  const threats = [];
  const patterns = [
    { name: "SQLi", regex: /(\bUNION\b|\bSELECT\b|\bDROP\b|\bINSERT\b|\bDELETE\b|\bUPDATE\b|--|\/\*|;\s*$)/i },
    { name: "XSS", regex: /(<script|javascript:|on\w+\s*=|<img\s|<svg\s|<iframe)/i },
    { name: "Path Traversal", regex: /(\.\.\/)|(\.\.\\)|(%2e%2e)/i },
    { name: "Command Injection", regex: /(;\s*\w+|`[^`]+`|\$\(|\|\s*\w+|&&\s*\w+)/i },
    { name: "SSTI", regex: /(\{\{.*\}\}|\$\{.*\}|<%.*%>)/i },
    { name: "LDAP Injection", regex: /([)(|*\\])/i },
    { name: "Null Byte", regex: /%00|\\x00|\\0/i },
    { name: "CRLF Injection", regex: /%0[dD]%0[aA]|\\r\\n/i },
  ];
  for (const p of patterns) {
    if (p.regex.test(input)) threats.push(p.name);
  }
  return { safe: threats.length === 0, threats };
}

/**
 * Gera inputs de boundary para teste de formulário
 * @param {string} fieldType
 * @returns {string[]}
 */
function generateBoundaryInputs(fieldType) {
  const common = ["", " ", "  \t\n  ", "null", "undefined", "NaN", "true", "false"];
  const type_specific = {
    text: [
      "a".repeat(256),
      "a".repeat(65536),
      "<script>alert(1)</script>",
      "' OR '1'='1",
      "\\x00\\x01\\x02",
      "🔥💀🎭",
      String.fromCharCode(0),
    ],
    number: [
      "0", "-1", "-0", "1e308", "-1e308", "NaN", "Infinity", "-Infinity",
      "9999999999999999", "0.0000001", "1.7976931348623157e+308",
    ],
    email: [
      "a@b", "@b.com", "a@", "a@b.c", "a".repeat(254) + "@b.com",
      "test@test.test", "a@b.c.d.e.f.g.h.i.j.k",
      "\"test\"@test.com", "test+tag@test.com",
    ],
    date: [
      "0000-00-00", "9999-12-31", "2000-13-01", "2000-02-30",
      "1970-01-01", "2038-01-19", "-1-01-01", "2000-00-00",
    ],
    url: [
      "javascript:alert(1)", "data:text/html,<script>alert(1)</script>",
      "file:///etc/passwd", "ftp://evil.test", "//evil.test",
      "http://127.0.0.1", "http://169.254.169.254",
    ],
  };
  return [...common, ...(type_specific[fieldType] || type_specific.text)];
}

if (require.main === module) {
  (async () => {
    console.log("[QA] WASM Input Validator");
    console.log("═".repeat(40));

    const wasm = await loadWasm();
    console.log(`WASM disponível: ${wasm !== null}`);

    const testInputs = [
      "normal text",
      "' OR '1'='1'--",
      "<script>alert(1)</script>",
      "../../etc/passwd",
      "{{7*7}}",
      "; ls -la",
      "admin@test.com",
    ];

    console.log("\nValidação de inputs:");
    for (const input of testInputs) {
      const result = validateInput(input);
      const status = result.safe ? "[SAFE]" : "[VULN]";
      console.log(`  ${status} "${input.slice(0, 40)}": ${result.threats.join(", ") || "limpo"}`);
    }

    console.log("\nBoundary inputs por tipo:");
    for (const type of ["text", "number", "email", "date", "url"]) {
      const inputs = generateBoundaryInputs(type);
      console.log(`  ${type}: ${inputs.length} inputs gerados`);
    }

    console.log("\n[QA] Input Validator completo");
  })();
}

module.exports = { WASM_VALIDATOR, loadWasm, validateInput, generateBoundaryInputs };
// PULL REQUEST END
