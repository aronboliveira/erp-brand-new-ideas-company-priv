/**
 * @fileoverview TypeScript version of public/assets/js/routes/ai/generate/copy.js
 * @generated from original JavaScript - manual review recommended
 * @module copy
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const $ = window.jQuery!;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const _dataBoundCopy = "data-bound-copy";
  const _dataBoundSel = "data-bound-sel";
  const dataBoundChange = "data-bound-template-change";
  const dataBoundGen = "data-bound-generate";
  const copiedMsgId = "ai-copied-msg";
  const qs = (s: string, r: ParentNode = document): HTMLElement | null =>
    r.querySelector(s);
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const _qsa = (s: string, r = document) =>
    // eslint-disable-next-line @typescript-eslint/no-unsafe-return
    Array.prototype.slice.call(r.querySelectorAll(s) || []);
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const hasBootstrap = () =>
    qs('link[rel="stylesheet"][href*="bootstrap"]') ||
    (qs('link[href*="bootstrap"]') && window.bootstrap.Toast);
  const ensureToastContainer = (): HTMLElement => {
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
  const showErrorNow = (message: string): void=> {
    if (hasBootstrap()) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  t.setAttribute(k, v);
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
  const scheduleInteractiveErrorClick = (message: string): void=> {
    const host = document.body;
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
    document.addEventListener("click", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("click", once);
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
      if (msg !== errFb && el) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const resolveRoute = (
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    el: HTMLElement | null | undefined,
    explicit: string,
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  ) => {
    const url = el?.getAttribute("data-url") || "";
    const href = el
      ? el.tagName === "FORM"
        ? (el.getAttribute("action") ?? "")
        : (el.getAttribute("href") ?? "")
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
  const setCheckedFirstRadio = (): void => {
    const modal = qs("#commonModalOver");
    if (!modal) {
      return;
    }
    const RadioSel = "#commonModalOver input[type='radio']";
    const first = qs(RadioSel) as HTMLInputElement | null;
    if (first) {
      if (!first.checked) {
        first.checked = true;
        $(first).trigger("change");
      // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
      }
    }
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const writeField = (name: string, value: unknown) => {
    if (!name) {
      return false;
    }
    const $in = $('input[name="' + name + '"]');
    if ($in.length > 0) {
      $in.val(String(value ?? ""));
      return true;
    }
    const $ta = $('textarea[name="' + name + '"]');
    if ($ta.length === 0) {
      return false;
    }
    const isSummer =
      $ta.hasClass("summernote-simple") || $ta.hasClass("summernote-simple-2");
    if (isSummer && typeof $ta.summernote === "function") {
      try {
        $ta.summernote("code", String(value ?? ""));
        return true;
      } catch (_) {
        $ta.val(String(value ?? ""));
        return true;
      }
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    }
    $ta.val(String(value ?? ""));
    return true;
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const ensureCopiedLabel = (afterEl: HTMLElement | null) => {
    const el = qs("#" + copiedMsgId);
    if (el) {
      return el;
    }
    const span = document.createElement("span");
    span.id = copiedMsgId;
    span.style.marginLeft = "0.5rem";
    // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
    span.textContent = getMsg(afterEl || document.body, "copied_label");
    (afterEl?.parentNode ?? document.body).insertBefore(
      span,
      // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
      afterEl?.nextSibling || null,
    );
    return span;
  };
  const copyText = (): void => {
    try {
      const selected =
        $('input[name="template_name"]:checked').attr("data-name") ?? "";
      const copied = $("#ai-description").val() ?? "";
      if (selected === "") {
        scheduleInteractiveErrorClick(
          getMsg(document.body, "copy_unavailable"),
        );
        return;
      }
      const ok = writeField(selected, copied);
      if (!ok) {
        scheduleInteractiveErrorClick(
          getMsg(document.body, "copy_unavailable"),
        );
        return;
      }
      const anchor = qs("#ai-description");
      ensureCopiedLabel(anchor);
      if (window.show_toastr) {
        window.show_toastr(
          "success",
          "Result text has been copied successfully",
          "success",
        );
      }
      $("#commonModalOver").modal("hide");
    } catch (_) {
      scheduleInteractiveErrorClick(getMsg(document.body, "copy_unavailable"));
    }
  };
  const copySelectedText = (): void => {
    try {
      const selected =
        $('input[name="template_name"]:checked').attr("data-name") ?? "";
      const selText =
        (window.getSelection && window.getSelection()?.toString()) || "";
      if (selected === "") {
        scheduleInteractiveErrorClick(
          getMsg(document.body, "copy_unavailable"),
        );
        return;
      }
      if (selText === "") {
        scheduleInteractiveErrorClick(
          getMsg(document.body, "selection_unavailable"),
        );
        return;
      }
      const ok = writeField(selected, selText);
      if (!ok) {
        scheduleInteractiveErrorClick(
          getMsg(document.body, "copy_unavailable"),
        );
        return;
      }
      const anchor = qs("#ai-description");
      ensureCopiedLabel(anchor);
      if (window.show_toastr) {
        window.show_toastr(
          "success",
          "Result text has been copied successfully",
          "success",
        );
      }
      $("#commonModalOver").modal("hide");
    } catch (_) {
      scheduleInteractiveErrorClick(getMsg(document.body, "copy_unavailable"));
    }
  };
  const bindTemplateChange = (): void => {
    const root = document.body;
    if (root.getAttribute(dataBoundChange) === "true") {
      return;
    }
    root.setAttribute(dataBoundChange, "true");
    $(document.body).on(
      "change.aiTemplate",
      ".template_name",
      function (): void {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
        const templateId = String($(this).val() ?? "");
        const explicit =
          '{{route("generate.keywords",["__templateId"])}}'.replace(
            "__templateId",
            templateId,
          );
        // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
        const endpoint = resolveRoute(this, explicit);
        if (!endpoint) {
          // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
          scheduleInteractiveErrorClick(getMsg(this, "keywords_unavailable"));
          return;
        }
        $.ajax({
          type: "post",
          url: endpoint,
          dataType: "json",
          data: { _token: "{{ csrf_token() }}", template_id: templateId },
          cache: false,
          success: function (data: { tone?: number; template?: string }) {
            try {
              if (data.tone == 1) {
                $(".tone").removeClass("d-none");
                $(".tone select").attr("name", "tone");
              } else {
                $(".tone").addClass("d-none");
                $(".d-none select").removeAttr("name");
              }
              $("#getkeywords").empty();
              $("#getkeywords").append(data.template ?? "");
            } catch (_) {
              scheduleInteractiveErrorClick(
                getMsg(document.body, "keywords_unavailable"),
              );
            }
          },
          error: function (): void {
            scheduleInteractiveErrorClick(
              getMsg(document.body, "server_unavailable"),
            );
          },
        });
      },
    );
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(root)) {
        $(document.body).off(".aiTemplate");
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const bindGenerate = (): void => {
    const btn = qs("#generate");
    if (!btn) {
      return;
    }
    if (btn.getAttribute(dataBoundGen) === "true") {
      return;
    }
    btn.setAttribute(dataBoundGen, "true");
    $(document.body).on("click.aiGenerate", "#generate", function (): void {
      const form = $("#myForm");
      const explicit = '{{ route("generate.response") }}';
      const endpoint = resolveRoute(form.get(0), explicit);
      if (!endpoint) {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
        scheduleInteractiveErrorClick(getMsg(this, "generate_unavailable"));
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
            $("#generate").empty();
            $("#generate").append(
              '<span class="spinner-grow spinner-grow-sm" role="status"></span>',
            );
          } catch (_) {
    console.error(`[copy] Error:`, _);
  }
        },
        success: function (data: string | { message?: string }) {
          try {
            $(".response").removeClass("d-none");
            $("#generate").text("Re-Generate");
            if (typeof data !== "string" && data.message) {
              if (window.show_toastr) {
                window.show_toastr("error", data.message, "error");
              }
              $("#commonModalOver").modal("hide");
            } else {
              $("#ai-description").val((data ?? "") as string);
            }
          } catch (_) {
            scheduleInteractiveErrorClick(
              getMsg(document.body, "generate_unavailable"),
            );
          }
        },
        error: function (): void {
          scheduleInteractiveErrorClick(
            getMsg(document.body, "server_unavailable"),
          );
        },
      });
    });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(btn)) {
        $(document.body).off(".aiGenerate");
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const exposeGlobals = (): void => {
    if (!window.copyText) {
      window.copyText = copyText;
    }
    if (!window.copySelectedText) {
      window.copySelectedText = copySelectedText;
    }
  };
  const init = (): void => {
    if (!$.fn) {
      try {
        console.info("jQuery unavailable");
      } catch (_) {
    console.error(`[copy] Error:`, _);
  }
      scheduleInteractiveErrorClick(
        getMsg(document.body, "plugin_unavailable"),
      );
      return;
    }
    setCheckedFirstRadio();
    bindTemplateChange();
    bindGenerate();
    exposeGlobals();
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
