@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
    $hasKey         = isset($key) && !empty($key);
    $updateBase     = VW::FT.'.update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasKey) ? route($updateResolved, $key) : '#';
    $updateGuard    = Utility::fetchLinkMessage($lang, 'features', 'update_route_unavailable')
                        ?? __('Update Features route is unavailable. Please contact technical support or your domain administrator.');
    $of      = is_array($other_features ?? null) ? $other_features : [];
    $heading = isset($of['other_features_heading']) && !empty($of['other_features_heading']) ? $of['other_features_heading'] : __('Other features');
    $desc    = isset($of['other_featured_description']) && !empty($of['other_featured_description']) ? $of['other_featured_description'] : __('No description available');
    $link    = isset($of['other_feature_buy_now_link']) && !empty($of['other_feature_buy_now_link']) ? $of['other_feature_buy_now_link'] : __('No purchase link available');
@endphp

{{ Form::model(null, [
    'route'               => $updateUrl,
    'method'            => 'POST',
    'enctype'           => 'multipart/form-data',
    'id'                => 'features-update-form',
    'data-url'          => $updateUrl,
    'data-guard-msg'    => $updateGuard,
    'data-sv-localized' => 'true'
]) }}
    <div class="modal-body">
        @csrf
        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('Heading', __('Heading'), ['class' => VC::FM_LB]) }}
                {{ Form::text('other_features_heading', $heading, ['class' => VC::FM_CT, 'placeholder' => __('Enter Heading')]) }}
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('Description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('other_featured_description', $desc, ['class' => VC::FM_CT . ' summernote-simple', 'placeholder' => __('Enter Description')]) }}
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('Buy Now Link', __('Buy Now Link'), ['class' => VC::FM_LB]) }}
                {{ Form::text('other_feature_buy_now_link', $link, ['class' => VC::FM_CT, 'placeholder' => __('Enter Link')]) }}
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('Image', __('Image'), ['class' => VC::FM_LB]) }}
                <input type="file" name="other_features_image" class="{{ VC::FM_CT }}">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/features/update.js') }}"></script>
{{ Form::close() }}

