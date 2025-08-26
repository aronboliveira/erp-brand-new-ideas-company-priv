(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    en: {
      cash_flow_unavailable: "Failed to load cash‑flow chart.",
      incExpBarChart_unavailable: "Failed to load income/expense bar chart.",
      expenseByCategory_unavailable:
        "Failed to load expense‑by‑category chart.",
      incomeByCategory_unavailable: "Failed to load income‑by‑category chart.",
      limitChart_unavailable: "Failed to load storage‑limit chart.",
    },
    pt: {
      cash_flow_unavailable: "Falha ao carregar o gráfico de fluxo de caixa.",
      incExpBarChart_unavailable:
        "Falha ao carregar o gráfico de entradas/saídas.",
      expenseByCategory_unavailable:
        "Falha ao carregar o gráfico de despesas por categoria.",
      incomeByCategory_unavailable:
        "Falha ao carregar o gráfico de receitas por categoria.",
      limitChart_unavailable:
        "Falha ao carregar o gráfico de limite de armazenamento.",
    },
    "pt-br": {
      cash_flow_unavailable: "Falha ao carregar o gráfico de fluxo de caixa.",
      incExpBarChart_unavailable:
        "Falha ao carregar o gráfico de entradas/saídas.",
      expenseByCategory_unavailable:
        "Falha ao carregar o gráfico de despesas por categoria.",
      incomeByCategory_unavailable:
        "Falha ao carregar o gráfico de receitas por categoria.",
      limitChart_unavailable:
        "Falha ao carregar o gráfico de limite de armazenamento.",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations[k] = {
        ...(window.translations[k] || {}),
        ...t[k],
      })
  );
})();
