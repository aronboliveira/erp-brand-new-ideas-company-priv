- Read through this .blade.php block and refactor applying safeguards with isset(), empty(), nullish coalescence/checks and, for multidepth chains of property (2+), using data_get()

- IF the string is meant to reach the html to be sent to the client view, default the failed values to some sort of variation of **('Could not find [alias for the property]'), **('Failed to get [alias for the property']), \_\_('No [alias for the property] available'), etc

---

Example 1:

```
<div class="{{ VC::DFL }} {{ VC::ALC }} {{ VC::JCE }}"> <div class="col-xl-3 col-lg-3 {{ VC::CM6 }} {{ VC::CS12 }} col-12 me-2"> <div class="btn-box"> {{ Form::label('issue_date', __('Date'), ['class' => VC::FM_LB]) }} {{ Form::text('issue_date', isset($_GET['issue_date']) ? $_GET['issue_date'] : null, ['class' => VC::FM_CT.' month-btn', 'id' => 'pc-daterangepicker-1']) }} </div> </div> <div class="col-xl-3 col-lg-3 {{ VC::CM6 }} {{ VC::CS12 }} col-12"> <div class="btn-box"> {{ Form::label('status', __('Status'), ['class' => VC::FM_LB]) }} {{ Form::select('status', ['' => 'Select Status'] + $status, isset($_GET['status']) ? $_GET['status'] : '', ['class' => VC::FM_CT_SL.' select']) }} </div> </div> <div class="{{ VC::C_AT }} {{ VC::FEND }} {{ VC::MS2 }} {{ VC::MT4 }}"> <a href="#" class="{{ VC::BT_SM_PM }}" onclick="document.getElementById('frm_submit').submit(); return false;" data-bs-toggle="tooltip" data-original-title="{{ __('apply') }}"> <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span> </a> <a href="{{ route(VW::PRD_SV.'.index') }}" class="{{ VC::BT_SM_DG }}" data-bs-toggle="tooltip" title="{{ __('Reset') }}"> <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span> </a> </div> </div>
```

Example 2:

