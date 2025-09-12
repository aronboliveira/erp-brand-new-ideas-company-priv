@php
    use App\Config\Constants\{
        ViewClassNamesConstants as VC,
        ViewsConstants as VW
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};

    $lang       = Utility::fetchUserLang();
    $hasLabel   = !empty($label ?? null) && data_get($label, 'id');

    $updateBase     = VW::LBL.'.update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasLabel) ? route($updateResolved, $label->id) : '#';
    $updateGuard    = Utility::fetchLinkMessage($lang, VW::LBL, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');

    $colorsIsList = (is_array($colors ?? null) && count($colors ?? []) > 0)
        || (($colors ?? null) instanceof Collection && $colors->isNotEmpty());
@endphp

{{ Form::model($label, [
    'url'               => $updateUrl,
    'method'            => 'PUT',
    'id'                => 'label-edit-form',
    'data-guard-msg'    => $updateGuard,
    'data-sv-localized' => 'true'
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('name', __('Label Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('pipeline_id', __('Pipeline'), ['class' => VC::FM_LB]) }}
                {{ Form::select('pipeline_id', $pipelines, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('color', __('Color'), ['class' => VC::FM_LB]) }}
                @if($colorsIsList)
                    <div class="row gutters-xs">
                        @foreach($colors as $color)
                            <div class="col-auto">
                                <label class="colorinput">
                                    <input name="color" type="radio" value="{{ $color }}" @checked($label->color == $color) class="colorinput-input">
                                    <span class="colorinput-color bg-{{ $color }}"></span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="{{ VC::TXS }} {{ VC::TXT_MT }}">{{ __('No colors configured.') }}</div>
                @endif
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/labels/edit.js') }}"></script>
{{ Form::close() }}

