/**
 * @file Employee Edit Route Guard
 * @description Guards employee edit buttons using ERPGuard singleton
 * Mirror of public/assets/js/routes/employees/edit.js
 */

(() => {
  const guard = (window as any).ERPGuard as
    | { bindClickGuard(sel: string, opts: { msgKey: string; fallbackMsg: string }): void }
    | undefined;
  if (!guard) return;

  guard.bindClickGuard(
    'a[id^="employee-edit-btn-"][data-url][data-guard-msg]',
    {
      msgKey: "edit_employee_unavailable",
      fallbackMsg:
        "Edit employee route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();

export {};
