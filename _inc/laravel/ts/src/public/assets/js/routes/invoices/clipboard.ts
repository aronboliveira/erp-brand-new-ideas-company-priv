/**
 * @fileoverview TypeScript version of public/assets/js/routes/invoices/clipboard.js
 * @generated from original JavaScript — automated migration
 * @module clipboard
 */
function copyToClipboard(element: HTMLElement): void {
  const _t = (k: string): string => {
    const l = document.documentElement?.lang || "en";
    return (window as Record<string, any>).translations?.[l]?.[k] ?? k;
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
