// ▓ Roleplay: Backend Dev — New Scripts Unit Tests ▓
// Testes para: hash_benchmark.cjs, api_rate_limit_test.cjs
// PULL REQUEST START
import { execSync } from "child_process";
import path from "path";

const SCRIPTS = path.resolve(__dirname, "../../../../../Feature/security/roleplay/backend-dev");

describe("Backend Dev — Hash Benchmark (WASM)", () => {
  // eslint-disable-next-line @typescript-eslint/no-require-imports
  const { benchmarkHash, evaluateBcryptCost, ALGORITHMS } = require(path.join(SCRIPTS, "wasm/scripts/hash_benchmark.ts"));

  test("ALGORITHMS contém md5, sha1, sha256, sha512", () => {
    expect(ALGORITHMS).toContain("md5");
    expect(ALGORITHMS).toContain("sha1");
    expect(ALGORITHMS).toContain("sha256");
    expect(ALGORITHMS).toContain("sha512");
  });

  test("benchmarkHash retorna métricas", () => {
    const result = benchmarkHash("sha256", 100);
    expect(result).toHaveProperty("algo", "sha256");
    expect(result).toHaveProperty("iterations", 100);
    expect(result).toHaveProperty("timeMs");
    expect(result).toHaveProperty("hashesPerSec");
    expect(result.timeMs).toBeGreaterThan(0);
  });

  test("benchmarkHash marca md5 como fraco", () => {
    const result = benchmarkHash("md5", 10);
    expect(result.weak).toBe(true);
  });

  test("benchmarkHash marca sha256 como forte", () => {
    const result = benchmarkHash("sha256", 10);
    expect(result.weak).toBe(false);
  });

  test("evaluateBcryptCost avalia cost baixo como inadequado", () => {
    const result = evaluateBcryptCost(8);
    expect(result.adequate).toBe(false);
    expect(result.recommendation).toMatch(/CRÍTICO/);
  });

  test("evaluateBcryptCost avalia cost 12 como adequado", () => {
    const result = evaluateBcryptCost(12);
    expect(result.adequate).toBe(true);
    expect(result.recommendation).toMatch(/OK/);
  });

  test("script executa via node", () => {
    const output = execSync(`node --experimental-strip-types "${path.join(SCRIPTS, "wasm/scripts/hash_benchmark.ts")}"`, {
      encoding: "utf-8",
      timeout: 15000,
    });
    expect(output).toContain("[BACKEND-DEV]");
    expect(output).toContain("Hash Benchmark");
  });
});

describe("Backend Dev — API Rate Limit Tester", () => {
  // eslint-disable-next-line @typescript-eslint/no-require-imports
  const { ENDPOINTS_TO_TEST, generateReport } = require(path.join(SCRIPTS, "js/scripts/api_rate_limit_test.ts"));

  test("ENDPOINTS_TO_TEST tem endpoints críticos", () => {
    expect(ENDPOINTS_TO_TEST.length).toBeGreaterThanOrEqual(5);
    const criticalEndpoints = ENDPOINTS_TO_TEST.filter(e => e.critical);
    expect(criticalEndpoints.length).toBeGreaterThanOrEqual(2);
  });

  test("generateReport calcula score corretamente", () => {
    const mockResults = [
      { endpoint: "Login", path: "/api/login", critical: true, protected: true },
      { endpoint: "Register", path: "/api/register", critical: true, protected: false },
      { endpoint: "Search", path: "/api/search", critical: false, protected: true },
    ];
    const report = generateReport(mockResults);
    expect(report.totalEndpoints).toBe(3);
    expect(report.protected).toBe(2);
    expect(report.unprotected).toBe(1);
    expect(report.issues.length).toBeGreaterThan(0);
    expect(report.issues[0]).toMatch(/CRÍTICO/);
  });

  test("generateReport dá nota A quando tudo protegido", () => {
    const mockResults = [
      { endpoint: "Login", critical: true, protected: true },
      { endpoint: "Register", critical: true, protected: true },
      { endpoint: "Search", critical: false, protected: true },
    ];
    const report = generateReport(mockResults);
    expect(report.grade).toBe("A");
    expect(report.score).toBe(100);
  });
});
// PULL REQUEST END
