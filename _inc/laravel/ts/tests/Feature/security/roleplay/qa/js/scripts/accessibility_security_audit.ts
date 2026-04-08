#!/usr/bin/env node
// ▓ Roleplay: QA — Accessibility + Security Audit ▓
// Verifica que controles de segurança não quebram acessibilidade
// PULL REQUEST START
import http from "http";
import url from "url";

const TARGET = process.env.APP_URL || "http://127.0.0.1:8000";

const A11Y_SECURITY_CHECKS = [
  {
    name: "CAPTCHA com alternativa acessível",
    check: body => {
      const hasCaptcha = /captcha|recaptcha|hcaptcha/i.test(body);
      const hasAlt = /aria-label.*captcha|role="img".*alt=|audio.*captcha/i.test(body);
      return { relevant: hasCaptcha, pass: !hasCaptcha || hasAlt };
    },
    severity: "medium",
  },
  {
    name: "Formulário de login tem labels",
    check: body => {
      const hasLoginForm = /type=["']password["']/i.test(body);
      const hasLabels = /<label\b[^>]*for=/i.test(body);
      const hasAriaLabel = /aria-label/i.test(body);
      return { relevant: hasLoginForm, pass: !hasLoginForm || hasLabels || hasAriaLabel };
    },
    severity: "high",
  },
  {
    name: "Mensagens de erro acessíveis via aria-live",
    check: body => {
      const hasErrors = /error|invalid|falha|erro/i.test(body);
      const hasAriaLive = /aria-live=["'](polite|assertive)["']/i.test(body);
      const hasRole = /role=["']alert["']/i.test(body);
      return { relevant: hasErrors, pass: !hasErrors || hasAriaLive || hasRole };
    },
    severity: "medium",
  },
  {
    name: "Timeout de sessão com aviso acessível",
    check: body => {
      const hasTimeout = /session.*timeout|sessão.*expirar/i.test(body);
      const hasWarning = /aria-live.*timeout|role="timer"/i.test(body);
      return { relevant: hasTimeout, pass: !hasTimeout || hasWarning };
    },
    severity: "high",
  },
  {
    name: "MFA com alternativas (não só visual)",
    check: body => {
      const hasMfa = /mfa|2fa|two.*factor|authenticator/i.test(body);
      const hasMultiple = /sms|email|backup.*code|recovery/i.test(body);
      return { relevant: hasMfa, pass: !hasMfa || hasMultiple };
    },
    severity: "high",
  },
  {
    name: "Contraste de cor em alertas de segurança",
    check: body => {
      const hasAlerts = /alert|warning|danger|error/i.test(body);
      // Não pode depender APENAS de cor
      const hasIcon = /icon|fa-|bi-|material-icons|⚠|❌|✓/i.test(body);
      const hasText = /aria-label|sr-only|visually-hidden/i.test(body);
      return { relevant: hasAlerts, pass: !hasAlerts || hasIcon || hasText };
    },
    severity: "medium",
  },
  {
    name: "Autocompletede seguro em campos sensíveis",
    check: body => {
      const hasPassword = /type=["']password["']/i.test(body);
      const hasAutocomplete = /autocomplete=["'](current-password|new-password|off)["']/i.test(body);
      return { relevant: hasPassword, pass: !hasPassword || hasAutocomplete };
    },
    severity: "low",
  },
];

/**
 * Busca o HTML de uma página
 * @param {string} targetUrl
 * @returns {Promise<string>}
 */
function fetchPage(targetUrl) {
  return new Promise(resolve => {
    const opts = { ...url.parse(targetUrl), method: "GET", timeout: 5000 };
    const req = http.request(opts, res => {
      let body = "";
      res.on("data", d => (body += d));
      res.on("end", () => resolve(body));
    });
    req.on("error", () => resolve(""));
    req.on("timeout", () => {
      req.destroy();
      resolve("");
    });
    req.end();
  });
}

/**
 * Roda todos os checks de acessibilidade + segurança
 * @param {string} html
 * @returns {object[]}
 */
function runChecks(html) {
  return A11Y_SECURITY_CHECKS.map(c => {
    const result = c.check(html);
    return {
      name: c.name,
      severity: c.severity,
      relevant: result.relevant,
      pass: result.pass,
    };
  });
}

if (process.argv[1]?.endsWith("accessibility_security_audit.ts")) {
  (async () => {
    console.log("[QA] Accessibility + Security Audit");
    console.log(`Alvo: ${TARGET}`);
    console.log("═".repeat(50));

    const pages = ["/login", "/register", "/", "/dashboard"];
    let totalChecks = 0;
    let totalPass = 0;
    let totalFail = 0;

    for (const page of pages) {
      console.log(`\nPágina: ${page}`);
      const html = await fetchPage(`${TARGET}${page}`);
      if (!html) {
        console.log("  [?] Não foi possível carregar");
        continue;
      }

      const results = runChecks(html);
      for (const r of results) {
        if (!r.relevant) continue;
        totalChecks++;
        const status = r.pass ? "[✓]" : "[✗]";
        if (r.pass) totalPass++;
        else totalFail++;
        console.log(`  ${status} ${r.name} (${r.severity})`);
      }
    }

    const score = totalChecks > 0 ? Math.round((totalPass / totalChecks) * 100) : 0;
    console.log(`\n── Resumo ──`);
    console.log(`  Checks relevantes: ${totalChecks}`);
    console.log(`  Pass: ${totalPass}`);
    console.log(`  Fail: ${totalFail}`);
    console.log(`  Score: ${score}%`);
    console.log("\n[QA] A11y Security Audit completo");
  })();
}

export { A11Y_SECURITY_CHECKS, fetchPage, runChecks };
// PULL REQUEST END
