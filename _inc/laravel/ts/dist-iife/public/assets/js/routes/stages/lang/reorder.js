(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
            reorder_unavailable: "تعذّر حفظ ترتيب العناصر.",
            ajax_unavailable: "تعذّر الاتصال بالخادم.",
        },
        da: {
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
            reorder_unavailable: "Kunne ikke gemme sorteringsrækkefølgen.",
            ajax_unavailable: "Serveren kunne ikke kontaktes.",
        },
        de: {
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
            reorder_unavailable: "Anordnung konnte nicht gespeichert werden.",
            ajax_unavailable: "Serververbindung fehlgeschlagen.",
        },
        en: {
            plugin_unavailable: "A required library failed to load.",
            reorder_unavailable: "Could not save item order.",
            ajax_unavailable: "Server request failed.",
        },
        es: {
            plugin_unavailable: "No se cargó una biblioteca requerida.",
            reorder_unavailable: "No se pudo guardar el orden.",
            ajax_unavailable: "La solicitud al servidor falló.",
        },
        fr: {
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
            reorder_unavailable: "Impossible d’enregistrer l’ordre.",
            ajax_unavailable: "Échec de la requête serveur.",
        },
        he: {
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
            reorder_unavailable: "לא ניתן לשמור את הסדר.",
            ajax_unavailable: "בקשת השרת נכשלה.",
        },
        it: {
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
            reorder_unavailable: "Impossibile salvare l’ordinamento.",
            ajax_unavailable: "Richiesta al server non riuscita.",
        },
        ja: {
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
            reorder_unavailable: "並び順を保存できませんでした。",
            ajax_unavailable: "サーバーへの要求に失敗しました。",
        },
        nl: {
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
            reorder_unavailable: "Kon de volgorde niet opslaan.",
            ajax_unavailable: "Serververzoek is mislukt.",
        },
        pl: {
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
            reorder_unavailable: "Nie można zapisać kolejności.",
            ajax_unavailable: "Żądanie do serwera nie powiodło się.",
        },
        pt: {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            reorder_unavailable: "Não foi possível salvar a ordem.",
            ajax_unavailable: "Falha na solicitação ao servidor.",
        },
        "pt-br": {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            reorder_unavailable: "Não foi possível salvar a ordem.",
            ajax_unavailable: "Falha na requisição ao servidor.",
        },
        ru: {
            plugin_unavailable: "Не загружена необходимая библиотека.",
            reorder_unavailable: "Не удалось сохранить порядок.",
            ajax_unavailable: "Ошибка запроса к серверу.",
        },
        tr: {
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
            reorder_unavailable: "Sıralama kaydedilemedi.",
            ajax_unavailable: "Sunucu isteği başarısız oldu.",
        },
        zh: {
            plugin_unavailable: "未能加载所需的库。",
            reorder_unavailable: "无法保存排序。",
            ajax_unavailable: "服务器请求失败。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();