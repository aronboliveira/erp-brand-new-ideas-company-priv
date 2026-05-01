(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            no_area: "المنطقة القابلة للطباعة غير موجودة",
            pdf_fail: "فشل حفظ الملف كـ PDF",
            chart_fail: "فشل عرض مخطط المشتريات الشهري",
        },
        da: {
            no_area: "Printbart område ikke fundet",
            pdf_fail: "Kunne ikke gemme som PDF",
            chart_fail: "Kunne ikke vise månedligt indkøbsdiagram",
        },
        de: {
            no_area: "Druckbereich nicht gefunden",
            pdf_fail: "Konnte nicht als PDF gespeichert werden",
            chart_fail: "Monatliches Einkaufsdiagramm konnte nicht angezeigt werden",
        },
        en: {
            no_area: "Printable area not found",
            pdf_fail: "Failed to save as PDF",
            chart_fail: "Failed to render monthly purchase chart",
        },
        es: {
            no_area: "Área imprimible no encontrada",
            pdf_fail: "Error al guardar PDF",
            chart_fail: "Error al mostrar gráfico de compras mensual",
        },
        fr: {
            no_area: "Zone imprimable introuvable",
            pdf_fail: "Échec de l'enregistrement PDF",
            chart_fail: "Échec de l'affichage du diagramme d'achat mensuel",
        },
        he: {
            no_area: "אזור ההדפסה לא נמצא",
            pdf_fail: "שמירה כ-PDF נכשלה",
            chart_fail: "נכשל בהצגת תרשים רכישות חודשי",
        },
        it: {
            no_area: "Area stampabile non trovata",
            pdf_fail: "Salvataggio come PDF non riuscito",
            chart_fail: "Impossibile visualizzare il grafico degli acquisti mensile",
        },
        ja: {
            no_area: "印刷可能な領域が見つかりません",
            pdf_fail: "PDFとして保存できませんでした",
            chart_fail: "月次購入チャートの表示に失敗しました",
        },
        nl: {
            no_area: "Afdrukbaar gebied niet gevonden",
            pdf_fail: "Opslaan als PDF mislukt",
            chart_fail: "Maandelijkse inkoopgrafiek weergeven mislukt",
        },
        pl: {
            no_area: "Nie znaleziono obszaru do druku",
            pdf_fail: "Nie udało się zapisać jako PDF",
            chart_fail: "Nie udało się wyświetlić miesięcznego wykresu zakupów",
        },
        pt: {
            no_area: "Área imprimível não encontrada",
            pdf_fail: "Falha ao salvar como PDF",
            chart_fail: "Falha ao exibir gráfico de compras mensal",
        },
        "pt-br": {
            no_area: "Área imprimível não encontrada",
            pdf_fail: "Falha ao salvar como PDF",
            chart_fail: "Falha ao exibir gráfico de compras mensal",
        },
        ru: {
            no_area: "Область для печати не найдена",
            pdf_fail: "Не удалось сохранить как PDF",
            chart_fail: "Не удалось отобразить ежемесячную диаграмму покупок",
        },
        tr: {
            no_area: "Yazdırılabilir alan bulunamadı",
            pdf_fail: "PDF olarak kaydedilemedi",
            chart_fail: "Aylık satın alma grafiği oluşturulamadı",
        },
        zh: {
            no_area: "未找到可打印区域",
            pdf_fail: "无法保存为PDF",
            chart_fail: "无法渲染月度采购图表",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();