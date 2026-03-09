(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  if (!guard || !utils) return;

  const showError = message => {
    guard.showToast(message);
  };
        let t = c.querySelector(".toast");
        if (!t) {
          t = document.createElement("div");
          t.className = "toast";
          t.setAttribute("role", "alert");
          t.setAttribute("aria-live", "assertive");
          t.setAttribute("aria-atomic", "true");
          const b = document.createElement("div");
          b.className = "toast-body";
          t.appendChild(b);
          c.appendChild(t);
        }
        t.querySelector(".toast-body").textContent = message;
        bootstrap.Toast.getOrCreateInstance(t).show();
      } else alert(message);
    } catch {
      alert(message);
    }
  };

  const delegate = (event, selector, handler, key) => {
    document.addEventListener(event, e => {
      const el = e.target.closest(selector);
      if (!el) return;
      try {
        handler(el, e);
      } catch {
        showError(getMsg(key, el));
      }
      const obs = new MutationObserver((m, o) => {
        if (!document.body.contains(el)) {
          o.disconnect();
        }
      });
      obs.observe(document.body, { childList: true, subtree: true });
    });
  };

  delegate(
    "change",
    'input[name="type"]:radio',
    el => {
      const t = el.value;
      if (t === "employee") {
        $(".employee").addClass("d-block").removeClass("d-none");
        $(".customer, .vendor").addClass("d-none").removeClass("d-block");
      } else if (t === "customer") {
        $(".customer").addClass("d-block").removeClass("d-none");
        $(".employee, .vendor").addClass("d-none").removeClass("d-block");
      } else {
        $(".vendor").addClass("d-block").removeClass("d-none");
        $(".employee, .customer").addClass("d-none").removeClass("d-block");
      }
    },
    "selection_failed"
  );

  delegate("change", "#employee", el => {
    $("#employee_detail").addClass("d-block").removeClass("d-none");
    $("#employee-box").addClass("d-none").removeClass("d-block");
    $.ajax({
      url: el.getAttribute("data-url"),
      type: "POST",
      headers: { "X-CSRF-TOKEN": jQuery("#token").val() },
      data: { id: el.value },
      success: data => {
        if (data) $("#employee_detail").innerHTML = data;
        else {
          $("#employee-box").addClass("d-block").removeClass("d-none");
          $("#employee_detail").addClass("d-none").removeClass("d-block");
        }
      },
      error: () => showError(getMsg("employee_fetch_failed", el)),
    });
  });

  delegate("change", "#customer", el => {
    $("#customer_detail").addClass("d-block").removeClass("d-none");
    $("#customer-box").addClass("d-none").removeClass("d-block");
    $.ajax({
      url: el.getAttribute("data-url"),
      type: "POST",
      headers: { "X-CSRF-TOKEN": jQuery("#token").val() },
      data: { id: el.value },
      success: data => {
        if (data) $("#customer_detail").innerHTML = data;
        else {
          $("#customer-box").addClass("d-block").removeClass("d-none");
          $("#customer_detail").addClass("d-none").removeClass("d-block");
        }
      },
      error: () => showError(getMsg("customer_fetch_failed", el)),
    });
  });

  delegate("change", "#vendor", el => {
    $("#vendor_detail").addClass("d-block").removeClass("d-none");
    $("#vendor-box").addClass("d-none").removeClass("d-block");
    $.ajax({
      url: el.getAttribute("data-url"),
      type: "POST",
      headers: { "X-CSRF-TOKEN": jQuery("#token").val() },
      data: { id: el.value },
      success: data => {
        if (data) $("#vendor_detail").innerHTML = data;
        else {
          $("#vendor-box").addClass("d-block").removeClass("d-none");
          $("#vendor_detail").addClass("d-none").removeClass("d-block");
        }
      },
      error: () => showError(getMsg("vendor_fetch_failed", el)),
    });
  });

  delegate("click", "#remove", el => {
    $(".vendor, .customer, .employee")
      .addClass("d-block")
      .removeClass("d-none");
    $("#vendor_detail, #customer_detail, #employee_detail")
      .addClass("d-none")
      .removeClass("d-block");
  });
})();
