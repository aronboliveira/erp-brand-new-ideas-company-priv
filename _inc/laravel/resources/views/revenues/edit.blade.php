@php
$revenue ??= null;
	$accounts ??= [];
	$customers ??= [];
	$categories ??= [];
	$revenueId ??= '';
	$revenueReceipt ??= '';
	try {
		$revenueId = data_get($revenue ?? null, 'id') ?? '';
		$revenueReceipt = data_get($revenue ?? null, 'add_receipt') ?? '';
	} catch (\Error $e) {
		Log::error('Error in revenues/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in revenues/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in revenues/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{{ Form::model($revenue, ['route' => ['revenue.update', $revenueId], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="{{ VC::FM_G }} col-md-6">
            {{ Form::label('date', __('Date'), ['class' => VC::FM_LB]) }}
            {{ Form::date('date', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
        </div>
        <div class="{{ VC::FM_G }} col-md-6">
            {{ Form::label('amount', __('Amount'), ['class' => VC::FM_LB]) }}
            {{ Form::number('amount', null, ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
        </div>
        <div class="{{ VC::FM_G }} col-md-6">
            {{ Form::label('account_id', __('Account'), ['class' => VC::FM_LB]) }}
            {{ Form::select('account_id', $accounts, null, ['class' => VC::FM_CT . ' select', 'required' => 'required']) }}
        </div>
        <div class="{{ VC::FM_G }} col-md-6">
            {{ Form::label('customer_id', __('Customer'), ['class' => VC::FM_LB]) }}
            {{ Form::select('customer_id', $customers, null, ['class' => VC::FM_CT . ' select', 'required' => 'required']) }}
        </div>
        <div class="{{ VC::FM_G }} col-md-12">
            {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
            {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'rows' => 3]) }}
        </div>
        <div class="{{ VC::FM_G }} col-md-6">
            {{ Form::label('category_id', __('Category'), ['class' => VC::FM_LB]) }}
            {{ Form::select('category_id', $categories, null, ['class' => VC::FM_CT . ' select', 'required' => 'required']) }}
        </div>

        <div class="{{ VC::FM_G }} col-md-6">
            {{ Form::label('reference', __('Reference'), ['class' => VC::FM_LB]) }}
            {{ Form::text('reference', null, ['class' => VC::FM_CT]) }}
        </div>

        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('add_receipt', __('Payment Receipt'), ['class' => 'col-form-label']) }}
            {{ Form::file('add_receipt', ['class' => 'form-control', 'id' => 'files']) }}
            <img id="image" src="{{ asset(Storage::url('uploads/revenue')) . '/' . $revenueReceipt }}" class="{{ VC::MT2 }}" style="width:25%;"/>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
</div>
{{ Form::close() }}

<script>
    document.getElementById('files').onchange = function () {
        var src = URL.createObjectURL(this.files[0])
        document.getElementById('image').src = src
    }
</script>
