/** @requires ERPGuard */
(() => {
  const { guard } = window.ERPBootstrap.require("ERPGuard");
  if (!guard) return;
  const q = (s, r = document) => r.querySelector(s);
  const qa = (s, r = document) => Array.from(r.querySelectorAll(s));

  const getLangCode = () =>
    (
      sessionStorage.getItem("erp-np-lang") ||
      document.documentElement.lang ||
      "en"
    )
      .toLowerCase()
      .replace(/_/g, "-");

  const translate = (key, fallback) => {
    const lc = getLangCode();
    const base = lc.slice(0, 2);
    return (
      (window.translations?.[lc] && window.translations[lc][key]) ||
      (window.translations?.[base] && window.translations[base][key]) ||
      (window.translations?.en && window.translations.en[key]) ||
      fallback
    );
  };

  const toast = (msg, variant = "danger") => guard.showToast(msg, variant);

  const guardMsg = (el, key) =>
    el?.getAttribute("data-guard-msg") || translate(key, "# ERROR");

  // --- Search products
  const searchInput = q("#searchproduct");
  if (searchInput) {
    searchInput.addEventListener(
      "input",
      async e => {
        const url = searchInput.getAttribute("data-url") || "#";
        if (url === "#") {
          toast(guardMsg(searchInput, "search_products_unavailable"));
          return;
        }
        const qv = e.target.value.trim();
        const list = q("#product-listing");
        if (!qv) {
          if (list) list.innerHTML = "";
          return;
        }
        try {
          const res = await fetch(url + "?q=" + encodeURIComponent(qv), {
            headers: { "X-Requested-With": "XMLHttpRequest" },
          });
          if (!res.ok) throw new Error(String(res.status));
          const data = await res.json();
          const items = Array.isArray(data)
            ? data
            : Array.isArray(data?.items)
              ? data.items
              : [];
          if (!list) return;
          list.innerHTML = items.length
            ? items
                .map(
                  p => `
            <div class="col-md-4 mb-2">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start">
                    <strong class="d-block text-truncate" title="${
                      p.name || ""
                    }">${p.name || "-"}</strong>
                    <span>${p.price_formatted || p.price || ""}</span>
                  </div>
                  <button class="btn btn-sm btn-primary mt-2" data-action="add-product" data-id="${
                    p.id
                  }">${p.add_label || "Add"}</button>
                </div>
              </div>
            </div>`,
                )
                .join("")
            : `<div class="col-12 text-center text-muted py-3">No products found</div>`;
        } catch {
          toast(guardMsg(searchInput, "search_products_unavailable"));
        }
      },
      { passive: true },
    );
  }

  const csrf = q('meta[name="csrf-token"]')?.content || "";

  const postForm = async (url, body) => {
    const form =
      body instanceof URLSearchParams ? body : new URLSearchParams(body);
    const res = await fetch(url, {
      method: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest", "X-CSRF-TOKEN": csrf },
      body: form,
    });
    if (!res.ok) throw new Error(String(res.status));
    return res.json();
  };

  const updateRowTotals = (row, payload) => {
    if (payload?.subtotal_formatted) {
      const s = row.querySelector(".subtotal");
      if (s) s.textContent = payload.subtotal_formatted;
    }
    if (payload?.total_formatted) {
      const totalDom = q("#displaytotal");
      if (totalDom) totalDom.textContent = payload.total_formatted;
      qa(".totalamount").forEach(
        el => (el.textContent = payload.total_formatted),
      );
    }
  };

  const changeQty = async (input, delta = 0) => {
    const row = input.closest("tr[data-product-id]");
    const url = input.getAttribute("data-url") || "#";
    if (url === "#") {
      toast(guardMsg(input, "update_cart_unavailable"));
      return;
    }
    const id = input.getAttribute("data-id");
    const current = Number(input.value || 1);
    const next = Math.max(1, current + delta);
    if (next === current && delta === 0) {
      toast(translate("nothing_to_update", "Nothing to update."), "secondary");
      return;
    }
    input.value = next;
    try {
      const data = await postForm(url, { id, quantity: String(next) });
      if (!data?.ok) throw new Error("bad");
      if (row) updateRowTotals(row, data);
    } catch {
      toast(guardMsg(input, "update_cart_unavailable"));
    }
  };

  qa("#tbody").forEach(tbody => {
    tbody.addEventListener("click", e => {
      const minus = e.target.closest(".minus");
      const plus = e.target.closest(".plus");
      if (!minus && !plus) return;
      const input = e.target
        .closest("tr")
        ?.querySelector('input[name="quantity"]');
      if (input) changeQty(input, minus ? -1 : 1);
    });
    tbody.addEventListener("change", e => {
      const input = e.target.closest('input[name="quantity"]');
      if (input) changeQty(input, 0);
    });
  });

  const bindConfirm = (anchor, modalId, okId) => {
    if (anchor.dataset.bound === "1") return;
    anchor.dataset.bound = "1";
    anchor.addEventListener("click", ev => {
      ev.preventDefault();
      const targetFormId = anchor.getAttribute("data-confirm-yes");
      const form = targetFormId ? document.getElementById(targetFormId) : null;
      if (!form) {
        toast(guardMsg(anchor, "remove_from_cart_unavailable"));
        return;
      }
      const parts = (anchor.getAttribute("data-confirm") || "").split("|");
      const title = parts[0] || "Are you sure?";
      const body = parts[1] || "";

      if (window.bootstrap?.Modal) {
        let modal = q("#" + modalId);
        if (!modal) {
          const tpl = document.createElement("div");
          tpl.innerHTML =
            `<div class="modal fade" id="${modalId}" tabindex="-1">` +
            `<div class="modal-dialog"><div class="modal-content">` +
            `<div class="modal-header"><h5 class="modal-title"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>` +
            `<div class="modal-body"></div>` +
            `<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>` +
            `<button type="button" class="btn btn-danger" id="${okId}">OK</button></div>` +
            `</div></div></div>`;
          document.body.appendChild(tpl.firstChild);
          modal = q("#" + modalId);
        }
        modal.querySelector(".modal-title").textContent = title;
        modal.querySelector(".modal-body").textContent = body;
        const yes = q("#" + okId);
        const handler = () => {
          yes.removeEventListener("click", handler);
          form.submit();
        };
        yes.addEventListener("click", handler);
        new window.bootstrap.Modal(modal).show();
      } else {
        if (confirm(`${title}\n${body}`)) form.submit();
      }
    });
  };

  qa(".bs-pass-para-pos").forEach(a =>
    bindConfirm(a, "confirm-modal-row", "confirm-row-yes"),
  );
  const payBtn = q("#btn-pur button.btn-primary[data-url]");
  if (payBtn) {
    payBtn.addEventListener("click", e => {
      const u = payBtn.getAttribute("data-url") || "#";
      if (u === "#") {
        e.preventDefault();
        toast(guardMsg(payBtn, "pos_create_unavailable"));
      }
    });
  }
  const emptyBtn = q(".btn-empty .btn-danger[data-confirm-yes]");
  if (emptyBtn)
    bindConfirm(emptyBtn, "confirm-modal-empty", "confirm-empty-yes");
})();
