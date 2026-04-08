/**
 * @fileoverview TypeScript version of public/assets/js/routes/emailTemplates/lang/toggle.js
 * @generated from original JavaScript — automated migration
 * @module toggle
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

((): void => {
  const mailPatch = {
    ar: {
      email_template_toggle_failed: "فشل تحديث حالة قالب البريد الإلكتروني.",
    },
    da: { email_template_toggle_failed: "Kunne ikke opdatere skabelonstatus." },
    de: {
      email_template_toggle_failed:
        "Aktualisieren des E‑Mail‑Vorlagenstatus fehlgeschlagen.",
    },
    en: {
      email_template_toggle_failed: "Failed to update e‑mail template status.",
    },
    es: {
      email_template_toggle_failed:
        "Error al actualizar el estado de la plantilla de correo.",
    },
    fr: {
      email_template_toggle_failed:
        "Échec de la mise à jour du statut du modèle d’e‑mail.",
    },
    he: { email_template_toggle_failed: "עדכון סטטוס תבנית הדוא״ל נכשל." },
    it: {
      email_template_toggle_failed:
        "Impossibile aggiornare lo stato del modello e‑mail.",
    },
    ja: {
      email_template_toggle_failed:
        "メールテンプレートの状態を更新できませんでした。",
    },
    nl: {
      email_template_toggle_failed:
        "Kon status van e‑mailsjabloon niet bijwerken.",
    },
    pl: {
      email_template_toggle_failed:
        "Nie udało się zaktualizować statusu szablonu e‑mail.",
    },
    pt: {
      email_template_toggle_failed:
        "Falha ao atualizar o estado do modelo de e‑mail.",
    },
    "pt-br": {
      email_template_toggle_failed:
        "Falha ao atualizar o status do modelo de e‑mail.",
    },
    ru: {
      email_template_toggle_failed:
        "Не удалось обновить статус шаблона письма.",
    },
    tr: {
      email_template_toggle_failed: "E‑posta şablonu durumu güncellenemedi.",
    },
    zh: { email_template_toggle_failed: "更新电子邮件模板状态失败。" },
  };
  window.translations = Object.keys(window.translations || {}).length
    ? Object.keys(mailPatch).reduce((a: Record<string, Record<string, string>>, l) => {
        a[l] = { ...(a[l] || {}), ...(mailPatch as Record<string, Record<string, string>>)[l] };
        return a;
      }, window.translations!)
    : mailPatch;
})();
