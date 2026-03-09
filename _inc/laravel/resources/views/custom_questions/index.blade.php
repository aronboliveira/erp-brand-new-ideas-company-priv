@php
    try {
$lang                    = Utility::fetchUserLang();
    } catch (\Throwable $e) {
        \Log::error('custom_questions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Custom Question for interview') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"{{ Route::has('dashboard') ? '' : ' aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Custom-Question') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create custom question')
            @php
                try {
                    $createRouteName         = ViewsConstants::CST_QT . '.create';
                    $createUrl               = Route::has($createRouteName)
                        ? route($createRouteName)
                        : (Route::has(Str::kebab($createRouteName))
                            ? route(Str::kebab($createRouteName))
                            : '#');
                    $linkId                  = 'custom-question-create-btn';
                    $guardMsg                = Utility::fetchLinkMessage(
                        $lang,
                        ViewsConstants::CST_QT,
                        'custom_question_create_route_unavailable'
                    ) ?? 'Create Custom Question route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('custom_questions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a
                id="{{ $linkId }}"
                href="#"
                data-url="{{ $createUrl }}"
                data-size="lg"
                data-ajax-popup="true"
                data-title="{{ __('Create New Custom Question') }}"
                class="{{ VC::BT_SM_PM }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                data-guard-msg="{{ base64_encode($guardMsg) }}"
                {{ $createUrl === '#' ? 'aria-disabled="true"' : '' }}
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
                <script defer src="{{ asset('assets/js/routes/customQuestions/create.js') }}"></script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Question') }}</th>
                                    <th>{{ __('Is Required') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @php
                                    $items = ((is_array($questions ?? null) && count($questions ?? [])) || (($questions ?? null) instanceof Collection && ($questions)->isNotEmpty())) ? $questions : [];
@endphp
                                @if(!empty($items))
                                    @foreach($items as $question)
                                        @php
                                            try {
                                                $qText = isset($question->question) && $question->question !== '' ? $question->question : __('No question text available');
                                                $reqKey = isset($question->is_required) ? $question->is_required : null;
                                                $isYes = $reqKey === 'yes';
                                                $reqLabel = isset(CustomQuestion::$is_required[$reqKey]) ? CustomQuestion::$is_required[$reqKey] : __('No requirement info available');
                                            } catch (\Throwable $e) {
                                                \Log::error('custom_questions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <tr>
                                            <td>{{ $qText }}</td>
                                            <td><span class="{{ VC::BDG }} {{ $isYes ? 'bg-primary' : 'bg-danger' }} p-2 px-3 rounded">{{ __($reqLabel) }}</span></td>
                                            <td>
                                                @can('edit custom question')
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        @php
                                                            try {
                                                                $editName = ViewsConstants::CST_QT . '.edit';
                                                                $editUrl = Route::has($editName) ? route($editName, $question->id) : (Route::has(Str::kebab($editName)) ? route(Str::kebab($editName), $question->id) : '#');
                                                                $editLinkId = 'custom-question-edit-link-' . $question->id;
                                                                $editGuard = Utility::fetchLinkMessage($lang, ViewsConstants::CST_QT, 'custom_question_edit_route_unavailable') ?? 'Edit Custom Question route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('custom_questions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <a id="{{ $editLinkId }}" href="{{ $editUrl }}" data-url="{{ $editUrl }}" data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-title="{{ __('Edit Custom Question') }}" data-guard-msg="{{ base64_encode($editGuard) }}" data-route-guard class="{{ VC::BT_SM_FL_CT }}" {{ $editUrl === '#' ? 'aria-disabled=true' : '' }}>
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete custom question')
                                                    @php
                                                        try {
                                                            $destroyName = ViewsConstants::CST_QT . '.destroy';
                                                            $destroyUrl = Route::has($destroyName) ? route($destroyName, $question->id) : (Route::has(Str::kebab($destroyName)) ? route(Str::kebab($destroyName), $question->id) : '#');
                                                            $delFormId = 'delete-form-' . $question->id;
                                                            $delLinkId = 'delete-custom-question-link-' . $question->id;
                                                            $delGuard = Utility::fetchLinkMessage($lang, ViewsConstants::CST_QT, 'custom_question_destroy_route_unavailable') ?? 'Delete Custom Question route is unavailable. Please contact technical support or your domain administrator.';
                                                        } catch (\Throwable $e) {
                                                            \Log::error('custom_questions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }} {{ VC::MS2 }}">
                                                        {!! Collective\Html\FormFacade::open(['method'=>'DELETE','url'=>$destroyUrl,'id'=>$delFormId]) !!}
                                                            <a id="{{ $delLinkId }}" href="#" class="{{ VC::BT_SM_CT_PR }}" data-url="{{ $destroyUrl }}" data-guard-msg="{{ base64_encode($delGuard) }}" data-route-guard data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3">
                                            <div class="{{ VC::TXCT }} {{ VC::TX_MUTED }}">{{ __('No custom questions available') }}</div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
