(() => {
    const billPatch = {
        ar: { bill_fetch_failed: "فشل جلب قيمة الفاتورة." },
        da: { bill_fetch_failed: "Kunne ikke hente beløbet for fakturaen." },
        de: { bill_fetch_failed: "Abrufen des Rechnungsbetrags fehlgeschlagen." },
        en: { bill_fetch_failed: "Failed to fetch bill amount." },
        es: { bill_fetch_failed: "Error al obtener el importe de la factura." },
        fr: {
            bill_fetch_failed: "Échec de la récupération du montant de la facture.",
        },
        he: { bill_fetch_failed: "נכשל בקבלת סכום החשבונית." },
        it: {
            bill_fetch_failed: "Impossibile recuperare l’importo della fattura.",
        },
        ja: { bill_fetch_failed: "請求書の金額を取得できませんでした。" },
        nl: { bill_fetch_failed: "Kon factuurbedrag niet ophalen." },
        pl: { bill_fetch_failed: "Nie udało się pobrać kwoty faktury." },
        pt: { bill_fetch_failed: "Falha ao obter o valor da fatura." },
        "pt-br": { bill_fetch_failed: "Falha ao obter o valor da fatura." },
        ru: { bill_fetch_failed: "Не удалось получить сумму счёта." },
        tr: { bill_fetch_failed: "Fatura tutarı alınamadı." },
        zh: { bill_fetch_failed: "获取账单金额失败。" },
    };
    window.translations = Object.keys(window.translations || {}).length
        ? Object.keys(billPatch).reduce((acc, l) => {
            acc[l] = { ...(acc[l] || {}), ...billPatch[l] };
            return acc;
        }, window.translations)
        : billPatch;
})();
//# sourceMappingURL=index.js.map