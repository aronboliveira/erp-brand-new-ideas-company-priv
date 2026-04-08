/**
 * @file route-guard.test.ts
 * @description Testes unitários para src/public/assets/js/core/route-guard.ts
 *
 * route-guard.ts exporta `export {}` (sem exports nomeados).
 * Contém um IIFE que atribui `window.RouteGuard = { ... }` ao carregar.
 * - Se `window.ERPGuard` existe → cria proxy de compatibilidade.
 * - Se `window.ERPGuard` NÃO existe → cria implementação standalone.
 *
 * Testamos ambos os caminhos via import dinâmico para efeitos colaterais.
 *
 * PULL REQUEST START
 */

export {};

/* ------------------------------------------------------------------ */
/*  Caminho do módulo                                                  */
/* ------------------------------------------------------------------ */

const ROUTE_MODULE = "../../../../../src/public/assets/js/core/route-guard";

/* ------------------------------------------------------------------ */
/*  Tipos auxiliares                                                    */
/* ------------------------------------------------------------------ */

interface RouteGuardAPI {
  init(): void;
  showToast(message: string, type?: string): void;
  scheduleInteractiveError(message: string): void;
  getMsg(el: HTMLElement | null, fallbackKey: string): string;
  attachGuard(el: HTMLElement): void;
  guardById(id: string): void;
  guardMultiple(...ids: (string | string[])[]): void;
  guardFormSubmit(formId: string | HTMLFormElement, opts?: { msgKey?: string }): void;
  guardAllInContainer(containerId: string, selector?: string): void;
  guardOnChange(elId: string | HTMLElement, callback?: (e: Event, el: HTMLElement) => void): void;
  csrfToken(): string;
  ajaxPost(url: string, data: unknown, opts?: Record<string, unknown>): Promise<{ ok: boolean; data?: unknown; status?: number; error?: string }>;
  isInvalidUrl(url: string | null | undefined): boolean;
  animations: {
    fadeIn(el: HTMLElement | null, dur?: number): Promise<void>;
    fadeOut(el: HTMLElement | null, dur?: number): Promise<void>;
    slideDown(el: HTMLElement | null, dur?: number): Promise<void>;
    slideUp(el: HTMLElement | null, dur?: number): Promise<void>;
    addAnimation(el: HTMLElement | null, anim: string, dur?: number): Promise<void>;
  };
  logError(context: string, err: unknown): void;
  safeFetch(url: string, opts?: RequestInit): Promise<{ ok: boolean; data?: unknown; status?: number; error?: string }>;
  TOAST_CONTAINER_ID: string;
  DATA_GUARD_MSG: string;
  DATA_SV_LOCALIZED: string;
  DATA_LISTENER_ACTIVE: string;
  DATA_FAILED_ROUTE: string;
}

/* ================================================================== */
/*  PATH A — Proxy mode (window.ERPGuard mockado)                      */
/* ================================================================== */

