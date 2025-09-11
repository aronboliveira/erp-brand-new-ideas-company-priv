@php
    use App.Config.Constants\{PlansConstants as PL, ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Route, Storage};
    use Illuminate\Support\{Collection, Str};

    $lang = Utility::fetchUserLang();
    $hasChatGPTSettings = method_exists(Utility::class, 'getChatGPTSettings');
    $plan = $hasChatGPTSettings ? Utility::getChatGPTSettings() : null;
    $aiEnabled = $plan?->{PL::COL_GPT} == 1;

    $rolesIsList = (is_array($roles ?? null) && count($roles ?? []) > 0) || (($roles ?? null) instanceof Collection && $roles->isNotEmpty());
    $roleOptions = ($roles ?? null) instanceof Collection ? $roles->toArray() : (is_array($roles ?? null) ? $roles : []);
    $roleHasError = $errors->has('role');
    $roleAttrs = [
        'id' => 'role',
        'class' => trim(VC::FM_CT_SL.' '.($roleHasError ? 'is-invalid' : '')),
        'placeholder' => __('Select Role'),
        'aria-invalid' => $roleHasError ? 'true' : 'false',
        'aria-describedby' => $roleHasError ? 'role-error' : null,
    ];
    if (!$rolesIsList) { $roleAttrs['disabled'] = 'disabled'; }

    $hasDocument = !empty($documentUpload->document ?? null);
    $docSrc = $hasDocument ? asset(Storage::url('uploads/documentUpload')).'/'.$documentUpload->document : null;

    $docUpdateBase = VW::DOC_UP.'.update';
    $docUpdateKebab = Str::kebab($docUpdateBase);
    $docUpdateResolved = Route::has($docUpdateBase) ? $docUpdateBase : (Route::has($docUpdateKebab) ? $docUpdateKebab : null);
    $docUpdateUrl = ($docUpdateResolved && !empty($documentUpload?->id)) ? route($docUpdateResolved, [$documentUpload->id]) : '#';
    $docUpdateFormId = 'document-upload-update-form-'.($documentUpload->id ?? 'x');
    $docUpdateGuardMsg = Utility::fetchLinkMessage($lang, VW::DOC_UP, 'update_document_route_unavailable') ?? 'Update document route is unavailable. Please contact technical support or your domain administrator.';
    $nameHasError = $errors->has('name');
    $nameAttrs = [
        'id' => 'name',
        'class' => trim(VC::FM_CT.' '.($nameHasError ? 'is-invalid' : '')),
        'required' => 'required',
        'aria-invalid' => $nameHasError ? 'true' : 'false',
        'aria-describedby' => $nameHasError ? 'name-error' : null,
        'autocomplete' => 'off',
    ];
    $genBase = 'generate';
    $genKebab = Str::kebab($genBase);
    $genResolved = Route::has($genBase) ? $genBase : (Route::has($genKebab) ? $genKebab : null);
    $genUrl = $aiEnabled ? ($genResolved ? route($genResolved, ['document']) : '#') : '#';
    $genGuardMsg = Utility::fetchLinkMessage($lang, VW::DOC_UP, 'generate_document_route_unavailable') ?? 'Generate document route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@if(!empty($documentUpload) && !isset($documentUpload->id))
    {{ Form::model($documentUpload, [
        'method' => 'PUT',
        'url' => $docUpdateUrl,
        'enctype' => 'multipart/form-data',
        'id' => $docUpdateFormId,
        'data-url' => $docUpdateUrl,
        'data-guard-msg' => $docUpdateGuardMsg,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            @if($aiEnabled)
                <div class="text-end">
                    <a id="document-generate-btn"
                    href="{{ $genUrl }}"
                    data-url="{{ $genUrl }}"
                    data-guard-msg="{{ $genGuardMsg }}"
                    data-sv-localized="true"
                    data-size="md"
                    class="{{ VC::BT_SM_PM }} btn-icon"
                    data-ajax-popup-over="true"
                    data-bs-placement="top"
                    data-title="{{ __('Generate content with AI') }}">
                        <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
            @endif
            <div class="{{ VC::RW }}">
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                        {{ Form::text('name', null, $nameAttrs) }}
                        @error('name')
                            <span id="name-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('role', __('Role'), ['class' => VC::FM_LB]) }}
                        {{ Form::select('role', $roleOptions, null, $roleAttrs) }}
                        @error('role')
                            <span id="role-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>
                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                        {{ Form::textarea('description', null, ['id' => 'description', 'class' => VC::FM_CT, 'rows' => 3]) }}
                    </div>
                </div>
                <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                    {{ Form::label('document', __('Document'), ['class' => VC::FM_LB]) }}
                    <div class="choose-file">
                        <label for="document" class="{{ VC::FM_LB }}">
                            <input type="file" class="{{ VC::FM_CT }}" name="document" id="document" data-filename="document_update">
                            @if($hasDocument)
                                <img id="image" src="{{ $docSrc }}" class="{{ VC::MT3 }}" style="width:25%;"/>
                            @else
                                <img id="image" class="{{ VC::MT3 }}" style="width:25%; display:none;"/>
                            @endif
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script async src="{{ asset('assets/js/routes/documentUploads/lang/edit.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/documentUploads/update.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/documentUploads/generate.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/documentUploads/img.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="modal-body">{{ __('No document upload available') }}</div>
@endif
