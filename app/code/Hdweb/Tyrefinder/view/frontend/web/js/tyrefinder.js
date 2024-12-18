require(
[
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mgs/owlcarousel'
],
function(
    $,
    modal
 ) {
    
   /* header.phtml */
    var WindowsSize=function(){
        var h=$(window).height()
    };

   $(document).ready(WindowsSize); 
   $(window).resize(WindowsSize);   

   jQuery(window).scroll(function(){
      var sticky = jQuery('.searchSec'),
          scroll = jQuery(window).scrollTop();
      if (scroll >= 300) sticky.addClass('sticky');
      else sticky.removeClass('sticky');
    });

   jQuery(document).on('change', '.regular-product-list .select_qty', function() {        
          var selectedQty=jQuery(this).val();
          var selectedPrice=jQuery(this).parent().parent().find('.single-tyre-price').text().replace(/,/g, '');;
          var selectedPrice=selectedPrice.replace("AED", "");
          selectedPrice=parseFloat(selectedPrice);
          var simpleTotal=parseInt(selectedQty) * selectedPrice;

           var discountqty=jQuery(this).data('discountqty');  
           var totalqty=jQuery(this).data('totalqty'); 
           var selected_qty=jQuery(this).val();
           
           if(discountqty != undefined && discountqty != undefined) { 

                   var unitprice=jQuery(this).data('unitprice');                           
                   var module_qty=selected_qty % totalqty ;
                   var discounted_qty=selected_qty-module_qty;
                   var pricefor_discount_item=( discounted_qty * unitprice  * discountqty ) / 100; //get percenatge
                   var offer_price_with_deducted_ammount=( discounted_qty * unitprice) - pricefor_discount_item;
                   var pricefor_without_discount_item=unitprice * module_qty ;
                   var front_chargebl_price=offer_price_with_deducted_ammount + pricefor_without_discount_item;
                   simpleTotal=front_chargebl_price;
                  // frontTotal=parseFloat(frontTotal).toFixed(2);  
              }
        
          simpleTotal=parseFloat(simpleTotal).toFixed(2);
          jQuery(this).parent().parent().find('.setfourprice').html('<span>Set of '+selectedQty+': AED'+simpleTotal+"</span>");
   });

   jQuery(document).on('change', '.bundle-product-list .select_qty', function() {

            var  front_qty=jQuery(this).parent().parent().find('.front-qty').val();
           var  front_price=jQuery(this).parent().parent().parent().find('.leftbundle').find('.single-tyre-price').text().replace(/,/g, '');;
 
           front_price=front_price.replace("AED", "");
           front_price=parseFloat(front_price);  
           var frontTotal=front_qty * front_price;
           frontTotal=parseFloat(frontTotal).toFixed(2);  

           var discountqty=jQuery(this).parent().parent().find('.front-qty').data('discountqty');  //  Discount Amount percen
           var totalqty=jQuery(this).parent().parent().find('.front-qty').data('totalqty');  // means  Discount Qty Step (Buy X)
           var selected_qty=jQuery(this).parent().parent().find('.front-qty').val();
          
          //if(jQuery(this).hasClass('front-qty')){
           
           if(discountqty != undefined && discountqty != undefined) { 

                   var unitprice=jQuery(this).parent().parent().find('.front-qty').data('unitprice');                           
                   var module_qty=selected_qty % totalqty ;
                   var discounted_qty=selected_qty-module_qty;
                   var pricefor_discount_item=( discounted_qty * unitprice  * discountqty ) / 100; //get percenatge
                   var offer_price_with_deducted_ammount=( discounted_qty * unitprice) - pricefor_discount_item;
                   var pricefor_without_discount_item=unitprice * module_qty ;
                   var front_chargebl_price=offer_price_with_deducted_ammount + pricefor_without_discount_item;
                   frontTotal=front_chargebl_price;
                   frontTotal=parseFloat(frontTotal).toFixed(2);  
              }
          //}  

          jQuery(this).parents('.bundleBox').find('.leftbundle').find('.setfourprice').html('Set of '+front_qty+': AED'+frontTotal);

           var  rear_qty=jQuery(this).parent().parent().find('.rear-qty').val();
           var  rear_price=jQuery(this).parent().parent().parent().find('.rightbundle').find('.single-tyre-price').text().replace(/,/g, '');;
           rear_price=rear_price.replace("AED", "");
           rear_price=parseFloat(rear_price);
           var rearTotal=rear_qty * rear_price;
           rearTotal=parseFloat(rearTotal).toFixed(2); 

           var discountqty=jQuery(this).parent().parent().find('.rear-qty').data('discountqty');  //  Discount Amount percen
           var totalqty=jQuery(this).parent().parent().find('.rear-qty').data('totalqty');  // means  Discount Qty Step (Buy X)
           var selected_qty=jQuery(this).parent().parent().find('.rear-qty').val();

            // if(jQuery(this).hasClass('rear-qty')){
               if(discountqty != undefined && discountqty != undefined) { 
                       var unitprice=jQuery(this).parent().parent().find('.rear-qty').data('unitprice');                           
                       var module_qty=selected_qty % totalqty ;
                       var discounted_qty=selected_qty-module_qty;
                       var pricefor_discount_item=( discounted_qty * unitprice  * discountqty ) / 100; //get percenatge
                       var offer_price_with_deducted_ammount=( discounted_qty * unitprice) - pricefor_discount_item;
                       var pricefor_without_discount_item=unitprice * module_qty ;
                       var rear_chargebl_price=offer_price_with_deducted_ammount + pricefor_without_discount_item;
                       rearTotal=rear_chargebl_price;
                       rearTotal=parseFloat(rearTotal).toFixed(2); 
                }
            //}  

            jQuery(this).parents('.bundleBox').find('.rightbundle').find('.setfourprice').html('Set of '+rear_qty+': AED'+rearTotal);

            frontTotal=parseFloat(frontTotal); 
            rearTotal=parseFloat(rearTotal); 
            var finalBundleTotal =(frontTotal) + (rearTotal);
            finalBundleTotal=parseFloat(finalBundleTotal).toFixed(2);
            var total_qty=parseInt(front_qty) + parseInt(rear_qty);

            jQuery(this).parent().parent().find('.bundle-price-fr-rr').html('Set of '+total_qty+' Tyres AED'+finalBundleTotal);
   }); 


   /* tyre finder block*/
    var tyre_finder_width_modal_options = {
        type: 'popup',
        responsive: true,   
        innerScroll: true,
        buttons: [{
            text: $.mage.__('Close'),
            class: 'tyreSearchwrapper',
            click: function () {
                this.closeModal();
            }
        }],
        opened: function($Event) {
            $(".modal-footer").hide();
        }
    };

    var tyre_finder_modal = modal(tyre_finder_width_modal_options, $('#tyre_finder_modal'));
    $("#frontWidthLabel").on('click',function(){
       
        $("#tyre_finder_modal").modal("openModal");
        jQuery('#tyre_finder_modal_wrapper .tyre_finder_allcontent').remove();
        jQuery('#tyre_finder_modal_wrapper').append('<ul class="tyre_finder_allcontent  tyre_finder_modal_front_width_content"></ul>');
        jQuery('#tyre_finder_modal_wrapper .tyre_finder_modal_front_width_content').html(jQuery('.tyre_finder_modal_front_width_wrapper').html());

        jQuery('#tyre_finder_modal .selectiondata').text('');
        jQuery('#tyre_finder_modal  .selectiontitle').text('Current Selection');
        jQuery('.allback').hide();

        jQuery('.stepper li').removeClass('active');
        jQuery('.stepper li').eq(0).addClass('active');

        jQuery('.tyreValue .valuetxt').removeClass('active');
        jQuery('.tyreValue .tvalue_1').addClass('active');

        jQuery('.tyretext .txtcolor').removeClass('active');
        jQuery('.tyretext .txtcolor').eq(0).addClass('active');
        
    });

    $("#frontHeightLabel").on('click',function(){
        $("#tyre_finder_modal").modal("openModal");
        jQuery('#tyre_finder_modal_wrapper .tyre_finder_allcontent').hide();
        jQuery('#tyre_finder_modal_wrapper .tyre_finder_modal_front_height_content').show();

        jQuery('.allback').hide();
        jQuery('.backwidth').show();

        jQuery('.stepper li').removeClass('active');
        jQuery('.stepper li').eq(1).addClass('active');

        jQuery('.tyreValue .valuetxt').removeClass('active');
        jQuery('.tyreValue .tvalue_3').addClass('active');

        jQuery('.tyretext .txtcolor').removeClass('active');
        jQuery('.tyretext .txtcolor').eq(1).addClass('active');
       
        jQuery('.tyreselection .selectiondata').text(jQuery('#frontWidthLabel').text());
    });

    $("#frontRimLabel").on('click',function(){
        $("#tyre_finder_modal").modal("openModal");
        jQuery('#tyre_finder_modal_wrapper .tyre_finder_allcontent').hide();
        jQuery('#tyre_finder_modal_wrapper .tyre_finder_modal_front_rim_content').show();

        jQuery('.allback').hide();
        jQuery('.backprofile').show();

        jQuery('.stepper li').removeClass('active');
        jQuery('.stepper li').eq(2).addClass('active');

        jQuery('.tyreValue .valuetxt').removeClass('active');
        jQuery('.tyreValue .tvalue_4').addClass('active');

        jQuery('.tyretext .txtcolor').removeClass('active');
        jQuery('.tyretext .txtcolor').eq(2).addClass('active');
       
        jQuery('.tyreselection .selectiondata').text(jQuery('#frontWidthLabel').text()+' / '+jQuery('#frontHeightLabel').text());

    });

      $('#categoryid').change(function(){
       var categoryid =$(this).val();
        jQuery.ajax({
            url: getCategoryWidthUrl,
            type: 'POST',
            data: {
                format: 'json',
                categoryid: categoryid,
            },
            error: function () {
                alert("Error");
            },
            success: function (data) {
                if (data.status == 'SUCCESS') {
                   jQuery('#tyre_finder_modal_wrapper .tyre_finder_allcontent').remove();
                   jQuery('#tyre_finder_modal_wrapper').append('<ul class="tyre_finder_allcontent  tyre_finder_modal_front_width_content"></ul>');
                   jQuery('#tyre_finder_modal_wrapper .tyre_finder_modal_front_width_content').html(data.response);
                   jQuery('.tyre_finder_modal_rear_width_wrapper').html(data.rearresponse);
                }
            }
        });

        resetFinderSearchForm(); // Reset search form

     });

    $(document).on("click",".reartyrelink",function() {
        jQuery('.searchloader').show();
        jQuery('.tyre_finder_allcontent').hide();
        jQuery('.tyre_finder_modal_rear_width_content,.tyre_finder_modal_rear_height_content,.tyre_finder_modal_rear_rim_content').remove();
        jQuery('#tyre_finder_modal_wrapper').append('<ul class="tyre_finder_allcontent  tyre_finder_modal_rear_width_content"></ul>');
        jQuery('#tyre_finder_modal_wrapper .tyre_finder_modal_rear_width_content').append(jQuery('.tyre_finder_modal_rear_width_wrapper').html());
        jQuery('.tyre_finder_modal_rear_width_content').show();

        jQuery('.allback').hide();
        jQuery('.backfromrim').show();

        jQuery('.stepper li').removeClass('active');
        jQuery('.stepper li').eq(0).addClass('active');

        jQuery('.tyreValue .valuetxt').removeClass('active');
        jQuery('.tyreValue .tvalue_1').addClass('active');

        jQuery('.tyretext .txtcolor').removeClass('active');
        jQuery('.tyretext .txtcolor').eq(0).addClass('active');
        jQuery('.selectiontitle').text('Front Tyre Selection: ');
        jQuery('.rear-tyre-selection').show();
        jQuery('.searchloader').hide();
    });

    // Search by vehicle start
      var vehicle_finder_modal_options = {
        type: 'popup',
        responsive: true,
        innerScroll: true,
        buttons: [{
            text: $.mage.__('Close'),
            class: 'tyreSearchwrapper',
            click: function () {
                this.closeModal();
            }
        }],
        opened: function($Event) {
            $(".modal-footer").hide();
        }
    };

    var vehicle_finder_modal = modal(vehicle_finder_modal_options, $('#vehicle_finder_modal'));
    $("#vehicle_make").on('click',function(){
        $("#vehicle_finder_modal").modal("openModal");
    });
    //  Search by vehicle end


    $(".nav-item").on('click',function(){
        $('.nav-item').removeClass('size');
        $('.nav-item').removeClass('vehicle');
        $('.nav-link').removeClass('active');

        $(this).find('.nav-link').addClass('active');

        if($(this).hasClass('sizetab')){
            $('.nav-item').addClass('size');
        }else{
            $('.nav-item').addClass('vehicle');
        }
    });

     /* footer start */
      $('.home_slider').owlCarousel({
                        center: true,
                        margin: 0,
                        nav:true,
                        navText:["<div class='nav-btn prev-slide'></div>","<div class='nav-btn next-slide'></div>"],   
                        dots:false,
                        autoplay:true,
                        loop: true,
                        autoplayTimeout:5000,
                        autoplayHoverPause:true,                        
                        items: 1            
        });

        $('.service_slider').owlCarousel({
            margin: 0,
            autoplay:true,
            loop: true,
            items: 1,
            responsive:{
                0:{ items: 1, margin: 15 },
                767:{ margin: 10 }
            }
        });
        $('.car_service_slider').owlCarousel({
            margin: 15,
            //autoplay:true,
            loop: true,
            items: 4,
            responsive:{
                0:{ items:1,margin: 10 },
                767:{ items:2},
                991:{ items:3},
                1000:{ items:4}
            }
        });
        
        $('.promotion_slider').owlCarousel({
            margin: 0,
            autoplay:true,
            loop: true,
            items: 1
        });
        $('.sponser_slider').owlCarousel({
            margin: 0,
            autoplay:true,
            loop: true,
            items: 5,
            nav:true,
            navText:["<div class='nav-btn prev-slide'></div>","<div class='nav-btn next-slide'></div>"],            
            dots:false,
            responsive:{
                0:{ margin: 10,items:2 },
                600:{ items:3},
                1000:{ items:5}
            }
        });
        $('.testimonial_slider').owlCarousel({
            margin: 0,
            autoplay:true,
            loop: true,
            nav:false,
            dots:false,
            items: 1,
            thumbs: true,
            thumbImage: true
        });
        jQuery('body').on("click", function () {
              jQuery('header nav').removeClass('open_menu');
              jQuery('.mobileMenu .la').removeClass('la-close');
        });
        jQuery('.mobileMenu').click(function() {
            event.stopPropagation();
            jQuery('header nav').addClass('open_menu');
            jQuery('.mobileMenu .la').addClass('la-close');
        });
        $('footer h6 i').click(function(){
          $(this).next(".footer_box").slideToggle();
          $(this).closest(".col-md-3").siblings().find('.footer_box').slideUp();
        });
        
     /* footer end*/

  
});   

