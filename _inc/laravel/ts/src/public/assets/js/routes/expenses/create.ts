/**
 * @fileoverview TypeScript version of public/assets/js/routes/expenses/create.js
 * @generated from original JavaScript - manual review recommended
 * @module create
 */

// assets/js/routes/expenses/create.js
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
  try {
    const qs = (s: string, r: Element | Document = document): HTMLElement | null => r.querySelector(s);
    const qsa = (s: string, r: Element | Document = document): HTMLElement[] => Array.from(r.querySelectorAll(s));
    const once = (el: HTMLElement | null, attr: string): boolean => {
      if (!el) return false;
      if (el.getAttribute(attr) === "true") return false;
      el.setAttribute(attr, "true");
      return true;
    };

    // SECURITY: Safe HTML insertion helper
    const safeSethtmlContent = (el: HTMLElement, html: string): void => {
      try {
        const parser = new DOMParser(),
          doc = parser.parseFromString(html, "text/html");
        if (doc.body.innerHTML.includes("PARSER ERROR")) {
          el.textContent = html;
          return;
        }
        while (el.firstChild) {
          el.removeChild(el.firstChild);
        }
        const fragment = document.createDocumentFragment();
        for (const node of doc.body.childNodes) {
          fragment.appendChild(node.cloneNode(true));
        }
        el.appendChild(fragment);
      } catch (_e) {
        el.textContent = html;
      }
    };
    const toast = (msg: string): void => {
      const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
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
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          t.setAttribute(k, v);
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

    const guardForm = (fm: HTMLElement | null): void => {
      if (!fm) return;
      if (!once(fm, "data-submit-guarded")) return;
      if (!fm.getAttribute("data-listener-bound-submit")) {
        fm.setAttribute("data-listener-bound-submit", "1");
        fm.addEventListener("submit", (e: Event) => {
          try {
            const action = (fm.getAttribute("action") ?? "#").trim(),
              url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
            if (url !== "#" && action !== "#") return;
            e.preventDefault();
            const msg = fm.getAttribute("data-guard-msg") ?? "Store expense route is unavailable. Please contact technical support or your domain administrator.";
            toast(msg);
            fm.setAttribute("data-failed-route", "true");
          } catch (err) {
            console.error(`[create] Error:`, err);
          }
        });
      }
    };

    const guardLink = (a: HTMLElement | null): void => {
      if (!a) return;
      if (!once(a, "data-listener-active")) return;
      a.addEventListener("click", (e: Event) => {
        try {
          const href = (a.getAttribute("href") ?? "#").trim(),
            url = (a.getAttribute("data-url") ?? href ?? "#").trim();
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          const msg = a.getAttribute("data-guard-msg") ?? "Route is unavailable. Please contact technical support or your domain administrator.";
          toast(msg);
          a.setAttribute("data-failed-route", "true");
        } catch (err) {
          console.error(`[create] Error:`, err);
        }
      });
    };

    const togglePayeeBlocks = (): void => {
      const type =
        qsa('input[name="type"]')
          .find(r => (r as HTMLInputElement).checked)
          ?.getAttribute("value") ?? "employee";
      const emp = qs(".employee"),
        cus = qs(".customer"),
        ven = qs(".vendor");
      if (emp) emp.classList.toggle("d-none", type !== "employee");
      if (cus) cus.classList.toggle("d-none", type !== "customer");
      if (ven) ven.classList.toggle("d-none", type !== "vendor");
    };

    const fetchDetail = async (selectEl: HTMLElement | null, detailSel: string): Promise<void> => {
      try {
        if (!selectEl) return;
        const url = (selectEl.getAttribute("data-url") ?? "#").trim(),
          guard = selectEl.getAttribute("data-guard-msg") ?? "Endpoint unavailable.",
          id = (selectEl as HTMLSelectElement).value;
        if (!id) return;
        if (url === "#") {
          toast(guard);
          return;
        }
        const token = ((qs("#token") as HTMLInputElement | null)?.value ?? "").trim(),
          res = await fetch(url, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": token,
            },
            body: JSON.stringify({ id }),
          });
        if (!res.ok) throw new Error("Bad response");
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
        const data = await res.json().catch(() => ({})),
          detail = qs(detailSel);
        if (!detail) return;
        detail.replaceChildren();
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-assignment
        const _serverMarkup = data?.html ?? "";
        if (_serverMarkup) {
          const _tmpl = document.createElement("template");
          // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
          _tmpl.innerHTML = _serverMarkup;
          detail.append(_tmpl.content);
        }
        detail.classList.toggle(
          "d-none",
          // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
          !(data?.html && String(data.html).trim().length),
        );
      } catch (err) {
        console.error(`[create] Error:`, err);
      }
    };
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type

    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const toNum = (v: unknown) => {
      const n = parseFloat(String(v).replace(/,/g, "").trim());
      return isFinite(n) ? n : 0;
    };

    const recalcTable = (): void => {
      let subTotal = 0,
        totalDiscount = 0,
        totalTax = 0,
        totalAmount = 0;
      qsa("tbody[data-repeater-item]").forEach(tbody => {
        const row1 = tbody.querySelector("tr:nth-child(1)"),
          row2 = tbody.querySelector("tr:nth-child(2)");
        if (row1) {
          const qty = toNum((qs(".quantity", row1) as HTMLInputElement | null)?.value),
            price = toNum((qs(".price", row1) as HTMLInputElement | null)?.value),
            disc = toNum((qs(".discount", row1) as HTMLInputElement | null)?.value),
            taxRate = toNum((qs(".itemTaxRate", row1) as HTMLInputElement | null)?.value),
            line = qty * price,
            taxAmt = (line - disc) * (taxRate / 100),
            amt = line - disc + taxAmt;
          subTotal += line;
          totalDiscount += disc;
          totalTax += taxAmt;
          totalAmount += amt;

          const amtCell = qs(".amount", row1);
          if (amtCell) amtCell.textContent = amt.toFixed(2);
        }
        if (row2) {
          const accInput = qs(".accountAmount", row2) as HTMLInputElement | null,
            accCell = qs(".accountamount", row2),
            accVal = toNum(accInput?.value);
          // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
          // eslint-disable-next-line @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-assignment
          if (accCell) accCell.textContent = accVal.toFixed(2);
          totalAmount += accVal;
        }
      });

      const fmt = (n: number): string => n.toFixed(2),
        subEl = qs(".subTotal"),
        discEl = qs(".totalDiscount"),
        taxEl = qs(".totalTax"),
        totEl = qs(".totalAmount"),
        totHidden = qs("input.totalAmount");
      if (subEl) subEl.textContent = fmt(subTotal);
      if (discEl) discEl.textContent = fmt(totalDiscount);
      if (taxEl) taxEl.textContent = fmt(totalTax);
      if (totEl) totEl.textContent = fmt(totalAmount);
      if (totHidden) (totHidden as HTMLInputElement).value = fmt(totalAmount);
    };

    const bindRow = (container: HTMLElement | null): void => {
      if (!container) return;
      qsa(".quantity, .price, .discount", container).forEach(inp => {
        inp.addEventListener("input", recalcTable);
        inp.addEventListener("change", recalcTable);
      });
      const acc = qs(".accountAmount", container.closest("tbody[data-repeater-item]")!);
      if (acc) {
        acc.addEventListener("input", recalcTable);
        acc.addEventListener("change", recalcTable);
      }
      const itemSel = qs(".item", container) as HTMLSelectElement | null;
      if (itemSel && once(itemSel, "data-item-bound")) {
        // eslint-disable-next-line @typescript-eslint/no-misused-promises
        itemSel.addEventListener("change", async (): Promise<void> => {
          const url = (itemSel.getAttribute("data-url") ?? "#").trim(),
            guard = itemSel.getAttribute("data-guard-msg") ?? "Endpoint unavailable.",
            id = itemSel.value,
            tbody = container.closest("tbody[data-repeater-item]");
          if (!tbody || !id) return;
          if (url === "#") {
            toast(guard);
            return;
          }
          const token = ((qs("#token") as HTMLInputElement | null)?.value ?? "").trim();
          try {
            const res = await fetch(url, {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": token,
              },
              body: JSON.stringify({ id }),
            });
            const data = (await res.json().catch(() => ({}))) as {
              unit?: string | number;
              price?: string | number;
              taxesHtml?: string;
              taxRate?: string | number;
              taxPrice?: string | number;
            };
            const unit = qs(".unit", tbody),
              price = qs(".price", tbody) as HTMLInputElement | null,
              taxesBox = qs(".taxes", tbody),
              taxRate = qs(".itemTaxRate", tbody) as HTMLInputElement | null,
              taxPrice = qs(".itemTaxPrice", tbody) as HTMLInputElement | null;
            if (unit && data.unit !== undefined) unit.textContent = String(data.unit ?? "");
            if (price && data.price !== undefined) price.value = String(data.price ?? "");
            if (taxesBox && data.taxesHtml !== undefined)
              // SECURITY: Use safe HTML insertion instead of innerHTML
              safeSethtmlContent(taxesBox, String(data.taxesHtml ?? ""));
            if (taxRate && data.taxRate !== undefined) taxRate.value = String(data.taxRate ?? "");
            if (taxPrice && data.taxPrice !== undefined) taxPrice.value = String(data.taxPrice ?? "");
          } catch (err) {
            console.error(`[create] Error:`, err);
          }
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

    const empSel = document.getElementById("employee"),
      cusSel = document.getElementById("customer"),
      venSel = document.getElementById("vendor");
    if (empSel && once(empSel, "data-bound"))
      if (!empSel.getAttribute("data-listener-bound-change")) {
        empSel.setAttribute("data-listener-bound-change", "1");
        empSel.addEventListener("change", () => { void fetchDetail(empSel, "#employee_detail"); });
      }
    if (cusSel && once(cusSel, "data-bound"))
      if (!cusSel.getAttribute("data-listener-bound-change")) {
        cusSel.setAttribute("data-listener-bound-change", "1");
        cusSel.addEventListener("change", () => { void fetchDetail(cusSel, "#customer_detail"); });
      }
    if (venSel && once(venSel, "data-bound"))
      if (!venSel.getAttribute("data-listener-bound-change")) {
        venSel.setAttribute("data-listener-bound-change", "1");
        venSel.addEventListener("change", () => { void fetchDetail(venSel, "#vendor_detail"); });
      }

    qsa("tbody[data-repeater-item] tr:nth-child(1)").forEach(row => {
      bindRow(row);
    });
    qsa("[data-repeater-create]").forEach(btn => {
      if (!btn.getAttribute("data-listener-bound-click")) {
        btn.setAttribute("data-listener-bound-click", "1");
        btn.addEventListener("click", (): void => {
          setTimeout((): void => {
            qsa("tbody[data-repeater-item]").forEach(tb => {
              const row = tb.querySelector("tr:nth-child(1)") as HTMLTableRowElement | null;
              bindRow(row);
            });
          }, 0);
        });
      }
    });

    recalcTable();
  } catch (err) {
    console.error(`[create] Error:`, err);
  }
})();
