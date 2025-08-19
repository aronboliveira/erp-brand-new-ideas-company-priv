@php
	use App\Config\Constants\{DatabaseConstants,
        ExtendingLayoutsConstants,SettingsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
	use App\Models\{Utility,WebhookSetting};
    use Collective\Html\FormFacade as Form;
	use Illuminate\Support\Facades\{Auth,Log,Route,URL};
    use Illuminate\Support\Str;
	$lang ??= Utility::fetchUserLang();
	$company_favicon ??= '';
	$color ??= '';
	$colorSettings ??= [];
	$data ??= [];
	$logo ??= '';
	$logo_dark ??= '';
	$logo_light ??= '';
	$currentLang ??= [];
	$siteRtl ??= false;
	$webhookSetting ??= collect([]);
	$faviconUrl ??= '';
	try {
		$data=Utility::prepareCommonViewData()?:[];
		$setting=$data[SettingsConstants::ENTITY]??[];
		$colorSettings=$data[SettingsConstants::CLR_STG]??[];
		$logo=$data[SettingsConstants::LOGO]??'';
		$logo_light=$setting[SettingsConstants::CPN_LG_LT]??'';
		$logo_dark=$setting[SettingsConstants::CPN_LG_DK]??'';
		$company_favicon=$setting[SettingsConstants::CPN_FAVICON_K]??'';
		$color=$data[SettingsConstants::THM_CLR]??'';
		$siteRtl=$data[SettingsConstants::RTL]??false;
		$currentLang=Utility::languages()?:[];
		$lang=Utility::getValByName(SettingsConstants::DEF_LNG)?:'';
		$webhookSetting=WebhookSetting::where(
			DatabaseConstants::TABLE_CREATOR,
			Auth::user()?->creatorId()
		)->get()?:collect([]);
		$faviconUrl=Utility::getCompanyLogo()?:'';
	} catch (\Error $e) {
		Log::error(
			'Error fetching settings/webhook data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching settings/webhook data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching settings/webhook data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	}
    $data = Utility::fallbackSettings($data);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Settings') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Settings') }}</li>
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
    <script async>
        window.translations={
            ar:{summernote_unavailable:'تعذّر تفعيل المحرر',save_failed:'تعذّر حفظ المحتوى',theme_switch_failed:'تعذّر تبديل النمط',preview_update_failed:'تعذّر تحديث المعاينة',scrollspy_failed:'تعذّر تهيئة ScrollSpy',image_preview_failed:'تعذّر معاينة الصورة',tax_toggle_failed:'تعذّر تبديل خيار الضريبة',send_email_failed:'تعذّر فتح نموذج البريد',test_email_failed:'تعذّر إرسال البريد التجريبي'},
            da:{summernote_unavailable:'Kunne ikke aktivere editor',save_failed:'Kunne ikke gemme indhold',theme_switch_failed:'Kunne ikke skifte tema',preview_update_failed:'Kunne ikke opdatere preview',scrollspy_failed:'Kunne ikke initialisere ScrollSpy',image_preview_failed:'Kunne ikke forhåndsvise billede',tax_toggle_failed:'Kunne ikke skifte momsvalg',send_email_failed:'Kunne ikke åbne mailformular',test_email_failed:'Kunne ikke sende testmail'},
            de:{summernote_unavailable:'Editor konnte nicht aktiviert werden',save_failed:'Inhalt konnte nicht gespeichert werden',theme_switch_failed:'Themewechsel fehlgeschlagen',preview_update_failed:'Vorschau konnte nicht aktualisiert werden',scrollspy_failed:'ScrollSpy konnte nicht initialisiert werden',image_preview_failed:'Bildvorschau fehlgeschlagen',tax_toggle_failed:'Steueroption konnte nicht umgeschaltet werden',send_email_failed:'E-Mail-Dialog konnte nicht geöffnet werden',test_email_failed:'Test-E-Mail konnte nicht gesendet werden'},
            en:{summernote_unavailable:'Editor unavailable',save_failed:'Failed to save content',theme_switch_failed:'Failed to switch theme',preview_update_failed:'Failed to update preview',scrollspy_failed:'Failed to init ScrollSpy',image_preview_failed:'Failed to preview image',tax_toggle_failed:'Failed to toggle tax option',send_email_failed:'Failed to open email dialog',test_email_failed:'Failed to send test email'},
            es:{summernote_unavailable:'Editor no disponible',save_failed:'Error al guardar contenido',theme_switch_failed:'Error al cambiar tema',preview_update_failed:'No se pudo actualizar la vista previa',scrollspy_failed:'No se pudo iniciar ScrollSpy',image_preview_failed:'No se pudo previsualizar la imagen',tax_toggle_failed:'No se pudo cambiar la opción de impuesto',send_email_failed:'No se pudo abrir el diálogo de correo',test_email_failed:'No se pudo enviar el correo de prueba'},
            fr:{summernote_unavailable:'Éditeur indisponible',save_failed:'Échec de l’enregistrement',theme_switch_failed:'Échec du changement de thème',preview_update_failed:'Échec de mise à jour de l’aperçu',scrollspy_failed:'Échec d’initialisation de ScrollSpy',image_preview_failed:'Échec de l’aperçu de l’image',tax_toggle_failed:'Échec du basculement fiscal',send_email_failed:'Échec d’ouverture du formulaire email',test_email_failed:'Échec de l’envoi de l’email de test'},
            he:{summernote_unavailable:'העורך לא זמין',save_failed:'שמירת התוכן נכשלה',theme_switch_failed:'כשל במעבר ערכת נושא',preview_update_failed:'כשל בעדכון התצוגה',scrollspy_failed:'כשל בהפעלת ScrollSpy',image_preview_failed:'כשל בתצוגה מקדימה',tax_toggle_failed:'כשל בהחלפת אפשרות מס',send_email_failed:'כשל בפתיחת דיאלוג אימייל',test_email_failed:'כשל בשליחת אימייל בדיקה'},
            it:{summernote_unavailable:'Editor non disponibile',save_failed:'Salvataggio contenuto non riuscito',theme_switch_failed:'Cambio tema non riuscito',preview_update_failed:'Aggiornamento anteprima non riuscito',scrollspy_failed:'Inizializzazione ScrollSpy non riuscita',image_preview_failed:'Anteprima immagine non riuscita',tax_toggle_failed:'Impossibile cambiare opzione fiscale',send_email_failed:'Impossibile aprire il dialogo email',test_email_failed:'Invio email di test non riuscito'},
            ja:{summernote_unavailable:'エディターを利用できません',save_failed:'保存に失敗しました',theme_switch_failed:'テーマの切替に失敗',preview_update_failed:'プレビューの更新に失敗',scrollspy_failed:'ScrollSpy の初期化に失敗',image_preview_failed:'画像プレビューに失敗',tax_toggle_failed:'税オプションの切替に失敗',send_email_failed:'メールダイアログを開けませんでした',test_email_failed:'テストメールの送信に失敗'},
            nl:{summernote_unavailable:'Editor niet beschikbaar',save_failed:'Opslaan mislukt',theme_switch_failed:'Thema wisselen mislukt',preview_update_failed:'Voorbeeld bijwerken mislukt',scrollspy_failed:'ScrollSpy initialiseren mislukt',image_preview_failed:'Afbeeldingsvoorbeeld mislukt',tax_toggle_failed:'Wisselen belastingoptie mislukt',send_email_failed:'E-mailvenster openen mislukt',test_email_failed:'Testmail verzenden mislukt'},
            pl:{summernote_unavailable:'Edytor niedostępny',save_failed:'Nie udało się zapisać treści',theme_switch_failed:'Nie udało się zmienić motywu',preview_update_failed:'Nie udało się zaktualizować podglądu',scrollspy_failed:'Nie udało się zainicjować ScrollSpy',image_preview_failed:'Nie udało się podejrzeć obrazu',tax_toggle_failed:'Nie udało się przełączyć opcji podatku',send_email_failed:'Nie udało się otworzyć okna e-mail',test_email_failed:'Nie udało się wysłać testowej wiadomości'},
            pt:{summernote_unavailable:'Editor indisponível',save_failed:'Falha ao salvar conteúdo',theme_switch_failed:'Falha ao alternar tema',preview_update_failed:'Falha ao atualizar a prévia',scrollspy_failed:'Falha ao iniciar o ScrollSpy',image_preview_failed:'Falha na pré-visualização da imagem',tax_toggle_failed:'Falha ao alternar imposto',send_email_failed:'Falha ao abrir o diálogo de e-mail',test_email_failed:'Falha ao enviar o e-mail de teste'},
            'pt-br':{summernote_unavailable:'Editor indisponível',save_failed:'Falha ao salvar conteúdo',theme_switch_failed:'Falha ao alternar tema',preview_update_failed:'Falha ao atualizar a prévia',scrollspy_failed:'Falha ao iniciar o ScrollSpy',image_preview_failed:'Falha na pré-visualização da imagem',tax_toggle_failed:'Falha ao alternar imposto',send_email_failed:'Falha ao abrir o diálogo de e-mail',test_email_failed:'Falha ao enviar o e-mail de teste'},
            ru:{summernote_unavailable:'Редактор недоступен',save_failed:'Не удалось сохранить',theme_switch_failed:'Не удалось сменить тему',preview_update_failed:'Не удалось обновить предпросмотр',scrollspy_failed:'Не удалось инициализировать ScrollSpy',image_preview_failed:'Не удалось показать превью',tax_toggle_failed:'Не удалось переключить опцию налога',send_email_failed:'Не удалось открыть форму почты',test_email_failed:'Не удалось отправить тестовое письмо'},
            tr:{summernote_unavailable:'Editör kullanılamıyor',save_failed:'İçerik kaydedilemedi',theme_switch_failed:'Tema değiştirilemedi',preview_update_failed:'Önizleme güncellenemedi',scrollspy_failed:'ScrollSpy başlatılamadı',image_preview_failed:'Görsel önizleme başarısız',tax_toggle_failed:'Vergi seçeneği değiştirilemedi',send_email_failed:'E-posta penceresi açılamadı',test_email_failed:'Test e-postası gönderilemedi'},
            zh:{summernote_unavailable:'编辑器不可用',save_failed:'保存失败',theme_switch_failed:'主题切换失败',preview_update_failed:'预览更新失败',scrollspy_failed:'ScrollSpy 初始化失败',image_preview_failed:'图片预览失败',tax_toggle_failed:'税选项切换失败',send_email_failed:'无法打开邮件对话框',test_email_failed:'测试邮件发送失败'}
        };
    </script>
    <script defer>
        (()=>{
            const ERR="# ERROR";
            const D_CLIENT="data-client-localized";
            const D_MSG="data-guard-msg";
            const D_BOUND="data-settings-bound";
            const once=(el,ev,fn,opt)=>{ if(!el) return; const h=(e)=>fn(e); el.addEventListener(ev,h,{once:true,...(opt||{})}); const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener(ev,h); o.disconnect(); }}); mo.observe(document.body,{childList:true,subtree:true}); };
            const langKey=()=>{ let l=(window.sessionStorage.getItem("erp-np-lang")||document.documentElement.lang||"en").toLowerCase().replace(/_/g,"-"); return l==="pt-br"?l:l.slice(0,2); };
            const t=(k,el)=>{ let v=ERR; if(el?.getAttribute("data-sv-localized")==="true"||el?.getAttribute(D_CLIENT)==="true"){ v=el.getAttribute(D_MSG)||ERR; }else{ v=window.translations?.[langKey()]?.[k]||el?.getAttribute(D_MSG)||window.translations?.en?.[k]||ERR; if(v!==ERR){ el?.setAttribute(D_MSG,v); el?.setAttribute(D_CLIENT,"true"); } } return v; };
            const toast=(msg)=>{ const hasBs=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast; if(hasBs){ let el=document.querySelector("#err-toast"); if(!el){ el=document.createElement("div"); el.id="err-toast"; el.className="toast align-items-center text-bg-danger border-0"; el.setAttribute("role","alert"); el.setAttribute("aria-live","assertive"); el.setAttribute("aria-atomic","true"); el.innerHTML=`<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`; document.body.appendChild(el); } new bootstrap.Toast(el).show(); } else { alert(msg); } };
            const showOn=(origin,key,ev='pointerup')=>once(document,ev,()=>toast(t(key, origin)));
            const safeUrl=(u)=>typeof u==="string"&&u.trim()!==""&&u.trim()!=="#";
            const $=(...a)=>window.jQuery?.apply?.(window.jQuery,a)??window.jQuery(...a); if(typeof jQuery==='undefined'){ console.error("jQuery failed to load"); return; }

            // 1) Summernote blur handlers (4 templates + footer notes) with reuse
            const bindSummernoteSave=(selector, urlKey)=>{
            const $els=$(selector);
            if(!$els.length){ return; }
            if(!$.fn?.summernote){ console.error("Summernote not available"); showOn(document.body,'summernote_unavailable'); return; }
            $els.off('summernote.blur.__guard').on('summernote.blur.__guard',function(){
                const el=this; const url=urlKey();
                if(!safeUrl(url)){ showOn(el,'save_failed'); return; }
                $.ajax({
                url:url, type:'POST',
                data:{ _token:$('meta[name="csrf-token"]').attr('content')||'', content: $(el).val()??'' },
                success:(res)=>{ if(res?.is_success){ if(typeof show_toastr==='function') show_toastr('success',res.success,'success'); } else { showOn(el,'save_failed'); } },
                error:(xhr)=>{ const r=xhr?.responseJSON; const ok=r?.is_success===true; if(!ok){ showOn(el,'save_failed'); } }
                });
            });
            };

            bindSummernoteSave('.summernote-simple0',()=> "{{ route('offer_letter.update', $offerlang) }}");
            bindSummernoteSave('.summernote-simple1',()=> "{{ route('joining_letter.update', $joininglang) }}");
            bindSummernoteSave('.summernote-simple2',()=> "{{ route('experience_certificate.update', $explang) }}");
            bindSummernoteSave('.summernote-simple3',()=> "{{ route('noc.update', $noclang) }}");
            bindSummernoteSave('.summernote-simple4',()=> "{{ route('systems.settings.footernote') }}"); // footer notes

            // 2) Theme switches
            const darkChk=document.querySelector("#cust-darklayout");
            if(darkChk && !darkChk.getAttribute(D_BOUND)){
            darkChk.setAttribute(D_BOUND,"1");
            darkChk.addEventListener("click",()=>{
                try{
                const styleEl=document.querySelector('#style');
                const logo=$('.dash-sidebar .main-logo a img');
                const darkHref='{{ env('APP_URL') }}'+'/public/assets/css/style-dark.css';
                const lightHref='{{ env('APP_URL') }}'+'/public/assets/css/style.css';
                if(darkChk.checked){ styleEl?.setAttribute('href',darkHref); if(logo.length){ logo.attr('src','{{ $logo . $logo_light }}'); } }
                else{ styleEl?.setAttribute('href',lightHref); if(logo.length){ logo.attr('src','{{ $logo . $logo_dark }}'); } }
                }catch{ showOn(darkChk,'theme_switch_failed','click'); }
            });
            }
            const bgChk=document.querySelector("#cust-theme-bg");
            if(bgChk && !bgChk.getAttribute(D_BOUND)){
            bgChk.setAttribute(D_BOUND,"1");
            bgChk.addEventListener("click",()=>{
                try{
                const sb=document.querySelector(".dash-sidebar");
                const hd=document.querySelector(".dash-header:not(.dash-mob-header)");
                if(bgChk.checked){ sb?.classList.add("transprent-bg"); hd?.classList.add("transprent-bg"); }
                else{ sb?.classList.remove("transprent-bg"); hd?.classList.remove("transprent-bg"); }
                }catch{ showOn(bgChk,'theme_switch_failed','click'); }
            });
            }

            // 3) Live previews (invoice / proposal / bill)
            $(document).on("change","select[name='invoice_template'], input[name='invoice_color']",function(){
            try{
                const template=$("select[name='invoice_template']").val()??""; const color=$("input[name='invoice_color']:checked").val()??"";
                const src=`{{ url('/invoices/preview') }}/${template}/${color}`;
                if(document.querySelector('#invoice_frame')) $('#invoice_frame').attr('src',src);
            }catch{ showOn(this,'preview_update_failed','click'); }
            });
            $(document).on("change","select[name='proposal_template'], input[name='proposal_color']",function(){
            try{
                const template=$("select[name='proposal_template']").val()??""; const color=$("input[name='proposal_color']:checked").val()??"";
                const src=`{{ url('/'.ViewsConstants::PPS.'/preview') }}/${template}/${color}`;
                if(document.querySelector('#proposal_frame')) $('#proposal_frame').attr('src',src);
            }catch{ showOn(this,'preview_update_failed','click'); }
            });
            $(document).on("change","select[name='bill_template'], input[name='bill_color']",function(){
            try{
                const template=$("select[name='bill_template']").val()??""; const color=$("input[name='bill_color']:checked").val()??"";
                const src=`{{ url('/bill/preview') }}/${template}/${color}`;
                if(document.querySelector('#bill_frame')) $('#bill_frame').attr('src',src);
            }catch{ showOn(this,'preview_update_failed','click'); }
            });

            // 4) ScrollSpy (Bootstrap)
            try{
            if(window.bootstrap?.ScrollSpy){ new bootstrap.ScrollSpy(document.body,{target:'#useradd-sidenav',offset:300}); }
            else{ /* no bootstrap: silently ignore */ }
            }catch{ showOn(document.body,'scrollspy_failed','click'); }

            // 5) Theme color radio sync
            $(document).on('click','.themes-color-change',function(){
            try{
                const color=$(this).data('value'); $('.theme-color').prop('checked',false);
                $('.themes-color-change').removeClass('active_color'); $(this).addClass('active_color');
                $(`input[value=${color}]`).prop('checked',true);
            }catch{ /* non-critical */ }
            });

            // 6) Image previews on file inputs (IDs may be constants)
            const bindPreview=(inputId,imgId)=>{
            const i=document.getElementById(inputId); const img=document.getElementById(imgId);
            if(!i||!img) return;
            if(i.getAttribute(D_BOUND)==='1') return;
            i.setAttribute(D_BOUND,'1');
            i.addEventListener('change',()=>{ try{ const f=i.files?.[0]; if(!f) return; const src=URL.createObjectURL(f); img.src=src; }catch{ showOn(i,'image_preview_failed','click'); } });
            };
            bindPreview(String(SettingsConstants::CPN_LG_DK),'image');
            bindPreview('company_logo_light','image1');
            bindPreview(String(SettingsConstants::CPN_FAVICON_K),'image2');

            // 7) VAT/GST toggle
            $(document).on('change','#vat_gst_number_switch',function(){
            try{ $(this).is(':checked')?$('.tax_type_div').removeClass('d-none'):$('.tax_type_div').addClass('d-none'); }
            catch{ showOn(this,'tax_toggle_failed','click'); }
            });

            // 8) Mail dialog + test send
            $(document).on("click",".send_email",function(e){
            e.preventDefault();
            const el=this; const title=$(el).attr('data-title')||''; const size='md'; const url=$(el).attr('data-url')||'';
            if(!safeUrl(url)){ showOn(el,'send_email_failed'); return; }
            try{
                $("#commonModal .modal-title").html(title);
                $("#commonModal .modal-dialog").addClass('modal-'+size);
                $("#commonModal").modal('show');
                $.post(url,{
                _token:'{{ csrf_token() }}',
                mail_driver:$("#mail_driver").val(), mail_host:$("#mail_host").val(), mail_port:$("#mail_port").val(),
                mail_username:$("#mail_username").val(), mail_password:$("#mail_password").val(),
                mail_encryption:$("#mail_encryption").val(), mail_from_address:$("#mail_from_address").val(),
                mail_from_name:$("#mail_from_name").val()
                },(data)=>{ $('#commonModal .body').html(data); }).fail(()=>showOn(el,'send_email_failed'));
            }catch{ showOn(el,'send_email_failed'); }
            });

            $(document).on('submit','#test_email',function(e){
            e.preventDefault();
            const form=this; const url=$(form).attr('action')||'';
            if(!safeUrl(url)){ showOn(form,'test_email_failed'); return; }
            const post=$(form).serialize();
            $.ajax({
                type:'post', url, data:post, cache:false,
                beforeSend:()=>$('#test_email .btn-create').attr('disabled','disabled'),
                success:(data)=>{ if(data?.success){ show_toastr?.('success',data.message,'success'); } else { showOn(form,'test_email_failed'); } $('#commonModal').modal('hide'); },
                complete:()=>$('#test_email .btn-create').removeAttr('disabled')
            }).fail(()=>showOn(form,'test_email_failed'));
            });

        })();
    </script>
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="{{ VC::CD_STK }}" style="top:30px">
                        @php
                            $anchors = [
                                'brand-settings',
                                'system-settings',
                                'company-settings',
                                'email-settings',
                                'tracker-settings',
                                'payment-settings',
                                'zoom-settings',
                                'slack-settings',
                                'telegram-settings',
                                'twilio-settings',
                                'email-notification-settings',
                                'offer-letter-settings',
                                'joining-letter-settings',
                                'experience-certificate-settings',
                                'noc-settings',
                                'google-calendar',
                                'webhook-settings',
                                'ip-restriction-settings',
                            ];
                        @endphp
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            @foreach($anchors as $anchor)
                                @php
                                    $label = preg_replace('/-+/', ' ', $anchor);
                                    $label = ucwords($label);
                                @endphp
                                <a href="#{{ $anchor }}"
                                    class="list-group-item list-group-item-action border-0">
                                    {{ __($label) }}
                                    <div class="float-end"><i class="{{ VC::TI_CHV_RT }}"></i></div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div id="brand-settings" class="card">
                        @php
                            $businessSettingBaseName                   = 'business.setting';
                            $businessSettingKebabName                  = Str::kebab($businessSettingBaseName);
                            $businessSettingResolvedName               = Route::has($businessSettingBaseName) ? $businessSettingBaseName : (Route::has($businessSettingKebabName) ? $businessSettingKebabName : null);
                            $businessSettingRouteArray                 = $businessSettingResolvedName ? [$businessSettingResolvedName] : ['#'];
                            $businessSettingUrl                        = $businessSettingResolvedName ? route($businessSettingResolvedName) : '#';
                            $businessSettingGuardMsg                   = Utility::fetchLinkMessage($lang, 'business', 'business_setting_route_unavailable') ?? 'Business setting route is unavailable. Please contact technical support or your domain administrator.';
                            $businessSettingFormId                     = 'business-setting-form';
                        @endphp
                        {!! Form::model($setting, [
                            'route'          => $businessSettingRouteArray,
                            'method'         => 'POST',
                            'enctype'        => 'multipart/form-data',
                            'id'             => $businessSettingFormId,
                            'data-url'       => $businessSettingUrl,
                            'data-guard-msg' => $businessSettingGuardMsg
                        ]) !!}
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        const form = document.getElementById('{{ $businessSettingFormId }}');
                                        if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                        form.setAttribute('data-listener-active', 'true');
                                        form.addEventListener('submit', e => {
                                            try {
                                                const url = form.getAttribute('data-url') || '#';
                                                const action = form.getAttribute('action') || '#';
                                                if (url !== '#' || action !== '#') return;
                                                e.preventDefault();
                                                const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
                                                    document.body.appendChild(container);
                                                }
                                                if (hasBootstrap) {
                                                    const toast = document.createElement('div');
                                                    toast.className = 'toast';
                                                    toast.setAttribute('role','alert');
                                                    toast.setAttribute('aria-live','assertive');
                                                    toast.setAttribute('aria-atomic','true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = msg;
                                                    toast.appendChild(body);
                                                    container.appendChild(toast);
                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                } else {
                                                    alert(msg);
                                                }
                                                form.setAttribute('data-failed-route', 'true');
                                            } catch (err) {}
                                        });
                                    })();
                                </script>
                            @endpush
                            <div class="card-header">
                                <h5>{{ __('Brand Settings') }}</h5>
                                <small class="text-muted">{{ __('Edit your brand details') }}</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-4 col-sm-6 col-md-6">
                                        <div class="{{ VC::CD }} logo_card">
                                            <div class="card-header">
                                                <h5>{{ __('Logo dark') }}</h5>
                                            </div>
                                            <div class="card-body pt-0">
                                                <div class="setting-card">
                                                    <div class="logo-content {{ VC::MT4 }}">
                                                        <img id="image"
                                                            src="{{ $logo . '/' . (isset($logo_dark) && !empty($logo_dark) ? $logo_dark : SettingsConstants::CPN_LG_DK_DEF) . '?timestamp=' . time() }}"
                                                            class="big-logo">
                                                    </div>
                                                    <div class="choose-files mt-5">
                                                        <label for="company_logo_dark">
                                                            <div class="{{ VC::BG_P }} company_logo_update">
                                                                <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                            </div>
                                                            <input type="file"
                                                                name="company_logo_dark"
                                                                id="company_logo_dark"
                                                                class="{{ VC::FM_CT }} file setting_logo"
                                                                data-filename="company_logo_update">
                                                        </label>
                                                    </div>
                                                    @error(SettingsConstants::CPN_LG_DK)
                                                        <div class="{{ VC::RW }}">
                                                            <span class="invalid-logo" role="alert">
                                                                <strong class="text-danger">{{ $message }}</strong>
                                                            </span>
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 col-md-6">
                                        <div class="{{ VC::CD }} logo_card">
                                            <div class="card-header">
                                                <h5>{{ __('Logo Light') }}</h5>
                                            </div>
                                            <div class="card-body pt-0">
                                                <div class="setting-card">
                                                    <div class="logo-content {{ VC::MT4 }}">
                                                        <img id="image1"
                                                            src="{{ $logo . '/' . (isset($logo_light) && !empty($logo_light) ? $logo_light : SettingsConstants::CPN_LG_LT_DEF) . '?timestamp=' . time() }}"
                                                            class="big-logo img_setting">
                                                    </div>
                                                    <div class="choose-files mt-5">
                                                        <label for="company_logo_light">
                                                            <div class="{{ VC::BG_P }} dark_logo_update">
                                                                <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                            </div>
                                                            <input type="file"
                                                                class="{{ VC::FM_CT }} file setting_logo"
                                                                name="company_logo_light"
                                                                id="company_logo_light"
                                                                data-filename="dark_logo_update">
                                                        </label>
                                                    </div>

                                                    @error(SettingsConstants::CPN_LG_LT)
                                                        <div class="{{ VC::RW }}">
                                                            <span class="invalid-logo" role="alert">
                                                                <strong class="text-danger">{{ $message }}</strong>
                                                            </span>
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 col-md-6">
                                        <div class="{{ VC::CD }} logo_card">
                                            <div class="card-header">
                                                <h5>{{ __('Favicon') }}</h5>
                                            </div>
                                            <div class="card-body pt-0">
                                                <div class="setting-card">
                                                    <div class="logo-content {{ VC::MT4 }}">
                                                        <img id="image2" src="{{ $faviconUrl }}" width="50px" class="img_setting">
                                                    </div>
                                                    <div class="choose-files mt-5">
                                                        <label for="company_favicon">
                                                            <div class="{{ VC::BG_P }} company_favicon_update">
                                                                <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                            </div>
                                                            <input type="file"
                                                                class="{{ VC::FM_CT }} file setting_logo"
                                                                id="company_favicon"
                                                                name="company_favicon"
                                                                data-filename="company_favicon_update">
                                                        </label>
                                                    </div>

                                                    @error(SettingsConstants::LOGO)
                                                        <div class="{{ VC::RW }}">
                                                            <span class="invalid-logo" role="alert">
                                                                <strong class="text-danger">{{ $message }}</strong>
                                                            </span>
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-3">
                                        {{ Form::label('title_text', __('Title Text'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('title_text', null, ['class' => VC::FM_CT, 'placeholder' => __('Title Text')]) }}
                                        @error('title_text')
                                            <span class="invalid-title_text" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 {{ VC::FM_G }}">
                                        {{ Form::label(SettingsConstants::FT_TXT, __('Footer Text'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text(SettingsConstants::FT_TXT, Utility::getValByName(SettingsConstants::FT_TXT), ['class' => VC::FM_CT, 'placeholder' => __('Enter Footer Text')]) }}
                                        @error(SettingsConstants::FT_TXT)
                                            <span class="invalid-footer_text" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label(SettingsConstants::DEF_LNG, __('Default Language'), ['class' => VC::FM_LB . ' text-dark']) }}
                                            <div class="changeLanguage">
                                                <select name="default_language" id="default_language" class="{{ VC::FM_CT_SL }}">
                                                    @foreach (\App\Models\Utility::languages() as $code => $language)
                                                        <option @if ($lang == $code) selected @endif value="{{ $code }}">{{ ucfirst($language) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error(SettingsConstants::DEF_LNG)
                                                <span class="invalid-default_language" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="{{ VC::FM_G }} col-md-3">
                                        <div class="{{ VC::CST_CTL }} custom-switch">
                                            <label class="text-dark mb-1 mt-1" for="SITE_RTL">{{ __('Enable RTL') }}</label>
                                            <div>
                                                <input type="checkbox"
                                                    name="SITE_RTL"
                                                    id="SITE_RTL"
                                                    data-toggle="switchbutton"
                                                    data-onstyle="primary"
                                                    {{ $siteRtl == 'on' ? 'checked="checked"' : '' }}>
                                                <label class="{{ VC::CST_LB }}" for="SITE_RTL"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <h5 class="small-title mt-2">{{ __('Theme Customizer') }}</h5>
                                    <div class="setting-card setting-logo-box">
                                        <div class="{{ VC::RW }}">
                                            <div class="col-lg-4 col-xl-4 col-md-4">
                                                <h6 class="{{ VC::MT1 }}">
                                                    <i data-feather="credit-card" class="me-2"></i>{{ __('Primary color settings') }}
                                                </h6>
                                                <hr class="my-2" />
                                                <div class="theme-color themes-color">
                                                    @foreach(range(1, 10) as $themeNumber)
                                                        @php $themeValue = "theme-{$themeNumber}"; @endphp
                                                        <a href="#!"
                                                        class="themes-color-change {{ $color == $themeValue ? 'active_color' : '' }}"
                                                        data-value="{{ $themeValue }}"></a>
                                                        <input type="radio"
                                                            class="theme_color d-none"
                                                            name="color"
                                                            value="{{ $themeValue }}"{{ $color == $themeValue ? ' checked' : '' }}>
                                                        @if($themeNumber == 5)
                                                            <br>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="col-lg-4 col-xl-4 col-md-4">
                                                <h6 class="{{ VC::MT1 }}">
                                                    <i data-feather="layout" class="me-2"></i>{{ __('Sidebar settings') }}
                                                </h6>
                                                <hr class="{{ VC::MT1 }}" />
                                                <div class="form-check form-switch">
                                                    <input type="checkbox"
                                                        class="form-check-input"
                                                        id="cust-theme-bg"
                                                        name="cust_theme_bg"
                                                        {{ !empty($setting[SettingsConstants::CST_BG]) && $setting[SettingsConstants::CST_BG] == 'on' ? 'checked' : '' }} />
                                                    <label class="form-check-label {{ VC::FW600 }} ps-1" for="cust-theme-bg">
                                                        {{ __('Transparent layout') }}
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-lg-4 col-xl-4 col-md-4">
                                                <h6 class="{{ VC::MT1 }}">
                                                    <i data-feather="sun" class="me-2"></i>{{ __('Layout settings') }}
                                                </h6>
                                                <hr class="{{ VC::MT1 }}" />
                                                <div class="form-check form-switch {{ VC::MT2 }}">
                                                    <input type="checkbox"
                                                        class="form-check-input"
                                                        id="cust-darklayout"
                                                        name="cust_darklayout"
                                                        {{ !empty($colorSettings[SettingsConstants::CST_DRK]) && $colorSettings[SettingsConstants::CST_DRK] == 'on' ? 'checked' : '' }} />
                                                    <label class="form-check-label {{ VC::FW600 }} ps-1" for="cust-darklayout">
                                                        {{ __('Dark Layout') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <div class="form-group">
                                    <input class="{{ VC::BT_PR_PRM10 }}" type="submit"
                                        value="{{ __('Save Changes') }}">
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                    <div id="system-settings" class="card">
                        <div class="card-header">
                            <h5>{{ __('System Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your system details') }}</small>
                        </div>
                        @php
                            $systemSettingsRouteBaseName       = ViewsConstants::SYS . '.settings';
                            $systemSettingsKebabRouteName      = Str::kebab($systemSettingsRouteBaseName);
                            $systemSettingsResolvedName        = Route::has($systemSettingsRouteBaseName)
                                ? $systemSettingsRouteBaseName
                                : (Route::has($systemSettingsKebabRouteName) ? $systemSettingsKebabRouteName : null);
                            $systemSettingsRouteArray          = $systemSettingsResolvedName ? [$systemSettingsResolvedName] : ['#'];
                            $systemSettingsUrl                 = $systemSettingsResolvedName ? route($systemSettingsResolvedName) : '#';
                            $systemSettingsGuardMsg            = Utility::fetchLinkMessage($lang, ViewsConstants::SYS, 'system_settings_route_unavailable') ?? 'System settings route is unavailable. Please contact technical support or your domain administrator.';
                            $systemSettingsFormId              = 'system-settings-form';
                        @endphp
                        {!! Form::model($setting, [
                            'route'          => $systemSettingsRouteArray,
                            'method'         => 'post',
                            'id'             => $systemSettingsFormId,
                            'data-url'       => $systemSettingsUrl,
                            'data-guard-msg' => $systemSettingsGuardMsg
                        ]) !!}
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        const form = document.getElementById('{{ $systemSettingsFormId }}');
                                        if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                        form.setAttribute('data-listener-active', 'true');
                                        form.addEventListener('submit', e => {
                                            try {
                                                const url = form.getAttribute('data-url') || '#';
                                                const action = form.getAttribute('action') || '#';
                                                if (url !== '#' || action !== '#') return;
                                                e.preventDefault();
                                                const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
                                                    document.body.appendChild(container);
                                                }
                                                if (hasBootstrap) {
                                                    const toast = document.createElement('div');
                                                    toast.className = 'toast';
                                                    toast.setAttribute('role','alert');
                                                    toast.setAttribute('aria-live','assertive');
                                                    toast.setAttribute('aria-atomic','true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = msg;
                                                    toast.appendChild(body);
                                                    container.appendChild(toast);
                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                } else {
                                                    alert(msg);
                                                }
                                                form.setAttribute('data-failed-route', 'true');
                                            } catch (err) {}
                                        });
                                    })();
                                </script>
                            @endpush
                            <div class="card-body">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        {{ Form::label('site_currency', __('Currency *'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('site_currency', $setting['site_currency'], ['class' => VC::FM_CT.' font-style', 'required', 'placeholder' => __('Enter Currency')]) }}
                                        <small>
                                            {{ __('Note: Add currency code as per three-letter ISO code.') }}<br>
                                            <a href="https://stripe.com/docs/currencies" target="_blank">{{ __('You can find out how to do that here.') }}</a>
                                        </small>
                                        <br>
                                        @error('site_currency')
                                            <span class="invalid-site_currency" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        {{ Form::label('site_currency_symbol', __('Currency Symbol *'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('site_currency_symbol', null, ['class' => VC::FM_CT]) }}
                                        @error('site_currency_symbol')
                                            <span class="invalid-site_currency_symbol" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        <label class="{{ VC::FM_LB }}" for="example3cols3Input">{{ __('Currency Symbol Position') }}</label>
                                        <div class="{{ VC::RW }} ms-1">
                                            <div class="form-check {{ VC::CM6 }}">
                                                <input class="form-check-input"
                                                    type="radio"
                                                    name="site_currency_symbol_position"
                                                    value="pre"
                                                    id="flexCheckDefault"
                                                    @if (@$setting['site_currency_symbol_position'] == 'pre') checked @endif>
                                                <label class="form-check-label" for="flexCheckDefault">{{ __('Pre') }}</label>
                                            </div>
                                            <div class="form-check {{ VC::CM6 }}">
                                                <input class="form-check-input"
                                                    type="radio"
                                                    name="site_currency_symbol_position"
                                                    value="post"
                                                    id="flexCheckChecked"
                                                    @if (@$setting['site_currency_symbol_position'] == 'post') checked @endif>
                                                <label class="form-check-label" for="flexCheckChecked">{{ __('Post') }}</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        {{ Form::label('decimal_number', __('Decimal Number Format'), ['class' => VC::FM_LB]) }}
                                        {{ Form::number('decimal_number', null, ['class' => VC::FM_CT]) }}
                                        @error('decimal_number')
                                            <span class="invalid-decimal_number" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        <label for="site_date_format" class="{{ VC::FM_LB }}">{{ __('Date Format') }}</label>
                                        <select name="site_date_format" id="site_date_format" class="{{ VC::FM_CT }} selectric">
                                            <option value="M j, Y" @if (@$setting['site_date_format'] == 'M j, Y') selected @endif>Jan 1,2015</option>
                                            <option value="d-m-Y" @if (@$setting['site_date_format'] == 'd-m-Y') selected @endif>dd-mm-yyyy</option>
                                            <option value="m-d-Y" @if (@$setting['site_date_format'] == 'm-d-Y') selected @endif>mm-dd-yyyy</option>
                                            <option value="Y-m-d" @if (@$setting['site_date_format'] == 'Y-m-d') selected @endif>yyyy-mm-dd</option>
                                        </select>
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        <label for="site_time_format" class="{{ VC::FM_LB }}">{{ __('Time Format') }}</label>
                                        <select name="site_time_format" id="site_time_format" class="{{ VC::FM_CT }} selectric">
                                            <option value="g:i A" @if (@$setting['site_time_format'] == 'g:i A') selected @endif>10:30 PM</option>
                                            <option value="g:i a" @if (@$setting['site_time_format'] == 'g:i a') selected @endif>10:30 pm</option>
                                            <option value="H:i"   @if (@$setting['site_time_format'] == 'H:i')   selected @endif>22:30</option>
                                        </select>
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        {{ Form::label('customer_prefix', __('Customer Prefix'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('customer_prefix', null, ['class' => VC::FM_CT]) }}
                                        @error('customer_prefix')
                                            <span class="invalid-customer_prefix" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        {{ Form::label('vendor_prefix', __('Vendor Prefix'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('vendor_prefix', null, ['class' => VC::FM_CT]) }}
                                        @error('vendor_prefix')
                                            <span class="invalid-vendor_prefix" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        {{ Form::label('proposal_prefix', __('Proposal Prefix'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('proposal_prefix', null, ['class' => VC::FM_CT]) }}
                                        @error('proposal_prefix')
                                            <span class="invalid-proposal_prefix" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        {{ Form::label('invoice_prefix', __('Invoice Prefix'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('invoice_prefix', null, ['class' => VC::FM_CT]) }}
                                        @error('invoice_prefix')
                                            <span class="invalid-invoice_prefix" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        {{ Form::label('bill_prefix', __('Bill Prefix'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('bill_prefix', null, ['class' => VC::FM_CT]) }}
                                        @error('bill_prefix')
                                            <span class="invalid-bill_prefix" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        {{ Form::label('purchase_prefix', __('Purchase Prefix'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('purchase_prefix', null, ['class' => VC::FM_CT]) }}
                                        @error('purchase_prefix')
                                            <span class="invalid-purchase_prefix" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        {{ Form::label('pos_prefix', __('Pos Prefix'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('pos_prefix', null, ['class' => VC::FM_CT]) }}
                                        @error('pos_prefix')
                                            <span class="invalid-pos_prefix" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        {{ Form::label('journal_prefix', __('Journal Prefix'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('journal_prefix', null, ['class' => VC::FM_CT]) }}
                                        @error('journal_prefix')
                                            <span class="invalid-journal_prefix" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        {{ Form::label('expense_prefix', __('Expense Prefix'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('expense_prefix', null, ['class' => VC::FM_CT]) }}
                                        @error('expense_prefix')
                                            <span class="invalid-expense_prefix" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        {{ Form::label('shipping_display', __('Display Shipping in Proposal / Invoice / Bill'), ['class' => VC::FM_LB]) }}
                                        <div class="form-switch form-switch-left">
                                            <input type="checkbox"
                                                class="form-check-input {{ VC::MT3 }}"
                                                name="shipping_display"
                                                id="email_template_13"
                                                {{ $setting['shipping_display'] == 'on' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="email_template_13"></label>
                                        </div>
                                        @error('shipping_display')
                                            <span class="invalid-shipping_display" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                                        {{ Form::label('footer_title', __('Proposal/Invoice/Bill/Purchase/POS Footer Title'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('footer_title', null, ['class' => VC::FM_CT]) }}
                                        @error('footer_title')
                                            <span class="invalid-footer_title" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                                        {{ Form::label('footer_notes', __('Proposal/Invoice/Bill/Purchase/POS Footer Note'), ['class' => VC::FM_LB]) }}
                                        <textarea class="summernote-simple4 summernote-simple">{!! $setting['footer_notes'] !!}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <div class="{{ VC::FM_G }}">
                                    <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                    <div id="company-settings" class="card">
                        <div class="card-header">
                            <h5>{{ __('Company Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your company details') }}</small>
                        </div>
                        @php
                            $cpSettingsRouteBaseName         = ViewsConstants::CP . '.settings';
                            $cpSettingsKebabRouteName        = Str::kebab($cpSettingsRouteBaseName);
                            $cpSettingsResolvedName          = Route::has($cpSettingsRouteBaseName)
                                ? $cpSettingsRouteBaseName
                                : (Route::has($cpSettingsKebabRouteName) ? $cpSettingsKebabRouteName : null);
                            $cpSettingsRouteArray            = $cpSettingsResolvedName ? [$cpSettingsResolvedName] : ['#'];
                            $cpSettingsUrl                   = $cpSettingsResolvedName ? route($cpSettingsResolvedName) : '#';
                            $cpSettingsGuardMsg              = Utility::fetchLinkMessage($lang, ViewsConstants::CP, 'company_settings_route_unavailable') ?? 'Company settings route is unavailable. Please contact technical support or your domain administrator.';
                            $cpSettingsFormId                = 'cp-settings-form';
                        @endphp
                        {!! Form::model($setting, [
                            'route'          => $cpSettingsRouteArray,
                            'method'         => 'post',
                            'id'             => $cpSettingsFormId,
                            'data-url'       => $cpSettingsUrl,
                            'data-guard-msg' => $cpSettingsGuardMsg
                        ]) !!}
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        const form = document.getElementById('{{ $cpSettingsFormId }}');
                                        if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                        form.setAttribute('data-listener-active', 'true');
                                        form.addEventListener('submit', e => {
                                            try {
                                                const url = form.getAttribute('data-url') || '#';
                                                const action = form.getAttribute('action') || '#';
                                                if (url !== '#' || action !== '#') return;
                                                e.preventDefault();
                                                const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
                                                    document.body.appendChild(container);
                                                }
                                                if (hasBootstrap) {
                                                    const toast = document.createElement('div');
                                                    toast.className = 'toast';
                                                    toast.setAttribute('role','alert');
                                                    toast.setAttribute('aria-live','assertive');
                                                    toast.setAttribute('aria-atomic','true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = msg;
                                                    toast.appendChild(body);
                                                    container.appendChild(toast);
                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                } else {
                                                    alert(msg);
                                                }
                                                form.setAttribute('data-failed-route', 'true');
                                            } catch (err) {}
                                        });
                                    })();
                                </script>
                            @endpush
                            <div class="card-body">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::FM_G }} col-md-6">
                                        {{ Form::label('company_name *', __('Company Name *'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('company_name', null, ['class' => VC::FM_CT . ' font-style']) }}
                                        @error('company_name')
                                            <span class="invalid-company_name" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} col-md-6">
                                        {{ Form::label('company_address', __('Address'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('company_address', null, ['class' => VC::FM_CT . ' font-style']) }}
                                        @error('company_address')
                                            <span class="invalid-company_address" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} col-md-6">
                                        {{ Form::label('company_city', __('City'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('company_city', null, ['class' => VC::FM_CT . ' font-style']) }}
                                        @error('company_city')
                                            <span class="invalid-company_city" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} col-md-6">
                                        {{ Form::label('company_state', __('State'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('company_state', null, ['class' => VC::FM_CT . ' font-style']) }}
                                        @error('company_state')
                                            <span class="invalid-company_state" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} col-md-6">
                                        {{ Form::label('company_zipcode', __('Zip/Post Code'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('company_zipcode', null, ['class' => VC::FM_CT]) }}
                                        @error('company_zipcode')
                                            <span class="invalid-company_zipcode" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} col-md-6">
                                        {{ Form::label('company_country', __('Country'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('company_country', null, ['class' => VC::FM_CT . ' font-style']) }}
                                        @error('company_country')
                                            <span class="invalid-company_country" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} col-md-6">
                                        {{ Form::label('company_telephone', __('Telephone'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('company_telephone', null, ['class' => VC::FM_CT]) }}
                                        @error('company_telephone')
                                            <span class="invalid-company_telephone" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} col-md-6">
                                        {{ Form::label('registration_number', __('Company Registration Number *'), ['class' => VC::FM_LB]) }}
                                        {{ Form::text('registration_number', null, ['class' => VC::FM_CT]) }}
                                        @error('registration_number')
                                            <span class="invalid-registration_number" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} col-md-4">
                                        {{ Form::label('company_start_time', __('Company Start Time *'), ['class' => VC::FM_LB]) }}
                                        {{ Form::time('company_start_time', null, ['class' => VC::FM_CT]) }}
                                        @error('company_start_time')
                                            <span class="invalid-company_start_time" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} col-md-4">
                                        {{ Form::label('company_end_time', __('Company End Time *'), ['class' => VC::FM_LB]) }}
                                        {{ Form::time('company_end_time', null, ['class' => VC::FM_CT]) }}
                                        @error('company_end_time')
                                            <span class="invalid-company_end_time" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} col-md-4">
                                        <label class="{{ VC::FM_LB }}" for="ip_restrict">{{ __('Ip Restrict') }}</label>
                                        <div class="custom-control custom-switch {{ VC::MT2 }}">
                                            <input type="checkbox"
                                                class="form-check-input"
                                                data-toggle="switchbutton"
                                                data-onstyle="primary"
                                                name="ip_restrict"
                                                id="ip_restrict"
                                                {{ $setting['ip_restrict'] == 'on' ? 'checked' : '' }}>
                                        </div>
                                    </div>

                                    <div class="{{ VC::FM_G }} col-md-12 {{ VC::MT2 }}">
                                        {{ Form::label('timezone', __('Timezone'), ['class' => VC::FM_LB]) }}
                                        <select name="timezone" class="{{ VC::FM_CT }} custom-select" id="timezone">
                                            <option value="">{{ __('Select Timezone') }}</option>
                                            @foreach ($timezones as $k => $timezone)
                                                <option value="{{ $k }}" {{ $setting['timezone'] == $k ? 'selected' : '' }}>{{ $timezone }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="{{ VC::FM_G }} col-md-6">
                                        <div class="{{ VC::RW }} {{ VC::MT4 }}">
                                            <div class="col-md-6">
                                                <label for="vat_gst_number_switch">{{ __('Tax Number') }}</label>
                                                <div class="form-check form-switch custom-switch-v1 float-end">
                                                    <input type="checkbox"
                                                        name="vat_gst_number_switch"
                                                        class="form-check-input input-primary pointer"
                                                        value="on"
                                                        id="vat_gst_number_switch"
                                                        {{ $setting['vat_gst_number_switch'] == 'on' ? ' checked ' : '' }}>
                                                    <label class="form-check-label" for="vat_gst_number_switch"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="{{ VC::FM_G }} col-md-6 tax_type_div {{ $setting['vat_gst_number_switch'] != 'on' ? ' d-none ' : '' }}">
                                        <div class="{{ VC::RW }}">
                                            <div class="col-md-6">
                                                <div class="{{ VC::FM_CHK_IL }} {{ VC::FM_GB3 }}">
                                                    <input type="radio" id="customRadio8" name="tax_type" value="VAT" class="form-check-input"
                                                        {{ $setting['tax_type'] == 'VAT' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="customRadio8">{{ __('VAT Number') }}</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="{{ VC::FM_CHK_IL }} {{ VC::FM_GB3 }}">
                                                    <input type="radio" id="customRadio7" name="tax_type" value="GST" class="form-check-input"
                                                        {{ $setting['tax_type'] == 'GST' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="customRadio7">{{ __('GST Number') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        {{ Form::text('vat_number', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter VAT / GST Number')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <div class="{{ VC::FM_G }}">
                                    <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                    <div id="email-settings" class="card">
                        <div class="card-header">
                            <h5>{{ __('Email Settings') }}</h5>
                        </div>
                        @php
                            $cpEmailSettingsRouteBaseName              = ViewsConstants::CP . '.email.settings';
                            $cpEmailSettingsKebabRouteName             = Str::kebab($cpEmailSettingsRouteBaseName);
                            $cpEmailSettingsResolvedName               = Route::has($cpEmailSettingsRouteBaseName)
                                ? $cpEmailSettingsRouteBaseName
                                : (Route::has($cpEmailSettingsKebabRouteName) ? $cpEmailSettingsKebabRouteName : null);
                            $cpEmailSettingsRouteArray                 = $cpEmailSettingsResolvedName ? [$cpEmailSettingsResolvedName] : ['#'];
                            $cpEmailSettingsUrl                        = $cpEmailSettingsResolvedName ? route($cpEmailSettingsResolvedName) : '#';
                            $cpEmailSettingsGuardMsg                   = Utility::fetchLinkMessage($lang, ViewsConstants::CP, 'company_email_settings_route_unavailable') ?? 'Company email settings route is unavailable. Please contact technical support or your domain administrator.';
                            $cpEmailSettingsFormId                     = 'cp-email-settings-form';
                        @endphp
                        {!! Form::model($setting, [
                            'route'          => $cpEmailSettingsRouteArray,
                            'method'         => 'post',
                            'id'             => $cpEmailSettingsFormId,
                            'data-url'       => $cpEmailSettingsUrl,
                            'data-guard-msg' => $cpEmailSettingsGuardMsg
                        ]) !!}
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        const form = document.getElementById('{{ $cpEmailSettingsFormId }}');
                                        if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                        form.setAttribute('data-listener-active', 'true');
                                        form.addEventListener('submit', e => {
                                            try {
                                                const url = form.getAttribute('data-url') || '#';
                                                const action = form.getAttribute('action') || '#';
                                                if (url !== '#' || action !== '#') return;
                                                e.preventDefault();
                                                const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
                                                    document.body.appendChild(container);
                                                }
                                                if (hasBootstrap) {
                                                    const toast = document.createElement('div');
                                                    toast.className = 'toast';
                                                    toast.setAttribute('role','alert');
                                                    toast.setAttribute('aria-live','assertive');
                                                    toast.setAttribute('aria-atomic','true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = msg;
                                                    toast.appendChild(body);
                                                    container.appendChild(toast);
                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                } else {
                                                    alert(msg);
                                                }
                                                form.setAttribute('data-failed-route', 'true');
                                            } catch (err) {}
                                        });
                                    })();
                                </script>
                            @endpush
                            <div class="card-body">
                                @csrf
                                <div class="{{ VC::RW }}">
                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_driver', __('Mail Driver'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('mail_driver', old('mail_driver', $emailSetting['mail_driver'] ?? ''), ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail Driver')]) }}
                                            @error('mail_driver')
                                                <span class="invalid-mail_driver" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_host', __('Mail Host'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('mail_host', old('mail_host', $emailSetting['mail_host'] ?? ''), ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail Host')]) }}
                                            @error('mail_host')
                                                <span class="invalid-mail_host" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_port', __('Mail Port'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('mail_port', old('mail_port', $emailSetting['mail_port'] ?? ''), ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail Port')]) }}
                                            @error('mail_port')
                                                <span class="invalid-mail_port" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::RW }}">
                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_username', __('Mail Username'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('mail_username', old('mail_username', $emailSetting['mail_username'] ?? ''), ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail Username')]) }}
                                            @error('mail_username')
                                                <span class="invalid-mail_username" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_password', __('Mail Password'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('mail_password', old('mail_password', $emailSetting['mail_password'] ?? ''), ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail Password')]) }}
                                            @error('mail_password')
                                                <span class="invalid-mail_password" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_encryption', __('Mail Encryption'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('mail_encryption', old('mail_encryption', $emailSetting['mail_encryption'] ?? ''), ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail Encryption')]) }}
                                            @error('mail_encryption')
                                                <span class="invalid-mail_encryption" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::RW }}">
                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_from_address', __('Mail From Address'), ['class' => VC::FM_LB]) }}
                                            {{ Form::email('mail_from_address', old('mail_from_address', $emailSetting['mail_from_address'] ?? ''), ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail From Address')]) }}
                                            @error('mail_from_address')
                                                <span class="invalid-mail_from_address" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('mail_from_name', __('Mail From Name'), ['class' => VC::FM_LB]) }}
                                            {{ Form::text('mail_from_name', old('mail_from_name', $emailSetting['mail_from_name'] ?? ''), ['class' => VC::FM_CT, 'placeholder' => __('Enter Mail From Name')]) }}
                                            @error('mail_from_name')
                                                <span class="invalid-mail_from_name" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::RW }}">
                                <div class="card-footer {{ VC::DFL }} {{ VC::JCE }}">
                                    <div class="{{ VC::FM_G }} me-2">
                                        @php
                                            $sendTestMailBaseName          = ViewsConstants::TT . '.mail';
                                            $sendTestMailKebabName         = Str::kebab($sendTestMailBaseName);
                                            $sendTestMailResolvedName      = Route::has($sendTestMailBaseName)
                                                ? $sendTestMailBaseName
                                                : (Route::has($sendTestMailKebabName) ? $sendTestMailKebabName : null);
                                            $sendTestMailUrl               = $sendTestMailResolvedName ? route($sendTestMailResolvedName) : '#';
                                            $sendTestMailGuardMsg          = Utility::fetchLinkMessage($lang, ViewsConstants::TT, 'send_test_mail_route_unavailable''send_test_mail_route_unavailable')
                                                ?? 'Send test mail route is unavailable. Please contact technical support or your domain administrator.';
                                            $sendTestMailBtnId             = 'send-test-mail-btn';
                                        @endphp
                                        <a
                                            id="{{ $sendTestMailBtnId }}"
                                            href="{{ $sendTestMailUrl }}"
                                            data-url="{{ $sendTestMailUrl }}"
                                            data-guard-msg="{{ $sendTestMailGuardMsg }}"
                                            data-title="{{ __('Send Test Mail') }}"
                                            class="{{ VC::BT_PRM }} send_email"
                                        >
                                            {{ __('Send Test Mail') }}
                                        </a>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    const btn = document.getElementById('{{ $sendTestMailBtnId }}');
                                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                    btn.setAttribute('data-listener-active', 'true');
                                                    btn.addEventListener('click', e => {
                                                        try {
                                                            const url = btn.getAttribute('data-url') || '#';
                                                            if (url !== '#') return;
                                                            e.preventDefault();
                                                            const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (hasBootstrap) {
                                                                const toast = document.createElement('div');
                                                                toast.className = 'toast';
                                                                toast.setAttribute('role','alert');
                                                                toast.setAttribute('aria-live','assertive');
                                                                toast.setAttribute('aria-atomic','true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toast.appendChild(body);
                                                                container.appendChild(toast);
                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            btn.setAttribute('data-failed-route', 'true');
                                                        } catch (err) {}
                                                    });
                                                })();
                                            </script>
                                        @endpush
                                    </div>
                                    <div class="{{ VC::FM_G }}">
                                        <input class="{{ VC::BT_PRM }}" type="submit" value="{{ __('Save Changes') }}">
                                    </div>
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                    <div id="tracker-settings" class="card">
                        <div class="card-header">
                            <h5>{{ __('Time Tracker Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your Time Tracker settings') }}</small>
                        </div>
                        @php
                            $timeTrackersSettingsBaseName     = ViewsConstants::TMT . '.settings';
                            $timeTrackersSettingsKebabName    = Str::kebab($timeTrackersSettingsBaseName);
                            $timeTrackersSettingsResolvedName = Route::has($timeTrackersSettingsBaseName)
                                ? $timeTrackersSettingsBaseName
                                : (Route::has($timeTrackersSettingsKebabName) ? $timeTrackersSettingsKebabName : null);
                            $timeTrackersSettingsRouteArray   = $timeTrackersSettingsResolvedName ? [$timeTrackersSettingsResolvedName] : ['#'];
                            $timeTrackersSettingsUrl          = $timeTrackersSettingsResolvedName ? route($timeTrackersSettingsResolvedName) : '#';
                            $timeTrackersSettingsGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::TMT, 'time_trackers_settings_route_unavailable') ?? 'Time trackers settings route is unavailable. Please contact technical support or your domain administrator.';
                            $timeTrackersSettingsFormId       = 'time-trackers-settings-form';
                        @endphp
                        {!! Form::model($setting, [
                            'route'          => $timeTrackersSettingsRouteArray,
                            'method'         => 'post',
                            'id'             => $timeTrackersSettingsFormId,
                            'data-url'       => $timeTrackersSettingsUrl,
                            'data-guard-msg' => $timeTrackersSettingsGuardMsg
                        ]) !!}
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    const form = document.getElementById('{{ $timeTrackersSettingsFormId }}');
                                    if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                    form.setAttribute('data-listener-active', 'true');
                                    form.addEventListener('submit', e => {
                                        try {
                                            const url = form.getAttribute('data-url') || '#';
                                            const action = form.getAttribute('action') || '#';
                                            if (url !== '#' || action !== '#') return;
                                            e.preventDefault();
                                            const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                            let container = document.getElementById('toast-container');
                                            if (!container) {
                                                container = document.createElement('div');
                                                container.id = 'toast-container';
                                                document.body.appendChild(container);
                                            }
                                            if (hasBootstrap) {
                                                const toast = document.createElement('div');
                                                toast.className = 'toast';
                                                toast.setAttribute('role','alert');
                                                toast.setAttribute('aria-live','assertive');
                                                toast.setAttribute('aria-atomic','true');
                                                const body = document.createElement('div');
                                                body.className = 'toast-body';
                                                body.textContent = msg;
                                                toast.appendChild(body);
                                                container.appendChild(toast);
                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                            } else {
                                                alert(msg);
                                            }
                                            form.setAttribute('data-failed-route', 'true');
                                        } catch (err) {}
                                    });
                                })();
                            </script>
                        @endpush
                            <div class="card-body">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        <label class="{{ VC::FM_LB }}">{{ __('Application URL') }}</label> <br>
                                        <small>{{ __('Application URL to log into the app.') }}</small>
                                        {{ Form::text('apps_url', old('apps_url', URL::to('/')), [
                                            'class' => VC::FM_CT,
                                            'placeholder' => __('Application URL'),
                                            'readonly' => true,
                                        ]) }}
                                    </div>
                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        <label class="{{ VC::FM_LB }}">{{ __('Tracking Interval') }}</label> <br>
                                        <small>{{ __('Image Screenshot Take Interval time ( 1 = 1 min)') }}</small>
                                        {{ Form::number('interval_time', old('interval_time', $setting['interval_time'] ?? '10'), [
                                            'class' => VC::FM_CT,
                                            'placeholder' => __('Enter Tracking Interval Time'),
                                            'min' => 1,
                                            'step' => 1,
                                        ]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer {{ VC::DFL }} {{ VC::JCE }}">
                                <div class="{{ VC::FM_G }}">
                                    <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                    <div class="card" id="payment-settings">
                        <div class="card-header">
                            <h5>{{ 'Payment Settings' }}</h5>
                            <small
                                class="text-secondary font-weight-bold">{{ __('These details will be used to collect invoice payments. Each invoice will have a payment button based on the below configuration.') }}</small>
                        </div>
                        @php
                            $cpPaymentSettingsBaseRouteName              = ViewsConstants::CP . '.payment.settings';
                            $cpPaymentSettingsKebabRouteName             = Str::kebab($cpPaymentSettingsBaseRouteName);
                            $cpPaymentSettingsResolvedRouteName          = Route::has($cpPaymentSettingsBaseRouteName)
                                ? $cpPaymentSettingsBaseRouteName
                                : (Route::has($cpPaymentSettingsKebabRouteName) ? $cpPaymentSettingsKebabRouteName : null);
                            $cpPaymentSettingsRouteArray                 = $cpPaymentSettingsResolvedRouteName ? [$cpPaymentSettingsResolvedRouteName] : ['#'];
                            $cpPaymentSettingsUrl                        = $cpPaymentSettingsResolvedRouteName ? route($cpPaymentSettingsResolvedRouteName) : '#';
                            $cpPaymentSettingsGuardMsg                   = Utility::fetchLinkMessage($lang, ViewsConstants::CP, 'company_payment_settings_route_unavailable') ?? 'Company payment settings route is unavailable. Please contact technical support or your domain administrator.';
                            $cpPaymentSettingsFormId                     = 'cp-payment-settings-form';
                        @endphp
                        {!! Form::model($setting, [
                            'route'          => $cpPaymentSettingsRouteArray,
                            'method'         => 'POST',
                            'id'             => $cpPaymentSettingsFormId,
                            'data-url'       => $cpPaymentSettingsUrl,
                            'data-guard-msg' => $cpPaymentSettingsGuardMsg
                        ]) !!}
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    const form = document.getElementById('{{ $cpPaymentSettingsFormId }}');
                                    if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                    form.setAttribute('data-listener-active', 'true');
                                    form.addEventListener('submit', (e) => {
                                        try {
                                            const url = form.getAttribute('data-url') || '#';
                                            const action = form.getAttribute('action') || '#';
                                            if (url !== '#' || action !== '#') return;
                                            e.preventDefault();
                                            const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                            let container = document.getElementById('toast-container');
                                            if (!container) {
                                                container = document.createElement('div');
                                                container.id = 'toast-container';
                                                document.body.appendChild(container);
                                            }
                                            if (hasBootstrap) {
                                                const toast = document.createElement('div');
                                                toast.className = 'toast';
                                                toast.setAttribute('role','alert');
                                                toast.setAttribute('aria-live','assertive');
                                                toast.setAttribute('aria-atomic','true');
                                                const body = document.createElement('div');
                                                body.className = 'toast-body';
                                                body.textContent = msg;
                                                toast.appendChild(body);
                                                container.appendChild(toast);
                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                            } else {
                                                alert(msg);
                                            }
                                            form.setAttribute('data-failed-route', 'true');
                                        } catch (err) {}
                                    });
                                })();
                            </script>
                        @endpush
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="faq justify-content-center">
                                        <div class="row">
                                            <div class="col-12">
                                                @php
                                                    $gateways = [
                                                        [
                                                            'key'     => 'bank',
                                                            'title'   => __('Bank Transfer'),
                                                            'enabled' => 'is_bank_transfer_enabled',
                                                            'fields'  => [
                                                                [
                                                                    'name'  => 'bank_details',
                                                                    'type'  => 'textarea',
                                                                    'label' => __('Bank Details'),
                                                                    'rows'  => 4,
                                                                    'col'   => 'col-lg-12',
                                                                    'placeholder' => __('Enter Your Bank Details'),
                                                                    'hint'  => __('Example : Bank : bank name </br> Account Number : 0000 0000 </br>'),
                                                                ],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'stripe',
                                                            'title'   => __('Stripe'),
                                                            'enabled' => 'is_stripe_enabled',
                                                            'fields'  => [
                                                                ['name' => 'stripe_key',    'label' => __('Stripe Key'),    'type' => 'text',     'col' => 'col-lg-6'],
                                                                ['name' => 'stripe_secret', 'label' => __('Stripe Secret'), 'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'paypal',
                                                            'title'   => __('Paypal'),
                                                            'enabled' => 'is_paypal_enabled',
                                                            'radios'  => ['name' => 'paypal_mode', 'options' => ['sandbox' => __('Sandbox'), 'live' => __('Live')], 'default' => 'sandbox'],
                                                            'fields'  => [
                                                                ['name' => 'paypal_client_id',  'label' => __('Client ID'),  'type' => 'text',     'col' => 'col-lg-6'],
                                                                ['name' => 'paypal_secret_key', 'label' => __('Secret Key'), 'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'paystack',
                                                            'title'   => __('Paystack'),
                                                            'enabled' => 'is_paystack_enabled',
                                                            'fields'  => [
                                                                ['name' => 'paystack_public_key', 'label' => __('Public Key'), 'type' => 'text',     'col' => 'col-lg-6'],
                                                                ['name' => 'paystack_secret_key', 'label' => __('Secret Key'), 'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'flutterwave',
                                                            'title'   => __('Flutterwave'),
                                                            'enabled' => 'is_flutterwave_enabled',
                                                            'fields'  => [
                                                                ['name' => 'flutterwave_public_key', 'label' => __('Public Key'), 'type' => 'text',     'col' => 'col-lg-6'],
                                                                ['name' => 'flutterwave_secret_key', 'label' => __('Secret Key'), 'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'razorpay',
                                                            'title'   => __('Razorpay'),
                                                            'enabled' => 'is_razorpay_enabled',
                                                            'fields'  => [
                                                                ['name' => 'razorpay_public_key', 'label' => __('Public Key'), 'type' => 'text',     'col' => 'col-lg-6'],
                                                                ['name' => 'razorpay_secret_key', 'label' => __('Secret Key'), 'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'paytm',
                                                            'title'   => __('Paytm'),
                                                            'enabled' => 'is_paytm_enabled',
                                                            'radios'  => ['name' => 'paytm_mode', 'options' => ['local' => __('Local'), 'production' => __('Production')], 'default' => 'local'],
                                                            'fields'  => [
                                                                ['name' => 'paytm_merchant_id',   'label' => __('Merchant ID'),   'type' => 'text',     'col' => 'col-lg-4'],
                                                                ['name' => 'paytm_merchant_key',  'label' => __('Merchant Key'),  'type' => 'password', 'col' => 'col-lg-4'],
                                                                ['name' => 'paytm_industry_type', 'label' => __('Industry Type'), 'type' => 'text',     'col' => 'col-lg-4'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'mercado',
                                                            'title'   => __('Mercado Pago'),
                                                            'enabled' => 'is_mercado_enabled',
                                                            'radios'  => ['name' => 'mercado_mode', 'options' => ['sandbox' => __('Sandbox'), 'live' => __('Live')], 'default' => 'sandbox'],
                                                            'fields'  => [
                                                                ['name' => 'mercado_access_token', 'label' => __('Access Token'), 'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'mollie',
                                                            'title'   => __('Mollie'),
                                                            'enabled' => 'is_mollie_enabled',
                                                            'fields'  => [
                                                                ['name' => 'mollie_api_key',    'label' => __('Mollie API Key'),   'type' => 'password', 'col' => 'col-lg-6'],
                                                                ['name' => 'mollie_profile_id', 'label' => __('Mollie Profile ID'), 'type' => 'text',     'col' => 'col-lg-6'],
                                                                ['name' => 'mollie_partner_id', 'label' => __('Mollie Partner ID'), 'type' => 'text',     'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'skrill',
                                                            'title'   => __('Skrill'),
                                                            'enabled' => 'is_skrill_enabled',
                                                            'fields'  => [
                                                                ['name' => 'skrill_email', 'label' => __('Skrill Email'), 'type' => 'email', 'col' => 'col-lg-6', 'attrs' => ['autocomplete' => 'email', 'inputmode' => 'email']],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'coingate',
                                                            'title'   => __('CoinGate'),
                                                            'enabled' => 'is_coingate_enabled',
                                                            'radios'  => ['name' => 'coingate_mode', 'options' => ['sandbox' => __('Sandbox'), 'live' => __('Live')], 'default' => 'sandbox'],
                                                            'fields'  => [
                                                                ['name' => 'coingate_auth_token', 'label' => __('CoinGate Auth Token'), 'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'paymentwall',
                                                            'title'   => __('PaymentWall'),
                                                            'enabled' => 'is_paymentwall_enabled',
                                                            'fields'  => [
                                                                ['name' => 'paymentwall_public_key', 'label' => __('Public Key'),  'type' => 'text',     'col' => 'col-lg-6'],
                                                                ['name' => 'paymentwall_secret_key', 'label' => __('Private Key'), 'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'toyyibpay',
                                                            'title'   => __('Toyyibpay'),
                                                            'enabled' => 'is_toyyibpay_enabled',
                                                            'fields'  => [
                                                                ['name' => 'toyyibpay_category_code', 'label' => __('Category Key'), 'type' => 'text',     'col' => 'col-lg-6'],
                                                                ['name' => 'toyyibpay_secret_key',    'label' => __('Secret Key'),   'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'payfast',
                                                            'title'   => __('PayFast'),
                                                            'enabled' => 'is_payfast_enabled',
                                                            'radios'  => ['name' => 'payfast_mode', 'options' => ['sandbox' => __('Sandbox'), 'live' => __('Live')], 'default' => 'sandbox'],
                                                            'fields'  => [
                                                                ['name' => 'payfast_merchant_id',  'label' => __('Merchant ID'),     'type' => 'text',     'col' => 'col-lg-4'],
                                                                ['name' => 'payfast_merchant_key', 'label' => __('Merchant Key'),    'type' => 'password', 'col' => 'col-lg-4'],
                                                                ['name' => 'payfast_signature',    'label' => __('Salt Passphrase'), 'type' => 'password', 'col' => 'col-lg-4'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'iyzipay',
                                                            'title'   => __('Iyzipay'),
                                                            'enabled' => 'is_iyzipay_enabled',
                                                            'radios'  => ['name' => 'iyzipay_mode', 'options' => ['sandbox' => __('Sandbox'), 'live' => __('Live')], 'default' => 'sandbox'],
                                                            'fields'  => [
                                                                ['name' => 'iyzipay_public_key', 'label' => __('Public Key'), 'type' => 'text',     'col' => 'col-lg-6'],
                                                                ['name' => 'iyzipay_secret_key', 'label' => __('Secret Key'), 'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'sspay',
                                                            'title'   => __('SSpay'),
                                                            'enabled' => 'is_sspay_enabled',
                                                            'fields'  => [
                                                                ['name' => 'sspay_category_code', 'label' => __('Category Code'), 'type' => 'text',     'col' => 'col-lg-6'],
                                                                ['name' => 'sspay_secret_key',    'label' => __('Secret Key'),    'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'paytab',
                                                            'title'   => __('PayTab'),
                                                            'enabled' => 'is_paytab_enabled',
                                                            'fields'  => [
                                                                ['name' => 'paytab_profile_id', 'label' => __('Profile Id'), 'type' => 'text',     'col' => 'col-lg-6'],
                                                                ['name' => 'paytab_server_key', 'label' => __('Server Key'),  'type' => 'password', 'col' => 'col-lg-6'],
                                                                ['name' => 'paytab_region',     'label' => __('Region'),      'type' => 'text',     'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'benefit',
                                                            'title'   => __('Benefit'),
                                                            'enabled' => 'is_benefit_enabled',
                                                            'fields'  => [
                                                                ['name' => 'benefit_api_key',    'label' => __('Benefit Key'),        'type' => 'text',     'col' => 'col-lg-6'],
                                                                ['name' => 'benefit_secret_key', 'label' => __('Benefit Secret Key'), 'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'cashfree',
                                                            'title'   => __('Cashfree'),
                                                            'enabled' => 'is_cashfree_enabled',
                                                            'fields'  => [
                                                                ['name' => 'cashfree_api_key',    'label' => __('Cashfree Key'),        'type' => 'text',     'col' => 'col-lg-6'],
                                                                ['name' => 'cashfree_secret_key', 'label' => __('Cashfree Secret Key'), 'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'aamarpay',
                                                            'title'   => __('Aamarpay'),
                                                            'enabled' => 'is_aamarpay_enabled',
                                                            'fields'  => [
                                                                ['name' => 'aamarpay_store_id',      'label' => __('Store Id'),       'type' => 'text',     'col' => 'col-lg-6'],
                                                                ['name' => 'aamarpay_signature_key', 'label' => __('Signature Key'),  'type' => 'password', 'col' => 'col-lg-6'],
                                                                ['name' => 'aamarpay_description',   'label' => __('Description'),    'type' => 'text',     'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'paytr',
                                                            'title'   => __('PayTR'),
                                                            'enabled' => 'is_paytr_enabled',
                                                            'fields'  => [
                                                                ['name' => 'paytr_merchant_id',   'label' => __('Merchant Id'),   'type' => 'text',     'col' => 'col-lg-4'],
                                                                ['name' => 'paytr_merchant_key',  'label' => __('Merchant Key'),  'type' => 'password', 'col' => 'col-lg-4'],
                                                                ['name' => 'paytr_merchant_salt', 'label' => __('Merchant Salt'), 'type' => 'password', 'col' => 'col-lg-4'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'yookassa',
                                                            'title'   => __('Yookassa'),
                                                            'enabled' => 'is_yookassa_enabled',
                                                            'fields'  => [
                                                                ['name' => 'yookassa_shop_id', 'label' => __('Shop ID Key'), 'type' => 'text',     'col' => 'col-lg-6'],
                                                                ['name' => 'yookassa_secret',  'label' => __('Secret Key'),  'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'midtrans',
                                                            'title'   => __('Midtrans'),
                                                            'enabled' => 'is_midtrans_enabled',
                                                            'fields'  => [
                                                                ['name' => 'midtrans_secret', 'label' => __('Secret Key'), 'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                        [
                                                            'key'     => 'xendit',
                                                            'title'   => __('Xendit'),
                                                            'enabled' => 'is_xendit_enabled',
                                                            'fields'  => [
                                                                ['name' => 'xendit_api',   'label' => __('API Key'), 'type' => 'password', 'col' => 'col-lg-6'],
                                                                ['name' => 'xendit_token', 'label' => __('Token'),   'type' => 'password', 'col' => 'col-lg-6'],
                                                            ],
                                                        ],
                                                    ];
                                                @endphp
                                                <div class="accordion accordion-flush setting-accordion"
                                                    id="accordionExample">
                                                    @foreach ($gateways as $gw)
                                                        @php
                                                            $enabledKey = $gw['enabled'];
                                                            $isEnabled  = old($enabledKey, $company_payment_setting[$enabledKey] ?? 'off') === 'on';
                                                            $key        = $gw['key'];
                                                            $headingId  = "heading_{$key}";
                                                            $collapseId = "collapse_{$key}";
                                                            $switchId   = "switch_{$enabledKey}";
                                                        @endphp
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="{{ $headingId }}">
                                                                <button class="accordion-button {{ $isEnabled ? '' : 'collapsed' }}"
                                                                        type="button"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#{{ $collapseId }}"
                                                                        aria-expanded="{{ $isEnabled ? 'true' : 'false' }}"
                                                                        aria-controls="{{ $collapseId }}">
                                                                    <span class="{{ VC::DFL_AIC }}">{{ $gw['title'] }}</span>
                                                                    <div class="{{ VC::DFL_AIC }}">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="{{ $enabledKey }}" value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="{{ $switchId }}"
                                                                                name="{{ $enabledKey }}"
                                                                                @checked($isEnabled) />
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>

                                                            <div id="{{ $collapseId }}"
                                                                class="accordion-collapse collapse {{ $isEnabled ? 'show' : '' }}"
                                                                aria-labelledby="{{ $headingId }}"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    {{-- Optional radios (mode/environment) --}}
                                                                    @if(isset($gw['radios']))
                                                                        @php
                                                                            $rName   = $gw['radios']['name'];
                                                                            $rValue  = old($rName, $company_payment_setting[$rName] ?? ($gw['radios']['default'] ?? null));
                                                                            if ($rValue === '' && isset($gw['radios']['default'])) { $rValue = $gw['radios']['default']; }
                                                                            $options = $gw['radios']['options'] ?? [];
                                                                        @endphp
                                                                        <div class="{{ VC::C12 }} {{ VC::MB4 }}">
                                                                            <label class="{{ VC::FM_LB }}" for="{{ $rName }}">{{ Str::headline(str_replace('_',' ', $rName)) }}</label>
                                                                            <div class="{{ VC::DFL }}">
                                                                                @foreach($options as $val => $label)
                                                                                    <div class="me-2" style="margin-right: 15px;">
                                                                                        <div class="{{ VC::BD }} {{ VC::CD }} {{ VC::P4 }}">
                                                                                            <div class="form-check">
                                                                                                <label class="form-check-label text-dark me-2">
                                                                                                    <input type="radio"
                                                                                                        class="form-check-input"
                                                                                                        name="{{ $rName }}"
                                                                                                        value="{{ $val }}"
                                                                                                        {{ $rValue === $val ? 'checked' : '' }}>
                                                                                                    {{ $label }}
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                    @endif

                                                                    <div class="{{ VC::RW }} gy-4">
                                                                        @foreach($gw['fields'] as $f)
                                                                            @php
                                                                                $name  = $f['name'];
                                                                                $type  = $f['type'] ?? 'text';
                                                                                $label = $f['label'] ?? Str::headline(str_replace('_',' ', $name));
                                                                                $col   = $f['col'] ?? 'col-lg-6';
                                                                                $val   = old($name, $company_payment_setting[$name] ?? ($f['value'] ?? ''));
                                                                                $attrs = array_merge([
                                                                                    'class'       => VC::FM_CT,
                                                                                    'placeholder' => $f['placeholder'] ?? $label,
                                                                                ], $f['attrs'] ?? []);

                                                                                if ($type === 'password') {
                                                                                    // Never re-print secrets
                                                                                    $val = null;
                                                                                    $attrs['autocomplete'] = 'off';
                                                                                    $attrs['spellcheck']   = 'false';
                                                                                }
                                                                            @endphp

                                                                            <div class="{{ $col }}">
                                                                                <div class="input-edits">
                                                                                    <div class="{{ VC::FM_G }}">
                                                                                        {{ Form::label($name, $label, ['class' => VC::FM_LB]) }}

                                                                                        @switch($type)
                                                                                            @case('textarea')
                                                                                                {{ Form::textarea($name, $val, array_merge($attrs, ['rows' => $f['rows'] ?? 4])) }}
                                                                                                @break
                                                                                            @case('number')
                                                                                                {{ Form::number($name, $val, $attrs) }}
                                                                                                @break
                                                                                            @case('password')
                                                                                                {{ Form::password($name, $attrs) }}
                                                                                                @break
                                                                                            @case('email')
                                                                                                {{ Form::email($name, $val, $attrs) }}
                                                                                                @break
                                                                                            @default
                                                                                                {{ Form::text($name, $val, $attrs) }}
                                                                                        @endswitch

                                                                                        @isset($f['hint'])
                                                                                            <small class="{{ VC::TXS }}">{!! $f['hint'] !!}</small>
                                                                                        @endisset

                                                                                        @error($name)
                                                                                            <span class="invalid-feedback {{ VC::DBL }}">{{ $message }}</span>
                                                                                        @enderror
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    {{-- <DEPRECATED>
                                                                                                                                                                @php
                                                            $bankEnabled = old('is_bank_transfer_enabled', $company_payment_setting['is_bank_transfer_enabled'] ?? 'off') === 'on';
                                                        @endphp
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingBank">
                                                                <button class="accordion-button {{ $bankEnabled ? '' : 'collapsed' }}"
                                                                        type="button"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#collapseBank"
                                                                        aria-expanded="{{ $bankEnabled ? 'true' : 'false' }}"
                                                                        aria-controls="collapseBank">
                                                                    <span class="{{ VC::DFL_AIC }}">
                                                                        {{ __('Bank Transfer') }}
                                                                    </span>
                                                                    <div class="{{ VC::DFL_AIC }}">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_bank_transfer_enabled" value="off">
                                                                            <input
                                                                                type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1_is_bank_transfer_enabled"
                                                                                name="is_bank_transfer_enabled"
                                                                                @checked($bankEnabled)
                                                                            >
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>

                                                            <div id="collapseBank"
                                                                class="accordion-collapse collapse {{ $bankEnabled ? 'show' : '' }}"
                                                                aria-labelledby="headingBank"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row gy-4">
                                                                        <div class="col-lg-12">
                                                                            <div class="input-edits">
                                                                                <div class="{{ VC::FM_G }}">
                                                                                    {{ Form::label('bank_details', __('Bank Details'), ['class' => VC::FM_LB]) }}
                                                                                    {{ Form::textarea('bank_details', old('bank_details', $company_payment_setting['bank_details'] ?? ''), [
                                                                                        'class' => VC::FM_CT,
                                                                                        'placeholder' => __('Enter Your Bank Details'),
                                                                                        'rows' => 4,
                                                                                    ]) }}
                                                                                    <small class="{{ VC::TXS }}">
                                                                                        {!! __('Example : Bank : bank name </br> Account Number : 0000 0000 </br>') !!}
                                                                                    </small>
                                                                                    @error('bank_details')
                                                                                        <span class="invalid-feedback {{ VC::DBL }}">{{ $message }}</span>
                                                                                    @enderror
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingOne">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse" data-bs-target="#collapseOne"
                                                                    aria-expanded="false" aria-controls="collapseOne">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Stripe') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_stripe_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_stripe_enabled"
                                                                                name="is_stripe_enabled"
                                                                                {{ isset($company_payment_setting['is_stripe_enabled']) && $company_payment_setting['is_stripe_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseOne" class="accordion-collapse collapse"
                                                                aria-labelledby="headingOne"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row gy-4">
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    {{ Form::label('stripe_key', __('Stripe Key'), ['class' => 'col-form-label']) }}
                                                                                    {{ Form::text('stripe_key', isset($company_payment_setting['stripe_key']) ? $company_payment_setting['stripe_key'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Stripe Key')]) }}
                                                                                    @if ($errors->has('stripe_key'))
                                                                                        <span class="invalid-feedback d-block">
                                                                                            {{ $errors->first('stripe_key') }}
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    {{ Form::label('stripe_secret', __('Stripe Secret'), ['class' => 'col-form-label']) }}
                                                                                    {{ Form::text('stripe_secret', isset($company_payment_setting['stripe_secret']) ? $company_payment_setting['stripe_secret'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Stripe Secret')]) }}
                                                                                    @if ($errors->has('stripe_secret'))
                                                                                        <span class="invalid-feedback d-block">
                                                                                            {{ $errors->first('stripe_secret') }}
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Paypal -->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingTwo">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse" data-bs-target="#collapseTwo"
                                                                    aria-expanded="false" aria-controls="collapseTwo">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Paypal') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_paypal_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_paypal_enabled"
                                                                                name="is_paypal_enabled"
                                                                                {{ isset($company_payment_setting['is_paypal_enabled']) && $company_payment_setting['is_paypal_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseTwo" class="accordion-collapse collapse"
                                                                aria-labelledby="headingTwo"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="d-flex">
                                                                        <div class="mr-2" style="margin-right: 15px;">
                                                                            <div class="border card p-1">
                                                                                <div class="form-check">
                                                                                    <label
                                                                                        class="form-check-label text-dark me-2">
                                                                                        <input type="radio"
                                                                                            name="paypal_mode" value="sandbox"
                                                                                            class="form-check-input"
                                                                                            {{ (isset($company_payment_setting['paypal_mode']) && $company_payment_setting['paypal_mode'] == '') || (isset($company_payment_setting['paypal_mode']) && $company_payment_setting['paypal_mode'] == 'sandbox') ? 'checked="checked"' : '' }}>
                                                                                        {{ __('Sandbox') }}
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="mr-2" style="margin-right: 15px;">
                                                                            <div class="border card p-1">
                                                                                <div class="form-check">
                                                                                    <label
                                                                                        class="form-check-label text-dark me-2">
                                                                                        <input type="radio"
                                                                                            name="paypal_mode" value="live"
                                                                                            class="form-check-input"
                                                                                            {{ isset($company_payment_setting['paypal_mode']) && $company_payment_setting['paypal_mode'] == 'live' ? 'checked="checked"' : '' }}>
                                                                                        {{ __('Live') }}
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row gy-4">
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label class="col-form-label"
                                                                                        for="paypal_client_id">{{ __('Client ID') }}</label>
                                                                                    <input type="text"
                                                                                        name="paypal_client_id"
                                                                                        id="paypal_client_id"
                                                                                        class="form-control"
                                                                                        value="{{ !isset($company_payment_setting['paypal_client_id']) || is_null($company_payment_setting['paypal_client_id']) ? '' : $company_payment_setting['paypal_client_id'] }}"
                                                                                        placeholder="{{ __('Client ID') }}">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label class="col-form-label"
                                                                                        for="paypal_secret_key">{{ __('Secret Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="paypal_secret_key"
                                                                                        id="paypal_secret_key"
                                                                                        class="form-control"
                                                                                        value="{{ isset($company_payment_setting['paypal_secret_key']) ? $company_payment_setting['paypal_secret_key'] : '' }}"
                                                                                        placeholder="{{ __('Secret Key') }}">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Paystack -->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingThree">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse" data-bs-target="#collapseThree"
                                                                    aria-expanded="false" aria-controls="collapseThree">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Paystack') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_paystack_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_paystack_enabled"
                                                                                name="is_paystack_enabled"
                                                                                {{ isset($company_payment_setting['is_paystack_enabled']) && $company_payment_setting['is_paystack_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseThree" class="accordion-collapse collapse"
                                                                aria-labelledby="headingThree"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row gy-4">
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="paypal_client_id"
                                                                                        class="col-form-label">{{ __('Public Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="paystack_public_key"
                                                                                        id="paystack_public_key"
                                                                                        class="form-control"
                                                                                        value="{{ isset($company_payment_setting['paystack_public_key']) ? $company_payment_setting['paystack_public_key'] : '' }}"
                                                                                        placeholder="{{ __('Public Key') }}" />
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="paystack_secret_key"
                                                                                        class="col-form-label">{{ __('Secret Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="paystack_secret_key"
                                                                                        id="paystack_secret_key"
                                                                                        class="form-control"
                                                                                        value="{{ isset($company_payment_setting['paystack_secret_key']) ? $company_payment_setting['paystack_secret_key'] : '' }}"
                                                                                        placeholder="{{ __('Secret Key') }}" />
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Flutterwave -->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingFour">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse" data-bs-target="#collapseFour"
                                                                    aria-expanded="false" aria-controls="collapseFour">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Flutterwave') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden"
                                                                                name="is_flutterwave_enabled" value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_flutterwave_enabled"
                                                                                name="is_flutterwave_enabled"
                                                                                {{ isset($company_payment_setting['is_flutterwave_enabled']) && $company_payment_setting['is_flutterwave_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseFour" class="accordion-collapse collapse"
                                                                aria-labelledby="headingFour"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row gy-4">
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="paypal_client_id"
                                                                                        class="col-form-label">{{ __('Public Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="flutterwave_public_key"
                                                                                        id="flutterwave_public_key"
                                                                                        class="form-control"
                                                                                        value="{{ isset($company_payment_setting['flutterwave_public_key']) ? $company_payment_setting['flutterwave_public_key'] : '' }}"
                                                                                        placeholder="Public Key">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="paystack_secret_key"
                                                                                        class="col-form-label">{{ __('Secret Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="flutterwave_secret_key"
                                                                                        id="flutterwave_secret_key"
                                                                                        class="form-control"
                                                                                        value="{{ isset($company_payment_setting['flutterwave_secret_key']) ? $company_payment_setting['flutterwave_secret_key'] : '' }}"
                                                                                        placeholder="Secret Key">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Razorpay -->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingFive">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse" data-bs-target="#collapseFive"
                                                                    aria-expanded="false" aria-controls="collapseFive">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Razorpay') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_razorpay_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_razorpay_enabled"
                                                                                name="is_razorpay_enabled"
                                                                                {{ isset($company_payment_setting['is_razorpay_enabled']) && $company_payment_setting['is_razorpay_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseFive" class="accordion-collapse collapse"
                                                                aria-labelledby="headingFive"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row gy-4">
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="paypal_client_id"
                                                                                        class="col-form-label">{{ __('Public Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="razorpay_public_key"
                                                                                        id="razorpay_public_key"
                                                                                        class="form-control"
                                                                                        value="{{ !isset($company_payment_setting['razorpay_public_key']) || is_null($company_payment_setting['razorpay_public_key']) ? '' : $company_payment_setting['razorpay_public_key'] }}"
                                                                                        placeholder="Public Key">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="paystack_secret_key"
                                                                                        class="col-form-label">
                                                                                        {{ __('Secret Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="razorpay_secret_key"
                                                                                        id="razorpay_secret_key"
                                                                                        class="form-control"
                                                                                        value="{{ !isset($company_payment_setting['razorpay_secret_key']) || is_null($company_payment_setting['razorpay_secret_key']) ? '' : $company_payment_setting['razorpay_secret_key'] }}"
                                                                                        placeholder="Secret Key">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Paytm -->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingSix">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse" data-bs-target="#collapseSix"
                                                                    aria-expanded="false" aria-controls="collapseSix">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Paytm') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_paytm_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_paytm_enabled"
                                                                                name="is_paytm_enabled"
                                                                                {{ isset($company_payment_setting['is_paytm_enabled']) && $company_payment_setting['is_paytm_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseSix" class="accordion-collapse collapse"
                                                                aria-labelledby="headingSix"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="col-md-12 pb-4">
                                                                        <label class="paypal-label col-form-label"
                                                                            for="paypal_mode">{{ __('Paytm Environment') }}</label>
                                                                        <br>
                                                                        <div class="d-flex">
                                                                            <div class="mr-2" style="margin-right: 15px;">
                                                                                <div class="border card p-1">
                                                                                    <div class="form-check">
                                                                                        <label
                                                                                            class="form-check-label text-dark me-2">
                                                                                            <input type="radio"
                                                                                                name="paytm_mode"
                                                                                                value="local"
                                                                                                class="form-check-input"
                                                                                                {{ !isset($company_payment_setting['paytm_mode']) || $company_payment_setting['paytm_mode'] == '' || $company_payment_setting['paytm_mode'] == 'local' ? 'checked="checked"' : '' }}>
                                                                                            {{ __('Local') }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="mr-2">
                                                                                <div class="border card p-1">
                                                                                    <div class="form-check">
                                                                                        <label
                                                                                            class="form-check-label text-dark me-2">
                                                                                            <input type="radio"
                                                                                                name="paytm_mode"
                                                                                                value="production"
                                                                                                class="form-check-input"
                                                                                                {{ isset($company_payment_setting['paytm_mode']) && $company_payment_setting['paytm_mode'] == 'production' ? 'checked="checked"' : '' }}>
                                                                                            {{ __('Production') }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row gy-4">
                                                                        <div class="col-lg-4">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="paytm_public_key"
                                                                                        class="col-form-label">{{ __('Merchant ID') }}</label>
                                                                                    <input type="text"
                                                                                        name="paytm_merchant_id"
                                                                                        id="paytm_merchant_id"
                                                                                        class="form-control"
                                                                                        value="{{ isset($company_payment_setting['paytm_merchant_id']) ? $company_payment_setting['paytm_merchant_id'] : '' }}"
                                                                                        placeholder="{{ __('Merchant ID') }}" />
                                                                                    @if ($errors->has('paytm_merchant_id'))
                                                                                        <span class="invalid-feedback d-block">
                                                                                            {{ $errors->first('paytm_merchant_id') }}
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-4">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="paytm_secret_key"
                                                                                        class="col-form-label">{{ __('Merchant Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="paytm_merchant_key"
                                                                                        id="paytm_merchant_key"
                                                                                        class="form-control"
                                                                                        value="{{ isset($company_payment_setting['paytm_merchant_key']) ? $company_payment_setting['paytm_merchant_key'] : '' }}"
                                                                                        placeholder="{{ __('Merchant Key') }}" />
                                                                                    @if ($errors->has('paytm_merchant_key'))
                                                                                        <span class="invalid-feedback d-block">
                                                                                            {{ $errors->first('paytm_merchant_key') }}
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-4">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="paytm_industry_type"
                                                                                        class="col-form-label">{{ __('Industry Type') }}</label>
                                                                                    <input type="text"
                                                                                        name="paytm_industry_type"
                                                                                        id="paytm_industry_type"
                                                                                        class="form-control"
                                                                                        value="{{ isset($company_payment_setting['paytm_industry_type']) ? $company_payment_setting['paytm_industry_type'] : '' }}"
                                                                                        placeholder="{{ __('Industry Type') }}" />
                                                                                    @if ($errors->has('paytm_industry_type'))
                                                                                        <span class="invalid-feedback d-block">
                                                                                            {{ $errors->first('paytm_industry_type') }}
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Mercado Pago -->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingseven">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse" data-bs-target="#collapseseven"
                                                                    aria-expanded="false" aria-controls="collapseseven">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Mercado Pago') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_mercado_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_mercado_enabled"
                                                                                name="is_mercado_enabled"
                                                                                {{ isset($company_payment_setting['is_mercado_enabled']) && $company_payment_setting['is_mercado_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseseven" class="accordion-collapse collapse"
                                                                aria-labelledby="headingseven"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="col-md-12 pb-4">
                                                                        <label class="coingate-label col-form-label"
                                                                            for="mercado_mode">{{ __('Mercado Mode') }}</label>
                                                                        <br>
                                                                        <div class="d-flex">
                                                                            <div class="mr-2" style="margin-right: 15px;">
                                                                                <div class="border card p-1">
                                                                                    <div class="form-check">
                                                                                        <label
                                                                                            class="form-check-label text-dark me-2">
                                                                                            <input type="radio"
                                                                                                name="mercado_mode"
                                                                                                value="sandbox"
                                                                                                class="form-check-input"
                                                                                                {{ (isset($company_payment_setting['mercado_mode']) && $company_payment_setting['mercado_mode'] == '') || (isset($company_payment_setting['mercado_mode']) && $company_payment_setting['mercado_mode'] == 'sandbox') ? 'checked="checked"' : '' }}>
                                                                                            {{ __('Sandbox') }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="mr-2" style="margin-right: 15px;">
                                                                                <div class="border card p-1">
                                                                                    <div class="form-check">
                                                                                        <label
                                                                                            class="form-check-label text-dark me-2">
                                                                                            <input type="radio"
                                                                                                name="mercado_mode"
                                                                                                value="live"
                                                                                                class="form-check-input"
                                                                                                {{ isset($company_payment_setting['mercado_mode']) && $company_payment_setting['mercado_mode'] == 'live' ? 'checked="checked"' : '' }}>
                                                                                            {{ __('Live') }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row gy-4">
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="mercado_access_token"
                                                                                        class="col-form-label">{{ __('Access Token') }}</label>
                                                                                    <input type="text"
                                                                                        name="mercado_access_token"
                                                                                        id="mercado_access_token"
                                                                                        class="form-control"
                                                                                        value="{{ isset($company_payment_setting['mercado_access_token']) ? $company_payment_setting['mercado_access_token'] : '' }}"
                                                                                        placeholder="{{ __('Access Token') }}" />
                                                                                    @if ($errors->has('mercado_secret_key'))
                                                                                        <span
                                                                                            class="invalid-feedback d-block">
                                                                                            {{ $errors->first('mercado_access_token') }}
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Mollie -->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingeight">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseeight" aria-expanded="false"
                                                                    aria-controls="collapseeight">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Mollie') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_mollie_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_mollie_enabled"
                                                                                name="is_mollie_enabled"
                                                                                {{ isset($company_payment_setting['is_mollie_enabled']) && $company_payment_setting['is_mollie_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseeight" class="accordion-collapse collapse"
                                                                aria-labelledby="headingeight"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row gy-4">
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="mollie_api_key"
                                                                                        class="col-form-label">{{ __('Mollie Api Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="mollie_api_key"
                                                                                        id="mollie_api_key"
                                                                                        class="form-control"
                                                                                        value="{{ !isset($company_payment_setting['mollie_api_key']) || is_null($company_payment_setting['mollie_api_key']) ? '' : $company_payment_setting['mollie_api_key'] }}"
                                                                                        placeholder="Mollie Api Key">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="mollie_profile_id"
                                                                                        class="col-form-label">{{ __('Mollie Profile Id') }}</label>
                                                                                    <input type="text"
                                                                                        name="mollie_profile_id"
                                                                                        id="mollie_profile_id"
                                                                                        class="form-control"
                                                                                        value="{{ !isset($company_payment_setting['mollie_profile_id']) || is_null($company_payment_setting['mollie_profile_id']) ? '' : $company_payment_setting['mollie_profile_id'] }}"
                                                                                        placeholder="Mollie Profile Id">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="mollie_partner_id"
                                                                                        class="col-form-label">{{ __('Mollie Partner Id') }}</label>
                                                                                    <input type="text"
                                                                                        name="mollie_partner_id"
                                                                                        id="mollie_partner_id"
                                                                                        class="form-control"
                                                                                        value="{{ !isset($company_payment_setting['mollie_partner_id']) || is_null($company_payment_setting['mollie_partner_id']) ? '' : $company_payment_setting['mollie_partner_id'] }}"
                                                                                        placeholder="Mollie Partner Id">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Skrill -->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingnine">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse" data-bs-target="#collapsenine"
                                                                    aria-expanded="false" aria-controls="collapsenine">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Skrill') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_skrill_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_skrill_enabled"
                                                                                name="is_skrill_enabled"
                                                                                {{ isset($company_payment_setting['is_skrill_enabled']) && $company_payment_setting['is_skrill_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapsenine" class="accordion-collapse collapse"
                                                                aria-labelledby="headingnine"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row gy-4">
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="mollie_api_key"
                                                                                        class="col-form-label">{{ __('Skrill Email') }}</label>
                                                                                    <input type="email"
                                                                                        name="skrill_email"
                                                                                        id="skrill_email"
                                                                                        class="form-control"
                                                                                        value="{{ isset($company_payment_setting['skrill_email']) ? $company_payment_setting['skrill_email'] : '' }}"
                                                                                        placeholder="{{ __('Enter Skrill Email') }}" />
                                                                                    @if ($errors->has('skrill_email'))
                                                                                        <span
                                                                                            class="invalid-feedback d-block">
                                                                                            {{ $errors->first('skrill_email') }}
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- CoinGate -->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingten">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse" data-bs-target="#collapseten"
                                                                    aria-expanded="false" aria-controls="collapseten">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('CoinGate') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_coingate_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_coingate_enabled"
                                                                                name="is_coingate_enabled"
                                                                                {{ isset($company_payment_setting['is_coingate_enabled']) && $company_payment_setting['is_coingate_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseten" class="accordion-collapse collapse"
                                                                aria-labelledby="headingten"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="col-md-12 pb-4">
                                                                        <label class="col-form-label"
                                                                            for="coingate_mode">{{ __('CoinGate Mode') }}</label>
                                                                        <br>
                                                                        <div class="d-flex">
                                                                            <div class="mr-2" style="margin-right: 15px;">
                                                                                <div class="border card p-1">
                                                                                    <div class="form-check">
                                                                                        <label
                                                                                            class="form-check-label text-dark me-2">
                                                                                            <input type="radio"
                                                                                                name="coingate_mode"
                                                                                                value="sandbox"
                                                                                                class="form-check-input"
                                                                                                {{ !isset($company_payment_setting['coingate_mode']) || $company_payment_setting['coingate_mode'] == '' || $company_payment_setting['coingate_mode'] == 'sandbox' ? 'checked="checked"' : '' }}>
                                                                                            {{ __('Sandbox') }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="mr-2" style="margin-right: 15px;">
                                                                                <div class="border card p-1">
                                                                                    <div class="form-check">
                                                                                        <label
                                                                                            class="form-check-label text-dark me-2">
                                                                                            <input type="radio"
                                                                                                name="coingate_mode"
                                                                                                value="live"
                                                                                                class="form-check-input"
                                                                                                {{ isset($company_payment_setting['coingate_mode']) && $company_payment_setting['coingate_mode'] == 'live' ? 'checked="checked"' : '' }}>
                                                                                            {{ __('Live') }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row gy-4">
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="coingate_auth_token"
                                                                                        class="col-form-label">{{ __('CoinGate Auth Token') }}</label>
                                                                                    <input type="text"
                                                                                        name="coingate_auth_token"
                                                                                        id="coingate_auth_token"
                                                                                        class="form-control"
                                                                                        value="{{ !isset($company_payment_setting['coingate_auth_token']) || is_null($company_payment_setting['coingate_auth_token']) ? '' : $company_payment_setting['coingate_auth_token'] }}"
                                                                                        placeholder="CoinGate Auth Token">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- PaymentWall -->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingeleven">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseeleven" aria-expanded="false"
                                                                    aria-controls="collapseeleven">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('PaymentWall') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden"
                                                                                name="is_paymentwall_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_paymentwall_enabled"
                                                                                name="is_paymentwall_enabled"
                                                                                {{ isset($company_payment_setting['is_paymentwall_enabled']) && $company_payment_setting['is_paymentwall_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseeleven" class="accordion-collapse collapse"
                                                                aria-labelledby="headingeleven"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row gy-4">
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="paymentwall_public_key"
                                                                                        class="col-form-label">{{ __('Public Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="paymentwall_public_key"
                                                                                        id="paymentwall_public_key"
                                                                                        class="form-control"
                                                                                        value="{{ !isset($company_payment_setting['paymentwall_public_key']) || is_null($company_payment_setting['paymentwall_public_key']) ? '' : $company_payment_setting['paymentwall_public_key'] }}"
                                                                                        placeholder="{{ __('Public Key') }}">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="paymentwall_secret_key"
                                                                                        class="col-form-label">{{ __('Private Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="paymentwall_secret_key"
                                                                                        id="paymentwall_secret_key"
                                                                                        class="form-control"
                                                                                        value="{{ !isset($company_payment_setting['paymentwall_secret_key']) || is_null($company_payment_setting['paymentwall_secret_key']) ? '' : $company_payment_setting['paymentwall_secret_key'] }}"
                                                                                        placeholder="{{ __('Private Key') }}">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Toyyibpay -->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingtwelve">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapsetwelve" aria-expanded="false"
                                                                    aria-controls="collapsetwelve">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Toyyibpay') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden"
                                                                                name="is_toyyibpay_enabled" value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_toyyibpay_enabled"
                                                                                name="is_toyyibpay_enabled"
                                                                                {{ isset($company_payment_setting['is_toyyibpay_enabled']) && $company_payment_setting['is_toyyibpay_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapsetwelve" class="accordion-collapse collapse"
                                                                aria-labelledby="headingtwelve"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row gy-4">
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="toyyibpay_category_code"
                                                                                        class="col-form-label">{{ __('Category Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="toyyibpay_category_code"
                                                                                        id="toyyibpay_category_code"
                                                                                        class="form-control"
                                                                                        value="{{ !isset($company_payment_setting['toyyibpay_category_code']) || is_null($company_payment_setting['toyyibpay_category_code']) ? '' : $company_payment_setting['toyyibpay_category_code'] }}"
                                                                                        placeholder="{{ __('Category Key') }}">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label for="toyyibpay_secret_key"
                                                                                        class="col-form-label">{{ __('Secrect Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="toyyibpay_secret_key"
                                                                                        id="toyyibpay_secret_key"
                                                                                        class="form-control"
                                                                                        value="{{ !isset($company_payment_setting['toyyibpay_secret_key']) || is_null($company_payment_setting['toyyibpay_secret_key']) ? '' : $company_payment_setting['toyyibpay_secret_key'] }}"
                                                                                        placeholder="{{ __('Secrect Key') }}">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Payfast -->
                                                        <div class="accordion accordion-flush setting-accordion"
                                                            id="accordionExample">
                                                            <div class="accordion-item">
                                                                <h2 class="accordion-header" id="headingOne">
                                                                    <button class="accordion-button collapsed"
                                                                        type="button" data-bs-toggle="collapse"
                                                                        data-bs-target="#collapseOne13"
                                                                        aria-expanded="false" aria-controls="collapseOne13">
                                                                        <span class="d-flex align-items-center">
                                                                            {{ __('PayFast') }}
                                                                        </span>
                                                                        <div class="d-flex align-items-center">
                                                                            <span class="me-2">{{ __('Enable') }}:</span>
                                                                            <div
                                                                                class="form-check form-switch custom-switch-v1">
                                                                                <input type="hidden"
                                                                                    name="is_payfast_enabled"
                                                                                    value="off">
                                                                                <input type="checkbox"
                                                                                    class="form-check-input"
                                                                                    name="is_payfast_enabled"
                                                                                    id="is_payfast_enabled"
                                                                                    {{ isset($company_payment_setting['is_payfast_enabled']) && $company_payment_setting['is_payfast_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                            </div>
                                                                        </div>
                                                                    </button>
                                                                </h2>
                                                                <div id="collapseOne13" class="accordion-collapse collapse"
                                                                    aria-labelledby="headingOne"
                                                                    data-bs-parent="#accordionExample">
                                                                    <div class="accordion-body">
                                                                        <div class="row">
                                                                            <label class="paypal-label col-form-label"
                                                                                for="payfast_mode">{{ __('Payfast Mode') }}</label>
                                                                            <div class="d-flex">
                                                                                <div class="mr-2"
                                                                                    style="margin-right: 15px;">
                                                                                    <div class="border card p-3">
                                                                                        <div class="form-check">
                                                                                            <label
                                                                                                class="form-check-labe text-dark {{ isset($company_payment_setting['payfast_mode']) && $company_payment_setting['payfast_mode'] == 'sandbox' ? 'active' : '' }}">
                                                                                                <input type="radio"
                                                                                                    name="payfast_mode"
                                                                                                    value="sandbox"
                                                                                                    class="form-check-input"
                                                                                                    {{ isset($company_payment_setting['payfast_mode']) && $company_payment_setting['payfast_mode'] == 'sandbox' ? 'checked="checked"' : '' }}>
                                                                                                {{ __('Sandbox') }}
                                                                                            </label>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="mr-2"
                                                                                    style="margin-right: 15px;">
                                                                                    <div class="border card p-3">
                                                                                        <div class="form-check">
                                                                                            <label
                                                                                                class="form-check-labe text-dark">
                                                                                                <input type="radio"
                                                                                                    name="payfast_mode"
                                                                                                    value="live"
                                                                                                    class="form-check-input"
                                                                                                    {{ isset($company_payment_setting['payfast_mode']) && $company_payment_setting['payfast_mode'] == 'live' ? 'checked="checked"' : '' }}>

                                                                                                {{ __('Live') }}
                                                                                            </label>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <div class="form-group">
                                                                                    <label for="paytm_public_key"
                                                                                        class="col-form-label">{{ __('Merchant ID') }}</label>
                                                                                    <input type="text"
                                                                                        name="payfast_merchant_id"
                                                                                        id="payfast_merchant_id"
                                                                                        class="form-control"
                                                                                        value="{{ !isset($company_payment_setting['payfast_merchant_id']) || is_null($company_payment_setting['payfast_merchant_id']) ? '' : $company_payment_setting['payfast_merchant_id'] }}"
                                                                                        placeholder="Merchant ID">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <div class="form-group">
                                                                                    <label for="paytm_secret_key"
                                                                                        class="col-form-label">{{ __('Merchant Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="payfast_merchant_key"
                                                                                        id="payfast_merchant_key"
                                                                                        class="form-control"
                                                                                        value="{{ !isset($company_payment_setting['payfast_merchant_key']) || is_null($company_payment_setting['payfast_merchant_key']) ? '' : $company_payment_setting['payfast_merchant_key'] }}"
                                                                                        placeholder="Merchant Key">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <div class="form-group">
                                                                                    <label for="payfast_signature"
                                                                                        class="col-form-label">{{ __('Salt Passphrase') }}</label>
                                                                                    <input type="text"
                                                                                        name="payfast_signature"
                                                                                        id="payfast_signature"
                                                                                        class="form-control"
                                                                                        value="{{ !isset($company_payment_setting['payfast_signature']) || is_null($company_payment_setting['payfast_signature']) ? '' : $company_payment_setting['payfast_signature'] }}"
                                                                                        placeholder="Industry Type">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Iyzipay -->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingFourteen">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseFourteen" aria-expanded="false"
                                                                    aria-controls="collapseFourteen">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Iyzipay') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">Enable:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_iyzipay_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_iyzipay_enabled"
                                                                                name="is_iyzipay_enabled"
                                                                                {{ isset($company_payment_setting['is_iyzipay_enabled']) && $company_payment_setting['is_iyzipay_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseFourteen" class="accordion-collapse collapse"
                                                                aria-labelledby="headingFourteen"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="col-md-12 pb-4">
                                                                        {{--                                                                        <label class="coingate-label col-form-label" --}}
                                                                        {{--                                                                               for="iyzipay_mode">{{ __('Iyzipay Mode') }}</label> --}}
                                                                        {{--                                                                        <br> --}}
                                                                        {{-- <div class="d-flex">
                                                                            <div class="mr-2" style="margin-right: 15px;">
                                                                                <div class="border card p-1">
                                                                                    <div class="form-check">
                                                                                        <label
                                                                                            class="form-check-label text-dark">
                                                                                            <input type="radio"
                                                                                                name="iyzipay_mode"
                                                                                                value="sandbox"
                                                                                                class="form-check-input"
                                                                                                {{ (isset($company_payment_setting['iyzipay_mode']) && $company_payment_setting['iyzipay_mode'] == '') || (isset($company_payment_setting['iyzipay_mode']) && $company_payment_setting['iyzipay_mode'] == 'sandbox') ? 'checked="checked"' : '' }}>
                                                                                            {{ __('Sandbox') }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="mr-2" style="margin-right: 15px;">
                                                                                <div class="border card p-1">
                                                                                    <div class="form-check">
                                                                                        <label
                                                                                            class="form-check-label text-dark">
                                                                                            <input type="radio"
                                                                                                name="iyzipay_mode"
                                                                                                value="live"
                                                                                                class="form-check-input"
                                                                                                {{ isset($company_payment_setting['iyzipay_mode']) && $company_payment_setting['iyzipay_mode'] == 'live' ? 'checked="checked"' : '' }}>
                                                                                            {{ __('Live') }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row gy-4">
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label class="col-form-label"
                                                                                        for="iyzipay_public_key">{{ __('Public Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="iyzipay_public_key"
                                                                                        id="iyzipay_public_key"
                                                                                        class="form-control"
                                                                                        value="{{ !isset($company_payment_setting['iyzipay_public_key']) || is_null($company_payment_setting['iyzipay_public_key']) ? '' : $company_payment_setting['iyzipay_public_key'] }}"
                                                                                        placeholder="{{ __('Public Key') }}">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="input-edits">
                                                                                <div class="form-group">
                                                                                    <label class="col-form-label"
                                                                                        for="iyzipay_secret_key">{{ __('Secret Key') }}</label>
                                                                                    <input type="text"
                                                                                        name="iyzipay_secret_key"
                                                                                        id="iyzipay_secret_key"
                                                                                        class="form-control"
                                                                                        value="{{ isset($company_payment_setting['iyzipay_secret_key']) ? $company_payment_setting['iyzipay_secret_key'] : '' }}"
                                                                                        placeholder="{{ __('Merchant Key') }}">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- SSPAY -->
                                                        <div class="accordion accordion-flush setting-accordion"
                                                            id="accordionExample">
                                                            <div class="accordion-item">
                                                                <h2 class="accordion-header" id="headingFourteen">
                                                                    <button class="accordion-button collapsed"
                                                                        type="button" data-bs-toggle="collapse"
                                                                        data-bs-target="#collapse15" aria-expanded="false"
                                                                        aria-controls="collapse15">
                                                                        <span class="d-flex align-items-center">
                                                                            {{ __('SSpay') }}
                                                                        </span>
                                                                        <div class="d-flex align-items-center">
                                                                            <span class="me-2">{{ __('Enable') }}:</span>
                                                                            <div
                                                                                class="form-check form-switch custom-switch-v1">
                                                                                <input type="hidden"
                                                                                    name="is_sspay_enabled" value="off">
                                                                                <input type="checkbox"
                                                                                    class="form-check-input input-primary"
                                                                                    id="customswitchv1-1 is_sspay_enabled"
                                                                                    name="is_sspay_enabled"
                                                                                    {{ isset($company_payment_setting['is_sspay_enabled']) && $company_payment_setting['is_sspay_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                            </div>
                                                                        </div>
                                                                    </button>
                                                                </h2>
                                                                <div id="collapse15" class="accordion-collapse collapse"
                                                                    aria-labelledby="headingFourteen"
                                                                    data-bs-parent="#accordionExample">
                                                                    <div class="accordion-body">
                                                                        <div class="row gy-4">
                                                                            <div class="col-lg-6">
                                                                                <div class="input-edits">
                                                                                    <div class="form-group">
                                                                                        <label class="col-form-label"
                                                                                            for="sspay_category_code">{{ __('Category Code') }}</label>
                                                                                        <input type="text"
                                                                                            name="sspay_category_code"
                                                                                            id="sspay_category_code"
                                                                                            class="form-control"
                                                                                            value="{{ !isset($company_payment_setting['sspay_category_code']) || is_null($company_payment_setting['sspay_category_code']) ? '' : $company_payment_setting['sspay_category_code'] }}"
                                                                                            placeholder="{{ __('Category Code') }}">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-lg-6">
                                                                                <div class="input-edits">
                                                                                    <div class="form-group">
                                                                                        <label class="col-form-label"
                                                                                            for="sspay_secret_key">{{ __('Secret Key') }}</label>
                                                                                        <input type="text"
                                                                                            name="sspay_secret_key"
                                                                                            id="sspay_secret_key"
                                                                                            class="form-control"
                                                                                            value="{{ isset($company_payment_setting['sspay_secret_key']) ? $company_payment_setting['sspay_secret_key'] : '' }}"
                                                                                            placeholder="{{ __('Secret Key') }}">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Paytab -->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingTwenty">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseTwenty" aria-expanded="true"
                                                                    aria-controls="collapseTwenty">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('PayTab') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable:') }}</span>
                                                                        <div
                                                                            class="form-check form-switch d-inline-block custom-switch-v1">
                                                                            <input type="hidden" name="is_paytab_enabled"
                                                                                value="off">
                                                                            <input type="checkbox" class="form-check-input"
                                                                                name="is_paytab_enabled"
                                                                                id="is_paytab_enabled"
                                                                                {{ isset($company_payment_setting['is_paytab_enabled']) && $company_payment_setting['is_paytab_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                            <label class="custom-control-label form-label"
                                                                                for="is_paytab_enabled"></label>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseTwenty"
                                                                class="accordion-collapse collapse"aria-labelledby="headingTwenty"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label for="paytab_profile_id"
                                                                                    class="col-form-label">{{ __('Profile Id') }}</label>
                                                                                <input type="text"
                                                                                    name="paytab_profile_id"
                                                                                    id="paytab_profile_id"
                                                                                    class="form-control"
                                                                                    value="{{ isset($company_payment_setting['paytab_profile_id']) ? $company_payment_setting['paytab_profile_id'] : '' }}"
                                                                                    placeholder="{{ __('Profile Id') }}">
                                                                            </div>
                                                                            @if ($errors->has('paytab_profile_id'))
                                                                                <span class="invalid-feedback d-block">
                                                                                    {{ $errors->first('paytab_profile_id') }}
                                                                                </span>
                                                                            @endif
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label for="paytab_server_key"
                                                                                    class="col-form-label">{{ __('Server Key') }}</label>
                                                                                <input type="text"
                                                                                    name="paytab_server_key"
                                                                                    id="paytab_server_key"
                                                                                    class="form-control"
                                                                                    value="{{ isset($company_payment_setting['paytab_server_key']) ? $company_payment_setting['paytab_server_key'] : '' }}"
                                                                                    placeholder="{{ __('Server Key') }}">
                                                                            </div>
                                                                            @if ($errors->has('paytab_server_key'))
                                                                                <span class="invalid-feedback d-block">
                                                                                    {{ $errors->first('paytab_server_key') }}
                                                                                </span>
                                                                            @endif
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label for="paytab_region"
                                                                                    class="form-label">{{ __('Region') }}</label>
                                                                                <input type="text" name="paytab_region"
                                                                                    id="paytab_region"
                                                                                    class="form-control form-control-label"
                                                                                    value="{{ isset($company_payment_setting['paytab_region']) ? $company_payment_setting['paytab_region'] : '' }}"
                                                                                    placeholder="{{ __('Region') }}" /><br>
                                                                                @if ($errors->has('paytab_region'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('paytab_region') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!--Benefit----->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingTwentyOne">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseTwentyOne"
                                                                    aria-expanded="false" aria-controls="collapseTwentyOne">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Benefit') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_benefit_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                name="is_benefit_enabled"
                                                                                id="is_benefit_enabled"
                                                                                {{ isset($company_payment_setting['is_benefit_enabled']) && $company_payment_setting['is_benefit_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                            <label class="form-check-label"
                                                                                for="is_benefit_enabled"></label>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseTwentyOne" class="accordion-collapse collapse"
                                                                aria-labelledby="headingTwentyOne"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row gy-4">

                                                                        <div class="col-lg-6">
                                                                            <div class="form-group">
                                                                                {{ Form::label('benefit_api_key', __('Benefit Key'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('benefit_api_key', isset($company_payment_setting['benefit_api_key']) ? $company_payment_setting['benefit_api_key'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Benefit Key')]) }}
                                                                                @error('benefit_api_key')
                                                                                    <span class="invalid-benefit_api_key"
                                                                                        role="alert">
                                                                                        <strong
                                                                                            class="text-danger">{{ $message }}</strong>
                                                                                    </span>
                                                                                @enderror
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group">
                                                                                {{ Form::label('benefit_secret_key', __('Benefit Secret Key'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('benefit_secret_key', isset($company_payment_setting['benefit_secret_key']) ? $company_payment_setting['benefit_secret_key'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Benefit Secret key')]) }}
                                                                                @error('benefit_secret_key')
                                                                                    <span class="invalid-benefit_secret_key"
                                                                                        role="alert">
                                                                                        <strong
                                                                                            class="text-danger">{{ $message }}</strong>
                                                                                    </span>
                                                                                @enderror
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!--Cashfree----->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingTwentyTwo">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseTwentyTwo"
                                                                    aria-expanded="false" aria-controls="collapseTwentyTwo">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Cashfree') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_cashfree_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                name="is_cashfree_enabled"
                                                                                id="is_cashfree_enabled"
                                                                                {{ isset($company_payment_setting['is_cashfree_enabled']) && $company_payment_setting['is_cashfree_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                            <label class="form-check-label"
                                                                                for="is_cashfree_enabled"></label>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseTwentyTwo" class="accordion-collapse collapse"
                                                                aria-labelledby="headingTwentyTwo"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row gy-4">
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group">
                                                                                {{ Form::label('cashfree_api_key', __('Cashfree Key'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('cashfree_api_key', isset($company_payment_setting['cashfree_api_key']) ? $company_payment_setting['cashfree_api_key'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Cashfree Key')]) }}
                                                                                @error('cashfree_api_key')
                                                                                    <span class="invalid-cashfree_api_key"
                                                                                        role="alert">
                                                                                        <strong
                                                                                            class="text-danger">{{ $message }}</strong>
                                                                                    </span>
                                                                                @enderror
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group">
                                                                                {{ Form::label('cashfree_secret_key', __('Cashfree Secret Key'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('cashfree_secret_key', isset($company_payment_setting['cashfree_secret_key']) ? $company_payment_setting['cashfree_secret_key'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Cashfree Secret key')]) }}
                                                                                @error('cashfree_secret_key')
                                                                                    <span class="invalid-cashfree_secret_key"
                                                                                        role="alert">
                                                                                        <strong
                                                                                            class="text-danger">{{ $message }}</strong>
                                                                                    </span>
                                                                                @enderror
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!--Aamarpay----->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingTwenty-One">
                                                                <button class="accordion-button" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseTwenty-One"
                                                                    aria-expanded="true" aria-controls="collapseTwenty-One">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Aamarpay') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">{{ __('Enable') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_aamarpay_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                name="is_aamarpay_enabled"
                                                                                id="is_aamarpay_enabled"
                                                                                {{ isset($company_payment_setting['is_aamarpay_enabled']) && $company_payment_setting['is_aamarpay_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                            <label class="form-check-label"
                                                                                for="is_aamarpay_enabled"></label>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseTwenty-One" class="accordion-collapse collapse"
                                                                aria-labelledby="headingTwenty-One"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row pt-2">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                {{ Form::label('aamarpay_store_id', __('Store Id'), ['class' => 'form-label']) }}
                                                                                {{ Form::text('aamarpay_store_id', isset($company_payment_setting['aamarpay_store_id']) ? $company_payment_setting['aamarpay_store_id'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Store Id')]) }}<br>
                                                                                @if ($errors->has('aamarpay_store_id'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('aamarpay_store_id') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                {{ Form::label('aamarpay_signature_key', __('Signature Key'), ['class' => 'form-label']) }}
                                                                                {{ Form::text('aamarpay_signature_key', isset($company_payment_setting['aamarpay_signature_key']) ? $company_payment_setting['aamarpay_signature_key'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Signature Key')]) }}<br>
                                                                                @if ($errors->has('aamarpay_signature_key'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('aamarpay_signature_key') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                {{ Form::label('aamarpay_description', __('Description'), ['class' => 'form-label']) }}
                                                                                {{ Form::text('aamarpay_description', isset($company_payment_setting['aamarpay_description']) ? $company_payment_setting['aamarpay_description'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}<br>
                                                                                @if ($errors->has('aamarpay_description'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('aamarpay_description') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!--PayTR----->
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingTwenty-Two">
                                                                <button class="accordion-button" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseTwentyfive"
                                                                    aria-expanded="true" aria-controls="collapseTwentyfive">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('PayTR') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <label class="form-check-label m-1"
                                                                            for="is_paytr_enabled">{{ __('Enable') }}</label>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_paytr_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                name="is_paytr_enabled"
                                                                                id="is_paytr_enabled"
                                                                                {{ isset($company_payment_setting['is_paytr_enabled']) && $company_payment_setting['is_paytr_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseTwentyfive" class="accordion-collapse collapse"
                                                                aria-labelledby="headingTwenty-Two"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row pt-2">
                                                                        <div class="col-md-4">
                                                                            <div class="form-group">
                                                                                {{ Form::label('paytr_merchant_id', __('Merchant Id'), ['class' => 'form-label']) }}
                                                                                {{ Form::text('paytr_merchant_id', isset($company_payment_setting['paytr_merchant_id']) ? $company_payment_setting['paytr_merchant_id'] : '', ['class' => 'form-control', 'placeholder' => __('Merchant Id')]) }}<br>
                                                                                @if ($errors->has('paytr_merchant_id'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('paytr_merchant_id') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="form-group">
                                                                                {{ Form::label('paytr_merchant_key', __('Merchant Key'), ['class' => 'form-label']) }}
                                                                                {{ Form::text('paytr_merchant_key', isset($company_payment_setting['paytr_merchant_key']) ? $company_payment_setting['paytr_merchant_key'] : '', ['class' => 'form-control', 'placeholder' => __('Merchant Key')]) }}<br>
                                                                                @if ($errors->has('paytr_merchant_key'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('paytr_merchant_key') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="form-group">
                                                                                {{ Form::label('paytr_merchant_salt', __('Merchant Salt'), ['class' => 'form-label']) }}
                                                                                {{ Form::text('paytr_merchant_salt', isset($company_payment_setting['paytr_merchant_salt']) ? $company_payment_setting['paytr_merchant_salt'] : '', ['class' => 'form-control', 'placeholder' => __('Merchant Salt')]) }}<br>
                                                                                @if ($errors->has('paytr_merchant_salt'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('paytr_merchant_salt') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!--Yookassa----->  
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingTwenty-Three">
                                                                <button class="accordion-button" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseTwentysix"
                                                                    aria-expanded="true" aria-controls="collapseTwentysix">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Yookassa') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <label class="form-check-label m-1"
                                                                            for="is_yookassa_enabled">{{ __('Enable') }}</label>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_yookassa_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                name="is_yookassa_enabled"
                                                                                id="is_yookassa_enabled"
                                                                                {{ isset($company_payment_setting['is_yookassa_enabled']) && $company_payment_setting['is_yookassa_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseTwentysix" class="accordion-collapse collapse"
                                                                aria-labelledby="headingTwenty-Three"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row pt-2">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                {{ Form::label('yookassa_shop_id', __('Shop ID Key'), ['class' => 'form-label']) }}
                                                                                {{ Form::text('yookassa_shop_id', isset($company_payment_setting['yookassa_shop_id']) ? $company_payment_setting['yookassa_shop_id'] : '', ['class' => 'form-control', 'placeholder' => __('Merchant Id')]) }}<br>
                                                                                @if ($errors->has('yookassa_shop_id'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('yookassa_shop_id') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                {{ Form::label('yookassa_secret', __('Secret Key'), ['class' => 'form-label']) }}
                                                                                {{ Form::text('yookassa_secret', isset($company_payment_setting['yookassa_secret']) ? $company_payment_setting['yookassa_secret'] : '', ['class' => 'form-control', 'placeholder' => __('Merchant Key')]) }}<br>
                                                                                @if ($errors->has('yookassa_secret'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('yookassa_secret') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!--Midtrans----->  
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingTwenty-four">
                                                                <button class="accordion-button" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseTwentyseven"
                                                                    aria-expanded="true" aria-controls="collapseTwentyseven">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Midtrans') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <label class="form-check-label m-1"
                                                                            for="is_midtrans_enabled">{{ __('Enable') }}</label>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_midtrans_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                name="is_midtrans_enabled"
                                                                                id="is_midtrans_enabled"
                                                                                {{ isset($company_payment_setting['is_midtrans_enabled']) && $company_payment_setting['is_midtrans_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseTwentyseven" class="accordion-collapse collapse"
                                                                aria-labelledby="headingTwenty-four"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row pt-2">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                {{ Form::label('midtrans_secret', __('Secret Key'), ['class' => 'form-label']) }}
                                                                                {{ Form::text('midtrans_secret', isset($company_payment_setting['midtrans_secret']) ? $company_payment_setting['midtrans_secret'] : '', ['class' => 'form-control', 'placeholder' => __('Merchant Id')]) }}<br>
                                                                                @if ($errors->has('midtrans_secret'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('midtrans_secret') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!--Xendit----->  
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingTwenty-five">
                                                                <button class="accordion-button" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseTwentyeight"
                                                                    aria-expanded="true" aria-controls="collapseTwentyeight">
                                                                    <span class="d-flex align-items-center">
                                                                        {{ __('Xendit') }}
                                                                    </span>
                                                                    <div class="d-flex align-items-center">
                                                                        <label class="form-check-label m-1"
                                                                            for="is_xendit_enabled">{{ __('Enable') }}</label>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_xendit_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                name="is_xendit_enabled"
                                                                                id="is_xendit_enabled"
                                                                                {{ isset($company_payment_setting['is_xendit_enabled']) && $company_payment_setting['is_xendit_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseTwentyeight" class="accordion-collapse collapse"
                                                                aria-labelledby="headingTwenty-five"
                                                                data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    <div class="row pt-2">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                {{ Form::label('xendit_api', __('API Key'), ['class' => 'form-label']) }}
                                                                                {{ Form::text('xendit_api', isset($company_payment_setting['xendit_api']) ? $company_payment_setting['xendit_api'] : '', ['class' => 'form-control', 'placeholder' => __('API Key')]) }}<br>
                                                                                @if ($errors->has('xendit_api'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('xendit_api') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                {{ Form::label('xendit_token', __('Token'), ['class' => 'form-label']) }}
                                                                                {{ Form::text('xendit_token', isset($company_payment_setting['xendit_token']) ? $company_payment_setting['xendit_token'] : '', ['class' => 'form-control', 'placeholder' => __('Token')]) }}<br>
                                                                                @if ($errors->has('xendit_token'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('xendit_token') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div> --}}
                                                    {{-- </DEPRECATED> --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="form-group">
                                <input class="{{ VC::BT_PR_PRM10 }}" type="submit"
                                    value="{{ __('Save Changes') }}">
                            </div>
                        </div>
                        </form>
                    </div>
                    <div id="zoom-settings" class="card">
                        <div class="card-header">
                            <h5>{{ __('Zoom Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your Zoom settings') }}</small>
                        </div>
                        @php
                            $zoomSettingsBaseRouteName      = 'zoom.settings';
                            $zoomSettingsKebabRouteName     = Str::kebab($zoomSettingsBaseRouteName);
                            $zoomSettingsResolvedName       = Route::has($zoomSettingsBaseRouteName)
                                ? $zoomSettingsBaseRouteName
                                : (Route::has($zoomSettingsKebabRouteName) ? $zoomSettingsKebabRouteName : null);
                            $zoomSettingsRouteArray         = $zoomSettingsResolvedName ? [$zoomSettingsResolvedName] : ['#'];
                            $zoomSettingsUrl                = $zoomSettingsResolvedName ? route($zoomSettingsResolvedName) : '#';
                            $zoomSettingsGuardMsg           = Utility::fetchLinkMessage($lang, 'zoom', 'zoom_settings_route_unavailable') ?? 'Zoom settings route is unavailable. Please contact technical support or your domain administrator.';
                            $zoomSettingsFormId             = 'zoom-settings-form';
                        @endphp
                        {!! Form::model($setting, [
                            'route'          => $zoomSettingsRouteArray,
                            'method'         => 'post',
                            'id'             => $zoomSettingsFormId,
                            'data-url'       => $zoomSettingsUrl,
                            'data-guard-msg' => $zoomSettingsGuardMsg
                        ]) !!}
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        const form = document.getElementById('{{ $zoomSettingsFormId }}');
                                        if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                        form.setAttribute('data-listener-active', 'true');
                                        form.addEventListener('submit', e => {
                                            try {
                                                const url = form.getAttribute('data-url') || '#';
                                                const action = form.getAttribute('action') || '#';
                                                if (url !== '#' || action !== '#') return;
                                                e.preventDefault();
                                                const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
                                                    document.body.appendChild(container);
                                                }
                                                if (hasBootstrap) {
                                                    const toast = document.createElement('div');
                                                    toast.className = 'toast';
                                                    toast.setAttribute('role','alert');
                                                    toast.setAttribute('aria-live','assertive');
                                                    toast.setAttribute('aria-atomic','true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = msg;
                                                    toast.appendChild(body);
                                                    container.appendChild(toast);
                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                } else {
                                                    alert(msg);
                                                }
                                                form.setAttribute('data-failed-route', 'true');
                                            } catch (err) {}
                                        });
                                    })();
                                </script>
                            @endpush
                            <div class="card-body">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        <label class="{{ VC::FM_LB }}">{{ __('Zoom Account ID') }}</label> <br>
                                        {{ Form::text('zoom_account_id', old('zoom_account_id', $setting['zoom_account_id'] ?? ''), [
                                            'class' => VC::FM_CT,
                                            'placeholder' => __('Enter Zoom Account ID'),
                                        ]) }}
                                        @error('zoom_account_id')
                                            <span class="invalid-feedback {{ VC::DBL }}">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        <label class="{{ VC::FM_LB }}">{{ __('Zoom Client ID') }}</label> <br>
                                        {{ Form::text('zoom_client_id', old('zoom_client_id', $setting['zoom_client_id'] ?? ''), [
                                            'class' => VC::FM_CT,
                                            'placeholder' => __('Enter Zoom Client ID'),
                                        ]) }}
                                        @error('zoom_client_id')
                                            <span class="invalid-feedback {{ VC::DBL }}">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        <label class="{{ VC::FM_LB }}">{{ __('Zoom Client Secret Key') }}</label> <br>
                                        {{ Form::password('zoom_client_secret', [
                                            'class' => VC::FM_CT,
                                            'placeholder' => __('Enter Zoom Client Secret Key'),
                                            'autocomplete' => 'off',
                                            'spellcheck' => 'false',
                                        ]) }}
                                        @error('zoom_client_secret')
                                            <span class="invalid-feedback {{ VC::DBL }}">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer {{ VC::DFL }} {{ VC::JCE }}">
                                <div class="{{ VC::FM_G }}">
                                    <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                    <div id="slack-settings" class="card">
                        <div class="card-header">
                            <h5>{{ __('Slack Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your Slack settings') }}</small>
                        </div>
                        @php
                            $slackSettingsBaseRouteName       = 'slack.settings';
                            $slackSettingsKebabRouteName      = Str::kebab($slackSettingsBaseRouteName);
                            $slackSettingsResolvedRouteName   = Route::has($slackSettingsBaseRouteName)
                                ? $slackSettingsBaseRouteName
                                : (Route::has($slackSettingsKebabRouteName) ? $slackSettingsKebabRouteName : null);
                            $slackSettingsRouteArray          = $slackSettingsResolvedRouteName ? [$slackSettingsResolvedRouteName] : ['#'];
                            $slackSettingsUrl                 = $slackSettingsResolvedRouteName ? route($slackSettingsResolvedRouteName) : '#';
                            $slackSettingsGuardMsg            = Utility::fetchLinkMessage($lang, 'slack', 'slack_settings_route_unavailable')
                                ?? 'Slack settings route is unavailable. Please contact technical support or your domain administrator.';
                            $slackSettingsFormId              = 'slack-setting';
                        @endphp
                        {!! Form::open([
                            'route'          => $slackSettingsRouteArray,
                            'id'             => $slackSettingsFormId,
                            'method'         => 'post',
                            'class'          => 'd-contents',
                            'data-url'       => $slackSettingsUrl,
                            'data-guard-msg' => $slackSettingsGuardMsg
                        ]) !!}
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        const form = document.getElementById('{{ $slackSettingsFormId }}');
                                        if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                        form.setAttribute('data-listener-active', 'true');
                                        form.addEventListener('submit', e => {
                                            try {
                                                const url = form.getAttribute('data-url') || '#';
                                                const action = form.getAttribute('action') || '#';
                                                if (url !== '#' || action !== '#') return;
                                                e.preventDefault();
                                                const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
                                                    document.body.appendChild(container);
                                                }
                                                if (hasBootstrap) {
                                                    const toast = document.createElement('div');
                                                    toast.className = 'toast';
                                                    toast.setAttribute('role','alert');
                                                    toast.setAttribute('aria-live','assertive');
                                                    toast.setAttribute('aria-atomic','true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = msg;
                                                    toast.appendChild(body);
                                                    container.appendChild(toast);
                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                } else {
                                                    alert(msg);
                                                }
                                                form.setAttribute('data-failed-route', 'true');
                                            } catch (err) {}
                                        });
                                    })();
                                </script>
                            @endpush
                            <div class="card-body">
                                <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                                    <label class="{{ VC::FM_LB }}">{{ __('Slack Webhook URL') }}</label> <br>
                                    {{ Form::url('slack_webhook', old('slack_webhook', $setting['slack_webhook'] ?? ''), [
                                        'class' => VC::FM_CT . ' w-100',
                                        'placeholder' => __('Enter Slack Webhook URL'),
                                        'required' => true,
                                    ]) }}
                                </div>

                                <div class="{{ VC::C12 }} mt-5 mb-2">
                                    <h5 class="small-title">{{ __('Module Settings') }}</h5>
                                </div>

                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Lead') }}</span>
                                                    {{ Form::checkbox(
                                                        'lead_notification',
                                                        '1',
                                                        old('lead_notification', $setting['lead_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'lead_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="lead_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Deal') }}</span>
                                                    {{ Form::checkbox(
                                                        'deal_notification',
                                                        '1',
                                                        old('deal_notification', $setting['deal_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'deal_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="deal_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('Lead to Deal Conversion') }}</span>
                                                    {{ Form::checkbox(
                                                        'leadtodeal_notification',
                                                        '1',
                                                        old('leadtodeal_notification', $setting['leadtodeal_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'leadtodeal_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="leadtodeal_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Contract') }}</span>
                                                    {{ Form::checkbox(
                                                        'contract_notification',
                                                        '1',
                                                        old('contract_notification', $setting['contract_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'contract_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="contract_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Project') }}</span>
                                                    {{ Form::checkbox(
                                                        'project_notification',
                                                        '1',
                                                        old('project_notification', $setting['project_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'project_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="project_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Task') }}</span>
                                                    {{ Form::checkbox(
                                                        'task_notification',
                                                        '1',
                                                        old('task_notification', $setting['task_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'task_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="task_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('Task Stage Updated') }}</span>
                                                    {{ Form::checkbox(
                                                        'taskmove_notification',
                                                        '1',
                                                        old('taskmove_notification', $setting['taskmove_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'taskmove_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="taskmove_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Task Comment') }}</span>
                                                    {{ Form::checkbox(
                                                        'taskcomment_notification',
                                                        '1',
                                                        old('taskcomment_notification', $setting['taskcomment_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'taskcomment_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="taskcomment_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="{{ VC::RW }} mt-2">
                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Monthly Payslip') }}</span>
                                                    {{ Form::checkbox(
                                                        'payslip_notification',
                                                        '1',
                                                        old('payslip_notification', $setting['payslip_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'payslip_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="payslip_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Award') }}</span>
                                                    {{ Form::checkbox(
                                                        'award_notification',
                                                        '1',
                                                        old('award_notification', $setting['award_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'award_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="award_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Announcement') }}</span>
                                                    {{ Form::checkbox(
                                                        'announcement_notification',
                                                        '1',
                                                        old('announcement_notification', $setting['announcement_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'announcement_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="announcement_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Holiday') }}</span>
                                                    {{ Form::checkbox(
                                                        'holiday_notification',
                                                        '1',
                                                        old('holiday_notification', $setting['holiday_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'holiday_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="holiday_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Support Ticket') }}</span>
                                                    {{ Form::checkbox(
                                                        'support_notification',
                                                        '1',
                                                        old('support_notification', $setting['support_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'support_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="support_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Event') }}</span>
                                                    {{ Form::checkbox(
                                                        'event_notification',
                                                        '1',
                                                        old('event_notification', $setting['event_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'event_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="event_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Meeting') }}</span>
                                                    {{ Form::checkbox(
                                                        'meeting_notification',
                                                        '1',
                                                        old('meeting_notification', $setting['meeting_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'meeting_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="meeting_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Company Policy') }}</span>
                                                    {{ Form::checkbox(
                                                        'policy_notification',
                                                        '1',
                                                        old('policy_notification', $setting['policy_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'policy_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="policy_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="{{ VC::RW }} mt-2">
                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Invoice') }}</span>
                                                    {{ Form::checkbox(
                                                        'invoice_notification',
                                                        '1',
                                                        old('invoice_notification', $setting['invoice_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'invoice_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="invoice_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Revenue') }}</span>
                                                    {{ Form::checkbox(
                                                        'revenue_notification',
                                                        '1',
                                                        old('revenue_notification', $setting['revenue_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'revenue_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="revenue_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Bill') }}</span>
                                                    {{ Form::checkbox(
                                                        'bill_notification',
                                                        '1',
                                                        old('bill_notification', $setting['bill_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'bill_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="bill_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Invoice Payment') }}</span>
                                                    {{ Form::checkbox(
                                                        'payment_notification',
                                                        '1',
                                                        old('payment_notification', $setting['payment_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'payment_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="payment_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Budget') }}</span>
                                                    {{ Form::checkbox(
                                                        'budget_notification',
                                                        '1',
                                                        old('budget_notification', $setting['budget_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'budget_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="budget_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer {{ VC::DFL }} {{ VC::JCE }}">
                                <div class="{{ VC::FM_G }}">
                                    <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                    <div id="telegram-settings" class="card">
                        <div class="card-header">
                            <h5>{{ __('Telegram Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your Telegram settings') }}</small>
                        </div>
                        @php
                            $telegramSettingsBaseRouteName        = 'telegram.settings';
                            $telegramSettingsKebabRouteName       = Str::kebab($telegramSettingsBaseRouteName);
                            $telegramSettingsResolvedRouteName    = Route::has($telegramSettingsBaseRouteName)
                                ? $telegramSettingsBaseRouteName
                                : (Route::has($telegramSettingsKebabRouteName) ? $telegramSettingsKebabRouteName : null);
                            $telegramSettingsRouteArray           = $telegramSettingsResolvedRouteName ? [$telegramSettingsResolvedRouteName] : ['#'];
                            $telegramSettingsUrl                  = $telegramSettingsResolvedRouteName ? route($telegramSettingsResolvedRouteName) : '#';
                            $telegramSettingsGuardMsg             = Utility::fetchLinkMessage($lang, 'telegram', 'telegram_settings_route_unavailable')
                                ?? 'Telegram settings route is unavailable. Please contact technical support or your domain administrator.';
                            $telegramSettingsFormId               = 'telegram-setting';
                        @endphp
                        {!! Form::open([
                            'route'          => $telegramSettingsRouteArray,
                            'id'             => $telegramSettingsFormId,
                            'method'         => 'post',
                            'class'          => 'd-contents',
                            'data-url'       => $telegramSettingsUrl,
                            'data-guard-msg' => $telegramSettingsGuardMsg
                        ]) !!}
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        const form = document.getElementById('{{ $telegramSettingsFormId }}');
                                        if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                        form.setAttribute('data-listener-active', 'true');
                                        form.addEventListener('submit', e => {
                                            try {
                                                const url = form.getAttribute('data-url') || '#';
                                                const action = form.getAttribute('action') || '#';
                                                if (url !== '#' || action !== '#') return;
                                                e.preventDefault();
                                                const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
                                                    document.body.appendChild(container);
                                                }
                                                if (hasBootstrap) {
                                                    const toast = document.createElement('div');
                                                    toast.className = 'toast';
                                                    toast.setAttribute('role','alert');
                                                    toast.setAttribute('aria-live','assertive');
                                                    toast.setAttribute('aria-atomic','true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = msg;
                                                    toast.appendChild(body);
                                                    container.appendChild(toast);
                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                } else {
                                                    alert(msg);
                                                }
                                                form.setAttribute('data-failed-route', 'true');
                                            } catch (err) {}
                                        });
                                    })();
                                </script>
                            @endpush
                            <div class="card-body">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        <label class="{{ VC::FM_LB }}">{{ __('Telegram AccessToken') }}</label> <br>
                                        {{ Form::text('telegram_accestoken', old('telegram_accestoken', $setting['telegram_accestoken'] ?? ''), [
                                            'class' => VC::FM_CT,
                                            'placeholder' => __('Enter Telegram AccessToken'),
                                            'autocomplete' => 'off',
                                            'spellcheck' => 'false',
                                        ]) }}
                                        @error('telegram_accestoken')
                                            <span class="invalid-feedback {{ VC::DBL }}">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                        <label class="{{ VC::FM_LB }}">{{ __('Telegram ChatID') }}</label> <br>
                                        {{ Form::text('telegram_chatid', old('telegram_chatid', $setting['telegram_chatid'] ?? ''), [
                                            'class' => VC::FM_CT,
                                            'placeholder' => __('Enter Telegram ChatID'),
                                        ]) }}
                                        @error('telegram_chatid')
                                            <span class="invalid-feedback {{ VC::DBL }}">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12 mt-5 mb-2">
                                    <h5 class="small-title">{{ __('Module Settings') }}</h5>
                                </div>

                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Lead') }}</span>
                                                    {{ Form::checkbox('telegram_lead_notification', '1',
                                                        old('telegram_lead_notification', $setting['telegram_lead_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_lead_notification']) }}
                                                    <label class="form-check-label" for="telegram_lead_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Deal') }}</span>
                                                    {{ Form::checkbox('telegram_deal_notification', '1',
                                                        old('telegram_deal_notification', $setting['telegram_deal_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_deal_notification']) }}
                                                    <label class="form-check-label" for="telegram_deal_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('Lead to Deal Conversion') }}</span>
                                                    {{ Form::checkbox('telegram_leadtodeal_notification', '1',
                                                        old('telegram_leadtodeal_notification', $setting['telegram_leadtodeal_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_leadtodeal_notification']) }}
                                                    <label class="form-check-label" for="telegram_leadtodeal_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Contract') }}</span>
                                                    {{ Form::checkbox('telegram_contract_notification', '1',
                                                        old('telegram_contract_notification', $setting['telegram_contract_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_contract_notification']) }}
                                                    <label class="form-check-label" for="telegram_contract_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Project') }}</span>
                                                    {{ Form::checkbox('telegram_project_notification', '1',
                                                        old('telegram_project_notification', $setting['telegram_project_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_project_notification']) }}
                                                    <label class="form-check-label" for="telegram_project_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Task') }}</span>
                                                    {{ Form::checkbox('telegram_task_notification', '1',
                                                        old('telegram_task_notification', $setting['telegram_task_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_task_notification']) }}
                                                    <label class="form-check-label" for="telegram_task_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('Task Stage Updated') }}</span>
                                                    {{ Form::checkbox('telegram_taskmove_notification', '1',
                                                        old('telegram_taskmove_notification', $setting['telegram_taskmove_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_taskmove_notification']) }}
                                                    <label class="form-check-label" for="telegram_taskmove_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Task Comment') }}</span>
                                                    {{ Form::checkbox('telegram_taskcomment_notification', '1',
                                                        old('telegram_taskcomment_notification', $setting['telegram_taskcomment_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_taskcomment_notification']) }}
                                                    <label class="form-check-label" for="telegram_taskcomment_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="{{ VC::RW }} mt-2">
                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Monthly Payslip') }}</span>
                                                    {{ Form::checkbox('telegram_payslip_notification', '1',
                                                        old('telegram_payslip_notification', $setting['telegram_payslip_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_payslip_notification']) }}
                                                    <label class="form-check-label" for="telegram_payslip_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Award') }}</span>
                                                    {{ Form::checkbox('telegram_award_notification', '1',
                                                        old('telegram_award_notification', $setting['telegram_award_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_award_notification']) }}
                                                    <label class="form-check-label" for="telegram_award_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Announcement') }}</span>
                                                    {{ Form::checkbox('telegram_announcement_notification', '1',
                                                        old('telegram_announcement_notification', $setting['telegram_announcement_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_announcement_notification']) }}
                                                    <label class="form-check-label" for="telegram_announcement_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Holiday') }}</span>
                                                    {{ Form::checkbox('telegram_holiday_notification', '1',
                                                        old('telegram_holiday_notification', $setting['telegram_holiday_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_holiday_notification']) }}
                                                    <label class="form-check-label" for="telegram_holiday_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Support Ticket') }}</span>
                                                    {{ Form::checkbox('telegram_support_notification', '1',
                                                        old('telegram_support_notification', $setting['telegram_support_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_support_notification']) }}
                                                    <label class="form-check-label" for="telegram_support_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Event') }}</span>
                                                    {{ Form::checkbox('telegram_event_notification', '1',
                                                        old('telegram_event_notification', $setting['telegram_event_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_event_notification']) }}
                                                    <label class="form-check-label" for="telegram_event_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Meeting') }}</span>
                                                    {{ Form::checkbox('telegram_meeting_notification', '1',
                                                        old('telegram_meeting_notification', $setting['telegram_meeting_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_meeting_notification']) }}
                                                    <label class="form-check-label" for="telegram_meeting_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Company Policy') }}</span>
                                                    {{ Form::checkbox('telegram_policy_notification', '1',
                                                        old('telegram_policy_notification', $setting['telegram_policy_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_policy_notification']) }}
                                                    <label class="form-check-label" for="telegram_policy_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="{{ VC::RW }} mt-2">
                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Invoice') }}</span>
                                                    {{ Form::checkbox('telegram_invoice_notification', '1',
                                                        old('telegram_invoice_notification', $setting['telegram_invoice_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_invoice_notification']) }}
                                                    <label class="form-check-label" for="telegram_invoice_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Revenue') }}</span>
                                                    {{ Form::checkbox('telegram_revenue_notification', '1',
                                                        old('telegram_revenue_notification', $setting['telegram_revenue_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_revenue_notification']) }}
                                                    <label class="form-check-label" for="telegram_revenue_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Bill') }}</span>
                                                    {{ Form::checkbox('telegram_bill_notification', '1',
                                                        old('telegram_bill_notification', $setting['telegram_bill_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_bill_notification']) }}
                                                    <label class="form-check-label" for="telegram_bill_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Invoice Payment') }}</span>
                                                    {{ Form::checkbox('telegram_payment_notification', '1',
                                                        old('telegram_payment_notification', $setting['telegram_payment_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_payment_notification']) }}
                                                    <label class="form-check-label" for="telegram_payment_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM3 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Budget') }}</span>
                                                    {{ Form::checkbox('telegram_budget_notification', '1',
                                                        old('telegram_budget_notification', $setting['telegram_budget_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'telegram_budget_notification']) }}
                                                    <label class="form-check-label" for="telegram_budget_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <div class="form-group">
                                    <input class="{{ VC::BT_PR_PRM10 }}" type="submit"
                                        value="{{ __('Save Changes') }}">
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                    <div id="twilio-settings" class="card">
                        <div class="card-header">
                            <h5>{{ __('Twilio Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your Twilio settings') }}</small>
                        </div>
                        @php
                            $twilioSettingBaseRouteName        = 'twilio.setting';
                            $twilioSettingKebabRouteName       = Str::kebab($twilioSettingBaseRouteName);
                            $twilioSettingResolvedRouteName    = Route::has($twilioSettingBaseRouteName)
                                ? $twilioSettingBaseRouteName
                                : (Route::has($twilioSettingKebabRouteName) ? $twilioSettingKebabRouteName : null);
                            $twilioSettingRouteArray           = $twilioSettingResolvedRouteName ? [$twilioSettingResolvedRouteName] : ['#'];
                            $twilioSettingUrl                  = $twilioSettingResolvedRouteName ? route($twilioSettingResolvedRouteName) : '#';
                            $twilioSettingGuardMsg             = Utility::fetchLinkMessage($lang, 'twilio', 'twilio_setting_route_unavailable') ?? 'Twilio setting route is unavailable. Please contact technical support or your domain administrator.';
                            $twilioSettingFormId               = 'twilio-setting-form';
                        @endphp
                        {!! Form::model($setting, [
                            'route'          => $twilioSettingRouteArray,
                            'method'         => 'post',
                            'id'             => $twilioSettingFormId,
                            'data-url'       => $twilioSettingUrl,
                            'data-guard-msg' => $twilioSettingGuardMsg
                        ]) !!}
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        const form = document.getElementById('{{ $twilioSettingFormId }}');
                                        if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                        form.setAttribute('data-listener-active', 'true');
                                        form.addEventListener('submit', e => {
                                            try {
                                                const url = form.getAttribute('data-url') || '#';
                                                const action = form.getAttribute('action') || '#';
                                                if (url !== '#' || action !== '#') return;
                                                e.preventDefault();
                                                const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
                                                    document.body.appendChild(container);
                                                }
                                                if (hasBootstrap) {
                                                    const toast = document.createElement('div');
                                                    toast.className = 'toast';
                                                    toast.setAttribute('role','alert');
                                                    toast.setAttribute('aria-live','assertive');
                                                    toast.setAttribute('aria-atomic','true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = msg;
                                                    toast.appendChild(body);
                                                    container.appendChild(toast);
                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                } else {
                                                    alert(msg);
                                                }
                                                form.setAttribute('data-failed-route', 'true');
                                            } catch (err) {}
                                        });
                                    })();
                                </script>
                            @endpush
                            <div class="card-body">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::FM_G }} {{ VC::CM4 }}">
                                        <label class="{{ VC::FM_LB }}">{{ __('Twilio SID') }}</label> <br>
                                        {{ Form::text('twilio_sid', old('twilio_sid', $setting['twilio_sid'] ?? ''), [
                                            'class' => VC::FM_CT . ' w-100',
                                            'placeholder' => __('Enter Twilio SID'),
                                            'required' => true,
                                        ]) }}
                                        @error('twilio_sid')
                                            <span class="invalid-feedback {{ VC::DBL }}">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM4 }}">
                                        <label class="{{ VC::FM_LB }}">{{ __('Twilio Token') }}</label> <br>
                                        {{-- Do not re-print secrets --}}
                                        {{ Form::password('twilio_token', [
                                            'class' => VC::FM_CT . ' w-100',
                                            'placeholder' => __('Enter Twilio Token'),
                                            'required' => true,
                                            'autocomplete' => 'off',
                                            'spellcheck' => 'false',
                                        ]) }}
                                        @error('twilio_token')
                                            <span class="invalid-feedback {{ VC::DBL }}">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::FM_G }} {{ VC::CM4 }}">
                                        <label class="{{ VC::FM_LB }}">{{ __('Twilio From') }}</label> <br>
                                        {{ Form::text('twilio_from', old('twilio_from', $setting['twilio_from'] ?? ''), [
                                            'class' => VC::FM_CT . ' w-100',
                                            'placeholder' => __('Enter Twilio From'),
                                            'required' => true,
                                        ]) }}
                                        @error('twilio_from')
                                            <span class="invalid-feedback {{ VC::DBL }}">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="{{ VC::C12 }} mt-4 mb-2">
                                        <h5 class="small-title">{{ __('Module Settings') }}</h5>
                                    </div>

                                    <div class="{{ VC::CM4 }} {{ VC::MB1 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Customer') }}</span>
                                                    {{ Form::checkbox(
                                                        'twilio_customer_notification',
                                                        '1',
                                                        old('twilio_customer_notification', $setting['twilio_customer_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'twilio_customer_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="twilio_customer_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Vendor') }}</span>
                                                    {{ Form::checkbox(
                                                        'twilio_vendor_notification',
                                                        '1',
                                                        old('twilio_vendor_notification', $setting['twilio_vendor_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'twilio_vendor_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="twilio_vendor_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM4 }} {{ VC::MB1 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Invoice') }}</span>
                                                    {{ Form::checkbox(
                                                        'twilio_invoice_notification',
                                                        '1',
                                                        old('twilio_invoice_notification', $setting['twilio_invoice_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'twilio_invoice_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="twilio_invoice_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Revenue') }}</span>
                                                    {{ Form::checkbox(
                                                        'twilio_revenue_notification',
                                                        '1',
                                                        old('twilio_revenue_notification', $setting['twilio_revenue_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'twilio_revenue_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="twilio_revenue_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM4 }} {{ VC::MB1 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Bill') }}</span>
                                                    {{ Form::checkbox(
                                                        'twilio_bill_notification',
                                                        '1',
                                                        old('twilio_bill_notification', $setting['twilio_bill_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'twilio_bill_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="twilio_bill_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Proposal') }}</span>
                                                    {{ Form::checkbox(
                                                        'twilio_proposal_notification',
                                                        '1',
                                                        old('twilio_proposal_notification', $setting['twilio_proposal_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'twilio_proposal_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="twilio_proposal_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="{{ VC::CM4 }} {{ VC::MB1 }}">
                                        <ul class="{{ VC::LGRP }}">
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('New Payment') }}</span>
                                                    {{ Form::checkbox(
                                                        'twilio_payment_notification',
                                                        '1',
                                                        old('twilio_payment_notification', $setting['twilio_payment_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'twilio_payment_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="twilio_payment_notification"></label>
                                                </div>
                                            </li>
                                            <li class="{{ VC::LGI }}">
                                                <div class="form-switch form-switch-right">
                                                    <span>{{ __('Invoice Reminder') }}</span>
                                                    {{ Form::checkbox(
                                                        'twilio_reminder_notification',
                                                        '1',
                                                        old('twilio_reminder_notification', $setting['twilio_reminder_notification'] ?? '0') == '1',
                                                        ['class' => 'form-check-input', 'id' => 'twilio_reminder_notification']
                                                    ) }}
                                                    <label class="form-check-label" for="twilio_reminder_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <div class="form-group">
                                    <input class="{{ VC::BT_PR_PRM10 }}" type="submit"
                                        value="{{ __('Save Changes') }}">
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                    <div id="email-notification-settings" class="card">
                        <div class="col-md-12">
                            <div class="card-header">
                                <h5>{{ __('Email Notification Settings') }}</h5>
                                <small class="text-muted">{{ __('Edit email notification settings') }}</small>
                            </div>
                            @php
                                $emailStatusLanguageBaseRoute    = ViewsConstants::EMLS . '.status.language';
                                $emailStatusLanguageKebabRoute   = Str::kebab($emailStatusLanguageBaseRoute);
                                $emailStatusLanguageResolvedName = Route::has($emailStatusLanguageBaseRoute)
                                    ? $emailStatusLanguageBaseRoute
                                    : (Route::has($emailStatusLanguageKebabRoute) ? $emailStatusLanguageKebabRoute : null);
                                $emailStatusLanguageRouteArray   = $emailStatusLanguageResolvedName ? [$emailStatusLanguageResolvedName] : ['#'];
                                $emailStatusLanguageUrl          = $emailStatusLanguageResolvedName ? route($emailStatusLanguageResolvedName) : '#';
                                $emailStatusLanguageGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::EMLS, 'email_status_language_route_unavailable') ?? 'Email status language route is unavailable. Please contact technical support or your domain administrator.';
                                $emailStatusLanguageFormId       = 'email-status-language-form';
                            @endphp
                            {!! Form::model($setting, [
                                'route'          => $emailStatusLanguageRouteArray,
                                'method'         => 'post',
                                'id'             => $emailStatusLanguageFormId,
                                'data-url'       => $emailStatusLanguageUrl,
                                'data-guard-msg' => $emailStatusLanguageGuardMsg
                            ]) !!}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer>
                                        (() => {
                                            const form = document.getElementById('{{ $emailStatusLanguageFormId }}');
                                            if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                            form.setAttribute('data-listener-active', 'true');
                                            form.addEventListener('submit', e => {
                                                try {
                                                    const url = form.getAttribute('data-url') || '#';
                                                    if (url !== '#') return;
                                                    e.preventDefault();
                                                    const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                                    const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bs) {
                                                        const toast = document.createElement('div');
                                                        toast.className = 'toast';
                                                        toast.setAttribute('role','alert');
                                                        toast.setAttribute('aria-live','assertive');
                                                        toast.setAttribute('aria-atomic','true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toast.appendChild(body);
                                                        container.appendChild(toast);
                                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    form.setAttribute('data-failed-route', 'true');
                                                } catch (err) {}
                                            });
                                        })();
                                    </script>
                                @endpush
                                @csrf
                                <div class="card-body">
                                    <div class="{{ VC::RW }}">
                                        @foreach ($emailTemplates as $emailTemplate)
                                            <div class="col-lg-4 col-md-6 col-sm-6 {{ VC::FM_G }}">
                                                <div class="{{ VC::LGRP }}">
                                                    <div class="{{ VC::LGI }} form-switch form-switch-right">
                                                        <label class="{{ VC::FM_LB }}" style="margin-left:5%;">{{ $emailTemplate->name }}</label>
                                                        @php
                                                            $emailTemplateId                      = $emailTemplate->template->id;
                                                            $emailTemplateCheckboxId              = 'email_template_' . $emailTemplateId;
                                                            $emailTemplateStatusLanguageUrl       = $emailStatusLanguageResolvedName
                                                                ? route($emailStatusLanguageResolvedName, [$emailTemplateId])
                                                                : '#';
                                                            $emailTemplateStatusLanguageGuardMsg  = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::EMLS,
                                                                'email_template_status_language_route_unavailable'
                                                            ) ?? 'Email template status language route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <input
                                                            class="form-check-input"
                                                            name="{{ $emailTemplate->id }}"
                                                            id="{{ $emailTemplateCheckboxId }}"
                                                            type="checkbox"
                                                            @if ($emailTemplate->template->is_active == 1) checked="checked" @endif
                                                            value="1"
                                                            data-url="{{ $emailTemplateStatusLanguageUrl }}"
                                                            data-guard-msg="{{ $emailTemplateStatusLanguageGuardMsg }}"
                                                        />
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const el = document.getElementById('{{ $emailTemplateCheckboxId }}');
                                                                    if (!el || el.getAttribute('data-change-listener-active') === 'true') return;
                                                                    el.setAttribute('data-change-listener-active', 'true');
                                                                    el.addEventListener('change', e => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url') || '#';
                                                                            if (url !== '#') return;
                                                                            e.preventDefault();
                                                                            el.checked = !el.checked;
                                                                            const msg = el.getAttribute('data-guard-msg') || '# ERROR';
                                                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                            let container = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (hasBootstrap) {
                                                                                const toast = document.createElement('div');
                                                                                toast.className = 'toast';
                                                                                toast.setAttribute('role','alert');
                                                                                toast.setAttribute('aria-live','assertive');
                                                                                toast.setAttribute('aria-atomic','true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toast.appendChild(body);
                                                                                container.appendChild(toast);
                                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                        } catch (err) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                        <label class="form-check-label" for="email_template_{{ $emailTemplate->template->id }}"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <div class="{{ VC::FM_G }}">
                                        <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                    </div>
                                </div>

                                                                                {{--                                                    <input class="form-check-input email-template-checkbox" --}}
                                                    {{--                                                           id="email_template_{{!empty($emailTemplate->template)?$emailTemplate->template->id:''}}" type="checkbox" --}}
                                                    {{--                                                           @if (!empty($emailTemplate->template) ? $emailTemplate->template->is_active : 0 == 1) checked="checked" @endif --}}
                                                    {{--                                                           type="checkbox" --}}
                                                    {{--                                                           value="{{!empty($emailTemplate->template)?$emailTemplate->template->is_active:1}}" --}}
                                                    {{--                                                           data-url="{{route('emails.status.language',[!empty($emailTemplate->template)?$emailTemplate->template->id:''])}}" /> --}}
                                                    {{--                                                    <label class="form-check-label" for="email_template_{{!empty($emailTemplate->template)?$emailTemplate->template->id:''}}"></label> --}}
                            {{ Form::close() }}
                        </div>
                    </div>
                    <div id="offer-letter-settings" class="{{ VC::CD }}">
                        <div class="col-md-12">
                            <div class="card-header {{ VC::DFL_JCB }}">
                                <h5>{{ __('Offer Letter Settings') }}</h5>
                                <div class="{{ VC::DFL . ' ' . VC::JCE }} drp-languages">
                                    <ul class="list-unstyled {{ VC::MB0 }} m-2">
                                        <li class="{{ VC::LNG_DD_IT }}" style="margin-top: -7px;">
                                            <a class="{{ VC::DRP_NO_ARROW }}"
                                            data-bs-toggle="dropdown" href="#" role="button"
                                            aria-haspopup="false" aria-expanded="false" id="dropdownLanguage">
                                                <span class="drp-text hide-mob text-primary me-2">
                                                    {{ ucfirst($offerlangName->full_name) }}
                                                </span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="{{ VC::DRP_MN_DSH_END }}" aria-labelledby="dropdownLanguage">
                                                @php
                                                    $offerLetterLangRouteBase   = ViewsConstants::SET . '.offer_letter.language';
                                                    $offerLetterLangRouteKebab  = Str::kebab($offerLetterLangRouteBase);
                                                    $offerLetterLangRouteName   = Route::has($offerLetterLangRouteBase)
                                                        ? $offerLetterLangRouteBase
                                                        : (Route::has($offerLetterLangRouteKebab) ? $offerLetterLangRouteKebab : null);

                                                    $offerLetterLangGuardMsg    = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::SET,
                                                        'offer_letter_language_route_unavailable'
                                                    ) ?? 'Offer letter language route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                @foreach ($currentLang as $code => $offerlangs)
                                                    @php
                                                        $offerLetterLangParams = [
                                                            'noclangs'     => $noclang,
                                                            'explangs'     => $explang,
                                                            'offerlangs'   => $code,
                                                            'joininglangs' => $joininglang,
                                                        ];
                                                        $offerLetterLangUrl = $offerLetterLangRouteName
                                                            ? route($offerLetterLangRouteName, $offerLetterLangParams)
                                                            : '#';
                                                        $offerLetterLangLinkId = 'offer-letter-language-link-' . $code;
                                                    @endphp
                                                    <a
                                                        id="{{ $offerLetterLangLinkId }}"
                                                        href="{{ $offerLetterLangUrl }}"
                                                        data-url="{{ $offerLetterLangUrl }}"
                                                        data-guard-msg="{{ $offerLetterLangGuardMsg }}"
                                                        class="dropdown-item ms-1 offer-letter-language-link {{ $offerlangs == $code ? 'text-primary' : '' }}"
                                                    >
                                                        {{ ucfirst($offerlangs) }}
                                                    </a>
                                                @endforeach
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            try {
                                                                const links = document.querySelectorAll('.offer-letter-language-link');
                                                                if (!links || links.length === 0) return;
                                                                links.forEach(l => {
                                                                    const active = l.getAttribute('data-listener-active');
                                                                    if (active === 'true') return;
                                                                    l.setAttribute('data-listener-active', 'true');
                                                                    l.addEventListener('click', e => {
                                                                        try {
                                                                            const url = l.getAttribute('data-url') || '#';
                                                                            if (url !== '#') return;
                                                                            e.preventDefault();
                                                                            const msg = l.getAttribute('data-guard-msg') || '# ERROR';
                                                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                            let container = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (hasBootstrap) {
                                                                                const toast = document.createElement('div');
                                                                                toast.className = 'toast';
                                                                                toast.setAttribute('role','alert');
                                                                                toast.setAttribute('aria-live','assertive');
                                                                                toast.setAttribute('aria-atomic','true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toast.appendChild(body);
                                                                                container.appendChild(toast);
                                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            l.setAttribute('data-failed-route', 'true');
                                                                        } catch (err) {}
                                                                    });
                                                                });
                                                            } catch (err) {}
                                                        })();
                                                    </script>
                                                @endpush
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-header card-body">
                                            <div class="{{ VC::RW }} {{ VC::TXS }}">
                                                <div class="{{ VC::RW }}">
                                                    <p class="col-4">{{ __('Applicant Name') }} :
                                                        <span class="pull-end text-primary">{applicant_name}</span></p>
                                                    <p class="col-4">{{ __('Company Name') }} :
                                                        <span class="pull-right text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{ __('Job title') }} :
                                                        <span class="pull-right text-primary">{job_title}</span></p>
                                                    <p class="col-4">{{ __('Job type') }} :
                                                        <span class="pull-right text-primary">{job_type}</span></p>
                                                    <p class="col-4">{{ __('Proposed Start Date') }} :
                                                        <span class="pull-right text-primary">{start_date}</span></p>
                                                    <p class="col-4">{{ __('Working Location') }} :
                                                        <span class="pull-right text-primary">{workplace_location}</span></p>
                                                    <p class="col-4">{{ __('Days Of Week') }} :
                                                        <span class="pull-right text-primary">{days_of_week}</span></p>
                                                    <p class="col-4">{{ __('Salary') }} :
                                                        <span class="pull-right text-primary">{salary}</span></p>
                                                    <p class="col-4">{{ __('Salary Type') }} :
                                                        <span class="pull-right text-primary">{salary_type}</span></p>
                                                    <p class="col-4">{{ __('Salary Duration') }} :
                                                        <span class="pull-end text-primary">{salary_duration}</span></p>
                                                    <p class="col-4">{{ __('Offer Expiration Date') }} :
                                                        <span class="pull-right text-primary">{offer_expiration_date}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                @php
                                    $offerLetterUpdateBaseName              = 'offer_letter.update';
                                    $offerLetterUpdateKebabName             = Str::kebab($offerLetterUpdateBaseName);
                                    $offerLetterUpdateResolvedName          = Route::has($offerLetterUpdateBaseName)
                                        ? $offerLetterUpdateBaseName
                                        : (Route::has($offerLetterUpdateKebabName) ? $offerLetterUpdateKebabName : null);
                                    $offerLetterUpdateRouteArray            = $offerLetterUpdateResolvedName ? [$offerLetterUpdateResolvedName, $offerlang] : ['#'];
                                    $offerLetterUpdateUrl                   = $offerLetterUpdateResolvedName ? route($offerLetterUpdateResolvedName, $offerlang) : '#';
                                    $offerLetterUpdateGuardMsg              = Utility::fetchLinkMessage($lang, ViewsConstants::SET, 'offer_letter_update_route_unavailable') ?? 'Offer letter update route is unavailable. Please contact technical support or your domain administrator.';
                                    $offerLetterUpdateFormId                = 'offer-letter-update-form-' . $offerlang;
                                @endphp
                                {!! Form::open([
                                    'route'          => $offerLetterUpdateRouteArray,
                                    'method'         => 'post',
                                    'id'             => $offerLetterUpdateFormId,
                                    'data-url'       => $offerLetterUpdateUrl,
                                    'data-guard-msg' => $offerLetterUpdateGuardMsg
                                ]) !!}
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const form = document.getElementById('{{ $offerLetterUpdateFormId }}');
                                                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                form.setAttribute('data-listener-active', 'true');
                                                form.addEventListener('submit', e => {
                                                    try {
                                                        const url = form.getAttribute('data-url') || '#';
                                                        const action = form.getAttribute('action') || '#';
                                                        if (url !== '#' || action !== '#') return;
                                                        e.preventDefault();
                                                        const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                        let container = document.getElementById('toast-container');
                                                        if (!container) {
                                                            container = document.createElement('div');
                                                            container.id = 'toast-container';
                                                            document.body.appendChild(container);
                                                        }
                                                        if (hasBootstrap) {
                                                            const toast = document.createElement('div');
                                                            toast.className = 'toast';
                                                            toast.setAttribute('role','alert');
                                                            toast.setAttribute('aria-live','assertive');
                                                            toast.setAttribute('aria-atomic','true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
                                                            toast.appendChild(body);
                                                            container.appendChild(toast);
                                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                                        } else {
                                                            alert(msg);
                                                        }
                                                        form.setAttribute('data-failed-route', 'true');
                                                    } catch (err) {}
                                                });
                                            })();
                                        </script>
                                    @endpush
                                    <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                                        {{ Form::label('content', __(' Format'), ['class' => VC::FM_LB . ' text-dark']) }}
                                        <textarea name="content" class="summernote-simple0 summernote-simple">{!! isset($currOfferletterLang->content) ? $currOfferletterLang->content : '' !!}</textarea>
                                    </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                    <div id="joining-letter-settings" class="{{ VC::CD }}">
                        <div class="col-md-12">
                            <div class="card-header {{ VC::DFL_JCB }}">
                                <h5>{{ __('Joining Letter Settings') }}</h5>
                                <div class="{{ VC::DFL . ' ' . VC::JCE }} drp-languages">
                                    <ul class="list-unstyled {{ VC::MB0 }} m-2">
                                        <li class="{{ VC::LNG_DD_IT }}" style="margin-top: -7px;">
                                            <a class="{{ VC::DRP_NO_ARROW }}"
                                            data-bs-toggle="dropdown" href="#" role="button"
                                            aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                                <span class="drp-text hide-mob text-primary me-2">
                                                    {{ ucfirst($joininglangName->full_name) }}
                                                </span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="{{ VC::DRP_MN_DSH_END }}" aria-labelledby="dropdownLanguage1">
                                                @php
                                                    $joiningLetterLangBaseName  = ViewsConstants::SET . '.joining_letter.language';
                                                    $joiningLetterLangKebabName = Str::kebab($joiningLetterLangBaseName);
                                                    $joiningLetterLangRouteName = Route::has($joiningLetterLangBaseName)
                                                        ? $joiningLetterLangBaseName
                                                        : (Route::has($joiningLetterLangKebabName) ? $joiningLetterLangKebabName : null);
                                                    $joiningLetterLangGuardMsg  = Utility::fetchLinkMessage($lang, ViewsConstants::SET, 'joining_letter_language_route_unavailable') ?? 'Joining letter language route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                @foreach ($currentLang as $code => $joininglangs)
                                                    @php
                                                        $joiningLetterParams = [
                                                            'noclangs'     => $noclang,
                                                            'explangs'     => $explang,
                                                            'offerlangs'   => $offerlang,
                                                            'joininglangs' => $code
                                                        ];
                                                        $joiningLetterLangUrl = $joiningLetterLangRouteName ? route($joiningLetterLangRouteName, $joiningLetterParams) : '#';
                                                        $joiningLetterLinkId  = 'joining-letter-language-link-' . $code;
                                                    @endphp
                                                    <a
                                                        id="{{ $joiningLetterLinkId }}"
                                                        href="{{ $joiningLetterLangUrl }}"
                                                        data-url="{{ $joiningLetterLangUrl }}"
                                                        data-guard-msg="{{ $joiningLetterLangGuardMsg }}"
                                                        class="dropdown-item joining-letter-language-link {{ $joininglangs == $code ? 'text-primary' : '' }}"
                                                    >
                                                        {{ ucfirst($joininglangs) }}
                                                    </a>
                                                @endforeach
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            try {
                                                                const links = document.querySelectorAll('.joining-letter-language-link');
                                                                if (!links || links.length === 0) return;
                                                                links.forEach(l => {
                                                                    if (l.getAttribute('data-listener-active') === 'true') return;
                                                                    l.setAttribute('data-listener-active', 'true');
                                                                    l.addEventListener('click', e => {
                                                                        try {
                                                                            const url = l.getAttribute('data-url') || '#';
                                                                            if (url !== '#') return;
                                                                            e.preventDefault();
                                                                            const msg = l.getAttribute('data-guard-msg') || '# ERROR';
                                                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                            let container = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (hasBootstrap) {
                                                                                const toast = document.createElement('div');
                                                                                toast.className = 'toast';
                                                                                toast.setAttribute('role','alert');
                                                                                toast.setAttribute('aria-live','assertive');
                                                                                toast.setAttribute('aria-atomic','true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toast.appendChild(body);
                                                                                container.appendChild(toast);
                                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            l.setAttribute('data-failed-route', 'true');
                                                                        } catch (err) {}
                                                                    });
                                                                });
                                                            } catch (err) {}
                                                        })();
                                                    </script>
                                                @endpush
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-header card-body">
                                            <div class="{{ VC::RW }} {{ VC::TXS }}">
                                                <div class="{{ VC::RW }}">
                                                    <p class="col-4">{{ __('Applicant Name') }} :
                                                        <span class="pull-end text-primary">{date}</span></p>
                                                    <p class="col-4">{{ __('Company Name') }} :
                                                        <span class="pull-right text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{ __('Employee Name') }} :
                                                        <span class="pull-right text-primary">{employee_name}</span></p>
                                                    <p class="col-4">{{ __('Address') }} :
                                                        <span class="pull-right text-primary">{address}</span></p>
                                                    <p class="col-4">{{ __('Designation') }} :
                                                        <span class="pull-right text-primary">{designation}</span></p>
                                                    <p class="col-4">{{ __('Start Date') }} :
                                                        <span class="pull-right text-primary">{start_date}</span></p>
                                                    <p class="col-4">{{ __('Branch') }} :
                                                        <span class="pull-right text-primary">{branch}</span></p>
                                                    <p class="col-4">{{ __('Start Time') }} :
                                                        <span class="pull-end text-primary">{start_time}</span></p>
                                                    <p class="col-4">{{ __('End Time') }} :
                                                        <span class="pull-right text-primary">{end_time}</span></p>
                                                    <p class="col-4">{{ __('Number of Hours') }} :
                                                        <span class="pull-right text-primary">{total_hours}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                @php
                                    $joiningLetterUpdateBaseName              = 'joining_letter.update';
                                    $joiningLetterUpdateKebabName             = Str::kebab($joiningLetterUpdateBaseName);
                                    $joiningLetterUpdateResolvedName          = Route::has($joiningLetterUpdateBaseName)
                                        ? $joiningLetterUpdateBaseName
                                        : (Route::has($joiningLetterUpdateKebabName) ? $joiningLetterUpdateKebabName : null);
                                    $joiningLetterUpdateRouteArray            = $joiningLetterUpdateResolvedName ? [$joiningLetterUpdateResolvedName, $joininglang] : ['#'];
                                    $joiningLetterUpdateUrl                   = $joiningLetterUpdateResolvedName ? route($joiningLetterUpdateResolvedName, $joininglang) : '#';
                                    $joiningLetterUpdateGuardMsg              = Utility::fetchLinkMessage($lang, ViewsConstants::SET, 'joining_letter_update_route_unavailable') ?? 'Joining letter update route is unavailable. Please contact technical support or your domain administrator.';
                                    $joiningLetterUpdateFormId                = 'joining-letter-update-form-' . $joininglang;
                                @endphp
                                {!! Form::open([
                                    'route'          => $joiningLetterUpdateRouteArray,
                                    'method'         => 'post',
                                    'id'             => $joiningLetterUpdateFormId,
                                    'data-url'       => $joiningLetterUpdateUrl,
                                    'data-guard-msg' => $joiningLetterUpdateGuardMsg
                                ]) !!}
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const form = document.getElementById('{{ $joiningLetterUpdateFormId }}');
                                                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                form.setAttribute('data-listener-active', 'true');
                                                form.addEventListener('submit', e => {
                                                    try {
                                                        const url = form.getAttribute('data-url') || '#';
                                                        const action = form.getAttribute('action') || '#';
                                                        if (url !== '#' || action !== '#') return;
                                                        e.preventDefault();
                                                        const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                        let container = document.getElementById('toast-container');
                                                        if (!container) {
                                                            container = document.createElement('div');
                                                            container.id = 'toast-container';
                                                            document.body.appendChild(container);
                                                        }
                                                        if (hasBootstrap) {
                                                            const toast = document.createElement('div');
                                                            toast.className = 'toast';
                                                            toast.setAttribute('role','alert');
                                                            toast.setAttribute('aria-live','assertive');
                                                            toast.setAttribute('aria-atomic','true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
                                                            toast.appendChild(body);
                                                            container.appendChild(toast);
                                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                                        } else {
                                                            alert(msg);
                                                        }
                                                        form.setAttribute('data-failed-route', 'true');
                                                    } catch (err) {}
                                                });
                                            })();
                                        </script>
                                    @endpush
                                    <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                                        {{ Form::label('content', __(' Format'), ['class' => VC::FM_LB . ' text-dark']) }}
                                        <textarea name="content" class="summernote-simple1 summernote-simple">{!! isset($currjoiningletterLang->content) ? $currjoiningletterLang->content : '' !!}</textarea>
                                    </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                    <div id="experience-certificate-settings" class="{{ VC::CD }}">
                        <div class="col-md-12">
                            <div class="card-header {{ VC::DFL_JCB }}">
                                <h5>{{ __('Experience Certificate Settings') }}</h5>
                                <div class="{{ VC::DFL . ' ' . VC::JCE }} drp-languages">
                                    <ul class="list-unstyled {{ VC::MB0 }} m-2">
                                        <li class="{{ VC::LNG_DD_IT }}" style="margin-top: -7px;">
                                            <a class="{{ VC::DRP_NO_ARROW }}"
                                            data-bs-toggle="dropdown" href="#" role="button"
                                            aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                                <span class="drp-text hide-mob text-primary me-2">
                                                    {{ ucfirst($explangName->full_name) }}
                                                </span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="{{ VC::DRP_MN_DSH_END }}" aria-labelledby="dropdownLanguage1">
                                                @php
                                                    $experienceCertificateLangBase   = ViewsConstants::SET . '.experience_certificate.language';
                                                    $experienceCertificateLangKebab  = Str::kebab($experienceCertificateLangBase);
                                                    $experienceCertificateLangName   = Route::has($experienceCertificateLangBase)
                                                        ? $experienceCertificateLangBase
                                                        : (Route::has($experienceCertificateLangKebab) ? $experienceCertificateLangKebab : null);
                                                    $experienceCertificateLangGuard  = Utility::fetchLinkMessage($lang, ViewsConstants::SET, 'experience_certificate_language_route_unavailable') ?? 'Experience certificate language route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                @foreach ($currentLang as $code => $explangs)
                                                    @php
                                                        $experienceCertificateParams = [
                                                            'noclangs'     => $noclang,
                                                            'explangs'     => $code,
                                                            'offerlangs'   => $offerlang,
                                                            'joininglangs' => $joininglang
                                                        ];
                                                        $experienceCertificateLangUrl = $experienceCertificateLangName ? route($experienceCertificateLangName, $experienceCertificateParams) : '#';
                                                        $experienceCertificateLinkId  = 'experience-certificate-language-link-' . $code;
                                                    @endphp
                                                    <a
                                                        id="{{ $experienceCertificateLinkId }}"
                                                        href="{{ $experienceCertificateLangUrl }}"
                                                        data-url="{{ $experienceCertificateLangUrl }}"
                                                        data-guard-msg="{{ $experienceCertificateLangGuard }}"
                                                        class="dropdown-item experience-certificate-language-link {{ $explangs == $code ? 'text-primary' : '' }}"
                                                    >
                                                        {{ ucfirst($explangs) }}
                                                    </a>
                                                @endforeach
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            try {
                                                                const links = document.querySelectorAll('.experience-certificate-language-link');
                                                                if (!links || links.length === 0) return;
                                                                links.forEach(l => {
                                                                    if (l.getAttribute('data-listener-active') === 'true') return;
                                                                    l.setAttribute('data-listener-active', 'true');
                                                                    l.addEventListener('click', e => {
                                                                        try {
                                                                            const url = l.getAttribute('data-url') || '#';
                                                                            if (url !== '#') return;
                                                                            e.preventDefault();
                                                                            const msg = l.getAttribute('data-guard-msg') || '# ERROR';
                                                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                            let container = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (hasBootstrap) {
                                                                                const toast = document.createElement('div');
                                                                                toast.className = 'toast';
                                                                                toast.setAttribute('role','alert');
                                                                                toast.setAttribute('aria-live','assertive');
                                                                                toast.setAttribute('aria-atomic','true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toast.appendChild(body);
                                                                                container.appendChild(toast);
                                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            l.setAttribute('data-failed-route', 'true');
                                                                        } catch (err) {}
                                                                    });
                                                                });
                                                            } catch (err) {}
                                                        })();
                                                    </script>
                                                @endpush
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-header card-body">
                                            <div class="{{ VC::RW }} {{ VC::TXS }}">
                                                <div class="{{ VC::RW }}">
                                                    <p class="col-4">{{ __('Company Name') }} :
                                                        <span class="pull-right text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{ __('Employee Name') }} :
                                                        <span class="pull-right text-primary">{employee_name}</span></p>
                                                    <p class="col-4">{{ __('Date of Issuance') }} :
                                                        <span class="pull-right text-primary">{date}</span></p>
                                                    <p class="col-4">{{ __('Designation') }} :
                                                        <span class="pull-right text-primary">{designation}</span></p>
                                                    <p class="col-4">{{ __('Start Date') }} :
                                                        <span class="pull-right text-primary">{start_date}</span></p>
                                                    <p class="col-4">{{ __('Branch') }} :
                                                        <span class="pull-right text-primary">{branch}</span></p>
                                                    <p class="col-4">{{ __('Start Time') }} :
                                                        <span class="pull-end text-primary">{start_time}</span></p>
                                                    <p class="col-4">{{ __('End Time') }} :
                                                        <span class="pull-right text-primary">{end_time}</span></p>
                                                    <p class="col-4">{{ __('Number of Hours') }} :
                                                        <span class="pull-right text-primary">{total_hours}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                @php
                                    $expCertUpdateBaseName                        = 'experience_certificate.update';
                                    $expCertUpdateKebabName                       = Str::kebab($expCertUpdateBaseName);
                                    $expCertUpdateResolvedName                    = Route::has($expCertUpdateBaseName)
                                        ? $expCertUpdateBaseName
                                        : (Route::has($expCertUpdateKebabName) ? $expCertUpdateKebabName : null);
                                    $expCertUpdateRouteArray                      = $expCertUpdateResolvedName
                                        ? [$expCertUpdateResolvedName, $explang]
                                        : ['#'];
                                    $expCertUpdateUrl                             = $expCertUpdateResolvedName
                                        ? route($expCertUpdateResolvedName, $explang)
                                        : '#';
                                    $expCertUpdateGuardMsg                        = Utility::fetchLinkMessage(
                                        $lang,
                                        ViewsConstants::SET,
                                        'experience_certificate_update_route_unavailable'
                                    ) ?? 'Experience certificate update route is unavailable. Please contact technical support or your domain administrator.';
                                    $expCertUpdateFormId                          = 'experience-certificate-update-form-' . $explang;
                                @endphp
                                {!! Form::open([
                                    'route'          => $expCertUpdateRouteArray,
                                    'method'         => 'post',
                                    'id'             => $expCertUpdateFormId,
                                    'data-url'       => $expCertUpdateUrl,
                                    'data-guard-msg' => $expCertUpdateGuardMsg
                                ]) !!}
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const form = document.getElementById('{{ $expCertUpdateFormId }}');
                                                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                form.setAttribute('data-listener-active', 'true');
                                                form.addEventListener('submit', (e) => {
                                                    try {
                                                        const url = form.getAttribute('data-url') || '#';
                                                        const action = form.getAttribute('action') || '#';
                                                        if (url !== '#' || action !== '#') return;
                                                        e.preventDefault();
                                                        const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                        let container = document.getElementById('toast-container');
                                                        if (!container) {
                                                            container = document.createElement('div');
                                                            container.id = 'toast-container';
                                                            document.body.appendChild(container);
                                                        }
                                                        if (hasBootstrap) {
                                                            const toast = document.createElement('div');
                                                            toast.className = 'toast';
                                                            toast.setAttribute('role', 'alert');
                                                            toast.setAttribute('aria-live', 'assertive');
                                                            toast.setAttribute('aria-atomic', 'true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
                                                            toast.appendChild(body);
                                                            container.appendChild(toast);
                                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                                        } else {
                                                            alert(msg);
                                                        }
                                                        form.setAttribute('data-failed-route', 'true');
                                                    } catch (err) {}
                                                });
                                            })();
                                        </script>
                                    @endpush
                                    <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                                        {{ Form::label('content', __(' Format'), ['class' => VC::FM_LB . ' text-dark']) }}
                                        <textarea name="content" class="summernote-simple2 summernote-simple">{!! isset($curr_exp_cetificate_Lang->content) ? $curr_exp_cetificate_Lang->content : '' !!}</textarea>
                                    </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                    <div id="noc-settings" class="{{ VC::CD }}">
                        <div class="col-md-12">
                            <div class="card-header {{ VC::DFL_JCB }}">
                                <h5>{{ __('NOC Settings') }}</h5>
                                <div class="{{ VC::DFL . ' ' . VC::JCE }} drp-languages">
                                    <ul class="list-unstyled {{ VC::MB0 }} m-2">
                                        <li class="{{ VC::LNG_DD_IT }}" style="margin-top: -7px;">
                                            <a class="{{ VC::DRP_NO_ARROW }}"
                                            data-bs-toggle="dropdown" href="#" role="button"
                                            aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                                <span class="drp-text hide-mob text-primary me-2">
                                                    {{ ucfirst($noclangName->full_name) }}
                                                </span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="{{ VC::DRP_MN_DSH_END }}" aria-labelledby="dropdownLanguage1">
                                                @php
                                                    $nocLanguageBaseName   = ViewsConstants::SET . '.noc.language';
                                                    $nocLanguageKebabName  = Str::kebab($nocLanguageBaseName);
                                                    $nocLanguageRouteName  = Route::has($nocLanguageBaseName)
                                                        ? $nocLanguageBaseName
                                                        : (Route::has($nocLanguageKebabName) ? $nocLanguageKebabName : null);
                                                    $nocLanguageGuardMsg   = Utility::fetchLinkMessage($lang, ViewsConstants::SET, 'noc_language_route_unavailable') ?? 'NOC language route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                @foreach ($currentLang as $code => $noclangs)
                                                    @php
                                                        $nocLanguageParams = [
                                                            'noclangs'     => $code,
                                                            'explangs'     => $explang,
                                                            'offerlangs'   => $offerlang,
                                                            'joininglangs' => $joininglang
                                                        ];
                                                        $nocLanguageUrl = $nocLanguageRouteName ? route($nocLanguageRouteName, $nocLanguageParams) : '#';
                                                        $nocLanguageLinkId = 'noc-language-link-' . $code;
                                                    @endphp
                                                    <a
                                                        id="{{ $nocLanguageLinkId }}"
                                                        href="{{ $nocLanguageUrl }}"
                                                        data-url="{{ $nocLanguageUrl }}"
                                                        data-guard-msg="{{ $nocLanguageGuardMsg }}"
                                                        class="dropdown-item noc-language-link {{ $noclangs == $code ? 'text-primary' : '' }}"
                                                    >
                                                        {{ ucfirst($noclangs) }}
                                                    </a>
                                                @endforeach
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            try {
                                                                const links = document.querySelectorAll('.noc-language-link');
                                                                if (!links || links.length === 0) return;
                                                                links.forEach(l => {
                                                                    if (l.getAttribute('data-listener-active') === 'true') return;
                                                                    l.setAttribute('data-listener-active', 'true');
                                                                    l.addEventListener('click', e => {
                                                                        try {
                                                                            const url = l.getAttribute('data-url') || '#';
                                                                            if (url !== '#') return;
                                                                            e.preventDefault();
                                                                            const msg = l.getAttribute('data-guard-msg') || '# ERROR';
                                                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                            let container = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (hasBootstrap) {
                                                                                const toast = document.createElement('div');
                                                                                toast.className = 'toast';
                                                                                toast.setAttribute('role','alert');
                                                                                toast.setAttribute('aria-live','assertive');
                                                                                toast.setAttribute('aria-atomic','true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toast.appendChild(body);
                                                                                container.appendChild(toast);
                                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            l.setAttribute('data-failed-route', 'true');
                                                                        } catch (err) {}
                                                                    });
                                                                });
                                                            } catch (err) {}
                                                        })();
                                                    </script>
                                                @endpush
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-header card-body">
                                            <div class="{{ VC::RW }} {{ VC::TXS }}">
                                                <div class="{{ VC::RW }}">
                                                    <p class="col-4">{{ __('Date') }} :
                                                        <span class="pull-end text-primary">{date}</span></p>
                                                    <p class="col-4">{{ __('Company Name') }} :
                                                        <span class="pull-right text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{ __('Employee Name') }} :
                                                        <span class="pull-right text-primary">{employee_name}</span></p>
                                                    <p class="col-4">{{ __('Designation') }} :
                                                        <span class="pull-right text-primary">{designation}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                @php
                                    $nocUpdateBaseName            = 'noc.update';
                                    $nocUpdateKebabName           = Str::kebab($nocUpdateBaseName);
                                    $nocUpdateResolvedName        = Route::has($nocUpdateBaseName)
                                        ? $nocUpdateBaseName
                                        : (Route::has($nocUpdateKebabName) ? $nocUpdateKebabName : null);
                                    $nocUpdateRouteArray          = $nocUpdateResolvedName ? [$nocUpdateResolvedName, $noclang] : ['#'];
                                    $nocUpdateUrl                 = $nocUpdateResolvedName ? route($nocUpdateResolvedName, $noclang) : '#';
                                    $nocUpdateGuardMsg            = Utility::fetchLinkMessage($lang, ViewsConstants::SET, 'noc_update_route_unavailable') ?? 'NOC update route is unavailable. Please contact technical support or your domain administrator.';
                                    $nocUpdateFormId              = 'noc-update-form-' . $noclang;
                                @endphp
                                {!! Form::open([
                                    'route'          => $nocUpdateRouteArray,
                                    'method'         => 'post',
                                    'id'             => $nocUpdateFormId,
                                    'data-url'       => $nocUpdateUrl,
                                    'data-guard-msg' => $nocUpdateGuardMsg
                                ]) !!}
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const form = document.getElementById('{{ $nocUpdateFormId }}');
                                                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                form.setAttribute('data-listener-active', 'true');
                                                form.addEventListener('submit', e => {
                                                    try {
                                                        const url = form.getAttribute('data-url') || '#';
                                                        const action = form.getAttribute('action') || '#';
                                                        if (url !== '#' || action !== '#') return;
                                                        e.preventDefault();
                                                        const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                        let container = document.getElementById('toast-container');
                                                        if (!container) {
                                                            container = document.createElement('div');
                                                            container.id = 'toast-container';
                                                            document.body.appendChild(container);
                                                        }
                                                        if (hasBootstrap) {
                                                            const toast = document.createElement('div');
                                                            toast.className = 'toast';
                                                            toast.setAttribute('role','alert');
                                                            toast.setAttribute('aria-live','assertive');
                                                            toast.setAttribute('aria-atomic','true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
                                                            toast.appendChild(body);
                                                            container.appendChild(toast);
                                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                                        } else {
                                                            alert(msg);
                                                        }
                                                        form.setAttribute('data-failed-route', 'true');
                                                    } catch (err) {}
                                                });
                                            })();
                                        </script>
                                    @endpush
                                    <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                                        {{ Form::label('content', __(' Format'), ['class' => VC::FM_LB . ' text-dark']) }}
                                        <textarea name="content" class="summernote-simple3 summernote-simple">{!! isset($currnocLang->content) ? $currnocLang->content : '' !!}</textarea>
                                    </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                    <div id="google-calendar" class="card">
                        <div class="col-md-12">
                            @php
                                $settingsGoogleCalendarBaseRouteName           = ViewsConstants::SET . '.google.calendar';
                                $settingsGoogleCalendarKebabRouteName          = Str::kebab($settingsGoogleCalendarBaseRouteName);
                                $settingsGoogleCalendarResolvedRouteName       = Route::has($settingsGoogleCalendarBaseRouteName)
                                    ? $settingsGoogleCalendarBaseRouteName
                                    : (Route::has($settingsGoogleCalendarKebabRouteName) ? $settingsGoogleCalendarKebabRouteName : null);
                                $settingsGoogleCalendarUrl                     = $settingsGoogleCalendarResolvedRouteName
                                    ? route($settingsGoogleCalendarResolvedRouteName)
                                    : '#';
                                $settingsGoogleCalendarGuardMsg                = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::SET,
                                    'settings_google_calendar_route_unavailable'
                                ) ?? 'Settings Google Calendar route is unavailable. Please contact technical support or your domain administrator.';
                                $settingsGoogleCalendarFormId                  = 'settings-google-calendar-form';
                            @endphp
                            {!! Form::open([
                                'url'            => $settingsGoogleCalendarUrl,
                                'enctype'        => 'multipart/form-data',
                                'id'             => $settingsGoogleCalendarFormId,
                                'data-url'       => $settingsGoogleCalendarUrl,
                                'data-guard-msg' => $settingsGoogleCalendarGuardMsg
                            ]) !!}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer>
                                        (() => {
                                            const form = document.getElementById('{{ $settingsGoogleCalendarFormId }}');
                                            if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                            form.setAttribute('data-listener-active', 'true');
                                            form.addEventListener('submit', e => {
                                                try {
                                                    const dataUrl = form.getAttribute('data-url') || '#';
                                                    const action  = form.getAttribute('action') || '#';
                                                    if (dataUrl !== '#' || action !== '#') return;
                                                    e.preventDefault();
                                                    const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                                                    const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (hasBootstrap) {
                                                        const toast = document.createElement('div');
                                                        toast.className = 'toast';
                                                        toast.setAttribute('role','alert');
                                                        toast.setAttribute('aria-live','assertive');
                                                        toast.setAttribute('aria-atomic','true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toast.appendChild(body);
                                                        container.appendChild(toast);
                                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    form.setAttribute('data-failed-route', 'true');
                                                } catch (err) {}
                                            });
                                        })();
                                    </script>
                                @endpush
                                <div class="card-header">
                                    <div class="{{ VC::RW }}">
                                        <div class="col-6">
                                            <h5 class="mb-2">{{ __('Google Calendar Settings') }}</h5>
                                        </div>
                                        <div class="col switch-width text-end">
                                            <div class="{{ VC::FM_G }} {{ VC::MB0 }}">
                                                <div class="{{ VC::CST_CTL }} custom-switch">
                                                    <input type="checkbox"
                                                        name="google_calendar_enable"
                                                        id="google_calendar_enable"
                                                        data-toggle="switchbutton"
                                                        data-onstyle="primary"
                                                        {{ $setting['google_calendar_enable'] == 'on' ? 'checked' : '' }}>
                                                    <label class="{{ VC::CST_LB }}" for="google_calendar_enable"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="{{ VC::RW }}">
                                        <div class="col-lg-6 {{ VC::CM6 }} {{ VC::CS12 }} {{ VC::FM_G }}">
                                            {{ Form::label('Google calendar id', __('Google Calendar Id'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('google_clender_id', !empty($setting['google_clender_id']) ? $setting['google_clender_id'] : '', ['class' => VC::FM_CT, 'placeholder' => 'Google Calendar Id', 'required' => 'required']) }}
                                        </div>

                                        <div class="col-lg-6 {{ VC::CM6 }} {{ VC::CS12 }} {{ VC::FM_G }}">
                                            {{ Form::label('Google calendar json file', __('Google Calendar json File'), ['class' => 'col-form-label']) }}
                                            <input type="file" class="{{ VC::FM_CT }}" name="google_calendar_json_file" id="file">
                                            {{-- {{ Form::text('zoom_secret_key', !empty($setting['zoom_secret_key']) ? $setting['zoom_secret_key'] : '' , ['class'=>'form-control', 'placeholder'=>'Google Calendar json File']) }} --}}
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button class="btn-submit {{ VC::BT_PRM }}" type="submit">
                                        {{ __('Save Changes') }}
                                    </button>
                                </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                    <div id="webhook-settings" class="card">
                        <div class="col-md-12">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5 class="mb-2">{{ __('Webhook Settings') }}</h5>
                                    </div>
                                    @can('create webhook')
                                        <div class="col-6 text-end">
                                            @php
                                                $webhookCreateBaseName              = ViewsConstants::WBH . '.create';
                                                $webhookCreateKebabName             = Str::kebab($webhookCreateBaseName);
                                                $webhookCreateResolvedName          = Route::has($webhookCreateBaseName)
                                                    ? $webhookCreateBaseName
                                                    : (Route::has($webhookCreateKebabName) ? $webhookCreateKebabName : null);
                                                $webhookCreateUrl                   = $webhookCreateResolvedName ? route($webhookCreateResolvedName) : '#';
                                                $webhookCreateGuardMsg              = Utility::fetchLinkMessage($lang, ViewsConstants::WBH, 'webhook_create_route_unavailable')
                                                    ?? 'Webhook create route is unavailable. Please contact technical support or your domain administrator.';
                                                $webhookCreateBtnId                 = 'webhook-create-btn';
                                            @endphp
                                            <a
                                                id="{{ $webhookCreateBtnId }}"
                                                href="{{ $webhookCreateUrl }}"
                                                data-url="{{ $webhookCreateUrl }}"
                                                data-guard-msg="{{ $webhookCreateGuardMsg }}"
                                                data-size="lg"
                                                data-ajax-popup="true"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Create') }}"
                                                data-title="{{ __('Create New Webhook') }}"
                                                class="{{ VC::BT_SM_PM }}"
                                            >
                                                <i class="{{ VC::TI_PLS }}"></i>
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        const btn = document.getElementById('{{ $webhookCreateBtnId }}');
                                                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                        btn.setAttribute('data-listener-active', 'true');
                                                        btn.addEventListener('click', e => {
                                                            try {
                                                                const url = btn.getAttribute('data-url') || '#';
                                                                if (url !== '#') return;
                                                                e.preventDefault();
                                                                const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                let container = document.getElementById('toast-container');
                                                                if (!container) {
                                                                    container = document.createElement('div');
                                                                    container.id = 'toast-container';
                                                                    document.body.appendChild(container);
                                                                }
                                                                if (hasBootstrap) {
                                                                    const toast = document.createElement('div');
                                                                    toast.className = 'toast';
                                                                    toast.setAttribute('role','alert');
                                                                    toast.setAttribute('aria-live','assertive');
                                                                    toast.setAttribute('aria-atomic','true');
                                                                    const body = document.createElement('div');
                                                                    body.className = 'toast-body';
                                                                    body.textContent = msg;
                                                                    toast.appendChild(body);
                                                                    container.appendChild(toast);
                                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                } else {
                                                                    alert(msg);
                                                                }
                                                                btn.setAttribute('data-failed-route', 'true');
                                                            } catch (err) {}
                                                        });
                                                    })();
                                                </script>
                                            @endpush
                                        </div>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Module') }}</th>
                                                <th>{{ __('Url') }}</th>
                                                <th>{{ __('Method') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-style">
                                            @forelse ($webhookSetting as $webhooksetting)
                                                <tr>
                                                    <td>{{ ucwords($webhooksetting->module) }}</td>
                                                    <td>{{ $webhooksetting->url }}</td>
                                                    <td>{{ ucwords($webhooksetting->method) }}</td>
                                                    <td class="Action">
                                                        <span>
                                                            @can(PermissionsConstants::ED_WHK)
                                                                <div class="action-btn bg-primary ms-2">
                                                                    @php
                                                                        $webhookEditBaseName                = ViewsConstants::WBH . '.edit';
                                                                        $webhookEditKebabName               = Str::kebab($webhookEditBaseName);
                                                                        $webhookEditResolvedName            = Route::has($webhookEditBaseName)
                                                                            ? $webhookEditBaseName
                                                                            : (Route::has($webhookEditKebabName) ? $webhookEditKebabName : null);
                                                                        $webhookEditUrl                     = $webhookEditResolvedName
                                                                            ? route($webhookEditResolvedName, $webhooksetting->id)
                                                                            : '#';
                                                                        $webhookEditGuardMsg                = Utility::fetchLinkMessage(
                                                                            $lang,
                                                                            ViewsConstants::WBH,
                                                                            'webhook_edit_route_unavailable'
                                                                        ) ?? 'Webhook edit route is unavailable. Please contact technical support or your domain administrator.';
                                                                        $webhookEditBtnId                   = 'webhook-edit-btn-' . $webhooksetting->id;
                                                                    @endphp
                                                                    <a
                                                                        id="{{ $webhookEditBtnId }}"
                                                                        href="{{ $webhookEditUrl }}"
                                                                        data-url="{{ $webhookEditUrl }}"
                                                                        data-guard-msg="{{ $webhookEditGuardMsg }}"
                                                                        class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                                        data-ajax-popup="true"
                                                                        data-bs-toggle="tooltip"
                                                                        data-size="lg"
                                                                        title="{{ __('Edit') }}"
                                                                        data-title="{{ __('Webhook Edit') }}"
                                                                    >
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                    @push(StacksConstants::ADM_SCR_PG)
                                                                        <script defer>
                                                                            (() => {
                                                                                const btn = document.getElementById('{{ $webhookEditBtnId }}');
                                                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                                btn.setAttribute('data-listener-active', 'true');
                                                                                btn.addEventListener('click', e => {
                                                                                    try {
                                                                                        const url = btn.getAttribute('data-url') || '#';
                                                                                        if (url !== '#') return;
                                                                                        e.preventDefault();
                                                                                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                                        let container = document.getElementById('toast-container');
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = 'toast-container';
                                                                                            document.body.appendChild(container);
                                                                                        }
                                                                                        if (hasBootstrap) {
                                                                                            const toast = document.createElement('div');
                                                                                            toast.className = 'toast';
                                                                                            toast.setAttribute('role','alert');
                                                                                            toast.setAttribute('aria-live','assertive');
                                                                                            toast.setAttribute('aria-atomic','true');
                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = msg;
                                                                                            toast.appendChild(body);
                                                                                            container.appendChild(toast);
                                                                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                        } else {
                                                                                            alert(msg);
                                                                                        }
                                                                                        btn.setAttribute('data-failed-route', 'true');
                                                                                    } catch (err) {}
                                                                                });
                                                                            })();
                                                                        </script>
                                                                    @endpush
                                                                </div>
                                                            @endcan
                                                            @can(PermissionsConstants::DEL_WHK)
                                                                @php
                                                                    $webhookDestroyBaseName                 = 'webhook.destroy';
                                                                    $webhookDestroyKebabName                = Str::kebab($webhookDestroyBaseName);
                                                                    $webhookDestroyResolvedName             = Route::has($webhookDestroyBaseName)
                                                                        ? $webhookDestroyBaseName
                                                                        : (Route::has($webhookDestroyKebabName) ? $webhookDestroyKebabName : null);
                                                                    $webhookDestroyRouteArray               = $webhookDestroyResolvedName
                                                                        ? [$webhookDestroyResolvedName, $webhooksetting->id]
                                                                        : ['#'];
                                                                    $webhookDestroyUrl                      = $webhookDestroyResolvedName
                                                                        ? route($webhookDestroyResolvedName, $webhooksetting->id)
                                                                        : '#';
                                                                    $webhookDestroyGuardMsg                 = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::WBH,
                                                                        'webhook_destroy_route_unavailable'
                                                                    ) ?? 'Webhook destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $webhookDeleteFormId                    = 'delete-form-' . $webhooksetting->id;
                                                                    $webhookDeleteBtnId                     = 'webhook-destroy-btn-' . $webhooksetting->id;
                                                                @endphp
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route'  => $webhookDestroyRouteArray,
                                                                        'id'     => $webhookDeleteFormId
                                                                    ]) !!}
                                                                        <a
                                                                            id="{{ $webhookDeleteBtnId }}"
                                                                            href="{{ $webhookDestroyUrl }}"
                                                                            data-url="{{ $webhookDestroyUrl }}"
                                                                            data-guard-msg="{{ $webhookDestroyGuardMsg }}"
                                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Delete') }}"
                                                                        >
                                                                            <i class="ti ti-trash text-white"></i>
                                                                        </a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            const btn = document.getElementById('{{ $webhookDeleteBtnId }}');
                                                                            if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                            btn.setAttribute('data-listener-active', 'true');
                                                                            btn.addEventListener('click', e => {
                                                                                try {
                                                                                    const url = btn.getAttribute('data-url') || '#';
                                                                                    if (url !== '#') return;
                                                                                    e.preventDefault();
                                                                                    const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                                    const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                                    let container = document.getElementById('toast-container');
                                                                                    if (!container) {
                                                                                        container = document.createElement('div');
                                                                                        container.id = 'toast-container';
                                                                                        document.body.appendChild(container);
                                                                                    }
                                                                                    if (hasBootstrap) {
                                                                                        const toast = document.createElement('div');
                                                                                        toast.className = 'toast';
                                                                                        toast.setAttribute('role','alert');
                                                                                        toast.setAttribute('aria-live','assertive');
                                                                                        toast.setAttribute('aria-atomic','true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
                                                                                        toast.appendChild(body);
                                                                                        container.appendChild(toast);
                                                                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                    } else {
                                                                                        alert(msg);
                                                                                    }
                                                                                    btn.setAttribute('data-failed-route', 'true');
                                                                                } catch (err) {}
                                                                            });
                                                                        })();
                                                                    </script>
                                                                @endpush
                                                            @endcan
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="text-center">
                                                    <td colspan="4">{{ __('No Data Found.!') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="ip-restriction-settings" class="{{ VC::CD }}">
                        <div class="col-md-12">
                            <div class="card-header">
                                <div class="{{ VC::RW }}">
                                    <div class="col-6">
                                        <h5 class="mb-2">{{ __('IP Restriction Settings') }}</h5>
                                    </div>
                                    @can('create webhook')
                                        <div class="col-6 text-end">
                                            @php
                                                $systemIpCreateBaseName             = ViewsConstants::SYS . '.ip.create';
                                                $systemIpCreateKebabName            = Str::kebab($systemIpCreateBaseName);
                                                $systemIpCreateResolvedName         = Route::has($systemIpCreateBaseName)
                                                    ? $systemIpCreateBaseName
                                                    : (Route::has($systemIpCreateKebabName) ? $systemIpCreateKebabName : null);
                                                $systemIpCreateUrl                  = $systemIpCreateResolvedName
                                                    ? route($systemIpCreateResolvedName)
                                                    : '#';
                                                $systemIpCreateGuardMsg             = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::SYS,
                                                    'system_ip_create_route_unavailable'
                                                ) ?? 'System IP create route is unavailable. Please contact technical support or your domain administrator.';
                                                $systemIpCreateBtnId                = 'system-ip-create-btn';
                                            @endphp
                                            <a
                                                id="{{ $systemIpCreateBtnId }}"
                                                href="{{ $systemIpCreateUrl }}"
                                                data-url="{{ $systemIpCreateUrl }}"
                                                data-guard-msg="{{ $systemIpCreateGuardMsg }}"
                                                data-size="md"
                                                data-ajax-popup="true"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Create') }}"
                                                data-title="{{ __('Create New IP') }}"
                                                class="{{ VC::BT_SM_PM }}"
                                            >
                                                <i class="{{ VC::TI_PLS }} {{ VC::TXT_WT }}"></i>
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        const btn = document.getElementById('{{ $systemIpCreateBtnId }}');
                                                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                        btn.setAttribute('data-listener-active', 'true');
                                                        btn.addEventListener('click', e => {
                                                            try {
                                                                const url = btn.getAttribute('data-url') || '#';
                                                                if (url !== '#') return;
                                                                e.preventDefault();
                                                                const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                let container = document.getElementById('toast-container');
                                                                if (!container) {
                                                                    container = document.createElement('div');
                                                                    container.id = 'toast-container';
                                                                    document.body.appendChild(container);
                                                                }
                                                                if (hasBootstrap) {
                                                                    const toast = document.createElement('div');
                                                                    toast.className = 'toast';
                                                                    toast.setAttribute('role','alert');
                                                                    toast.setAttribute('aria-live','assertive');
                                                                    toast.setAttribute('aria-atomic','true');
                                                                    const body = document.createElement('div');
                                                                    body.className = 'toast-body';
                                                                    body.textContent = msg;
                                                                    toast.appendChild(body);
                                                                    container.appendChild(toast);
                                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                } else {
                                                                    alert(msg);
                                                                }
                                                                btn.setAttribute('data-failed-route', 'true');
                                                            } catch (err) {}
                                                        });
                                                    })();
                                                </script>
                                            @endpush
                                        </div>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="{{ VC::TB }}">
                                        <thead>
                                            <tr>
                                                <th class="w-75">{{ __('IP') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-style">
                                            @forelse ($ips as $ip)
                                                <tr>
                                                    <td>{{ $ip->ip }}</td>
                                                    <td class="Action">
                                                        <span>
                                                            @can(PermissionsConstants::ED_WHK)
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    @php
                                                                        $systemIpEditBaseName         = ViewsConstants::SYS . '.ip.edit';
                                                                        $systemIpEditKebabName        = Str::kebab($systemIpEditBaseName);
                                                                        $systemIpEditResolvedName     = Route::has($systemIpEditBaseName)
                                                                            ? $systemIpEditBaseName
                                                                            : (Route::has($systemIpEditKebabName) ? $systemIpEditKebabName : null);
                                                                        $systemIpEditUrl              = $systemIpEditResolvedName ? route($systemIpEditResolvedName, $ip->id) : '#';
                                                                        $systemIpEditGuardMsg         = Utility::fetchLinkMessage($lang, ViewsConstants::SYS, 'system_ip_edit_route_unavailable')
                                                                            ?? 'System IP edit route is unavailable. Please contact technical support or your domain administrator.';
                                                                        $systemIpEditBtnId            = 'system-ip-edit-btn-' . $ip->id;
                                                                    @endphp
                                                                    <a
                                                                        id="{{ $systemIpEditBtnId }}"
                                                                        href="{{ $systemIpEditUrl }}"
                                                                        data-url="{{ $systemIpEditUrl }}"
                                                                        data-guard-msg="{{ $systemIpEditGuardMsg }}"
                                                                        class="{{ VC::BT_SM_FL_CT }}"
                                                                        data-ajax-popup="true"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Edit') }}"
                                                                        data-title="{{ __('IP Edit') }}"
                                                                    >
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                    @push(StacksConstants::ADM_SCR_PG)
                                                                        <script defer>
                                                                            (() => {
                                                                                const btn = document.getElementById('{{ $systemIpEditBtnId }}');
                                                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                                btn.setAttribute('data-listener-active', 'true');
                                                                                btn.addEventListener('click', e => {
                                                                                    try {
                                                                                        const url = btn.getAttribute('data-url') || '#';
                                                                                        if (url !== '#') return;
                                                                                        e.preventDefault();
                                                                                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                                        let container = document.getElementById('toast-container');
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = 'toast-container';
                                                                                            document.body.appendChild(container);
                                                                                        }
                                                                                        if (hasBootstrap) {
                                                                                            const toast = document.createElement('div');
                                                                                            toast.className = 'toast';
                                                                                            toast.setAttribute('role','alert');
                                                                                            toast.setAttribute('aria-live','assertive');
                                                                                            toast.setAttribute('aria-atomic','true');
                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = msg;
                                                                                            toast.appendChild(body);
                                                                                            container.appendChild(toast);
                                                                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                        } else {
                                                                                            alert(msg);
                                                                                        }
                                                                                        btn.setAttribute('data-failed-route', 'true');
                                                                                    } catch (err) {}
                                                                                });
                                                                            })();
                                                                        </script>
                                                                    @endpush
                                                                </div>
                                                            @endcan
                                                            @can(PermissionsConstants::DEL_WHK)
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    @php
                                                                        $systemIpDestroyBaseName            = ViewsConstants::SYS . '.ip.destroy';
                                                                        $systemIpDestroyKebabName           = Str::kebab($systemIpDestroyBaseName);
                                                                        $systemIpDestroyResolvedName        = Route::has($systemIpDestroyBaseName)
                                                                            ? $systemIpDestroyBaseName
                                                                            : (Route::has($systemIpDestroyKebabName) ? $systemIpDestroyKebabName : null);
                                                                        $systemIpDestroyRouteArray          = $systemIpDestroyResolvedName
                                                                            ? [$systemIpDestroyResolvedName, $ip->id]
                                                                            : ['#'];
                                                                        $systemIpDestroyUrl                 = $systemIpDestroyResolvedName
                                                                            ? route($systemIpDestroyResolvedName, $ip->id)
                                                                            : '#';
                                                                        $systemIpDestroyGuardMsg            = Utility::fetchLinkMessage($lang, ViewsConstants::SYS, 'system_ip_destroy_route_unavailable')
                                                                            ?? 'System IP destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                                        $systemIpDeleteFormId               = 'delete-form-' . $ip->id;
                                                                        $systemIpDeleteBtnId                = 'system-ip-destroy-btn-' . $ip->id;
                                                                    @endphp
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route'  => $systemIpDestroyRouteArray,
                                                                        'id'     => $systemIpDeleteFormId
                                                                    ]) !!}
                                                                        <a
                                                                            id="{{ $systemIpDeleteBtnId }}"
                                                                            href="{{ $systemIpDestroyUrl }}"
                                                                            data-url="{{ $systemIpDestroyUrl }}"
                                                                            data-guard-msg="{{ $systemIpDestroyGuardMsg }}"
                                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Delete') }}"
                                                                        >
                                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                        </a>
                                                                    {!! Form::close() !!}
                                                                    @push(StacksConstants::ADM_SCR_PG)
                                                                        <script defer>
                                                                            (() => {
                                                                                const btn = document.getElementById('{{ $systemIpDeleteBtnId }}');
                                                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                                btn.setAttribute('data-listener-active', 'true');
                                                                                btn.addEventListener('click', (e) => {
                                                                                    try {
                                                                                        const url = btn.getAttribute('data-url') || '#';
                                                                                        if (url !== '#') return;
                                                                                        e.preventDefault();
                                                                                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                                        let container = document.getElementById('toast-container');
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = 'toast-container';
                                                                                            document.body.appendChild(container);
                                                                                        }
                                                                                        if (hasBootstrap) {
                                                                                            const toast = document.createElement('div');
                                                                                            toast.className = 'toast';
                                                                                            toast.setAttribute('role', 'alert');
                                                                                            toast.setAttribute('aria-live', 'assertive');
                                                                                            toast.setAttribute('aria-atomic', 'true');
                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = msg;
                                                                                            toast.appendChild(body);
                                                                                            container.appendChild(toast);
                                                                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                        } else {
                                                                                            alert(msg);
                                                                                        }
                                                                                        btn.setAttribute('data-failed-route', 'true');
                                                                                    } catch (err) {}
                                                                                });
                                                                            })();
                                                                        </script>
                                                                    @endpush
                                                                </div>
                                                            @endcan
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="text-center">
                                                    <td colspan="4">{{ __('No Data Found.!') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
