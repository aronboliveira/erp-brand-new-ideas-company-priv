/**
 * @fileoverview TypeScript version of public/assets/js/routes/ai/generate/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

((): void => {
  try {
    const f = document.getElementById("ai-template-form");
    const btn = document.getElementById("ai-generate-btn");
    const desc = document.getElementById(
      "ai-description",
    ) as HTMLTextAreaElement | null;

    if (!f || !btn || !desc) return;
    if (btn.getAttribute("data-listener-active") === "true") return;
    btn.setAttribute("data-listener-active", "true");

    const showNotice = (msg: string): void => {
      try {
        if (!msg) return;
        const bsLink = document.querySelector('link[href*="bootstrap"]');
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        if (bsLink && window.bootstrap.Toast) {
          const toast = document.createElement("div");
          toast.className = "toast";
          for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toast.setAttribute(k, v);
          const body = document.createElement("div");
          body.className = "toast-body";
          body.textContent = msg;
          toast.appendChild(body);
          container.appendChild(toast);
          window.bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
          alert(msg);
        }
      } catch (_) {
        alert(msg);
      }
    };

    btn.addEventListener("click", (e: Event) => {
      try {
        e.preventDefault();

        const selected = f.querySelector(
          'input.template_name[type="radio"]:checked',
        );
        if (!selected) {
          const msg =
            btn.getAttribute("data-msg-select-template") ??
            "Please select a template first.";
          showNotice(msg);
          return;
        }

        const templateId = selected.getAttribute("value") ?? "";
        const templateName = selected.getAttribute("data-name") ?? "";
        const language = (
          (document.getElementById("language") as HTMLSelectElement | null)
            ?.value ?? ""
        ).toString();
        const tone = (
          (f.querySelector('select[name="tone"]') as HTMLSelectElement | null)
            ?.value ?? ""
        ).toString();
        const creativity = (
          (document.getElementById("ai_creativity") as HTMLInputElement | null)
            ?.value ?? ""
        ).toString();
        const num = (
          (document.getElementById("num_of_result") as HTMLInputElement | null)
            ?.value ?? ""
        ).toString();
        const maxLen = (
          (
            f.querySelector(
              'input[name="result_length"]',
            ) as HTMLInputElement | null
          )?.value ?? ""
        ).toString();

        const payload = {
          templateId,
          templateName,
          language,
          tone,
          creativity,
          num,
          maxLen,
        };

        const evt = new CustomEvent("ai:generate", { detail: payload });
        document.dispatchEvent(evt);

        const hint = `[ready] ${
          templateName
        } • lang=${language} • tone=${tone} • creativity=${creativity} • results=${num} • maxLen=${maxLen}`;
        if (!desc.value || desc.value.trim().length === 0) {
          desc.value = hint;
        }
      } catch (err) {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error(
            "[assets/js/routes/aiTemplates/generate.js] Click handler error:",
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            err?.constructor?.name ?? "Error",
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            err?.message ?? "Unknown error",
          );
      }
    });
  } catch (error) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error(
        "[assets/js/routes/aiTemplates/generate.js] Initialization error:",
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
        error?.constructor?.name ?? "Error",
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
        error?.message ?? "Unknown error",
      );
  }
})();

export {};
