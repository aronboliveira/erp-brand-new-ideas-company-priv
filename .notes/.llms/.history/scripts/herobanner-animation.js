(() => {
  try {
    const elements = Array.from(document.querySelectorAll("g")).filter(
      e =>
        getComputedStyle(e).transform ===
        "matrix(0.935871, 0, 0, 0.935871, 1707.63, 579.304)"
    );
    elements.forEach(element => {
      if (!element) return;
      const id = element.id,
        classList = Array.from(element.classList);
      Array.from(document.styleSheets).forEach(sheet => {
        try {
          const rules = Array.from(sheet.cssRules || sheet.rules || []);
          rules.forEach(rule => {
            try {
              if (!rule.selectorText) return;
              const selectors = rule.selectorText.split(",").map(s => s.trim());
              const matches = selectors.some(selector => {
                if (id && selector.includes(`#${id}`)) return true;
                return classList.some(cls => selector.includes(`.${cls}`));
              });
              if (!matches) return;
              if (rule.style.opacity && rule.style.opacity !== "0") {
                rule.style.setProperty("opacity", "0", "important");
              }
              if (rule.style.visibility && rule.style.visibility !== "hidden") {
                rule.style.setProperty("visibility", "hidden", "important");
              }
            } catch {}
          });
        } catch {}
      });
      element.style.setProperty("opacity", "0", "important");
      element.style.setProperty("visibility", "hidden", "important");
      const svg = element.closest("svg");
      if (svg) {
        const closestIsHeroBanner =
          svg?.classList.contains("logo_herobanner_animated") ||
          svg?.id?.includes("_herobanner");
        svg.style.clipPath = closestIsHeroBanner
          ? "polygon(0% 0%, 100% 0%, 15% 25%, 20% 100%, 0% 100%)"
          : "polygon(0% 0%, 100% 0%, 100% 86%, 75% 100%, 61% 90%, 0% 100%)";
        if (id || closestIsHeroBanner) {
          Array.from(document.styleSheets).some(sheet => {
            try {
              closestIsHeroBanner
                ? sheet.insertRule(
                    `svg:has(#${id})${
                      svg.id?.includes("_herobanner")
                        ? ", .logo_herobanner_animated"
                        : ""
                    } { clip-path: polygon(0% 0%, 100% 0%, 15% 25%, 20% 100%, 0% 100%) !important }`,
                    sheet.cssRules.length
                  )
                : sheet.insertRule(
                    `svg:has(#${id}) { clip-path: polygon(0% 0%, 100% 0%, 100% 86%, 75% 100%, 61% 90%, 0% 100%) !important }`,
                    sheet.cssRules.length
                  );
              return true;
            } catch {
              return false;
            }
          });
        }
      }
    });
  } catch {}
})();
