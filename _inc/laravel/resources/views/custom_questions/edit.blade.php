@php
$lang ??= 'en';
	$canUpdate ??= false;
	$routeName ??= null;
	$hasRoute ??= false;
	$actionUrl ??= '#';
	$guardMsg ??= '';
	$formId ??= 'edit-custom-question-form-x';
	$isRequiredOptions ??= [];
	$customQuestionId ??= null;
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$canUpdate = Gate::check('edit custom question');
		$customQuestionId = data_get($customQuestion ?? null, 'id');
		$routeName = $canUpdate ? VW::CST_QT . '.update' : null;
		$hasRoute = $routeName && Route::has($routeName);
		$actionUrl = ($hasRoute && $customQuestionId) ? (route($routeName, $customQuestionId) ?? '#') : '#';
		$guardMsg = Utility::fetchLinkMessage($lang, VW::CST_QT, 'update_custom_question_route_unavailable')
			?? 'Update Custom Question route is unavailable. Please contact technical support or your domain administrator.';
		$formId = 'edit-custom-question-form-' . ($customQuestionId ?? 'x');
		$isRequiredOptions = (is_array($is_required ?? null) && $is_required)
			? $is_required
			: ['0' => __('No'), '1' => __('Yes')];
	} catch (\Error $e) {
		Log::error('Error in custom_questions/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in custom_questions/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in custom_questions/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($customQuestion) && isset($customQuestion->id))
    {!! Form::model($customQuestion, [
        $hasRoute ? 'route' : 'url' => $hasRoute ? [$routeName, $customQuestion->id ?? ''] : '#',
        'method' => 'PUT',
        'id'     => $formId,
        'data-url' => $actionUrl,
        'data-guard-msg' => $guardMsg,
        'data-sv-localized' => 'true'
    ]) !!}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('question', __('Question'), ['class' => VC::FM_LB]) }}
                        {{ Form::text('question', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter question')]) }}
                    </div>
                </div>
                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('is_required', __('Is Required'), ['class' => VC::FM_LB]) }}
                        {{ Form::select('is_required', $isRequiredOptions, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer>
            (function(){
                try{
                    var form = document.getElementById('{{ $formId }}');
                    if(!form) return;
                    function toastOrAlert(msg){
                        try{
                            if(window.bootstrap && window.bootstrap.Toast){
                                var c=document.getElementById('toast-container');
                                if(!c){ c=document.createElement('div'); c.id='toast-container'; c.className='position-fixed bottom-0 end-0 p-3'; document.body.appendChild(c); }
                                var t=document.createElement('div');
                                t.className='toast align-items-center text-bg-danger border-0';
                                t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                t.innerHTML='<div class="{{ VC::DFL }}"><div class="toast-body"></div><button type="button" class="{{ VC::BT_CL }} btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>';
                                t.querySelector('.toast-body').textContent = msg || '#';
                                c.appendChild(t);
                                bootstrap.Toast.getOrCreateInstance(t,{delay:4000}).show();
                            } else { alert(msg || '#'); }
                        }catch(e){ alert(msg || '#'); }
                    }
                    form.addEventListener('submit', function(e){
                        try{
                            var url = form.getAttribute('data-url') || form.getAttribute('action') || '#';
                            if(!url || url === '#'){
                                e.preventDefault();
                                toastOrAlert(form.getAttribute('data-guard-msg'));
                            }
                        }catch(_){}
                    }, {passive:false});
                }catch(_){}
            })();
        </script>
    {!! Form::close() !!}
@else
    <div class="{{ VC::ALT_DNG }}">
        {{ __('Invalid Custom Question data.') }}
    </div>
    @php
 return;
@endphp
@endif
