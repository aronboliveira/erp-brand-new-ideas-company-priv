(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            image_preview_unavailable: "تعذّر معاينة الصورة",
            toggle_quantity_unavailable: "تعذّر تبديل حقل الكمية",
        },
        da: {
            image_preview_unavailable: "Kunne ikke forhåndsvise billede",
            toggle_quantity_unavailable: "Kunne ikke skifte mængdefelt",
        },
        de: {
            image_preview_unavailable: "Bildvorschau kann nicht angezeigt werden",
            toggle_quantity_unavailable: "Mengenfeld kann nicht umgeschaltet werden",
        },
        en: {
            image_preview_unavailable: "Cannot preview image",
            toggle_quantity_unavailable: "Cannot toggle quantity field",
        },
        es: {
            image_preview_unavailable: "No se puede previsualizar la imagen",
            toggle_quantity_unavailable: "No se puede alternar el campo de cantidad",
        },
        fr: {
            image_preview_unavailable: "Impossible d’afficher l’aperçu de l’image",
            toggle_quantity_unavailable: "Impossible d’activer/désactiver le champ quantité",
        },
        he: {
            image_preview_unavailable: "לא ניתן להציג תצוגה מקדימה לתמונה",
            toggle_quantity_unavailable: "לא ניתן להחליף שדה כמות",
        },
        it: {
            image_preview_unavailable: "Impossibile visualizzare l’anteprima dell’immagine",
            toggle_quantity_unavailable: "Impossibile attivare/disattivare il campo quantità",
        },
        ja: {
            image_preview_unavailable: "画像プレビューを表示できません",
            toggle_quantity_unavailable: "数量フィールドを切り替えできません",
        },
        nl: {
            image_preview_unavailable: "Kan afbeeldingsvoorbeeld niet tonen",
            toggle_quantity_unavailable: "Kan veld hoeveelheid niet schakelen",
        },
        pl: {
            image_preview_unavailable: "Nie można wyświetlić podglądu obrazu",
            toggle_quantity_unavailable: "Nie można przełączyć pola ilości",
        },
        pt: {
            image_preview_unavailable: "Não é possível pré-visualizar a imagem",
            toggle_quantity_unavailable: "Não é possível alternar o campo de quantidade",
        },
        "pt-br": {
            image_preview_unavailable: "Não é possível pré-visualizar a imagem",
            toggle_quantity_unavailable: "Não é possível alternar o campo de quantidade",
        },
        ru: {
            image_preview_unavailable: "Невозможно показать предпросмотр изображения",
            toggle_quantity_unavailable: "Невозможно переключить поле количества",
        },
        tr: {
            image_preview_unavailable: "Görüntü önizlenemiyor",
            toggle_quantity_unavailable: "Miktar alanı değiştirilemiyor",
        },
        zh: {
            image_preview_unavailable: "无法预览图片",
            toggle_quantity_unavailable: "无法切换数量字段",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();
//# sourceMappingURL=create.js.map