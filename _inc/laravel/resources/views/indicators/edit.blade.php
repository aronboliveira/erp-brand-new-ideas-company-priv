{{Collective\Html\FormFacade::model($indicator,array('route' => array('indicators.update', $indicator->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('branch',__('Branch'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::select('branch',$brances,null,array('class'=>'form-control select','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('department',__('Department'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::select('department',$departments,null,array('class'=>'form-control select','required'=>'required','id'=>'department_id'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('designation',__('Designation'),['class'=>'form-label'])}}
                <select class="select form-control select2-multiple" id="designation_id" name="designation"
                        data-toggle="select2" data-placeholder="{{ __('Select Designation ...') }}" required>
                </select>
            </div>
        </div>

    </div>

     @foreach($performance as $performances)
    <div class="row">
        <div class="col-md-12 mt-3">
            <h6>{{$performances->name}}</h6>
            <hr class="mt-0">
        </div>
            @foreach($performances->types as $types)

            <div class="col-6">
                {{$types->name}}
            </div>
            <div class="col-6">
                <fieldset id='demo1' class="rating">
                    <input class="stars" type="radio" id="technical-5-{{$types->id}}" name="rating[{{$types->id}}]" value="5" {{ (isset($ratings[$types->id]) && $ratings[$types->id] == 5)? 'checked':''}}>
                    <label class="full" for="technical-5-{{$types->id}}" title="Awesome - 5 stars"></label>
                    <input class="stars" type="radio" id="technical-4-{{$types->id}}" name="rating[{{$types->id}}]" value="4" {{ (isset($ratings[$types->id]) && $ratings[$types->id] == 4)? 'checked':''}}>
                    <label class="full" for="technical-4-{{$types->id}}" title="Pretty good - 4 stars"></label>
                    <input class="stars" type="radio" id="technical-3-{{$types->id}}" name="rating[{{$types->id}}]" value="3" {{ (isset($ratings[$types->id]) && $ratings[$types->id] == 3)? 'checked':''}}>
                    <label class="full" for="technical-3-{{$types->id}}" title="Meh - 3 stars"></label>
                    <input class="stars" type="radio" id="technical-2-{{$types->id}}" name="rating[{{$types->id}}]" value="2" {{ (isset($ratings[$types->id]) && $ratings[$types->id] == 2)? 'checked':''}}>
                    <label class="full" for="technical-2-{{$types->id}}" title="Kinda bad - 2 stars"></label>
                    <input class="stars" type="radio" id="technical-1-{{$types->id}}" name="rating[{{$types->id}}]" value="1" {{ (isset($ratings[$types->id]) && $ratings[$types->id] == 1)? 'checked':''}}>
                    <label class="full" for="technical-1-{{$types->id}}" title="Sucks big time - 1 star"></label>
                </fieldset>
            </div>
        @endforeach
    </div>
    @endforeach

</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}
    <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
      ar: {
        designation_fetch_unavailable: 'تعذر جلب بيانات التعيين.',
        designation_fetch_failed:      'فشل تحميل بيانات التعيين.',
        designation_default:           'اختر أي تعيين'
      },
      da: {
        designation_fetch_unavailable: 'Kunne ikke hente tildelinger.',
        designation_fetch_failed:      'Kunne ikke hente tildelingsdata.',
        designation_default:           'Vælg en tildeling'
      },
      de: {
        designation_fetch_unavailable: 'Zuweisungen konnten nicht abgerufen werden.',
        designation_fetch_failed:      'Zuweisungsdaten konnten nicht geladen werden.',
        designation_default:           'Wählen Sie eine Zuordnung'
      },
      en: {
        designation_fetch_unavailable: 'Failed to fetch designations.',
        designation_fetch_failed:      'Failed to fetch designation data.',
        designation_default:           'Select any Designation'
      },
      es: {
        designation_fetch_unavailable: 'No se pudieron obtener las asignaciones.',
        designation_fetch_failed:      'Error al obtener datos de asignación.',
        designation_default:           'Selecciona cualquier designación'
      },
      fr: {
        designation_fetch_unavailable: 'Impossible de récupérer les affectations.',
        designation_fetch_failed:      'Impossible de récupérer les données d’affectation.',
        designation_default:           'Sélectionnez une désignation'
      },
      he: {
        designation_fetch_unavailable: 'נכשלו טעינת התפקידים.',
        designation_fetch_failed:      'נכשלו טעינת נתוני התפקיד.',
        designation_default:           'בחר תפקיד כלשהו'
      },
      it: {
        designation_fetch_unavailable: 'Impossibile recuperare le assegnazioni.',
        designation_fetch_failed:      'Impossibile recuperare i dati di assegnazione.',
        designation_default:           'Seleziona una qualunque designazione'
      },
      ja: {
        designation_fetch_unavailable: '役割の取得に失敗しました。',
        designation_fetch_failed:      '役割データの取得に失敗しました。',
        designation_default:           '任意の役割を選択してください'
      },
      nl: {
        designation_fetch_unavailable: 'Kon toewijzingen niet ophalen.',
        designation_fetch_failed:      'Kon toewijzingsgegevens niet ophalen.',
        designation_default:           'Selecteer een willekeurige aanstelling'
      },
      pl: {
        designation_fetch_unavailable: 'Nie udało się pobrać przypisań.',
        designation_fetch_failed:      'Nie udało się pobrać danych przypisania.',
        designation_default:           'Wybierz dowolne stanowisko'
      },
      pt: {
        designation_fetch_unavailable: 'Falha ao obter atribuições.',
        designation_fetch_failed:      'Falha ao obter dados de designação.',
        designation_default:           'Selecione qualquer designação'
      },
      'pt-br': {
        designation_fetch_unavailable: 'Falha ao obter atribuições.',
        designation_fetch_failed:      'Falha ao obter dados de designação.',
        designation_default:           'Selecione qualquer designação'
      },
      ru: {
        designation_fetch_unavailable: 'Не удалось получить назначения.',
        designation_fetch_failed:      'Не удалось получить данные о назначении.',
        designation_default:           'Выберите любое назначение'
      },
      tr: {
        designation_fetch_unavailable: 'Atamalar alınamadı.',
        designation_fetch_failed:      'Atama verileri alınamadı.',
        designation_default:           'Herhangi bir atama seçin'
      },
      zh: {
        designation_fetch_unavailable: '无法获取职称。',
        designation_fetch_failed:      '无法获取职称数据。',
        designation_default:           '请选择任何职称'
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
      const ERR_FB = '# ERROR';
      const CLIENT_FLAG = 'data-client-localized';
      const GUARD_MSG = 'data-guard-msg';
      const LANG_KEY = 'erp-np-lang';
    
      const getMsg = (key, el) => {
        let msg = ERR_FB;
        if (el?.getAttribute(CLIENT_FLAG) === 'true') {
          msg = el.getAttribute(GUARD_MSG) || msg;
        } else {
          let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
            .toLowerCase().replace(/_/g, '-');
          lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
          msg = window.translations?.[lang]?.[key]
            || el?.getAttribute(GUARD_MSG)
            || window.translations?.['en']?.[key]
            || msg;
          if (msg !== ERR_FB && el) {
            el.setAttribute(GUARD_MSG, msg);
            el.setAttribute(CLIENT_FLAG, 'true');
          }
        }
        return msg;
      };
    
      const showError = message => {
        try {
          const bsAvailable = !!document.querySelector('link[href*="bootstrap"]') && !!window.bootstrap?.Toast;
          if (bsAvailable) {
            const container = document.getElementById('toast-container') 
              || (() => {
                const c = document.createElement('div');
                c.id = 'toast-container';
                document.body.appendChild(c);
                return c;
              })();
            const toastEl = document.createElement('div');
            toastEl.className = 'toast';
            toastEl.setAttribute('role','alert');
            toastEl.setAttribute('aria-live','assertive');
            toastEl.setAttribute('aria-atomic','true');
            const body = document.createElement('div');
            body.className = 'toast-body';
            body.textContent = message;
            toastEl.appendChild(body);
            container.appendChild(toastEl);
            window.bootstrap.Toast.getOrCreateInstance(toastEl).show();
          } else {
            alert(message);
          }
        } catch {
          alert(message);
        }
      };
    
      const fetchDesignation = did => {
        try {
          if (!did) throw new Error('designation_fetch_unavailable');
          const url = '{{ route("employees.json") }}';
          if (!url || url === '#') throw new Error('designation_fetch_unavailable');
          $.ajax({
            url,
            type: 'POST',
            data: { department_id: did, _token: '{{ csrf_token() }}' },
          })
          .done(data => {
            const sel = document.getElementById('designation_id');
            if (!sel) return;
            sel.innerHTML = '<option value="">' 
              + (window.translations?.[navigator.language.slice(0,2)]?.designation_default 
                 || 'Select any Designation') 
              + '</option>';
            data.forEach((v, k) => {
              const opt = document.createElement('option');
              opt.value = k;
              if (k == '{{ $indicator->designation }}') opt.selected = true;
              opt.textContent = v;
              sel.appendChild(opt);
            });
          })
          .fail(() => { throw new Error('designation_fetch_failed'); });
        } catch (e) {
          const el = document.getElementById('department_id');
          showError(getMsg(e.message, el));
        }
      };
    
      const init = () => {
        const dep = $('#department_id');
        if (!dep.length) return;
        const did = dep.val();
        fetchDesignation(did);
        dep.off('change.designationListener')
           .on('change.designationListener', () => {
             fetchDesignation(dep.val());
           });
      };
    
      $(document).ready(init);
    
      new MutationObserver((muts, obs) => {
        muts.forEach(m => Array.from(m.removedNodes).forEach(n => {
          if (n.id === 'department_id') {
            obs.disconnect();
          }
        }));
      }).observe(document.body, { childList: true, subtree: true });
    })();
</script>
    


