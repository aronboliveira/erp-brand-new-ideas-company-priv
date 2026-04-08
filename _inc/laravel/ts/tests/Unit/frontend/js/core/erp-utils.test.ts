/**
 * @file erp-utils.test.ts
 * @description Testes unitários para src/public/assets/js/core/erp-utils.ts
 *
 * O módulo TS exporta funções utilitárias nomeadas (NÃO um singleton).
 * Grupos: DOM (qs, qsa, byId, createEl, setAttrs), Translation (t,
 * mergeTranslations, getLang), Type Guards (isNumber, isInt, isObject,
 * isNil, nonNull), Functional (debounce, noop, safeJsonParse),
 * AJAX (getCsrf, postAjax, deleteAjax), PDF (saveAsPDF, printArea).
 *
 * PULL REQUEST START
 */

export {};

/* ------------------------------------------------------------------ */
/*  Caminho do módulo                                                  */
/* ------------------------------------------------------------------ */

const MODULE_PATH = "../../../../../src/public/assets/js/core/erp-utils";

/* ------------------------------------------------------------------ */
/*  Tipos auxiliares                                                    */
/* ------------------------------------------------------------------ */

interface UtilsModule {
  qs<T extends Element>(sel: string, root?: ParentNode): T | null;
  qsa<T extends Element>(sel: string, root?: ParentNode): T[];
  byId<T extends HTMLElement>(id: string): T | null;
  createEl<K extends keyof HTMLElementTagNameMap>(tag: K, attrs?: Partial<HTMLElementTagNameMap[K]>): HTMLElementTagNameMap[K];
  setAttrs(el: Element, map: Record<string, string>): void;
  t(namespace: string, key: string, lang?: string): string;
  mergeTranslations(map: Record<string, Record<string, string>>): void;
  getLang(): string;
  isNumber(v: unknown): boolean;
  isInt(v: unknown): boolean;
  isObject(v: unknown): boolean;
  isNil(v: unknown): boolean;
  nonNull<T>(value: T | null | undefined, msg?: string): T;
  debounce<T extends (...args: unknown[]) => void>(fn: T, ms: number): (...args: unknown[]) => void;
  noop(): void;
  safeJsonParse<T>(str: string): T | null;
  getCsrf(): string;
  postAjax<T>(url: string, data: Record<string, unknown>): Promise<T>;
  deleteAjax<T>(url: string, data?: Record<string, unknown>): Promise<T>;
  saveAsPDF(el: HTMLElement, opts?: Record<string, unknown>): void;
  printArea(areaId: string): void;
}

/* ------------------------------------------------------------------ */
/*  Setup / Teardown                                                   */
/* ------------------------------------------------------------------ */

let mod: UtilsModule;

beforeEach(async () => {
  jest.resetModules();
  document.body.innerHTML = "";
  document.head.innerHTML = "";
  delete (window as unknown as Record<string, unknown>).translations;
  delete (window as unknown as Record<string, unknown>).__APP_LANG__;
  mod = (await import(MODULE_PATH)) as unknown as UtilsModule;
});

afterEach(() => {
  jest.restoreAllMocks();
  delete (window as unknown as Record<string, unknown>).translations;
  delete (window as unknown as Record<string, unknown>).__APP_LANG__;
  delete (window as unknown as Record<string, unknown>).html2pdf;
});

/* ================================================================== */
/*  DOM — qs / qsa / byId                                              */
/* ================================================================== */

describe("qs", () => {
  test("retorna o primeiro elemento correspondente", () => {
    document.body.innerHTML = '<div class="a">1</div><div class="a">2</div>';
    const el = mod.qs<HTMLDivElement>(".a");
    expect(el).not.toBeNull();
    expect(el!.textContent).toBe("1");
  });

  test("retorna null quando não encontrado", () => {
    expect(mod.qs(".inexistente")).toBeNull();
  });

  test("aceita root personalizado", () => {
    document.body.innerHTML = '<div id="box"><span class="inner">hi</span></div>';
    const box = document.getElementById("box")!;
    expect(mod.qs<HTMLSpanElement>(".inner", box)).not.toBeNull();
  });
});