//  Search by Size Start
function getheight(widthValue,label,type)
{
    jQuery("#frontWidthLabel-hidden").val(widthValue);
    jQuery("#frontWidthLabel").text(label);
    jQuery('.searchloader').show();
    jQuery.ajax({
        url: getHeightUrl,
        type: 'POST',
        data: {
            format: 'json',
            type: type,
            width: widthValue,
        },
        error: function () {
            alert("Error");
        },
        success: function (data) {
            if (data.status == 'SUCCESS') {
                        jQuery('.tyre_finder_allcontent').hide();
                        jQuery('#tyre_finder_modal_wrapper ul.tyre_finder_modal_front_height_content').remove();
                        jQuery('#tyre_finder_modal_wrapper').append('<ul class="tyre_finder_allcontent  tyre_finder_modal_front_height_content"></ul>');
                        jQuery('#tyre_finder_modal_wrapper .tyre_finder_modal_front_height_content').html(data.response);
                        jQuery('#frontHeightLabel').removeClass('disablesearchoption');

                        jQuery('.allback').hide();
                        jQuery('.backwidth').show();

                        jQuery('.reset-size-selection').show();

                        jQuery('.stepper li').removeClass('active');
                        jQuery('.stepper li').eq(1).addClass('active');

                        jQuery('.tyreValue .tvalue_1').text(label);
                        jQuery('.tyreValue .valuetxt').removeClass('active');
                        jQuery('.tyreValue .tvalue_2').addClass('active');
                        jQuery('.tyreValue .tvalue_3').addClass('active');

                        jQuery('.tyretext .txtcolor').removeClass('active');
                        jQuery('.tyretext .txtcolor').eq(1).addClass('active');
                        
                        var selectiondata=jQuery('#tyre_finder_modal .selectiondata').text();
                        if(selectiondata != '') {
                            jQuery('#tyre_finder_modal .selectiondata').text(jQuery('#tyre_finder_modal .selectiondata').text()+' / '+label);    
                        }else{
                            jQuery('#tyre_finder_modal .selectiondata').text(label);    
                        }

                        jQuery('.searchloader').hide();
                        
            }
        },
    });
}

