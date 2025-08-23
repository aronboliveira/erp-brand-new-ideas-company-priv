@php
	use App\Config\Constants\{
        SettingsConstants,
        ViewClassNamesConstants as VC
    };
	use App\Models\Utility;
	use Illuminate\Support\Facades\Log;
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
			'Error loading header view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception loading header view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable loading header view data',
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
            ar:{scrollspy_unavailable:"تعذّر تفعيل ScrollSpy",purchase_preview_unavailable:"تعذّر عرض معاينة أمر الشراء",purchase_logo_unavailable:"تعذّر معاينة شعار أمر الشراء",pos_preview_unavailable:"تعذّر عرض معاينة نقطة البيع",pos_logo_unavailable:"تعذّر معاينة شعار نقطة البيع"},
            da:{scrollspy_unavailable:"Kunne ikke aktivere ScrollSpy",purchase_preview_unavailable:"Kunne ikke vise indkøbsforhåndsvisning",purchase_logo_unavailable:"Kunne ikke forhåndsvise indkøbslogo",pos_preview_unavailable:"Kunne ikke vise POS-forhåndsvisning",pos_logo_unavailable:"Kunne ikke forhåndsvise POS-logo"},
            de:{scrollspy_unavailable:"ScrollSpy konnte nicht aktiviert werden",purchase_preview_unavailable:"Bestellvorschau konnte nicht geladen werden",purchase_logo_unavailable:"Bestelllogo konnte nicht angezeigt werden",pos_preview_unavailable:"POS-Vorschau konnte nicht geladen werden",pos_logo_unavailable:"POS-Logo konnte nicht angezeigt werden"},
            en:{scrollspy_unavailable:"Cannot enable ScrollSpy",purchase_preview_unavailable:"Cannot load purchase preview",purchase_logo_unavailable:"Cannot preview purchase logo",pos_preview_unavailable:"Cannot load POS preview",pos_logo_unavailable:"Cannot preview POS logo"},
            es:{scrollspy_unavailable:"No se puede activar ScrollSpy",purchase_preview_unavailable:"No se puede cargar la vista previa de compra",purchase_logo_unavailable:"No se puede previsualizar el logo de compra",pos_preview_unavailable:"No se puede cargar la vista previa de POS",pos_logo_unavailable:"No se puede previsualizar el logo de POS"},
            fr:{scrollspy_unavailable:"Impossible d’activer ScrollSpy",purchase_preview_unavailable:"Impossible de charger l’aperçu d’achat",purchase_logo_unavailable:"Impossible d’afficher l’aperçu du logo d’achat",pos_preview_unavailable:"Impossible de charger l’aperçu du PDV",pos_logo_unavailable:"Impossible d’afficher l’aperçu du logo PDV"},
            he:{scrollspy_unavailable:"לא ניתן להפעיל ScrollSpy",purchase_preview_unavailable:"לא ניתן לטעון תצוגה מקדימה של הזמנה",purchase_logo_unavailable:"לא ניתן להציג תצוגה מקדימה של לוגו הזמנה",pos_preview_unavailable:"לא ניתן לטעון תצוגה מקדימה של קופה",pos_logo_unavailable:"לא ניתן להציג תצוגה מקדימה של לוגו קופה"},
            it:{scrollspy_unavailable:"Impossibile abilitare ScrollSpy",purchase_preview_unavailable:"Impossibile caricare l’anteprima ordine",purchase_logo_unavailable:"Impossibile anteprima logo ordine",pos_preview_unavailable:"Impossibile caricare anteprima POS",pos_logo_unavailable:"Impossibile anteprima logo POS"},
            ja:{scrollspy_unavailable:"ScrollSpy を有効にできません",purchase_preview_unavailable:"購入プレビューを読み込めません",purchase_logo_unavailable:"購入ロゴをプレビューできません",pos_preview_unavailable:"POS プレビューを読み込めません",pos_logo_unavailable:"POS ロゴをプレビューできません"},
            nl:{scrollspy_unavailable:"ScrollSpy kan niet worden ingeschakeld",purchase_preview_unavailable:"Voorbeeld van inkoop kan niet laden",purchase_logo_unavailable:"Voorbeeld van inkooplogo mislukt",pos_preview_unavailable:"POS-voorbeeld kan niet laden",pos_logo_unavailable:"POS-logo kan niet worden bekeken"},
            pl:{scrollspy_unavailable:"Nie można włączyć ScrollSpy",purchase_preview_unavailable:"Nie można wczytać podglądu zamówienia",purchase_logo_unavailable:"Nie można podglądnąć logo zamówienia",pos_preview_unavailable:"Nie można wczytać podglądu POS",pos_logo_unavailable:"Nie można podglądnąć logo POS"},
            pt:{scrollspy_unavailable:"Não foi possível ativar o ScrollSpy",purchase_preview_unavailable:"Não foi possível carregar a pré-visualização de compra",purchase_logo_unavailable:"Não foi possível pré-visualizar o logotipo da compra",pos_preview_unavailable:"Não foi possível carregar a pré-visualização do POS",pos_logo_unavailable:"Não foi possível pré-visualizar o logotipo do POS"},
            "pt-br":{scrollspy_unavailable:"Não foi possível ativar o ScrollSpy",purchase_preview_unavailable:"Não foi possível carregar a prévia da compra",purchase_logo_unavailable:"Não foi possível pré-visualizar o logo da compra",pos_preview_unavailable:"Não foi possível carregar a prévia do PDV",pos_logo_unavailable:"Não foi possível pré-visualizar o logo do PDV"},
            ru:{scrollspy_unavailable:"Не удалось включить ScrollSpy",purchase_preview_unavailable:"Не удалось загрузить предварительный просмотр закупки",purchase_logo_unavailable:"Не удалось показать логотип закупки",pos_preview_unavailable:"Не удалось загрузить предварительный просмотр POS",pos_logo_unavailable:"Не удалось показать логотип POS"},
            tr:{scrollspy_unavailable:"ScrollSpy etkinleştirilemedi",purchase_preview_unavailable:"Satın alma önizlemesi yüklenemiyor",purchase_logo_unavailable:"Satın alma logosu önizlenemiyor",pos_preview_unavailable:"POS önizlemesi yüklenemiyor",pos_logo_unavailable:"POS logosu önizlenemiyor"},
            zh:{scrollspy_unavailable:"无法启用 ScrollSpy",purchase_preview_unavailable:"无法加载采购预览",purchase_logo_unavailable:"无法预览采购徽标",pos_preview_unavailable:"无法加载收银预览",pos_logo_unavailable:"无法预览收银徽标"}
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
            const getMsg = (el, msgKey) => {
                let msg = errFb;
                if (
                el?.getAttribute("data-sv-localized") === "true" ||
                el?.getAttribute(dataClientLocalized) === "true"
                )
                msg = el.getAttribute(dataGuardMsg) || errFb;
                else {
                let lang = (
                    window.sessionStorage.getItem("erp-np-lang") ||
                    document.documentElement.lang ||
                    "en"
                )
                    .toLowerCase()
                    .replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                const key = msgKey;
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
            const showFeedback = (el, key, ev = "click") => {
                const text = getMsg(el || document.body, key);
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
                    toast.innerHTML = `<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                    document.body.appendChild(toast);
                }
                const handler = () => new bootstrap.Toast(toast).show();
                if (!toast.getAttribute(DATA_LISTENER_ADDED)) {
                    toast.setAttribute(DATA_LISTENER_ADDED, "true");
                    const mo = new MutationObserver((_, o) => {
                    if (!document.body.contains(toast)) {
                        document.removeEventListener(ev, handler);
                        o.disconnect();
                    }
                    });
                    mo.observe(document.body, { childList: true, subtree: true });
                }
                document.addEventListener(ev, handler, { once: true });
                } else {
                const handler = () => alert(text);
                document.addEventListener(ev, handler, { once: true });
                }
            };
            const guardOnce = (el, key, ev = "click") => {
                if (!el || el.getAttribute(DATA_LISTENER_ADDED) === "true") return;
                const handler = () => showFeedback(el, key, ev);
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
                console.error("jQuery failed to load");
                return;
                }
                try {
                if (window.bootstrap?.ScrollSpy) {
                    new bootstrap.ScrollSpy(document.body, {
                    target: "#useradd-sidenav",
                    offset: 300,
                    });
                } else {
                    guardOnce(document.body, "scrollspy_unavailable", "click");
                }
                } catch {
                guardOnce(document.body, "scrollspy_unavailable", "click");
                }
                $(document).on(
                "change",
                "select[name='purchase_template'], input[name='purchase_color']",
                function () {
                    try {
                    const template = $("select[name='purchase_template']").val() ?? "";
                    const color = $("input[name='purchase_color']:checked").val() ?? "";
                    const $frame = $("#purchase_frame");
                    const preview = `{{url('/purchase/preview')}}/${template}/${color}`;
                    if (!$frame.length || routeGuard($frame.get(0), preview)) {
                        guardOnce(
                        $frame.get(0) || document.body,
                        "purchase_preview_unavailable",
                        "click"
                        );
                        return;
                    }
                    $frame.attr("src", preview);
                    } catch {
                    guardOnce(document.body, "purchase_preview_unavailable", "click");
                    }
                }
                );
                (() => {
                const input = document.getElementById("purchase_logo");
                const img = document.getElementById("purchase_image");
                if (!input || !img) {
                    guardOnce(document.body, "purchase_logo_unavailable", "click");
                    return;
                }
                if (!input.getAttribute(DATA_LISTENER_ADDED)) {
                    input.addEventListener(
                    "change",
                    () => {
                        try {
                        const f = input.files?.[0];
                        if (!f) {
                            return;
                        }
                        const src = URL.createObjectURL(f);
                        img.src = src;
                        } catch {
                        guardOnce(input, "purchase_logo_unavailable", "click");
                        }
                    },
                    { once: false }
                    );
                    input.setAttribute(DATA_LISTENER_ADDED, "true");
                    const mo = new MutationObserver((_, o) => {
                    if (!document.body.contains(input)) {
                        input.removeEventListener("change", () => {});
                        o.disconnect();
                    }
                    });
                    mo.observe(document.body, { childList: true, subtree: true });
                }
                })();
                $(document).on(
                "change",
                "select[name='pos_template'], input[name='pos_color']",
                function () {
                    try {
                    const template = $("select[name='pos_template']").val() ?? "";
                    const color = $("input[name='pos_color']:checked").val() ?? "";
                    const $frame = $("#pos_frame");
                    const preview = `{{url('/pos/preview')}}/${template}/${color}`;
                    if (!$frame.length || routeGuard($frame.get(0), preview)) {
                        guardOnce(
                        $frame.get(0) || document.body,
                        "pos_preview_unavailable",
                        "click"
                        );
                        return;
                    }
                    $frame.attr("src", preview);
                    } catch {
                    guardOnce(document.body, "pos_preview_unavailable", "click");
                    }
                }
                );
                (() => {
                const input = document.getElementById("pos_logo");
                const img = document.getElementById("pos_image");
                if (!input || !img) {
                    guardOnce(document.body, "pos_logo_unavailable", "click");
                    return;
                }
                if (!input.getAttribute(DATA_LISTENER_ADDED)) {
                    input.addEventListener(
                    "change",
                    () => {
                        try {
                        const f = input.files?.[0];
                        if (!f) {
                            return;
                        }
                        const src = URL.createObjectURL(f);
                        img.src = src;
                        } catch {
                        guardOnce(input, "pos_logo_unavailable", "click");
                        }
                    },
                    { once: false }
                    );
                    input.setAttribute(DATA_LISTENER_ADDED, "true");
                    const mo = new MutationObserver((_, o) => {
                    if (!document.body.contains(input)) {
                        input.removeEventListener("change", () => {});
                        o.disconnect();
                    }
                    });
                    mo.observe(document.body, { childList: true, subtree: true });
                }
                })();
            } catch (e) {
                console.error("Initialization failed", e);
            }
        })();
    </script>
@endpush
@section('content')
    <div class="{{ VC::CS12 }} {{ VC::MT4 }}">
        <div class="{{ VC::CD }}">
            <div class="card-body">
                <ul class="{{ VC::NAV_PL }} {{ VC::MB3 }}" id="pills-tab" role="tablist">
                    <li class="{{ VC::NV_IT }}">
                        <a class="{{ VC::NV_LK }} active"
                        id="pills-purchase-tab"
                        data-bs-toggle="pill"
                        href="#pills-purchase"
                        role="tab"
                        aria-controls="pills-purchase"
                        aria-selected="false">
                            {{ __('Purchase Print Setting') }}
                        </a>
                    </li>
                    <li class="{{ VC::NV_IT }}">
                        <a class="{{ VC::NV_LK }}"
                        id="pills-pos-tab"
                        data-bs-toggle="pill"
                        href="#pills-pos"
                        role="tab"
                        aria-controls="pills-pos"
                        aria-selected="false">
                            {{ __('POS Print Setting') }}
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-purchase" role="tabpanel" aria-labelledby="pills-purchase-tab">
                        <div class="bg-none">
                            <div class="{{ VC::RW }} company-setting">
                                <div class="{{ VC::CM3 }}">
                                    <div class="card-body">
                                        <h5></h5>
                                        <form id="setting-form" method="post" action="{{ route(ViewsConstants::PRC_TMP . 'settings') }}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="{{ VC::FM_G }}">
                                                <label for="address" class="{{ VC::FM_LB }}">{{ __('Purchase Template') }}</label>
                                                <select class="{{ VC::FM_CT }}" name="purchase_template">
                                                    @foreach(Utility::templateData()['templates'] as $key => $template)
                                                        <option value="{{ $key }}" {{ (isset($settings[BillsConstants::COL_PRC_TMP]) && $settings[BillsConstants::COL_PRC_TMP] == $key) ? 'selected' : '' }}>
                                                            {{ $template }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="{{ VC::FM_G }}">
                                                <label class="{{ VC::FM_LB }}">{{ __('Color Input') }}</label>
                                                <div class="{{ VC::RW }} gutters-xs">
                                                    @foreach(Utility::templateData()['colors'] as $key => $color)
                                                        <div class="{{ VC::C_AT }}">
                                                            <label class="colorinput">
                                                                <input name="purchase_color" type="radio" value="{{ $color }}" class="colorinput-input" {{ (isset($settings['purchase_color']) && $settings['purchase_color'] == $color) ? 'checked' : '' }}>
                                                                <span class="colorinput-color" style="background: #{{ $color }}"></span>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="{{ VC::FM_G }}">
                                                <label class="{{ VC::FM_LB }}">{{ __('Purchase Logo') }}</label>
                                                <div class="choose-files mt-2">
                                                    <label for="purchase_logo">
                                                        <div class="{{ VC::BG_P }} purchase_logo_update">
                                                            <i class="{{ VC::TI }} ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" class="{{ VC::FM_CT }} file" name="purchase_logo" id="purchase_logo" data-filename="purchase_logo_update">
                                                        <img id="purchase_image" class="mt-2" style="width:25%;"/>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="{{ VC::FM_G }} mt-2 text-end">
                                                <input type="submit" value="{{ __('Save') }}" class="{{ VC::BT_PR_PRM10 }}">
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="{{ VC::CM9 }}">
                                    @if(isset($settings[BillsConstants::COL_PRC_TMP]) && isset($settings['purchase_color']))
                                        <iframe id="purchase_frame" class="w-100 h-100" frameborder="0" src="{{ route(ViewsConstants::PRC . '.preview', [$settings[BillsConstants::COL_PRC_TMP], $settings['purchase_color']]) }}"></iframe>
                                    @else
                                        <iframe id="purchase_frame" class="w-100 h-100" frameborder="0" src="{{ route(ViewsConstants::PRC . '.preview', ['template1','ffffff']) }}"></iframe>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-pos" role="tabpanel" aria-labelledby="pills-pos-tab">
                        <div class="bg-none">
                            <div class="{{ VC::RW }} company-setting">
                                <div class="{{ VC::CM3 }}">
                                    <div class="card-body">
                                        <h5></h5>
                                        <form id="setting-form" method="post" action="{{ route(ViewsConstants::PRC_TMP . 'settings') }}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="{{ VC::FM_G }}">
                                                <label for="address" class="{{ VC::FM_LB }}">{{ __('POS Template') }}</label>
                                                <select class="{{ VC::FM_CT }}" name="pos_template">
                                                    @foreach(Utility::templateData()['templates'] as $key => $template)
                                                        <option value="{{ $key }}" {{ (isset($settings[BillsConstants::COL_BL_]) && $settings[BillsConstants::COL_BL_] == $key) ? 'selected' : '' }}>
                                                            {{ $template }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="{{ VC::FM_G }}">
                                                <label class="{{ VC::FM_LB }}">{{ __('Color Input') }}</label>
                                                <div class="{{ VC::RW }} gutters-xs">
                                                    @foreach(Utility::templateData()['colors'] as $key => $color)
                                                        <div class="{{ VC::C_AT }}">
                                                            <label class="colorinput">
                                                                <input name="pos_color" type="radio" value="{{ $color }}" class="colorinput-input" {{ (isset($settings['pos_color']) && $settings['pos_color'] == $color) ? 'checked' : '' }}>
                                                                <span class="colorinput-color" style="background: #{{ $color }}"></span>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="{{ VC::FM_G }}">
                                                <label class="{{ VC::FM_LB }}">{{ __('POS Logo') }}</label>
                                                <div class="choose-files mt-2">
                                                    <label for="pos_logo">
                                                        <div class="{{ VC::BG_P }} pos_logo_update">
                                                            <i class="{{ VC::TI }} ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" class="{{ VC::FM_CT }} file" name="pos_logo" id="pos_logo" data-filename="pos_logo_update">
                                                        <img id="pos_image" class="mt-2" style="width:25%;"/>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="{{ VC::FM_G }} mt-2 text-end">
                                                <input type="submit" value="{{ __('Save') }}" class="{{ VC::BT_PR_PRM10 }}">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="{{ VC::CM9 }}">
                                    @if(isset($settings[BillsConstants::COL_BL_]) && isset($settings['pos_color']))
                                        <iframe id="pos_frame" class="w-100 h-100" frameborder="0" src="{{ route('pos.preview', [$settings[BillsConstants::COL_BL_], $settings['pos_color']]) }}"></iframe>
                                    @else
                                        <iframe id="pos_frame" class="w-100 h-100" frameborder="0" src="{{ route('pos.preview', ['template1','ffffff']) }}"></iframe>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
