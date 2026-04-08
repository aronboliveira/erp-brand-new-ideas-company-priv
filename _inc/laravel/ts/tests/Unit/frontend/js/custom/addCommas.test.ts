/**
 * Tests for addCommas(num)
 *
 * addCommas() formats a number with commas as thousand-separators,
 * two decimal places, and wraps it with the site currency symbol
 * (controlled by globals site_currency_symbol & site_currency_symbol_position).
 */

import {
  loadCustomJs,
  buildJQueryEnv,
  buildDomSkeleton,
} from "../helpers/setup";

beforeEach(() => {
  document.body.innerHTML = "";
  document.head.innerHTML = "";
  buildJQueryEnv();
  buildDomSkeleton();
  loadCustomJs();
});

describe("addCommas", () => {
  test("is defined as a global function", () => {
    expect(typeof (globalThis as any).addCommas).toBe("function");
  });

  /* ---- default config: symbol='$', position='pre' ---- */

  test('formats zero as "$0.00"', () => {
    expect((globalThis as any).addCommas(0)).toBe("$0.00");
  });

  test("formats small integer", () => {
    expect((globalThis as any).addCommas(42)).toBe("$42.00");
  });

  test("formats with two decimals", () => {
    expect((globalThis as any).addCommas(9.9)).toBe("$9.90");
  });

  test("rounds to two decimals", () => {
    expect((globalThis as any).addCommas(3.456)).toBe("$3.46");
  });

  test("adds comma for thousands", () => {
    expect((globalThis as any).addCommas(1234)).toBe("$1,234.00");
  });

  test("adds commas for millions", () => {
    expect((globalThis as any).addCommas(1234567.89)).toBe("$1,234,567.89");
  });

  test("handles negative numbers", () => {
    expect((globalThis as any).addCommas(-500)).toBe("$-500.00");
  });

  test("handles string input", () => {
    expect((globalThis as any).addCommas("12345.6")).toBe("$12,345.60");
  });

  test("NaN input produces NaN string", () => {
    const result = (globalThis as any).addCommas("abc");
    expect(result).toContain("NaN");
  });

  /* ---- post-symbol position ---- */

  test("symbol after number when position=post", () => {
    (globalThis as any).site_currency_symbol_position = "post";
    (globalThis as any).site_currency_symbol = "€";
    expect((globalThis as any).addCommas(1000)).toBe("1,000.00€");
    // restore defaults
    (globalThis as any).site_currency_symbol_position = "pre";
    (globalThis as any).site_currency_symbol = "$";
  });

  /* ---- different symbols ---- */

  test("works with BRL symbol", () => {
    (globalThis as any).site_currency_symbol = "R$";
    expect((globalThis as any).addCommas(2500)).toBe("R$2,500.00");
    (globalThis as any).site_currency_symbol = "$";
  });
});
