@php
$user ??= null;
	$lang ??= 'en';
	$hasJournal ??= false;
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateResolved ??= null;
	$updateUrl ??= '#';
	$updateGuard ??= '';
	$indexBase ??= '';
	$indexKebab ??= '';
	$indexResolved ??= null;
	$indexUrl ??= '#';
	$indexGuard ??= '';
	$genBase ??= '';
	$genResolved ??= null;
	$genUrl ??= '#';
	$genGuard ??= '';
	$journalNumber ??= '';
	$currencySym ??= '';
	try {
		$user = Auth::user();
		$lang = Utility::fetchUserLang(user: $user) ?? 'en';
		$hasJournal = !empty($journalEntry ?? null) && data_get($journalEntry, 'id');
		$updateBase = VW::JRN_ET . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$updateUrl = ($updateResolved && $hasJournal) ? (route($updateResolved, data_get($journalEntry ?? null, 'id')) ?? '#') : '#';
		$updateGuard = Utility::fetchLinkMessage($lang, VW::JRN_ET, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
		$indexBase = VW::JRN_ET . '.index';
		$indexKebab = Str::kebab($indexBase);
		$indexResolved = Route::has($indexBase) ? $indexBase : (Route::has($indexKebab) ? $indexKebab : null);
		$indexUrl = $indexResolved ? (route($indexResolved) ?? '#') : '#';
		$indexGuard = Utility::fetchLinkMessage($lang, VW::JRN_ET, 'index_route_unavailable') ?? __('Index route is unavailable. Please contact technical support or your domain administrator.');
		$genBase = 'generate';
		$genResolved = Route::has($genBase) ? $genBase : (Route::has(Str::kebab($genBase)) ? Str::kebab($genBase) : null);
		$genUrl = $genResolved ? (route($genResolved, ['journal entry']) ?? '#') : '#';
		$genGuard = Utility::fetchLinkMessage($lang, 'ai', 'generate_route_unavailable') ?? __('AI generation route is unavailable. Please contact technical support or your domain administrator.');
		$journalNumber = (method_exists($user, 'journalNumberFormat') ? ($user?->journalNumberFormat(data_get($journalEntry ?? null, 'journal_id')) ?: __('No journal number found.')) : __('No journal number found.'));
		$currencySym = (method_exists($user, 'currencySymbol') ? ($user?->currencySymbol() ?: __('No currency could be found.')) : __('No currency could be found.'));
	} catch (\Error $e) {
		Log::error('Error in journal_entries/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in journal_entries/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in journal_entries/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Journal Entry Edit') }}
@endsection

