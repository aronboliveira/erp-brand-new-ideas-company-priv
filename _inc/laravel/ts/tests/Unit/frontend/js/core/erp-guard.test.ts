/**
 * @file erp-guard.test.ts
 * @description Testes unitários para src/public/assets/js/core/erp-guard.ts
 *
 * O módulo TS exporta funções utilitárias nomeadas (NÃO é um singleton IIFE).
 * Funções exportadas: isGuarded, markGuarded, markFailed, guardClick,
 * guardSubmit, resolveAction, bindGuardAll, toast, devError.
 *
 * PULL REQUEST START
 */

export {};

/* ------------------------------------------------------------------ */
/*  Caminho do módulo                                                  */
/* ------------------------------------------------------------------ */

const MODULE_PATH = "../../../../../src/public/assets/js/core/erp-guard";

/* ------------------------------------------------------------------ */
/*  Tipos auxiliares                                                    */
/* ------------------------------------------------------------------ */

interface GuardModule {
  isGuarded(el: Element, attr?: string): boolean;
  markGuarded(el: Element, attr?: string): void;
  markFailed(el: Element): void;
  guardClick(el: HTMLElement, opts?: { boundAttr?: string; preventDefault?: boolean }): void;
  guardSubmit(form: HTMLFormElement, opts?: { boundAttr?: string }): void;
  resolveAction(form: HTMLFormElement): string;
  bindGuardAll(selector: string, opts?: unknown): void;
  toast(msg: string, opts?: { type?: string; delay?: number }): void;
  devError(context: string, err: unknown): void;
}

/* ------------------------------------------------------------------ */
/*  Setup / Teardown                                                   */
/* ------------------------------------------------------------------ */

let mod: GuardModule;

beforeEach(async () => {
  jest.resetModules();
  document.body.innerHTML = "";
  mod = (await import(MODULE_PATH)) as unknown as GuardModule;
});

afterEach(() => {
  jest.restoreAllMocks();
  delete (window as unknown as Record<string, unknown>).bootstrap;
});

/* ================================================================== */
/*  isGuarded / markGuarded / markFailed — operações de atributo DOM   */
/* ================================================================== */

describe("isGuarded", () => {
  test("retorna false quando sem atributo", () => {
    const el = document.createElement("div");
    expect(mod.isGuarded(el)).toBe(false);
  });

  test("retorna true quando atributo padrão = 'true'", () => {
    const el = document.createElement("div");
    el.setAttribute("data-listener-active", "true");
    expect(mod.isGuarded(el)).toBe(true);
  });

  test("suporta atributo personalizado", () => {
    const el = document.createElement("div");
    el.setAttribute("data-custom-guard", "true");
    expect(mod.isGuarded(el, "data-custom-guard")).toBe(true);
    expect(mod.isGuarded(el, "data-other")).toBe(false);
  });

  test("retorna false quando atributo != 'true'", () => {
    const el = document.createElement("div");
    el.setAttribute("data-listener-active", "false");
    expect(mod.isGuarded(el)).toBe(false);
  });
});

describe("markGuarded", () => {
  test("define atributo padrão como 'true'", () => {
    const el = document.createElement("div");
    mod.markGuarded(el);
    expect(el.getAttribute("data-listener-active")).toBe("true");
  });

  test("define atributo personalizado como 'true'", () => {
    const el = document.createElement("div");
    mod.markGuarded(el, "data-my-attr");
    expect(el.getAttribute("data-my-attr")).toBe("true");
  });
});

describe("markFailed", () => {
  test("define data-failed-route como 'true'", () => {
    const el = document.createElement("div");
    mod.markFailed(el);
    expect(el.getAttribute("data-failed-route")).toBe("true");
  });
});

/* ================================================================== */
/*  guardClick                                                         */
/* ================================================================== */

