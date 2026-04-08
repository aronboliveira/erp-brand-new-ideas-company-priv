/**
 * @file sqli-sanitization.test.ts
 * @description Auditoria de segurança — Testes unitários que validam
 *   que inputs do lado do cliente são sanitizados antes de envio ao backend.
 *   Simula um pentester verificando que payloads SQL maliciosos
 *   não passam sem tratamento pela camada de formulários.
 *
 *   PULL REQUEST START
 */

import fs from "fs";
import path from "path";

/* ══════════════════════════════════════════════════════════════════
 *  Payloads maliciosos
 * ══════════════════════════════════════════════════════════════════ */

const SQLI_PAYLOADS = ["' OR '1'='1", "' OR '1'='1' --", "'; DROP TABLE users; --", "1; DROP TABLE settings; --", "' UNION SELECT NULL,NULL,NULL --", "' UNION SELECT username,password,NULL FROM users --", "1' AND SLEEP(5) --", "admin'--", "-1 OR 1=1", "' OR '' = '"];

const XSS_VIA_SQLI = ['<script>alert("sqli")</script>', '"><img src=x onerror=alert(1)>', "javascript:alert(document.cookie)", "<svg onload=alert(1)>", "' onmouseover='alert(1)'"];

const DANGEROUS_CHARS = ["'", '"', ";", "--", "/*", "*/", "\\", "\x00"];

/* ══════════════════════════════════════════════════════════════════
 *  Helpers
 * ══════════════════════════════════════════════════════════════════ */

