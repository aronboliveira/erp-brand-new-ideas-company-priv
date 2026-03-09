@php
    try {
$lang = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang() : app()->getLocale();

        $leadOk   = isset($lead) && !empty($lead);
        $formId   = 'leads-discussion-form';

        $storeBase   = VW::LD . '.discussion.store';
        $storeKebab  = Str::kebab($storeBase);
        $storeName   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);

        $formUrl     = ($leadOk && $storeName) ? route($storeName, [$lead->id]) : '#';
        $formGuard   = Utility::fetchLinkMessage($lang, VW::LD, 'discussion_store_route_unavailable') ?? 'Leads discussion store route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('leads/discussions — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@if($leadOk)
    {{ Form::model($lead, [
        'url'              => $formUrl,
        'method'           => 'POST',
        'id'               => $formId,
        'data-url'         => $formUrl,
        'data-guard-msg'   => $formGuard,
        'data-sv-localized'=> 'true',
    ]) }}
        {{ Form::token() }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    {{ Form::label('comment', __('Message'), ['class'=>'form-label']) }}
                    {{ Form::textarea('comment', null, ['class' => 'form-control']) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script async src="{{ asset('assets/js/routes/leads/lang/discussions.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/leads/discussions.js') }}"></script>
    {{ Form::close() }}
@else
    <div>{{ __('No lead could be found') }}</div>
@endif