function getRearheight(rearwidthValue,label,type)
{

    jQuery("#rearWidthLabel-hidden").val(rearwidthValue);
    jQuery("#rearWidthLabel-text").val(label);
    jQuery('.searchloader').show();
    jQuery.ajax({
        url: getHeightUrl,
        type: 'POST',
        data: {
            format: 'json',
            type: type,
            width: rearwidthValue,
        },
        error: function () {
            alert("Error");
        },
        success: function (data) {
            if (data.status == 'SUCCESS') {
                        jQuery('.tyre_finder_allcontent').hide();
                        jQuery('#tyre_finder_modal_wrapper ul.tyre_finder_modal_rear_height_content').remove();
                        jQuery('#tyre_finder_modal_wrapper').append('<ul class="tyre_finder_allcontent  tyre_finder_modal_rear_height_content"></ul>');
                        jQuery('#tyre_finder_modal_wrapper .tyre_finder_modal_rear_height_content').html(data.response);
                        jQuery('.allback').hide();
                        jQuery('.rearbackwidth').show();

                        jQuery('.stepper li').removeClass('active');
                        jQuery('.stepper li').eq(1).addClass('active');

                        jQuery('.tyreValue .tvalue_1').text(label);
                        jQuery('.tyreValue .valuetxt').removeClass('active');
                        jQuery('.tyreValue .tvalue_2').addClass('active');
                        jQuery('.tyreValue .tvalue_3').addClass('active');

                        jQuery('.tyretext .txtcolor').removeClass('active');
                        jQuery('.tyretext .txtcolor').eq(1).addClass('active');
                        var rearselectiondata=jQuery('.rearselectiondata').text();
                        if(rearselectiondata != '') {
                            jQuery('.rearselectiondata').text(jQuery('.rearselectiondata').text()+' / '+label);    
                        }else{
                            jQuery('.rearselectiondata').text(label);    
                        }

                        jQuery('.searchloader').hide();
            }
        },
    });
}

