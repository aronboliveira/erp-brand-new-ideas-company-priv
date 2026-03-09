(() => {
  try {
    const homeTab = document.getElementById("pills-home-tab");
    const listenerAttr = "data-daily-purchase-nav-listener-added";
    if (homeTab && homeTab.getAttribute(listenerAttr) !== "true") {
      homeTab.setAttribute(listenerAttr, "true");
      homeTab.addEventListener("click", e => {
        e.preventDefault();
        const url = homeTab.getAttribute("data-url");
        const href = homeTab.getAttribute("href");
        if ((!url || url === "#") && (!href || href === "#")) return;
        const msg = "{{ $dailyPurchaseNavMsg }}";
        const toastEl = document.querySelector(".toast");
        if (
          toastEl &&
          window.bootstrap &&
          typeof bootstrap.Toast === "function"
        ) {
          const toast = new bootstrap.Toast(toastEl);
          const body = toastEl.querySelector(".toast-body");
          if (body) {
            body.textContent = msg;
          }
          toast.show();
        } else {
          alert(msg);
        }
        window.location.href = url;
      });
    }
  } catch (error) {}
})();
