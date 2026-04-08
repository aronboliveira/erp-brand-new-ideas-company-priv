// ▓ Roleplay: Green Hat — Script Validation Tests (Jest)
// Valida que os scripts do green-hat funcionam e que o sistema
// defende contra as técnicas ingênuas que eles tentam.
// PULL REQUEST START
import { execSync } from "child_process";
import path from "path";

const SCRIPTS = path.resolve(__dirname, "../../../../../Feature/security/roleplay/green-hat");

describe("Green Hat — Script Validation", () => {
  // ─── JS: Cookie Stealer ───────────────────────────────────
  describe("cookie_stealer", () => {
    // eslint-disable-next-line @typescript-eslint/no-require-imports
    const { stealSession, decodeJwt } = require(path.join(SCRIPTS, "js/scripts/cookie_stealer.ts"));

    test("stealSession retorna estrutura correta sem window", () => {
      const result = stealSession(null);
      expect(result).toHaveProperty("cookies");
      expect(result).toHaveProperty("localStorage");
      expect(result).toHaveProperty("sessionStorage");
      expect(result.cookies).toBe("");
    });

    test("decodeJwt decodifica JWT válido", () => {
      // Header: {"alg":"HS256"}, Payload: {"user_id":"1","role":"admin"}
      const token = "eyJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoiMSIsInJvbGUiOiJhZG1pbiJ9.fake";
      const decoded = decodeJwt(token);
      expect(decoded).not.toBeNull();
      expect(decoded.user_id).toBe("1");
      expect(decoded.role).toBe("admin");
    });

    test("decodeJwt retorna null para token inválido", () => {
      expect(decodeJwt("not-a-jwt")).toBeNull();
      expect(decodeJwt("")).toBeNull();
      expect(decodeJwt("a.b")).toBeNull();
    });

    test("script executa sem erros via node", () => {
      const scriptPath = path.join(SCRIPTS, "js/scripts/cookie_stealer.ts");
      const output = execSync(`node --experimental-strip-types "${scriptPath}"`, {
        encoding: "utf-8",
        timeout: 10000,
      });
      expect(output).toContain("[GREEN-HAT]");
      expect(output).toContain("Exfiltração simulada completa");
    });
  });

  // ─── Bash: Brute Login ────────────────────────────────────
  describe("brute_login.sh", () => {
    test("script é executável e produz output estruturado", () => {
      const scriptPath = path.join(SCRIPTS, "bash/scripts/brute_login.sh");
      // Executa com timeout curto — não espera sucesso contra servidor real
      let output;
      try {
        output = execSync(`bash "${scriptPath}"`, {
          encoding: "utf-8",
          timeout: 60000,
          env: { ...process.env, APP_URL: "http://127.0.0.1:8000" },
        });
      } catch (e) {
        output = e.stdout || e.message;
      }
      expect(output).toContain("[GREEN-HAT] Brute Force Login");
      expect(output).toContain("Resumo:");
    });
  });

  // ─── Python: Session Dump ─────────────────────────────────
  describe("session_dump.py", () => {
    test("script executa e produz output estruturado", () => {
      const scriptPath = path.join(SCRIPTS, "py/scripts/session_dump.py");
      let output;
      try {
        output = execSync(`python3 "${scriptPath}" http://127.0.0.1:8000/login`, { encoding: "utf-8", timeout: 15000 });
      } catch (e) {
        output = e.stdout || e.message;
      }
      expect(output).toContain("[GREEN-HAT] Session Dump");
    });
  });
});
// PULL REQUEST END
