@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Gate, Route};
    use App\Models\Utility;

    $lang = Utility::fetchUserLang();
    $canUpdate = Gate::check('edit custom question');
    $routeName = $canUpdate ? VW::CST_QT . '.update' : null;
    $hasRoute  = $routeName && Route::has($routeName);
    $actionUrl = $hasRoute ? route($routeName, $customQuestion->id ?? '') : '#';
    $guardMsg  = Utility::fetchLinkMessage($lang, VW::CST_QT, 'update_custom_question_route_unavailable')
                ?? 'Update Custom Question route is unavailable. Please contact technical support or your domain administrator.';
    $formId    = 'edit-custom-question-form-'.($customQuestion->id ?? 'x');
    $isRequiredOptions = (is_array($is_required ?? null) && $is_required)
        ? $is_required
        : ['0' => __('No'), '1' => __('Yes')];
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
                                t.innerHTML='<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>';
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
    <div class="alert alert-danger">
        {{ __('Invalid Custom Question data.') }}
    </div>
    @php return; @endphp
@endif
