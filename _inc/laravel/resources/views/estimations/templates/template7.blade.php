@php
    # Template 7
    use App.Config\Constants\{DatabaseConstants, ViewClassNamesConstants};
    use App\Models\{Utility};
    use Illuminate\Support\Facades\{Auth, Log};
    use InvalidArgumentException;
    use RuntimeException;
    use TypeError;

    $usr ??= null;
    $lang ??= (string)'';
    $siteRtl ??= (string)'';
    $color ??= (string)'#ffffff';
    $font_color ??= (string)'#000000';
    $img ??= (string)'';
    $meta_title ??= (string)'';
    $meta_desc ??= (string)'';
    $settings ??= [];
    $estimation ??= null;
    $client ??= null;
    $items ??= [];

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

    $hasPriceFormat = is_object($usr) && method_exists($usr, 'priceFormat');
    $hasDateFormat = is_object($usr) && method_exists($usr, 'dateFormat');
    $hasEstimateNumberFormat = is_object($usr) && method_exists($usr, 'estimateNumberFormat');
    $hasGetSubTotal = is_object($estimation) && method_exists($estimation, 'getSubTotal');
    $hasGetTax = is_object($estimation) && method_exists($estimation, 'getTax');

    $subTotalVal = (float)($hasGetSubTotal ? ($estimation->getSubTotal() ?? 0) : 0);
    $discountVal = (float)($estimation->discount ?? 0);
    $taxVal = (float)($hasGetTax ? ($estimation->getTax() ?? 0) : 0);
    $totalVal = $subTotalVal - $discountVal + $taxVal;

    $imgSrc = ($img !== '') ? $img : asset('assets/img/placeholder.png');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang ? (str_replace('_','-',is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG)) : DatabaseConstants::DEFAULT_LANG }}" dir="{{ ($siteRtl ?? '') === 'on' ? 'rtl' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $meta_title !== '' ? $meta_title : __('Estimation') }}</title>
    <meta name="description" content="{{ $meta_desc !== '' ? $meta_desc : __('Estimation document') }}">
    <link href="https://fonts.googleapis.com/css?family=Lato&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/routes/estimations/template1.css') }}">
    @if(($siteRtl ?? '') === 'on')
        <link rel="stylesheet" href="{{ asset('css/bootstrap-rtl.css') }}">
    @endif
