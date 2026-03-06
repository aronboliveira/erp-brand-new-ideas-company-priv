/**
 * @fileoverview TypeScript version of public/assets/js/pages/form-validation.js
 * @generated from original JavaScript - manual review recommended
 * @module form-validation
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

'use strict';

const bouncer = new Bouncer('[data-validate]', {
    disableSubmit: true,
    customValidations: {
        valueMismatch: function (field) {
            const selector = field.getAttribute('data-bouncer-match');
            if (!selector) return false;
            const otherField = field.form.querySelector(selector);
            if (!otherField) return false;
            return otherField.value !== field.value;

        }
    },
    messages: {
        valueMismatch: function (field) {
            const customMessage = field.getAttribute('data-bouncer-mismatch-message');
            return customMessage ? customMessage : 'Please make sure the fields match.'
        }
    }
});

document.addEventListener('bouncerFormInvalid', function (event) {
    window.scrollTo(0, event.detail.errors[0].offsetTop);
}, false);

document.addEventListener('bouncerFormValid', function (): void {
    alert('Form submitted successfully!');
    window.location.reload();
}, false);