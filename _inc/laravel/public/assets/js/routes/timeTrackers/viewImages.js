const _t = k => {
  const l = (document.documentElement || {}).lang || "en";
  const t = window.translations;
  return (t && t[l] && t[l][k]) || k;
};
(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  if (!guard) return;

  try {
    const items = document.querySelectorAll(".view-images");
    if (!items || items.length === 0) return;

    items.forEach(img => {
      try {
        if (img.getAttribute("data-listener-active") === "true") return;
        img.setAttribute("data-listener-active", "true");

        img.addEventListener("click", async e => {
          try {
            e.preventDefault();

            const url = img.getAttribute("data-url") ?? "#";
            if (!url || url === "#") {
              const msg = img.getAttribute("data-guard-msg") || "View tracker images route is unavailable. Please contact technical support or your domain administrator.";
              guard.showToast(msg);
              img.setAttribute("data-failed-route", "true");
              return;
            }

            const modal = document.getElementById("exampleModalCenter");
            const content = modal ? modal.querySelector(".image_sider_div") : null;
            if (!modal || !content) {
              alert(_t("Could not find images modal container"));
              return;
            }

            try {
              const rsp = await fetch(url, {
                method: "GET",
                credentials: "same-origin",
                headers: { "X-Requested-With": "XMLHttpRequest" },
              });
              if (!rsp.ok) throw new Error("HTTP " + rsp.status);
              const html = await rsp.text();
              content.innerHTML = html;

              if (typeof window.bootstrap !== "undefined" && window.bootstrap?.Modal) {
                const m = window.bootstrap.Modal.getOrCreateInstance(modal);
                m.show();
              }
            } catch (xhrErr) {
              const msg = _t("Failed to load tracker images. Please try again later.");
              guard.showToast(msg);
            }
          } catch {}
        });
      } catch {}
    });
  } catch {}
})();
