/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/boards/convert.js
 * @generated from original JavaScript - manual review recommended
 * @module convert
 */

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const Q = (s: string) => document.querySelector(s),
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    QA = (s: string) => Array.from(document.querySelectorAll(s)),
    G = (): void => {
      try {
        QA('[data-bs-toggle="tooltip"]').forEach((el: Element): void => {
          try {
            bootstrap.Tooltip.getOrCreateInstance(el);
          } catch (_) {
            console.error(`[convert] Error:`, _);
          }
        });
      } catch (_) {
        console.error(`[convert] Error:`, _);
      }
    };
  const L = (): void => {
    QA('input[type="file"][data-filename]').forEach(el => {
      const i = el as HTMLInputElement,
        c = i.getAttribute("data-filename"),
        o = c ? Q(`.${c}`) : null;
      const set = (): void => {
        if (o) o.textContent = i.files?.[0]?.name ?? "";
      };
      i.addEventListener("change", set);
      set();
    });
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const N = (d: unknown) => {
    if (!d) return [];
    if (Array.isArray(d))
      return d
        .map(x =>
          typeof x === "object"
            ? // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
              // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-assignment
              { id: x.id ?? x.value ?? "", name: x.name ?? x.text ?? "" }
            : null,
        )
        .filter((x): x is { id: unknown; name: unknown } => x != null);
    if (typeof d === "object")
      return Object.keys(d).map(k => ({
        id: k,
        name: String((d as unknown as Record<string, unknown>)[k]),
      }));
    return [];
  };
  const P = (
    sel: HTMLSelectElement | null,
    items: unknown[],
    selId = "",
  ): void => {
    if (!sel) return;
    sel.innerHTML = "";
    const def = document.createElement("option");
    def.value = "";
    def.textContent = "Select any Designation";
    sel.appendChild(def);
    // eslint-disable-next-line @typescript-eslint/no-unsafe-call
    N(items).forEach(it => {
      const o = document.createElement("option");
      // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-member-access
      o.value = String(it.id);
      // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-member-access
      o.textContent = String(it.name);
      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
      if (selId && String(selId) === String(it.id)) o.selected = true;
      sel.appendChild(o);
    });
    try {
      const jQ = window.jQuery;
      if (jQ?.fn.select2 && jQ(sel).data("select2"))
        jQ(sel).trigger("change.select2");
      // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    } catch (_) {
      console.error(`[convert] Error:`, _);
    }
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const C = () => {
    const d = Q("#department_id") as HTMLSelectElement | null,
      s = Q("#designation_id") as HTMLSelectElement | null;
    if (!d || !s) return;
    const url =
        (s.getAttribute("data-url") ||
          d.getAttribute("data-designation-url")) ??
        "#",
      _guard =
        (s.getAttribute("data-guard-msg") ||
          d.getAttribute("data-guard-msg")) ??
        "";
    const csrf =
      ((
        document.querySelector(
          'meta[name="csrf-token"]',
        ) as HTMLMetaElement | null
      )?.content ||
        (
          document.querySelector(
            'input[name="_token"]',
            // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
          ) as HTMLInputElement | null
        )?.value) ??
      "";
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const load = async (id: string) => {
      if (!id) {
        P(s, []);
        return;
      }
      if (!url || url.trim() === "#" || /^javascript:/i.test(url)) return;
      const payload = { department_id: id };
      // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
      const doFetch = () =>
        fetch(url, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            ...(csrf ? { "X-CSRF-TOKEN": csrf } : {}),
          },
          body: JSON.stringify(payload),
        }).then(r => (r.ok ? r.json().catch(() => ({})) : Promise.reject()));
      const doAjax = (): Promise<unknown> => {
        if (typeof $ === "undefined") return Promise.reject();
        return Promise.resolve(
          $.ajax({
            url,
            method: "POST",
            headers: csrf ? { "X-CSRF-TOKEN": csrf } : {},
            data: payload,
          }),
        );
      };
      try {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
        const res = await (typeof fetch === "function"
          ? doFetch().catch(doAjax)
          : doAjax().catch(doFetch));
        // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-argument
        P(s, res?.data ?? res ?? []);
      } catch (_) {
        console.error(`[convert] Error:`, _);
      }
    };
    // eslint-disable-next-line @typescript-eslint/no-misused-promises
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
