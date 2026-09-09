"use strict";
var items=[];
var currentItem=null;
var currentItemSelectedPrice=null;
var lastAdded=null;
var previouslySelected=[];
var extrasSelected=[];
var variantID=null;
var modifiersSelected={};
var debug=true;

function debugMe(title,message){
    if(debug){
        
        
        
    }
}

/*
* Price formater
* @param {Nummber} price
*/
function formatPrice(price){
    var locale=LOCALE;
    if(CASHIER_CURRENCY.toUpperCase()=="USD"){
        locale=locale+"-US";
    }

    var formatter = new Intl.NumberFormat(locale, {
        style: 'currency',
        currency:  CASHIER_CURRENCY,
    });

    var formated=formatter.format(price);

    return formated;
}

/**
 * Load extras for variant
 * @param {Number} variant_id the variant id
 * */
function loadExtras(variant_id){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'GET',
            url: '/items/variants/'+variant_id+'/extras',
            success:function(response){
                if(response.status){
                    response.data.forEach(element => {
                        $('#exrtas-area-inside').append('<div class="custom-control custom-checkbox mb-3"><input onclick="recalculatePrice('+element.item_id+');" class="custom-control-input" id="'+element.id+'" name="extra"  value="'+element.price+'" type="checkbox"><label class="custom-control-label" for="'+element.id+'">'+element.name+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+'+formatPrice(element.price)+'</label></div>');
                    });
                    $('#exrtas-area').show();
                    if(response.data.length>0){
                        $('#theExtrasLabel').show();
                    }else{
                        $('#theExtrasLabel').hide();
                    }

                }
            }, error: function (response) {
            }
        })
}




/**
 *
 * Set the selected variant, set price and shows qty area and calls load extras
 * */
function setSelectedVariant(element){

    $('#modalPrice').html(formatPrice(element.price));

    console.log("Set selected variant",element);

    //Set current item price
    currentItemSelectedPrice=element.price;

    //Set variantID
    variantID=element.id;

    //iMenu 2026 - البوابة تفحص المقاس والمجموعات الإلزامية معًا
    imUpdateGate();

    //Empty the extras, and call it
    $('#exrtas-area-inside').empty();
    loadExtras(variantID);

    if(element.enable_qty){
        currentItem.qty=element.qty;
    }else{
        currentItem.qty=100;
    }

   

}

function getTheDataForTheFoundVariable(){
    
    var comparableObject={};
    previouslySelected.forEach(element => {
        comparableObject[element.option_id]=element.name.trim().toLowerCase().replace(/\s/g , "-");
    });
    comparableObject=JSON.stringify(comparableObject)
    currentItem['variants'].forEach(element => {
        if(comparableObject==JSON.stringify(JSON.parse(element.options))){
            setSelectedVariant(element);
        }
    });

}


function checkIfVariableExists(forOption,optionValue){

    var newElement={"option_id":forOption,"name":optionValue};

    var possibleSelection=JSON.parse(JSON.stringify(previouslySelected));
    possibleSelection.push(newElement);

    var filteredObjects=[];
        currentItem.variants.forEach(theVariant => {
            var theOptions=JSON.parse(theVariant.optionsiconv?theVariant.optionsiconv:theVariant.options);
            var ok=true;
            Object.keys(theOptions).map((key)=>{
                possibleSelection.forEach(element => {
                    if(key==element.option_id){
                        if(theOptions[key]+""!=element.name.trim().toLowerCase().replace(/\s/g , "-")+""){
                            ok=false;
                        }
                    }
                });

            })

            if(ok){
                    filteredObjects.push(theVariant);
                }
            });



    return filteredObjects.length>0;

}

function appendOption(name,id){
    lastAdded=id;
    $('#variants-area-inside').append('<div id="variants-area-'+id+'"><br /><label class="form-control-label"><b>'+name+'<b></label><div><div id="variants-area-inside-'+id+'" class="flex-wrap btn-group btn-group-toggle" data-toggle="buttons"> </div></div>');
}

