(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
            ajax_unavailable: "تعذّر الاتصال بالخادم.",
            color_update_unavailable: "تعذّر تحديث لون الأولوية.",
        },
        da: {
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
            ajax_unavailable: "Serveren kunne ikke kontaktes.",
            color_update_unavailable: "Kunne ikke opdatere prioritetens farve.",
        },
        de: {
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
            ajax_unavailable: "Serveranfrage fehlgeschlagen.",
            color_update_unavailable: "Prioritätsfarbe konnte nicht aktualisiert werden.",
        },
        en: {
            plugin_unavailable: "A required library failed to load.",
            ajax_unavailable: "Server request failed.",
            color_update_unavailable: "Could not update priority color.",
        },
        es: {
            plugin_unavailable: "No se cargó una biblioteca requerida.",
            ajax_unavailable: "Falló la solicitud al servidor.",
            color_update_unavailable: "No se pudo actualizar el color de prioridad.",
        },
        fr: {
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
            ajax_unavailable: "Échec de la requête serveur.",
            color_update_unavailable: "Impossible de mettre à jour la couleur de priorité.",
        },
        he: {
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
            ajax_unavailable: "בקשת השרת נכשלה.",
            color_update_unavailable: "לא ניתן היה לעדכן את צבע העדיפות.",
        },
        it: {
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
            ajax_unavailable: "Richiesta al server non riuscita.",
            color_update_unavailable: "Impossibile aggiornare il colore di priorità.",
        },
        ja: {
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
            ajax_unavailable: "サーバー要求に失敗しました。",
            color_update_unavailable: "優先度の色を更新できませんでした。",
        },
        nl: {
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
            ajax_unavailable: "Serververzoek is mislukt.",
            color_update_unavailable: "Prioriteitskleur kon niet worden bijgewerkt.",
        },
        pl: {
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
            ajax_unavailable: "Żądanie do serwera nie powiodło się.",
            color_update_unavailable: "Nie udało się zaktualizować koloru priorytetu.",
        },
        pt: {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            ajax_unavailable: "Falha na solicitação ao servidor.",
            color_update_unavailable: "Não foi possível atualizar a cor da prioridade.",
        },
        "pt-br": {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            ajax_unavailable: "Falha na requisição ao servidor.",
            color_update_unavailable: "Não foi possível atualizar a cor da prioridade.",
        },
        ru: {
            plugin_unavailable: "Не загружена необходимая библиотека.",
            ajax_unavailable: "Сбой запроса к серверу.",
            color_update_unavailable: "Не удалось обновить цвет приоритета.",
        },
        tr: {
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
            ajax_unavailable: "Sunucu isteği başarısız oldu.",
            color_update_unavailable: "Öncelik rengi güncellenemedi.",
        },
        zh: {
            plugin_unavailable: "未能加载所需的库。",
            ajax_unavailable: "服务器请求失败。",
            color_update_unavailable: "无法更新优先级颜色。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();
//# sourceMappingURL=color.js.map