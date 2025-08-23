@php
    use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,
        PlansConstants,
        SettingsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Plan,User,Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Journal Entry Edit')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Double Entry')}}</li>
    <li class="breadcrumb-item">{{__('Journal Entry')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script defer src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script defer src="{{ asset('js/jquery-searchbox.js') }}"></script>
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            ar:  { repeater_show_unavailable: 'فشل عرض المكرر', repeater_hide_unavailable: 'فشل إخفاء المكرر', calc_unavailable: 'فشل الحساب', destroy_unavailable: 'فشل حذف الحساب' },
            da:  { repeater_show_unavailable: 'Visning af gentager mislykkedes', repeater_hide_unavailable: 'Skjul af gentager mislykkedes', calc_unavailable: 'Beregning mislykkedes', destroy_unavailable: 'Sletning af post mislykkedes' },
            de:  { repeater_show_unavailable: 'Wiederholer-Anzeige fehlgeschlagen', repeater_hide_unavailable: 'Wiederholer-Ausblenden fehlgeschlagen', calc_unavailable: 'Berechnung fehlgeschlagen', destroy_unavailable: 'Löschen des Kontos fehlgeschlagen' },
            en:  { repeater_show_unavailable: 'Cannot show repeater', repeater_hide_unavailable: 'Cannot hide repeater', calc_unavailable: 'Calculation failed', destroy_unavailable: 'Failed to delete account' },
            es:  { repeater_show_unavailable: 'Error al mostrar repetidor', repeater_hide_unavailable: 'Error al ocultar repetidor', calc_unavailable: 'Error en el cálculo', destroy_unavailable: 'Error al eliminar la cuenta' },
            fr:  { repeater_show_unavailable: 'Échec de l\'affichage du répéteur', repeater_hide_unavailable: 'Échec de la suppression du répéteur', calc_unavailable: 'Échec du calcul', destroy_unavailable: 'Échec de la suppression du compte' },
            he:  { repeater_show_unavailable: 'הצגת החוזר נכשלה', repeater_hide_unavailable: 'הסתרת החוזר נכשלה', calc_unavailable: 'החישוב נכשל', destroy_unavailable: 'המחיקה נכשלה' },
            it:  { repeater_show_unavailable: 'Impossibile mostrare il ripetitore', repeater_hide_unavailable: 'Impossibile nascondere il ripetitore', calc_unavailable: 'Errore nel calcolo', destroy_unavailable: 'Impossibile eliminare il conto' },
            ja:  { repeater_show_unavailable: 'リピーターの表示に失敗しました', repeater_hide_unavailable: 'リピーターの非表示に失敗しました', calc_unavailable: '計算に失敗しました', destroy_unavailable: 'アカウントの削除に失敗しました' },
            nl:  { repeater_show_unavailable: 'Herhaler weergeven mislukt', repeater_hide_unavailable: 'Herhaler verbergen mislukt', calc_unavailable: 'Berekening mislukt', destroy_unavailable: 'Verwijderen van account mislukt' },
            pl:  { repeater_show_unavailable: 'Nie można wyświetlić powtarzacza', repeater_hide_unavailable: 'Nie można ukryć powtarzacza', calc_unavailable: 'Błąd obliczeń', destroy_unavailable: 'Nie można usunąć konta' },
            pt:  { repeater_show_unavailable: 'Não foi possível exibir o repetidor', repeater_hide_unavailable: 'Não foi possível ocultar o repetidor', calc_unavailable: 'Falha no cálculo', destroy_unavailable: 'Falha ao excluir conta' },
            'pt-br': { repeater_show_unavailable: 'Não foi possível exibir o repetidor', repeater_hide_unavailable: 'Não foi possível ocultar o repetidor', calc_unavailable: 'Falha no cálculo', destroy_unavailable: 'Falha ao excluir conta' },
            ru:  { repeater_show_unavailable: 'Не удалось показать повторитель', repeater_hide_unavailable: 'Не удалось скрыть повторитель', calc_unavailable: 'Ошибка вычисления', destroy_unavailable: 'Не удалось удалить запись' },
            tr:  { repeater_show_unavailable: 'Tekrar gösterilemedi', repeater_hide_unavailable: 'Tekrar gizlenemedi', calc_unavailable: 'Hesaplama başarısız', destroy_unavailable: 'Hesap silme başarısız' },
            zh:  { repeater_show_unavailable: '无法显示重复项', repeater_hide_unavailable: '无法隐藏重复项', calc_unavailable: '计算失败', destroy_unavailable: '删除账户失败' }
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
            const DATA_LISTENER_ADDED   = 'data-listener-added';
            const ERR_FB                = '# ERROR';
            const DATA_CLIENT_LOCALIZED = 'data-client-localized';
            const DATA_GUARD_MSG        = 'data-guard-msg';

            const getLocalizedMessage = (el, key) => {
                let msg = ERR_FB;
                if (
                    el?.getAttribute('data-sv-localized') === 'true' ||
                    el?.getAttribute(DATA_CLIENT_LOCALIZED) === 'true'
                ) {
                    msg = el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
                } else {
                    let lang = (
                        sessionStorage.getItem('erp-np-lang') ||
                        document.documentElement.lang ||
                        'en'
                    )
                        .toLowerCase()
                        .replace(/_/g, '-');
                    lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                    msg =
                        window.translations?.[lang]?.[key] ||
                        window.translations?.['en']?.[key] ||
                        ERR_FB;
                    if (msg !== ERR_FB) {
                        el.setAttribute(DATA_GUARD_MSG, msg);
                        el.setAttribute(DATA_CLIENT_LOCALIZED, 'true');
                    }
                }
                return msg;
            };

            const handleErrorDisplay = (el, key) => {
                const message = el
                    ? getLocalizedMessage(el, key)
                    : ERR_FB;
                const hasBootstrap =
                    document.querySelector('link[href*="bootstrap"]') &&
                    window.bootstrap?.Toast;
                if (hasBootstrap) {
                    if (!document.querySelector('#error-toast')) {
                        const toast = document.createElement('div');
                        toast.id        = 'error-toast';
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

            try {
                if (typeof $ === 'undefined') {
                    console.error('jQuery is required');
                    return;
                }

                const selector = 'body';
                if ($(selector + ' .repeater').length) {
                    let $repeater;
                    try {
                        $repeater = $(selector + ' .repeater').repeater({
                            initEmpty: false,
                            defaultValues: { status: 1 },
                            show() {
                                try {
                                    $(this).slideDown();
                                    const $multi = $(this).find('input.multi');
                                    if ($multi.length) {
                                        $multi.MultiFile({
                                            max:       3,
                                            accept:    'png|jpg|jpeg',
                                            max_size:  {{ SettingsConstants::MAX_U_SIZE_DEF }}
                                        });
                                    }
                                    if (typeof JsSearchBox === 'function') {
                                        JsSearchBox();
                                    }
                                    if ($('.select2').length) {
                                        $('.select2').select2();
                                    }
                                } catch {
                                    const el = this;
                                    if (!el.hasAttribute(DATA_LISTENER_ADDED)) {
                                        el.addEventListener('click', () =>
                                            handleErrorDisplay(el, 'repeater_show_unavailable')
                                        );
                                        el.setAttribute(DATA_LISTENER_ADDED, 'true');
                                    }
                                }
                            },
                            hide(deleteElement) {
                                try {
                                    if (confirm('Are you sure you want to delete this element?')) {
                                        const $row = $(this);
                                        $row.slideUp(deleteElement);
                                        $row.remove();
                                        let totalD = 0, totalC = 0;
                                        $('.debit').each((_,i) => totalD += parseFloat($(i).val())||0);
                                        $('.credit').each((_,i) => totalC += parseFloat($(i).val())||0);
                                        $('.totalDebit').html(totalD.toFixed(2));
                                        $('.totalCredit').html(totalC.toFixed(2));
                                        const id = $row.find('.id').val();
                                        $.ajax({
                                            url: '{{ route("journal.account.destroy") }}',
                                            type: 'POST',
                                            headers: { 'X-CSRF-TOKEN': $('#token').val() },
                                            data: { id },
                                            cache: false,
                                            success: () => {},
                                            error: () => handleErrorDisplay($row[0], 'destroy_unavailable')
                                        });
                                    }
                                } catch {
                                    const el = this;
                                    if (!el.hasAttribute(DATA_LISTENER_ADDED)) {
                                        el.addEventListener('click', () =>
                                            handleErrorDisplay(el, 'repeater_hide_unavailable')
                                        );
                                        el.setAttribute(DATA_LISTENER_ADDED, 'true');
                                    }
                                }
                            },
                            ready: () => {},
                            isFirstItemUndeletable: true
                        });

                        const val = $(selector + ' .repeater').attr('data-value') ?? '';
                        if (val) {
                            try {
                                const list = JSON.parse(val);
                                $repeater.setList(list);
                                list.forEach((item, i) => {
                                    if (item.credit > 0) {
                                        $(`input[name="accounts[${i}][credit]"]`).trigger('keyup');
                                    }
                                    if (item.debit > 0) {
                                        $(`input[name="accounts[${i}][debit]"]`).trigger('keyup');
                                    }
                                });
                            } catch {
                                handleErrorDisplay(document.querySelector('.repeater'), 'repeater_show_unavailable');
                            }
                        }
                    } catch {
                        handleErrorDisplay(document.querySelector('.repeater'), 'repeater_show_unavailable');
                    }
                }

                const recalc = () => {
                    try {
                        let totalD = 0, totalC = 0;
                        $('.debit').each((_,i) => totalD += parseFloat($(i).val())||0);
                        $('.credit').each((_,i) => totalC += parseFloat($(i).val())||0);
                        $('.totalDebit').html(totalD.toFixed(2));
                        $('.totalCredit').html(totalC.toFixed(2));
                    } catch {
                        handleErrorDisplay(document.body, 'calc_unavailable');
                    }
                };

                $(document).on('keyup', '.debit', function() {
                    try {
                        const $row = $(this).closest('tr');
                        $row.find('.credit').val('').prop('disabled', true);
                        if (!$(this).val()) $row.find('.credit').prop('disabled', false);
                        $row.find('.amount').html($(this).val());
                        recalc();
                    } catch {
                        handleErrorDisplay(this, 'calc_unavailable');
                    }
                });

                $(document).on('keyup', '.credit', function() {
                    try {
                        const $row = $(this).closest('tr');
                        $row.find('.debit').val('').prop('disabled', true);
                        if (!$(this).val()) $row.find('.debit').prop('disabled', false);
                        $row.find('.amount').html($(this).val());
                        recalc();
                    } catch {
                        handleErrorDisplay(this, 'calc_unavailable');
                    }
                });

            } catch (e) {
                console.error('Initialization failed', e);
            }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    @if($user && method_exists($user, 'creatorId'))
        @php
            $planUser = User::find($user->creatorId());
            $plan = Plan::getPlan($planUser?->plan ?? DatabaseConstants::DEFAULT_PLAN);
        @endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="float-end">
                <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['journal entry']) }}"
                data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{__('Generate with AI')}}</span>
                </a>
            </div>
        @endif
    @endif
@endsection
@section(YieldingConstants::ADM_CTT)
    @if($journalEntry)
        {{ Form::model($journalEntry, array('route' => array(ViewsConstants::JRN_ET.'.update', $journalEntry->id), 'method' => 'PUT','class'=>'w-100')) }}
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="row mt-4">
                <div class="col-xl-12">
                    <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="{{ VC::CLM4 }}">
                            <div class="form-group">
                                {{ Form::label('journal_number', __('Journal Number'),['class'=>'form-label']) }}
                                <div class="form-icon-user">
                                    <input type="text" class="form-control" value="{{ $user?->journalNumberFormat($journalEntry->journal_id) ?? __('No journal number found.')}}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::CLM4 }}">
                            <div class="form-group">
                                {{ Form::label('date', __('Transaction Date'),['class'=>'form-label']) }}
                                <div class="form-icon-user">
                                    {{Form::date('date',null,array('class'=>'form-control','required'=>'required'))}}
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::CLM4 }}">
                            <div class="form-group">
                                {{ Form::label('reference', __('Reference'),['class'=>'form-label']) }}
                                <div class="form-icon-user">
                                    {{ Form::text('reference', null, array('class' => 'form-control')) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 col-md-8">
                            <div class="form-group">
                                {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
                                {{ Form::textarea('description', null, array('class' => 'form-control','rows'=>'2')) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card repeater" data-value='{!! json_encode($journalEntry->accounts) !!}'>
                        <div class="item-section py-4">
                            <div class="row justify-content-between align-items-center">
                                <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                                    <div class="all-button-box">
                                        <a href="#" data-repeater-create="" class="btn btn-primary me-4" data-toggle="modal" data-target="#add-bank">
                                            <i class="ti ti-plus"></i> {{__('Add Account')}}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table mb-0" data-repeater-list="accounts" id="sortable-table">
                                    <thead>
                                    <tr>
                                        <th>{{__('Account')}}</th>
                                        <th>{{__('Debit')}}</th>
                                        <th>{{__('Credit')}} </th>
                                        <th>{{__('Description')}}</th>
                                        <th class="text-end">{{__('Amount')}} </th>
                                        <th width="2%"></th>
                                    </tr>
                                    </thead>

                                    <tbody class="ui-sortable" data-repeater-item>

                                    <tr>
                                        {{ Form::hidden('id',null, array('class' => 'form-control id')) }}
                                            {{--                                <td width="25%">--}}
                                            {{--                                    <div class="form-group">--}}
                                            {{--                                        {{ Form::select('account', $accounts,'', array('class' => 'form-control js-searchBox','required'=>'required')) }}--}}

                                            {{--                                    </div>--}}
                                            {{--                                </td>--}}

                                        <td width="25%" class="form-group pt-0">
                                            {{ Form::select('account', $accounts,'', array('class' => 'form-control js-searchBox','required'=>'required')) }}
                                        </td>

                                        <td>
                                            <div class="form-group price-input">
                                                {{ Form::text('debit','', array('class' => 'form-control debit','required'=>'required','placeholder'=>__('Debit'),'required'=>'required')) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group price-input">
                                                {{ Form::text('credit','', array('class' => 'form-control credit','required'=>'required','placeholder'=>__('Credit'),'required'=>'required')) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                {{ Form::text('description',null, array('class' => 'form-control','placeholder'=>__('Description'))) }}
                                            </div>
                                        </td>
                                        <td class="text-end amount">0.00</td>
                                        <td>
                                            <a href="#" class="ti ti-trash text-white text-danger" data-repeater-delete></a>
                                        </td>
                                    </tr>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td></td>
                                        <td class="text-end"><strong>{{__('Total Credit')}} ({{ $user?->currencySymbol() ?? __(' No currency could be found.')}})</strong></td>
                                        <td class="text-end totalCredit">0.00</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td class="text-end"><strong>{{__('Total Debit')}} ({{ $user?->currencySymbol() ?? __(' No currency could be found.')}})</strong></td>
                                        <td class="text-end totalDebit">0.00</td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="button" value="{{__('Cancel')}}" onclick="location.href = ' {{ route(ViewsConstants::JRN_ET.'.index') }}';" class="btn btn-light">
                <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
            </div>
        {{ Form::close() }}
    @else
        <p>{{ __('No journal entry found.') }}</p>
    @endif
@endsection

