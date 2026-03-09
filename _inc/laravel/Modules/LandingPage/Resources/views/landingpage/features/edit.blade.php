@php
    try {



        $user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);

        $hasKey         = isset($key) && !empty($key);
        $updateBase     = 'feature_update';
        $updateKebab    = Str::kebab($updateBase);
        $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
        $updateUrl      = ($updateResolved && $hasKey) ? route($updateResolved, $key) : '#';
        $updateGuard    = Utility::fetchLinkMessage($lang, 'features', 'update_route_unavailable')
                            ?? __('Update Feature route is unavailable. Please contact technical support or your domain administrator.');
        $f        = is_array($feature ?? null) ? $feature : [];
        $heading  = !empty($f['feature_heading']) ? $f['feature_heading'] : '';
        $desc     = !empty($f['feature_description']) ? $f['feature_description'] : '';
    } catch (\Throwable $e) {
        \Log::error('Modules/LandingPage/Resources/views/landingpage/features/edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::model(null, [
    'url'               => $updateUrl,
    'method'            => 'POST',
    'enctype'           => 'multipart/form-data',
    'id'                => 'feature-update-form',
    'data-url'          => $updateUrl,
    'data-guard-msg'    => $updateGuard,
    'data-sv-localized' => 'true'
]) }}
    <div class="modal-body">
        @csrf
        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('Heading', __('Heading'), ['class' => VC::FM_LB]) }}
                {{ Form::text('feature_heading', $heading, ['class' => VC::FM_CT, 'placeholder' => __('Enter Heading')]) }}
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('Description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('feature_description', $desc, ['class' => VC::FM_CT . ' summernote-simple', 'placeholder' => __('Enter Description')]) }}
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('Logo', __('Logo'), ['class' => VC::FM_LB]) }}
                <input type="file" name="feature_logo" class="{{ VC::FM_CT }}">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/features/edit.js') }}"></script>
{{ Form::close() }}

{{--<script>--}}
{{--    tinymce.init({--}}
{{--      selector: '#mytextarea',--}}
{{--      menubar: '',--}}
{{--    });--}}
{{--  </script>--}}
