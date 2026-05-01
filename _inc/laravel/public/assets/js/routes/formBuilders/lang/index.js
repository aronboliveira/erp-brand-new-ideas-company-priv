(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            link_copy_success: "تم نسخ الرابط إلى الحافظة.",
            link_copy_failed: "فشل نسخ الرابط.",
        },
        da: {
            link_copy_success: "Link kopieret til udklipsholder.",
            link_copy_failed: "Kunne ikke kopiere link.",
        },
        de: {
            link_copy_success: "Link in die Zwischenablage kopiert.",
            link_copy_failed: "Kopieren des Links fehlgeschlagen.",
        },
        en: {
            link_copy_success: "Link copied to clipboard.",
            link_copy_failed: "Failed to copy link.",
        },
        es: {
            link_copy_success: "Enlace copiado al portapapeles.",
            link_copy_failed: "Error al copiar el enlace.",
        },
        fr: {
            link_copy_success: "Lien copié dans le presse-papiers.",
            link_copy_failed: "Échec de la copie du lien.",
        },
        he: {
            link_copy_success: "הקישור הועתק ללוח.",
            link_copy_failed: "העתקת הקישור נכשלה.",
        },
        it: {
            link_copy_success: "Link copiato negli appunti.",
            link_copy_failed: "Copia del link non riuscita.",
        },
        ja: {
            link_copy_success: "リンクがクリップボードにコピーされました。",
            link_copy_failed: "リンクのコピーに失敗しました。",
        },
        nl: {
            link_copy_success: "Link gekopieerd naar klembord.",
            link_copy_failed: "Kopiëren van link mislukt.",
        },
        pl: {
            link_copy_success: "Link skopiowany do schowka.",
            link_copy_failed: "Nie udało się skopiować linku.",
        },
        pt: {
            link_copy_success: "Link copiado para a área de transferência.",
            link_copy_failed: "Falha ao copiar o link.",
        },
        "pt-br": {
            link_copy_success: "Link copiado para a área de transferência.",
            link_copy_failed: "Falha ao copiar o link.",
        },
        ru: {
            link_copy_success: "Ссылка скопирована в буфер обмена.",
            link_copy_failed: "Не удалось скопировать ссылку.",
        },
        tr: {
            link_copy_success: "Bağlantı panoya kopyalandı.",
            link_copy_failed: "Bağlantı kopyalanamadı.",
        },
        zh: {
            link_copy_success: "链接已复制到剪贴板。",
            link_copy_failed: "复制链接失败。",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();