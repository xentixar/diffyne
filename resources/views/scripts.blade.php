{{-- 
    Diffyne Scripts
    Include this in your layout before closing </body> tag
--}}

<script>
    window.diffyneConfig = {
        transport: '{{ config('diffyne.transport', 'ajax') }}',
        endpoint: '{{ route('diffyne.update') }}',
        debug: {{ config('diffyne.debug', false) ? 'true' : 'false' }}
    };
</script>
<script src="{{ config('diffyne.asset_url', '/vendor/diffyne') }}/diffyne.js" defer></script>
