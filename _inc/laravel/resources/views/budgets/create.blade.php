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
        <script defer src="{{ asset('assets/js/routes/budgets/plannerIndex.js') }}"></script>
    @endpush
    <li class="breadcrumb-item">{{__('Budget Create')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script async src="{{ asset('assets/js/routes/budgets/lang/toggle.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/budgets/toggleCreate.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_CTT)
    <div class="card bg-none card-box mt-3">
        <div class="card-body">
            @php
                $periodOptions = (is_array($periods ?? null) && count($periods ?? [])) || (($periods ?? null) instanceof Collection && ($periods)->isNotEmpty()) ? $periods : [];
                $yearOptions = (is_array($yearList ?? null) && count($yearList ?? [])) || (($yearList ?? null) instanceof Collection && ($yearList)->isNotEmpty()) ? $yearList : [];
                $monthsMonthly = (is_array($monthList ?? null) && count($monthList ?? [])) || (($monthList ?? null) instanceof Collection && ($monthList)->isNotEmpty()) ? $monthList : [];
                $monthsQuarterly = (is_array($quarterly_monthlist ?? null) && count($quarterly_monthlist ?? [])) || (($quarterly_monthlist ?? null) instanceof Collection && ($quarterly_monthlist)->isNotEmpty()) ? $quarterly_monthlist : [];
                $monthsHalfYearly = (is_array($half_yearly_monthlist ?? null) && count($half_yearly_monthlist ?? [])) || (($half_yearly_monthlist ?? null) instanceof Collection && ($half_yearly_monthlist)->isNotEmpty()) ? $half_yearly_monthlist : [];
                $monthsYearly = (is_array($yearly_monthlist ?? null) && count($yearly_monthlist ?? [])) || (($yearly_monthlist ?? null) instanceof Collection && ($yearly_monthlist)->isNotEmpty()) ? $yearly_monthlist : [];
                $incomeList = (is_array($incomeproduct ?? null) && count($incomeproduct ?? [])) || (($incomeproduct ?? null) instanceof Collection && ($incomeproduct)->isNotEmpty()) ? $incomeproduct : [];
                $expenseList = (is_array($expenseproduct ?? null) && count($expenseproduct ?? [])) || (($expenseproduct ?? null) instanceof Collection && ($expenseproduct)->isNotEmpty()) ? $expenseproduct : [];
                $guardMsg = Utility::fetchLinkMessage($lang ?? null, ViewsConstants::BDG, 'budget_planner_store_route_unavailable') ?? __('Failed to get budget planner route');
            @endphp
            {!! Form::open(['url' => route(ViewsConstants::BDG), 'id' => 'budget-planner-form', 'data-url' => route(ViewsConstants::BDG), 'data-guard-msg' => $guardMsg, 'class' => 'w-100']) !!}
            <div class="row">
                <div class="form-group col-md-4">
                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                    {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-4">
                    {{ Form::label('period', __('Budget Period'), ['class' => 'form-label']) }}
                    {{ Form::select('period', $periodOptions, null, ['class' => 'form-control select period', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-4">
                    <div class="btn-box">
                        {{ Form::label('year', __('Year'), ['class' => 'form-label']) }}
                        {{ Form::select('year', $yearOptions, request('year', ''), ['class' => 'form-control select']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body table-border-style">
                {{-- Start Monthly Budget --}}
                <div class="table-responsive budget_plan d-block" id="monthly">
                    <table class="table mb-0" id="dataTable-manual">
                        <thead>
                            <tr>
                                <th>{{ __('Category') }}</th>
                                @if(!empty($monthsMonthly))
                                    @foreach($monthsMonthly as $month)<td class="total text-dark">{{ $month }}</td>@endforeach
                                @else
                                    <td class="total text-dark">{{ __('No months available') }}</td>
                                @endif
                                <th>{{ __('Total :') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><th colspan="14" class="text-dark light_blue"><span>{{ __('Income :') }}</span></th></tr>
                            @if(!empty($incomeList))
                                @foreach ($incomeList as $productService)
                                    @php $psId = is_numeric($productService->id ?? null) ? (int) $productService->id : null; @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsMonthly) && !empty($psId))
                                            @foreach($monthsMonthly as $month)
                                                <td>
                                                    <input type="number" class="form-control pl-1 pr-1 income_data {{ $month }}_income" data-month="{{ $month }}" name="income[{{ $psId }}][{{ $month }}]" value="{{ is_numeric(data_get($budget ?? [], 'income_data.'.$psId.'.'.$month)) ? (float) data_get($budget ?? [], 'income_data.'.$psId.'.'.$month) : 0 }}" id="income_data_{{ $month }}">
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
                                    @foreach($monthsMonthly as $month)<td><span class="{{ $month }}_total_income text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No months available') }}</td>
                                @endif
                                <td><span class="income text-dark">0.00</span></td>
                            </tr>
                            <tr><th colspan="14" class="text-dark light_blue"><span>{{ __('Expense :') }}</span></th></tr>
                            @if(!empty($expenseList))
                                @foreach ($expenseList as $productService)
                                    @php $psId = is_numeric($productService->id ?? null) ? (int) $productService->id : null; @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsMonthly) && !empty($psId))
                                            @foreach($monthsMonthly as $month)
                                                <td>
                                                    <input type="number" class="form-control pl-1 pr-1 expense_data {{ $month }}_expense" data-month="{{ $month }}" name="expense[{{ $psId }}][{{ $month }}]" value="{{ is_numeric(data_get($budget ?? [], 'expense_data.'.$psId.'.'.$month)) ? (float) data_get($budget ?? [], 'expense_data.'.$psId.'.'.$month) : 0 }}" id="expense_data_{{ $month }}">
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
                                    @foreach($monthsMonthly as $month)<td><span class="{{ $month }}_total_expense text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No months available') }}</td>
                                @endif
                                <td><span class="expense text-dark">0.00</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        <input type="button" id="expense-cancel-btn-monthly" value="{{ __('Cancel') }}" class="btn btn-light" data-url="{{ route(ViewsConstants::BDG . '.index') }}" data-guard-msg="{{ Utility::fetchLinkMessage($lang ?? null, 'expenses', 'budget_planner_index_route_unavailable') ?? __('Failed to get cancel route') }}">
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/budgets/expenseCancelMonthly.js') }}"></script>
                        @endpush
                        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
                    </div>
                </div>
                {{-- End Monthly Budget --}}
                {{-- Start Quarterly Budget --}}
                <div class="table-responsive budget_plan d-none" id="quarterly">
                    <table class="table mb-0" id="dataTable-manual">
                        <thead>
                            <tr>
                                <th>{{ __('Category') }}</th>
                                @if(!empty($monthsQuarterly))
                                    @foreach($monthsQuarterly as $month)<td class="total text-dark">{{ $month }}</td>@endforeach
                                @else
                                    <td class="total text-dark">{{ __('No periods available') }}</td>
                                @endif
                                <th>{{ __('Total :') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><th colspan="37" class="text-dark light_blue"><span>{{ __('Income :') }}</span></th></tr>
                            @if(!empty($incomeList))
                                @foreach ($incomeList as $productService)
                                    @php $psId = is_numeric($productService->id ?? null) ? (int) $productService->id : null; @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsQuarterly) && !empty($psId))
                                            @foreach($monthsQuarterly as $month)
                                                <td>
                                                    <input type="number" class="form-control income_data {{ $month }}_income" data-month="{{ $month }}" name="income[{{ $psId }}][{{ $month }}]" value="{{ is_numeric(data_get($budget ?? [], 'income_data.'.$psId.'.'.$month)) ? (float) data_get($budget ?? [], 'income_data.'.$psId.'.'.$month) : 0 }}" id="income_data_{{ $month }}">
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
                                    @foreach($monthsQuarterly as $month)<td><span class="{{ $month }}_total_income text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="text-end"><span class="income text-dark">0.00</span></td>
                            </tr>
                            <tr><th colspan="14" class="text-dark light_blue"><span>{{ __('Expense :') }}</span></th></tr>
                            @if(!empty($expenseList))
                                @foreach ($expenseList as $productService)
                                    @php $psId = is_numeric($productService->id ?? null) ? (int) $productService->id : null; @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsQuarterly) && !empty($psId))
                                            @foreach($monthsQuarterly as $month)
                                                <td>
                                                    <input type="number" class="form-control expense_data {{ $month }}_expense" data-month="{{ $month }}" name="expense[{{ $psId }}][{{ $month }}]" value="{{ is_numeric(data_get($budget ?? [], 'expense_data.'.$psId.'.'.$month)) ? (float) data_get($budget ?? [], 'expense_data.'.$psId.'.'.$month) : 0 }}" id="expense_data_{{ $month }}">
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
                                    @foreach($monthsQuarterly as $month)<td><span class="{{ $month }}_total_expense text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="text-end"><span class="expense text-dark">0.00</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        <input type="button" id="budget-cancel-btn-quarterly" value="{{ __('Cancel') }}" class="btn btn-light" data-url="{{ route(ViewsConstants::BDG . '.index') }}" data-guard-msg="{{ Utility::fetchLinkMessage($lang ?? null, ViewsConstants::BDG, 'budget_planner_index_route_unavailable') ?? __('Failed to get cancel route') }}">
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/budgets/expenseCancelQuarterly.js') }}"></script>
                        @endpush
                        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
                    </div>
                </div>
                {{-- End Quarterly Budget --}}
                {{-- Start Half-Yearly Budget --}}
                <div class="table-responsive budget_plan d-none" id="half-yearly">
                    <table class="table mb-0" id="dataTable-manual">
                        <thead>
                            <tr>
                                <th>{{ __('Category') }}</th>
                                @if(!empty($monthsHalfYearly))
                                    @foreach($monthsHalfYearly as $month)<td class="total text-dark">{{ $month }}</td>@endforeach
                                @else
                                    <td class="total text-dark">{{ __('No periods available') }}</td>
                                @endif
                                <th>{{ __('Total :') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><th colspan="14" class="text-dark light_blue"><span>{{ __('Income :') }}</span></th></tr>
                            @if(!empty($incomeList))
                                @foreach ($incomeList as $productService)
                                    @php $psId = is_numeric($productService->id ?? null) ? (int) $productService->id : null; @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsHalfYearly) && !empty($psId))
                                            @foreach($monthsHalfYearly as $month)
                                                <td>
                                                    <input type="number" class="form-control income_data {{ $month }}_income" data-month="{{ $month }}" name="income[{{ $psId }}][{{ $month }}]" value="{{ is_numeric(data_get($budget ?? [], 'income_data.'.$psId.'.'.$month)) ? (float) data_get($budget ?? [], 'income_data.'.$psId.'.'.$month) : 0 }}" id="income_data_{{ $month }}">
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
                                    @foreach($monthsHalfYearly as $month)<td><span class="{{ $month }}_total_income text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="text-end"><span class="income text-dark">0.00</span></td>
                            </tr>
                            <tr><th colspan="14" class="text-dark light_blue"><span>{{ __('Expense :') }}</span></th></tr>
                            @if(!empty($expenseList))
                                @foreach ($expenseList as $productService)
                                    @php $psId = is_numeric($productService->id ?? null) ? (int) $productService->id : null; @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsHalfYearly) && !empty($psId))
                                            @foreach($monthsHalfYearly as $month)
                                                <td>
                                                    <input type="number" class="form-control expense_data {{ $month }}_expense" data-month="{{ $month }}" name="expense[{{ $psId }}][{{ $month }}]" value="{{ is_numeric(data_get($budget ?? [], 'expense_data.'.$psId.'.'.$month)) ? (float) data_get($budget ?? [], 'expense_data.'.$psId.'.'.$month) : 0 }}" id="expense_data_{{ $month }}">
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
                                    @foreach($monthsHalfYearly as $month)<td><span class="{{ $month }}_total_expense text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="text-end"><span class="expense text-dark">0.00</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        <input type="button" id="expense-cancel-btn-half-yearly" value="{{ __('Cancel') }}" class="btn btn-light" data-url="{{ route(ViewsConstants::EXP . '.index') }}" data-guard-msg="{{ Utility::fetchLinkMessage($lang ?? null, ViewsConstants::BDG, 'expense_cancel_route_unavailable') ?? __('Failed to get expense section route') }}">
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/budgets/expenseCancelHalfYearly.js') }}"></script>
                        @endpush
                        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
                    </div>
                </div>
                {{-- End Half-Yearly Budget --}}
                {{-- Start Yearly Budget --}}
                <div class="table-responsive budget_plan d-none" id="yearly">
                    <table class="table mb-0" id="dataTable-manual">
                        <thead>
                            <tr>
                                <th>{{ __('Category') }}</th>
                                @if(!empty($monthsYearly))
                                    @foreach($monthsYearly as $month)<td class="total text-dark">{{ $month }}</td>@endforeach
                                @else
                                    <td class="total text-dark">{{ __('No periods available') }}</td>
                                @endif
                                <th>{{ __('Total :') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><th colspan="14" class="text-dark light_blue"><span>{{ __('Income :') }}</span></th></tr>
                            @if(!empty($incomeList))
                                @foreach ($incomeList as $productService)
                                    @php $psId = is_numeric($productService->id ?? null) ? (int) $productService->id : null; @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsYearly) && !empty($psId))
                                            @foreach($monthsYearly as $month)
                                                <td>
                                                    <input type="number" class="form-control income_data {{ $month }}_income" data-month="{{ $month }}" name="income[{{ $psId }}][{{ $month }}]" value="{{ is_numeric(data_get($budget ?? [], 'income_data.'.$psId.'.'.$month)) ? (float) data_get($budget ?? [], 'income_data.'.$psId.'.'.$month) : 0 }}" id="income_data_{{ $month }}">
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
                                    @foreach($monthsYearly as $month)<td><span class="{{ $month }}_total_income text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="text-end"><span class="income text-dark">0.00</span></td>
                            </tr>
                            <tr><th colspan="14" class="text-dark light_blue"><span>{{ __('Expense :') }}</span></th></tr>
                            @if(!empty($expenseList))
                                @foreach ($expenseList as $productService)
                                    @php $psId = is_numeric($productService->id ?? null) ? (int) $productService->id : null; @endphp
                                    <tr>
                                        <td>{{ !empty($productService->name) ? $productService->name : __('No product/service name available') }}</td>
                                        @if(!empty($monthsYearly) && !empty($psId))
                                            @foreach($monthsYearly as $month)
                                                <td>
                                                    <input type="number" class="form-control expense_data {{ $month }}_expense" data-month="{{ $month }}" name="expense[{{ $psId }}][{{ $month }}]" value="{{ is_numeric(data_get($budget ?? [], 'expense_data.'.$psId.'.'.$month)) ? (float) data_get($budget ?? [], 'expense_data.'.$psId.'.'.$month) : 0 }}" id="expense_data_{{ $month }}">
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
                                    @foreach($monthsYearly as $month)<td><span class="{{ $month }}_total_expense text-dark">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="text-end"><span class="expense text-dark">0.00</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        <input type="button" id="expense-cancel-btn-yearly" value="{{ __('Cancel') }}" class="btn btn-light" data-url="{{ route(ViewsConstants::EXP . '.index') }}" data-guard-msg="{{ Utility::fetchLinkMessage($lang ?? null, ViewsConstants::BDG, 'expense_cancel_route_unavailable') ?? __('Failed to get expense section route') }}">
                        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
                    </div>
                </div>
                {{-- End Yearly Budget --}}
            </div>
            {{ Form::close() }}
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/routes/budgets/plannerForm.js') }}"></script>
            @endpush
        </div>
    </div>
@endsection

