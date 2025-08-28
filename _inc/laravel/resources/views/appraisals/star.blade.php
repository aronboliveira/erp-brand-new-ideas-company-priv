@php
    use App\Config\Constants\ViewClassNamesConstants as VC;
    $starOptions = [
        5 => __('Excellent – 5 stars'),
        4 => __('Very Good – 4 stars'),
        3 => __('Good – 3 stars'),
        2 => __('Fair – 2 stars'),
        1 => __('Poor – 1 star'),
    ];
@endphp
<div class="{{ VC::RW }}">
    <div class="col-5 text-end" style="margin-left:51px;"><h5>{{ __('Indicator') }}</h5></div>
    <div class="col-4 text-end"><h5>{{ __('Appraisal') }}</h5></div>
    @if(isset($performance_types) && !empty($performance_types) && count($performance_types) > 0)
        @foreach($performance_types as $performance_type)
            <div class="{{ VC::CM12 }} {{ VC::MT3 }}"><h6>{{ $performance_type->name ?? __('No name found') }}</h6><hr class="mt-0"></div>
            @if(isset($performance_type->types) && !empty($performance_type->types) && count($performance_type->types) > 0)
                @foreach($performance_type->types as $type)
                    <div class="col-4">{{ $type->name ?? __('No name found') }}</div>
                    <div class="col-4">
                        <fieldset class="rating">
                            @foreach($starOptions as $value => $label)
                                <input class="stars" type="radio" id="indicator-{{ $value }}-{{ $type->id }}" name="ratings[{{ $type->id }}]" value="{{ $value }}" {{ (isset($ratings[$type->id]) && (int) $ratings[$type->id] === (int) $value) ? 'checked' : '' }} disabled>
                                <label class="full" for="indicator-{{ $value }}-{{ $type->id }}" title="{{ $label }}"></label>
                            @endforeach
                        </fieldset>
                    </div>
                    <div class="col-4">
                        <fieldset class="rating">
                            @foreach($starOptions as $value => $label)
                                <input class="stars" type="radio" id="rating-{{ $value }}-{{ $type->id }}" name="rating[{{ $type->id }}]" value="{{ $value }}" {{ (isset($rating[$type->id]) && (int) $rating[$type->id] === (int) $value) ? 'checked' : '' }} disabled>
                                <label class="full" for="rating-{{ $value }}-{{ $type->id }}" title="{{ $label }}"></label>
                            @endforeach
                        </fieldset>
                    </div>
                @endforeach
            @endif
        @endforeach
    @endif
</div>
