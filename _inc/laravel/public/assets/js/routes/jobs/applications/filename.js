(() => {
  try {
    const inputs = Array.from(
      document.querySelectorAll('input[type="file"][data-filename]')
    );
    if (!inputs.length) return;
    inputs.forEach(inp => {
      if (inp.getAttribute("data-filename-guarded") === "true") return;
      inp.setAttribute("data-filename-guarded", "true");
      inp.addEventListener("change", () => {
        try {
          const sel = inp.getAttribute("data-filename") || "";
          const out = sel ? document.querySelector("." + sel) : null;
          if (!out) return;
          const file = inp.files && inp.files[0] ? inp.files[0] : null;
          out.textContent = file ? file.name : "";
        } catch {}
      });
    });
  } catch {}
})();
