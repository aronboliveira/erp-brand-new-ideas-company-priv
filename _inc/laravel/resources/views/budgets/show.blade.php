@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants,
    };
    use App\Models\{Budget,Utility};
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Collection;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $tbCls = "table table-bordered table-item data";
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Budget Vs Actual')}}
@endsection
@if(!empty($budget) && isset($budget->id))
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
            <script defer src="{{ asset('assets/js/routes/budgets/plannerIndex.js') }}"></script>
        @endpush
        <li class="breadcrumb-item">{{ $budget->name ?? __('No budget name available') }}</li>
    @endsection
    @push(StacksConstants::ADM_SCR_PG)
        <script src="{{asset('js/jquery-ui.min.js')}}"></script>
        <script async src="{{ asset('assets/js/routes/budgets/lang/toggle.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/budgets/toggle.js') }}"></script>
    @endpush
    @section(YieldingConstants::ADM_CTT)
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
                            <table class="{{ $tbCls }}">
                                <thead>
                                    @php
                                        $hasMonths = (is_array($monthList ?? null) && count($monthList ?? []) > 0) || (($monthList ?? null) instanceof Collection && ($monthList)->isNotEmpty());
                                        $canFormat = ($user ?? null) && method_exists($user, 'priceFormat');
                                        $pctClass  = Budget::class;
                                        $pctAvail  = class_exists($pctClass) && method_exists($pctClass, 'percentage');
                                    @endphp
                                    <tr>
                                        <td rowspan="2"></td>
                                        @if($hasMonths)
                                            @foreach($monthList as $month)
                                                <th colspan="3" scope="colgroup" class="text-center br-1px">{{ isset($month) && $month !== '' ? $month : __('No month label available') }}</th>
                                            @endforeach
                                        @else
                                            <th colspan="3" scope="colgroup" class="text-center br-1px">{{ __('No months available') }}</th>
                                        @endif
                                    </tr>
                                    <tr>
                                        @if($hasMonths)
                                            @foreach($monthList as $month)
                                                <th scope="col" class="br-1px">{{ __('Budget') }}</th>
                                                <th scope="col" class="br-1px">{{ __('Actual') }}</th>
                                                <th scope="col" class="br-1px">{{ __('Over Budget') }}</th>
                                            @endforeach
                                        @else
                                            <th scope="col" class="br-1px">{{ __('Budget') }}</th>
                                            <th scope="col" class="br-1px">{{ __('Actual') }}</th>
                                            <th scope="col" class="br-1px">{{ __('Over Budget') }}</th>
                                        @endif
                                    </tr>
                                </thead>

                                <tr>
                                    <th colspan="37" class="text-dark light_blue"><span>{{ __('Income :') }}</span></th>
                                </tr>

                                @php
                                    $overBudgetTotal = [];
                                    $hasIncomeProducts = (is_array($incomeproduct ?? null) && count($incomeproduct ?? []) > 0) || (($incomeproduct ?? null) instanceof Collection && ($incomeproduct)->isNotEmpty());
                                @endphp

                                @if($hasIncomeProducts && $hasMonths)
                                    @foreach ($incomeproduct as $productService)
                                        @php
                                            $prodId   = data_get($productService, 'id');
                                            $prodName = data_get($productService, 'name');
                                            $nameOut  = isset($prodName) && $prodName !== '' ? (string) $prodName : __('No product/service name available');
                                        @endphp
                                        <tr>
                                            <td class="text-dark">{{ $nameOut }}</td>
                                            @foreach($monthList as $month)
                                                @php
                                                    $budgetAmount  = (float) (data_get($budget, "income_data.$prodId.$month", 0) ?? 0);
                                                    $actualAmount  = (float) (data_get($incomeArr, "$prodId.$month", 0) ?? 0);
                                                    $overAmount    = $actualAmount - $budgetAmount;
                                                    $overBudgetTotal[$prodId][$month] = $overAmount;

                                                    $budgetFmt = $canFormat ? $user->priceFormat($budgetAmount) : number_format($budgetAmount, 2);
                                                    $actualFmt = $canFormat ? $user->priceFormat($actualAmount) : number_format($actualAmount, 2);
                                                    $overFmt   = $canFormat ? $user->priceFormat($overAmount)   : number_format($overAmount, 2);

                                                    $pctActual = ($budgetAmount != 0 && $pctAvail) ? Budget::percentage($budgetAmount, $actualAmount) : 0;
                                                    $pctOver   = ($budgetAmount != 0 && $pctAvail) ? Budget::percentage($budgetAmount, $overAmount)  : 0;

                                                    $overClass = $budgetAmount < $overAmount ? 'green-text' : ($budgetAmount > $overAmount ? 'red-text' : '');
                                                @endphp
                                                <td class="income_data {{ $month }}_income">{{ $budgetFmt }}</td>
                                                <td>
                                                    {{ $actualFmt }}
                                                    <p>{{ $pctActual != 0 ? '(' . $pctActual . '%)' : '' }}</p>
                                                </td>
                                                <td>
                                                    {{ $overFmt }}
                                                    <p class="{{ $overClass }}">{{ $pctOver != 0 ? '(' . $pctOver . '%)' : '' }}</p>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="text-center text-dark" colspan="37">{{ __('No income products or months available') }}</td>
                                    </tr>
                                @endif

                                @php
                                    $overBudgetTotalArr = [];
                                    foreach(($overBudgetTotal ?? []) as $overBudget){
                                        foreach(($overBudget ?? []) as $k => $value){
                                            $overBudgetTotalArr[$k] = (isset($overBudgetTotalArr[$k]) ? $overBudgetTotalArr[$k] + (float)$value : (float)$value);
                                        }
                                    }

                                    $hasBudgetTotal   = is_array($budgetTotal ?? null) && count($budgetTotal ?? []) > 0;
                                    $hasIncomeTotals  = is_array($incomeTotalArr ?? null) && count($incomeTotalArr ?? []) > 0;
                                @endphp

                                <tr class="total">
                                    <td class="text-dark"><span></span><strong>{{ __('Total :') }}</strong></td>
                                    @if($hasMonths)
                                        @foreach($monthList as $month)
                                            @php
                                                $bTot   = (float) ($hasBudgetTotal  ? ($budgetTotal[$month]   ?? 0) : 0);
                                                $aTot   = (float) ($hasIncomeTotals ? ($incomeTotalArr[$month] ?? 0) : 0);
                                                $oTot   = (float) ($overBudgetTotalArr[$month] ?? 0);

                                                $bFmt   = $canFormat ? $user->priceFormat($bTot) : number_format($bTot, 2);
                                                $aFmt   = $canFormat ? $user->priceFormat($aTot) : number_format($aTot, 2);
                                                $oFmt   = $canFormat ? $user->priceFormat($oTot) : number_format($oTot, 2);

                                                $pctA   = ($bTot != 0 && $pctAvail) ? Budget::percentage($bTot, $aTot) : 0;
                                                $pctO   = ($bTot != 0 && $pctAvail) ? Budget::percentage($bTot, $oTot) : 0;

                                                $oClass = $bTot < $oTot ? 'green-text' : ($bTot > $oTot ? 'red-text' : '');
                                            @endphp
                                            <td class="text-dark {{ $month }}_total_income"><strong>{{ $bFmt }}</strong></td>
                                            <td class="text-dark">
                                                <strong>{{ $aFmt }}</strong>
                                                <p>{{ $pctA != 0 ? '(' . $pctA . '%)' : '' }}</p>
                                            </td>
                                            <td class="text-dark">
                                                <strong>{{ $oFmt }}</strong>
                                                <p class="{{ $oClass }}">{{ $pctO != 0 ? '(' . $pctO . '%)' : '' }}</p>
                                            </td>
                                        @endforeach
                                    @else
                                        <td class="text-dark" colspan="3"><strong>{{ __('No totals available (missing months)') }}</strong></td>
                                    @endif
                                </tr>

                                <tr>
                                    <th colspan="37" class="text-dark light_blue"><span>{{ __('Expense :') }}</span></th>
                                </tr>

                                @php
                                    $overExpenseBudgetTotal = [];
                                    $hasExpenseProducts = (is_array($expenseproduct ?? null) && count($expenseproduct ?? []) > 0) || (($expenseproduct ?? null) instanceof Collection && ($expenseproduct)->isNotEmpty());
                                @endphp

                                @if($hasExpenseProducts && $hasMonths)
                                    @foreach ($expenseproduct as $productService)
                                        @php
                                            $prodId   = data_get($productService, 'id');
                                            $prodName = data_get($productService, 'name');
                                            $nameOut  = isset($prodName) && $prodName !== '' ? (string) $prodName : __('No product/service name available');
                                        @endphp
                                        <tr>
                                            <td class="text-dark">{{ $nameOut }}</td>
                                            @foreach($monthList as $month)
                                                @php
                                                    $budgetAmount  = (float) (data_get($budget, "expense_data.$prodId.$month", 0) ?? 0);
                                                    $actualAmount  = (float) (data_get($expenseArr, "$prodId.$month", 0) ?? 0);
                                                    $overAmount    = $actualAmount - $budgetAmount;
                                                    $overExpenseBudgetTotal[$prodId][$month] = $overAmount;

                                                    $budgetFmt = $canFormat ? $user->priceFormat($budgetAmount) : number_format($budgetAmount, 2);
                                                    $actualFmt = $canFormat ? $user->priceFormat($actualAmount) : number_format($actualAmount, 2);
                                                    $overFmt   = $canFormat ? $user->priceFormat($overAmount)   : number_format($overAmount, 2);

                                                    $pctActual = ($budgetAmount != 0 && $pctAvail) ? Budget::percentage($budgetAmount, $actualAmount) : 0;
                                                    $pctOver   = ($budgetAmount != 0 && $pctAvail) ? Budget::percentage($budgetAmount, $overAmount)  : 0;

                                                    $overClass = $budgetAmount < $overAmount ? 'green-text' : ($budgetAmount > $overAmount ? 'red-text' : '');
                                                @endphp
                                                <td class="expense_data {{ $month }}_expense">{{ $budgetFmt }}</td>
                                                <td>
                                                    {{ $actualFmt }}
                                                    <p>{{ $pctActual != 0 ? '(' . $pctActual . '%)' : '' }}</p>
                                                </td>
                                                <td>
                                                    {{ $overFmt }}
                                                    <p class="{{ $overClass }}">{{ $pctOver != 0 ? '(' . $pctOver . '%)' : '' }}</p>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="text-center text-dark" colspan="37">{{ __('No expense products or months available') }}</td>
                                    </tr>
                                @endif

                                @php
                                    $overExpenseBudgetTotalArr = [];
                                    foreach(($overExpenseBudgetTotal ?? []) as $overExpenseBudget){
                                        foreach(($overExpenseBudget ?? []) as $k => $value){
                                            $overExpenseBudgetTotalArr[$k] = (isset($overExpenseBudgetTotalArr[$k]) ? $overExpenseBudgetTotalArr[$k] + (float)$value : (float)$value);
                                        }
                                    }

                                    $hasBudgetExpenseTotal = is_array($budgetExpenseTotal ?? null) && count($budgetExpenseTotal ?? []) > 0;
                                    $hasExpenseTotals      = is_array($expenseTotalArr ?? null) && count($expenseTotalArr ?? []) > 0;
                                @endphp

                                <tr class="total">
                                    <td class="text-dark"><span></span><strong>{{ __('Total :') }}</strong></td>
                                    @if($hasMonths)
                                        @foreach($monthList as $month)
                                            @php
                                                $bTot   = (float) ($hasBudgetExpenseTotal ? ($budgetExpenseTotal[$month]  ?? 0) : 0);
                                                $aTot   = (float) ($hasExpenseTotals      ? ($expenseTotalArr[$month]     ?? 0) : 0);
                                                $oTot   = (float) ($overExpenseBudgetTotalArr[$month] ?? 0);

                                                $bFmt   = $canFormat ? $user->priceFormat($bTot) : number_format($bTot, 2);
                                                $aFmt   = $canFormat ? $user->priceFormat($aTot) : number_format($aTot, 2);
                                                $oFmt   = $canFormat ? $user->priceFormat($oTot) : number_format($oTot, 2);

                                                $pctA   = ($bTot != 0 && $pctAvail) ? Budget::percentage($bTot, $aTot) : 0;
                                                $pctO   = ($bTot != 0 && $pctAvail) ? Budget::percentage($bTot, $oTot) : 0;

                                                $oClass = $bTot < $oTot ? 'green-text' : ($bTot > $oTot ? 'red-text' : '');
                                            @endphp
                                            <td class="text-dark {{ $month }}_total_expense"><strong>{{ $bFmt }}</strong></td>
                                            <td class="text-dark">
                                                <strong>{{ $aFmt }}</strong>
                                                <p>{{ $pctA != 0 ? '(' . $pctA . '%)' : '' }}</p>
                                            </td>
                                            <td class="text-dark">
                                                <strong>{{ $oFmt }}</strong>
                                                <p class="{{ $oClass }}">{{ $pctO != 0 ? '(' . $pctO . '%)' : '' }}</p>
                                            </td>
                                        @endforeach
                                    @else
                                        <td class="text-dark" colspan="3"><strong>{{ __('No totals available (missing months)') }}</strong></td>
                                    @endif
                                </tr>

                                <td></td>

                                <tfoot>
                                    <tr class="total">
                                        <td class="text-dark"><span></span><strong>{{ __('NET PROFIT :') }}</strong></td>
                                        @php
                                            $overbudgetprofit = [];
                                            $keys = array_keys(($overBudgetTotalArr ?? []) + ($overExpenseBudgetTotalArr ?? []));
                                            foreach($keys as $v){
                                                $overbudgetprofit[$v] = (float) (($overBudgetTotalArr[$v] ?? 0)) - (float) (($overExpenseBudgetTotalArr[$v] ?? 0));
                                            }

                                            $hasBudgetProfit = is_array($budgetprofit ?? null) && count($budgetprofit ?? []) > 0;
                                            $hasActualProfit = is_array($actualprofit ?? null) && count($actualprofit ?? []) > 0;
                                        @endphp

                                        @if($hasMonths)
                                            @foreach($monthList as $month)
                                                @php
                                                    $bProf = (float) ($hasBudgetProfit ? ($budgetprofit[$month] ?? 0) : 0);
                                                    $aProf = (float) ($hasActualProfit ? ($actualprofit[$month] ?? 0) : 0);
                                                    $oProf = (float) ($overbudgetprofit[$month] ?? 0);

                                                    $bFmt  = $canFormat ? $user->priceFormat($bProf) : number_format($bProf, 2);
                                                    $aFmt  = $canFormat ? $user->priceFormat($aProf) : number_format($aProf, 2);
                                                    $oFmt  = $canFormat ? $user->priceFormat($oProf) : number_format($oProf, 2);

                                                    $pctA  = ($bProf != 0 && $pctAvail) ? Budget::percentage($bProf, $aProf) : 0;
                                                    $pctO  = ($bProf != 0 && $pctAvail) ? Budget::percentage($bProf, $oProf) : 0;

                                                    $oClass = $bProf < $oProf ? 'green-text' : ($bProf > $oProf ? 'red-text' : '');
                                                @endphp
                                                <td class="text-dark"><strong>{{ $bFmt }}</strong></td>
                                                <td class="text-dark">
                                                    <strong>{{ $aFmt }}</strong>
                                                    <p>{{ $pctA != 0 ? '(' . $pctA . '%)' : '' }}</p>
                                                </td>
                                                <td class="text-dark">
                                                    <strong>{{ $oFmt }}</strong>
                                                    <p class="{{ $oClass }}">{{ $pctO != 0 ? '(' . $pctO . '%)' : '' }}</p>
                                                </td>
                                            @endforeach
                                        @else
                                            <td class="text-dark" colspan="3"><strong>{{ __('No profit data available (missing months)') }}</strong></td>
                                        @endif
                                    </tr>
                                </tfoot>
                            </table>
                        {{--  Quarterly Budget--}}
                        @elseif($budget->period == 'quarterly')
                            <table class="{{ $tbCls }}">
                                <thead>
                                    @php
                                        $hasMonths = (is_array($quarterly_monthlist ?? null) && count($quarterly_monthlist ?? []) > 0) || (($quarterly_monthlist ?? null) instanceof \Illuminate\Support\Collection && ($quarterly_monthlist)->isNotEmpty());
                                        $canFormat = ($user ?? null) && method_exists($user, 'priceFormat');
                                        $pctAvail  = class_exists(Budget::class) && method_exists(Budget::class, 'percentage');
                                    @endphp
                                    <tr>
                                        <td rowspan="2"></td>
                                        @if($hasMonths)
                                            @foreach($quarterly_monthlist as $month)
                                                <th colspan="3" scope="colgroup" class="text-center br-1px">{{ isset($month) && $month !== '' ? $month : __('No month label available') }}</th>
                                            @endforeach
                                        @else
                                            <th colspan="3" scope="colgroup" class="text-center br-1px">{{ __('No months available') }}</th>
                                        @endif
                                    </tr>
                                    <tr>
                                        @if($hasMonths)
                                            @foreach($quarterly_monthlist as $month)
                                                <th scope="col" class="br-1px">{{ __('Budget') }}</th>
                                                <th scope="col" class="br-1px">{{ __('Actual') }}</th>
                                                <th scope="col" class="br-1px">{{ __('Over Budget') }}</th>
                                            @endforeach
                                        @else
                                            <th scope="col" class="br-1px">{{ __('Budget') }}</th>
                                            <th scope="col" class="br-1px">{{ __('Actual') }}</th>
                                            <th scope="col" class="br-1px">{{ __('Over Budget') }}</th>
                                        @endif
                                    </tr>
                                </thead>

                                <tr>
                                    <th colspan="37" class="text-dark light_blue"><span>{{ __('Income :') }}</span></th>
                                </tr>

                                @php
                                    $overBudgetTotal = [];
                                    $hasIncomeProducts = (is_array($incomeproduct ?? null) && count($incomeproduct ?? []) > 0) || (($incomeproduct ?? null) instanceof \Illuminate\Support\Collection && ($incomeproduct)->isNotEmpty());
                                @endphp

                                @if($hasIncomeProducts && $hasMonths)
                                    @foreach ($incomeproduct as $productService)
                                        @php
                                            $prodId   = data_get($productService, 'id');
                                            $prodName = data_get($productService, 'name');
                                            $nameOut  = isset($prodName) && $prodName !== '' ? (string) $prodName : __('No product/service name available');
                                        @endphp
                                        <tr>
                                            <td class="text-dark">{{ $nameOut }}</td>
                                            @foreach($quarterly_monthlist as $month)
                                                @php
                                                    $budgetAmount = (float) (data_get($budget ?? [], "income_data.$prodId.$month", 0) ?? 0);
                                                    $actualAmount = (float) (data_get($incomeArr ?? [], "$prodId.$month", 0) ?? 0);
                                                    $overAmount   = $actualAmount - $budgetAmount;
                                                    $overBudgetTotal[$prodId][$month] = $overAmount;

                                                    $budgetFmt = $canFormat ? $user->priceFormat($budgetAmount) : number_format($budgetAmount, 2);
                                                    $actualFmt = $canFormat ? $user->priceFormat($actualAmount) : number_format($actualAmount, 2);
                                                    $overFmt   = $canFormat ? $user->priceFormat($overAmount)   : number_format($overAmount, 2);

                                                    $pctActual = ($budgetAmount != 0 && $pctAvail) ? Budget::percentage($budgetAmount, $actualAmount) : 0;
                                                    $pctOver   = ($budgetAmount != 0 && $pctAvail) ? Budget::percentage($budgetAmount, $overAmount)  : 0;

                                                    $overClass = $budgetAmount < $overAmount ? 'green-text' : ($budgetAmount > $overAmount ? 'red-text' : '');
                                                @endphp
                                                <td class="income_data {{ $month }}_income">{{ $budgetFmt }}</td>
                                                <td>
                                                    {{ $actualFmt }}
                                                    <p>{{ $pctActual != 0 ? '(' . $pctActual . '%)' : '' }}</p>
                                                </td>
                                                <td>
                                                    {{ $overFmt }}
                                                    <p class="{{ $overClass }}">{{ $pctOver != 0 ? '(' . $pctOver . '%)' : '' }}</p>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="text-center text-dark" colspan="37">{{ __('No income products or months available') }}</td>
                                    </tr>
                                @endif

                                @php
                                    $overBudgetTotalArr = [];
                                    foreach(($overBudgetTotal ?? []) as $overBudget){
                                        foreach(($overBudget ?? []) as $k => $value){
                                            $overBudgetTotalArr[$k] = (isset($overBudgetTotalArr[$k]) ? $overBudgetTotalArr[$k] + (float)$value : (float)$value);
                                        }
                                    }
                                    $hasBudgetTotal  = is_array($budgetTotal ?? null) && count($budgetTotal ?? []) > 0;
                                    $hasIncomeTotals = is_array($incomeTotalArr ?? null) && count($incomeTotalArr ?? []) > 0;
                                @endphp

                                <tr class="total">
                                    <td class="text-dark"><strong>{{ __('Total :') }}</strong></td>
                                    @if($hasMonths && $hasBudgetTotal)
                                        @foreach($quarterly_monthlist as $month)
                                            @php
                                                $bTot = (float) ($budgetTotal[$month]   ?? 0);
                                                $aTot = (float) ($incomeTotalArr[$month] ?? 0);
                                                $oTot = (float) ($overBudgetTotalArr[$month] ?? 0);

                                                $bFmt = $canFormat ? $user->priceFormat($bTot) : number_format($bTot, 2);
                                                $aFmt = $canFormat ? $user->priceFormat($aTot) : number_format($aTot, 2);
                                                $oFmt = $canFormat ? $user->priceFormat($oTot) : number_format($oTot, 2);

                                                $pctA = ($bTot != 0 && $pctAvail) ? Budget::percentage($bTot, $aTot) : 0;
                                                $pctO = ($bTot != 0 && $pctAvail) ? Budget::percentage($bTot, $oTot) : 0;

                                                $oClass = $bTot < $oTot ? 'green-text' : ($bTot > $oTot ? 'red-text' : '');
                                            @endphp
                                            <td class="text-dark {{ $month }}_total_income"><strong>{{ $bFmt }}</strong></td>
                                            <td>
                                                <strong>{{ $aFmt }}</strong>
                                                <p>{{ $pctA != 0 ? '(' . $pctA . '%)' : '' }}</p>
                                            </td>
                                            <td class="text-dark">
                                                <strong>{{ $oFmt }}</strong>
                                                <p class="{{ $oClass }}">{{ $pctO != 0 ? '(' . $pctO . '%)' : '' }}</p>
                                            </td>
                                        @endforeach
                                    @else
                                        <td class="text-dark" colspan="3"><strong>{{ __('No income totals available') }}</strong></td>
                                    @endif
                                </tr>

                                <tr>
                                    <th colspan="37" class="text-dark light_blue"><span>{{ __('Expense :') }}</span></th>
                                </tr>

                                @php
                                    $overExpenseBudgetTotal = [];
                                    $hasExpenseProducts = (is_array($expenseproduct ?? null) && count($expenseproduct ?? []) > 0) || (($expenseproduct ?? null) instanceof \Illuminate\Support\Collection && ($expenseproduct)->isNotEmpty());
                                @endphp

                                @if($hasExpenseProducts && $hasMonths)
                                    @foreach ($expenseproduct as $productService)
                                        @php
                                            $prodId   = data_get($productService, 'id');
                                            $prodName = data_get($productService, 'name');
                                            $nameOut  = isset($prodName) && $prodName !== '' ? (string) $prodName : __('No product/service name available');
                                        @endphp
                                        <tr>
                                            <td class="text-dark">{{ $nameOut }}</td>
                                            @foreach($quarterly_monthlist as $month)
                                                @php
                                                    $budgetAmount = (float) (data_get($budget ?? [], "expense_data.$prodId.$month", 0) ?? 0);
                                                    $actualAmount = (float) (data_get($expenseArr ?? [], "$prodId.$month", 0) ?? 0);
                                                    $overAmount   = $actualAmount - $budgetAmount;
                                                    $overExpenseBudgetTotal[$prodId][$month] = $overAmount;

                                                    $budgetFmt = $canFormat ? $user->priceFormat($budgetAmount) : number_format($budgetAmount, 2);
                                                    $actualFmt = $canFormat ? $user->priceFormat($actualAmount) : number_format($actualAmount, 2);
                                                    $overFmt   = $canFormat ? $user->priceFormat($overAmount)   : number_format($overAmount, 2);

                                                    $pctActual = ($budgetAmount != 0 && $pctAvail) ? Budget::percentage($budgetAmount, $actualAmount) : 0;
                                                    $pctOver   = ($budgetAmount != 0 && $pctAvail) ? Budget::percentage($budgetAmount, $overAmount)  : 0;

                                                    $overClass = $budgetAmount < $overAmount ? 'green-text' : ($budgetAmount > $overAmount ? 'red-text' : '');
                                                @endphp
                                                <td class="expense_data {{ $month }}_expense">{{ $budgetFmt }}</td>
                                                <td>
                                                    {{ $actualFmt }}
                                                    <p>{{ $pctActual != 0 ? '(' . $pctActual . '%)' : '' }}</p>
                                                </td>
                                                <td>
                                                    {{ $overFmt }}
                                                    <p class="{{ $overClass }}">{{ $pctOver != 0 ? '(' . $pctOver . '%)' : '' }}</p>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="text-center text-dark" colspan="37">{{ __('No expense products or months available') }}</td>
                                    </tr>
                                @endif

                                @php
                                    $overExpenseBudgetTotalArr = [];
                                    foreach(($overExpenseBudgetTotal ?? []) as $overExpenseBudget)
                                        foreach(($overExpenseBudget ?? []) as $k => $value)
                                            $overExpenseBudgetTotalArr[$k] = (isset($overExpenseBudgetTotalArr[$k]) ? $overExpenseBudgetTotalArr[$k] + (float)$value : (float)$value);
                                    $hasBudgetExpenseTotal = is_array($budgetExpenseTotal ?? null) && count($budgetExpenseTotal ?? []) > 0;
                                    $hasExpenseTotals      = is_array($expenseTotalArr ?? null) && count($expenseTotalArr ?? []) > 0;
                                @endphp

                                <tr class="total">
                                    <td class="text-dark"><span></span><strong>{{ __('Total :') }}</strong></td>
                                    @if($hasMonths && $hasBudgetExpenseTotal)
                                        @foreach($quarterly_monthlist as $month)
                                            @php
                                                $bTot = (float) ($budgetExpenseTotal[$month]  ?? 0);
                                                $aTot = (float) ($expenseTotalArr[$month]     ?? 0);
                                                $oTot = (float) ($overExpenseBudgetTotalArr[$month] ?? 0);

                                                $bFmt = $canFormat ? $user->priceFormat($bTot) : number_format($bTot, 2);
                                                $aFmt = $canFormat ? $user->priceFormat($aTot) : number_format($aTot, 2);
                                                $oFmt = $canFormat ? $user->priceFormat($oTot) : number_format($oTot, 2);

                                                $pctA = ($bTot != 0 && $pctAvail) ? Budget::percentage($bTot, $aTot) : 0;
                                                $pctO = ($bTot != 0 && $pctAvail) ? Budget::percentage($bTot, $oTot) : 0;

                                                $oClass = $bTot < $oTot ? 'green-text' : ($bTot > $oTot ? 'red-text' : '');
                                            @endphp
                                            <td class="text-dark {{ $month }}_total_expense"><strong>{{ $bFmt }}</strong></td>
                                            <td class="text-dark">
                                                <strong>{{ $aFmt }}</strong>
                                                <p>{{ $pctA != 0 ? '(' . $pctA . '%)' : '' }}</p>
                                            </td>
                                            <td class="text-dark">
                                                <strong>{{ $oFmt }}</strong>
                                                <p class="{{ $oClass }}">{{ $pctO != 0 ? '(' . $pctO . '%)' : '' }}</p>
                                            </td>
                                        @endforeach
                                    @else
                                        <td class="text-dark" colspan="3"><strong>{{ __('No expense totals available') }}</strong></td>
                                    @endif
                                </tr>

                                <td></td>

                                <tfoot>
                                    <tr class="total">
                                        <td class="text-dark"><span></span><strong>{{ __('NET PROFIT :') }}</strong></td>
                                        @php
                                            $overbudgetprofit = [];
                                            $keys = array_keys(($overBudgetTotalArr ?? []) + ($overExpenseBudgetTotalArr ?? []));
                                            foreach($keys as $v){
                                                $overbudgetprofit[$v] = (float) (($overBudgetTotalArr[$v] ?? 0)) - (float) (($overExpenseBudgetTotalArr[$v] ?? 0));
                                            }
                                            $hasBudgetProfit = is_array($budgetprofit ?? null) && count($budgetprofit ?? []) > 0;
                                            $hasActualProfit = is_array($actualprofit ?? null) && count($actualprofit ?? []) > 0;
                                        @endphp

                                        @if($hasMonths && $hasBudgetProfit)
                                            @foreach($quarterly_monthlist as $month)
                                                @php
                                                    $bProf = (float) ($budgetprofit[$month] ?? 0);
                                                    $aProf = (float) ($actualprofit[$month] ?? 0);
                                                    $oProf = (float) ($overbudgetprofit[$month] ?? 0);

                                                    $bFmt  = $canFormat ? $user->priceFormat($bProf) : number_format($bProf, 2);
                                                    $aFmt  = $canFormat ? $user->priceFormat($aProf) : number_format($aProf, 2);
                                                    $oFmt  = $canFormat ? $user->priceFormat($oProf) : number_format($oProf, 2);

                                                    $pctA  = ($bProf != 0 && $pctAvail) ? Budget::percentage($bProf, $aProf) : 0;
                                                    $pctO  = ($bProf != 0 && $pctAvail) ? Budget::percentage($bProf, $oProf) : 0;

                                                    $oClass = $bProf < $oProf ? 'green-text' : ($bProf > $oProf ? 'red-text' : '');
                                                @endphp
                                                <td class="text-dark"><strong>{{ $bFmt }}</strong></td>
                                                <td class="text-dark"><strong>{{ $aFmt }}</strong></td>
                                                <td class="text-dark">
                                                    <strong>{{ $oFmt }}</strong>
                                                    <p class="{{ $oClass }}">{{ $pctO != 0 ? '(' . $pctO . '%)' : '' }}</p>
                                                </td>
                                            @endforeach
                                        @else
                                            <td class="text-dark" colspan="3"><strong>{{ __('No profit data available') }}</strong></td>
                                        @endif
                                    </tr>
                                </tfoot>
                            </table>
                        {{--  Half -Yearly Budget--}}
                        @elseif($budget->period == 'half-yearly')
                            <table class="{{ $tbCls }}">
                                <thead>
                                    @php
                                        $hasMonths   = (is_array($half_yearly_monthlist ?? null) && count($half_yearly_monthlist ?? []) > 0) || (($half_yearly_monthlist ?? null) instanceof \Illuminate\Support\Collection && ($half_yearly_monthlist)->isNotEmpty());
                                        $canFormat   = ($user ?? null) && method_exists($user, 'priceFormat');
                                        $pctFunction = class_exists(\App\Models\Budget::class) && method_exists(\App\Models\Budget::class, 'percentage');
                                    @endphp
                                    <tr>
                                        <td rowspan="2"></td>
                                        @if($hasMonths)
                                            @foreach($half_yearly_monthlist as $month)
                                                <th colspan="3" scope="colgroup" class="text-center br-1px">{{ isset($month) && $month !== '' ? $month : __('No month label available') }}</th>
                                            @endforeach
                                        @else
                                            <th colspan="3" scope="colgroup" class="text-center br-1px">{{ __('No months available') }}</th>
                                        @endif
                                    </tr>
                                    <tr>
                                        @if($hasMonths)
                                            @foreach($half_yearly_monthlist as $month)
                                                <th scope="col" class="br-1px">{{ __('Budget') }}</th>
                                                <th scope="col" class="br-1px">{{ __('Actual') }}</th>
                                                <th scope="col" class="br-1px">{{ __('Over Budget') }}</th>
                                            @endforeach
                                        @else
                                            <th scope="col" class="br-1px">{{ __('Budget') }}</th>
                                            <th scope="col" class="br-1px">{{ __('Actual') }}</th>
                                            <th scope="col" class="br-1px">{{ __('Over Budget') }}</th>
                                        @endif
                                    </tr>
                                </thead>

                                <tr>
                                    <th colspan="37" class="text-dark light_blue"><span>{{ __('Income :') }}</span></th>
                                </tr>

                                @php
                                    $overBudgetTotal = [];
                                    $hasIncomeProducts = (is_array($incomeproduct ?? null) && count($incomeproduct ?? []) > 0) || (($incomeproduct ?? null) instanceof \Illuminate\Support\Collection && ($incomeproduct)->isNotEmpty());
                                @endphp

                                @if($hasIncomeProducts && $hasMonths)
                                    @foreach ($incomeproduct as $productService)
                                        @php
                                            $prodId   = data_get($productService, 'id');
                                            $prodName = data_get($productService, 'name');
                                            $nameOut  = isset($prodName) && $prodName !== '' ? (string) $prodName : __('No product/service name available');
                                        @endphp
                                        <tr>
                                            <td class="text-dark">{{ $nameOut }}</td>
                                            @foreach($half_yearly_monthlist as $month)
                                                @php
                                                    $budgetAmount = (float) (data_get($budget ?? [], "income_data.$prodId.$month", 0) ?? 0);
                                                    $actualAmount = (float) (data_get($incomeArr ?? [], "$prodId.$month", 0) ?? 0);
                                                    $overAmount   = $actualAmount - $budgetAmount;
                                                    $overBudgetTotal[$prodId][$month] = $overAmount;

                                                    $budgetFmt = $canFormat ? $user->priceFormat($budgetAmount) : number_format($budgetAmount, 2);
                                                    $actualFmt = $canFormat ? $user->priceFormat($actualAmount) : number_format($actualAmount, 2);
                                                    $overFmt   = $canFormat ? $user->priceFormat($overAmount)   : number_format($overAmount, 2);

                                                    $pctActual = ($budgetAmount != 0 && $pctFunction) ? \App\Models\Budget::percentage($budgetAmount, $actualAmount) : 0;
                                                    $pctOver   = ($budgetAmount != 0 && $pctFunction) ? \App\Models\Budget::percentage($budgetAmount, $overAmount)  : 0;

                                                    $overClass = $budgetAmount < $overAmount ? 'green-text' : ($budgetAmount > $overAmount ? 'red-text' : '');
                                                @endphp
                                                <td class="income_data {{ $month }}_income">{{ $budgetFmt }}</td>
                                                <td>
                                                    {{ $actualFmt }}
                                                    <p>{{ $pctActual != 0 ? '(' . $pctActual . '%)' : '' }}</p>
                                                </td>
                                                <td>
                                                    {{ $overFmt }}
                                                    <p class="{{ $overClass }}">{{ $pctOver != 0 ? '(' . $pctOver . '%)' : '' }}</p>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="text-center text-dark" colspan="37">{{ __('No income products or months available') }}</td>
                                    </tr>
                                @endif

                                @php
                                    $overBudgetTotalArr = [];
                                    foreach(($overBudgetTotal ?? []) as $overBudget){
                                        foreach(($overBudget ?? []) as $k => $value){
                                            $overBudgetTotalArr[$k] = (isset($overBudgetTotalArr[$k]) ? $overBudgetTotalArr[$k] + (float)$value : (float)$value);
                                        }
                                    }
                                    $hasBudgetTotal  = is_array($budgetTotal ?? null) && count($budgetTotal ?? []) > 0;
                                    $hasIncomeTotals = is_array($incomeTotalArr ?? null) && count($incomeTotalArr ?? []) > 0;
                                @endphp

                                <tr class="total">
                                    <td class="text-dark"><span></span><strong>{{ __('Total :') }}</strong></td>
                                    @if($hasMonths && $hasBudgetTotal)
                                        @foreach($half_yearly_monthlist as $month)
                                            @php
                                                $bTot = (float) ($budgetTotal[$month]   ?? 0);
                                                $aTot = (float) ($incomeTotalArr[$month] ?? 0);
                                                $oTot = (float) ($overBudgetTotalArr[$month] ?? 0);

                                                $bFmt = $canFormat ? $user->priceFormat($bTot) : number_format($bTot, 2);
                                                $aFmt = $canFormat ? $user->priceFormat($aTot) : number_format($aTot, 2);
                                                $oFmt = $canFormat ? $user->priceFormat($oTot) : number_format($oTot, 2);

                                                $pctA = ($bTot != 0 && $pctFunction) ? \App\Models\Budget::percentage($bTot, $aTot) : 0;
                                                $pctO = ($bTot != 0 && $pctFunction) ? \App\Models\Budget::percentage($bTot, $oTot) : 0;

                                                $oClass = $bTot < $oTot ? 'green-text' : ($bTot > $oTot ? 'red-text' : '');
                                            @endphp
                                            <td class="text-dark {{ $month }}_total_income"><strong>{{ $bFmt }}</strong></td>
                                            <td class="text-dark">
                                                <strong>{{ $aFmt }}</strong>
                                                <p>{{ $pctA != 0 ? '(' . $pctA . '%)' : '' }}</p>
                                            </td>
                                            <td class="text-dark">
                                                <strong>{{ $oFmt }}</strong>
                                                <p class="{{ $oClass }}">{{ $pctO != 0 ? '(' . $pctO . '%)' : '' }}</p>
                                            </td>
                                        @endforeach
                                    @else
                                        <td class="text-dark" colspan="3"><strong>{{ __('No income totals available') }}</strong></td>
                                    @endif
                                </tr>

                                <tr>
                                    <th colspan="37" class="text-dark light_blue"><span>{{ __('Expense :') }}</span></th>
                                </tr>

                                @php
                                    $overExpenseBudgetTotal = [];
                                    $hasExpenseProducts = (is_array($expenseproduct ?? null) && count($expenseproduct ?? []) > 0) || (($expenseproduct ?? null) instanceof \Illuminate\Support\Collection && ($expenseproduct)->isNotEmpty());
                                @endphp

                                @if($hasExpenseProducts && $hasMonths)
                                    @foreach ($expenseproduct as $productService)
                                        @php
                                            $prodId   = data_get($productService, 'id');
                                            $prodName = data_get($productService, 'name');
                                            $nameOut  = isset($prodName) && $prodName !== '' ? (string) $prodName : __('No product/service name available');
                                        @endphp
                                        <tr>
                                            <td class="text-dark">{{ $nameOut }}</td>
                                            @foreach($half_yearly_monthlist as $month)
                                                @php
                                                    $budgetAmount = (float) (data_get($budget ?? [], "expense_data.$prodId.$month", 0) ?? 0);
                                                    $actualAmount = (float) (data_get($expenseArr ?? [], "$prodId.$month", 0) ?? 0);
                                                    $overAmount   = $actualAmount - $budgetAmount;
                                                    $overExpenseBudgetTotal[$prodId][$month] = $overAmount;

                                                    $budgetFmt = $canFormat ? $user->priceFormat($budgetAmount) : number_format($budgetAmount, 2);
                                                    $actualFmt = $canFormat ? $user->priceFormat($actualAmount) : number_format($actualAmount, 2);
                                                    $overFmt   = $canFormat ? $user->priceFormat($overAmount)   : number_format($overAmount, 2);

                                                    $pctActual = ($budgetAmount != 0 && $pctFunction) ? \App\Models\Budget::percentage($budgetAmount, $actualAmount) : 0;
                                                    $pctOver   = ($budgetAmount != 0 && $pctFunction) ? \App\Models\Budget::percentage($budgetAmount, $overAmount)  : 0;

                                                    $overClass = $budgetAmount < $overAmount ? 'green-text' : ($budgetAmount > $overAmount ? 'red-text' : '');
                                                @endphp
                                                <td class="expense_data {{ $month }}_expense">{{ $budgetFmt }}</td>
                                                <td class="text-dark">
                                                    {{ $actualFmt }}
                                                    <p>{{ $pctActual != 0 ? '(' . $pctActual . '%)' : '' }}</p>
                                                </td>
                                                <td class="text-dark">
                                                    {{ $overFmt }}
                                                    <p class="{{ $overClass }}">{{ $pctOver != 0 ? '(' . $pctOver . '%)' : '' }}</p>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="text-center text-dark" colspan="37">{{ __('No expense products or months available') }}</td>
                                    </tr>
                                @endif

                                @php
                                    $overExpenseBudgetTotalArr = [];
                                    foreach(($overExpenseBudgetTotal ?? []) as $overExpenseBudget){
                                        foreach(($overExpenseBudget ?? []) as $k => $value){
                                            $overExpenseBudgetTotalArr[$k] = (isset($overExpenseBudgetTotalArr[$k]) ? $overExpenseBudgetTotalArr[$k] + (float)$value : (float)$value);
                                        }
                                    }
                                    $hasBudgetExpenseTotal = is_array($budgetExpenseTotal ?? null) && count($budgetExpenseTotal ?? []) > 0;
                                    $hasExpenseTotals      = is_array($expenseTotalArr ?? null) && count($expenseTotalArr ?? []) > 0;
                                @endphp

                                <tr class="total">
                                    <td class="text-dark"><span></span><strong>{{ __('Total :') }}</strong></td>
                                    @if($hasMonths && $hasBudgetExpenseTotal)
                                        @foreach($half_yearly_monthlist as $month)
                                            @php
                                                $bTot = (float) ($budgetExpenseTotal[$month]       ?? 0);
                                                $aTot = (float) ($expenseTotalArr[$month]          ?? 0);
                                                $oTot = (float) ($overExpenseBudgetTotalArr[$month] ?? 0);

                                                $bFmt = $canFormat ? $user->priceFormat($bTot) : number_format($bTot, 2);
                                                $aFmt = $canFormat ? $user->priceFormat($aTot) : number_format($aTot, 2);
                                                $oFmt = $canFormat ? $user->priceFormat($oTot) : number_format($oTot, 2);

                                                $pctA = ($bTot != 0 && $pctFunction) ? \App\Models\Budget::percentage($bTot, $aTot) : 0;
                                                $pctO = ($bTot != 0 && $pctFunction) ? \App\Models\Budget::percentage($bTot, $oTot) : 0;

                                                $oClass = $bTot < $oTot ? 'green-text' : ($bTot > $oTot ? 'red-text' : '');
                                            @endphp
                                            <td class="text-dark {{ $month }}_total_expense"><strong>{{ $bFmt }}</strong></td>
                                            <td class="text-dark">
                                                <strong>{{ $aFmt }}</strong>
                                                <p>{{ $pctA != 0 ? '(' . $pctA . '%)' : '' }}</p>
                                            </td>
                                            <td class="text-dark">
                                                <strong>{{ $oFmt }}</strong>
                                                <p class="{{ $oClass }}">{{ $pctO != 0 ? '(' . $pctO . '%)' : '' }}</p>
                                            </td>
                                        @endforeach
                                    @else
                                        <td class="text-dark" colspan="3"><strong>{{ __('No expense totals available') }}</strong></td>
                                    @endif
                                </tr>

                                <td></td>

                                <tfoot>
                                    <tr class="total">
                                        @php
                                            $overbudgetprofit = [];
                                            $keys = array_keys(($overBudgetTotalArr ?? []) + ($overExpenseBudgetTotalArr ?? []));
                                            foreach($keys as $v){
                                                $overbudgetprofit[$v] = (float) (($overBudgetTotalArr[$v] ?? 0)) - (float) (($overExpenseBudgetTotalArr[$v] ?? 0));
                                            }
                                            $hasBudgetProfit = is_array($budgetprofit ?? null) && count($budgetprofit ?? []) > 0;
                                            $hasActualProfit = is_array($actualprofit ?? null) && count($actualprofit ?? []) > 0;
                                        @endphp
                                        <td class="text-dark"><span></span><strong>{{ __('NET PROFIT :') }}</strong></td>
                                        @if($hasMonths && $hasBudgetProfit)
                                            @foreach($half_yearly_monthlist as $month)
                                                @php
                                                    $bProf = (float) ($budgetprofit[$month]     ?? 0);
                                                    $aProf = (float) ($actualprofit[$month]     ?? 0);
                                                    $oProf = (float) ($overbudgetprofit[$month] ?? 0);

                                                    $bFmt  = $canFormat ? $user->priceFormat($bProf) : number_format($bProf, 2);
                                                    $aFmt  = $canFormat ? $user->priceFormat($aProf) : number_format($aProf, 2);
                                                    $oFmt  = $canFormat ? $user->priceFormat($oProf) : number_format($oProf, 2);

                                                    $pctO  = ($bProf != 0 && $pctFunction) ? \App\Models\Budget::percentage($bProf, $oProf) : 0;
                                                    $oClass = $bProf < $oProf ? 'green-text' : ($bProf > $oProf ? 'red-text' : '');
                                                @endphp
                                                <td class="text-dark"><strong>{{ $bFmt }}</strong></td>
                                                <td class="text-dark"><strong>{{ $aFmt }}</strong></td>
                                                <td class="text-dark">
                                                    <strong>{{ $oFmt }}</strong>
                                                    <p class="{{ $oClass }}">{{ $pctO != 0 ? '(' . $pctO . '%)' : '' }}</p>
                                                </td>
                                            @endforeach
                                        @else
                                            <td class="text-dark" colspan="3"><strong>{{ __('No profit data available') }}</strong></td>
                                        @endif
                                    </tr>
                                </tfoot>
                            </table>
                        {{-- Yearly Budget--}}
                        @else
                            <table class="{{ $tbCls }}">
                                <thead>
                                    @php
                                        $hasMonths      = (is_array($yearly_monthlist ?? null) && count($yearly_monthlist ?? []) > 0) || (($yearly_monthlist ?? null) instanceof \Illuminate\Support\Collection && ($yearly_monthlist)->isNotEmpty());
                                        $canFormatMoney = ($user ?? null) && method_exists($user, 'priceFormat');
                                        $hasPctHelper   = class_exists(\App\Models\Budget::class) && method_exists(\App\Models\Budget::class, 'percentage');
                                    @endphp
                                    <tr>
                                        <td rowspan="2"></td>
                                        @if($hasMonths)
                                            @foreach($yearly_monthlist as $month)
                                                <th colspan="3" scope="colgroup" class="text-center br-1px">{{ isset($month) && $month !== '' ? $month : __('No month label available') }}</th>
                                            @endforeach
                                        @else
                                            <th colspan="3" scope="colgroup" class="text-center br-1px">{{ __('No months available') }}</th>
                                        @endif
                                    </tr>
                                    <tr>
                                        @if($hasMonths)
                                            @foreach($yearly_monthlist as $month)
                                                <th scope="col" class="br-1px">{{ __('Budget') }}</th>
                                                <th scope="col" class="br-1px">{{ __('Actual') }}</th>
                                                <th scope="col" class="br-1px">{{ __('Over Budget') }}</th>
                                            @endforeach
                                        @else
                                            <th scope="col" class="br-1px">{{ __('Budget') }}</th>
                                            <th scope="col" class="br-1px">{{ __('Actual') }}</th>
                                            <th scope="col" class="br-1px">{{ __('Over Budget') }}</th>
                                        @endif
                                    </tr>
                                </thead>

                                <tr>
                                    <th colspan="37" class="text-dark light_blue"><span>{{ __('Income :') }}</span></th>
                                </tr>

                                @php
                                    $overBudgetTotal = [];
                                    $hasIncome = (is_array($incomeproduct ?? null) && count($incomeproduct ?? []) > 0) || (($incomeproduct ?? null) instanceof \Illuminate\Support\Collection && ($incomeproduct)->isNotEmpty());
                                @endphp

                                @if($hasIncome && $hasMonths)
                                    @foreach ($incomeproduct as $productService)
                                        @php
                                            $prodId   = data_get($productService, 'id');
                                            $prodName = data_get($productService, 'name');
                                            $nameOut  = isset($prodName) && $prodName !== '' ? (string) $prodName : __('No product/service name available');
                                        @endphp
                                        <tr>
                                            <td class="text-dark">{{ $nameOut }}</td>
                                            @foreach($yearly_monthlist as $month)
                                                @php
                                                    $budgetAmount = (float) (data_get($budget ?? [], "income_data.$prodId.$month", 0) ?? 0);
                                                    $actualAmount = (float) (data_get($incomeArr ?? [], "$prodId.$month", 0) ?? 0);
                                                    $overAmount   = $actualAmount - $budgetAmount;
                                                    $overBudgetTotal[$prodId][$month] = $overAmount;

                                                    $budgetFmt = $canFormatMoney ? $user->priceFormat($budgetAmount) : number_format($budgetAmount, 2);
                                                    $actualFmt = $canFormatMoney ? $user->priceFormat($actualAmount) : number_format($actualAmount, 2);
                                                    $overFmt   = $canFormatMoney ? $user->priceFormat($overAmount)   : number_format($overAmount, 2);

                                                    $pctActual = ($budgetAmount != 0 && $hasPctHelper) ? \App\Models\Budget::percentage($budgetAmount, $actualAmount) : 0;
                                                    $pctOver   = ($budgetAmount != 0 && $hasPctHelper) ? \App\Models\Budget::percentage($budgetAmount, $overAmount)  : 0;

                                                    $overClass = $budgetAmount < $overAmount ? 'green-text' : ($budgetAmount > $overAmount ? 'red-text' : '');
                                                @endphp
                                                <td class="income_data {{ $month }}_income">{{ $budgetFmt }}</td>
                                                <td>
                                                    {{ $actualFmt }}
                                                    <p>{{ $pctActual != 0 ? '(' . $pctActual . '%)' : '' }}</p>
                                                </td>
                                                <td>
                                                    {{ $overFmt }}
                                                    <p class="{{ $overClass }}">{{ $pctOver != 0 ? '(' . $pctOver . '%)' : '' }}</p>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="text-center text-dark" colspan="37">{{ __('No income products or months available') }}</td>
                                    </tr>
                                @endif

                                @php
                                    $overBudgetTotalArr = [];
                                    foreach(($overBudgetTotal ?? []) as $overBudget){
                                        foreach(($overBudget ?? []) as $k => $value){
                                            $overBudgetTotalArr[$k] = (isset($overBudgetTotalArr[$k]) ? $overBudgetTotalArr[$k] + (float) $value : (float) $value);
                                        }
                                    }
                                    $hasIncomeTotals = is_array($incomeTotalArr ?? null) && count($incomeTotalArr ?? []) > 0;
                                    $hasBudgetTotal  = is_array($budgetTotal ?? null)     && count($budgetTotal ?? [])     > 0;
                                @endphp

                                <tr class="total text-dark">
                                    <td><span></span><strong>{{ __('Total :') }}</strong></td>
                                    @if($hasMonths && $hasBudgetTotal)
                                        @foreach($yearly_monthlist as $month)
                                            @php
                                                $bTot = (float) ($budgetTotal[$month]        ?? 0);
                                                $aTot = (float) ($incomeTotalArr[$month]      ?? 0);
                                                $oTot = (float) ($overBudgetTotalArr[$month]  ?? 0);

                                                $bFmt = $canFormatMoney ? $user->priceFormat($bTot) : number_format($bTot, 2);
                                                $aFmt = $canFormatMoney ? $user->priceFormat($aTot) : number_format($aTot, 2);
                                                $oFmt = $canFormatMoney ? $user->priceFormat($oTot) : number_format($oTot, 2);

                                                $pctA = ($bTot != 0 && $hasPctHelper) ? \App\Models\Budget::percentage($bTot, $aTot) : 0;
                                                $pctO = ($bTot != 0 && $hasPctHelper) ? \App\Models\Budget::percentage($bTot, $oTot) : 0;

                                                $oClass = $bTot < $oTot ? 'green-text' : ($bTot > $oTot ? 'red-text' : '');
                                            @endphp
                                            <td class="{{ $month }}_total_income"><strong>{{ $bFmt }}</strong></td>
                                            <td class="text-dark">
                                                <strong>{{ $aFmt }}</strong>
                                                <p>{{ $pctA != 0 ? '(' . $pctA . '%)' : '' }}</p>
                                            </td>
                                            <td class="text-dark">
                                                <strong>{{ $oFmt }}</strong>
                                                <p class="{{ $oClass }}">{{ $pctO != 0 ? '(' . $pctO . '%)' : '' }}</p>
                                            </td>
                                        @endforeach
                                    @else
                                        <td class="text-dark" colspan="3"><strong>{{ __('No income totals available') }}</strong></td>
                                    @endif
                                </tr>

                                <tr>
                                    <th colspan="37" class="text-dark light_blue"><span>{{ __('Expense :') }}</span></th>
                                </tr>

                                @php
                                    $overExpenseBudgetTotal = [];
                                    $hasExpense = (is_array($expenseproduct ?? null) && count($expenseproduct ?? []) > 0) || (($expenseproduct ?? null) instanceof \Illuminate\Support\Collection && ($expenseproduct)->isNotEmpty());
                                @endphp

                                @if($hasExpense && $hasMonths)
                                    @foreach ($expenseproduct as $productService)
                                        @php
                                            $prodId   = data_get($productService, 'id');
                                            $prodName = data_get($productService, 'name');
                                            $nameOut  = isset($prodName) && $prodName !== '' ? (string) $prodName : __('No product/service name available');
                                        @endphp
                                        <tr>
                                            <td class="text-dark">{{ $nameOut }}</td>
                                            @foreach($yearly_monthlist as $month)
                                                @php
                                                    $budgetAmount = (float) (data_get($budget ?? [], "expense_data.$prodId.$month", 0) ?? 0);
                                                    $actualAmount = (float) (data_get($expenseArr ?? [], "$prodId.$month", 0) ?? 0);
                                                    $overAmount   = $actualAmount - $budgetAmount;
                                                    $overExpenseBudgetTotal[$prodId][$month] = $overAmount;

                                                    $budgetFmt = $canFormatMoney ? $user->priceFormat($budgetAmount) : number_format($budgetAmount, 2);
                                                    $actualFmt = $canFormatMoney ? $user->priceFormat($actualAmount) : number_format($actualAmount, 2);
                                                    $overFmt   = $canFormatMoney ? $user->priceFormat($overAmount)   : number_format($overAmount, 2);

                                                    $pctActual = ($budgetAmount != 0 && $hasPctHelper) ? \App\Models\Budget::percentage($budgetAmount, $actualAmount) : 0;
                                                    $pctOver   = ($budgetAmount != 0 && $hasPctHelper) ? \App\Models\Budget::percentage($budgetAmount, $overAmount)  : 0;

                                                    $overClass = $budgetAmount < $overAmount ? 'green-text' : ($budgetAmount > $overAmount ? 'red-text' : '');
                                                @endphp
                                                <td class="expense_data {{ $month }}_expense">{{ $budgetFmt }}</td>
                                                <td class="text-dark">
                                                    {{ $actualFmt }}
                                                    <p>{{ $pctActual != 0 ? '(' . $pctActual . '%)' : '' }}</p>
                                                </td>
                                                <td class="text-dark">
                                                    {{ $overFmt }}
                                                    <p class="{{ $overClass }}">{{ $pctOver != 0 ? '(' . $pctOver . '%)' : '' }}</p>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="text-center text-dark" colspan="37">{{ __('No expense products or months available') }}</td>
                                    </tr>
                                @endif

                                @php
                                    $overExpenseBudgetTotalArr = [];
                                    foreach(($overExpenseBudgetTotal ?? []) as $overExpenseBudget){
                                        foreach(($overExpenseBudget ?? []) as $k => $value){
                                            $overExpenseBudgetTotalArr[$k] = (isset($overExpenseBudgetTotalArr[$k]) ? $overExpenseBudgetTotalArr[$k] + (float) $value : (float) $value);
                                        }
                                    }
                                    $hasBudgetExpenseTotal = is_array($budgetExpenseTotal ?? null) && count($budgetExpenseTotal ?? []) > 0;
                                    $hasExpenseTotals      = is_array($expenseTotalArr ?? null)      && count($expenseTotalArr ?? [])      > 0;
                                @endphp

                                <tr class="total">
                                    <td class="text-dark"><span></span><strong>{{ __('Total :') }}</strong></td>
                                    @if($hasMonths && $hasBudgetExpenseTotal)
                                        @foreach($yearly_monthlist as $month)
                                            @php
                                                $bTot = (float) ($budgetExpenseTotal[$month]         ?? 0);
                                                $aTot = (float) ($expenseTotalArr[$month]            ?? 0);
                                                $oTot = (float) ($overExpenseBudgetTotalArr[$month]  ?? 0);

                                                $bFmt = $canFormatMoney ? $user->priceFormat($bTot) : number_format($bTot, 2);
                                                $aFmt = $canFormatMoney ? $user->priceFormat($aTot) : number_format($aTot, 2);
                                                $oFmt = $canFormatMoney ? $user->priceFormat($oTot) : number_format($oTot, 2);

                                                $pctA = ($bTot != 0 && $hasPctHelper) ? \App\Models\Budget::percentage($bTot, $aTot) : 0;
                                                $pctO = ($bTot != 0 && $hasPctHelper) ? \App\Models\Budget::percentage($bTot, $oTot) : 0;

                                                $oClass = $bTot < $oTot ? 'green-text' : ($bTot > $oTot ? 'red-text' : '');
                                            @endphp
                                            <td class="text-dark {{ $month }}_total_expense"><strong>{{ $bFmt }}</strong></td>
                                            <td class="text-dark">
                                                <strong>{{ $aFmt }}</strong>
                                                <p>{{ $pctA != 0 ? '(' . $pctA . '%)' : '' }}</p>
                                            </td>
                                            <td class="text-dark">
                                                <strong>{{ $oFmt }}</strong>
                                                <p class="{{ $oClass }}">{{ $pctO != 0 ? '(' . $pctO . '%)' : '' }}</p>
                                            </td>
                                        @endforeach
                                    @else
                                        <td class="text-dark" colspan="3"><strong>{{ __('No expense totals available') }}</strong></td>
                                    @endif
                                </tr>

                                <td></td>

                                <tfoot>
                                    <tr class="total">
                                        @php
                                            $overbudgetprofit = [];
                                            $keys = array_keys(($overBudgetTotalArr ?? []) + ($overExpenseBudgetTotalArr ?? []));
                                            foreach($keys as $v){
                                                $overbudgetprofit[$v] = (float) (($overBudgetTotalArr[$v] ?? 0)) - (float) (($overExpenseBudgetTotalArr[$v] ?? 0));
                                            }
                                            $hasBudgetProfit = is_array($budgetprofit ?? null) && count($budgetprofit ?? []) > 0;
                                            $hasActualProfit = is_array($actualprofit ?? null) && count($actualprofit ?? []) > 0;
                                        @endphp
                                        <td class="text-dark"><span></span><strong>{{ __('NET PROFIT :') }}</strong></td>
                                        @if($hasMonths && $hasBudgetProfit && $hasActualProfit)
                                            @foreach($yearly_monthlist as $month)
                                                @php
                                                    $bProf = (float) ($budgetprofit[$month]     ?? 0);
                                                    $aProf = (float) ($actualprofit[$month]     ?? 0);
                                                    $oProf = (float) ($overbudgetprofit[$month] ?? 0);

                                                    $bFmt  = $canFormatMoney ? $user->priceFormat($bProf) : number_format($bProf, 2);
                                                    $aFmt  = $canFormatMoney ? $user->priceFormat($aProf) : number_format($aProf, 2);
                                                    $oFmt  = $canFormatMoney ? $user->priceFormat($oProf) : number_format($oProf, 2);

                                                    $pctO  = ($bProf != 0 && $hasPctHelper) ? \App\Models\Budget::percentage($bProf, $oProf) : 0;
                                                    $oClass = $bProf < $oProf ? 'green-text' : ($bProf > $oProf ? 'red-text' : '');
                                                @endphp
                                                <td class="text-dark"><strong>{{ $bFmt }}</strong></td>
                                                <td class="text-dark"><strong>{{ $aFmt }}</strong></td>
                                                <td class="text-dark">
                                                    <strong>{{ $oFmt }}</strong>
                                                    <p class="{{ $oClass }}">{{ $pctO != 0 ? '(' . $pctO . '%)' : '' }}</p>
                                                </td>
                                            @endforeach
                                        @else
                                            <td class="text-dark" colspan="3"><strong>{{ __('No profit data available') }}</strong></td>
                                        @endif
                                    </tr>
                                </tfoot>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endsection
@else
    <div class="alert alert-warning">{{__('No budget data available')}}</div>
@endif