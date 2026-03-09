@php
    try {
$lang        = Utility::fetchUserLang();
        $hasForm     = !empty($form ?? null) && data_get($form, 'id');
        $hasField    = !empty($form_field ?? null) && data_get($form_field, 'id');
        $hasEntity   = $hasForm && $hasField;

        if ($hasEntity) {
            $updBase     = VW::FM_FD . '.update';
            $updKebab    = Str::kebab($updBase);
            $updResolved = Route::has($updBase) ? $updBase : (Route::has($updKebab) ? $updKebab : null);
            $updUrl      = $updResolved ? route($updResolved, [data_get($form, 'id'), data_get($form_field, 'id')]) : '#';
            $updGuardMsg = Utility::fetchLinkMessage($lang, VW::FM_FD, 'update_field_route_unavailable') ?? __('Form field update route is unavailable. Please contact technical support or your domain administrator.');

            $typesIsList = (is_array($types ?? null) && count($types ?? []) > 0) || (($types ?? null) instanceof Collection && $types->isNotEmpty());
            $typeOptions = $typesIsList ? (is_array($types) ? $types : $types->toArray()) : [__('No types available')];
            $formId      = 'fm-fd-update-form';
        }
    } catch (\Throwable $e) {
        \Log::error('form_builders/field_edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@if(!$hasEntity)
    <div class="{{ VC::ALT_DNG }} {{ VC::MB0 }}" role="alert">{{ __('The requested form or field was not found or is unavailable.') }}</div>
@else
    {{ Form::model($form_field, [
        'url'               => $updUrl,
        'method'            => 'POST',
        'id'                => $formId,
        'data-url'          => $updUrl,
        'data-guard-msg'    => $updGuardMsg,
        'data-sv-localized' => 'true'
    ]) }}
        <div class="modal-body">
            <div class="row" id="frm_field_data">
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    {{ Form::label('name', __('Question Name') ?: __('Failed to get label: Question Name'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required', 'placeholder' => __('Enter question name') ?: __('Failed to get placeholder: question name')]) }}
                </div>
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    {{ Form::label('type', __('Type') ?: __('Failed to get label: Type'), ['class' => VC::FM_LB]) }}
                    {{ Form::select(
                        'type',
                        $typeOptions,
                        null,
                        array_merge(['class' => VC::FM_CT.' select2', 'id' => 'choices-multiple1', 'required' => 'required'], $typesIsList ? [] : ['disabled' => 'disabled'])
                    ) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') ?: __('Failed to get label: Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') ?: __('Failed to get label: Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/fm_fd/updateField.js') }}"></script>
    {{ Form::close() }}
@endif
