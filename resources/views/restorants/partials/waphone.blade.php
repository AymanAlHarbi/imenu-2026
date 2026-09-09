<br />
<h6 class="heading-small text-muted mb-4">{{ __('WhatsApp number') }}</h6>
<!-- Whatsapp phone -->
@include('partials.fields',['fields'=>[
    ['required'=>false,'ftype'=>'input','name'=>'Whatsapp phone', 'placeholder'=>'05XXXXXXXX', 'id'=>'whatsapp_phone', 'dir'=>'ltr', 'inputmode'=>'numeric', 'maxlength'=>10, 'additionalInfo'=>'Enter the number starting with 05 — without + or 966', 'value'=>\App\Helpers\SaudiPhone::local($restorant->whatsapp_phone)],
]])  