/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/boards/convert.js
 * @generated from original JavaScript - manual review recommended
 * @module convert
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const Q = s => document.querySelector(s),
    QA = s => Array.from(document.querySelectorAll(s)),
    G = (): void => {
      try {
        QA('[data-bs-toggle="tooltip"]').forEach((el: Element): void => {
          try {
            bootstrap.Tooltip.getOrCreateInstance(el);
          } catch (_) {}
        });
      } catch (_) {}
    };
  const L = (): void => {
    QA('input[type="file"][data-filename]').forEach(i => {
      const c = i.getAttribute("data-filename");
      const o = c ? Q(`.${c}`) : null;
      const set = (): void => {
        if (o) o.textContent = i.files?.[0]?.name ?? "";
      };
      i.addEventListener("change", set);
      set();
    });
  };
  const N = d => {
    if (!d) return [];
    if (Array.isArray(d))
      return d
        .map(x =>
          typeof x === "object"
            ? { id: x.id ?? x.value ?? "", name: x.name ?? x.text ?? "" }
            : null
        )
        .filter(Boolean);
    if (typeof d === "object")
      return Object.keys(d).map(k => ({ id: k, name: String(d[k]) }));
    return [];
  };
  const P = (sel, items, selId = "") => {
    if (!sel) return;
    sel.innerHTML = "";
    const def = document.createElement("option");
    def.value = "";
    def.textContent = "Select any Designation";
    sel.appendChild(def);
    N(items).forEach(it => {
      const o = document.createElement("option");
      o.value = it.id;
      o.textContent = it.name;
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (selId && String(selId) === String(it.id)) o.selected = true;
      sel.appendChild(o);
    });
    try {
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
      if (window.jQuery.fn.select2 && window.jQuery(sel).data("select2"))
        window.jQuery(sel).trigger("change.select2");
    } catch (_) {}
  };
  const C = (): void => {
    const d = Q("#department_id"),
      s = Q("#designation_id");
    if (!d || !s) return;
    const url =
      s.getAttribute("data-url") ||
      d.getAttribute("data-designation-url") ?? "#";
    const guard =
      s.getAttribute("data-guard-msg") ||
      d.getAttribute("data-guard-msg") ?? "";
    const csrf =
      document.querySelector('meta[name="csrf-token"]')?.content ||
      document.querySelector('input[name="_token"]')?.value ?? "";
    const load = async id => {
      if (!id) {
        P(s, []);
        return;
      }
      if (!url || url.trim() === "#" || /^javascript:/i.test(url)) return;
      const payload = { department_id: id };
      const doFetch = () =>
        fetch(url, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            ...(csrf ? { "X-CSRF-TOKEN": csrf } : {}),
          },
          body: JSON.stringify(payload),
        }).then(r => (r.ok ? r.json().catch(() => ({})) : Promise.reject()));
      const doAjax = (): void => {
        if (typeof $ === "undefined") return Promise.reject();
        return $.ajax({
          url,
          method: "POST",
          headers: csrf ? { "X-CSRF-TOKEN": csrf } : {},
          data: payload,
        });
      };
      try {
        const res = await (typeof fetch === "function"
          ? doFetch().catch(doAjax)
          : doAjax().catch(doFetch));
        P(s, res?.data ?? res ?? []);
      } catch (_) {}
    };
    d.addEventListener("change", () => load(d.value));
    if (d.value) void load(d.value);
  };
  document.addEventListener("DOMContentLoaded", (): void => {
    G();
    L();
    C();
  });
})();

export {};
