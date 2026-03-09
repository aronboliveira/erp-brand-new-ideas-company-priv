/**
 * @file employees/fileImport.js
 * @description Import employee button guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#employee-import-btn");
  } catch (err) {
    console.error("Error initializing employee import guard:", err);
  }
})();
