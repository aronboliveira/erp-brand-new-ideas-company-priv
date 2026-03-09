(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            file_upload_failed: "فشل تحميل الملف.",
            file_delete_failed: "فشل حذف الملف.",
            notes_save_failed: "فشل حفظ الملاحظات.",
            task_toggle_failed: "فشل تحديث حالة المهمة.",
        },
        da: {
            file_upload_failed: "Kunne ikke uploade fil.",
            file_delete_failed: "Kunne ikke slette fil.",
            notes_save_failed: "Kunne ikke gemme noter.",
            task_toggle_failed: "Kunne ikke ændre opgavestatus.",
        },
        de: {
            file_upload_failed: "Datei-Upload fehlgeschlagen.",
            file_delete_failed: "Datei-Löschung fehlgeschlagen.",
            notes_save_failed: "Notizen konnten nicht gespeichert werden.",
            task_toggle_failed: "Aufgabenstatus konnte nicht aktualisiert werden.",
        },
        en: {
            file_upload_failed: "File upload failed.",
            file_delete_failed: "File deletion failed.",
            notes_save_failed: "Failed to save notes.",
            task_toggle_failed: "Failed to toggle task status.",
        },
        es: {
            file_upload_failed: "Error al subir el archivo.",
            file_delete_failed: "Error al eliminar el archivo.",
            notes_save_failed: "Error al guardar las notas.",
            task_toggle_failed: "Error al actualizar el estado de la tarea.",
        },
        fr: {
            file_upload_failed: "Échec du téléchargement du fichier.",
            file_delete_failed: "Échec de la suppression du fichier.",
            notes_save_failed: "Échec de l’enregistrement des notes.",
            task_toggle_failed: "Échec de la mise à jour du statut de la tâche.",
        },
        he: {
            file_upload_failed: "טעינת הקובץ נכשלה.",
            file_delete_failed: "מחיקת הקובץ נכשלה.",
            notes_save_failed: "שמירת ההערות נכשלה.",
            task_toggle_failed: "עדכון מצב המשימה נכשל.",
        },
        it: {
            file_upload_failed: "Caricamento del file non riuscito.",
            file_delete_failed: "Eliminazione del file non riuscita.",
            notes_save_failed: "Impossibile salvare le note.",
            task_toggle_failed: "Impossibile aggiornare lo stato dell’attività.",
        },
        ja: {
            file_upload_failed: "ファイルのアップロードに失敗しました。",
            file_delete_failed: "ファイルの削除に失敗しました。",
            notes_save_failed: "ノートの保存に失敗しました。",
            task_toggle_failed: "タスクの状態更新に失敗しました。",
        },
        nl: {
            file_upload_failed: "Bestand uploaden mislukt.",
            file_delete_failed: "Bestand verwijderen mislukt.",
            notes_save_failed: "Notities konden niet worden opgeslagen.",
            task_toggle_failed: "Kan taakstatus niet bijwerken.",
        },
        pl: {
            file_upload_failed: "Nie udało się przesłać pliku.",
            file_delete_failed: "Nie udało się usunąć pliku.",
            notes_save_failed: "Nie udało się zapisać notatek.",
            task_toggle_failed: "Nie udało się zaktualizować statusu zadania.",
        },
        pt: {
            file_upload_failed: "Falha no envio do arquivo.",
            file_delete_failed: "Falha na exclusão do arquivo.",
            notes_save_failed: "Falha ao salvar notas.",
            task_toggle_failed: "Falha ao atualizar o status da tarefa.",
        },
        "pt-br": {
            file_upload_failed: "Falha no upload do arquivo.",
            file_delete_failed: "Falha ao excluir o arquivo.",
            notes_save_failed: "Falha ao salvar as anotações.",
            task_toggle_failed: "Falha ao atualizar o status da tarefa.",
        },
        ru: {
            file_upload_failed: "Не удалось загрузить файл.",
            file_delete_failed: "Не удалось удалить файл.",
            notes_save_failed: "Не удалось сохранить заметки.",
            task_toggle_failed: "Не удалось изменить статус задачи.",
        },
        tr: {
            file_upload_failed: "Dosya yüklenemedi.",
            file_delete_failed: "Dosya silinemedi.",
            notes_save_failed: "Notlar kaydedilemedi.",
            task_toggle_failed: "Görev durumu güncellenemedi.",
        },
        zh: {
            file_upload_failed: "文件上传失败。",
            file_delete_failed: "文件删除失败。",
            notes_save_failed: "保存备注失败。",
            task_toggle_failed: "更新任务状态失败。",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();