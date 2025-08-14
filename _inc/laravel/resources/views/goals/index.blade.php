@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Goals')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Goal')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
<div class="float-end">
     @can('create goal')
        <a href="#"
            data-url="{{ route(ViewsConstants::GL.'.create') }}"
            data-bs-toggle="tooltip"
            data-size="lg"
            title="{{ __('Create') }}"
            data-ajax-popup="true"
            data-title="{{ __('Create New Goal') }}"
            class="{{ ViewClassNamesConstants::BT_SM_PM }}">
            <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
        </a>
    @endcan
</div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ ViewClassNamesConstants::RW }}">
        <div class="col-xl-12">
            <div class="{{ ViewClassNamesConstants::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ ViewClassNamesConstants::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('From') }}</th>
                                    <th>{{ __('To') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Is Dashboard Display') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($golas as $gola)
                                    <tr>
                                        <td class="font-style">{{ $gola->name }}</td>
                                        <td class="font-style">{{ __(\App\Models\Goal::$goalType[$gola->type]) }}</td>
                                        <td class="font-style">{{ $gola->from }}</td>
                                        <td class="font-style">{{ $gola->to }}</td>
                                        <td class="font-style">{{ $user?->priceFormat($gola->amount) }}</td>
                                        <td class="font-style">{{ $gola->is_display==1?__('Yes'):__('No') }}</td>
                                        <td class="Action">
                                            <span>
                                                @can('edit goal')
                                                    <div class="{{ ViewClassNamesConstants::ACT_BTN }} {{ ViewClassNamesConstants::BG_P }} {{ ViewClassNamesConstants::MS2 }}">
                                                        <a href="#"
                                                           class="{{ ViewClassNamesConstants::BT_SM_MX3 }} {{ ViewClassNamesConstants::AL_IT_CT }}"
                                                           data-url="{{ route(ViewsConstants::GL.'.edit',$gola->id) }}"
                                                           data-ajax-popup="true"
                                                           data-title="{{ __('Edit Goal') }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Edit') }}">
                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete goal')
                                                    <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                        {!! Collective\Html\FormFacade::open([
                                                            'method'=>'DELETE',
                                                            'route'=>[ViewsConstants::GL.'.destroy',$gola->id],
                                                            'id'=>'delete-form-'.$gola->id
                                                        ]) !!}
                                                        <a href="#"
                                                           class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                           data-confirm-yes="document.getElementById('delete-form-{{$gola->id}}').submit();">
                                                            <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                        </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
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
