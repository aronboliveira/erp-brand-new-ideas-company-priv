@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants,
    };
    use App\Models\{Budget,Utility};
    use Illuminate\Support\Facades\Route;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Budget Vs Actual')}}
@endsection
@section('breadcrumb')
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
    <li class="breadcrumb-item">{{ $budget->name }}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script>
        window.translations = {
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
    </script>
    <script defer>
        (() => {
        const errFb = '# ERROR';
        const clientFlag = 'data-client-localized';
        const guardMsgKey = 'data-guard-msg';
        const langKey = 'erp-np-lang';
        
        function getLocalizedMessage(key, el) {
            let msg = errFb;
            if (el.getAttribute(clientFlag) === 'true') {
            msg = el.getAttribute(guardMsgKey) ?? msg;
            } else {
            let lang = (sessionStorage.getItem(langKey) ?? document.documentElement.lang ?? 'en')
                .toLowerCase().replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg = translations?.[lang]?.[key]
                ?? el.getAttribute(guardMsgKey)
                ?? translations?.['en']?.[key]
                ?? msg;
            if (msg !== errFb) {
                el.setAttribute(guardMsgKey, msg);
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
            const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
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
        
        let errorMessage = '';
        const onErrorPointerUp = () => {
            if (errorMessage) {
            showError(errorMessage);
            errorMessage = '';
            }
        };
        document.addEventListener('pointerup', onErrorPointerUp);
        new MutationObserver((muts, obs) => {
            muts.forEach(m => Array.from(m.removedNodes).forEach(n => {
            if (n === document.documentElement) {
                document.removeEventListener('pointerup', onErrorPointerUp);
                obs.disconnect();
            }
            }));
        }).observe(document.body, { childList: true, subtree: true });
        
        document.addEventListener('DOMContentLoaded', () => {
            const bindField = (selector, handler, isThrottle) => {
            document.querySelectorAll(selector).forEach(el => {
                if (el.dataset.listenerAttached === 'true') return;
                el.dataset.listenerAttached = 'true';
                el.addEventListener(isThrottle ? 'keyup' : 'change', handler);
                new MutationObserver((ms, obs) => {
                ms.forEach(m => Array.from(m.removedNodes).forEach(n => {
                    if (n === el) {
                    el.removeEventListener(isThrottle ? 'keyup' : 'change', handler);
                    obs.disconnect();
                    }
                }));
                }).observe(document.body, { childList: true, subtree: true });
            });
            };
        
            const onIncomeKeyup = event => {
            try {
                const row = event.currentTarget.closest('tr');
                const inputs = row.querySelectorAll('.income_data');
                let total = 0;
                inputs.forEach(i => total += parseFloat(i.value) || 0);
                row.querySelector('.totalIncome').textContent = total;
                const month = event.currentTarget.dataset.month;
                const monthInputs = row.parentElement.querySelectorAll(`.${month}_income`);
                let mTotal = 0;
                monthInputs.forEach(i => mTotal += parseFloat(i.value) || 0);
                row.parentElement.querySelector(`.${month}_total_income`).textContent = mTotal;
                const allTotals = row.parentElement.querySelectorAll('.totalIncome');
                let grand = 0;
                allTotals.forEach(t => grand += parseFloat(t.textContent) || 0);
                row.parentElement.querySelector('.income').textContent = grand;
            } catch {
                errorMessage = getLocalizedMessage('income_calculation_failed', event.currentTarget);
            }
            };
        
            const onExpenseKeyup = event => {
            try {
                const row = event.currentTarget.closest('tr');
                const inputs = row.querySelectorAll('.expense_data');
                let total = 0;
                inputs.forEach(i => total += parseFloat(i.value) || 0);
                row.querySelector('.totalExpense').textContent = total;
                const month = event.currentTarget.dataset.month;
                const monthInputs = row.parentElement.querySelectorAll(`.${month}_expense`);
                let mTotal = 0;
                monthInputs.forEach(i => mTotal += parseFloat(i.value) || 0);
                row.parentElement.querySelector(`.${month}_total_expense`).textContent = mTotal;
                const allTotals = row.parentElement.querySelectorAll('.totalExpense');
                let grand = 0;
                allTotals.forEach(t => grand += parseFloat(t.textContent) || 0);
                row.parentElement.querySelector('.expense').textContent = grand;
            } catch {
                errorMessage = getLocalizedMessage('expense_calculation_failed', event.currentTarget);
            }
            };
        
            const onPeriodChange = event => {
            try {
                const val = event.currentTarget.value;
                document.querySelectorAll('.budget_plan').forEach(el => el.classList.add('d-none'));
                const target = document.getElementById(val);
                if (target) target.classList.replace('d-none', 'd-block');
            } catch {
                showError(getLocalizedMessage('period_toggle_failed', event.currentTarget));
            }
            };
        
            bindField('.income_data', onIncomeKeyup, true);
            bindField('.expense_data', onExpenseKeyup, true);
            bindField('.period', onPeriodChange, false);
            document.querySelectorAll('.period').forEach(el => el.dispatchEvent(new Event('change')));
        });
        })();
    </script>
@endpush

@section('content')
    <div class="col-12 mt-4">
        <div class="card p-4 mb-4">
            <h6 class="report-text mb-0 text-center">{{__('Year :')}} {{ $budget->from }}</h6>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body" style="overflow-x: auto">
                    {{--  Monthly Budget--}}
                    @if($budget->period == 'monthly')
                        <table class="table table-bordered table-item data">
                            <thead>
                            <tr>
                                <td rowspan="2"></td>
                                @foreach($monthList as $month)
                                    <th colspan="3" scope="colgroup" class="text-center br-1px">{{$month}}</th>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($monthList as $month)
                                    <th scope="col" class="br-1px">Budget</th>
                                    <th scope="col" class="br-1px">Actual</th>
                                    <th scope="col" class="br-1px">Over Budget</th>
                                @endforeach
                            </tr>
                            </thead>
                            <!----INCOME Category ---------------------->
                            <tr>
                                <th colspan="37" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
                            </tr>
                            @php
                                $overBudgetTotal=[];
                            @endphp
                            @foreach ($incomeproduct as $productService)
                                <tr>
                                    <td class="text-dark">{{$productService->name}}</td>
                                    @foreach($monthList as $month)
                                        @php
                                            $budgetAmount= ($budget['income_data'][$productService->id][$month])?$budget['income_data'][$productService->id][$month]:0;
                                            $actualAmount=$incomeArr[$productService->id][$month];
                                            $overBudgetAmount=$actualAmount-$budgetAmount;
                                            $overBudgetTotal[$productService->id][$month]=$overBudgetAmount;
                                        @endphp
                                        <td class="income_data {{$month}}_income">{{!empty ($user?->priceFormat($budget['income_data'][$productService->id][$month]))?$user?->priceFormat($budget['income_data'][$productService->id][$month]):0}}</td>
                                        <td>{{$user?->priceFormat($incomeArr[$productService->id][$month])}}
                                            <p>{{($budget['income_data'][$productService->id][$month] !=0)? (Budget::percentage($budget['income_data'][$productService->id][$month],$incomeArr[$productService->id][$month])!=0) ? '('.(Budget::percentage($budget['income_data'][$productService->id][$month],$incomeArr[$productService->id][$month]).'%)') :'':''}}</p>
                                        </td>
                                        <td>{{$user?->priceFormat($overBudgetAmount)}}
                                            <p class="{{($budget['income_data'][$productService->id][$month] < $overBudgetAmount)? 'green-text':''}} {{($budget['income_data'][$productService->id][$month] > $overBudgetAmount)? 'red-text':''}}" >{{($budget['income_data'][$productService->id][$month] !=0)? (Budget::percentage($budget['income_data'][$productService->id][$month],$overBudgetAmount) !=0) ?'('.(Budget::percentage($budget['income_data'][$productService->id][$month],$overBudgetAmount).'%)') :'':''}}</p>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            @php
                                $overBudgetTotalArr = array();
                                  foreach($overBudgetTotal as $overBudget)
                                  {
                                      foreach($overBudget as $k => $value)
                                      {
                                          $overBudgetTotalArr[$k] = (isset($overBudgetTotalArr[$k]) ? $overBudgetTotalArr[$k] + $value : $value);
                                      }
                                  }
                            @endphp
                            <tr class="total">
                                <td class="text-dark"><span></span><strong>{{__('Total :')}}</strong></td>
                                @if(!empty($budgetTotal) )
                                    @foreach($monthList as $month)
                                        <td class="text-dark {{$month}}_total_income"><strong>{{$user?->priceFormat($budgetTotal[$month])}}</strong></td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($incomeTotalArr[$month])}}</strong>
                                            <p>{{($budgetTotal[$month] !=0)? (Budget::percentage($budgetTotal[$month],$incomeTotalArr[$month])!=0) ? '('.(Budget::percentage($budgetTotal[$month],$incomeTotalArr[$month]).'%)') :'':''}}</p>
                                        </td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($overBudgetTotalArr[$month]) }}</strong>
                                            <p class="{{($budgetTotal[$month] < $overBudgetAmount)? 'green-text':''}} {{($budgetTotal[$month] > $overBudgetAmount)? 'red-text':''}}">{{($budgetTotal[$month] !=0)? (Budget::percentage($budgetTotal[$month],$overBudgetTotalArr[$month])!=0) ?'('.(Budget::percentage($budgetTotal[$month],$overBudgetTotalArr[$month]).'%)') :'':''}}</p>
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                            <tr>
                                <th colspan="37" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
                            </tr>
                            @php
                                $overExpenseBudgetTotal=[];
                            @endphp

                            @foreach ($expenseproduct as $productService)
                                <tr>
                                    <td class="text-dark">{{$productService->name}}</td>
                                    @foreach($monthList as $month)
                                        @php
                                            $budgetAmount= ($budget['expense_data'][$productService->id][$month])?$budget['expense_data'][$productService->id][$month]:0;
                                            $actualAmount=$expenseArr[$productService->id][$month];
                                            $overBudgetAmount=$actualAmount-$budgetAmount;
                                            $overExpenseBudgetTotal[$productService->id][$month]=$overBudgetAmount;
                                        @endphp
                                        <td class="expense_data {{$month}}_expense">{{$user?->priceFormat(!empty($budget['expense_data'][$productService->id][$month]))?$user?->priceFormat($budget['expense_data'][$productService->id][$month]):0}}</td>
                                        <td>{{$user?->priceFormat($expenseArr[$productService->id][$month])}}
                                            <p>{{($budget['expense_data'][$productService->id][$month] !=0)? (Budget::percentage($budget['expense_data'][$productService->id][$month],$expenseArr[$productService->id][$month])!=0) ? '('.(Budget::percentage($budget['expense_data'][$productService->id][$month],$expenseArr[$productService->id][$month]).'%)') :'':''}}</p>

                                        </td>
                                        <td>{{$user?->priceFormat($overBudgetAmount)}}
                                            <p class="{{($budget['expense_data'][$productService->id][$month] < $overBudgetAmount)? 'green-text':''}} {{($budget['expense_data'][$productService->id][$month] > $overBudgetAmount)? 'red-text':''}}" >{{($budget['expense_data'][$productService->id][$month] !=0)? (Budget::percentage($budget['expense_data'][$productService->id][$month],$overBudgetAmount) !=0) ?'('.(Budget::percentage($budget['expense_data'][$productService->id][$month],$overBudgetAmount).'%)') :'':''}}</p>

                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            @php
                                $overExpenseBudgetTotalArr = array();
                                  foreach($overExpenseBudgetTotal as $overExpenseBudget)
                                  {
                                      foreach($overExpenseBudget as $k => $value)
                                      {
                                          $overExpenseBudgetTotalArr[$k] = (isset($overExpenseBudgetTotalArr[$k]) ? $overExpenseBudgetTotalArr[$k] + $value : $value);
                                      }
                                  }
                            @endphp
                            <tr class="total">
                                <td class="text-dark"><span></span><strong>{{__('Total :')}}</strong></td>
                                @if(!empty($budgetExpenseTotal) )
                                    @foreach($monthList as $month)
                                        <td class="text-dark {{$month}}_total_expense"><strong>{{$user?->priceFormat($budgetExpenseTotal[$month])}}</strong></td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($expenseTotalArr[$month])}}</strong>
                                            <p>{{($budgetExpenseTotal[$month] !=0)? (Budget::percentage($budgetExpenseTotal[$month],$expenseTotalArr[$month])!=0) ? '('.(Budget::percentage($budgetExpenseTotal[$month],$expenseTotalArr[$month]).'%)') :'':''}}</p>
                                        </td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($overExpenseBudgetTotalArr[$month]) }}</strong>
                                            <p class="{{($budgetExpenseTotal[$month] < $overExpenseBudgetTotalArr[$month])? 'green-text':''}} {{($budgetExpenseTotal[$month] >$overExpenseBudgetTotalArr[$month])? 'red-text':''}}">{{($budgetExpenseTotal[$month] !=0)? (Budget::percentage($budgetExpenseTotal[$month],$overExpenseBudgetTotalArr[$month])!=0) ? '('.(Budget::percentage($budgetExpenseTotal[$month],$overExpenseBudgetTotalArr[$month]).'%)') :'':''}}</p>
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                            <td></td>
                            <tfoot>
                            <tr class="total">
                                <td class="text-dark"><span></span><strong>{{__('NET PROFIT :')}}</strong></td>
                                @php
                                    // NET PROFIT OF OVER BUDGET
                                     $overbudgetprofit = [];
                                     $keys  = array_keys($overBudgetTotalArr + $overExpenseBudgetTotalArr);
                                     foreach($keys as $v)
                                     {
                                         $overbudgetprofit[$v] = (empty($overBudgetTotalArr[$v]) ? 0 : $overBudgetTotalArr[$v]) - (empty($overExpenseBudgetTotalArr[$v]) ? 0 : $overExpenseBudgetTotalArr[$v]);
                                     }
                                     $data['overbudgetprofit']             = $overbudgetprofit;
                                @endphp
                                @if(!empty($budgetprofit) )
                                    @foreach($monthList as $month)
                                        <td class="text-dark"><strong>{{$user?->priceFormat($budgetprofit[$month]) }}</strong></td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($actualprofit[$month]) }}</strong>
                                            <p>{{($budgetprofit[$month] !=0)? (Budget::percentage($budgetprofit[$month],$actualprofit[$month])!=0) ?'('.(Budget::percentage($budgetprofit[$month],$actualprofit[$month]).'%)') :'':''}}</p>

                                        </td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($overbudgetprofit[$month]) }}</strong>
                                            <p class="{{($budgetprofit[$month] < $overbudgetprofit[$month])? 'green-text':''}} {{($budgetprofit[$month] < $overbudgetprofit[$month])? 'green-text':''}}">{{($budgetprofit[$month] !=0)? (Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month])!=0) ? '('.(Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month]).'%)') :'':''}}</p>
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                            </tfoot>
                        </table>
                        {{--  Quarterly Budget--}}
                    @elseif($budget->period == 'quarterly')
                        <table class="table table-bordered table-item data">
                            <thead>
                            <tr>
                                <td rowspan="2"></td> <!-- merge two rows -->
                                @foreach($quarterly_monthlist as $month)
                                    <th colspan="3" scope="colgroup" class="text-center br-1px">{{$month}}</th>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($quarterly_monthlist as $month)
                                    <th scope="col" class="br-1px">Budget</th>
                                    <th scope="col" class="br-1px">Actual</th>
                                    <th scope="col" class="br-1px">Over Budget</th>
                                @endforeach
                            </tr>
                            </thead>
                            <!----INCOME Category ---------------------->
                            <tr>
                                <th colspan="37" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
                            </tr>
                            @php
                                $overBudgetTotal=[];
                            @endphp
                            @foreach ($incomeproduct as $productService)
                                <tr>
                                    <td class="text-dark">{{$productService->name}}</td>
                                    @foreach($quarterly_monthlist as $month)
                                        @php
                                            $budgetAmount= ($budget['income_data'][$productService->id][$month])?$budget['income_data'][$productService->id][$month]:0;
                                            $actualAmount=$incomeArr[$productService->id][$month];
                                            $overBudgetAmount=$actualAmount-$budgetAmount;
                                            $overBudgetTotal[$productService->id][$month]=$overBudgetAmount;
                                        @endphp

                                        <td class="income_data {{$month}}_income">{{!empty ($user?->priceFormat($budget['income_data'][$productService->id][$month]))?$user?->priceFormat($budget['income_data'][$productService->id][$month]):0}}</td>
                                        <td>{{$user?->priceFormat($incomeArr[$productService->id][$month])}}
                                            {{--                                        @if($budget['income_data'][$productService->id][$month] )--}}
                                            <p>{{($budget['income_data'][$productService->id][$month] !=0)? (Budget::percentage($budget['income_data'][$productService->id][$month],$incomeArr[$productService->id][$month])!=0) ? '('.(Budget::percentage($budget['income_data'][$productService->id][$month],$incomeArr[$productService->id][$month]).'%)') :'':''}}</p>

                                        </td>
                                        <td>{{$user?->priceFormat($overBudgetAmount)}}
                                            <p class="{{($budget['income_data'][$productService->id][$month] < $overBudgetAmount)? 'green-text':''}} {{($budget['income_data'][$productService->id][$month] > $overBudgetAmount)? 'red-text':''}}">{{($budget['income_data'][$productService->id][$month] !=0)? '('.(Budget::percentage($budget['income_data'][$productService->id][$month],$overBudgetAmount).'%)') :''}}</p>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            @php
                                $overBudgetTotalArr = array();
                                  foreach($overBudgetTotal as $overBudget)
                                  {
                                      foreach($overBudget as $k => $value)
                                      {
                                          $overBudgetTotalArr[$k] = (isset($overBudgetTotalArr[$k]) ? $overBudgetTotalArr[$k] + $value : $value);
                                      }
                                  }
                            @endphp
                            <tr class="total">
                                <td class="text-dark"><strong>{{__('Total :')}}</strong></td>
                                @if(!empty($budgetTotal) )
                                    @foreach($quarterly_monthlist as $month)
                                        <td class="text-dark {{$month}}_total_income"><strong>{{$user?->priceFormat($budgetTotal[$month])}}</strong></td>
                                        <td><strong>{{$user?->priceFormat($incomeTotalArr[$month])}}</strong>
                                            <p>{{($budgetTotal[$month] !=0)? (Budget::percentage($budgetTotal[$month],$incomeTotalArr[$month]) !=0)?'('.(Budget::percentage($budgetTotal[$month],$incomeTotalArr[$month]).'%)') :'':''}}</p>
                                        </td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($overBudgetTotalArr[$month]) }}</strong>
                                            <p class="{{($budgetTotal[$month] < $overBudgetTotalArr[$month])? 'green-text':''}} {{($budgetTotal[$month] > $overBudgetTotalArr[$month])? 'red-text':''}}">{{($budgetTotal[$month] !=0)? '('.(Budget::percentage($budgetTotal[$month],$overBudgetTotalArr[$month]).'%)') :''}}</p>
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                            <!------------ EXPENSE Category ---------------------->
                            <tr>
                                <th colspan="37" class="text-dark light_blue"><span>{{__('Expense :')}}</span>
                                </th>
                            </tr>
                            @php
                                $overExpenseBudgetTotal=[];
                            @endphp
                            @foreach ($expenseproduct as $productService)
                                <tr>
                                    <td class="text-dark">{{$productService->name}}</td>
                                    @foreach($quarterly_monthlist as $month)
                                        @php
                                            $budgetAmount= ($budget['expense_data'][$productService->id][$month])?$budget['expense_data'][$productService->id][$month]:0;
                                            $actualAmount=$expenseArr[$productService->id][$month];
                                            $overBudgetAmount=$actualAmount-$budgetAmount;
                                            $overExpenseBudgetTotal[$productService->id][$month]=$overBudgetAmount;
                                        @endphp
                                        <td class="expense_data {{$month}}_expense">{{$user?->priceFormat(!empty($budget['expense_data'][$productService->id][$month]))?$user?->priceFormat($budget['expense_data'][$productService->id][$month]):0}}</td>
                                        <td>{{$user?->priceFormat($expenseArr[$productService->id][$month])}}
                                            <p>{{($budget['expense_data'][$productService->id][$month] !=0)? (Budget::percentage($budget['expense_data'][$productService->id][$month],$expenseArr[$productService->id][$month]) !=0) ?'('.(Budget::percentage($budget['expense_data'][$productService->id][$month],$expenseArr[$productService->id][$month]).'%)') :'':''}}</p>
                                        </td>
                                        <td>{{$user?->priceFormat($overBudgetAmount)}}
                                            <p class="{{($budget['expense_data'][$productService->id][$month] < $overBudgetAmount)? 'green-text':''}} {{($budget['expense_data'][$productService->id][$month] > $overBudgetAmount)? 'red-text':''}} ">{{($budget['expense_data'][$productService->id][$month] !=0)? (Budget::percentage($budget['expense_data'][$productService->id][$month],$overBudgetAmount)!=0) ? '('.(Budget::percentage($budget['expense_data'][$productService->id][$month],$overBudgetAmount)
                                        .'%)') :'':''}}</p>
                                        </td>
                                    @endforeach
                                    @if(!empty($budgetExpenseTotal) )
                                        @php
                                            $overExpenseBudgetTotalArr = array();
                                              foreach($overExpenseBudgetTotal as $overExpenseBudget)
                                              {
                                                  foreach($overExpenseBudget as $k => $value)
                                                  {
                                                      $overExpenseBudgetTotalArr[$k] = (isset($overExpenseBudgetTotalArr[$k]) ? $overExpenseBudgetTotalArr[$k] + $value : $value);
                                                  }
                                              }
                                        @endphp
                                    @endif
                                </tr>
                            @endforeach
                            <tr class="total">
                                <td class="text-dark"><span></span><strong>{{__('Total :')}}</strong></td>
                                @if(!empty($budgetExpenseTotal) )
                                    @foreach($quarterly_monthlist as $month)
                                        <td class="text-dark {{$month}}_total_expense"><strong>{{$user?->priceFormat($budgetExpenseTotal[$month])}}</strong></td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($expenseTotalArr[$month])}}</strong>
                                            <p>{{($budgetExpenseTotal[$month] !=0)? (Budget::percentage($budgetExpenseTotal[$month],$expenseTotalArr[$month])!=0) ? '('.(Budget::percentage($budgetExpenseTotal[$month],$expenseTotalArr[$month]).'%)') :'':''}}</p>
                                        </td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($overExpenseBudgetTotalArr[$month]) }}</strong>
                                            <p class="{{($budgetExpenseTotal[$month] < $overExpenseBudgetTotalArr[$month])? 'green-text':''}} {{($budgetExpenseTotal[$month] > $overExpenseBudgetTotalArr[$month])? 'red-text':''}}">{{($budgetExpenseTotal[$month] !=0)? (Budget::percentage($budgetExpenseTotal[$month],$overExpenseBudgetTotalArr[$month])!=0) ?'('.(Budget::percentage($budgetExpenseTotal[$month],$overExpenseBudgetTotalArr[$month]).'%)') :'':''}}</p>
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                            <td></td>
                            <tfoot>
                            <tr class="total">
                                <td class="text-dark"><span></span><strong>{{__('NET PROFIT :')}}</strong></td>
                                @if(!empty($overExpenseBudgetTotalArr) )
                                    @php
                                        // NET PROFIT OF OVER BUDGET
                                         $overbudgetprofit = [];
                                         $keys  = array_keys($overBudgetTotalArr + $overExpenseBudgetTotalArr);
                                         foreach($keys as $v)
                                         {
                                             $overbudgetprofit[$v] = (empty($overBudgetTotalArr[$v]) ? 0 : $overBudgetTotalArr[$v]) - (empty($overExpenseBudgetTotalArr[$v]) ? 0 : $overExpenseBudgetTotalArr[$v]);
                                         }
                                         $data['overbudgetprofit']             = $overbudgetprofit;
                                    @endphp
                                @endif
                                @if(!empty($budgetprofit) )
                                    @foreach($quarterly_monthlist as $month)
                                        <td class="text-dark"><strong>{{$user?->priceFormat($budgetprofit[$month])}}</strong></td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($actualprofit[$month]) }}</strong></td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($overbudgetprofit[$month]) }}</strong>
                                            <p class="{{($budgetprofit[$month] < $overbudgetprofit[$month])? 'green-text':''}} {{($budgetprofit[$month] > $overbudgetprofit[$month])? 'red-text':''}}">{{($budgetprofit[$month] !=0)? (Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month])!=0) ? '('.(Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month]).'%)') :'':''}}</p>
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                            </tfoot>
                        </table>
                        {{--  Half -Yearly Budget--}}
                    @elseif($budget->period == 'half-yearly')
                        <table class="table table-bordered table-item data">
                            <thead>
                            <tr>
                                <td rowspan="2"></td>
                                @foreach($half_yearly_monthlist as $month)
                                    <th colspan="3" scope="colgroup" class="text-center br-1px">{{$month}}</th>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($half_yearly_monthlist as $month)
                                    <th scope="col" class="br-1px">Budget</th>
                                    <th scope="col" class="br-1px">Actual</th>
                                    <th scope="col" class="br-1px">Over Budget</th>
                                @endforeach
                            </tr>
                            </thead>
                            <!----INCOME Category ---------------------->
                            <tr>
                                <th colspan="37" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
                            </tr>
                            @php
                                $overBudgetTotal=[];
                            @endphp
                            @foreach ($incomeproduct as $productService)
                                <tr>
                                    <td class="text-dark">{{$productService->name}}</td>
                                    @foreach($half_yearly_monthlist as $month)
                                        @php
                                            $budgetAmount= ($budget['income_data'][$productService->id][$month])?$budget['income_data'][$productService->id][$month]:0;
                                            $actualAmount=$incomeArr[$productService->id][$month];
                                            $overBudgetAmount=$actualAmount-$budgetAmount;
                                            $overBudgetTotal[$productService->id][$month]=$overBudgetAmount;
                                        @endphp
                                        <td class="income_data {{$month}}_income">{{!empty ($user?->priceFormat($budget['income_data'][$productService->id][$month]))?$user?->priceFormat($budget['income_data'][$productService->id][$month]):0}}</td>
                                        <td>{{$user?->priceFormat($incomeArr[$productService->id][$month])}}
                                            <p>{{($budget['income_data'][$productService->id][$month] !=0)? (Budget::percentage($budget['income_data'][$productService->id][$month],$incomeArr[$productService->id][$month])!=0) ?'('.(Budget::percentage($budget['income_data'][$productService->id][$month],$incomeArr[$productService->id][$month]).'%)') :'':''}}</p>
                                        </td>
                                        <td>{{$user?->priceFormat($overBudgetAmount)}}
                                            <p class="{{($budget['income_data'][$productService->id][$month] < $overBudgetAmount)? 'green-text':''}} {{($budget['income_data'][$productService->id][$month] > $overBudgetAmount)? 'red-text':''}}">{{($budget['income_data'][$productService->id][$month] !=0)? (Budget::percentage($budget['income_data'][$productService->id][$month],$overBudgetAmount)!=0) ?'('.(Budget::percentage($budget['income_data'][$productService->id][$month],
                                        $overBudgetAmount).'%)') :'':''}}</p>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            @php
                                $overBudgetTotalArr = array();
                                  foreach($overBudgetTotal as $overBudget)
                                  {
                                      foreach($overBudget as $k => $value)
                                      {
                                          $overBudgetTotalArr[$k] = (isset($overBudgetTotalArr[$k]) ? $overBudgetTotalArr[$k] + $value : $value);
                                      }
                                  }
                            @endphp
                            <tr class="total">
                                <td class="text-dark"><span></span><strong>{{__('Total :')}}</strong></td>
                                @if(!empty($budgetTotal) )
                                    @foreach($half_yearly_monthlist as $month)

                                        <td class="text-dark {{$month}}_total_income"><strong>{{$user?->priceFormat($budgetTotal[$month])}}</strong></td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($incomeTotalArr[$month])}}</strong>
                                            <p>{{($budgetTotal[$month] !=0)? (Budget::percentage($budgetTotal[$month],$incomeTotalArr[$month])!=0) ?'('.(Budget::percentage($budgetTotal[$month],$incomeTotalArr[$month]).'%)') :'':''}}</p>
                                        </td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($overBudgetTotalArr[$month]) }}</strong>
                                            <p class="{{($budgetTotal[$month] < $overBudgetTotalArr[$month])? 'green-text':''}} {{($budgetTotal[$month] > $overBudgetTotalArr[$month])? 'red-text':''}}">{{($budgetTotal[$month] !=0)? (Budget::percentage($budgetTotal[$month],$overBudgetTotalArr[$month])!=0) ?'('.(Budget::percentage($budgetTotal[$month],$overBudgetTotalArr[$month]).'%)') :'':''}}</p>
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                            <!------------ EXPENSE Category ---------------------->
                            <tr>
                                <th colspan="37" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
                            </tr>
                            @php
                                $overExpenseBudgetTotal=[];
                            @endphp
                            @foreach ($expenseproduct as $productService)
                                <tr>
                                    <td class="text-dark">{{$productService->name}}</td>
                                    @foreach($half_yearly_monthlist as $month)
                                        @php
                                            $budgetAmount= ($budget['expense_data'][$productService->id][$month])?$budget['expense_data'][$productService->id][$month]:0;
                                            $actualAmount=$expenseArr[$productService->id][$month];
                                            $overBudgetAmount=$actualAmount-$budgetAmount;
                                            $overExpenseBudgetTotal[$productService->id][$month]=$overBudgetAmount;
                                        @endphp
                                        <td class="expense_data {{$month}}_expense">{{$user?->priceFormat(!empty($budget['expense_data'][$productService->id][$month]))?$user?->priceFormat($budget['expense_data'][$productService->id][$month]):0}}</td>
                                        <td>{{$user?->priceFormat($expenseArr[$productService->id][$month])}}
                                            <p>{{($budget['expense_data'][$productService->id][$month] !=0)? (Budget::percentage($budget['expense_data'][$productService->id][$month],$expenseArr[$productService->id][$month])!=0) ?'('.(Budget::percentage($budget['expense_data'][$productService->id][$month],$expenseArr[$productService->id][$month]).'%)') :'':''}}</p>
                                        </td>
                                        <td>{{$user?->priceFormat($overBudgetAmount)}}
                                            <p class="{{($budget['expense_data'][$productService->id][$month] < $overBudgetAmount)? 'green-text':''}} {{($budget['expense_data'][$productService->id][$month] > $overBudgetAmount)? 'red-text':''}}">{{($budget['expense_data'][$productService->id][$month] !=0)? (Budget::percentage($budget['expense_data'][$productService->id][$month],$overBudgetAmount)!=0)?'('.(Budget::percentage
                                        ($budget['expense_data'][$productService->id][$month],$overBudgetAmount).'%)') :'':''}}</p>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            @php
                                $overExpenseBudgetTotalArr = array();
                                  foreach($overExpenseBudgetTotal as $overExpenseBudget)
                                  {
                                      foreach($overExpenseBudget as $k => $value)
                                      {
                                          $overExpenseBudgetTotalArr[$k] = (isset($overExpenseBudgetTotalArr[$k]) ? $overExpenseBudgetTotalArr[$k] + $value : $value);
                                      }
                                  }
                            @endphp
                            <tr class="total">
                                <td class="text-dark"><span></span><strong>{{__('Total :')}}</strong></td>
                                @if(!empty($budgetExpenseTotal) )
                                    @foreach($half_yearly_monthlist as $month)
                                        <td class="text-dark {{$month}}_total_expense"><strong>{{$user?->priceFormat($budgetExpenseTotal[$month])}}</strong></td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($expenseTotalArr[$month])}}</strong>
                                            <p>{{($budgetExpenseTotal[$month] !=0)? (Budget::percentage($budgetExpenseTotal[$month],$expenseTotalArr[$month])!=0) ?'('.(Budget::percentage($budgetExpenseTotal[$month],$expenseTotalArr[$month]).'%)') :'':''}}</p>
                                        </td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($overExpenseBudgetTotalArr[$month]) }}</strong>
                                            <p class="{{($budgetExpenseTotal[$month] < $overExpenseBudgetTotalArr[$month])? 'green-text':''}} {{($budgetExpenseTotal[$month] > $overExpenseBudgetTotalArr[$month])? 'red-text':''}}">{{($budgetExpenseTotal[$month] !=0)? (Budget::percentage($budgetExpenseTotal[$month],$overExpenseBudgetTotalArr[$month])!=0) ? '('.(Budget::percentage($budgetExpenseTotal[$month],$overExpenseBudgetTotalArr[$month]).'%)') :'':''}}</p>
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                            <td></td>
                            <tfoot>
                            <tr class="total">
                                <td class="text-dark"><span></span><strong>{{__('NET PROFIT :')}}</strong></td>
                                @php
                                    // NET PROFIT OF OVER BUDGET
                                     $overbudgetprofit = [];
                                     $keys  = array_keys($overBudgetTotalArr + $overExpenseBudgetTotalArr);
                                     foreach($keys as $v)
                                     {
                                         $overbudgetprofit[$v] = (empty($overBudgetTotalArr[$v]) ? 0 : $overBudgetTotalArr[$v]) - (empty($overExpenseBudgetTotalArr[$v]) ? 0 : $overExpenseBudgetTotalArr[$v]);
                                     }
                                     $data['overbudgetprofit']             = $overbudgetprofit;
                                @endphp
                                @if(!empty($budgetprofit) )
                                    @foreach($half_yearly_monthlist as $month)
                                        <td class="text-dark"><strong>{{$user?->priceFormat($budgetprofit[$month])}}</strong></td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($actualprofit[$month]) }}</strong>
                                            <p>{{($budgetprofit[$month] !=0)? (Budget::percentage($budgetprofit[$month],$actualprofit[$month])!=0) ? '('.(Budget::percentage($budgetprofit[$month],$actualprofit[$month]).'%)') :'':''}}</p>
                                        </td>
                                        <td class="text-dark"><strong>{{$user?->priceFormat($overbudgetprofit[$month]) }}</strong>
                                            <p class="{{($budgetprofit[$month] < $overbudgetprofit[$month])? 'green-text':''}} {{($budgetprofit[$month] > $overbudgetprofit[$month])? 'red-text':''}}">{{($budgetprofit[$month] !=0)? (Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month])!=0) ?'('.(Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month]).'%)') :'':''}}</p>
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                            </tfoot>
                        </table>
                        {{-- Yearly Budget--}}
                    @else
                        <table class="table table-bordered table-item data">
                            <thead>
                            <tr>
                                <td rowspan="2"></td> <!-- merge two rows -->
                                @foreach($yearly_monthlist as $month)
                                    <th colspan="3" scope="colgroup" class="text-center br-1px">{{$month}}</th>
                                @endforeach`
                            </tr>
                            <tr>
                                @foreach($yearly_monthlist as $month)
                                    <th scope="col" class="br-1px">Budget</th>
                                    <th scope="col" class="br-1px">Actual</th>
                                    <th scope="col" class="br-1px">Over Budget</th>
                                @endforeach
                            </tr>
                            </thead>
                            <!----INCOME Category ---------------------->
                            <tr>
                                <th colspan="37" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
                            </tr>
                            @php
                                $overBudgetTotal=[];
                            @endphp
                            @foreach ($incomeproduct as $productService)
                                <tr>
                                    <td class="text-dark">{{$productService->name}}</td>
                                    @foreach($yearly_monthlist as $month)
                                        @php
                                            $budgetAmount= ($budget['income_data'][$productService->id][$month])?$budget['income_data'][$productService->id][$month]:0;
                                            $actualAmount=$incomeArr[$productService->id][$month];
                                            $overBudgetAmount=$actualAmount-$budgetAmount;
                                            $overBudgetTotal[$productService->id][$month]=$overBudgetAmount;
                                        @endphp
                                        <td class="income_data {{$month}}_income">{{!empty ($user?->priceFormat($budget['income_data'][$productService->id][$month]))?$user?->priceFormat($budget['income_data'][$productService->id][$month]):0}}</td>
                                        <td>{{$user?->priceFormat($incomeArr[$productService->id][$month])}}
                                            <p>{{($budget['income_data'][$productService->id][$month] !=0)? (Budget::percentage($budget['income_data'][$productService->id][$month],$incomeArr[$productService->id][$month])!=0) ?'('.(Budget::percentage($budget['income_data'][$productService->id][$month],$incomeArr[$productService->id][$month]).'%)') :'':''}}</p>
                                        </td>
                                        <td>{{$user?->priceFormat($overBudgetAmount)}}
                                            <p class="{{($budget['income_data'][$productService->id][$month] < $overBudgetAmount)? 'green-text':''}} {{($budget['income_data'][$productService->id][$month] > $overBudgetAmount)? 'red-text':''}}">{{($budget['income_data'][$productService->id][$month] !=0)? (Budget::percentage($budget['income_data'][$productService->id][$month],$overBudgetAmount)!=0) ?'('.(Budget::percentage
                                        ($budget['income_data'][$productService->id][$month],$overBudgetAmount).'%)') :'':''}}</p>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            @php
                                $overBudgetTotalArr = array();
                                  foreach($overBudgetTotal as $overBudget)
                                  {
                                      foreach($overBudget as $k => $value)
                                      {
                                          $overBudgetTotalArr[$k] = (isset($overBudgetTotalArr[$k]) ? $overBudgetTotalArr[$k] + $value : $value);
                                      }
                                  }
                            @endphp
                            <tr class="total text-dark">
                                <td class=""><span></span><strong>{{__('Total :')}}</strong></td>
                                @foreach($yearly_monthlist as $month)
                                    @php
                                        @endphp
                                    <td class="text-dark {{$month}}_total_income"><strong>{{$user?->priceFormat($budgetTotal[$month])}}</strong></td>
                                    <td class="text-dark"><strong>{{$user?->priceFormat($incomeTotalArr[$month])}}</strong>
                                        <p>{{($budgetTotal[$month] !=0)? (Budget::percentage($budgetTotal[$month],$incomeTotalArr[$month])!=0)?'('.(Budget::percentage($budgetTotal[$month],$incomeTotalArr[$month]).'%)') :'':''}}</p>
                                    </td>
                                    <td class="text-dark"><strong>{{$user?->priceFormat($overBudgetTotalArr[$month]) }}</strong>
                                        <p class="{{($budgetTotal[$month] < $overBudgetTotalArr[$month])? 'green-text':''}} {{($budgetTotal[$month] > $overBudgetTotalArr[$month])? 'red-text':''}}">{{($budgetTotal[$month] !=0)? (Budget::percentage($budgetTotal[$month],$overBudgetTotalArr[$month])!=0) ?'('.(Budget::percentage($budgetTotal[$month],$overBudgetTotalArr[$month]).'%)') :'':''}}</p>
                                    </td>
                                @endforeach
                            </tr>
                            <!------------ EXPENSE Category ---------------------->
                            <tr>
                                <th colspan="37" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
                            </tr>
                            @php
                                $overExpenseBudgetTotal=[];
                            @endphp
                            @foreach ($expenseproduct as $productService)
                                <tr>
                                    <td class="text-dark">{{$productService->name}}</td>
                                    @foreach($yearly_monthlist as $month)
                                        @php
                                            $budgetAmount= ($budget['expense_data'][$productService->id][$month])?$budget['expense_data'][$productService->id][$month]:0;
                                            $actualAmount=$expenseArr[$productService->id][$month];
                                            $overBudgetAmount=$actualAmount-$budgetAmount;
                                            $overExpenseBudgetTotal[$productService->id][$month]=$overBudgetAmount;
                                        @endphp
                                        <td class="expense_data {{$month}}_expense">{{$user?->priceFormat(!empty($budget['expense_data'][$productService->id][$month]))?$user?->priceFormat($budget['expense_data'][$productService->id][$month]):0}}</td>
                                        <td>{{$user?->priceFormat($expenseArr[$productService->id][$month])}}
                                            <p>{{($budget['expense_data'][$productService->id][$month] !=0)? (Budget::percentage($budget['expense_data'][$productService->id][$month],$expenseArr[$productService->id][$month])!=0) ?'('.(Budget::percentage($budget['expense_data'][$productService->id][$month],$expenseArr[$productService->id][$month]).'%)') :'':''}}</p>
                                        </td>
                                        <td>{{$user?->priceFormat($overBudgetAmount)}}
                                            <p class="{{($budget['expense_data'][$productService->id][$month] < $overBudgetAmount)? 'green-text':''}} {{($budget['expense_data'][$productService->id][$month] > $overBudgetAmount)? 'red-text':''}}">{{($budget['expense_data'][$productService->id][$month] !=0)? (Budget::percentage($budget['expense_data'][$productService->id][$month],$overBudgetAmount)!=0) ?'('.(Budget::percentage
                                        ($budget['expense_data'][$productService->id][$month],$overBudgetAmount).'%)') :'':''}}</p>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            @php
                                $overExpenseBudgetTotalArr = array();
                                  foreach($overExpenseBudgetTotal as $overExpenseBudget)
                                  {
                                      foreach($overExpenseBudget as $k => $value)
                                      {
                                          $overExpenseBudgetTotalArr[$k] = (isset($overExpenseBudgetTotalArr[$k]) ? $overExpenseBudgetTotalArr[$k] + $value : $value);
                                      }
                                  }
                            @endphp
                            <tr class="total">
                                <td class="text-dark"><span></span><strong>{{__('Total :')}}</strong></td>
                                @foreach($yearly_monthlist as $month)
                                    @php
                                        @endphp
                                    <td class="text-dark {{$month}}_total_expense"><strong>{{$user?->priceFormat($budgetExpenseTotal[$month])}}</strong></td>
                                    <td class="text-dark"><strong>{{$user?->priceFormat($expenseTotalArr[$month])}}</strong>
                                        <p>{{($budgetExpenseTotal[$month] !=0)? (Budget::percentage($budgetExpenseTotal[$month],$expenseTotalArr[$month])!=0) ?'('.(Budget::percentage($budgetExpenseTotal[$month],$expenseTotalArr[$month]).'%)') :'':''}}</p>
                                    </td>
                                    <td class="text-dark"><strong>{{$user?->priceFormat($overExpenseBudgetTotalArr[$month]) }}</strong>
                                        <p class="{{($budgetExpenseTotal[$month] < $overExpenseBudgetTotalArr[$month])? 'green-text':''}} {{($budgetExpenseTotal[$month] > $overExpenseBudgetTotalArr[$month])? 'red-text':''}}">{{($budgetExpenseTotal[$month] !=0)? (Budget::percentage($budgetExpenseTotal[$month],$overExpenseBudgetTotalArr[$month])!=0) ? '('.(Budget::percentage($budgetExpenseTotal[$month],$overExpenseBudgetTotalArr[$month]).'%)') :'':''}}</p>
                                    </td>
                                @endforeach
                            </tr>
                            <td></td>
                            <tfoot>
                            <tr class="total">
                                <td class="text-dark"><span></span><strong>{{__('NET PROFIT :')}}</strong></td>
                                @php
                                    // NET PROFIT OF OVER BUDGET
                                     $overbudgetprofit = [];
                                     $keys  = array_keys($overBudgetTotalArr + $overExpenseBudgetTotalArr);
                                     foreach($keys as $v)
                                     {
                                         $overbudgetprofit[$v] = (empty($overBudgetTotalArr[$v]) ? 0 : $overBudgetTotalArr[$v]) - (empty($overExpenseBudgetTotalArr[$v]) ? 0 : $overExpenseBudgetTotalArr[$v]);
                                     }
                                     $data['overbudgetprofit']             = $overbudgetprofit;
                                @endphp
                                @foreach($yearly_monthlist as $month)
                                    <td class="text-dark"><strong>{{$user?->priceFormat($budgetprofit[$month])}}</strong></td>
                                    <td class="text-dark"><strong>{{$user?->priceFormat($actualprofit[$month]) }}</strong>
                                        <p>{{($budgetprofit[$month] !=0)? (Budget::percentage($budgetprofit[$month],$actualprofit[$month])!=0) ?'('.(Budget::percentage($budgetprofit[$month],$actualprofit[$month]).'%)') :'':''}}</p>

                                    </td>
                                    <td class="text-dark"><strong>{{$user?->priceFormat($overbudgetprofit[$month]) }}</strong>
                                        <p class="{{($budgetprofit[$month] < $overbudgetprofit[$month])? 'green-text':''}} {{($budgetprofit[$month] > $overbudgetprofit[$month])? 'red-text':''}}">{{($budgetprofit[$month] !=0)? (Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month])!=0) ?'('.(Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month]).'%)') :'':''}}</p>
                                    </td>
                                @endforeach
                            </tr>
                            </tfoot>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
