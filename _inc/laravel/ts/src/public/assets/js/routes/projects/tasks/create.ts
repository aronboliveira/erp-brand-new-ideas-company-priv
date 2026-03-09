/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/tasks/create.js
 * @generated from original JavaScript - manual review recommended
 * @module create
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

((): void => {
  try {
    const f = document.getElementById("create-project-task-form");
    if (!f) return;
    if (f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");

    const resolved = f.getAttribute("data-resolved-action") ?? "#";
    if (
      f.hasAttribute("action") &&
      (f.getAttribute("action") === "#" || !f.getAttribute("action")) &&
      resolved !== "#"
    )
      f.setAttribute("action", resolved);

    const assigneesInput = document.getElementById(
      "project-task-assignees",
    ) as HTMLInputElement | null;
    if (!assigneesInput) return;
    const toSet = new Set(
      (assigneesInput.value ?? "")
        .split(",")
        .map((s: string) => s.trim())
        .filter(Boolean),
    );

    document.querySelectorAll(".add_usr").forEach(wrap => {
      try {
        if (!wrap || wrap.getAttribute("data-listener-active") === "true")
          return;
        wrap.setAttribute("data-listener-active", "true");

        wrap.addEventListener("click", (_e: Event) => {
          try {
            const id = wrap.getAttribute("data-id") ?? "";
            if (id === "") return;

            if (toSet.has(id)) {
              toSet.delete(id);
            } else {
              toSet.add(id);
            }
            if (assigneesInput)
              assigneesInput.value = Array.from(toSet).join(",");

            const icon = document.getElementById(`usr_icon_${id}`),
              txt = document.getElementById(`usr_txt_${id}`);
            if (icon) {
              icon.classList.toggle("ti-plus", !toSet.has(id));
              icon.classList.toggle("ti-check", toSet.has(id));
            }
            if (txt) {
              const added = toSet.has(id),
                current = txt.textContent,
                addLabel = "Add",
                addedLabel = "Added";
              if (added && current !== addedLabel) txt.textContent = addedLabel;
              if (!added && current !== addLabel) txt.textContent = addLabel;
            }
          } catch (err) {
            if (
              window.location.hostname === "localhost" ||
              window.location.hostname === "127.0.0.1"
            )
              console.error(
                "[assets/js/routes/projects/tasks/create.js] add_usr click error:",
                // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                (err as Error)?.constructor?.name ?? "Error",
                // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                (err as Error)?.message ?? "Unknown error",
              );
          }
        });
      } catch (err) {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error(
            "[assets/js/routes/projects/tasks/create.js] bind add_usr error:",
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            (err as Error)?.constructor?.name ?? "Error",
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            (err as Error)?.message ?? "Unknown error",
          );
      }
    });

    const ta = f.querySelector<HTMLTextAreaElement>(
      'textarea[data-toggle="autosize"]',
    );
    if (ta && ta.getAttribute("data-autosize-active") !== "true") {
      ta.setAttribute("data-autosize-active", "true");
      const auto = (): void => {
        try {
          ta.style.height = "auto";
          Object.assign(ta.style, {
            overflowY: "hidden",
            height: `${ta.scrollHeight}px`,
          });
        } catch (_) {
          console.error(`[create] Error:`, _);
        }
      };
      ["input", "change"].forEach(ev => {
        ta.addEventListener(ev, auto);
      });
      auto();
    }

    f.addEventListener("submit", (e: Event) => {
      try {
        const action = f.getAttribute("action") ?? "#";
        if (action !== "#") return;
        e.preventDefault();

        const msg =
          f.getAttribute("data-guard-msg") ??
          "Create project task route is unavailable. Please contact technical support or your domain administrator.";
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }

        const bsLink = document.querySelector('link[href*="bootstrap"]');
        if (bsLink && window.bootstrap.Toast) {
          const toast = document.createElement("div");
          toast.className = "toast";
          for (const [k, v] of Object.entries({
            role: "alert",
            "aria-live": "assertive",
            "aria-atomic": "true",
          }))
            toast.setAttribute(k, v);

          const body = document.createElement("div");
          body.className = "toast-body";
          body.textContent = msg;

          toast.appendChild(body);
          container.appendChild(toast);

          try {
            window.bootstrap.Toast.getOrCreateInstance(toast).show();
          } catch (err) {
            if (
              window.location.hostname === "localhost" ||
              window.location.hostname === "127.0.0.1"
            )
              console.error(
                "[assets/js/routes/projects/tasks/create.js] Bootstrap toast instantiation error:",
                // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                (err as Error)?.constructor?.name ?? "Error",
                // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                (err as Error)?.message ?? "Unknown error",
              );
            alert(msg);
          }
        } else {
          alert(msg);
        }

        f.setAttribute("data-failed-route", "true");
      } catch (err) {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error(
            "[assets/js/routes/projects/tasks/create.js] Submit handler error:",
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            (err as Error)?.constructor?.name ?? "Error",
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            (err as Error)?.message ?? "Unknown error",
          );
      }
    });
  } catch (error) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error(
        "[assets/js/routes/projects/tasks/create.js] Initialization error:",
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
        (error as Error)?.constructor?.name ?? "Error",
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
        (error as Error)?.message ?? "Unknown error",
      );
  }
})();

export {};
