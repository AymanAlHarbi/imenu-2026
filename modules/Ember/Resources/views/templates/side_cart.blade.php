<div class="em-cart">
    <p class="em-cart-h"><i class="las la-shopping-bag"></i>{{ __('Shopping Cart') }}</p>

    <div id="{{ $id }}">
        <div v-for="item in items" class="em-cart-item" v-cloak>
            <p class="nm">@{{ item.name }}</p>
            <p class="qt">@{{ item.quantity }} x @{{ item.attributes.friendly_price }}</p>
            <div class="em-cart-btns">
                <button type="button" v-on:click="decQuantity(item.id)" :value="item.id" aria-label="-">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </button>
                <button type="button" v-on:click="incQuantity(item.id)" :value="item.id" aria-label="+">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </button>
                <button type="button" v-on:click="remove(item.id)" :value="item.id" aria-label="x">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </button>
            </div>
        </div>
    </div>

    <div id="{{ $idtotal }}">
        <div v-if="totalPrice==0" class="em-cart-empty">{{ __('Cart is empty') }}!</div>
        <div v-if="totalPrice" v-cloak>
            <div class="em-subtotal"><span>{{ __('Subtotal') }}</span><span class="v">@{{ totalPriceFormat }}</span></div>
            <a href="/cart-checkout" class="em-checkout">{{ __('Checkout') }}</a>
        </div>
    </div>
</div>
