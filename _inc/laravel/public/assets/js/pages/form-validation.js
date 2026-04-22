/**
 * @fileoverview TypeScript version of public/assets/js/pages/form-validation.js
 * @generated from original JavaScript - manual review recommended
 * @module form-validation
 */
"use strict";
if (document.body?.getAttribute("data-validation-init") !== "true") {
    document.body?.setAttribute("data-validation-init", "true");
    if (typeof Bouncer !== "undefined" && Bouncer) {
        try {
            const _bouncer = new Bouncer("[data-bouncer]", {
                disableSubmit: true,
                customValidations: {
                    valueMismatch: function (field) {
                        const selector = field.getAttribute("data-bouncer-match");
                        if (!selector)
                            return false;
                        const otherField = field.form?.querySelector(selector);
                        if (!otherField)
                            return true;
                        return otherField.value !== field.value;
                    },
                },
                messages: {
                    valueMismatch: function (field) {
                        const customMessage = field.getAttribute("data-bouncer-mismatch-message");
                        return customMessage
                            ? customMessage
                            : "Please make sure the fields match.";
                    },
                },
            });
        }
        catch (err) {
            console.error("Error initializing Bouncer", err);
        }
    }
    document.addEventListener("bouncerFormInvalid", function (event) {
        window.scrollTo(0, event.detail.errors[0].offsetTop);
    }, false);
    document.addEventListener("bouncerFormValid", function () {
        alert("Form submitted successfully!");
        window.location.reload();
    }, false);
}
//# sourceMappingURL=form-validation.js.map