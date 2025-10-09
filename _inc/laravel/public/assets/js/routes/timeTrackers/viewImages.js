(() => {
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
              const msg =
                img.getAttribute("data-guard-msg") ||
                "View tracker images route is unavailable. Please contact technical support or your domain administrator.";
              let container = document.getElementById("toast-container");
              if (!container) {
                container = document.createElement("div");
                container.id = "toast-container";
                container.className =
                  "toast-container position-fixed top-0 end-0 p-3";
                container.style.zIndex = "1080";
                document.body.appendChild(container);
              }
              const bsLink = document.querySelector('link[href*="bootstrap"]');
              if (
                bsLink &&
                typeof window.bootstrap !== "undefined" &&
                window.bootstrap?.Toast
              ) {
                const toast = document.createElement("div");
                toast.className = "toast";
                toast.setAttribute("role", "alert");
                toast.setAttribute("aria-live", "assertive");
                toast.setAttribute("aria-atomic", "true");
                const body = document.createElement("div");
                body.className = "toast-body";
                body.textContent = msg;
                toast.appendChild(body);
                container.appendChild(toast);
                try {
                  window.bootstrap.Toast.getOrCreateInstance(toast).show();
                } catch {
                  alert(msg);
                }
              } else {
                alert(msg);
              }
              img.setAttribute("data-failed-route", "true");
              return;
            }

            const modal = document.getElementById("exampleModalCenter");
            const content = modal
              ? modal.querySelector(".image_sider_div")
              : null;
            if (!modal || !content) {
              alert("Could not find images modal container");
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

              if (
                typeof window.bootstrap !== "undefined" &&
                window.bootstrap?.Modal
              ) {
                const m = window.bootstrap.Modal.getOrCreateInstance(modal);
                m.show();
              }
            } catch (xhrErr) {
              const msg =
                "Failed to load tracker images. Please try again later.";
              const bsLink = document.querySelector('link[href*="bootstrap"]');
              let container = document.getElementById("toast-container");
              if (!container) {
                container = document.createElement("div");
                container.id = "toast-container";
                container.className =
                  "toast-container position-fixed top-0 end-0 p-3";
                container.style.zIndex = "1080";
                document.body.appendChild(container);
              }
              if (
                bsLink &&
                typeof window.bootstrap !== "undefined" &&
                window.bootstrap?.Toast
              ) {
                const toast = document.createElement("div");
                toast.className = "toast";
                toast.setAttribute("role", "alert");
                toast.setAttribute("aria-live", "assertive");
                toast.setAttribute("aria-atomic", "true");
                const body = document.createElement("div");
                body.className = "toast-body";
                body.textContent = msg;
                toast.appendChild(body);
                container.appendChild(toast);
                try {
                  window.bootstrap.Toast.getOrCreateInstance(toast).show();
                } catch {
                  alert(msg);
                }
              } else {
                alert(msg);
              }
            }
          } catch {}
        });
      } catch {}
    });
  } catch {}
})();
