(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            edit_journal_entry_unavailable: "مسار التعديل غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال.",
            delete_journal_entry_unavailable: "مسار الحذف غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال.",
        },
        da: {
            edit_journal_entry_unavailable: "Redigeringsruten er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator.",
            delete_journal_entry_unavailable: "Sletningsruten er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator.",
        },
        de: {
            edit_journal_entry_unavailable: "Bearbeitungsroute nicht verfügbar. Bitte kontaktieren Sie den technischen Support oder Ihren Domain-Administrator.",
            delete_journal_entry_unavailable: "Löschroute nicht verfügbar. Bitte kontaktieren Sie den technischen Support oder Ihren Domain-Administrator.",
        },
        en: {
            edit_journal_entry_unavailable: "Edit route is unavailable. Please contact technical support or your domain administrator.",
            delete_journal_entry_unavailable: "Delete route is unavailable. Please contact technical support or your domain administrator.",
        },
        es: {
            edit_journal_entry_unavailable: "La ruta de edición no está disponible. Por favor, contacte al soporte técnico o a su administrador de dominio.",
            delete_journal_entry_unavailable: "La ruta de eliminación no está disponible. Por favor, contacte al soporte técnico o a su administrador de dominio.",
        },
        fr: {
            edit_journal_entry_unavailable: "La route d'édition n'est pas disponible. Veuillez contacter le support technique ou votre administrateur de domaine.",
            delete_journal_entry_unavailable: "La route de suppression n'est pas disponible. Veuillez contacter le support technique ou votre administrateur de domaine.",
        },
        he: {
            edit_journal_entry_unavailable: "נתיב העריכה אינו זמין. אנא צור קשר עם התמיכה הטכנית או עם מנהל הדומיין שלך.",
            delete_journal_entry_unavailable: "נתיב המחיקה אינו זמין. אנא צור קשר עם התמיכה הטכנית או עם מנהל הדומיין שלך.",
        },
        it: {
            edit_journal_entry_unavailable: "La rotta di modifica non è disponibile. Si prega di contattare il supporto tecnico o l'amministratore del dominio.",
            delete_journal_entry_unavailable: "La rotta di eliminazione non è disponibile. Si prega di contattare il supporto tecnico o l'amministratore del dominio.",
        },
        ja: {
            edit_journal_entry_unavailable: "編集ルートが利用できません。テクニカルサポートまたはドメイン管理者に連絡してください。",
            delete_journal_entry_unavailable: "削除ルートが利用できません。テクニカルサポートまたはドメイン管理者に連絡してください。",
        },
        nl: {
            edit_journal_entry_unavailable: "Bewerkingsroute is niet beschikbaar. Neem contact op met technische ondersteuning of uw domeinbeheerder.",
            delete_journal_entry_unavailable: "Verwijderingsroute is niet beschikbaar. Neem contact op met technische ondersteuning of uw domeinbeheerder.",
        },
        pl: {
            edit_journal_entry_unavailable: "Trasa edycji jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
            delete_journal_entry_unavailable: "Trasa usuwania jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
        },
        pt: {
            edit_journal_entry_unavailable: "A rota de edição não está disponível. Entre em contato com o suporte técnico ou o administrador do domínio.",
            delete_journal_entry_unavailable: "A rota de exclusão não está disponível. Entre em contato com o suporte técnico ou o administrador do domínio.",
        },
        "pt-br": {
            edit_journal_entry_unavailable: "A rota de edição não está disponível. Entre em contato com o suporte técnico ou o administrador do domínio.",
            delete_journal_entry_unavailable: "A rota de exclusão não está disponível. Entre em contato com o suporte técnico ou o administrador do domínio.",
        },
        ru: {
            edit_journal_entry_unavailable: "Маршрут редактирования недоступен. Пожалуйста, обратитесь в техническую поддержку или к администратору домена.",
            delete_journal_entry_unavailable: "Маршрут удаления недоступен. Пожалуйста, обратитесь в техническую поддержку или к администратору домена.",
        },
        tr: {
            edit_journal_entry_unavailable: "Düzenleme rotası kullanılamıyor. Lütfen teknik destek veya alan yöneticinizle iletişime geçin.",
            delete_journal_entry_unavailable: "Silme rotası kullanılamıyor. Lütfen teknik destek veya alan yöneticinizle iletişime geçin.",
        },
        zh: {
            edit_journal_entry_unavailable: "编辑路由不可用。请联系技术支持或您的域管理员。",
            delete_journal_entry_unavailable: "删除路由不可用。请联系技术支持或您的域管理员。",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();