# Product Import

Imports product data into Magento 2 via direct database access.

## Important

Use this library if you need speed and don't care about any plugins or custom event handlers that are normally activated when products change in Magento.

This library just helps you to get products into Magento's database quickly, low level.

After an import has completed, the product and category indexers need to be run. The library will not do this for you.

You need to perform logging yourself.


## Example

    $log = "";

    $config = new ImportConfig();
    $config->eavAttributes = ['name', 'price'];
    $config->resultCallback[] = function(Product $product) use (&$log) {

        if ($product->ok) {
            $log .= sprintf("%s: success! sku = %s, id = %s\n", $product->lineNumber, $product->sku, $product->id);
        } else {
            $log .= sprintf("%s: failed! error = %s\n", $product->lineNumber, $product->error);
        }

    };

    $factory = ObjectManager::getInstance()->get(ImporterFactory::class);
    list($importer, $error) = $factory->create($config);

    $lines = [
        ['Purple Box', "purple-box", "3.95"],
        ['Yellow Box', "yellow-box", "2.95"]
    ];

    foreach ($lines as $i => $line) {

        $product = new SimpleProduct();
        $product->name = $line[0];
        $product->sku = $line[1];
        $product->price = $line[2];
        $product->lineNumber = $i + 1;

        $importer->insert($product);
    }

    $importer->flush();

## Supported

* inserts
* updates (using sku as a key)
* input validation
* import at global and store view level

## Features

* trims leading and trailing whitespace (spaces, tabs, newlines) from all fields
* input is validated on data type, requiredness,  and length restrictions

## To be supported

* dry run
* all types of products
* categories
* product-category links (by id, by name)
* auto create category (from name)
* stock
* store view name
* auto add attribute option
* csv import
* rest request
* error reporting of failed imports
* import images using files, urls
* plugins (ie using new product ids to perform consecutive actions)
* trim: none, all except text fields

## Goals

* fast
* easy to use
* robust
* hard to make mistakes
* flexible
* complete

## Assumptions

* Input in UTF-8

## Notes

* Think about very long field values (not crossing default 1MB query size?)
* People might import configurables before simples
* check for non-utf-8 in csv imports
* Is there no better way to ensure that no 2 products get the same sku?
* Other values than 1 and 2 can be entered for 'status'
* Updates: do not use default value for attribute_set_code, because it may unintentionally overwrite an existing one

## On empty values

* An unassigned property will not be stored at all
* A value of null will not be stored at all
* A value of "" will be stored as an empty string for varchar and text attributes, otherwise will not be stored at all

## Thanks to

This project ows a great deal of ideas and code from Magmi / Magento 1 [Magmi](https://github.com/dweeves/magmi-git)

https://dev.mysql.com/doc/refman/5.6/en/insert-optimization.html
https://dev.mysql.com/doc/refman/5.6/en/optimizing-innodb-bulk-data-loading.html

## Funny constructs

Why I use

    list($importer, $error) = $factory->create($config);

This is Go-style programming. It forces the developer to think about the error that may occur.

## Fields

sku
store_view_code
attribute_set_code
product_type
categories
product_websites
name
description
short_description
weight
product_online
tax_class_name
visibility
price
special_price
special_price_from_date
special_price_to_date
url_key	meta_title
meta_keywords
meta_description
base_image
base_image_label
small_image
small_image_label
thumbnail_image
thumbnail_image_label
swatch_image
swatch_image_label
created_at
updated_at
new_from_date
new_to_date
display_product_options_in
map_price
msrp_price
map_enabled
gift_message_available
custom_design
custom_design_from
custom_design_to
custom_layout_update
page_layout
product_options_container
msrp_display_actual_price_type
country_of_manufacture
additional_attributes
related_skus
related_position
crosssell_skus
crosssell_position
upsell_skus
upsell_position
additional_images
additional_image_labels
hide_from_product_page
bundle_price_type
bundle_sku_type
bundle_price_view
bundle_weight_type
bundle_values
bundle_shipment_type
associated_skus

