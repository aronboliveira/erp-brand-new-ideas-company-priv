(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            pos_fetch_unavailable: "تعذّر جلب المنتجات من المستودع",
            copy_unavailable: "تعذّر نسخ الرابط",
            copy_success: "تم نسخ الرابط إلى الحافظة",
            pdf_unavailable: "تعذّر إنشاء ملف PDF",
        },
        da: {
            pos_fetch_unavailable: "Kunne ikke hente produkter fra lageret",
            copy_unavailable: "Kunne ikke kopiere linket",
            copy_success: "Link kopieret til udklipsholder",
            pdf_unavailable: "Kunne ikke generere PDF",
        },
        de: {
            pos_fetch_unavailable: "Produkte konnten nicht aus dem Lager abgerufen werden",
            copy_unavailable: "Link konnte nicht kopiert werden",
            copy_success: "Link in die Zwischenablage kopiert",
            pdf_unavailable: "PDF konnte nicht erstellt werden",
        },
        en: {
            pos_fetch_unavailable: "Could not fetch products for this warehouse",
            copy_unavailable: "Could not copy the link",
            copy_success: "Link copied to clipboard",
            pdf_unavailable: "Could not generate the PDF",
        },
        es: {
            pos_fetch_unavailable: "No se pudieron obtener productos del almacén",
            copy_unavailable: "No se pudo copiar el enlace",
            copy_success: "Enlace copiado al portapapeles",
            pdf_unavailable: "No se pudo generar el PDF",
        },
        fr: {
            pos_fetch_unavailable: "Impossible de récupérer les produits de l’entrepôt",
            copy_unavailable: "Impossible de copier le lien",
            copy_success: "Lien copié dans le presse-papiers",
            pdf_unavailable: "Impossible de générer le PDF",
        },
        he: {
            pos_fetch_unavailable: "לא ניתן לאחזר מוצרים מהמחסן",
            copy_unavailable: "לא ניתן להעתיק את הקישור",
            copy_success: "הקישור הועתק ללוח",
            pdf_unavailable: "לא ניתן ליצור קובץ PDF",
        },
        it: {
            pos_fetch_unavailable: "Impossibile recuperare i prodotti dal magazzino",
            copy_unavailable: "Impossibile copiare il link",
            copy_success: "Link copiato negli appunti",
            pdf_unavailable: "Impossibile generare il PDF",
        },
        ja: {
            pos_fetch_unavailable: "倉庫の商品を取得できませんでした",
            copy_unavailable: "リンクをコピーできませんでした",
            copy_success: "リンクをクリップボードにコピーしました",
            pdf_unavailable: "PDF を生成できませんでした",
        },
        nl: {
            pos_fetch_unavailable: "Producten konden niet uit het magazijn worden opgehaald",
            copy_unavailable: "Link kon niet worden gekopieerd",
            copy_success: "Link gekopieerd naar klembord",
            pdf_unavailable: "PDF kon niet worden gegenereerd",
        },
        pl: {
            pos_fetch_unavailable: "Nie udało się pobrać produktów z magazynu",
            copy_unavailable: "Nie można skopiować linku",
            copy_success: "Link skopiowano do schowka",
            pdf_unavailable: "Nie udało się wygenerować PDF",
        },
        pt: {
            pos_fetch_unavailable: "Não foi possível buscar os produtos do armazém",
            copy_unavailable: "Não foi possível copiar o link",
            copy_success: "Link copiado para a área de transferência",
            pdf_unavailable: "Não foi possível gerar o PDF",
        },
        "pt-br": {
            pos_fetch_unavailable: "Não foi possível buscar os produtos do armazém",
            copy_unavailable: "Não foi possível copiar o link",
            copy_success: "Link copiado para a área de transferência",
            pdf_unavailable: "Não foi possível gerar o PDF",
        },
        ru: {
            pos_fetch_unavailable: "Не удалось получить товары со склада",
            copy_unavailable: "Не удалось скопировать ссылку",
            copy_success: "Ссылка скопирована в буфер обмена",
            pdf_unavailable: "Не удалось создать PDF",
        },
        tr: {
            pos_fetch_unavailable: "Depodan ürünler alınamadı",
            copy_unavailable: "Bağlantı kopyalanamadı",
            copy_success: "Bağlantı panoya kopyalandı",
            pdf_unavailable: "PDF oluşturulamadı",
        },
        zh: {
            pos_fetch_unavailable: "无法从仓库获取产品",
            copy_unavailable: "无法复制链接",
            copy_success: "链接已复制到剪贴板",
            pdf_unavailable: "无法生成 PDF",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();