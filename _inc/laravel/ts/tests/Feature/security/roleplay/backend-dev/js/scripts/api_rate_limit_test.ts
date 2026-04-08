#!/usr/bin/env node
// ▓ Roleplay: Backend Dev — API Rate Limit Tester ▓
// Verifica se rate limiting está configurado nos endpoints
// PULL REQUEST START
import http from "http";
import url from "url";

const TARGET = process.env.APP_URL || "http://127.0.0.1:8000";

const ENDPOINTS_TO_TEST = [
  { path: "/api/login", method: "POST", critical: true, name: "Login" },
  { path: "/api/register", method: "POST", critical: true, name: "Register" },
  { path: "/api/password/reset", method: "POST", critical: true, name: "Password Reset" },
  { path: "/api/users", method: "GET", critical: false, name: "User List" },
  { path: "/api/search", method: "GET", critical: false, name: "Search" },
  { path: "/api/export", method: "GET", critical: true, name: "Data Export" },
  { path: "/api/upload", method: "POST", critical: false, name: "File Upload" },
];

/**
 * Envia N requests rápidos para testar rate limit
 * @param {string} targetUrl
 * @param {object} endpoint
 * @param {number} count
 * @returns {Promise<object>}
 */
function testRateLimit(targetUrl, endpoint, count = 30) {
  return new Promise(resolve => {
    const results = [];
    let completed = 0;
    let rateLimited = false;
    let rateLimitHeader = null;

    for (let i = 0; i < count; i++) {
      const opts = {
        ...url.parse(targetUrl + endpoint.path),
        method: endpoint.method,
        timeout: 5000,
        headers: {
          "Content-Type": "application/json",
          "User-Agent": "RateLimitTester/1.0",
        },
      };

      const req = http.request(opts, res => {
        let body = "";
        res.on("data", d => (body += d));
        res.on("end", () => {
          results.push({ status: res.statusCode, headers: res.headers });
          if (res.statusCode === 429) rateLimited = true;

          // Check rate limit headers
          const rlHeaders = ["x-ratelimit-limit", "x-ratelimit-remaining", "retry-after", "x-rate-limit-limit", "ratelimit-limit"];
          for (const h of rlHeaders) {
            if (res.headers[h]) {
              rateLimitHeader = { name: h, value: res.headers[h] };
            }
          }

          completed++;
          if (completed === count) {
            resolve({
              endpoint: endpoint.name,
              path: endpoint.path,
              critical: endpoint.critical,
              totalRequests: count,
              rateLimited,
              rateLimitHeader,
              statusCodes: results.reduce((acc, r) => {
                acc[r.status] = (acc[r.status] || 0) + 1;
                return acc;
              }, {}),
              protected: rateLimited || !!rateLimitHeader,
            });
          }
        });
      });

      req.on("error", () => {
        completed++;
        if (completed === count) {
          resolve({
            endpoint: endpoint.name,
            path: endpoint.path,
            critical: endpoint.critical,
            totalRequests: count,
            error: true,
            rateLimited: false,
            protected: false,
          });
        }
      });

      if (endpoint.method === "POST") {
        req.write(JSON.stringify({ test: true }));
      }
      req.end();
    }
  });
}

/**
 * Gera relatório de rate limiting
 * @param {object[]} results
 * @returns {{score: number, grade: string, issues: string[]}}
 */
function generateReport(results) {
  const issues = [];
  let protectedCount = 0;

  for (const r of results) {
    if (r.protected) {
      protectedCount++;
    } else if (r.critical) {
      issues.push(`CRÍTICO: ${r.endpoint} (${r.path}) sem rate limit`);
    } else if (!r.error) {
      issues.push(`AVISO: ${r.endpoint} (${r.path}) sem rate limit`);
    }
  }

  const criticalEndpoints = results.filter(r => r.critical && !r.error);
  const criticalProtected = criticalEndpoints.filter(r => r.protected);

  const score = results.length > 0 ? Math.round((protectedCount / results.length) * 100) : 0;

  const grade = score >= 90 ? "A" : score >= 70 ? "B" : score >= 50 ? "C" : score >= 30 ? "D" : "F";

  return {
    totalEndpoints: results.length,
    protected: protectedCount,
    unprotected: results.length - protectedCount,
    criticalTotal: criticalEndpoints.length,
    criticalProtected: criticalProtected.length,
    score,
    grade,
    issues,
  };
}

if (process.argv[1]?.endsWith("api_rate_limit_test.ts")) {
  (async () => {
    console.log("[BACKEND-DEV] API Rate Limit Tester");
    console.log(`Alvo: ${TARGET}`);
    console.log("═".repeat(50));

    const results = [];
    for (const ep of ENDPOINTS_TO_TEST) {
      console.log(`\nTestando: ${ep.name} (${ep.method} ${ep.path})...`);
      const result = await testRateLimit(TARGET, ep, 20);
      results.push(result);

      if (result.error) {
        console.log(`  [?] Erro de conexão`);
      } else if (result.protected) {
        console.log(`  [✓] Protegido (429 ou headers de rate limit)`);
      } else {
        console.log(`  [✗] SEM rate limit detectado`);
      }
    }

    const report = generateReport(results);
    console.log("\n── Relatório ──");
    console.log(`  Endpoints: ${report.totalEndpoints}`);
    console.log(`  Protegidos: ${report.protected}`);
    console.log(`  Desprotegidos: ${report.unprotected}`);
    console.log(`  Críticos protegidos: ${report.criticalProtected}/${report.criticalTotal}`);
    console.log(`  Grade: ${report.grade} (${report.score}%)`);

    if (report.issues.length) {
      console.log("\n  Issues:");
      report.issues.forEach(i => console.log(`    - ${i}`));
    }

    console.log("\n[BACKEND-DEV] Rate Limit Test completo");
  })();
}

export { ENDPOINTS_TO_TEST, testRateLimit, generateReport };
// PULL REQUEST END
