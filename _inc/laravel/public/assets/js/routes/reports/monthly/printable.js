/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/monthly/printable.js
 * @generated from original JavaScript - manual review recommended
 * @module printable
 */
(() => {
    const BS_LINK = 'link[href*="bootstrap"]', PRINTABLE_AREA = "printableArea", FILENAME_INPUT = "#filename", _translations = {
        ar: {
            pdf_fail: "فشل حفظ الملف كـ PDF",
            no_area: "المنطقة القابلة للطباعة غير موجودة",
            no_lib: "مكتبة PDF غير محملة",
        },
        da: {
            pdf_fail: "Kunne ikke gemme som PDF",
            no_area: "Printbart område ikke fundet",
            no_lib: "PDF-bibliotek ikke indlæst",
        },
        de: {
            pdf_fail: "Konnte nicht als PDF gespeichert werden",
            no_area: "Druckbereich nicht gefunden",
            no_lib: "PDF-Bibliothek nicht geladen",
        },
        en: {
            pdf_fail: "Failed to save as PDF",
            no_area: "Printable area not found",
            no_lib: "PDF library not loaded",
        },
        es: {
            pdf_fail: "Error al guardar PDF",
            no_area: "Área imprimible no encontrada",
            no_lib: "Biblioteca PDF no cargada",
        },
        fr: {
            pdf_fail: "Échec de l'enregistrement PDF",
            no_area: "Zone imprimable introuvable",
            no_lib: "Bibliothèque PDF non chargée",
        },
        he: {
            pdf_fail: "שמירה כ-PDF נכשלה",
            no_area: "אזור ההדפסה לא נמצא",
            no_lib: "ספריית PDF לא נטענה",
        },
        it: {
            pdf_fail: "Salvataggio come PDF non riuscito",
            no_area: "Area stampabile non trovata",
            no_lib: "Libreria PDF non caricata",
        },
        ja: {
            pdf_fail: "PDFとして保存できませんでした",
            no_area: "印刷可能な領域が見つかりません",
            no_lib: "PDFライブラリが読み込まれていません",
        },
        nl: {
            pdf_fail: "Opslaan als PDF mislukt",
            no_area: "Afdrukbaar gebied niet gevonden",
            no_lib: "PDF-bibliotheek niet geladen",
        },
        pl: {
            pdf_fail: "Nie udało się zapisać jako PDF",
            no_area: "Nie znaleziono obszaru do druku",
            no_lib: "Biblioteka PDF nie została załadowana",
        },
        pt: {
            pdf_fail: "Falha ao salvar como PDF",
            no_area: "Área imprimível não encontrada",
            no_lib: "Biblioteca PDF não carregada",
        },
        "pt-br": {
            pdf_fail: "Falha ao salvar como PDF",
            no_area: "Área imprimível não encontrada",
            no_lib: "Biblioteca PDF não carregada",
        },
        ru: {
            pdf_fail: "Не удалось сохранить как PDF",
            no_area: "Область для печати не найдена",
            no_lib: "PDF библиотека не загружена",
        },
        tr: {
            pdf_fail: "PDF olarak kaydedilemedi",
            no_area: "Yazdırılabilir alan bulunamadı",
            no_lib: "PDF kütüphanesi yüklenmedi",
        },
        zh: {
            pdf_fail: "保存为PDF失败",
            no_area: "未找到可打印区域",
            no_lib: "PDF库未加载",
        },
    };
    let toastContainer = null;
    const getToastContainer = () => {
        if (!toastContainer) {
            toastContainer =
                document.querySelector(".toast-container") ??
                    document.createElement("div");
            toastContainer.className =
                "toast-container position-fixed bottom-0 end-0 p-3";
            if (!toastContainer.isConnected)
                document.body.append(toastContainer);
        }
        return toastContainer;
    };
    const showError = (key, el = null) => {
        const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg";
        let msg = errFb;
        if (el?.getAttribute("data-sv-localized") === "true" ||
            el?.getAttribute(dataClientLocalized) === "true")
            msg = el.getAttribute(dataGuardMsg) || errFb;
        else {
            let lang = (window.sessionStorage.getItem("erp-np-lang") ??
                document.documentElement.lang ??
                "en")
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            const msgKey = key;
            msg =
                window.translations?.[lang]?.[msgKey] ||
                    el?.getAttribute(dataGuardMsg) ||
                    window.translations?.en?.[msgKey] ||
                    errFb;
            if (msg !== errFb && el) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
        }
        const hasBootstrap = document.querySelector(BS_LINK) && window.bootstrap.Toast;
        if (hasBootstrap) {
            const container = getToastContainer(), toast = document.createElement("div");
            toast.className = "toast align-items-center text-bg-danger border-0";
            for (const [k, v] of Object.entries({
                role: "alert",
                "aria-live": "assertive",
                "aria-atomic": "true",
            }))
                toast.setAttribute(k, v);
            {
                toast.replaceChildren();
                const _d = document.createElement("div");
                _d.className = "d-flex";
                const _b = document.createElement("div");
                _b.className = "toast-body";
                _b.textContent = msg;
                const _c = document.createElement("button");
                _c.type = "button";
                _c.className = "btn-close btn-close-white me-2 m-auto";
                _c.dataset.bsDismiss = "toast";
                _c.setAttribute("aria-label", "Close");
                _d.append(_b, _c);
                toast.append(_d);
            }
            container.append(toast);
            new bootstrap.Toast(toast, { autohide: true, delay: 5000 }).show();
        }
        else {
            alert(msg);
        }
    };
    const saveAsPDF = () => {
        try {
            if (typeof window.html2pdf !== "object" ||
                // eslint-disable-next-line @typescript-eslint/no-explicit-any, @typescript-eslint/no-unsafe-member-access
                typeof window.html2pdf().set !==
                    "function") {
                showError("no_lib");
                return;
            }
            const printable = document.getElementById(PRINTABLE_AREA);
            if (!printable) {
                showError("no_area");
                return;
            }
            let filename = "document.pdf";
            try {
                filename = String($(FILENAME_INPUT).val() ?? filename);
            }
            catch {
                const input = document.querySelector(FILENAME_INPUT);
                if (input)
                    filename = input.value || filename;
            }
            // eslint-disable-next-line @typescript-eslint/no-explicit-any, @typescript-eslint/no-unsafe-call
            window.html2pdf()
                // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                .set({
                margin: 0.3,
                filename,
                image: { type: "jpeg", quality: 1 },
                html2canvas: { scale: 4, dpi: 72, letterRendering: true },
                jsPDF: { unit: "in", format: "a2" },
            })
                // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                .from(printable)
                // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                .save();
        }
        catch (e) {
            showError("pdf_fail");
        }
    };
    window.saveAsPDF = saveAsPDF;
})();
//# sourceMappingURL=printable.js.map