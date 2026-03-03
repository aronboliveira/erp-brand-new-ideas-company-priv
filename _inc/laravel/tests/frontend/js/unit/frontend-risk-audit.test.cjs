const {
  collectLineMatches,
  getRouteScriptFiles,
} = require("../helpers/mock-page-audit.cjs");

describe("First-party frontend security risk audit", () => {
  const routeScripts = getRouteScriptFiles();

  test("route scripts avoid eval and new Function", () => {
    const offenders = collectLineMatches(routeScripts, line => {
      const trimmed = line.trim();
      if (
        trimmed.startsWith("//") ||
        trimmed.startsWith("*") ||
        trimmed.startsWith("/*")
      )
        return false;
      return /\b(?:eval|new Function)\s*\(/.test(line);
    });

    expect(offenders).toEqual([]);
  });

  test("route scripts avoid document.write", () => {
    const offenders = collectLineMatches(routeScripts, line =>
      /document\.write\s*\(/.test(line),
    );

    expect(offenders).toEqual([]);
  });

  test("route scripts avoid direct innerHTML-style injection from message or data payloads", () => {
    const offenders = collectLineMatches(routeScripts, line => {
      const usesHtmlSink =
        /\.innerHTML\s*=/.test(line) || /insertAdjacentHTML\s*\(/.test(line);
      const usesDynamicPayload =
        /\b(?:msg|message|text|html|data|response|result)\b/.test(line);

      return usesHtmlSink && usesDynamicPayload;
    });

    expect(offenders).toEqual([]);
  });
});
