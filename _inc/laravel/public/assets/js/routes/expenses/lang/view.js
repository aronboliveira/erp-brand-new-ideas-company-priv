(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: { shipping_unavailable: "تعذّر تنفيذ الإجراء الخاص بالشحن." },
        da: { shipping_unavailable: "Kunne ikke udføre forsendelseshandlingen." },
        de: {
            shipping_unavailable: "Vorgang „Versand“ konnte nicht ausgeführt werden.",
        },
        en: { shipping_unavailable: "Could not perform the shipping action." },
        es: { shipping_unavailable: "No se pudo realizar la acción de envío." },
        fr: {
            shipping_unavailable: "Impossible d’exécuter l’action d’expédition.",
        },
        he: { shipping_unavailable: "לא ניתן לבצע את פעולת המשלוח." },
        it: {
            shipping_unavailable: "Impossibile eseguire l'azione di spedizione.",
        },
        ja: { shipping_unavailable: "配送アクションを実行できませんでした。" },
        nl: { shipping_unavailable: "Kan de verzendactie niet uitvoeren." },
        pl: { shipping_unavailable: "Nie można wykonać akcji wysyłki." },
        pt: { shipping_unavailable: "Não foi possível executar a ação de envio." },
        "pt-br": {
            shipping_unavailable: "Não foi possível executar a ação de envio.",
        },
        ru: { shipping_unavailable: "Не удалось выполнить действие доставки." },
        tr: { shipping_unavailable: "Gönderim işlemi gerçekleştirilemedi." },
        zh: { shipping_unavailable: "无法执行发货操作。" },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();