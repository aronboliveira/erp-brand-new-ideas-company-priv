@php
    try {
$lang = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang() : app()->getLocale();

        $leadOk = isset($lead) && !empty($lead);
        $formId = 'leads-labels-form';

        $storeBase  = VW::LD . '.labels.store';
        $storeKebab = Str::kebab($storeBase);
        $storeName  = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);

        $formUrl   = ($leadOk && $storeName) ? route($storeName, [$lead->id]) : '#';
        $formGuard = Utility::fetchLinkMessage($lang, VW::LD, 'leads_labels_store_route_unavailable') ?? 'Leads labels store route is unavailable. Please contact technical support or your domain administrator.';

        $labelsIter   = isset($labels) ? (is_array($labels) ? $labels : (method_exists($labels,'all') ? $labels->all() : [])) : [];
        $selectedMap  = isset($selected) && is_array($selected) ? $selected : [];
        $hasLabels    = !empty($labelsIter);
    } catch (\Throwable $e) {
        \Log::error('leads/labels — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@if($leadOk)
    {{ Form::open([
        'url'               => $formUrl,
        'method'            => 'POST',
        'id'                => $formId,
        'data-url'          => $formUrl,
        'data-guard-msg'    => $formGuard,
        'data-sv-localized' => 'true',
    ]) }}
        {{ Form::token() }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    <div class="row gutters-xs">
                        @if($hasLabels)
                            @foreach ($labelsIter as $label)
                                @php
                                    try {
                                        $lid   = $label->id ?? null;
                                        $lname = isset($label->name) ? ucfirst($label->name) : __('Unnamed');
                                        $lclr  = $label->color ?? 'secondary';
                                        $chkId = 'labels_' . $lid;
                                        $isOn  = $lid !== null && array_key_exists($lid, $selectedMap);
                                    } catch (\Throwable $e) {
                                        \Log::error('leads/labels — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                <div class="{{ VC::C12 }} {{ VC::CST_CT_CB }} {{ VC::MT2 }} {{ VC::MB2 }}">
                                    {{ Form::checkbox('labels[]', $lid, $isOn ? true : false, ['class' => 'form-check-input', 'id' => $chkId]) }}
                                    {{ Form::label($chkId, $lname, ['class' => 'custom-control-label ml-4 text-white p-2 px-3 rounded badge bg-' . $lclr]) }}
                                </div>
                            @endforeach
                        @else
                            <div class="{{ VC::C12 }} {{ VC::MT2 }} {{ VC::MB2 }}">{{ __('No labels available') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script async src="{{ asset('assets/js/routes/leads/lang/labels.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/leads/labels.js') }}"></script>
    {{ Form::close() }}
@else
    <div>{{ __('No lead could be found') }}</div>
@endif
