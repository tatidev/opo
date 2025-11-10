
# DEBUGING  `NEW ITEM Form`

# FIX

See this code in application/views/item/item_list.php circa line 200.
```php
    // Product is empty
    // Show modal to add items
    // open_item_modal( item_id, is_multi, product_type, referrer )
    // eg. open_item_modal( '0', false , product_type, 'item_list.php');
    open_item_modal( '0', false , product_type, 'item_list.php');
    // broken code was  open_item_modal() with no params
```



## FILE: 
`ASSETS` common.js
`CONTROLLER`: Product::edit()
              Product::submit_form() <-- Check POST
`VIEW`: product/form/form_regular
```
<form id="frmProduct" method="post" action="https://localhost:8444/product/submit_form" class="">
        <input type="hidden" class="form-control" id="product_messages_encoded" name="product_messages_encoded" value="">
        <input type="hidden" name="product_id" value="0">
        <input type="hidden" name="product_type" value="R">

```

`CONTROLLER`: Item::edit_item()
`VIEW`: item/form/view.php

__FINDINGS NOTES:__
- Looking for where the product Type is failing to make it to the view:
- The open_item_model() is being passed a null value for product_type.
```
$(document).on('click', '.btnEditItem', function () {
    var item_id = $(this).attr('data-item-id');
    var product_type = $(this).attr('data-product_type');
    //product_id = ( $(this).attr('data-product_id') === 'undefined' ? product_id : $(this).attr('data-product_id') );
    open_item_modal(item_id, false, product_type);
});
```
IN item::edit_item()
 - For NEW Product Product Type is not getting set to 'R' (when regular)
    ```
    POST: Item.php::edit_item(): 212
    Array
    (
        [product_type] => BLANK <--- why? bc its not passed to Common.js open_item_modal(item_id, false, product_type);
        [product_id] => 7257
        [item_id] => 0
    )
    ```
IN Product:submit_form() POST does bring data:
```
 POST: Product.php::submit_form(): 765
Array
(
    [product_messages_encoded] => 
    [product_id] => 0
    [product_type] => R
    [product_name] => PKL LEGACY TEST 2025-02-26-C
    ...
)
```
Examining Product:submit_form() ...



# VIEW: application/views/product/form/form_regular.php



## Missing Sections in NEW ITEM Form


### Section : `Vendor Information`
```

VIEW: application/views/item/form/view.php
@ 399
```
#VIEW: application/views/product/form/form_regular.php
#@ 453
#Subject to IF @ 447:  <?php if(!$is_showroom){ ?>




### Section : `Showcase / Website Information`
```
application/views/item/form/view.php
@ 508
```
#VIEW: application/views/product/form/form_regular.php
#@ 677
#Subject to IF @ 447:  <?php if(!$is_showroom){ ?>




```
<?php
echo "<pre> INFO: ".basename(__FILE__). "::" . __FUNCTION__ . "(): ". __LINE__. "<br />";
echo "isMultiEdit: (" . $isMultiEdit . ")<br />";
echo "PROD TYPE(_product_type): " . $product_type . "<br />";
echo "PROD TYPE(_info[product_type]): " . $info['product_type'] . "<br />";
print_r($info);
echo "</pre>";
die();
?>

<?php
echo "<pre> INFO: ".basename(__FILE__). "::" . __FUNCTION__ . "(): ". __LINE__. "<br />";
echo "is_showroom: (" . $is_showroom . ")<br />";
echo "PROD TYPE: " . $product_type . "<br />";
//print_r($info);
echo "</pre>";
die();
?>


```

