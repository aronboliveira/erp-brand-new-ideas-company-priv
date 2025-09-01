@php
    use App\Config\Constants\{
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Illuminate\Support\{Facades\Route, Str};
    use Collective\Html\FormFacade as Form;

    $lang       = Utility::fetchUserLang();
    $storeBase  = VW::WBH . '.store';
    $storeKebab = Str::kebab($storeBase);
    $storeName  = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl   = $storeName ? route($storeName) : '#';
    $storeGuard = Utility::fetchLinkMessage($lang, VW::WBH, 'store_webhook_route_unavailable')
        ?? 'Store webhook route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{!! Form::open([
    'url'                  => $storeUrl,
    'method'               => 'post',
    'id'                   => 'create_webhook',
    'data-resolved-action' => $storeUrl,
    'data-guard-msg'       => $storeGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    {{ Form::label('module', __('Module'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('module', ($modules ?? []), null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
                    @error('module')
                        <span class="invalid-module" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <div class="form-group">
                    {{ Form::label('url', __('Url'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('url', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Webhook Url')]) }}
                    @error('url')
                        <span class="invalid-name" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <div class="form-group">
                    {{ Form::label('method', __('Method'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('method', ($methods ?? []), null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
                    @error('method')
                        <span class="invalid-method" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>

    <script defer src="{{ asset('assets/js/routes/webhooks/store.js') }}"></script>
{!! Form::close() !!}
