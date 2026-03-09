@php
    try {
$user = Auth::user();

        $hasFetchUserLang    = is_callable([Utility::class, 'fetchUserLang']);
        $hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);
        $hasSettingsMethod   = is_callable([Utility::class, 'settings']);

        $lang      = $hasFetchUserLang ? Utility::fetchUserLang(user: $user) : app()->getLocale();
        $settings  = $hasSettingsMethod ? (Utility::settings() ?? []) : [];
        $hasSettingsData = Utility::isFilled($settings ?? []);

        $hasJournalNumberFormat = is_object($user) && method_exists($user, 'journalNumberFormat');
        $hasDateFormat          = is_object($user) && method_exists($user, 'dateFormat');
        $hasPriceFormat         = is_object($user) && method_exists($user, 'priceFormat');

            $idxBase = VW::JRN_ET . '.index';
        $idxUrl  = Route::has($idxBase) ? route($idxBase) : '#';
        $idxMsg  = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::JRN_ET, 'index_route_unavailable') : null)
                   ?? __('Journal Entry index route is unavailable. Please contact technical support or your domain administrator.');

            $jnRaw = $journalEntry->journal_id ?? null;
        $jnTxt = $jnRaw !== null
            ? ($hasJournalNumberFormat ? $user->journalNumberFormat($jnRaw) : __('Failed to format journal number.'))
            : __('Journal number was not available.');
        $refTxt = isset($journalEntry->reference) && $journalEntry->reference !== '' ? $journalEntry->reference : __('Reference not provided.');
        $dateRaw = $journalEntry->date ?? null;
        $dateTxt = $dateRaw
            ? ($hasDateFormat ? $user->dateFormat($dateRaw) : __('Failed to format journal date.'))
            : __('Journal date was not available.');

            $settingsError = $hasSettingsMethod ? null : __('Company settings could not be fetched.');
    } catch (\Throwable $e) {
        \Log::error('journal_entries/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Journal Detail') }}
@endsection

