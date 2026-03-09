/**
 * @fileoverview TypeScript version of public/assets/js/routes/employees/lang/create.js
 * @generated from original JavaScript — automated migration
 * @module create
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
      file_name_append_failed: "تعذر عرض اسم الملف.",
      designation_fetch_failed: "فشل جلب المناصب الوظيفية.",
    },
    da: {
      file_name_append_failed: "Kunne ikke vise filnavnet.",
      designation_fetch_failed: "Kunne ikke hente betegnelse.",
    },
    de: {
      file_name_append_failed: "Dateiname konnte nicht angezeigt werden.",
      designation_fetch_failed: "Abrufen der Bezeichnungen fehlgeschlagen.",
    },
    en: {
      file_name_append_failed: "Unable to display file name.",
      designation_fetch_failed: "Failed to fetch designations.",
    },
    es: {
      file_name_append_failed: "No se pudo mostrar el nombre del archivo.",
      designation_fetch_failed: "Error al obtener designaciones.",
    },
    fr: {
      file_name_append_failed: "Impossible d’afficher le nom du fichier.",
      designation_fetch_failed: "Échec de la récupération des intitulés.",
    },
    he: {
      file_name_append_failed: "לא ניתן להציג את שם הקובץ.",
      designation_fetch_failed: "לא ניתן לאחזר תפקידים.",
    },
    it: {
      file_name_append_failed: "Impossibile mostrare il nome del file.",
      designation_fetch_failed: "Impossibile recuperare le mansioni.",
    },
    ja: {
      file_name_append_failed: "ファイル名を表示できませんでした。",
      designation_fetch_failed: "役職を取得できませんでした。",
    },
    nl: {
      file_name_append_failed: "Bestandsnaam kon niet worden weergegeven.",
      designation_fetch_failed: "Ophalen van functies mislukt.",
    },
    pl: {
      file_name_append_failed: "Nie można wyświetlić nazwy pliku.",
      designation_fetch_failed: "Nie udało się pobrać stanowisk.",
    },
    pt: {
      file_name_append_failed: "Não foi possível exibir o nome do ficheiro.",
      designation_fetch_failed: "Falha ao obter designações.",
    },
    "pt-br": {
      file_name_append_failed: "Não foi possível exibir o nome do arquivo.",
      designation_fetch_failed: "Falha ao buscar cargos.",
    },
    ru: {
      file_name_append_failed: "Не удалось отобразить имя файла.",
      designation_fetch_failed: "Не удалось получить должности.",
    },
    tr: {
      file_name_append_failed: "Dosya adı gösterilemedi.",
      designation_fetch_failed: "Unvanlar alınamadı.",
    },
    zh: {
      file_name_append_failed: "无法显示文件名。",
      designation_fetch_failed: "获取职位失败。",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations![k] = { ...(window.translations![k] || {}),
        ...t[k],
      })
  );
})();
