/**
 * Simple HTTP server for serving test harness pages and compiled dist/ files
 *
 * Usage: node serve-harness.cjs [port]
 * Default port: 3333
 *
 * Serves:
 *   /harness/*  -> ts/tests/harness/*
 *   /dist/*     -> ts/dist/*
 */

const http = require("http");
const fs = require("fs");
const path = require("path");
const { URL } = require("url");

const PORT = parseInt(process.argv[2] || "3333", 10);
const BASE_DIR = __dirname;

const MIME_TYPES = {
  ".html": "text/html; charset=utf-8",
  ".js": "application/javascript; charset=utf-8",
  ".mjs": "application/javascript; charset=utf-8",
  ".css": "text/css; charset=utf-8",
  ".json": "application/json; charset=utf-8",
  ".map": "application/json; charset=utf-8",
  ".ts": "text/typescript; charset=utf-8",
};

/**
 * Resolve URL path to filesystem path
 */
function resolvePath(urlPath) {
  // Normalize and prevent directory traversal
  const normalized = path.normalize(urlPath).replace(/^(\.\.[\/\\])+/, "");

  if (normalized.startsWith("/harness/") || normalized === "/harness") {
    // Serve from ts/tests/harness/
    return path.join(BASE_DIR, "harness", normalized.slice(9));
  }

  if (normalized.startsWith("/dist/") || normalized === "/dist") {
    // Serve from ts/dist/
    return path.join(BASE_DIR, "..", "dist", normalized.slice(5));
  }

  // Default: serve harness index
  if (normalized === "/" || normalized === "") {
    return path.join(BASE_DIR, "harness", "index.html");
  }

  return null;
}

/**
 * HTTP request handler
 */
function handleRequest(req, res) {
  const url = new URL(req.url, `http://localhost:${PORT}`);
  const fsPath = resolvePath(url.pathname);

  if (!fsPath) {
    res.writeHead(404, { "Content-Type": "text/plain" });
    res.end("Not Found: " + url.pathname);
    return;
  }

  fs.stat(fsPath, (err, stats) => {
    if (err) {
      res.writeHead(404, { "Content-Type": "text/plain" });
      res.end("Not Found: " + url.pathname);
      return;
    }

    // If directory, look for index.html
    if (stats.isDirectory()) {
      const indexPath = path.join(fsPath, "index.html");
      fs.readFile(indexPath, (err, data) => {
        if (err) {
          // Generate directory listing
          fs.readdir(fsPath, (err, files) => {
            if (err) {
              res.writeHead(500, { "Content-Type": "text/plain" });
              res.end("Error reading directory");
              return;
            }
            res.writeHead(200, { "Content-Type": "text/html" });
            res.end(`<!DOCTYPE html>
<html><head><title>Directory: ${url.pathname}</title></head>
<body>
<h1>Directory: ${url.pathname}</h1>
<ul>
${files.map(f => `<li><a href="${url.pathname}/${f}">${f}</a></li>`).join("\n")}
</ul>
</body></html>`);
          });
          return;
        }
        res.writeHead(200, { "Content-Type": "text/html; charset=utf-8" });
        res.end(data);
      });
      return;
    }

    // Serve file
    const ext = path.extname(fsPath).toLowerCase();
    const contentType = MIME_TYPES[ext] || "application/octet-stream";

    fs.readFile(fsPath, (err, data) => {
      if (err) {
        res.writeHead(500, { "Content-Type": "text/plain" });
        res.end("Error reading file");
        return;
      }
      res.writeHead(200, {
        "Content-Type": contentType,
        "Cache-Control": "no-cache",
        "Access-Control-Allow-Origin": "*",
      });
      res.end(data);
    });
  });
}

const server = http.createServer(handleRequest);

server.listen(PORT, () => {
  console.log(`\n🧪 Test harness server running at http://localhost:${PORT}`);
  console.log(`\n   Routes:`);
  console.log(`   /harness/         -> Test pages`);
  console.log(`   /dist/            -> Compiled TypeScript output`);
  console.log(`\n   Example test pages:`);
  console.log(
    `   http://localhost:${PORT}/harness/pages/attendances-delete.html`,
  );
  console.log(`\n   Press Ctrl+C to stop\n`);
});
