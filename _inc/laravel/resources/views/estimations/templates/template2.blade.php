@php
# Template 2
	use App\Config\Constants\{DatabaseConstants, ViewClassNamesConstants as VC};
	use App\Models\{Utility};
	use Illuminate\Support\Facades\{Auth, Log};
	use InvalidArgumentException;
	use RuntimeException;
	use TypeError;
	$usr ??= null;
	$lang ??= (string)'';
	$siteRtl ??= (string)'';
	$color ??= (string)'#ffffff';
	$img ??= (string)'';
	$meta_title ??= (string)'';
	$meta_desc ??= (string)'';
	$settings ??= [];
	$estimation ??= null;
	$client ??= null;
	$items ??= [];
	$borderColor ??= (string)'black';
	try {
		$usr = Auth::user();
	} catch (InvalidArgumentException $e) {
		Log::error('auth_user_fetch_failed', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (RuntimeException $e) {
		Log::error('auth_user_runtime_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (TypeError $e) {
		Log::error('auth_user_type_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Error $e) {
		Log::error('auth_user_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Exception $e) {
		Log::error('auth_user_exception', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Throwable $e) {
		Log::error('auth_user_throwable', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	}
	try {
		$lang = (string)(Utility::fetchUserLang(user:$usr) ?? '');
	} catch (InvalidArgumentException $e) {
		Log::error('fetch_user_lang_invalid_argument', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (RuntimeException $e) {
		Log::error('fetch_user_lang_runtime_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (TypeError $e) {
		Log::error('fetch_user_lang_type_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Error $e) {
		Log::error('fetch_user_lang_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Exception $e) {
		Log::error('fetch_user_lang_exception', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Throwable $e) {
		Log::error('fetch_user_lang_throwable', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	}
	try {
		$items = (isset($estimation->getProducts) && is_countable($estimation->getProducts) && count($estimation->getProducts) > 0) ? $estimation->getProducts : [];
	} catch (\Throwable $e) {
		Log::error('estimation_items_normalization_failed', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
		$items = [];
	}
	try {
		$borderColor = $color === '#ffffff' ? 'black' : (string)$color;
	} catch (\Throwable $e) {
		Log::error('border_color_compute_failed', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
		$borderColor = 'black';
	}
@endphp
<!DOCTYPE html>
<html lang="{{ $lang ? str_replace('_','-',is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) : DatabaseConstants::DEFAULT_LANG }}" dir="{{ $siteRtl === 'on' ? 'rtl' : '' }}">
    <head>
        @include('fragments.std',['meta_title'=>$meta_title,'meta_desc'=>$meta_desc])
        <link href="https://fonts.googleapis.com/css?family=Lato&amp;display=swap" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/routes/estimations/template2.css') }}">
        @if($siteRtl === 'on')
        <link rel="stylesheet" href="{{ asset('css/bootstrap-rtl.css') }}">
        @endif
    </head>
    <body class="overflow-x-hidden">
        <div class="{{ VC::CT }}">
            <div id="app">
                <div class="editor">
                    <div class="invoice-preview-inner">
                        <div class="editor-content">
                            <div class="preview-main client-preview">
                                <div data-v-37eeda86 class="d" style="width:800px;margin-left:auto;margin-right:auto;" id="boxes">
                                    <div data-v-37eeda86 class="d-inner">
                                        <div data-v-37eeda86 class="row">
                                            <div data-v-37eeda86 class="col-2">
                                                <img src="{{ isset($img) && $img !== '' ? $img : asset('assets/img/placeholder.png') }}" style="max-width:150px">
                                            </div>
                                            <div data-v-37eeda86 class="col-2 text-end">
                                                <p data-v-37eeda86>{{ isset($settings['company_name']) && $settings['company_name'] !== '' ? $settings['company_name'] : __('No company name available') }}</p>
                                                <p data-v-37eeda86>
                                                    {{ isset($settings['company_address']) && $settings['company_address'] !== '' ? $settings['company_address'] : __('No address available for company') }}
                                                    {!! (isset($settings['company_city']) && $settings['company_city'] !== '') ? '<br> '.e($settings['company_city']).',' : __('No city available for company') !!}
                                                    {{ isset($settings['company_state']) && $settings['company_state'] !== '' ? $settings['company_state'] : __('No state available for company') }}
                                                    {!! (isset($settings['company_zipcode']) && $settings['company_zipcode'] !== '') ? ' - '.e($settings['company_zipcode']) : __('No zipcode available for company') !!}
                                                    {!! (isset($settings['company_country']) && $settings['company_country'] !== '') ? '<br>'.e($settings['company_country']) : __('No country available for company') !!}
                                                </p>
                                            </div>
                                        </div>
                                        <div data-v-37eeda86 class="break-50"></div>
                                        <div data-v-37eeda86 class="row">
                                            <div data-v-37eeda86 class="col-66">
                                                <strong class="tu mb5" style="color: {{ $color === '#ffffff' ? 'black' : $color }};">
                                                    {{ __('Bill To') }}:
                                                </strong>
                                                <p>{{ isset($client->name) && $client->name !== '' ? $client->name : __('No client name available') }}<br>{{ isset($client->email) && $client->email !== '' ? $client->email : __('No client email available') }}</p>
                                            </div>
                                            <div data-v-37eeda86 class="col-33">
                                                <strong data-v-37eeda86 class="tu mb5" style="color: {{ $color === '#ffffff' ? 'black' : $color }};">
                                                    {{ __('ESTIMATION') }}
                                                </strong>
                                                <table data-v-37eeda86 class="summary-table">
                                                    <tbody data-v-37eeda86>
                                                        <tr data-v-37eeda86>
                                                            <td data-v-37eeda86 class="tu">{{ __('Number') }}:</td>
                                                            <td data-v-37eeda86 class="text-end">{{ isset($estimation->estimation_id) ? ((string)($usr?->estimateNumberFormat($estimation->estimation_id) ?? $estimation->estimation_id)) : __('No estimation number available') }}</td>
                                                        </tr>
                                                        <tr data-v-37eeda86>
                                                            <td data-v-37eeda86 class="tu">{{ __('Issue Date') }}:</td>
                                                            <td data-v-37eeda86 class="text-end">{{ isset($estimation->issue_date) && $estimation->issue_date !== '' ? ((method_exists($usr, 'dateFormat') ? (string)($usr->dateFormat($estimation->issue_date) ?? $estimation->issue_date) : __('No issue date available'))) : __('No issue date available') }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div data-v-37eeda86 class="break-25"></div>
                                        <div data-v-37eeda86 class="d-table">
                                            <div data-v-37eeda86 class="d-table">
                                                <div data-v-37eeda86 class="tu d-table-tr" style="color: {{ $color === '#ffffff' ? 'black' : $color }};border-bottom:1px solid {{ $color }};border-top:1px solid {{ $color }}">
                                                    <div class="d-table-th w-2">{{ __('#') }}</div>
                                                    <div class="d-table-th w-7">{{ __('Item') }}</div>
                                                    <div class="d-table-th w-5">{{ __('Price') }}</div>
                                                    <div class="d-table-th w-5">{{ __('Quantity') }}</div>
                                                    <div class="d-table-th w-4 text-end">{{ __('Totals') }}</div>
                                                </div>
                                                <div data-v-37eeda86 class="d-table-body">
                                                    @if(!empty($items))
                                                        @foreach($items as $key => $item)
                                                            <div class="d-table-tr" style="border-bottom:1px solid {{ $color }}">
                                                                <div class="d-table-td w-2"><span>{{ (int)$key + 1 }}</span></div>
                                                                <div class="d-table-td w-7"><pre data-v-f2a183a6>{{ isset($item->name) && $item->name !== '' ? $item->name : __('No item name available') }}</pre></div>
                                                                <div class="d-table-td w-5"><pre data-v-f2a183a6>{{ isset($item->pivot->price) ? ((string)($usr?->priceFormat($item->pivot->price) ?? $item->pivot->price)) : __('No price available') }}</pre></div>
                                                                <div class="d-table-td w-5"><pre data-v-f2a183a6>{{ isset($item->pivot->quantity) ? $item->pivot->quantity : __('No quantity available') }}</pre></div>
                                                                <div class="d-table-td w-4 text-end"><span>{{ isset($item->pivot->price,$item->pivot->quantity) ? ((string)($usr?->priceFormat($item->pivot->price * $item->pivot->quantity) ?? ($item->pivot->price * $item->pivot->quantity))) : __('No total available') }}</span></div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="d-table-tr" style="border-bottom:1px solid {{ $color }}">
                                                            <div class="d-table-td w-2"><span>-</span></div>
                                                            <div class="d-table-td w-7"><pre data-v-f2a183a6>-</pre></div>
                                                            <div class="d-table-td w-5"><pre data-v-f2a183a6>-</pre></div>
                                                            <div class="d-table-td w-5"><pre data-v-f2a183a6>-</pre></div>
                                                            <div class="d-table-td w-4 text-end"><span>-</span></div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div data-v-37eeda86 class="d-table-footer">
                                                    <div data-v-37eeda86 class="d-table-controls"></div>
                                                    <div class="d-table-summary">
                                                        @php
                                                            $subtotal = (float)($estimation?->getSubTotal() ?? 0);
                                                            $discount = (float)($estimation->discount ?? 0);
                                                            $tax = (float)($estimation?->getTax() ?? 0);
                                                            $total = $subtotal - $discount + $tax;
                                                            $priceFormatAvailable = method_exists($usr, 'priceFormat');
                                                        @endphp
                                                        <div class="d-table-summary-item">
                                                            <div class="tu d-table-label">{{ __('Subtotal') }}:</div>
                                                            <div class="d-table-value">{{ (string)($priceFormatAvailable ? $usr->priceFormat($subtotal) : number_format($subtotal,2)) }}</div>
                                                        </div>
                                                        @if($discount > 0)
                                                            <div class="d-table-summary-item">
                                                                <div class="tu d-table-label">{{ __('Discount') }}:</div>
                                                                <div class="d-table-value">{{ (string)($priceFormatAvailable ? $usr->priceFormat($discount) : number_format($discount,2)) }}</div>
                                                            </div>
                                                        @endif
                                                        @if($tax > 0)
                                                            <div class="d-table-summary-item">
                                                                <div class="tu d-table-label">{{ (data_get($estimation,'tax.name') ?: __('Tax')) }} ({{ (string)(data_get($estimation,'tax.rate') ?? '0') }}%):</div>
                                                                <div class="d-table-value">{{ (string)($priceFormatAvailable ? $usr->priceFormat($tax) : number_format($tax,2)) }}</div>
                                                            </div>
                                                        @endif
                                                        <div class="d-table-summary-item" style="border-top:1px solid {{ $color }};border-bottom:1px solid {{ $color }}">
                                                            <div class="tu d-table-label">{{ __('Total') }}:</div>
                                                            <div class="d-table-value">{{ (string)($priceFormatAvailable ? $usr->priceFormat($total) : number_format($total,2)) }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <b>{{ isset($settings['footer_title']) && $settings['footer_title'] !== '' ? $settings['footer_title'] : __('No footer title available') }}</b>
                                            <p>{{ isset($settings['footer_note']) && $settings['footer_note'] !== '' ? $settings['footer_note'] : __('No footer note available') }}</p>
                                        </div>
                                    </div>
                                </div>
                                @if(empty($estimation) || empty($client))
                                    <div class="text-center mt-3">{{ __('Some estimation or client data is missing') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(!isset($preview))
        @include('estimations.script')
        @endif
    </body>
</html>
