(() => {
  const selector = ".create-invoice-link";
  const alias = "data-listening-createinvoiceclick";

  document.querySelectorAll(selector).forEach(el => {
    if (!el.hasAttribute(alias)) {
      el.setAttribute(alias, "true");
      el.addEventListener("click", event => {
        const url = el.getAttribute("data-url");
        const href = el.href;
        if ((!url || url === "#") && (!href || href === "#")) {
          event.preventDefault();
          const hasBS = Array.from(document.scripts).some(
            s =>
              s.src &&
              s.src.includes("bootstrap.min.js") &&
              window.bootstrap &&
              typeof window.bootstrap.Modal === "function"
          );
          const msg =
            el.getAttribute("data-guard-msg") ||
            "Create invoice route is unavailable. Please contact technical support or your domain administrator.";
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
          } else {
            alert(msg);
          }
        }
      });
    }
  });
})();
