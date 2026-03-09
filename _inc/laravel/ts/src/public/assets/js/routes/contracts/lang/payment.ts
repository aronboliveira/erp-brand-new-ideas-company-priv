/**
 * @fileoverview TypeScript version of public/assets/js/routes/contracts/lang/payment.js
 * @generated from original JavaScript — automated migration
 * @module payment
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

(function (): void {
  if (!window.translations) window.translations = {};
  const t: Record<string, Record<string, string>> = {
    ar: {
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
      route_unavailable: "رابط غير صالح.",
      payment_init_unavailable: "تعذّر تهيئة نموذج الدفع.",
      payment_redirect_unavailable: "تعذّر متابعة إعادة التوجيه بعد الدفع.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      route_unavailable: "Ugyldig URL.",
      payment_init_unavailable: "Kunne ikke initialisere betalingsformularen.",
      payment_redirect_unavailable:
        "Kunne ikke fortsætte med omdirigering efter betaling.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      route_unavailable: "Ungültige URL.",
      payment_init_unavailable:
        "Zahlungsformular konnte nicht initialisiert werden.",
      payment_redirect_unavailable:
        "Weiterleitung nach Zahlung fehlgeschlagen.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      route_unavailable: "Invalid URL.",
      payment_init_unavailable: "Could not initialize the payment form.",
      payment_redirect_unavailable:
        "Could not proceed with post-payment redirect.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      route_unavailable: "URL no válida.",
      payment_init_unavailable: "No se pudo inicializar el formulario de pago.",
      payment_redirect_unavailable:
        "No se pudo continuar con la redirección después del pago.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      route_unavailable: "URL non valide.",
      payment_init_unavailable:
        "Impossible d’initialiser le formulaire de paiement.",
      payment_redirect_unavailable: "Redirection après paiement impossible.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      route_unavailable: "כתובת לא חוקית.",
      payment_init_unavailable: "לא ניתן היה לאתחל את טופס התשלום.",
      payment_redirect_unavailable: "לא ניתן להמשיך בהפניה לאחר תשלום.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      route_unavailable: "URL non valido.",
      payment_init_unavailable:
        "Impossibile inizializzare il modulo di pagamento.",
      payment_redirect_unavailable:
        "Impossibile procedere con il reindirizzamento post-pagamento.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      route_unavailable: "無効なURLです。",
      payment_init_unavailable: "支払いフォームを初期化できませんでした。",
      payment_redirect_unavailable:
        "支払い後のリダイレクトを続行できませんでした。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      route_unavailable: "Ongeldige URL.",
      payment_init_unavailable:
        "Kon het betalingsformulier niet initialiseren.",
      payment_redirect_unavailable:
        "Kon doorgaan met doorverwijzing na betaling niet.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      route_unavailable: "Nieprawidłowy adres URL.",
      payment_init_unavailable:
        "Nie udało się zainicjować formularza płatności.",
      payment_redirect_unavailable:
        "Nie można kontynuować przekierowania po płatności.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      route_unavailable: "URL inválido.",
      payment_init_unavailable:
        "Não foi possível inicializar o formulário de pagamento.",
      payment_redirect_unavailable:
        "Não foi possível continuar o redirecionamento após o pagamento.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      route_unavailable: "URL inválida.",
      payment_init_unavailable:
        "Não foi possível inicializar o formulário de pagamento.",
      payment_redirect_unavailable:
        "Não foi possível continuar o redirecionamento após o pagamento.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      route_unavailable: "Недействительный URL.",
      payment_init_unavailable: "Не удалось инициализировать форму оплаты.",
      payment_redirect_unavailable:
        "Не удалось выполнить перенаправление после оплаты.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      route_unavailable: "Geçersiz URL.",
      payment_init_unavailable: "Ödeme formu başlatılamadı.",
      payment_redirect_unavailable: "Ödeme sonrası yönlendirme sürdürülemedi.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      route_unavailable: "无效的链接。",
      payment_init_unavailable: "无法初始化支付表单。",
      payment_redirect_unavailable: "无法继续支付后的跳转。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations![k] = { ...(window.translations![k] || {}), ...t[k] };
  });
})();
