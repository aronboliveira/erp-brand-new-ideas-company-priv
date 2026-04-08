#!/usr/bin/env node
// ▓ Roleplay: CISO — TLS Certificate Audit ▓
// Audita configuração TLS/SSL de endpoints
// PULL REQUEST START
import https from "https";
import tls from "tls";
import url from "url";

const TARGET = process.env.APP_URL || "https://127.0.0.1:443";

const WEAK_CIPHERS = ["RC4", "DES", "3DES", "MD5", "NULL", "EXPORT", "anon", "RC2", "IDEA", "SEED"];

const WEAK_PROTOCOLS = ["SSLv2", "SSLv3", "TLSv1", "TLSv1.1"];

const REQUIRED_HEADERS = {
  "strict-transport-security": {
    present: false,
    minMaxAge: 31536000,
    requireSubdomains: true,
    requirePreload: true,
  },
};

/**
 * Verifica o certificado TLS de um host
 * @param {string} hostname
 * @param {number} port
 * @returns {Promise<object>}
 */
function checkCertificate(hostname, port = 443) {
  return new Promise(resolve => {
    const socket = tls.connect({ host: hostname, port, rejectUnauthorized: false, timeout: 5000 }, () => {
      const cert = socket.getPeerCertificate();
      const cipher = socket.getCipher();
      const protocol = socket.getProtocol();

      const now = new Date();
      const validFrom = new Date(cert.valid_from);
      const validTo = new Date(cert.valid_to);
      const daysToExpiry = Math.floor((validTo - now) / (1000 * 60 * 60 * 24));

      const result = {
        subject: cert.subject || {},
        issuer: cert.issuer || {},
        validFrom: cert.valid_from,
        validTo: cert.valid_to,
        daysToExpiry,
        expired: daysToExpiry < 0,
        expiringSoon: daysToExpiry > 0 && daysToExpiry < 30,
        serialNumber: cert.serialNumber,
        fingerprint: cert.fingerprint256 || cert.fingerprint,
        cipher: cipher ? cipher.name : "unknown",
        protocol,
        weakCipher: cipher ? WEAK_CIPHERS.some(w => cipher.name.includes(w)) : false,
        weakProtocol: protocol ? WEAK_PROTOCOLS.includes(protocol) : false,
        selfSigned: cert.issuer && cert.subject && JSON.stringify(cert.issuer) === JSON.stringify(cert.subject),
        san: cert.subjectaltname || "",
      };

      socket.end();
      resolve(result);
    });

    socket.on("error", err => {
      resolve({ error: err.message });
    });

    socket.on("timeout", () => {
      socket.destroy();
      resolve({ error: "timeout" });
    });
  });
}

/**
 * Verifica headers HSTS
 * @param {string} targetUrl
 * @returns {Promise<object>}
 */
function checkHSTS(targetUrl) {
  return new Promise(resolve => {
    const opts = { ...url.parse(targetUrl), method: "HEAD", rejectUnauthorized: false, timeout: 5000 };
    const req = https.request(opts, res => {
      const hsts = res.headers["strict-transport-security"] || "";
      const result = {
        present: !!hsts,
        value: hsts,
        maxAge: 0,
        includeSubDomains: false,
        preload: false,
      };
      if (hsts) {
        const maxAgeMatch = hsts.match(/max-age=(\d+)/);
        if (maxAgeMatch) result.maxAge = parseInt(maxAgeMatch[1], 10);
        result.includeSubDomains = /includeSubDomains/i.test(hsts);
        result.preload = /preload/i.test(hsts);
      }
      resolve(result);
    });
    req.on("error", () => resolve({ present: false, error: true }));
    req.on("timeout", () => {
      req.destroy();
      resolve({ present: false, error: true });
    });
    req.end();
  });
}

/**
 * Gera relatório de compliance TLS
 * @param {object} cert
 * @param {object} hsts
 * @returns {{score: number, grade: string, issues: string[]}}
 */
function generateTlsReport(cert, hsts) {
  let score = 100;
  const issues = [];

  if (cert.error) {
    issues.push("Não foi possível conectar via TLS");
    score -= 100;
  }
  if (cert.expired) {
    issues.push("Certificado expirado");
    score -= 50;
  }
  if (cert.expiringSoon) {
    issues.push(`Certificado expira em ${cert.daysToExpiry} dias`);
    score -= 20;
  }
  if (cert.selfSigned) {
    issues.push("Certificado auto-assinado");
    score -= 30;
  }
  if (cert.weakCipher) {
    issues.push(`Cipher fraco: ${cert.cipher}`);
    score -= 25;
  }
  if (cert.weakProtocol) {
    issues.push(`Protocolo fraco: ${cert.protocol}`);
    score -= 25;
  }
  if (!hsts.present) {
    issues.push("HSTS não habilitado");
    score -= 15;
  }
  if (hsts.present && hsts.maxAge < 31536000) {
    issues.push("HSTS max-age < 1 ano");
    score -= 10;
  }
  if (hsts.present && !hsts.includeSubDomains) {
    issues.push("HSTS sem includeSubDomains");
    score -= 5;
  }
  if (hsts.present && !hsts.preload) {
    issues.push("HSTS sem preload");
    score -= 5;
  }

  score = Math.max(0, score);
  const grade = score >= 90 ? "A" : score >= 80 ? "B" : score >= 60 ? "C" : score >= 40 ? "D" : "F";

  return { score, grade, issues };
}

if (process.argv[1]?.endsWith("tls_certificate_audit.ts")) {
  (async () => {
    console.log("[CISO] TLS Certificate Audit");
    console.log(`Alvo: ${TARGET}`);
    console.log("═".repeat(50));

    const parsed = url.parse(TARGET);
    const hostname = parsed.hostname || "127.0.0.1";
    const port = parseInt(parsed.port || "443", 10);

    const cert = await checkCertificate(hostname, port);
    const hsts = await checkHSTS(TARGET);
    const report = generateTlsReport(cert, hsts);

    if (cert.error) {
      console.log(`\n[!] Erro: ${cert.error}`);
    } else {
      console.log(`\nCertificado:`);
      console.log(`  Emitido para: ${JSON.stringify(cert.subject)}`);
      console.log(`  Emissor: ${JSON.stringify(cert.issuer)}`);
      console.log(`  Válido até: ${cert.validTo} (${cert.daysToExpiry} dias)`);
      console.log(`  Cipher: ${cert.cipher}`);
      console.log(`  Protocolo: ${cert.protocol}`);
      console.log(`  Auto-assinado: ${cert.selfSigned}`);
    }

    console.log(`\nHSTS: ${hsts.present ? hsts.value : "NÃO HABILITADO"}`);
    console.log(`\nGrade: ${report.grade} (${report.score}/100)`);
    if (report.issues.length) {
      console.log("Issues:");
      report.issues.forEach(i => console.log(`  - ${i}`));
    }

    console.log("\n[CISO] TLS Audit completo");
  })();
}

export { checkCertificate, checkHSTS, generateTlsReport, WEAK_CIPHERS, WEAK_PROTOCOLS };
// PULL REQUEST END
