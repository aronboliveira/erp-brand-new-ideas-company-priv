/**
 * @fileoverview TypeScript version of public/assets/js/routes/customFields/delete.js
 * @generated from original JavaScript - manual review recommended
 * @module delete
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

((): void => {
  try {
    const selector = ".delete-custom-field-link[data-route-guard][data-url]";
    const alias = "data-listening-customfieldsdeleteclick";
    document.querySelectorAll(selector).forEach((el): void => {
      if (!el.hasAttribute(alias)) {
        el.setAttribute(alias, "true");
        el.addEventListener("click", event => {
          if (el.getAttribute(alias) !== "true") return;
          const url = el.getAttribute("data-url");
          if (url === "#" && (el as HTMLAnchorElement).href === "#") {
            event.preventDefault();
            const hasBS = Array.from(document.scripts).some(
              s =>
                s.src &&
                s.src.includes("bootstrap.min.js") &&
                window.bootstrap &&
                typeof window.bootstrap.Modal === "function",
            );
            const msg =
              (event.currentTarget as Element).getAttribute("data-guard-msg") ??
              "Delete route is unavailable. Please contact technical support or your domain administrator.";
            if (hasBS) {
              const wrapper = document.createElement("div");
              wrapper.innerHTML = `
                                          <div class="modal fade" tabindex="-1">
                                              <div class="modal-dialog modal-sm">
                                                  <div class="modal-content">
                                                      <div class="modal-header">
                                                          <h5 class="modal-title">Error</h5>
                                                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                      </div>
                                                      <div class="modal-body"><p>${msg}</p></div>
                                                      <div class="modal-footer">
                                                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>`;
              document.body.appendChild(wrapper);
              new window.bootstrap.Modal(wrapper.querySelector(".modal")!).show();
            } else {
              alert(msg);
            }
          }
        });
      }
    });
  } catch (__moduleErr) {
    console.error("[delete] failed to initialise:", __moduleErr);
  }
})();

export {};
