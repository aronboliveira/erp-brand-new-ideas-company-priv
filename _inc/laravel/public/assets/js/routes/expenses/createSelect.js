(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  if (!guard || !utils) return;

  let errorMessage = "";

  const getMsg = (key, el) => {
    return utils.getTranslation(key) || "# ERROR";
  };

  const showError = message => {
    guard.showToast(message);
  };

  document.addEventListener("pointerup", () => {
    if (errorMessage) {
      showError(errorMessage);
      errorMessage = "";
    }
  });

  const initSelection = () => {
    const first = document.querySelector("input[name=type]");
    if (!first) return;
    first.checked = true;
    const radios = document.querySelectorAll('input[name="type"]');
    radios.forEach(r => {
      if (r.getAttribute("data-listener-active") === "true") return;
      r.setAttribute("data-listener-active", "true");
      r.addEventListener("change", onTypeChange);
      new MutationObserver((m, o) => {
        m.forEach(mut =>
          mut.removedNodes.forEach(n => {
            if (n === r) {
              r.removeEventListener("change", onTypeChange);
              o.disconnect();
            }
          }),
        );
      }).observe(document.body, { childList: true, subtree: true });
    });
    onTypeChange.call(document.querySelector('input[name="type"]:checked'));
  };

  const onTypeChange = function () {
    const type = this.value;
    ["employee", "customer", "vendor"].forEach(cls => {
      document.querySelectorAll(`.${cls}`).forEach(el => {
        el.classList.toggle("d-block", cls === type);
        el.classList.toggle("d-none", cls !== type);
      });
    });
  };

  const setupAjax = type => {
    const sel = document.getElementById(type);
    if (!sel || sel.getAttribute("data-listener-active") === "true") return;
    sel.setAttribute("data-listener-active", "true");
    const detail = document.getElementById(`${type}_detail`);
    const box = document.getElementById(`${type}-box`);
    sel.addEventListener("change", () => {
      if (detail) detail.classList.replace("d-none", "d-block");
      if (box) box.classList.replace("d-block", "d-none");
      const url = sel.getAttribute("data-url");
      if (!url) {
        errorMessage = getMsg(`${type}_fetch_failed`, sel);
        return;
      }
      const id = sel.value;
      $.ajax({
        url,
        type: "POST",
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: { id },
      })
        .done(data => {
          if (data && detail) detail.innerHTML = data;
          else if (box && detail) {
            box.classList.replace("d-none", "d-block");
            detail.classList.replace("d-block", "d-none");
          }
        })
        .fail(() => {
          errorMessage = getMsg(`${type}_fetch_failed`, sel);
        });
    });
    new MutationObserver((m, o) => {
      m.forEach(mut =>
        mut.removedNodes.forEach(n => {
          if (n === sel) {
            sel.removeEventListener("change", () => {});
            o.disconnect();
          }
        }),
      );
    }).observe(document.body, { childList: true, subtree: true });
  };

  document.addEventListener("DOMContentLoaded", () => {
    try {
      initSelection();
      ["employee", "customer", "vendor"].forEach(setupAjax);
    } catch {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("Initialization error");
    }
  });
})();
