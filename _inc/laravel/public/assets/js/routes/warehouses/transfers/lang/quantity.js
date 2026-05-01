(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
            warehouse_products_unavailable: "تعذّر جلب قائمة المنتجات.",
            warehouse_quantity_unavailable: "تعذّر جلب الكمية.",
        },
        da: {
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
            warehouse_products_unavailable: "Kunne ikke hente produktlisten.",
            warehouse_quantity_unavailable: "Kunne ikke hente antal.",
        },
        de: {
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
            warehouse_products_unavailable: "Produktliste konnte nicht geladen werden.",
            warehouse_quantity_unavailable: "Menge konnte nicht geladen werden.",
        },
        en: {
            plugin_unavailable: "A required library failed to load.",
            warehouse_products_unavailable: "Could not fetch product list.",
            warehouse_quantity_unavailable: "Could not fetch quantity.",
        },
        es: {
            plugin_unavailable: "No se cargó una biblioteca requerida.",
            warehouse_products_unavailable: "No se pudo obtener la lista de productos.",
            warehouse_quantity_unavailable: "No se pudo obtener la cantidad.",
        },
        fr: {
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
            warehouse_products_unavailable: "Impossible de récupérer la liste des produits.",
            warehouse_quantity_unavailable: "Impossible de récupérer la quantité.",
        },
        he: {
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
            warehouse_products_unavailable: "לא ניתן היה לאחזר את רשימת המוצרים.",
            warehouse_quantity_unavailable: "לא ניתן היה לאחזר את הכמות.",
        },
        it: {
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
            warehouse_products_unavailable: "Impossibile recuperare l’elenco prodotti.",
            warehouse_quantity_unavailable: "Impossibile recuperare la quantità.",
        },
        ja: {
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
            warehouse_products_unavailable: "商品リストを取得できませんでした。",
            warehouse_quantity_unavailable: "在庫数を取得できませんでした。",
        },
        nl: {
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
            warehouse_products_unavailable: "Kon de productlijst niet ophalen.",
            warehouse_quantity_unavailable: "Kon de hoeveelheid niet ophalen.",
        },
        pl: {
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
            warehouse_products_unavailable: "Nie można pobrać listy produktów.",
            warehouse_quantity_unavailable: "Nie można pobrać ilości.",
        },
        pt: {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            warehouse_products_unavailable: "Não foi possível buscar a lista de produtos.",
            warehouse_quantity_unavailable: "Não foi possível buscar a quantidade.",
        },
        "pt-br": {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            warehouse_products_unavailable: "Não foi possível buscar a lista de produtos.",
            warehouse_quantity_unavailable: "Não foi possível buscar a quantidade.",
        },
        ru: {
            plugin_unavailable: "Не загружена необходимая библиотека.",
            warehouse_products_unavailable: "Не удалось получить список товаров.",
            warehouse_quantity_unavailable: "Не удалось получить количество.",
        },
        tr: {
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
            warehouse_products_unavailable: "Ürün listesi alınamadı.",
            warehouse_quantity_unavailable: "Miktar alınamadı.",
        },
        zh: {
            plugin_unavailable: "未能加载所需的库。",
            warehouse_products_unavailable: "无法获取产品列表。",
            warehouse_quantity_unavailable: "无法获取数量。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();