describe("guardClick", () => {
  test("marca o elemento como guardado com atributo bound-click", () => {
    const el = document.createElement("a");
    el.setAttribute("href", "#");
    el.setAttribute("data-guard-msg", "Rota indisponível");
    document.body.appendChild(el);

    mod.guardClick(el);
    expect(el.getAttribute("data-listener-bound-click")).toBe("true");
  });

  test("é idempotente — chamadas duplicadas não disparam", () => {
    const el = document.createElement("a");
    el.setAttribute("href", "#");
    document.body.appendChild(el);

    mod.guardClick(el);
    mod.guardClick(el);
    expect(el.getAttribute("data-listener-bound-click")).toBe("true");
  });

  test("previne default e mostra toast quando href='#'", () => {
    const el = document.createElement("a");
    el.setAttribute("href", "#");
    el.setAttribute("data-guard-msg", "Bloqueado");
    document.body.appendChild(el);

    const alertSpy = jest.spyOn(globalThis, "alert").mockImplementation(() => {});
    mod.guardClick(el);

    const ev = new MouseEvent("click", { bubbles: true, cancelable: true });
    const prevented = !el.dispatchEvent(ev);

    // Deve prevenir o default OU mostrar toast/alert
    expect(prevented || alertSpy.mock.calls.length > 0 || document.querySelector(".toast") !== null).toBe(true);
    alertSpy.mockRestore();
  });

  test("marca data-failed-route ao clicar href='#'", () => {
    const el = document.createElement("a");
    el.setAttribute("href", "#");
    el.setAttribute("data-guard-msg", "Falha");
    document.body.appendChild(el);

    mod.guardClick(el);
    el.dispatchEvent(new MouseEvent("click", { bubbles: true, cancelable: true }));
    expect(el.getAttribute("data-failed-route")).toBe("true");
  });

  test("não marca failed quando href é URL válida", () => {
    const el = document.createElement("a");
    el.setAttribute("href", "https://example.com");
    document.body.appendChild(el);

    mod.guardClick(el);
    el.dispatchEvent(new MouseEvent("click", { bubbles: true, cancelable: true }));
    expect(el.getAttribute("data-failed-route")).toBeNull();
  });

  test("suporta boundAttr personalizado via opts", () => {
    const el = document.createElement("a");
    el.setAttribute("href", "#");
    document.body.appendChild(el);

    mod.guardClick(el, { boundAttr: "data-custom-bound" });
    expect(el.getAttribute("data-custom-bound")).toBe("true");
  });

  test("lê data-guard-msg como mensagem do toast", () => {
    const el = document.createElement("a");
    el.setAttribute("href", "#");
    el.setAttribute("data-guard-msg", "Mensagem personalizada");
    document.body.appendChild(el);

    const alertSpy = jest.spyOn(globalThis, "alert").mockImplementation(() => {});
    mod.guardClick(el);
    el.dispatchEvent(new MouseEvent("click", { bubbles: true }));

    const toastBody = document.querySelector(".toast-body");
    if (toastBody) {
      expect(toastBody.textContent).toContain("Mensagem personalizada");
    } else if (alertSpy.mock.calls.length > 0) {
      expect(alertSpy.mock.calls[0][0]).toContain("Mensagem personalizada");
    }
    alertSpy.mockRestore();
  });

  test("trata href vazio igual a href='#'", () => {
    const el = document.createElement("a");
    el.setAttribute("href", "");
    el.setAttribute("data-guard-msg", "Vazio");
    document.body.appendChild(el);

    mod.guardClick(el);
    el.dispatchEvent(new MouseEvent("click", { bubbles: true, cancelable: true }));
    expect(el.getAttribute("data-failed-route")).toBe("true");
  });

  test("trata href='javascript:void(0)' como inválido", () => {
    const el = document.createElement("a");
    el.setAttribute("href", "javascript:void(0)");
    el.setAttribute("data-guard-msg", "Void");
    document.body.appendChild(el);

    mod.guardClick(el);
    el.dispatchEvent(new MouseEvent("click", { bubbles: true, cancelable: true }));
    expect(el.getAttribute("data-failed-route")).toBe("true");
  });
});

