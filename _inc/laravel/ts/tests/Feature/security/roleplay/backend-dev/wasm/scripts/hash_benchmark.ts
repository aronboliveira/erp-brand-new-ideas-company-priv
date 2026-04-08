#!/usr/bin/env node
// ▓ Roleplay: Backend Dev — WASM Hash Benchmark ▓
// Usa WASM para benchmarkar performance de hashing
// PULL REQUEST START
import crypto from "crypto";

const WASM_BENCH = new Uint8Array([0x00, 0x61, 0x73, 0x6d, 0x01, 0x00, 0x00, 0x00, 0x01, 0x05, 0x01, 0x60, 0x00, 0x01, 0x7f, 0x03, 0x02, 0x01, 0x00, 0x07, 0x09, 0x01, 0x05, 0x62, 0x65, 0x6e, 0x63, 0x68, 0x00, 0x00, 0x0a, 0x06, 0x01, 0x04, 0x00, 0x41, 0x01, 0x0b]);

async function loadWasm() {
  try {
    const { instance } = await WebAssembly.instantiate(WASM_BENCH);
    return instance.exports;
  } catch {
    return null;
  }
}

const ALGORITHMS = ["md5", "sha1", "sha256", "sha512"];

/**
 * Benchmarka um algoritmo de hash
 * @param {string} algo
 * @param {number} iterations
 * @returns {{algo: string, iterations: number, timeMs: number, hashesPerSec: number}}
 */
function benchmarkHash(algo, iterations = 10000) {
  const data = crypto.randomBytes(256);
  const start = process.hrtime.bigint();
  for (let i = 0; i < iterations; i++) {
    crypto.createHash(algo).update(data).digest("hex");
  }
  const elapsed = Number(process.hrtime.bigint() - start) / 1e6;
  return {
    algo,
    iterations,
    timeMs: Math.round(elapsed * 100) / 100,
    hashesPerSec: Math.round(iterations / (elapsed / 1000)),
    weak: ["md5", "sha1"].includes(algo),
  };
}

/**
 * Verifica se bcrypt cost é adequado
 * @param {number} cost
 * @returns {{adequate: boolean, recommendation: string, estimatedMs: number}}
 */
function evaluateBcryptCost(cost) {
  const estimatedMs = Math.pow(2, cost) * 0.05;
  return {
    cost,
    adequate: cost >= 12,
    estimatedMs: Math.round(estimatedMs),
    recommendation: cost < 10 ? "CRÍTICO: Cost muito baixo, use >= 12" : cost < 12 ? "AVISO: Cost recomendado >= 12" : "OK: Cost adequado",
  };
}

if (process.argv[1]?.endsWith("hash_benchmark.ts")) {
  (async () => {
    console.log("[BACKEND-DEV] WASM Hash Benchmark");
    console.log("═".repeat(50));

    const wasm = await loadWasm();
    console.log(`WASM disponível: ${wasm !== null}`);

    console.log("\nBenchmark de algoritmos:");
    for (const algo of ALGORITHMS) {
      const result = benchmarkHash(algo, 5000);
      const status = result.weak ? "[FRACO]" : "[OK]   ";
      console.log(`  ${status} ${algo}: ${result.hashesPerSec} h/s (${result.timeMs}ms)`);
    }

    console.log("\nAvaliação de bcrypt cost:");
    for (const cost of [8, 10, 12, 14]) {
      const eval_ = evaluateBcryptCost(cost);
      console.log(`  Cost ${cost}: ~${eval_.estimatedMs}ms — ${eval_.recommendation}`);
    }

    console.log("\n[BACKEND-DEV] Hash Benchmark completo");
  })();
}

export { WASM_BENCH, loadWasm, benchmarkHash, evaluateBcryptCost, ALGORITHMS };
// PULL REQUEST END
