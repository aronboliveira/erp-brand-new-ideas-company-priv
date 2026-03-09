/**
 * @fileoverview Click guard for project task comment file attachment using ERPGuard singleton
 * @module assets/js/routes/projects/tasks/comments/storeFIle
 */
(() => {
  try {
    window.ERPGuard?.bindClickGuard?.("#file_attachment_submit", {
      msg: btoa(
        "Store file for task comment route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
