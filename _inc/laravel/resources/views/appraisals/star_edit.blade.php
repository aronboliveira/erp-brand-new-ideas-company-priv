@php
    $starOptions = [
        5 => __('Excellent – 5 stars'),
        4 => __('Very Good – 4 stars'),
        3 => __('Good – 3 stars'),
        2 => __('Fair – 2 stars'),
        1 => __('Poor – 1 star'),
    ];
    $sections = ['ratings' => __('Indicator'), 'rating' => __('Appraisal')];
@endphp
<div class="row">
    <div class="col-5 text-end" style="margin-left:51px;"><h5>{{ __('Indicator') }}</h5></div>
    <div class="col-4 text-end"><h5>{{ __('Appraisal') }}</h5></div>
    @foreach($performance_types as $performance_type)
        <div class="col-md-12 mt-3"><h6>{{ $performance_type->name ?? __('No name found') }}</h6><hr class="mt-0"></div>
        @foreach($performance_type->types as $type)
            <div class="col-4">{{ $type->name ?? __('No name found') }}</div>
            @foreach($sections as $field => $heading)
                <div class="col-4">
                    <fieldset class="rating">
                        @foreach($starOptions as $value => $label)
                            <input
                                class="stars"
                                type="radio"
                                id="{{ $field }}-{{ $value }}-{{ $type->id }}"
                                name="{{ $field }}[{{ $type->id }}]"
                                value="{{ $value }}"
                                {{ (isset($$field[$type->id]) && (int) $$field[$type->id] === (int) $value) ? 'checked' : '' }}
                                disabled>
                            <label
                                class="full"
                                for="{{ $field }}-{{ $value }}-{{ $type->id }}"
                                title="{{ $label }}"></label>
                        @endforeach
                    </fieldset>
                </div>
            @endforeach
        @endforeach
    @endforeach
</div>
