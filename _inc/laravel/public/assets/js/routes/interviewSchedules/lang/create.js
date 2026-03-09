/**
 * @requires ERPUtils (translations infrastructure)
 */
(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: { candidate_unavailable: "المرشح غير متوفر" },
    da: { candidate_unavailable: "Kandidaten ikke tilgængelig" },
    de: { candidate_unavailable: "Kandidat nicht verfügbar" },
    en: { candidate_unavailable: "Candidate unavailable" },
    es: { candidate_unavailable: "Candidato no disponible" },
    fr: { candidate_unavailable: "Candidat indisponible" },
    he: { candidate_unavailable: "המועמד אינו זמין" },
    it: { candidate_unavailable: "Candidato non disponibile" },
    ja: { candidate_unavailable: "候補者は利用できません" },
    nl: { candidate_unavailable: "Kandidaat niet beschikbaar" },
    pl: { candidate_unavailable: "Kandydat niedostępny" },
    pt: { candidate_unavailable: "Candidato indisponível" },
    "pt-br": { candidate_unavailable: "Candidato indisponível" },
    ru: { candidate_unavailable: "Кандидат недоступен" },
    tr: { candidate_unavailable: "Aday mevcut değil" },
    zh: { candidate_unavailable: "候选人不可用" },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations[k] = {
        ...(window.translations[k] || {}),
        ...t[k],
      }),
  );
})();
