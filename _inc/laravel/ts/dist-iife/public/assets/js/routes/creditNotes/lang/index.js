(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            invoice_fetch_unavailable: "فشل جلب بيانات الفاتورة.",
        },
        da: {
            invoice_fetch_unavailable: "Kunne ikke hente fakturadata.",
        },
        de: {
            invoice_fetch_unavailable: "Abruf der Rechnungsdaten fehlgeschlagen.",
        },
        en: {
            invoice_fetch_unavailable: "Failed to fetch invoice data.",
        },
        es: {
            invoice_fetch_unavailable: "Error al obtener los datos de la factura.",
        },
        fr: {
            invoice_fetch_unavailable: "Échec de la récupération des données de la facture.",
        },
        he: {
            invoice_fetch_unavailable: "הנתונים של החשבונית לא נטענו.",
        },
        it: {
            invoice_fetch_unavailable: "Impossibile recuperare i dati della fattura.",
        },
        ja: {
            invoice_fetch_unavailable: "請求書データの取得に失敗しました。",
        },
        nl: {
            invoice_fetch_unavailable: "Kon factuurgegevens niet ophalen.",
        },
        pl: {
            invoice_fetch_unavailable: "Nie udało się pobrać danych faktury.",
        },
        pt: {
            invoice_fetch_unavailable: "Falha ao obter dados da fatura.",
        },
        "pt-br": {
            invoice_fetch_unavailable: "Falha ao obter dados da fatura.",
        },
        ru: {
            invoice_fetch_unavailable: "Не удалось получить данные счета.",
        },
        tr: {
            invoice_fetch_unavailable: "Fatura verileri alınamadı.",
        },
        zh: {
            invoice_fetch_unavailable: "获取发票数据失败。",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();