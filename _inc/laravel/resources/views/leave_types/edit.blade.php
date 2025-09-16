@php
    use App\Config\Constants\{
        ViewClassNamesConstants as VC,
        ViewsConstants as VW,
        StacksConstants as ST
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};

    $lang   = Utility::fetchUserLang();
    $hasLT  = !empty($leavetype ?? null) && data_get($leavetype, 'id');

    $updateBase     = VW::LV_TP . '.update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasLT) ? route($updateResolved, $leavetype->id) : '#';
    $updateGuard    = Utility::fetchLinkMessage($lang, VW::LV_TP, 'update_route_unavailable')
                        ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@if($hasLT)
    {{ Form::model($leavetype, [
        'url'            => $updateUrl,
        'method'         => 'PUT',
        'id'             => 'leaveType-edit-form',
        'data-guard-msg' => $updateGuard
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('title', __('Leave Type'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('title', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Leave Type Name')]) }}
                    @error('title')
                        <span class="invalid-name" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('days', __('Days Per Year'), ['class' => VC::FM_LB]) }}
                    {{ Form::number('days', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Days / Year'), 'min' => 0]) }}
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/leaves/types/update.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="{{ VC::TXS }} {{ VC::TXT_MT }}">{{ __('Requested leave type was not found or is unavailable.') }}</div>
@endif
