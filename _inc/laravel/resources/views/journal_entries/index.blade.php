@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        PermissionsConstants as PERM,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};

    $user                 = Auth::user();
    $hasFetchUserLang     = is_callable([Utility::class, 'fetchUserLang']);
    $hasFetchLinkMessage  = is_callable([Utility::class, 'fetchLinkMessage']);
    $lang                 = $hasFetchUserLang ? Utility::fetchUserLang(user: $user) : app()->getLocale();

    $hasDateFormat             = is_object($user) && method_exists($user, 'dateFormat');
    $hasTimeFormat             = is_object($user) && method_exists($user, 'timeFormat');
    $hasPriceFormat            = is_object($user) && method_exists($user, 'priceFormat');
    $hasJournalNumberFormat    = is_object($user) && method_exists($user, 'journalNumberFormat');
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Journal Entry') }}
@endsection

@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Journal Entry') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can(PERM::CR_JNL)
            @php
                $createBase = VW::JRN_ET.'.create';
                $createUrl  = Route::has($createBase) ? route($createBase) : '#';
                $createMsg  = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::JRN_ET, 'create_journal_entry_unavailable') : null)
                              ?? __('Create journal entry route is unavailable. Please contact technical support or your domain administrator.');
            @endphp
            <a  href="{{ $createUrl }}"
                data-url="{{ $createUrl }}"
                data-ajax-popup="true"
                data-title="{{ __('Create New Journal') }}"
                data-sv-localized="true"
                data-guard-msg="{{ $createMsg }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YW::ADM_CTT)
    @if(!$user)
        <div class="{{ VC::CD }} {{ VC::MB3 }}">
            <div class="card-body">
                <div class="alert alert-warning mb-0" role="alert">{{ __('The current user context was not available; data could not be formatted or displayed.') }}</div>
            </div>
        </div>
    @else
        <div class="{{ VC::RW }}">
            <div class="col-xl-12">
                <div class="{{ VC::CD }}">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="{{ VC::TB }} datatable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Journal ID') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th width="10%">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($journalEntries as $entry)
                                        @php
                                            $eid            = isset($entry->id) ? (string)$entry->id : '';
                                            $rawDate        = $entry->date ?? null;
                                            $dateTxt        = $rawDate
                                                ? ($hasDateFormat ? $user->dateFormat($rawDate) : __('Failed to format date.'))
                                                : __('Date was not available.');
                                            $hasTotalCredit = is_object($entry) && method_exists($entry, 'totalCredit');
                                            $amountRaw      = $hasTotalCredit ? $entry->totalCredit() : null;
                                            $amountTxt      = $amountRaw !== null
                                                ? ($hasPriceFormat ? $user->priceFormat($amountRaw) : number_format((float)$amountRaw, 2))
                                                : __('Amount total was not available.');
                                            $descTxt        = isset($entry->description) && $entry->description !== '' ? $entry->description : '-';
                                        @endphp
                                        <tr>
                                            @can(PERM::SHW_JNL)
                                                @php
                                                    $showBase = VW::JRN_ET.'.show';
                                                    $showUrl  = (Route::has($showBase) && $eid !== '') ? route($showBase, $eid) : '#';
                                                    $showMsg  = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::JRN_ET, 'show_journal_entry_unavailable') : null)
                                                                ?? __('Show journal entry route is unavailable. Please contact technical support or your domain administrator.');
                                                    $jnRaw    = $entry->journal_id ?? null;
                                                    $jnTxt    = $jnRaw !== null
                                                        ? ($hasJournalNumberFormat ? $user->journalNumberFormat($jnRaw) : __('Failed to format journal number.'))
                                                        : __('Journal number was not available.');
                                                @endphp
                                                <td class="Id">
                                                    <a  href="{{ $showUrl }}"
                                                        data-url="{{ $showUrl }}"
                                                        data-guard-msg="{{ $showMsg }}"
                                                        data-sv-localized="true"
                                                        class="{{ VC::BT_OUTPM }}">
                                                        {{ $jnTxt }}
                                                    </a>
                                                </td>
                                            @else
                                                @php
                                                    $jnRaw    = $entry->journal_id ?? null;
                                                    $jnTxt    = $jnRaw !== null
                                                        ? ($hasJournalNumberFormat ? $user->journalNumberFormat($jnRaw) : __('Failed to format journal number.'))
                                                        : __('Journal number was not available.');
                                                @endphp
                                                <td class="Id">{{ $jnTxt }}</td>
                                            @endcan

                                            <td>{{ $dateTxt }}</td>
                                            <td>{{ $amountTxt }}</td>
                                            <td>{{ $descTxt }}</td>
                                            <td class="Action">
                                                <span>
                                                    @can(PERM::ED_JNL)
                                                        @php
                                                            $editBase = VW::JRN_ET.'.edit';
                                                            $editUrl  = (Route::has($editBase) && $eid !== '') ? route($editBase, [$eid]) : '#';
                                                            $editMsg  = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::JRN_ET, 'edit_journal_entry_unavailable') : null)
                                                                        ?? __('Edit journal entry route is unavailable. Please contact technical support or your domain administrator.');
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a  href="{{ $editUrl }}"
                                                                class="{{ VC::BT_SM_CT }}"
                                                                data-url="{{ $editUrl }}"
                                                                data-guard-msg="{{ $editMsg }}"
                                                                data-sv-localized="true"
                                                                data-ajax-popup="true"
                                                                data-title="{{ __('Edit Journal Entry') }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Edit') }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can(PERM::DEL_JNL)
                                                        @php
                                                            $destroyBase = VW::JRN_ET.'.destroy';
                                                            $destroyUrl  = (Route::has($destroyBase) && $eid !== '') ? route($destroyBase, $eid) : '#';
                                                            $destroyMsg  = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::JRN_ET, 'destroy_journal_entry_unavailable') : null)
                                                                            ?? __('Delete journal entry route is unavailable. Please contact technical support or your domain administrator.');
                                                            $delFormId   = 'delete-journal-entry-form-'.($eid === '' ? 'x' : $eid);
                                                            $confirmTitle = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? 'Are You Sure?';
                                                            $confirmBody  = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? 'This action can not be undone. Do you want to continue?';
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            {!! Form::open([
                                                                'method'            => 'DELETE',
                                                                'url'               => $destroyUrl,
                                                                'id'                => $delFormId,
                                                                'data-url'          => $destroyUrl,
                                                                'data-guard-msg'    => $destroyMsg,
                                                                'data-sv-localized' => 'true',
                                                            ]) !!}
                                                                <a  href="#"
                                                                    class="{{ VC::BT_SM_CT_PR }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Delete') }}"
                                                                    data-confirm="{{ __($confirmTitle) }}|{{ __($confirmBody) }}"
                                                                    data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();">
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push(ST::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/journalEntries/lang/index.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/journalEntries/index.js') }}"></script>
@endpush
