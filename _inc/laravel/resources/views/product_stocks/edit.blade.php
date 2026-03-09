@php
$lang ??= 'en';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
	} catch (\Error $e) {
		Log::error('Error in product_stocks/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in product_stocks/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in product_stocks/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($productService) && isset($productService->id))
    @php
$productStockUpdateBaseRoute ??= '';
		$productStockUpdateKebabRoute ??= '';
		$productStockUpdateResolvedName ??= null;
		$productStockUpdateRouteArray ??= ['#'];
		$productStockUpdateUrl ??= '#';
		$productStockUpdateGuardMsg ??= '';
		$productStockUpdateFormId ??= 'product-stock-update-form';
		try {
			$productStockUpdateBaseRoute = ViewsConstants::PRD_STK . '.update';
			$productStockUpdateKebabRoute = Str::kebab($productStockUpdateBaseRoute);
			$productStockUpdateResolvedName = Route::has($productStockUpdateBaseRoute) ? $productStockUpdateBaseRoute : (Route::has($productStockUpdateKebabRoute) ? $productStockUpdateKebabRoute : null);
			$productStockUpdateRouteArray = $productStockUpdateResolvedName ? [$productStockUpdateResolvedName, data_get($productService ?? null, 'id')] : ['#'];
			$productStockUpdateUrl = $productStockUpdateResolvedName ? (route($productStockUpdateResolvedName, data_get($productService ?? null, 'id')) ?? '#') : '#';
			$productStockUpdateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRD_STK, 'product_stock_update_route_unavailable') ?? 'Product stock update route is unavailable. Please contact technical support or your domain administrator.';
			$productStockUpdateFormId = 'product-stock-update-form-' . data_get($productService ?? null, 'id', 'x');
		} catch (\Error $e) {
			FormLog::error('Error in product_stocks/edit.blade.php form @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\Exception $e) {
			FormLog::error('Exception in product_stocks/edit.blade.php form @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\Throwable $e) {
			FormLog::error('Throwable in product_stocks/edit.blade.php form @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		}
@endphp
    {!! Form::model($productService, [
        'route'          => $productStockUpdateRouteArray,
        'method'         => 'PUT',
        'id'             => $productStockUpdateFormId,
        'data-url'       => $productStockUpdateUrl,
        'data-guard-msg' => $productStockUpdateGuardMsg
    ]) !!}
        <div class="{{ VC::MD_BD }}">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('Product', __('Product'), ['class' => VC::FM_LB]) }}<br>
                    {{ $productService->name }}
                </div>
                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('Product', __('SKU'), ['class' => VC::FM_LB]) }}<br>
                    {{ $productService->sku }}
                </div>
                <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                    {{ Form::label('quantity', __('Quantity'), ['class' => VC::FM_LB]) }}<span class="{{ VC::TXT_DNG }}">*</span>
                    {{ Form::number('quantity', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
            </div>
        </div>
        <div class="{{ VC::MD_FT }}">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Save') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer>
            (() => {
                const form = document.getElementById('{{ $productStockUpdateFormId }}');
                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                form.setAttribute('data-listener-active', 'true');
                form.addEventListener('submit', e => {
                    try {
                        const url = form.getAttribute('data-url') || '#';
                        if (url !== '#') return;
                        e.preventDefault();
                        const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                        const RG = window.RouteGuard || {};
                        (RG.showToast || (m => alert(m)))(msg);
                        form.setAttribute('data-failed-route', 'true');
                    } catch (error) {}
                });
            })();
        </script>
    {{ Form::close() }}
@else
    <div class="{{ VC::ALT_WRN }} {{ VC::MB3 }}" role="alert">
        {{ __('Product information is not available. Please refresh the page and try again. If the problem persists, please contact technical support or your domain administrator.') }}
    </div>
    @php
 return;
@endphp
@endif
{{--        <div class="{{ VC::FM_G }} quantity">--}}
{{--            <div class="{{ VC::DFL }} radio-check">--}}
{{--                <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP_COLM6 }}">--}}
{{--                    <input type="radio" id="plus_quantity" value="Add" name="quantity_type" class="form-check-input" checked="checked">--}}
{{--                    <label class="form-check-label" for="plus_quantity">{{__('Add Quantity')}}</label>--}}
{{--                </div>--}}
{{--                <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP_COLM6 }}">--}}
{{--                    <input type="radio" id="minus_quantity" value="Less" name="quantity_type" class="form-check-input">--}}
{{--                    <label class="form-check-label" for="minus_quantity">{{__('Less Quantity')}}</label>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
