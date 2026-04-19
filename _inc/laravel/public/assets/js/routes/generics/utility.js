/**
 * @fileoverview TypeScript version of public/assets/js/routes/generics/utility.js
 * @generated from original JavaScript - manual review recommended
 * @module utility
 */
function _displayUnavailableRouteMessage(lang = "pt-br", msg = null) {
    const message = msg ??
        window.translations?.[lang]?.route_unavailable ??
        "Rota indisponível!";
    if (!document.getElementById("toast-error-styles")) {
        const styleTag = document.createElement("style");
        styleTag.id = "toast-error-styles";
        styleTag.textContent = `
      @keyframes fadeInDown {0%{opacity:0;transform:translateY(-20px)}100%{opacity:1;transform:translateY(0)}}
      @keyframes fadeOutUp {0%{opacity:1;transform:translateY(0)}100%{opacity:0;transform:translateY(-20px)}}
      .toast-route{z-index:999999}
      .toast-route.toast-error{animation:fadeInDown .4s ease-out;border:1px solid rgba(220,53,69,.2);backdrop-filter:blur(10px);transition:all .3s ease}
      .toast-route.toast-error:hover{transform:translateY(-2px)}
      .toast-route.toast-error.hiding{animation:fadeOutUp .3s ease-in forwards}
      .toast-route .toast-header{border-bottom:1px solid rgba(255,255,255,.2)}
      .toast-route .bi-exclamation-triangle-fill{font-size:1.1rem;animation:pulse 2s infinite}
      @keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.1)}}
    `;
        document.head.appendChild(styleTag);
    }
    if (window.bootstrap.Toast) {
        let toast = document.getElementById("route-unavailable-toast");
        if (!toast) {
            const tpl = document.createElement("template");
            tpl.innerHTML = `
        <div id="route-unavailable-toast"
             class="toast toast-route position-fixed top-0 end-0 m-3 toast-error"
             role="alert" aria-live="assertive" aria-atomic="true">
          <div class="toast-header bg-danger text-white">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong class="me-auto">Erro</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body bg-light text-danger fw-bold"></div>
        </div>`;
            const firstEl = tpl.content.firstElementChild;
            if (firstEl)
                document.body.appendChild(firstEl);
            toast = document.getElementById("route-unavailable-toast");
            if (!toast)
                return;
            toast.addEventListener("hide.bs.toast", () => {
                toast?.classList.add("hiding");
            });
            toast.addEventListener("hidden.bs.toast", () => {
                toast?.remove();
            });
        }
        const body = toast.querySelector(".toast-body");
        if (body)
            body.textContent = message;
        const instance = window.bootstrap.Toast.getOrCreateInstance(toast, {
            autohide: true,
            delay: 4000,
        });
        instance.show();
        return;
    }
    if (window.toastr) {
        window.toastr.error(message, "Erro", {
            positionClass: "toast-top-center",
            timeOut: 6000,
        });
        return;
    }
    alert(message);
}
//# sourceMappingURL=utility.js.map