describe("qsa", () => {
  test("retorna array com todos os elementos", () => {
    document.body.innerHTML = '<p class="x">a</p><p class="x">b</p><p class="x">c</p>';
    const result = mod.qsa<HTMLParagraphElement>(".x");
    expect(Array.isArray(result)).toBe(true);
    expect(result).toHaveLength(3);
  });

  test("retorna array vazio quando sem correspondência", () => {
    expect(mod.qsa(".nada")).toEqual([]);
  });
});

describe("byId", () => {
  test("retorna elemento por ID", () => {
    document.body.innerHTML = '<input id="field" />';
    expect(mod.byId<HTMLInputElement>("field")).not.toBeNull();
  });

  test("retorna null quando ID não existe", () => {
    expect(mod.byId("fantasma")).toBeNull();
  });
});

/* ================================================================== */
/*  DOM — createEl / setAttrs                                          */
/* ================================================================== */

describe("createEl", () => {
  test("cria elemento com a tag certa", () => {
    const el = mod.createEl("div");
    expect(el.tagName).toBe("DIV");
  });

  test("aplica atributos", () => {
    const el = mod.createEl("input", { type: "text", id: "inp" } as Partial<HTMLInputElement>);
    expect(el.type).toBe("text");
    expect(el.id).toBe("inp");
  });

  test("ignora valores null/undefined nos atributos", () => {
    const el = mod.createEl("div", { id: undefined } as unknown as Partial<HTMLDivElement>);
    expect(el.id).toBe("");
  });
});

describe("setAttrs", () => {
  test("define múltiplos atributos", () => {
    const el = document.createElement("div");
    mod.setAttrs(el, { "data-x": "1", "data-y": "2", role: "alert" });
    expect(el.getAttribute("data-x")).toBe("1");
    expect(el.getAttribute("data-y")).toBe("2");
    expect(el.getAttribute("role")).toBe("alert");
  });
});

/* ================================================================== */
/*  Translation — t / mergeTranslations / getLang                      */
/* ================================================================== */

describe("t", () => {
  test("retorna a chave quando window.translations não existe", () => {
    expect(mod.t("invoices", "total_due")).toBe("total_due");
  });

  test("retorna tradução do namespace", () => {
    (window as unknown as Record<string, unknown>).translations = {
      invoices: { total_due: "Total Devido" },
    };
    expect(mod.t("invoices", "total_due")).toBe("Total Devido");
  });

  test("retorna fallback de chave quando namespace não existe", () => {
    (window as unknown as Record<string, unknown>).translations = {};
    expect(mod.t("missing_ns", "key")).toBe("key");
  });

  test("suporta parâmetro lang para busca por idioma", () => {
    (window as unknown as Record<string, unknown>).translations = {
      pt: { greeting: "Olá" },
      en: { greeting: "Hello" },
    };
    expect(mod.t("pt", "greeting", "pt")).toBe("Olá");
  });
});

describe("mergeTranslations", () => {
  test("cria window.translations quando não existe", () => {
    mod.mergeTranslations({ en: { hello: "Hello" } });
    const w = window as unknown as Record<string, Record<string, Record<string, string>>>;
    expect(w.translations?.en?.hello).toBe("Hello");
  });

  test("faz merge em traduções existentes", () => {
    (window as unknown as Record<string, unknown>).translations = {
      en: { existing: "yes" },
    };
    mod.mergeTranslations({ en: { newKey: "New" }, fr: { bonjour: "Bonjour" } });
    const w = window as unknown as Record<string, Record<string, Record<string, string>>>;
    expect(w.translations?.en?.existing).toBe("yes");
    expect(w.translations?.en?.newKey).toBe("New");
    expect(w.translations?.fr?.bonjour).toBe("Bonjour");
  });
});

