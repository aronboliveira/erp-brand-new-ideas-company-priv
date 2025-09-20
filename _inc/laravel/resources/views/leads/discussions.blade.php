@php
    use App\Config\Constants\{
        ViewClassNamesConstants as VC,
        ViewsConstants as VW
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang() : app()->getLocale();

    $leadOk   = isset($lead) && !empty($lead);
    $formId   = 'leads-discussion-form';

    $storeBase   = VW::LD . '.discussion.store';
    $storeKebab  = Str::kebab($storeBase);
    $storeName   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);

    $formUrl     = ($leadOk && $storeName) ? route($storeName, [$lead->id]) : '#';
    $formGuard   = Utility::fetchLinkMessage($lang, VW::LD, 'discussion_store_route_unavailable') ?? 'Leads discussion store route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@if($leadOk)
    {{ Form::model($lead, [
        'url'              => $formUrl,
        'method'           => 'POST',
        'id'               => $formId,
        'data-url'         => $formUrl,
        'data-guard-msg'   => $formGuard,
        'data-sv-localized'=> 'true',
    ]) }}
        {{ Form::token() }}
        <div class="modal-body">
            <div class="row">
                <div class="col-12 form-group">
                    {{ Form::label('comment', __('Message'), ['class'=>'form-label']) }}
                    {{ Form::textarea('comment', null, ['class' => 'form-control']) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script async src="{{ asset('assets/js/routes/leads/lang/discussions.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/leads/discussions.js') }}"></script>
    {{ Form::close() }}
@else
    <div>{{ __('No lead could be found') }}</div>
@endif
