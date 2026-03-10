@php
    use App\Models\Utility;
@endphp
<script src="{{ asset('js/jquery.min.js') }} "></script>
<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
@if(isset($proposal) && !empty($proposal->proposal_id) && methods_exists(Utility::class, 'customerProposalNumberFormat'))
    <script async src="{{ asset('js/routes/proposals/lang/pdf.js') }}"></script>
    <script defer src="{{ asset('js/routes/proposals/pdf.js') }}"></script>
@else
    <script>
        // Failed to load proposal data script
    </script>
@endif

