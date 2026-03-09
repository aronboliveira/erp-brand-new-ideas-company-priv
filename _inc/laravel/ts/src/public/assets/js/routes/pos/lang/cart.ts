/**
 * @fileoverview TypeScript version of public/assets/js/routes/pos/lang/cart.js
 * @generated from original JavaScript — automated migration
 * @module cart
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

window.translations = window.translations || {};
(function (): void {
  const dict = {
    ar: {
      search_products_unavailable:
        "البحث غير متاح. يُرجى التواصل مع الدعم الفني أو مسؤول النطاق.",
      pos_create_unavailable:
        "الدفع غير متاح. يُرجى التواصل مع الدعم الفني أو مسؤول النطاق.",
      update_cart_unavailable:
        "تحديث السلة غير متاح. يُرجى التواصل مع الدعم الفني أو مسؤول النطاق.",
      remove_from_cart_unavailable:
        "إزالة من السلة غير متاحة. يُرجى التواصل مع الدعم الفني أو مسؤول النطاق.",
      empty_cart_unavailable:
        "إفراغ السلة غير متاح. يُرجى التواصل مع الدعم الفني أو مسؤول النطاق.",
      nothing_to_update: "لا يوجد ما يتم تحديثه.",
    },
    da: {
      search_products_unavailable:
        "Søgning er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator.",
      pos_create_unavailable:
        "Betaling er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator.",
      update_cart_unavailable:
        "Opdatering af kurv er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator.",
      remove_from_cart_unavailable:
        "Fjern fra kurv er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator.",
      empty_cart_unavailable:
        "Tøm kurv er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator.",
      nothing_to_update: "Intet at opdatere.",
    },
    de: {
      search_products_unavailable:
        "Suche nicht verfügbar. Bitte wenden Sie sich an den technischen Support oder Ihren Domain-Administrator.",
      pos_create_unavailable:
        "Kassiervorgang nicht verfügbar. Bitte wenden Sie sich an den technischen Support oder Ihren Domain-Administrator.",
      update_cart_unavailable:
        "Aktualisierung des Warenkorbs nicht verfügbar. Bitte wenden Sie sich an den technischen Support oder Ihren Domain-Administrator.",
      remove_from_cart_unavailable:
        "Entfernen aus dem Warenkorb nicht verfügbar. Bitte wenden Sie sich an den technischen Support oder Ihren Domain-Administrator.",
      empty_cart_unavailable:
        "Warenkorb leeren nicht verfügbar. Bitte wenden Sie sich an den technischen Support oder Ihren Domain-Administrator.",
      nothing_to_update: "Nichts zu aktualisieren.",
    },
    en: {
      search_products_unavailable:
        "Search is unavailable. Please contact technical support or your domain administrator.",
      pos_create_unavailable:
        "Checkout is unavailable. Please contact technical support or your domain administrator.",
      update_cart_unavailable:
        "Update cart is unavailable. Please contact technical support or your domain administrator.",
      remove_from_cart_unavailable:
        "Remove from cart is unavailable. Please contact technical support or your domain administrator.",
      empty_cart_unavailable:
        "Empty cart is unavailable. Please contact technical support or your domain administrator.",
      nothing_to_update: "Nothing to update.",
    },
    es: {
      search_products_unavailable:
        "La búsqueda no está disponible. Póngase en contacto con soporte técnico o el administrador del dominio.",
      pos_create_unavailable:
        "El cobro no está disponible. Póngase en contacto con soporte técnico o el administrador del dominio.",
      update_cart_unavailable:
        "La actualización del carrito no está disponible. Póngase en contacto con soporte técnico o el administrador del dominio.",
      remove_from_cart_unavailable:
        "Quitar del carrito no está disponible. Póngase en contacto con soporte técnico o el administrador del dominio.",
      empty_cart_unavailable:
        "Vaciar el carrito no está disponible. Póngase en contacto con soporte técnico o el administrador del dominio.",
      nothing_to_update: "Nada que actualizar.",
    },
    fr: {
      search_products_unavailable:
        "La recherche est indisponible. Veuillez contacter le support technique ou l’administrateur du domaine.",
      pos_create_unavailable:
        "L’encaissement est indisponible. Veuillez contacter le support technique ou l’administrateur du domaine.",
      update_cart_unavailable:
        "La mise à jour du panier est indisponible. Veuillez contacter le support technique ou l’administrateur du domaine.",
      remove_from_cart_unavailable:
        "Le retrait du panier est indisponible. Veuillez contacter le support technique ou l’administrateur du domaine.",
      empty_cart_unavailable:
        "La vidange du panier est indisponible. Veuillez contacter le support technique ou l’administrateur du domaine.",
      nothing_to_update: "Rien à mettre à jour.",
    },
    he: {
      search_products_unavailable:
        "החיפוש אינו זמין. פנו לתמיכה הטכנית או למנהל הדומיין.",
      pos_create_unavailable:
        "התשלום אינו זמין. פנו לתמיכה הטכנית או למנהל הדומיין.",
      update_cart_unavailable:
        "עדכון העגלה אינו זמין. פנו לתמיכה הטכנית או למנהל הדומיין.",
      remove_from_cart_unavailable:
        "הסרת פריטים מהעגלה אינה זמינה. פנו לתמיכה הטכנית או למנהל הדומיין.",
      empty_cart_unavailable:
        "ריקון העגלה אינו זמין. פנו לתמיכה הטכנית או למנהל הדומיין.",
      nothing_to_update: "אין מה לעדכן.",
    },
    it: {
      search_products_unavailable:
        "Ricerca non disponibile. Contatta l’assistenza tecnica o l’amministratore del dominio.",
      pos_create_unavailable:
        "Pagamento non disponibile. Contatta l’assistenza tecnica o l’amministratore del dominio.",
      update_cart_unavailable:
        "Aggiornamento del carrello non disponibile. Contatta l’assistenza tecnica o l’amministratore del dominio.",
      remove_from_cart_unavailable:
        "Rimozione dal carrello non disponibile. Contatta l’assistenza tecnica o l’amministratore del dominio.",
      empty_cart_unavailable:
        "Svuotamento del carrello non disponibile. Contatta l’assistenza tecnica o l’amministratore del dominio.",
      nothing_to_update: "Nulla da aggiornare.",
    },
    ja: {
      search_products_unavailable:
        "検索は利用できません。システム管理者またはサポートに連絡してください。",
      pos_create_unavailable:
        "チェックアウトは利用できません。システム管理者またはサポートに連絡してください。",
      update_cart_unavailable:
        "カートの更新は利用できません。システム管理者またはサポートに連絡してください。",
      remove_from_cart_unavailable:
        "カートからの削除は利用できません。システム管理者またはサポートに連絡してください。",
      empty_cart_unavailable:
        "カートを空にする機能は利用できません。システム管理者またはサポートに連絡してください。",
      nothing_to_update: "更新する内容がありません。",
    },
    nl: {
      search_products_unavailable:
        "Zoeken is niet beschikbaar. Neem contact op met de technische ondersteuning of uw domeinbeheerder.",
      pos_create_unavailable:
        "Afrekenen is niet beschikbaar. Neem contact op met de technische ondersteuning of uw domeinbeheerder.",
      update_cart_unavailable:
        "Winkelwagen bijwerken is niet beschikbaar. Neem contact op met de technische ondersteuning of uw domeinbeheerder.",
      remove_from_cart_unavailable:
        "Verwijderen uit winkelwagen is niet beschikbaar. Neem contact op met de technische ondersteuning of uw domeinbeheerder.",
      empty_cart_unavailable:
        "Winkelwagen legen is niet beschikbaar. Neem contact op met de technische ondersteuning of uw domeinbeheerder.",
      nothing_to_update: "Niets om bij te werken.",
    },
    pl: {
      search_products_unavailable:
        "Wyszukiwanie jest niedostępne. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
      pos_create_unavailable:
        "Finalizacja zakupu jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
      update_cart_unavailable:
        "Aktualizacja koszyka jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
      remove_from_cart_unavailable:
        "Usuwanie z koszyka jest niedostępne. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
      empty_cart_unavailable:
        "Opróżnianie koszyka jest niedostępne. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
      nothing_to_update: "Brak zmian do zaktualizowania.",
    },
    pt: {
      search_products_unavailable:
        "Pesquisa indisponível. Contacte o suporte técnico ou o administrador do domínio.",
      pos_create_unavailable:
        "Finalização indisponível. Contacte o suporte técnico ou o administrador do domínio.",
      update_cart_unavailable:
        "Atualização do carrinho indisponível. Contacte o suporte técnico ou o administrador do domínio.",
      remove_from_cart_unavailable:
        "Remoção do carrinho indisponível. Contacte o suporte técnico ou o administrador do domínio.",
      empty_cart_unavailable:
        "Esvaziar carrinho indisponível. Contacte o suporte técnico ou o administrador do domínio.",
      nothing_to_update: "Nada para atualizar.",
    },
    "pt-br": {
      search_products_unavailable:
        "Busca indisponível. Contate o suporte técnico ou o administrador do domínio.",
      pos_create_unavailable:
        "Finalização indisponível. Contate o suporte técnico ou o administrador do domínio.",
      update_cart_unavailable:
        "Atualização do carrinho indisponível. Contate o suporte técnico ou o administrador do domínio.",
      remove_from_cart_unavailable:
        "Remover do carrinho indisponível. Contate o suporte técnico ou o administrador do domínio.",
      empty_cart_unavailable:
        "Esvaziar carrinho indisponível. Contate o suporte técnico ou o administrador do domínio.",
      nothing_to_update: "Nada para atualizar.",
    },
    ru: {
      search_products_unavailable:
        "Поиск недоступен. Обратитесь в службу поддержки или к администратору домена.",
      pos_create_unavailable:
        "Оформление заказа недоступно. Обратитесь в службу поддержки или к администратору домена.",
      update_cart_unavailable:
        "Обновление корзины недоступно. Обратитесь в службу поддержки или к администратору домена.",
      remove_from_cart_unavailable:
        "Удаление из корзины недоступно. Обратитесь в службу поддержки или к администратору домена.",
      empty_cart_unavailable:
        "Очистка корзины недоступна. Обратитесь в службу поддержки или к администратору домена.",
      nothing_to_update: "Нечего обновлять.",
    },
    tr: {
      search_products_unavailable:
        "Arama kullanılamıyor. Lütfen teknik destekle veya alan adı yöneticinizle iletişime geçin.",
      pos_create_unavailable:
        "Ödeme işlemi kullanılamıyor. Lütfen teknik destekle veya alan adı yöneticinizle iletişime geçin.",
      update_cart_unavailable:
        "Sepeti güncelleme kullanılamıyor. Lütfen teknik destekle veya alan adı yöneticinizle iletişime geçin.",
      remove_from_cart_unavailable:
        "Sepetten kaldırma kullanılamıyor. Lütfen teknik destekle veya alan adı yöneticinizle iletişime geçin.",
      empty_cart_unavailable:
        "Sepeti boşaltma kullanılamıyor. Lütfen teknik destekle veya alan adı yöneticinizle iletişime geçin.",
      nothing_to_update: "Güncellenecek bir şey yok.",
    },
    zh: {
      search_products_unavailable: "搜索不可用。请联系技术支持或域管理员。",
      pos_create_unavailable: "结账不可用。请联系技术支持或域管理员。",
      update_cart_unavailable: "购物车更新不可用。请联系技术支持或域管理员。",
      remove_from_cart_unavailable:
        "无法从购物车移除。请联系技术支持或域管理员。",
      empty_cart_unavailable: "清空购物车不可用。请联系技术支持或域管理员。",
      nothing_to_update: "无可更新的内容。",
    },
  };
  Object.keys(dict).forEach(function (lc) {
    window.translations![lc] = Object.assign(
      {},
      window.translations![lc] || {},
      (dict as Record<string, Record<string, string>>)[lc]
    );
  });
})();
