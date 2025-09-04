@php
    use App\Config\Constants\{
        PlansConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Collective\Html\FormFacade as Form;

    $lang = Utility::fetchUserLang();
    $updateRoute = Route::has(ViewsConstants::ANC.'.update')
        ? route(ViewsConstants::ANC.'.update', $announcement->id)
        : '#';
    $formId = 'announcement-update-form';
    $updateMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::ANC,
        'announcement_update_route_unavailable'
    ) ?? 'Announcement update route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@if(!empty($announcement) && isset($announcement?->id))
    {{ Form::model($announcement, [
        'route'             => [$updateRoute],
        'method'            => 'PUT',
        'id'                => $formId,
        'data-url'          => $updateRoute,
        'data-sv-localized' => 'true',
        'data-guard-msg'    => $updateMsg,
    ]) }}
        <div class="modal-body">
            @php $plan = Utility::getChatGPTSettings(); @endphp
            @if($plan?->{PlansConstants::COL_GPT} == 1)
                @php
                    $lang = Utility::fetchUserLang();
                    $aiGenerateRoute = Route::has('generate')
                        ? route('generate', ['announcement'])
                        : '#';
                    $aiGenerateId = 'announcement-ai-generate-link';
                    $aiGenerateMsg = Utility::fetchLinkMessage(
                        $lang,
                        ViewsConstants::ANC,
                        'announcement_generate_route_unavailable'
                    ) ?? 'Generate with AI route is unavailable. Please contact technical support or your domain administrator.';
                @endphp
                <div class="{{ VC::DFL_JCE }}">
                    <a href="#"
                    data-size="md"
                    class="{{ VC::BT_SM_PM }} btn-icon btn-sm"
                    data-ajax-popup-over="true"
                    data-url="{{ route('generate',['announcement']) }}"
                    data-bs-placement="top"
                    title="{{ __('Generate content with AI') }}">
                        <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
            @endif
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('title',__('Announcement Title'),['class'=>VC::FM_LB]) }}
                        {{ Form::text('title',null,['class'=>VC::FM_CT,'placeholder'=>__('Enter Announcement Title')]) }}
                    </div>
                </div>
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('branch_id',__('Branch'),['class'=>VC::FM_LB]) }}
                        {{ Form::select('branch_id',$branch,null,['class'=>VC::FM_CT_SL]) }}
                    </div>
                </div>
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('department_id',__('Department'),['class'=>VC::FM_LB]) }}
                        {{ Form::select('department_id',$departments,null,['class'=>VC::FM_CT_SL]) }}
                    </div>
                </div>
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('start_date',__('Announcement Start Date'),['class'=>VC::FM_LB]) }}
                        {{ Form::date('start_date',null,['class'=>VC::FM_CT]) }}
                    </div>
                </div>
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('end_date',__('Announcement End Date'),['class'=>VC::FM_LB]) }}
                        {{ Form::date('end_date',null,['class'=>VC::FM_CT]) }}
                    </div>
                </div>
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('description',__('Announcement Description'),['class'=>VC::FM_LB]) }}
                        {{ Form::textarea('description',null,['class'=>VC::FM_CT,'placeholder'=>__('Enter Announcement Description')]) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/announcements/edit.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="alert alert-danger">{{ __('Announcement not found.') }}</div>
@endif