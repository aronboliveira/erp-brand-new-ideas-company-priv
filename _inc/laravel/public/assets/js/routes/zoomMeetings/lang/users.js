(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: { zoom_users_unavailable: "تعذّر تحميل مستخدمي المشروع" },
    da: { zoom_users_unavailable: "Kunne ikke hente projektbrugere" },
    de: {
      zoom_users_unavailable: "Projektbenutzer konnten nicht geladen werden",
    },
    en: { zoom_users_unavailable: "Could not load project users" },
    es: {
      zoom_users_unavailable: "No se pudieron cargar los usuarios del proyecto",
    },
    fr: {
      zoom_users_unavailable:
        "Impossible de charger les utilisateurs du projet",
    },
    he: { zoom_users_unavailable: "לא ניתן לטעון משתמשי פרויקט" },
    it: {
      zoom_users_unavailable: "Impossibile caricare gli utenti del progetto",
    },
    ja: {
      zoom_users_unavailable: "プロジェクトのユーザーを読み込めませんでした",
    },
    nl: { zoom_users_unavailable: "Projectgebruikers laden is mislukt" },
    pl: {
      zoom_users_unavailable: "Nie udało się wczytać użytkowników projektu",
    },
    pt: {
      zoom_users_unavailable:
        "Não foi possível carregar os utilizadores do projeto",
    },
    "pt-br": {
      zoom_users_unavailable:
        "Não foi possível carregar os usuários do projeto",
    },
    ru: {
      zoom_users_unavailable: "Не удалось загрузить пользователей проекта",
    },
    tr: { zoom_users_unavailable: "Proje kullanıcıları yüklenemedi" },
    zh: { zoom_users_unavailable: "无法加载项目用户" },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations[k] = {
        ...(window.translations[k] || {}),
        ...t[k],
      })
  );
})();
