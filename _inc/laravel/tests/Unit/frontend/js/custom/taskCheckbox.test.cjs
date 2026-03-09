/**
 * Tests for taskCheckbox()
 *
 * taskCheckbox() counts checked vs total checkboxes inside #check-list,
 * calculates a percentage, writes it to .custom-label, sets the
 * #taskProgress bar width, and applies a colour class based on the value.
 *
 * Colour bands:
 *   0-15  → bg-danger
 *  16-33  → bg-warning
 *  34-70  → bg-primary
 *  71-100 → bg-success
 */

const {
  loadCustomJs,
  buildJQueryEnv,
  buildDomSkeleton,
} = require("../helpers/setup.cjs");

beforeEach(() => {
  document.body.innerHTML = "";
  document.head.innerHTML = "";
  buildJQueryEnv();
  buildDomSkeleton();
  loadCustomJs();
});

/** Helper – inject N checkboxes, mark `checked` of them as checked */
function injectCheckboxes(total, checked) {
  const container = document.getElementById("check-list");
  container.innerHTML = "";
  for (let i = 0; i < total; i++) {
    const cb = document.createElement("input");
    cb.type = "checkbox";
    if (i < checked) cb.checked = true;
    container.appendChild(cb);
  }
}

describe("taskCheckbox", () => {
  test("is defined as a global function", () => {
    expect(typeof global.taskCheckbox).toBe("function");
  });

  test("0 of 0 checkboxes → 0%, bg-danger", () => {
    // No checkboxes at all → percentage = NaN → 0
    global.taskCheckbox();
    expect(document.querySelector(".custom-label").textContent).toBe("0%");
    expect(
      document.getElementById("taskProgress").classList.contains("bg-danger"),
    ).toBe(true);
  });

  test("0 of 4 → 0%, bg-danger", () => {
    injectCheckboxes(4, 0);
    global.taskCheckbox();
    expect(document.querySelector(".custom-label").textContent).toBe("0%");
    expect(
      document.getElementById("taskProgress").classList.contains("bg-danger"),
    ).toBe(true);
  });

  test("1 of 10 → 10%, bg-danger (≤15)", () => {
    injectCheckboxes(10, 1);
    global.taskCheckbox();
    expect(document.querySelector(".custom-label").textContent).toBe("10%");
    expect(
      document.getElementById("taskProgress").classList.contains("bg-danger"),
    ).toBe(true);
  });

  test("1 of 5 → 20%, bg-warning (16-33)", () => {
    injectCheckboxes(5, 1);
    global.taskCheckbox();
    expect(document.querySelector(".custom-label").textContent).toBe("20%");
    expect(
      document.getElementById("taskProgress").classList.contains("bg-warning"),
    ).toBe(true);
  });

  test("1 of 3 → 33%, bg-warning (boundary)", () => {
    injectCheckboxes(3, 1);
    global.taskCheckbox();
    expect(document.querySelector(".custom-label").textContent).toBe("33%");
    expect(
      document.getElementById("taskProgress").classList.contains("bg-warning"),
    ).toBe(true);
  });

  test("2 of 4 → 50%, bg-primary (34-70)", () => {
    injectCheckboxes(4, 2);
    global.taskCheckbox();
    expect(document.querySelector(".custom-label").textContent).toBe("50%");
    expect(
      document.getElementById("taskProgress").classList.contains("bg-primary"),
    ).toBe(true);
  });

  test("7 of 10 → 70%, bg-primary (boundary)", () => {
    injectCheckboxes(10, 7);
    global.taskCheckbox();
    expect(document.querySelector(".custom-label").textContent).toBe("70%");
    expect(
      document.getElementById("taskProgress").classList.contains("bg-primary"),
    ).toBe(true);
  });

  test("3 of 4 → 75%, bg-success (>70)", () => {
    injectCheckboxes(4, 3);
    global.taskCheckbox();
    expect(document.querySelector(".custom-label").textContent).toBe("75%");
    expect(
      document.getElementById("taskProgress").classList.contains("bg-success"),
    ).toBe(true);
  });

  test("4 of 4 → 100%, bg-success", () => {
    injectCheckboxes(4, 4);
    global.taskCheckbox();
    expect(document.querySelector(".custom-label").textContent).toBe("100%");
    expect(
      document.getElementById("taskProgress").classList.contains("bg-success"),
    ).toBe(true);
  });

  test("progress bar width is set to percentage", () => {
    injectCheckboxes(4, 2);
    global.taskCheckbox();
    expect(document.getElementById("taskProgress").style.width).toBe("50%");
  });

  test("previous colour class is removed when percentage changes", () => {
    injectCheckboxes(10, 1); // 10% → danger
    global.taskCheckbox();
    const bar = document.getElementById("taskProgress");
    expect(bar.classList.contains("bg-danger")).toBe(true);

    // Now check more boxes → success
    const cbs = document.querySelectorAll("#check-list input[type=checkbox]");
    cbs.forEach(cb => (cb.checked = true)); // all 10 checked → 100%
    global.taskCheckbox();
    expect(bar.classList.contains("bg-success")).toBe(true);
    expect(bar.classList.contains("bg-danger")).toBe(false);
  });
});
