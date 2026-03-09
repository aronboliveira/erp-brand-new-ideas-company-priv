/**
 * @fileoverview TypeScript version of public/assets/js/routes/ai/grammar/lang/init.js
 * @generated from original JavaScript — automated migration
 * @module init
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
      grammar_init_unavailable: "تعذّر تهيئة المحرّر أو الحقول.",
      ajax_unavailable: "تعذّر الاتصال بالخادم.",
      generate_unavailable: "تعذّر إنشاء الرد.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      grammar_init_unavailable: "Kunne ikke initialisere editor eller felter.",
      ajax_unavailable: "Serveren kunne ikke kontaktes.",
      generate_unavailable: "Kunne ikke generere svaret.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      grammar_init_unavailable:
        "Editor oder Felder konnten nicht initialisiert werden.",
      ajax_unavailable: "Serveranfrage fehlgeschlagen.",
      generate_unavailable: "Antwort konnte nicht generiert werden.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      grammar_init_unavailable: "Could not initialize editor or fields.",
      ajax_unavailable: "Server request failed.",
      generate_unavailable: "Could not generate response.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      grammar_init_unavailable:
        "No se pudo inicializar el editor o los campos.",
      ajax_unavailable: "Falló la solicitud al servidor.",
      generate_unavailable: "No se pudo generar la respuesta.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      grammar_init_unavailable:
        "Impossible d’initialiser l’éditeur ou les champs.",
      ajax_unavailable: "Échec de la requête serveur.",
      generate_unavailable: "Impossible de générer la réponse.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      grammar_init_unavailable: "לא ניתן היה לאתחל עורך או שדות.",
      ajax_unavailable: "בקשת השרת נכשלה.",
      generate_unavailable: "לא ניתן היה ליצור תגובה.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      grammar_init_unavailable: "Impossibile inizializzare l’editor o i campi.",
      ajax_unavailable: "Richiesta al server non riuscita.",
      generate_unavailable: "Impossibile generare la risposta.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      grammar_init_unavailable:
        "エディタまたはフィールドを初期化できませんでした。",
      ajax_unavailable: "サーバー要求に失敗しました。",
      generate_unavailable: "応答を生成できませんでした。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      grammar_init_unavailable: "Kon editor of velden niet initialiseren.",
      ajax_unavailable: "Serververzoek is mislukt.",
      generate_unavailable: "Kon reactie niet genereren.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      grammar_init_unavailable: "Nie można zainicjować edytora lub pól.",
      ajax_unavailable: "Żądanie do serwera nie powiodło się.",
      generate_unavailable: "Nie udało się wygenerować odpowiedzi.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      grammar_init_unavailable:
        "Não foi possível inicializar o editor ou os campos.",
      ajax_unavailable: "Falha na solicitação ao servidor.",
      generate_unavailable: "Não foi possível gerar a resposta.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      grammar_init_unavailable:
        "Não foi possível inicializar o editor ou os campos.",
      ajax_unavailable: "Falha na requisição ao servidor.",
      generate_unavailable: "Não foi possível gerar a resposta.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      grammar_init_unavailable:
        "Не удалось инициализировать редактор или поля.",
      ajax_unavailable: "Сбой запроса к серверу.",
      generate_unavailable: "Не удалось сформировать ответ.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      grammar_init_unavailable: "Düzenleyici veya alanlar başlatılamadı.",
      ajax_unavailable: "Sunucu isteği başarısız oldu.",
      generate_unavailable: "Yanıt oluşturulamadı.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      grammar_init_unavailable: "无法初始化编辑器或字段。",
      ajax_unavailable: "服务器请求失败。",
      generate_unavailable: "无法生成响应。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations![k] = { ...(window.translations![k] || {}), ...t[k] };
  });
})();
