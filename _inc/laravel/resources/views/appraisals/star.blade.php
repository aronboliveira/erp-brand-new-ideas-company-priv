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
    @if(!empty($performance_types) && ((is_array($performance_types ?? null) && count($performance_types) > 0) || (($performance_types ?? null) instanceof \Illuminate\Support\Collection && $performance_types->isNotEmpty())))
        @foreach($performance_types as $performance_type)
            <div class="{{ VC::CM12 }} {{ VC::MT3 }}"><h6>{{ $performance_type->name ?? __('No name found') }}</h6><hr class="mt-0"></div>
            @php $types = $performance_type->types ?? null; @endphp
            @if(!empty($types) && ((is_array($types) && count($types) > 0) || ($types instanceof \Illuminate\Support\Collection && $types->isNotEmpty())))
                @foreach($types as $type)
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
            @else
                <div class="col-4">{{ __('No type found') }}</div>
            @endif
        @endforeach
    @else
        <div class="col-3 text-end"><h5>{{ __('No Indicator group found') }}</h5></div>
    @endif
</div>
