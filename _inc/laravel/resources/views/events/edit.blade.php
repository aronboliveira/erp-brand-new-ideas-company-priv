    {{Collective\Html\FormFacade::model($event,array('route' => array('event.update', $event->id), 'method' => 'PUT')) }}
    <div class="modal-body">
        {{-- start for ai module--}}
        @php
            $plan= \App\Models\Utility::getChatGPTSettings();
        @endphp
        @if($plan->chatgpt == 1)
        <div class="text-end">
            <a href="#" data-size="md" class="btn  btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['event']) }}"
               data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
                <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
            </a>
        </div>
        @endif
        {{-- end for ai module--}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('title',__('Event Title'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::text('title',null,array('class'=>'form-control','placeholder'=>__('Enter Event Title')))}}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('start_date',__('Event start Date'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::date('start_date',null,array('class'=>'form-control'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('end_date',__('Event End Date'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::date('end_date',null,array('class'=>'form-control'))}}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                {{ Collective\Html\FormFacade::label('color', __('Event Select Color'), ['class' => 'col-form-label d-block mb-3']) }}
                <div class=" btn-group-toggle btn-group-colors event-tag" data-toggle="buttons">
                    <label
                        class="btn bg-info p-3 {{ $event->color == 'event-info'
                            ? 'custom_color_radio_button
                                                                                                                        '
                            : '' }} "><input
                            type="radio" name="color" class="d-none" value="event-info"
                            {{ $event->color == 'event-info' ? 'checked' : '' }}></label>

                    <label
                        class="btn bg-warning p-3 {{ $event->color == 'event-warning' ? 'custom_color_radio_button' : '' }}"><input
                            type="radio" class="d-none" name="color" value="event-warning"
                            {{ $event->color == 'event-warning' ? 'checked' : '' }}></label>

                    <label
                        class="btn bg-danger p-3 {{ $event->color == 'event-danger' ? 'custom_color_radio_button' : '' }}"><input
                            type="radio" name="color" class="d-none" value="event-danger"
                            {{ $event->color == 'event-danger' ? 'checked' : '' }}></label>


                    <label
                        class="btn bg-primary p-3 {{ $event->color == 'event-success' ? 'custom_color_radio_button' : '' }}"><input
                            type="radio" class="d-none" name="color" value="event-success"
                            {{ $event->color == 'event-success' ? 'checked' : '' }}></label>

                    <label class="btn p-3 {{ $event->color == 'event-primary' ? 'custom_color_radio_button' : '' }}"
                           style="background-color: #51459d !important"><input type="radio" class="d-none"
                                                                               name="color" value="event-primary"
                            {{ $event->color == 'event-primary' ? 'checked' : '' }}></label>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('description',__('Event Description'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::textarea('description',null,array('class'=>'form-control','placeholder'=>__('Enter Event Description')))}}
            </div>
        </div>

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
    {{Collective\Html\FormFacade::close()}}

@push('script-page')
    <script>
        window.translations = {
        en: { date_picker_init_failed: 'Failed to initialize date picker.' },
        ar: { date_picker_init_failed: 'فشل تهيئة منتقي التاريخ.' },
        da: { date_picker_init_failed: 'Kunne ikke starte datovælgeren.' },
        de: { date_picker_init_failed: 'Initialisierung des Datumsauswahl fehlgeschlagen.' },
        es: { date_picker_init_failed: 'Error al inicializar el selector de fecha.' },
        fr: { date_picker_init_failed: 'Échec de l’initialisation du sélecteur de date.' },
        it: { date_picker_init_failed: 'Impossibile inizializzare il selettore data.' },
        ja: { date_picker_init_failed: '日付ピッカーの初期化に失敗しました。' },
        nl: { date_picker_init_failed: 'Initialisatie van de datakeuze mislukt.' },
        pl: { date_picker_init_failed: 'Nie udało się zainicjalizować selektora daty.' },
        pt: { date_picker_init_failed: 'Falha ao inicializar o seletor de data.' },
        'pt-br': { date_picker_init_failed: 'Falha ao iniciar o seletor de data.' },
        ru: { date_picker_init_failed: 'Не удалось инициализировать выбор даты.' },
        tr: { date_picker_init_failed: 'Tarih seçici başlatılamadı.' },
        zh: { date_picker_init_failed: '初始化日期选择器失败。' }
        };
    </script>
    <script defer>
        (() => {
        const errFb = '# ERROR';
        const dataClient = 'data-client-localized';
        const dataGuard  = 'data-guard-msg';
        const langKey    = 'erp-np-lang';
        const toastId    = 'toast-box';
        
        const getMsg = key => {
            let lang = (sessionStorage.getItem(langKey)
            || document.documentElement.lang
            || 'en')
            .toLowerCase()
            .replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            return window.translations?.[lang]?.[key]
            || window.translations.en[key]
            || errFb;
        };
        
        const showToast = msg => {
            const hasBs = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
            .some(l => /bootstrap/i.test(l.href)) && window.bootstrap?.Toast;
            if (hasBs) {
            let box = document.getElementById(toastId);
            if (!box) {
                box = document.createElement('div');
                box.id = toastId;
                box.setAttribute('aria-live', 'polite');
                box.setAttribute('aria-atomic', 'true');
                document.body.appendChild(box);
            }
            const t = document.createElement('div');
            t.className = 'toast';
            t.innerHTML = `<div class="toast-body">${msg}</div>`;
            box.appendChild(t);
            bootstrap.Toast.getOrCreateInstance(t).show();
            } else {
            alert(msg);
            }
        };
        
        let queued = '';
        const flush = () => {
            if (queued) {
            showToast(queued);
            queued = '';
            }
        };
        document.addEventListener('pointerup', flush);
        new MutationObserver((recs, obs) => {
            for (const r of recs) {
            for (const n of r.removedNodes) {
                if (n === document.documentElement) {
                document.removeEventListener('pointerup', flush);
                obs.disconnect();
                }
            }
            }
        }).observe(document.body, { childList: true, subtree: true });
        
        try {
            if (!window.$ || !$.fn.daterangepicker) throw 0;
            const els = document.querySelectorAll('.datepicker');
            if (!els.length) return;
            const opts = {
            singleDatePicker: true,
            locale: window.date_picker_locale ?? { format: 'YYYY-MM-DD' }
            };
            els.forEach(el => $(el).daterangepicker(opts));
        } catch {
            queued = getMsg('date_picker_init_failed');
        }
        })();
    </script>    
@endpush
