@php
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        PlansConstants,
        StacksConstants,
        ViewClassNamesConstants as VC
    };

    $lang        = Utility::fetchUserLang();
    $routeName   = ViewsConstants::CPN_PL . '.store';
    $storeRoute  = Route::has($routeName)
        ? route($routeName)
        : (Route::has(Str::kebab($routeName))
            ? route(Str::kebab($routeName))
            : '#');
    $formId      = 'companyPolicyCreateForm';
    $guardMsg    = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CPN_PL,
        'company_policy_store_route_unavailable'
    ) ?? 'Company Policy store route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::open([
    'route'          => [ViewsConstants::CPN_PL . '.store'],
    'method'         => 'post',
    'enctype'        => 'multipart/form-data',
    'id'             => $formId,
    'data-url'       => $storeRoute,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        @php $plan = Utility::getChatGPTSettings(); @endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="{{ VC::FEND }}">
                <a
                    href="#"
                    data-size="md"
                    class="{{ VC::BT_PRM }} {{ VC::BT_LG }} btn-icon btn-sm"
                    data-ajax-popup-over="true"
                    data-url="{{ route('generate',['company policy']) }}"
                    data-bs-placement="top"
                    data-title="{{ __('Generate content with AI') }}"
                >
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('branch', __('Branch'), ['class' => VC::FM_LB]) }}
                {{ Form::select('branch', $branch, null, ['class' => VC::FM_CT . ' select', 'required' => 'required']) }}
            </div>
            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('title', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT]) }}
            </div>
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('attachment', __('Attachment'), ['class' => VC::FM_LB]) }}
                <div class="choose-file {{ VC::FM_G }}">
                    <label for="attachment" class="{{ VC::FM_LB }}">
                        <input
                            type="file"
                            class="{{ VC::FM_CT }}"
                            name="attachment"
                            id="attachment"
                            data-filename="attachment_create"
                        >
                        <img id="image" class="mt-3" style="width:25%;" />
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input
            type="button"
            value="{{ __('Cancel') }}"
            class="{{ VC::BT_LG }}"
            data-bs-dismiss="modal"
        >
        <input
            type="submit"
            value="{{ __('Create') }}"
            class="{{ VC::BT_PRM }}"
        >
    </div>
{{ Form::close() }}
    <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
    ar: {
      selection_failed:                 'فشل تغيير النوع.',
      employee_fetch_failed:            'فشل جلب بيانات الموظف.',
      customer_fetch_failed:            'فشل جلب بيانات العميل.',
      vendor_fetch_failed:              'فشل جلب بيانات البائع.',
      repeater_initialization_failed:   'فشل تهيئة المكرر.',
      item_fetch_failed:                'فشل جلب بيانات الصنف.',
      calculation_failed:               'فشل حساب الإجماليات.',
      repeater_delete_failed:           'فشل حذف عنصر المكرر.',
      preview_failed:                   'فشل عرض المرفق.'
    },
    da: {
      selection_failed:                 'Kunne ikke ændre typen.',
      employee_fetch_failed:            'Kunne ikke hente medarbejderdata.',
      customer_fetch_failed:            'Kunne ikke hente kundedata.',
      vendor_fetch_failed:              'Kunne ikke hente leverandørdata.',
      repeater_initialization_failed:   'Kunne ikke initialisere gentager.',
      item_fetch_failed:                'Kunne ikke hente varedata.',
      calculation_failed:               'Kunne ikke beregne totaler.',
      repeater_delete_failed:           'Kunne ikke slette gentagerelement.',
      preview_failed:                   'Forhåndsvisning mislykkedes.'
    },
    de: {
      selection_failed:                 'Auswahl konnte nicht geändert werden.',
      employee_fetch_failed:            'Mitarbeiterdaten konnten nicht geladen werden.',
      customer_fetch_failed:            'Kundendaten konnten nicht geladen werden.',
      vendor_fetch_failed:              'Anbieterdaten konnten nicht geladen werden.',
      repeater_initialization_failed:   'Initialisierung des Repeaters fehlgeschlagen.',
      item_fetch_failed:                'Elementdaten konnten nicht abgerufen werden.',
      calculation_failed:               'Berechnung der Summen fehlgeschlagen.',
      repeater_delete_failed:           'Löschen des Repeater-Elements fehlgeschlagen.',
      preview_failed:                   'Vorschau fehlgeschlagen.'
    },
    en: {
      selection_failed:                 'Failed to change type.',
      employee_fetch_failed:            'Failed to load employee details.',
      customer_fetch_failed:            'Failed to load customer details.',
      vendor_fetch_failed:              'Failed to load vendor details.',
      repeater_initialization_failed:   'Failed to initialize repeater.',
      item_fetch_failed:                'Failed to fetch item data.',
      calculation_failed:               'Failed to calculate totals.',
      repeater_delete_failed:           'Failed to delete repeater item.',
      preview_failed:                   'Failed to preview attachment.'
    },
    es: {
      selection_failed:                 'Error al cambiar el tipo.',
      employee_fetch_failed:            'Error al cargar datos del empleado.',
      customer_fetch_failed:            'Error al cargar datos del cliente.',
      vendor_fetch_failed:              'Error al cargar datos del proveedor.',
      repeater_initialization_failed:   'No se pudo inicializar el repetidor.',
      item_fetch_failed:                'No se pudieron obtener los datos del artículo.',
      calculation_failed:               'No se pudieron calcular los totales.',
      repeater_delete_failed:           'Error al eliminar elemento del repetidor.',
      preview_failed:                   'Error al previsualizar el archivo.'
    },
    fr: {
      selection_failed:                 'Échec du changement de type.',
      employee_fetch_failed:            'Échec du chargement des détails de l’employé.',
      customer_fetch_failed:            'Échec du chargement des détails du client.',
      vendor_fetch_failed:              'Échec du chargement des détails du fournisseur.',
      repeater_initialization_failed:   'Échec de l’initialisation du répéteur.',
      item_fetch_failed:                'Échec de la récupération des données de l’article.',
      calculation_failed:               'Échec du calcul des totaux.',
      repeater_delete_failed:           'Échec de la suppression de l’élément du répéteur.',
      preview_failed:                   'Échec de l’aperçu de la pièce jointe.'
    },
    he: {
      selection_failed:                 'נכשל שינוי הסוג.',
      employee_fetch_failed:            'נכשלו טעינת פרטי העובד.',
      customer_fetch_failed:            'נכשלו טעינת פרטי הלקוח.',
      vendor_fetch_failed:              'נכשלו טעינת פרטי הספק.',
      repeater_initialization_failed:   'נכשלה אתחול המחזור.',
      item_fetch_failed:                'נכשלו טעינת נתוני הפריט.',
      calculation_failed:               'נכשל חישוב הסיכומים.',
      repeater_delete_failed:           'נכשל מחיקת פריט המחזור.',
      preview_failed:                   'נכשל תצוגת המקדמה.'
    },
    it: {
      selection_failed:                 'Impossibile cambiare tipo.',
      employee_fetch_failed:            'Impossibile caricare i dettagli del dipendente.',
      customer_fetch_failed:            'Impossibile caricare i dettagli del cliente.',
      vendor_fetch_failed:              'Impossibile caricare i dettagli del fornitore.',
      repeater_initialization_failed:   'Impossibile inizializzare il ripetitore.',
      item_fetch_failed:                'Impossibile recuperare i dati dell’articolo.',
      calculation_failed:               'Impossibile calcolare i totali.',
      repeater_delete_failed:           'Impossibile eliminare l’elemento del ripetitore.',
      preview_failed:                   'Impossibile visualizzare l’allegato.'
    },
    ja: {
      selection_failed:                 'タイプの変更に失敗しました。',
      employee_fetch_failed:            '従業員の詳細の読み込みに失敗しました。',
      customer_fetch_failed:            '顧客の詳細の読み込みに失敗しました。',
      vendor_fetch_failed:              'ベンダーの詳細の読み込みに失敗しました。',
      repeater_initialization_failed:   'リピーターの初期化に失敗しました。',
      item_fetch_failed:                'アイテムデータの取得に失敗しました。',
      calculation_failed:               '合計の計算に失敗しました。',
      repeater_delete_failed:           'リピーター項目の削除に失敗しました。',
      preview_failed:                   '添付ファイルのプレビューに失敗しました。'
    },
    nl: {
      selection_failed:                 'Kon type niet wijzigen.',
      employee_fetch_failed:            'Kon medewerkersgegevens niet laden.',
      customer_fetch_failed:            'Kon klantgegevens niet laden.',
      vendor_fetch_failed:              'Kon leveranciersgegevens niet laden.',
      repeater_initialization_failed:   'Kon herhaler niet initialiseren.',
      item_fetch_failed:                'Kon artikelgegevens niet ophalen.',
      calculation_failed:               'Kon totalen niet berekenen.',
      repeater_delete_failed:           'Kon herhaler-item niet verwijderen.',
      preview_failed:                   'Kan voorbeeld niet weergeven.'
    },
    pl: {
      selection_failed:                 'Nie udało się zmienić typu.',
      employee_fetch_failed:            'Nie udało się załadować danych pracownika.',
      customer_fetch_failed:            'Nie udało się załadować danych klienta.',
      vendor_fetch_failed:              'Nie udało się załadować danych dostawcy.',
      repeater_initialization_failed:   'Nie udało się zainicjować powtarzacza.',
      item_fetch_failed:                'Nie udało się pobrać danych elementu.',
      calculation_failed:               'Nie udało się obliczyć sum.',
      repeater_delete_failed:           'Nie udało się usunąć elementu powtarzacza.',
      preview_failed:                   'Nie udało się wyświetlić podglądu.'
    },
    pt: {
      selection_failed:                 'Falha ao alterar tipo.',
      employee_fetch_failed:            'Falha ao carregar dados do funcionário.',
      customer_fetch_failed:            'Falha ao carregar dados do cliente.',
      vendor_fetch_failed:              'Falha ao carregar dados do fornecedor.',
      repeater_initialization_failed:   'Falha ao inicializar repetidor.',
      item_fetch_failed:                'Falha ao buscar dados do item.',
      calculation_failed:               'Falha ao calcular totais.',
      repeater_delete_failed:           'Falha ao excluir item do repetidor.',
      preview_failed:                   'Falha ao visualizar o anexo.'
    },
    'pt-br': {
      selection_failed:                 'Falha ao alterar tipo.',
      employee_fetch_failed:            'Falha ao carregar dados do funcionário.',
      customer_fetch_failed:            'Falha ao carregar dados do cliente.',
      vendor_fetch_failed:              'Falha ao carregar dados do fornecedor.',
      repeater_initialization_failed:   'Falha ao inicializar repetidor.',
      item_fetch_failed:                'Falha ao buscar dados do item.',
      calculation_failed:               'Falha ao calcular totais.',
      repeater_delete_failed:           'Falha ao excluir item do repetidor.',
      preview_failed:                   'Falha ao visualizar o anexo.'
    },
    ru: {
      selection_failed:                 'Не удалось изменить тип.',
      employee_fetch_failed:            'Не удалось загрузить данные сотрудника.',
      customer_fetch_failed:            'Не удалось загрузить данные клиента.',
      vendor_fetch_failed:              'Не удалось загрузить данные поставщика.',
      repeater_initialization_failed:   'Не удалось инициализировать повторитель.',
      item_fetch_failed:                'Не удалось получить данные элемента.',
      calculation_failed:               'Не удалось вычислить итоги.',
      repeater_delete_failed:           'Не удалось удалить элемент повторителя.',
      preview_failed:                   'Не удалось просмотреть вложение.'
    },
    tr: {
      selection_failed:                 'Tür değiştirilemedi.',
      employee_fetch_failed:            'Çalışan bilgileri yüklenemedi.',
      customer_fetch_failed:            'Müşteri bilgileri yüklenemedi.',
      vendor_fetch_failed:              'Tedarikçi bilgileri yüklenemedi.',
      repeater_initialization_failed:   'Tekrarlayıcı başlatılamadı.',
      item_fetch_failed:                'Öğe verileri alınamadı.',
      calculation_failed:               'Toplamlar hesaplanamadı.',
      repeater_delete_failed:           'Tekrarlayıcı öğe silinemedi.',
      preview_failed:                   'Önizleme başarısız oldu.'
    },
    zh: {
      selection_failed:                 '更改类型失败。',
      employee_fetch_failed:            '无法加载员工详情。',
      customer_fetch_failed:            '无法加载客户详情。',
      vendor_fetch_failed:              '无法加载供应商详情。',
      repeater_initialization_failed:   '重复器初始化失败。',
      item_fetch_failed:                '获取项目数据失败。',
      calculation_failed:               '计算总计失败。',
      repeater_delete_failed:           '删除重复器项目失败。',
      preview_failed:                   '预览附件失败。'
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
<script defer>
    (() => {
      const errFb = '# ERROR';
      const dataClientLocalized = 'data-client-localized';
      const dataGuardMsg = 'data-guard-msg';
    
      const el = document.getElementById('attachment');
      if (!el || el.getAttribute('data-listener-active') === 'true') return;
      el.setAttribute('data-listener-active', 'true');
    
      el.addEventListener('change', function(e) {
        try {
          const file = e.target.files?.[0];
          if (!file) return;
          const img = document.getElementById('image');
          if (!img) throw new Error('noIMG');
          img.src = URL.createObjectURL(file);
        } catch {
          let msg = errFb;
          if (
            el.getAttribute('data-sv-localized') === 'true' ||
            el.getAttribute(dataClientLocalized) === 'true'
          ) {
            msg = el.getAttribute(dataGuardMsg) || errFb;
          } else {
            let lang = (
              window.sessionStorage.getItem('erp-np-lang') ||
              document.documentElement.lang ||
              'en'
            )
              .toLowerCase()
              .replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            const key = 'preview_failed';
            msg =
              window.translations?.[lang]?.[key] ||
              el.getAttribute(dataGuardMsg) ||
              window.translations?.['en']?.[key] ||
              errFb;
            if (msg !== errFb) {
              el.setAttribute(dataGuardMsg, msg);
              el.setAttribute(dataClientLocalized, 'true');
            }
          }
          const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
          if (bootstrapLink && window.bootstrap) {
            let container = document.getElementById('toast-container');
            if (!container) {
              container = document.createElement('div');
              container.id = 'toast-container';
              document.body.appendChild(container);
            }
            const toastEl = document.createElement('div');
            toastEl.className = 'toast';
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            const body = document.createElement('div');
            body.className = 'toast-body';
            body.textContent = msg;
            toastEl.appendChild(body);
            container.appendChild(toastEl);
            bootstrap.Toast.getOrCreateInstance(toastEl).show();
          } else {
            alert(msg);
          }
        }
      });
    
      const observer = new MutationObserver(() => {
        if (!document.getElementById('attachment')) observer.disconnect();
      });
      observer.observe(document.body, { childList: true, subtree: true });
    })();
</script>
