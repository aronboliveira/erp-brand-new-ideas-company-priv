(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      designation_unavailable: "تعذّر تحميل المسميات الوظيفية.",
      datatable_unavailable: "تعذّر تحميل جدول البيانات.",
      element_unavailable: "العنصر المطلوب غير موجود.",
      request_failed: "فشل الطلب.",
    },
    da: {
      designation_unavailable: "Kunne ikke indlæse stillinger.",
      datatable_unavailable: "Kunne ikke indlæse datatabellen.",
      element_unavailable: "Påkrævet element mangler.",
      request_failed: "Forespørgslen mislykkedes.",
    },
    de: {
      designation_unavailable: "Positionen konnten nicht geladen werden.",
      datatable_unavailable: "DataTable konnte nicht geladen werden.",
      element_unavailable: "Erforderliches Element fehlt.",
      request_failed: "Anfrage fehlgeschlagen.",
    },
    en: {
      designation_unavailable: "Could not load designations.",
      datatable_unavailable: "Could not load the datatable.",
      element_unavailable: "Required element is missing.",
      request_failed: "The request failed.",
    },
    es: {
      designation_unavailable: "No se pudieron cargar las designaciones.",
      datatable_unavailable: "No se pudo cargar la tabla.",
      element_unavailable: "Falta el elemento requerido.",
      request_failed: "La solicitud falló.",
    },
    fr: {
      designation_unavailable: "Impossible de charger les fonctions.",
      datatable_unavailable: "Impossible de charger le tableau.",
      element_unavailable: "Élément requis manquant.",
      request_failed: "La requête a échoué.",
    },
    he: {
      designation_unavailable: "לא ניתן לטעון תפקידים.",
      datatable_unavailable: "לא ניתן לטעון טבלת נתונים.",
      element_unavailable: "האלמנט הנדרש חסר.",
      request_failed: "הבקשה נכשלה.",
    },
    it: {
      designation_unavailable: "Impossibile caricare le mansioni.",
      datatable_unavailable: "Impossibile caricare la tabella.",
      element_unavailable: "Elemento richiesto mancante.",
      request_failed: "Richiesta non riuscita.",
    },
    ja: {
      designation_unavailable: "役職を読み込めませんでした。",
      datatable_unavailable: "データテーブルを読み込めませんでした。",
      element_unavailable: "必要な要素が見つかりません。",
      request_failed: "リクエストに失敗しました。",
    },
    nl: {
      designation_unavailable: "Kon functies niet laden.",
      datatable_unavailable: "Kon de datatabel niet laden.",
      element_unavailable: "Vereist element ontbreekt.",
      request_failed: "Aanvraag mislukt.",
    },
    pl: {
      designation_unavailable: "Nie można załadować stanowisk.",
      datatable_unavailable: "Nie można załadować tabeli.",
      element_unavailable: "Brakuje wymaganego elementu.",
      request_failed: "Żądanie nie powiodło się.",
    },
    pt: {
      designation_unavailable: "Não foi possível carregar as funções.",
      datatable_unavailable: "Não foi possível carregar a tabela.",
      element_unavailable: "Elemento necessário ausente.",
      request_failed: "A solicitação falhou.",
    },
    "pt-br": {
      designation_unavailable: "Não foi possível carregar os cargos.",
      datatable_unavailable: "Não foi possível carregar a tabela.",
      element_unavailable: "Elemento obrigatório ausente.",
      request_failed: "A solicitação falhou.",
    },
    ru: {
      designation_unavailable: "Не удалось загрузить должности.",
      datatable_unavailable: "Не удалось загрузить таблицу.",
      element_unavailable: "Отсутствует необходимый элемент.",
      request_failed: "Запрос не выполнен.",
    },
    tr: {
      designation_unavailable: "Unvanlar yüklenemedi.",
      datatable_unavailable: "Veri tablosu yüklenemedi.",
      element_unavailable: "Gerekli öğe eksik.",
      request_failed: "İstek başarısız oldu.",
    },
    zh: {
      designation_unavailable: "无法加载职位。",
      datatable_unavailable: "无法加载数据表。",
      element_unavailable: "缺少所需元素。",
      request_failed: "请求失败。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
