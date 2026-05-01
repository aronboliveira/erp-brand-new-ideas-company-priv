(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            deals_order_failed: "فشل ترتيب الصفقات.",
            pipeline_change_failed: "فشل تغيير مسار العملية.",
        },
        da: {
            deals_order_failed: "Kunne ikke sortere handler.",
            pipeline_change_failed: "Kunne ikke ændre pipeline.",
        },
        de: {
            deals_order_failed: "Reihenfolge der Deals konnte nicht gespeichert werden.",
            pipeline_change_failed: "Pipeline konnte nicht gewechselt werden.",
        },
        en: {
            deals_order_failed: "Failed to reorderViewsConstants::DL. .",
            pipeline_change_failed: "Failed to change pipeline.",
        },
        es: {
            deals_order_failed: "Error al reordenar las ofertas.",
            pipeline_change_failed: "Error al cambiar el pipeline.",
        },
        fr: {
            deals_order_failed: "Échec du réordonnancement des transactions.",
            pipeline_change_failed: "Échec du changement de pipeline.",
        },
        he: {
            deals_order_failed: "עדכון סדר העסקאות נכשל.",
            pipeline_change_failed: "שינוי הצנרת נכשל.",
        },
        it: {
            deals_order_failed: "Ripristino ordine offerte non riuscito.",
            pipeline_change_failed: "Impossibile cambiare pipeline.",
        },
        ja: {
            deals_order_failed: "取引の並び替えに失敗しました。",
            pipeline_change_failed: "パイプラインの変更に失敗しました。",
        },
        nl: {
            deals_order_failed: "Kon deals niet opnieuw ordenen.",
            pipeline_change_failed: "Kon pipeline niet wijzigen.",
        },
        pl: {
            deals_order_failed: "Nie udało się zmienić kolejności transakcji.",
            pipeline_change_failed: "Nie udało się zmienić pipeline.",
        },
        pt: {
            deals_order_failed: "Falha ao reordenar negócios.",
            pipeline_change_failed: "Falha ao alterar pipeline.",
        },
        "pt-br": {
            deals_order_failed: "Falha ao reordenar negócios.",
            pipeline_change_failed: "Falha ao alterar pipeline.",
        },
        ru: {
            deals_order_failed: "Не удалось изменить порядок сделок.",
            pipeline_change_failed: "Не удалось сменить воронку.",
        },
        tr: {
            deals_order_failed: "Anlaşmaların sıralaması yapılamadı.",
            pipeline_change_failed: "Pipeline değiştirilemedi.",
        },
        zh: {
            deals_order_failed: "重新排序交易失败。",
            pipeline_change_failed: "更改管道失败。",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();