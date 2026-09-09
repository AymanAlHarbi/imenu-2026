<div class="card card-profile shadow">
    <div class="px-4">
      <div class="mt-5">
        <h3>{{ __('Dine In / Takeaway / Deliver') }}<span class="font-weight-light"></span></h3>
      </div>
      <div class="card-content border-top">
        <br />
       
        <div class="custom-control custom-radio mb-3 ck-opt">
          <input name="dineType" class="custom-control-input" id="deliveryTypeDinein" type="radio" value="dinein" checked>
          <label class="custom-control-label" for="deliveryTypeDinein">{{ __('Dine In') }}</label>
          <i class="fa fa-cutlery ck-opt-icon" aria-hidden="true"></i>
        </div>
        <div class="custom-control custom-radio mb-3 ck-opt">
          <input name="dineType" class="custom-control-input" id="deliveryTypeTakeAway" type="radio" value="takeaway">
          <label class="custom-control-label" for="deliveryTypeTakeAway">{{ __('Takeaway') }}</label>
          <i class="fa fa-shopping-bag ck-opt-icon" aria-hidden="true"></i>
        </div>
        <div class="custom-control custom-radio mb-3 ck-opt">
            <input name="dineType" class="custom-control-input" id="deliveryTypeDerlivery" type="radio" value="delivery">
            <label class="custom-control-label" for="deliveryTypeDerlivery">{{ __('Delivery') }}</label>
            <i class="fa fa-motorcycle ck-opt-icon" aria-hidden="true"></i>
          </div>

      </div>
      <br />
      <br />
    </div>
  </div>
  <br />