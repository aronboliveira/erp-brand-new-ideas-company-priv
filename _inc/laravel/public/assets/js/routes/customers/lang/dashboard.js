(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            plugin_unavailable: "تعذّر تحميل مكتبة مطلوبة.",
            chart_unavailable: "فشل إنشاء المخطط.",
            container_unavailable: "عنصر المخطط غير موجود.",
        },
        da: {
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
            chart_unavailable: "Kunne ikke oprette diagram.",
            container_unavailable: "Diagramcontainer findes ikke.",
        },
        de: {
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
            chart_unavailable: "Diagramm konnte nicht erstellt werden.",
            container_unavailable: "Diagramm-Container ist nicht vorhanden.",
        },
        en: {
            plugin_unavailable: "A required library failed to load.",
            chart_unavailable: "Could not render the chart.",
            container_unavailable: "Chart container is missing.",
        },
        es: {
            plugin_unavailable: "No se cargó una biblioteca requerida.",
            chart_unavailable: "No se pudo renderizar el gráfico.",
            container_unavailable: "Falta el contenedor del gráfico.",
        },
        fr: {
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
            chart_unavailable: "Impossible d’afficher le graphique.",
            container_unavailable: "Conteneur du graphique manquant.",
        },
        he: {
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
            chart_unavailable: "לא ניתן להציג את התרשים.",
            container_unavailable: "חסר אלמנט של התרשים.",
        },
        it: {
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
            chart_unavailable: "Impossibile renderizzare il grafico.",
            container_unavailable: "Contenitore del grafico mancante.",
        },
        ja: {
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
            chart_unavailable: "チャートを描画できませんでした。",
            container_unavailable: "チャートのコンテナが見つかりません。",
        },
        nl: {
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
            chart_unavailable: "Grafiek kon niet worden weergegeven.",
            container_unavailable: "Grafiekcontainer ontbreekt.",
        },
        pl: {
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
            chart_unavailable: "Nie można wyświetlić wykresu.",
            container_unavailable: "Brak kontenera wykresu.",
        },
        pt: {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            chart_unavailable: "Não foi possível renderizar o gráfico.",
            container_unavailable: "Falta o contêiner do gráfico.",
        },
        "pt-br": {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            chart_unavailable: "Não foi possível renderizar o gráfico.",
            container_unavailable: "Contêiner do gráfico ausente.",
        },
        ru: {
            plugin_unavailable: "Не загружена необходимая библиотека.",
            chart_unavailable: "Не удалось отобразить график.",
            container_unavailable: "Отсутствует контейнер графика.",
        },
        tr: {
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
            chart_unavailable: "Grafik oluşturulamadı.",
            container_unavailable: "Grafik kapsayıcısı eksik.",
        },
        zh: {
            plugin_unavailable: "未能加载所需的库。",
            chart_unavailable: "无法渲染图表。",
            container_unavailable: "缺少图表容器。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();
//# sourceMappingURL=dashboard.js.map