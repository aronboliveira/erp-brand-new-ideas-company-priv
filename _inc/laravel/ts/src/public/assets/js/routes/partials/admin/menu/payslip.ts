/**
 * @file payslip.ts — Payslip menu link guard
 * @description Mirrors public/assets/js/routes/partials/admin/menu/payslip.js
 */

(() => {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const guard = (window as any).ERPGuard as { scheduleError?: (msg: string, ctx: string) => void } | undefined;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const utils = (window as any).ERPUtils as { getMsg?: (key: string) => string } | undefined;
  const { scheduleError } = guard ?? {};
  const { getMsg } = utils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    return;
  }

  try {
    const l = document.getElementById("payslip-link");
    if (!l) return;
    if (l.getAttribute("data-listener-active") === "true") return;
    l.setAttribute("data-listener-active", "true");

    l.addEventListener("click", (e: MouseEvent) => {
      try {
        const href = (l.getAttribute("href") ?? "#").trim(),
          url = (l.getAttribute("data-url") ?? href ?? "#").trim();
        if (url !== "#" && href !== "#") return;

        e.preventDefault();

        const msg = l.getAttribute("data-guard-msg") || getMsg("payslip_route_unavailable");
        scheduleError(msg, "click");
        l.setAttribute("data-failed-route", "true");
      } catch (_) {
        /* silent in prod */
      }
    });
  } catch (_) {
    /* silent in prod */
  }
})();

export {};
