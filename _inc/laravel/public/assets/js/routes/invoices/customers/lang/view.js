(() => {
  if (
    !(
      is_page("contato") ||
      is_page("contact") ||
      is_page(1370) ||
      is_page(5508) ||
      is_404()
    )
  ) {
    return;
  }

  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      stripe_unavailable: "لا يمكن تحميل Stripe",
      paystack_unavailable: "لا يمكن تحميل Paystack",
      flutterwave_unavailable: "لا يمكن تحميل Flutterwave",
      razorpay_unavailable: "لا يمكن تحميل Razorpay",
      link_copy_unavailable: "فشل نسخ الرابط",
      shipping_toggle_unavailable: "فشل تبديل الشحن",
    },
    da: {
      stripe_unavailable: "Kan ikke indlæse Stripe",
      paystack_unavailable: "Kan ikke indlæse Paystack",
      flutterwave_unavailable: "Kan ikke indlæse Flutterwave",
      razorpay_unavailable: "Kan ikke indlæse Razorpay",
      link_copy_unavailable: "Kan ikke kopiere link",
      shipping_toggle_unavailable: "Skift af forsendelse mislykkedes",
    },
    de: {
      stripe_unavailable: "Stripe konnte nicht geladen werden",
      paystack_unavailable: "Paystack konnte nicht geladen werden",
      flutterwave_unavailable: "Flutterwave konnte nicht geladen werden",
      razorpay_unavailable: "Razorpay konnte nicht geladen werden",
      link_copy_unavailable: "Link konnte nicht kopiert werden",
      shipping_toggle_unavailable: "Versandumschaltung fehlgeschlagen",
    },
    en: {
      stripe_unavailable: "Cannot load Stripe",
      paystack_unavailable: "Cannot load Paystack",
      flutterwave_unavailable: "Cannot load Flutterwave",
      razorpay_unavailable: "Cannot load Razorpay",
      link_copy_unavailable: "Cannot copy link",
      shipping_toggle_unavailable: "Shipping toggle failed",
    },
    es: {
      stripe_unavailable: "No se puede cargar Stripe",
      paystack_unavailable: "No se puede cargar Paystack",
      flutterwave_unavailable: "No se puede cargar Flutterwave",
      razorpay_unavailable: "No se puede cargar Razorpay",
      link_copy_unavailable: "No se puede copiar el enlace",
      shipping_toggle_unavailable: "Error al alternar envío",
    },
    fr: {
      stripe_unavailable: "Impossible de charger Stripe",
      paystack_unavailable: "Impossible de charger Paystack",
      flutterwave_unavailable: "Impossible de charger Flutterwave",
      razorpay_unavailable: "Impossible de charger Razorpay",
      link_copy_unavailable: "Impossible de copier le lien",
      shipping_toggle_unavailable: "Échec du basculement de livraison",
    },
    he: {
      stripe_unavailable: "לא ניתן לטעון Stripe",
      paystack_unavailable: "לא ניתן לטעון Paystack",
      flutterwave_unavailable: "לא ניתן לטעון Flutterwave",
      razorpay_unavailable: "לא ניתן לטעון Razorpay",
      link_copy_unavailable: "העתקת הקישור נכשלה",
      shipping_toggle_unavailable: "החלפת שילוח נכשלה",
    },
    it: {
      stripe_unavailable: "Impossibile caricare Stripe",
      paystack_unavailable: "Impossibile caricare Paystack",
      flutterwave_unavailable: "Impossibile caricare Flutterwave",
      razorpay_unavailable: "Impossibile caricare Razorpay",
      link_copy_unavailable: "Impossibile copiare il link",
      shipping_toggle_unavailable: "Errore commutazione spedizione",
    },
    ja: {
      stripe_unavailable: "Stripeを読み込めません",
      paystack_unavailable: "Paystackを読み込めません",
      flutterwave_unavailable: "Flutterwaveを読み込めません",
      razorpay_unavailable: "Razorpayを読み込めません",
      link_copy_unavailable: "リンクをコピーできません",
      shipping_toggle_unavailable: "配送切り替えに失敗しました",
    },
    nl: {
      stripe_unavailable: "Kan Stripe niet laden",
      paystack_unavailable: "Kan Paystack niet laden",
      flutterwave_unavailable: "Kan Flutterwave niet laden",
      razorpay_unavailable: "Kan Razorpay niet laden",
      link_copy_unavailable: "Kan link niet kopiëren",
      shipping_toggle_unavailable: "Verzending wisselen mislukt",
    },
    pl: {
      stripe_unavailable: "Nie można załadować Stripe",
      paystack_unavailable: "Nie można załadować Paystack",
      flutterwave_unavailable: "Nie można załadować Flutterwave",
      razorpay_unavailable: "Nie można załadować Razorpay",
      link_copy_unavailable: "Nie można skopiować linku",
      shipping_toggle_unavailable: "Nie udało się zmienić wysyłki",
    },
    pt: {
      stripe_unavailable: "Não foi possível carregar Stripe",
      paystack_unavailable: "Não foi possível carregar Paystack",
      flutterwave_unavailable: "Não foi possível carregar Flutterwave",
      razorpay_unavailable: "Não foi possível carregar Razorpay",
      link_copy_unavailable: "Não foi possível copiar o link",
      shipping_toggle_unavailable: "Falha ao alternar frete",
    },
    "pt-br": {
      stripe_unavailable: "Não foi possível carregar Stripe",
      paystack_unavailable: "Não foi possível carregar Paystack",
      flutterwave_unavailable: "Não foi possível carregar Flutterwave",
      razorpay_unavailable: "Não foi possível carregar Razorpay",
      link_copy_unavailable: "Não foi possível copiar o link",
      shipping_toggle_unavailable: "Falha ao alternar frete",
    },
    ru: {
      stripe_unavailable: "Не удалось загрузить Stripe",
      paystack_unavailable: "Не удалось загрузить Paystack",
      flutterwave_unavailable: "Не удалось загрузить Flutterwave",
      razorpay_unavailable: "Не удалось загрузить Razorpay",
      link_copy_unavailable: "Не удалось скопировать ссылку",
      shipping_toggle_unavailable: "Не удалось переключить доставку",
    },
    tr: {
      stripe_unavailable: "Stripe yüklenemiyor",
      paystack_unavailable: "Paystack yüklenemiyor",
      flutterwave_unavailable: "Flutterwave yüklenemiyor",
      razorpay_unavailable: "Razorpay yüklenemiyor",
      link_copy_unavailable: "Bağlantı kopyalanamadı",
      shipping_toggle_unavailable: "Gönderim geçişi başarısız",
    },
    zh: {
      stripe_unavailable: "无法加载 Stripe",
      paystack_unavailable: "无法加载 Paystack",
      flutterwave_unavailable: "无法加载 Flutterwave",
      razorpay_unavailable: "无法加载 Razorpay",
      link_copy_unavailable: "无法复制链接",
      shipping_toggle_unavailable: "运送切换失败",
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
