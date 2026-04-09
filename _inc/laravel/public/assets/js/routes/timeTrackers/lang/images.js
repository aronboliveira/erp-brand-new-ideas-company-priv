(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
            ajax_unavailable: "تعذّر الاتصال بالخادم.",
            img_preview_unavailable: "تعذّر فتح معاينة الصور.",
            img_remove_unavailable: "تعذّر حذف الصورة.",
            slider_unavailable: "عارض الشرائح غير متاح.",
            images_empty_label: "لا توجد صور متاحة.",
        },
        da: {
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
            ajax_unavailable: "Serveren kunne ikke kontaktes.",
            img_preview_unavailable: "Kunne ikke åbne billedvisning.",
            img_remove_unavailable: "Kunne ikke fjerne billede.",
            slider_unavailable: "Billedfremviser er ikke tilgængelig.",
            images_empty_label: "Ingen billeder tilgængelige.",
        },
        de: {
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
            ajax_unavailable: "Serveranfrage fehlgeschlagen.",
            img_preview_unavailable: "Bildvorschau konnte nicht geöffnet werden.",
            img_remove_unavailable: "Bild konnte nicht entfernt werden.",
            slider_unavailable: "Slider ist nicht verfügbar.",
            images_empty_label: "Keine Bilder verfügbar.",
        },
        en: {
            plugin_unavailable: "A required library failed to load.",
            ajax_unavailable: "Server request failed.",
            img_preview_unavailable: "Could not open image preview.",
            img_remove_unavailable: "Could not remove image.",
            slider_unavailable: "Slider is unavailable.",
            images_empty_label: "Images not available.",
        },
        es: {
            plugin_unavailable: "No se cargó una biblioteca requerida.",
            ajax_unavailable: "Falló la solicitud al servidor.",
            img_preview_unavailable: "No se pudo abrir la vista previa de imágenes.",
            img_remove_unavailable: "No se pudo eliminar la imagen.",
            slider_unavailable: "El visor no está disponible.",
            images_empty_label: "Imágenes no disponibles.",
        },
        fr: {
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
            ajax_unavailable: "Échec de la requête serveur.",
            img_preview_unavailable: "Impossible d’ouvrir l’aperçu d’images.",
            img_remove_unavailable: "Impossible de supprimer l’image.",
            slider_unavailable: "Le diaporama est indisponible.",
            images_empty_label: "Aucune image disponible.",
        },
        he: {
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
            ajax_unavailable: "בקשת השרת נכשלה.",
            img_preview_unavailable: "לא ניתן היה לפתוח תצוגה מקדימה של תמונות.",
            img_remove_unavailable: "לא ניתן היה להסיר את התמונה.",
            slider_unavailable: "מציג השקופיות אינו זמין.",
            images_empty_label: "אין תמונות זמינות.",
        },
        it: {
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
            ajax_unavailable: "Richiesta al server non riuscita.",
            img_preview_unavailable: "Impossibile aprire l’anteprima immagini.",
            img_remove_unavailable: "Impossibile rimuovere l’immagine.",
            slider_unavailable: "Slider non disponibile.",
            images_empty_label: "Immagini non disponibili.",
        },
        ja: {
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
            ajax_unavailable: "サーバー要求に失敗しました。",
            img_preview_unavailable: "画像プレビューを開けませんでした。",
            img_remove_unavailable: "画像を削除できませんでした。",
            slider_unavailable: "スライダーが利用できません。",
            images_empty_label: "利用可能な画像がありません。",
        },
        nl: {
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
            ajax_unavailable: "Serververzoek is mislukt.",
            img_preview_unavailable: "Kon de afbeeldingvoorbeeld niet openen.",
            img_remove_unavailable: "Kon afbeelding niet verwijderen.",
            slider_unavailable: "Slider niet beschikbaar.",
            images_empty_label: "Geen afbeeldingen beschikbaar.",
        },
        pl: {
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
            ajax_unavailable: "Żądanie do serwera nie powiodło się.",
            img_preview_unavailable: "Nie można otworzyć podglądu obrazów.",
            img_remove_unavailable: "Nie udało się usunąć obrazu.",
            slider_unavailable: "Suwak jest niedostępny.",
            images_empty_label: "Brak dostępnych obrazów.",
        },
        pt: {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            ajax_unavailable: "Falha na solicitação ao servidor.",
            img_preview_unavailable: "Não foi possível abrir a pré-visualização das imagens.",
            img_remove_unavailable: "Não foi possível remover a imagem.",
            slider_unavailable: "Slider indisponível.",
            images_empty_label: "Imagens indisponíveis.",
        },
        "pt-br": {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            ajax_unavailable: "Falha na requisição ao servidor.",
            img_preview_unavailable: "Não foi possível abrir a visualização de imagens.",
            img_remove_unavailable: "Não foi possível remover a imagem.",
            slider_unavailable: "Slider indisponível.",
            images_empty_label: "Imagens indisponíveis.",
        },
        ru: {
            plugin_unavailable: "Не загружена необходимая библиотека.",
            ajax_unavailable: "Сбой запроса к серверу.",
            img_preview_unavailable: "Не удалось открыть предпросмотр изображений.",
            img_remove_unavailable: "Не удалось удалить изображение.",
            slider_unavailable: "Слайдер недоступен.",
            images_empty_label: "Изображения недоступны.",
        },
        tr: {
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
            ajax_unavailable: "Sunucu isteği başarısız oldu.",
            img_preview_unavailable: "Görüntü önizlemesi açılamadı.",
            img_remove_unavailable: "Görüntü kaldırılamadı.",
            slider_unavailable: "Slider kullanılamıyor.",
            images_empty_label: "Görüntü yok.",
        },
        zh: {
            plugin_unavailable: "未能加载所需的库。",
            ajax_unavailable: "服务器请求失败。",
            img_preview_unavailable: "无法打开图片预览。",
            img_remove_unavailable: "无法删除图片。",
            slider_unavailable: "轮播不可用。",
            images_empty_label: "暂无图片可用。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();
//# sourceMappingURL=images.js.map