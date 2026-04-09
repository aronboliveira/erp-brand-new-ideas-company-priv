(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: { attachment_preview_unavailable: "تعذّر معاينة المرفق" },
        da: {
            attachment_preview_unavailable: "Kunne ikke forhåndsvise vedhæftning",
        },
        de: {
            attachment_preview_unavailable: "Anhang kann nicht in der Vorschau angezeigt werden",
        },
        en: { attachment_preview_unavailable: "Cannot preview attachment" },
        es: {
            attachment_preview_unavailable: "No se puede previsualizar el adjunto",
        },
        fr: {
            attachment_preview_unavailable: "Impossible d’afficher l’aperçu de la pièce jointe",
        },
        he: {
            attachment_preview_unavailable: "לא ניתן להציג תצוגה מקדימה לקובץ המצורף",
        },
        it: {
            attachment_preview_unavailable: "Impossibile visualizzare l’anteprima dell’allegato",
        },
        ja: {
            attachment_preview_unavailable: "添付ファイルをプレビューできません",
        },
        nl: {
            attachment_preview_unavailable: "Bijlage kan niet worden weergegeven",
        },
        pl: {
            attachment_preview_unavailable: "Nie można wyświetlić podglądu załącznika",
        },
        pt: {
            attachment_preview_unavailable: "Não é possível pré-visualizar o anexo",
        },
        "pt-br": {
            attachment_preview_unavailable: "Não é possível pré-visualizar o anexo",
        },
        ru: {
            attachment_preview_unavailable: "Не удаётся показать предварительный просмотр вложения",
        },
        tr: { attachment_preview_unavailable: "Ek önizlenemiyor" },
        zh: { attachment_preview_unavailable: "无法预览附件" },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();
//# sourceMappingURL=attachments.js.map