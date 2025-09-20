(() => {
  const t = {
    ar: {
      ld_call_route_unavailable: "الإجراء غير متاح.",
      ai_generate_unavailable: "إنشاء المحتوى بالذكاء الاصطناعي غير متاح.",
    },
    da: {
      ld_call_route_unavailable: "Handlingen er ikke tilgængelig.",
      ai_generate_unavailable: "AI-indholdsgenerering er ikke tilgængelig.",
    },
    de: {
      ld_call_route_unavailable: "Aktion nicht verfügbar.",
      ai_generate_unavailable: "KI-Inhaltserstellung ist nicht verfügbar.",
    },
    en: {
      ld_call_route_unavailable: "Action unavailable.",
      ai_generate_unavailable: "AI content generation is unavailable.",
    },
    es: {
      ld_call_route_unavailable: "Acción no disponible.",
      ai_generate_unavailable:
        "La generación de contenido con IA no está disponible.",
    },
    fr: {
      ld_call_route_unavailable: "Action indisponible.",
      ai_generate_unavailable:
        "La génération de contenu par IA est indisponible.",
    },
    he: {
      ld_call_route_unavailable: "הפעולה אינה זמינה.",
      ai_generate_unavailable: "יצירת תוכן ב־AI אינה זמינה.",
    },
    it: {
      ld_call_route_unavailable: "Azione non disponibile.",
      ai_generate_unavailable:
        "La generazione di contenuti IA non è disponibile.",
    },
    ja: {
      ld_call_route_unavailable: "操作を利用できません。",
      ai_generate_unavailable: "AI によるコンテンツ生成は利用できません。",
    },
    nl: {
      ld_call_route_unavailable: "Actie niet beschikbaar.",
      ai_generate_unavailable: "AI-inhoudsgeneratie is niet beschikbaar.",
    },
    pl: {
      ld_call_route_unavailable: "Akcja niedostępna.",
      ai_generate_unavailable: "Generowanie treści AI jest niedostępne.",
    },
    pt: {
      ld_call_route_unavailable: "Ação indisponível.",
      ai_generate_unavailable: "Geração de conteúdo por IA indisponível.",
    },
    "pt-br": {
      ld_call_route_unavailable: "Ação indisponível.",
      ai_generate_unavailable: "Geração de conteúdo por IA indisponível.",
    },
    ru: {
      ld_call_route_unavailable: "Действие недоступно.",
      ai_generate_unavailable: "Генерация контента ИИ недоступна.",
    },
    tr: {
      ld_call_route_unavailable: "İşlem kullanılamıyor.",
      ai_generate_unavailable: "Yapay zekâ içerik üretimi kullanılamıyor.",
    },
    zh: {
      ld_call_route_unavailable: "操作不可用。",
      ai_generate_unavailable: "AI 内容生成不可用。",
    },
  };
  if (!window.translations) {
    window.translations = t;
  } else {
    Object.keys(t).forEach(k => {
      window.translations[k] = Object.assign(
        {},
        window.translations[k] || {},
        t[k]
      );
    });
  }
})();
