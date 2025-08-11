<div class="modal-body">
    @php
      $details = [
        ['col'=>'col-md-12','label'=>__('Branch'),'value'=>$appraisal->branches->name ?? ''],
        ['col'=>'col-md-6 mt-3','label'=>__('Employee'),'value'=>$appraisal->employees->name ?? ''],
        ['col'=>'col-md-6 mt-3','label'=>__('Appraisal Date'),'value'=>$appraisal->appraisal_date],
      ];
      $titles = [
        5 => __('Outstanding – 5 stars'),
        4 => __('Very Good – 4 stars'),
        3 => __('Satisfactory – 3 stars'),
        2 => __('Needs Improvement – 2 stars'),
        1 => __('Unsatisfactory – 1 star'),
      ];
    @endphp
    <div class="row py-4">
      @foreach($details as $d)
        <div class="{{ $d['col'] }}">
          <div class="info text-sm font-style">
            <strong>{{ $d['label'] }}:</strong> <span>{{ $d['value'] }}</span>
          </div>
        </div>
      @endforeach
    </div>
    <div class="row">
      <div class="col-5 text-end" style="margin-left:51px;"><h5>{{ __('Indicator') }}</h5></div>
      <div class="col-4 text-end"><h5>{{ __('Appraisal') }}</h5></div>
      @foreach($performance_types as $pt)
        <div class="col-md-12 mt-3"><h6>{{ $pt->name }}</h6><hr class="mt-0"></div>
        @foreach($pt->types as $type)
          <div class="col-4">{{ $type->name }}</div>
          <div class="col-4">
            <fieldset class="rating">
              @for($i=5; $i>=1; $i--)
                <input class="stars" type="radio"
                       id="indicator-{{ $i }}-{{ $type->id }}"
                       name="ratings[{{ $type->id }}]" value="{{ $i }}"
                       {{ (isset($ratings[$type->id]) && $ratings[$type->id]==$i) ? 'checked' : '' }} disabled>
                <label class="full" for="indicator-{{ $i }}-{{ $type->id }}" title="{{ $titles[$i] }}"></label>
              @endfor
            </fieldset>
          </div>
          <div class="col-4">
            <fieldset class="rating">
              @for($i=5; $i>=1; $i--)
                <input class="stars" type="radio"
                       id="rating-{{ $i }}-{{ $type->id }}"
                       name="rating[{{ $type->id }}]" value="{{ $i }}"
                       {{ (isset($rating[$type->id]) && $rating[$type->id]==$i) ? 'checked' : '' }} disabled>
                <label class="full" for="rating-{{ $i }}-{{ $type->id }}" title="{{ $titles[$i] }}"></label>
              @endfor
            </fieldset>
          </div>
        @endforeach
      @endforeach
    </div>
    <div class="row">
      <div class="col-md-12"><hr><h6>{{ __('Remark') }}</h6></div>
      <div class="col-md-12 mt-3"><p class="text-sm">{{ $appraisal->remark }}</p></div>
    </div>
</div>
  