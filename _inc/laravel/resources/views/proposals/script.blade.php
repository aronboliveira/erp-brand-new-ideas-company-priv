@php
    use App\Models\Utility;
@endphp
<script src="{{ asset('js/jquery.min.js') }} "></script>
<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
@if(isset($proposal) && !empty($proposal->proposal_id) && methods_exists(Utility::class, 'customerProposalNumberFormat'))
    <script async>
    window.translations={
        ar:{proposal_pdf_unavailable:"تعذّر إنشاء أو تنزيل ملف PDF للاقتراح"},
        da:{proposal_pdf_unavailable:"Kunne ikke oprette eller downloade forslagets PDF"},
        de:{proposal_pdf_unavailable:"PDF des Angebots konnte nicht erstellt oder heruntergeladen werden"},
        en:{proposal_pdf_unavailable:"Unable to generate or download the proposal PDF"},
        es:{proposal_pdf_unavailable:"No se pudo generar o descargar el PDF de la propuesta"},
        fr:{proposal_pdf_unavailable:"Impossible de générer ou de télécharger le PDF de la proposition"},
        he:{proposal_pdf_unavailable:"לא ניתן ליצור או להוריד את קובץ ה-PDF של ההצעה"},
        it:{proposal_pdf_unavailable:"Impossibile generare o scaricare il PDF della proposta"},
        ja:{proposal_pdf_unavailable:"提案書のPDFを生成またはダウンロードできませんでした"},
        nl:{proposal_pdf_unavailable:"Kan het PDF-bestand van het voorstel niet genereren of downloaden"},
        pl:{proposal_pdf_unavailable:"Nie można wygenerować ani pobrać pliku PDF oferty"},
        pt:{proposal_pdf_unavailable:"Não foi possível gerar ou baixar o PDF da proposta"},
        "pt-br":{proposal_pdf_unavailable:"Não foi possível gerar ou baixar o PDF da proposta"},
        ru:{proposal_pdf_unavailable:"Не удалось создать или скачать PDF предложения"},
        tr:{proposal_pdf_unavailable:"Teklif PDF’si oluşturulamadı veya indirilemedi"},
        zh:{proposal_pdf_unavailable:"无法生成或下载提案 PDF"}
    };
    </script>
    <script defer>
        (()=>{
            const errFb="# ERROR";
            const dataClientLocalized="data-client-localized";
            const dataGuardMsg="data-guard-msg";
            const DATA_BOUND="data-np-bound";

            const localize=(el,msgKey)=>{
            let msg=errFb;
            if(el.getAttribute("data-sv-localized")==="true"||el.getAttribute(dataClientLocalized)==="true"){
                msg=el.getAttribute(dataGuardMsg)||errFb;
            }else{
                let lang=(window.sessionStorage.getItem("erp-np-lang")||document.documentElement.lang||"en").toLowerCase().replace(/_/g,"-");
                lang=lang==="pt-br"?lang:lang.slice(0,2);
                msg=window.translations?.[lang]?.[msgKey]||el.getAttribute(dataGuardMsg)||window.translations?.en?.[msgKey]||errFb;
                if(msg!==errFb){ el.setAttribute(dataGuardMsg,msg); el.setAttribute(dataClientLocalized,"true"); }
            }
            return msg;
            };

            const showErrorOnPointer=key=>{
            const target=document.body;
            if(!target||target.getAttribute(DATA_BOUND)==="true") return;
            const handler=()=>{
                const text=localize(document.body,key);
                const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast;
                if(hasBootstrap){
                let toast=document.querySelector("#np-error-toast");
                if(!toast){
                    toast=document.createElement("div");
                    toast.id="np-error-toast";
                    toast.className="toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3";
                    toast.setAttribute("role","alert");
                    toast.setAttribute("aria-live","assertive");
                    toast.setAttribute("aria-atomic","true");
                    toast.innerHTML=`<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                    document.body.appendChild(toast);
                }
                new bootstrap.Toast(toast).show();
                }else{
                alert(text);
                }
            };
            target.addEventListener("pointerup",handler,{once:true});
            target.setAttribute(DATA_BOUND,"true");
            const mo=new MutationObserver((_,obs)=>{ if(!document.body.contains(target)){ target.removeEventListener("pointerup",handler); obs.disconnect(); } });
            mo.observe(document.body,{childList:true,subtree:true});
            };

            const closeWindowSafely=()=>{
            try{ setTimeout(()=>{ window.open(window.location.href,"_self"); window.close(); },1000); }catch{}
            };

            try{
            if(typeof $==="undefined"){ console.error("jQuery failed to load"); return; }
            $(window).on("load",()=>{
                try{
                const el=document.getElementById("boxes");
                if(!el||typeof html2pdf==="undefined"){ console.error("html2pdf not available or target missing"); showErrorOnPointer("proposal_pdf_unavailable"); return; }
                const opt={
                    filename:'{{Utility::customerProposalNumberFormat($proposal->proposal_id)}}',
                    image:{type:"jpeg",quality:1},
                    html2canvas:{scale:4,dpi:72,letterRendering:true},
                    jsPDF:{unit:"in",format:"A4"}
                };
                html2pdf().set(opt).from(el).save().then(closeWindowSafely).catch(()=>showErrorOnPointer("proposal_pdf_unavailable"));
                }catch{ showErrorOnPointer("proposal_pdf_unavailable"); }
            });
            }catch(e){
            console.error("Initialization failed",e);
            }
        })();
    </script>
@else
    <script>
        // Failed to load proposal data script
    </script>
@endif

