(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
            dragula_unavailable: "إضافة السحب والإفلات غير متاحة.",
            leads_order_unavailable: "تعذّر تحديث ترتيب العملاء المحتملين.",
            route_unavailable: "رابط غير صالح.",
            form_unavailable: "تعذّر إرسال النموذج.",
        },
        da: {
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
            dragula_unavailable: "Træk-og-slip-plugin er ikke tilgængeligt.",
            leads_order_unavailable: "Kunne ikke opdatere rækkefølgen af leads.",
            route_unavailable: "Ugyldig URL.",
            form_unavailable: "Kunne ikke indsende formularen.",
        },
        de: {
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
            dragula_unavailable: "Drag-and-drop-Plugin ist nicht verfügbar.",
            leads_order_unavailable: "Lead-Reihenfolge konnte nicht aktualisiert werden.",
            route_unavailable: "Ungültige URL.",
            form_unavailable: "Formular konnte nicht gesendet werden.",
        },
        en: {
            plugin_unavailable: "A required library failed to load.",
            dragula_unavailable: "Drag & drop plugin is unavailable.",
            leads_order_unavailable: "Could not update lead order.",
            route_unavailable: "Invalid URL.",
            form_unavailable: "Could not submit the form.",
        },
        es: {
            plugin_unavailable: "No se cargó una biblioteca requerida.",
            dragula_unavailable: "El complemento de arrastrar y soltar no está disponible.",
            leads_order_unavailable: "No se pudo actualizar el orden de leads.",
            route_unavailable: "URL no válida.",
            form_unavailable: "No se pudo enviar el formulario.",
        },
        fr: {
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
            dragula_unavailable: "Le module de glisser-déposer est indisponible.",
            leads_order_unavailable: "Impossible de mettre à jour l’ordre des prospects.",
            route_unavailable: "URL non valide.",
            form_unavailable: "Impossible d’envoyer le formulaire.",
        },
        he: {
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
            dragula_unavailable: "תוסף גרירה ושחרור אינו זמין.",
            leads_order_unavailable: "לא ניתן לעדכן את סדר הלידים.",
            route_unavailable: "כתובת לא חוקית.",
            form_unavailable: "לא ניתן לשלוח את הטופס.",
        },
        it: {
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
            dragula_unavailable: "Il plugin di trascinamento non è disponibile.",
            leads_order_unavailable: "Impossibile aggiornare l’ordine dei lead.",
            route_unavailable: "URL non valido.",
            form_unavailable: "Impossibile inviare il modulo.",
        },
        ja: {
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
            dragula_unavailable: "ドラッグ＆ドロップのプラグインが利用できません。",
            leads_order_unavailable: "リードの順序を更新できませんでした。",
            route_unavailable: "無効なURLです。",
            form_unavailable: "フォームを送信できませんでした。",
        },
        nl: {
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
            dragula_unavailable: "Drag-en-drop-plugin is niet beschikbaar.",
            leads_order_unavailable: "Kon de volgorde van leads niet bijwerken.",
            route_unavailable: "Ongeldige URL.",
            form_unavailable: "Kon het formulier niet verzenden.",
        },
        pl: {
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
            dragula_unavailable: "Wtyczka przeciągnij-i-upuść jest niedostępna.",
            leads_order_unavailable: "Nie udało się zaktualizować kolejności leadów.",
            route_unavailable: "Nieprawidłowy adres URL.",
            form_unavailable: "Nie można wysłać formularza.",
        },
        pt: {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            dragula_unavailable: "Plugin de arrastar e soltar indisponível.",
            leads_order_unavailable: "Não foi possível atualizar a ordem dos leads.",
            route_unavailable: "URL inválido.",
            form_unavailable: "Não foi possível enviar o formulário.",
        },
        "pt-br": {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            dragula_unavailable: "Plugin de arrastar e soltar indisponível.",
            leads_order_unavailable: "Não foi possível atualizar a ordem dos leads.",
            route_unavailable: "URL inválido.",
            form_unavailable: "Não foi possível enviar o formulário.",
        },
        ru: {
            plugin_unavailable: "Не загружена необходимая библиотека.",
            dragula_unavailable: "Плагин перетаскивания недоступен.",
            leads_order_unavailable: "Не удалось обновить порядок лидов.",
            route_unavailable: "Недействительный URL.",
            form_unavailable: "Не удалось отправить форму.",
        },
        tr: {
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
            dragula_unavailable: "Sürükle-bırak eklentisi kullanılamıyor.",
            leads_order_unavailable: "Lead sırası güncellenemedi.",
            route_unavailable: "Geçersiz URL.",
            form_unavailable: "Form gönderilemedi.",
        },
        zh: {
            plugin_unavailable: "未能加载所需的库。",
            dragula_unavailable: "拖放插件不可用。",
            leads_order_unavailable: "无法更新线索顺序。",
            route_unavailable: "无效的链接。",
            form_unavailable: "无法提交表单。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();