/**
 * @fileoverview TypeScript version of public/assets/js/routes/settings/pos/lang/purchase.js
 * @generated from original JavaScript — automated migration
 * @module purchase
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

((): void => {
  if (!window.translations) {
    window.translations = {};
  }
  const t: Record<string, Record<string, string>> = {
    ar: {
      scrollspy_unavailable: "تعذّر تفعيل ScrollSpy",
      purchase_preview_unavailable: "تعذّر عرض معاينة أمر الشراء",
      purchase_logo_unavailable: "تعذّر معاينة شعار أمر الشراء",
      pos_preview_unavailable: "تعذّر عرض معاينة نقطة البيع",
      pos_logo_unavailable: "تعذّر معاينة شعار نقطة البيع",
    },
    da: {
      scrollspy_unavailable: "Kunne ikke aktivere ScrollSpy",
      purchase_preview_unavailable: "Kunne ikke vise indkøbsforhåndsvisning",
      purchase_logo_unavailable: "Kunne ikke forhåndsvise indkøbslogo",
      pos_preview_unavailable: "Kunne ikke vise POS-forhåndsvisning",
      pos_logo_unavailable: "Kunne ikke forhåndsvise POS-logo",
    },
    de: {
      scrollspy_unavailable: "ScrollSpy konnte nicht aktiviert werden",
      purchase_preview_unavailable:
        "Bestellvorschau konnte nicht geladen werden",
      purchase_logo_unavailable: "Bestelllogo konnte nicht angezeigt werden",
      pos_preview_unavailable: "POS-Vorschau konnte nicht geladen werden",
      pos_logo_unavailable: "POS-Logo konnte nicht angezeigt werden",
    },
    en: {
      scrollspy_unavailable: "Cannot enable ScrollSpy",
      purchase_preview_unavailable: "Cannot load purchase preview",
      purchase_logo_unavailable: "Cannot preview purchase logo",
      pos_preview_unavailable: "Cannot load POS preview",
      pos_logo_unavailable: "Cannot preview POS logo",
    },
    es: {
      scrollspy_unavailable: "No se puede activar ScrollSpy",
      purchase_preview_unavailable:
        "No se puede cargar la vista previa de compra",
      purchase_logo_unavailable: "No se puede previsualizar el logo de compra",
      pos_preview_unavailable: "No se puede cargar la vista previa de POS",
      pos_logo_unavailable: "No se puede previsualizar el logo de POS",
    },
    fr: {
      scrollspy_unavailable: "Impossible d’activer ScrollSpy",
      purchase_preview_unavailable: "Impossible de charger l’aperçu d’achat",
      purchase_logo_unavailable:
        "Impossible d’afficher l’aperçu du logo d’achat",
      pos_preview_unavailable: "Impossible de charger l’aperçu du PDV",
      pos_logo_unavailable: "Impossible d’afficher l’aperçu du logo PDV",
    },
    he: {
      scrollspy_unavailable: "לא ניתן להפעיל ScrollSpy",
      purchase_preview_unavailable: "לא ניתן לטעון תצוגה מקדימה של הזמנה",
      purchase_logo_unavailable: "לא ניתן להציג תצוגה מקדימה של לוגו הזמנה",
      pos_preview_unavailable: "לא ניתן לטעון תצוגה מקדימה של קופה",
      pos_logo_unavailable: "לא ניתן להציג תצוגה מקדימה של לוגו קופה",
    },
    it: {
      scrollspy_unavailable: "Impossibile abilitare ScrollSpy",
      purchase_preview_unavailable: "Impossibile caricare l’anteprima ordine",
      purchase_logo_unavailable: "Impossibile anteprima logo ordine",
      pos_preview_unavailable: "Impossibile caricare anteprima POS",
      pos_logo_unavailable: "Impossibile anteprima logo POS",
    },
    ja: {
      scrollspy_unavailable: "ScrollSpy を有効にできません",
      purchase_preview_unavailable: "購入プレビューを読み込めません",
      purchase_logo_unavailable: "購入ロゴをプレビューできません",
      pos_preview_unavailable: "POS プレビューを読み込めません",
      pos_logo_unavailable: "POS ロゴをプレビューできません",
    },
    nl: {
      scrollspy_unavailable: "ScrollSpy kan niet worden ingeschakeld",
      purchase_preview_unavailable: "Voorbeeld van inkoop kan niet laden",
      purchase_logo_unavailable: "Voorbeeld van inkooplogo mislukt",
      pos_preview_unavailable: "POS-voorbeeld kan niet laden",
      pos_logo_unavailable: "POS-logo kan niet worden bekeken",
    },
    pl: {
      scrollspy_unavailable: "Nie można włączyć ScrollSpy",
      purchase_preview_unavailable: "Nie można wczytać podglądu zamówienia",
      purchase_logo_unavailable: "Nie można podglądnąć logo zamówienia",
      pos_preview_unavailable: "Nie można wczytać podglądu POS",
      pos_logo_unavailable: "Nie można podglądnąć logo POS",
    },
    pt: {
      scrollspy_unavailable: "Não foi possível ativar o ScrollSpy",
      purchase_preview_unavailable:
        "Não foi possível carregar a pré-visualização de compra",
      purchase_logo_unavailable:
        "Não foi possível pré-visualizar o logotipo da compra",
      pos_preview_unavailable:
        "Não foi possível carregar a pré-visualização do POS",
      pos_logo_unavailable: "Não foi possível pré-visualizar o logotipo do POS",
    },
    "pt-br": {
      scrollspy_unavailable: "Não foi possível ativar o ScrollSpy",
      purchase_preview_unavailable:
        "Não foi possível carregar a prévia da compra",
      purchase_logo_unavailable:
        "Não foi possível pré-visualizar o logo da compra",
      pos_preview_unavailable: "Não foi possível carregar a prévia do PDV",
      pos_logo_unavailable: "Não foi possível pré-visualizar o logo do PDV",
    },
    ru: {
      scrollspy_unavailable: "Не удалось включить ScrollSpy",
      purchase_preview_unavailable:
        "Не удалось загрузить предварительный просмотр закупки",
      purchase_logo_unavailable: "Не удалось показать логотип закупки",
      pos_preview_unavailable:
        "Не удалось загрузить предварительный просмотр POS",
      pos_logo_unavailable: "Не удалось показать логотип POS",
    },
    tr: {
      scrollspy_unavailable: "ScrollSpy etkinleştirilemedi",
      purchase_preview_unavailable: "Satın alma önizlemesi yüklenemiyor",
      purchase_logo_unavailable: "Satın alma logosu önizlenemiyor",
      pos_preview_unavailable: "POS önizlemesi yüklenemiyor",
      pos_logo_unavailable: "POS logosu önizlenemiyor",
    },
    zh: {
      scrollspy_unavailable: "无法启用 ScrollSpy",
      purchase_preview_unavailable: "无法加载采购预览",
      purchase_logo_unavailable: "无法预览采购徽标",
      pos_preview_unavailable: "无法加载收银预览",
      pos_logo_unavailable: "无法预览收银徽标",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations![k] = { ...(window.translations![k] || {}),
        ...t[k],
      })
  );
})();
