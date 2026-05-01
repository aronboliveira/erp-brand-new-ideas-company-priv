#!/usr/bin/env node
const fs = require("node:fs");
const path = require("node:path");
const { spawnSync } = require("node:child_process");

const postmanRoot = path.resolve(__dirname, "..");
const collectionPath = path.join(postmanRoot, "brand-new-ideas-company.collection.json");
const environmentPath = path.join(postmanRoot, "local.environment.json");
const reportsDir = path.join(postmanRoot, "reports");
const localBin = path.join(postmanRoot, "node_modules", ".bin", process.platform === "win32" ? "newman.cmd" : "newman");

const baseUrl = (process.env.POSTMAN_BASE_URL || process.env.APP_URL || "http://127.0.0.1:18081").replace(/\/$/, "");
const apiBase = (process.env.POSTMAN_API_BASE || `${baseUrl}/apis`).replace(/\/$/, "");
const apiEmail = process.env.POSTMAN_API_EMAIL || "";
const apiPassword = process.env.POSTMAN_API_PASSWORD || "";
const haveCreds = Boolean(apiEmail && apiPassword);

fs.mkdirSync(reportsDir, { recursive: true });

const args = [
  "newman",
  "run",
  collectionPath,
  "-e",
  environmentPath,
  "--reporters",
  "cli,json",
  "--reporter-json-export",
  path.join(reportsDir, "newman-report.json"),
  "--env-var",
  `baseUrl=${baseUrl}`,
  "--env-var",
  `apiBase=${apiBase}`,
  "--env-var",
  `apiEmail=${apiEmail}`,
  "--env-var",
  `apiPassword=${apiPassword}`
];

if (!haveCreds) {
  console.error("[postman] POSTMAN_API_EMAIL / POSTMAN_API_PASSWORD not set; running anonymous security folder only.");
  args.push("--folder", "00 Anonymous Security");
}

args.push(...process.argv.slice(2));

const command = fs.existsSync(localBin) ? localBin : "npx";
const commandArgs = fs.existsSync(localBin) ? args.slice(1) : args;
const result = spawnSync(command, commandArgs, { stdio: "inherit" });

if (result.error) {
  console.error(`[postman] Failed to launch Newman: ${result.error.message}`);
  process.exit(1);
}

process.exit(result.status ?? 1);

