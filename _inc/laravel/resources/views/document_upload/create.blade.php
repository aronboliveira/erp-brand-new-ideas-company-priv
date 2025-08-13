{{Collective\Html\FormFacade::open(array('url'=>'document-upload','method'=>'post', 'enctype' => "multipart/form-data"))}}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
    @endphp
    @if($plan->chatgpt == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['document']) }}"
           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
        </a>
    </div>
    @endif
    {{-- end for ai module--}}
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('name',__('Name'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::text('name',null,array('class'=>'form-control','required'=>'required'))}}
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('role',__('Role'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::select('role',$roles,null,array('class'=>'form-control select'))}}
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                {{ Collective\Html\FormFacade::label('description', __('Description'),['class'=>'form-label'])}}
                {{ Collective\Html\FormFacade::textarea('description',null, array('class' => 'form-control' ,'rows'=> 3)) }}
            </div>
        </div>

        <div class="col-md-6 form-group">
            {{Collective\Html\FormFacade::label('document',__('Document'),['class'=>'form-label'])}}
            <div class="choose-file ">
                <label for="document" class="form-label">
                    <input type="file" class="form-control" name="document" id="document" data-filename="document_create" required>
                    <img id="image" class="mt-3" style="width:25%;"/>
                </label>
            </div>
        </div>


    </div>
</div>
<div class="modal-footer">

    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}

<script async>
    const imgPatch = {
      ar: { image_preview_failed: 'تعذر معاينة الصورة.' },
      da: { image_preview_failed: 'Kunne ikke forhåndsvise billedet.' },
      de: { image_preview_failed: 'Bildvorschau fehlgeschlagen.' },
      en: { image_preview_failed: 'Image preview failed.' },
      es: { image_preview_failed: 'No se pudo previsualizar la imagen.' },
      fr: { image_preview_failed: 'Échec de l’aperçu de l’image.' },
      he: { image_preview_failed: 'תצוגה מקדימה של התמונה נכשלה.' },
      it: { image_preview_failed: 'Anteprima immagine non riuscita.' },
      ja: { image_preview_failed: '画像プレビューに失敗しました。' },
      nl: { image_preview_failed: 'Afbeelding kon niet worden weergegeven.' },
      pl: { image_preview_failed: 'Nie udało się wyświetlić podglądu obrazu.' },
      pt: { image_preview_failed: 'Falha na pré‑visualização da imagem.' },
      'pt-br': { image_preview_failed: 'Falha na pré‑visualização da imagem.' },
      ru: { image_preview_failed: 'Не удалось просмотреть изображение.' },
      tr: { image_preview_failed: 'Resim ön izlemesi başarısız.' },
      zh: { image_preview_failed: '图像预览失败。' }
    };
    window.translations = Object.keys(window.translations || {}).length
      ? Object.keys(imgPatch).reduce((a,l)=>{a[l]={...(a[l]||{}),...imgPatch[l]};return a;},window.translations)
      : imgPatch;
</script>
<script defer>
    (() => {
    const fileInput = document.getElementById('document');
    const imgEl     = document.getElementById('image');
    if (!fileInput || !imgEl) return;                     // nothing to wire up
    
    const langShort = () => {
        const l = (sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en')
        .toLowerCase().replace(/_/g,'-');
        return l === 'pt-br' ? l : l.slice(0,2);
    };
    const t = k => window.translations?.[langShort()]?.[k]
                ?? window.translations?.en?.[k] ?? '# ERROR';
    const toast = m => window.show_toastr ? window.show_toastr('error', m, 'error') : alert(m);
    
    let lastUrl = '';
    
    const previewHandler = () => {
        try {
        const file = fileInput.files?.[0];
        if (!file) return;
        if (lastUrl) URL.revokeObjectURL(lastUrl);
        lastUrl       = URL.createObjectURL(file);
        imgEl.src     = lastUrl;
        } catch {
        toast(t('image_preview_failed'));
        }
    };
    
    fileInput.addEventListener('change', previewHandler);
    new MutationObserver((ms, obs) => {
        ms.forEach(m => m.removedNodes.forEach(n => {
        if (n === fileInput) {
            fileInput.removeEventListener('change', previewHandler);
            if (lastUrl) URL.revokeObjectURL(lastUrl);
            obs.disconnect();
        }
        }));
    }).observe(document.body, { childList: true, subtree: true });
    })();
</script>
    
