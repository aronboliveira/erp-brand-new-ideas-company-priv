/** @requires ERPUtils (translations) */
(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      tags_unavailable: "لا يمكن تهيئة الوسوم",
      rating_unavailable: "فشل إرسال التقييم",
      stage_change_unavailable: "فشل تغيير مرحلة المرشح",
    },
    da: {
      tags_unavailable: "Kan ikke initialisere tags",
      rating_unavailable: "Kunne ikke sende vurdering",
      stage_change_unavailable: "Ændring af fase mislykkedes",
    },
    de: {
      tags_unavailable: "Tags konnten nicht initialisiert werden",
      rating_unavailable: "Bewertung konnte nicht gesendet werden",
      stage_change_unavailable: "Phasenänderung fehlgeschlagen",
    },
    en: {
      tags_unavailable: "Cannot initialize tags",
      rating_unavailable: "Cannot submit rating",
      stage_change_unavailable: "Cannot change candidate stage",
    },
    es: {
      tags_unavailable: "No se pueden inicializar etiquetas",
      rating_unavailable: "No se pudo enviar la valoración",
      stage_change_unavailable: "No se pudo cambiar la etapa del candidato",
    },
    fr: {
      tags_unavailable: "Impossible d’initialiser les tags",
      rating_unavailable: "Impossible d’envoyer la note",
      stage_change_unavailable: "Impossible de changer l’étape du candidat",
    },
    he: {
      tags_unavailable: "לא ניתן לאתחל תגים",
      rating_unavailable: "שליחת הדירוג נכשלה",
      stage_change_unavailable: "שינוי שלב המועמד נכשל",
    },
    it: {
      tags_unavailable: "Impossibile inizializzare i tag",
      rating_unavailable: "Impossibile inviare la valutazione",
      stage_change_unavailable: "Impossibile cambiare fase candidato",
    },
    ja: {
      tags_unavailable: "タグを初期化できません",
      rating_unavailable: "評価を送信できません",
      stage_change_unavailable: "候補者フェーズの変更に失敗しました",
    },
    nl: {
      tags_unavailable: "Kan tags niet initialiseren",
      rating_unavailable: "Kan beoordeling niet verzenden",
      stage_change_unavailable: "Fase wijzigen mislukt",
    },
    pl: {
      tags_unavailable: "Nie można zainicjalizować tagów",
      rating_unavailable: "Nie można wysłać oceny",
      stage_change_unavailable: "Nie udało się zmienić etapu kandydata",
    },
    pt: {
      tags_unavailable: "Não foi possível inicializar as tags",
      rating_unavailable: "Não foi possível enviar avaliação",
      stage_change_unavailable: "Não foi possível alterar etapa do candidato",
    },
    "pt-br": {
      tags_unavailable: "Não foi possível inicializar as tags",
      rating_unavailable: "Não foi possível enviar avaliação",
      stage_change_unavailable: "Não foi possível alterar etapa do candidato",
    },
    ru: {
      tags_unavailable: "Не удалось инициализировать теги",
      rating_unavailable: "Не удалось отправить оценку",
      stage_change_unavailable: "Не удалось изменить этап кандидата",
    },
    tr: {
      tags_unavailable: "Etiketler başlatılamıyor",
      rating_unavailable: "Değerlendirme gönderilemedi",
      stage_change_unavailable: "Aday aşaması değiştirilemedi",
    },
    zh: {
      tags_unavailable: "无法初始化标签",
      rating_unavailable: "无法提交评分",
      stage_change_unavailable: "无法更改候选人阶段",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations[k] = {
        ...(window.translations[k] || {}),
        ...t[k],
      })
  );
})();
