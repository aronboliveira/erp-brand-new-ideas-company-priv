@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    $user        = Auth::user();
    $profilePath = Utility::getFile('uploads/avatar/');
    $row         = ViewClassNamesConstants::RW;
    $card        = ViewClassNamesConstants::CD;
    $btnPrimary  = ViewClassNamesConstants::BT_SM_PM;
    $btnDanger   = ViewClassNamesConstants::ACT_BTN_DNG_2;
    $flexBetween = ViewClassNamesConstants::DFL_JCB;
    $avatarSm    = ViewClassNamesConstants::AV_CC_SM;
    $tableCls    = ViewClassNamesConstants::TB;
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Assets') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Assets') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ ViewClassNamesConstants::FEND }}">
        @can(PermissionsConstants::CRT_AST)
            <a href="#" data-url="{{ route(ViewsConstants::ACC_AST.'.create') }}"
               data-size="lg" data-ajax-popup="true" data-title="{{ __('Create New Asset') }}"
               data-bs-toggle="tooltip" title="{{ __('Create') }}"
               class="{{ $btnPrimary }}">
                <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ $row }}">
        <div class="col-md-12">
            <div class="{{ $card }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ $tableCls }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Users') }}</th>
                                    <th>{{ __('Purchase Date') }}</th>
                                    <th>{{ __('Supported Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assets as $asset)
                                    <tr>
                                        <td class="font-style">{{ $asset->name }}</td>
                                        <td>
                                            <div class="avatar-group">
                                                @foreach($asset->users($asset->employee_id) as $usr)
                                                    <a href="#" class="avatar {{ $avatarSm }}">
                                                        <img alt="{{ $usr->name }}"
                                                             src="{{ $usr->avatar
                                                ? $profilePath.'/'.$usr->avatar
                                                : asset('/storage/uploads/avatar/avatar.png') }}"
                                                             data-bs-toggle="tooltip"
                                                             title="{{ $usr->name }}">
                                                    </a>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="font-style">{{ $user?->dateFormat($asset->purchase_date) }}</td>
                                        <td class="font-style">{{ $user?->dateFormat($asset->supported_date) }}</td>
                                        <td class="font-style">{{ $user?->priceFormat($asset->amount) }}</td>
                                        <td class="font-style">{{ $asset?->description ?: '-' }}</td>
                                        <td>
                                            <div class="{{ $flexBetween }}">
                                                @can(PermissionsConstants::ED_AST)
                                                    <a href="#"
                                                       data-url="{{ route(ViewsConstants::ACC_AST.'.edit',$asset->id) }}"
                                                       data-ajax-popup="true" data-size="lg"
                                                       data-title="{{ __('Edit Asset') }}"
                                                       data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                       class="{{ $btnPrimary }}">
                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                    </a>
                                                @endcan
                                                @can(PermissionsConstants::DEL_AST)
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method'=>'DELETE',
                                                        'route'=>[ViewsConstants::ACC_AST.'.destroy',$asset->id],
                                                        'id'=>'delete-form-'.$asset->id
                                                    ]) !!}
                                                        <a href="#"
                                                           class="{{ $btnDanger }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                           data-confirm-yes="document.getElementById('delete-form-{{$asset->id}}').submit();">
                                                            <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
