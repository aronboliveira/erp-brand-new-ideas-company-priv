/**
 * @fileoverview TypeScript version of Modules/LandingPage/Resources/assets/js/pages/form-validation.js
 * @generated from original JavaScript - manual review recommended
 * @module form-validation
 */
"use strict";
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */
const bouncer = new Bouncer("[data-validate]", {
    disableSubmit: true,
    customValidations: {
        valueMismatch: function (field) {
            const selector = field.getAttribute("data-bouncer-match");
            if (!selector)
                return false;
            const form = field.form;
            if (!form)
                return false;
            const otherField = form.querySelector(selector);
            if (!otherField)
                return false;
            return otherField.value !== field.value;
        },
    },
    messages: {
        valueMismatch: function (field) {
            const customMessage = field.getAttribute("data-bouncer-mismatch-message");
            return customMessage ? customMessage : "Please make sure the fields match.";
        },
    },
});
document.addEventListener("bouncerFormInvalid", function (event) {
    const detail = event.detail;
    window.scrollTo(0, detail.errors[0].offsetTop);
}, false);
document.addEventListener("bouncerFormValid", function () {
    alert("Form submitted successfully!");
    window.location.reload();
});
//# sourceMappingURL=form-validation.js.map