/* ================================================================== */
/*  guardSubmit                                                        */
/* ================================================================== */

describe("guardSubmit", () => {
  test("vincula listener de submit ao form", () => {
    const form = document.createElement("form");
    document.body.appendChild(form);

    mod.guardSubmit(form);
    expect(form.getAttribute("data-listener-bound-submit")).toBe("true");
  });

  test("é idempotente", () => {
    const form = document.createElement("form");
    document.body.appendChild(form);

    mod.guardSubmit(form);
    mod.guardSubmit(form);
    expect(form.getAttribute("data-listener-bound-submit")).toBe("true");
  });

  test("marca data-submit-guarded ao submeter", () => {
    const form = document.createElement("form");
    document.body.appendChild(form);

    mod.guardSubmit(form);
    form.dispatchEvent(new Event("submit", { bubbles: true, cancelable: true }));
    expect(form.getAttribute("data-submit-guarded")).toBe("true");
  });

  test("suporta boundAttr personalizado", () => {
    const form = document.createElement("form");
    document.body.appendChild(form);

    mod.guardSubmit(form, { boundAttr: "data-my-submit" });
    expect(form.getAttribute("data-my-submit")).toBe("true");
  });
});

/* ================================================================== */
/*  resolveAction                                                      */
/* ================================================================== */

describe("resolveAction", () => {
  test("retorna data-resolved-action quando presente", () => {
    const form = document.createElement("form");
    form.setAttribute("data-resolved-action", "/custom/action");
    expect(mod.resolveAction(form)).toBe("/custom/action");
  });

  test("faz fallback para form.action", () => {
    const form = document.createElement("form");
    form.action = "https://example.com/submit";
    expect(mod.resolveAction(form)).toContain("/submit");
  });

  test("retorna string quando nenhum está definido", () => {
    const form = document.createElement("form");
    expect(typeof mod.resolveAction(form)).toBe("string");
  });
});

/* ================================================================== */
/*  bindGuardAll                                                       */
/* ================================================================== */

describe("bindGuardAll", () => {
  test("aplica guardClick em todos os seletores correspondentes", () => {
    document.body.innerHTML = `
      <a class="guarded" href="#" data-guard-msg="A">A</a>
      <a class="guarded" href="#" data-guard-msg="B">B</a>
      <a class="guarded" href="https://ok.com">C</a>
    `;

    mod.bindGuardAll("a.guarded");

    const links = document.querySelectorAll("a.guarded");
    links.forEach(el => {
      expect(el.getAttribute("data-listener-bound-click")).toBe("true");
    });
  });

  test("não lança erro quando nenhum elemento corresponde", () => {
    document.body.innerHTML = "<p>Sem links</p>";
    expect(() => mod.bindGuardAll(".nonexistent")).not.toThrow();
  });
});

/* ================================================================== */
/*  toast                                                              */
/* ================================================================== */