@section(YW::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">
        <a href="{{ $idxUrl }}"
           data-url="{{ $idxUrl }}"
           data-guard-msg="{{ base64_encode($idxMsg) }}"
           data-sv-localized="true">
            {{ __('Journal Entry') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ $jnTxt }}</li>
@endsection

@section(YW::ADM_CTT)
    @if(!$user)
        <div class="{{ VC::CD }} {{ VC::MB3 }}">
            <div class="{{ VC::CD_BD }}">
                <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">
                    {{ __('The current user context was not available; data could not be formatted or displayed.') }}
                </div>
            </div>
        </div>
    @else
        @if($settingsError)
            <div class="{{ VC::CD }} {{ VC::MB3 }}">
                <div class="{{ VC::CD_BD }}">
                    <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">{{ $settingsError }}</div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        <div class="invoice">
                            <div class="invoice-print">
                                <div class="row invoice-title {{ VC::MT2 }}">
                                    <div class="{{ VC::CXS12 }} {{ VC::CS12 }} col-nd-6 {{ VC::CL6 }} {{ VC::C12 }}">
                                        <h2>{{ __('Journal') }}</h2>
                                    </div>
                                    <div class="{{ VC::CXS12 }} {{ VC::CS12 }} col-nd-6 {{ VC::CL6 }} {{ VC::C12 }} {{ VC::TX_END }}">
                                        <h3 class="invoice-number">{{ $jnTxt }}</h3>
                                    </div>
                                    <div class="{{ VC::C12 }}">
                                        <hr>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="{{ VC::CM6 }}">
                                        <small class="font-style">
                                            <strong>{{ __('To') }} :</strong><br>
                                            {{ $hasSettingsData && !empty($settings['company_name']) ? $settings['company_name'] : __('Company name not available.') }}<br>
                                            {{ $hasSettingsData && !empty($settings['company_telephone']) ? $settings['company_telephone'] : __('Phone not available.') }}<br>
                                            {{ $hasSettingsData && !empty($settings['company_address']) ? $settings['company_address'] : __('Address not available.') }}<br>
                                            @php
                                                try {
                                                    $city  = $hasSettingsData && !empty($settings['company_city']) ? $settings['company_city'] : null;
                                                    $state = $hasSettingsData && !empty($settings['company_state']) ? $settings['company_state'] : null;
                                                    $country = $hasSettingsData && !empty($settings['company_country']) ? $settings['company_country'] : null;
                                                    $loc = array_filter([$city, $state, $country], fn($v) => $v !== null && $v !== '');
                                                    $locTxt = !empty($loc) ? implode(', ', $loc) : __('Location not available.');
                                                } catch (\Throwable $e) {
                                                    \Log::error('journal_entries/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            {{ $locTxt }}
                                        </small>
                                    </div>
                                    <div class="{{ VC::CM6 }} {{ VC::TX_END }}">
                                        <small>
                                            <strong>{{ __('Journal No') }} :</strong>
                                            {{ $jnTxt }}
                                        </small><br>
                                        <small>
                                            <strong>{{ __('Journal Ref') }} :</strong>
                                            {{ $refTxt }}
                                        </small> <br>
                                        <small>
                                            <strong>{{ __('Journal Date') }} :</strong>
                                            {{ $dateTxt }}
                                        </small>
                                    </div>
                                </div>

                                <div class="row {{ VC::MT4 }}">
                                    <div class="{{ VC::CM12 }}">
                                        <div class="font-weight-bold">{{ __('Journal Account Summary') }}</div>
                                        <div class="{{ VC::TB_RSP }} {{ VC::MT2 }}">
                                            <table class="{{ VC::TB_MB0 }}">
                                                <tr>
                                                    <th data-width="40" class="{{ VC::TX_DK }}">#</th>
                                                    <th class="{{ VC::TX_DK }}">{{ __('Account') }}</th>
                                                    <th class="{{ VC::TX_DK }}" width="25%">{{ __('Description') }}</th>
                                                    <th class="{{ VC::TX_DK }}">{{ __('Debit') }}</th>
                                                    <th class="{{ VC::TX_DK }}">{{ __('Credit') }}</th>
                                                    <th class="{{ VC::TX_DK }}">{{ __('Amount') }}</th>
                                                    <th></th>
                                                </tr>

                                                @foreach($accounts as $key => $account)
                                                    @php
                                                        try {
                                                            $accIdx  = (int)$key + 1;
                                                            $accName = (!empty($account->accounts) && isset($account->accounts->code, $account->accounts->name))
                                                                ? ($account->accounts->code.' - '.$account->accounts->name)
                                                                : __('Account not available.');
                                                            $accDesc = isset($account->description) && $account->description !== '' ? $account->description : '-';

                                                            $debit   = isset($account->debit)  ? (float)$account->debit  : 0.0;
                                                            $credit  = isset($account->credit) ? (float)$account->credit : 0.0;

                                                            $debitTxt  = $hasPriceFormat ? $user->priceFormat($debit)  : number_format($debit, 2);
                                                            $creditTxt = $hasPriceFormat ? $user->priceFormat($credit) : number_format($credit, 2);
                                                            $amt       = $debit != 0 ? $debit : $credit;
                                                            $amtTxt    = $hasPriceFormat ? $user->priceFormat($amt) : number_format($amt, 2);

                                                            $aid   = isset($account->id) ? (string)$account->id : '';
                                                            $formId = 'delete-form-'.($aid === '' ? 'x' : $aid);
                                                            $linkId = 'delete-link-'.($aid === '' ? 'x' : $aid);

                                                            $destroyBase = VW::JRN . '.destroy';
                                                            $destroyUrl  = (Route::has($destroyBase) && $aid !== '')
                                                                ? route($destroyBase, $aid)
                                                                : '#';
                                                            $destroyMsg  = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::JRN, 'destroy_journal_line_unavailable') : null)
                                                                           ?? __('Delete route is unavailable. Please contact technical support or your domain administrator.');
                                                        } catch (\Throwable $e) {
                                                            \Log::error('journal_entries/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <tr>
                                                        <td>{{ $accIdx }}</td>
                                                        <td>{{ $accName }}</td>
                                                        <td>{{ $accDesc }}</td>
                                                        <td>{{ $debitTxt }}</td>
                                                        <td>{{ $creditTxt }}</td>
                                                        <td>{{ $amtTxt }}</td>
                                                        <td>
                                                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                {!! Form::open([
                                                                    'method'            => 'DELETE',
                                                                    'url'               => $destroyUrl,
                                                                    'id'                => $formId,
                                                                    'data-url'          => $destroyUrl,
                                                                    'data-guard-msg'    => $destroyMsg,
                                                                    'data-sv-localized' => 'true',
                                                                ]) !!}
                                                                    <a  href="#"
                                                                        id="{{ $linkId }}"
                                                                        class="{{ VC::TRS_PARA }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Delete') }}"
                                                                        data-confirm="{{ __( ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? 'Are You Sure?' ) }}|{{ __( ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? 'This action can not be undone. Do you want to continue?' ) }}"
                                                                        data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach

                                                @php
                                                    try {
                                                        $hasTotalCredit = is_object($journalEntry) && method_exists($journalEntry, 'totalCredit');
                                                        $hasTotalDebit  = is_object($journalEntry) && method_exists($journalEntry, 'totalDebit');
                                                        $totCredRaw = $hasTotalCredit ? $journalEntry->totalCredit() : null;
                                                        $totDebtRaw = $hasTotalDebit  ? $journalEntry->totalDebit()  : null;

                                                        $totCredTxt = $totCredRaw !== null
                                                            ? ($hasPriceFormat ? $user->priceFormat($totCredRaw) : number_format((float)$totCredRaw, 2))
                                                            : __('Total credit could not be calculated.');
                                                        $totDebtTxt = $totDebtRaw !== null
                                                            ? ($hasPriceFormat ? $user->priceFormat($totDebtRaw) : number_format((float)$totDebtRaw, 2))
                                                            : __('Total debit could not be calculated.');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('journal_entries/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="4"></td>
                                                        <td><b>{{ __('Total Credit') }}</b></td>
                                                        <td>{{ $totCredTxt }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="4"></td>
                                                        <td><b>{{ __('Total Debit') }}</b></td>
                                                        <td>{{ $totDebtTxt }}</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <div class="font-bold {{ VC::MT2 }}">{{ __('Description') }} :</div>
                                        <small>{{ isset($journalEntry->description) && $journalEntry->description !== '' ? $journalEntry->description : __('No description found.') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push(ST::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/journalEntries/lang/index.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/journalEntries/view.js') }}"></script>
@endpush
