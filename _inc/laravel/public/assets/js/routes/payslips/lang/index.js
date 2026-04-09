(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            payslip_unavailable: "تعذر تحميل قسائم الرواتب.",
            permission_denied: "الإذن مرفوض.",
            deleted_success: "تم حذف قسيمة الراتب بنجاح.",
            no_entries: "لا توجد سجلات.",
            request_failed: "فشل الطلب.",
        },
        da: {
            payslip_unavailable: "Kunne ikke indlæse lønsedler.",
            permission_denied: "Tilladelse nægtet.",
            deleted_success: "Lønseddel blev slettet.",
            no_entries: "Ingen poster fundet.",
            request_failed: "Forespørgslen mislykkedes.",
        },
        de: {
            payslip_unavailable: "Lohnabrechnungen konnten nicht geladen werden.",
            permission_denied: "Zugriff verweigert.",
            deleted_success: "Lohnabrechnung gelöscht.",
            no_entries: "Keine Einträge gefunden.",
            request_failed: "Anfrage fehlgeschlagen.",
        },
        en: {
            payslip_unavailable: "Could not load payslips.",
            permission_denied: "Permission denied.",
            deleted_success: "Payslip deleted successfully.",
            no_entries: "No entries found.",
            request_failed: "The request failed.",
        },
        es: {
            payslip_unavailable: "No se pudieron cargar las nóminas.",
            permission_denied: "Permiso denegado.",
            deleted_success: "Nómina eliminada correctamente.",
            no_entries: "No se encontraron entradas.",
            request_failed: "La solicitud falló.",
        },
        fr: {
            payslip_unavailable: "Impossible de charger les bulletins de paie.",
            permission_denied: "Permission refusée.",
            deleted_success: "Bulletin supprimé avec succès.",
            no_entries: "Aucune entrée trouvée.",
            request_failed: "La requête a échoué.",
        },
        he: {
            payslip_unavailable: "לא ניתן לטעון תלושי שכר.",
            permission_denied: "הגישה נדחתה.",
            deleted_success: "תלוש שכר נמחק בהצלחה.",
            no_entries: "לא נמצאו רשומות.",
            request_failed: "הבקשה נכשלה.",
        },
        it: {
            payslip_unavailable: "Impossibile caricare i cedolini.",
            permission_denied: "Permesso negato.",
            deleted_success: "Cedolino eliminato correttamente.",
            no_entries: "Nessuna voce trovata.",
            request_failed: "Richiesta non riuscita.",
        },
        ja: {
            payslip_unavailable: "給与明細を読み込めませんでした。",
            permission_denied: "権限がありません。",
            deleted_success: "給与明細を削除しました。",
            no_entries: "エントリがありません。",
            request_failed: "リクエストに失敗しました。",
        },
        nl: {
            payslip_unavailable: "Kon loonstroken niet laden.",
            permission_denied: "Toestemming geweigerd.",
            deleted_success: "Loonstrook succesvol verwijderd.",
            no_entries: "Geen items gevonden.",
            request_failed: "Aanvraag mislukt.",
        },
        pl: {
            payslip_unavailable: "Nie można załadować pasków płac.",
            permission_denied: "Brak uprawnień.",
            deleted_success: "Pasek płac usunięty.",
            no_entries: "Brak wyników.",
            request_failed: "Żądanie nie powiodło się.",
        },
        pt: {
            payslip_unavailable: "Não foi possível carregar os holerites.",
            permission_denied: "Permissão negada.",
            deleted_success: "Holerite excluído com sucesso.",
            no_entries: "Nenhum registro encontrado.",
            request_failed: "A solicitação falhou.",
        },
        "pt-br": {
            payslip_unavailable: "Não foi possível carregar os contracheques.",
            permission_denied: "Permissão negada.",
            deleted_success: "Contracheque excluído com sucesso.",
            no_entries: "Nenhum registro encontrado.",
            request_failed: "A solicitação falhou.",
        },
        ru: {
            payslip_unavailable: "Не удалось загрузить расчетные листки.",
            permission_denied: "Доступ запрещен.",
            deleted_success: "Расчетный лист удален.",
            no_entries: "Записей не найдено.",
            request_failed: "Ошибка запроса.",
        },
        tr: {
            payslip_unavailable: "Maaş bordroları yüklenemedi.",
            permission_denied: "İzin reddedildi.",
            deleted_success: "Bordro başarıyla silindi.",
            no_entries: "Kayıt bulunamadı.",
            request_failed: "İstek başarısız oldu.",
        },
        zh: {
            payslip_unavailable: "无法加载工资单。",
            permission_denied: "没有权限。",
            deleted_success: "工资单删除成功。",
            no_entries: "没有记录。",
            request_failed: "请求失败。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();
//# sourceMappingURL=index.js.map