#!/usr/bin/env node
// ▓ Roleplay: White Hat — DOM XSS Probe
// Pentester ético. Verifica vetores de DOM-based XSS.
// PULL REQUEST START
/**
 * Gera payloads DOM XSS para testar contra innerHTML, document.write, eval.
 * @returns {Array<{vector: string, payload: string, sink: string}>}
 */
function generateDomXssPayloads() {
  return [
    { vector: "innerHTML", payload: '<img src=x onerror="alert(1)">', sink: "innerHTML" },
    { vector: "innerHTML-svg", payload: '<svg onload="alert(1)">', sink: "innerHTML" },
    { vector: "document.write", payload: "<script>alert(1)<\/script>", sink: "document.write" },
    { vector: "eval-injection", payload: "';alert(1);//", sink: "eval" },
    { vector: "location.hash", payload: "#<img src=x onerror=alert(1)>", sink: "location.hash" },
    { vector: "template-literal", payload: "${alert(1)}", sink: "template" },
    { vector: "event-handler", payload: "javascript:alert(1)", sink: "href" },
    { vector: "data-uri", payload: "data:text/html,<script>alert(1)</script>", sink: "src" },
  ];
}

/**
 * Testa se um HTML string contém sinks perigosos.
 * @param {string} html
 * @returns {Array<{sink: string, pattern: string, line: number}>}
 */
function auditHtmlForSinks(html) {
  const DANGEROUS_SINKS = [
    { sink: "innerHTML", pattern: /\.innerHTML\s*=/ },
    { sink: "outerHTML", pattern: /\.outerHTML\s*=/ },
    { sink: "document.write", pattern: /document\.write(ln)?\s*\(/ },
    { sink: "eval", pattern: /\beval\s*\(/ },
    { sink: "setTimeout-string", pattern: /setTimeout\s*\(\s*['"`]/ },
    { sink: "setInterval-string", pattern: /setInterval\s*\(\s*['"`]/ },
    { sink: "Function-constructor", pattern: /new\s+Function\s*\(/ },
  ];

  const findings = [];
  const lines = html.split("\n");
  for (let i = 0; i < lines.length; i++) {
    for (const { sink, pattern } of DANGEROUS_SINKS) {
      if (pattern.test(lines[i])) {
        findings.push({ sink, pattern: pattern.source, line: i + 1 });
      }
    }
  }
  return findings;
}

if (process.argv[1]?.endsWith("dom_xss_probe.ts")) {
  console.log("[WHITE-HAT] DOM XSS Probe v1.0");
  console.log("[WHITE-HAT] Gerando payloads DOM XSS...");

  const payloads = generateDomXssPayloads();
  for (const p of payloads) {
    console.log(`  [${p.sink.toUpperCase()}] ${p.vector}: ${p.payload.slice(0, 50)}`);
  }

  console.log(`\n[WHITE-HAT] ${payloads.length} payloads DOM XSS gerados`);

  // Exemplo: auditar um HTML mock
  const mockHtml = `
    <script>
      document.getElementById("out").innerHTML = userInput;
      eval("var x = " + data);
      setTimeout("doStuff(" + param + ")", 100);
    </script>
  `;
  const sinks = auditHtmlForSinks(mockHtml);
  console.log(`\n[WHITE-HAT] Sinks perigosos encontrados: ${sinks.length}`);
  for (const s of sinks) {
    console.log(`  [SINK] ${s.sink} na linha ${s.line}`);
  }
}

export { generateDomXssPayloads, auditHtmlForSinks };
// PULL REQUEST END
