#!/usr/bin/env node
// ▓ Roleplay: Green Hat — WASM Hello Security ▓
// Carrega módulo WASM básico como exercício de aprendizado
// PULL REQUEST START
"use strict";

/**
 * Módulo WASM mínimo que retorna um valor fixo (42).
 * Demonstra como um iniciante carrega e executa WASM.
 */
const WASM_HELLO = new Uint8Array([
  0x00, 0x61, 0x73, 0x6d, // magic: \0asm
  0x01, 0x00, 0x00, 0x00, // version: 1
  // Type section: (func (result i32))
  0x01, 0x05, 0x01, 0x60, 0x00, 0x01, 0x7f,
  // Function section
  0x03, 0x02, 0x01, 0x00,
  // Export section: "answer" -> func 0
  0x07, 0x0a, 0x01, 0x06, 0x61, 0x6e, 0x73, 0x77, 0x65, 0x72, 0x00, 0x00,
  // Code section: return 42
  0x0a, 0x06, 0x01, 0x04, 0x00, 0x41, 0x2a, 0x0b,
]);

/**
 * Carrega e instancia o módulo WASM
 * @returns {Promise<{answer: () => number} | null>}
 */
async function loadWasm() {
  try {
    const { instance } = await WebAssembly.instantiate(WASM_HELLO);
    return instance.exports;
  } catch {
    return null;
  }
}

/**
 * "Ofuscação" ingênua — XOR com chave fixa
 * @param {string} str
 * @returns {string}
 */
function simpleXor(str) {
  const key = 0x13;
  return Buffer.from(str)
    .map((b) => b ^ key)
    .toString("hex");
}

if (require.main === module) {
  (async () => {
    console.log("[GREEN-HAT] WASM Hello Security");
    console.log("═".repeat(40));

    const wasm = await loadWasm();
    if (wasm) {
      console.log(`WASM loaded — answer(): ${wasm.answer()}`);
    } else {
      console.log("WASM not available, using JS fallback");
    }

    const testStr = "admin:password123";
    console.log(`\nXOR ofuscação simples:`);
    console.log(`  Original: ${testStr}`);
    console.log(`  XOR(0x13): ${simpleXor(testStr)}`);
    console.log("\n[GREEN-HAT] WASM Hello completo");
  })();
}

module.exports = { WASM_HELLO, loadWasm, simpleXor };
// PULL REQUEST END
