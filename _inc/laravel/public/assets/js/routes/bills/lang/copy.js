(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: { copy_link_unavailable: "نسخ الرابط غير متاح" },
    da: { copy_link_unavailable: "Kopiering af link ikke tilgængelig" },
    de: { copy_link_unavailable: "Link kopieren nicht verfügbar" },
    en: { copy_link_unavailable: "Copy link unavailable" },
    es: { copy_link_unavailable: "Copia de enlace no disponible" },
    fr: { copy_link_unavailable: "Copie du lien non disponible" },
    he: { copy_link_unavailable: "העתקת הקישור אינה זמינה" },
    it: { copy_link_unavailable: "Copia del link non disponibile" },
    ja: { copy_link_unavailable: "リンクのコピーは利用できません" },
    nl: { copy_link_unavailable: "Kopiëren van de link niet beschikbaar" },
    pl: { copy_link_unavailable: "Kopiowanie linku niedostępne" },
    pt: { copy_link_unavailable: "Cópia do link indisponível" },
    "pt-br": { copy_link_unavailable: "Cópia do link indisponível" },
    ru: { copy_link_unavailable: "Копирование ссылки недоступно" },
    tr: { copy_link_unavailable: "Bağlantı kopyalama kullanılamıyor" },
    zh: { copy_link_unavailable: "无法复制链接" },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations[k] = {
        ...(window.translations[k] || {}),
        ...t[k],
      })
  );
})();
