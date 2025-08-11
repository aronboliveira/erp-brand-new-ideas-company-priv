(() => {
  const BS_LINK = 'link[href*="bootstrap"]';
  const PRINTABLE_AREA_ID = "printableArea";
  const FILENAME_INPUT = "#filename";
  const translations = {
    ar: {
      pdf_save_failed: "فشل حفظ PDF",
      printable_not_found: "لم يتم العثور على المنطقة القابلة للطباعة",
    },
    da: {
      pdf_save_failed: "Kunne ikke gemme PDF",
      printable_not_found: "Printbart område ikke fundet",
    },
    de: {
      pdf_save_failed: "PDF konnte nicht gespeichert werden",
      printable_not_found: "Druckbereich nicht gefunden",
    },
    en: {
      pdf_save_failed: "Failed to save as PDF",
      printable_not_found: "Printable area not found",
    },
    es: {
      pdf_save_failed: "Error al guardar PDF",
      printable_not_found: "Área imprimible no encontrada",
    },
    fr: {
      pdf_save_failed: "Échec de l'enregistrement PDF",
      printable_not_found: "Zone imprimable introuvable",
    },
    he: {
      pdf_save_failed: "שמירת PDF נכשלה",
      printable_not_found: "אזור ההדפסה לא נמצא",
    },
    it: {
      pdf_save_failed: "Salvataggio PDF non riuscito",
      printable_not_found: "Area stampabile non trovata",
    },
    ja: {
      pdf_save_failed: "PDFの保存に失敗しました",
      printable_not_found: "印刷可能な領域が見つかりません",
    },
    nl: {
      pdf_save_failed: "PDF opslaan mislukt",
      printable_not_found: "Afdrukbaar gebied niet gevonden",
    },
    pl: {
      pdf_save_failed: "Nie udało się zapisać PDF",
      printable_not_found: "Nie znaleziono obszaru do druku",
    },
    pt: {
      pdf_save_failed: "Falha ao salvar PDF",
      printable_not_found: "Área imprimível não encontrada",
    },
    "pt-br": {
      pdf_save_failed: "Falha ao salvar PDF",
      printable_not_found: "Área imprimível não encontrada",
    },
    ru: {
      pdf_save_failed: "Не удалось сохранить PDF",
      printable_not_found: "Область для печати не найдена",
    },
    tr: {
      pdf_save_failed: "PDF kaydedilemedi",
      printable_not_found: "Yazdırılabilir alan bulunamadı",
    },
    zh: {
      pdf_save_failed: "保存PDF失败",
      printable_not_found: "未找到可打印区域",
    },
  };

  const toastContainer = (() => {
    const existing = document.querySelector(".toast-container");
    if (existing) return existing;
    const container = document.createElement("div");
    container.className = "toast-container position-fixed bottom-0 end-0 p-3";
    document.body.append(container);
    return container;
  })();

  const showError = (key) => {
    const errFb = "# ERROR";
    const dataClientLocalized = "data-client-localized";
    const dataGuardMsg = "data-guard-msg";
    let msg = errFb;
    if (el.getAttribute("data-sv-localized") === "true" || el.getAttribute(dataClientLocalized) === "true")
      msg = el.getAttribute(dataGuardMsg) || errFb;
    else {
      let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el.getAttribute(dataGuardMsg) ||
        window.translations?.["en"]?.[msgKey] ||
        errFb;
      if (msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    const bs = document.querySelector(BS_LINK);
    if (bs && window.bootstrap?.Toast) {
      const toast = document.createElement("div");
      toast.className = "toast align-items-center text-bg-danger border-0";
      toast.setAttribute("role", "alert");
      toast.setAttribute("aria-live", "assertive");
      toast.setAttribute("aria-atomic", "true");
      toast.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
      toastContainer.append(toast);
      new window.bootstrap.Toast(toast).show();
    } else {
      alert(msg);
    }
  };

  let filename = "document.pdf";
  const saveAsPDF = () => {
    try {
      const element = document.getElementById(PRINTABLE_AREA_ID);
      if (!element) {
        showError("printable_not_found");
        return;
      }

      if (typeof $ === "function") {
        filename = $(FILENAME_INPUT).val() ?? filename;
      } else {
        const input = document.querySelector(FILENAME_INPUT);
        if (input) filename = input.value || filename;
      }

      if (typeof html2pdf !== "function") {
        showError("pdf_save_failed");
        return;
      }

      html2pdf()
        .set({
          margin: 0.3,
          filename,
          image: { type: "jpeg", quality: 1 },
          html2canvas: { scale: 4, dpi: 72, letterRendering: true },
          jsPDF: { unit: "in", format: "A2" },
        })
        .from(element)
        .save();
    } catch {
      showError("pdf_save_failed");
    }
  };

  window.saveAsPDF = saveAsPDF;
})();
