@php
    use App\Config\Constants\{
        ViewClassNamesConstants as VC,
        ViewsConstants as VW
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang() : app()->getLocale();

    $leadOk = isset($lead) && !empty($lead);
    $formId = 'leads-sources-form';

    $updateBase  = VW::LD . '.sources.update';
    $updateKebab = Str::kebab($updateBase);
    $updateName  = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);

    $formUrl   = ($leadOk && $updateName) ? route($updateName, [$lead->id]) : '#';
    $formGuard = Utility::fetchLinkMessage($lang, VW::LD, 'sources_update_route_unavailable') ?? 'Leads sources update route is unavailable. Please contact technical support or your domain administrator.';

    $sourcesIter  = isset($sources) ? (is_array($sources) ? $sources : (method_exists($sources,'all') ? $sources->all() : [])) : [];
    $selectedMap  = isset($selected) && is_array($selected) ? $selected : [];
@endphp

@if($leadOk)
    {{ Form::model($lead, [
        'url'               => $formUrl,
        'method'            => 'PUT',
        'id'                => $formId,
        'data-url'          => $formUrl,
        'data-guard-msg'    => $formGuard,
        'data-sv-localized' => 'true',
    ]) }}
        {{ Form::token() }}
        <div class="modal-body">
            <div class="row">
                <div class="col-12 form-group">
                    <div class="row gutters-xs">
                        @foreach ($sourcesIter as $source)
                            @php
                                $sid   = $source->id ?? null;
                                $sname = isset($source->name) ? ucfirst($source->name) : __('Unnamed');
                                $chkId = 'sources_' . $sid;
                                $isOn  = $sid !== null && array_key_exists($sid, $selectedMap);
                            @endphp
                            <div class="col-12 custom-control custom-checkbox mt-2 mb-2">
                                {{ Form::checkbox('sources[]', $sid, $isOn ? true : false, ['class' => 'form-check-input', 'id' => $chkId]) }}
                                {{ Form::label($chkId, $sname, ['class' => 'form-check-label']) }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script async src="{{ asset('assets/js/routes/leads/lang/sources.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/leads/sources.js') }}"></script>
    {{ Form::close() }}
@else
    <div>{{ __('No lead could be found') }}</div>
@endif
