@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\ViewsConstants;

    $lang = Utility::fetchUserLang();
    $createName     = ViewsConstants::CPN_PL . '.create';
    $createRoute    = Route::has($createName)
        ? route($createName)
        : (Route::has(Str::kebab($createName))
            ? route(Str::kebab($createName))
            : '#');
    $createGuardMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CPN_PL,
        'company_policy_create_route_unavailable'
    ) ?? 'Create Company Policy route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Company Policy') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Company Policy') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create company policy')
            <a
                href="#"
                id="createPolicyBtn"
                data-url="{{ $createRoute }}"
                data-guard-msg="{{ $createGuardMsg }}"
                data-listener-alias="create-policy"
                data-ajax-popup="true"
                data-title="{{ __('Create New Company Policy') }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ VC::BT_SM }} {{ VC::BT_PRM }}"
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
                            $policies = ((is_array($companyPolicy ?? null) && count($companyPolicy ?? [])) || (($companyPolicy ?? null) instanceof Collection && ($companyPolicy)->isNotEmpty())) ? $companyPolicy : [];
                            $basePath = Utility::getFile('uploads/companyPolicy') ?: '';
                        @endphp
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Attachment') }}</th>
                                    @if(Gate::check('edit company policy') || Gate::check('delete company policy'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @forelse($policies as $policy)
                                    @php
                                        $pid = data_get($policy,'id');
                                        $branchName = data_get($policy,'branches.name') ?: __('No branch name available');
                                        $title = data_get($policy,'title') ?: __('No title available');
                                        $desc = data_get($policy,'description') ?: __('No description available');
                                        $attachment = data_get($policy,'attachment');
                                        $fileUrl = ($attachment && $basePath) ? ($basePath.'/'.$attachment) : null;
                                    @endphp
                                    <tr>
                                        <td>{{ $branchName }}</td>
                                        <td>{{ $title }}</td>
                                        <td>{{ $desc }}</td>
                                        <td>
                                            @if($fileUrl)
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a href="{{ $fileUrl }}" download class="{{ VC::BT_SM }} {{ VC::AL_IT_CT }}"><i class="ti ti-download {{ VC::TXT_WT }}"></i></a>
                                                </div>
                                                <div class="{{ VC::ACT_BTN }} bg-secondary ms-2">
                                                    <a href="{{ $fileUrl }}" target="_blank" class="{{ VC::BT_SM }} {{ VC::AL_IT_CT }}" data-bs-toggle="tooltip" title="{{ __('Preview') }}"><i class="ti ti-crosshair {{ VC::TXT_WT }}"></i></a>
                                                </div>
                                            @else
                                                <p>{{ __('No attachment available') }}</p>
                                            @endif
                                        </td>
                                        @if(Gate::check('edit company policy') || Gate::check('delete company policy'))
                                            <td>
                                                @can('edit company policy')
                                                    @php
                                                        $editName = ViewsConstants::CPN_PL.'.edit';
                                                        $editRoute = (Route::has($editName) ? route($editName, $pid) : (Route::has(Str::kebab($editName)) ? route(Str::kebab($editName), $pid) : '#'));
                                                        $editGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::CPN_PL, 'company_policy_edit_route_unavailable') ?: __('Failed to get company policy edit route');
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a href="#" class="{{ VC::BT_SM }} {{ VC::AL_IT_CT }}" id="editPolicyBtn_{{ $pid }}" data-url="{{ $editRoute }}" data-guard-msg="{{ $editGuardMsg }}" data-listener-alias="edit-policy" data-ajax-popup="true" data-title="{{ __('Edit Company Policy') }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}"><i class="ti ti-pencil {{ VC::TXT_WT }}"></i></a>
                                                    </div>
                                                @endcan
                                                @can('delete company policy')
                                                    @php
                                                        $destroyName = ViewsConstants::CPN_PL.'.destroy';
                                                        $destroyRoute = (Route::has($destroyName) ? route($destroyName, $pid) : (Route::has(Str::kebab($destroyName)) ? route(Str::kebab($destroyName), $pid) : '#'));
                                                        $destroyGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::CPN_PL, 'company_policy_destroy_route_unavailable') ?: __('Failed to get company policy delete route');
                                                        $deleteFormId = 'delete-form-'.$pid;
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open(['method' => 'DELETE','url' => $destroyRoute,'id' => $deleteFormId,'data-url' => $destroyRoute,'data-guard-msg' => $destroyGuardMsg]) !!}
                                                        <a href="#" class="{{ VC::BT_SM_CT_PR }}" data-listener-alias="delete-policy" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();" data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i class="ti ti-trash {{ VC::TXT_WT }}"></i></a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ (Gate::check('edit company policy') || Gate::check('delete company policy')) ? 5 : 4 }}">
                                            <div class="{{ VC::RW }} {{ VC::JCC }} {{ VC::ALC }}">
                                                <div class="{{ VC::C6 }} {{ VC::TXCT }}">
                                                    <p class="{{ VC::TXSM }} {{ VC::TX_MUTED }}">{{ __('No company policies available') }}</p>
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
    <script defer src="{{ asset('assets/js/routes/companyPolicies/index.js') }}"></script>
@endpush
