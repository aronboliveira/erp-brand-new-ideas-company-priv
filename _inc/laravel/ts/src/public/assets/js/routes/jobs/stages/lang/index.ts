/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/stages/lang/index.js
 * @generated from original JavaScript — automated migration
 * @module index
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
      create_job_stage_unavailable:
        "مسار إنشاء مرحلة الوظيفة غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول النطاق.",
    },
    da: {
      create_job_stage_unavailable:
        "Ruten til oprettelse af jobstadie er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator.",
    },
    de: {
      create_job_stage_unavailable:
        "Die Route zum Erstellen der Jobphase ist nicht verfügbar. Bitte wenden Sie sich an den technischen Support oder Ihren Domain-Administrator.",
    },
    en: {
      create_job_stage_unavailable:
        "Create job stage route is unavailable. Please contact technical support or your domain administrator.",
    },
    es: {
      create_job_stage_unavailable:
        "La ruta de creación de etapa de trabajo no está disponible. Contacte al soporte técnico o a su administrador de dominio.",
    },
    fr: {
      create_job_stage_unavailable:
        "La route de création des étapes du job n'est pas disponible. Contactez le support technique ou votre administrateur de domaine.",
    },
    he: {
      create_job_stage_unavailable:
        "נתיב יצירת שלב העבודה אינו זמין. אנא פנה לתמיכה טכנית או למנהל הדומיין שלך.",
    },
    it: {
      create_job_stage_unavailable:
        "La rotta di creazione della fase di lavoro non è disponibile. Contatta il supporto tecnico o l'amministratore del dominio.",
    },
    ja: {
      create_job_stage_unavailable:
        "求人ステージ作成ルートが利用できません。テクニカルサポートまたはドメイン管理者にお問い合わせください。",
    },
    nl: {
      create_job_stage_unavailable:
        "Aanmaakroute voor jobfase is niet beschikbaar. Neem contact op met technische ondersteuning of uw domeinbeheerder.",
    },
    pl: {
      create_job_stage_unavailable:
        "Trasa tworzenia etapu pracy jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
    },
    pt: {
      create_job_stage_unavailable:
        "A rota de criação da fase de trabalho não está disponível. Entre em contato com o suporte técnico ou seu administrador de domínio.",
    },
    "pt-br": {
      create_job_stage_unavailable:
        "A rota para criar estágio de trabalho não está disponível. Entre em contato com o suporte técnico ou o administrador do domínio.",
    },
    ru: {
      create_job_stage_unavailable:
        "Маршрут создания этапа вакансии недоступен. Пожалуйста, обратитесь в техническую поддержку или к администратору домена.",
    },
    tr: {
      create_job_stage_unavailable:
        "İş aşaması oluşturma rotası kullanılamıyor. Lütfen teknik destek veya alan yöneticinizle iletişime geçin.",
    },
    zh: {
      create_job_stage_unavailable:
        "创建工作阶段路由不可用。请联系技术支持或您的域管理员。",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations![k] = { ...(window.translations![k] || {}),
        ...t[k],
      })
  );
})();
