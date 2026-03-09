@php
    try {
$lang = Utility::fetchUserLang();

        $storeBase = VW::WRH;
        $storeTry  = [$storeBase, $storeBase . '.store', Str::kebab($storeBase), Str::kebab($storeBase) . '.store'];
        $storeName = collect($storeTry)->first(fn($n) => Route::has($n));
        $storeUrl  = $storeName ? route($storeName) : '#';
        $storeGuard = Utility::fetchLinkMessage($lang, VW::WRH, 'store_warehouse_route_unavailable')
            ?? 'Store warehouse route is unavailable. Please contact technical support or your domain administrator.';
        $formId = 'create_warehouse';

        $plan = Utility::getChatGPTSettings();
    } catch (\Throwable $e) {
        \Log::error('warehouses/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{!! Form::open([
    'url'                  => $storeUrl,
    'method'               => 'post',
    'id'                   => $formId,
    'data-resolved-action' => $storeUrl,
    'data-guard-msg'       => $storeGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        @if(($plan?->{PlansConstants::COL_GPT} ?? 0) == 1)
            @php
                $genBase  ??= 'generate';
                try {
                    $genName  = Route::has($genBase) ? $genBase : (Route::has(Str::kebab($genBase)) ? Str::kebab($genBase) : null);
                    $genUrl   = $genName ? route($genName, ['warehouse']) : '#';
                    $genGuard = Utility::fetchLinkMessage($lang, VW::WRH, 'ai_generate_content_unavailable')
                        ?? 'AI content generation for warehouses is unavailable. Please contact technical support or your domain administrator.';
                    $genId = 'warehouse-ai-generate-link';
                } catch (\Throwable $e) {
                    \Log::error('warehouses/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <div class="{{ VC::TX_END }}">
                <a id="{{ $genId }}"
                   href="{{ $genUrl }}"
                   data-url="{{ $genUrl }}"
                   data-ajax-popup-over="true"
                   data-size="md"
                   data-title="{{ __('Generate content with AI') }}"
                   data-bs-placement="top"
                   data-guard-msg="{{ base64_encode($genGuard) }}"
                   data-sv-localized="true"
                   class="{{ VC::BT_SM_PM }} btn-icon">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
            <script defer src="{{ asset('assets/js/routes/warehouses/generate.js') }}"></script>
        @endif

        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, [ 'class' => VC::FM_CT, 'required' => true ]) }}
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('address', __('Address'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('address', null, [ 'class' => VC::FM_CT, 'rows' => 3 ]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('city', __('City'), ['class' => VC::FM_LB]) }}
                {{ Form::text('city', null, [ 'class' => VC::FM_CT ]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('zip', __('Zip Code'), ['class' => VC::FM_LB]) }}
                {{ Form::text('zip', null, [ 'class' => VC::FM_CT ]) }}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/warehouses/store.js') }}"></script>
{!! Form::close() !!}
