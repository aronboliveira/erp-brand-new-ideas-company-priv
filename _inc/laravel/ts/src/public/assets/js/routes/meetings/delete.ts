/**
 * @fileoverview TypeScript version of public/assets/js/routes/meetings/delete.js
 * @generated from original JavaScript - manual review recommended
 * @module delete
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

((): void => {
  try {
    const selector = ".delete-meeting-link",
      alias = "data-listening-deletemeetingclick";

    document.querySelectorAll(selector).forEach((el: Element): void => {
      if (!el.hasAttribute(alias)) {
        el.setAttribute(alias, "true");
        el.addEventListener("click", event => {
          const url = el.getAttribute("data-url"),
            href = (el as HTMLAnchorElement).href
              .replace(window.location.origin, "")
              .replace(window.location.pathname, "");
          if ((!url || url === "#") && (!href || href === "#")) {
            event.preventDefault();
            const hasBS = Array.from(document.scripts).some(
              s =>
                s.src &&
                s.src.includes("bootstrap.min.js") &&
                window.bootstrap &&
                typeof window.bootstrap.Modal === "function",
            );
            const errFb = "# ERROR",
              dataClientLocalized = "data-client-localized",
              dataGuardMsg = "data-guard-msg";
            let msg = errFb;
            if (
              el.getAttribute("data-sv-localized") === "true" ||
              el.getAttribute(dataClientLocalized) === "true"
            )
              msg = el.getAttribute(dataGuardMsg) ?? errFb;
            else {
              let lang = (
                window.sessionStorage.getItem("erp-np-lang") ??
                document.documentElement.lang ??
                "en"
              )
                .toLowerCase()
                .replace(/_/g, "-");
              lang = lang === "pt-br" ? lang : lang.slice(0, 2);
              const msgKey = "delete_meeting_route_unavailable";
              msg =
                window.translations?.[lang]?.[msgKey] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.en?.[msgKey] ||
                errFb;
              if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
              }
            }
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
              new window.bootstrap.Modal(
                wrapper.querySelector(".modal")!,
              ).show();
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
