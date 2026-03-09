/**
 * @fileoverview TypeScript version of public/assets/js/routes/ai/grammar/init.js
 * @generated from original JavaScript - manual review recommended
 * @module init
 */

import type { GrammarAjaxResponse } from "../../../../../../declarations/routes/ajax-responses.interfaces";

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const $ = window.jQuery!;
  const errFb = "# ERROR",
    dataClientLocalized = "data-client-localized",
    dataGuardMsg = "data-guard-msg",
    dataSvLocalized = "data-sv-localized",
    dataErrGuard = "data-error-guard",
    dataBoundInit = "data-bound-grammar-init",
    dataBoundRegen = "data-bound-grammar-regen";
  const qs = (
    s: string,
    r: Document | Element = document,
  ): HTMLElement | null => r.querySelector(s);
  const ensureToastContainer = (): HTMLElement => {
    const c = qs("#np-toast-container");
    if (c) return c;
    const div = document.createElement("div");
    div.id = "np-toast-container";
    div.setAttribute("aria-live", "polite");
    div.setAttribute("aria-atomic", "true");
    Object.assign(div.style, { position: "fixed", top: "1rem", right: "1rem" });
    document.body.appendChild(div);
    return div;
  };
  const showErrorNow = (message: string): void => {
    const hasBootstrap =
      (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]')) &&
      window.bootstrap.Toast;
    if (hasBootstrap) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          t.setAttribute(k, v);
        t.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(t);
      }
      const body = qs(".toast-body", t);
      if (body) body.textContent = message ?? errFb;
      try {
        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };
  const scheduleInteractiveError = (message: string): void => {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") return;
    host.setAttribute(dataErrGuard, "true");
    const once = (): void => {
      try {
        showErrorNow(message);
      } finally {
        host.removeAttribute(dataErrGuard);
      }
    };
    document.addEventListener("pointerup", once, { once: true });
    const mo = new MutationObserver((_m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("pointerup", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const getMsg = (el: HTMLElement, key: string) => {
    let msg = errFb;
    if (
      el.getAttribute(dataSvLocalized) === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (el && msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const resolveRoute = (
    el: HTMLElement | undefined,
    explicit: string,
  ): string | null => {
    const url = el?.getAttribute("data-url") || "",
      href = el
        ? el.tagName === "FORM"
          ? (el.getAttribute("action") ?? "")
          : (el.getAttribute("href") ?? "")
        : "";
    if (
      (!explicit || explicit === "#") &&
      (!url || url === "#") &&
      (!href || href === "#")
    )
      return null;
    return explicit && explicit !== "#"
      ? explicit
      : url && url !== "#"
        ? url
        : href;
  };
  const initGrammarSeed = (): void => {
    const host = document.body;
    if (!host || host.getAttribute(dataBoundInit) === "true") return;
    host.setAttribute(dataBoundInit, "true");
    try {
      let summernoteValue = "";
      if ($(".grammer_textarea").length > 0) {
        summernoteValue = String($(".grammer_textarea").val() ?? "");
      } else {
        if (!$.fn) {
          try {
            if (
              window.location.hostname === "localhost" ||
              window.location.hostname === "127.0.0.1"
            )
              console.error("jQuery unavailable");
          } catch (_) {
            console.error(`[init] Error:`, _);
          }
          scheduleInteractiveError(getMsg(host, "plugin_unavailable"));
          return;
        }
        if ("summernote" in $.fn && $(".summernote-simple").length > 0) {
          try {
            $(".summernote-simple").summernote();
            summernoteValue = String(
              $(".summernote-simple").summernote("code") ?? "",
            );
          } catch (_) {
            summernoteValue = String($(".summernote-simple").val() ?? "");
          }
        } else {
          scheduleInteractiveError(getMsg(host, "plugin_unavailable"));
        }
      }
      summernoteValue = String(summernoteValue).replace(/<(.|\n)*?>/g, "");
      const desc = $("#description");
      if (desc.length !== 0) {
        desc.text(summernoteValue);
      } else {
        scheduleInteractiveError(getMsg(host, "grammar_init_unavailable"));
      }
    } catch (_) {
      scheduleInteractiveError(
        getMsg(document.body, "grammar_init_unavailable"),
      );
    }
    const mo = new MutationObserver((_m, o) => {
      if (!document.body.contains(host)) o.disconnect();
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const bindRegenerate = (): void => {
    const btn = qs("#regenerate");
    if (!btn) return;
    if (btn.getAttribute(dataBoundRegen) === "true") return;
    btn.setAttribute(dataBoundRegen, "true");
    $(document.body).on("click.grammarRegen", "#regenerate", function (): void {
      try {
        const form = $("#myGrammarForm"),
          formEl = form.get(0) as HTMLElement | undefined,
          explicit = "{{ route('grammar.response') }}",
          endpoint = resolveRoute(formEl, explicit);
        if (!endpoint) {
          scheduleInteractiveError(
            // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
            getMsg(formEl || document.body, "generate_unavailable"),
          );
          return;
        }
        $.ajax({
          type: "post",
          url: endpoint,
          dataType: "json",
          data: form.serialize(),
          cache: false,
          beforeSend: function (): void {
            try {
              $("#regenerate").empty();
              $("#regenerate").append(
                '<span class="spinner-grow spinner-grow-sm" role="status"></span>',
              );
            } catch (_) {
              console.error(`[init] Error:`, _);
            }
          },
          success: function (data: GrammarAjaxResponse) {
            try {
              $(".response").removeClass("d-none");
              $("#regenerate").text("Re-Generate");
              if (data.message) {
                if (window.show_toastr)
                  window.show_toastr("error", data.message, "error");
                (
                  $("#commonModalOver") as JQuery<HTMLElement> & {
                    modal: (cmd: string) => void;
                  }
                ).modal("hide");
              } else {
                $("#ai-description").val(String(data ?? ""));
              }
            } catch (_) {
              scheduleInteractiveError(
                getMsg(document.body, "generate_unavailable"),
              );
            }
          },
          error: function (): void {
            scheduleInteractiveError(getMsg(document.body, "ajax_unavailable"));
          },
        });
      } catch (_) {
        scheduleInteractiveError(getMsg(document.body, "generate_unavailable"));
      }
    });
    const mo = new MutationObserver((_m, o) => {
      if (!document.body.contains(btn)) {
        $(document.body).off(".grammarRegen");
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const exposeCopy = (): void => {
    if (!window.copyGrammerText) {
      window.copyGrammerText = function (): void {
        try {
          const copied = String($("#ai-description").val() ?? "");
          if ($(".grammer_textarea").length > 0) {
            $(".grammer_textarea").val(copied);
          } else {
            if ("summernote" in $.fn && $(".summernote-simple").length > 0) {
              try {
                $(".summernote-simple").summernote("code", copied);
              } catch (_) {
                $(".summernote-simple").val(copied);
              }
            } else {
              scheduleInteractiveError(
                getMsg(document.body, "plugin_unavailable"),
              );
            }
          }
          if (window.show_toastr)
            window.show_toastr(
              "success",
              "Result text has been copied successfully",
              "success",
            );
          (
            $("#commonModalOver") as JQuery<HTMLElement> & {
              modal: (cmd: string) => void;
            }
          ).modal("hide");
        } catch (_) {
          scheduleInteractiveError(
            getMsg(document.body, "grammar_init_unavailable"),
          );
        }
      };
    }
  };
  const init = (): void => {
    if (!$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {
        console.error(`[init] Error:`, _);
      }
      scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
      return;
    }
    initGrammarSeed();
    bindRegenerate();
    exposeCopy();
  };
  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", init, { once: true })
    : init();
})();

export {};
