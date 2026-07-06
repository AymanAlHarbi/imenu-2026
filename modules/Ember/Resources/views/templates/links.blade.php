<?php
    $facebook  = $restorant->getConfig('facebook','');
    $instagram = $restorant->getConfig('instagram','');
    $twitter   = $restorant->getConfig('twitter','');
    $youtube   = $restorant->getConfig('youtube','');
    $website   = $restorant->getConfig('website','');
    $phone     = $restorant->phone;
?>
@if (strlen($facebook)>2||strlen($instagram)>2||strlen($twitter)>2||strlen($youtube)>2||strlen($website)>2)
<div class="em-social">
    @if (strlen($facebook)>2) <a href="{{ $facebook }}" aria-label="facebook"><i class="lab la-facebook-f" style="font-size:22px;"></i></a> @endif
    @if (strlen($instagram)>2)<a href="{{ $instagram }}" aria-label="instagram"><i class="lab la-instagram" style="font-size:22px;"></i></a> @endif
    @if (strlen($twitter)>2)  <a href="{{ $twitter }}" aria-label="twitter"><i class="lab la-twitter" style="font-size:22px;"></i></a> @endif
    @if (strlen($youtube)>2)  <a href="{{ $youtube }}" aria-label="youtube"><i class="lab la-youtube" style="font-size:22px;"></i></a> @endif
    @if (strlen($website)>2)  <a href="{{ $website }}" aria-label="website"><i class="las la-globe" style="font-size:22px;"></i></a> @endif
</div>
@endif
