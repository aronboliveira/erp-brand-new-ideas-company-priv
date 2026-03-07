@php
	use App\Config\Constants\{
		BillsConstants,
		ExtendingLayoutsConstants,
		SettingsConstants,
		StacksConstants,
		ViewClassNamesConstants as VC,
		ViewsConstants,
		YieldingConstants
	};
	use App\Models\Utility;
	use Illuminate\Support\Facades\{Log,Route};
	$data ??= [];
	$logo ??= '';
	$company_logo ??= '';
	$company_favicon ??= '';
	$lang ??= '';
	try {
		$data = Utility::prepareCommonViewData() ?: [];
		$logo = $data[SettingsConstants::LOGO] ?? '';
		$company_logo = Utility::getValByName(SettingsConstants::CPN_LG) ?: '';
		$company_favicon = $data[SettingsConstants::FAV_ICN] ?? '';
		$lang = Utility::getValByName(SettingsConstants::DEF_LNG) ?: '';
	} catch (\Error $e) {
		Log::error(
			'Error preparing bill layout view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception preparing bill layout view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable preparing bill layout view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	}
    $data = Utility::fallbackSettings($data);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Settings')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Print-Settings')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            ar:{scrollspy_unavailable:"تعذّر تفعيل ScrollSpy",invoice_preview_unavailable:"تعذّر عرض معاينة الفاتورة",proposal_preview_unavailable:"تعذّر عرض معاينة المقترح",bill_preview_unavailable:"تعذّر عرض معاينة الفاتورة",invoice_logo_unavailable:"تعذّر معاينة شعار الفاتورة",proposal_logo_unavailable:"تعذّر معاينة شعار المقترح",bill_logo_unavailable:"تعذّر معاينة شعار الفاتورة"},
            da:{scrollspy_unavailable:"Kunne ikke aktivere ScrollSpy",invoice_preview_unavailable:"Kunne ikke indlæse fakturaforhåndsvisning",proposal_preview_unavailable:"Kunne ikke indlæse tilbudsforhåndsvisning",bill_preview_unavailable:"Kunne ikke indlæse regningsforhåndsvisning",invoice_logo_unavailable:"Kunne ikke forhåndsvise fakturalogo",proposal_logo_unavailable:"Kunne ikke forhåndsvise tilbudslogo",bill_logo_unavailable:"Kunne ikke forhåndsvise regningslogo"},
            de:{scrollspy_unavailable:"ScrollSpy konnte nicht aktiviert werden",invoice_preview_unavailable:"Rechnungsvorschau konnte nicht geladen werden",proposal_preview_unavailable:"Angebotsvorschau konnte nicht geladen werden",bill_preview_unavailable:"Rechnungsvorschau konnte nicht geladen werden",invoice_logo_unavailable:"Rechnungslogo konnte nicht angezeigt werden",proposal_logo_unavailable:"Angebotslogo konnte nicht angezeigt werden",bill_logo_unavailable:"Rechnungslogo konnte nicht angezeigt werden"},
            en:{scrollspy_unavailable:"Cannot enable ScrollSpy",invoice_preview_unavailable:"Cannot load invoice preview",proposal_preview_unavailable:"Cannot load proposal preview",bill_preview_unavailable:"Cannot load bill preview",invoice_logo_unavailable:"Cannot preview invoice logo",proposal_logo_unavailable:"Cannot preview proposal logo",bill_logo_unavailable:"Cannot preview bill logo"},
            es:{scrollspy_unavailable:"No se puede activar ScrollSpy",invoice_preview_unavailable:"No se puede cargar la vista previa de la factura",proposal_preview_unavailable:"No se puede cargar la vista previa de la propuesta",bill_preview_unavailable:"No se puede cargar la vista previa de la cuenta",invoice_logo_unavailable:"No se puede previsualizar el logo de la factura",proposal_logo_unavailable:"No se puede previsualizar el logo de la propuesta",bill_logo_unavailable:"No se puede previsualizar el logo de la cuenta"},
            fr:{scrollspy_unavailable:"Impossible d’activer ScrollSpy",invoice_preview_unavailable:"Impossible de charger l’aperçu de la facture",proposal_preview_unavailable:"Impossible de charger l’aperçu du devis",bill_preview_unavailable:"Impossible de charger l’aperçu de la note",invoice_logo_unavailable:"Impossible d’afficher l’aperçu du logo de facture",proposal_logo_unavailable:"Impossible d’afficher l’aperçu du logo de devis",bill_logo_unavailable:"Impossible d’afficher l’aperçu du logo de note"},
            he:{scrollspy_unavailable:"לא ניתן להפעיל ScrollSpy",invoice_preview_unavailable:"לא ניתן לטעון תצוגה מקדימה של חשבונית",proposal_preview_unavailable:"לא ניתן לטעון תצוגה מקדימה של הצעה",bill_preview_unavailable:"לא ניתן לטעון תצוגה מקדימה של חשבון",invoice_logo_unavailable:"לא ניתן להציג תצוגה מקדימה של לוגו חשבונית",proposal_logo_unavailable:"לא ניתן להציג תצוגה מקדימה של לוגו הצעה",bill_logo_unavailable:"לא ניתן להציג תצוגה מקדימה של לוגו חשבון"},
            it:{scrollspy_unavailable:"Impossibile abilitare ScrollSpy",invoice_preview_unavailable:"Impossibile caricare l’anteprima fattura",proposal_preview_unavailable:"Impossibile caricare l’anteprima proposta",bill_preview_unavailable:"Impossibile caricare l’anteprima conto",invoice_logo_unavailable:"Impossibile visualizzare l’anteprima del logo fattura",proposal_logo_unavailable:"Impossibile visualizzare l’anteprima del logo proposta",bill_logo_unavailable:"Impossibile visualizzare l’anteprima del logo conto"},
            ja:{scrollspy_unavailable:"ScrollSpy を有効にできません",invoice_preview_unavailable:"請求書プレビューを読み込めません",proposal_preview_unavailable:"提案書プレビューを読み込めません",bill_preview_unavailable:"請求プレビューを読み込めません",invoice_logo_unavailable:"請求書ロゴをプレビューできません",proposal_logo_unavailable:"提案書ロゴをプレビューできません",bill_logo_unavailable:"請求ロゴをプレビューできません"},
            nl:{scrollspy_unavailable:"ScrollSpy kan niet worden ingeschakeld",invoice_preview_unavailable:"Factuurvoorbeeld kan niet laden",proposal_preview_unavailable:"Offertevoorbeeld kan niet laden",bill_preview_unavailable:"Rekeningvoorbeeld kan niet laden",invoice_logo_unavailable:"Voorbeeld factuurlogo mislukt",proposal_logo_unavailable:"Voorbeeld offertelogo mislukt",bill_logo_unavailable:"Voorbeeld rekeninglogo mislukt"},
            pl:{scrollspy_unavailable:"Nie można włączyć ScrollSpy",invoice_preview_unavailable:"Nie można wczytać podglądu faktury",proposal_preview_unavailable:"Nie można wczytać podglądu oferty",bill_preview_unavailable:"Nie można wczytać podglądu rachunku",invoice_logo_unavailable:"Nie można podglądnąć logo faktury",proposal_logo_unavailable:"Nie można podglądnąć logo oferty",bill_logo_unavailable:"Nie można podglądnąć logo rachunku"},
            pt:{scrollspy_unavailable:"Não foi possível ativar o ScrollSpy",invoice_preview_unavailable:"Não foi possível carregar a pré-visualização da fatura",proposal_preview_unavailable:"Não foi possível carregar a pré-visualização da proposta",bill_preview_unavailable:"Não foi possível carregar a pré-visualização da conta",invoice_logo_unavailable:"Não foi possível pré-visualizar o logotipo da fatura",proposal_logo_unavailable:"Não foi possível pré-visualizar o logotipo da proposta",bill_logo_unavailable:"Não foi possível pré-visualizar o logotipo da conta"},
            "pt-br":{scrollspy_unavailable:"Não foi possível ativar o ScrollSpy",invoice_preview_unavailable:"Não foi possível carregar a prévia da fatura",proposal_preview_unavailable:"Não foi possível carregar a prévia da proposta",bill_preview_unavailable:"Não foi possível carregar a prévia do boleto/conta",invoice_logo_unavailable:"Não foi possível pré-visualizar o logo da fatura",proposal_logo_unavailable:"Não foi possível pré-visualizar o logo da proposta",bill_logo_unavailable:"Não foi possível pré-visualizar o logo da conta"},
            ru:{scrollspy_unavailable:"Не удалось включить ScrollSpy",invoice_preview_unavailable:"Не удалось загрузить предпросмотр счета",proposal_preview_unavailable:"Не удалось загрузить предпросмотр предложения",bill_preview_unavailable:"Не удалось загрузить предпросмотр квитанции",invoice_logo_unavailable:"Не удалось показать логотип счета",proposal_logo_unavailable:"Не удалось показать логотип предложения",bill_logo_unavailable:"Не удалось показать логотип квитанции"},
            tr:{scrollspy_unavailable:"ScrollSpy etkinleştirilemedi",invoice_preview_unavailable:"Fatura önizlemesi yüklenemiyor",proposal_preview_unavailable:"Teklif önizlemesi yüklenemiyor",bill_preview_unavailable:"Fiş önizlemesi yüklenemiyor",invoice_logo_unavailable:"Fatura logosu önizlenemiyor",proposal_logo_unavailable:"Teklif logosu önizlenemiyor",bill_logo_unavailable:"Fiş logosu önizlenemiyor"},
            zh:{scrollspy_unavailable:"无法启用 ScrollSpy",invoice_preview_unavailable:"无法加载发票预览",proposal_preview_unavailable:"无法加载提案预览",bill_preview_unavailable:"无法加载账单预览",invoice_logo_unavailable:"无法预览发票徽标",proposal_logo_unavailable:"无法预览提案徽标",bill_logo_unavailable:"无法预览账单徽标"}
        };
