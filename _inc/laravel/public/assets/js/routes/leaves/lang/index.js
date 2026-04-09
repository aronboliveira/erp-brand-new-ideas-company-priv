(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
            route_unavailable: "رابط غير صالح.",
            leaves_unavailable: "تعذّر تحميل أنواع الإجازة.",
        },
        da: {
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
            route_unavailable: "Ugyldig URL.",
            leaves_unavailable: "Kunne ikke indlæse fraværstyper.",
        },
        de: {
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
            route_unavailable: "Ungültige URL.",
            leaves_unavailable: "Urlaubstypen konnten nicht geladen werden.",
        },
        en: {
            plugin_unavailable: "A required library failed to load.",
            route_unavailable: "Invalid URL.",
            leaves_unavailable: "Failed to load leave types.",
        },
        es: {
            plugin_unavailable: "No se cargó una biblioteca requerida.",
            route_unavailable: "URL no válida.",
            leaves_unavailable: "No se pudieron cargar los tipos de permisos.",
        },
        fr: {
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
            route_unavailable: "URL non valide.",
            leaves_unavailable: "Échec du chargement des types de congé.",
        },
        he: {
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
            route_unavailable: "כתובת לא חוקית.",
            leaves_unavailable: "נכשל בטעינת סוגי חופשה.",
        },
        it: {
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
            route_unavailable: "URL non valido.",
            leaves_unavailable: "Impossibile caricare i tipi di permesso.",
        },
        ja: {
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
            route_unavailable: "無効なURLです。",
            leaves_unavailable: "休暇タイプを読み込めませんでした。",
        },
        nl: {
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
            route_unavailable: "Ongeldige URL.",
            leaves_unavailable: "Verlofsoorten laden is mislukt.",
        },
        pl: {
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
            route_unavailable: "Nieprawidłowy adres URL.",
            leaves_unavailable: "Nie udało się wczytać typów urlopów.",
        },
        pt: {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            route_unavailable: "URL inválido.",
            leaves_unavailable: "Falha ao carregar os tipos de licença.",
        },
        "pt-br": {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            route_unavailable: "URL inválida.",
            leaves_unavailable: "Falha ao carregar os tipos de licença.",
        },
        ru: {
            plugin_unavailable: "Не загружена необходимая библиотека.",
            route_unavailable: "Недействительный URL.",
            leaves_unavailable: "Не удалось загрузить типы отпусков.",
        },
        tr: {
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
            route_unavailable: "Geçersiz URL.",
            leaves_unavailable: "İzin türleri yüklenemedi.",
        },
        zh: {
            plugin_unavailable: "未能加载所需的库。",
            route_unavailable: "无效的链接。",
            leaves_unavailable: "请假类型加载失败。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();
//# sourceMappingURL=index.js.map