function getrim(heightValue,label,type)
{
        jQuery("#frontHeightLabel-hidden").val(heightValue);
        jQuery("#frontHeightLabel").text(label);
        jQuery('.searchloader').show();
        var widthValue = jQuery("#frontWidthLabel-hidden").val();

         jQuery.ajax({
            url: getRimUrl,
            type: 'POST',
            data: {
                format: 'json',
                type: type,
                width: widthValue,
                height: heightValue,
            },
            error: function () {
                alert("Error");
            },
            success: function (data) {
                if (data.status == 'SUCCESS') {
                           jQuery('.tyre_finder_allcontent').hide();
                           jQuery('#tyre_finder_modal_wrapper ul.tyre_finder_modal_front_rim_content').remove();
                           jQuery('#tyre_finder_modal_wrapper').append('<ul class="tyre_finder_allcontent  tyre_finder_modal_front_rim_content"></ul>');
                           jQuery('#tyre_finder_modal_wrapper .tyre_finder_modal_front_rim_content').html(data.response);

                           jQuery('#frontRimLabel').removeClass('disablesearchoption');

                           jQuery('.allback').hide();
                           jQuery('.backprofile').show();

                           jQuery('.stepper li').removeClass('active');
                           jQuery('.stepper li').eq(2).addClass('active');

                           jQuery('.tyreValue .tvalue_3').text(label);
                           jQuery('.tyreValue .valuetxt').removeClass('active');
                           jQuery('.tyreValue .tvalue_4').addClass('active');

                           jQuery('.tyretext .txtcolor').removeClass('active');
                           jQuery('.tyretext .txtcolor').eq(2).addClass('active');
                           var selectiondata=jQuery('.selectiondata').text();
                            if(selectiondata != '') {
                                jQuery('#tyre_finder_modal .selectiondata').text(jQuery('#tyre_finder_modal .selectiondata').text()+' / '+label);    
                            }else{
                                jQuery('#tyre_finder_modal .selectiondata').text(label);    
                            }

                            jQuery('.searchloader').hide();
                           
                }
            },
        });
}

function getrimforoffset(widthValue,label,type)
{
        jQuery("#frontWidthLabel-hidden").val(widthValue);
        jQuery("#frontWidthLabel").text(label);
        jQuery('.searchloader').show();

         jQuery.ajax({
            url: getRimUrlForOffset,
            type: 'POST',
            data: {
                format: 'json',
                type: type,
                width: widthValue,
            },
            error: function () {
                alert("Error");
            },
            success: function (data) {
                if (data.status == 'SUCCESS') {
                        jQuery('.tyre_finder_allcontent').hide();
                        jQuery('#tyre_finder_modal_wrapper ul.tyre_finder_modal_front_height_content').remove();
                        jQuery('#tyre_finder_modal_wrapper').append('<ul class="tyre_finder_allcontent  tyre_finder_modal_front_height_content"></ul>');
                        jQuery('#tyre_finder_modal_wrapper .tyre_finder_modal_front_height_content').html(data.response);
                        jQuery('#frontHeightLabel').removeClass('disablesearchoption');

                        jQuery('.allback').hide();
                        jQuery('.backwidth').show();

                        jQuery('.reset-size-selection').show();

                        jQuery('.stepper li').removeClass('active');
                        jQuery('.stepper li').eq(1).addClass('active');

                        jQuery('.tyreValue .tvalue_1').text(label);
                        jQuery('.tyreValue .valuetxt').removeClass('active');
                        jQuery('.tyreValue .tvalue_2').addClass('active');
                        jQuery('.tyreValue .tvalue_3').addClass('active');

                        jQuery('.tyretext .txtcolor').removeClass('active');
                        jQuery('.tyretext .txtcolor').eq(1).addClass('active');
                        
                        var selectiondata=jQuery('#tyre_finder_modal .selectiondata').text();
                        if(selectiondata != '') {
                            jQuery('#tyre_finder_modal .selectiondata').text(jQuery('#tyre_finder_modal .selectiondata').text()+' / '+label);    
                        }else{
                            jQuery('#tyre_finder_modal .selectiondata').text(label);    
                        }

                        jQuery('.searchloader').hide();
                        
            }
            },
        });
}

function getoffset(rimValue,label)
{
        jQuery("#frontHeightLabel-hidden").val(rimValue);
        jQuery("#frontHeightLabel").text(label);
        jQuery('.searchloader').show();
        var widthValue = jQuery("#frontWidthLabel-hidden").val();

         jQuery.ajax({
            url: getOffsetUrl,
            type: 'POST',
            data: {
                format: 'json',
                width: widthValue,
                rim: rimValue,
            },
            error: function () {
                alert("Error");
            },
            success: function (data) {
                if (data.status == 'SUCCESS') {
                           jQuery('.tyre_finder_allcontent').hide();
                           jQuery('#tyre_finder_modal_wrapper ul.tyre_finder_modal_front_rim_content').remove();
                           jQuery('#tyre_finder_modal_wrapper').append('<ul class="tyre_finder_allcontent  tyre_finder_modal_front_rim_content"></ul>');
                           jQuery('#tyre_finder_modal_wrapper .tyre_finder_modal_front_rim_content').html(data.response);

                           jQuery('#frontRimLabel').removeClass('disablesearchoption');

                           jQuery('.allback').hide();
                           jQuery('.backprofile').show();

                           jQuery('.stepper li').removeClass('active');
                           jQuery('.stepper li').eq(2).addClass('active');

                           jQuery('.tyreValue .tvalue_3').text(label);
                           jQuery('.tyreValue .valuetxt').removeClass('active');
                           jQuery('.tyreValue .tvalue_4').addClass('active');

                           jQuery('.tyretext .txtcolor').removeClass('active');
                           jQuery('.tyretext .txtcolor').eq(2).addClass('active');
                           var selectiondata=jQuery('.selectiondata').text();
                            if(selectiondata != '') {
                                jQuery('#tyre_finder_modal .selectiondata').text(jQuery('#tyre_finder_modal .selectiondata').text()+' / '+label);    
                            }else{
                                jQuery('#tyre_finder_modal .selectiondata').text(label);    
                            }

                            jQuery('.searchloader').hide();
                           
                }
            },
        });
}

function selectOffset(offsetValue,label)
 {  
    jQuery('.searchloader').show();
    jQuery("#frontRimLabel-hidden").val(offsetValue);
    jQuery("#frontRimLabel").text(label);
    jQuery('.tyre_finder_allcontent').hide();
    jQuery('#tyre_finder_modal_wrapper ul.tyre_finder_modal_submittyre').remove();
    jQuery('#tyre_finder_modal_wrapper').append('<ul class="tyre_finder_allcontent tyre_finder_modal_submittyre"></ul>');
    jQuery('#tyre_finder_modal_wrapper .tyre_finder_modal_submittyre').html(jQuery('#tyre_finder_modal_front_submittyreselection').html());

    jQuery('.allback').hide();
    jQuery('.backrim').show();

    jQuery('.stepper li').addClass('active');

    jQuery('.tyreValue .tvalue_4').text('R'+label);
    jQuery('.tyreValue .valuetxt').addClass('active');

    jQuery('.tyretext .txtcolor').addClass('active');
    var selectiondata=jQuery('#tyre_finder_modal .selectiondata').text();
    if(selectiondata != '') {
        jQuery('#tyre_finder_modal .selectiondata').text(jQuery('#tyre_finder_modal .selectiondata').text()+' R '+label);    
    }else{
        jQuery('#tyre_finder_modal .selectiondata').text(label);    
    }
    
    if(jQuery('.rear-tyre-selection').is(':visible')) { 
        var widthLabel = jQuery("#frontWidthLabel").text();
        var profileLabel = jQuery("#frontHeightLabel").text();
        jQuery('.selectiondata').text('');
        jQuery(".selectiondata").text(widthLabel+' / '+profileLabel+' / '+label);       
    }
    
    jQuery('.searchloader').hide();   
}

