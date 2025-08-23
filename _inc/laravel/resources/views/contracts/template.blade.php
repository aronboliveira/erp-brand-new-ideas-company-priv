
@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        YieldingConstants,
        SettingsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $logo = Utility::getFile('uploads/logo/');
    $dark_logo   = Utility::getValByName('dark_logo');
    $img = asset($logo . '/' . (isset($dark_logo) && !empty($dark_logo) ? $dark_logo : SettingsConstants::CPN_LG_DK_DEF));
    $settings = Utility::settings();
@endphp
@extends(ExtendingLayoutsConstants::CTC)
@section(YieldingConstants::CTC_PG_TTL)
@endsection
@section('title')
@endsection
@section(YieldingConstants::CTC_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CL10 }}">
            <div class="{{ VC::CT }}">
                <div>
                    <div class="{{ VC::CD }} mt-5" id="printTable" style="margin-left:180px;margin-right:-57px;">
                        <div class="card-body" id="boxes">
                            <div class="{{ VC::RW }} invoice-title mt-2">
                                <div class="{{ VC::CS12 }} {{ VC::CM6 }} {{ VC::CL6 }}">
                                    <img src="{{ $img }}" style="max-width:150px;">
                                </div>
                                <div class="{{ VC::CS12 }} {{ VC::CM6 }} {{ VC::CL6 }} text-end">
                                    <h3 class="invoice-number">{{ $user?->contractNumberFormat($contract->id) }}</h3>
                                </div>
                            </div>

                            <div class="{{ VC::R_ALC_M4 }}">
                                <div class="{{ VC::CS12 }} {{ VC::CM6 }} mt-3">
                                    <div class="{{ VC::C12 }} mb-3">
                                        <h6 class="d-inline-block m-0 d-print-none">{{ __('Contract Type  :') }}</h6>
                                        <span class="text-md">{{ $contract->types->name }}</span>
                                    </div>
                                    <div class="{{ VC::C12 }}">
                                        <h6 class="d-inline-block m-0 d-print-none">{{ __('Contract Value   :') }}</h6>
                                        <span class="text-md">{{ $user?->priceFormat($contract->value) }}</span>
                                    </div>
                                </div>
                                <div class="{{ VC::CS12 }} {{ VC::CM6 }} text-sm-end">
                                    <div class="float-end">
                                        <h6 class="d-inline-block m-0 d-print-none">{{ __('Start Date   :') }}</h6>
                                        <span class="text-md">{{ $user?->dateFormat($contract->start_date) }}</span>
                                        <div class="mt-3">
                                            <h6 class="d-inline-block m-0 d-print-none">{{ __('End Date   :') }}</h6>
                                            <span class="text-md">{{ $user?->dateFormat($contract->end_date) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>{!! $contract->description !!}</div><br>
                            <div>{!! $contract->contract_description !!}</div>

                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::CS6 }}">
                                    <img width="200px" src="{{ $contract->company_signature }}">
                                    <h5 class="mt-auto">{{ __('Company Signature') }}</h5>
                                </div>
                                <div class="{{ VC::CS6 }} text-end">
                                    <img width="150px" src="{{ $contract->client_signature }}">
                                    <h5 class="mt-auto">{{ __('Client Signature') }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::CTC_SCR_PG)
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
        <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
          ar: {
            pdf_generation_failed: 'فشل إنشاء PDF.',
            window_close_failed: 'فشل إغلاق النافذة.'
          },
          da: {
            pdf_generation_failed: 'Kunne ikke generere PDF.',
            window_close_failed: 'Kunne ikke lukke vindue.'
          },
          de: {
            pdf_generation_failed: 'PDF-Erstellung fehlgeschlagen.',
            window_close_failed: 'Fenster konnte nicht geschlossen werden.'
          },
          en: {
            pdf_generation_failed: 'Failed to generate PDF.',
            window_close_failed: 'Failed to close window.'
          },
          es: {
            pdf_generation_failed: 'Error al generar PDF.',
            window_close_failed: 'Error al cerrar la ventana.'
          },
          fr: {
            pdf_generation_failed: 'Échec de la génération du PDF.',
            window_close_failed: 'Échec de la fermeture de la fenêtre.'
          },
          he: {
            pdf_generation_failed: 'יצירת PDF נכשלה.',
            window_close_failed: 'נכשל סגירת החלון.'
          },
          it: {
            pdf_generation_failed: 'Creazione PDF non riuscita.',
            window_close_failed: 'Chiusura finestra non riuscita.'
          },
          ja: {
            pdf_generation_failed: 'PDF の生成に失敗しました。',
            window_close_failed: 'ウィンドウを閉じることに失敗しました。'
          },
          nl: {
            pdf_generation_failed: 'Genereren van PDF mislukt.',
            window_close_failed: 'Venster kon niet worden gesloten.'
          },
          pl: {
            pdf_generation_failed: 'Nie udało się wygenerować pliku PDF.',
            window_close_failed: 'Nie udało się zamknąć okna.'
          },
          pt: {
            pdf_generation_failed: 'Falha ao gerar PDF.',
            window_close_failed: 'Falha ao fechar a janela.'
          },
          'pt-br': {
            pdf_generation_failed: 'Falha ao gerar PDF.',
            window_close_failed: 'Falha ao fechar a janela.'
          },
          ru: {
            pdf_generation_failed: 'Не удалось создать PDF.',
            window_close_failed: 'Не удалось закрыть окно.'
          },
          tr: {
            pdf_generation_failed: 'PDF oluşturma başarısız.',
            window_close_failed: 'Pencere kapatılamadı.'
          },
          zh: {
            pdf_generation_failed: '生成 PDF 失败。',
            window_close_failed: '关闭窗口失败。'
          }
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
    <script>
        (() => {
        const errFb = '# ERROR';
        const dataClientLocalized = 'data-client-localized';
        const dataGuardMsg = 'data-guard-msg';
        const langSessionKey = 'erp-np-lang';
        let errorMessage = '';
        
        const getLocalizedMessage = (msgKey, el) => {
            let msg = errFb;
            if (el.getAttribute(dataClientLocalized) === 'true') {
            msg = el.getAttribute(dataGuardMsg) ?? errFb;
            } else {
            let lang = (
                sessionStorage.getItem(langSessionKey) ??
                document.documentElement.lang ??
                'en'
            ).toLowerCase().replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg = translations?.[lang]?.[msgKey] ??
                    el.getAttribute(dataGuardMsg) ??
                    translations?.['en']?.[msgKey] ??
                    errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, 'true');
            }
            }
            return msg;
        };
        
        const showError = message => {
            try {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
            }
            const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
            if (bs) {
                const toast = document.createElement('div');
                toast.className = 'toast';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                const body = document.createElement('div');
                body.className = 'toast-body';
                toast.appendChild(body);
                container.appendChild(toast);
                if (toast.getAttribute('data-click-listener') !== 'true') {
                toast.addEventListener('click', () => body.textContent = message);
                toast.setAttribute('data-click-listener', 'true');
                }
                body.textContent = message;
                new bootstrap.Toast(toast).show();
            } else {
                alert(message);
            }
            } catch {
            alert(message);
            }
        };
        
        const onPointerUp = () => {
            if (errorMessage) {
            showError(errorMessage);
            errorMessage = '';
            }
        };
        document.addEventListener('pointerup', onPointerUp);
        new MutationObserver((muts, obs) => {
            muts.forEach(m =>
            Array.from(m.removedNodes).forEach(n => {
                if (n === document.documentElement) {
                document.removeEventListener('pointerup', onPointerUp);
                obs.disconnect();
                }
            })
            );
        }).observe(document.body, { childList: true, subtree: true });
        
        const closeScript = () => {
            setTimeout(() => {
            try {
                window.open(window.location, '_self').close();
            } catch {
                errorMessage = getLocalizedMessage('window_close_failed', document.body);
            }
            }, 1000);
        };
        
        window.addEventListener('load', () => {
            try {
            if (typeof html2pdf !== 'function') {
                console.log('html2pdf library not loaded');
                throw new Error('pdf_generation_failed');
            }
            const element = document.getElementById('boxes');
            if (!element) throw new Error('pdf_generation_failed');
            const opt = {
                filename: '{{ App\Models\Utility::contractNumberFormat($contract->id) }}',
                image: { type: 'jpeg', quality: 1 },
                html2canvas: { scale: 4, dpi: 72, letterRendering: true },
                jsPDF: { unit: 'in', format: 'A4' }
            };
            html2pdf()
                .set(opt)
                .from(element)
                .save()
                .then(closeScript)
                .catch(() => {
                throw new Error('pdf_generation_failed');
                });
            } catch (e) {
            errorMessage = getLocalizedMessage('pdf_generation_failed', document.body);
            }
        });
        })();
    </script>
@endpush
