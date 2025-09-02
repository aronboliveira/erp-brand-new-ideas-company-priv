(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      env_toggle_unavailable: "تعذّر تبديل بيئة الإعداد.",
      tab_db_unavailable: "تعذّر فتح إعدادات قاعدة البيانات.",
      tab_app_unavailable: "تعذّر فتح إعدادات التطبيق.",
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
    },
    da: {
      env_toggle_unavailable: "Kunne ikke skifte miljøvisning.",
      tab_db_unavailable: "Kunne ikke åbne databaseindstillinger.",
      tab_app_unavailable: "Kunne ikke åbne applikationsindstillinger.",
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
    },
    de: {
      env_toggle_unavailable:
        "Umgebungsansicht konnte nicht umgeschaltet werden.",
      tab_db_unavailable:
        "Datenbankeinstellungen konnten nicht geöffnet werden.",
      tab_app_unavailable:
        "Anwendungseinstellungen konnten nicht geöffnet werden.",
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
    },
    en: {
      env_toggle_unavailable: "Could not toggle environment field.",
      tab_db_unavailable: "Could not open Database Settings.",
      tab_app_unavailable: "Could not open Application Settings.",
      plugin_unavailable: "A required library failed to load.",
    },
    es: {
      env_toggle_unavailable: "No se pudo alternar el campo de entorno.",
      tab_db_unavailable: "No se pudieron abrir los ajustes de base de datos.",
      tab_app_unavailable: "No se pudieron abrir los ajustes de la aplicación.",
      plugin_unavailable: "No se cargó una biblioteca requerida.",
    },
    fr: {
      env_toggle_unavailable: "Impossible d’activer le champ d’environnement.",
      tab_db_unavailable:
        "Impossible d’ouvrir les paramètres de base de données.",
      tab_app_unavailable:
        "Impossible d’ouvrir les paramètres de l’application.",
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
    },
    he: {
      env_toggle_unavailable: "לא ניתן היה להחליף את שדה הסביבה.",
      tab_db_unavailable: "לא ניתן לפתוח את הגדרות מסד הנתונים.",
      tab_app_unavailable: "לא ניתן לפתוח את הגדרות היישום.",
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
    },
    it: {
      env_toggle_unavailable: "Impossibile attivare il campo ambiente.",
      tab_db_unavailable: "Impossibile aprire Impostazioni database.",
      tab_app_unavailable: "Impossibile aprire Impostazioni applicazione.",
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
    },
    ja: {
      env_toggle_unavailable: "環境フィールドを切り替えられませんでした。",
      tab_db_unavailable: "データベース設定を開けませんでした。",
      tab_app_unavailable: "アプリケーション設定を開けませんでした。",
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
    },
    nl: {
      env_toggle_unavailable: "Kon het omgevingsveld niet wisselen.",
      tab_db_unavailable: "Kon Database-instellingen niet openen.",
      tab_app_unavailable: "Kon Applicatie-instellingen niet openen.",
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
    },
    pl: {
      env_toggle_unavailable: "Nie można przełączyć pola środowiska.",
      tab_db_unavailable: "Nie udało się otworzyć ustawień bazy danych.",
      tab_app_unavailable: "Nie udało się otworzyć ustawień aplikacji.",
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
    },
    pt: {
      env_toggle_unavailable: "Não foi possível alternar o campo de ambiente.",
      tab_db_unavailable:
        "Não foi possível abrir as Definições de Base de Dados.",
      tab_app_unavailable: "Não foi possível abrir as Definições da Aplicação.",
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
    },
    "pt-br": {
      env_toggle_unavailable: "Não foi possível alternar o campo de ambiente.",
      tab_db_unavailable: "Não foi possível abrir as Configurações do Banco.",
      tab_app_unavailable:
        "Não foi possível abrir as Configurações do Aplicativo.",
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
    },
    ru: {
      env_toggle_unavailable: "Не удалось переключить поле окружения.",
      tab_db_unavailable: "Не удалось открыть настройки базы данных.",
      tab_app_unavailable: "Не удалось открыть настройки приложения.",
      plugin_unavailable: "Не загружена необходимая библиотека.",
    },
    tr: {
      env_toggle_unavailable: "Ortam alanı değiştirilemedi.",
      tab_db_unavailable: "Veritabanı Ayarları açılamadı.",
      tab_app_unavailable: "Uygulama Ayarları açılamadı.",
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
    },
    zh: {
      env_toggle_unavailable: "无法切换环境字段。",
      tab_db_unavailable: "无法打开数据库设置。",
      tab_app_unavailable: "无法打开应用设置。",
      plugin_unavailable: "未能加载所需的库。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
