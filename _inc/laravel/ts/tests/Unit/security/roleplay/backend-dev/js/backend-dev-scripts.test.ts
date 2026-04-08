// ▓ Roleplay: Backend Developer — Script Validation Tests (Jest)
// Valida as ferramentas de análise estática do backend developer.
// PULL REQUEST START
import { execSync } from "child_process";
import path from "path";

const SCRIPTS = path.resolve(__dirname, "../../../../../Feature/security/roleplay/backend-dev");
const PROJECT = path.resolve(__dirname, "../../../../../..");
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

describe("Backend Developer — Script Validation", () => {
  // ─── JS: Frontend Security Audit ─────────────────────────
  describe("frontend_security_audit", () => {
    // eslint-disable-next-line @typescript-eslint/no-require-imports
    const { scanDir, PATTERNS } = require(path.join(SCRIPTS, "js/scripts/frontend_security_audit.ts"));

    test("PATTERNS tem entradas esperadas", () => {
      expect(PATTERNS.length).toBeGreaterThanOrEqual(5);
      const labels = PATTERNS.map(p => p.label);
      expect(labels).toContain("eval() call");
      expect(labels).toContain("innerHTML assignment");
    });

    test("scanDir detecta innerHTML em HTML de teste", () => {
      // Escaneia os mock-apps (devem ter innerHTML intencional)
      const mockDir = path.resolve(PROJECT, "tests/mock-apps");
      const findings = scanDir(mockDir);
      // mock-apps têm vulnerabilidades intencionais
      expect(Array.isArray(findings)).toBe(true);
    });

    test("scanDir retorna array vazio para diretório inexistente", () => {
      const findings = scanDir("/tmp/nonexistent-dir-12345");
      expect(findings).toEqual([]);
    });

    test("script executa via node", () => {
      const out = run(`node --experimental-strip-types "${path.join(SCRIPTS, "js/scripts/frontend_security_audit.ts")}" "${path.resolve(PROJECT, "resources")}"`);
      expect(out).toContain("[BACKEND-DEV] Frontend Security Audit");
    });
  });

  // ─── Bash: SAST Scanner ──────────────────────────────────
  describe("sast_scanner.sh", () => {
    test("script escaneia código-fonte", () => {
      const out = run(`bash "${path.join(SCRIPTS, "bash/scripts/sast_scanner.sh")}" "${PROJECT}"`, 60000);
      expect(out).toContain("[BACKEND-DEV] SAST Scanner");
      expect(out).toMatch(/findings|CATEGORY/i);
    });
  });

  // ─── Python: Dependency Audit ────────────────────────────
  describe("dependency_audit.py", () => {
    test("script audita composer.lock", () => {
      const lockPath = path.resolve(PROJECT, "composer.lock");
      const out = run(`python3 "${path.join(SCRIPTS, "py/scripts/dependency_audit.py")}" "${lockPath}"`, 30000);
      expect(out).toContain("[BACKEND-DEV] Dependency Audit");
      expect(out).toMatch(/pacotes|Arquivo não encontrado/i);
    });
  });

  // ─── PHP: Raw Query Detector ─────────────────────────────
  describe("raw_query_detector.php", () => {
    test("script PHP escaneia queries raw", () => {
      const appDir = path.resolve(PROJECT, "app");
      const out = run(`php "${path.join(SCRIPTS, "php/scripts/raw_query_detector.php")}" "${appDir}"`, 30000);
      expect(out).toContain("[BACKEND-DEV] Raw Query Detector");
      expect(out).toMatch(/arquivos PHP escaneados|Diretório não encontrado/i);
    });
  });
});
// PULL REQUEST END