@section(YW::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Double Entry') }}</li>
    <li class="{{ VC::BCI }}">{{ __('Journal Entry') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    @if($user && method_exists($user, 'creatorId'))
        @php
$planUser ??= null;
			$plan ??= null;
			try {
				$planUser = User::find($user->creatorId());
				$plan = Plan::getPlan($planUser?->plan ?? DatabaseConstants::DEFAULT_PLAN);
			} catch (\\Error $e) {
				AiLog::error('Error in journal_entries/edit.blade.php AI @php block', [
					'exception_class' => get_class($e),
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			} catch (\\Exception $e) {
				AiLog::error('Exception in journal_entries/edit.blade.php AI @php block', [
					'exception_class' => get_class($e),
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			} catch (\\Throwable $e) {
				AiLog::error('Throwable in journal_entries/edit.blade.php AI @php block', [
					'exception_class' => get_class($e),
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			}
@endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="{{ VC::FEND }}">
                <a href="#"
                   data-size="md"
                   class="{{ VC::BT_SM_PM }} btn-icon"
                   data-ajax-popup-over="true"
                   data-url="{{ $genUrl }}"
                   data-guard-msg="{{ base64_encode($genGuard) }}"
                   data-sv-localized="true"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif
    @endif
@endsection

@section(YW::ADM_CTT)
    @if($hasJournal)
        {{ Form::model($journalEntry, [
            'url'               => $updateUrl,
            'method'            => 'PUT',
            'class'             => 'w-100',
            'id'                => 'journalEntry-edit-form',
            'data-url'          => $updateUrl,
            'data-guard-msg'    => $updateGuard,
            'data-sv-localized' => 'true'
        ]) }}
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="{{ VC::RW }} {{ VC::MT4 }}">
                <div class="{{ VC::CXL12 }}">
                    <div class="{{ VC::CD }}">
                        <div class="{{ VC::CD_BD }}">
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::CLM4 }}">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label('journal_number', __('Journal Number'), ['class'=> VC::FM_LB]) }}
                                        <div class="form-icon-user">
                                            <input type="text" class="{{ VC::FM_CT }}" value="{{ $journalNumber }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::CLM4 }}">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label('date', __('Transaction Date'), ['class'=> VC::FM_LB]) }}
                                        <div class="form-icon-user">
                                            {{ Form::date('date', null, ['class'=> VC::FM_CT,'required'=>'required']) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::CLM4 }}">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label('reference', __('Reference'), ['class'=> VC::FM_LB]) }}
                                        <div class="form-icon-user">
                                            {{ Form::text('reference', null, ['class' => VC::FM_CT]) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::CL8 }} {{ VC::CM8 }}">
                                    <div class="{{ VC::FM_GCB12 }}">
                                        {{ Form::label('description', __('Description'), ['class'=> VC::FM_LB]) }}
                                        {{ Form::textarea('description', null, ['class' => VC::FM_CT,'rows'=>'2']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::CD }} repeater" data-value='@json($journalEntry->accounts)'>
                        <div class="item-section {{ VC::PY2 }} py-4">
                            <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                                <div class="col-md-12 {{ VC::DFL }} {{ VC::ALC }} {{ VC::JCE }} justify-content-md-end">
                                    <div class="all-button-box">
                                        <a href="#" data-repeater-create="" class="{{ VC::BT_PRM }} me-4" data-toggle="modal" data-target="#add-bank">
                                            <i class="{{ VC::TI_PLS }}"></i> {{ __('Add Account') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::CD_BD_TB_BD }}">
                            <div class="{{ VC::TB_RSP }}">
                                <table class="{{ VC::TB_MB0 }}" data-repeater-list="accounts" id="sortable-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Account') }}</th>
                                            <th>{{ __('Debit') }}</th>
                                            <th>{{ __('Credit') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th class="{{ VC::TX_END }}">{{ __('Amount') }}</th>
                                            <th width="2%"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="ui-sortable" data-repeater-item>
                                        <tr>
                                            {{ Form::hidden('id', null, ['class' => 'form-control id']) }}
                                            <td width="25%" class="{{ VC::FM_G }} pt-0">
                                                {{ Form::select('account', $accounts, '', ['class' => VC::FM_CT . ' js-searchBox','required'=>'required']) }}
                                            </td>
                                            <td>
                                                <div class="{{ VC::FM_G }}">
                                                    {{ Form::text('debit','', ['class' => VC::FM_CT . ' debit','required'=>'required','placeholder'=>__('Debit')]) }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="{{ VC::FM_G }}">
                                                    {{ Form::text('credit','', ['class' => VC::FM_CT . ' credit','required'=>'required','placeholder'=>__('Credit')]) }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="{{ VC::FM_G }}">
                                                    {{ Form::text('description', null, ['class' => VC::FM_CT,'placeholder'=>__('Description')]) }}
                                                </div>
                                            </td>
                                            <td class="{{ VC::TX_END }} amount">0.00</td>
                                            <td>
                                                <a href="#" class="ti ti-trash {{ VC::TXT_WT }} text-danger" data-repeater-delete></a>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td></td>
                                            <td class="{{ VC::TX_END }}"><strong>{{ __('Total Credit') }} ({{ $currencySym }})</strong></td>
                                            <td class="{{ VC::TX_END }} totalCredit">0.00</td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td class="{{ VC::TX_END }}"><strong>{{ __('Total Debit') }} ({{ $currencySym }})</strong></td>
                                            <td class="{{ VC::TX_END }} totalDebit">0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <input type="button"
                       value="{{ __('Cancel') }}"
                       class="{{ VC::BT_LG }}"
                       data-index-url="{{ $indexUrl }}"
                       data-guard-msg="{{ base64_encode($indexGuard) }}"
                       data-navigate-to="{{ $indexUrl }}">
                <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
            </div>
        {{ Form::close() }}
    @else
        <p>{{ __('No journal entry found.') }}</p>
    @endif
@endsection

@push(ST::ADM_SCR_PG)
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/journalEntries/edit.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/journalEntries/generateEdit.js') }}"></script>
    <script defer src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script defer src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/journalEntries/lang/edit.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/journalEntries/shared/repeater-utils.js') }}"></script>
    <script defer>
        (() => {
            "use strict";

            /* CSP-safe handler for [data-navigate-to] */
            document.addEventListener("click", (e) => {
                const trigger = e.target.closest("[data-navigate-to]");
                if (!trigger) return;
                e.preventDefault();
                const url = trigger.getAttribute("data-navigate-to");
                if (url) location.href = url;
            });

            try {
                if (typeof JournalEntryRepeater !== "undefined") {
                    JournalEntryRepeater.initRepeater({
                        selector: "body",
                        maxUploadSize: "{{ SettingsConstants::MAX_U_SIZE_DEF ?? '2048' }}",
                        destroyRoute: "{{ route(VW::JRN.'.account.destroy') }}",
                        isEditMode: true
                    });
                }
            } catch (e) {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");
            }
        })();
    </script>
@endpush
