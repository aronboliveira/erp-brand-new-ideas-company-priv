/** @requires ERPGuard */
(function () {
  const { guard } = window.ERPBootstrap.require("ERPGuard");
  if (!guard) return;
  const $ = window.jQuery;

  const dataBoundInit = "data-bound-grammar-init";
  const dataBoundRegen = "data-bound-grammar-regen";
  const qs = (s, r = document) => r.querySelector(s);
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
  const initGrammarSeed = () => {
    const host = document.body;
    if (!host || host.getAttribute(dataBoundInit) === "true") {
      return;
    }
    host.setAttribute(dataBoundInit, "true");
    try {
      let summernoteValue = "";
      if ($ && $(".grammer_textarea").length > 0) {
        summernoteValue = $(".grammer_textarea").val() ?? "";
      } else {
        if (!$ || !$.fn) {
          try {
            if (
              window.location.hostname === "localhost" ||
              window.location.hostname === "127.0.0.1"
            )
              console.error("jQuery unavailable");
          } catch (_) {}
          guard.scheduleInteractiveError(guard.getMsg("plugin_unavailable"));
          return;
        }
        if ($.fn.summernote && $(".summernote-simple").length > 0) {
          try {
            $(".summernote-simple").summernote();
            summernoteValue = $(".summernote-simple").summernote("code") ?? "";
          } catch (_) {
            summernoteValue = $(".summernote-simple").val() ?? "";
          }
        } else {
          guard.scheduleInteractiveError(guard.getMsg("plugin_unavailable"));
        }
      }
      summernoteValue = String(summernoteValue).replace(/<(.|\n)*?>/g, "");
      const desc = $("#description");
      if (desc && desc.length) {
        desc.text(summernoteValue ?? "");
      } else {
        guard.scheduleInteractiveError(guard.getMsg("grammar_init_unavailable"));
      }
    } catch (_) {
      guard.scheduleInteractiveError(
        guard.getMsg("grammar_init_unavailable")
      );
    }
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const bindRegenerate = () => {
    const btn = qs("#regenerate");
    if (!btn) {
      return;
    }
    if (btn.getAttribute(dataBoundRegen) === "true") {
      return;
    }
    btn.setAttribute(dataBoundRegen, "true");
    $(document.body).on("click.grammarRegen", "#regenerate", function () {
      try {
        const form = $("#myGrammarForm");
        const formEl = form.get(0);
        const explicit = "{{ route('grammar.response') }}";
        const endpoint = resolveRoute(formEl, explicit);
        if (!endpoint) {
          guard.scheduleInteractiveError(
            guard.getMsg("generate_unavailable")
          );
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
              if (data && data.message) {
                if (window.show_toastr) {
                  window.show_toastr("error", data.message, "error");
                }
                $("#commonModalOver").modal("hide");
              } else {
                $("#ai-description").val(data ?? "");
              }
            } catch (_) {
              guard.scheduleInteractiveError(
                guard.getMsg("generate_unavailable")
              );
            }
          },
          error: function () {
            guard.scheduleInteractiveError(guard.getMsg("ajax_unavailable"));
          },
        });
      } catch (_) {
        guard.scheduleInteractiveError(guard.getMsg("generate_unavailable"));
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
  const exposeCopy = () => {
    if (!window.copyGrammerText) {
      window.copyGrammerText = function () {
        try {
          const copied = $("#ai-description").val() ?? "";
          if ($ && $(".grammer_textarea").length > 0) {
            $(".grammer_textarea").val(copied ?? "");
          } else {
            if (
              $ &&
              $.fn &&
              $.fn.summernote &&
              $(".summernote-simple").length > 0
            ) {
              try {
                $(".summernote-simple").summernote("code", copied ?? "");
              } catch (_) {
                $(".summernote-simple").val(copied ?? "");
              }
            } else {
              guard.scheduleInteractiveError(
                guard.getMsg("plugin_unavailable")
              );
            }
          }
          if (window.show_toastr) {
            window.show_toastr(
              "success",
              "Result text has been copied successfully",
              "success"
            );
          }
          $("#commonModalOver").modal("hide");
        } catch (_) {
          guard.scheduleInteractiveError(
            guard.getMsg("grammar_init_unavailable")
          );
        }
      };
    }
  };
  const init = () => {
    if (!$ || !$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {}
      guard.scheduleInteractiveError(guard.getMsg("plugin_unavailable"));
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
