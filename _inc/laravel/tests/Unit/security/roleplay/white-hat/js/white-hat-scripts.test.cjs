// @ts-nocheck
// ▓ Roleplay: White Hat — Script Validation Tests (Jest)
// Valida que as ferramentas OWASP do white-hat funcionam corretamente,
// geram output estruturado e que o sistema defende contra as técnicas.
// PULL REQUEST START
"use strict";

const { execSync } = require("child_process");
const path = require("path");

const SCRIPTS = path.resolve(__dirname, "../../../../../Feature/security/roleplay/white-hat");
const SERVER = process.env.APP_URL || "http://127.0.0.1:8000";

/** Roda script com timeout e retorna stdout. */
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

describe("White Hat — Script Validation", () => {
  // ─── JS: DOM XSS Probe ───────────────────────────────────
  describe("dom_xss_probe.cjs", () => {
    const { generateDomXssPayloads, auditHtmlForSinks } = require(path.join(SCRIPTS, "js/scripts/dom_xss_probe.cjs"));

    test("gera payloads XSS com campo obrigatórios", () => {
      const payloads = generateDomXssPayloads();
      expect(Array.isArray(payloads)).toBe(true);
      expect(payloads.length).toBeGreaterThanOrEqual(5);
      for (const p of payloads) {
        expect(p).toHaveProperty("payload");
        expect(typeof p.payload).toBe("string");
      }
    });

    test("detecta innerHTML sink em HTML", () => {
      const html = `<script>document.getElementById('x').innerHTML = input;</script>`;
      const sinks = auditHtmlForSinks(html);
      expect(sinks.length).toBeGreaterThan(0);
      expect(sinks.some(s => /innerHTML/i.test(s.sink))).toBe(true);
    });

    test("retorna vazio para HTML seguro", () => {
      const html = `<p>Texto seguro</p>`;
      const sinks = auditHtmlForSinks(html);
      expect(sinks.length).toBe(0);
    });

    test("script executa via node", () => {
      const out = run(`node "${path.join(SCRIPTS, "js/scripts/dom_xss_probe.cjs")}"`);
      expect(out).toContain("[WHITE-HAT]");
    });
  });

  // ─── Bash: OWASP SQLi Scanner ────────────────────────────
  describe("owasp_sqli_scan.sh", () => {
    test("script produz output estruturado", () => {
      const out = run(`bash "${path.join(SCRIPTS, "bash/scripts/owasp_sqli_scan.sh")}"`, 60000);
      expect(out).toContain("[WHITE-HAT] OWASP SQLi");
      expect(out).toMatch(/error.based|union|boolean|time.blind|stacked/i);
    });
  });

  // ─── Bash: Session Fixation Test ──────────────────────────
  describe("session_fixation_test.sh", () => {
    test("script produz output sobre fixação de sessão", () => {
      const out = run(`bash "${path.join(SCRIPTS, "bash/scripts/session_fixation_test.sh")}"`, 30000);
      expect(out).toContain("[WHITE-HAT] Session Fixation");
    });
  });

  // ─── Python: OWASP Header Audit ──────────────────────────
  describe("owasp_header_audit.py", () => {
    test("script analisa headers e produz relatório", () => {
      const out = run(`python3 "${path.join(SCRIPTS, "py/scripts/owasp_header_audit.py")}" ${SERVER}`, 20000);
      expect(out).toContain("[WHITE-HAT]");
      expect(out).toMatch(/header|audit/i);
    });
  });

  // ─── Python: Auth Bypass Probe ────────────────────────────
  describe("auth_bypass_probe.py", () => {
    test("script testa bypass de autenticação", () => {
      const out = run(`python3 "${path.join(SCRIPTS, "py/scripts/auth_bypass_probe.py")}" ${SERVER}`, 30000);
      expect(out).toContain("[WHITE-HAT] Auth Bypass Probe");
      expect(out).toMatch(/testes|bypasses/i);
    });
  });

  // ─── PHP: Parameter Tamper ────────────────────────────────
  describe("param_tamper.php", () => {
    test("script PHP executa e testa type juggling", () => {
      const out = run(`php "${path.join(SCRIPTS, "php/scripts/param_tamper.php")}"`, 30000);
      expect(out).toContain("[WHITE-HAT]");
      expect(out).toMatch(/type.juggling|param/i);
    });
  });
});
// PULL REQUEST END
