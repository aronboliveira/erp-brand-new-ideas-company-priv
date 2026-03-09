@php
    $aiEnabled ??= false;
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);

        $formId     = 'jrn-et-store-form';
        $storeBase  = VW::JRN_ET;
        $storeKebab = Str::kebab($storeBase);
        $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
        $storeUrl   = $storeRes ? route($storeRes) : '#';
        $storeGuard = Utility::fetchLinkMessage($lang, VW::JRN_ET, 'store_route_unavailable') ?? __('Journal entry store route is unavailable. Please contact technical support or your domain administrator.');

        $planUser   = $user && method_exists($user, 'creatorId') ? User::find($user->creatorId()) : null;
        $plan       = Plan::getPlan($planUser?->plan);
        $aiEnabled  = (int) data_get($plan, PlansConstants::COL_GPT, 0) === 1;

        $aiContextId = (string) ($user?->creatorId() ?? $user?->id ?? '0');
        $genBase     = 'generate';
        $genKebab    = Str::kebab($genBase);
        $genRes      = Route::has($genBase) ? $genBase : (Route::has($genKebab) ? $genKebab : null);
        $genUrl      = $genRes ? route($genRes, [$aiContextId]) : '#';
        $genGuard    = Utility::fetchLinkMessage($lang, VW::JRN_ET, 'generate_route_unavailable') ?? __('Generate content route is unavailable. Please contact technical support or your domain administrator.');

        $indexBase   = VW::JRN_ET . '.index';
        $indexKebab  = Str::kebab($indexBase);
        $indexRes    = Route::has($indexBase) ? $indexBase : (Route::has($indexKebab) ? $indexKebab : null);
        $indexUrl    = $indexRes ? route($indexRes) : '#';
        $indexGuard  = Utility::fetchLinkMessage($lang, VW::JRN_ET, 'index_route_unavailable') ?? __('Journal entries index route is unavailable. Please contact technical support or your domain administrator.');
    } catch (\Throwable $e) {
        \Log::error('journal_entries/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Journal Entry Create') }}
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
    @if($aiEnabled)
        <div class="{{ VC::FEND }}">
            <a  href="{{ $genUrl }}"
                data-size="md"
                data-ajax-popup-over="true"
                data-url="{{ $genUrl }}"
                data-guard-msg="{{ base64_encode($genGuard) }}"
                data-bs-placement="top"
                data-title="{{ __('Generate content with AI') }}"
                class="ai-btn btn btn-icon {{ VC::BT_SM_PM }}">
                <i class="{{ VC::FAS_RB }}"></i>
                <span>{{ __('Generate with AI') }}</span>
            </a>
        </div>
    @endif
@endsection

@section(YW::ADM_CTT)
    {{ Form::open([
        'url'               => $storeUrl,
        'class'             => 'w-100',
        'id'                => $formId,
        'data-url'          => $storeUrl,
        'data-guard-msg'    => $storeGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <div class="{{ VC::RW }} {{ VC::MT4 }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::P4 }}">
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CLM4 }}">
                                <div class="{{ VC::FM_G }}">
                                    {{ Form::label('journal_number', __('Journal Number'), ['class' => VC::FM_LB]) }}
                                    <input type="text" class="{{ VC::FM_CT }}" value="{{ $user?->journalNumberFormat($journalId) ?? __('Journal number unavailable') }}" readonly>
                                </div>
                            </div>
                            <div class="{{ VC::CLM4 }}">
                                <div class="{{ VC::FM_G }}">
                                    {{ Form::label('date', __('Transaction Date'), ['class' => VC::FM_LB]) }}
                                    {{ Form::date('date', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="{{ VC::CLM4 }}">
                                <div class="{{ VC::FM_G }}">
                                    {{ Form::label('reference', __('Reference'), ['class' => VC::FM_LB]) }}
                                    {{ Form::text('reference', '', ['class' => VC::FM_CT]) }}
                                </div>
                            </div>
                            <div class="{{ VC::CLM6 }} {{ VC::CM6 }}">
                                <div class="{{ VC::FM_G }}">
                                    {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                                    {{ Form::textarea('description', '', ['class' => VC::FM_CT, 'rows' => 2]) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="card repeater">
                    <div class="item-section {{ VC::PY2 }} {{ VC::PX3 }}">
                        <div class="{{ VC::R_FLX_ALC_JCE }}">
                            <a href="#" data-repeater-create class="{{ VC::BT_PRM }} {{ VC::ME3 }}" data-toggle="modal" data-target="#add-bank">
                                <i class="{{ VC::TI_PLS }}"></i> {{ __('Add Accounts') }}
                            </a>
                        </div>
                    </div>
                    <div class="{{ VC::CD_BD_TB_BD }}">
                        <div class="{{ VC::TB_RSP }}">
                            <table class="{{ VC::TB }} {{ VC::MB0 }}" data-repeater-list="accounts" id="sortable-table">
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
                                        <td width="25%" class="{{ VC::FM_G }} pt-0">
                                            {{ Form::select('account', $accounts, '', ['class' => VC::FM_CT . ' js-searchBox', 'required' => 'required']) }}
                                        </td>
                                        <td>
                                            <div class="{{ VC::FM_G }} price-input">
                                                {{ Form::text('debit', '', ['class' => VC::FM_CT . ' debit', 'required' => 'required', 'placeholder' => __('Debit')]) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="{{ VC::FM_G }} price-input">
                                                {{ Form::text('credit', '', ['class' => VC::FM_CT . ' credit', 'required' => 'required', 'placeholder' => __('Credit')]) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::text('description', '', ['class' => VC::FM_CT, 'placeholder' => __('Description')]) }}
                                            </div>
                                        </td>
                                        <td class="{{ VC::TX_END }} amount">0.00</td>
                                        <td>
                                            <a href="#" class="{{ VC::TI_TRS_ALT }}" data-repeater-delete></a>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td></td>
                                        <td class="{{ VC::TX_END }}">
                                            <strong>{{ __('Total Credit') }} ({{ $user?->currencySymbol() ?? __('Currency unavailable') }})</strong>
                                        </td>
                                        <td class="{{ VC::TX_END }} totalCredit">0.00</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td class="{{ VC::TX_END }}">
                                            <strong>{{ __('Total Debit') }} ({{ $user?->currencySymbol() ?? __('Currency unavailable') }})</strong>
                                        </td>
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
                    class="{{ VC::BT_LG }} cancel-link"
                    data-href="{{ $indexUrl }}"
                    data-guard-msg="{{ base64_encode($indexGuard) }}">
                {{ __('Cancel') }}
            </input>
            <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
        </div>
    {{ Form::close() }}
@endsection

@push(ST::ADM_SCR_PG)
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/journalEntries/lang/create.js') }}"></script>
    <script defer src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script defer src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/journalEntries/shared/repeater-utils.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/journalEntries/store.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/journalEntries/cancel.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/journalEntries/generateStore.js') }}"></script>
    <script defer>
        (() => {
            "use strict";
            try {
                if (typeof JournalEntryRepeater !== "undefined") {
                    JournalEntryRepeater.initRepeater({
                        selector: "body",
                        maxUploadSize: "{{ SettingsConstants::MAX_U_SIZE_DEF ?? '2048' }}",
                        isEditMode: false
                    });
                }
            } catch (e) {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("Initialization failed", e);
            }
        })();
    </script>
@endpush
