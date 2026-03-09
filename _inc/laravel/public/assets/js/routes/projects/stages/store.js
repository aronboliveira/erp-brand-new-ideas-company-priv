/**
 * @file Project Stage Store Route Guard
 * @description Guards the project stage creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('#create-project-stage-form', {
    msgKey: 'create_project_stage_unavailable',
    fallbackMsg: 'Create project stage route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
