// ▓ Roleplay: CISO — CSP Policy Analyzer
// Auditor executivo. Analisa Content-Security-Policy do servidor.
// PULL REQUEST START
// @ts-check
"use strict";

const http = require("http");
const https = require("https");
const url = require("url");

const SERVER = process.env.APP_URL || "http://127.0.0.1:8000";

/** Directivas CSP recomendadas e seu nível de risco. */
const CSP_DIRECTIVES = {
  "default-src": { required: true, recommended: "'self'", severity: "HIGH" },
  "script-src": {
    required: true,
    recommended: "'self'",
    dangerous: ["'unsafe-inline'", "'unsafe-eval'", "*", "data:"],
    severity: "CRITICAL",
  },
  "style-src": {
    required: false,
    recommended: "'self'",
    dangerous: ["'unsafe-inline'", "*"],
    severity: "MEDIUM",
  },
  "img-src": { required: false, recommended: "'self'", severity: "LOW" },
  "connect-src": {
    required: false,
    recommended: "'self'",
    dangerous: ["*"],
    severity: "MEDIUM",
  },
  "frame-ancestors": {
    required: true,
    recommended: "'none'",
    severity: "HIGH",
  },
  "base-uri": { required: true, recommended: "'self'", severity: "HIGH" },
  "form-action": {
    required: true,
    recommended: "'self'",
    severity: "HIGH",
  },
  "object-src": {
    required: true,
    recommended: "'none'",
    severity: "MEDIUM",
  },
};

/**
 * Faz parse de uma string CSP em um mapa directiva → valores.
 * @param {string} csp
 * @returns {Record<string, string[]>}
 */
function parseCSP(csp) {
  const directives = {};
  if (!csp) return directives;
  for (const part of csp.split(";")) {
    const trimmed = part.trim();
    if (!trimmed) continue;
    const [name, ...values] = trimmed.split(/\s+/);
    directives[name.toLowerCase()] = values;
  }
  return directives;
}

/**
 * Analisa CSP e retorna findings.
 * @param {string} csp
 * @returns {Array<{directive: string, status: string, severity: string, detail: string}>}
 */
function analyzeCSP(csp) {
  const parsed = parseCSP(csp);
  const findings = [];

  for (const [directive, config] of Object.entries(CSP_DIRECTIVES)) {
    const values = parsed[directive];

    if (!values) {
      if (config.required) {
        findings.push({
          directive,
          status: "MISSING",
          severity: config.severity,
          detail: `Directiva obrigatória ausente (recomendado: ${config.recommended})`,
        });
      }
      continue;
    }

    // Verifica valores perigosos
    if (config.dangerous) {
      for (const val of values) {
        if (config.dangerous.includes(val)) {
          findings.push({
            directive,
            status: "DANGEROUS",
            severity: config.severity,
            detail: `Valor perigoso: ${val}`,
          });
        }
      }
    }

    // Verifica wildcard
    if (values.includes("*")) {
      findings.push({
        directive,
        status: "WILDCARD",
        severity: "HIGH",
        detail: "Wildcard (*) permite qualquer origem",
      });
    }
  }

  // Verifica diretivas desconhecidas
  for (const dir of Object.keys(parsed)) {
    if (!CSP_DIRECTIVES[dir] && !["report-uri", "report-to", "upgrade-insecure-requests"].includes(dir)) {
      findings.push({
        directive: dir,
        status: "UNKNOWN",
        severity: "LOW",
        detail: `Directiva não reconhecida`,
      });
    }
  }

  return findings;
}

/**
 * Busca CSP do servidor.
 * @param {string} targetUrl
 * @returns {Promise<string>}
 */
function fetchCSP(targetUrl) {
  return new Promise((resolve, reject) => {
    const parsed = url.parse(targetUrl);
    const client = parsed.protocol === "https:" ? https : http;
    const req = client.get(targetUrl, { timeout: 10000 }, (res) => {
      const csp =
        res.headers["content-security-policy"] ||
        res.headers["content-security-policy-report-only"] ||
        "";
      resolve(csp);
    });
    req.on("error", reject);
    req.on("timeout", () => {
      req.destroy();
      reject(new Error("Timeout"));
    });
  });
}

/** Exporta funções para reuso em testes. */
module.exports = { parseCSP, analyzeCSP, fetchCSP, CSP_DIRECTIVES };

// Execução standalone
if (require.main === module) {
  (async () => {
    const target = process.argv[2] || SERVER;
    console.log("[CISO] CSP Policy Analyzer v1.0");
    console.log(`[CISO] Alvo: ${target}`);
    console.log("═".repeat(50));

    try {
      const csp = await fetchCSP(target);
      if (!csp) {
        console.log("\n[✗] Content-Security-Policy AUSENTE");
        console.log("[CISO] Recomendação: Implementar CSP header");
        return;
      }

      console.log(`\n[CSP] ${csp.substring(0, 100)}...`);
      const parsed = parseCSP(csp);
      console.log(`[CSP] ${Object.keys(parsed).length} directivas encontradas\n`);

      const findings = analyzeCSP(csp);
      for (const f of findings) {
        const icon = f.status === "MISSING" || f.status === "DANGEROUS" ? "✗" : "!";
        console.log(`  [${icon}] ${f.directive}: ${f.detail} (${f.severity})`);
      }

      const critical = findings.filter((f) => f.severity === "CRITICAL" || f.severity === "HIGH").length;
      console.log(`\n${"═".repeat(50)}`);
      console.log(`[CISO] ${findings.length} findings, ${critical} críticos/altos`);
    } catch (e) {
      console.log(`[CISO] Erro: ${e.message}`);
    }
  })();
}
// PULL REQUEST END
