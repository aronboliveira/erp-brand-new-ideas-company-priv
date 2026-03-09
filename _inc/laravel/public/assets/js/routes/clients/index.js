/**
 * @file Clients Index Route Guard
 * @description Guards client action buttons using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#createClientBtn", {
    msgKey: "create_client_unavailable",
    fallbackMsg: "Create client route is unavailable.",
  });

  guard.bindClickGuard('[data-listener-alias="edit-client"]', {
    msgKey: "edit_client_unavailable",
    fallbackMsg: "Edit client route is unavailable.",
  });

  guard.bindClickGuard('[data-listener-alias="delete-client"]', {
    msgKey: "delete_client_unavailable",
    fallbackMsg: "Delete client route is unavailable.",
  });

  guard.bindClickGuard('[data-listener-alias="reset-client"]', {
    msgKey: "reset_client_unavailable",
    fallbackMsg: "Reset client route is unavailable.",
  });
})();
