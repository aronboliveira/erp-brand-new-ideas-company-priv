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
          const targetId = inp.getAttribute("data-preview-target");
          const img = targetId ? document.getElementById(targetId) : null;
          const file = inp.files && inp.files[0] ? inp.files[0] : null;
          if (!img || !file) return;
          img.src = URL.createObjectURL(file);
        } catch {}
      });
    });
  } catch {}
})();
