(function () {
  "use strict";
  /**
   * @fileoverview TypeScript version of public/assets/js/routes/invoices/clipboard.js
   * @generated from original JavaScript — automated migration
   * @module clipboard
   */
  function copyToClipboard(element) {
    const _t = k => {
      const l = document.documentElement?.lang || "en";
      return window.translations?.[l]?.[k] ?? k;
    };
    const copyText = element.id;
    navigator.clipboard.writeText(copyText);
    // document.addEventListener('copy', function (e) {
    //     e.clipboardData.setData('text/plain', copyText);
    //     e.preventDefault();
    // }, true);
    //
    // document.execCommand('copy');
    show_toastr("success", _t("URL copied to clipboard"), "success");
  }
})();
