<script src="https://js.pusher.com/7.0.3/pusher.min.js"></script>
<script>
  messenger = "{{ @$id ?? '0' }}";
</script>
<script src="{{ asset('js/chatify/code.js') }}"></script>
@unless(app()->environment('production'))
  <script>
      if (!window.pusherConfig || !Object.entries(window.pusherConfig || []).length)
        window.pusherConfig = {
            key: "{{ config('chatify.pusher.key') }}",
            cluster: "{{ config('chatify.pusher.options.cluster') }}",
            wsHost: "ws-{{ config('chatify.pusher.options.cluster') }}.pusher.com"
        };
  </script>
  <script src="{{ asset('assets/js/routes/vendors/chatify/lang/pusher.js') }}"></script>
  <script src="{{ asset('assets/js/routes/vendors/chatify/pusher.js') }}"></script>
@endunless
