@php
    try {
$lang = Utility::fetchUserLang();
        $namespace       = ViewsConstants::DL;
        $routeName       = "{$namespace}.discussion.store";
        $hasStoreRoute   = Route::has($routeName);
        $storeGuardMsg   = Utility::fetchLinkMessage(
            $lang,
            $namespace,
            'discussion_store_route_unavailable'
        ) ?? 'Discussion store route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('deals/discussions — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@if(!empty($deal) && isset($deal->id))
    @if($hasStoreRoute)
        {{ Form::model($deal, [
            'route'           => [$routeName, $deal->id],
            'method'          => 'POST',
            'id'              => 'discussion-store-form',
            'data-guard-msg'  => $storeGuardMsg
        ]) }}
    @else
        {{ Form::model($deal, [
            'url'             => '#',
            'method'          => 'POST',
            'id'              => 'discussion-store-form',
            'data-guard-msg'  => $storeGuardMsg
        ]) }}
    @endif
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    {{ Form::label('comment', __('Message'), ['class' => VC::FM_LB]) }}
                    {{ Form::textarea('comment', null, ['class' => VC::FM_CT]) }}
                </div>
            </div>
        </div>
        <div class="{{ VC::DFL }} modal-footer">
            <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
        </div>
        <script defer src="{{ asset('assets/js/routes/deals/discussionStore.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="{{ VC::ALT_WRN }}">
        {{ __('Failed to fetch deal data.') }}
    </div>
@endif
