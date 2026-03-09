/**
 * @fileoverview TypeScript version of public/assets/js/routes/deals/lang/tasks.js
 * @generated from original JavaScript — automated migration
 * @module tasks
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

const langPatch = {
  ar: {
    datepicker_init_failed: "فشل تحميل منتقي التاريخ.",
    timepicker_init_failed: "فشل تحميل منتقي الوقت.",
  },
  da: {
    datepicker_init_failed: "Kunne ikke indlæse datovælger.",
    timepicker_init_failed: "Kunne ikke indlæse tidsvælger.",
  },
  de: {
    datepicker_init_failed: "Datum‑Picker konnte nicht geladen werden.",
    timepicker_init_failed: "Zeit‑Picker konnte nicht geladen werden.",
  },
  en: {
    datepicker_init_failed: "Failed to initialise date picker.",
    timepicker_init_failed: "Failed to initialise time picker.",
  },
  es: {
    datepicker_init_failed: "Error al cargar el selector de fecha.",
    timepicker_init_failed: "Error al cargar el selector de hora.",
  },
  fr: {
    datepicker_init_failed: "Échec du chargement du sélecteur de date.",
    timepicker_init_failed: "Échec du chargement du sélecteur d’heure.",
  },
  he: {
    datepicker_init_failed: "טעינת בוחר התאריך נכשלה.",
    timepicker_init_failed: "טעינת בוחר השעה נכשלה.",
  },
  it: {
    datepicker_init_failed: "Impossibile caricare il Date‑Picker.",
    timepicker_init_failed: "Impossibile caricare il Time‑Picker.",
  },
  ja: {
    datepicker_init_failed: "日付ピッカーの読み込みに失敗しました。",
    timepicker_init_failed: "時刻ピッカーの読み込みに失敗しました。",
  },
  nl: {
    datepicker_init_failed: "Laden van datumpicker mislukt.",
    timepicker_init_failed: "Laden van tijdpicker mislukt.",
  },
  pl: {
    datepicker_init_failed: "Nie udało się załadować wyboru daty.",
    timepicker_init_failed: "Nie udało się załadować wyboru czasu.",
  },
  pt: {
    datepicker_init_failed: "Falha ao carregar o seletor de data.",
    timepicker_init_failed: "Falha ao carregar o seletor de hora.",
  },
  "pt-br": {
    datepicker_init_failed: "Falha ao carregar o seletor de data.",
    timepicker_init_failed: "Falha ao carregar o seletor de hora.",
  },
  ru: {
    datepicker_init_failed: "Не удалось загрузить выбор даты.",
    timepicker_init_failed: "Не удалось загрузить выбор времени.",
  },
  tr: {
    datepicker_init_failed: "Tarih seçici yüklenemedi.",
    timepicker_init_failed: "Saat seçici yüklenemedi.",
  },
  zh: {
    datepicker_init_failed: "日期选择器加载失败。",
    timepicker_init_failed: "时间选择器加载失败。",
  },
};
window.translations = Object.keys(window.translations || {}).length
  ? Object.keys(langPatch).reduce((acc: Record<string, Record<string, string>>, l) => {
      acc[l] = { ...(acc[l] || {}), ...(langPatch as Record<string, Record<string, string>>)[l] };
      return acc;
    }, window.translations as Record<string, Record<string, string>>)
  : langPatch;
