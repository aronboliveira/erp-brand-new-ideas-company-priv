(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            income_calculation_failed: "فشل حساب إجمالي الدخل.",
            expense_calculation_failed: "فشل حساب إجمالي المصروفات.",
            period_toggle_failed: "فشل تغيير الفترة.",
        },
        da: {
            income_calculation_failed: "Kunne ikke beregne samlet indkomst.",
            expense_calculation_failed: "Kunne ikke beregne samlet udgift.",
            period_toggle_failed: "Kunne ikke skifte periode.",
        },
        de: {
            income_calculation_failed: "Fehler bei der Berechnung des Gesamteinkommens.",
            expense_calculation_failed: "Fehler bei der Berechnung der Gesamtaufwendungen.",
            period_toggle_failed: "Fehler beim Wechseln des Zeitraums.",
        },
        en: {
            income_calculation_failed: "Failed to calculate total income.",
            expense_calculation_failed: "Failed to calculate total expense.",
            period_toggle_failed: "Failed to switch period.",
        },
        es: {
            income_calculation_failed: "Error al calcular el ingreso total.",
            expense_calculation_failed: "Error al calcular el gasto total.",
            period_toggle_failed: "Error al cambiar el período.",
        },
        fr: {
            income_calculation_failed: "Échec du calcul du revenu total.",
            expense_calculation_failed: "Échec du calcul des dépenses totales.",
            period_toggle_failed: "Échec du changement de période.",
        },
        he: {
            income_calculation_failed: "החישוב הכולל של ההכנסה נכשל.",
            expense_calculation_failed: "החישוב הכולל של ההוצאות נכשל.",
            period_toggle_failed: "החלפת התקופה נכשלה.",
        },
        it: {
            income_calculation_failed: "Impossibile calcolare il reddito totale.",
            expense_calculation_failed: "Impossibile calcolare la spesa totale.",
            period_toggle_failed: "Impossibile cambiare il periodo.",
        },
        ja: {
            income_calculation_failed: "総収入の計算に失敗しました。",
            expense_calculation_failed: "総支出の計算に失敗しました。",
            period_toggle_failed: "期間の切り替えに失敗しました。",
        },
        nl: {
            income_calculation_failed: "Kon totale inkomsten niet berekenen.",
            expense_calculation_failed: "Kon totale uitgaven niet berekenen.",
            period_toggle_failed: "Kon periode niet wijzigen.",
        },
        pl: {
            income_calculation_failed: "Nie udało się obliczyć całkowitego przychodu.",
            expense_calculation_failed: "Nie udało się obliczyć całkowitego wydatku.",
            period_toggle_failed: "Nie udało się zmienić okresu.",
        },
        pt: {
            income_calculation_failed: "Falha ao calcular receita total.",
            expense_calculation_failed: "Falha ao calcular despesa total.",
            period_toggle_failed: "Falha ao trocar período.",
        },
        "pt-br": {
            income_calculation_failed: "Falha ao calcular receita total.",
            expense_calculation_failed: "Falha ao calcular despesa total.",
            period_toggle_failed: "Falha ao trocar período.",
        },
        ru: {
            income_calculation_failed: "Не удалось вычислить общий доход.",
            expense_calculation_failed: "Не удалось вычислить общий расход.",
            period_toggle_failed: "Не удалось изменить период.",
        },
        tr: {
            income_calculation_failed: "Toplam geliri hesaplama başarısız.",
            expense_calculation_failed: "Toplam gideri hesaplama başarısız.",
            period_toggle_failed: "Dönem değiştirilemedi.",
        },
        zh: {
            income_calculation_failed: "计算总收入失败。",
            expense_calculation_failed: "计算总支出失败。",
            period_toggle_failed: "切换期间失败。",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();