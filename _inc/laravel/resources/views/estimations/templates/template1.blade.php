@php
$usr ??= null;
	$siteRtl ??= (string)'';
	$SITE_RTL ??= (string)'';
	$color ??= (string)'#ffffff';
	$font_color ??= (string)'#000000';
	$img ??= (string)'';
	$settings ??= [];
	$estimation ??= null;
	$client ??= null;
	$items ??= [];
	$border_color ??= (string)'black';
	$lang ??= (string)'';
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
		$border_color = (isset($color) && $color === '#ffffff') ? 'black' : (string)($color ?? 'black');
	} catch (\Throwable $e) {
		Log::error('border_color_compute_failed', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
		$border_color = 'black';
	}
	try {
		$items = (isset($estimation) && isset($estimation->getProducts) && is_countable($estimation->getProducts) && count($estimation->getProducts) > 0) ? $estimation->getProducts : [];
	} catch (\Throwable $e) {
		Log::error('estimation_items_normalization_failed', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
		$items = [];
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
@endphp
<!DOCTYPE html>
<html lang="{{ $lang ? (str_replace('_','-',is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG)) : DatabaseConstants::DEFAULT_LANG }}" dir="{{ (($siteRtl ?? '') === 'on' || ($SITE_RTL ?? '') === 'on') ? 'rtl' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://fonts.googleapis.com/css?family=Lato&amp;display=swap" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/routes/estimations/template1.css') }}">
        @if(($SITE_RTL ?? '') === 'on' || ($siteRtl ?? '') === 'on')
        <link rel="stylesheet" href="{{ asset('css/bootstrap-rtl.css') }}">
        @endif
    </head>
    <body class="overflow-x-hidden">
        <div class="container">
            <div id="app">
                <div class="editor">
                    <div class="invoice-preview-inner">
                        <div class="editor-content">
                            <div class="preview-main client-preview">
                                <div data-v-f2a183a6 class="d" id="boxes" style="width:800px;margin-left:auto;margin-right:auto;">
                                    <div data-v-f2a183a6 class="d-header" style="background: {{ isset($color) && $color !== '' ? $color : '#ffffff' }};color: {{ isset($font_color) && $font_color !== '' ? $font_color : '#000000' }}">
                                        <div data-v-f2a183a6 class="d-header-inner">
                                            <div data-v-f2a183a6 class="d-header-50">
                                                <div data-v-f2a183a6 class="d-header-brand">
                                                    <img src="{{ isset($img) && $img !== '' ? $img : asset('assets/img/placeholder.png') }}" style="max-width:100px">
                                                </div>
                                                <div data-v-f2a183a6 class="break-25"></div>
                                                <p data-v-f2a183a6>{{ isset($settings['company_name']) && $settings['company_name'] !== '' ? $settings['company_name'] : __('No company name available') }}</p>
                                                <p data-v-f2a183a6>
                                                    {{ isset($settings['company_address']) && $settings['company_address'] !== '' ? $settings['company_address'] : __('No address available for company') }}
                                                    {!! (isset($settings['company_city']) && $settings['company_city'] !== '') ? '<br> '.e($settings['company_city']).',' : __('No city available for company') !!}
                                                    {{ isset($settings['company_state']) && $settings['company_state'] !== '' ? $settings['company_state'] : __('No state available for company') }}
                                                    {!! (isset($settings['company_zipcode']) && $settings['company_zipcode'] !== '') ? ' - '.e($settings['company_zipcode']) : __('No zipcode available for company') !!}
                                                    {!! (isset($settings['company_country']) && $settings['company_country'] !== '') ? '<br>'.e($settings['company_country']) : __('No country available for company') !!}
                                                </p>
                                            </div>
                                            <div data-v-f2a183a6 class="d-header-50 d-right">
                                                <div data-v-f2a183a6 class="d-title">{{ __('ESTIMATION') }}</div>
                                                <table data-v-f2a183a6 class="summary-table">
                                                    <tbody data-v-f2a183a6>
                                                        <tr>
                                                            <td>{{ __('Number') }}:</td>
                                                            <td>
                                                                @if(isset($estimation->estimation_id))
                                                                    {{ (is_object($usr) && method_exists($usr,'estimateNumberFormat')) ? (string)$usr->estimateNumberFormat($estimation->estimation_id) : (string)$estimation->estimation_id }}
                                                                @else
                                                                    {{ __('No estimation number available') }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>{{ __('Issue Date') }}:</td>
                                                            <td>
                                                                @if(isset($estimation->issue_date) && $estimation->issue_date !== '')
                                                                    {{ (is_object($usr) && method_exists($usr,'dateFormat')) ? (string)$usr->dateFormat($estimation->issue_date) : (string)$estimation->issue_date }}
                                                                @else
                                                                    {{ __('No issue date available') }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div data-v-f2a183a6 class="d-body">
                                        <div data-v-f2a183a6 class="d-bill-to"><strong data-v-f2a183a6>{{ __('Bill To') }}:</strong>
                                            <p>
                                                {{ isset($client->name) && $client->name !== '' ? $client->name : __('No client name available') }}<br>
                                                {{ isset($client->email) && $client->email !== '' ? $client->email : __('No client email available') }}
                                            </p>
                                            <div data-v-f2a183a6 class="d-table">
                                                <div data-v-f2a183a6 class="d-table">
                                                    <div data-v-f2a183a6 class="d-table-tr" style="background: {{ isset($color) && $color !== '' ? $color : '#ffffff' }};color: {{ isset($font_color) && $font_color !== '' ? $font_color : '#000000' }}">
                                                        <div class="d-table-th w-2">{{ __('#') }}</div>
                                                        <div class="d-table-th w-7">{{ __('Item') }}</div>
                                                        <div class="d-table-th w-5">{{ __('Price') }}</div>
                                                        <div class="d-table-th w-5">{{ __('Quantity') }}</div>
                                                        <div class="d-table-th w-4 {{ VC::TX_END }}">{{ __('Totals') }}</div>
                                                    </div>
                                                    <div class="d-table-body">
                                                        @if(!empty($items))
                                                            @foreach($items as $key => $item)
                                                                @php
                                                                    try {
                                                                        $price = isset($item->pivot->price) ? (float)$item->pivot->price : null;
                                                                        $qty = isset($item->pivot->quantity) ? (float)$item->pivot->quantity : null;
                                                                        $lineTotal = (is_numeric($price) && is_numeric($qty)) ? ($price * $qty) : null;
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('estimations/templates/template1 — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <div class="d-table-tr" style="border-bottom:1px solid {{ $border_color }};">
                                                                    <div class="d-table-td w-2"><span>{{ (int)$key + 1 }}</span></div>
                                                                    <div class="d-table-td w-7"><pre data-v-f2a183a6>{{ isset($item->name) && $item->name !== '' ? $item->name : __('No item name available') }}</pre></div>
                                                                    <div class="d-table-td w-5"><pre data-v-f2a183a6>
                                                                        @if(!is_null($price))
                                                                            {{ (is_object($usr) && method_exists($usr,'priceFormat')) ? (string)$usr->priceFormat($price) : number_format((float)$price,2) }}
                                                                        @else
                                                                            {{ __('No price available') }}
                                                                        @endif
                                                                    </pre></div>
                                                                    <div class="d-table-td w-5"><pre data-v-f2a183a6>{{ !is_null($qty) ? $qty : __('No quantity available') }}</pre></div>
                                                                    <div class="d-table-td w-4 {{ VC::TX_END }}"><span>
                                                                        @if(!is_null($lineTotal))
                                                                            {{ (is_object($usr) && method_exists($usr,'priceFormat')) ? (string)$usr->priceFormat($lineTotal) : number_format((float)$lineTotal,2) }}
                                                                        @else
                                                                            {{ __('No total available') }}
                                                                        @endif
                                                                    </span></div>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="d-table-tr" style="border-bottom:1px solid {{ $border_color }};">
                                                                <div class="d-table-td w-2"><span>-</span></div>
                                                                <div class="d-table-td w-7"><pre data-v-f2a183a6>-</pre></div>
                                                                <div class="d-table-td w-5"><pre data-v-f2a183a6>-</pre></div>
                                                                <div class="d-table-td w-5"><pre data-v-f2a183a6>-</pre></div>
                                                                <div class="d-table-td w-4 {{ VC::TX_END }}"><span>-</span></div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div data-v-f2a183a6 class="d-table-footer">
                                                        <div data-v-f2a183a6 class="d-table-controls"></div>
                                                        <div data-v-f2a183a6 class="d-table-summary">
                                                            @php
                                                                try {
                                                                    $subtotal = (float)($estimation?->getSubTotal() ?? 0);
                                                                    $discount = (float)($estimation->discount ?? 0);
                                                                    $tax = (float)($estimation?->getTax() ?? 0);
                                                                    $total = $subtotal - $discount + $tax;
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('estimations/templates/template1 — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            <div data-v-f2a183a6 class="d-table-summary-item">
                                                                <div data-v-f2a183a6 class="d-table-label">{{ __('Subtotal') }}:</div>
                                                                <div data-v-f2a183a6 class="d-table-value">{{ (is_object($usr) && method_exists($usr,'priceFormat')) ? (string)$usr->priceFormat($subtotal) : number_format($subtotal,2) }}</div>
                                                            </div>
                                                            @if($discount > 0)
                                                                <div data-v-f2a183a6 class="d-table-summary-item">
                                                                    <div data-v-f2a183a6 class="d-table-label">{{ __('Discount') }}:</div>
                                                                    <div data-v-f2a183a6 class="d-table-value">{{ (is_object($usr) && method_exists($usr,'priceFormat')) ? (string)$usr->priceFormat($discount) : number_format($discount,2) }}</div>
                                                                </div>
                                                            @endif
                                                            @if($tax > 0)
                                                                <div data-v-f2a183a6 class="d-table-summary-item">
                                                                    <div data-v-f2a183a6 class="d-table-label">{{ (data_get($estimation,'tax.name') ?: __('Tax')) }} ({{ (string)(data_get($estimation,'tax.rate') ?? '0') }}%):</div>
                                                                    <div data-v-f2a183a6 class="d-table-value">{{ (is_object($usr) && method_exists($usr,'priceFormat')) ? (string)$usr->priceFormat($tax) : number_format($tax,2) }}</div>
                                                                </div>
                                                            @endif
                                                            <div data-v-f2a183a6 class="d-table-summary-item" style="border-top:1px solid {{ $border_color }};border-bottom:1px solid {{ $border_color }};">
                                                                <div data-v-f2a183a6 class="d-table-label">{{ __('Total') }}:</div>
                                                                <div data-v-f2a183a6 class="d-table-value">{{ (is_object($usr) && method_exists($usr,'priceFormat')) ? (string)$usr->priceFormat($total) : number_format($total,2) }}</div>
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
                                </div>
                                @if(empty($estimation) || empty($client))
                                    <div class="{{ VC::TXCT }} {{ VC::MT3 }}">{{ __('Some estimation or client data is missing') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
