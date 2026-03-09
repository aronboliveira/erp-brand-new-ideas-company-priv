@php
$lang ??= 'en';
	$announcementId ??= null;
	$updateRoute ??= '#';
	$formId ??= 'announcement-update-form';
	$updateMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$announcementId = data_get($announcement ?? null, 'id', null);
		$updateRoute = ($announcementId && Route::has(ViewsConstants::ANC . '.update'))
			? (route(ViewsConstants::ANC . '.update', $announcementId) ?? '#')
			: '#';
		$updateMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::ANC,
			'announcement_update_route_unavailable'
		) ?? 'Announcement update route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in announcements/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in announcements/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in announcements/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($announcement) && isset($announcement?->id))
    {{ Form::model($announcement, [
        'url'               => $updateRoute,
        'method'            => 'PUT',
        'id'                => $formId,
        'data-url'          => $updateRoute,
        'data-sv-localized' => 'true',
        'data-guard-msg'    => $updateMsg,
    ]) }}
        <div class="modal-body">
            @php
 $plan = Utility::getChatGPTSettings();
@endphp
            @if($plan?->{PlansConstants::COL_GPT} == 1)
                @php
$aiLang ??= 'en';
                    $aiGenerateRoute ??= '#';
                    $aiGenerateId ??= 'announcement-ai-generate-link';
                    $aiGenerateMsg ??= '';
                    try {
                        $aiLang = Utility::fetchUserLang() ?? 'en';
                        $aiGenerateRoute = Route::has('generate')
                            ? (route('generate', ['announcement']) ?? '#')
                            : '#';
                        $aiGenerateMsg = Utility::fetchLinkMessage(
                            $aiLang,
                            ViewsConstants::ANC,
                            'announcement_generate_route_unavailable'
                        ) ?? 'Generate with AI route is unavailable. Please contact technical support or your domain administrator.';
                    } catch (\Error $e) {
                        AiLog::error('Error in announcements/edit.blade.php AI @php block', [
                            'exception_class' => get_class($e),
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ]);
                    } catch (\Exception $e) {
                        AiLog::error('Exception in announcements/edit.blade.php AI @php block', [
                            'exception_class' => get_class($e),
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ]);
                    } catch (\Throwable $e) {
                        AiLog::error('Throwable in announcements/edit.blade.php AI @php block', [
                            'exception_class' => get_class($e),
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ]);
                    }
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
    <div class="{{ VC::ALT_DNG }}">{{ __('Announcement not found.') }}</div>
@endif
