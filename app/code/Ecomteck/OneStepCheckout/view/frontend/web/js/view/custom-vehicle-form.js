define([
    'jquery',
    'uiComponent',
    'mage/validation',
    'ko',
    'Hdweb_Shippingform/js/action/getvehiclemodel',
    'Hdweb_Shippingform/js/action/getvehicleyear',
], function ($, Component, validation, ko,getvehiclemodelAction, getvehicleyearAction) {
    'use strict';


    $(document).on('change', "select[name='vehiclelist1']", function() {
      var selectedmodel = $("select[name='vehiclelist1']").val();
             if (selectedmodel) {
                getvehiclemodelAction(selectedmodel);
            }
    });
    $(document).on('change', "select[name='vehiclemodel1']", function() {
      var selectevehicledmodel = $("select[name='vehiclelist1']").val();
             var selectedyear = $("select[name='vehiclemodel1']").val();
             var data = {'make': selectevehicledmodel, 'model': selectedyear};
             if (data) {
                getvehicleyearAction(data);
            }
    });

    return Component.extend({
        initialize: function () {
            this._super();
            // component initialization logic
            return this;
        },

        initObservable: function () {

            this._super()
                .observe({
                    isSameAsVehicle: ko.observable(false)                        
                });
                
            return this;
        },

        getVehicleList: function () {
            
            
                    var areaList = ko.observableArray(window.checkoutConfig.vehicle_makes);
               
            
            return areaList;
        },

        changevehicle: function (){

             var selectedmodel = $('#vehiclelist').val();
             if (selectedmodel) {
                getvehiclemodelAction(selectedmodel);
            }
           
        },

        changemodel: function (){
            var selectevehicledmodel = $('#vehiclelist').val();
             var selectedyear = $('#vehiclemodel').val();
             var data = {'make': selectevehicledmodel, 'model': selectedyear};
             if (data) {
                getvehicleyearAction(data);
            }
           
        },

        getVehicleModel: function () {
            
            
                   var vmodel = ko.observableArray([]);
                        return vmodel;
        },

        getVehicleYear: function () {

            var vyear = ko.observableArray([]);
                        return vyear;

        },



        /**
         * Form submit handler
         *
         * This method can have any name.
         */
        savecustomshippingdata: function() {

            if (jQuery('#custom-shipping-address-chk').is(':checked'))
            {
                jQuery('#billing-button-save').trigger('click');
            } 
            else
            {   
                if (jQuery(".payment-method").hasClass("_active"))
                {
                    // trigger form validation
                    this.source.set('params.invalid', false);
                    this.source.trigger('customCheckoutForm.data.validate');

                    //check billing form validation
                    this.source.trigger('billingAddressshared.data.validate');

                    if (this.source.get('billingAddressshared.custom_attributes')) {
                        this.source.trigger('billingAddressshared.custom_attributes.data.validate');
                    }

                    // verify that form data is valid
                    if (!this.source.get('params.invalid')) {
                        // data is retrieved from data provider by value of the customScope property
                        var formData = this.source.get('customCheckoutForm');
                        // do something with form data
                        /*console.dir(formData);*/
                        savecustomshippingdataAction(formData);
                    }
                }else{
                    alert($.mage.__('Please Selcect Payment Method First'));
                }                    
            }
        }
        // isdetailsVisible: function() {
        //     var selectedShippingRate = checkoutData.getSelectedShippingRate();
        //     if (selectedShippingRate) {
        //         var activeMethod = selectedShippingRate.split("_");
        //         if (activeMethod[0] == 'shiptoaddress') {
        //             return true;
        //         }
        //     }
        // }
    });
});