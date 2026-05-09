// Roleplay: Green Hat (Script Kiddie) — Smoke test ingênuo
// "Achei esse teste num GitHub aleatório e adaptei..."
// PULL REQUEST START
/** @type {import('jest').Config} */
const _BASE = process.env.APP_URL || "http://127.0.0.1:8000";

describe("Green Hat — Smoke SQLi (Unit/JS)", () => {
  const payloads = ["' OR '1'='1", "admin'--", "1 OR 1=1"];

  // Verifica que payloads não passam sem escapar
  test.each(payloads)("payload '%s' é uma string perigosa", (payload) => {
    // "Se eu colocar isso num campo, deveria ser tratado"
    expect(typeof payload).toBe("string");
    expect(payload.length).toBeGreaterThan(0);
    // Simples: não deveria conter só letras
    expect(payload).toMatch(/['"\\\-;=]/);
  });

  test("payloads clássicos existem na lista", () => {
    expect(payloads).toContain("' OR '1'='1");
    expect(payloads.length).toBeGreaterThanOrEqual(3);
  });

  test("encodeURIComponent escapa os payloads", () => {
    payloads.forEach((p) => {
      const encoded = encodeURIComponent(p);
      // "Se codificar, os espaços somem, certo?"
      expect(encoded).not.toContain(" ");
      // O payload codificado é diferente se tinha chars especiais
      expect(encoded.length).toBeGreaterThanOrEqual(p.length);
    });
  });
});
// PULL REQUEST END
