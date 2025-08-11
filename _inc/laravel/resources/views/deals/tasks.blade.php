@php
    use App\Config\Constants\{
        ActivitiesConstants, 
        ProjectsConstants, 
        ViewsConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $isUpdate      = isset($task);
    $routeKey      = ViewsConstants::DL.'.tasks.' . ($isUpdate ? 'update' : 'store');
    $kebabKey      = Str::kebab($routeKey);
    $hasRoute      = Route::has($routeKey);
    $hasKebab      = Route::has($kebabKey);
    $routeName     = $hasRoute
        ? $routeKey
        : ($hasKebab ? $kebabKey : null);
    $routeParams   = $routeName
        ? ($isUpdate
            ? [$routeName, $deal->id, $task->id]
            : [$routeName, $deal->id])
        : ['#'];
    $routeUrl      = $routeName
        ? ($isUpdate
            ? route($routeName, [$deal->id, $task->id])
            : route($routeName, $deal->id))
        : '#';
    $guardKey      = $isUpdate
        ? 'deal_tasks_update_route_unavailable'
        : 'deal_tasks_store_route_unavailable';
    $guardMsg      = Utility::fetchLinkMessage($lang, ViewsConstants::DL, $guardKey)
        ?? ($isUpdate
            ? 'Update deal task route is unavailable. Please contact technical support or your domain administrator.'
            : 'Create deal task route is unavailable. Please contact technical support or your domain administrator.'
        );
@endphp

@if($isUpdate)
    {!! Form::model(
        $task,
        [
            'route'          => $routeParams,
            'method'         => 'PUT',
            'id'             => 'form-tasks-'. $deal->id .'-'. $task->id,
            'data-url'       => $routeUrl,
            'data-guard-msg' => $guardMsg
        ]
    ) !!}
@else
    {!! Form::open([
        'route'          => $routeParams,
        'id'             => 'form-tasks-'. $deal->id,
        'data-url'       => $routeUrl,
        'data-guard-msg' => $guardMsg
    ]) !!}
@endif

<div class="modal-body">
    <div class="row">
        <div class="col-12 form-group">
            {{ Collective\Html\FormFacade::label(ProjectsConstants::COL_NM, __('Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text(ProjectsConstants::COL_NM, null, array('class' => 'form-control',
                'required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label(ActivitiesConstants::COL_TSK_DATE, __('Date'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::date(ActivitiesConstants::COL_TSK_DATE, null, array('class' => 'form-control',
                'required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label(ActivitiesConstants::COL_TSK_TIME, __('Time'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::time(ActivitiesConstants::COL_TSK_TIME, null, array('class' => 'form-control',
                'required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label(ProjectsConstants::COL_PRT, __('Priority'),['class'=>'form-label']) }}
            <select class="form-control select2" name="priority" required id="choices-multiple1">
                @foreach($priorities as $key => $priority)
                    <option value="{{$key}}" @if(isset($task) && $task->priority == $key) selected @endif>{{__($priority)}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label(ActivitiesConstants::COL_TSK_STT, __('Status'),['class'=>'form-label']) }}
            <select class="form-control select2" name="status" id="choices-multiple2" required>
                @foreach($status as $key => $st)
                    <option value="{{$key}}" @if(isset($task) && $task->status == $key) selected @endif>{{__($st)}}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
    @if(isset($task))
        <input type="submit" value="{{__('Update')}}" class="{{ VC::BT_PRM }}">
    @else
        <input type="submit" value="{{__('Create')}}" class="{{ VC::BT_PRM }}">
    @endif
</div>
{{Collective\Html\FormFacade::close()}}

<script>
    const langPatch = {
      ar: { datepicker_init_failed: 'فشل تحميل منتقي التاريخ.',
            timepicker_init_failed: 'فشل تحميل منتقي الوقت.' },
      da: { datepicker_init_failed: 'Kunne ikke indlæse datovælger.',
            timepicker_init_failed: 'Kunne ikke indlæse tidsvælger.' },
      de: { datepicker_init_failed: 'Datum‑Picker konnte nicht geladen werden.',
            timepicker_init_failed: 'Zeit‑Picker konnte nicht geladen werden.' },
      en: { datepicker_init_failed: 'Failed to initialise date picker.',
            timepicker_init_failed: 'Failed to initialise time picker.' },
      es: { datepicker_init_failed: 'Error al cargar el selector de fecha.',
            timepicker_init_failed: 'Error al cargar el selector de hora.' },
      fr: { datepicker_init_failed: 'Échec du chargement du sélecteur de date.',
            timepicker_init_failed: 'Échec du chargement du sélecteur d’heure.' },
      he: { datepicker_init_failed: 'טעינת בוחר התאריך נכשלה.',
            timepicker_init_failed: 'טעינת בוחר השעה נכשלה.' },
      it: { datepicker_init_failed: 'Impossibile caricare il Date‑Picker.',
            timepicker_init_failed: 'Impossibile caricare il Time‑Picker.' },
      ja: { datepicker_init_failed: '日付ピッカーの読み込みに失敗しました。',
            timepicker_init_failed: '時刻ピッカーの読み込みに失敗しました。' },
      nl: { datepicker_init_failed: 'Laden van datumpicker mislukt.',
            timepicker_init_failed: 'Laden van tijdpicker mislukt.' },
      pl: { datepicker_init_failed: 'Nie udało się załadować wyboru daty.',
            timepicker_init_failed: 'Nie udało się załadować wyboru czasu.' },
      pt: { datepicker_init_failed: 'Falha ao carregar o seletor de data.',
            timepicker_init_failed: 'Falha ao carregar o seletor de hora.' },
      'pt-br':{ datepicker_init_failed: 'Falha ao carregar o seletor de data.',
                timepicker_init_failed: 'Falha ao carregar o seletor de hora.' },
      ru: { datepicker_init_failed: 'Не удалось загрузить выбор даты.',
            timepicker_init_failed: 'Не удалось загрузить выбор времени.' },
      tr: { datepicker_init_failed: 'Tarih seçici yüklenemedi.',
            timepicker_init_failed: 'Saat seçici yüklenemedi.' },
      zh: { datepicker_init_failed: '日期选择器加载失败。',
            timepicker_init_failed: '时间选择器加载失败。' }
    };
    window.translations = Object.keys(window.translations||{}).length
      ? Object.keys(langPatch).reduce((acc,l)=>{acc[l]={...(acc[l]||{}),...langPatch[l]};return acc;},window.translations)
      : langPatch;
</script>
<script defer>
    (() => {
    const getMsg = (key) => {
        const lang = (sessionStorage.getItem('erp-np-lang') ||
                    document.documentElement.lang || 'en').toLowerCase().replace(/_/g,'-');
        const short = lang==='pt-br'?lang:lang.slice(0,2);
        return (window.translations?.[short]?.[key] ||
                window.translations?.['en']?.[key] || '# ERROR');
    };
    const toast = (msg) => window.show_toastr
        ? window.show_toastr('error', msg, 'error')
        : alert(msg);
    
    document.addEventListener('DOMContentLoaded', () => {
        try {
        const dateInput = document.getElementById('date');
        if (dateInput && $.fn.daterangepicker) {
            $('#date').daterangepicker({
            locale: { format: 'YYYY-MM-DD' },
            singleDatePicker: true
            });
        } else throw 0;
        } catch {
        toast(getMsg('datepicker_init_failed'));
        }
    
        try {
        const timeInput = document.getElementById('time');
        if (timeInput && $.fn.timepicker) {
            $('#time').timepicker({
            icons: { up: 'ti ti-chevron-up', down: 'ti ti-chevron-down' }
            });
        } else throw 0;
        } catch {
        toast(getMsg('timepicker_init_failed'));
        }
    });
    })();
</script>
    