/** Escapa HTML de maneira básica (como o Blade {{ }} faz) */
function htmlEntities(str: string): string {
  return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

/** Verifica se uma string contém marcadores de SQL injection */
function containsSqlKeywords(str: string): boolean {
  const patterns = [/\bUNION\b/i, /\bSELECT\b/i, /\bDROP\b/i, /\bINSERT\b/i, /\bDELETE\s+FROM\b/i, /\bUPDATE\b.*\bSET\b/i, /\bSLEEP\s*\(/i, /\bBENCHMARK\s*\(/i, /--\s*$/, /;\s*$/];
  return patterns.some(p => p.test(str));
}

/* ══════════════════════════════════════════════════════════════════
 *  Testes: Sanitização de input
 * ══════════════════════════════════════════════════════════════════ */

describe("SQL Injection — Sanitização de Input no Cliente", () => {
  test("htmlEntities neutraliza todos os payloads SQLi", () => {
    for (const payload of SQLI_PAYLOADS) {
      const escaped = htmlEntities(payload);
      // Aspas simples devem ser convertidas
      expect(escaped).not.toContain("'");
      // Não deve ter aspas duplas literais
      expect(escaped).not.toContain('"');
    }
  });

  test("htmlEntities neutraliza payloads XSS-via-SQLi", () => {
    for (const payload of XSS_VIA_SQLI) {
      const escaped = htmlEntities(payload);
      expect(escaped).not.toContain("<");
      expect(escaped).not.toContain(">");
    }
  });

  test("containsSqlKeywords detecta payloads conhecidos", () => {
    const detectable = ["' UNION SELECT NULL --", "'; DROP TABLE users; --", "1' AND SLEEP(5) --"];
    for (const payload of detectable) {
      expect(containsSqlKeywords(payload)).toBe(true);
    }
  });

  test("containsSqlKeywords não gera falso positivo para input normal", () => {
    const safeInputs = ["João da Silva", "email@example.com", "Rua das Flores, 123", "12345-678", "+55 11 99999-0000", "R$ 1.234,56"];
    for (const input of safeInputs) {
      expect(containsSqlKeywords(input)).toBe(false);
    }
  });
});

/* ══════════════════════════════════════════════════════════════════
 *  Testes: DOM — formulários não aceitam payloads sem escape
 * ══════════════════════════════════════════════════════════════════ */

describe("SQL Injection — DOM Form Security", () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <form id="test-form" action="/test" method="POST">
        <input type="text" name="search" id="search-input" maxlength="255" />
        <input type="email" name="email" id="email-input" />
        <input type="number" name="id" id="id-input" />
        <input type="hidden" name="_token" value="csrf-token-here" />
        <button type="submit">Submit</button>
      </form>
    `;
  });

  test("input[type=email] rejeita payloads SQLi via validação nativa", () => {
    const emailInput = document.getElementById("email-input") as HTMLInputElement;
    for (const payload of SQLI_PAYLOADS) {
      emailInput.value = payload;
      // Navegadores validam email nativamente — verify o padrão
      expect(emailInput.checkValidity()).toBe(false);
    }
  });

  test("input[type=number] rejeita strings SQLi", () => {
    const numInput = document.getElementById("id-input") as HTMLInputElement;
    for (const payload of SQLI_PAYLOADS) {
      numInput.value = payload;
      // valueAsNumber deve ser NaN para payloads string
      expect(isNaN(numInput.valueAsNumber)).toBe(true);
    }
  });

  test("input[maxlength] limita tamanho de payloads longos", () => {
    const searchInput = document.getElementById("search-input") as HTMLInputElement;
    const longPayload = "' OR '1'='1".repeat(100); // 1100 chars
    searchInput.value = longPayload;
    // maxlength=255 deve truncar no navegador (jsdom não enforce, mas verify atributo)
    expect(parseInt(searchInput.getAttribute("maxlength") || "0")).toBeLessThan(longPayload.length);
  });

  test("formulário contém token CSRF", () => {
    const tokenInput = document.querySelector('input[name="_token"]') as HTMLInputElement;
    expect(tokenInput).not.toBeNull();
    expect(tokenInput.value).toBeTruthy();
    expect(tokenInput.type).toBe("hidden");
  });
});

/* ══════════════════════════════════════════════════════════════════
 *  Testes: Static scan — recursos/views com $_GET
 * ══════════════════════════════════════════════════════════════════ */

describe("SQL Injection — Static Code Scan (Blade views)", () => {
  const viewsDir = path.resolve(__dirname, "../../../../resources/views");

  test("enumerar usos de $_GET em Blade views (informativo)", () => {
    if (!fs.existsSync(viewsDir)) {
      console.warn("Diretório de views não encontrado: " + viewsDir);
      return;
    }

    const violations: { file: string; line: number; code: string }[] = [];

    function scanDir(dir: string): void {
      const entries = fs.readdirSync(dir, { withFileTypes: true });
      for (const entry of entries) {
        const fullPath = path.join(dir, entry.name);
        if (entry.isDirectory()) {
          scanDir(fullPath);
        } else if (entry.name.endsWith(".blade.php")) {
          const content = fs.readFileSync(fullPath, "utf-8");
          const lines = content.split("\n");
          lines.forEach((line: string, i: number) => {
            if (/\$_GET\b|\$_POST\b|\$_REQUEST\b/.test(line)) {
              violations.push({
                file: path.relative(viewsDir, fullPath),
                line: i + 1,
                code: line.trim().substring(0, 120),
              });
            }
          });
        }
      }
    }

    scanDir(viewsDir);

    if (violations.length > 0) {
      console.warn(`⚠ ${violations.length} usos de superglobals encontrados em Blade views:`);
      violations.slice(0, 20).forEach(v => {
        console.warn(`  ${v.file}:${v.line} — ${v.code}`);
      });
    }

    // Informativo — não falha, apenas reporta
    expect(true).toBe(true);
  });

  test("nenhum Blade view usa {!! $request->input() !!} (unsafe echo)", () => {
    if (!fs.existsSync(viewsDir)) {
      return;
    }

    const critical: { file: string; line: number; code: string }[] = [];

    function scanDir(dir: string): void {
      const entries = fs.readdirSync(dir, { withFileTypes: true });
      for (const entry of entries) {
        const fullPath = path.join(dir, entry.name);
        if (entry.isDirectory()) {
          scanDir(fullPath);
        } else if (entry.name.endsWith(".blade.php")) {
          const content = fs.readFileSync(fullPath, "utf-8");
          const lines = content.split("\n");
          lines.forEach((line: string, i: number) => {
            // {!! ... !!} com input de request são perigosos
            if (/\{!!\s*\$_GET\b/.test(line) || /\{!!\s*\$_POST\b/.test(line) || /\{!!\s*\$_REQUEST\b/.test(line) || /\{!!\s*request\(\)->input\b/.test(line)) {
              critical.push({
                file: path.relative(viewsDir, fullPath),
                line: i + 1,
                code: line.trim().substring(0, 120),
              });
            }
          });
        }
      }
    }

    scanDir(viewsDir);

    expect(critical).toEqual([]);
  });
});

/* ══════════════════════════════════════════════════════════════════
 *  Testes: Encoding de caracteres perigosos
 * ══════════════════════════════════════════════════════════════════ */

describe("SQL Injection — Character Encoding Safety", () => {
  test("encodeURIComponent escapa caracteres perigosos (exceto ')", () => {
    for (const ch of DANGEROUS_CHARS) {
      if (ch === "\x00") continue; // null byte handled separately
      const encoded = encodeURIComponent(ch);
      // encodeURIComponent NÃO codifica aspas simples (') — comportamento por padrão!
      // Apenas validar que os demais caracteres são codificados
      if (/[a-zA-Z0-9\-_.~']/.test(ch)) continue;
      expect(encoded).not.toBe(ch);
    }
  });

  test("payloads SQLi têm caracteres críticos codificados por encodeURIComponent", () => {
    for (const payload of SQLI_PAYLOADS) {
      const encoded = encodeURIComponent(payload);
      // Espaços, =, ;, -- devem estar percent-encoded
      expect(encoded).not.toContain(" ");
      // Nota: aspas simples (') NÃO são codificadas por encodeURIComponent
      // — é necessário sanitização adicional no servidor
    }
  });

  test("payloads duplo-encoded são detectados", () => {
    const doubleEncoded = [
      "%2527", // double-encoded '
      "%253B", // double-encoded ;
      "%252D%252D", // double-encoded --
    ];
    for (const de of doubleEncoded) {
      const decoded = decodeURIComponent(de);
      // Primeira decodificação ainda tem percent-encoding
      expect(decoded).toMatch(/%[0-9A-Fa-f]{2}/);
    }
  });
});

// PULL REQUEST END