describe("getLang", () => {
  test("retorna window.__APP_LANG__ quando definido", () => {
    (window as unknown as Record<string, unknown>).__APP_LANG__ = "pt-br";
    expect(mod.getLang()).toBe("pt-br");
  });

  test("faz fallback para document.documentElement.lang", () => {
    document.documentElement.lang = "fr";
    expect(mod.getLang()).toBe("fr");
  });

  test("faz fallback para meta tag app-locale", () => {
    document.documentElement.lang = "";
    document.head.innerHTML = '<meta name="app-locale" content="de">';
    expect(mod.getLang()).toBe("de");
  });

  test("retorna 'en' como último fallback", () => {
    document.documentElement.lang = "";
    expect(mod.getLang()).toBe("en");
  });
});

/* ================================================================== */
/*  Type Guards — isNumber, isInt, isObject, isNil, nonNull            */
/* ================================================================== */

describe("isNumber", () => {
  test("true para números finitos", () => {
    expect(mod.isNumber(42)).toBe(true);
    expect(mod.isNumber(3.14)).toBe(true);
    expect(mod.isNumber(-0)).toBe(true);
  });

  test("false para NaN e Infinity", () => {
    expect(mod.isNumber(NaN)).toBe(false);
    expect(mod.isNumber(Infinity)).toBe(false);
    expect(mod.isNumber(-Infinity)).toBe(false);
  });

  test("false para não-números", () => {
    expect(mod.isNumber("42")).toBe(false);
    expect(mod.isNumber(null)).toBe(false);
    expect(mod.isNumber(undefined)).toBe(false);
  });
});

describe("isInt", () => {
  test("true para inteiros", () => {
    expect(mod.isInt(0)).toBe(true);
    expect(mod.isInt(42)).toBe(true);
    expect(mod.isInt(-7)).toBe(true);
  });

  test("false para floats", () => {
    expect(mod.isInt(3.14)).toBe(false);
  });

  test("false para não-números", () => {
    expect(mod.isInt("1")).toBe(false);
  });
});

describe("isObject", () => {
  test("true para objetos simples", () => {
    expect(mod.isObject({})).toBe(true);
    expect(mod.isObject({ a: 1 })).toBe(true);
  });

  test("false para arrays e null", () => {
    expect(mod.isObject([])).toBe(false);
    expect(mod.isObject(null)).toBe(false);
  });

  test("false para primitivos", () => {
    expect(mod.isObject(42)).toBe(false);
    expect(mod.isObject("str")).toBe(false);
  });
});

describe("isNil", () => {
  test("true para null e undefined", () => {
    expect(mod.isNil(null)).toBe(true);
    expect(mod.isNil(undefined)).toBe(true);
  });

  test("false para valores definidos", () => {
    expect(mod.isNil(0)).toBe(false);
    expect(mod.isNil("")).toBe(false);
    expect(mod.isNil(false)).toBe(false);
  });
});

describe("nonNull", () => {
  test("retorna valor quando não é null/undefined", () => {
    expect(mod.nonNull(42)).toBe(42);
    expect(mod.nonNull("hello")).toBe("hello");
    expect(mod.nonNull(0)).toBe(0);
  });

  test("lança erro para null", () => {
    expect(() => mod.nonNull(null)).toThrow();
  });

  test("lança erro para undefined", () => {
    expect(() => mod.nonNull(undefined)).toThrow();
  });

  test("usa mensagem de erro personalizada", () => {
    expect(() => mod.nonNull(null, "customizado")).toThrow("customizado");
  });
});

/* ================================================================== */
/*  Functional — debounce, noop, safeJsonParse                         */
/* ================================================================== */

