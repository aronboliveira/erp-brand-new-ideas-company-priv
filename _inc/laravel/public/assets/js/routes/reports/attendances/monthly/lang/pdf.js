(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
      pdf_unavailable: "تعذّر إنشاء ملف PDF الآن.",
      endpoint_unavailable: "المسار المطلوب غير متاح.",
      department_unavailable: "تعذّر تحميل الأقسام.",
      employee_unavailable: "تعذّر تحميل الموظفين.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      pdf_unavailable: "Kunne ikke generere PDF lige nu.",
      endpoint_unavailable: "Den ønskede sti er ikke tilgængelig.",
      department_unavailable: "Afdelinger kunne ikke indlæses.",
      employee_unavailable: "Medarbejdere kunne ikke indlæses.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      pdf_unavailable: "PDF konnte derzeit nicht erstellt werden.",
      endpoint_unavailable: "Angeforderter Endpunkt ist nicht verfügbar.",
      department_unavailable: "Abteilungen konnten nicht geladen werden.",
      employee_unavailable: "Mitarbeiter konnten nicht geladen werden.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      pdf_unavailable: "PDF export failed.",
      endpoint_unavailable: "Requested endpoint is unavailable.",
      department_unavailable: "Failed to load departments.",
      employee_unavailable: "Failed to load employees.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      pdf_unavailable: "La exportación a PDF falló.",
      endpoint_unavailable: "El endpoint solicitado no está disponible.",
      department_unavailable: "No se pudieron cargar los departamentos.",
      employee_unavailable: "No se pudieron cargar los empleados.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      pdf_unavailable: "L’export PDF a échoué.",
      endpoint_unavailable: "Le point de terminaison demandé est indisponible.",
      department_unavailable: "Échec du chargement des services.",
      employee_unavailable: "Échec du chargement des employés.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      pdf_unavailable: "ייצוא ה-PDF נכשל.",
      endpoint_unavailable: "נקודת הקצה המבוקשת אינה זמינה.",
      department_unavailable: "טעינת המחלקות נכשלה.",
      employee_unavailable: "טעינת העובדים נכשלה.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      pdf_unavailable: "Esportazione PDF non riuscita.",
      endpoint_unavailable: "L’endpoint richiesto non è disponibile.",
      department_unavailable: "Impossibile caricare i reparti.",
      employee_unavailable: "Impossibile caricare i dipendenti.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      pdf_unavailable: "PDF の書き出しに失敗しました。",
      endpoint_unavailable: "要求されたエンドポイントは利用できません。",
      department_unavailable: "部署を読み込めませんでした。",
      employee_unavailable: "従業員を読み込めませんでした。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      pdf_unavailable: "PDF-export is mislukt.",
      endpoint_unavailable: "Aangevraagde endpoint is niet beschikbaar.",
      department_unavailable: "Afdelingen laden mislukt.",
      employee_unavailable: "Werknemers laden mislukt.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      pdf_unavailable: "Eksport do PDF nie powiódł się.",
      endpoint_unavailable: "Żądany endpoint jest niedostępny.",
      department_unavailable: "Nie udało się wczytać działów.",
      employee_unavailable: "Nie udało się wczytać pracowników.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      pdf_unavailable: "Falha na exportação para PDF.",
      endpoint_unavailable: "Endpoint solicitado indisponível.",
      department_unavailable: "Falha ao carregar departamentos.",
      employee_unavailable: "Falha ao carregar colaboradores.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      pdf_unavailable: "Falha ao exportar o PDF.",
      endpoint_unavailable: "Endpoint solicitado indisponível.",
      department_unavailable: "Falha ao carregar departamentos.",
      employee_unavailable: "Falha ao carregar colaboradores.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      pdf_unavailable: "Не удалось выполнить экспорт PDF.",
      endpoint_unavailable: "Запрошенная точка недоступна.",
      department_unavailable: "Не удалось загрузить отделы.",
      employee_unavailable: "Не удалось загрузить сотрудников.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      pdf_unavailable: "PDF dışa aktarma başarısız oldu.",
      endpoint_unavailable: "İstenen uç nokta kullanılamıyor.",
      department_unavailable: "Birimler yüklenemedi.",
      employee_unavailable: "Çalışanlar yüklenemedi.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      pdf_unavailable: "PDF 导出失败。",
      endpoint_unavailable: "请求的端点不可用。",
      department_unavailable: "无法加载部门。",
      employee_unavailable: "无法加载员工。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
