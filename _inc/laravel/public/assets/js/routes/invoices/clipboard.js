/**
 * @file Invoice Clipboard Utility
 * @description Clipboard copy functionality using ERPUtils singleton
 */

/**
 * Copy element ID to clipboard
 * @param {HTMLElement} element - Element containing ID to copy
 * @returns {void}
 */
function copyToClipboard(element) {
  const { copyToClipboard: copy } = window.ERPUtils ?? {};
  const { scheduleError } = window.ERPGuard ?? {};

  if (!copy || !scheduleError) {
    if (scheduleError) scheduleError("Utility system not loaded", "click");
    return;
  }

  const copyText = element.id;
  copy(copyText, true);
}