function getRearrim(rearheightValue,label,type)
{
        jQuery("#rearHeightLabel-hidden").val(rearheightValue);
        jQuery("#rearHeightLabel-text").val(label);
        jQuery('.searchloader').show();
        var rearwidthValue = jQuery("#rearWidthLabel-hidden").val();

         jQuery.ajax({
            url: getRimUrl,
            type: 'POST',
            data: {
                format: 'json',
                type: type,
                width: rearwidthValue,
                height: rearheightValue,
            },
            error: function () {
                alert("Error");
            },
            success: function (data) {
                if (data.status == 'SUCCESS') {
                           jQuery('.tyre_finder_allcontent').hide();
                           jQuery('#tyre_finder_modal_wrapper ul.tyre_finder_modal_rear_rim_content').remove();
                           jQuery('#tyre_finder_modal_wrapper').append('<ul class="tyre_finder_allcontent  tyre_finder_modal_rear_rim_content"></ul>');
                           jQuery('#tyre_finder_modal_wrapper .tyre_finder_modal_rear_rim_content').html(data.response);

                           jQuery('.allback').hide();
                           jQuery('.rearbackprofile').show();

                           jQuery('.stepper li').removeClass('active');
                           jQuery('.stepper li').eq(2).addClass('active');

                           jQuery('.tyreValue .tvalue_3').text(label);
                           jQuery('.tyreValue .valuetxt').removeClass('active');
                           jQuery('.tyreValue .tvalue_4').addClass('active');

                           jQuery('.tyretext .txtcolor').removeClass('active');
                           jQuery('.tyretext .txtcolor').eq(2).addClass('active');
                           var rearselectiondata=jQuery('.rearselectiondata').text();
                            if(rearselectiondata != '') {
                                jQuery('.rearselectiondata').text(jQuery('.rearselectiondata').text()+' / '+label);    
                            }else{
                                jQuery('.rearselectiondata').text(label);    
                            }

                            jQuery('.searchloader').hide();
                }
            },
        });
}

function selectRim(rimValue,label)
 {  
    jQuery('.searchloader').show();
    jQuery("#frontRimLabel-hidden").val(rimValue);
    jQuery("#frontRimLabel").text(label);
    jQuery('.tyre_finder_allcontent').hide();
    jQuery('#tyre_finder_modal_wrapper ul.tyre_finder_modal_submittyre').remove();
    jQuery('#tyre_finder_modal_wrapper').append('<ul class="tyre_finder_allcontent tyre_finder_modal_submittyre"></ul>');
    jQuery('#tyre_finder_modal_wrapper .tyre_finder_modal_submittyre').html(jQuery('#tyre_finder_modal_front_submittyreselection').html());

    jQuery('.allback').hide();
    jQuery('.backrim').show();

    jQuery('.stepper li').addClass('active');

    jQuery('.tyreValue .tvalue_4').text('R'+label);
    jQuery('.tyreValue .valuetxt').addClass('active');

    jQuery('.tyretext .txtcolor').addClass('active');
    var selectiondata=jQuery('#tyre_finder_modal .selectiondata').text();
    if(selectiondata != '') {
        jQuery('#tyre_finder_modal .selectiondata').text(jQuery('#tyre_finder_modal .selectiondata').text()+' R '+label);    
    }else{
        jQuery('#tyre_finder_modal .selectiondata').text(label);    
    }
    
    if(jQuery('.rear-tyre-selection').is(':visible')) { 
        var widthLabel = jQuery("#frontWidthLabel").text();
        var profileLabel = jQuery("#frontHeightLabel").text();
        jQuery('.selectiondata').text('');
        jQuery(".selectiondata").text(widthLabel+' / '+profileLabel+' / '+label);       
    }
    
    jQuery('.searchloader').hide();   
}

function selectRearRim(rimValue,label)
 {
    jQuery('.searchloader').show();
    jQuery("#rearRimLabel-hidden").val(rimValue);
    jQuery("#rearRimLabel-text").val(label);
    jQuery('.tyre_finder_allcontent').hide();
    jQuery('#tyre_finder_modal_wrapper ul.tyre_finder_modal_submittyre').remove();
    jQuery('#tyre_finder_modal_wrapper').append('<ul class="tyre_finder_allcontent tyre_finder_modal_submittyre"></ul>');
    jQuery('#tyre_finder_modal_wrapper .tyre_finder_modal_submittyre').html(jQuery('#tyre_finder_modal_rear_submittyreselection').html());

    jQuery('.allback').hide();
    jQuery('.rearbackrim').show();

    jQuery('.stepper li').addClass('active');

    jQuery('.tyreValue .tvalue_4').text('R'+label);
    jQuery('.tyreValue .valuetxt').addClass('active');

    jQuery('.tyretext .txtcolor').addClass('active');
    var rearselectiondata=jQuery('.rearselectiondata').text();
    if(rearselectiondata != '') {
        jQuery('.rearselectiondata').text(jQuery('.rearselectiondata').text()+' R '+label);    
    }else{
        jQuery('.rearselectiondata').text(label);    
    }
    jQuery('.searchloader').hide();
}

function submitTyreSelection(){
    jQuery('.searchloader').show();
    jQuery('#finder-size-form').submit();
}

function resetFinderSearchForm(){
    jQuery('.searchloader').show();
    jQuery('#frontWidthLabel').text('Width');
    jQuery('#frontWidthLabel-hidden').val('');

    jQuery('#frontHeightLabel').text('Height');
    jQuery('#frontHeightLabel-hidden').val('');

    jQuery('#frontRimLabel').text('Rim');
    jQuery('#frontRimLabel-hidden').val('');

    jQuery('#rearWidthLabel-hidden').val('');
    jQuery('#rearHeightLabel-hidden').val('');
    jQuery('#rearRimLabel-hidden').val('');

    jQuery('.searchloader').hide();
}

// Search by size popup end

