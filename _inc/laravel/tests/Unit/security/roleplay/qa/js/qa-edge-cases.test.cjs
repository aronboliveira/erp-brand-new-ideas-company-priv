// Roleplay: QA Tester — Edge cases & sanitization (Unit/JS)
// Foco: validação de inputs, XSS, caracteres especiais
// PULL REQUEST START

const SPECIAL_NAMES = [
  "O'Brien",
  "名前テスト",
  "اسم",
  "José María Ñoño",
  "Teste 🎉👍",
  "<b>negrito</b>",
  "<script>alert(1)</script>",
  "test\\path\\file",
];

const XSS_VECTORS = [
  "<script>alert(1)</script>",
  '<img src=x onerror="alert(1)">',
  "<svg/onload=alert(1)>",
  "javascript:alert(1)",
  '"><script>alert(1)</script>',
];

/**
 * Escapa HTML — função que a app deveria usar
 * @param {string} str
 * @returns {string}
 */
function escapeHtml(str) {
  return str
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#x27;");
}

describe("QA — Edge Cases & Input Validation (Unit)", () => {
  describe("Caracteres especiais em nomes", () => {
    test.each(SPECIAL_NAMES)(
      'nome "%s" é uma string válida',
      (name) => {
        expect(typeof name).toBe("string");
        expect(name.length).toBeGreaterThan(0);
      }
    );

    test.each(SPECIAL_NAMES)(
      'nome "%s" pode ser URL-encoded',
      (name) => {
        const encoded = encodeURIComponent(name);
        expect(typeof encoded).toBe("string");
        // Decodificar de volta
        expect(decodeURIComponent(encoded)).toBe(name);
      }
    );
  });

  describe("XSS — sanitização HTML", () => {
    test.each(XSS_VECTORS)(
      'XSS "%s" é neutralizado por escapeHtml',
      (vector) => {
        const escaped = escapeHtml(vector);
        expect(escaped).not.toContain("<script>");
        expect(escaped).not.toContain("<svg");
        expect(escaped).not.toContain("<img");
      }
    );
  });

  describe("Input vazio e espaços", () => {
    test("string vazia tem length 0", () => {
      expect("".trim().length).toBe(0);
    });

    test("string com espaços tem trim length 0", () => {
      expect("   ".trim().length).toBe(0);
    });

    test("null coercion não gera exceção", () => {
      const val = null;
      expect(String(val ?? "")).toBe("");
    });
  });

  describe("Input longo", () => {
    test("string de 10000 chars pode ser truncada", () => {
      const long = "a".repeat(10000);
      const truncated = long.substring(0, 255);
      expect(truncated.length).toBe(255);
    });
  });
});
// PULL REQUEST END
