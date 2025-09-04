@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Edit Budget Planner')}}
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
        <script defer src="{{ asset('assets/js/routes/budgets/plannerIndex.js') }}"></script>
    @endpush
    <li class="breadcrumb-item">{{__('Budget Edit')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script async src="{{ asset('assets/js/routes/budgets/lang/editToggle.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/budgets/editToggle.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_CTT)
    <div class="card bg-none card-box mt-3">
        <div class="card-body">
        @php
            $budgetUpdateRoute       = Route::has(ViewsConstants::BDG . '.update')
                ? route(ViewsConstants::BDG . '.update', $budget->id)
                : '#';
            $budgetUpdateFormId      = 'budget-update-form-' . $budget->id;
            $budgetUpdateGuardMsg    = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::BDG,
                'budget_update_route_unavailable'
            ) ?? 'Budget update route is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        {!! Form::model($budget, [
            'route'            => [$budgetUpdateRoute],
            'method'           => 'PUT',
            'id'               => $budgetUpdateFormId,
            'data-url'         => $budgetUpdateRoute,
            'data-guard-msg'   => $budgetUpdateGuardMsg,
            'class'            => 'w-100',
        ]) !!}
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    const form = document.getElementById('{{ $budgetUpdateFormId }}');
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
            <div class="row">
                <input type="hidden" name="type" id="type" value="{{ csrf_token() }}">
                <div class="form-group col-md-4">
                    {{ Form::label('name', __('Name'),['class'=>'form-label']) }}
                    {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
                </div>
                <div class="form-group col-md-4">
                    {{ Form::label('period', __('Budget Period'),['class'=>'form-label']) }}
                    {{ Form::select('period', $periods,null, array('class' => 'form-control select period','required'=>'required')) }}
                </div>
                <div class="form-group  col-md-4">
                    <div class="btn-box">
                        {{ Form::label('year', __('Year'),['class'=>'form-label']) }}
                        {{ Form::select('year',$yearList,isset($_GET['year'])?$_GET['year']:'', array('class' => 'form-control select')) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body table-border-style">
                @php
                    $monthsMonthly = (is_array($monthList ?? null) && count($monthList ?? [])) || (($monthList ?? null) instanceof Collection && ($monthList)->isNotEmpty()) ? $monthList : [];
                    $monthsQuarterly = (is_array($quarterly_monthlist ?? null) && count($quarterly_monthlist ?? [])) || (($quarterly_monthlist ?? null) instanceof Collection && ($quarterly_monthlist)->isNotEmpty()) ? $quarterly_monthlist : [];
                    $monthsHalfYearly = (is_array($half_yearly_monthlist ?? null) && count($half_yearly_monthlist ?? [])) || (($half_yearly_monthlist ?? null) instanceof Collection && ($half_yearly_monthlist)->isNotEmpty()) ? $half_yearly_monthlist : [];
                    $monthsYearly = (is_array($yearly_monthlist ?? null) && count($yearly_monthlist ?? [])) || (($yearly_monthlist ?? null) instanceof Collection && ($yearly_monthlist)->isNotEmpty()) ? $yearly_monthlist : [];
                    $incomeList = (is_array($incomeproduct ?? null) && count($incomeproduct ?? [])) || (($incomeproduct ?? null) instanceof Collection && ($incomeproduct)->isNotEmpty()) ? $incomeproduct : [];
                    $expenseList = (is_array($expenseproduct ?? null) && count($expenseproduct ?? [])) || (($expenseproduct ?? null) instanceof Collection && ($expenseproduct)->isNotEmpty()) ? $expenseproduct : [];
                    $hasPriceFormat = ($user ?? null) && method_exists($user, 'priceFormat');
                @endphp
                <div class="table-responsive budget_plan d-block" id="monthly">
                    <table class="table mb-0" id="dataTable-manual">
                        <thead>
                            <tr>
                                <th>{{ __('Category') }}</th>
                                @if(!empty($monthsMonthly))
                                    @foreach($monthsMonthly as $m)<td class="total text-dark">{{ $m }}</td>@endforeach
                                @else
                                    <td class="total text-dark">{{ __('No months available') }}</td>
                                @endif
                                <th>{{ __('Total :') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><th colspan="14" class="text-dark light_blue"><span>{{ __('Income :') }}</span></th></tr>
                            @if(!empty($incomeList))
                                @foreach($incomeList as $productService)
                                    @php
                                        $psId = is_numeric($productService->id ?? null) ? (int)$productService->id : null;
                                    @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsMonthly) && !empty($psId))
                                            @foreach($monthsMonthly as $m)
                                                <td>
                                                    <input type="number" class="form-control pl-1 pr-1 income_data {{ $m }}_income" data-month="{{ $m }}" name="income[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'income_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'income_data.'.$psId.'.'.$m) : 0 }}" id="income_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find months or product id') }}</td>
                                        @endif
                                        <td class="totalIncome text-dark">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="text-center text-dark">{{ __('No income products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="text-dark">{{ __('Total :') }}</td>
                                @if(!empty($monthsMonthly))
                                    @foreach($monthsMonthly as $m)<td><span class="{{ $m }}_total_income text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No months available') }}</td>
                                @endif
                                <td><span class="income text-dark">0.00</span></td>
                            </tr>
                            <tr><th colspan="14" class="text-dark light_blue"><span>{{ __('Expense :') }}</span></th></tr>
                            @if(!empty($expenseList))
                                @foreach($expenseList as $productService)
                                    @php
                                        $psId = is_numeric($productService->id ?? null) ? (int)$productService->id : null;
                                    @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsMonthly) && !empty($psId))
                                            @foreach($monthsMonthly as $m)
                                                <td>
                                                    <input type="number" class="form-control pl-1 pr-1 expense_data {{ $m }}_expense" data-month="{{ $m }}" name="expense[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m) : 0 }}" id="expense_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find months or product id') }}</td>
                                        @endif
                                        <td class="totalExpense text-dark">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="text-center text-dark">{{ __('No expense products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="text-dark">{{ __('Total :') }}</td>
                                @if(!empty($monthsMonthly))
                                    @foreach($monthsMonthly as $m)<td><span class="{{ $m }}_total_expense text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No months available') }}</td>
                                @endif
                                <td><span class="expense text-dark">0.00</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        @php
                            $budgetCancelBtnId = 'budget-planner-cancel-btn-monthly';
                        @endphp
                        <input type="button" id="{{ $budgetCancelBtnId }}" value="{{ __('Cancel') }}" class="btn btn-light" data-url="{{ $budgetIndexRoute ?? '#' }}" data-guard-msg="{{ $budgetIndexGuardMsg ?? __('Failed to get budget index route') }}">
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/budgets/cancelMonthly.js') }}"></script>
                        @endpush
                        <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
                    </div>
                </div>
                <div class="table-responsive budget_plan d-none" id="quarterly">
                    <table class="table mb-0" id="dataTable-manual">
                        <thead>
                            <tr>
                                <th>{{ __('Category') }}</th>
                                @if(!empty($monthsQuarterly))
                                    @foreach($monthsQuarterly as $m)<td class="total text-dark">{{ $m }}</td>@endforeach
                                @else
                                    <td class="total text-dark">{{ __('No periods available') }}</td>
                                @endif
                                <th>{{ __('Total :') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><th colspan="14" class="text-dark light_blue"><span>{{ __('Income :') }}</span></th></tr>
                            @if(!empty($incomeList))
                                @foreach($incomeList as $productService)
                                    @php
                                        $psId = is_numeric($productService->id ?? null) ? (int)$productService->id : null;
                                    @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsQuarterly) && !empty($psId))
                                            @foreach($monthsQuarterly as $m)
                                                <td>
                                                    <input type="number" class="form-control income_data {{ $m }}_income" data-month="{{ $m }}" name="income[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'income_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'income_data.'.$psId.'.'.$m) : 0 }}" id="income_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find periods or product id') }}</td>
                                        @endif
                                        <td class="text-end totalIncome text-dark">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="text-center text-dark">{{ __('No income products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="text-dark">{{ __('Total :') }}</td>
                                @if(!empty($monthsQuarterly))
                                    @foreach($monthsQuarterly as $m)<td><span class="{{ $m }}_total_income text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="text-end"><span class="income text-dark">0.00</span></td>
                            </tr>
                            <tr><th colspan="14" class="text-dark light_blue"><span>{{ __('Expense :') }}</span></th></tr>
                            @if(!empty($expenseList))
                                @foreach($expenseList as $productService)
                                    @php
                                        $psId = is_numeric($productService->id ?? null) ? (int)$productService->id : null;
                                    @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsQuarterly) && !empty($psId))
                                            @foreach($monthsQuarterly as $m)
                                                <td>
                                                    <input type="number" class="form-control expense_data {{ $m }}_expense" data-month="{{ $m }}" name="expense[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m) : 0 }}" id="expense_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find periods or product id') }}</td>
                                        @endif
                                        <td class="text-end totalExpense text-dark">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="text-center text-dark">{{ __('No expense products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="text-dark">{{ __('Total :') }}</td>
                                @if(!empty($monthsQuarterly))
                                    @foreach($monthsQuarterly as $m)<td><span class="{{ $m }}_total_expense text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="text-end"><span class="expense text-dark">0.00</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        @php
                            $cancelBtnId = 'budget-planner-cancel-btn-quarterly';
                        @endphp
                        <input type="button" id="{{ $cancelBtnId }}" value="{{ __('Cancel') }}" class="btn btn-light" data-url="{{ $budgetIndexRoute ?? '#' }}" data-guard-msg="{{ $budgetIndexGuardMsg ?? __('Failed to get budget index route') }}">
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/budgets/cancelQuarterly.js') }}"></script>
                        @endpush
                        <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
                    </div>
                </div>
                <div class="table-responsive budget_plan d-none" id="half-yearly">
                    <table class="table mb-0" id="dataTable-manual">
                        <thead>
                            <tr>
                                <th>{{ __('Category') }}</th>
                                @if(!empty($monthsHalfYearly))
                                    @foreach($monthsHalfYearly as $m)<td class="total text-dark">{{ $m }}</td>@endforeach
                                @else
                                    <td class="total text-dark">{{ __('No periods available') }}</td>
                                @endif
                                <th>{{ __('Total :') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><th colspan="14" class="text-dark light_blue"><span>{{ __('Income :') }}</span></th></tr>
                            @if(!empty($incomeList))
                                @foreach($incomeList as $productService)
                                    @php
                                        $psId = is_numeric($productService->id ?? null) ? (int)$productService->id : null;
                                    @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsHalfYearly) && !empty($psId))
                                            @foreach($monthsHalfYearly as $m)
                                                <td>
                                                    <input type="number" class="form-control income_data {{ $m }}_income" data-month="{{ $m }}" name="income[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'income_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'income_data.'.$psId.'.'.$m) : 0 }}" id="income_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find periods or product id') }}</td>
                                        @endif
                                        <td class="text-end totalIncome text-dark">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="text-center text-dark">{{ __('No income products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="text-dark">{{ __('Total :') }}</td>
                                @if(!empty($monthsHalfYearly))
                                    @foreach($monthsHalfYearly as $m)<td><span class="{{ $m }}_total_income text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="text-end"><span class="income text-dark">0.00</span></td>
                            </tr>
                            <tr><th colspan="14" class="text-dark light_blue"><span>{{ __('Expense :') }}</span></th></tr>
                            @if(!empty($expenseList))
                                @foreach($expenseList as $productService)
                                    @php
                                        $psId = is_numeric($productService->id ?? null) ? (int)$productService->id : null;
                                    @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsHalfYearly) && !empty($psId))
                                            @foreach($monthsHalfYearly as $m)
                                                <td>
                                                    <input type="number" class="form-control expense_data {{ $m }}_expense" data-month="{{ $m }}" name="expense[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m) : 0 }}" id="expense_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find periods or product id') }}</td>
                                        @endif
                                        <td class="text-end totalExpense text-dark">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="text-center text-dark">{{ __('No expense products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="text-dark">{{ __('Total :') }}</td>
                                @if(!empty($monthsHalfYearly))
                                    @foreach($monthsHalfYearly as $m)<td><span class="{{ $m }}_total_expense text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="text-end"><span class="expense text-dark">0.00</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        @php
                            $cancelBtnId = 'budget-planner-cancel-btn-half-yearly';
                        @endphp
                        <input type="button" id="{{ $cancelBtnId }}" value="{{ __('Cancel') }}" class="btn btn-light" data-url="{{ $budgetIndexRoute ?? '#' }}" data-guard-msg="{{ $budgetIndexGuardMsg ?? __('Failed to get budget index route') }}">
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/budgets/cancelHalfYearly.js') }}"></script>
                        @endpush
                        <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
                    </div>
                </div>
                <div class="table-responsive budget_plan d-none" id="yearly">
                    <table class="table mb-0" id="dataTable-manual">
                        <thead>
                            <tr>
                                <th>{{ __('Category') }}</th>
                                @if(!empty($monthsYearly))
                                    @foreach($monthsYearly as $m)<td class="total text-dark">{{ $m }}</td>@endforeach
                                @else
                                    <td class="total text-dark">{{ __('No periods available') }}</td>
                                @endif
                                <th>{{ __('Total :') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><th colspan="14" class="text-dark light_blue"><span>{{ __('Income :') }}</span></th></tr>
                            @if(!empty($incomeList))
                                @foreach($incomeList as $productService)
                                    @php
                                        $psId = is_numeric($productService->id ?? null) ? (int)$productService->id : null;
                                    @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsYearly) && !empty($psId))
                                            @foreach($monthsYearly as $m)
                                                <td>
                                                    <input type="number" class="form-control income_data {{ $m }}_income" data-month="{{ $m }}" name="income[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'income_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'income_data.'.$psId.'.'.$m) : 0 }}" id="income_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find periods or product id') }}</td>
                                        @endif
                                        <td class="text-end totalIncome text-dark">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="text-center text-dark">{{ __('No income products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="text-dark">{{ __('Total :') }}</td>
                                @if(!empty($monthsYearly))
                                    @foreach($monthsYearly as $m)<td><span class="{{ $m }}_total_income text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="text-end"><span class="income text-dark">0.00</span></td>
                            </tr>
                            <tr><th colspan="14" class="text-dark light_blue"><span>{{ __('Expense :') }}</span></th></tr>
                            @if(!empty($expenseList))
                                @foreach($expenseList as $productService)
                                    @php
                                        $psId = is_numeric($productService->id ?? null) ? (int)$productService->id : null;
                                    @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsYearly) && !empty($psId))
                                            @foreach($monthsYearly as $m)
                                                <td>
                                                    <input type="number" class="form-control expense_data {{ $m }}_expense" data-month="{{ $m }}" name="expense[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m) : 0 }}" id="expense_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find periods or product id') }}</td>
                                        @endif
                                        <td class="text-end totalExpense text-dark">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="text-center text-dark">{{ __('No expense products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="text-dark">{{ __('Total :') }}</td>
                                @if(!empty($monthsYearly))
                                    @foreach($monthsYearly as $m)<td><span class="{{ $m }}_total_expense text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="text-end"><span class="expense text-dark">0.00</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        @php
                            $cancelBtnId = 'budget-planner-cancel-btn-yearly';
                        @endphp
                        <input type="button" id="{{ $cancelBtnId }}" value="{{ __('Cancel') }}" class="btn btn-light" data-url="{{ $budgetIndexRoute ?? '#' }}" data-guard-msg="{{ $budgetIndexGuardMsg ?? __('Failed to get budget index route') }}">
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/budgets/cancelYearly.js') }}"></script>
                        @endpush
                        <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
                    </div>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
@endsection




