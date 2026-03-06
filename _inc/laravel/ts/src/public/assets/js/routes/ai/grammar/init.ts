/**
 * @fileoverview TypeScript version of public/assets/js/routes/ai/grammar/init.js
 * @generated from original JavaScript - manual review recommended
 * @module init
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
(function (): void {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataBoundInit = "data-bound-grammar-init";
  const dataBoundRegen = "data-bound-grammar-regen";
  const qs = (s, r = document) => r.querySelector(s);
  const ensureToastContainer = (): void => {
    let c = qs("#np-toast-container");
    if (c) {
      return c;
    }
    c = document.createElement("div");
    c.id = "np-toast-container";
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = message => {
    const hasBootstrap =
      (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]')) &&
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain, @typescript-eslint/strict-boolean-expressions
      window.bootstrap &&
      window.bootstrap.Toast;
    if (hasBootstrap) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        t.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(t);
      }
      const body = qs(".toast-body", t);
      if (body) {
        body.textContent = message ?? errFb;
      }
      try {
        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };
  const scheduleInteractiveError = message => {
    const host = document.body;
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!host || host.getAttribute(dataErrGuard) === "true") {
      return;
    }
    host.setAttribute(dataErrGuard, "true");
    const once = (): void => {
      try {
        showErrorNow(message);
      } finally {
        host.removeAttribute(dataErrGuard);
      }
    };
    document.addEventListener("pointerup", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("pointerup", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const getMsg = (el, key) => {
    let msg = errFb;
    if (
      el?.getAttribute?.(dataSvLocalized) === "true" ||
      el?.getAttribute?.(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ?? "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el?.getAttribute?.(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (el && msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const resolveRoute = (el, explicit) => {
    const url = el?.getAttribute?.("data-url") || "";
    const href = el
      ? el.tagName === "FORM"
        ? el.getAttribute("action") ?? ""
        : el.getAttribute("href") ?? ""
      : "";
    if (
      (!explicit || explicit === "#") &&
      (!url || url === "#") &&
      (!href || href === "#")
    ) {
      return null;
    }
    return explicit && explicit !== "#"
      ? explicit
      : url && url !== "#"
      ? url
      : href;
  };
  const initGrammarSeed = (): void => {
    const host = document.body;
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!host || host.getAttribute(dataBoundInit) === "true") {
      return;
    }
    host.setAttribute(dataBoundInit, "true");
    try {
      let summernoteValue = "";
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
      if ($ && $(".grammer_textarea").length > 0) {
        summernoteValue = $(".grammer_textarea").val() ?? "";
      } else {
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        if (!$.fn) {
          try {
            if (
              window.location.hostname === "localhost" ||
              window.location.hostname === "127.0.0.1"
            )
              console.error("jQuery unavailable");
          } catch (_) {}
          scheduleInteractiveError(getMsg(host, "plugin_unavailable"));
          return;
        }
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
        if ($.fn.summernote && $(".summernote-simple").length > 0) {
          try {
            $(".summernote-simple").summernote();
            summernoteValue = $(".summernote-simple").summernote("code");
          } catch (_) {
            summernoteValue = $(".summernote-simple").val() ?? "";
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
        getMsg(document.body, "grammar_init_unavailable")
      );
    }
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const bindRegenerate = (): void => {
    const btn = qs("#regenerate");
    if (!btn) {
      return;
    }
    if (btn.getAttribute(dataBoundRegen) === "true") {
      return;
    }
    btn.setAttribute(dataBoundRegen, "true");
    $(document.body).on("click.grammarRegen", "#regenerate", function (): void {
      try {
        const form = $("#myGrammarForm");
        const formEl = form.get(0);
        const explicit = "{{ route('grammar.response') }}";
        const endpoint = resolveRoute(formEl, explicit);
        if (!endpoint) {
          scheduleInteractiveError(
            // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
            getMsg(formEl || document.body, "generate_unavailable")
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
                '<span class="spinner-grow spinner-grow-sm" role="status"></span>'
              );
            } catch (_) {}
          },
          success: function (data) {
            try {
              $(".response").removeClass("d-none");
              $("#regenerate").text("Re-Generate");
              if (data?.message) {
                // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
                if (window.show_toastr) {
                  window.show_toastr("error", data.message, "error");
                }
                $("#commonModalOver").modal("hide");
              } else {
                $("#ai-description").val(data ?? "");
              }
            } catch (_) {
              scheduleInteractiveError(
                getMsg(document.body, "generate_unavailable")
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
    const mo = new MutationObserver((m, o) => {
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
          const copied = $("#ai-description").val() ?? "";
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
          if ($ && $(".grammer_textarea").length > 0) {
            $(".grammer_textarea").val(copied);
          } else {
            if (
              // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/prefer-optional-chain, @typescript-eslint/no-unnecessary-condition
              $ &&
              // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
              $.fn &&
              // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
              $.fn.summernote &&
              $(".summernote-simple").length > 0
            ) {
              try {
                $(".summernote-simple").summernote("code", copied);
              } catch (_) {
                $(".summernote-simple").val(copied);
              }
            } else {
              scheduleInteractiveError(
                getMsg(document.body, "plugin_unavailable")
              );
            }
          }
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
          if (window.show_toastr) {
            window.show_toastr(
              "success",
              "Result text has been copied successfully",
              "success"
            );
          }
          $("#commonModalOver").modal("hide");
        } catch (_) {
          scheduleInteractiveError(
            getMsg(document.body, "grammar_init_unavailable")
          );
        }
      };
    }
  };
  const init = (): void => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {}
      scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
      return;
    }
    initGrammarSeed();
    bindRegenerate();
    exposeCopy();
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