function optionChanged(option_id,name){

    var newElement={"option_id":option_id,"name":name};
    debugMe("selected option",JSON.stringify(newElement));

    
    //Append / insert the new selectioin
    var newSelectionState=[];
    var userClickedOnAlreadySelectedOption=false;
    previouslySelected.forEach(element => {

        if(userClickedOnAlreadySelectedOption){
            $( "#variants-area-"+element.option_id ).remove();
        }

        if(element.option_id!=newElement.option_id){
            //If we haven't yet found the item add this in the selection
            if(!userClickedOnAlreadySelectedOption){newSelectionState.push(element);}
        }else{
            userClickedOnAlreadySelectedOption=true;
        }

        
    });



    if(userClickedOnAlreadySelectedOption&&lastAdded!=newElement.option_id){
        //remove also last inserted, and readded it
        $( "#variants-area-"+lastAdded ).remove();
    }

    newSelectionState.push(newElement);
    previouslySelected=newSelectionState;
    debugMe("Selection",JSON.stringify(previouslySelected));
    setVariants();


}

function appendOptionValue(name,value,enabled,option_id){
    $('#variants-area-inside-'+option_id).append('<label style="opacity: '+(enabled?1:0.5)+'" class="btn btn-outline-primary"><input  onchange="optionChanged('+option_id+',\''+value+'\')"  '+ (enabled?"":"disabled") +' type="radio" name="option_'+option_id+'" value="option_'+option_id+"_"+name+'" autocomplete="off" />'+js.trans(name)+'</label>')
}

function setVariants(){
    //1. Determine previously selected variants

   //HIDE QTY
   $('.quantity-area').hide();
   $('#exrtas-area-inside').empty();
   $('#exrtas-area').hide();

    //2. Get the new option to show
    var newOptionToShow=null;
    debugMe("previouslySelected length",previouslySelected.length);
    newOptionToShow=currentItem.options[previouslySelected.length];
    debugMe("newOptionToShow",JSON.stringify(newOptionToShow));

    if(newOptionToShow!=undefined){
        //2.1 Add the options in the table
        appendOption(newOptionToShow.name,newOptionToShow.id);


        var values=(newOptionToShow.optionsiconv?newOptionToShow.optionsiconv:newOptionToShow.options).split(",");
        var titles=(newOptionToShow.options).split(",");

        for (let index = 0; index < values.length; index++) {
            const theValue = values[index];
            const theTitle = titles[index];

            if(checkIfVariableExists(newOptionToShow.id,theValue)){
                //Next variable exists
                appendOptionValue(theTitle,theValue,true,newOptionToShow.id);
            }else{
                //Varaiable with the next option value doens't exists
                appendOptionValue(theTitle,theValue,false,newOptionToShow.id);
            }

        }

    }else{
        
        getTheDataForTheFoundVariable();
    }




    //3. Add the new option options
    //3.1 If new option is null, show the variant price
}


function setCurrentItem(id){


    var item=items[id];
    console.log("---- ITEM ----");  
    console.log(item); 
    
    currentItem=item;
    previouslySelected=[];
    $('#modalTitle').text(item.name);
    $('#modalName').text(item.name);
    $('#modalPrice').html(item.price);
    $('#modalID').text(item.id);
    $('#quantity').val(1);

    if(item.image != "/default/restaurant_large.jpg"){
        $("#modalImg").attr("src",item.image);
        $("#modalDialogItem").addClass("modal-lg");
        $("#modalImgPart").show();

        $("#modalItemDetailsPart").removeClass("col-sm-6 col-md-6 col-lg-6 offset-3");
        $("#modalItemDetailsPart").addClass("col-sm col-md col-lg");
    }else{
        $("#modalImgPart").hide();
        $("#modalItemDetailsPart").removeClass("col-sm col-md col-lg");
        $("#modalItemDetailsPart").addClass("col-sm-6 col-md-6 col-lg-6 offset-3");

        $("#modalDialogItem").removeClass("modal-lg");
        $("#modalDialogItem").addClass("col-sm-6 col-md-6 col-lg-6 offset-3");
    }

    $('#modalDescription').html(item.description);


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


    $('#productModal').modal('show');

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

    //iMenu 2026 - مجموعات الخيارات: تُعرض بعد المقاس وقبل الإضافات
    imRenderModifiers(item);
}

