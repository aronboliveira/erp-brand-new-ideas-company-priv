@php
    try {
$lang = Utility::fetchUserLang();
        $createLangRoute = Route::has(VW::LNG.'.store')
            ? route(VW::LNG.'.store')
            : '#';
        $formId = 'language-create-form';
        $createLangMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::LNG,
            'language_store_route_unavailable'
        ) ?? 'Language create route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('languages/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
{!! Form::open([
    'route'    => $createLangRoute,
    'method' => 'post',
    'id'     => $formId,
    'data-sv-localized' => 'true',
    'data-guard-msg'    => $createLangMsg,
]) !!}
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('code', __('Language Code'), ['class' => 'form-label']) }}
                {{ Form::text('code', '', ['class' => 'form-control', 'required' => 'required']) }}
                @error('code')
                    <span class="invalid-code" role="alert">
                        <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('full_name', __('Language Name'), ['class' => 'form-label']) }}
                {{ Form::text('full_name', '', ['class' => 'form-control', 'required' => 'required']) }}
                @error('full_name')
                    <span class="invalid-full_name" role="alert">
                        <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/languages/store.js') }}"></script>
{!! Form::close() !!}
