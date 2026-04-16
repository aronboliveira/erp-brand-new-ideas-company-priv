@php
    try {
$lang                   = Utility::fetchUserLang();
        $routeName              = ViewsConstants::CST_QT;
        $createUrl              = Route::has($routeName)
            ? route($routeName)
            : (Route::has(Str::kebab($routeName))
                ? route(Str::kebab($routeName))
                : '#');
        $formId                 = 'custom-question-store-form';
        $guardMsg               = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::CST_QT,
            'custom_question_index_route_unavailable'
        ) ?? 'Custom Question index route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('custom_questions/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url'            => $createUrl,
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $createUrl,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('question', __('Question'), ['class' => VC::FM_LB]) }}
                    {{ Form::text(
                        'question',
                        null,
                        [
                            'class'       => VC::FM_CT,
                            'placeholder' => __('Enter question'),
                            'required'    => 'required'
                        ]
                    ) }}
                </div>
            </div>
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('is_required', __('Is Required'), ['class' => VC::FM_LB]) }}
                    {{ Form::select(
                        'is_required',
                        $isRequired ?? [],
                        null,
                        [
                            'class'    => VC::FM_CT_SL,
                            'required' => 'required'
                        ]
                    ) }}
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input
            type="button"
            value="{{ __('Cancel') }}"
            class="{{ VC::BT_LG }}"
            data-bs-dismiss="modal"
        >
        <input
            type="submit"
            value="{{ __('Create') }}"
            class="{{ VC::BT_PRM }}"
        >
    </div>
    <script defer src="{{ asset('assets/js/routes/customQuestions/store.js') }}"></script>
{{ Form::close() }}