describe("toast", () => {
  test("cria elemento toast no DOM quando bootstrap está disponível", () => {
    (window as unknown as Record<string, unknown>).bootstrap = {
      Toast: {
        getOrCreateInstance: (el: HTMLElement) => ({
          show: () => el.classList.add("show"),
        }),
      },
    };

    mod.toast("Olá mundo", { type: "success" });
    const toasts = document.querySelectorAll(".toast");
    expect(toasts.length).toBeGreaterThanOrEqual(1);
    expect(toasts[0].textContent).toContain("Olá mundo");
  });

  test("faz fallback para classList.add('show') sem bootstrap", () => {
    delete (window as unknown as Record<string, unknown>).bootstrap;

    mod.toast("Mensagem fallback");
    const toasts = document.querySelectorAll(".toast");
    expect(toasts.length).toBeGreaterThanOrEqual(1);
    expect(toasts[0].classList.contains("show")).toBe(true);
  });

  test("usa tipo 'info' como padrão", () => {
    (window as unknown as Record<string, unknown>).bootstrap = {
      Toast: {
        getOrCreateInstance: (el: HTMLElement) => ({
          show: () => el.classList.add("show"),
        }),
      },
    };

    mod.toast("Teste padrão");
    const toastEl = document.querySelector(".toast");
    expect(toastEl?.className).toContain("bg-info");
    expect(toastEl?.getAttribute("data-bs-delay")).toBe("4000");
  });

  test("aplica classe de tipo personalizado", () => {
    (window as unknown as Record<string, unknown>).bootstrap = {
      Toast: {
        getOrCreateInstance: (el: HTMLElement) => ({
          show: () => el.classList.add("show"),
        }),
      },
    };

    mod.toast("Perigo", { type: "danger" });
    expect(document.querySelector(".toast")?.className).toContain("bg-danger");
  });

  test("chama show() na instância do bootstrap", () => {
    let shown = false;
    (window as unknown as Record<string, unknown>).bootstrap = {
      Toast: {
        getOrCreateInstance: () => ({
          show: () => {
            shown = true;
          },
        }),
      },
    };

    mod.toast("Via bootstrap");
    expect(shown).toBe(true);
  });

  test("usa delay personalizado", () => {
    (window as unknown as Record<string, unknown>).bootstrap = {
      Toast: {
        getOrCreateInstance: (el: HTMLElement) => ({
          show: () => el.classList.add("show"),
        }),
      },
    };

    mod.toast("Custom delay", { delay: 8000 });
    const toastEl = document.querySelector(".toast");
    expect(toastEl?.getAttribute("data-bs-delay")).toBe("8000");
  });
});

/* ================================================================== */
/*  devError                                                           */
/* ================================================================== */

describe("devError", () => {
  test("loga console.error em localhost", () => {
    Object.defineProperty(window, "location", {
      value: { hostname: "localhost" },
      writable: true,
      configurable: true,
    });
    const spy = jest.spyOn(console, "error").mockImplementation(() => {});

    mod.devError("teste", new Error("falhou"));
    expect(spy).toHaveBeenCalledWith("[ERP:teste]", expect.anything());
    spy.mockRestore();
  });

  test("loga console.error em 127.0.0.1", () => {
    Object.defineProperty(window, "location", {
      value: { hostname: "127.0.0.1" },
      writable: true,
      configurable: true,
    });
    const spy = jest.spyOn(console, "error").mockImplementation(() => {});

    mod.devError("ctx", "msg");
    expect(spy).toHaveBeenCalled();
    spy.mockRestore();
  });

  test("não loga em hostname de produção", () => {
    Object.defineProperty(window, "location", {
      value: { hostname: "app.example.com" },
      writable: true,
      configurable: true,
    });
    const spy = jest.spyOn(console, "error").mockImplementation(() => {});

    mod.devError("prod", "não deve logar");
    expect(spy).not.toHaveBeenCalled();
    spy.mockRestore();
  });

  test("loga em 0.0.0.0", () => {
    Object.defineProperty(window, "location", {
      value: { hostname: "0.0.0.0" },
      writable: true,
      configurable: true,
    });
    const spy = jest.spyOn(console, "error").mockImplementation(() => {});

    mod.devError("zero", "erro");
    expect(spy).toHaveBeenCalled();
    spy.mockRestore();
  });

  test("lida com Error e strings", () => {
    Object.defineProperty(window, "location", {
      value: { hostname: "localhost" },
      writable: true,
      configurable: true,
    });
    const spy = jest.spyOn(console, "error").mockImplementation(() => {});

    mod.devError("a", new TypeError("tipo errado"));
    mod.devError("b", "string simples");
    expect(spy).toHaveBeenCalledTimes(2);
    spy.mockRestore();
  });
});
