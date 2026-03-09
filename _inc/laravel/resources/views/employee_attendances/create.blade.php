@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC, UsersConstants as UC};
    use App\Models\Utility;
    try {
        $lang       = Utility::fetchUserLang();
        $routeName  = VW::EMP_ATD . '.store';
        $storeUrl   = Route::has($routeName) ? route($routeName) : '#';
        $formId     = 'attendance-store-form';
        $empList    = $employees ?? collect();
        $guardMsg   = Utility::fetchLinkMessage(
            $lang,
            VW::EMP_ATD,
            'attendance_index_route_unavailable'
        ) ?? 'Attendance index route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('employee_attendances/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        $storeUrl  ??= '#';
        $formId    ??= 'attendance-store-form';
        $empList   ??= collect();
        $guardMsg  ??= '';
    }
@endphp

{{ Form::open([
    'url'            => $storeUrl,
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $storeUrl,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">

            {{-- Employee select --}}
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label(UC::COL_EMP_ID, __('Employee'), ['class' => VC::FM_LB]) }}
                    {{ Form::select(
                        UC::COL_EMP_ID,
                        $empList->prepend(__('Select Employee'), '')->toArray(),
                        null,
                        ['class' => VC::FM_CT_SL, 'required' => 'required']
                    ) }}
                </div>
            </div>

            {{-- Date --}}
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('date', __('Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date(
                        'date',
                        now()->format('Y-m-d'),
                        ['class' => VC::FM_CT, 'required' => 'required']
                    ) }}
                </div>
            </div>

            {{-- Clock In --}}
            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('clock_in', __('Clock In'), ['class' => VC::FM_LB]) }}
                    {{ Form::time(
                        'clock_in',
                        null,
                        ['class' => VC::FM_CT, 'required' => 'required']
                    ) }}
                </div>
            </div>

            {{-- Clock Out --}}
            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('clock_out', __('Clock Out'), ['class' => VC::FM_LB]) }}
                    {{ Form::time(
                        'clock_out',
                        null,
                        ['class' => VC::FM_CT, 'required' => 'required']
                    ) }}
                </div>
            </div>

        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_SM_CT }}" data-bs-dismiss="modal">
        {{ Form::submit(__('Create'), ['class' => VC::BT_SM_PM]) }}
    </div>
{{ Form::close() }}
