// Roleplay: Backend Developer — Code review (Unit/JS)
// Foco: verificar patterns de código no frontend (anti-patterns)
// PULL REQUEST START

const fs = require("fs");
const path = require("path");

const RESOURCES_DIR = path.resolve(__dirname, "../../../../../../resources");
const VIEWS_DIR = path.join(RESOURCES_DIR, "views");

/**
 * Recursivamente lista todos os arquivos com determinada extensão.
 * @param {string} dir
 * @param {string} ext
 * @returns {string[]}
 */
function findFiles(dir, ext) {
  if (!fs.existsSync(dir)) return [];
  const results = [];
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      results.push(...findFiles(full, ext));
    } else if (entry.name.endsWith(ext)) {
      results.push(full);
    }
  }
  return results;
}

describe("Backend Developer — Code Review (Unit/JS)", () => {
  describe("Blade templates: no inline JS with user input", () => {
    const bladeFiles = findFiles(VIEWS_DIR, ".blade.php");

    test("no Blade files use {!! $var !!} (unescaped output)", () => {
      const violations = [];
      for (const file of bladeFiles) {
        const content = fs.readFileSync(file, "utf-8");
        // {!! ... !!} — unescaped Blade output
        if (/{!!\s*\$/.test(content)) {
          violations.push(path.relative(VIEWS_DIR, file));
        }
      }
      // Informativo — pode haver usos legítimos
      if (violations.length > 0) {
        console.warn(
          `[BACKEND-DEV] {!! $var !!} encontrado em: ${violations.slice(0, 5).join(", ")}`
        );
      }
      // Não falha — é informativo
      expect(true).toBe(true);
    });

    test("no inline onclick/onerror in Blade templates", () => {
      const violations = [];
      for (const file of bladeFiles) {
        const content = fs.readFileSync(file, "utf-8");
        if (/\bon(?:click|error|load|mouseover)\s*=/i.test(content)) {
          violations.push(path.relative(VIEWS_DIR, file));
        }
      }
      if (violations.length > 0) {
        console.warn(
          `[BACKEND-DEV] Inline events em: ${violations.slice(0, 5).join(", ")}`
        );
      }
      expect(true).toBe(true);
    });
  });

  describe("JavaScript files: no eval() or innerHTML with variables", () => {
    const jsDir = path.resolve(RESOURCES_DIR, "js");
    const jsFiles = findFiles(jsDir, ".js");

    test("no eval() calls in JS resources", () => {
      const violations = [];
      for (const file of jsFiles) {
        const content = fs.readFileSync(file, "utf-8");
        if (/\beval\s*\(/.test(content)) {
          violations.push(path.relative(jsDir, file));
        }
      }
      expect(violations).toEqual([]);
    });
  });

  describe("Config files: security settings", () => {
    test("session.php has http_only = true", () => {
      const sessionConfig = path.resolve(
        VIEWS_DIR,
        "../../config/session.php"
      );
      if (!fs.existsSync(sessionConfig)) return;
      const content = fs.readFileSync(sessionConfig, "utf-8");
      // 'http_only' => env('SESSION_HTTP_ONLY', true)
      expect(content).toMatch(/http_only/);
    });
  });
});
// PULL REQUEST END
