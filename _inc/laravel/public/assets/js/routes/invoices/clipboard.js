/**
 * @fileoverview TypeScript version of public/assets/js/routes/invoices/clipboard.js
 * @generated from original JavaScript — automated migration
 * @module clipboard
 */
function _copyToClipboard(element) {
  const copyText = element.id;
  navigator.clipboard.writeText(copyText);
  // document.addEventListener('copy', function (e) {
  //     e.clipboardData.setData('text/plain', copyText);
  //     e.preventDefault();
  // }, true);
  //
  // document.execCommand('copy');
  show_toastr("success", "Url copied to clipboard", "success");
}
//# sourceMappingURL=clipboard.js.map
