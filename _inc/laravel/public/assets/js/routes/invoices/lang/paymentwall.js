/**
 * @requires ERPUtils (translations infrastructure)
 */
(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: { paymentwall_unavailable: "لا يمكن تحميل Paymentwall" },
    da: { paymentwall_unavailable: "Kan ikke indlæse Paymentwall" },
    de: { paymentwall_unavailable: "Paymentwall konnte nicht geladen werden" },
    en: { paymentwall_unavailable: "Cannot load Paymentwall" },
    es: { paymentwall_unavailable: "No se puede cargar Paymentwall" },
    fr: { paymentwall_unavailable: "Impossible de charger Paymentwall" },
    he: { paymentwall_unavailable: "לא ניתן לטעון Paymentwall" },
    it: { paymentwall_unavailable: "Impossibile caricare Paymentwall" },
    ja: { paymentwall_unavailable: "Paymentwallを読み込めません" },
    nl: { paymentwall_unavailable: "Kan Paymentwall niet laden" },
    pl: { paymentwall_unavailable: "Nie można załadować Paymentwall" },
    pt: { paymentwall_unavailable: "Não foi possível carregar Paymentwall" },
    "pt-br": {
      paymentwall_unavailable: "Não foi possível carregar Paymentwall",
    },
    ru: { paymentwall_unavailable: "Не удалось загрузить Paymentwall" },
    tr: { paymentwall_unavailable: "Paymentwall yüklenemiyor" },
    zh: { paymentwall_unavailable: "无法加载 Paymentwall" },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations[k] = {
        ...(window.translations[k] || {}),
        ...t[k],
      }),
  );
})();
