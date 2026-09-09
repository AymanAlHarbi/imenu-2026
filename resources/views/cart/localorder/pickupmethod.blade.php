{{-- iMenu 2026 — طريقة الاستلام: من الشباك أو من السيارة --}}
{{-- مهم: dineType مطلوب دائمًا، وإلا اعتبر النظام الطلب "داخليًا" (OrderController سطر 310) --}}
<input type="hidden" name="dineType" value="takeaway">

<div class="card card-profile shadow" id="pickupMethodBox">
  <div class="px-4">
    <div class="mt-5">
      <h3>{{ __('How would you like to receive your order?') }}<span class="font-weight-light"></span></h3>
      <p class="text-muted text-sm mb-0">{{ __('Order ahead and pay at the coffee shop when you pick up') }}</p>
    </div>
    <div class="card-content border-top">
      <br />

      <div class="custom-control custom-radio mb-3 ck-opt">
        <input name="custom[pickup_method]" class="custom-control-input" id="pickupWindow" type="radio" value="window" checked>
        <label class="custom-control-label" for="pickupWindow">{{ __('From the window') }}</label>
        <i class="fa fa-shopping-bag ck-opt-icon" aria-hidden="true"></i>
      </div>

      @php $imenuCarOn = \App\Services\CarPickup::isOn($restorant ?? null); @endphp
      <div class="custom-control custom-radio mb-3 ck-opt" @if(! $imenuCarOn) style="opacity:.55" @endif>
        <input name="custom[pickup_method]" class="custom-control-input" id="pickupCar" type="radio" value="car" @if(! $imenuCarOn) disabled @endif>
        <label class="custom-control-label" for="pickupCar">{{ __('From my car') }}</label>
        <i class="fa fa-car ck-opt-icon" aria-hidden="true"></i>
        @if (! $imenuCarOn)
          <div class="text-muted text-sm mt-1">{{ __('The coffee shop is busy — window pickup only') }}</div>
        @endif
      </div>

      <div id="savedVehicleBox" class="mb-3 p-3 rounded" style="display:none;background:#f6f9fc;">
        <div class="d-flex align-items-center">
          <i class="fa fa-car mr-3" style="font-size:22px;color:#8898aa;"></i>
          <div class="flex-grow-1">
            <div class="font-weight-bold text-sm" id="savedVehicleTitle"></div>
            <div class="text-muted text-sm" id="savedVehiclePlate"></div>
          </div>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="changeVehicleBtn">{{ __('Change') }}</button>
        </div>
      </div>

      <div id="vehicleForm" class="p-3 rounded" style="display:none;background:#f6f9fc;">
        <p class="text-muted text-sm mb-3">{{ __('Your vehicle details — we save them for your next orders') }}</p>

        <div class="row">
          <div class="col-6 form-group">
            <label class="form-control-label text-sm" for="vehicleBrand">{{ __('Brand') }}</label>
            <select class="form-control form-control-sm" id="vehicleBrand" name="custom[vehicle_brand]">
              <option value="">{{ __('Choose') }}</option>
              <option>تويوتا</option>
              <option>هيونداي</option>
              <option>نيسان</option>
              <option>لكزس</option>
              <option>فورد</option>
              <option>شيفروليه</option>
              <option>كيا</option>
              <option>هوندا</option>
              <option>مرسيدس</option>
              <option>بي إم دبليو</option>
              <option>جي إم سي</option>
              <option>{{ __('Other') }}</option>
            </select>
          </div>
          <div class="col-6 form-group">
            <label class="form-control-label text-sm" for="vehicleModel">{{ __('Model') }}</label>
            <input type="text" class="form-control form-control-sm" id="vehicleModel" name="custom[vehicle_model]" placeholder="كامري">
          </div>
        </div>

        <div class="row">
          <div class="col-6 form-group">
            <label class="form-control-label text-sm" for="vehicleColor">{{ __('Color') }} <span class="text-danger">*</span></label>
            <select class="form-control form-control-sm" id="vehicleColor" name="custom[vehicle_color]">
              <option value="">{{ __('Choose') }}</option>
              <option>أبيض</option>
              <option>أسود</option>
              <option>فضي</option>
              <option>رمادي</option>
              <option>أزرق</option>
              <option>أحمر</option>
              <option>بني</option>
              <option>ذهبي</option>
              <option>{{ __('Other') }}</option>
            </select>
          </div>
          <div class="col-6 form-group">
            <label class="form-control-label text-sm" for="vehiclePlate">{{ __('Plate') }} <small class="text-muted">{{ __('optional') }}</small></label>
            <input type="text" class="form-control form-control-sm" id="vehiclePlate" name="custom[vehicle_plate]" placeholder="ا ب ج ١٢٣٤">
          </div>
        </div>

        <div class="text-danger text-sm" id="vehicleError" style="display:none;">{{ __('Please select your vehicle color') }}</div>

        <div class="text-muted text-sm mt-2">
          <i class="fa fa-bell" aria-hidden="true"></i>
          {{ __('After confirming, an "I have arrived" button appears — tap it when you get there') }}
        </div>
      </div>

    </div>
    <br />
    <br />
  </div>
