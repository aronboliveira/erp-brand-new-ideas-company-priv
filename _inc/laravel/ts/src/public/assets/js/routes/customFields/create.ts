/**
 * @fileoverview TypeScript version of public/assets/js/routes/customFields/create.js
 * @generated from original JavaScript - manual review recommended
 * @module create
 */

/* global bootstrap, $, jQuery */
((): void => {
  const link = document.getElementById(
    "create-custom-field-link",
  ) as HTMLAnchorElement | null;
  const alias = "data-listening-createclick";
  if (!link || link.hasAttribute(alias)) return;
  link.addEventListener("click", event => {
    if (link.getAttribute(alias) !== "true") return;
    const url = link.getAttribute("data-url");
    if (url !== "#" || link.href !== "#") return;
    const hasBS = Array.from(document.scripts).some(
      s =>
        s.src &&
        s.src.includes("bootstrap.min.js") &&
        window.bootstrap &&
        typeof window.bootstrap.Modal === "function",
    );
    const msg =
      (event.currentTarget as HTMLElement)?.getAttribute("data-guard-msg") ??
      "Create route is unavailable. Please contact technical support or your domain administrator.";
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
    event.preventDefault();
  });
  link.setAttribute(alias, "true");
})();

export {};
