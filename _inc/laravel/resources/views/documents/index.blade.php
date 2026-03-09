@php
    try {
$lang       = Utility::fetchUserLang();
        $hasActions = Gate::check('edit document type') || Gate::check('delete document type');
        $colspan    = $hasActions ? 3 : 2;
    } catch (\Throwable $e) {
        \Log::error('documents/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Document Type') }}
@endsection

@section(YW::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Document Type') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create document type')
            @php
                try {
                    $docCreateBase  = VW::DOC.'.create';
                    $docCreateKebab = Str::kebab($docCreateBase);
                    $docCreateName  = Route::has($docCreateBase) ? $docCreateBase : (Route::has($docCreateKebab) ? $docCreateKebab : null);
                    $docCreateUrl   = $docCreateName ? route($docCreateName) : '#';
                    $docCreateGuard = Utility::fetchLinkMessage($lang, VW::DOC, 'create_document_route_unavailable') ?? 'Create document route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('documents/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a id="document-create-btn"
               href="{{ $docCreateUrl }}"
               data-url="{{ $docCreateUrl }}"
               data-guard-msg="{{ base64_encode($docCreateGuard) }}"
               data-sv-localized="true"
               data-ajax-popup="true"
               data-title="{{ __('Create New Document') }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YW::ADM_CTT)
    <div class="row">
        <div class="{{ VC::C3 }}">
            @include('layouts.hrm_setup')
        </div>
        <div class="{{ VC::C9 }}">
            <div class="card">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Document') }}</th>
                                    <th>{{ __('Required Field') }}</th>
                                    @if($hasActions)
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @forelse($documents as $document)
                                    @php
                                        try {
                                            $docName = !empty($document->name) ? $document->name : __('No name available for document');
                                            $req     = (int) ($document->is_required ?? 0);
                                            $docId   = (string) ($document->id ?? '');
                                        } catch (\Throwable $e) {
                                            \Log::error('documents/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <tr>
                                        <td>{{ $docName }}</td>
                                        <td>
                                            @if($req === 1)
                                                <div class="doc_status_badge {{ VC::BDG }} bg-primary p-2 px-3 rounded">{{ __('Required') }}</div>
                                            @else
                                                <div class="doc_status_badge {{ VC::BDG }} bg-danger p-2 px-3 rounded">{{ __('Not Required') }}</div>
                                            @endif
                                        </td>
                                        @if($hasActions)
                                            <td>
                                                @can('edit document type')
                                                    @php
                                                        try {
                                                            $docEditBase  = VW::DOC.'.edit';
                                                            $docEditKebab = Str::kebab($docEditBase);
                                                            $docEditName  = Route::has($docEditBase) ? $docEditBase : (Route::has($docEditKebab) ? $docEditKebab : null);
                                                            $docEditUrl   = ($docEditName && $docId !== '') ? route($docEditName, [$docId]) : '#';
                                                            $docEditGuard = Utility::fetchLinkMessage($lang, VW::DOC, 'edit_document_route_unavailable') ?? 'Edit document route is unavailable. Please contact technical support or your domain administrator.';
                                                        } catch (\Throwable $e) {
                                                            \Log::error('documents/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a id="document-edit-btn-{{ $docId }}"
                                                           href="{{ $docEditUrl }}"
                                                           data-url="{{ $docEditUrl }}"
                                                           data-guard-msg="{{ base64_encode($docEditGuard) }}"
                                                           data-sv-localized="true"
                                                           data-ajax-popup="true"
                                                           data-title="{{ __('Edit Document Type') }}"
                                                           class="{{ VC::BT_SM_CT }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Edit') }}">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete document type')
                                                    @php
                                                        try {
                                                            $docDestroyBase  = VW::DOC.'.destroy';
                                                            $docDestroyKebab = Str::kebab($docDestroyBase);
                                                            $docDestroyName  = Route::has($docDestroyBase) ? $docDestroyBase : (Route::has($docDestroyKebab) ? $docDestroyKebab : null);
                                                            $docDestroyUrl   = ($docDestroyName && $docId !== '') ? route($docDestroyName, [$docId]) : '#';
                                                            $docDestroyGuard = Utility::fetchLinkMessage($lang, VW::DOC, 'destroy_document_route_unavailable') ?? 'Delete document route is unavailable. Please contact technical support or your domain administrator.';
                                                            $formId          = 'delete-form-'.$docId;
                                                            $areYouSure      = __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?');
                                                            $irreversible    = __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                                        } catch (\Throwable $e) {
                                                            \Log::error('documents/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {{ Form::open([
                                                            'method' => 'DELETE',
                                                            'url'    => $docDestroyUrl,
                                                            'id'     => $formId
                                                        ]) }}
                                                            <a id="delete-document-btn-{{ $docId }}"
                                                               href="{{ $docDestroyUrl }}"
                                                               data-url="{{ $docDestroyUrl }}"
                                                               data-guard-msg="{{ base64_encode($docDestroyGuard) }}"
                                                               data-sv-localized="true"
                                                               class="{{ VC::BT_SM_CT_PR }}"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Delete') }}"
                                                               data-confirm="{{ $areYouSure }}|{{ $irreversible }}"
                                                               data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {{ Form::close() }}
                                                    </div>
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $colspan }}" class="{{ VC::TXCT_MT }}">
                                            {{ __('No Document Types Found') }}
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
    @can('create document type')
        <script defer src="{{ asset('assets/js/routes/documents/create.js') }}"></script>
    @endcan
    @can('edit document type')
        <script defer src="{{ asset('assets/js/routes/documents/edit.js') }}"></script>
    @endcan
    @can('delete document type')
        <script defer src="{{ asset('assets/js/routes/documents/destroy.js') }}"></script>
    @endcan
@endpush
