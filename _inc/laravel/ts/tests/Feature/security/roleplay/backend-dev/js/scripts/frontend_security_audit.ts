// ▓ Roleplay: Backend Developer — Frontend Security Audit
// Dev backend. Escaneia código JS/HTML para padrões inseguros no frontend.
// PULL REQUEST START
import fs from "fs";
import path from "path";

// eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
const _scriptDir = (globalThis as any).__dirname ?? path.dirname(process.argv[1] || ".");
const DEFAULT_ROOT = path.resolve(_scriptDir, "..", "..", "..", "..", "..", "..", "resources");

/** Padrões inseguros no frontend. */
const PATTERNS = [
  {
    label: "innerHTML assignment",
    regex: /\.innerHTML\s*=(?!=)/g,
    severity: "HIGH",
    ext: [".js", "", ".mjs", ".ts", ".vue", ".blade.php"],
  },
  {
    label: "eval() call",
    regex: /\beval\s*\(/g,
    severity: "CRITICAL",
    ext: [".js", "", ".mjs", ".ts", ".vue"],
  },
  {
    label: "document.write()",
    regex: /document\.write\s*\(/g,
    severity: "HIGH",
    ext: [".js", "", ".mjs", ".ts", ".vue", ".blade.php"],
  },
  {
    label: "Inline event handler",
    regex: /\bon(click|load|error|mouseover|submit|focus)\s*=/gi,
    severity: "MEDIUM",
    ext: [".html", ".blade.php", ".vue"],
  },
  {
    label: "javascript: protocol",
    regex: /href\s*=\s*["']javascript:/gi,
    severity: "HIGH",
    ext: [".html", ".blade.php", ".vue"],
  },
  {
    label: "Unescaped Blade {!! !!}",
    regex: /\{!!/g,
    severity: "MEDIUM",
    ext: [".blade.php"],
  },
  {
    label: "localStorage sensitive data",
    regex: /localStorage\.(setItem|getItem)\s*\(\s*['"](?:token|password|secret|key)/gi,
    severity: "HIGH",
    ext: [".js", "", ".mjs", ".ts", ".vue"],
  },
  {
    label: "Hardcoded API key/secret",
    regex: /(api[_-]?key|api[_-]?secret|auth[_-]?token)\s*[:=]\s*["'][^"']{8,}/gi,
    severity: "CRITICAL",
    ext: [".js", "", ".mjs", ".ts", ".vue", ".env"],
  },
];

/**
 * Escaneia um diretório recursivamente.
 * @param {string} dir
 * @returns {Array<{file: string, line: number, pattern: string, severity: string, code: string}>}
 */
function scanDir(dir) {
  const findings = [];
  if (!fs.existsSync(dir)) return findings;

  const walk = d => {
    for (const entry of fs.readdirSync(d, { withFileTypes: true })) {
      const full = path.join(d, entry.name);
      if (entry.isDirectory()) {
        if (["node_modules", "vendor", ".git"].includes(entry.name)) continue;
        walk(full);
      } else {
        const ext = path.extname(entry.name);
        for (const p of PATTERNS) {
          if (!p.ext.includes(ext)) continue;
          try {
            const content = fs.readFileSync(full, "utf-8");
            const lines = content.split("\n");
            for (let i = 0; i < lines.length; i++) {
              if (p.regex.test(lines[i])) {
                findings.push({
                  file: full,
                  line: i + 1,
                  pattern: p.label,
                  severity: p.severity,
                  code: lines[i].trim().substring(0, 120),
                });
              }
              p.regex.lastIndex = 0; // Reset global regex
            }
          } catch {
            // Skip unreadable files
          }
        }
      }
    }
  };

  walk(dir);
  return findings;
}

export { scanDir, PATTERNS };

// Execução standalone
if (process.argv[1]?.endsWith("frontend_security_audit.ts")) {
  const root = process.argv[2] || DEFAULT_ROOT;
  console.log("[BACKEND-DEV] Frontend Security Audit v1.0");
  console.log(`[BACKEND-DEV] Raiz: ${root}`);
  console.log("═".repeat(50));

  const findings = scanDir(root);

  if (findings.length === 0) {
    console.log("\n  [✓] Nenhum padrão inseguro encontrado");
  } else {
    console.log(`\n  [✗] ${findings.length} padrões inseguros:`);
    for (const f of findings.slice(0, 20)) {
      const rel = path.relative(root, f.file);
      console.log(`    [${f.severity}] ${rel}:${f.line}`);
      console.log(`      ${f.pattern}: ${f.code.substring(0, 80)}`);
    }
    if (findings.length > 20) {
      console.log(`    ... e mais ${findings.length - 20}`);
    }
  }

  console.log(`\n${"═".repeat(50)}`);
  console.log(`[BACKEND-DEV] Total: ${findings.length} findings`);
}
// PULL REQUEST END