function recalculatePrice(id,value){
    //iMenu 2026 - الأساس + فروق المجموعات + الإضافات
    var mainPrice=parseFloat(currentItemSelectedPrice)+imModifiersDelta();
    extrasSelected=[];

    //Get the selected check boxes
    $.each($("input[name='extra']:checked"), function(){
        mainPrice+=parseFloat(($(this).val()+""));
        extrasSelected.push($(this).attr('id'));
    });
    $('#modalPrice').html(formatPrice(mainPrice));

}

/* ===== iMenu 2026 — مجموعات الخيارات بفروق أسعار =====
   الحاوية تُنشأ بالـJS مرة واحدة قبل منطقة الإضافات، فتعمل في كل
   القوالب (Elegant · Ember · Glow · الافتراضي) بلا تعديل أربعة ملفات. */

function imModifiersDelta(){
    var total=0;
    $(".im-mg-input:checked").each(function(){ total+=parseFloat($(this).attr("data-delta")||0); });
    return total;
}

function imCollectModifiers(){
    modifiersSelected={};
    var groups=(currentItem&&currentItem.modifiers)?currentItem.modifiers:[];
    groups.forEach(function(g,gi){ modifiersSelected[gi]=[]; });
    $(".im-mg-input:checked").each(function(){
        var gi=$(this).attr("data-gi"), oi=parseInt($(this).attr("data-oi"),10);
        if(!modifiersSelected[gi]){ modifiersSelected[gi]=[]; }
        modifiersSelected[gi].push(oi);
    });
}

function imModifiersSatisfied(){
    var groups=(currentItem&&currentItem.modifiers)?currentItem.modifiers:[];
    for(var gi=0; gi<groups.length; gi++){
        var g=groups[gi];
        var n=(modifiersSelected[gi]||[]).length;
        var min=g.required?Math.max(1,(g.min||1)):(g.min||0);
        if(n<min){ return false; }
        if(g.multiple&&g.max>0&&n>g.max){ return false; }
    }
    return true;
}

/* بوابة واحدة للشراء: المقاس مختار (إن وُجد) والمجموعات الإلزامية مُجابة.
   .quantity-area موجودة في بعض القوالب فقط (وفي Ember لا تُطبع إلا إذا كان
   الطلب مفعّلًا)، فنُعطّل زر الإضافة نفسه أيضًا — وهو يعمل في كل قالب. */
function imUpdateGate(){
    var variantOk=!(currentItem&&currentItem.has_variants)||variantID!==null;
    var ok=variantOk&&imModifiersSatisfied();

    if(ok){ $(".quantity-area").show(); } else { $(".quantity-area").hide(); }

    $("#addToCart1 button, .em-addbtn, #addToCart1 .btn").each(function(){
        this.disabled=!ok;
        this.style.opacity=ok?"":"0.45";
        this.style.pointerEvents=ok?"":"none";
    });
}

function imModifierChanged(gi){
    imCollectModifiers();

    /* الحد الأقصى يُطبَّق بتعطيل ما لم يُختر، لا برسالة خطأ بعد الضغط */
    var groups=(currentItem&&currentItem.modifiers)?currentItem.modifiers:[];
    groups.forEach(function(g,index){
        if(g.multiple&&g.max>0){
            var reached=(modifiersSelected[index]||[]).length>=g.max;
            $(".im-mg[data-gi='"+index+"'] .im-mg-input").each(function(){
                if(!this.checked){ this.disabled=reached; }
            });
        }
    });

    recalculatePrice(currentItem?currentItem.id:null);
    imUpdateGate();
}

