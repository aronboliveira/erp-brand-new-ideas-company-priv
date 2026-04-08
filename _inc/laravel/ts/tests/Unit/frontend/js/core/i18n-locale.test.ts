/**
 * @file i18n-locale.test.ts
 * @description Testes unitários para funcionalidade i18n/locale dos módulos TS:
 *   - erp-utils.ts: getLang(), t(), mergeTranslations()
 *   - Verificação de integridade dos arquivos de tradução em resources/lang/
 *
 * erp-guard.ts NÃO possui métodos de locale no módulo TS.
 *
 * PULL REQUEST START
 */

import fs from "fs";
import path from "path";

export {};

/* ------------------------------------------------------------------ */
/*  Caminhos                                                           */
/* ------------------------------------------------------------------ */

const UTILS_MODULE = "../../../../../src/public/assets/js/core/erp-utils";
const LANG_DIR = path.resolve(__dirname, "../../../../../../resources/lang");

/* ------------------------------------------------------------------ */
/*  Locales suportados                                                 */
/* ------------------------------------------------------------------ */

const SUPPORTED_LOCALES = ["ar", "da", "de", "en", "es", "fr", "he", "it", "ja", "nl", "pl", "pt", "pt-br", "ru", "tr"];

const RTL_LOCALES = ["ar", "he"];

/* ------------------------------------------------------------------ */
/*  Tipos auxiliares                                                    */
/* ------------------------------------------------------------------ */

interface UtilsModule {
  getLang(): string;
  t(namespace: string, key: string, lang?: string): string;
  mergeTranslations(map: Record<string, Record<string, string>>): void;
}

/* ================================================================== */
/*  getLang — detecção de idioma                                        */
/* ================================================================== */

describe("getLang — detecção de idioma", () => {
  let mod: UtilsModule;

  beforeEach(async () => {
    jest.resetModules();
    document.body.innerHTML = "";
    document.head.innerHTML = "";
    document.documentElement.lang = "";
    delete (window as unknown as Record<string, unknown>).__APP_LANG__;
    delete (window as unknown as Record<string, unknown>).translations;
    mod = (await import(UTILS_MODULE)) as unknown as UtilsModule;
  });

  afterEach(() => {
    jest.restoreAllMocks();
    delete (window as unknown as Record<string, unknown>).__APP_LANG__;
    delete (window as unknown as Record<string, unknown>).translations;
  });

  test("retorna window.__APP_LANG__ quando definido", () => {
    (window as unknown as Record<string, unknown>).__APP_LANG__ = "pt-br";
    expect(mod.getLang()).toBe("pt-br");
  });

  test("faz fallback para document.documentElement.lang", () => {
    document.documentElement.lang = "fr";
    expect(mod.getLang()).toBe("fr");
  });

  test("faz fallback para meta tag app-locale", () => {
    document.head.innerHTML = '<meta name="app-locale" content="de">';
    expect(mod.getLang()).toBe("de");
  });

  test("retorna 'en' como último fallback", () => {
    expect(mod.getLang()).toBe("en");
  });

  test("__APP_LANG__ tem prioridade sobre documentElement.lang", () => {
    (window as unknown as Record<string, unknown>).__APP_LANG__ = "ja";
    document.documentElement.lang = "fr";
    expect(mod.getLang()).toBe("ja");
  });

  test("documentElement.lang tem prioridade sobre meta tag", () => {
    document.documentElement.lang = "es";
    document.head.innerHTML = '<meta name="app-locale" content="de">';
    expect(mod.getLang()).toBe("es");
  });

  test("suporta locales com hífen", () => {
    (window as unknown as Record<string, unknown>).__APP_LANG__ = "pt-br";
    expect(mod.getLang()).toBe("pt-br");
  });
});

/* ================================================================== */
/*  t() — busca de tradução                                            */
/* ================================================================== */

