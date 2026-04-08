// ▓ Roleplay: QA — Script Validation Tests (Jest)
// Valida as ferramentas de fuzzing e boundary testing do QA.
// PULL REQUEST START
import { execSync } from "child_process";
import path from "path";

const SCRIPTS = path.resolve(__dirname, "../../../../../Feature/security/roleplay/qa");
const SERVER = process.env.APP_URL || "http://127.0.0.1:8000";

function run(cmd, timeout = 30000) {
  try {
    return execSync(cmd, {
      encoding: "utf-8",
      timeout,
      env: { ...process.env, APP_URL: SERVER },
    });
  } catch (e) {
    return e.stdout || e.stderr || e.message;
  }
}

describe("QA — Script Validation", () => {
  // ─── JS: Form Fuzzer ─────────────────────────────────────
  describe("form_fuzzer", () => {
    // eslint-disable-next-line @typescript-eslint/no-require-imports
    const { generateFuzzPayloads, FUZZ_PAYLOADS } = require(path.join(SCRIPTS, "js/scripts/form_fuzzer.ts"));

    test("gera payloads de fuzzing por categoria", () => {
      const payloads = generateFuzzPayloads();
      expect(Array.isArray(payloads)).toBe(true);
      expect(payloads.length).toBeGreaterThanOrEqual(15);
      for (const p of payloads) {
        expect(p).toHaveProperty("category");
        expect(p).toHaveProperty("payload");
      }
    });

    test("tem categorias esperadas", () => {
      const categories = Object.keys(FUZZ_PAYLOADS);
      expect(categories).toContain("xss");
      expect(categories).toContain("sqli");
      expect(categories).toContain("overflow");
      expect(categories).toContain("special");
    });

    test("payloads XSS incluem script tags", () => {
      expect(FUZZ_PAYLOADS.xss.some(p => p.includes("<script"))).toBe(true);
    });

    test("payloads overflow incluem strings longas", () => {
      expect(FUZZ_PAYLOADS.overflow.some(p => p.length > 200)).toBe(true);
    });

    test("script executa via node", () => {
      const out = run(`node --experimental-strip-types "${path.join(SCRIPTS, "js/scripts/form_fuzzer.ts")}"`);
      expect(out).toContain("[QA] Form Fuzzer");
      expect(out).toContain("payloads de fuzz");
    });
  });

  // ─── Python: Boundary Value Generator ────────────────────
  describe("boundary_value_gen.py", () => {
    test("script gera valores de fronteira", () => {
      const out = run(`python3 "${path.join(SCRIPTS, "py/scripts/boundary_value_gen.py")}"`);
      expect(out).toContain("[QA] Boundary Value Generator");
      expect(out).toMatch(/campos|valores gerados/i);
    });
  });

  // ─── Bash: URL Tamper ────────────────────────────────────
  describe("url_tamper.sh", () => {
    test("script testa manipulação de URL", () => {
      const out = run(`bash "${path.join(SCRIPTS, "bash/scripts/url_tamper.sh")}"`, 60000);
      expect(out).toContain("[QA] URL Parameter Tamper");
      expect(out).toMatch(/IDOR|Path Traversal|CATEGORY/i);
    });
  });
});
// PULL REQUEST END
