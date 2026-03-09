@php
$lang ??= 'en';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
	} catch (\Error $e) {
		Log::error('Error in budgets/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in budgets/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in budgets/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Edit Budget Planner')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
$budgetIndexRoute ??= '#';
		$budgetIndexLinkId ??= 'budget-planner-index-link';
		$budgetIndexGuardMsg ??= '';
		try {
			$budgetIndexRoute = Route::has(ViewsConstants::BDG . '.index')
				? (route(ViewsConstants::BDG . '.index') ?? '#')
				: '#';
			$budgetIndexGuardMsg = Utility::fetchLinkMessage(
				$lang,
				ViewsConstants::BDG,
				'budget_planner_index_route_unavailable'
			) ?? 'Budget Planner index route is unavailable. Please contact technical support or your domain administrator.';
		} catch (\Error $e) {
			BcLog::error('Error in budgets/edit.blade.php breadcrumb @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\Exception $e) {
			BcLog::error('Exception in budgets/edit.blade.php breadcrumb @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\Throwable $e) {
			BcLog::error('Throwable in budgets/edit.blade.php breadcrumb @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		}
@endphp
    <li class="{{ VC::BCI }}">
        <a
            id="{{ $budgetIndexLinkId }}"
            href="{{ $budgetIndexRoute }}"
            data-url="{{ $budgetIndexRoute }}"
            data-guard-msg="{{ base64_encode($budgetIndexGuardMsg) }}"
        >
            {{ __('Budget Planner') }}
        </a>
    </li>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/budgets/plannerIndex.js') }}"></script>
    @endpush
    <li class="{{ VC::BCI }}">{{__('Budget Edit')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script async src="{{ asset('assets/js/routes/budgets/lang/editToggle.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/budgets/editToggle.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::CD_BGN_BX_MT3 }}">
        <div class="{{ VC::CD_BD }}">
        @php
$budgetUpdateRoute ??= '#';
			$budgetUpdateFormId ??= 'budget-update-form-unknown';
			$budgetUpdateGuardMsg ??= '';
			$budgetId ??= null;
			try {
				$budgetId = data_get($budget ?? null, 'id');
				$budgetUpdateRoute = ($budgetId && Route::has(ViewsConstants::BDG . '.update'))
					? (route(ViewsConstants::BDG . '.update', $budgetId) ?? '#')
					: '#';
				$budgetUpdateFormId = 'budget-update-form-' . ($budgetId ?? 'unknown');
				$budgetUpdateGuardMsg = Utility::fetchLinkMessage(
					$lang,
					ViewsConstants::BDG,
					'budget_update_route_unavailable'
				) ?? 'Budget update route is unavailable. Please contact technical support or your domain administrator.';
			} catch (\Error $e) {
				FormLog::error('Error in budgets/edit.blade.php form @php block', [
					'exception_class' => get_class($e),
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			} catch (\Exception $e) {
				FormLog::error('Exception in budgets/edit.blade.php form @php block', [
					'exception_class' => get_class($e),
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			} catch (\Throwable $e) {
				FormLog::error('Throwable in budgets/edit.blade.php form @php block', [
					'exception_class' => get_class($e),
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			}
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
            <script defer>window.RouteGuard?.guardFormSubmit?.('{{ $budgetUpdateFormId }}');</script>
        @endpush
            <div class="row">
                <input type="hidden" name="type" id="type" value="{{ csrf_token() }}">
                <div class="{{ VC::FM_GCB4 }}">
                    {{ Form::label('name', __('Name'),['class'=>'form-label']) }}
                    {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
                </div>
                <div class="{{ VC::FM_GCB4 }}">
                    {{ Form::label('period', __('Budget Period'),['class'=>'form-label']) }}
                    {{ Form::select('period', $periods,null, array('class' => 'form-control select period','required'=>'required')) }}
                </div>
                <div class="{{ VC::FM_G }} {{ VC::CM4 }}">
                    <div class="btn-box">
                        {{ Form::label('year', __('Year'),['class'=>'form-label']) }}
                        {{ Form::select('year',$yearList,isset($_GET['year'])?$_GET['year']:'', array('class' => 'form-control select')) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::C12 }}">
        <div class="card">
            <div class="{{ VC::CD_BD_TB_BD }}">
                @php
                    try {
                        $monthsMonthly = (is_array($monthList ?? null) && count($monthList ?? [])) || (($monthList ?? null) instanceof Collection && ($monthList)->isNotEmpty()) ? $monthList : [];
                        $monthsQuarterly = (is_array($quarterly_monthlist ?? null) && count($quarterly_monthlist ?? [])) || (($quarterly_monthlist ?? null) instanceof Collection && ($quarterly_monthlist)->isNotEmpty()) ? $quarterly_monthlist : [];
                        $monthsHalfYearly = (is_array($half_yearly_monthlist ?? null) && count($half_yearly_monthlist ?? [])) || (($half_yearly_monthlist ?? null) instanceof Collection && ($half_yearly_monthlist)->isNotEmpty()) ? $half_yearly_monthlist : [];
                        $monthsYearly = (is_array($yearly_monthlist ?? null) && count($yearly_monthlist ?? [])) || (($yearly_monthlist ?? null) instanceof Collection && ($yearly_monthlist)->isNotEmpty()) ? $yearly_monthlist : [];
                        $incomeList = (is_array($incomeproduct ?? null) && count($incomeproduct ?? [])) || (($incomeproduct ?? null) instanceof Collection && ($incomeproduct)->isNotEmpty()) ? $incomeproduct : [];
                        $expenseList = (is_array($expenseproduct ?? null) && count($expenseproduct ?? [])) || (($expenseproduct ?? null) instanceof Collection && ($expenseproduct)->isNotEmpty()) ? $expenseproduct : [];
                        $hasPriceFormat = ($user ?? null) && method_exists($user, 'priceFormat');
                    } catch (\Throwable $e) {
                        \Log::error('budgets/edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <div class="{{ VC::TB_RSP }} budget_plan {{ VC::DBL }}" id="monthly">
                    <table class="{{ VC::TB_MB0 }}" id="dataTable-manual">
                        <thead>
                            <tr>
                                <th>{{ __('Category') }}</th>
                                @if(!empty($monthsMonthly))
                                    @foreach($monthsMonthly as $m)<td class="{{ VC::TTL_TX_DK }}">{{ $m }}</td>@endforeach
                                @else
                                    <td class="{{ VC::TTL_TX_DK }}">{{ __('No months available') }}</td>
                                @endif
                                <th>{{ __('Total :') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><th colspan="14" class="{{ VC::TX_DK_LBL }}"><span>{{ __('Income :') }}</span></th></tr>
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
                                                    <input type="number" class="{{ VC::FM_CT }} pl-1 pr-1 income_data {{ $m }}_income" data-month="{{ $m }}" name="income[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'income_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'income_data.'.$psId.'.'.$m) : 0 }}" id="income_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find months or product id') }}</td>
                                        @endif
                                        <td class="totalIncome {{ VC::TX_DK }}">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="{{ VC::TXCT_DK }}">{{ __('No income products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="{{ VC::TX_DK }}">{{ __('Total :') }}</td>
                                @if(!empty($monthsMonthly))
                                    @foreach($monthsMonthly as $m)<td><span class="{{ $m }}_total_income {{ VC::TX_DK }}">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No months available') }}</td>
                                @endif
                                <td><span class="{{ VC::INC_TX_DK }}">0.00</span></td>
                            </tr>
                            <tr><th colspan="14" class="{{ VC::TX_DK_LBL }}"><span>{{ __('Expense :') }}</span></th></tr>
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
                                                    <input type="number" class="{{ VC::FM_CT }} pl-1 pr-1 expense_data {{ $m }}_expense" data-month="{{ $m }}" name="expense[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m) : 0 }}" id="expense_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find months or product id') }}</td>
                                        @endif
                                        <td class="totalExpense {{ VC::TX_DK }}">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="{{ VC::TXCT_DK }}">{{ __('No expense products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="{{ VC::TX_DK }}">{{ __('Total :') }}</td>
                                @if(!empty($monthsMonthly))
                                    @foreach($monthsMonthly as $m)<td><span class="{{ $m }}_total_expense {{ VC::TX_DK }}">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No months available') }}</td>
                                @endif
                                <td><span class="{{ VC::EXP_TX_DK }}">0.00</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        @php
                            $budgetCancelBtnId ??= 'budget-planner-cancel-btn-monthly';
@endphp
                        <input type="button" id="{{ $budgetCancelBtnId }}" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-url="{{ $budgetIndexRoute ?? '#' }}" data-guard-msg="{{ base64_encode($budgetIndexGuardMsg ?? __('Failed to get budget index route')) }}">
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/budgets/cancelMonthly.js') }}"></script>
                        @endpush
                        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
                    </div>
                </div>
                <div class="{{ VC::TB_RSP_BDG_DN }}" id="quarterly">
                    <table class="{{ VC::TB_MB0 }}" id="dataTable-manual">
                        <thead>
                            <tr>
                                <th>{{ __('Category') }}</th>
                                @if(!empty($monthsQuarterly))
                                    @foreach($monthsQuarterly as $m)<td class="{{ VC::TTL_TX_DK }}">{{ $m }}</td>@endforeach
                                @else
                                    <td class="{{ VC::TTL_TX_DK }}">{{ __('No periods available') }}</td>
                                @endif
                                <th>{{ __('Total :') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><th colspan="14" class="{{ VC::TX_DK_LBL }}"><span>{{ __('Income :') }}</span></th></tr>
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
                                                    <input type="number" class="{{ VC::FM_CT }} income_data {{ $m }}_income" data-month="{{ $m }}" name="income[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'income_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'income_data.'.$psId.'.'.$m) : 0 }}" id="income_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find periods or product id') }}</td>
                                        @endif
                                        <td class="{{ VC::TX_END_INC_DK }}">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="{{ VC::TXCT_DK }}">{{ __('No income products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="{{ VC::TX_DK }}">{{ __('Total :') }}</td>
                                @if(!empty($monthsQuarterly))
                                    @foreach($monthsQuarterly as $m)<td><span class="{{ $m }}_total_income {{ VC::TX_DK }}">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="{{ VC::TX_END }}"><span class="{{ VC::INC_TX_DK }}">0.00</span></td>
                            </tr>
                            <tr><th colspan="14" class="{{ VC::TX_DK_LBL }}"><span>{{ __('Expense :') }}</span></th></tr>
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
                                                    <input type="number" class="{{ VC::FM_CT }} expense_data {{ $m }}_expense" data-month="{{ $m }}" name="expense[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m) : 0 }}" id="expense_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find periods or product id') }}</td>
                                        @endif
                                        <td class="{{ VC::TX_END_EXP_DK }}">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="{{ VC::TXCT_DK }}">{{ __('No expense products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="{{ VC::TX_DK }}">{{ __('Total :') }}</td>
                                @if(!empty($monthsQuarterly))
                                    @foreach($monthsQuarterly as $m)<td><span class="{{ $m }}_total_expense {{ VC::TX_DK }}">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="{{ VC::TX_END }}"><span class="{{ VC::EXP_TX_DK }}">0.00</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        @php
                            $cancelBtnId ??= 'budget-planner-cancel-btn-quarterly';
@endphp
                        <input type="button" id="{{ $cancelBtnId }}" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-url="{{ $budgetIndexRoute ?? '#' }}" data-guard-msg="{{ base64_encode($budgetIndexGuardMsg ?? __('Failed to get budget index route')) }}">
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/budgets/cancelQuarterly.js') }}"></script>
                        @endpush
                        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
                    </div>
                </div>
                <div class="{{ VC::TB_RSP_BDG_DN }}" id="half-yearly">
                    <table class="{{ VC::TB_MB0 }}" id="dataTable-manual">
                        <thead>
                            <tr>
                                <th>{{ __('Category') }}</th>
                                @if(!empty($monthsHalfYearly))
                                    @foreach($monthsHalfYearly as $m)<td class="{{ VC::TTL_TX_DK }}">{{ $m }}</td>@endforeach
                                @else
                                    <td class="{{ VC::TTL_TX_DK }}">{{ __('No periods available') }}</td>
                                @endif
                                <th>{{ __('Total :') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><th colspan="14" class="{{ VC::TX_DK_LBL }}"><span>{{ __('Income :') }}</span></th></tr>
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
                                                    <input type="number" class="{{ VC::FM_CT }} income_data {{ $m }}_income" data-month="{{ $m }}" name="income[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'income_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'income_data.'.$psId.'.'.$m) : 0 }}" id="income_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find periods or product id') }}</td>
                                        @endif
                                        <td class="{{ VC::TX_END_INC_DK }}">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="{{ VC::TXCT_DK }}">{{ __('No income products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="{{ VC::TX_DK }}">{{ __('Total :') }}</td>
                                @if(!empty($monthsHalfYearly))
                                    @foreach($monthsHalfYearly as $m)<td><span class="{{ $m }}_total_income {{ VC::TX_DK }}">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="{{ VC::TX_END }}"><span class="{{ VC::INC_TX_DK }}">0.00</span></td>
                            </tr>
                            <tr><th colspan="14" class="{{ VC::TX_DK_LBL }}"><span>{{ __('Expense :') }}</span></th></tr>
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
                                                    <input type="number" class="{{ VC::FM_CT }} expense_data {{ $m }}_expense" data-month="{{ $m }}" name="expense[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m) : 0 }}" id="expense_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find periods or product id') }}</td>
                                        @endif
                                        <td class="{{ VC::TX_END_EXP_DK }}">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="{{ VC::TXCT_DK }}">{{ __('No expense products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="{{ VC::TX_DK }}">{{ __('Total :') }}</td>
                                @if(!empty($monthsHalfYearly))
                                    @foreach($monthsHalfYearly as $m)<td><span class="{{ $m }}_total_expense {{ VC::TX_DK }}">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="{{ VC::TX_END }}"><span class="{{ VC::EXP_TX_DK }}">0.00</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        @php
                            $cancelBtnId ??= 'budget-planner-cancel-btn-half-yearly';
@endphp
                        <input type="button" id="{{ $cancelBtnId }}" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-url="{{ $budgetIndexRoute ?? '#' }}" data-guard-msg="{{ base64_encode($budgetIndexGuardMsg ?? __('Failed to get budget index route')) }}">
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/budgets/cancelHalfYearly.js') }}"></script>
                        @endpush
                        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
                    </div>
                </div>
                <div class="{{ VC::TB_RSP_BDG_DN }}" id="yearly">
                    <table class="{{ VC::TB_MB0 }}" id="dataTable-manual">
                        <thead>
                            <tr>
                                <th>{{ __('Category') }}</th>
                                @if(!empty($monthsYearly))
                                    @foreach($monthsYearly as $m)<td class="{{ VC::TTL_TX_DK }}">{{ $m }}</td>@endforeach
                                @else
                                    <td class="{{ VC::TTL_TX_DK }}">{{ __('No periods available') }}</td>
                                @endif
                                <th>{{ __('Total :') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><th colspan="14" class="{{ VC::TX_DK_LBL }}"><span>{{ __('Income :') }}</span></th></tr>
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
                                                    <input type="number" class="{{ VC::FM_CT }} income_data {{ $m }}_income" data-month="{{ $m }}" name="income[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'income_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'income_data.'.$psId.'.'.$m) : 0 }}" id="income_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find periods or product id') }}</td>
                                        @endif
                                        <td class="{{ VC::TX_END_INC_DK }}">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="{{ VC::TXCT_DK }}">{{ __('No income products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="{{ VC::TX_DK }}">{{ __('Total :') }}</td>
                                @if(!empty($monthsYearly))
                                    @foreach($monthsYearly as $m)<td><span class="{{ $m }}_total_income {{ VC::TX_DK }}">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="{{ VC::TX_END }}"><span class="{{ VC::INC_TX_DK }}">0.00</span></td>
                            </tr>
                            <tr><th colspan="14" class="{{ VC::TX_DK_LBL }}"><span>{{ __('Expense :') }}</span></th></tr>
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
                                                    <input type="number" class="{{ VC::FM_CT }} expense_data {{ $m }}_expense" data-month="{{ $m }}" name="expense[{{ $psId }}][{{ $m }}]" value="{{ is_numeric(data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m)) ? (float)data_get($budget ?? [], 'expense_data.'.$psId.'.'.$m) : 0 }}" id="expense_data_{{ $m }}">
                                                </td>
                                            @endforeach
                                        @else
                                            <td>{{ __('Could not find periods or product id') }}</td>
                                        @endif
                                        <td class="{{ VC::TX_END_EXP_DK }}">0.00</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="14" class="{{ VC::TXCT_DK }}">{{ __('No expense products available') }}</td></tr>
                            @endif
                            <tr>
                                <td class="{{ VC::TX_DK }}">{{ __('Total :') }}</td>
                                @if(!empty($monthsYearly))
                                    @foreach($monthsYearly as $m)<td><span class="{{ $m }}_total_expense {{ VC::TX_DK }}">0.00</span></td>@endforeach
                                @else
                                    <td>{{ __('No periods available') }}</td>
                                @endif
                                <td class="{{ VC::TX_END }}"><span class="{{ VC::EXP_TX_DK }}">0.00</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        @php
                            $cancelBtnId ??= 'budget-planner-cancel-btn-yearly';
@endphp
                        <input type="button" id="{{ $cancelBtnId }}" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-url="{{ $budgetIndexRoute ?? '#' }}" data-guard-msg="{{ base64_encode($budgetIndexGuardMsg ?? __('Failed to get budget index route')) }}">
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/budgets/cancelYearly.js') }}"></script>
                        @endpush
                        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
                    </div>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
@endsection
