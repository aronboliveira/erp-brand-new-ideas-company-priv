@php
    use App\Config\Constants\PlansConstants;
@endphp
{{ Collective\Html\FormFacade::model($holiday, array('route' => array('holiday.update', $holiday->id), 'method' => 'PUT')) }}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
    @endphp
    @if($plan?->{PlansConstants::COL_GPT} == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['holiday']) }}"
           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
        </a>
    </div>
    @endif
    {{-- end for ai module--}}
    <div class="row">
        <div class="form-group col-md-12">
            {{Collective\Html\FormFacade::label('occasion',__('Occasion'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::text('occasion',null,array('class'=>'form-control'))}}
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('date',__('Start Date'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::date('date',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('end_date',__('End Date'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::date('end_date',null,array('class'=>'form-control'))}}
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}
<script async>
    window.translations = {
      ar: {
        datepicker_plugin_unavailable: 'مكون التاريخ غير متاح.',
        datepicker_init_failed:        'فشل تهيئة محدد التاريخ.'
      },
      da: {
        datepicker_plugin_unavailable: 'Datepicker-plugin ikke tilgængelig.',
        datepicker_init_failed:        'Kunne ikke initialisere datovælger.'
      },
      de: {
        datepicker_plugin_unavailable: 'Datepicker-Plugin nicht verfügbar.',
        datepicker_init_failed:        'Initialisierung des Datumsauswahlfelds fehlgeschlagen.'
      },
      en: {
        datepicker_plugin_unavailable: 'Datepicker plugin unavailable.',
        datepicker_init_failed:        'Failed to initialize datepicker.'
      },
      es: {
        datepicker_plugin_unavailable: 'Complemento de selector de fecha no disponible.',
        datepicker_init_failed:        'Error al inicializar el selector de fecha.'
      },
      fr: {
        datepicker_plugin_unavailable: 'Plugin de sélecteur de date indisponible.',
        datepicker_init_failed:        'Échec de l’initialisation du sélecteur de date.'
      },
      he: {
        datepicker_plugin_unavailable: 'תוסף בוחר התאריך אינו זמין.',
        datepicker_init_failed:        'ההפעלה של בוחר התאריך נכשלה.'
      },
      it: {
        datepicker_plugin_unavailable: 'Plugin del selettore data non disponibile.',
        datepicker_init_failed:        'Impossibile inizializzare il selettore data.'
      },
      ja: {
        datepicker_plugin_unavailable: '日付ピッカー プラグインが利用できません。',
        datepicker_init_failed:        '日付ピッカーの初期化に失敗しました。'
      },
      nl: {
        datepicker_plugin_unavailable: 'Datepicker-plug-in niet beschikbaar.',
        datepicker_init_failed:        'Kon datepicker niet initialiseren.'
      },
      pl: {
        datepicker_plugin_unavailable: 'Wtyczka wyboru daty niedostępna.',
        datepicker_init_failed:        'Nie udało się zainicjalizować selektora daty.'
      },
      pt: {
        datepicker_plugin_unavailable: 'Plugin de seletor de data indisponível.',
        datepicker_init_failed:        'Falha ao inicializar o seletor de data.'
      },
      'pt-br': {
        datepicker_plugin_unavailable: 'Plugin de datepicker indisponível.',
        datepicker_init_failed:        'Falha ao inicializar o datepicker.'
      },
      ru: {
        datepicker_plugin_unavailable: 'Плагин выбора даты недоступен.',
        datepicker_init_failed:        'Не удалось инициализировать выбор даты.'
      },
      tr: {
        datepicker_plugin_unavailable: 'Tarih seçici eklentisi kullanılamıyor.',
        datepicker_init_failed:        'Tarih seçici başlatılamadı.'
      },
      zh: {
        datepicker_plugin_unavailable: '日期选择插件不可用。',
        datepicker_init_failed:        '初始化日期选择器失败。'
      }
    };
</script>
<script defer>
    (() => {
      const ERR_FB      = '# ERROR';
      const CLIENT_FLAG = 'data-client-localized';
      const GUARD_MSG   = 'data-guard-msg';
      const LANG_KEY    = 'erp-np-lang';
    
      const getMsg = (key, el) => {
        let msg = ERR_FB;
        if (el.getAttribute(CLIENT_FLAG) === 'true') {
          msg = el.getAttribute(GUARD_MSG) || msg;
        } else {
          let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
            .toLowerCase().replace(/_/g,'-');
          lang = (lang === 'pt-br' ? lang : lang.slice(0,2));
          msg = window.translations?.[lang]?.[key]
             || el.getAttribute(GUARD_MSG)
             || window.translations?.['en']?.[key]
             || msg;
          if (msg !== ERR_FB) {
            el.setAttribute(GUARD_MSG, msg);
            el.setAttribute(CLIENT_FLAG, 'true');
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
          const hasBS = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
          if (hasBS) {
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
      };
    
      document.addEventListener('DOMContentLoaded', () => {
        try {
          if (typeof jQuery === 'undefined' || typeof jQuery.fn.daterangepicker !== 'function') {
            throw new Error('datepicker_plugin_unavailable');
          }
          const els = document.querySelectorAll('.datepicker');
          if (!els.length) return;
          els.forEach(el => {
            const locale = window.date_picker_locale || {};
            jQuery(el).daterangepicker({
              singleDatePicker: true,
              locale,
              locale: { ...locale, format: 'YYYY-MM-DD' }
            });
          });
        } catch (e) {
          const key = e.message === 'datepicker_plugin_unavailable'
            ? 'datepicker_plugin_unavailable'
            : 'datepicker_init_failed';
          const msg = getMsg(key, document.documentElement);
          showError(msg);
        }
      });
    })();
</script>
    
