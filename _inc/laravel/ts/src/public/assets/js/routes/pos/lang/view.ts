/**
 * @fileoverview TypeScript version of public/assets/js/routes/pos/lang/view.js
 * @generated from original JavaScript — automated migration
 * @module view
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
  Object.keys(t).forEach(k => {
    window.translations![k] = { ...(window.translations![k] || {}), ...t[k] };
  });
  (function () {
    const t = {
      ar: {
        action_unavailable: "الإجراء غير متاح.",
        dashboard_unavailable: "لوحة التحكم غير متاحة.",
        report_route_unavailable: "تقرير نقاط البيع غير متاح.",
        pdf_route_unavailable: "ملف PDF غير متاح.",
      },
      da: {
        action_unavailable: "Handling ikke tilgængelig.",
        dashboard_unavailable: "Dashboard er ikke tilgængeligt.",
        report_route_unavailable: "POS-rapport er ikke tilgængelig.",
        pdf_route_unavailable: "PDF er ikke tilgængelig.",
      },
      de: {
        action_unavailable: "Aktion nicht verfügbar.",
        dashboard_unavailable: "Dashboard ist nicht verfügbar.",
        report_route_unavailable: "POS-Bericht ist nicht verfügbar.",
        pdf_route_unavailable: "PDF ist nicht verfügbar.",
      },
      en: {
        action_unavailable: "Action unavailable.",
        dashboard_unavailable: "Dashboard route is unavailable.",
        report_route_unavailable: "POS report route is unavailable.",
        pdf_route_unavailable: "PDF route is unavailable.",
      },
      es: {
        action_unavailable: "Acción no disponible.",
        dashboard_unavailable: "La ruta del panel no está disponible.",
        report_route_unavailable: "El informe de POS no está disponible.",
        pdf_route_unavailable: "La ruta del PDF no está disponible.",
      },
      fr: {
        action_unavailable: "Action indisponible.",
        dashboard_unavailable: "La route du tableau de bord est indisponible.",
        report_route_unavailable: "Rapport POS indisponible.",
        pdf_route_unavailable: "Route PDF indisponible.",
      },
      he: {
        action_unavailable: "הפעולה אינה זמינה.",
        dashboard_unavailable: "כתובת לוח הבקרה אינה זמינה.",
        report_route_unavailable: "דוח POS אינו זמין.",
        pdf_route_unavailable: "כתובת ה-PDF אינה זמינה.",
      },
      it: {
        action_unavailable: "Azione non disponibile.",
        dashboard_unavailable: "Percorso dashboard non disponibile.",
        report_route_unavailable: "Report POS non disponibile.",
        pdf_route_unavailable: "Percorso PDF non disponibile.",
      },
      ja: {
        action_unavailable: "操作を利用できません。",
        dashboard_unavailable: "ダッシュボードのルートは利用できません。",
        report_route_unavailable: "POSレポートは利用できません。",
        pdf_route_unavailable: "PDFのルートは利用できません。",
      },
      nl: {
        action_unavailable: "Actie niet beschikbaar.",
        dashboard_unavailable: "Dashboardroute is niet beschikbaar.",
        report_route_unavailable: "POS-rapport is niet beschikbaar.",
        pdf_route_unavailable: "PDF-route is niet beschikbaar.",
      },
      pl: {
        action_unavailable: "Akcja niedostępna.",
        dashboard_unavailable: "Trasa do panelu jest niedostępna.",
        report_route_unavailable: "Raport POS jest niedostępny.",
        pdf_route_unavailable: "Trasa do PDF jest niedostępna.",
      },
      pt: {
        action_unavailable: "Ação indisponível.",
        dashboard_unavailable: "Rota do painel indisponível.",
        report_route_unavailable: "Relatório do PDV indisponível.",
        pdf_route_unavailable: "Rota do PDF indisponível.",
      },
      "pt-br": {
        action_unavailable: "Ação indisponível.",
        dashboard_unavailable: "Rota do painel indisponível.",
        report_route_unavailable: "Relatório do PDV indisponível.",
        pdf_route_unavailable: "Rota do PDF indisponível.",
      },
      ru: {
        action_unavailable: "Действие недоступно.",
        dashboard_unavailable: "Маршрут к панели недоступен.",
        report_route_unavailable: "Отчет POS недоступен.",
        pdf_route_unavailable: "Маршрут к PDF недоступен.",
      },
      tr: {
        action_unavailable: "Eylem kullanılamıyor.",
        dashboard_unavailable: "Panel rotası kullanılamıyor.",
        report_route_unavailable: "POS raporu kullanılamıyor.",
        pdf_route_unavailable: "PDF rotası kullanılamıyor.",
      },
      zh: {
        action_unavailable: "无法执行此操作。",
        dashboard_unavailable: "仪表板路径不可用。",
        report_route_unavailable: "POS 报告不可用。",
        pdf_route_unavailable: "PDF 路径不可用。",
      },
    };
    if (!window.translations) window.translations = t;
    else {
      Object.keys(t).forEach(function (k) {
        window.translations![k] = Object.assign(
          {},
          window.translations![k] || {},
          (t as Record<string, Record<string, string>>)[k]
        );
      });
    }
  })();
})();
