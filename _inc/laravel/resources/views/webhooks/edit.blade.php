@php
    use App\Config\Constants\{
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Illuminate\Support\{Facades\Route, Str};
    use Collective\Html\FormFacade as Form;

    $lang        = Utility::fetchUserLang();
    $wid         = (string) data_get($webhooksetting ?? null, 'id', '');
    $updateBase  = VW::WBH . '.update';
    $updateKebab = Str::kebab($updateBase);
    $updateName  = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl   = ($updateName && $wid !== '') ? route($updateName, [$wid]) : '#';
    $updateGuard = Utility::fetchLinkMessage($lang, VW::WBH, 'update_webhook_route_unavailable')
        ?? 'Update webhook route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{!! Form::model($webhooksetting, [
    'url'                  => $updateUrl,
    'method'               => 'POST',
    'id'                   => 'edit_webhook',
    'data-resolved-action' => $updateUrl,
    'data-guard-msg'       => $updateGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        <div class="row">
            <div class="col-12 form-group">
                {{ Form::label('module', __('Module'), ['class' => VC::FM_LB]) }}
                {{ Form::select('module', ($modules ?? []), null, ['class' => VC::FM_CT_SL, 'placeholder' => __('Select Module')]) }}
                @error('module')
                    <span class="invalid-module" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="col-12 form-group">
                {{ Form::label('url', __('Url'), ['class' => VC::FM_LB]) }}
                {{ Form::text('url', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Webhook Url')]) }}
                @error('url')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="col-12 form-group">
                {{ Form::label('method', __('Method'), ['class' => VC::FM_LB]) }}
                {{ Form::select('method', ($methods ?? []), null, ['class' => VC::FM_CT_SL, 'placeholder' => __('Select Method')]) }}
                @error('method')
                    <span class="invalid-method" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>

    <script defer src="{{ asset('assets/js/routes/webhooks/update.js') }}"></script>
{!! Form::close() !!}
