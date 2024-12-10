/**
 * Ecomteck_StorePickup Magento Extension
 *
 * @category    Ecomteck
 * @package     Ecomteck_StorePickup
 * @author      Ecomteck <ecomteck@gmail.com>
 * @website    http://www.ecomteck.com
 */

define(['jquery'], function($) {
    var google_maps_loaded_def = null;
    if (!google_maps_loaded_def) {

        google_maps_loaded_def = $.Deferred();

        window.google_maps_loaded = function() {
            google_maps_loaded_def.resolve(google.maps);
        }
        var key = window.checkoutConfig.shipping.select_store.maps_api_key;
        require(['https://maps.googleapis.com/maps/api/js?key='+ key +'&callback=google_maps_loaded'], function() {}, function(err) {
            google_maps_loaded_def.reject();
        });
    }
    return google_maps_loaded_def.promise();
});