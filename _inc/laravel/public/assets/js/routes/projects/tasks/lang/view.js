/** @requires ERPUtils (translations) */
(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      color_picker_unavailable: "تعذّر تهيئة منتقي الألوان",
      color_change_unavailable: "تعذّر تحديث لون أولوية المهمة",
    },
    da: {
      color_picker_unavailable: "Kunne ikke initialisere farvevælger",
      color_change_unavailable: "Kunne ikke opdatere opgavens prioritesfarve",
    },
    de: {
      color_picker_unavailable: "Farbauswahl konnte nicht initialisiert werden",
      color_change_unavailable:
        "Aktualisierung der Aufgabenprioritätsfarbe fehlgeschlagen",
    },
    en: {
      color_picker_unavailable: "Cannot initialize color picker",
      color_change_unavailable: "Cannot update task priority color",
    },
    es: {
      color_picker_unavailable: "No se puede inicializar el selector de color",
      color_change_unavailable:
        "No se puede actualizar el color de prioridad de la tarea",
    },
    fr: {
      color_picker_unavailable:
        "Impossible d’initialiser le sélecteur de couleur",
      color_change_unavailable:
        "Impossible de mettre à jour la couleur de priorité de la tâche",
    },
    he: {
      color_picker_unavailable: "לא ניתן לאתחל בוחר צבע",
      color_change_unavailable: "לא ניתן לעדכן את צבע עדיפות המשימה",
    },
    it: {
      color_picker_unavailable: "Impossibile inizializzare il selettore colore",
      color_change_unavailable:
        "Impossibile aggiornare il colore di priorità del task",
    },
    ja: {
      color_picker_unavailable: "カラーピッカーを初期化できません",
      color_change_unavailable: "タスクの優先色を更新できません",
    },
    nl: {
      color_picker_unavailable: "Kan kleurkiezer niet initialiseren",
      color_change_unavailable: "Kan prioriteitskleur van taak niet bijwerken",
    },
    pl: {
      color_picker_unavailable: "Nie można zainicjować selektora kolorów",
      color_change_unavailable:
        "Nie można zaktualizować koloru priorytetu zadania",
    },
    pt: {
      color_picker_unavailable: "Não foi possível iniciar o seletor de cores",
      color_change_unavailable:
        "Não foi possível atualizar a cor de prioridade da tarefa",
    },
    "pt-br": {
      color_picker_unavailable: "Não foi possível iniciar o seletor de cores",
      color_change_unavailable:
        "Não foi possível atualizar a cor de prioridade da tarefa",
    },
    ru: {
      color_picker_unavailable: "Не удалось инициализировать выбор цвета",
      color_change_unavailable: "Не удалось обновить цвет приоритета задачи",
    },
    tr: {
      color_picker_unavailable: "Renk seçici başlatılamıyor",
      color_change_unavailable: "Görev öncelik rengi güncellenemedi",
    },
    zh: {
      color_picker_unavailable: "无法初始化取色器",
      color_change_unavailable: "无法更新任务优先级颜色",
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
