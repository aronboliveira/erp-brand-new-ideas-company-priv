@php
    try {
$lang        = Utility::fetchUserLang();
        $hasEntity   = !empty($formbuilder ?? null) && data_get($formbuilder, 'id');
        $formId      = 'fm-fd-store-form';

        if ($hasEntity) {
            $storeBase     = VW::FM_FD . '.store';
            $storeKebab    = Str::kebab($storeBase);
            $storeResolved = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
            $storeUrl      = ($storeResolved && $hasEntity) ? route($storeResolved, data_get($formbuilder, 'id')) : '#';
            $storeGuardMsg = Utility::fetchLinkMessage($lang, VW::FM_FD, 'store_field_route_unavailable') ?? __('Form field store route is unavailable. Please contact technical support or your domain administrator.');

            $typesIsList   = (is_array($types ?? null) && count($types ?? []) > 0) || (($types ?? null) instanceof Collection && $types->isNotEmpty());
            $typeOptions   = $typesIsList ? (is_array($types) ? $types : $types->toArray()) : [__('No types available')];
        }
    } catch (\Throwable $e) {
        \Log::error('form_builders/field_create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@if(!$hasEntity)
    <div class="{{ VC::ALT_DNG }} {{ VC::MB0 }}" role="alert">{{ __('The requested form builder was not found or is unavailable.') }}</div>
@else
    {{ Form::open([
        'url'               => $storeUrl,
        'id'                => $formId,
        'data-url'          => $storeUrl,
        'data-guard-msg'    => $storeGuardMsg,
        'data-sv-localized' => 'true',
        'route'             => null
    ]) }}
        <div class="modal-body">
            <div class="row" id="frm_field_data">
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    {{ Form::label('name', __('Question Name') ?: __('Failed to get label: Question Name'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('name[]', '', ['class' => VC::FM_CT, 'required' => 'required', 'placeholder' => __('Enter question name') ?: __('Failed to get placeholder: question name')]) }}
                </div>
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    {{ Form::label('type', __('Type') ?: __('Failed to get label: Type'), ['class' => VC::FM_LB]) }}
                    {{ Form::select(
                        'type[]',
                        $typeOptions,
                        null,
                        array_merge(
                            ['class' => VC::FM_CT.' select2', 'id' => 'choices-multiple1', 'required' => 'required'],
                            ($typesIsList ?? false) ? [] : ['disabled' => 'disabled']
                        )
                    ) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') ?: __('Failed to get label: Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Create') ?: __('Failed to get label: Create') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/formBuilders/storeField.js') }}"></script>
    {{ Form::close() }}
@endif
