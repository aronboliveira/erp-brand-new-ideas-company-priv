(function () {
  if (!window.translations) window.translations = {};
  const t = {
    ar: {
      no_data_found: "لا توجد بيانات.",
      are_you_sure: "هل أنت متأكد؟",
      remove_all_items: "إزالة جميع العناصر من السلة؟",
      error: "خطأ",
      success: "نجاح",
    },
    da: {
      no_data_found: "Ingen data fundet.",
      are_you_sure: "Er du sikker?",
      remove_all_items: "Fjern alle varer fra kurven?",
      error: "Fejl",
      success: "Succes",
    },
    de: {
      no_data_found: "Keine Daten gefunden.",
      are_you_sure: "Sind Sie sicher?",
      remove_all_items: "Alle Artikel aus dem Warenkorb entfernen?",
      error: "Fehler",
      success: "Erfolg",
    },
    en: {
      no_data_found: "No data found.",
      are_you_sure: "Are you sure?",
      remove_all_items: "Remove all items from cart?",
      error: "Error",
      success: "Success",
    },
    es: {
      no_data_found: "No se encontraron datos.",
      are_you_sure: "¿Estás seguro?",
      remove_all_items: "¿Quitar todos los artículos del carrito?",
      error: "Error",
      success: "Éxito",
    },
    fr: {
      no_data_found: "Aucune donnée trouvée.",
      are_you_sure: "Êtes-vous sûr ?",
      remove_all_items: "Retirer tous les articles du panier ?",
      error: "Erreur",
      success: "Succès",
    },
    he: {
      no_data_found: "לא נמצאו נתונים.",
      are_you_sure: "האם אתה בטוח?",
      remove_all_items: "להסיר את כל הפריטים מהעגלה?",
      error: "שגיאה",
      success: "הצלחה",
    },
    it: {
      no_data_found: "Nessun dato trovato.",
      are_you_sure: "Sei sicuro?",
      remove_all_items: "Rimuovere tutti gli articoli dal carrello?",
      error: "Errore",
      success: "Successo",
    },
    ja: {
      no_data_found: "データが見つかりません。",
      are_you_sure: "本当によろしいですか？",
      remove_all_items: "カート内のすべての商品を削除しますか？",
      error: "エラー",
      success: "成功",
    },
    nl: {
      no_data_found: "Geen gegevens gevonden.",
      are_you_sure: "Weet u het zeker?",
      remove_all_items: "Alle items uit de winkelwagen verwijderen?",
      error: "Fout",
      success: "Succes",
    },
    pl: {
      no_data_found: "Nie znaleziono danych.",
      are_you_sure: "Czy na pewno?",
      remove_all_items: "Usunąć wszystkie pozycje z koszyka?",
      error: "Błąd",
      success: "Sukces",
    },
    pt: {
      no_data_found: "Nenhum dado encontrado.",
      are_you_sure: "Tem certeza?",
      remove_all_items: "Remover todos os itens do carrinho?",
      error: "Erro",
      success: "Sucesso",
    },
    "pt-br": {
      no_data_found: "Nenhum dado encontrado.",
      are_you_sure: "Tem certeza?",
      remove_all_items: "Remover todos os itens do carrinho?",
      error: "Erro",
      success: "Sucesso",
    },
    ru: {
      no_data_found: "Данные не найдены.",
      are_you_sure: "Вы уверены?",
      remove_all_items: "Удалить все товары из корзины?",
      error: "Ошибка",
      success: "Успех",
    },
    tr: {
      no_data_found: "Veri bulunamadı.",
      are_you_sure: "Emin misiniz?",
      remove_all_items: "Sepetteki tüm ürünler kaldırılsın mı?",
      error: "Hata",
      success: "Başarılı",
    },
    zh: {
      no_data_found: "未找到数据。",
      are_you_sure: "确定吗？",
      remove_all_items: "移除购物车中的所有商品？",
      error: "错误",
      success: "成功",
    },
  };
  Object.keys(t).forEach(k => {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
