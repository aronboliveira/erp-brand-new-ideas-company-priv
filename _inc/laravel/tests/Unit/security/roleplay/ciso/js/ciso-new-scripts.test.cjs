// ▓ Roleplay: CISO — New Scripts Unit Tests ▓
// Testes para: policy_hash.cjs, tls_certificate_audit.cjs
// PULL REQUEST START
"use strict";

const { execSync } = require("child_process");
const path = require("path");

const SCRIPTS = path.resolve(
  __dirname,
  "../../../../../Feature/security/roleplay/ciso"
);

describe("CISO — Policy Hash Verifier (WASM)", () => {
  const { SECURITY_POLICIES, hashPolicy, auditPolicy } = require(
    path.join(SCRIPTS, "wasm/scripts/policy_hash.cjs")
  );

  test("SECURITY_POLICIES contém todas as políticas", () => {
    expect(SECURITY_POLICIES).toHaveProperty("passwordPolicy");
    expect(SECURITY_POLICIES).toHaveProperty("sessionPolicy");
    expect(SECURITY_POLICIES).toHaveProperty("accessControl");
    expect(SECURITY_POLICIES).toHaveProperty("dataProtection");
  });

  test("hashPolicy gera SHA256 de 64 chars", () => {
    const hash = hashPolicy(SECURITY_POLICIES.passwordPolicy);
    expect(hash).toMatch(/^[a-f0-9]{64}$/);
  });

  test("hashPolicy é determinístico", () => {
    const h1 = hashPolicy(SECURITY_POLICIES.passwordPolicy);
    const h2 = hashPolicy(SECURITY_POLICIES.passwordPolicy);
    expect(h1).toBe(h2);
  });

  test("auditPolicy aprova política compliant", () => {
    const result = auditPolicy("passwordPolicy", SECURITY_POLICIES.passwordPolicy);
    expect(result.compliant).toBe(true);
    expect(result.issues).toHaveLength(0);
  });

  test("auditPolicy detecta senha fraca", () => {
    const weakPolicy = { ...SECURITY_POLICIES.passwordPolicy, minLength: 6, requireSpecial: false };
    const result = auditPolicy("passwordPolicy", weakPolicy);
    expect(result.compliant).toBe(false);
    expect(result.issues.length).toBeGreaterThan(0);
  });

  test("auditPolicy detecta sessão insegura", () => {
    const weakSession = { ...SECURITY_POLICIES.sessionPolicy, secureCookie: false, maxIdleMinutes: 60 };
    const result = auditPolicy("sessionPolicy", weakSession);
    expect(result.compliant).toBe(false);
  });

  test("script executa via node", () => {
    const output = execSync(`node "${path.join(SCRIPTS, "wasm/scripts/policy_hash.cjs")}"`, {
      encoding: "utf-8",
      timeout: 10000,
    });
    expect(output).toContain("[CISO]");
    expect(output).toContain("Policy Hash Verifier");
  });
});

describe("CISO — TLS Certificate Audit", () => {
  const { generateTlsReport, WEAK_CIPHERS, WEAK_PROTOCOLS } = require(
    path.join(SCRIPTS, "js/scripts/tls_certificate_audit.cjs")
  );

  test("WEAK_CIPHERS contém ciphers conhecidos fracos", () => {
    expect(WEAK_CIPHERS).toContain("RC4");
    expect(WEAK_CIPHERS).toContain("DES");
    expect(WEAK_CIPHERS).toContain("NULL");
  });

  test("WEAK_PROTOCOLS contém protocolos antigos", () => {
    expect(WEAK_PROTOCOLS).toContain("SSLv3");
    expect(WEAK_PROTOCOLS).toContain("TLSv1");
  });

  test("generateTlsReport dá nota A para cert perfeito", () => {
    const cert = {
      expired: false,
      expiringSoon: false,
      selfSigned: false,
      weakCipher: false,
      weakProtocol: false,
    };
    const hsts = { present: true, maxAge: 31536000, includeSubDomains: true, preload: true };
    const report = generateTlsReport(cert, hsts);
    expect(report.grade).toBe("A");
    expect(report.score).toBe(100);
  });

  test("generateTlsReport penaliza cert expirado", () => {
    const cert = { expired: true, selfSigned: false, weakCipher: false, weakProtocol: false };
    const hsts = { present: true, maxAge: 31536000, includeSubDomains: true, preload: true };
    const report = generateTlsReport(cert, hsts);
    expect(report.score).toBeLessThan(100);
    expect(report.issues).toContain("Certificado expirado");
  });

  test("generateTlsReport penaliza falta de HSTS", () => {
    const cert = { expired: false, selfSigned: false, weakCipher: false, weakProtocol: false };
    const hsts = { present: false };
    const report = generateTlsReport(cert, hsts);
    expect(report.issues).toContain("HSTS não habilitado");
  });
});
// PULL REQUEST END
