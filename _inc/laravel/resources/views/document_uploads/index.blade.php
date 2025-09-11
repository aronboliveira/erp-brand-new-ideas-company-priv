@php
    use App\Config\Constants\{ExtendingLayoutsConstants as EL, StacksConstants as ST, ViewsConstants as VW, ViewClassNamesConstants as VC, YieldingConstants as YW};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Route, Gate};
    use Illuminate\Support\{Collection, Str};
    use Spatie\Permission\Models\Role;

    $lang = Utility::fetchUserLang();
@endphp
@extends(EL::ADM)
@section(YW::ADM_PG_TTL)
    {{ __('Manage Document') }}
@endsection
@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item">{{ __('Document') }}</li>
@endsection
@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create document')
            @php
                $docCreateBase = VW::DOC_UP.'.create';
                $docCreateKeb  = Str::kebab($docCreateBase);
                $docCreateName = Route::has($docCreateBase) ? $docCreateBase : (Route::has($docCreateKeb) ? $docCreateKeb : null);
                $docCreateUrl  = $docCreateName ? route($docCreateName) : '#';
                $docCreateMsg  = Utility::fetchLinkMessage($lang, VW::DOC_UP, 'create_document_route_unavailable') ?? 'Create document route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a id="document-create-btn" href="{{ $docCreateUrl }}" data-url="{{ $docCreateUrl }}" data-guard-msg="{{ $docCreateMsg }}" data-sv-localized="true" data-ajax-popup="true" data-title="{{ __('Create New Document') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}" class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection
@section(YW::ADM_CTT)
    @php
        $docList    = (is_array($documents ?? null) && count($documents ?? [])) || (($documents ?? null) instanceof Collection && $documents->isNotEmpty()) ? $documents : [];
        $hasActions = Gate::check('edit document') || Gate::check('delete document');
        $canGetFile = method_exists(Utility::class, 'getFile');
        $documentBase = $canGetFile ? Utility::getFile('uploads/documentUpload') : asset('storage/uploads/documentUpload');
        $canFindRole = class_exists(Role::class) && method_exists(Role::class, 'find');
        $confirmTitle = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?';
        $confirmBody  = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
    @endphp
    <div class="{{ VC::RW }}">
        <div class="col-3">@include('layouts.hrm_setup')</div>
        <div class="col-9">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Document') }}</th>
                                    <th>{{ __('Role') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    @if($hasActions)
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @forelse ($docList as $document)
                                    @php
                                        $docName = (string) ($document->name ?? __('No name available'));
                                        $docFile = (string) ($document->document ?? '');
                                        $hasDoc  = $docFile !== '';
                                        $fileUrl = $hasDoc ? $documentBase.'/'.$docFile : '#';
                                        $roleName = 'All';
                                        if ($canFindRole && !empty($document->role)) {
                                            $roleObj = Role::find($document->role);
                                            if ($roleObj && isset($roleObj->name)) $roleName = $roleObj->name;
                                        }
                                        $desc = (string) ($document->description ?? __('No description available'));
                                        $did  = (string) ($document->id ?? '');
                                    @endphp
                                    <tr>
                                        <td>{{ $docName }}</td>
                                        <td>
                                            @if($hasDoc)
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a class="{{ VC::BT_SM_CT }}" href="{{ $fileUrl }}" download><i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }}"></i></a>
                                                </div>
                                                <div class="action-btn bg-secondary ms-2">
                                                    <a class="{{ VC::BT_SM_CT }}" href="{{ $fileUrl }}" target="_blank"><i class="ti ti-crosshair {{ VC::TXT_WT }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Preview') }}"></i></a>
                                                </div>
                                            @else
                                                <p>-</p>
                                            @endif
                                        </td>
                                        <td>{{ $roleName }}</td>
                                        <td>{{ $desc }}</td>
                                        @if($hasActions)
                                            <td>
                                                @can('edit document')
                                                    @php
                                                        $docEditBase = VW::DOC_UP.'.edit';
                                                        $docEditKeb  = Str::kebab($docEditBase);
                                                        $docEditName = Route::has($docEditBase) ? $docEditBase : (Route::has($docEditKeb) ? $docEditKeb : null);
                                                        $docEditUrl  = ($docEditName && $did !== '') ? route($docEditName, [$did]) : '#';
                                                        $docEditMsg  = Utility::fetchLinkMessage($lang, VW::DOC_UP, 'edit_document_route_unavailable') ?? 'Edit document route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a id="document-edit-btn-{{ $did }}" href="{{ $docEditUrl }}" data-url="{{ $docEditUrl }}" data-guard-msg="{{ $docEditMsg }}" data-sv-localized="true" data-size="lg" data-ajax-popup="true" data-title="{{ __('Edit Document') }}" class="{{ VC::BT_SM_CT }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}"><i class="{{ VC::TI_PC_WT }}"></i></a>
                                                    </div>
                                                @endcan
                                                @can('delete document')
                                                    @php
                                                        $docDestroyBase = VW::DOC_UP.'.destroy';
                                                        $docDestroyKeb  = Str::kebab($docDestroyBase);
                                                        $docDestroyName = Route::has($docDestroyBase) ? $docDestroyBase : (Route::has($docDestroyKeb) ? $docDestroyKeb : null);
                                                        $docDestroyUrl  = ($docDestroyName && $did !== '') ? route($docDestroyName, [$did]) : '#';
                                                        $docDestroyMsg  = Utility::fetchLinkMessage($lang, VW::DOC_UP, 'destroy_document_route_unavailable') ?? 'Delete document route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {{ Form::open(['method' => 'DELETE', 'url' => $docDestroyUrl, 'id' => 'delete-form-'.$did]) }}
                                                            <a id="delete-document-btn-{{ $did }}" href="{{ $docDestroyUrl }}" data-url="{{ $docDestroyUrl }}" data-guard-msg="{{ $docDestroyMsg }}" data-sv-localized="true" class="{{ VC::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-confirm="{{ __($confirmTitle) }}|{{ __($confirmBody) }}" data-confirm-yes="document.getElementById('delete-form-{{ $did }}').submit();"><i class="{{ VC::TI_TRS_WT }}"></i></a>
                                                        {{ Form::close() }}
                                                    </div>
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $hasActions ? 5 : 4 }}" class="text-center text-muted">{{ __('No documents found') }}</td>
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
    @can('create document')
        <script defer src="{{ asset('assets/js/routes/documentUploads/create.js') }}"></script>
    @endcan
    @can('edit document')
        <script defer src="{{ asset('assets/js/routes/documentUploads/edit.js') }}"></script>
    @endcan
    @can('delete document')
        <script defer src="{{ asset('assets/js/routes/documentUploads/destroy.js') }}"></script>
    @endcan
@endpush
