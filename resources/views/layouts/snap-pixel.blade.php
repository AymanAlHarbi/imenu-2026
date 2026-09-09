{{-- Snap Pixel — iMenu 2026 --}}
@php
    // بكسل خاص بالمطعم إن وُجد وغير فارغ، وإلا البكسل العام من الإعدادات
    $snapRestorant = $restorant ?? ($order->restorant ?? null);
    $snapPixelId = ($snapRestorant ? $snapRestorant->getConfig('snap_pixel_id') : null)
        ?: config('settings.snapchat_pixel');
@endphp
@if ($snapPixelId)
<script type="text/javascript">
(function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function()
{a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};
a.queue=[];var s='script';r=t.createElement(s);r.async=!0;
r.src=n;var u=t.getElementsByTagName(s)[0];
u.parentNode.insertBefore(r,u);})(window,document,
'https://sc-static.net/scevent.min.js');

snaptr('init', '{{ $snapPixelId }}');
snaptr('track', 'PAGE_VIEW');
</script>
@endif