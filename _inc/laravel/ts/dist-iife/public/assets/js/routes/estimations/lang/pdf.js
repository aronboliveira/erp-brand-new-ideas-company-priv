(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: { estimate_pdf_unavailable: "فشل إنشاء ملف PDF للتقدير." },
        da: { estimate_pdf_unavailable: "Kunne ikke generere PDF for estimat." },
        de: {
            estimate_pdf_unavailable: "PDF-Erstellung für Kostenvoranschlag fehlgeschlagen.",
        },
        en: { estimate_pdf_unavailable: "Failed to generate estimate PDF." },
        es: {
            estimate_pdf_unavailable: "Error al generar el PDF del presupuesto.",
        },
        fr: { estimate_pdf_unavailable: "Échec de la génération du PDF du devis." },
        he: { estimate_pdf_unavailable: "לא ניתן ליצור PDF להצעת המחיר." },
        it: {
            estimate_pdf_unavailable: "Impossibile generare il PDF del preventivo.",
        },
        ja: { estimate_pdf_unavailable: "見積書のPDFを生成できませんでした。" },
        nl: { estimate_pdf_unavailable: "Kon PDF voor schatting niet genereren." },
        pl: {
            estimate_pdf_unavailable: "Nie udało się wygenerować pliku PDF wyceny.",
        },
        pt: { estimate_pdf_unavailable: "Falha ao gerar PDF da estimativa." },
        "pt-br": { estimate_pdf_unavailable: "Falha ao gerar PDF do orçamento." },
        ru: { estimate_pdf_unavailable: "Не удалось создать PDF сметы." },
        tr: { estimate_pdf_unavailable: "Keşif PDF'i oluşturulamadı." },
        zh: { estimate_pdf_unavailable: "生成估算 PDF 失败。" },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();