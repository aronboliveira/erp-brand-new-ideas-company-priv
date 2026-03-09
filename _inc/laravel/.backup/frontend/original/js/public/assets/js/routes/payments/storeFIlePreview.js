(() => {
  try {
    const inputs = Array.from(
      document.querySelectorAll('input[type="file"][data-preview-target]')
    );
    if (!inputs.length) return;
    inputs.forEach(inp => {
      if (inp.getAttribute("data-preview-guarded") === "true") return;
      inp.setAttribute("data-preview-guarded", "true");
      inp.addEventListener("change", () => {
        try {
          const targetSel = inp.getAttribute("data-preview-target");
          const target = targetSel ? document.querySelector(targetSel) : null;
          const file = inp.files && inp.files[0] ? inp.files[0] : null;
          if (!target || !file) return;
          const url = URL.createObjectURL(file);
          target.setAttribute("src", url);
        } catch {}
      });
    });
  } catch {}
})();
