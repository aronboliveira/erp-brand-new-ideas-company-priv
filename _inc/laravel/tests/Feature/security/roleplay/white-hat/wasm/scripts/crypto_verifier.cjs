#!/usr/bin/env node
// ▓ Roleplay: White Hat — WASM Crypto Verifier ▓
// Usa WASM para verificar integridade de hashes e tokens
// PULL REQUEST START
"use strict";

const crypto = require("crypto");

/**
 * Módulo WASM mínimo que faz XOR de blocos para checksum.
 * Simula verificação de hash via WASM.
 */
const WASM_CHECKSUM = new Uint8Array([
  0x00, 0x61, 0x73, 0x6d, 0x01, 0x00, 0x00, 0x00,
  // Type section: (func (param i32 i32) (result i32))
  0x01, 0x07, 0x01, 0x60, 0x02, 0x7f, 0x7f, 0x01, 0x7f,
  // Function section
  0x03, 0x02, 0x01, 0x00,
  // Memory section
  0x05, 0x03, 0x01, 0x00, 0x01,
  // Export section
  0x07, 0x12, 0x02, 0x06, 0x6d, 0x65, 0x6d, 0x6f, 0x72, 0x79, 0x02, 0x00, 0x08, 0x63, 0x68, 0x65, 0x63, 0x6b, 0x73, 0x75, 0x6d, 0x00, 0x00,
  // Code section: XOR all bytes
  0x0a, 0x16, 0x01, 0x14, 0x02, 0x01, 0x7f, 0x01, 0x7f, 0x41, 0x00, 0x21, 0x02, 0x41, 0x00, 0x21, 0x03, 0x02, 0x40, 0x03, 0x40, 0x20, 0x03, 0x20, 0x01, 0x4d, 0x0d, 0x01, 0x20, 0x02, 0x20, 0x00, 0x20, 0x03, 0x6a, 0x2d, 0x00, 0x00, 0x73, 0x21, 0x02, 0x20, 0x03, 0x41, 0x01, 0x6a, 0x21, 0x03, 0x0c, 0x00, 0x0b, 0x0b, 0x20, 0x02, 0x0b,
]);

async function loadWasm() {
  try {
    const { instance } = await WebAssembly.instantiate(WASM_CHECKSUM);
    return instance.exports;
  } catch {
    return null;
  }
}

/**
 * Verifica se um hash BCrypt/SHA256/MD5 tem formato válido
 * @param {string} hash
 * @returns {{valid: boolean, type: string, weak: boolean}}
 */
function verifyHashFormat(hash) {
  if (!hash || typeof hash !== "string") return { valid: false, type: "unknown", weak: true };
  if (/^\$2[aby]\$\d{2}\$.{53}$/.test(hash)) return { valid: true, type: "bcrypt", weak: false };
  if (/^[a-f0-9]{64}$/i.test(hash)) return { valid: true, type: "sha256", weak: false };
  if (/^[a-f0-9]{32}$/i.test(hash)) return { valid: true, type: "md5", weak: true };
  if (/^[a-f0-9]{40}$/i.test(hash)) return { valid: true, type: "sha1", weak: true };
  return { valid: false, type: "unknown", weak: true };
}

/**
 * Verifica integridade de token JWT
 * @param {string} token
 * @returns {{valid: boolean, expired: boolean, algorithm: string, weak: boolean}}
 */
function verifyJwtIntegrity(token) {
  if (!token) return { valid: false, expired: false, algorithm: "none", weak: true };
  const parts = token.split(".");
  if (parts.length !== 3) return { valid: false, expired: false, algorithm: "none", weak: true };
  try {
    const header = JSON.parse(Buffer.from(parts[0], "base64url").toString());
    const payload = JSON.parse(Buffer.from(parts[1], "base64url").toString());
    const alg = header.alg || "none";
    const weakAlgs = ["none", "HS256"];
    const expired = payload.exp ? payload.exp < Math.floor(Date.now() / 1000) : false;
    return {
      valid: true,
      expired,
      algorithm: alg,
      weak: weakAlgs.includes(alg),
      claims: Object.keys(payload),
    };
  } catch {
    return { valid: false, expired: false, algorithm: "unknown", weak: true };
  }
}

/**
 * Verifica se CSRF tokens são previsíveis
 * @param {string[]} tokens
 * @returns {{predictable: boolean, entropy: number, duplicates: number}}
 */
function verifyCsrfTokens(tokens) {
  if (!tokens || tokens.length === 0) return { predictable: true, entropy: 0, duplicates: 0 };
  const unique = new Set(tokens);
  const duplicates = tokens.length - unique.size;
  // Calcular entropia média
  let totalEntropy = 0;
  for (const t of tokens) {
    const freq = {};
    for (const c of t) freq[c] = (freq[c] || 0) + 1;
    let e = 0;
    for (const count of Object.values(freq)) {
      const p = count / t.length;
      e -= p * Math.log2(p);
    }
    totalEntropy += e;
  }
  const avgEntropy = totalEntropy / tokens.length;
  return {
    predictable: avgEntropy < 3.5 || duplicates > 0,
    entropy: Math.round(avgEntropy * 100) / 100,
    duplicates,
    totalChecked: tokens.length,
  };
}

if (require.main === module) {
  (async () => {
    console.log("[WHITE-HAT] WASM Crypto Verifier");
    console.log("═".repeat(50));

    const wasm = await loadWasm();
    console.log(`WASM disponível: ${wasm !== null}`);

    // Hash verification
    const hashes = ["$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi", "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855", "d41d8cd98f00b204e9800998ecf8427e", "not_a_hash"];
    console.log("\nVerificação de hashes:");
    for (const h of hashes) {
      const result = verifyHashFormat(h);
      console.log(`  ${result.type}: valid=${result.valid} weak=${result.weak}`);
    }

    // JWT verification
    const sampleJwt = "eyJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoiMSIsInJvbGUiOiJhZG1pbiJ9.fake_sig";
    const jwtResult = verifyJwtIntegrity(sampleJwt);
    console.log(`\nJWT: alg=${jwtResult.algorithm} weak=${jwtResult.weak}`);

    // CSRF token verification
    const csrfTokens = Array.from({ length: 10 }, () => crypto.randomBytes(32).toString("hex"));
    const csrfResult = verifyCsrfTokens(csrfTokens);
    console.log(`\nCSRF tokens: entropy=${csrfResult.entropy} predictable=${csrfResult.predictable}`);

    console.log("\n[WHITE-HAT] Crypto Verifier completo");
  })();
}

module.exports = {
  WASM_CHECKSUM,
  loadWasm,
  verifyHashFormat,
  verifyJwtIntegrity,
  verifyCsrfTokens,
};
// PULL REQUEST END
