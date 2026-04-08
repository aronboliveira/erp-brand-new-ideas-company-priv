// ▓ Roleplay: CISO — Script Validation Tests (Jest)
// Valida as ferramentas de auditoria de compliance do CISO.
// PULL REQUEST START
import { execSync } from "child_process";
import path from "path";

const SCRIPTS = path.resolve(__dirname, "../../../../../Feature/security/roleplay/ciso");
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

describe("CISO — Script Validation", () => {
  // ─── JS: CSP Analyzer ────────────────────────────────────
  describe("csp_analyzer", () => {
    // eslint-disable-next-line @typescript-eslint/no-require-imports
    const { parseCSP, analyzeCSP, CSP_DIRECTIVES } = require(path.join(SCRIPTS, "js/scripts/csp_analyzer.ts"));

    test("parseCSP extrai directivas", () => {
      const csp = "default-src 'self'; script-src 'self' 'unsafe-inline'";
      const parsed = parseCSP(csp);
      expect(parsed["default-src"]).toEqual(["'self'"]);
      expect(parsed["script-src"]).toEqual(["'self'", "'unsafe-inline'"]);
    });

    test("parseCSP retorna vazio para string vazia", () => {
      expect(parseCSP("")).toEqual({});
      expect(parseCSP(null)).toEqual({});
    });

    test("analyzeCSP detecta unsage-inline como perigoso", () => {
      const findings = analyzeCSP("script-src 'self' 'unsafe-inline'");
      const dangerous = findings.filter(f => f.status === "DANGEROUS" && f.directive === "script-src");
      expect(dangerous.length).toBeGreaterThan(0);
    });

    test("analyzeCSP detecta directivas ausentes", () => {
      const findings = analyzeCSP("default-src 'self'");
      const missing = findings.filter(f => f.status === "MISSING");
      expect(missing.length).toBeGreaterThan(0);
    });

    test("CSP_DIRECTIVES tem entradas esperadas", () => {
      expect(CSP_DIRECTIVES).toHaveProperty("default-src");
      expect(CSP_DIRECTIVES).toHaveProperty("script-src");
      expect(CSP_DIRECTIVES["script-src"].severity).toBe("CRITICAL");
    });

    test("script executa via node", () => {
      const out = run(`node --experimental-strip-types "${path.join(SCRIPTS, "js/scripts/csp_analyzer.ts")}"`);
      expect(out).toContain("[CISO] CSP Policy Analyzer");
    });
  });

  // ─── Bash: Security Header Scan ──────────────────────────
  describe("security_header_scan.sh", () => {
    test("script audita headers de compliance", () => {
      const out = run(`bash "${path.join(SCRIPTS, "bash/scripts/security_header_scan.sh")}"`, 60000);
      expect(out).toContain("[CISO] Security Header Compliance");
      expect(out).toMatch(/compliance|Nota/i);
    });
  });

  // ─── Bash: Cookie Flags Audit ────────────────────────────
  describe("cookie_flags_audit.sh", () => {
    test("script audita flags de cookies", () => {
      const out = run(`bash "${path.join(SCRIPTS, "bash/scripts/cookie_flags_audit.sh")}"`, 30000);
      expect(out).toContain("[CISO] Cookie Security Flags");
      expect(out).toMatch(/flags|CONFORME/i);
    });
  });

  // ─── Python: Compliance Audit ────────────────────────────
  describe("compliance_audit.py", () => {
    test("script executa auditoria OWASP", () => {
      const out = run(`python3 "${path.join(SCRIPTS, "py/scripts/compliance_audit.py")}"`, 30000);
      expect(out).toContain("[CISO] OWASP Compliance Audit");
      expect(out).toMatch(/checks passaram|Classificação/i);
    });
  });

  // ─── PHP: CSRF Audit ─────────────────────────────────────
  describe("csrf_audit.php", () => {
    test("script PHP audita proteção CSRF", () => {
      const out = run(`php "${path.join(SCRIPTS, "php/scripts/csrf_audit.php")}"`, 30000);
      expect(out).toContain("[CISO] CSRF Protection Audit");
      expect(out).toMatch(/rotas protegidas/i);
    });
  });
});
// PULL REQUEST END
