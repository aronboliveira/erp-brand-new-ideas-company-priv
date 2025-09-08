@php
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
        StacksConstants
    };

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
    $createName     = ViewsConstants::CPL . '.create';
    $createRoute    = Route::has($createName)
        ? route($createName)
        : '#';
    $createGuardMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CPL,
        'complaint_create_route_unavailable'
    ) ?? 'Create Complaint route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Complain') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled=\"true\"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Complain') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create complaint')
            <a
                href="#"
                id="createComplaintBtn"
                data-url="{{ $createRoute }}"
                data-guard-msg="{{ $createGuardMsg }}"
                data-listener-alias="create-complaint"
                data-ajax-popup="true"
                data-title="{{ __('Create New Complaint') }}"
                data-size="lg"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ VC::BT_SM_PM }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD }}-body table-border-style">
                    <div class="table-responsive">
                        @php
                            $isDateFormatAvailable = method_exists($user,'dateFormat');
                            $rows = ((is_array($complaints ?? null) && count($complaints ?? [])) || (($complaints ?? null) instanceof Collection && ($complaints)->isNotEmpty())) ? $complaints : [];
                        @endphp
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Complaint From') }}</th>
                                    <th>{{ __('Complaint Against') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Complaint Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    @if(Gate::check('edit complaint') || Gate::check('delete complaint'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @forelse ($rows as $complaint)
                                    @php
                                        $cid = data_get($complaint,'id');
                                        $fromName = data_get($complaint,'complaintFrom.name') ?: __('No complainant available');
                                        $againstName = data_get($complaint,'complaintAgainst.name') ?: __('No respondent available');
                                        $title = data_get($complaint,'title') ?: __('No title available');
                                        $desc = data_get($complaint,'description') ?: __('No description available');
                                        $rawDate = data_get($complaint,'complaint_date');
                                        $dateOut = $rawDate ? ($isDateFormatAvailable ? $user?->dateFormat($rawDate) : (string)$rawDate) : __('No complaint date available');
                                    @endphp
                                    <tr>
                                        <td>{{ $fromName }}</td>
                                        <td>{{ $againstName }}</td>
                                        <td>{{ $title }}</td>
                                        <td>{{ $dateOut }}</td>
                                        <td>{{ $desc }}</td>
                                        @if(Gate::check('edit complaint') || Gate::check('delete complaint'))
                                            <td>
                                                @can('edit complaint')
                                                    @php
                                                        $editName = ViewsConstants::CPL.'.edit';
                                                        $editRoute = (Route::has($editName) ? route($editName, $cid) : (Route::has(Str::kebab($editName)) ? route(Str::kebab($editName), $cid) : '#'));
                                                        $editGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::CPL, 'complaint_edit_route_unavailable') ?: __('Failed to get complaint edit route');
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a href="#" id="editComplaintBtn_{{ $cid ?: 'na' }}" data-url="{{ $editRoute }}" data-guard-msg="{{ $editGuardMsg }}" data-listener-alias="edit-complaint" data-ajax-popup="true" data-title="{{ __('Edit Complaint') }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}" class="{{ VC::DFL_IL_VC }}"><i class="{{ VC::TI_PC_WT }}"></i></a>
                                                    </div>
                                                @endcan
                                                @can('delete complaint')
                                                    @php
                                                        $destroyName = ViewsConstants::CPL.'.destroy';
                                                        $destroyRoute = (Route::has($destroyName) ? route($destroyName, $cid) : (Route::has(Str::kebab($destroyName)) ? route(Str::kebab($destroyName), $cid) : '#'));
                                                        $destroyGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::CPL, 'complaint_destroy_route_unavailable') ?: __('Failed to get complaint delete route');
                                                        $deleteFormId = 'delete-form-'.($cid ?: 'na');
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Collective\Html\FormFacade::open(['method' => 'DELETE','url' => $destroyRoute,'id' => $deleteFormId,'data-url' => $destroyRoute,'data-guard-msg' => $destroyGuardMsg]) !!}
                                                            <a href="#" class="{{ VC::BT_SM_CT_PR }}" data-listener-alias="delete-complaint" data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?: __('Are You Sure?')) }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?: __('This action can not be undone. Do you want to continue?')) }}" data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"><i class="{{ VC::TI_TRS_WT }}"></i></a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ Gate::check('edit complaint') || Gate::check('delete complaint') ? 6 : 5 }}">
                                            <div class="{{ VC::RW }} {{ VC::JCC }} {{ VC::ALC }}">
                                                <div class="{{ VC::C6 }} {{ VC::TXCT }}">
                                                    <p class="{{ VC::TXSM }} {{ VC::TX_MUTED }}">{{ __('No complaints available') }}</p>
                                                </div>
                                            </div>
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

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/complaints/index.js') }}"></script>
@endpush
