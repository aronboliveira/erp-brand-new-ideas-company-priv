@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{
        ViewClassNamesConstants as VC,
        ViewsConstants as VW,
        StacksConstants,
        PermissionsConstants,
        UsersConstants
    };
    use App\Models\Utility;
    use Illuminate\Support\{Facades\Auth, Facades\Route, Str};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
    $storeBase = VW::USR . '.store';
    $storeKebab = Str::kebab($storeBase);
    $storeName  = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl   = $storeName ? route($storeName, []) : '#';
    $storeGuard = Utility::fetchLinkMessage($lang, VW::USR, 'store_user_route_unavailable')
        ?? 'Store user route is unavailable. Please contact technical support or your domain administrator.';
    $formId = 'user-store-form';
    $fields = [
        [
            'name'      => 'name',
            'type'      => 'text',
            'label'     => __('Name'),
            'cols'      => 6,
            'attrs'     => ['class' => 'form-control', 'placeholder' => __('Enter User Name'), 'required' => 'required'],
            'error_key' => 'name',
        ],
        [
            'name'      => 'email',
            'type'      => 'text',
            'label'     => __('Email'),
            'cols'      => 6,
            'attrs'     => ['class' => 'form-control', 'placeholder' => __('Enter User Email'), 'required' => 'required'],
            'error_key' => 'email',
        ],
        [
            'name'      => 'password',
            'type'      => 'password',
            'label'     => __('Password'),
            'cols'      => 6,
            'attrs'     => ['class' => 'form-control', 'placeholder' => __('Enter User Password'), 'required' => 'required', 'minlength' => 6],
            'error_key' => 'password',
        ],
    ];
@endphp

{!! Form::open([
    'url'                  => $storeUrl,
    'method'               => 'POST',
    'id'                   => $formId,
    'data-resolved-action' => $storeUrl,
    'data-guard-msg'       => $storeGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            @foreach($fields as $f)
                @php
                    $cols  = (int)($f['cols'] ?? 6);
                    $type  = $f['type'] ?? 'text';
                    $name  = $f['name'] ?? '';
                    $label = $f['label'] ?? '';
                    $attrs = $f['attrs'] ?? [];
                    $attrs['class'] = VC::FM_CT;
                @endphp
                <div class="col-md-{{ $cols }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label($name, $label, ['class' => VC::FM_LB]) }}

                        @switch($type)
                            @case('password')
                                {{ Form::password($name, $attrs) }}
                                @break

                            @case('text')
                            @default
                                {{ Form::text($name, null, $attrs) }}
                                @break
                        @endswitch

                        @error($f['error_key'])
                            <small class="text-danger" role="alert"><strong>{{ $message }}</strong></small>
                        @enderror
                    </div>
                </div>
            @endforeach

            @if($user?->{UsersConstants::COL_TP} != PermissionsConstants::SA)
                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('role', __('User Role'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('role', $roles ?? [], null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                    @error('role')
                        <small class="text-danger" role="alert"><strong>{{ $message }}</strong></small>
                    @enderror
                </div>
            @else
                {{ Form::hidden('role', PermissionsConstants::CPN) }}
            @endif

            @if(!empty($customFields) && !$customFields->isEmpty())
                <div class="{{ VC::CM6 }}">
                    <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                        @include(VW::CST_FD . '.formBuilder')
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
    </div>
    <script defer src="{{ asset('assets/js/routes/users/store.js') }}"></script>
{!! Form::close() !!}

