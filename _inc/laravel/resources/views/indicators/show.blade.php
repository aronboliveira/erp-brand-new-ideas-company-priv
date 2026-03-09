@php
    try {
$branchName = (isset($indicator->branches) && !empty($indicator->branches->name))
            ? $indicator->branches->name
            : __('Branch data was not available.');
        $deptName = (isset($indicator->departments) && !empty($indicator->departments->name))
            ? $indicator->departments->name
            : __('Department data was not available.');
        $desigName = (isset($indicator->designations) && !empty($indicator->designations->name))
            ? $indicator->designations->name
            : __('Designation data was not available.');
    } catch (\Throwable $e) {
        \Log::error('indicators/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

<div class="modal-body">
    <div class="{{ VC::RW_PY4 }}">
        <div class="{{ VC::CM12 }}">
            <div class="info {{ VC::TXSM }}">
                <strong>{{ __('Branch') }} : </strong>
                <span>{{ $branchName }}</span>
            </div>
        </div>
        <div class="{{ VC::CM6 }} {{ VC::MT2 }}">
            <div class="info {{ VC::TXSM }} font-style">
                <strong>{{ __('Department') }} : </strong>
                <span>{{ $deptName }}</span>
            </div>
        </div>
        <div class="{{ VC::CM6 }} {{ VC::MT3 }}">
            <div class="info {{ VC::TXSM }} font-style">
                <strong>{{ __('Designation') }} : </strong>
                <span>{{ $desigName }}</span>
            </div>
        </div>
    </div>

    @forelse($performance as $group)
        <div class="row">
            <div class="{{ VC::CM12 }} {{ VC::MT3 }}">
                <h6>{{ $group->name ?? __('Untitled group') }}</h6>
                <hr class="mt-0">
            </div>
            @forelse($group->types as $type)
                <div class="{{ VC::C6 }}">{{ $type->name ?? __('Untitled criterion') }}</div>
                <div class="{{ VC::C6 }}">
                    <fieldset class="rating" aria-label="{{ __('Rating') }}">
                        <input class="stars" type="radio" id="type-5-{{ $type->id }}" name="rating[{{ $type->id }}]" value="5" {{ (isset($ratings[$type->id]) && $ratings[$type->id] == 5) ? 'checked' : '' }} disabled>
                        <label class="full" for="type-5-{{ $type->id }}" title="{{ __('Excellent — 5 stars') }}"></label>

                        <input class="stars" type="radio" id="type-4-{{ $type->id }}" name="rating[{{ $type->id }}]" value="4" {{ (isset($ratings[$type->id]) && $ratings[$type->id] == 4) ? 'checked' : '' }} disabled>
                        <label class="full" for="type-4-{{ $type->id }}" title="{{ __('Very good — 4 stars') }}"></label>

                        <input class="stars" type="radio" id="type-3-{{ $type->id }}" name="rating[{{ $type->id }}]" value="3" {{ (isset($ratings[$type->id]) && $ratings[$type->id] == 3) ? 'checked' : '' }} disabled>
                        <label class="full" for="type-3-{{ $type->id }}" title="{{ __('Satisfactory — 3 stars') }}"></label>

                        <input class="stars" type="radio" id="type-2-{{ $type->id }}" name="rating[{{ $type->id }}]" value="2" {{ (isset($ratings[$type->id]) && $ratings[$type->id] == 2) ? 'checked' : '' }} disabled>
                        <label class="full" for="type-2-{{ $type->id }}" title="{{ __('Needs improvement — 2 stars') }}"></label>

                        <input class="stars" type="radio" id="type-1-{{ $type->id }}" name="rating[{{ $type->id }}]" value="1" {{ (isset($ratings[$type->id]) && $ratings[$type->id] == 1) ? 'checked' : '' }} disabled>
                        <label class="full" for="type-1-{{ $type->id }}" title="{{ __('Unsatisfactory — 1 star') }}"></label>
                    </fieldset>
                </div>
            @empty
                <div class="{{ VC::C12 }} {{ VC::TXSM }} {{ VC::TXT_MT }}">{{ __('No evaluation criteria were available to display.') }}</div>
            @endforelse
        </div>
    @empty
        <div class="row">
            <div class="{{ VC::C12 }} {{ VC::TXSM }} {{ VC::TXT_MT }}">{{ __('No performance groups were available to display.') }}</div>
        </div>
    @endforelse
</div>
