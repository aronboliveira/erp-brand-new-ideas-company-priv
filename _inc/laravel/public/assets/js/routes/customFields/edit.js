/**
 * @fileoverview TypeScript version of public/assets/js/routes/customFields/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */
// eslint-disable-next-line @typescript-eslint/no-unused-vars
(() => {
    try {
        const selector = ".edit-custom-field-link[data-ajax-popup][data-url]", alias = "data-listening-customfieldseditclick";
        document.querySelectorAll(selector).forEach((el) => {
            if (!el.hasAttribute(alias)) {
                el.addEventListener("click", event => {
                    if (el.getAttribute(alias) !== "true")
                        return;
                    if (el.getAttribute("data-url") === "#" &&
                        el.href === "#") {
                        event.preventDefault();
                        const hasBS = Array.from(document.scripts).some(s => s.src &&
                            s.src.includes("bootstrap.min.js") &&
                            window.bootstrap &&
                            typeof window.bootstrap.Modal === "function");
                        const msg = event.currentTarget.getAttribute("data-guard-msg") ??
                            "Edit route is unavailable. Please contact technical support or your domain administrator.";
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
                            new window.bootstrap.Modal(wrapper.querySelector(".modal")).show();
                        }
                        else {
                            alert(msg);
                        }
                    }
                });
                el.setAttribute(alias, "true");
            }
        });
    }
    catch (__moduleErr) {
        console.error("[edit] failed to initialise:", __moduleErr);
    }
})();
//# sourceMappingURL=edit.js.map