//  Search by Vehicle popup start
function getmodel(value,label)
{
   jQuery("#vehicle_make").text(label);
   jQuery("#vehicle_make_hidden").val(value);
   jQuery('.searchloader').show();

        jQuery.ajax({
            type: 'POST',
            url: getModelUrl,
            data: "make=" + value,
            success: function (data) {
                   jQuery('.vehicle_finder_modal_allcontent').hide();
                   jQuery('#vehicle_finder_modal_wrapper ul.vehicle_finder_modal_model_content').remove();
                   jQuery('#vehicle_finder_modal_wrapper').append('<ul class="vehicle_finder_modal_allcontent  vehicle_finder_modal_model_content"></ul>');
                   jQuery('#vehicle_finder_modal_wrapper .vehicle_finder_modal_model_content').html(data.response);

                   jQuery('.vehicleallbackbutton').hide();
                   jQuery('.vehiclebackmake').show();

                    jQuery('.stepper li').removeClass('active');
                    jQuery('.stepper li.model').addClass('active');

                   jQuery('.reset-vehicle-selection').show();
                    var vehicleselectiondata=jQuery('#vehicle_finder_modal .vehicleselectiondata').text();
                    if(vehicleselectiondata != '') {
                        jQuery('#vehicle_finder_modal .vehicleselectiondata').text(jQuery('#vehicle_finder_modal .vehicleselectiondata').text()+' / '+label);    
                    }else{
                        jQuery('#vehicle_finder_modal .vehicleselectiondata').text(label);    
                    }

                    jQuery('.searchloader').hide();
            }
        });
}

function getyear(value,label)
{
    var model = value;
    var make = jQuery('#vehicle_make_hidden').val();
    jQuery("#vehicle_model").text(label);
    jQuery("#vehicle_model_hidden").val(value);
    jQuery('.searchloader').show();

        jQuery.ajax({
            type: 'POST',
            url: getyearUrl,
            data: {make: make, model: model},
            success: function (data) {
                   jQuery('.vehicle_finder_modal_allcontent').hide();
                   jQuery('#vehicle_finder_modal_wrapper ul.vehicle_finder_modal_year_content').remove();
                   jQuery('#vehicle_finder_modal_wrapper').append('<ul class="vehicle_finder_modal_allcontent  vehicle_finder_modal_year_content"></ul>');
                   jQuery('#vehicle_finder_modal_wrapper .vehicle_finder_modal_year_content').html(data.response);

                   jQuery('.vehicleallbackbutton').hide();
                   jQuery('.vehiclebackmodel').show();

                    jQuery('.stepper li.model').removeClass('active');
                    jQuery('.stepper li.year').addClass('active');                   

                    var vehicleselectiondata=jQuery('#vehicle_finder_modal .vehicleselectiondata').text();
                    if(vehicleselectiondata != '') {
                        jQuery('#vehicle_finder_modal .vehicleselectiondata').text(jQuery('#vehicle_finder_modal .vehicleselectiondata').text()+' / '+label);    
                    }else{
                        jQuery('#vehicle_finder_modal .vehicleselectiondata').text(label);    
                    }

                    jQuery('.searchloader').hide();
            }
        });
}

var enginesTyre="";
function getengine(value,label)
{
    var make = jQuery('#vehicle_make_hidden').val();
    var model = jQuery('#vehicle_model_hidden').val();
    var year = value;
    jQuery('.searchloader').show();

    jQuery("#vehicle_year").text(label);
    jQuery("#vehicle_year_hidden").val(value);

        jQuery.ajax({
            type: 'POST',
            url: getengineUrl,
            data: {make: make, model: model,year: year},
            success: function (data) {
                   /* jQuery('.vehicle_finder_modal_allcontent').hide();
                   jQuery('#vehicle_finder_modal_wrapper ul.vehicle_finder_modal_engine_content').remove();
                   jQuery('#vehicle_finder_modal_wrapper').append('<ul class="vehicle_finder_modal_allcontent  vehicle_finder_modal_engine_content"></ul>');
                   jQuery('#vehicle_finder_modal_wrapper .vehicle_finder_modal_engine_content').html(data.engineHtml);
                   enginesTyre=data.enginesTyre;

                   jQuery('.vehicleallbackbutton').hide();
                   jQuery('.vehiclebackyear').show();

                    jQuery('.stepper li.year').removeClass('active');
                    jQuery('.stepper li.engine').addClass('active'); 

                    var vehicleselectiondata=jQuery('#vehicle_finder_modal .vehicleselectiondata').text();
                    if(vehicleselectiondata != '') {
                        jQuery('#vehicle_finder_modal .vehicleselectiondata').text(jQuery('#vehicle_finder_modal .vehicleselectiondata').text()+' / '+label);    
                    }else{
                        jQuery('#vehicle_finder_modal .vehicleselectiondata').text(label);    
                    } */
					
					/* Start unique tyre sizes */
					console.log(data.enginesTyre);
					//var enginesTyre=data.enginesTyre.toString();
					var enginesTyre=data.enginesTyre.join("");
					jQuery('#vehicle_engine_hidden').val(label);
					jQuery('.searchloader').show();
					jQuery('#input-search').val('');
					jQuery('.vehicle_finder_modal_engine_content').attr('id','');
					jQuery('.vehicle_finder_modal_allcontent').hide();
					jQuery('#vehicle_finder_modal_wrapper ul.vehicle_finder_modal_tyresize_content').remove();
					jQuery('#vehicle_finder_modal_wrapper').append('<ul class="vehicle_finder_modal_allcontent  vehicle_finder_modal_tyresize_content" id="searchUL"></ul>');

					jQuery('#vehicle_finder_modal_wrapper .vehicle_finder_modal_tyresize_content').html(enginesTyre);
					jQuery(".vehicle_finder_modal_allcontent.vehicle_finder_modal_tyresize_content").append("<li id='last-note'><span>Note: Most vehicle manufacturer’s produce vehicles with more than one possible size.  We strongly recommend all customers check the tyre size printed on the side wall of their tyres before purchase.</span></li>");
					jQuery('.vehicleallbackbutton').hide();
					jQuery('.vehiclebackyear').show();
					jQuery('.stepper li.year').removeClass('active');
                    jQuery('.stepper li.engine').addClass('active'); 
					var vehicleselectiondata=jQuery('#vehicle_finder_modal .vehicleselectiondata').text();
					if(vehicleselectiondata != '') {
						jQuery('#vehicle_finder_modal .vehicleselectiondata').text(jQuery('#vehicle_finder_modal .vehicleselectiondata').text()+' / '+label);    
					}else{
						jQuery('#vehicle_finder_modal .vehicleselectiondata').text(label);    
					}
					/* End unique tyre sizes */

                    jQuery('.searchloader').hide();
            }
        });
}