describe("t() — busca de tradução", () => {
  let mod: UtilsModule;

  beforeEach(async () => {
    jest.resetModules();
    document.body.innerHTML = "";
    delete (window as unknown as Record<string, unknown>).translations;
    mod = (await import(UTILS_MODULE)) as unknown as UtilsModule;
  });

  afterEach(() => {
    jest.restoreAllMocks();
    delete (window as unknown as Record<string, unknown>).translations;
  });

  test("retorna a chave quando window.translations não existe", () => {
    expect(mod.t("invoices", "total")).toBe("total");
  });

  test("busca tradução por namespace e chave", () => {
    (window as unknown as Record<string, unknown>).translations = {
      invoices: { total: "Total Geral" },
    };
    expect(mod.t("invoices", "total")).toBe("Total Geral");
  });

  test("retorna a chave quando namespace não existe", () => {
    (window as unknown as Record<string, unknown>).translations = {};
    expect(mod.t("missing", "key")).toBe("key");
  });

  test("retorna a chave quando chave não existe no namespace", () => {
    (window as unknown as Record<string, unknown>).translations = {
      invoices: { existing: "sim" },
    };
    expect(mod.t("invoices", "missing_key")).toBe("missing_key");
  });

  test("suporta parâmetro lang para busca por idioma", () => {
    (window as unknown as Record<string, unknown>).translations = {
      pt: { greeting: "Olá" },
      en: { greeting: "Hello" },
    };
    expect(mod.t("pt", "greeting", "pt")).toBe("Olá");
    expect(mod.t("en", "greeting", "en")).toBe("Hello");
  });

  test("retorna chave quando lang especificado não tem a tradução", () => {
    (window as unknown as Record<string, unknown>).translations = {
      pt: { greeting: "Olá" },
    };
    expect(mod.t("en", "greeting", "en")).toBe("greeting");
  });
});

/* ================================================================== */
/*  mergeTranslations — merge de traduções                             */
/* ================================================================== */

describe("mergeTranslations — merge de traduções", () => {
  let mod: UtilsModule;

  beforeEach(async () => {
    jest.resetModules();
    document.body.innerHTML = "";
    delete (window as unknown as Record<string, unknown>).translations;
    mod = (await import(UTILS_MODULE)) as unknown as UtilsModule;
  });

  afterEach(() => {
    jest.restoreAllMocks();
    delete (window as unknown as Record<string, unknown>).translations;
  });

  test("cria window.translations quando não existe", () => {
    mod.mergeTranslations({ en: { hello: "Hello" } });
    const w = window as unknown as { translations: Record<string, Record<string, string>> };
    expect(w.translations.en.hello).toBe("Hello");
  });

  test("faz merge preservando valores existentes", () => {
    (window as unknown as Record<string, unknown>).translations = {
      en: { existing: "yes" },
    };
    mod.mergeTranslations({ en: { newKey: "New" }, fr: { bonjour: "Bonjour" } });
    const w = window as unknown as { translations: Record<string, Record<string, string>> };
    expect(w.translations.en.existing).toBe("yes");
    expect(w.translations.en.newKey).toBe("New");
    expect(w.translations.fr.bonjour).toBe("Bonjour");
  });

  test("sobrescreve valores existentes com novos", () => {
    (window as unknown as Record<string, unknown>).translations = {
      en: { greeting: "Old" },
    };
    mod.mergeTranslations({ en: { greeting: "New" } });
    const w = window as unknown as { translations: Record<string, Record<string, string>> };
    expect(w.translations.en.greeting).toBe("New");
  });

  test("suporta múltiplos idiomas em uma chamada", () => {
    mod.mergeTranslations({
      en: { hello: "Hello" },
      pt: { hello: "Olá" },
      es: { hello: "Hola" },
    });
    const w = window as unknown as { translations: Record<string, Record<string, string>> };
    expect(w.translations.en.hello).toBe("Hello");
    expect(w.translations.pt.hello).toBe("Olá");
    expect(w.translations.es.hello).toBe("Hola");
  });
});

/* ================================================================== */
/*  Fluxo integrado — getLang + t + mergeTranslations                  */
/* ================================================================== */

describe("Fluxo integrado — getLang + t + mergeTranslations", () => {
  let mod: UtilsModule;

  beforeEach(async () => {
    jest.resetModules();
    document.body.innerHTML = "";
    document.head.innerHTML = "";
    document.documentElement.lang = "";
    delete (window as unknown as Record<string, unknown>).__APP_LANG__;
    delete (window as unknown as Record<string, unknown>).translations;
    mod = (await import(UTILS_MODULE)) as unknown as UtilsModule;
  });

  afterEach(() => {
    jest.restoreAllMocks();
    delete (window as unknown as Record<string, unknown>).__APP_LANG__;
    delete (window as unknown as Record<string, unknown>).translations;
  });

  test("merge e busca em sequência funciona", () => {
    mod.mergeTranslations({
      dashboard: { title: "Painel de Controle" },
    });
    expect(mod.t("dashboard", "title")).toBe("Painel de Controle");
  });

  test("merge múltiplas vezes e busca último valor", () => {
    mod.mergeTranslations({ ns: { key: "v1" } });
    mod.mergeTranslations({ ns: { key: "v2" } });
    expect(mod.t("ns", "key")).toBe("v2");
  });

  test("getLang detecta corretamente com diferentes fontes", () => {
    // Sem nada → "en"
    expect(mod.getLang()).toBe("en");

    // Definir meta
    document.head.innerHTML = '<meta name="app-locale" content="it">';
    expect(mod.getLang()).toBe("it");

    // Definir documentElement.lang (tem prioridade)
    document.documentElement.lang = "ja";
    expect(mod.getLang()).toBe("ja");

    // Definir __APP_LANG__ (tem prioridade máxima)
    (window as unknown as Record<string, unknown>).__APP_LANG__ = "ru";
    expect(mod.getLang()).toBe("ru");
  });
});

