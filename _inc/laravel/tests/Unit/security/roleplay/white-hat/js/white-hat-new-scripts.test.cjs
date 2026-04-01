// ▓ Roleplay: White Hat — New Scripts Unit Tests ▓
// Testes para: crypto_verifier.cjs, csp_bypass_test.cjs
// PULL REQUEST START
"use strict";

const { execSync } = require("child_process");
const path = require("path");

const SCRIPTS = path.resolve(
  __dirname,
  "../../../../../Feature/security/roleplay/white-hat"
);

describe("White Hat — Crypto Verifier (WASM)", () => {
  const { verifyHashFormat, verifyJwtIntegrity, verifyCsrfTokens } = require(
    path.join(SCRIPTS, "wasm/scripts/crypto_verifier.cjs")
  );

  describe("verifyHashFormat", () => {
    test("detecta bcrypt", () => {
      const r = verifyHashFormat("$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi");
      expect(r.valid).toBe(true);
      expect(r.type).toBe("bcrypt");
      expect(r.weak).toBe(false);
    });

    test("detecta sha256", () => {
      const r = verifyHashFormat("e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855");
      expect(r.valid).toBe(true);
      expect(r.type).toBe("sha256");
    });

    test("detecta md5 como fraco", () => {
      const r = verifyHashFormat("d41d8cd98f00b204e9800998ecf8427e");
      expect(r.valid).toBe(true);
      expect(r.type).toBe("md5");
      expect(r.weak).toBe(true);
    });

    test("rejeita hash inválido", () => {
      const r = verifyHashFormat("not_a_hash");
      expect(r.valid).toBe(false);
    });
  });

  describe("verifyJwtIntegrity", () => {
    test("parseia JWT válido", () => {
      const jwt = "eyJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoiMSIsInJvbGUiOiJhZG1pbiJ9.fake_sig";
      const r = verifyJwtIntegrity(jwt);
      expect(r.valid).toBe(true);
      expect(r.algorithm).toBe("HS256");
      expect(r.weak).toBe(true);
    });

    test("rejeita token inválido", () => {
      const r = verifyJwtIntegrity("not.a.jwt");
      expect(r.valid).toBe(false);
    });
  });

  describe("verifyCsrfTokens", () => {
    test("tokens aleatórios não são previsíveis", () => {
      const tokens = Array.from({ length: 10 }, () =>
        require("crypto").randomBytes(32).toString("hex")
      );
      const r = verifyCsrfTokens(tokens);
      expect(r.entropy).toBeGreaterThan(3);
      expect(r.duplicates).toBe(0);
    });

    test("tokens duplicados são previsíveis", () => {
      const r = verifyCsrfTokens(["aaa", "aaa", "aaa"]);
      expect(r.duplicates).toBe(2);
      expect(r.predictable).toBe(true);
    });
  });
});

describe("White Hat — CSP Bypass Tester", () => {
  const { CSP_BYPASS_VECTORS, parseCSP, analyzeBypasses } = require(
    path.join(SCRIPTS, "js/scripts/csp_bypass_test.cjs")
  );

  test("parseCSP parseia diretivas corretamente", () => {
    const csp = "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src *";
    const directives = parseCSP(csp);
    expect(directives["default-src"]).toEqual(["'self'"]);
    expect(directives["script-src"]).toEqual(["'self'", "'unsafe-inline'"]);
    expect(directives["style-src"]).toEqual(["*"]);
  });

  test("analyzeBypasses detecta unsafe-inline", () => {
    const directives = parseCSP("script-src 'unsafe-inline'");
    const findings = analyzeBypasses(directives);
    const scriptFindings = findings.filter((f) => f.cspDirective.startsWith("script-src"));
    expect(scriptFindings.some((f) => f.vulnerable)).toBe(true);
  });

  test("analyzeBypasses detecta wildcard", () => {
    const directives = parseCSP("default-src *");
    const findings = analyzeBypasses(directives);
    expect(findings.some((f) => f.vulnerable)).toBe(true);
  });

  test("CSP_BYPASS_VECTORS tem vetores suficientes", () => {
    expect(CSP_BYPASS_VECTORS.length).toBeGreaterThanOrEqual(5);
  });
});
// PULL REQUEST END
