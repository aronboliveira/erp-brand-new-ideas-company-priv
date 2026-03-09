/**
 * @fileoverview TypeScript version of public/assets/js/routes/auth/login/lang/submit.js
 * @generated from original JavaScript — automated migration
 * @module submit
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

(function (): void {
  if (!window.translations) {
    window.translations = {};
  }
  const t: Record<string, Record<string, string>> = {
    ar: {
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
      login_submit_unavailable: "تعذّر تعطيل زر تسجيل الدخول.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      login_submit_unavailable: "Kunne ikke deaktivere login-knappen.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      login_submit_unavailable:
        "Login-Schaltfläche konnte nicht deaktiviert werden.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      login_submit_unavailable: "Could not disable the login button.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      login_submit_unavailable:
        "No se pudo desactivar el botón de inicio de sesión.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      login_submit_unavailable:
        "Impossible de désactiver le bouton de connexion.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      login_submit_unavailable: "לא ניתן היה להשבית את לחצן ההתחברות.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      login_submit_unavailable:
        "Impossibile disabilitare il pulsante di accesso.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      login_submit_unavailable: "ログインボタンを無効化できませんでした。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      login_submit_unavailable: "Kon de inlogknop niet uitschakelen.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      login_submit_unavailable: "Nie można wyłączyć przycisku logowania.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      login_submit_unavailable: "Não foi possível desativar o botão de login.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      login_submit_unavailable: "Não foi possível desativar o botão de login.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      login_submit_unavailable: "Не удалось отключить кнопку входа.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      login_submit_unavailable: "Giriş düğmesi devre dışı bırakılamadı.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      login_submit_unavailable: "无法禁用登录按钮。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations![k] = { ...(window.translations![k] || {}), ...t[k] };
  });
})();
