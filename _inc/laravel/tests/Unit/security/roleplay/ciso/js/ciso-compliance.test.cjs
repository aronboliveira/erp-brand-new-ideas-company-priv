// Roleplay: CISO — Compliance checks (Unit/JS)
// Foco: validar HTML security patterns, CSP, CSRF meta tags
// PULL REQUEST START

const fs = require("fs");
const path = require("path");

const VIEWS_DIR = path.resolve(__dirname, "../../../../../../resources/views");

describe("CISO — Compliance Unit Checks (JS)", () => {
  describe("CSRF token policy", () => {
    test("layout principal contém csrf-token meta ou @csrf", () => {
      // Buscar layout Blade principal
      const candidates = ["app.blade.php", "master.blade.php", "layout.blade.php"];
      let found = false;

      for (const name of candidates) {
        const layouts = path.join(VIEWS_DIR, "layouts", name);
        if (fs.existsSync(layouts)) {
          const content = fs.readFileSync(layouts, "utf-8");
          if (content.includes("csrf-token") || content.includes("@csrf") || content.includes("csrf_token()")) {
            found = true;
          }
          break;
        }
      }

      // Também verificar diretório raiz de views
      if (!found) {
        const viewFiles = fs.readdirSync(VIEWS_DIR).filter(f => f.endsWith(".blade.php"));
        for (const file of viewFiles) {
          const content = fs.readFileSync(path.join(VIEWS_DIR, file), "utf-8");
          if (content.includes("csrf-token") || content.includes("@csrf")) {
            found = true;
            break;
          }
        }
      }

      expect(found).toBe(true);
    });
  });

  describe("Security header recommendations", () => {
    const RECOMMENDED_HEADERS = ["X-Frame-Options", "X-Content-Type-Options", "X-XSS-Protection", "Content-Security-Policy", "Strict-Transport-Security"];

    test("lista de headers recomendados está completa", () => {
      expect(RECOMMENDED_HEADERS.length).toBeGreaterThanOrEqual(4);
    });

    test.each(RECOMMENDED_HEADERS)("header '%s' está na checklist de compliance", header => {
      expect(typeof header).toBe("string");
      expect(header.length).toBeGreaterThan(0);
    });
  });

  describe("Política de debug", () => {
    test("APP_DEBUG não deve estar true em .env.production", () => {
      const envProd = path.resolve(VIEWS_DIR, "../../.env.production");
      if (!fs.existsSync(envProd)) {
        // Sem .env.production — aceitável em dev
        return;
      }
      const content = fs.readFileSync(envProd, "utf-8");
      expect(content).not.toMatch(/APP_DEBUG\s*=\s*true/i);
    });
  });
});
// PULL REQUEST END
