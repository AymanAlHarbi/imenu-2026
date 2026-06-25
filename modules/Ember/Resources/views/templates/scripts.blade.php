
 <!-- Popper -->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" type="text/javascript"></script>
 
 <!-- jQuery -->
 <script src="{{ asset('argonfront') }}/js/core/jquery.min.js" type="text/javascript"></script>

  <!-- Bootstrap -->
 <script src="{{ asset('argonfront') }}/js/core/bootstrap.min.js" type="text/javascript"></script>

 <script>
    var CASHIER_CURRENCY = "<?php echo  config('settings.cashier_currency') ?>";
    var LOCALE="<?php echo  App::getLocale() ?>";
    var IS_POS=false;
</script>
<script src="{{ asset('custom') }}/js/cartFunctions.js"></script>

@if (isset($showGoogleTranslate)&&$showGoogleTranslate&&!$showLanguagesSelector)
    @include('googletranslate::scripts')
@endif



 
 


 <!-- scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.3/min/tiny-slider.js"></script>


 <!-- Custom js -->
 <script src="{{ asset('custom') }}/js/order.js"></script>

 <!-- Interface from PHP items to JS Array -->
 @include('restorants.phporderinterface') 

 <!-- All in one -->
 <script src="{{ asset('custom') }}/js/js.js?id={{ config('config.version')}}"></script>
 <script src="{{ asset('custom') }}/js/eleganttemplate.js"></script>
 <script>
     function openNav(){
      document.body.classList.toggle("mobile-menu-opened");
    }
 </script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>

    <script>

// set the modal menu element
const $targetEl = document.getElementById('productModal');

        // options with default values
        const options = {
            //placement: 'bottom-right',
            backdrop: 'dynamic',
            backdropClasses: 'bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-40',
            closable: true,
            onHide: () => {
                console.log('modal is hidden');
            },
            onShow: () => {
                console.log('modal is shown');
            },
            onToggle: () => {
                console.log('modal has been toggled');
            }
        };


        const productModal = new Modal($targetEl, options);

        function setCurrentItemInEmber(id){

            var item=items[id];
            console.log("---- ITEM ----");  
            console.log(item); 

            currentItem=item;
            previouslySelected=[];
            $('#modalTitle').text(item.name);
            $('#productDescription').text(item.description);
            $('#modalPrice').html(item.price);
            $('#modalID').text(item.id);
            $('#quantity').val(1);

            $("#productImage").css("background-image", "url(" + item.image + ")");

            if(item.has_variants){
                //Vith variants
                //Hide the counter, and extrasts
                $('.quantity-area').hide();

            //Now show the variants options
            $('#variants-area-inside').empty();
            $('#variants-area').show();
            setVariants();




            }else{
                //Normal
                currentItemSelectedPrice=item.priceNotFormated;
                $('#variants-area').hide();
                $('.quantity-area').show();
            }

            // toggle the modal
            productModal.toggle();


            extrasSelected=[];

            variantID=null;

            //Now set the extras
            if(item.extras.length==0||item.has_variants){
                
                $('#exrtas-area-inside').empty();
                $('#exrtas-area').hide();
            }else{
                
                $('#exrtas-area-inside').empty();
                item.extras.forEach(element => {
                    
                    $('#exrtas-area-inside').append('<div class="custom-control custom-checkbox mb-3"><input onclick="recalculatePrice('+id+');" class="custom-control-input" id="'+element.id+'" name="extra"  value="'+element.price+'" type="checkbox"><label class="custom-control-label" for="'+element.id+'">'+element.name+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+'+element.priceFormated+'</label></div>');
                });
                $('#exrtas-area').show();
            }

        }
    </script>