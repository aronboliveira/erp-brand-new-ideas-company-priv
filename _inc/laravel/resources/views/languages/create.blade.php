@php
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{
        ViewClassNamesConstants as VC,
        ViewsConstants,
        StacksConstants
    };
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
            <div class="form-group col-md-12">
                {{ Form::label('code', __('Language Code'), ['class' => 'form-label']) }}
                {{ Form::text('code', '', ['class' => 'form-control', 'required' => 'required']) }}
                @error('code')
                    <span class="invalid-code" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-12">
                {{ Form::label('full_name', __('Language Name'), ['class' => 'form-label']) }}
                {{ Form::text('full_name', '', ['class' => 'form-control', 'required' => 'required']) }}
                @error('full_name')
                    <span class="invalid-full_name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
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