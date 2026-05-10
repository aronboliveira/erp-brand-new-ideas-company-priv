/* global Swal, show_toastr */
(() => {
  const payload = window.__reliabilityOperation;
  if (!payload || payload.__shown === true) return;
  payload.__shown = true;

  const styleId = "reliability-operation-feedback-style";
  const state = { ...payload };

  const escapeHtml = value =>
    String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");

  const injectStyle = () => {
    if (document.getElementById(styleId)) return;
    const style = document.createElement("style");
    style.id = styleId;
    style.textContent = `
      .reliability-operation-feedback .progress { height: .65rem; }
      .reliability-operation-feedback .progress-bar { transition: width .35s ease, background-color .2s ease; }
      .reliability-operation-feedback__meta { font-size: .8125rem; line-height: 1.45; }
    `;
    document.head.appendChild(style);
  };

  const progress = current => {
    const outbox = current.outbox_status || current.outbox?.[0]?.status;
    const ledger = current.ledger_status;
    if (["failed", "compensating"].includes(ledger)) return 100;
    if (["dead_letter", "cancelled"].includes(outbox)) return 100;
    if (ledger === "closed" || outbox === "dispatched") return 100;
    return Number(current.progress || 65);
  };

  const variant = current => {
    const outbox = current.outbox_status || current.outbox?.[0]?.status;
    const ledger = current.ledger_status;
    if (["failed", "compensating"].includes(ledger) || ["dead_letter", "cancelled"].includes(outbox)) {
      return "warning";
    }
    if (ledger === "closed" || outbox === "dispatched") return "success";
    return "info";
  };

  const title = current => {
    if (variant(current) === "warning") return "Financial control needs review";
    if (variant(current) === "success") return "Financial control completed";
    return "Financial control running";
  };

  const render = current => {
    const pct = Math.max(0, Math.min(100, progress(current)));
    const outbox = current.outbox_status || current.outbox?.[0]?.status || "pending";
    const ledger = current.ledger_status || "started";
    const bar = variant(current) === "warning" ? "bg-warning" : variant(current) === "success" ? "bg-success" : "bg-info";

    return `
      <div class="reliability-operation-feedback text-start">
        <div class="progress mb-3" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="${pct}">
          <div class="progress-bar ${bar}" style="width: ${pct}%"></div>
        </div>
        <div class="reliability-operation-feedback__meta text-muted">
          <div><strong>Operation:</strong> ${escapeHtml(current.summary || current.operation_type || "Finance operation")}</div>
          <div><strong>Ledger:</strong> ${escapeHtml(ledger)}</div>
          <div><strong>Outbox:</strong> ${escapeHtml(outbox)}</div>
        </div>
      </div>
    `;
  };

  const updateSwal = current => {
    if (typeof Swal === "undefined" || typeof Swal.getHtmlContainer !== "function") return;
    const container = Swal.getHtmlContainer();
    if (container) container.innerHTML = render(current);
    if (typeof Swal.update === "function") {
      Swal.update({ title: title(current), icon: variant(current) });
    }
  };

  const applyStatus = data => {
    state.operation_type = data.operation_type || state.operation_type;
    state.summary = data.summary || state.summary;
    state.ledger_status = data.ledger_status || state.ledger_status;
    state.outbox_status = data.outbox?.[0]?.status || state.outbox_status;
    state.progress = data.progress || progress(state);
  };

  const isFinal = current => {
    const outbox = current.outbox_status || current.outbox?.[0]?.status;
    return ["closed", "failed", "compensating", "compensated"].includes(current.ledger_status)
      || ["dispatched", "dead_letter", "cancelled"].includes(outbox);
  };

  const poll = async attempts => {
    if (!state.status_url || attempts <= 0 || isFinal(state)) return;

    try {
      const response = await fetch(state.status_url, {
        headers: { Accept: "application/json" },
        credentials: "same-origin",
      });
      if (response.ok) {
        applyStatus(await response.json());
        updateSwal(state);
      }
    } catch {
      return;
    }

    if (!isFinal(state)) {
      window.setTimeout(() => {
        void poll(attempts - 1);
      }, 1500);
    }
  };

  const showFallback = () => {
    const message = `${title(state)}: ${state.summary || "Finance operation"}`;
    if (typeof show_toastr === "function") {
      show_toastr(variant(state), message);
      return;
    }
    window.alert(message);
  };

  const show = () => {
    injectStyle();
    if (typeof Swal === "undefined" || typeof Swal.fire !== "function") {
      showFallback();
      return;
    }

    void Swal.fire({
      title: title(state),
      icon: variant(state),
      html: render(state),
      showConfirmButton: true,
      confirmButtonText: "OK",
      didOpen: () => {
        void poll(6);
      },
    });
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", show, { once: true });
  } else {
    show();
  }
})();
