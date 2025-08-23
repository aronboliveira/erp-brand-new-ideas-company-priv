@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{StacksConstants, ViewsConstants};
    $lang               = Utility::fetchUserLang();
    $fallbackRoute      = Route::has($data['fallback_url'])
        ? route($data['fallback_url'], $data)
        : (Route::has(Str::kebab($data['fallback_url']))
            ? route(Str::kebab($data['fallback_url']), $data)
            : '#');
    $formId             = 'submit_form';
    $fallbackGuardMsg   = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::BNK_TRF,
        'fallback_route_unavailable'
    ) ?? 'Fallback route is unavailable. Please contact technical support or your domain administrator.';
@endphp
<html>
  <head>
    @include('fragments.std', [
      'meta_title' => $meta_title,
      'meta_desc' => $meta_desc,
      'meta_vp' => ''
    ])
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <!-- @TODO: replace SET_YOUR_CLIENT_KEY_HERE with your client key -->
    <script type="text/javascript"
      src="https://app.sandbox.midtrans.com/snap/snap.js"
      data-client-key="{{ $data['midtrans_secret'] }}"></script>
    <!-- Note: replace with src="https://app.midtrans.com/snap/snap.js" for Production environment -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  </head>
  <body>
    <form
        action="{{ $fallbackRoute }}"
        id="{{ $formId }}"
        method="POST"
        data-url="{{ $fallbackRoute }}"
        data-guard-msg="{{ $fallbackGuardMsg }}"
    >
        @csrf
        <input type="hidden" name="json" id="json_callback">
    </form>
        <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
        ar: {
          snap_sdk_unavailable: 'خدمة الدفع غير محملة.',
          payment_init_failed: 'فشل بدء نافذة الدفع.',
          payment_popup_closed: 'أغلقت النافذة قبل إتمام الدفع.',
          response_submit_failed: 'فشل إرسال نتيجة الدفع.'
        },
        da: {
          snap_sdk_unavailable: 'Snap SDK er ikke indlæst.',
          payment_init_failed: 'Kunne ikke åbne betalingsvinduet.',
          payment_popup_closed: 'Du lukkede vinduet uden at gennemføre betalingen.',
          response_submit_failed: 'Kunne ikke sende betalingsresultatet.'
        },
        de: {
          snap_sdk_unavailable: 'Snap SDK ist nicht geladen.',
          payment_init_failed: 'Fehler beim Öffnen des Zahlungsfensters.',
          payment_popup_closed: 'Sie haben das Fenster geschlossen, ohne die Zahlung abzuschließen.',
          response_submit_failed: 'Fehler beim Senden des Zahlungsergebnisses.'
        },
        en: {
          snap_sdk_unavailable: 'Snap payment SDK is not loaded.',
          payment_init_failed: 'Failed to open payment popup.',
          payment_popup_closed: 'You closed the popup without finishing the payment.',
          response_submit_failed: 'Failed to submit payment response.'
        },
        es: {
          snap_sdk_unavailable: 'El SDK de Snap no está cargado.',
          payment_init_failed: 'Error al abrir la ventana de pago.',
          payment_popup_closed: 'Cerraste la ventana sin completar el pago.',
          response_submit_failed: 'Error al enviar la respuesta de pago.'
        },
        fr: {
          snap_sdk_unavailable: 'Le SDK Snap n’est pas chargé.',
          payment_init_failed: 'Échec de l’ouverture de la fenêtre de paiement.',
          payment_popup_closed: 'Vous avez fermé la fenêtre avant de terminer le paiement.',
          response_submit_failed: 'Échec de l’envoi de la réponse de paiement.'
        },
        he: {
          snap_sdk_unavailable: 'SDK התשלום לא נטען.',
          payment_init_failed: 'הפעלת חלון התשלום נכשלה.',
          payment_popup_closed: 'סגרתם את החלון מבלי להשלים את התשלום.',
          response_submit_failed: 'שליחת תשובת התשלום נכשלה.'
        },
        it: {
          snap_sdk_unavailable: 'Snap SDK non caricato.',
          payment_init_failed: 'Impossibile aprire la finestra di pagamento.',
          payment_popup_closed: 'Hai chiuso il popup senza completare il pagamento.',
          response_submit_failed: 'Invio della risposta di pagamento non riuscito.'
        },
        ja: {
          snap_sdk_unavailable: 'Snap SDK が読み込まれていません。',
          payment_init_failed: '支払いポップアップを開けませんでした。',
          payment_popup_closed: '支払いを完了せずにポップアップを閉じました。',
          response_submit_failed: '支払い結果の送信に失敗しました。'
        },
        nl: {
          snap_sdk_unavailable: 'Snap SDK is niet geladen.',
          payment_init_failed: 'Betalingspopup kon niet worden geopend.',
          payment_popup_closed: 'Je hebt de popup gesloten zonder de betaling af te ronden.',
          response_submit_failed: 'Betalingsrespons verzenden mislukt.'
        },
        pl: {
          snap_sdk_unavailable: 'Snap SDK nie jest załadowany.',
          payment_init_failed: 'Nie udało się otworzyć okna płatności.',
          payment_popup_closed: 'Zamknąłeś okno bez ukończenia płatności.',
          response_submit_failed: 'Nie udało się wysłać odpowiedzi płatności.'
        },
        pt: {
          snap_sdk_unavailable: 'SDK Snap não está carregado.',
          payment_init_failed: 'Falha ao abrir o pop-up de pagamento.',
          payment_popup_closed: 'Você fechou o pop-up sem concluir o pagamento.',
          response_submit_failed: 'Falha ao enviar resposta de pagamento.'
        },
        'pt-br': {
          snap_sdk_unavailable: 'SDK Snap não está carregado.',
          payment_init_failed: 'Falha ao abrir o pop-up de pagamento.',
          payment_popup_closed: 'Você fechou o pop-up sem concluir o pagamento.',
          response_submit_failed: 'Falha ao enviar resposta de pagamento.'
        },
        ru: {
          snap_sdk_unavailable: 'Snap SDK не загружен.',
          payment_init_failed: 'Не удалось открыть окно оплаты.',
          payment_popup_closed: 'Вы закрыли окно, не завершив оплату.',
          response_submit_failed: 'Не удалось отправить ответ оплаты.'
        },
        tr: {
          snap_sdk_unavailable: 'Snap SDK yüklenmedi.',
          payment_init_failed: 'Ödeme penceresi açılamadı.',
          payment_popup_closed: 'Ödemeyi tamamlamadan pencereyi kapattınız.',
          response_submit_failed: 'Ödeme yanıtı gönderilemedi.'
        },
        zh: {
          snap_sdk_unavailable: 'Snap SDK 未加载。',
          payment_init_failed: '无法打开支付弹窗。',
          payment_popup_closed: '您在完成支付前关闭了弹窗。',
          response_submit_failed: '提交支付响应失败。'
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
    <script defer>
      (() => {
        const errFb = '# ERROR';
        const dataClientLocalized = 'data-client-localized';
        const dataGuardMsg = 'data-guard-msg';
        const langSessionKey = 'erp-np-lang';
        function getLocalizedMessage(key, el) {
          let msg = errFb;
          if (el.getAttribute('data-sv-localized') === 'true'
          || el.getAttribute(dataClientLocalized) === 'true') {
            msg = el.getAttribute(dataGuardMsg) ?? errFb;
          } else {
            let lang = (window.sessionStorage.getItem(langSessionKey)
                      ?? document.documentElement.lang
                      ?? 'en').toLowerCase().replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg = window.translations?.[lang]?.[key]
              ?? el.getAttribute(dataGuardMsg)
              ?? window.translations?.['en']?.[key]
              ?? errFb;
            if (msg !== errFb) {
              el.setAttribute(dataGuardMsg, msg);
              el.setAttribute(dataClientLocalized, 'true');
            }
          }
          return msg;
        }
        function showError(message) {
          try {
            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
            let container = document.getElementById('toast-container');
            if (!container) {
              container = document.createElement('div');
              container.id = 'toast-container';
              document.body.appendChild(container);
            }
            if (bootstrapLink && window.bootstrap) {
              const toastEl = document.createElement('div');
              toastEl.className = 'toast';
              toastEl.setAttribute('role', 'alert');
              toastEl.setAttribute('aria-live', 'assertive');
              toastEl.setAttribute('aria-atomic', 'true');
              const body = document.createElement('div');
              body.className = 'toast-body';
              body.textContent = message;
              toastEl.appendChild(body);
              container.appendChild(toastEl);
              bootstrap.Toast.getOrCreateInstance(toastEl).show();
            } else {
              alert(message);
            }
          } catch {
            alert(message);
          }
        }
        let errorMessage = '';
        const onErrorPointerUp = () => {
          if (errorMessage) {
            showError(errorMessage);
            errorMessage = '';
          }
        };
        document.addEventListener('pointerup', onErrorPointerUp);
        new MutationObserver((muts, obs) => {
          muts.forEach(m => m.removedNodes.forEach(n => {
            if (n === document.documentElement) {
              document.removeEventListener('pointerup', onErrorPointerUp);
              obs.disconnect();
            }
          }));
        }).observe(document.body, { childList: true, subtree: true });
        document.addEventListener('DOMContentLoaded', () => {
          const token = '{{ $data['snap_token'] }}';
          if (!window.snap?.pay) {
            console.log('Snap SDK missing');
            showError(getLocalizedMessage('snap_sdk_unavailable', document.body));
            return;
          }
          try {
            window.snap.pay(token, {
              onSuccess: result => sendResponse(result),
              onPending: result => sendResponse(result),
              onError: result   => sendResponse(result),
              onClose: ()       => {
                errorMessage = getLocalizedMessage('payment_popup_closed', document.body);
              }
            });
          } catch {
            errorMessage = getLocalizedMessage('payment_init_failed', document.body);
          }
        });
        function sendResponse(result) {
          try {
            document.getElementById('json_callback').value = JSON.stringify(result);
            document.getElementById('submit_form')?.submit();
          } catch {
            errorMessage = getLocalizedMessage('response_submit_failed', document.body);
          }
        }
      })();
    </script>
    <script defer>
        (() => {
            const form = document.getElementById('{{ $formId }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', event => {
                try {
                    const action = form.getAttribute('action');
                    const url    = form.getAttribute('data-url');
                    if ((action && action !== '#') || (url && url !== '#')) return;
                    event.preventDefault();
                    const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                    let container       = document.getElementById('toast-container');
                    if (!container) {
                        container       = document.createElement('div');
                        container.id    = 'toast-container';
                        document.body.appendChild(container);
                    }
                    if (bootstrapLink && window.bootstrap) {
                        const toastEl      = document.createElement('div');
                        toastEl.className  = 'toast';
                        toastEl.setAttribute('role', 'alert');
                        toastEl.setAttribute('aria-live', 'assertive');
                        toastEl.setAttribute('aria-atomic', 'true');
                        const body         = document.createElement('div');
                        body.className     = 'toast-body';
                        body.textContent   = msg;
                        toastEl.appendChild(body);
                        container.appendChild(toastEl);
                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                    } else {
                        alert(msg);
                    }
                    form.setAttribute('data-failed-route', 'true');
                } catch (e) {}
            });
        })();
    </script>
  </body>
</html>
