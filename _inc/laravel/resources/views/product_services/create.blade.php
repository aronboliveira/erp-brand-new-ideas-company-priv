@php
    use App\Config\Constants\{
        PlansConstants, 
        ViewsConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    $lang = Utility::fetchUserLang();
@endphp
{{ Form::open(array('url' => ViewsConstants::PRD_SV,'enctype' => "multipart/form-data")) }}
    <div class="modal-body">
        {{-- start for ai module--}}
        @php
            $plan= Utility::getChatGPTSettings();
        @endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
        <div class="text-end">
            <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',[ViewsConstants::PRD_SV]) }}"
            data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
                <i class="{{ VC::FAS_RB }}"></i> <span>{{__('Generate with AI')}}</span>
            </a>
        </div>
        @endif
        {{-- end for ai module--}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('name', __('Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('name', '', array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('sku', __('SKU'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('sku', '', array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('sale_price', __('Sale Price'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::number('sale_price', '', array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
                </div>
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('sale_chartaccount_id', __('Income Account'),['class'=>'form-label']) }}
                {{ Form::select('sale_chartaccount_id',$incomeChartAccounts,null, array('class' => 'form-control select','required'=>'required')) }}
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('purchase_price', __('Purchase Price'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::number('purchase_price', '', array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
                </div>
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('expense_chartaccount_id', __('Expense Account'),['class'=>'form-label']) }}
                {{ Form::select('expense_chartaccount_id',$expenseChartAccounts,null, array('class' => 'form-control select','required'=>'required')) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('tax_id', __('Tax'),['class'=>'form-label']) }}
                {{ Form::select('tax_id[]', $tax,null, array('class' => 'form-control select2','id'=>'choices-multiple1','multiple')) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('category_id', __('Category'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::select('category_id', $category,null, array('class' => 'form-control select','required'=>'required')) }}

                <div class=" text-xs">
                    {{__('Please add constant category. ')}}<a href="{{route(ViewsConstants::PRD_SV_CAT.'.index')}}"><b>{{__('Add Category')}}</b></a>
                </div>
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('unit_id', __('Unit'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::select('unit_id', $unit,null, array('class' => 'form-control select','required'=>'required')) }}
            </div>
            <div class="col-md-6 form-group">
                {{Form::label('pro_image',__('Product Image'),['class'=>'form-label'])}}
                <div class="choose-file ">
                    <label for="pro_image" class="form-label">
                        <input type="file" class="form-control" name="pro_image" id="pro_image" data-filename="pro_image_create">
                        <img id="image" class="mt-3" style="width:25%;"/>

                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <div class="btn-box">
                        <label class="d-block form-label">{{__('Type')}}</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="{{ VC::FM_CHK_IL }}">
                                    <input type="radio" class="form-check-input type" id="customRadio5" name="type" value="product" checked="checked" >
                                    <label class="custom-control-label form-label" for="customRadio5">{{__('Product')}}</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="{{ VC::FM_CHK_IL }}">
                                    <input type="radio" class="form-check-input type" id="customRadio6" name="type" value="service" >
                                    <label class="custom-control-label form-label" for="customRadio6">{{__('Service')}}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-6 quantity">
                {{ Form::label('quantity', __('Quantity'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('quantity',null, array('class' => 'form-control')) }}
            </div>
            <div class="form-group col-md-12">
                {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
                {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'2']) !!}
            </div>
            @if(!$customFields->isEmpty())
                <div class="{{ VC::CLM6 }}">
                    <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                        @include(ViewsConstants::CST_FD . '.formBuilder')
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
    </div>
{{Form::close()}}
    <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
    ar: { image_preview_unavailable: "تعذّر معاينة الصورة", toggle_quantity_unavailable: "تعذّر تبديل حقل الكمية" },
    da: { image_preview_unavailable: "Kunne ikke forhåndsvise billede", toggle_quantity_unavailable: "Kunne ikke skifte mængdefelt" },
    de: { image_preview_unavailable: "Bildvorschau kann nicht angezeigt werden", toggle_quantity_unavailable: "Mengenfeld kann nicht umgeschaltet werden" },
    en: { image_preview_unavailable: "Cannot preview image", toggle_quantity_unavailable: "Cannot toggle quantity field" },
    es: { image_preview_unavailable: "No se puede previsualizar la imagen", toggle_quantity_unavailable: "No se puede alternar el campo de cantidad" },
    fr: { image_preview_unavailable: "Impossible d’afficher l’aperçu de l’image", toggle_quantity_unavailable: "Impossible d’activer/désactiver le champ quantité" },
    he: { image_preview_unavailable: "לא ניתן להציג תצוגה מקדימה לתמונה", toggle_quantity_unavailable: "לא ניתן להחליף שדה כמות" },
    it: { image_preview_unavailable: "Impossibile visualizzare l’anteprima dell’immagine", toggle_quantity_unavailable: "Impossibile attivare/disattivare il campo quantità" },
    ja: { image_preview_unavailable: "画像プレビューを表示できません", toggle_quantity_unavailable: "数量フィールドを切り替えできません" },
    nl: { image_preview_unavailable: "Kan afbeeldingsvoorbeeld niet tonen", toggle_quantity_unavailable: "Kan veld hoeveelheid niet schakelen" },
    pl: { image_preview_unavailable: "Nie można wyświetlić podglądu obrazu", toggle_quantity_unavailable: "Nie można przełączyć pola ilości" },
    pt: { image_preview_unavailable: "Não é possível pré-visualizar a imagem", toggle_quantity_unavailable: "Não é possível alternar o campo de quantidade" },
    "pt-br": { image_preview_unavailable: "Não é possível pré-visualizar a imagem", toggle_quantity_unavailable: "Não é possível alternar o campo de quantidade" },
    ru: { image_preview_unavailable: "Невозможно показать предпросмотр изображения", toggle_quantity_unavailable: "Невозможно переключить поле количества" },
    tr: { image_preview_unavailable: "Görüntü önizlenemiyor", toggle_quantity_unavailable: "Miktar alanı değiştirilemiyor" },
    zh: { image_preview_unavailable: "无法预览图片", toggle_quantity_unavailable: "无法切换数量字段" }
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
  (()=>{
    const errFb = "# ERROR";
    const dataClientLocalized = "data-client-localized";
    const dataGuardMsg = "data-guard-msg";
    const DATA_LISTENER_ADDED = "data-listener-added";

    const getMsg = (el, msgKey) => {
      let msg = errFb;
      if (el?.getAttribute("data-sv-localized") === "true" || el?.getAttribute(dataClientLocalized) === "true") {
        msg = el.getAttribute(dataGuardMsg) || errFb;
      } else {
        let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
          .toLowerCase()
          .replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        msg =
          window.translations?.[lang]?.[msgKey] ||
          el?.getAttribute(dataGuardMsg) ||
          window.translations?.en?.[msgKey] ||
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
      const hasBs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
      if (hasBs) {
        let toast = document.querySelector("#np-error-toast");
        if (!toast) {
          toast = document.createElement("div");
          toast.id = "np-error-toast";
          toast.className = "toast align-items-center text-bg-danger border-0";
          toast.setAttribute("role", "alert");
          toast.setAttribute("aria-live", "assertive");
          toast.setAttribute("aria-atomic", "true");
          toast.innerHTML = `
            <div class="d-flex">
              <div class="toast-body">${text}</div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>`;
          document.body.appendChild(toast);
        }
        const handler = () => new bootstrap.Toast(toast).show();
        document.addEventListener(ev, handler, { once: true });
        const mo = new MutationObserver((_, o) => {
          if (!document.body.contains(toast)) {
            document.removeEventListener(ev, handler);
            o.disconnect();
          }
        });
        mo.observe(document.body, { childList: true, subtree: true });
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

    const bindImagePreview = () => {
      try {
        const handler = function () {
          try {
            const file = this?.files?.[0];
            if (!file) return;
            const $img = $("#image");
            if (!$img.length) { guardOnce(this, "image_preview_unavailable"); return; }
            const prev = this.getAttribute("data-prev-url") || "";
            const url = URL.createObjectURL(file);
            $img.attr("src", url);
            if (prev) { try { URL.revokeObjectURL(prev); } catch {} }
            this.setAttribute("data-prev-url", url);
          } catch { guardOnce(this, "image_preview_unavailable"); }
        };

        const $input = $("#pro_image");
        if ($input.length && $input.attr("data-np-bound") !== "true") {
          $input.on("change", handler);
          $input.attr("data-np-bound", "true");
          const el = $input.get(0);
          const mo = new MutationObserver((_, o) => {
            if (!document.body.contains(el)) { $input.off("change", handler); o.disconnect(); }
          });
          mo.observe(document.body, { childList: true, subtree: true });
        }

        if (document.body.getAttribute("data-np-delegate-img") !== "true") {
          $(document).on("change", "#pro_image", function () {
            if ($(this).attr("data-np-bound") === "true") return;
            handler.call(this);
          });
          document.body.setAttribute("data-np-delegate-img", "true");
        }
      } catch { guardOnce(document.body, "image_preview_unavailable"); }
    };

    const bindQuantityToggle = () => {
      try {
        if (document.body.getAttribute("data-np-qty-bound") === "true") return;
        $(document).on("click", ".type", function () {
          try {
            const type = String($(this).val() ?? "").toLowerCase();
            const $q = $(".quantity");
            if (!$q.length) return;
            if (type === "product") {
              $q.removeClass("d-none").addClass("d-block");
            } else {
              $q.addClass("d-none").removeClass("d-block");
            }
          } catch { guardOnce(this, "toggle_quantity_unavailable"); }
        });
        document.body.setAttribute("data-np-qty-bound", "true");
      } catch { guardOnce(document.body, "toggle_quantity_unavailable"); }
    };

    try {
      if (typeof $ === "undefined") { console.error("jQuery failed to load"); return; }
      bindImagePreview();
      bindQuantityToggle();
    } catch (e) {
      console.error("Initialization failed", e);
    }
  })();
</script>
