
@if(!empty($contract) && isset($contract))
    <div class="modal-body">
        <div class="{{ VC::FM_G }}">
            <label class="{{ VC::FM_LB }}" for="exampleFormControlTextarea1">{{__('Description')}}</label>
            <textarea class="{{ VC::FM_CT }}" id="exampleFormControlTextarea1" rows="10" readonly>{{$contract->description ?? __('No description available')}}</textarea>
        </div>
    </div>
@else
    <div class="modal-body">
        <span>{{__('No contract available for editing the description')}}</span>
    </div>
@endif
