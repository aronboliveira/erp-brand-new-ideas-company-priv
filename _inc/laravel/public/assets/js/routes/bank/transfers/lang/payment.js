(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            snap_sdk_unavailable: "خدمة الدفع غير محملة.",
            payment_init_failed: "فشل بدء نافذة الدفع.",
            payment_popup_closed: "أغلقت النافذة قبل إتمام الدفع.",
            response_submit_failed: "فشل إرسال نتيجة الدفع.",
        },
        da: {
            snap_sdk_unavailable: "Snap SDK er ikke indlæst.",
            payment_init_failed: "Kunne ikke åbne betalingsvinduet.",
            payment_popup_closed: "Du lukkede vinduet uden at gennemføre betalingen.",
            response_submit_failed: "Kunne ikke sende betalingsresultatet.",
        },
        de: {
            snap_sdk_unavailable: "Snap SDK ist nicht geladen.",
            payment_init_failed: "Fehler beim Öffnen des Zahlungsfensters.",
            payment_popup_closed: "Sie haben das Fenster geschlossen, ohne die Zahlung abzuschließen.",
            response_submit_failed: "Fehler beim Senden des Zahlungsergebnisses.",
        },
        en: {
            snap_sdk_unavailable: "Snap payment SDK is not loaded.",
            payment_init_failed: "Failed to open payment popup.",
            payment_popup_closed: "You closed the popup without finishing the payment.",
            response_submit_failed: "Failed to submit payment response.",
        },
        es: {
            snap_sdk_unavailable: "El SDK de Snap no está cargado.",
            payment_init_failed: "Error al abrir la ventana de pago.",
            payment_popup_closed: "Cerraste la ventana sin completar el pago.",
            response_submit_failed: "Error al enviar la respuesta de pago.",
        },
        fr: {
            snap_sdk_unavailable: "Le SDK Snap n’est pas chargé.",
            payment_init_failed: "Échec de l’ouverture de la fenêtre de paiement.",
            payment_popup_closed: "Vous avez fermé la fenêtre avant de terminer le paiement.",
            response_submit_failed: "Échec de l’envoi de la réponse de paiement.",
        },
        he: {
            snap_sdk_unavailable: "SDK התשלום לא נטען.",
            payment_init_failed: "הפעלת חלון התשלום נכשלה.",
            payment_popup_closed: "סגרתם את החלון מבלי להשלים את התשלום.",
            response_submit_failed: "שליחת תשובת התשלום נכשלה.",
        },
        it: {
            snap_sdk_unavailable: "Snap SDK non caricato.",
            payment_init_failed: "Impossibile aprire la finestra di pagamento.",
            payment_popup_closed: "Hai chiuso il popup senza completare il pagamento.",
            response_submit_failed: "Invio della risposta di pagamento non riuscito.",
        },
        ja: {
            snap_sdk_unavailable: "Snap SDK が読み込まれていません。",
            payment_init_failed: "支払いポップアップを開けませんでした。",
            payment_popup_closed: "支払いを完了せずにポップアップを閉じました。",
            response_submit_failed: "支払い結果の送信に失敗しました。",
        },
        nl: {
            snap_sdk_unavailable: "Snap SDK is niet geladen.",
            payment_init_failed: "Betalingspopup kon niet worden geopend.",
            payment_popup_closed: "Je hebt de popup gesloten zonder de betaling af te ronden.",
            response_submit_failed: "Betalingsrespons verzenden mislukt.",
        },
        pl: {
            snap_sdk_unavailable: "Snap SDK nie jest załadowany.",
            payment_init_failed: "Nie udało się otworzyć okna płatności.",
            payment_popup_closed: "Zamknąłeś okno bez ukończenia płatności.",
            response_submit_failed: "Nie udało się wysłać odpowiedzi płatności.",
        },
        pt: {
            snap_sdk_unavailable: "SDK Snap não está carregado.",
            payment_init_failed: "Falha ao abrir o pop-up de pagamento.",
            payment_popup_closed: "Você fechou o pop-up sem concluir o pagamento.",
            response_submit_failed: "Falha ao enviar resposta de pagamento.",
        },
        "pt-br": {
            snap_sdk_unavailable: "SDK Snap não está carregado.",
            payment_init_failed: "Falha ao abrir o pop-up de pagamento.",
            payment_popup_closed: "Você fechou o pop-up sem concluir o pagamento.",
            response_submit_failed: "Falha ao enviar resposta de pagamento.",
        },
        ru: {
            snap_sdk_unavailable: "Snap SDK не загружен.",
            payment_init_failed: "Не удалось открыть окно оплаты.",
            payment_popup_closed: "Вы закрыли окно, не завершив оплату.",
            response_submit_failed: "Не удалось отправить ответ оплаты.",
        },
        tr: {
            snap_sdk_unavailable: "Snap SDK yüklenmedi.",
            payment_init_failed: "Ödeme penceresi açılamadı.",
            payment_popup_closed: "Ödemeyi tamamlamadan pencereyi kapattınız.",
            response_submit_failed: "Ödeme yanıtı gönderilemedi.",
        },
        zh: {
            snap_sdk_unavailable: "Snap SDK 未加载。",
            payment_init_failed: "无法打开支付弹窗。",
            payment_popup_closed: "您在完成支付前关闭了弹窗。",
            response_submit_failed: "提交支付响应失败。",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();
//# sourceMappingURL=payment.js.map