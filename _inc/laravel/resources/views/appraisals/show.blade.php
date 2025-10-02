@php
    use App\Config\Constants\{ViewClassNamesConstants as VC};
    use Illuminate\Support\{Arr, Collection};
    $titles = [
        5 => __('Outstanding – 5 stars'),
        4 => __('Very Good – 4 stars'),
        3 => __('Satisfactory – 3 stars'),
        2 => __('Needs Improvement – 2 stars'),
        1 => __('Unsatisfactory – 1 star'),
    ];
@endphp

<div class="modal-body">
    @if(!empty($appraisal) && isset($appraisal))
        @php
            $details = [
                ['col' => 'col-md-12',        'label' => __('Branch'),         'value' => data_get($appraisal, 'branches.name', __('Failed to get branch name'))],
                ['col' => 'col-md-6 mt-3',    'label' => __('Employee'),       'value' => data_get($appraisal, 'employees.name', __('Failed to get employee name'))],
                ['col' => 'col-md-6 mt-3',    'label' => __('Appraisal Date'), 'value' => $appraisal->appraisal_date ?? __('Failed to get appraisal date')],
            ];
        @endphp
        <div class="{{ VC::RW }} py-4">
            @foreach($details as $d)
                <div class="{{ $d['col'] }}">
                    <div class="info {{ VC::TXSM }} font-style">
                        <strong>{{ $d['label'] }}:</strong> <span>{{ $d['value'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="{{ VC::RW }}">{{ __('No appraisal details found.') }}</div>
    @endif
    <div class="{{ VC::RW }}">
        <div class="col-5 text-end" style="margin-left:51px;"><h5>{{ __('Indicator') }}</h5></div>
        <div class="col-4 text-end"><h5>{{ __('Appraisal') }}</h5></div>
        @if(!empty($performance_types) && ((is_array($performance_types) && count($performance_types) > 0) || ($performance_types instanceof Collection && $performance_types->isNotEmpty())))
            @foreach($performance_types as $pt)
                <div class="col-md-12 mt-3"><h6>{{ $pt->name ?? __('No name for Performance type group found') }}</h6><hr class="mt-0"></div>
                @if($pt->types && count($pt->types) > 0 || $pt->types instanceof Collection && $pt->types->isNotEmpty())
                    @foreach($pt->types as $type)
                        <div class="col-4">{{ $type->name ?? __('No name for type found') }}</div>
                        <div class="col-4">
                            <fieldset class="rating">
                                @for($i=5; $i>=1; $i--)
                                    <input
                                        class="stars"
                                        type="radio"
                                        id="indicator-{{ $i }}-{{ $type->id }}"
                                        name="ratings[{{ $type->id }}]"
                                        value="{{ $i }}"
                                        {{ (isset($ratings[$type->id]) && (int)$ratings[$type->id] === $i) ? 'checked' : '' }}
                                        disabled>
                                    <label class="full" for="indicator-{{ $i }}-{{ $type->id }}" title="{{ $titles[$i] }}"></label>
                                @endfor
                            </fieldset>
                        </div>
                        <div class="col-4">
                            <fieldset class="rating">
                                @for($i=5; $i>=1; $i--)
                                    <input
                                        class="stars"
                                        type="radio"
                                        id="rating-{{ $i }}-{{ $type->id }}"
                                        name="rating[{{ $type->id }}]"
                                        value="{{ $i }}"
                                        {{ (isset($rating[$type->id]) && (int)$rating[$type->id] === $i) ? 'checked' : '' }}
                                        disabled>
                                    <label class="full" for="rating-{{ $i }}-{{ $type->id }}" title="{{ $titles[$i] }}"></label>
                                @endfor
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
    @if(!empty($appraisal) && isset($appraisal))
        <div class="{{ VC::RW }}">
            <div class="col-md-12"><hr><h6>{{ __('Remark') }}</h6></div>
            <div class="col-md-12 mt-3"><p class="{{ VC::TXSM }}">{{ $appraisal->remark ?? __('No remark found.') }}</p></div>
        </div>
    @else
        <div class="{{ VC::RW }}">{{ __('No appraisal found.') }}</div>
    @endif
</div>
