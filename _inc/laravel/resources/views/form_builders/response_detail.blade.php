<div class="modal-body">
    <div class="row">
        @php
            try {
                $items = ($response instanceof \Illuminate\Support\Collection)
                            ? $response->all()
                            : (is_array($response ?? null) ? $response : []);
            } catch (\Throwable $e) {
                \Log::error('form_builders/response_detail — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp

        @forelse($items as $que => $ans)
            @php
                $q = (isset($que) && $que !== '') ? $que : __('Untitled question');
                $a = (isset($ans) && $ans !== '') ? $ans : __('No answer provided');
@endphp
            <div class="{{ VC::C12 }} {{ VC::TXS }}">
                <h6 class="text-small">{{ $q }}</h6>
                <p class="{{ VC::TXSM }}">{{ $a }}</p>
            </div>
        @empty
            <div class="{{ VC::C12 }} {{ VC::TXCT_MT }}">{{ __('No responses found for this submission.') }}</div>
        @endforelse
    </div>
</div>
