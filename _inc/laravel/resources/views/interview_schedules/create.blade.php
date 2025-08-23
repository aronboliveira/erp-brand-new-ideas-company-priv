
{{Collective\Html\FormFacade::open(array('url'=>'interview-schedule','method'=>'post'))}}
    <div class="modal-body">

    <div class="row">
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('candidate',__('Interview To'),['class'=>'form-label'])}}
            {{ Collective\Html\FormFacade::select('candidate', $candidates,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('employee',__('Interviewer'),['class'=>'form-label'])}}
            {{ Collective\Html\FormFacade::select('employee', $employees,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('date',__('Interview Date'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::date('date',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('time',__('Interview Time'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::time('time',null,array('class'=>'form-control timepicker'))}}
        </div>
        <div class="form-group col-md-12">
            {{Collective\Html\FormFacade::label('comment',__('Comment'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::textarea('comment',null,array('class'=>'form-control'))}}
        </div>

        @if(isset($settings['google_calendar_enable']) && $settings['google_calendar_enable'] == 'on')
            <div class="form-group col-md-12">
                {{Collective\Html\FormFacade::label('synchronize_type',__('Synchronize in Google Calendar ?'),array('class'=>'form-label')) }}
                <div class=" form-switch">
                    <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow" value="google_calendar">
                    <label class="form-check-label" for="switch-shadow"></label>
                </div>
            </div>
        @endif

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
    {{Collective\Html\FormFacade::close()}}
@if($candidate!=0)
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            ar:  { candidate_unavailable: 'المرشح غير متوفر' },
            da:  { candidate_unavailable: 'Kandidaten ikke tilgængelig' },
            de:  { candidate_unavailable: 'Kandidat nicht verfügbar' },
            en:  { candidate_unavailable: 'Candidate unavailable' },
            es:  { candidate_unavailable: 'Candidato no disponible' },
            fr:  { candidate_unavailable: 'Candidat indisponible' },
            he:  { candidate_unavailable: 'המועמד אינו זמין' },
            it:  { candidate_unavailable: 'Candidato non disponibile' },
            ja:  { candidate_unavailable: '候補者は利用できません' },
            nl:  { candidate_unavailable: 'Kandidaat niet beschikbaar' },
            pl:  { candidate_unavailable: 'Kandydat niedostępny' },
            pt:  { candidate_unavailable: 'Candidato indisponível' },
            'pt-br': { candidate_unavailable: 'Candidato indisponível' },
            ru:  { candidate_unavailable: 'Кандидат недоступен' },
            tr:  { candidate_unavailable: 'Aday mevcut değil' },
            zh:  { candidate_unavailable: '候选人不可用' }
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
        const dataListenerAdded = 'data-listener-added';
        const errFb = '# ERROR';
        const dataClientLocalized = 'data-client-localized';
        const dataGuardMsg = 'data-guard-msg';
        
        const getLocalizedMessage = (el, msgKey) => {
            let msg = errFb;
            if (el.getAttribute('data-sv-localized') === 'true' ||
                el.getAttribute(dataClientLocalized) === 'true') {
            msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
            let lang = (window.sessionStorage.getItem('erp-np-lang') ||
                        document.documentElement.lang ||
                        'en')
                        .toLowerCase()
                        .replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg = window.translations?.[lang]?.[msgKey] ||
                    el.getAttribute(dataGuardMsg) ||
                    window.translations?.['en']?.[msgKey] ||
                    errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, 'true');
            }
            }
            return msg;
        };
        
        try {
            if (!window.jQuery) {
            console.error('jQuery is not available');
            return;
            }
            const $candidate = jQuery('select#candidate');
            const el = $candidate.get(0);
            if (!el) {
            console.error('Select#candidate element not found');
            return;
            }
            const url = el.getAttribute('data-url');
            const href = el.href;
            if ((!url || url === '#') && (!href || href === '#')) {
            return;
            }
            const candidateVal = {{$candidate}};
            if (candidateVal == null) {
            return;
            }
            $candidate.val(candidateVal).trigger('change');
        } catch {
            const handleErrorDisplay = () => {
            const el = document.querySelector('select#candidate');
            const msgKey = 'candidate_unavailable';
            const message = el ? getLocalizedMessage(el, msgKey) : errFb;
            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') &&
                                window.bootstrap?.Toast;
            if (hasBootstrap) {
                if (!document.querySelector('#error-toast')) {
                const toast = document.createElement('div');
                toast.id = 'error-toast';
                toast.className = 'toast align-items-center text-bg-danger border-0';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                toast.innerHTML = `
                    <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button"
                            class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>`;
                document.body.appendChild(toast);
                }
                new bootstrap.Toast(document.querySelector('#error-toast')).show();
            } else {
                alert(message);
            }
            };
            const el = document.querySelector('select#candidate');
            if (el && el.getAttribute(dataListenerAdded) !== 'true') {
            const observer = new MutationObserver((_, obs) => {
                if (!document.body.contains(el)) {
                el.removeEventListener('click', handleErrorDisplay);
                obs.disconnect();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
            el.addEventListener('click', handleErrorDisplay);
            el.setAttribute(dataListenerAdded, 'true');
            }
        }
        })();
    </script>
    
@endif
