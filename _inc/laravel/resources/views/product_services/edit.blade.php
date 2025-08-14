@php
    use App\Config\Constants\{
        DatabaseConstants,
        PlansConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    $lang = Utility::fetchUserLang();
    $plan = Utility::getChatGPTSettings();
    $fields = [
        ['name'=>'name',                   'type'=>'text',     'label'=>__('Name'),             'required'=>true],
        ['name'=>'sku',                    'type'=>'text',     'label'=>__('SKU'),              'required'=>true],
        ['name'=>'sale_price',             'type'=>'number',   'label'=>__('Sale Price'),       'required'=>true,'step'=>'0.01'],
        ['name'=>'sale_chartaccount_id',   'type'=>'select',   'label'=>__('Income Account'),   'options'=>$incomeChartAccounts,'required'=>true],
        ['name'=>'purchase_price',         'type'=>'number',   'label'=>__('Purchase Price'),   'required'=>true,'step'=>'0.01'],
        ['name'=>'expense_chartaccount_id','type'=>'select',   'label'=>__('Expense Account'),  'options'=>$expenseChartAccounts,'required'=>true],
        ['name'=>'tax_id',                 'type'=>'select2',  'label'=>__('Tax'),              'options'=>$tax,'multiple'=>true],
        ['name'=>'category_id',            'type'=>'select',   'label'=>__('Category'),         'options'=>$category,'required'=>true],
        ['name'=>'unit_id',                'type'=>'select',   'label'=>__('Unit'),             'options'=>$unit,'required'=>true],
        ['name'=>'description',            'type'=>'textarea','label'=>__('Description'),      'rows'=>2],
    ];
@endphp
{{ Form::model($productService,[
    'route'     => [ViewsConstants::PRD_SV.'.update',$productService->id],
    'method'    => 'PUT',
    'enctype'   => 'multipart/form-data',
]) }}
    <div class="modal-body">
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="text-end mb-3">
                <a href="#"
                class="btn btn-primary btn-icon btn-sm"
                data-ajax-popup-over="true"
                data-size="md"
                data-url="{{ route('generate',[ViewsConstants::PRD_SV]) }}"
                data-bs-placement="top"
                data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i> {{ __('Generate with AI') }}
                </a>
            </div>
        @endif
        <div class="row">
            @foreach($fields as $f)
                @php
                    $attrs = ['class'=>'form-control'];
                    if(!empty($f['required'])) $attrs['required']='required';
                    if(!empty($f['step']))     $attrs['step']=$f['step'];
                @endphp
                <div class="form-group col-md-6">
                    {{ Form::label($f['name'],$f['label'],['class'=>'form-label']) }}
                    @if(!empty($f['required']))<span class="text-danger">*</span>@endif

                    @if($f['type']=='text' || $f['type']=='number')
                        {{ Form::{ $f['type'] }($f['name'], null, $attrs) }}
                    @elseif($f['type']=='select')
                        {{ Form::select($f['name'], $f['options'], null, $attrs + ['class'=>'form-control select']) }}
                    @elseif($f['type']=='select2')
                        {{ Form::select($f['name'], $f['options'], null, $attrs + ['class'=>'form-control select2','id'=>'choices-multiple1','multiple'=>'']) }}
                    @elseif($f['type']=='textarea')
                        {{ Form::textarea($f['name'], null, $attrs + ['rows'=>$f['rows']]) }}
                    @endif
                </div>
            @endforeach

            <div class="col-md-6 form-group">
                {{ Form::label('pro_image',__('Product Image'),['class'=>'form-label']) }}
                <div class="choose-file">
                    <label for="pro_image" class="form-label">
                        <input
                            type="file"
                            name="pro_image"
                            id="pro_image"
                            class="form-control"
                            data-filename="pro_image_create"
                        >
                        <img
                            id="image"
                            class="mt-3"
                            width="100"
                            src="{{ $productService->pro_image
                                ? asset(Storage::url('uploads/pro_image/'.$productService->pro_image))
                                : asset(Storage::url('uploads/pro_image/user-2_1654779769.jpg')) }}"
                        />
                    </label>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="d-block form-label">{{ __('Type') }}</label>
                    <div class="row">
                        @foreach(['product','service'] as $type)
                            <div class="col-md-6">
                                <div class="{{ VC::FM_CHK_IL }}">
                                    <input
                                        class="form-check-input type"
                                        type="radio"
                                        name="type"
                                        id="type_{{ $type }}"
                                        value="{{ $type }}"
                                        {{ $productService->type == $type ? 'checked' : '' }}
                                    >
                                    <label class="form-label" for="type_{{ $type }}">{{ ucfirst($type) }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="form-group col-md-6 quantity {{ $productService->type=='service' ? 'd-none' : '' }}">
                {{ Form::label('quantity',__('Quantity'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('quantity', null, ['class'=>'form-control','required'=>'required']) }}
            </div>
            @if(!$customFields->isEmpty())
                <div class="col-md-6">
                    <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                        @include(ViewsConstants::CST_FD . '.form_builder')
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
    </div>
{{ Form::close() }}
<script async>
  window.translations = {
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
        let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en").toLowerCase().replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        msg = window.translations?.[lang]?.[msgKey] || el?.getAttribute(dataGuardMsg) || window.translations?.en?.[msgKey] || errFb;
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

    try {
      if (typeof $ === "undefined") { console.error("jQuery failed to load"); return; }

      const $imgInput = $("#pro_image");
      const $img = $("#image");
      if ($imgInput.length) {
        const onImgChange = function() {
          try {
            const file = this?.files?.[0];
            if (!file || !$img.length) { guardOnce(this, "image_preview_unavailable"); return; }
            const prev = this.getAttribute("data-prev-url") || "";
            const url = URL.createObjectURL(file);
            $img.attr("src", url);
            if (prev) { try { URL.revokeObjectURL(prev); } catch {} }
            this.setAttribute("data-prev-url", url);
          } catch { guardOnce(this, "image_preview_unavailable"); }
        };
        if ($imgInput.attr("data-np-bound") !== "true") {
          $imgInput.on("change", onImgChange);
          $imgInput.attr("data-np-bound", "true");
          const el = $imgInput.get(0);
          const mo = new MutationObserver((_, o) => {
            if (!document.body.contains(el)) { $imgInput.off("change", onImgChange); o.disconnect(); }
          });
          mo.observe(document.body, { childList: true, subtree: true });
        }
      }

      if (document.body.getAttribute("data-np-qty-bound") !== "true") {
        $(document).on("click", ".type", function() {
          try {
            const isProduct = String($(this).val() ?? "").toLowerCase() === "product";
            const $qty = $(".quantity");
            if (!$qty.length) { guardOnce(this, "toggle_quantity_unavailable"); return; }
            $qty.toggleClass("d-none", !isProduct).toggleClass("d-block", isProduct);
          } catch { guardOnce(this, "toggle_quantity_unavailable"); }
        });
        document.body.setAttribute("data-np-qty-bound", "true");
      }
    } catch (e) {
      console.error("Initialization failed", e);
    }
  })();
</script>

