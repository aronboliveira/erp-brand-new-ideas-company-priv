<div class="modal-body">
    <div class="row">
        @php
            $items = ($response instanceof \Illuminate\Support\Collection)
                        ? $response->all()
                        : (is_array($response ?? null) ? $response : []);
        @endphp

        @forelse($items as $que => $ans)
            @php
                $q = (isset($que) && $que !== '') ? $que : __('Untitled question');
                $a = (isset($ans) && $ans !== '') ? $ans : __('No answer provided');
            @endphp
            <div class="col-12 text-xs">
                <h6 class="text-small">{{ $q }}</h6>
                <p class="text-sm">{{ $a }}</p>
            </div>
        @empty
            <div class="col-12 text-center text-muted">{{ __('No responses found for this submission.') }}</div>
        @endforelse
    </div>
</div>