```
<div class="{{ VC::RW }}">
		<div class="{{ VC::CS12 }}">
				<div class="mt-2" id="multiCollapseExample1">
						<div class="{{ VC::CD }}">
								<div class="card-body">
										@php
												$acctStmtBase = ViewsConstants::RPT.'.account.statement';
												$acctStmtKebab = Str::kebab($acctStmtBase);
												$acctStmtResolved = Route::has($acctStmtBase) ? $acctStmtBase : (Route::has($acctStmtKebab) ? $acctStmtKebab : null);
												$actionRoute = $acctStmtResolved ? [$acctStmtResolved] : ['#'];
												$actionUrl = $acctStmtResolved ? route($acctStmtResolved) : '#';
												$langValue = isset($lang) ? $lang : Utility::fetchUserLang();
												$applyGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'apply_account_statement_route_unavailable') ?? 'Apply account statement route is unavailable. Please contact technical support or your domain administrator.';
												$resetGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'reset_account_statement_route_unavailable') ?? 'Reset account statement route is unavailable. Please contact technical support or your domain administrator.';
										@endphp
										{{ Form::open(['route'=> $actionRoute,'method'=>'GET','id'=>'report_account','data-url'=>$actionUrl,'data-guard-msg'=>$applyGuardMsg,'data-sv-localized'=>'true']) }}
												<div class="{{ VC::R_ALC_JCE }}">
														<div class="col-xl-10">
																<div class="{{ VC::RW }}">
																		<div class="{{ VC::CL_XL3 }}">
																				<div class="btn-box">
																						{{ Form::label('start_month', __('Start Month'), ['class' => VC::FM_LB]) }}
																						{{ Form::month('start_month', isset($_GET['start_month']) ? $_GET['start_month'] : date('Y-m', strtotime('-5 month')), ['class' => 'month-btn ' . VC::FM_CT]) }}
																				</div>
																		</div>
																		<div class="{{ VC::CL_XL3 }}">
																				<div class="btn-box">
																						{{ Form::label('end_month', __('End Month'), ['class' => VC::FM_LB]) }}
																						{{ Form::month('end_month', isset($_GET['end_month']) ? $_GET['end_month'] : date('Y-m'), ['class' => 'month-btn ' . VC::FM_CT]) }}
																				</div>
																		</div>
																		<div class="{{ VC::CL_XL3 }}">
																				<div class="btn-box">
																						{{ Form::label('account', __('Account'), ['class' => VC::FM_LB]) }}
																						{{ Form::select('account', $account, isset($_GET['account']) ? $_GET['account'] : '', ['class' => VC::FM_CT_SL]) }}
																				</div>
																		</div>
																		<div class="{{ VC::CL_XL3 }}">
																				<div class="btn-box">
																						{{ Form::label('type', __('Category'), ['class' => VC::FM_LB]) }}
																						{{ Form::select('type', $types, isset($_GET['type']) ? $_GET['type'] : '', ['class' => VC::FM_CT_SL, 'placeholder' => __('Select Category')]) }}
																				</div>
																		</div>
																</div>
														</div>
														<div class="{{ VC::C_AT }}">
																<div class="{{ VC::RW }}">
																		<div class="{{ VC::C_AT }} {{ VC::MT4 }}">
																				<a id="apply-account-statement"
																				href="#"
																				class="{{ VC::BT_SM_PM }}"
																				data-form-id="report_account"
																				data-guard-msg="{{ $applyGuardMsg }}"
																				data-sv-localized="true"
																				data-bs-toggle="tooltip"
																				title="{{ __('Apply') }}"
																				data-original-title="{{ __('apply') }}">
																						<span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
																				</a>
																				<a id="reset-account-statement"
																				href="{{ $actionUrl }}"
																				class="{{ VC::BT_SM_DG }}"
																				data-url="{{ $actionUrl }}"
																				data-guard-msg="{{ $resetGuardMsg }}"
																				data-sv-localized="true"
																				data-bs-toggle="tooltip"
																				title="{{ __('Reset') }}"
																				data-original-title="{{ __('Reset') }}">
																						<span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
																				</a>
																		</div>
																</div>
														</div>
												</div>
										{{ Form::close() }}
										@push(StacksConstants::ADM_SCRP_PG)
												<script src="{{ asset('assets/js/routes/reports/accountStatements/apply.js') }}" defer></script>
												<script src="{{ asset('assets/js/routes/reports/accountStatements/reset.js') }}" defer></script>
										@endpush
								</div>
						</div>
				</div>
		</div>
</div>

<div id="printableArea">
		<div class="{{ VC::RW }} {{ VC::MT3 }}">
				<div class="col">
						<input type="hidden"
										id="filename"
										value="{{ __('Account Statement') . ' ' . $filter['type'] . ' ' . __('Report of') . ' ' . $filter['startDateRange'] . ' ' . __('to') . ' ' . $filter['endDateRange'] }}">
						<div class="{{ VC::CD_POS }}">
								<h7 class="{{ VC::RPT_TX_GR }}">{{ __('Report') }} :</h7>
								<h6 class="{{ VC::RPT_TX_DEF }}">{{ __('Account Statement Summary') }}</h6>
						</div>
				</div>

				@if($filter['account'] != __('All'))
						<div class="col">
								<div class="{{ VC::CD_POS }}">
										<h7 class="{{ VC::RPT_TX_GR }}">{{ __('Account') }} :</h7>
										<h6 class="{{ VC::RPT_TX_DEF }}">{{ $filter['account'] }}</h6>
								</div>
						</div>
				@endif

				@if($filter['type'] != __('All'))
						<div class="col">
								<div class="{{ VC::CD_POS }}">
										<h7 class="{{ VC::RPT_TX_GR }}">{{ __('Type') }} :</h7>
										<h6 class="{{ VC::RPT_TX_DEF }}">{{ $filter['type'] }}</h6>
								</div>
						</div>
				@endif

				<div class="col">
						<div class="{{ VC::CD_POS }}">
								<h7 class="{{ VC::RPT_TX_GR }}">{{ __('Duration') }} :</h7>
								<h6 class="{{ VC::RPT_TX_DEF }}">{{ $filter['startDateRange'] . ' ' . __('to') . ' ' . $filter['endDateRange'] }}</h6>
						</div>
				</div>
		</div>

		@if(!empty($reportData['revenueAccounts']))
				<div class="{{ VC::RW }}">
						@foreach($reportData['revenueAccounts'] as $acc)
								<div class="{{ VC::CL_XL3 }}">
										<div class="{{ VC::CD_POS }}">
												@if($acc->holder_name == 'Cash')
														<h7 class="{{ VC::RPT_TX_GR }}">{{ $acc->holder_name }}</h7>
												@elseif(empty($acc->holder_name))
														<h7 class="{{ VC::RPT_TX_GR }}">{{ __('Stripe / PayPal') }}</h7>
												@else
														<h7 class="{{ VC::RPT_TX_GR }}">{{ $acc->holder_name . ' - ' . $acc->bank_name }}</h7>
												@endif
												<h6 class="{{ VC::RPT_TX_DEF }}">{{ $user?->priceFormat($acc->total) }}</h6>
										</div>
								</div>
						@endforeach
				</div>
		@endif

		@if(!empty($reportData['paymentAccounts']))
				<div class="{{ VC::RW }}">
						@foreach($reportData['paymentAccounts'] as $acc)
								<div class="{{ VC::CL_XL3 }}">
										<div class="{{ VC::CD_POS }}">
												@if($acc->holder_name == 'Cash')
														<h7 class="{{ VC::RPT_TX_GR }}">{{ $acc->holder_name }}</h7>
												@elseif(empty($acc->holder_name))
														<h7 class="{{ VC::RPT_TX_GR }}">{{ __('Stripe / PayPal') }}</h7>
												@else
														<h7 class="{{ VC::RPT_TX_GR }}">{{ $acc->holder_name . ' - ' . $acc->bank_name }}</h7>
												@endif
												<h6 class="{{ VC::RPT_TX_DEF }}">{{ $user?->priceFormat($acc->total) }}</h6>
										</div>
								</div>
						@endforeach
				</div>
		@endif
</div>

@php
		$revTotal = 0.0;
		$payTotal = 0.0;
		if (!empty($reportData['revenues'])) {
				foreach ($reportData['revenues'] as $r) { $revTotal += (float) $r->amount; }
		}
		if (!empty($reportData['payments'])) {
				foreach ($reportData['payments'] as $p) { $payTotal += (float) $p->amount; }
		}
		$netTotal = $revTotal - $payTotal;
@endphp

<div class="{{ VC::RW }}">
		<div class="{{ VC::CM12 }}">
				<div class="{{ VC::CD }}">
						<div class="card-body table-border-style">
								<div class="table-responsive">
										<table class="{{ VC::TB }} datatable" id="account-statement-table">
												<thead>
														<tr>
																<th>{{ __('Date') }}</th>
																<th class="text-end">{{ __('Amount') }}</th>
																<th>{{ __('Description') }}</th>
														</tr>
												</thead>
												<tbody>
														@php $hasRows = false; @endphp

														@if(!empty($reportData['revenues']))
																@foreach ($reportData['revenues'] as $revenue)
																		@php $hasRows = true; @endphp
																		<tr class="font-style">
																				<td>{{ $user?->dateFormat($revenue->date) }}</td>
																				<td class="text-end">{{ $user?->priceFormat($revenue->amount) }}</td>
																				<td>{{ $revenue->description }}</td>
																		</tr>
																@endforeach
														@endif

														@if(!empty($reportData['payments']))
																@foreach ($reportData['payments'] as $payment)
																		@php $hasRows = true; @endphp
																		<tr class="font-style">
																				<td>{{ $user?->dateFormat($payment->date) }}</td>
																				<td class="text-end">{{ $user?->priceFormat($payment->amount) ?? __('Failed to fetch user data.') }}</td>
																				<td>{{ !empty($payment->description) ? $payment->description : __('No description.') }}</td>
																		</tr>
																@endforeach
														@endif

														@unless($hasRows)
																<tr>
																		<td colspan="3" class="text-center text-muted">{{ __('No transactions found for the selected period.') }}</td>
																</tr>
														@endunless
												</tbody>

												@if($hasRows)
														<tfoot>
																<tr>
																		<th class="text-end">{{ __('Total Revenue') }}</th>
																		<th class="text-end">{{ $user?->priceFormat($revTotal) ?? __('Failed to fetch user data.') }}</th>
																		<th></th>
																</tr>
																<tr>
																		<th class="text-end">{{ __('Total Payments') }}</th>
																		<th class="text-end">{{ $user?->priceFormat($payTotal) ?? __('Failed to fetch user data.') }}</th>
																		<th></th>
																</tr>
																<tr>
																		<th class="text-end">{{ __('Net Total') }}</th>
																		<th class="text-end">{{ $user?->priceFormat($netTotal) ?? __('Failed to fetch user data.') }}</th>
																		<th></th>
																</tr>
														</tfoot>
												@endif
										</table>
								</div>
						</div>
				</div>
		</div>
</div>
```

```
----

- Don't add comments or additional newlines, please;
- Do not include route checking as well. This will be done separately;
- Always indent the @php content so I can fold it in the editor;
- $labelThProposal = __('Proposal') ?: __('Failed to get proposal label') => This type of checks on raw strings do not make sense. Don't do these;
- Never forget to check critical data, like, for instance, if $proposal, in the loop for $proposals, is set and not empty, and if the $proposal is an array or a laravel collection;
- Do not create variables to store values that do not repeat;
- Always nest properly the html tags within their genealogy;
```
