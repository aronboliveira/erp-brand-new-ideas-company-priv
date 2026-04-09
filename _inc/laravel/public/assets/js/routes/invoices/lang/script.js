(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: { invoice_pdf_unavailable: "لا يمكن إنشاء ملف PDF" },
        da: { invoice_pdf_unavailable: "Kan ikke generere PDF" },
        de: { invoice_pdf_unavailable: "PDF konnte nicht erstellt werden" },
        en: { invoice_pdf_unavailable: "Cannot generate PDF" },
        es: { invoice_pdf_unavailable: "No se puede generar PDF" },
        fr: { invoice_pdf_unavailable: "Impossible de générer le PDF" },
        he: { invoice_pdf_unavailable: "לא ניתן ליצור קובץ PDF" },
        it: { invoice_pdf_unavailable: "Impossibile generare PDF" },
        ja: { invoice_pdf_unavailable: "PDF を生成できません" },
        nl: { invoice_pdf_unavailable: "Kan geen PDF genereren" },
        pl: { invoice_pdf_unavailable: "Nie można wygenerować PDF" },
        pt: { invoice_pdf_unavailable: "Não foi possível gerar PDF" },
        "pt-br": { invoice_pdf_unavailable: "Não foi possível gerar PDF" },
        ru: { invoice_pdf_unavailable: "Не удалось создать PDF" },
        tr: { invoice_pdf_unavailable: "PDF oluşturulamıyor" },
        zh: { invoice_pdf_unavailable: "无法生成 PDF" },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();
//# sourceMappingURL=script.js.map