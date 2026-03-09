@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
    } catch (\Throwable $e) {
        \Log::error('transactions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Transaction Summary')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Report')}}</li>
    <li class="{{ VC::BCI }}">{{__('Transaction Summary')}}</li>
@endsection

@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/datatable/buttons.dataTables.min.css') }}">
@endpush

{{--    <script src="{{ asset('js/datatable/dataTables.buttons.min.js') }}"></script>--}}
{{--    <script src="{{ asset('js/datatable/buttons.html5.min.js') }}"></script>--}}
{{--    <script type="text/javascript" src="{{ asset('js/datatable/buttons.print.min.js') }}"></script>--}}
{{--    <script src="{{ asset('assets/js/plugins/simple-datatables.js') }}"></script>--}}
@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
		<script async src="{{ asset('js/routes/transactions/lang/pdf.js') }}"></script>
		<script defer src="{{ asset('js/routes/transactions/pdf.js') }}"></script>
    <script defer src="{{ asset('js/datatable/jszip.min.js') }}"></script>
    <script defer src="{{ asset('js/datatable/pdfmake.min.js') }}"></script>
    <script defer src="{{ asset('js/datatable/vfs_fonts.js') }}"></script>
@endpush

{{--        <a class="{{ VC::BT_SM_PM }}" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
{{--            <i class="ti ti-filter"></i>--}}
{{--        </a>--}}
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            try {
                $tstExportBase = VW::TST.'.export';
                $tstExportKebab = Str::kebab($tstExportBase);
                $tstExportResolved = Route::has($tstExportBase) ? $tstExportBase : (Route::has($tstExportKebab) ? $tstExportKebab : null);
                $tstExportUrl = $tstExportResolved ? route($tstExportResolved) : '#';
                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $tstExportGuardMsg = Utility::fetchLinkMessage($langValue, VW::TST, 'export_test_route_unavailable') ?? 'Export test route is unavailable. Please contact technical support or your domain administrator.';
                $tstExportAnchorId = 'tst-export-btn';
            } catch (\Throwable $e) {
                \Log::error('transactions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <a href="{{ $tstExportUrl }}"
        id="{{ $tstExportAnchorId }}"
        data-url="{{ $tstExportUrl }}"
        data-guard-msg="{{ base64_encode($tstExportGuardMsg) }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ __('Export') }}"
        class="{{ VC::BT_SM_PM }}">
            <i class="{{ VC::TI_EXP }}"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    try {
                        const el = document.getElementById('{{ $tstExportAnchorId }}');
                        if (!el) { return; }
                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                        el.setAttribute('data-listener-active','true');
                        el.addEventListener('click',(e) => {
                            try {
                                const href = el.getAttribute('href') ?? '#';
                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                if (url !== '#' && href !== '#') { return; }
                                e.preventDefault();
                                const msg = el.getAttribute('data-guard-msg') ?? 'Export test route is unavailable. Please contact technical support or your domain administrator.';
                                (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                el.setAttribute('data-failed-route','true');
                            } catch (err) {}
                        });
                    } catch (err) {}
                })();
            </script>
        @endpush
        @php
            $downloadLabelTr = __('Download');
            $downloadGuardMsgTr = Utility::fetchLinkMessage($lang, VW::TST, 'download_transactions_report_unavailable') ?? 'Download function for Transactions report is unavailable. Please contact technical support or your domain administrator.';
@endphp
        <a href="#"
        class="{{ VC::BT_SM_PM }} download-transactions"
        data-func-name="saveAsPDF"
        data-guard-msg="{{ base64_encode($downloadGuardMsgTr) }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ $downloadLabelTr }}"
        aria-label="{{ $downloadLabelTr }}"
        data-original-title="{{ $downloadLabelTr }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/transactions/download.js') }}" defer></script>
        @endpush
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
	@php
		try {
		    $indexBase      = VW::TST . '.index';
		    $indexKebab     = Str::kebab($indexBase);
		    $indexResolved  = Route::has($indexBase) ? $indexBase : (Route::has($indexKebab) ? $indexKebab : null);
		    $indexUrl       = $indexResolved ? route($indexResolved) : '#';
		    $indexGuard     = Utility::fetchLinkMessage($lang, VW::TST, 'index_transaction_route_unavailable') ?? 'Transaction index route is unavailable. Please contact technical support or your domain administrator.';
		    $accountOptions = is_array($account ?? null) ? $account : (method_exists(($account ?? null), 'toArray') ? $account->toArray() : ['' => __('No accounts available')]);
		    $categoryOpts   = is_array($category ?? null) ? $category : (method_exists(($category ?? null), 'toArray') ? $category->toArray() : ['' => __('No categories available')]);
		} catch (\Throwable $e) {
		    \Log::error('transactions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		}
@endphp
	<div class="{{ VC::RW }}">
		<div class="{{ VC::CS12 }}">
			<div class="{{ VC::MT2 }}" id="multiCollapseExample1">
				<div class="{{ VC::CD }}">
					<div class="{{ VC::CD_BD }}">
						{{ Form::open([
							'url'                  => $indexUrl,
							'method'               => 'get',
							'id'                   => 'transaction_report',
							'data-resolved-action' => $indexUrl,
							'data-guard-msg'       => $indexGuard,
							'data-sv-localized'    => 'true',
						]) }}
							<div class="{{ VC::R_ALC_JCE }}">
								<div class="{{ VC::CXL10 }}">
									<div class="{{ VC::RW }}">
										<div class="{{ VC::CL_XL3 }}">
											<div class="btn-box">
												{{ Form::label('start_month', __('Start Month'), ['class' => VC::FM_LB]) }}
												{{ Form::month('start_month', $_GET['start_month'] ?? date('Y-m', strtotime('-5 month')), ['class' => 'month-btn ' . VC::FM_CT]) }}
											</div>
										</div>
										<div class="{{ VC::CL_XL3 }}">
											<div class="btn-box">
												{{ Form::label('end_month', __('End Month'), ['class' => VC::FM_LB]) }}
												{{ Form::month('end_month', $_GET['end_month'] ?? date('Y-m'), ['class' => 'month-btn ' . VC::FM_CT]) }}
											</div>
										</div>
										<div class="{{ VC::CL_XL3 }}">
											<div class="btn-box">
												{{ Form::label('account', __('Account'), ['class' => VC::FM_LB]) }}
												{{ Form::select('account', $accountOptions, $_GET['account'] ?? '', ['class' => VC::FM_CT_SL]) }}
											</div>
										</div>
										<div class="{{ VC::CL_XL3 }}">
											<div class="btn-box">
												{{ Form::label('category', __('Category'), ['class' => VC::FM_LB]) }}
												{{ Form::select('category', $categoryOpts, $_GET['category'] ?? '', ['class' => VC::FM_CT_SL]) }}
											</div>
										</div>
									</div>
                                </div>
								<div class="{{ VC::C_AT }} {{ VC::MT4 }}">
									<div class="{{ VC::RW }}">
										<div class="{{ VC::C_AT }}">
											<a href="#"
											   class="{{ VC::BT_SM_PM }}"
											   onclick="document.getElementById('transaction_report').submit(); return false;"
											   data-bs-toggle="tooltip"
											   title="{{ __('Apply') }}"
											   data-original-title="{{ __('apply') }}">
												<span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
											</a>
											<a href="#"
											   id="transaction-report-reset"
											   class="{{ VC::BT_SM_DG }}"
											   data-url="{{ $indexUrl }}"
											   data-guard-msg="{{ base64_encode($indexGuard) }}"
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
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="printableArea">
		<div class="{{ VC::RW }}">
			<div class="col">
				<input type="hidden" value="{{ (data_get($filter,'category') ?: __('No category available')).' '.__('Category').' '.__('Transaction').' '.__('Report of').' '.(data_get($filter,'startDateRange') ?: __('No start date available')).' '.__('to').' '.(data_get($filter,'endDateRange') ?: __('No end date available')) }}" id="filename">
				<div class="{{ VC::CD_POS }}">
					<h6 class="{{ VC::MB0 }}">{{ __('Report') }} :</h6>
					<h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Transaction Summary') }}</h7>
				</div>
			</div>
			@if((data_get($filter,'account') ?? '') != __('All'))
				<div class="col">
					<div class="{{ VC::CD_POS }}">
						<h6 class="{{ VC::MB0 }}">{{ __('Account') }} :</h6>
						<h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ data_get($filter,'account') ?: __('No account available') }}</h7>
					</div>
				</div>
			@endif
			@if((data_get($filter,'category') ?? '') != __('All'))
				<div class="col">
					<div class="{{ VC::CD_POS }}">
						<h6 class="{{ VC::MB0 }}">{{ __('Category') }} :</h6>
						<h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ data_get($filter,'category') ?: __('No category available') }}</h7>
					</div>
				</div>
			@endif
			<div class="col">
				<div class="{{ VC::CD_POS }}">
					<h6 class="{{ VC::MB0 }}">{{ __('Duration') }} :</h6>
					<h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ (data_get($filter,'startDateRange') ?: __('No start date available')).' '.__('to').' '.(data_get($filter,'endDateRange') ?: __('No end date available')) }}</h7>
				</div>
			</div>
		</div>

		<div class="{{ VC::RW }}">
			@forelse(((($accounts ?? null) && (is_array($accounts) || method_exists($accounts,'toArray')))) ? (is_array($accounts) ? $accounts : $accounts->toArray()) : [] as $account)
				@php
					try {
					    $__holder = (string) (data_get($account,'holder_name') ?? '');
					    $__bank   = (string) (data_get($account,'bank_name') ?? '');
					    $__total  = data_get($account,'total');
					} catch (\Throwable $e) {
					    \Log::error('transactions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
					}
@endphp
				<div class="{{ VC::CL_XL3 }}">
					<div class="{{ VC::CD_POS }}">
						@if($__holder === 'Cash')
							<h6 class="{{ VC::MB0 }}">{{ $__holder }}</h6>
						@elseif(empty($__holder))
							<h6 class="{{ VC::MB0 }}">{{ __('Stripe / Paypal') }}</h6>
						@else
							<h6 class="{{ VC::MB0 }}">{{ trim($__holder.' - '.$__bank) }}</h6>
						@endif
						<h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $user?->priceFormat((float)($__total ?? 0)) ?? __('Failed to get amount') }}</h7>
					</div>
				</div>
			@empty
				<div class="{{ VC::CM12 }}"><p class="{{ VC::TXCT_MT }}">{{ __('No accounts available') }}</p></div>
			@endforelse
		</div>
	</div>
	<div class="{{ VC::RW }}">
		<div class="{{ VC::CM12 }}">
			<div class="{{ VC::CD }}">
				<div class="{{ VC::CD_BD_TB_BD }}">
					<div class="{{ VC::TB_RSP }}">
						<table class="{{ VC::TB }} datatable">
							<thead>
							<tr>
								<th>{{ __('Date') }}</th>
								<th>{{ __('Account') }}</th>
								<th>{{ __('Type') }}</th>
								<th>{{ __('Category') }}</th>
								<th>{{ __('Description') }}</th>
								<th>{{ __('Amount') }}</th>
							</tr>
							</thead>
							<tbody>
							@forelse(((($transactions ?? null) && (is_array($transactions) || method_exists($transactions,'toArray')))) ? (is_array($transactions) ? $transactions : $transactions->toArray()) : [] as $transaction)
								<tr>
									<td>{{ $user?->dateFormat(data_get($transaction,'date')) ?? __('Failed to get date') }}</td>
									<td>
										@php
											try {
											    $__ba      = (is_object($transaction) && method_exists($transaction,'bankAccount')) ? ($transaction->bankAccount() ?? null) : null;
											    $__bHolder = data_get($__ba,'holder_name');
											    $__bName   = data_get($__ba,'bank_name');
											} catch (\Throwable $e) {
											    \Log::error('transactions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
											}
@endphp
										@if($__ba && $__bHolder === 'Cash')
											{{ $__bHolder }}
										@else
											{{ $__ba ? (trim((string)($__bName ?? '').' '.(string)($__bHolder ?? '')) ?: __('No account available')) : __('No account available') }}
										@endif
									</td>
									<td>{{ data_get($transaction,'type') ?: __('No type available') }}</td>
									<td>{{ data_get($transaction,'category') ?: __('No category available') }}</td>
									<td>{{ data_get($transaction,'description') ?: __('No description available') }}</td>
									<td>{{ $user?->priceFormat((float)(data_get($transaction,'amount') ?? 0)) ?? __('Failed to get amount') }}</td>
								</tr>
							@empty
								<tr><td colspan="6" class="{{ VC::TXCT_MT }}">{{ __('No transactions available') }}</td></tr>
							@endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
	<script defer src="{{ asset('assets/js/routes/transactions/report.js') }}"></script>
@endpush
