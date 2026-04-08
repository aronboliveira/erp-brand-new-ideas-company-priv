// ▓ Roleplay: Green Hat — WASM + PHP Script Tests ▓
// Testes para novos scripts: wasm_hello.cjs, form_spam.php
// PULL REQUEST START
import { execSync } from "child_process";
import path from "path";

const SCRIPTS = path.resolve(__dirname, "../../../../../Feature/security/roleplay/green-hat");

describe("Green Hat — WASM Hello", () => {
  // eslint-disable-next-line @typescript-eslint/no-require-imports
  const { WASM_HELLO, simpleXor } = require(path.join(SCRIPTS, "wasm/scripts/wasm_hello.ts"));

  test("WASM_HELLO é Uint8Array com magic bytes", () => {
    expect(WASM_HELLO).toBeInstanceOf(Uint8Array);
    expect(WASM_HELLO[0]).toBe(0x00);
    expect(WASM_HELLO[1]).toBe(0x61);
    expect(WASM_HELLO[2]).toBe(0x73);
    expect(WASM_HELLO[3]).toBe(0x6d);
  });

  test("simpleXor retorna hex string", () => {
    const result = simpleXor("test");
    expect(typeof result).toBe("string");
    expect(result).toMatch(/^[0-9a-f]+$/);
    expect(result.length).toBe(8); // 4 chars * 2 hex digits
  });

  test("script executa via node", () => {
    const output = execSync(`node --experimental-strip-types "${path.join(SCRIPTS, "wasm/scripts/wasm_hello.ts")}"`, {
      encoding: "utf-8",
      timeout: 10000,
    });
    expect(output).toContain("[GREEN-HAT]");
    expect(output).toContain("WASM Hello");
  });
});

describe("Green Hat — Form Spam (PHP)", () => {
  test("script PHP é executável", () => {
    const scriptPath = path.join(SCRIPTS, "php/scripts/form_spam.php");
    let output;
    try {
      output = execSync(`php "${scriptPath}"`, {
        encoding: "utf-8",
        timeout: 30000,
      });
    } catch (e) {
      output = e.stdout || e.message;
    }
    expect(output).toContain("[GREEN-HAT] Form Spam");
    expect(output).toContain("Resumo");
  });
});
// PULL REQUEST END