function getengineforoffset(value,label)
{
    var make = jQuery('#vehicle_make_hidden').val();
    var model = jQuery('#vehicle_model_hidden').val();
    var year = value;
    jQuery('.searchloader').show();

    jQuery("#vehicle_year").text(label);
    jQuery("#vehicle_year_hidden").val(value);

        jQuery.ajax({
            type: 'POST',
            url: getengineUrl,
            data: {make: make, model: model,year: year},
            success: function (data) {
          
          /* Start unique tyre sizes */
          console.log(data.enginesTyre);
          //var enginesTyre=data.enginesTyre.toString();
          var enginesTyre=data.enginesTyre.join("");
          jQuery('#vehicle_engine_hidden').val(label);
          jQuery('.searchloader').show();
          jQuery('#input-search').val('');
          jQuery('.vehicle_finder_modal_engine_content').attr('id','');
          jQuery('.vehicle_finder_modal_allcontent').hide();
          jQuery('#vehicle_finder_modal_wrapper ul.vehicle_finder_modal_tyresize_content').remove();
          jQuery('#vehicle_finder_modal_wrapper').append('<ul class="vehicle_finder_modal_allcontent  vehicle_finder_modal_tyresize_content" id="searchUL"></ul>');

          jQuery('#vehicle_finder_modal_wrapper .vehicle_finder_modal_tyresize_content').html(enginesTyre);
          jQuery(".vehicle_finder_modal_allcontent.vehicle_finder_modal_tyresize_content").append("<li id='last-note'><span>Note: Most vehicle manufacturer’s produce vehicles with more than one possible size.  We strongly recommend all customers check the wheel size printed on the side wall of their wheel size before purchase.</span></li>");
          jQuery('.vehicleallbackbutton').hide();
          jQuery('.vehiclebackyear').show();
          jQuery('.stepper li.year').removeClass('active');
                    jQuery('.stepper li.engine').addClass('active'); 
          var vehicleselectiondata=jQuery('#vehicle_finder_modal .vehicleselectiondata').text();
          if(vehicleselectiondata != '') {
            jQuery('#vehicle_finder_modal .vehicleselectiondata').text(jQuery('#vehicle_finder_modal .vehicleselectiondata').text()+' / '+label);    
          }else{
            jQuery('#vehicle_finder_modal .vehicleselectiondata').text(label);    
          }
          /* End unique tyre sizes */

                    jQuery('.searchloader').hide();
            }
        });
}

