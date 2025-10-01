@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YD
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Collection;

    $user = Auth::user();

    $hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);
    $lang = is_callable([Utility::class, 'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
@endphp

@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Manage Loan Option') }}
@endsection

@section(YD::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Loan Option') }}</li>
@endsection

@section(YD::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create loan option')
            @php
                $createUrl = Route::has(VW::LN_OPT.'.create') ? route(VW::LN_OPT.'.create') : '#';
                $createGuard = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LN_OPT, 'create_loan_option_unavailable') : null)
                    ?? __('Create Loan Option route is unavailable. Please contact technical support or your domain administrator.');
            @endphp
            <a href="{{ $createUrl }}"
               class="{{ VC::BT_SM_PM }}"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-title="{{ __('Create New Loan Option') }}"
               data-sv-localized="true"
               data-guard-msg="{{ $createGuard }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YD::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C3 }}">
            @include('layouts.hrm_setup')
        </div>
        <div class="{{ VC::C9 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Loan Option') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @php
                                    $list = [];
                                    if (is_array($loanoptions ?? null) && count($loanoptions ?? []) > 0) {
                                        $list = $loanoptions;
                                    } elseif (($loanoptions ?? null) instanceof Collection && $loanoptions->isNotEmpty()) {
                                        $list = $loanoptions;
                                    }
                                @endphp

                                @forelse ($list as $loanoption)
                                    @php
                                        $name = isset($loanoption->name) && $loanoption->name !== ''
                                            ? $loanoption->name
                                            : __('Loan option name was not available.');

                                        $id = $loanoption->id ?? null;
                                    @endphp
                                    <tr>
                                        <td>{{ $name }}</td>
                                        <td>
                                            @can('edit loan option')
                                                @php
                                                    $editUrl = ($id !== null && Route::has(VW::LN_OPT.'.edit'))
                                                        ? route(VW::LN_OPT.'.edit', $id)
                                                        : '#';
                                                    $editGuard = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LN_OPT, 'edit_loan_option_unavailable') : null)
                                                        ?? __('Edit Loan Option route is unavailable. Please contact technical support or your domain administrator.');
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a href="#"
                                                       class="{{ VC::BT_SM_CT }}"
                                                       data-url="{{ $editUrl }}"
                                                       data-ajax-popup="true"
                                                       data-title="{{ __('Edit Loan Option') }}"
                                                       data-sv-localized="true"
                                                       data-guard-msg="{{ $editGuard }}"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('Edit') }}"
                                                       data-original-title="{{ __('Edit') }}">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete loan option')
                                                @php
                                                    $formId   = 'delete-loanoption-'.$id;
                                                    $delUrl   = ($id !== null && Route::has(VW::LN_OPT.'.destroy'))
                                                        ? route(VW::LN_OPT.'.destroy', $id)
                                                        : '#';
                                                    $delGuard = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LN_OPT, 'delete_loan_option_unavailable') : null)
                                                        ?? __('Delete Loan Option route is unavailable. Please contact technical support or your domain administrator.');
                                                    $confirmA = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? __('Are You Sure?');
                                                    $confirmB = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? __('This action can not be undone. Do you want to continue?');
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open([
                                                        'method'            => 'DELETE',
                                                        'url'               => $delUrl,
                                                        'id'                => $formId,
                                                        'data-url'          => $delUrl,
                                                        'data-sv-localized' => 'true',
                                                        'data-guard-msg'    => $delGuard,
                                                    ]) !!}
                                                        <a href="#"
                                                           class="{{ VC::BT_SM_CT_PR }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-original-title="{{ __('Delete') }}"
                                                           data-confirm="{{ __($confirmA) }}|{{ __($confirmB) }}"
                                                           data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">
                                            {{ __('Loan options data was not available or failed to be fetched.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/loans/options/index.js') }}"></script>
@endpush
