@php
    try {
        $lang = Utility::fetchUserLang();
} catch (\Throwable $e) {
        $lang ??= 'en';
        \Log::error('warnings/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Warning') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Warning') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create warning')
            <a href="#"
               data-url="{{ route('warnings.create') }}"
               data-size="lg"
               data-ajax-popup="true"
               data-title="{{ __('Create New Warning') }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Warning By') }}</th>
                                    <th>{{ __('Warning To') }}</th>
                                    <th>{{ __('Subject') }}</th>
                                    <th>{{ __('Warning Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    @if(Gate::check('edit warning') || Gate::check('delete warning'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach ($warnings as $warning)
                                    <tr>
                                        <td>{{ !empty($warning->WarningBy) ? $warning->WarningBy->name : '' }}</td>
                                        <td>{{ !empty($warning->warningTo) ? $warning->warningTo->name : '' }}</td>
                                        <td>{{ $warning->subject }}</td>
                                        <td>{{ isset($user) && $user ? $user->dateFormat($warning->warning_date) : ($warning->warning_date ?? '') }}</td>
                                        <td>{{ $warning->description }}</td>

                                        @if(Gate::check('edit warning') || Gate::check('delete warning'))
                                            <td>
                                                @can('edit warning')
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a href="#"
                                                           class="{{ VC::BT_SM_FL_CT }}"
                                                           data-size="lg"
                                                           data-url="{{ URL::to('warning/'.$warning->id.'/edit') }}"
                                                           data-ajax-popup="true"
                                                           data-title="{{ __('Edit Warning') }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Edit') }}"
                                                           data-original-title="{{ __('Edit') }}">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('delete warning')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Collective\Html\FormFacade::open([
                                                            'method' => 'DELETE',
                                                            'route'  => ['warnings.destroy', $warning->id],
                                                            'id'     => 'delete-form-'.$warning->id
                                                        ]) !!}
                                                            <a href="#"
                                                               class="{{ VC::BT_SM_CT_PR }}"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Delete') }}"
                                                               data-original-title="{{ __('Delete') }}"
                                                               data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                               data-confirm-yes="document.getElementById('delete-form-{{$warning->id}}').submit();">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        @endif
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