Object.keys(t).forEach(
  k =>
    (window.translations[k] = {
      ...(window.translations[k] || {}),
      ...t[k],
    })
);
     
          })();
    </script>
    <script defer>
        (() => {
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        const DATA_LISTENER_ADDED = "data-listener-added";

        const getMsg = (el, key) => {
            let msg = errFb;
            if (
            el?.getAttribute("data-sv-localized") === "true" ||
            el?.getAttribute(dataClientLocalized) === "true"
            ) {
            msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
            let lang = (
                window.sessionStorage.getItem("erp-np-lang") ||
                document.documentElement.lang ||
                "en"
            )
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[key] ||
                el?.getAttribute(dataGuardMsg) ||
                window.translations?.en?.[key] ||
                errFb;
            if (msg !== errFb) {
                el?.setAttribute(dataGuardMsg, msg);
                el?.setAttribute(dataClientLocalized, "true");
            }
            }
            return msg;
        };

        const attachOneTimeFeedback = (el, key, ev = "click") => {
            if (!el || el.getAttribute(DATA_LISTENER_ADDED) === "true") return;
            const handler = () => {
            const text = getMsg(el, key);
            const hasBs =
                document.querySelector('link[href*="bootstrap"]') &&
                window.bootstrap?.Toast;
            if (hasBs) {
                let toast = document.querySelector("#np-error-toast");
                if (!toast) {
                toast = document.createElement("div");
                toast.id = "np-error-toast";
                toast.className = "toast align-items-center text-bg-danger border-0";
                toast.setAttribute("role", "alert");
                toast.setAttribute("aria-live", "assertive");
                toast.setAttribute("aria-atomic", "true");
                toast.innerHTML = `<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>`;
                document.body.appendChild(toast);
                }
                new bootstrap.Toast(toast).show();
            } else {
                alert(text);
            }
            };
            el.addEventListener(ev, handler, { once: true });
            el.setAttribute(DATA_LISTENER_ADDED, "true");
            const mo = new MutationObserver((_, o) => {
            if (!document.body.contains(el)) {
                el.removeEventListener(ev, handler);
                o.disconnect();
            }
            });
            mo.observe(document.body, { childList: true, subtree: true });
        };

        const routeGuard = (element, alt) => {
            const url = element?.getAttribute?.("data-url");
            const href = element?.action ?? element?.href;
            return (
            (!url || url === "#") && (!href || href === "#") && (!alt || alt === "#")
            );
        };

        try {
            if (typeof $ === "undefined") {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");
                return;
            }

            try {
            if (window.bootstrap?.ScrollSpy) {
                new bootstrap.ScrollSpy(document.body, {
                target: "#useradd-sidenav",
                offset: 300,
                });
            } else {
                attachOneTimeFeedback(document.body, "scrollspy_unavailable", "click");
            }
            } catch {
            attachOneTimeFeedback(document.body, "scrollspy_unavailable", "click");
            }

            const setFrameSrc = (frameSel, base, templateSel, colorSel, key) => {
            try {
                const template = $(templateSel).val() ?? "";
                const color = $(colorSel + ":checked").val() ?? "";
                const $frame = $(frameSel);
                const preview = `${base}/${template}/${color}`;
                if (!$frame.length || routeGuard($frame.get(0), preview)) {
                attachOneTimeFeedback($frame.get(0) || document.body, key, "click");
                return;
                }
                $frame.attr("src", preview);
            } catch {
                attachOneTimeFeedback(document.body, key, "click");
            }
            };

            $(document).on(
            "change",
            "select[name='invoice_template'], input[name='invoice_color']",
            function () {
                setFrameSrc(
                "#invoice_frame",
                "{{ url('/invoices/preview') }}",
                "select[name='invoice_template']",
                "input[name='invoice_color']",
                "invoice_preview_unavailable"
                );
            }
            );

            $(document).on(
            "change",
            "select[name='proposal_template'], input[name='proposal_color']",
            function () {
                setFrameSrc(
                "#proposal_frame",
                "{{ url('/'.ViewsConstants::PPS.'/preview') }}",
                "select[name='proposal_template']",
                "input[name='proposal_color']",
                "proposal_preview_unavailable"
                );
            }
            );

            $(document).on(
            "change",
            "select[name='{{ BillsConstants::COL_POS_TMP }}'], input[name='bill_color']",
            function () {
                setFrameSrc(
                "#bill_frame",
                "{{ url('/bill/preview') }}",
                "select[name='{{ BillsConstants::COL_POS_TMP }}']",
                "input[name='bill_color']",
                "bill_preview_unavailable"
                );
            }
            );

            const bindLocalLogoPreview = (inputId, imgId, key) => {
            const input = document.getElementById(inputId);
            const img = document.getElementById(imgId);
            if (!input || !img) {
                attachOneTimeFeedback(document.body, key, "click");
                return;
            }
            if (!input.getAttribute(DATA_LISTENER_ADDED)) {
                const handler = () => {
                try {
                    const f = input.files?.[0];
                    if (!f) return;
                    img.src = URL.createObjectURL(f);
                } catch {
                    attachOneTimeFeedback(input, key, "click");
                }
                };
                input.addEventListener("change", handler, false);
                input.setAttribute(DATA_LISTENER_ADDED, "true");
                const mo = new MutationObserver((_, o) => {
                if (!document.body.contains(input)) {
                    input.removeEventListener("change", handler);
                    o.disconnect();
                }
                });
                mo.observe(document.body, { childList: true, subtree: true });
            }
            };

            bindLocalLogoPreview(
            "proposal_logo",
            "proposal_image",
            "proposal_logo_unavailable"
            );
            bindLocalLogoPreview(
            "invoice_logo",
            "invoice_image",
            "invoice_logo_unavailable"
            );
            bindLocalLogoPreview("bill_logo", "bill_image", "bill_logo_unavailable");
        } catch (e) {
            if (
                window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1"
            ) {
                console.error("Initialization failed", e);
            }
        }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_CTT)
    @php 
        $templateData = Utility::templateData(); 
        $templates = $templateData['templates'];
        $colors = $templateData['colors'];
    @endphp
    <div class="{{ VC::CS12 }} {{ VC::MT4 }}">
        <div class="{{ VC::CD }}">
            <div class="card-body">
                @php
                    $tabs = [
                        ['id' => 'proposal', 'label' => __('Proposal Print Setting')],
                        ['id' => 'invoice',  'label' => __('Invoice Print Setting')],
                        ['id' => 'bill',     'label' => __('Bill Print Setting')],
                    ];
                @endphp
                <ul class="{{ VC::NAV_PL }} {{ VC::MB3 }}" id="pills-tab" role="tablist">
                    @if(Utility::isFilled($tabs) ?? [])
                        @foreach($tabs as $index => $tab)
                            <li class="{{ VC::NV_IT }}" role="presentation">
                                <a class="{{ VC::NV_LK }} {{ $index === 0 ? 'active' : '' }}"
                                id="pills-{{ $tab['id'] }}-tab"
                                data-bs-toggle="pill"
                                href="#pills-{{ $tab['id'] }}"
                                role="tab"
                                aria-controls="pills-{{ $tab['id'] }}"
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                    {{ $tab['label'] }}
                                </a>
                            </li>
                        @endforeach
                    @else
                        <li class="{{ VC::NV_IT }}" role="presentation">{{ __('No tabs available') }}</li>
                    @endif
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    {{-- Proposal Setting --}}
                    <div class="tab-pane fade show active" id="pills-proposal" role="tabpanel" aria-labelledby="pills-proposal-tab">
                        <div class="bg-none">
                            <div class="{{ VC::RW }} company-setting">
                                <div class="{{ VC::CM3 }}">
                                    <div class="card-body">
                                        <h5></h5>
                                        @php
                                            $proposalTemplateSettingsRouteBase = ViewsConstants::PPS_TMP.'settings';
                                            $proposalTemplateSettingsRouteKebab = Str::kebab($proposalTemplateSettingsRouteBase);
                                            $proposalTemplateSettingsRouteResolved = Route::has($proposalTemplateSettingsRouteBase) ? $proposalTemplateSettingsRouteBase : (Route::has($proposalTemplateSettingsRouteKebab) ? $proposalTemplateSettingsRouteKebab : null);
                                            $proposalTemplateSettingsUrl = $proposalTemplateSettingsRouteResolved ? route($proposalTemplateSettingsRouteResolved) : '#';
                                            $userLangForProposalTemplate = isset($lang) ? $lang : Utility::fetchUserLang();
                                            $proposalTemplateSettingsGuardMsg = Utility::fetchLinkMessage($userLangForProposalTemplate, ViewsConstants::PPS_TMP, 'settings_proposal_template_route_unavailable') ?? 'Proposal template settings route is unavailable. Please contact technical support or your domain administrator.';
                                            $proposalTemplateSettingsFormId = 'proposal-template-settings-form';
                                            $proposalTemplateSelectId = 'proposal-template-select';
                                            $proposalColorRadioName = 'proposal_color';
                                            $proposalLogoFileInputId = 'proposal-logo-input';
                                            $proposalLogoPreviewImgId = 'proposal-logo-preview-img';
                                        @endphp
                                        <form id="{{ $proposalTemplateSettingsFormId }}" method="post" action="{{ $proposalTemplateSettingsUrl }}" enctype="multipart/form-data" data-url="{{ $proposalTemplateSettingsUrl }}" data-guard-msg="{{ $proposalTemplateSettingsGuardMsg }}" data-sv-localized="true">
                                            @csrf
                                            <div class="{{ VC::FM_G }}">
                                                <label for="{{ $proposalTemplateSelectId }}" class="{{ VC::FM_LB }}">{{ __('Proposal Template') }}</label>
                                                <select id="{{ $proposalTemplateSelectId }}" class="{{ VC::FM_CT }} select2" name="proposal_template">
                                                    @if (Utility::isFilled($templates) ?? [])
                                                        @foreach($templates as $key => $template)
                                                            <option value="{{ $key }}" {{ (isset($settings[BillsConstants::COL_PPS_TMP]) && $settings[BillsConstants::COL_PPS_TMP] == $key) ? 'selected' : '' }}>
                                                                {{ $template }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option value="">{{ __('No templates available for Proposals') }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="{{ VC::FM_G }}">
                                                <label class="{{ VC::FM_LB }}">{{ __('Color Input') }}</label>
                                                <div class="{{ VC::RW }} gutters-xs">
                                                    @if (Utility::isFilled($colors) ?? [])
                                                        @foreach($colors as $key => $color)
                                                            <div class="{{ VC::C_AT }}">
                                                                <label class="colorinput">
                                                                    <input name="{{ $proposalColorRadioName }}" type="radio" value="{{ $color }}" class="colorinput-input" {{ (isset($settings['proposal_color']) && $settings['proposal_color'] == $color) ? 'checked' : '' }}>
                                                                    <span class="colorinput-color" style="background: #{{ $color }}"></span>
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="{{ VC::C_AT }}">
                                                            <label class="colorinput">
                                                                <input name="{{ $proposalColorRadioName }}" type="radio" value="" class="colorinput-input" disabled>
                                                                <span class="colorinput-color" style="background: #{{ $color }}"></span>
                                                            </label>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="{{ VC::FM_G }}">
                                                <label class="{{ VC::FM_LB }}" for="{{ $proposalLogoFileInputId }}">{{ __('Proposal Logo') }}</label>
                                                <div class="choose-files">
                                                    <label for="{{ $proposalLogoFileInputId }}">
                                                        <div class="{{ VC::BG_P }} proposal_logo_update">
                                                            <i class="{{ VC::TI }} ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" class="{{ VC::FM_CT }} file" name="proposal_logo" id="{{ $proposalLogoFileInputId }}" data-filename="proposal_logo_update">
                                                        <img id="{{ $proposalLogoPreviewImgId }}" class="mt-2" style="width:25%;" />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="{{ VC::FM_G }} mt-2 text-end">
                                                <input type="submit" value="{{ __('Save') }}" class="{{ VC::BT_PR_PRM10 }}">
                                            </div>
                                        </form>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer src="{{ asset('assets/js/routes/settings/proposals/settings.js') }}"></script>
                                        @endpush
                                    </div>
                                </div>
                                @php
                                    $proposalPreviewRouteBase = ViewsConstants::PPS.'.preview';
                                    $proposalPreviewRouteKebab = Str::kebab($proposalPreviewRouteBase);
                                    $proposalPreviewRouteResolved = Route::has($proposalPreviewRouteBase) ? $proposalPreviewRouteBase : (Route::has($proposalPreviewRouteKebab) ? $proposalPreviewRouteKebab : null);
                                    $proposalTemplateValue = (isset($settings[BillsConstants::COL_PPS_TMP]) && isset($settings['proposal_color'])) ? $settings[BillsConstants::COL_PPS_TMP] : 'template1';
                                    $proposalColorValue = (isset($settings[BillsConstants::COL_PPS_TMP]) && isset($settings['proposal_color'])) ? $settings['proposal_color'] : 'ffffff';
                                    $proposalPreviewUrl = $proposalPreviewRouteResolved ? route($proposalPreviewRouteResolved, [$proposalTemplateValue, $proposalColorValue]) : '#';
                                    $userLangForProposalPreview = isset($lang) ? $lang : Utility::fetchUserLang();
                                    $proposalPreviewGuardMsg = Utility::fetchLinkMessage($userLangForProposalPreview, ViewsConstants::PPS, 'preview_proposal_route_unavailable') ?? 'Proposal preview route is unavailable. Please contact technical support or your domain administrator.';
                                    $proposalTemplatePreviewIframeId = 'proposal-template-preview-frame';
                                @endphp
                                <div class="{{ VC::CM9 }}">
                                    <iframe id="{{ $proposalTemplatePreviewIframeId }}" class="w-100 h-100" frameborder="0" src="{{ $proposalPreviewUrl }}" data-url="{{ $proposalPreviewUrl }}" data-guard-msg="{{ $proposalPreviewGuardMsg }}" data-sv-localized="true"></iframe>
                                </div>
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/proposals/preview.js') }}">
                                    </script>
                                @endpush
                            </div>
                        </div>
                    </div>
                    {{-- Invoice Setting --}}
                    <div class="tab-pane fade" id="pills-invoice" role="tabpanel" aria-labelledby="pills-invoice-tab">
                        <div class="bg-none">
                            <div class="{{ VC::RW }} company-setting">
                                <div class="{{ VC::CM3 }}">
                                    <div class="card-body">
                                        <h5></h5>
                                        @php
                                            $invoiceTemplateSettingsRouteBase = 'template.setting';
                                            $invoiceTemplateSettingsRouteKebab = Str::kebab($invoiceTemplateSettingsRouteBase);
                                            $invoiceTemplateSettingsRouteResolved = Route::has($invoiceTemplateSettingsRouteBase) ? $invoiceTemplateSettingsRouteBase : (Route::has($invoiceTemplateSettingsRouteKebab) ? $invoiceTemplateSettingsRouteKebab : null);
                                            $invoiceTemplateSettingsUrl = $invoiceTemplateSettingsRouteResolved ? route($invoiceTemplateSettingsRouteResolved) : '#';
                                            $userLangForInvoiceTemplate = isset($lang) ? $lang : Utility::fetchUserLang();
                                            $invoiceTemplateSettingsGuardMsg = Utility::fetchLinkMessage($userLangForInvoiceTemplate, VW::INV, 'settings_invoice_template_route_unavailable') ?? 'Invoice template settings route is unavailable. Please contact technical support or your domain administrator.';
                                            $invoiceTemplateSettingsFormId = 'invoice-template-settings-form';
                                            $invoiceTemplateSelectId = 'invoice-template-select';
                                            $invoiceColorRadioName = 'invoice_color';
                                            $invoiceLogoFileInputId = 'invoice-logo-input';
                                            $invoiceLogoPreviewImgId = 'invoice-logo-preview-img';
                                        @endphp
                                        <form id="{{ $invoiceTemplateSettingsFormId }}" method="post" action="{{ $invoiceTemplateSettingsUrl }}" enctype="multipart/form-data" data-url="{{ $invoiceTemplateSettingsUrl }}" data-guard-msg="{{ $invoiceTemplateSettingsGuardMsg }}" data-sv-localized="true">
                                            @csrf
                                            <div class="{{ VC::FM_G }}">
                                                <label for="{{ $invoiceTemplateSelectId }}" class="{{ VC::FM_LB }}">{{ __('Invoice Template') }}</label>
                                                <select id="{{ $invoiceTemplateSelectId }}" class="{{ VC::FM_CT }} select2" name="invoice_template">
                                                    @if(Utility::isFilled($templates) ?? [])
                                                        @foreach($templates as $key => $template)
                                                            <option value="{{ $key }}" {{ (isset($settings[BillsConstants::COL_INV_TMP]) && $settings[BillsConstants::COL_INV_TMP] == $key) ? 'selected' : '' }}>
                                                                {{ $template }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option value="">{{ __('No templates available for Invoices') }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="{{ VC::FM_G }}">
                                                <label class="{{ VC::FM_LB }}">{{ __('Color Input') }}</label>
                                                <div class="{{ VC::RW }} gutters-xs">
                                                    @if (Utility::isFilled($colors) ?? [])
                                                        @foreach($colors as $key => $color)
                                                            <div class="{{ VC::C_AT }}">
                                                                <label class="colorinput">
                                                                    <input name="{{ $invoiceColorRadioName }}" type="radio" value="{{ $color }}" class="colorinput-input" {{ (isset($settings['invoice_color']) && $settings['invoice_color'] == $color) ? 'checked' : '' }}>
                                                                    <span class="colorinput-color" style="background: #{{ $color }}"></span>
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="{{ VC::C_AT }}">
                                                            <label class="colorinput">
                                                                <input name="{{ $invoiceColorRadioName }}" type="radio" value="ffffff" class="colorinput-input" {{ (isset($settings['invoice_color']) && $settings['invoice_color'] == 'ffffff') ? 'checked' : '' }}>
                                                                <span class="colorinput-color" style="background: #ffffff"></span>
                                                            </label>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="{{ VC::FM_G }}">
                                                <label class="{{ VC::FM_LB }}" for="{{ $invoiceLogoFileInputId }}">{{ __('Invoice Logo') }}</label>
                                                <div class="choose-files">
                                                    <label for="{{ $invoiceLogoFileInputId }}">
                                                        <div class="{{ VC::BG_P }} invoice_logo_update">
                                                            <i class="{{ VC::TI }} ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" class="{{ VC::FM_CT }} file" name="invoice_logo" id="{{ $invoiceLogoFileInputId }}" data-filename="invoice_logo_update">
                                                        <img id="{{ $invoiceLogoPreviewImgId }}" class="mt-2" style="width:25%;" />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="{{ VC::FM_G }} mt-2 text-end">
                                                <input type="submit" value="{{ __('Save') }}" class="{{ VC::BT_PR_PRM10 }}">
                                            </div>
                                        </form>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer src="{{ asset('assets/js/routes/settings/invoices/settings.js') }}"></script>
                                        @endpush
                                    </div>
                                </div>
                                @php
                                    $invoicePreviewRouteBase = ViewsConstants::INV.'.preview';
                                    $invoicePreviewRouteKebab = Str::kebab($invoicePreviewRouteBase);
                                    $invoicePreviewRouteResolved = Route::has($invoicePreviewRouteBase) ? $invoicePreviewRouteBase : (Route::has($invoicePreviewRouteKebab) ? $invoicePreviewRouteKebab : null);
                                    $invoiceTemplateValue = (isset($settings[BillsConstants::COL_INV_TMP]) && isset($settings['invoice_color'])) ? $settings[BillsConstants::COL_INV_TMP] : 'template1';
                                    $invoiceColorValue = (isset($settings[BillsConstants::COL_INV_TMP]) && isset($settings['invoice_color'])) ? $settings['invoice_color'] : 'ffffff';
                                    $invoicePreviewUrl = $invoicePreviewRouteResolved ? route($invoicePreviewRouteResolved, [$invoiceTemplateValue, $invoiceColorValue]) : '#';
                                    $userLangForInvoicePreview = isset($lang) ? $lang : Utility::fetchUserLang();
                                    $invoicePreviewGuardMsg = Utility::fetchLinkMessage($userLangForInvoicePreview, ViewsConstants::INV, 'preview_invoice_route_unavailable') ?? 'Invoice preview route is unavailable. Please contact technical support or your domain administrator.';
                                    $invoiceTemplatePreviewIframeId = 'invoice-template-preview-frame';
                                @endphp
                                <div class="{{ VC::CM9 }}">
                                    <iframe id="{{ $invoiceTemplatePreviewIframeId }}" class="w-100 h-100" frameborder="0" src="{{ $invoicePreviewUrl }}" data-url="{{ $invoicePreviewUrl }}" data-guard-msg="{{ $invoicePreviewGuardMsg }}" data-sv-localized="true"></iframe>
                                </div>
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/invoices/preview.js') }}"></script>
                                @endpush
                            </div>
                        </div>
                    </div>
                    {{-- Bill Setting --}}
                    <div class="tab-pane fade" id="pills-bill" role="tabpanel" aria-labelledby="pills-bill-tab">
                        <div class="bg-none">
                            <div class="{{ VC::RW }} company-setting">
                                <div class="{{ VC::CM3 }}">
                                    <div class="card-body">
                                        <h5></h5>
                                        @php
                                            $billTemplateSettingsRouteBase = ViewsConstants::BIL_TMP.'settings';
                                            $billTemplateSettingsRouteKebab = Str::kebab($billTemplateSettingsRouteBase);
                                            $billTemplateSettingsRouteResolved = Route::has($billTemplateSettingsRouteBase)
                                                ? $billTemplateSettingsRouteBase
                                                : (Route::has($billTemplateSettingsRouteKebab) ? $billTemplateSettingsRouteKebab : null);
                                            $billTemplateSettingsUrl = $billTemplateSettingsRouteResolved ? route($billTemplateSettingsRouteResolved) : '#';
                                            $billTemplateSettingsUserLang = isset($lang) ? $lang : Utility::fetchUserLang();
                                            $billTemplateSettingsGuardMsg = Utility::fetchLinkMessage($billTemplateSettingsUserLang, ViewsConstants::BIL_TMP, 'settings_bill_template_route_unavailable') ?? 'Bill template settings route is unavailable. Please contact technical support or your domain administrator.';
                                            $billTemplateSettingsFormId   = 'bill-template-settings-form';
                                            $billTemplateSelectId         = 'bill-template-select';
                                            $billColorRadioName           = 'bill_color';
                                            $billLogoFileInputId          = 'bill-logo-input';
                                            $billLogoPreviewImgId         = 'bill-logo-preview-img';
                                        @endphp
                                        <form id="{{ $billTemplateSettingsFormId }}"
                                            method="post"
                                            action="{{ $billTemplateSettingsUrl }}"
                                            enctype="multipart/form-data"
                                            data-url="{{ $billTemplateSettingsUrl }}"
                                            data-guard-msg="{{ $billTemplateSettingsGuardMsg }}"
                                            data-sv-localized="true">
                                            @csrf

                                            <div class="{{ VC::FM_G }}">
                                                <label for="{{ $billTemplateSelectId }}" class="{{ VC::FM_LB }}">{{ __('Bill Template') }}</label>
                                                <select id="{{ $billTemplateSelectId }}" class="{{ VC::FM_CT }}" name="bill_template">
                                                    @if(Utility::isFilled($templates) ?? [])
                                                        @foreach($templates as $key => $template)
                                                            <option value="{{ $key }}" {{ (isset($settings[BillsConstants::COL_POS_TMP]) && $settings[BillsConstants::COL_POS_TMP] == $key) ? 'selected' : '' }}>
                                                                {{ $template }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option value="" disabled>{{ __('No templates available for Bills') }}</option>
                                                    @endif
                                                </select>
                                            </div>

                                            <div class="{{ VC::FM_G }}">
                                                <label class="{{ VC::FM_LB }}">{{ __('Color Input') }}</label>
                                                <div class="{{ VC::RW }} gutters-xs">
                                                    @if(Utility::isFilled($colors) ?? [])
                                                        @foreach($colors as $key => $color)
                                                            <div class="{{ VC::C_AT }}">
                                                                <label class="colorinput">
                                                                    <input name="{{ $billColorRadioName }}" type="radio" value="{{ $color }}" class="colorinput-input" {{ (isset($settings['bill_color']) && $settings['bill_color'] == $color) ? 'checked' : '' }}>
                                                                    <span class="colorinput-color" style="background: #{{ $color }}"></span>
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="{{ VC::C_AT }}">
                                                            <label class="colorinput">
                                                                <input name="{{ $billColorRadioName }}" type="radio" value="" class="colorinput-input" {{ (isset($settings['bill_color']) && $settings['bill_color'] == '') ? 'checked' : '' }}>
                                                                <span class="colorinput-color" style="background: #{{ '' }}"></span>
                                                            </label>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="{{ VC::FM_G }}">
                                                <label class="{{ VC::FM_LB }}" for="{{ $billLogoFileInputId }}">{{ __('Bill Logo') }}</label>
                                                <div class="choose-files">
                                                    <label for="{{ $billLogoFileInputId }}">
                                                        <div class="{{ VC::BG_P }} bill_logo_update">
                                                            <i class="{{ VC::TI }} ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" class="{{ VC::FM_CT }} file" name="bill_logo" id="{{ $billLogoFileInputId }}" data-filename="bill_logo_update">
                                                        <img id="{{ $billLogoPreviewImgId }}" class="mt-2" style="width:25%;" />
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="{{ VC::FM_G }} mt-2 text-end">
                                                <input type="submit" value="{{ __('Save') }}" class="{{ VC::BT_PR_PRM10 }}">
                                            </div>
                                        </form>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer src="{{asset('assets/js/routes/settings/bills/settings.js')}}"></script>
                                        @endpush
                                    </div>
                                </div>
                                @php
                                    $billPreviewRouteBase = ViewsConstants::BIL.'.preview';
                                    $billPreviewRouteKebab = Str::kebab($billPreviewRouteBase);
                                    $billPreviewRouteResolved = Route::has($billPreviewRouteBase) ? $billPreviewRouteBase : (Route::has($billPreviewRouteKebab) ? $billPreviewRouteKebab : null);
                                    $billTemplateValue = (isset($settings[BillsConstants::COL_POS_TMP]) && isset($settings['bill_color'])) ? $settings[BillsConstants::COL_POS_TMP] : 'template1';
                                    $billColorValue = (isset($settings[BillsConstants::COL_POS_TMP]) && isset($settings['bill_color'])) ? $settings['bill_color'] : 'ffffff';
                                    $billPreviewUrl = $billPreviewRouteResolved ? route($billPreviewRouteResolved, [$billTemplateValue, $billColorValue]) : '#';
                                    $billPreviewLang = isset($lang) ? $lang : Utility::fetchUserLang();
                                    $billPreviewGuardMsg = Utility::fetchLinkMessage($billPreviewLang, ViewsConstants::BIL, 'preview_bill_route_unavailable') ?? 'Bill preview route is unavailable. Please contact technical support or your domain administrator.';
                                    $billTemplatePreviewIframeId = 'bill-template-preview-frame';
                                @endphp
                                <div class="{{ VC::CM9 }}">
                                    <iframe id="{{ $billTemplatePreviewIframeId }}"
                                            class="w-100 h-100"
                                            frameborder="0"
                                            src="{{ $billPreviewUrl }}"
                                            data-url="{{ $billPreviewUrl }}"
                                            data-guard-msg="{{ $billPreviewGuardMsg }}"
                                            data-sv-localized="true"></iframe>
                                </div>
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/bills/preview.js') }}"></script>
                                @endpush
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