function getenginetyre(value, label){
    jQuery('#vehicle_engine_hidden').val(label);
    jQuery('.searchloader').show();
    var tyresize=enginesTyre[value];
    jQuery('.vehicle_finder_modal_allcontent').hide();
    jQuery('#vehicle_finder_modal_wrapper ul.vehicle_finder_modal_tyresize_content').remove();
    jQuery('#vehicle_finder_modal_wrapper').append('<ul class="vehicle_finder_modal_allcontent  vehicle_finder_modal_tyresize_content"></ul>');
    jQuery('#vehicle_finder_modal_wrapper .vehicle_finder_modal_tyresize_content').html(tyresize);

    jQuery('.vehicleallbackbutton').hide();
    jQuery('.vehiclebackengine').show();
    var vehicleselectiondata=jQuery('#vehicle_finder_modal .vehicleselectiondata').text();
    if(vehicleselectiondata != '') {
        jQuery('#vehicle_finder_modal .vehicleselectiondata').text(jQuery('#vehicle_finder_modal .vehicleselectiondata').text()+' / '+label);    
    }else{
        jQuery('#vehicle_finder_modal .vehicleselectiondata').text(label);    
    }

    jQuery('.searchloader').hide();
}
function showproduct(width, height, rim, rear_width, rear_height, rear_rim) {
        jQuery('.searchloader').show();
        jQuery("#frontWidthLabel-hidden").val(width);

        jQuery("#frontHeightLabel-hidden").val(height);

        jQuery("#frontRimLabel-hidden").val(rim);

        if(rear_width != '')
        {
           jQuery("#rearWidthLabel-hidden").val(rear_width);
        }

        if(rear_height != '')
        {
           jQuery("#rearHeightLabel-hidden").val(rear_height);
        }

        if(rear_rim != '')
        {
              jQuery("#rearRimLabel-hidden").val(rear_rim);
        }

        jQuery('#finder-size-form').submit();
        jQuery('.searchloader').hide();
}
function showwheelsproduct(width, rim, offset) {
        if ( (width == '') || (rim == '') || (offset == '') ){
          alert('There are no products in this wheel size');
		  return false;
        }
        jQuery('.searchloader').show();
        jQuery("#frontWidthLabel-hidden").val(width);
        jQuery("#frontHeightLabel-hidden").val(rim);
        jQuery("#frontRimLabel-hidden").val(offset);

        jQuery('#finder-size-form').submit();
        jQuery('.searchloader').hide();
}
function goback(contentname,backbutton,type,searchbytype){
    jQuery('.searchloader').show();
    jQuery('.tyre_finder_allcontent').hide();
    jQuery('.'+contentname).show();

    jQuery('.allback').hide();
    jQuery('.'+backbutton).show();

    console.log(searchbytype);
    if(searchbytype == 'Width' && type != 'rear'){
        jQuery('.allback').hide();
    }

    if(searchbytype == 'FrontRim'){
        jQuery('.stepper li').removeClass('active');
        jQuery('.stepper li').eq(2).addClass('active');

        jQuery('.tyreValue .valuetxt').removeClass('active');
        jQuery('.tyreValue .tvalue_4').addClass('active');

        jQuery('.tyretext .txtcolor').removeClass('active');
        jQuery('.tyretext .txtcolor').eq(2).addClass('active');
       
        var widthLabel = jQuery("#frontWidthLabel").text();
        var profileLabel = jQuery("#frontHeightLabel").text();
        jQuery(".data-selected").text(widthLabel+' / '+profileLabel);

        jQuery('.rear-tyre-selection').hide();
        jQuery('.rearselectiondata').text('');
        
       
    }

    if(searchbytype == 'Rim' || searchbytype == 'rearRim'){
        jQuery('.stepper li').removeClass('active');
        jQuery('.stepper li').eq(2).addClass('active');

        jQuery('.tyreValue .valuetxt').removeClass('active');
        jQuery('.tyreValue .tvalue_4').addClass('active');

        jQuery('.tyretext .txtcolor').removeClass('active');
        jQuery('.tyretext .txtcolor').eq(2).addClass('active');
       
        if(jQuery('.rear-tyre-selection').is(':visible')) { 
            var rearselectiondata=jQuery('.rearselectiondata').text();
                if(rearselectiondata != "") {
                    var widthLabel = jQuery("#rearWidthLabel-text").val();
                    var profileLabel = jQuery("#rearHeightLabel-text").val();
                    jQuery(".rearselectiondata").text(widthLabel+' / '+profileLabel);
                }else{
                    jQuery("#rearwWidthLabel").hide();
                    jQuery('.allback').hide();
                    jQuery('.backrim').show();

                }
            }else{
                var widthLabel = jQuery("#frontWidthLabel").text();
                var profileLabel = jQuery("#frontHeightLabel").text();
                jQuery(".data-selected").text(widthLabel+' / '+profileLabel);
            }
    }

    if(searchbytype == 'Height' || searchbytype == 'rearHeight'){
        jQuery('.stepper li').removeClass('active');
        jQuery('.stepper li').eq(1).addClass('active');

        jQuery('.tyreValue .valuetxt').removeClass('active');
        jQuery('.tyreValue .tvalue_2').addClass('active');
        jQuery('.tyreValue .tvalue_3').addClass('active');
        jQuery('.tyretext .txtcolor').removeClass('active');
        jQuery('.tyretext .txtcolor').eq(1).addClass('active');
        if(jQuery('.rear-tyre-selection').is(':visible'))  {
            var widthLabel = jQuery("#rearWidthLabel-text").val();
            jQuery(".rearselectiondata").text(widthLabel);
        }else{
            var widthLabel = jQuery("#frontWidthLabel").text();
            jQuery(".data-selected").text(widthLabel);
        }
    }

    if(searchbytype == 'Width' || searchbytype == 'rearWidth'){
      jQuery('.stepper li').removeClass('active');
      jQuery('.stepper li').eq(0).addClass('active');

      jQuery('.tyreValue .valuetxt').removeClass('active');
      jQuery('.tyreValue .tvalue_1').addClass('active');

      jQuery('.tyretext .txtcolor').removeClass('active');
      jQuery('.tyretext .txtcolor').eq(0).addClass('active');
      if(jQuery('.rear-tyre-selection').is(':visible'))  {
            jQuery(".rearselectiondata").text('');
        }else{
            jQuery(".data-selected").text('');
        }
    }
    if(searchbytype == 'Engine'){
      alert('Test Dev');  
      jQuery('.stepper li.Engine').removeClass('active');
      jQuery('.stepper li.Year').addClass('active');        
    }

    if(searchbytype == 'Year'){
      jQuery('.stepper li.Engine').removeClass('active');
      jQuery('.stepper li.Make').addClass('active');          
    }    

    if(searchbytype == 'Model'){
      jQuery('.stepper li.Engine').removeClass('active');
      jQuery('.stepper li.Make').addClass('active');          
    }    

    if(searchbytype == 'Make'){
      jQuery('.stepper li.Engine').removeClass('active');
      jQuery('.stepper li.Make').addClass('active');          
    }      

    jQuery('.searchloader').hide();
}
function resetselection(){
  jQuery('.searchloader').show();
   jQuery('.tyre_finder_allcontent').hide();
   jQuery('.tyre_finder_modal_front_width_content').show();

   jQuery('.allback').hide();

   jQuery('#frontWidthLabel').text('Width');
   jQuery('#frontWidthLabel-hidden').val('');

   jQuery('#frontHeightLabel').text('Height');
   jQuery('#frontHeightLabel-hidden').val('');

   jQuery('#frontRimLabel').text('Rim');
   jQuery('#frontRimLabel-hidden').val('');

   jQuery('.reset-size-selection').hide();
   jQuery('.data-selected').text('');
   jQuery('.tyreInfoLeft .tyreinfoResult .tyreselection .selectiontitle').text('Current Selection');
   jQuery('.tyreInfoLeft .stepper li').removeClass('active');
   jQuery('.tyreInfoLeft .stepper li:first').addClass('active');
   jQuery('.tyreInfoLeft .tyreinfoResult .rear-tyre-selection .rearselectiondata').text('');
   jQuery('.rear-tyre-selection').hide();
   jQuery('.searchloader').hide();
}
 function vehcilegoback(contentname,backbutton,roundimage,searchbytype){
    jQuery('.searchloader').show();
    jQuery('.vehicle_finder_modal_allcontent').hide();
    jQuery('.'+contentname).show();

    jQuery('.vehicleallbackbutton').hide();
    jQuery('.'+backbutton).show();
    jQuery('.searchloader').hide();

    var vehicle_make=jQuery('#vehicle_make').text();
    var vehicle_model=jQuery('#vehicle_model').text();
    var vehicle_year=jQuery('#vehicle_year').text();
    
    if(searchbytype == 'Engine') {
      jQuery('#vehicle_finder_modal .vehicleselectiondata').text(vehicle_make+' / '+vehicle_model+' / '+vehicle_year);    
      jQuery('.vehiclestepper li').removeClass('active');
        jQuery('.vehiclestepper li').eq(3).addClass('active');
    }

    if(searchbytype == 'Year') {
      jQuery('#vehicle_finder_modal .vehicleselectiondata').text(vehicle_make+' / '+vehicle_model);    
      jQuery('.vehiclestepper li').removeClass('active');
      jQuery('.vehiclestepper li').eq(2).addClass('active');
    }

    if(searchbytype == 'Model') {
      jQuery('#vehicle_finder_modal .vehicleselectiondata').text(vehicle_make);    
      jQuery('.vehiclestepper li').removeClass('active');
       jQuery('.vehiclestepper li').eq(1).addClass('active');
    }
    if(searchbytype == 'Make') {
      jQuery('#vehicle_finder_modal .vehicleselectiondata').text('');    
      jQuery('.vehiclestepper li').removeClass('active');
      jQuery('.vehiclestepper li').eq(0).addClass('active');
    }
    
 }
 function resetvehicleselection(){
    jQuery('.searchloader').show();
    jQuery('.vehicle_finder_modal_allcontent').hide();
    jQuery('.vehicle_finder_modal_make_content').show();

    jQuery('.vehicleallbackbutton').hide();

    jQuery('#vehicle_make').text('Make');
    jQuery('#vehicle_make_hidden').val('');

    jQuery('#vehicle_model').text('Model');
    jQuery('#vehicle_model-hidden').val('');

    jQuery('#vehicle_year').text('Year');
    jQuery('#vehicle_year-hidden').val('');

    jQuery('.reset-vehicle-selection').hide();
    jQuery('.reset-vehicle-selection').hide();
    jQuery('.searchloader').hide();
 }
// Start To prevent on click search direct redirection without selecting options
jQuery('#finder-size-form').submit(function(e){
  e.preventDefault(e);
  var frontWidth = jQuery("#frontWidthLabel-hidden").val();
  var frontHeight = jQuery("#frontHeightLabel-hidden").val();
  var frontRim = jQuery("#frontRimLabel-hidden").val();
  if (frontWidth && frontHeight && frontRim) {
    jQuery(this).unbind('submit').submit();
  } else {
    jQuery( "#frontWidthLabel" ).trigger( "click" );
  }
});

jQuery('#finder-vehicle-form').submit(function(e){
  e.preventDefault(e);
  var vehicleMake = jQuery("#vehicle_make_hidden").val();
  var vehicleModel = jQuery("#vehicle_model_hidden").val();
  var vehicleYear = jQuery("#vehicle_year_hidden").val();
  if (vehicleMake && vehicleModel && vehicleYear) {
    jQuery(this).unbind('submit').submit();
  } else {
    jQuery( "#vehicle_make" ).trigger( "click" );  
  }
});
// End To prevent on click search direct redirection without selecting options