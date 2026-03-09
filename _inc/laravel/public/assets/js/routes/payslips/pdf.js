(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const mail = document.getElementById("payslip-mail-send");
  if (mail) {
    mail.addEventListener("click", e => {
      const url =
        mail.getAttribute("href") || mail.getAttribute("data-url") || "#";
      if (!url || url === "#") {
        e.preventDefault();
        const msg =
          mail.getAttribute("data-guard-msg") ||
          getMsg("send_payslip_unavailable");
        scheduleError(msg, "click");
      }
    });
  }

  const printableId = "printableArea";
  const printBtn = document.getElementById("payslip-download");
  const printFn = () => {
    const el = document.getElementById(printableId);
    if (!el) return;
    const w = window.open("", "_blank", "noopener,noreferrer");
    if (!w) return;
    w.document.open();
    w.document.write(`
      <html>
        <head>
          <title>Payslip</title>
          <meta charset="utf-8" />
          <meta name="viewport" content="width=device-width, initial-scale=1" />
          <link rel="stylesheet" href="${
            document.querySelector('link[href*="bootstrap"]')?.href || ""
          }">
          <style>body{padding:16px}</style>
        </head>
        <body>${el.innerHTML}</body>
      </html>
    `);
    w.document.close();
    w.focus();
    w.onload = () => w.print();
  };
  if (printBtn) {
    printBtn.addEventListener("click", e => {
      e.preventDefault();
      printFn();
    });
  }
  if (!window.saveAsPDF) window.saveAsPDF = printFn;
})();
