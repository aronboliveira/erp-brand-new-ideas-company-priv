// assets/js/routes/expenses/create.js
(() => {
  try {
    const qs = (s, r = document) => r.querySelector(s);
    const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));
    const once = (el, attr) => {
      if (!el) return false;
      if (el.getAttribute(attr) === "true") return false;
      el.setAttribute(attr, "true");
      return true;
    };
    const toast = msg => {
      const hasBootstrap = !!(
        document.querySelector('link[href*="bootstrap"]') && window.bootstrap
      );
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
      }
      if (hasBootstrap) {
        const t = document.createElement("div");
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = msg;
        t.appendChild(b);
        container.appendChild(t);
        bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(msg);
      }
    };

    const guardForm = fm => {
      if (!fm) return;
      if (!once(fm, "data-submit-guarded")) return;
      fm.addEventListener("submit", e => {
        try {
          const action = (fm.getAttribute("action") ?? "#").trim();
          const url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
          if (url !== "#" && action !== "#") return;
          e.preventDefault();
          const msg =
            fm.getAttribute("data-guard-msg") ??
            "Store expense route is unavailable. Please contact technical support or your domain administrator.";
          toast(msg);
          fm.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    };

    const guardLink = a => {
      if (!a) return;
      if (!once(a, "data-listener-active")) return;
      a.addEventListener("click", e => {
        try {
          const href = (a.getAttribute("href") ?? "#").trim();
          const url = (a.getAttribute("data-url") ?? href ?? "#").trim();
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          const msg =
            a.getAttribute("data-guard-msg") ??
            "Route is unavailable. Please contact technical support or your domain administrator.";
          toast(msg);
          a.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    };

    const togglePayeeBlocks = () => {
      const type =
        qsa('input[name="type"]').find(r => r.checked)?.value || "employee";
      const emp = qs(".employee");
      const cus = qs(".customer");
      const ven = qs(".vendor");
      if (emp) emp.classList.toggle("d-none", type !== "employee");
      if (cus) cus.classList.toggle("d-none", type !== "customer");
      if (ven) ven.classList.toggle("d-none", type !== "vendor");
    };

    const fetchDetail = async (selectEl, detailSel) => {
      try {
        if (!selectEl) return;
        const url = (selectEl.getAttribute("data-url") ?? "#").trim();
        const guard =
          selectEl.getAttribute("data-guard-msg") ?? "Endpoint unavailable.";
        const id = selectEl.value;
        if (!id) return;
        if (url === "#") {
          toast(guard);
          return;
        }
        const token = (qs("#token")?.value ?? "").trim();
        const res = await fetch(url, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": token,
          },
          body: JSON.stringify({ id }),
        });
        if (!res.ok) throw new Error("Bad response");
        const data = await res.json().catch(() => ({}));
        const detail = qs(detailSel);
        if (!detail) return;
        detail.replaceChildren();
        const _serverMarkup = data?.html ?? "";
        if (_serverMarkup) {
          const _tmpl = document.createElement("template");
          _tmpl.innerHTML = _serverMarkup;
          detail.append(_tmpl.content);
        }
        detail.classList.toggle(
          "d-none",
          !(data?.html && String(data.html).trim().length),
        );
      } catch (err) {}
    };

    const toNum = v => {
      const n = parseFloat(String(v).replace(/,/g, "").trim());
      return isFinite(n) ? n : 0;
    };

    const recalcTable = () => {
      let subTotal = 0;
      let totalDiscount = 0;
      let totalTax = 0;
      let totalAmount = 0;

      qsa("tbody[data-repeater-item]").forEach(tbody => {
        const row1 = tbody.querySelector("tr:nth-child(1)");
        const row2 = tbody.querySelector("tr:nth-child(2)");
        if (row1) {
          const qty = toNum(qs(".quantity", row1)?.value);
          const price = toNum(qs(".price", row1)?.value);
          const disc = toNum(qs(".discount", row1)?.value);
          const taxRate = toNum(qs(".itemTaxRate", row1)?.value);
          const line = qty * price;
          const taxAmt = (line - disc) * (taxRate / 100);
          const amt = line - disc + taxAmt;

          subTotal += line;
          totalDiscount += disc;
          totalTax += taxAmt;
          totalAmount += amt;

          const amtCell = qs(".amount", row1);
          if (amtCell) amtCell.textContent = amt.toFixed(2);
        }
        if (row2) {
          const accInput = qs(".accountAmount", row2);
          const accCell = qs(".accountamount", row2);
          const accVal = toNum(accInput?.value);
          if (accCell) accCell.textContent = accVal.toFixed(2);
          totalAmount += accVal;
        }
      });

      const fmt = n => n.toFixed(2);
      const subEl = qs(".subTotal");
      const discEl = qs(".totalDiscount");
      const taxEl = qs(".totalTax");
      const totEl = qs(".totalAmount");
      const totHidden = qs("input.totalAmount");

      if (subEl) subEl.textContent = fmt(subTotal);
      if (discEl) discEl.textContent = fmt(totalDiscount);
      if (taxEl) taxEl.textContent = fmt(totalTax);
      if (totEl) totEl.textContent = fmt(totalAmount);
      if (totHidden) totHidden.value = fmt(totalAmount);
    };

    const bindRow = container => {
      if (!container) return;
      const inputs = qsa(".quantity, .price, .discount", container);
      inputs.forEach(inp => {
        inp.addEventListener("input", recalcTable);
        inp.addEventListener("change", recalcTable);
      });
      const acc = qs(
        ".accountAmount",
        container.closest("tbody[data-repeater-item]"),
      );
      if (acc) {
        acc.addEventListener("input", recalcTable);
        acc.addEventListener("change", recalcTable);
      }
      const itemSel = qs(".item", container);
      if (itemSel && once(itemSel, "data-item-bound")) {
        itemSel.addEventListener("change", async () => {
          const url = (itemSel.getAttribute("data-url") ?? "#").trim();
          const guard =
            itemSel.getAttribute("data-guard-msg") ?? "Endpoint unavailable.";
          const id = itemSel.value;
          const tbody = container.closest("tbody[data-repeater-item]");
          if (!tbody || !id) return;
          if (url === "#") {
            toast(guard);
            return;
          }
          const token = (qs("#token")?.value ?? "").trim();
          try {
            const res = await fetch(url, {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": token,
              },
              body: JSON.stringify({ id }),
            });
            const data = await res.json().catch(() => ({}));
            const unit = qs(".unit", tbody);
            const price = qs(".price", tbody);
            const taxesBox = qs(".taxes", tbody);
            const taxRate = qs(".itemTaxRate", tbody);
            const taxPrice = qs(".itemTaxPrice", tbody);

            if (unit && data?.unit !== undefined)
              unit.textContent = String(data.unit ?? "");
            if (price && data?.price !== undefined)
              price.value = String(data.price ?? "");
            if (taxesBox && data?.taxesHtml !== undefined)
              // SECURITY: Use safe HTML insertion instead of innerHTML
              safeSethtmlContent(taxesBox, String(data.taxesHtml ?? ""));
            if (taxRate && data?.taxRate !== undefined)
              taxRate.value = String(data.taxRate ?? "");
            if (taxPrice && data?.taxPrice !== undefined)
              taxPrice.value = String(data.taxPrice ?? "");
          } catch (err) {}
          recalcTable();
        });
      }
    };

    const fm = document.getElementById("expense-create-form");
    guardForm(fm);
    guardLink(document.getElementById("expense-cancel-link"));

    qsa('input[name="type"]').forEach(r => {
      r.addEventListener("change", togglePayeeBlocks);
    });
    togglePayeeBlocks();

    const empSel = document.getElementById("employee");
    const cusSel = document.getElementById("customer");
    const venSel = document.getElementById("vendor");
    if (empSel && once(empSel, "data-bound"))
      empSel.addEventListener("change", () =>
        fetchDetail(empSel, "#employee_detail"),
      );
    if (cusSel && once(cusSel, "data-bound"))
      cusSel.addEventListener("change", () =>
        fetchDetail(cusSel, "#customer_detail"),
      );
    if (venSel && once(venSel, "data-bound"))
      venSel.addEventListener("change", () =>
        fetchDetail(venSel, "#vendor_detail"),
      );

    qsa("tbody[data-repeater-item] tr:nth-child(1)").forEach(row =>
      bindRow(row),
    );
    qsa("[data-repeater-create]").forEach(btn => {
      btn.addEventListener("click", () => {
        setTimeout(() => {
          qsa("tbody[data-repeater-item]").forEach(tb => {
            const row = tb.querySelector("tr:nth-child(1)");
            bindRow(row);
          });
        }, 0);
      });
    });

    recalcTable();
  } catch (err) {}
})();
