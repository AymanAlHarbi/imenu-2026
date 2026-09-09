<br />
<h6 class="heading-small text-muted mb-4">{{ __('Snap Pixel') }}</h6>
<!-- Snap Pixel ID -->
@include('partials.fields',['fields'=>[
    ['required'=>false,'ftype'=>'input','name'=>'Snap Pixel ID', 'placeholder'=>'4e9f31c2-88ab-4a7e-b1d3-92f6a0c11e57', 'additionalInfo'=>'Snap Pixel Info', 'id'=>'snap_pixel_id', 'value'=>$restorant->getConfig('snap_pixel_id','')],
]])