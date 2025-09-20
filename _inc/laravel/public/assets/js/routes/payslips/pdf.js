(() => {
  const toast = msg => {
    try {
      if (window.bootstrap?.Toast) {
        const c =
          document.getElementById("toast-container") ||
          (() => {
            const t = document.createElement("div");
            t.id = "toast-container";
            document.body.appendChild(t);
            return t;
          })();
        const el = document.createElement("div");
        el.className = "toast";
        el.setAttribute("role", "alert");
        el.setAttribute("aria-live", "assertive");
        el.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = msg;
        el.appendChild(body);
        c.appendChild(el);
        window.bootstrap.Toast.getOrCreateInstance(el).show();
      } else {
        alert(msg);
      }
    } catch {
      alert(msg);
    }
  };

  const mail = document.getElementById("payslip-mail-send");
  if (mail) {
    mail.addEventListener("click", e => {
      const url =
        mail.getAttribute("href") || mail.getAttribute("data-url") || "#";
      if (!url || url === "#") {
        e.preventDefault();
        const msg =
          mail.getAttribute("data-guard-msg") ||
          "Send Payslip route is unavailable. Please contact technical support or your domain administrator.";
        toast(msg);
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
