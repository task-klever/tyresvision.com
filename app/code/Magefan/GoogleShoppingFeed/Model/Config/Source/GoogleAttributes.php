<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\GoogleShoppingFeed\Model\Config\Source;

class GoogleAttributes
{
    /**
     * List of attributes
     * https://support.google.com/merchants/answer/6324456?hl=en&ref_topic=6324338
     */
    const attributes = [
        ['label' => 'ID [id]', 'value' => 'id', 'tag' => 'g:id'],
        ['label' => 'Title [title]', 'value' => 'title', 'tag' => 'g:title'],
        ['label' => 'Description [description]', 'value' => 'description', 'tag' => 'g:description'],
        ['label' => 'Link [link]', 'value' => 'link', 'tag' => 'g:link'],
        ['label' => 'Image link [image_link]', 'value' => 'image_link', 'tag' => 'g:image_link'],
        //['label' => 'Mobile link [mobile_link]', 'value' => 'mobile_link', 'tag' => 'g:mobile_link'],
        ['label' => 'Additional image link [additional_image_link]', 'value' => 'additional_image_link', 'tag' => 'g:image_link'],
        ['label' => 'Availability [availability]', 'value' => 'availability', 'tag' => 'g:availability'],
        //['label' => 'Availability date [availability_date]', 'value' => 'availability_date', 'tag' => 'g:availability_date'],
        ['label' => 'Cost of goods (cogs) [cost_of_goods_sold]', 'value' => 'cost_of_goods_sold', 'tag' => 'g:cost_of_goods_sold'],
        ['label' => 'Expiration date [expiration_date]', 'value' => 'expiration_date', 'tag' => 'g:expiration_date'],
        ['label' => 'Price [price]', 'value' => 'price', 'tag' => 'g:price'],
        ['label' => 'Sale price [sale_price]', 'value' => 'sale_price', 'tag' => 'g:sale_price'],
        ['label' => 'Sale price effective date [sale_price_effective_date]', 'value' => 'sale_price_effective_date', 'tag' => 'g:sale_price_effective_date'],
        ['label' => 'Unit pricing measure [unit_pricing_measure]', 'value' => 'unit_pricing_measure', 'tag' => 'g:unit_pricing_measure'],
        ['label' => 'Unit pricing base measure [unit_pricing_base_measure]', 'value' => 'unit_pricing_base_measure', 'tag' => 'g:unit_pricing_base_measure'],
        //['label' => 'Installment [installment]', 'value' => 'installment', 'tag' => 'g:installment'],
        //Subscription cost [subscription_cost]
        //Loyalty points [loyalty_points]
        ['label' => 'Google product category [google_product_category]', 'value' => 'google_product_category', 'tag' => 'g:google_product_category'],
        ['label' => 'Product type [product_type]', 'value' => 'product_type', 'tag' => 'g:product_type'],
        //Google Search index link [canonical_link]
        ['label' => 'Brand [brand]', 'value' => 'brand', 'tag' => 'g:brand'],
        ['label' => 'GTIN [gtin]', 'value' => 'gtin', 'tag' => 'g:gtin'],
        ['label' => 'MPN [mpn]', 'value' => 'mpn', 'tag' => 'g:mpn'],
        ['label' => 'Identifier exists [identifier_exists]', 'value' => 'identifier_exists', 'tag' => 'g:identifier_exists'],
        ['label' => 'Condition [condition]', 'value' => 'condition', 'tag' => 'g:condition'],
        ['label' => 'Adult [adult]', 'value' => 'adult', 'tag' => 'g:adult'],
        ['label' => 'Multipack [multipack]', 'value' => 'multipack', 'tag' => 'g:multipack'],
        ['label' => 'Bundle [is_bundle]', 'value' => 'is_bundle', 'tag' => 'g:is_bundle'],
        //Energy efficiency class [energy_efficiency_class], Minimum energy efficiency class [min_energy_efficiency_class], Maximum energy efficiency class [max_energy_efficiency_class]
        ['label' => 'Age group [age_group]', 'value' => 'age_group', 'tag' => 'g:age_group'],
        ['label' => 'Color [color]', 'value' => 'color', 'tag' => 'g:color'],
        ['label' => 'Gender [gender]', 'value' => 'gender', 'tag' => 'g:gender'],
        ['label' => 'Material [material]', 'value' => 'material', 'tag' => 'g:material'],
        ['label' => 'Pattern [pattern]', 'value' => 'pattern', 'tag' => 'g:pattern'],
        ['label' => 'Size [size]', 'value' => 'size', 'tag' => 'g:size'],
        ['label' => 'Size type [size_type]', 'value' => 'size_type', 'tag' => 'g:size_type'],
        ['label' => 'Size system [size_system]', 'value' => 'size_system', 'tag' => 'g:size_system'],
        //Item group ID [item_group_id]
        //Product length [product_length], product width [product_width], product height [product_height], product weight [product_weight]
        //Product detail [product_detail]
        ['label' => 'Product highlight [product_highlight]', 'value' => 'product_highlight', 'tag' => 'g:product_highlight'],
        //Ads redirect [ads_redirect]
        ['label' => 'Custom label 0 [custom_label_0]', 'value' => 'custom_label_0', 'tag' => 'g:custom_label_0'],
        ['label' => 'Custom label 1 [custom_label_1]', 'value' => 'custom_label_1', 'tag' => 'g:custom_label_1'],
        ['label' => 'Custom label 2 [custom_label_2]', 'value' => 'custom_label_2', 'tag' => 'g:custom_label_2'],
        ['label' => 'Custom label 3 [custom_label_3]', 'value' => 'custom_label_3', 'tag' => 'g:custom_label_3'],
        ['label' => 'Custom label 4 [custom_label_4]', 'value' => 'custom_label_4', 'tag' => 'g:custom_label_4'],
        ['label' => 'Promotion ID [promotion_id]', 'value' => 'promotion_id', 'tag' => 'g:promotion_id'],
        //Excluded destination [excluded_destination]
        //Included destination [included_destination]
        //Excluded countries for Shopping ads [shopping_ads_excluded_country]
        //Pause [pause]
        //Shipping [shipping]
        [
            'label' => 'Shipping [shipping]',
            'value' =>
                [
                    ['label' => 'Shipping [country]', 'value' => 'shipping_country', 'tag' => 'g:country'],
                    ['label' => 'Shipping [region]', 'value' => 'shipping_region', 'tag' => 'g:region'],
                    ['label' => 'Shipping [postal_code]', 'value' => 'shipping_postal_code', 'tag' => 'g:postal_code'],
                    ['label' => 'Shipping [location_id]', 'value' => 'shipping_location_id', 'tag' => 'g:location_id'],
                    ['label' => 'Shipping [location_group_name]', 'value' => 'shipping_location_group_name', 'tag' => 'g:location_group_name'],
                    ['label' => 'Shipping [service]', 'value' => 'shipping_service', 'tag' => 'g:service'],
                    ['label' => 'Shipping [price]', 'value' => 'shipping_price', 'tag' => 'g:price'],
                    ['label' => 'Shipping [min_handling_time]', 'value' => 'shipping_min_handling_time', 'tag' => 'g:min_handling_time'],
                    ['label' => 'Shipping [max_handling_time]', 'value' => 'shipping_max_handling_time', 'tag' => 'g:max_handling_time'],
                    ['label' => 'Shipping [min_transit_time]', 'value' => 'shipping_min_transit_time', 'tag' => 'g:min_transit_time'],
                    ['label' => 'Shipping [max_transit_time]', 'value' => 'shipping_max_transit_time', 'tag' => 'g:max_transit_time'],
                ]
        ],
        ['label' => 'Shipping label [shipping_label]', 'value' => 'shipping_label', 'tag' => 'g:shipping_label'],
        ['label' => 'Shipping length [shipping_length]', 'value' => 'shipping_length', 'tag' => 'g:shipping_length'],
        ['label' => 'Shipping width [shipping_width]', 'value' => 'shipping_width', 'tag' => 'g:shipping_width'],
        ['label' => 'Shipping weight [shipping_weight]', 'value' => 'shipping_weight', 'tag' => 'g:shipping_weight'],
        ['label' => 'Shipping height [shipping_height]', 'value' => 'shipping_height', 'tag' => 'g:shipping_height'],
        [
            'label' => 'Tax [tax]',
            'value' =>
                [
                    ['label' => 'Tax [country]', 'value' => 'tax_country', 'tag' => 'g:country'],
                    ['label' => 'Tax [region]', 'value' => 'tax_region', 'tag' => 'g:region'],
                    ['label' => 'Tax rate [rate]', 'value' => 'tax_rate', 'tag' => 'g:rate'],
                    ['label' => 'Tax [tax_ship]', 'value' => 'tax_ship', 'tag' => 'g:tax_ship'],
                ]
        ],
        //Ships from country [ships_from_country]
        //Transit time label [transit_time_label]
        //Maximum handling time [max_handling_time], minimum handling time [min_handling_time]
        //Tax [tax]
        //Tax category [tax_category]
    ];
}