describe("debounce", () => {
  beforeEach(() => jest.useFakeTimers());
  afterEach(() => jest.useRealTimers());

  test("atrasa execução", () => {
    const fn = jest.fn();
    const debounced = mod.debounce(fn, 200);
    debounced();
    expect(fn).not.toHaveBeenCalled();
    jest.advanceTimersByTime(200);
    expect(fn).toHaveBeenCalledTimes(1);
  });

  test("reinicia timer em chamadas rápidas", () => {
    const fn = jest.fn();
    const debounced = mod.debounce(fn, 100);
    debounced();
    jest.advanceTimersByTime(50);
    debounced();
    jest.advanceTimersByTime(50);
    expect(fn).not.toHaveBeenCalled();
    jest.advanceTimersByTime(50);
    expect(fn).toHaveBeenCalledTimes(1);
  });

  test("passa argumentos para a função", () => {
    const fn = jest.fn();
    const debounced = mod.debounce(fn, 50);
    debounced("a", "b");
    jest.advanceTimersByTime(50);
    expect(fn).toHaveBeenCalledWith("a", "b");
  });
});

describe("noop", () => {
  test("é uma função que retorna undefined", () => {
    expect(mod.noop()).toBeUndefined();
  });
});

describe("safeJsonParse", () => {
  test("faz parse de JSON válido", () => {
    expect(mod.safeJsonParse('{"a":1}')).toEqual({ a: 1 });
  });

  test("retorna null para JSON inválido", () => {
    expect(mod.safeJsonParse("not json")).toBeNull();
  });

  test("faz parse de arrays", () => {
    expect(mod.safeJsonParse("[1,2,3]")).toEqual([1, 2, 3]);
  });

  test("faz parse de strings JSON", () => {
    expect(mod.safeJsonParse('"hello"')).toBe("hello");
  });

  test("retorna null para string vazia", () => {
    expect(mod.safeJsonParse("")).toBeNull();
  });
});

/* ================================================================== */
/*  AJAX — getCsrf, postAjax, deleteAjax                              */
/* ================================================================== */

describe("getCsrf", () => {
  test("lê token da meta tag csrf-token", () => {
    document.head.innerHTML = '<meta name="csrf-token" content="abc123">';
    expect(mod.getCsrf()).toBe("abc123");
  });

  test("retorna string vazia quando meta tag ausente", () => {
    expect(mod.getCsrf()).toBe("");
  });
});

describe("postAjax", () => {
  beforeEach(() => {
    if (!globalThis.fetch) {
      (globalThis as any).fetch = jest.fn();
    }
  });

  test("envia POST com JSON e cabeçalhos CSRF", async () => {
    document.head.innerHTML = '<meta name="csrf-token" content="tok">';

    const mockResponse = { success: true };
    const fetchSpy = jest.spyOn(globalThis, "fetch").mockResolvedValue({
      ok: true,
      json: () => Promise.resolve(mockResponse),
    } as Response);

    const result = await mod.postAjax("/api/test", { key: "val" });
    expect(result).toEqual(mockResponse);

    expect(fetchSpy).toHaveBeenCalledWith(
      "/api/test",
      expect.objectContaining({
        method: "POST",
        headers: expect.objectContaining({
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": "tok",
        }),
      }),
    );
    fetchSpy.mockRestore();
  });

  test("lança erro para resposta não-ok", async () => {
    jest.spyOn(globalThis, "fetch").mockResolvedValue({
      ok: false,
      status: 500,
    } as Response);

    await expect(mod.postAjax("/api/fail", {})).rejects.toThrow("500");
  });
});

