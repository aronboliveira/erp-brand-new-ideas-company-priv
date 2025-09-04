@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{ViewsConstants, StacksConstants};
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang = Utility::fetchUserLang();
    $updateRoute = Route::has(ViewsConstants::BNK_ACC . '.update')
        ? route(ViewsConstants::BNK_ACC . '.update', $bankAccount->id)
        : (Route::has(Str::kebab(ViewsConstants::BNK_ACC . '.update'))
            ? route(Str::kebab(ViewsConstants::BNK_ACC . '.update'), $bankAccount->id)
            : '#');
    $formId = 'bank-account-update-form';
    $updateMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::BNK_ACC,
        'bank_account_update_route_unavailable'
    ) ?? 'Bank Account update route is unavailable. Please contact technical support or your domain administrator.';
    $fields = [
        ['name'=>'chart_account_id','type'=>'select','label'=>__('Account'),'options'=>$chart_accounts,'cols'=>6],
        ['name'=>'holder_name',     'type'=>'text',  'label'=>__('Bank Holder Name'),                           'cols'=>6],
        ['name'=>'bank_name',       'type'=>'text',  'label'=>__('Bank Name'),                                  'cols'=>6],
        ['name'=>'account_number',  'type'=>'text',  'label'=>__('Account Number'),                             'cols'=>6],
        ['name'=>'opening_balance', 'type'=>'number','label'=>__('Opening Balance'),'attrs'=>['step'=>'0.01'],'cols'=>6],
        ['name'=>'contact_number',  'type'=>'text',  'label'=>__('Contact Number'),                             'cols'=>6],
        ['name'=>'bank_address',    'type'=>'textarea','label'=>__('Bank Address'),'attrs'=>['rows'=>3],        'cols'=>12],
    ];
@endphp
@if(!empty($bankAccount) && isset($bankAccount?->id))
    {{ Form::model($bankAccount, [
        'url'            => $updateRoute,
        'method'         => 'PUT',
        'id'             => $formId,
        'data-url'       => $updateRoute,
        'data-guard-msg' => $updateMsg,
    ]) }}
        <div class="modal-body">
            <div class="row">
                @foreach($fields as $f)
                    <div class="form-group col-md-{{ $f['cols'] }}">
                        {{ Form::label($f['name'], $f['label'], ['class'=>'form-label']) }}
                        @php
                            $attrs = ['class'=>'form-control','required'=>'required'];
                            if(!empty($f['attrs'])) {
                                $attrs = array_merge($attrs, $f['attrs']);
                            }
                        @endphp
                        @if($f['type']==='select')
                            {{ Form::select($f['name'], $f['options'], null, $attrs + ['placeholder'=>'']) }}
                        @elseif($f['type']==='textarea')
                            {{ Form::textarea($f['name'], null, $attrs) }}
                        @else
                            {{ Form::{$f['type']}($f['name'], null, $attrs) }}
                        @endif
                    </div>
                @endforeach

                @if(!$customFields->isEmpty())
                    <div class="col-md-12">
                        <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                            @include(ViewsConstants::CST_FD . '.formBuilder')
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
        </div>
        <script defer src="{{ asset('assets/js/routes/bank/accounts/edit.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <p class="text-muted">{{ __('No bank account found.') }}</p>
            </div>
        </div>
    </div>
@endif