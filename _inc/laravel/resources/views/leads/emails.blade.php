@php
    use App\Config\Constants\{
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    function resolveRoute(string $base): ?string {
        $k = Str::kebab($base);
        return Route::has($base) ? $base : (Route::has($k) ? $k : null);
    }

    $lang = Utility::fetchUserLang();
    $storeBase = VW::LD.'.emails.store';
    $storeResolved = resolveRoute($storeBase);
    $storeUrl = $storeResolved ? route($storeResolved, [$lead->id ?? null]) : '#';
    $storeGuard = __(Utility::fetchLinkMessage($lang, VW::LD, 'emails_store_route_unavailable') ?? 'Leads email store route is unavailable. Please contact technical support or your domain administrator.');

    $formOpen = $storeResolved
        ? ['route' => [$storeResolved, $lead->id ?? null], 'id' => 'leads-emails-form', 'data-url' => $storeUrl, 'data-guard-msg' => $storeGuard, 'data-sv-localized' => 'true']
        : ['url' => '#', 'id' => 'leads-emails-form', 'data-url' => '#', 'data-guard-msg' => $storeGuard, 'data-sv-localized' => 'true'];
@endphp
@if(!empty($lead) && isset($lead))
    {{ Form::open($formOpen) }}
        <div class="modal-body">
            <div class="row">
                <div class="col-6 form-group">
                    {{ Form::label('to', __('Mail To'), ['class' => VC::FM_LB]) }}
                    {{ Form::email('to', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('subject', __('Subject'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('subject', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
                <div class="col-12 form-group">
                    {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                    {{ Form::textarea('description', null, ['class' => 'summernote-simple', 'id' => 'leads-emails-summernote']) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" id="leads-emails-submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script async src="{{ asset('assets/js/routes/leads/lang/emails.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/leads/emails.js') }}"></script>
    {{ Form::close() }}
    @if(!$storeResolved)
        <div class="text-center text-muted py-3">{{ __('Failed to mount the form because the submit route was not available.') }}</div>
    @endif
@else
    <div class="text-center text-muted py-3">{{ __('No lead could be found') }}</div>
@endif

