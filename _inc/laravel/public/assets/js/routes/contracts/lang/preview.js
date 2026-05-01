(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: { contract_export_unavailable: "تعذّر إنشاء ملف PDF للعقد." },
        da: { contract_export_unavailable: "Kunne ikke oprette kontraktens PDF." },
        de: {
            contract_export_unavailable: "PDF-Erstellung für Vertrag fehlgeschlagen.",
        },
        en: { contract_export_unavailable: "Could not generate the contract PDF." },
        es: {
            contract_export_unavailable: "No se pudo generar el PDF del contrato.",
        },
        fr: {
            contract_export_unavailable: "Impossible de générer le PDF du contrat.",
        },
        he: { contract_export_unavailable: "לא ניתן היה ליצור PDF של החוזה." },
        it: {
            contract_export_unavailable: "Impossibile generare il PDF del contratto.",
        },
        ja: { contract_export_unavailable: "契約書のPDFを生成できませんでした。" },
        nl: { contract_export_unavailable: "Kan het contract-PDF niet genereren." },
        pl: { contract_export_unavailable: "Nie udało się wygenerować PDF umowy." },
        pt: {
            contract_export_unavailable: "Não foi possível gerar o PDF do contrato.",
        },
        "pt-br": {
            contract_export_unavailable: "Não foi possível gerar o PDF do contrato.",
        },
        ru: { contract_export_unavailable: "Не удалось создать PDF договора." },
        tr: { contract_export_unavailable: "Sözleşme PDF’i oluşturulamadı." },
        zh: { contract_export_unavailable: "无法生成合同 PDF。" },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();