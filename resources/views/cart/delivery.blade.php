<div class="card card-profile shadow">
    <div class="px-4">
      <div class="mt-5">
        <h3>{{ __('Delivery / Pickup') }}<span class="font-weight-light"></span></h3>
      </div>
      <div class="card-content border-top">
        <br />

        <div class="custom-control custom-radio mb-3 ck-opt">
          <input name="deliveryType" class="custom-control-input" id="deliveryTypeDeliver" type="radio" value="delivery" checked>
          <label class="custom-control-label" for="deliveryTypeDeliver">{{ __('Delivery') }}</label>
          <i class="fa fa-motorcycle ck-opt-icon" aria-hidden="true"></i>
        </div>
        <div class="custom-control custom-radio mb-3 ck-opt">
          <input name="deliveryType" class="custom-control-input" id="deliveryTypePickup" type="radio" value="pickup">
          <label class="custom-control-label" for="deliveryTypePickup">{{ __('Pickup') }}</label>
          <i class="fa fa-shopping-bag ck-opt-icon" aria-hidden="true"></i>
        </div>

      </div>
      <br />
      <br />
    </div>
  </div>
  <br />