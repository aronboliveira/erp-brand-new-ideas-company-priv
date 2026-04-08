// Roleplay: White Hat (Ethical Pentester) — Jest OWASP payload validation
// Ref: OWASP Testing Guide v4 — OTG-INPVAL-005
// PULL REQUEST START

/**
 * Pentester ético — verifica que payloads categorizados são reconhecidos
 * como perigosos e que encoding funciona corretamente.
 */

const ERROR_BASED = [
  "' AND EXTRACTVALUE(1,CONCAT(0x7e,version()))--",
  "' AND UPDATEXML(1,CONCAT(0x7e,version()),1)--",
];

const UNION_BASED = [
  "' UNION SELECT NULL--",
  "' UNION SELECT NULL,NULL,NULL--",
  "1' UNION SELECT username,password FROM users--",
];

const BOOLEAN_BLIND = [
  "1' AND 1=1--",
  "1' AND 1=2--",
  "1' AND SUBSTRING(@@version,1,1)='8'--",
];

const TIME_BLIND = ["1' AND SLEEP(0)--", "1' AND IF(1=1,SLEEP(0),0)--"];

const ALL = [...ERROR_BASED, ...UNION_BASED, ...BOOLEAN_BLIND, ...TIME_BLIND];

// Regex simples para detecção de SQLi (WAF-level)
const SQLI_PATTERN =
  /(\bUNION\b|\bSELECT\b|\bSLEEP\b|\bEXTRACTVALUE\b|\bUPDATEXML\b|\bWAITFOR\b|--|;|')/i;

describe("White Hat — OWASP SQLi Payload Classification", () => {
  describe("Error-based payloads", () => {
    test.each(ERROR_BASED)("detecta error-based: %s", (payload) => {
      expect(SQLI_PATTERN.test(payload)).toBe(true);
    });
  });

  describe("Union-based payloads", () => {
    test.each(UNION_BASED)("detecta union-based: %s", (payload) => {
      expect(payload.toUpperCase()).toMatch(/UNION\s+SELECT/);
    });
  });

  describe("Boolean-blind payloads", () => {
    test.each(BOOLEAN_BLIND)("detecta boolean-blind: %s", (payload) => {
      expect(payload).toMatch(/AND\s+/i);
    });
  });

  describe("Time-blind payloads", () => {
    test.each(TIME_BLIND)("detecta time-blind: %s", (payload) => {
      expect(payload.toUpperCase()).toMatch(/SLEEP|WAITFOR/);
    });
  });

  describe("Encoding analysis", () => {
    test.each(ALL)(
      "encodeURIComponent transforma payload: %s",
      (payload) => {
        const encoded = encodeURIComponent(payload);
        // Espaços devem estar codificados como %20
        expect(encoded).not.toContain(" ");
        // O payload codificado é diferente do original (tem chars escapados)
        expect(encoded.length).toBeGreaterThanOrEqual(payload.length);
      }
    );
  });

  test("total de payloads OWASP catalogados", () => {
    expect(ALL.length).toBeGreaterThanOrEqual(9);
  });
});
// PULL REQUEST END
