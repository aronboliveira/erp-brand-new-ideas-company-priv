// ▓ Roleplay: QA — New Scripts Unit Tests ▓
// Testes para: input_validator.cjs, accessibility_security_audit.cjs
// PULL REQUEST START
"use strict";

const { execSync } = require("child_process");
const path = require("path");

const SCRIPTS = path.resolve(
  __dirname,
  "../../../../../Feature/security/roleplay/qa"
);

describe("QA — WASM Input Validator", () => {
  const { validateInput, generateBoundaryInputs } = require(
    path.join(SCRIPTS, "wasm/scripts/input_validator.cjs")
  );

  describe("validateInput", () => {
    test("aceita input limpo", () => {
      const r = validateInput("Normal text input");
      expect(r.safe).toBe(true);
      expect(r.threats).toHaveLength(0);
    });

    test("detecta SQLi", () => {
      const r = validateInput("' OR '1'='1'--");
      expect(r.safe).toBe(false);
      expect(r.threats).toContain("SQLi");
    });

    test("detecta XSS", () => {
      const r = validateInput("<script>alert(1)</script>");
      expect(r.safe).toBe(false);
      expect(r.threats).toContain("XSS");
    });

    test("detecta Path Traversal", () => {
      const r = validateInput("../../etc/passwd");
      expect(r.safe).toBe(false);
      expect(r.threats).toContain("Path Traversal");
    });

    test("detecta SSTI", () => {
      const r = validateInput("{{7*7}}");
      expect(r.safe).toBe(false);
      expect(r.threats).toContain("SSTI");
    });

    test("detecta Command Injection", () => {
      const r = validateInput("; ls -la");
      expect(r.safe).toBe(false);
      expect(r.threats).toContain("Command Injection");
    });

    test("detecta múltiplas ameaças", () => {
      const r = validateInput("<script>; DROP TABLE</script>");
      expect(r.threats.length).toBeGreaterThanOrEqual(2);
    });

    test("trata null/undefined", () => {
      expect(validateInput(null).safe).toBe(true);
      expect(validateInput(undefined).safe).toBe(true);
      expect(validateInput("").safe).toBe(true);
    });
  });

  describe("generateBoundaryInputs", () => {
    test("gera inputs de texto", () => {
      const inputs = generateBoundaryInputs("text");
      expect(inputs.length).toBeGreaterThan(5);
      expect(inputs.some((i) => i.length > 255)).toBe(true);
    });

    test("gera inputs numéricos", () => {
      const inputs = generateBoundaryInputs("number");
      expect(inputs).toContain("0");
      expect(inputs).toContain("-1");
      expect(inputs).toContain("NaN");
    });

    test("gera inputs de email", () => {
      const inputs = generateBoundaryInputs("email");
      expect(inputs.some((i) => i.includes("@"))).toBe(true);
    });

    test("gera inputs de URL", () => {
      const inputs = generateBoundaryInputs("url");
      expect(inputs.some((i) => i.startsWith("javascript:"))).toBe(true);
    });
  });

  test("script executa via node", () => {
    const output = execSync(`node "${path.join(SCRIPTS, "wasm/scripts/input_validator.cjs")}"`, {
      encoding: "utf-8",
      timeout: 10000,
    });
    expect(output).toContain("[QA]");
    expect(output).toContain("Input Validator");
  });
});

describe("QA — Accessibility + Security Audit", () => {
  const { A11Y_SECURITY_CHECKS, runChecks } = require(
    path.join(SCRIPTS, "js/scripts/accessibility_security_audit.cjs")
  );

  test("A11Y_SECURITY_CHECKS contém checks", () => {
    expect(A11Y_SECURITY_CHECKS.length).toBeGreaterThanOrEqual(5);
    expect(A11Y_SECURITY_CHECKS[0]).toHaveProperty("name");
    expect(A11Y_SECURITY_CHECKS[0]).toHaveProperty("check");
  });

  test("runChecks funciona com HTML simples", () => {
    const html = '<html><body><form><input type="password"><label for="pw">PW</label></form></body></html>';
    const results = runChecks(html);
    expect(Array.isArray(results)).toBe(true);
    expect(results.length).toBe(A11Y_SECURITY_CHECKS.length);
  });

  test("runChecks detecta formulário de login sem label", () => {
    const html = '<html><body><form><input type="password" name="pw"></form></body></html>';
    const results = runChecks(html);
    const loginCheck = results.find((r) => r.name.includes("login"));
    if (loginCheck && loginCheck.relevant) {
      expect(loginCheck.pass).toBe(false);
    }
  });
});
// PULL REQUEST END
