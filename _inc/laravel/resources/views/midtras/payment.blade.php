@php
    try {
$lang               = is_callable([Utility::class, 'fetchUserLang']) ? Utility::fetchUserLang() : app()->getLocale();
        $fallbackRoute      = Route::has($data['fallback_url'] ?? '')
            ? route($data['fallback_url'], $data ?? [])
            : (Route::has(Str::kebab($data['fallback_url'] ?? ''))
                ? route(Str::kebab($data['fallback_url']), $data ?? [])
                : '#');
        $formId             = 'submit_form';
        $snapToken          = e($data['snap_token'] ?? '');
        $midtransClientKey  = e($data['midtrans_secret'] ?? '');
    } catch (\Throwable $e) {
        \Log::error('midtras/payment — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <!-- @TODO: replace SET_YOUR_CLIENT_KEY_HERE with your client key -->

    <script type="text/javascript"
      src="https://app.sandbox.midtrans.com/snap/snap.js"
      data-client-key="{{ $midtransClientKey }}"></script>
    <!-- Note: replace with src="https://app.midtrans.com/snap/snap.js" for Production environment -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  </head>

  <body>

    <form action="{{ $fallbackRoute }}" id="{{ $formId }}" method="POST" data-url="{{ $fallbackRoute }}">
        @csrf
        <input type="hidden" name="json" id="json_callback">
    </form>

    <script type="text/javascript">
      // For example trigger on button clicked, or any time you need

        // Trigger snap popup. @TODO: Replace TRANSACTION_TOKEN_HERE with your transaction token
        window.snap.pay('{{ $snapToken }}', {
          onSuccess: function(result){
            /* You may add your own implementation here */
            console.log(result);
            send_response_to_form(result);
          },
          onPending: function(result){
            /* You may add your own implementation here */
            console.log(result);
            send_response_to_form(result);
          },
          onError: function(result){
            /* You may add your own implementation here */
            console.log(result);
            send_response_to_form(result);
          },
          onClose: function(){
            /* You may add your own implementation here */
            alert('you closed the popup without finishing the payment');
          }
        })

      function send_response_to_form(result){
        document.getElementById('json_callback').value = JSON.stringify(result);
        $('#submit_form').submit();
      }
    </script>
  </body>
</html>