</div>
<br />

<script>
(function () {
  var KEY = 'imenu_vehicle';
  var optWindow = document.getElementById('pickupWindow');
  var optCar = document.getElementById('pickupCar');
  var form = document.getElementById('vehicleForm');
  var savedBox = document.getElementById('savedVehicleBox');
  var errorBox = document.getElementById('vehicleError');
  var fBrand = document.getElementById('vehicleBrand');
  var fModel = document.getElementById('vehicleModel');
  var fColor = document.getElementById('vehicleColor');
  var fPlate = document.getElementById('vehiclePlate');
  var fields = [fBrand, fModel, fColor, fPlate];
  var showSaved = false;

  function readSaved() {
    try {
      var raw = window.localStorage.getItem(KEY);
      return raw ? JSON.parse(raw) : null;
    } catch (e) { return null; }
  }

  function writeSaved() {
    try {
      window.localStorage.setItem(KEY, JSON.stringify({
        brand: fBrand.value, model: fModel.value,
        color: fColor.value, plate: fPlate.value
      }));
    } catch (e) {}
  }

  var saved = readSaved();
  if (saved && saved.color) {
    fBrand.value = saved.brand || '';
    fModel.value = saved.model || '';
    fColor.value = saved.color || '';
    fPlate.value = saved.plate || '';
    showSaved = true;
    document.getElementById('savedVehicleTitle').textContent =
      [saved.brand, saved.model].filter(Boolean).join(' ') + (saved.color ? ' · ' + saved.color : '');
    document.getElementById('savedVehiclePlate').textContent = saved.plate || '';
  }

  function render() {
    var isCar = optCar.checked;
    savedBox.style.display = (isCar && showSaved) ? 'block' : 'none';
    form.style.display = (isCar && !showSaved) ? 'block' : 'none';
    for (var i = 0; i < fields.length; i++) { fields[i].disabled = !isCar; }
    if (!isCar) { errorBox.style.display = 'none'; }
  }

  optWindow.addEventListener('change', render);
  optCar.addEventListener('change', render);

  document.getElementById('changeVehicleBtn').addEventListener('click', function () {
    showSaved = false;
    render();
  });

  fColor.addEventListener('change', function () {
    if (this.value) { errorBox.style.display = 'none'; }
  });

  var orderForm = document.getElementById('order-form');
  if (orderForm) {
    orderForm.addEventListener('submit', function (e) {
      if (!optCar.checked) { return; }
      if (!fColor.value) {
        e.preventDefault();
        e.stopPropagation();
        showSaved = false;
        render();
        errorBox.style.display = 'block';
        fColor.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
      }
      writeSaved();
    });
  }

  render();
})();
</script>
