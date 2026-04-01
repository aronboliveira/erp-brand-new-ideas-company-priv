// ▓ Roleplay: QA — Form Fuzzer
// QA tester. Fuzzing automatizado de formulários com inputs maliciosos.
// PULL REQUEST START
// @ts-check
"use strict";

const http = require("http");
const querystring = require("querystring");

const SERVER = process.env.APP_URL || "http://127.0.0.1:8000";

/** Payloads de fuzzing por categoria. */
const FUZZ_PAYLOADS = {
  xss: [
    '<script>alert(1)</script>',
    '"><img src=x onerror=alert(1)>',
    "javascript:alert(1)",
    '<svg/onload=alert(1)>',
  ],
  sqli: [
    "' OR 1=1--",
    "1; DROP TABLE users--",
    "' UNION SELECT null--",
    "admin'--",
  ],
  overflow: [
    "A".repeat(256),
    "A".repeat(1024),
    "A".repeat(65536),
  ],
  special: [
    "",
    " ",
    "\0",
    "\t\n\r",
    "null",
    "undefined",
    "NaN",
    "true",
    "false",
    "-1",
    "0",
    "999999999",
    "1.7976931348623157e+308",
  ],
  format: [
    "%s%s%s%s%s",
    "${7*7}",
    "{{7*7}}",
    "#{7*7}",
    "../../../etc/passwd",
    "..\\..\\..\\windows\\system32",
  ],
};

/**
 * Gera todos os payloads de fuzz.
 * @returns {Array<{category: string, payload: string}>}
 */
function generateFuzzPayloads() {
  const all = [];
  for (const [category, payloads] of Object.entries(FUZZ_PAYLOADS)) {
    for (const payload of payloads) {
      all.push({ category, payload });
    }
  }
  return all;
}

/**
 * Envia um payload via POST para um endpoint.
 * @param {string} url
 * @param {Record<string, string>} fields
 * @returns {Promise<{status: number, body: string}>}
 */
function postForm(url, fields) {
  return new Promise((resolve) => {
    const data = querystring.stringify(fields);
    const parsed = new URL(url);
    const options = {
      hostname: parsed.hostname,
      port: parsed.port || 80,
      path: parsed.pathname,
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "Content-Length": Buffer.byteLength(data),
      },
      timeout: 10000,
    };

    const req = http.request(options, (res) => {
      let body = "";
      res.on("data", (chunk) => (body += chunk));
      res.on("end", () =>
        resolve({ status: res.statusCode || 0, body: body.substring(0, 500) })
      );
    });

    req.on("error", () => resolve({ status: 0, body: "ERROR" }));
    req.on("timeout", () => {
      req.destroy();
      resolve({ status: 0, body: "TIMEOUT" });
    });
    req.write(data);
    req.end();
  });
}

/**
 * Faz fuzzing de um campo em um endpoint.
 * @param {string} endpoint
 * @param {string} fieldName
 * @param {Array<{category: string, payload: string}>} payloads
 */
async function fuzzField(endpoint, fieldName, payloads) {
  const results = [];
  for (const { category, payload } of payloads) {
    const fields = { [fieldName]: payload };
    const res = await postForm(endpoint, fields);
    results.push({
      category,
      payload: payload.substring(0, 50),
      status: res.status,
      reflected: res.body.includes(payload.substring(0, 20)),
      error: res.status >= 500,
    });
  }
  return results;
}

module.exports = { generateFuzzPayloads, postForm, fuzzField, FUZZ_PAYLOADS };

// Execução standalone
if (require.main === module) {
  (async () => {
    const target = process.argv[2] || SERVER;
    console.log("[QA] Form Fuzzer v1.0");
    console.log(`[QA] Alvo: ${target}`);
    console.log("═".repeat(50));

    const payloads = generateFuzzPayloads();
    console.log(`[QA] ${payloads.length} payloads de fuzz preparados`);

    const endpoints = [
      { url: `${target}/login`, field: "email" },
      { url: `${target}/search`, field: "q" },
    ];

    for (const ep of endpoints) {
      console.log(`\n[ENDPOINT] ${ep.url} (campo: ${ep.field})`);
      const results = await fuzzField(ep.url, ep.field, payloads.slice(0, 10));
      for (const r of results) {
        const icon = r.error ? "✗" : r.reflected ? "!" : "✓";
        console.log(
          `  [${icon}] [${r.category}] "${r.payload}" → ${r.status}${r.reflected ? " REFLETIDO" : ""}`
        );
      }
    }

    console.log(`\n${"═".repeat(50)}`);
    console.log("[QA] Fuzzing concluído");
  })();
}
// PULL REQUEST END