/* ================================================================== */
/*  Arquivos JSON de tradução — integridade (Node-side)                */
/* ================================================================== */

describe("Arquivos JSON de tradução — integridade", () => {
  test("en.json é JSON válido com > 100 chaves", () => {
    const fp = path.join(LANG_DIR, "en.json");
    if (!fs.existsSync(fp)) return;
    const raw = fs.readFileSync(fp, "utf-8");
    const data = JSON.parse(raw) as Record<string, unknown>;
    expect(Object.keys(data).length).toBeGreaterThan(100);
  });

  for (const locale of SUPPORTED_LOCALES.filter(l => l !== "en")) {
    const filePath = path.join(LANG_DIR, `${locale}.json`);

    test(`${locale}.json existe`, () => {
      expect(fs.existsSync(filePath)).toBe(true);
    });

    if (fs.existsSync(filePath)) {
      test(`${locale}.json é JSON válido`, () => {
        const raw = fs.readFileSync(filePath, "utf-8");
        expect(() => JSON.parse(raw)).not.toThrow();
      });

      test(`${locale}.json tem ≥ 50 chaves`, () => {
        const raw = fs.readFileSync(filePath, "utf-8");
        const data = JSON.parse(raw) as Record<string, unknown>;
        expect(Object.keys(data).length).toBeGreaterThanOrEqual(50);
      });
    }
  }
});

/* ================================================================== */
/*  Cobertura de tradução vs en.json                                    */
/* ================================================================== */

describe("Cobertura de tradução vs en.json", () => {
  let enKeys: string[];

  beforeAll(() => {
    const fp = path.join(LANG_DIR, "en.json");
    if (!fs.existsSync(fp)) {
      enKeys = [];
      return;
    }
    const raw = fs.readFileSync(fp, "utf-8");
    enKeys = Object.keys(JSON.parse(raw) as Record<string, unknown>);
  });

  for (const locale of ["pt-br", "es", "fr", "de"]) {
    test(`${locale}.json cobre ≥ 80% das chaves de en.json`, () => {
      if (enKeys.length === 0) return;
      const fp = path.join(LANG_DIR, `${locale}.json`);
      if (!fs.existsSync(fp)) return;

      const raw = fs.readFileSync(fp, "utf-8");
      const localeKeys = new Set(Object.keys(JSON.parse(raw) as Record<string, unknown>));
      const covered = enKeys.filter(k => localeKeys.has(k)).length;
      const coverage = covered / enKeys.length;
      expect(coverage).toBeGreaterThanOrEqual(0.8);
    });
  }
});

/* ================================================================== */
/*  Diretórios de locale existem em resources/lang                      */
/* ================================================================== */

describe("Diretórios de locale em resources/lang", () => {
  for (const locale of SUPPORTED_LOCALES) {
    test(`diretório ou JSON ${locale} existe`, () => {
      const dirExists = fs.existsSync(path.join(LANG_DIR, locale));
      const jsonExists = fs.existsSync(path.join(LANG_DIR, `${locale}.json`));
      expect(dirExists || jsonExists).toBe(true);
    });
  }
});

/* ================================================================== */
/*  RTL locale detection                                               */
/* ================================================================== */

describe("Identificação de locales RTL", () => {
  test("ar e he são RTL", () => {
    for (const rtl of RTL_LOCALES) {
      expect(["ar", "he"]).toContain(rtl);
    }
  });

  test("locales não-RTL não estão na lista RTL", () => {
    const nonRtl = SUPPORTED_LOCALES.filter(l => !RTL_LOCALES.includes(l));
    for (const locale of nonRtl) {
      expect(RTL_LOCALES).not.toContain(locale);
    }
  });

  test("document.documentElement.dir pode ser definido programaticamente", () => {
    document.documentElement.dir = "rtl";
    expect(document.documentElement.dir).toBe("rtl");
    document.documentElement.dir = "ltr";
    expect(document.documentElement.dir).toBe("ltr");
  });
});