describe("route-guard.ts — proxy mode (ERPGuard presente)", () => {
  beforeEach(async () => {
    jest.resetModules();
    document.body.innerHTML = "";
    document.documentElement.lang = "en";

    jest.spyOn(console, "info").mockImplementation(() => {});
    jest.spyOn(console, "warn").mockImplementation(() => {});
    jest.spyOn(console, "error").mockImplementation(() => {});

    // Criar mock de ERPGuard ANTES de importar route-guard
    (window as unknown as Record<string, unknown>).ERPGuard = {
      showToast: jest.fn(),
      isInvalidUrl: jest.fn((url: string) => !url || url === "#"),
      getMsg: jest.fn((_key: string) => "mocked message"),
      scheduleInteractiveError: jest.fn(),
      bindClickGuard: jest.fn(),
      bindSubmitGuard: jest.fn(),
      safeFetch: jest.fn().mockResolvedValue({ ok: true }),
      getCsrfToken: jest.fn().mockReturnValue("mock-csrf"),
      ajaxPost: jest.fn().mockResolvedValue({ ok: true }),
    };

    delete (window as unknown as Record<string, unknown>).RouteGuard;
    await import(ROUTE_MODULE);
  });

  afterEach(() => {
    delete (window as unknown as Record<string, unknown>).ERPGuard;
    delete (window as unknown as Record<string, unknown>).RouteGuard;
    delete (window as unknown as Record<string, unknown>).bootstrap;
    jest.restoreAllMocks();
  });

  test("window.RouteGuard é definido pelo IIFE", () => {
    expect((window as unknown as { RouteGuard?: RouteGuardAPI }).RouteGuard).toBeDefined();
  });

  test("showToast delega para ERPGuard.showToast", () => {
    const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
    const eg = (window as unknown as { ERPGuard: { showToast: jest.Mock } }).ERPGuard;

    rg.showToast("hello", "success");
    expect(eg.showToast).toHaveBeenCalledWith("hello", "success");
  });

  test("isInvalidUrl delega para ERPGuard.isInvalidUrl", () => {
    const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
    expect(rg.isInvalidUrl("")).toBe(true);
    expect(rg.isInvalidUrl("#")).toBe(true);
  });

  test("getMsg delega para ERPGuard.getMsg", () => {
    const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
    const result = rg.getMsg(null, "error");
    expect(typeof result).toBe("string");
  });

  test("scheduleInteractiveError delega para ERPGuard", () => {
    const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
    const eg = (window as unknown as { ERPGuard: { scheduleInteractiveError: jest.Mock } }).ERPGuard;

    rg.scheduleInteractiveError("err msg");
    expect(eg.scheduleInteractiveError).toHaveBeenCalledWith("err msg");
  });

  test("init() é no-op e não lança", () => {
    const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
    expect(() => rg.init()).not.toThrow();
  });

  test("animations são funções no-op que retornam Promise", () => {
    const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
    expect(rg.animations.fadeIn(null)).toBeInstanceOf(Promise);
    expect(rg.animations.fadeOut(null)).toBeInstanceOf(Promise);
    expect(rg.animations.slideDown(null)).toBeInstanceOf(Promise);
    expect(rg.animations.slideUp(null)).toBeInstanceOf(Promise);
    expect(rg.animations.addAnimation(null, "bounce")).toBeInstanceOf(Promise);
  });

  test("expõe constantes", () => {
    const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
    expect(rg.TOAST_CONTAINER_ID).toBe("erp-toast-container");
    expect(rg.DATA_GUARD_MSG).toBe("data-guard-msg");
    expect(rg.DATA_SV_LOCALIZED).toBe("data-sv-localized");
    expect(rg.DATA_LISTENER_ACTIVE).toBe("data-listener-active");
    expect(rg.DATA_FAILED_ROUTE).toBe("data-failed-route");
  });
});

/* ================================================================== */
/*  PATH B — Standalone mode (sem ERPGuard)                            */
/* ================================================================== */