function imRenderModifiers(item){
    if($("#modifiers-area").length===0){
        if($("#exrtas-area").length){ $("#exrtas-area").before('<div id="modifiers-area" style="display:none"></div>'); }
        else{ $(".quantity-area").first().before('<div id="modifiers-area" style="display:none"></div>'); }
    }
    var host=$("#modifiers-area");
    host.empty();
    modifiersSelected={};

    var groups=(item&&item.modifiers)?item.modifiers:[];
    if(groups.length===0){ host.hide(); return; }

    groups.forEach(function(g,gi){
        modifiersSelected[gi]=[];
        var type=g.multiple?"checkbox":"radio";
        var star=g.required?' <span style="color:#C0392B">*</span>':'';
        var html='<div class="im-mg mb-3" data-gi="'+gi+'"><label class="form-control-label"><b>'+g.name+'</b>'+star+'</label>';
        g.options.forEach(function(o,oi){
            var id="im_mg_"+gi+"_"+oi;
            var delta=parseFloat(o.delta||0)>0?"&nbsp; + "+formatPrice(o.delta):"";
            html+='<div class="custom-control custom-'+type+' mb-2">'+
                  '<input class="custom-control-input im-mg-input" type="'+type+'" id="'+id+'" name="im_mg_'+gi+'" '+
                  'data-gi="'+gi+'" data-oi="'+oi+'" data-delta="'+(o.delta||0)+'" onchange="imModifierChanged('+gi+')">'+
                  '<label class="custom-control-label" for="'+id+'">'+o.name+delta+'</label></div>';
        });
        html+='</div>';
        host.append(html);
    });
    host.show();
    imUpdateGate();
}

function getLocation(callback){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type:'GET',
        url: '/get/rlocation/'+$('#rid').val(),
        success:function(response){
            if(response.status){
                return callback(true, response.data)
            }
        }, error: function (response) {
        return callback(false, response.responseJSON.errMsg);
        }
    })
}

function initializeMap(lat, lng){
    var map_options = {
        zoom: 13,
        center: new google.maps.LatLng(lat, lng),
        mapTypeId: "terrain",
        scaleControl: true
    }

    map_location = new google.maps.Map( document.getElementById("map3"), map_options );
}

function initializeMarker(lat, lng){
    var markerData = new google.maps.LatLng(lat, lng);
    marker = new google.maps.Marker({
        position: markerData,
        map: map_location,
        icon: start
    });
}

function showInitProduct(){
    if(PID!="" && PID!=null && PID!=undefined){
        setCurrentItem(PID);
    }
}


var start = "/images/pin.png"
var area = "/images/green_pin.png"
var map_location = null;
var map_area = null;
var marker = null;
var infoWindow = null;
var lat = null;
var lng = null;
var circle = null;
var isClosed = false;
var poly = null;
var markers = [];
var markerArea = null;
var markerIndex = null;
var path = null;

window.onload?window.onload():null;

window.onload = function () {

    getLocation(function(isFetched, currPost){
        if(isFetched){


            if(currPost.lat != 0 && currPost.lng != 0){
                //initialize map
                initializeMap(currPost.lat, currPost.lng)

                //initialize marker
                initializeMarker(currPost.lat, currPost.lng)
            }
        }
    });

    showInitProduct();
}







$(".nav-item-category").on('click', function() {
    $.each(categories, function( index, value ) {
        $("."+value).show();
    });

    var id = $(this).attr("id");
    var category_id = id.substr(id.indexOf("_")+1, id.length);

    $.each(categories, function( index, value ) {
        if(value != category_id){
            $("."+value).hide();
        }
    });
});