</head>
<body class="overflow-x-hidden">
    <div class="{{ ViewClassNamesConstants::CT }}">
        <div id="app">
            <div class="editor">
                <div class="invoice-preview-inner">
                    <div class="editor-content">
                        <div class="preview-main client-preview">
                            <div data-v-1ad6e3b9 class="d" style="width:800px;margin-left:auto;margin-right:auto;" id="boxes">
                                <div data-v-1ad6e3b9 class="d-inner d-no-pad" style="border-top:15px solid {{ $color }}; border-bottom:15px solid {{ $color }}">
                                    <div data-v-1ad6e3b9 class="row grey-box">
                                        <div data-v-1ad6e3b9 class="col-33">
                                            <img data-v-1ad6e3b9 src="{{ $imgSrc }}" class="d-logo" style="max-width:200px;">
                                            <br data-v-1ad6e3b9><br data-v-1ad6e3b9>
                                            <p data-v-1ad6e3b9>{{ ($settings['company_name'] ?? '') !== '' ? $settings['company_name'] : __('No company name available') }}</p>
                                            <pre data-v-1ad6e3b9>{{ ($settings['company_address'] ?? '') !== '' ? $settings['company_address'] : __('No address available for company') }}</pre>
                                            <p data-v-1ad6e3b9>
                                                {!! (($settings['company_city'] ?? '') !== '') ? e($settings['company_city']).', ' : __('No city available for company').' ' !!}
                                                {{ (($settings['company_state'] ?? '') !== '') ? $settings['company_state'] : __('No state available for company') }}
                                                {!! (($settings['company_zipcode'] ?? '') !== '') ? ' - '.e($settings['company_zipcode']) : ' - '.__('No zipcode available for company') !!}
                                            </p>
                                            <p data-v-1ad6e3b9>{{ (($settings['company_country'] ?? '') !== '') ? $settings['company_country'] : __('No country available for company') }}</p>
                                        </div>
                                        <div data-v-1ad6e3b9 class="col-33">&nbsp;</div>
                                        <div data-v-1ad6e3b9 class="col-33">
                                            <h1 data-v-1ad6e3b9 class="fancy-title mb5">{{ __('ESTIMATION') }}</h1>
                                            <br data-v-1ad6e3b9><br data-v-1ad6e3b9>
                                            <table data-v-1ad6e3b9 class="summary-table">
                                                <tbody data-v-1ad6e3b9>
                                                    <tr data-v-1ad6e3b9>
                                                        <td data-v-1ad6e3b9 class="fwb">{{ __('Number') }}:</td>
                                                        <td data-v-1ad6e3b9 class="text-end">
                                                            @if(isset($estimation->estimation_id))
                                                                {{ $hasEstimateNumberFormat ? (string)$usr->estimateNumberFormat($estimation->estimation_id) : (string)$estimation->estimation_id }}
                                                            @else
                                                                {{ __('No estimation number available') }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr data-v-1ad6e3b9>
                                                        <td data-v-1ad6e3b9 class="fwb">{{ __('Issue Date') }}:</td>
                                                        <td data-v-1ad6e3b9 class="text-end">
                                                            @if(isset($estimation->issue_date) && $estimation->issue_date !== '')
                                                                {{ $hasDateFormat ? (string)$usr->dateFormat($estimation->issue_date) : (string)$estimation->issue_date }}
                                                            @else
                                                                {{ __('No issue date available') }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div data-v-1ad6e3b9 class="d-inner-2">
                                        <div data-v-1ad6e3b9 class="row">
                                            <div data-v-1ad6e3b9 class="col-2">
                                                <div data-v-1ad6e3b9 class="col-66">
                                                    <small data-v-1ad6e3b9>{{ __('To') }}:</small>
                                                    <strong data-v-1ad6e3b9 class="sub-title">{{ isset($client->name) && $client->name !== '' ? $client->name : __('No client name available') }}</strong>
                                                    <pre data-v-1ad6e3b9>{{ isset($client->email) && $client->email !== '' ? $client->email : __('No client email available') }}</pre>
                                                </div>
                                            </div>
                                        </div>
                                        <div data-v-1ad6e3b9 class="break-25"></div>
                                        <div data-v-1ad6e3b9 class="d-table">
                                            <div data-v-1ad6e3b9 class="d-table">
                                                <div data-v-1ad6e3b9 class="tu d-table-tr" style="background: {{ $color }}; color: {{ $font_color }}">
                                                    <div data-v-1ad6e3b9 class="d-table-th w-3" style="padding:5px;">{{ __('#') }}</div>
                                                    <div data-v-1ad6e3b9 class="d-table-th w-13" style="padding:5px;">{{ __('Item description') }}</div>
                                                    <div data-v-1ad6e3b9 class="d-table-th w-3" style="padding:5px;">{{ __('Price') }}</div>
                                                    <div data-v-1ad6e3b9 class="d-table-th w-2" style="padding:5px;">{{ __('Qty') }}</div>
                                                    <div data-v-1ad6e3b9 class="d-table-th w-3 text-end" style="padding:5px;">{{ __('Amount') }}</div>
                                                </div>
                                                <div data-v-1ad6e3b9 class="d-table-body">
                                                    @if(!empty($items))
                                                        @foreach($items as $key => $item)
                                                            @php
                                                                $p = isset($item->pivot->price) ? (float)$item->pivot->price : null;
                                                                $q = isset($item->pivot->quantity) ? (float)$item->pivot->quantity : null;
                                                                $lt = (!is_null($p) && !is_null($q)) ? ($p * $q) : null;
                                                            @endphp
                                                            <div data-v-1ad6e3b9 class="d-table-tr">
                                                                <div data-v-1ad6e3b9 class="d-table-td w-3" style="border:1px solid {{ $color }}; padding:5px;"><span data-v-1ad6e3b9>{{ (int)$key + 1 }}</span></div>
                                                                <div data-v-1ad6e3b9 class="d-table-td w-13" style="border:1px solid {{ $color }}; padding:5px;">
                                                                    <pre data-v-1ad6e3b9>{{ isset($item->name) && $item->name !== '' ? $item->name : __('No item name available') }}<br data-v-1ad6e3b9></pre>
                                                                </div>
                                                                <div data-v-1ad6e3b9 class="d-table-td w-3" style="border:1px solid {{ $color }}; padding:5px;">
                                                                    <span data-v-1ad6e3b9>
                                                                        @if(!is_null($p))
                                                                            {{ $hasPriceFormat ? (string)$usr->priceFormat($p) : number_format($p, 2) }}
                                                                        @else
                                                                            {{ __('No price available') }}
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                                <div data-v-1ad6e3b9 class="d-table-td w-2" style="border:1px solid {{ $color }}; padding:5px;"><span data-v-1ad6e3b9>{{ !is_null($q) ? $q : __('No quantity available') }}</span></div>
                                                                <div data-v-1ad6e3b9 class="d-table-td w-3 text-end" style="border:1px solid {{ $color }}; padding:5px;">
                                                                    <span data-v-1ad6e3b9>
                                                                        @if(!is_null($lt))
                                                                            {{ $hasPriceFormat ? (string)$usr->priceFormat($lt) : number_format($lt, 2) }}
                                                                        @else
                                                                            {{ __('No total available') }}
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div data-v-1ad6e3b9 class="d-table-tr">
                                                            <div data-v-1ad6e3b9 class="d-table-td w-3" style="border:1px solid {{ $color }}; padding:5px;"><span data-v-1ad6e3b9>-</span></div>
                                                            <div data-v-1ad6e3b9 class="d-table-td w-13" style="border:1px solid {{ $color }}; padding:5px;"><pre data-v-1ad6e3b9>-<br data-v-1ad6e3b9></pre></div>
                                                            <div data-v-1ad6e3b9 class="d-table-td w-3" style="border:1px solid {{ $color }}; padding:5px;"><span data-v-1ad6e3b9>-</span></div>
                                                            <div data-v-1ad6e3b9 class="d-table-td w-2" style="border:1px solid {{ $color }}; padding:5px;"><span data-v-1ad6e3b9>-</span></div>
                                                            <div data-v-1ad6e3b9 class="d-table-td w-3 text-end" style="border:1px solid {{ $color }}; padding:5px;"><span data-v-1ad6e3b9>-</span></div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div data-v-1ad6e3b9 class="d-table-footer">
                                                    <div data-v-1ad6e3b9 class="d-table-controls"></div>
                                                    <div data-v-1ad6e3b9 class="d-table-summary">
                                                        <div data-v-1ad6e3b9 class="d-table-summary-item">
                                                            <div data-v-1ad6e3b9 class="tu d-table-label">{{ __('Sub Total') }}:</div>
                                                            <div data-v-1ad6e3b9 class="d-table-value">{{ $hasPriceFormat ? (string)$usr->priceFormat($subTotalVal) : number_format($subTotalVal, 2) }}</div>
                                                        </div>
                                                        @if($discountVal > 0)
                                                            <div data-v-1ad6e3b9 class="d-table-summary-item">
                                                                <div data-v-1ad6e3b9 class="tu d-table-label">{{ __('Discount') }}:</div>
                                                                <div data-v-1ad6e3b9 class="d-table-value">{{ $hasPriceFormat ? (string)$usr->priceFormat($discountVal) : number_format($discountVal, 2) }}</div>
                                                            </div>
                                                        @endif
                                                        @if($taxVal > 0)
                                                            <div data-v-1ad6e3b9 class="d-table-summary-item">
                                                                <div data-v-1ad6e3b9 class="tu d-table-label">{{ (data_get($estimation, 'tax.name') ?: __('Tax')) }} ({{ (string)(data_get($estimation, 'tax.rate') ?? '0') }}%):</div>
                                                                <div data-v-1ad6e3b9 class="d-table-value">{{ $hasPriceFormat ? (string)$usr->priceFormat($taxVal) : number_format($taxVal, 2) }}</div>
                                                            </div>
                                                        @endif
                                                        <div data-v-1ad6e3b9 class="d-table-summary-item">
                                                            <div data-v-1ad6e3b9 class="tu d-table-label"><strong data-v-1ad6e3b9>{{ __('Total') }}:</strong></div>
                                                            <div data-v-1ad6e3b9 class="d-table-value"><strong data-v-1ad6e3b9>{{ $hasPriceFormat ? (string)$usr->priceFormat($totalVal) : number_format($totalVal, 2) }}</strong></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div data-v-1ad6e3b9 class="break-25"></div>
                                        <div>
                                            <b>{{ (($settings['footer_title'] ?? '') !== '') ? $settings['footer_title'] : __('No footer title available') }}</b>
                                            <p>{{ (($settings['footer_note'] ?? '') !== '') ? $settings['footer_note'] : __('No footer note available') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
