@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC};
@endphp

<form id="form_pad" method="post" enctype="multipart/form-data">
    @method('POST')
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            @csrf
            <input type="hidden" name="contract_id" value="{{ $contract->id }}">
            <div class="{{ VC::FM_G }}">
                <canvas id="signature-pad" class="signature-pad" height="200"></canvas>
                <input
                    type="hidden"
                    @if(Auth::user()->type === 'company') name="company_signature" @else name="client_signature" @endif
                    id="SignupImage1">
            </div>
            <div class="mt-1">
                <button type="button" class="{{ VC::BT_SM }} btn-danger" id="clearSig">{{ __('Clear') }}</button>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }} btn-secondary" data-bs-dismiss="modal">
        <input type="button" id="addSig" value="{{ __('Sign') }}" class="{{ VC::BT_SM_PM }} ms-2">
    </div>
</form>


<script src="{{asset('assets/js/plugins/signature_pad/signature_pad.min.js')}}"></script>
<script>
    window.translations = {
      ar: {
        signature_init_failed: 'فشل تهيئة لوحة التوقيع.',
        signature_save_failed: 'فشل حفظ التوقيع.'
      },
      da: {
        signature_init_failed: 'Kunne ikke initialisere signatur.',
        signature_save_failed: 'Kunne ikke gemme signatur.'
      },
      de: {
        signature_init_failed: 'Initialisierung des Signaturfelds fehlgeschlagen.',
        signature_save_failed: 'Speichern der Signatur fehlgeschlagen.'
      },
      en: {
        signature_init_failed: 'Failed to initialize signature pad.',
        signature_save_failed: 'Failed to save signature.'
      },
      es: {
        signature_init_failed: 'Error al inicializar el área de firma.',
        signature_save_failed: 'Error al guardar la firma.'
      },
      fr: {
        signature_init_failed: 'Échec de l’initialisation du pad de signature.',
        signature_save_failed: 'Échec de l’enregistrement de la signature.'
      },
      he: {
        signature_init_failed: 'האתחול של לוח החתימה נכשל.',
        signature_save_failed: 'שמירת החתימה נכשלה.'
      },
      it: {
        signature_init_failed: 'Impossibile inizializzare il pad di firma.',
        signature_save_failed: 'Impossibile salvare la firma.'
      },
      ja: {
        signature_init_failed: '署名パッドの初期化に失敗しました。',
        signature_save_failed: '署名の保存に失敗しました。'
      },
      nl: {
        signature_init_failed: 'Initialisatie van handtekeningveld mislukt.',
        signature_save_failed: 'Handtekening opslaan mislukt.'
      },
      pl: {
        signature_init_failed: 'Nie udało się zainicjalizować pola podpisu.',
        signature_save_failed: 'Nie udało się zapisać podpisu.'
      },
      pt: {
        signature_init_failed: 'Falha ao inicializar pad de assinatura.',
        signature_save_failed: 'Falha ao salvar assinatura.'
      },
      'pt-br': {
        signature_init_failed: 'Falha ao inicializar pad de assinatura.',
        signature_save_failed: 'Falha ao salvar assinatura.'
      },
      ru: {
        signature_init_failed: 'Не удалось инициализировать панель подписи.',
        signature_save_failed: 'Не удалось сохранить подпись.'
      },
      tr: {
        signature_init_failed: 'İmza paneli başlatılamadı.',
        signature_save_failed: 'İmza kaydedilemedi.'
      },
      zh: {
        signature_init_failed: '初始化签名板失败。',
        signature_save_failed: '保存签名失败。'
      }
    };
</script>
<script defer>
    (() => {
    const ERR_FB = '# ERROR';
    const GUARD_MSG = 'data-guard-msg';
    const CLIENT_FLAG = 'data-client-localized';
    const LANG_KEY = 'erp-np-lang';
    let errorMessage = '';
    
    function getMsg(key, el) {
        let msg = ERR_FB;
        if (el.getAttribute(CLIENT_FLAG) === 'true') {
        msg = el.getAttribute(GUARD_MSG) || msg;
        } else {
        let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
            .toLowerCase().replace(/_/g, '-');
        lang = lang === 'pt-br' ? lang : lang.slice(0,2);
        msg = translations?.[lang]?.[key] ||
                el.getAttribute(GUARD_MSG) ||
                translations?.['en']?.[key] ||
                msg;
        if (msg !== ERR_FB) {
            el.setAttribute(GUARD_MSG, msg);
            el.setAttribute(CLIENT_FLAG, 'true');
        }
        }
        return msg;
    }
    
    function showError(message) {
        try {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }
        const hasBs = !!document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
        if (hasBs) {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.setAttribute('role','alert');
            toast.setAttribute('aria-live','assertive');
            toast.setAttribute('aria-atomic','true');
            const body = document.createElement('div');
            body.className = 'toast-body';
            body.textContent = message;
            toast.appendChild(body);
            container.appendChild(toast);
            bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
            alert(message);
        }
        } catch {
        alert(message);
        }
    }
    
    const onPointerUp = () => {
        if (errorMessage) {
        showError(errorMessage);
        errorMessage = '';
        }
    };
    document.addEventListener('pointerup', onPointerUp);
    new MutationObserver((muts, obs) => {
        muts.forEach(m => m.removedNodes.forEach(n => {
        if (n === document.documentElement) {
            document.removeEventListener('pointerup', onPointerUp);
            obs.disconnect();
        }
        }));
    }).observe(document.body, { childList:true, subtree:true });
    
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.querySelector('.signature-pad');
        const clearBtn = document.getElementById('clearSig');
        const saveBtn = document.getElementById('addSig');
        if (!canvas || !clearBtn || !saveBtn) return;
    
        let pad;
        try {
        pad = new SignaturePad(canvas);
        } catch {
        errorMessage = getMsg('signature_init_failed', canvas);
        return;
        }
    
        if (!clearBtn.dataset.listenerAttached) {
        clearBtn.dataset.listenerAttached = 'true';
        clearBtn.addEventListener('click', () => {
            try {
            pad.clear();
            } catch {
            errorMessage = getMsg('signature_init_failed', clearBtn);
            }
        });
        new MutationObserver((m, obs) => {
            m.forEach(mut => mut.removedNodes.forEach(node => {
            if (node === clearBtn) {
                clearBtn.removeEventListener('click', pad.clear);
                obs.disconnect();
            }
            }));
        }).observe(document.body, { childList:true, subtree:true });
        }
    
        if (!saveBtn.dataset.listenerAttached) {
        saveBtn.dataset.listenerAttached = 'true';
        saveBtn.addEventListener('click', () => {
            try {
            const dataURL = pad.toDataURL('image/png');
            document.getElementById('SignupImage1').value = dataURL;
            const url = '{{route(ViewsConstants::CTC.".signature.store")}}';
            if (!url) throw new Error('signature_save_failed');
            $.ajax({
                url,
                type: 'POST',
                data: $("form").serialize()
            })
            .done(res => {
                location.reload();
                toastrs('success', res.message, 'success');
                $('#exampleModal').modal('hide');
            })
            .fail(() => {
                throw new Error('signature_save_failed');
            });
            } catch (e) {
            errorMessage = getMsg(e.message, saveBtn);
            }
        });
        new MutationObserver((m, obs) => {
            m.forEach(mut => mut.removedNodes.forEach(node => {
            if (node === saveBtn) {
                saveBtn.removeEventListener('click', arguments.callee);
                obs.disconnect();
            }
            }));
        }).observe(document.body, { childList:true, subtree:true });
        }
    });
    })();
</script>
