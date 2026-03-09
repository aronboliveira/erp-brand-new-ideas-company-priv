/**
 * @fileoverview TypeScript version of public/assets/js/routes/leads/lang/edit.js
 * @generated from original JavaScript — automated migration
 * @module edit
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

((): void => {
  if (!window.translations) {
    window.translations = {};
  }
  const t: Record<string, Record<string, string>> = {
    ar: { pipeline_stages_unavailable: "تعذّر تحميل المراحل" },
    da: { pipeline_stages_unavailable: "Kunne ikke indlæse faser" },
    de: { pipeline_stages_unavailable: "Phasen konnten nicht geladen werden" },
    en: { pipeline_stages_unavailable: "Cannot load stages" },
    es: { pipeline_stages_unavailable: "No se pueden cargar las etapas" },
    fr: { pipeline_stages_unavailable: "Impossible de charger les étapes" },
    he: { pipeline_stages_unavailable: "לא ניתן לטעון שלבים" },
    it: { pipeline_stages_unavailable: "Impossibile caricare le fasi" },
    ja: { pipeline_stages_unavailable: "ステージを読み込めません" },
    nl: { pipeline_stages_unavailable: "Fases kunnen niet worden geladen" },
    pl: { pipeline_stages_unavailable: "Nie można wczytać etapów" },
    pt: { pipeline_stages_unavailable: "Não foi possível carregar as etapas" },
    "pt-br": {
      pipeline_stages_unavailable: "Não foi possível carregar as etapas",
    },
    ru: { pipeline_stages_unavailable: "Не удалось загрузить этапы" },
    tr: { pipeline_stages_unavailable: "Aşamalar yüklenemiyor" },
    zh: { pipeline_stages_unavailable: "无法加载阶段" },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations![k] = { ...(window.translations![k] || {}),
        ...t[k],
      })
  );
  (function () {
    const t = {
      ar: {
        action_unavailable: "الإجراء غير متاح.",
        update_unavailable: "تحديث غير متاح.",
      },
      da: {
        action_unavailable: "Handling ikke tilgængelig.",
        update_unavailable: "Opdatering ikke tilgængelig.",
      },
      de: {
        action_unavailable: "Aktion nicht verfügbar.",
        update_unavailable: "Aktualisierung nicht verfügbar.",
      },
      en: {
        action_unavailable: "Action unavailable.",
        update_unavailable: "Update is unavailable.",
      },
      es: {
        action_unavailable: "Acción no disponible.",
        update_unavailable: "Actualización no disponible.",
      },
      fr: {
        action_unavailable: "Action indisponible.",
        update_unavailable: "Mise à jour indisponible.",
      },
      he: {
        action_unavailable: "הפעולה אינה זמינה.",
        update_unavailable: "עדכון אינו זמין.",
      },
      it: {
        action_unavailable: "Azione non disponibile.",
        update_unavailable: "Aggiornamento non disponibile.",
      },
      ja: {
        action_unavailable: "操作を利用できません。",
        update_unavailable: "更新は利用できません。",
      },
      nl: {
        action_unavailable: "Actie niet beschikbaar.",
        update_unavailable: "Bijwerken niet beschikbaar.",
      },
      pl: {
        action_unavailable: "Akcja niedostępna.",
        update_unavailable: "Aktualizacja niedostępna.",
      },
      pt: {
        action_unavailable: "Ação indisponível.",
        update_unavailable: "Atualização indisponível.",
      },
      "pt-br": {
        action_unavailable: "Ação indisponível.",
        update_unavailable: "Atualização indisponível.",
      },
      ru: {
        action_unavailable: "Действие недоступно.",
        update_unavailable: "Обновление недоступно.",
      },
      tr: {
        action_unavailable: "Eylem kullanılamıyor.",
        update_unavailable: "Güncelleme kullanılamıyor.",
      },
      zh: {
        action_unavailable: "无法执行此操作。",
        update_unavailable: "无法更新。",
      },
    };
    if (!window.translations) window.translations = t;
    else
      Object.keys(t).forEach(function (k) {
        window.translations![k] = Object.assign(
          {},
          window.translations![k] || {},
          (t as Record<string, Record<string, string>>)[k]
        );
      });
  })();
  (function () {
    const t = {
      ar: {
        ai_generate_unavailable: "إنشاء المحتوى بالذكاء الاصطناعي غير متاح.",
        action_unavailable: "الإجراء غير متاح.",
      },
      da: {
        ai_generate_unavailable: "AI-generering er ikke tilgængelig.",
        action_unavailable: "Handling ikke tilgængelig.",
      },
      de: {
        ai_generate_unavailable: "KI-Generierung ist nicht verfügbar.",
        action_unavailable: "Aktion nicht verfügbar.",
      },
      en: {
        ai_generate_unavailable: "AI generation is unavailable.",
        action_unavailable: "Action unavailable.",
      },
      es: {
        ai_generate_unavailable: "La generación por IA no está disponible.",
        action_unavailable: "Acción no disponible.",
      },
      fr: {
        ai_generate_unavailable: "La génération IA n'est pas disponible.",
        action_unavailable: "Action indisponible.",
      },
      he: {
        ai_generate_unavailable: "יצירת תוכן ב-AI אינה זמינה.",
        action_unavailable: "הפעולה אינה זמינה.",
      },
      it: {
        ai_generate_unavailable: "Generazione AI non disponibile.",
        action_unavailable: "Azione non disponibile.",
      },
      ja: {
        ai_generate_unavailable: "AI 生成は利用できません。",
        action_unavailable: "操作を利用できません。",
      },
      nl: {
        ai_generate_unavailable: "AI-generatie is niet beschikbaar.",
        action_unavailable: "Actie niet beschikbaar.",
      },
      pl: {
        ai_generate_unavailable: "Generowanie AI jest niedostępne.",
        action_unavailable: "Akcja niedostępna.",
      },
      pt: {
        ai_generate_unavailable: "Geração por IA indisponível.",
        action_unavailable: "Ação indisponível.",
      },
      "pt-br": {
        ai_generate_unavailable: "Geração por IA indisponível.",
        action_unavailable: "Ação indisponível.",
      },
      ru: {
        ai_generate_unavailable: "Генерация ИИ недоступна.",
        action_unavailable: "Действие недоступно.",
      },
      tr: {
        ai_generate_unavailable: "Yapay zekâ üretimi kullanılamıyor.",
        action_unavailable: "Eylem kullanılamıyor.",
      },
      zh: {
        ai_generate_unavailable: "AI 生成不可用。",
        action_unavailable: "无法执行此操作。",
      },
    };
    if (!window.translations) window.translations = t;
    else
      Object.keys(t).forEach(function (k) {
        window.translations![k] = Object.assign(
          {},
          window.translations![k] || {},
          (t as Record<string, Record<string, string>>)[k]
        );
      });
  })();
})();
