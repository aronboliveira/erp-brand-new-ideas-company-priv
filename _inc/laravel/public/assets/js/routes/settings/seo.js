/**
 * Settings SEO Route Guards
 * Handles SEO, cookies, and ChatGPT settings forms with AI generation links
 * @module routes/settings/seo
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#generate-ai-seo-link", {
    fallbackMsg:
      "SEO generation route is unavailable. Please contact technical support or your domain administrator.",
  });

  guard.bindClickGuard("#generate-ai-cookie-link", {
    fallbackMsg:
      "Cookie generation route is unavailable. Please contact technical support or your domain administrator.",
  });

  guard.bindSubmitGuard("#settings-seo-store-form", {
    fallbackMsg:
      "SEO settings store route is unavailable. Please contact technical support or your domain administrator.",
  });

  guard.bindSubmitGuard("#{{ $settingsCookiesStoreFormId }}", {
    fallbackMsg:
      "Cookie settings store route is unavailable. Please contact technical support or your domain administrator.",
  });

  guard.bindSubmitGuard("#settings-chatgpt-settings-form", {
    fallbackMsg:
      "ChatGPT settings store route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
