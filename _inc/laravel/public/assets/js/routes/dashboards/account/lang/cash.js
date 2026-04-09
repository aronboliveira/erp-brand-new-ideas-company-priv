(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            cash_flow_unavailable: "فشل تحميل مخطط التدفق النقدي.",
            incExpBarChart_unavailable: "فشل تحميل مخطط أعمدة الدخل/المصروف.",
            expenseByCategory_unavailable: "فشل تحميل مخطط المصروفات حسب الفئة.",
            incomeByCategory_unavailable: "فشل تحميل مخطط الدخل حسب الفئة.",
            limitChart_unavailable: "فشل تحميل مخطط حد التخزين.",
        },
        da: {
            cash_flow_unavailable: "Kunne ikke indlæse cashflow-diagram.",
            incExpBarChart_unavailable: "Kunne ikke indlæse søjlediagram for indtægter/udgifter.",
            expenseByCategory_unavailable: "Kunne ikke indlæse diagram over udgifter efter kategori.",
            incomeByCategory_unavailable: "Kunne ikke indlæse diagram over indtægter efter kategori.",
            limitChart_unavailable: "Kunne ikke indlæse diagram over lagergrænse.",
        },
        de: {
            cash_flow_unavailable: "Cashflow-Diagramm konnte nicht geladen werden.",
            incExpBarChart_unavailable: "Balkendiagramm für Einnahmen/Ausgaben konnte nicht geladen werden.",
            expenseByCategory_unavailable: "Diagramm der Ausgaben nach Kategorie konnte nicht geladen werden.",
            incomeByCategory_unavailable: "Diagramm der Einnahmen nach Kategorie konnte nicht geladen werden.",
            limitChart_unavailable: "Speicherlimit-Diagramm konnte nicht geladen werden.",
        },
        en: {
            cash_flow_unavailable: "Failed to load cash‑flow chart.",
            incExpBarChart_unavailable: "Failed to load income/expense bar chart.",
            expenseByCategory_unavailable: "Failed to load expense‑by‑category chart.",
            incomeByCategory_unavailable: "Failed to load income‑by‑category chart.",
            limitChart_unavailable: "Failed to load storage‑limit chart.",
        },
        es: {
            cash_flow_unavailable: "No se pudo cargar el gráfico de flujo de caja.",
            incExpBarChart_unavailable: "No se pudo cargar el gráfico de barras de ingresos/gastos.",
            expenseByCategory_unavailable: "No se pudo cargar el gráfico de gastos por categoría.",
            incomeByCategory_unavailable: "No se pudo cargar el gráfico de ingresos por categoría.",
            limitChart_unavailable: "No se pudo cargar el gráfico del límite de almacenamiento.",
        },
        fr: {
            cash_flow_unavailable: "Impossible de charger le graphique de flux de trésorerie.",
            incExpBarChart_unavailable: "Impossible de charger l’histogramme des revenus/dépenses.",
            expenseByCategory_unavailable: "Impossible de charger le graphique des dépenses par catégorie.",
            incomeByCategory_unavailable: "Impossible de charger le graphique des revenus par catégorie.",
            limitChart_unavailable: "Impossible de charger le graphique de limite de stockage.",
        },
        he: {
            cash_flow_unavailable: "טעינת תרשים תזרים המזומנים נכשלה.",
            incExpBarChart_unavailable: "טעינת תרשים עמודות הכנסות/הוצאות נכשלה.",
            expenseByCategory_unavailable: "טעינת תרשים הוצאות לפי קטגוריה נכשלה.",
            incomeByCategory_unavailable: "טעינת תרשים הכנסות לפי קטגוריה נכשלה.",
            limitChart_unavailable: "טעינת תרשים מגבלת האחסון נכשלה.",
        },
        it: {
            cash_flow_unavailable: "Impossibile caricare il grafico del flusso di cassa.",
            incExpBarChart_unavailable: "Impossibile caricare il grafico a barre entrate/spese.",
            expenseByCategory_unavailable: "Impossibile caricare il grafico delle spese per categoria.",
            incomeByCategory_unavailable: "Impossibile caricare il grafico delle entrate per categoria.",
            limitChart_unavailable: "Impossibile caricare il grafico del limite di archiviazione.",
        },
        ja: {
            cash_flow_unavailable: "キャッシュフローチャートを読み込めませんでした。",
            incExpBarChart_unavailable: "収入/支出の棒グラフを読み込めませんでした。",
            expenseByCategory_unavailable: "カテゴリ別支出チャートを読み込めませんでした。",
            incomeByCategory_unavailable: "カテゴリ別収入チャートを読み込めませんでした。",
            limitChart_unavailable: "ストレージ上限チャートを読み込めませんでした。",
        },
        nl: {
            cash_flow_unavailable: "Kan kasstroomgrafiek niet laden.",
            incExpBarChart_unavailable: "Kan staafdiagram voor inkomsten/uitgaven niet laden.",
            expenseByCategory_unavailable: "Kan grafiek met uitgaven per categorie niet laden.",
            incomeByCategory_unavailable: "Kan grafiek met inkomsten per categorie niet laden.",
            limitChart_unavailable: "Kan opslaglimietgrafiek niet laden.",
        },
        pl: {
            cash_flow_unavailable: "Nie udało się załadować wykresu przepływów pieniężnych.",
            incExpBarChart_unavailable: "Nie udało się załadować wykresu słupkowego przychodów/wydatków.",
            expenseByCategory_unavailable: "Nie udało się załadować wykresu wydatków według kategorii.",
            incomeByCategory_unavailable: "Nie udało się załadować wykresu przychodów według kategorii.",
            limitChart_unavailable: "Nie udało się załadować wykresu limitu pamięci masowej.",
        },
        pt: {
            cash_flow_unavailable: "Falha ao carregar o gráfico de fluxo de caixa.",
            incExpBarChart_unavailable: "Falha ao carregar o gráfico de entradas/saídas.",
            expenseByCategory_unavailable: "Falha ao carregar o gráfico de despesas por categoria.",
            incomeByCategory_unavailable: "Falha ao carregar o gráfico de receitas por categoria.",
            limitChart_unavailable: "Falha ao carregar o gráfico de limite de armazenamento.",
        },
        "pt-br": {
            cash_flow_unavailable: "Falha ao carregar o gráfico de fluxo de caixa.",
            incExpBarChart_unavailable: "Falha ao carregar o gráfico de entradas/saídas.",
            expenseByCategory_unavailable: "Falha ao carregar o gráfico de despesas por categoria.",
            incomeByCategory_unavailable: "Falha ao carregar o gráfico de receitas por categoria.",
            limitChart_unavailable: "Falha ao carregar o gráfico de limite de armazenamento.",
        },
        ru: {
            cash_flow_unavailable: "Не удалось загрузить график денежного потока.",
            incExpBarChart_unavailable: "Не удалось загрузить столбчатый график доходов/расходов.",
            expenseByCategory_unavailable: "Не удалось загрузить график расходов по категориям.",
            incomeByCategory_unavailable: "Не удалось загрузить график доходов по категориям.",
            limitChart_unavailable: "Не удалось загрузить график лимита хранилища.",
        },
        tr: {
            cash_flow_unavailable: "Nakit akışı grafiği yüklenemedi.",
            incExpBarChart_unavailable: "Gelir/gider sütun grafiği yüklenemedi.",
            expenseByCategory_unavailable: "Kategoriye göre gider grafiği yüklenemedi.",
            incomeByCategory_unavailable: "Kategoriye göre gelir grafiği yüklenemedi.",
            limitChart_unavailable: "Depolama limiti grafiği yüklenemedi.",
        },
        zh: {
            cash_flow_unavailable: "无法加载现金流图表。",
            incExpBarChart_unavailable: "无法加载收入/支出柱状图。",
            expenseByCategory_unavailable: "无法加载按类别划分的支出图表。",
            incomeByCategory_unavailable: "无法加载按类别划分的收入图表。",
            limitChart_unavailable: "无法加载存储限制图表。",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();
//# sourceMappingURL=cash.js.map