describe("deleteAjax", () => {
  beforeEach(() => {
    if (!globalThis.fetch) {
      (globalThis as any).fetch = jest.fn();
    }
  });

  test("envia DELETE com cabeçalhos CSRF", async () => {
    document.head.innerHTML = '<meta name="csrf-token" content="del-tok">';

    const fetchSpy = jest.spyOn(globalThis, "fetch").mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({ deleted: true }),
    } as Response);

    const result = await mod.deleteAjax("/api/item/1");
    expect(result).toEqual({ deleted: true });

    expect(fetchSpy).toHaveBeenCalledWith(
      "/api/item/1",
      expect.objectContaining({
        method: "DELETE",
      }),
    );
    fetchSpy.mockRestore();
  });

  test("lança erro para resposta não-ok", async () => {
    jest.spyOn(globalThis, "fetch").mockResolvedValue({
      ok: false,
      status: 404,
    } as Response);

    await expect(mod.deleteAjax("/api/item/999")).rejects.toThrow("404");
  });

  test("envia body quando data é fornecido", async () => {
    const fetchSpy = jest.spyOn(globalThis, "fetch").mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({}),
    } as Response);

    await mod.deleteAjax("/api/item/1", { reason: "test" });
    const callArgs = fetchSpy.mock.calls[0][1] as RequestInit;
    expect(callArgs.body).toBe(JSON.stringify({ reason: "test" }));
    fetchSpy.mockRestore();
  });

  test("não envia body quando data é undefined", async () => {
    const fetchSpy = jest.spyOn(globalThis, "fetch").mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({}),
    } as Response);

    await mod.deleteAjax("/api/item/1");
    const callArgs = fetchSpy.mock.calls[0][1] as RequestInit;
    expect(callArgs.body).toBeUndefined();
    fetchSpy.mockRestore();
  });
});

/* ================================================================== */
/*  PDF — saveAsPDF, printArea                                          */
/* ================================================================== */

describe("saveAsPDF", () => {
  test("chama html2pdf quando disponível", () => {
    const saveFn = jest.fn();
    const setFn = jest.fn().mockReturnValue({ save: saveFn });
    const h2pFn = jest.fn().mockReturnValue({ set: setFn });
    (window as unknown as Record<string, unknown>).html2pdf = h2pFn;

    const el = document.createElement("div");
    mod.saveAsPDF(el);
    expect(h2pFn).toHaveBeenCalledWith(el);
    expect(setFn).toHaveBeenCalled();
    expect(saveFn).toHaveBeenCalled();
  });

  test("faz fallback silencioso quando html2pdf não está carregado", () => {
    const warnSpy = jest.spyOn(console, "warn").mockImplementation(() => {});
    const el = document.createElement("div");
    mod.saveAsPDF(el);
    expect(warnSpy).toHaveBeenCalledWith(expect.stringContaining("html2pdf"));
    warnSpy.mockRestore();
  });

  test("faz merge de opções personalizadas com opções padrão", () => {
    const setFn = jest.fn().mockReturnValue({ save: jest.fn() });
    (window as unknown as Record<string, unknown>).html2pdf = jest.fn().mockReturnValue({ set: setFn });

    const el = document.createElement("div");
    mod.saveAsPDF(el, { filename: "custom.pdf" });
    const opts = setFn.mock.calls[0][0] as Record<string, unknown>;
    expect(opts.filename).toBe("custom.pdf");
  });
});

describe("printArea", () => {
  test("abre janela com conteúdo do elemento", () => {
    document.body.innerHTML = '<div id="print-area"><p>Conteúdo</p></div>';

    const mockWin = {
      document: {
        write: jest.fn(),
        close: jest.fn(),
      },
      focus: jest.fn(),
      print: jest.fn(),
      close: jest.fn(),
    };
    jest.spyOn(window, "open").mockReturnValue(mockWin as unknown as Window);

    mod.printArea("print-area");
    expect(mockWin.document.write).toHaveBeenCalledWith(expect.stringContaining("Conteúdo"));
    expect(mockWin.print).toHaveBeenCalled();
  });

  test("faz warn quando elemento não é encontrado", () => {
    const warnSpy = jest.spyOn(console, "warn").mockImplementation(() => {});
    mod.printArea("inexistente");
    expect(warnSpy).toHaveBeenCalledWith(expect.stringContaining("inexistente"));
    warnSpy.mockRestore();
  });

  test("não lança quando window.open retorna null", () => {
    document.body.innerHTML = '<div id="area">X</div>';
    jest.spyOn(window, "open").mockReturnValue(null);
    expect(() => mod.printArea("area")).not.toThrow();
  });
});
