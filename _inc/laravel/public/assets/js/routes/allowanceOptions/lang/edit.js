(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            form_submit_unavailable: "إرسال النموذج غير متاح.",
        },
        da: {
            form_submit_unavailable: "Indsendelse af formularen er ikke tilgængelig.",
        },
        de: {
            form_submit_unavailable: "Formularübermittlung ist nicht verfügbar.",
        },
        en: {
            form_submit_unavailable: "Form submission is unavailable.",
        },
        es: {
            form_submit_unavailable: "El envío del formulario no está disponible.",
        },
        fr: {
            form_submit_unavailable: "La soumission du formulaire n’est pas disponible.",
        },
        he: {
            form_submit_unavailable: "שליחת הטופס אינה זמינה.",
        },
        it: {
            form_submit_unavailable: "Invio del modulo non disponibile.",
        },
        ja: {
            form_submit_unavailable: "フォームの送信は利用できません。",
        },
        nl: {
            form_submit_unavailable: "Formulierverzending is niet beschikbaar.",
        },
        pl: {
            form_submit_unavailable: "Przesyłanie formularza jest niedostępne.",
        },
        pt: {
            form_submit_unavailable: "Envio do formulário indisponível.",
        },
        "pt-br": {
            form_submit_unavailable: "Envio do formulário indisponível.",
        },
        ru: {
            form_submit_unavailable: "Отправка формы недоступна.",
        },
        tr: {
            form_submit_unavailable: "Form gönderimi kullanılamıyor.",
        },
        zh: {
            form_submit_unavailable: "表单提交不可用。",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();