describe("route-guard.ts — standalone mode (sem ERPGuard)", () => {
  beforeEach(async () => {
    jest.resetModules();
    document.body.innerHTML = '<meta name="csrf-token" content="test-token-456">';
    document.documentElement.lang = "en";

    jest.spyOn(console, "info").mockImplementation(() => {});
    jest.spyOn(console, "warn").mockImplementation(() => {});
    jest.spyOn(console, "error").mockImplementation(() => {});

    delete (window as unknown as Record<string, unknown>).ERPGuard;
    delete (window as unknown as Record<string, unknown>).RouteGuard;

    (window as unknown as Record<string, unknown>).bootstrap = {
      Toast: class Toast {
        show() {}
        hide() {}
        constructor(_el: HTMLElement, _opts: Record<string, unknown>) {}
      },
    };

    await import(ROUTE_MODULE);
  });

  afterEach(() => {
    delete (window as unknown as Record<string, unknown>).ERPGuard;
    delete (window as unknown as Record<string, unknown>).RouteGuard;
    delete (window as unknown as Record<string, unknown>).bootstrap;
    jest.restoreAllMocks();
  });

  test("window.RouteGuard é definido", () => {
    expect((window as unknown as { RouteGuard?: RouteGuardAPI }).RouteGuard).toBeDefined();
  });

  /* ---- isInvalidUrl (standalone) ---- */

  describe("isInvalidUrl (standalone)", () => {
    test("string vazia / '#' / null / undefined → true", () => {
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      expect(rg.isInvalidUrl("")).toBe(true);
      expect(rg.isInvalidUrl("#")).toBe(true);
      expect(rg.isInvalidUrl(null)).toBe(true);
      expect(rg.isInvalidUrl(undefined)).toBe(true);
    });

    test("URL válida → false", () => {
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      expect(rg.isInvalidUrl("https://example.com")).toBe(false);
      expect(rg.isInvalidUrl("/dashboard")).toBe(false);
    });
  });

  /* ---- showToast (standalone) ---- */

  describe("showToast (standalone)", () => {
    test("cria toast no DOM quando bootstrap está disponível", () => {
      document.head.innerHTML = '<link rel="stylesheet" href="/css/bootstrap.min.css">';
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;

      rg.showToast("Alert!", "error");
      const container = document.getElementById("np-toast-container");
      expect(container).not.toBeNull();
      const toasts = container!.querySelectorAll(".toast");
      expect(toasts.length).toBeGreaterThanOrEqual(1);
    });

    test("faz fallback para alert quando bootstrap não tem Toast", () => {
      delete (window as unknown as Record<string, unknown>).bootstrap;
      const alertSpy = jest.spyOn(globalThis, "alert").mockImplementation(() => {});
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;

      rg.showToast("fallback");
      expect(alertSpy).toHaveBeenCalledWith("fallback");
      alertSpy.mockRestore();
    });
  });

  /* ---- csrfToken ---- */

  test("csrfToken lê da meta tag", () => {
    const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
    expect(rg.csrfToken()).toBe("test-token-456");
  });

  /* ---- safeFetch ---- */

  describe("safeFetch (standalone)", () => {
    test("rejeita URLs inválidas", async () => {
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      const result = await rg.safeFetch("");
      expect(result.ok).toBe(false);
      expect(result.error).toBe("Invalid URL");
    });

    test("rejeita '#' como URL", async () => {
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      const result = await rg.safeFetch("#");
      expect(result.ok).toBe(false);
    });
  });

  /* ---- guardById ---- */

  describe("guardById (standalone)", () => {
    test("vincula listener ao elemento", () => {
      document.body.insertAdjacentHTML("beforeend", '<a id="test-link" href="#">Link</a>');
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;

      rg.guardById("test-link");
      const el = document.getElementById("test-link")!;
      expect(el.getAttribute("data-listener-active")).toBe("true");
    });

    test("é no-op quando elemento não existe", () => {
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      expect(() => rg.guardById("nonexistent")).not.toThrow();
    });
  });

  /* ---- guardMultiple ---- */

  test("guardMultiple protege múltiplos elementos", () => {
    document.body.insertAdjacentHTML("beforeend", '<a id="lnk1" href="#">L1</a><a id="lnk2" href="#">L2</a>');
    const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;

    rg.guardMultiple("lnk1", "lnk2");
    expect(document.getElementById("lnk1")!.getAttribute("data-listener-active")).toBe("true");
    expect(document.getElementById("lnk2")!.getAttribute("data-listener-active")).toBe("true");
  });

  /* ---- getMsg (standalone) ---- */

  describe("getMsg (standalone)", () => {
    test("retorna fallback para elemento sem localização", () => {
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      const el = document.createElement("div");
      const msg = rg.getMsg(el, "route_unavailable");
      expect(typeof msg).toBe("string");
    });

    test("lê data-guard-msg de elemento localizado", () => {
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      const el = document.createElement("div");
      el.setAttribute("data-sv-localized", "true");
      el.setAttribute("data-guard-msg", "Custom error");
      expect(rg.getMsg(el, "anything")).toBe("Custom error");
    });

    test("retorna string para elemento null", () => {
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      const msg = rg.getMsg(null, "error");
      expect(typeof msg).toBe("string");
    });
  });

  /* ---- logError ---- */

  test("logError loga em console.error", () => {
    Object.defineProperty(window, "location", {
      value: { hostname: "localhost" },
      writable: true,
      configurable: true,
    });
    const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;

    rg.logError("test-context", new Error("test err"));
    expect(console.error).toHaveBeenCalledWith("[RouteGuard:test-context]", "test err");
  });

  /* ---- animations ---- */

  describe("animations (standalone)", () => {
    test("fadeIn retorna Promise", () => {
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      const el = document.createElement("div");
      expect(rg.animations.fadeIn(el, 10)).toBeInstanceOf(Promise);
    });

    test("fadeOut retorna Promise", () => {
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      const el = document.createElement("div");
      expect(rg.animations.fadeOut(el, 10)).toBeInstanceOf(Promise);
    });

    test("fadeIn com null retorna Promise resolvida", async () => {
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      const result = await rg.animations.fadeIn(null, 10);
      expect(result).toBeUndefined();
    });

    test("slideDown e slideUp retornam Promise", () => {
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      const el = document.createElement("div");
      document.body.appendChild(el);
      expect(rg.animations.slideDown(el, 10)).toBeInstanceOf(Promise);
      expect(rg.animations.slideUp(el, 10)).toBeInstanceOf(Promise);
    });

    test("addAnimation retorna Promise", () => {
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      const el = document.createElement("div");
      expect(rg.animations.addAnimation(el, "bounce", 10)).toBeInstanceOf(Promise);
    });

    test("addAnimation com null retorna Promise resolvida", async () => {
      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      const result = await rg.animations.addAnimation(null, "bounce", 10);
      expect(result).toBeUndefined();
    });
  });

  /* ---- constantes (standalone) ---- */

  test("expõe constantes do standalone", () => {
    const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
    expect(rg.TOAST_CONTAINER_ID).toBe("np-toast-container");
    expect(rg.DATA_GUARD_MSG).toBe("data-guard-msg");
    expect(rg.DATA_SV_LOCALIZED).toBe("data-sv-localized");
  });

  /* ---- guardOnChange ---- */

  describe("guardOnChange (standalone)", () => {
    test("vincula listener de change", () => {
      const input = document.createElement("input");
      input.id = "change-test";
      document.body.appendChild(input);

      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      const cb = jest.fn();
      rg.guardOnChange("change-test", cb);
      expect(input.getAttribute("data-change-listener")).toBe("true");

      input.dispatchEvent(new Event("change"));
      expect(cb).toHaveBeenCalledTimes(1);
    });

    test("não duplica listener em chamadas repetidas", () => {
      const input = document.createElement("input");
      input.id = "change-test-2";
      document.body.appendChild(input);

      const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;
      const cb = jest.fn();
      rg.guardOnChange("change-test-2", cb);
      rg.guardOnChange("change-test-2", cb);

      input.dispatchEvent(new Event("change"));
      expect(cb).toHaveBeenCalledTimes(1);
    });
  });

  /* ---- guardFormSubmit ---- */

  test("guardFormSubmit vincula listener ao form", () => {
    document.body.insertAdjacentHTML("beforeend", '<form id="testform" action="#"></form>');
    const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;

    rg.guardFormSubmit("testform");
    const form = document.getElementById("testform")!;
    expect(form.getAttribute("data-listener-active")).toBe("true");
  });

  /* ---- guardAllInContainer ---- */

  test("guardAllInContainer protege elementos filhos", () => {
    document.body.innerHTML = `
      <div id="container">
        <a href="#" data-route-guard>Link 1</a>
        <a href="#" data-sv-localized="true">Link 2</a>
      </div>
    `;
    const rg = (window as unknown as { RouteGuard: RouteGuardAPI }).RouteGuard;

    rg.guardAllInContainer("container");
    const links = document.querySelectorAll("#container a");
    links.forEach(el => {
      expect(el.getAttribute("data-listener-active")).toBe("true");
    });
  });
});
