@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants, ViewClassNamesConstants,
        YieldingConstants
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Create Budget Planner')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
        $budgetIndexRoute       = Route::has(ViewsConstants::BDG . '.index')
            ? route(ViewsConstants::BDG . '.index')
            : '#';
        $budgetIndexLinkId      = 'budget-planner-index-link';
        $budgetIndexGuardMsg    = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::BDG,
            'budget_planner_index_route_unavailable'
        ) ?? 'Budget Planner index route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <li class="breadcrumb-item">
        <a
            id="{{ $budgetIndexLinkId }}"
            href="{{ $budgetIndexRoute }}"
            data-url="{{ $budgetIndexRoute }}"
            data-guard-msg="{{ $budgetIndexGuardMsg }}"
        >
            {{ __('Budget Planner') }}
        </a>
    </li>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer>
            (() => {
                const link = document.getElementById('{{ $budgetIndexLinkId }}');
                if (!link || link.getAttribute('data-listener-active') === 'true') return;
                link.setAttribute('data-listener-active', 'true');
                link.addEventListener('click', event => {
                    try {
                        const href = link.getAttribute('href');
                        const url  = link.getAttribute('data-url');
                        if ((href && href !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
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
                        link.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            })();
        </script>
    @endpush
    <li class="breadcrumb-item">{{__('Budget Create')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
        <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
          ar: {
            income_calculation_failed: 'فشل حساب إجمالي الدخل.',
            expense_calculation_failed: 'فشل حساب إجمالي المصروفات.',
            period_toggle_failed: 'فشل تغيير الفترة.'
          },
          da: {
            income_calculation_failed: 'Kunne ikke beregne samlet indkomst.',
            expense_calculation_failed: 'Kunne ikke beregne samlet udgift.',
            period_toggle_failed: 'Kunne ikke skifte periode.'
          },
          de: {
            income_calculation_failed: 'Fehler bei der Berechnung des Gesamteinkommens.',
            expense_calculation_failed: 'Fehler bei der Berechnung der Gesamtaufwendungen.',
            period_toggle_failed: 'Fehler beim Wechseln des Zeitraums.'
          },
          en: {
            income_calculation_failed: 'Failed to calculate total income.',
            expense_calculation_failed: 'Failed to calculate total expense.',
            period_toggle_failed: 'Failed to switch period.'
          },
          es: {
            income_calculation_failed: 'Error al calcular el ingreso total.',
            expense_calculation_failed: 'Error al calcular el gasto total.',
            period_toggle_failed: 'Error al cambiar el período.'
          },
          fr: {
            income_calculation_failed: 'Échec du calcul du revenu total.',
            expense_calculation_failed: 'Échec du calcul des dépenses totales.',
            period_toggle_failed: 'Échec du changement de période.'
          },
          he: {
            income_calculation_failed: 'החישוב הכולל של ההכנסה נכשל.',
            expense_calculation_failed: 'החישוב הכולל של ההוצאות נכשל.',
            period_toggle_failed: 'החלפת התקופה נכשלה.'
          },
          it: {
            income_calculation_failed: 'Impossibile calcolare il reddito totale.',
            expense_calculation_failed: 'Impossibile calcolare la spesa totale.',
            period_toggle_failed: 'Impossibile cambiare il periodo.'
          },
          ja: {
            income_calculation_failed: '総収入の計算に失敗しました。',
            expense_calculation_failed: '総支出の計算に失敗しました。',
            period_toggle_failed: '期間の切り替えに失敗しました。'
          },
          nl: {
            income_calculation_failed: 'Kon totale inkomsten niet berekenen.',
            expense_calculation_failed: 'Kon totale uitgaven niet berekenen.',
            period_toggle_failed: 'Kon periode niet wijzigen.'
          },
          pl: {
            income_calculation_failed: 'Nie udało się obliczyć całkowitego przychodu.',
            expense_calculation_failed: 'Nie udało się obliczyć całkowitego wydatku.',
            period_toggle_failed: 'Nie udało się zmienić okresu.'
          },
          pt: {
            income_calculation_failed: 'Falha ao calcular receita total.',
            expense_calculation_failed: 'Falha ao calcular despesa total.',
            period_toggle_failed: 'Falha ao trocar período.'
          },
          'pt-br': {
            income_calculation_failed: 'Falha ao calcular receita total.',
            expense_calculation_failed: 'Falha ao calcular despesa total.',
            period_toggle_failed: 'Falha ao trocar período.'
          },
          ru: {
            income_calculation_failed: 'Не удалось вычислить общий доход.',
            expense_calculation_failed: 'Не удалось вычислить общий расход.',
            period_toggle_failed: 'Не удалось изменить период.'
          },
          tr: {
            income_calculation_failed: 'Toplam geliri hesaplama başarısız.',
            expense_calculation_failed: 'Toplam gideri hesaplama başarısız.',
            period_toggle_failed: 'Dönem değiştirilemedi.'
          },
          zh: {
            income_calculation_failed: '计算总收入失败。',
            expense_calculation_failed: '计算总支出失败。',
            period_toggle_failed: '切换期间失败。'
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
        const guardMsg = 'data-guard-msg';
        const clientFlag = 'data-client-localized';
        const langKey = 'erp-np-lang';
        let errorMessage = '';
        
        function getLocalizedMessage(key, el) {
            let msg = errFb;
            if (el.getAttribute(clientFlag) === 'true') {
            msg = el.getAttribute(guardMsg) || msg;
            } else {
            let lang = (sessionStorage.getItem(langKey) || document.documentElement.lang || 'en')
                .toLowerCase().replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg = translations?.[lang]?.[key] ||
                    el.getAttribute(guardMsg) ||
                    translations?.['en']?.[key] ||
                    msg;
            if (msg !== errFb) {
                el.setAttribute(guardMsg, msg);
                el.setAttribute(clientFlag, 'true');
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
            const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
            if (bs) {
                const toast = document.createElement('div');
                toast.className = 'toast';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
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
        
        const onErrorPointerUp = () => {
            if (errorMessage) {
            showError(errorMessage);
            errorMessage = '';
            }
        };
        document.addEventListener('pointerup', onErrorPointerUp);
        new MutationObserver((ms, obs) => {
            ms.forEach(m => Array.from(m.removedNodes).forEach(n => {
            if (n === document.documentElement) {
                document.removeEventListener('pointerup', onErrorPointerUp);
                obs.disconnect();
            }
            }));
        }).observe(document.body, { childList: true, subtree: true });
        
        $(() => {
            const bindHandler = (selector, event, handler, errorKey) => {
            $(document).on(event, selector, function() {
                try {
                handler.call(this);
                } catch {
                errorMessage = getLocalizedMessage(errorKey, this);
                }
            });
            };
        
            bindHandler('.income_data', 'keyup', function() {
            const $row = $(this).closest('tr');
            let cat = 0;
            $row.find('.income_data').each((_, i) => cat += parseFloat($(i).val()) || 0);
            $row.find('.totalIncome').text(cat);
            const m = $(this).data('month') || '';
            let mt = 0;
            $row.parent().find(`.${m}_income`).each((_, i) => mt += parseFloat($(i).val()) || 0);
            $row.parent().find(`.${m}_total_income`).text(mt);
            let grand = 0;
            $row.parent().find('.totalIncome').each((_, i) => grand += parseFloat($(i).text()) || 0);
            $row.parent().find('.income').text(grand);
            }, 'income_calculation_failed');
        
            bindHandler('.expense_data', 'keyup', function() {
            const $row = $(this).closest('tr');
            let cat = 0;
            $row.find('.expense_data').each((_, i) => cat += parseFloat($(i).val()) || 0);
            $row.find('.totalExpense').text(cat);
            const m = $(this).data('month') || '';
            let mt = 0;
            $row.parent().find(`.${m}_expense`).each((_, i) => mt += parseFloat($(i).val()) || 0);
            $row.parent().find(`.${m}_total_expense`).text(mt);
            let grand = 0;
            $row.parent().find('.totalExpense').each((_, i) => grand += parseFloat($(i).text()) || 0);
            $row.parent().find('.expense').text(grand);
            }, 'expense_calculation_failed');
        
            bindHandler('.period', 'change', function() {
            const v = $(this).val() || '';
            $('.budget_plan').addClass('d-none');
            $(`#${v}`).removeClass('d-none').addClass('d-block');
            }, 'period_toggle_failed');
        
            $('.period').trigger('change');
        });
        })();
    </script>
@endpush
@section('content')
    <div class="card bg-none card-box mt-3">
        <div class="card-body">
            @php
                $bdgRoute   = Route::has(ViewsConstants::BDG)
                    ? route(ViewsConstants::BDG)
                    : '#';
                $formId     = 'budget-planner-form';
                $guardMsg   = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::BDG,
                    'budget_planner_store_route_unavailable'
                ) ?? 'Budget Planner route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            {!! Form::open([
                'url'            => $bdgRoute,
                'id'             => $formId,
                'data-url'       => $bdgRoute,
                'data-guard-msg' => $guardMsg,
                'class'          => 'w-100',
            ]) !!}
            <div class="row">
                <div class="form-group col-md-4">
                    {{ Collective\Html\FormFacade::label('name', __('Name'),['class'=>'form-label']) }}
                    {{ Collective\Html\FormFacade::text('name',null, array('class' => 'form-control','required'=>'required')) }}
                </div>
                <div class="form-group col-md-4">
                    {{ Collective\Html\FormFacade::label('period', __('Budget Period'),['class'=>'form-label']) }}
                    {{ Collective\Html\FormFacade::select('period', $periods,null, array('class' => 'form-control select period','required'=>'required')) }}
                </div>
                <div class="form-group col-md-4">
                    <div class="btn-box">
                        {{ Collective\Html\FormFacade::label('year', __('Year'),['class'=>'form-label']) }}
                        {{ Collective\Html\FormFacade::select('year',$yearList,isset($_GET['year'])?$_GET['year']:'', array('class' => 'form-control select')) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body table-border-style">
                <!---- Start Monthly Budget ------------------------------------------------------------------------>
                <div class="table-responsive budget_plan d-block"  id="monthly">
                    <table class="table mb-0" id="dataTable-manual">
                        <thead>
                        <tr>
                            <th>{{__('Category')}}</th>
                            @foreach($monthList as $month)
                                <td class="total text-dark">{{$month}}</td>
                            @endforeach
                            <th>{{__('Total :')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!------------------   Income Category ----------------------------------->
                        <tr>
                            <th colspan="14" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
                        </tr>
                        @foreach ($incomeproduct as $productService)
                            <tr>
                                <td>{{$productService->name}}</td>
                                @foreach($monthList as $month)
                                    <td>
                                        <input type="number" class="form-control pl-1 pr-1 income_data {{$month}}_income" data-month="{{$month}}" name="income[{{$productService->id}}][{{$month}}]" value="0" id="income_data_{{$month}}">
                                    </td>
                                @endforeach
                                <td class="totalIncome text-dark">
                                    0.00
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="text-dark">{{__('Total :')}}</td>
                            @foreach($monthList as $month)
                                <td>
                                    <span class="{{$month}}_total_income text-dark">0.00</span>
                                </td>
                            @endforeach
                            <td>
                                <span class="income text-dark">0.00</span>
                            </td>
                        </tr>
                        <!------------------   Expense Category ----------------------------------->
                        <tr>
                            <th colspan="14" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
                        </tr>
                        @foreach ($expenseproduct as $productService)
                            <tr>
                                <td>{{$productService->name}}</td>
                                @foreach($monthList as $month)
                                    <td>
                                        <input type="number" class="form-control pl-1 pr-1 expense_data {{$month}}_expense" data-month="{{$month}}" name="expense[{{$productService->id}}][{{$month}}]" value="0" id="expense_data_{{$month}}">
                                    </td>
                                @endforeach
                                <td class="totalExpense text-dark">
                                    0.00
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td  class="text-dark">{{__('Total :')}}</span></td>
                            @foreach($monthList as $month)
                                <td>
                                    <span class="{{$month}}_total_expense text-dark">0.00</span>
                                </td>
                            @endforeach
                            <td>
                                <span class="expense text-dark">0.00</span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        @php
                            $cancelRoute        = Route::has(ViewsConstants::BDG.'.index')
                                ? route(ViewsConstants::BDG.'.index')
                                : '#';
                            $cancelBtnId        = 'expense-cancel-btn';
                            $cancelGuardMsg     = Utility::fetchLinkMessage(
                                $lang,
                                'expenses',
                                'budget_planner_index_route_unavailable'
                            ) ?? 'Expense cancel route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <input
                            type="button"
                            id="{{ $cancelBtnId }}"
                            value="{{ __('Cancel') }}"
                            class="btn btn-light"
                            data-url="{{ $cancelRoute }}"
                            data-guard-msg="{{ $cancelGuardMsg }}"
                        >
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    const btn = document.getElementById('{{ $cancelBtnId }}');
                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                    btn.setAttribute('data-listener-active', 'true');
                                    btn.addEventListener('click', event => {
                                        try {
                                            const url = btn.getAttribute('data-url');
                                            if (!url || url === '#') {
                                                event.preventDefault();
                                                const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
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
                                                btn.setAttribute('data-failed-route', 'true');
                                                return;
                                            }
                                            window.location.href = url;
                                        } catch (e) {}
                                    });
                                })();
                            </script>
                        @endpush
                        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
                    </div>
                </div>

                <!---- End Monthly Budget ----->
                <!---- Start Quarterly Budget ----------------------------------------------------------------------->
                <div class="table-responsive budget_plan d-none" id="quarterly">
                    <table class="table mb-0" id="dataTable-manual">
                        <thead>
                        <tr>
                            <th>{{__('Category')}}</th>
                            @foreach($quarterly_monthlist as $month)
                                <td class="total text-dark">{{$month}}</td>
                            @endforeach
                            <th>{{__('Total :')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!------------------   Income Category ----------------------------------->
                        <tr>
                            <th colspan="37" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
                        </tr>

                        @foreach ($incomeproduct as $productService)
                            <tr>
                                <td>{{$productService->name}}</td>
                                @foreach($quarterly_monthlist as $month)
                                    <td>
                                        <input type="number" class="form-control income_data {{$month}}_income" data-month="{{$month}}"
                                               name="income[{{$productService->id}}][{{$month}}]" value="0" id="income_data_{{$month}}">
                                    </td>
                                @endforeach
                                <td class="text-end totalIncome  text-dark">
                                    0.00
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <td class="text-dark">{{__('Total :')}}</td>
                            @foreach($quarterly_monthlist as $month)
                                <td>
                                    <span class="{{$month}}_total_income  text-dark">0.00</span>
                                </td>
                            @endforeach
                            <td class="text-end">
                                <span class="income  text-dark">0.00</span>
                            </td>
                        </tr>

                        <!------------------   Expense Category ----------------------------------->

                        <tr>
                            <th colspan="14" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
                        </tr>

                        @foreach ($expenseproduct as $productService)
                            <tr>
                                <td>{{$productService->name}}</td>
                                @foreach($quarterly_monthlist as $month)
                                    <td>
                                        <input type="number" class="form-control expense_data {{$month}}_expense" data-month="{{$month}}" name="expense[{{$productService->id}}][{{$month}}]" value="0" id="expense_data_{{$month}}">
                                    </td>
                                @endforeach
                                <td class="text-end totalExpense  text-dark">
                                    0.00
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <td  class="text-dark">{{__('Total :')}}</span></td>
                            @foreach($quarterly_monthlist as $month)
                                <td>
                                    <span class="{{$month}}_total_expense  text-dark">0.00</span>
                                </td>
                            @endforeach
                            <td class="text-end">
                                <span class="expense  text-dark">0.00</span>
                            </td>

                        </tr>

                        </tbody>

                    </table>
                    <div class="modal-footer">
                        @php
                            $cancelRoute        = Route::has(ViewsConstants::BDG . '.index')
                                ? route(ViewsConstants::BDG . '.index')
                                : '#';
                            $cancelBtnId        = 'budget-cancel-btn';
                            $cancelGuardMsg     = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::BDG,
                                'budget_planner_index_route_unavailable'
                            ) ?? 'Budget Planner index route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <input
                            type="button"
                            id="{{ $cancelBtnId }}"
                            value="{{ __('Cancel') }}"
                            class="btn btn-light"
                            data-url="{{ $cancelRoute }}"
                            data-guard-msg="{{ $cancelGuardMsg }}"
                        >
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    const btn = document.getElementById('{{ $cancelBtnId }}');
                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                    btn.setAttribute('data-listener-active', 'true');
                                    btn.addEventListener('click', event => {
                                        try {
                                            const url = btn.getAttribute('data-url');
                                            if (!url || url === '#') {
                                                event.preventDefault();
                                                const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
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
                                                btn.setAttribute('data-failed-route', 'true');
                                                return;
                                            }
                                            window.location.href = url;
                                        } catch (e) {}
                                    });
                                })();
                            </script>
                        @endpush
                        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
                    </div>
                </div>

                <!---- End Quarterly Budget ----->
                <!---Start Half-Yearly Budget --------------------------------------------------------------------->
                <div class="table-responsive budget_plan d-none" id="half-yearly">
                    <table class="table  mb-0" id="dataTable-manual">
                        <thead>
                        <tr>
                            <th>{{__('Category')}}</th>
                            @foreach($half_yearly_monthlist as $month)
                                <td class="total text-dark">{{$month}}</td>
                            @endforeach
                            <th>{{__('Total :')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!------------------   Income Category ----------------------------------->
                        <tr>
                            <th colspan="14" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
                        </tr>
                        @foreach ($incomeproduct as $productService)
                            <tr>
                                <td>{{$productService->name}}</td>
                                @foreach($half_yearly_monthlist as $month)
                                    <td>
                                        <input type="number" class="form-control income_data {{$month}}_income" data-month="{{$month}}" name="income[{{$productService->id}}][{{$month}}]" value="0" id="income_data_{{$month}}">
                                    </td>
                                @endforeach
                                <td class="text-end totalIncome  text-dark">
                                    0.00
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <td class="text-dark">{{__('Total :')}}</td>
                            @foreach($half_yearly_monthlist as $month)
                                <td>
                                    <span class="{{$month}}_total_income  text-dark">0.00</span>
                                </td>
                            @endforeach
                            <td class="text-end">
                                <span class="income text-dark">0.00</span>
                            </td>
                        </tr>

                        <!------------------   Expense Category ----------------------------------->

                        <tr>
                            <th colspan="14" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
                        </tr>

                        @foreach ($expenseproduct as $productService)
                            <tr>
                                <td>{{$productService->name}}</td>
                                @foreach($half_yearly_monthlist as $month)
                                    <td>
                                        <input type="number" class="form-control expense_data {{$month}}_expense" data-month="{{$month}}" name="expense[{{$productService->id}}][{{$month}}]" value="0" id="expense_data_{{$month}}">
                                    </td>
                                @endforeach
                                <td class="text-end totalExpense text-dark">
                                    0.00
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <td  class="text-dark">{{__('Total :')}}</span></td>
                            @foreach($half_yearly_monthlist as $month)
                                <td>
                                    <span class="{{$month}}_total_expense text-dark">0.00</span>
                                </td>
                            @endforeach
                            <td class="text-end">
                                <span class="expense text-dark">0.00</span>
                            </td>

                        </tr>

                        </tbody>

                    </table>
                    <div class="modal-footer">
                        @php
                            $expenseCancelRoute   = Route::has(ViewsConstants::EXP . '.index')
                                ? route(ViewsConstants::EXP . '.index')
                                : '#';
                            $expenseCancelBtnId   = 'expense-cancel-btn';
                            $expenseCancelGuardMsg = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::BDG,
                                'expense_cancel_route_unavailable'
                            ) ?? 'Expense section route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <input
                            type="button"
                            id="{{ $expenseCancelBtnId }}"
                            value="{{ __('Cancel') }}"
                            class="btn btn-light"
                            data-url="{{ $expenseCancelRoute }}"
                            data-guard-msg="{{ $expenseCancelGuardMsg }}"
                        >
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    const btn = document.getElementById('{{ $expenseCancelBtnId }}');
                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                    btn.setAttribute('data-listener-active', 'true');
                                    btn.addEventListener('click', event => {
                                        try {
                                            const url = btn.getAttribute('data-url');
                                            if (!url || url === '#') {
                                                event.preventDefault();
                                                const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
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
                                                btn.setAttribute('data-failed-route', 'true');
                                                return;
                                            }
                                            window.location.href = url;
                                        } catch (e) {}
                                    });
                                })();
                            </script>
                        @endpush
                        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
                    </div>
                </div>

                <!---End Half-Yearly Budget ----->

                <!---Start Yearly Budget --------------------------------------------------------------------------------->
                <div class="table-responsive budget_plan d-none" id="yearly">
                    <table class="table  mb-0" id="dataTable-manual">
                        <thead>
                        <tr>
                            <th>{{__('Category')}}</th>
                            @foreach($yearly_monthlist as $month)
                                <td class="total text-dark">{{$month}}</td>
                            @endforeach
                            <th>{{__('Total :')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!------------------   Income Category ----------------------------------->
                        <tr>
                            <th colspan="14" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
                        </tr>

                        @foreach ($incomeproduct as $productService)
                            <tr>
                                <td>{{$productService->name}}</td>

                                @foreach($yearly_monthlist as $month)

                                    <td>
                                        <input type="number" class="form-control income_data {{$month}}_income" data-month="{{$month}}" name="income[{{$productService->id}}][{{$month}}]" value="{{!empty($budget['income_data'][$productService->id][$month])?$budget['income_data'][$productService->id][$month]:0}}" id="income_data_{{$month}}">
                                    </td>
                                @endforeach
                                <td class="text-end totalIncome text-dark">
                                    0.00
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <td class="text-dark">{{__('Total :')}}</td>
                            @foreach($yearly_monthlist as $month)
                                <td>
                                    <span class="{{$month}}_total_income text-dark">0.00</span>
                                </td>
                            @endforeach
                            <td class="text-end">
                                <span class="income text-dark">0.00</span>
                            </td>
                        </tr>

                        <!------------------   Expense Category ----------------------------------->

                        <tr>
                            <th colspan="14" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
                        </tr>

                        @foreach ($expenseproduct as $productService)
                            <tr>
                                <td>{{$productService->name}}</td>
                                @foreach($yearly_monthlist as $month)
                                    <td>
                                        <input type="number" class="form-control expense_data {{$month}}_expense" data-month="{{$month}}" name="expense[{{$productService->id}}][{{$month}}]" value="{{!empty($budget['expense_data'][$productService->id][$month])?$budget['expense_data'][$productService->id][$month]:0}}" id="expense_data_{{$month}}">
                                    </td>
                                @endforeach
                                <td class="text-end totalExpense text-dark">
                                    0.00
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <td  class="text-dark">{{__('Total :')}}</span></td>
                            @foreach($yearly_monthlist as $month)
                                <td>
                                    <span class="{{$month}}_total_expense text-dark">0.00</span>
                                </td>
                            @endforeach
                            <td class="text-end">
                                <span class="expense text-dark">0.00</span>
                            </td>

                        </tr>

                        </tbody>

                    </table>
                    <div class="modal-footer">
                        <input
                            type="button"
                            id="{{ $expenseCancelBtnId }}"
                            value="{{ __('Cancel') }}"
                            class="btn btn-light"
                            data-url="{{ $expenseCancelRoute }}"
                            data-guard-msg="{{ $expenseCancelGuardMsg }}"
                        >
                        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
                    </div>
                </div>

                <!---End Yearly Budget ----->

            </div>
            {{ Collective\Html\FormFacade::close() }}
            @push(StacksConstants::ADM_SCR_PG)
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
            @endpush
        </div>
    </div>

@endsection
