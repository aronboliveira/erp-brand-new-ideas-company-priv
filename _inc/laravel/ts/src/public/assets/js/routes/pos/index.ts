/**
 * @fileoverview TypeScript version of public/assets/js/routes/pos/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

interface PosCartResponse {
  ok?: boolean;
  subtotal_formatted?: string;
  total_formatted?: string;
}

interface ProductItem {
  id: string | number;
  name?: string;
  price?: string | number;
  price_formatted?: string;
  add_label?: string;
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
  const q = (s: string, r: Document | Element = document): HTMLElement | null =>
    r.querySelector(s);
  const qa = (s: string, r: Document | Element = document): Element[] =>
    Array.from(r.querySelectorAll(s));

  const getLangCode = (): string =>
    (
      sessionStorage.getItem("erp-np-lang") ??
      (document.documentElement.lang || "en")
    )
      .toLowerCase()
      .replace(/_/g, "-");

  const translate = (key: string, fallback: string): string => {
    const lc = getLangCode();
    const base = lc.slice(0, 2);
    return (
      window.translations?.[lc]?.[key] ||
      window.translations?.[base]?.[key] ||
      window.translations?.en?.[key] ||
      fallback
    );
  };

  const ensureToastContainer = (): HTMLElement => {
    const wrapId = "toast-wrap-guard";
    let wrap = q("#" + wrapId);
    if (!wrap) {
      wrap = document.createElement("div");
      wrap.id = wrapId;
      wrap.className = "position-fixed top-0 end-0 p-3";
      wrap.style.zIndex = "1080";
      document.body.appendChild(wrap);
    }
    return wrap;
  };

  const toast = (msg: string, variant = "danger"): void=> {
    const wrap = ensureToastContainer();
    const node = document.createElement("div");
    node.className = `toast align-items-center text-bg-${variant} border-0`;
    node.setAttribute("role", "alert");
    node.setAttribute("aria-live", "assertive");
    node.setAttribute("aria-atomic", "true");
    node.innerHTML =
      `<div class="d-flex"><div class="toast-body">${msg}</div>` +
      `<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
    wrap.appendChild(node);
    if (window.bootstrap.Toast)
      new window.bootstrap.Toast(node, { autohide: true, delay: 3000 }).show();
    else alert(msg);
  };

  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const guardMsg = (el: HTMLElement, key: string) =>
    el.getAttribute("data-guard-msg") || translate(key, "# ERROR");

  // --- Search products
  const searchInput = q("#searchproduct");
  if (searchInput) {
    searchInput.addEventListener(
      "input",
      // eslint-disable-next-line @typescript-eslint/no-misused-promises
      async e => {
        const url = searchInput.getAttribute("data-url") ?? "#";
        if (url === "#") {
          toast(guardMsg(searchInput, "search_products_unavailable"));
          return;
        }
        const qv = (e.target as HTMLInputElement | null)?.value.trim();
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
          // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
          const data = await res.json();
          // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
          const items = Array.isArray(data)
            ? data
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            : Array.isArray(data?.items)
              // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
              ? data.items
              : [];
          if (!list) return;
          // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
          // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-assignment
          list.innerHTML = items.length
            // eslint-disable-next-line @typescript-eslint/no-unsafe-call
            ? items
                // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                .map(
                  (p: ProductItem) => `
            <div class="col-md-4 mb-2">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start">
                    <strong class="d-block text-truncate" title="${
                      p.name ?? ""
                    }">${p.name ?? "-"}</strong>
                    <span>${p.price_formatted || (p.price ?? "")}</span>
                  </div>
                  <button class="btn btn-sm btn-primary mt-2" data-action="add-product" data-id="${
                    p.id
                  }">${p.add_label ?? "Add"}</button>
                </div>
              </div>
            </div>`,
                )
                // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                .join("")
            : `<div class="col-12 text-center text-muted py-3">No products found</div>`;
        } catch {
          toast(guardMsg(searchInput, "search_products_unavailable"));
        }
      },
      { passive: true },
    );
  }

  const csrf =
    (q('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? "";

  const postForm = async (
    url: string,
    body: Record<string, string> | URLSearchParams,
  ): Promise<PosCartResponse> => {
    const form =
      body instanceof URLSearchParams ? body : new URLSearchParams(body);
    const res = await fetch(url, {
      method: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest", "X-CSRF-TOKEN": csrf },
      body: form,
    });
    if (!res.ok) throw new Error(String(res.status));
    // eslint-disable-next-line @typescript-eslint/no-unsafe-return
    return res.json();
  };

  const updateRowTotals = (
    row: HTMLElement,
    payload: PosCartResponse,
  ): void => {
    if (payload.subtotal_formatted) {
      const s = row.querySelector(".subtotal");
      if (s) s.textContent = payload.subtotal_formatted;
    }
    if (payload.total_formatted) {
      const totalDom = q("#displaytotal");
      if (totalDom) totalDom.textContent = payload.total_formatted;
      qa(".totalamount").forEach((el: Element): void => {
        el.textContent = payload.total_formatted ?? "";
      });
    }
  };

  const changeQty = async (input: HTMLInputElement, delta = 0): Promise<void> => {
    const row = input.closest("tr[data-product-id]");
    const url = input.getAttribute("data-url") ?? "#";
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
    input.value = String(next);
    try {
      const data = await postForm(url, {
        id: id ?? "",
        quantity: String(next),
      });
      if (!data.ok) throw new Error("bad");
      if (row) updateRowTotals(row as HTMLElement, data);
    } catch {
      toast(guardMsg(input, "update_cart_unavailable"));
    }
  };

  qa("#tbody").forEach(tbody => {
    tbody.addEventListener("click", (e: Event) => {
      const target = e.target as HTMLElement | null;
      if (!target) return;
      const minus = target.closest(".minus");
      const plus = target.closest(".plus");
      if (!minus && !plus) return;
      const input = target
        .closest("tr")
        ?.querySelector('input[name="quantity"]') as HTMLInputElement | null;
      if (input) void changeQty(input, minus ? -1 : 1);
    });
    tbody.addEventListener("change", (e: Event) => {
      const target = e.target as HTMLElement | null;
      if (!target) return;
      const input = target.closest(
        'input[name="quantity"]',
      ) as HTMLInputElement | null;
      if (input) void changeQty(input, 0);
    });
  });

  const bindConfirm = (
    anchor: HTMLElement,
    modalId: string,
    okId: string,
  ): void => {
    if (anchor.dataset.bound === "1") return;
    anchor.dataset.bound = "1";
    anchor.addEventListener("click", (ev: Event) => {
      ev.preventDefault();
      const targetFormId = anchor.getAttribute("data-confirm-yes");
      const form = targetFormId
        ? (document.getElementById(targetFormId) as HTMLFormElement | null)
        : null;
      if (!form) {
        toast(guardMsg(anchor, "remove_from_cart_unavailable"));
        return;
      }
      const parts = (anchor.getAttribute("data-confirm") ?? "").split("|");
      const title = parts[0] || "Are you sure?";
      const body = parts[1] || "";

      if (window.bootstrap.Modal) {
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
          const firstChild = tpl.firstChild;
          if (firstChild) document.body.appendChild(firstChild);
          modal = q("#" + modalId);
        }
        if (modal) {
          const modalTitle = modal.querySelector(".modal-title");
          const modalBody = modal.querySelector(".modal-body");
          if (modalTitle) modalTitle.textContent = title;
          if (modalBody) modalBody.textContent = body;
        }
        const yes = q("#" + okId);
        if (yes) {
          const handler = (): void => {
            yes.removeEventListener("click", handler);
            form.submit();
          };
          yes.addEventListener("click", handler);
        }
        if (modal) new window.bootstrap.Modal(modal).show();
      } else {
        if (confirm(`${title}\n${body}`)) form.submit();
      }
    });
  };

  qa(".bs-pass-para-pos").forEach(a => {
    bindConfirm(a as HTMLElement, "confirm-modal-row", "confirm-row-yes");
  });
  const payBtn = q("#btn-pur button.btn-primary[data-url]");
  if (payBtn) {
    payBtn.addEventListener("click", (e: Event) => {
      const u = payBtn.getAttribute("data-url") ?? "#";
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

export {};
