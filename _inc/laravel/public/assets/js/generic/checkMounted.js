(() => {
  console.log("checking mount...");
  const checkMounted = () => {
    console.log("checking body content...");
    const m =
      document.body && document.body.innerHTML
        ? document.body.innerHTML.match(/^[\s\n\t\r]*[0-9]+</)
        : null;
    if (!m) return;

    const ensureCss = (href, selector) => {
      if (!document.querySelector(selector)) {
        const l = document.createElement("link");
        l.rel = "stylesheet";
        l.href = href;
        document.body.appendChild(l);
      }
    };
    const ensureJs = (src, selector) => {
      if (!document.querySelector(selector)) {
        const s = document.createElement("script");
        s.src = src;
        s.defer = true;
        document.body.appendChild(s);
      }
    };

    ensureCss(
      "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css",
      'link[rel="stylesheet"][href*="bootstrap"][href$=".css"]:not([href*="icons"])'
    );
    ensureCss(
      "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css",
      'link[rel="stylesheet"][href*="bootstrap-icons"]'
    );
    ensureJs(
      "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js",
      'script[src*="bootstrap"][src*="bundle"]'
    );

    if (document.getElementById("manual-recovery-overlay")) return;

    const overlay = document.createElement("div");
    overlay.id = "manual-recovery-overlay";
    overlay.className =
      "position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3";
    overlay.style.zIndex = "2147483000";
    overlay.style.background = "rgba(0,0,0,.25)";
    overlay.setAttribute("role", "dialog");
    overlay.setAttribute("aria-modal", "true");
    overlay.setAttribute("aria-labelledby", "diagTitle");
    overlay.setAttribute("aria-describedby", "diagDesc");
    overlay.tabIndex = -1;

    const card = document.createElement("div");
    card.className = "card shadow-lg border-0";
    card.style.maxWidth = "720px";
    card.style.width = "100%";
    card.setAttribute("role", "document");

    const cardBody = document.createElement("div");
    cardBody.className = "card-body p-4 text-center";

    const iconWrap = document.createElement("div");
    iconWrap.className = "mb-3";
    iconWrap.innerHTML =
      '<i class="bi bi-exclamation-triangle-fill fs-1 text-warning" aria-hidden="true"></i>';

    const title = document.createElement("h1");
    title.id = "diagTitle";
    title.className = "h4 fw-bold mb-2";
    title.textContent = "Unexpected Output Detected";

    const desc = document.createElement("p");
    desc.id = "diagDesc";
    desc.className = "text-muted mb-3";
    desc.innerHTML =
      "We found unexpected content at the start of this page. You can safely navigate using the options below.";

    const imgWrap = document.createElement("div");
    imgWrap.className = "my-3";

    const img = document.createElement("img");
    img.src = "/assets/images/404-art.webp";
    img.alt = "Illustration for error/404";
    img.className = "img-fluid rounded";
    img.style.maxHeight = "240px";
    img.loading = "lazy";
    img.setAttribute("aria-hidden", "true");
    img.onerror = function () {
      const alert = document.createElement("div");
      alert.className =
        "alert alert-warning d-flex align-items-center justify-content-center gap-2 mt-3 mb-0";
      alert.setAttribute("role", "alert");
      alert.setAttribute("aria-live", "assertive");
      alert.setAttribute("aria-atomic", "true");
      alert.innerHTML =
        '<i class="bi bi-image-alt" aria-hidden="true"></i><div>Illustration failed to load.</div>';
      this.replaceWith(alert);
    };
    imgWrap.appendChild(img);

    const actions = document.createElement("div");
    actions.className = "d-grid gap-2 d-sm-flex justify-content-sm-center mt-3";

    const backBtn = document.createElement("button");
    backBtn.type = "button";
    backBtn.className = "btn btn-primary";
    backBtn.setAttribute("aria-label", "Go back to previous page");
    backBtn.innerHTML =
      '<i class="bi bi-arrow-left" aria-hidden="true"></i> Go Back';
    backBtn.onclick = () => history.back();

    const homeA = document.createElement("a");
    homeA.className = "btn btn-outline-secondary";
    homeA.href = "/";
    homeA.setAttribute("aria-label", "Return to the home page");
    homeA.innerHTML = '<i class="bi bi-house" aria-hidden="true"></i> Home';

    const reloadBtn = document.createElement("button");
    reloadBtn.type = "button";
    reloadBtn.className = "btn btn-outline-dark";
    reloadBtn.setAttribute("aria-label", "Reload this page");
    reloadBtn.innerHTML =
      '<i class="bi bi-arrow-repeat" aria-hidden="true"></i> Reload';
    reloadBtn.onclick = () => location.reload();

    actions.append(backBtn, homeA, reloadBtn);

    const hint = document.createElement("p");
    hint.className = "mt-3 small text-muted";
    hint.innerHTML =
      '<i class="bi bi-info-circle" aria-hidden="true"></i> Press <kbd>Esc</kbd> to dismiss this message.';

    cardBody.append(iconWrap, title, desc, imgWrap, actions, hint);
    card.appendChild(cardBody);
    overlay.appendChild(card);
    document.body.appendChild(overlay);
    document.body.classList.add("overflow-hidden");

    setTimeout(() => overlay.focus(), 0);

    const onEsc = e => {
      if (e.key === "Escape") {
        document.removeEventListener("keydown", onEsc);
        overlay.remove();
        document.body.classList.remove("overflow-hidden");
      }
    };
    document.addEventListener("keydown", onEsc, { once: true });
  };
  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", checkMounted);
  else checkMounted();
})();
