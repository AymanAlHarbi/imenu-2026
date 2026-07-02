<!-- Product modal (Flowbite outer kept; Ember styled inner) -->
<div id="productModal" tabindex="-1" role="dialog" class="fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full items-center justify-center">
        <div class="em-modal">
        <button type="button" class="em-close" aria-label="{{ __('Close') }}" onclick="productModal.hide()">×</button>

        <div id="productImage" class="em-modal-img"></div>
        <div class="em-modal-body">
            <input id="modalID" type="hidden">
            <p id="modalTitle" class="em-modal-title notranslate"></p>
            <p id="modalPrice" class="em-modal-price"></p>
            <p id="productDescription" class="em-modal-desc"></p>

            <div id="variants-area">
                <label class="lbl">{{ __('Select your options') }}</label>
                <div id="variants-area-inside"></div>
            </div>

            <div id="exrtas-area">
                <label class="lbl">{{ __('Extras') }}</label>
                <div id="exrtas-area-inside"></div>
            </div>

            @if(!(isset($canDoOrdering)&&!$canDoOrdering))
                <div class="quantity-area">
                    <label class="lbl" for="quantity">{{ __('Quantity') }}</label>
                    
                    
                                        <div class="em-qtyrow">
                        <button type="button" onclick="emQty(1)" aria-label="+">+</button>
                        <input type="number" oninput="validateInput(this)" min="1" step="1"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                               name="quantity" id="quantity" value="1" placeholder="1" required>
                        <button type="button" onclick="emQty(-1)" aria-label="−">−</button>
                    </div>
                    <script>
                        function emQty(d){
                            var el = document.getElementById('quantity');
                            var v = (parseInt(el.value) || 1) + d;
                            if (v < 1) v = 1;
                            if (typeof currentItem !== 'undefined' && currentItem && currentItem.qty > 0 && v > currentItem.qty) v = currentItem.qty;
                            el.value = v;
                        }
                    </script>

                    
                    <div id="addToCart1">
                        <button class="em-addbtn" v-on:click='addToCartAct'>{{ __('Add To Cart') }}</button>
                    </div>
                    <script>
                        function validateInput(input){
                            if (input.value > currentItem.qty){
                                if(currentItem.qty==0){ alert('The item is out of stock'); }
                                else{ alert('The number must not be greater than '+currentItem.qty); }
                                input.value=currentItem.qty;
                            }
                        }
                    </script>
                </div>
            @endif

            @if (isset($openingTime)&&!empty($openingTime))
                <span class="em-closed">{{ __('Opens') }} {{ $openingTime }}</span>
                @if(!(isset($canDoOrdering)&&!$canDoOrdering))
                    <span class="em-closed">{{ __('Pre orders are possible') }}</span>
                @endif
            @endif
        </div>
    </div>
</div>
