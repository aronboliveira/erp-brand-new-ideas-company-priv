/** @requires ERPUtils (translations) */
(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: { project_stage_order_unavailable: "فشل تحديث ترتيب مراحل المشروع" },
    da: {
      project_stage_order_unavailable:
        "Opdatering af projektfaserækkefølge mislykkedes",
    },
    de: {
      project_stage_order_unavailable:
        "Aktualisieren der Projektphasenreihenfolge fehlgeschlagen",
    },
    en: {
      project_stage_order_unavailable: "Failed to update project stages order",
    },
    es: {
      project_stage_order_unavailable:
        "Error al actualizar el orden de las etapas del proyecto",
    },
    fr: {
      project_stage_order_unavailable:
        "Échec de la mise à jour de l’ordre des étapes du projet",
    },
    he: { project_stage_order_unavailable: "עדכון סדר שלבי הפרויקט נכשל" },
    it: {
      project_stage_order_unavailable:
        "Aggiornamento ordine fasi progetto non riuscito",
    },
    ja: {
      project_stage_order_unavailable:
        "プロジェクト段階の順序を更新できませんでした",
    },
    nl: {
      project_stage_order_unavailable:
        "Bijwerken van volgorde projectfasen mislukt",
    },
    pl: {
      project_stage_order_unavailable:
        "Nie udało się zaktualizować kolejności etapów projektu",
    },
    pt: {
      project_stage_order_unavailable:
        "Falha ao atualizar a ordem das etapas do projeto",
    },
    "pt-br": {
      project_stage_order_unavailable:
        "Falha ao atualizar a ordem das etapas do projeto",
    },
    ru: {
      project_stage_order_unavailable:
        "Не удалось обновить порядок этапов проекта",
    },
    tr: {
      project_stage_order_unavailable: "Proje aşamaları sırası güncellenemedi",
    },
    zh: { project_stage_order_unavailable: "无法更新项目阶段顺序" },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations[k] = {
        ...(window.translations[k] || {}),
        ...t[k],
      })
  );
})();
