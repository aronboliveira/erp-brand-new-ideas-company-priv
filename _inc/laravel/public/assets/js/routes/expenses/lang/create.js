/** @requires ERPUtils (translations) */
(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      selection_failed: "فشل تغيير النوع.",
      employee_fetch_failed: "فشل جلب بيانات الموظف.",
      customer_fetch_failed: "فشل جلب بيانات العميل.",
      vendor_fetch_failed: "فشل جلب بيانات البائع.",
      repeater_initialization_failed: "فشل تهيئة المكرر.",
      item_fetch_failed: "فشل جلب بيانات الصنف.",
      calculation_failed: "فشل حساب الإجماليات.",
      repeater_delete_failed: "فشل حذف عنصر التكرار.",
    },
    da: {
      selection_failed: "Kunne ikke ændre typen.",
      employee_fetch_failed: "Kunne ikke hente medarbejderdata.",
      customer_fetch_failed: "Kunne ikke hente kundedata.",
      vendor_fetch_failed: "Kunne ikke hente leverandørdata.",
      repeater_initialization_failed: "Kunne ikke initialisere gentager.",
      item_fetch_failed: "Kunne ikke hente varedata.",
      calculation_failed: "Kunne ikke beregne totaler.",
      repeater_delete_failed: "Kunne ikke slette gentagelseselement.",
    },
    de: {
      selection_failed: "Auswahl konnte nicht geändert werden.",
      employee_fetch_failed: "Mitarbeiterdaten konnten nicht geladen werden.",
      customer_fetch_failed: "Kundendaten konnten nicht geladen werden.",
      vendor_fetch_failed: "Anbieterdaten konnten nicht geladen werden.",
      repeater_initialization_failed:
        "Initialisierung des Repeaters fehlgeschlagen.",
      item_fetch_failed: "Elementdaten konnten nicht abgerufen werden.",
      calculation_failed: "Berechnung der Summen fehlgeschlagen.",
      repeater_delete_failed: "Fehler beim Löschen des Wiederholungselements.",
    },
    en: {
      selection_failed: "Failed to change type.",
      employee_fetch_failed: "Failed to load employee details.",
      customer_fetch_failed: "Failed to load customer details.",
      vendor_fetch_failed: "Failed to load vendor details.",
      repeater_initialization_failed: "Failed to initialize repeater.",
      item_fetch_failed: "Failed to fetch item data.",
      calculation_failed: "Failed to calculate totals.",
      repeater_delete_failed: "Failed to delete repeater item.",
    },
    es: {
      selection_failed: "Error al cambiar el tipo.",
      employee_fetch_failed: "Error al cargar datos del empleado.",
      customer_fetch_failed: "Error al cargar datos del cliente.",
      vendor_fetch_failed: "Error al cargar datos del proveedor.",
      repeater_initialization_failed: "No se pudo inicializar el repetidor.",
      item_fetch_failed: "No se pudieron obtener los datos del artículo.",
      calculation_failed: "No se pudieron calcular los totales.",
      repeater_delete_failed: "No se pudo eliminar el elemento repetidor.",
    },
    fr: {
      selection_failed: "Échec du changement de type.",
      employee_fetch_failed: "Échec du chargement des détails de l’employé.",
      customer_fetch_failed: "Échec du chargement des détails du client.",
      vendor_fetch_failed: "Échec du chargement des détails du fournisseur.",
      repeater_initialization_failed: "Échec de l’initialisation du répéteur.",
      item_fetch_failed: "Échec de la récupération des données de l’article.",
      calculation_failed: "Échec du calcul des totaux.",
      repeater_delete_failed:
        "Échec de la suppression de l’élément répétiteur.",
    },
    he: {
      repeater_delete_failed: "המחיקה של פריט החזרה נכשלה.",
    },
    it: {
      repeater_delete_failed: "Impossibile eliminare l’elemento ripetitore.",
    },
    ja: {
      repeater_delete_failed: "リピーター項目の削除に失敗しました。",
    },
    nl: {
      repeater_delete_failed: "Kan herhaler-item niet verwijderen.",
    },
    pl: {
      repeater_delete_failed: "Nie udało się usunąć elementu repeatera.",
    },
    pt: {
      repeater_delete_failed: "Falha ao excluir item do repetidor.",
    },
    "pt-br": {
      repeater_delete_failed: "Falha ao excluir item do repetidor.",
    },
    ru: {
      repeater_delete_failed: "Не удалось удалить элемент повторителя.",
    },
    tr: {
      repeater_delete_failed: "Tekrarlayıcı öğe silinemedi.",
    },
    zh: {
      repeater_delete_failed: "无法删除重复器项目。",
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
