@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        ViewClassNamesConstants as VC,
        StacksConstants
    };
    use Collective\Html\FormFacade as Form;

    $lang            = Utility::fetchUserLang();
    $branchStoreRoute = Route::has(ViewsConstants::BRC)
        ? route(ViewsConstants::BRC)
        : '#';
    $formId           = 'store-branch-form';
    $branchStoreMsg   = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::BRC,
        'branch_store_route_unavailable'
    ) ?? 'Branch store route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{!! Form::open([
    'url'            => $branchStoreRoute,
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $branchStoreRoute,
    'data-guard-msg' => $branchStoreMsg,
]) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::FM_G }}">
                    {!! Form::label('name', __('Name'), ['class' => VC::FM_LB]) !!}
                    {!! Form::text('name', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Branch Name')]) !!}
                    @error('name')
                        <span class="invalid-name" role="alert">
                            <strong class="{{ VC::TXT_MT }}">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
    </div>
    <script defer src="{{ asset('assets/js/routes/branches/store.js') }}"></script>
{!! Form::close() !!}
