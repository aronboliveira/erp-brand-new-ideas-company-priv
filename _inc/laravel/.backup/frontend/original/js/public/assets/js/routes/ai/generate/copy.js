(function () {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataBoundCopy = "data-bound-copy";
  const dataBoundSel = "data-bound-sel";
  const dataBoundChange = "data-bound-template-change";
  const dataBoundGen = "data-bound-generate";
  const copiedMsgId = "ai-copied-msg";
  const qs = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) =>
    Array.prototype.slice.call(r.querySelectorAll(s) || []);
  const hasBootstrap = () =>
    qs('link[rel="stylesheet"][href*="bootstrap"]') ||
    (qs('link[href*="bootstrap"]') &&
      window.bootstrap &&
      window.bootstrap.Toast);
  const ensureToastContainer = () => {
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
    if (hasBootstrap()) {
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
  const scheduleInteractiveErrorClick = message => {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") {
      return;
    }
    host.setAttribute(dataErrGuard, "true");
    const once = () => {
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
        window.sessionStorage.getItem("erp-np-lang") ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el?.getAttribute?.(dataGuardMsg) ||
        window.translations?.["en"]?.[msgKey] ||
        errFb;
      if (msg !== errFb && el) {
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
        ? el.getAttribute("action") || ""
        : el.getAttribute("href") || ""
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
  const setCheckedFirstRadio = () => {
    const modal = qs("#commonModalOver");
    if (!modal) {
      return;
    }
    const RadioSel = "#commonModalOver input[type='radio']";
    const first = qs(RadioSel);
    if (first) {
      if (!first.checked) {
        first.checked = true;
        $(first).trigger("change");
      }
    }
  };
  const writeField = (name, value) => {
    if (!name) {
      return false;
    }
    const $in = $('input[name="' + name + '"]');
    if ($in.length > 0) {
      $in.val(value ?? "");
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
        $ta.summernote("code", value ?? "");
        return true;
      } catch (_) {
        $ta.val(value ?? "");
        return true;
      }
    }
    $ta.val(value ?? "");
    return true;
  };
  const ensureCopiedLabel = afterEl => {
    const el = qs("#" + copiedMsgId);
    if (el) {
      return el;
    }
    const span = document.createElement("span");
    span.id = copiedMsgId;
    span.style.marginLeft = "0.5rem";
    span.textContent = getMsg(afterEl || document.body, "copied_label");
    (afterEl?.parentNode || document.body).insertBefore(
      span,
      afterEl?.nextSibling || null
    );
    return span;
  };
  const copyText = () => {
    try {
      const selected =
        $('input[name="template_name"]:checked').attr("data-name") ?? "";
      const copied = $("#ai-description").val() ?? "";
      if (!selected) {
        scheduleInteractiveErrorClick(
          getMsg(document.body, "copy_unavailable")
        );
        return;
      }
      const ok = writeField(selected, copied);
      if (!ok) {
        scheduleInteractiveErrorClick(
          getMsg(document.body, "copy_unavailable")
        );
        return;
      }
      const anchor = qs("#ai-description");
      ensureCopiedLabel(anchor);
      if (window.show_toastr) {
        window.show_toastr(
          "success",
          "Result text has been copied successfully",
          "success"
        );
      }
      $("#commonModalOver").modal("hide");
    } catch (_) {
      scheduleInteractiveErrorClick(getMsg(document.body, "copy_unavailable"));
    }
  };
  const copySelectedText = () => {
    try {
      const selected =
        $('input[name="template_name"]:checked').attr("data-name") ?? "";
      const selText =
        (window.getSelection && window.getSelection().toString()) || "";
      if (!selected) {
        scheduleInteractiveErrorClick(
          getMsg(document.body, "copy_unavailable")
        );
        return;
      }
      if (!selText) {
        scheduleInteractiveErrorClick(
          getMsg(document.body, "selection_unavailable")
        );
        return;
      }
      const ok = writeField(selected, selText);
      if (!ok) {
        scheduleInteractiveErrorClick(
          getMsg(document.body, "copy_unavailable")
        );
        return;
      }
      const anchor = qs("#ai-description");
      ensureCopiedLabel(anchor);
      if (window.show_toastr) {
        window.show_toastr(
          "success",
          "Result text has been copied successfully",
          "success"
        );
      }
      $("#commonModalOver").modal("hide");
    } catch (_) {
      scheduleInteractiveErrorClick(getMsg(document.body, "copy_unavailable"));
    }
  };
  const bindTemplateChange = () => {
    const root = document.body;
    if (root.getAttribute(dataBoundChange) === "true") {
      return;
    }
    root.setAttribute(dataBoundChange, "true");
    $(document.body).on("change.aiTemplate", ".template_name", function () {
      const templateId = $(this).val() ?? "";
      const explicit =
        '{{route("generate.keywords",["__templateId"])}}'.replace(
          "__templateId",
          templateId ?? ""
        );
      const endpoint = resolveRoute(this, explicit);
      if (!endpoint) {
        scheduleInteractiveErrorClick(getMsg(this, "keywords_unavailable"));
        return;
      }
      $.ajax({
        type: "post",
        url: endpoint,
        dataType: "json",
        data: { _token: "{{ csrf_token() }}", template_id: templateId },
        cache: false,
        success: function (data) {
          try {
            if (data && data.tone == 1) {
              $(".tone").removeClass("d-none");
              $(".tone select").attr("name", "tone");
            } else {
              $(".tone").addClass("d-none");
              $(".d-none select").removeAttr("name");
            }
            $("#getkeywords").empty();
            $("#getkeywords").append(data?.template ?? "");
          } catch (_) {
            scheduleInteractiveErrorClick(
              getMsg(document.body, "keywords_unavailable")
            );
          }
        },
        error: function () {
          scheduleInteractiveErrorClick(
            getMsg(document.body, "server_unavailable")
          );
        },
      });
    });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(root)) {
        $(document.body).off(".aiTemplate");
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const bindGenerate = () => {
    const btn = qs("#generate");
    if (!btn) {
      return;
    }
    if (btn.getAttribute(dataBoundGen) === "true") {
      return;
    }
    btn.setAttribute(dataBoundGen, "true");
    $(document.body).on("click.aiGenerate", "#generate", function () {
      const form = $("#myForm");
      const explicit = '{{ route("generate.response") }}';
      const endpoint = resolveRoute(form.get(0), explicit);
      if (!endpoint) {
        scheduleInteractiveErrorClick(getMsg(this, "generate_unavailable"));
        return;
      }
      $.ajax({
        type: "post",
        url: endpoint,
        dataType: "json",
        data: form.serialize(),
        cache: false,
        beforeSend: function () {
          try {
            $("#generate").empty();
            $("#generate").append(
              '<span class="spinner-grow spinner-grow-sm" role="status"></span>'
            );
          } catch (_) {}
        },
        success: function (data) {
          try {
            $(".response").removeClass("d-none");
            $("#generate").text("Re-Generate");
            if (data && data.message) {
              if (window.show_toastr) {
                window.show_toastr("error", data.message, "error");
              }
              $("#commonModalOver").modal("hide");
            } else {
              $("#ai-description").val(data ?? "");
            }
          } catch (_) {
            scheduleInteractiveErrorClick(
              getMsg(document.body, "generate_unavailable")
            );
          }
        },
        error: function () {
          scheduleInteractiveErrorClick(
            getMsg(document.body, "server_unavailable")
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
  const exposeGlobals = () => {
    if (!window.copyText) {
      window.copyText = copyText;
    }
    if (!window.copySelectedText) {
      window.copySelectedText = copySelectedText;
    }
  };
  const init = () => {
    if (!$ || !$.fn) {
      try {
        console.log("jQuery unavailable");
      } catch (_) {}
      scheduleInteractiveErrorClick(
        getMsg(document.body, "plugin_unavailable")
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
