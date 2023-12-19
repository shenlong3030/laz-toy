<?php
// To understand product structure, see this link below
// https://open.lazada.com/doc/api.htm?spm=a2o9m.11193487.0.0.3ac413fers8uIL#/api?cid=5&path=/product/create

require_once('src/helper.php');

function prepareProduct($product) {
    // fix key
    $product['Attributes'] = $product['attributes'];
    $product['Skus'] = $product['skus'];
    $product['PrimaryCategory'] = $product['primary_category'];
    $product['Images'] = $product['images'];
    //$product['ItemId'] = $product['item_id'];

    // remove wrong keyName
    unset($product['attributes']);
    unset($product['skus']);
    unset($product['primary_category']);
    unset($product['images']);

    ////////////////////////////////////////////////////
    // HARDCORE (remove if possible)
    ////////////////////////////////////////////////////
    // fix bug API khong the update
    unset($product['variation']);
    // $product['variation']['Variation1']['hasImage'] = true;
    // $product['variation']['variation1'] = $product['variation']['Variation1'];
    // $product['variation']['variation2'] = $product['variation']['Variation2'];
    unset($product['variation']['Variation1']);
    unset($product['variation']['Variation2']);

    // fix bug API khong the copyInfo
    unset($product['Attributes']['warranty_type']);
    unset($product['Attributes']['Hazmat']);

    $product = fixProductSaleProp($product);

    ////////////////////////////////////////////////////
    return $product;
}

function fixProductSaleProp($product) {
    foreach ($product['Skus'] as $i => $skuDict) {
        if(!empty($skuDict['compatibility_by_model'])) {
            $product['Skus'][$i]['saleProp']['compatibility_by_model'] = $skuDict['compatibility_by_model'];
        }
        if(!empty($skuDict['color_family'])) {
            $product['Skus'][$i]['saleProp']['color_family'] = $skuDict['color_family'];
        }
        if(!empty($skuDict['type_screen_guard'])) {
            $product['Skus'][$i]['saleProp']['type_screen_guard'] = $skuDict['type_screen_guard'];
        }
        if(!empty($skuDict['Variation'])) {
            $product['Skus'][$i]['saleProp']['Variation'] = $skuDict['Variation'];
        }

        // remove old key
        unset($product['Skus'][$i]['compatibility_by_model']);
        unset($product['Skus'][$i]['color_family']);
        unset($product['Skus'][$i]['type_screen_guard']);
        unset($product['Skus'][$i]['Variation']);
    }
    return $product;
}

function prepareProductForUpdating($product) {
    $product = prepareProduct($product);

    // remove english name, desc, short_desc
    if(isset($product['Attributes']["name_en"])) {
        $product['Attributes']["name_en"] = $product['Attributes']["name"];
    }
    if(isset($product['Attributes']['short_description_en'])) {
        $product['Attributes']['short_description_en'] = $product['Attributes']['short_description'];
    }
    if(isset($product['Attributes']['description_en'])) {
        $product['Attributes']['description_en'] = $product['Attributes']['description'];
    }

    return $product;
}

function prepareProductForCreating($product, $keepAllSkus = FALSE) {
    $product = prepareProduct($product);

    // keep skus[0], remove all others
    if(!$keepAllSkus) {
        $product['Skus'] = array_splice($product['Skus'], 0, 1);

        // force active product
        $product['Skus'][0]['Status'] = "active";
        $product['Skus'][0]['SellerSku'] = "";

        unset($product['Skus'][0]['ShopSku']);
        unset($product['Skus'][0]['SkuId']);
    }

    // remove name_en
    unset($product['Attributes']["name_en"]);
    unset($product['Attributes']['short_description_en']);
    unset($product['Attributes']['description_en']);
    
    return $product;
}

function setProductCategory($product, $category) {
    $product['PrimaryCategory'] = $category;

    //$product = setProductColor($product, "...");
    //$product = setProductModel($product, "...");

    return $product;
}

// SKU is reference variable,
// SKU will be updated after run this function
function setProductSku($product, &$sku) {
    $product['Skus'][0]['SellerSku'] = $sku;
    return $product;
}

function setProductAssociatedSku($product, $sku) {
    if(!empty($sku)) {
       $product['AssociatedSku'] = $sku;
    }
    return $product;
}

// migrage images before setting
// $fromindex : just setImages after this index
function setProductImages($product, $images, $reset=FALSE, $fromindex = 0) {
    if($reset) {
         $product['Images'] = array();
    }
    foreach($images as $index => $url) {
        if (is_url($url)) {
            $product['Images'][$index + $fromindex] = $url;
        } else {
            if(!empty($url)) {
                myecho("INVALID URL : " + $images[$index], __FUNCTION__);
            }
        }
    }
    return $product;
}

function getProductImages($product) {
    return val($product['images'], $product['Images']);
}

// migrage images before setting
// $fromindex : just setImages after this index
function setProductSKUImages($product, $images, $reset=FALSE, $fromindex = 0) {
    if($reset) {
         $product['Skus'][0]['Images'] = array();
    }
    foreach($images as $index => $url) {
        if (is_url($url)) {
            $product['Skus'][0]['Images'][$index + $fromindex] = $url;
        } else {
            if(!empty($url)) {
                myecho("INVALID URL : " + $images[$index], __FUNCTION__);
            }
        }
    }
    return $product;
}

function setProductName($product, $name) {
    // set new product name
    if(!empty($name)) {
        $product['Attributes']["name"] = $name;

        if(isset($product['Attributes']["name_en"])) {
            $product['Attributes']["name_en"] = $name;
        }
    }
    return $product;
}

function setProductBrand($product, $val) {
    if(!empty($val)) {
        $product['Attributes']["brand"] = $val;
    }
    return $product;
}

// input price and sale price
function setProductPrice($product, $price, $sale_price) {
    // set price
    if(is_numeric($sale_price)) {
        if($price) {
            $product['Skus'][0]['price'] = $price;
            $product['Skus'][0]['special_price'] = $sale_price;
        } else {
            $product['Skus'][0]['price'] = round($sale_price * 1.3 / 100) * 100;
            $product['Skus'][0]['special_price'] = $sale_price;
        }
        
        $product['Skus'][0]['special_from_date'] = "2023-01-01";
        $product['Skus'][0]['special_to_date'] = "2030-12-12";
    }
    return $product;
}

function setProductQuantity($product, $val) {
    $product['Skus'][0]['quantity'] = $val;
    return $product;
}

/*
$key = variation1, variation2
$hasImage = true / false , only variation1 can $hasImage=true
*/
function setProductVariation($product, $key, $name, $hasImage=false, $reset=false) {
    if($reset) {
        $product['variation'] = array();
    }
    $product['variation'][$key]['name'] = $name;
    $product['variation'][$key]['hasImage'] = $hasImage;
    $product['variation'][$key]['customize'] = true;
    $product['variation'][$key]['options'] = array('...');
    return $product;
}

/* sale prop:
$product['Skus'][0]['saleProp']['compatibility_by_model']
$product['Skus'][0]['saleProp']['Variation']
$product['Skus'][0]['saleProp']['type_screen_guard']
$product['Skus'][0]['saleProp']['color_family']
*/
function setProductSaleProp($product, $key, $value, $reset=false) {
    if($reset) {
        $product['Skus'][0]['saleProp'] = array();
    }
    $product['Skus'][0]['saleProp'][$key] = $value;
    return $product;
}

function setProductColor($product, $color) {
    $product['Skus'][0]['saleProp']['color_family'] = $color;
    return $product;
}

function setProductModel($product, $model) {
    $product['Skus'][0]['saleProp']['compatibility_by_model'] = $model;
    return $product;
}

function setProductAttributes($product, $attrList, $values) {
    foreach ($attrList as $i => $attr) {
        $product['Skus'][0]['saleProp'][$attr] = $values[$i];
    }
    return $product;
}

function setProductAttribute($product, $attr, $value) {
    $product['Skus'][0]['saleProp'][$attr] = $value;
    return $product;
}

function setProductShortDescription($product, $value) {
    $product['Attributes']['short_description'] = $value;

    if(isset($product['Attributes']['short_description_en'])) {
        $product['Attributes']['short_description_en'] = $value;
    }
    return $product;
}

function setProductDescription($product, $value) {
    $product['Attributes']['description'] = $value;

    if(isset($product['Attributes']['description_en'])) {
        $product['Attributes']['description_en'] = $value;
    }
    return $product;
}

function setProductPackageWeight($product, $value) {
    $product['Skus'][0]['package_weight'] = $value;
    return $product;
}

function setProductPackageSize($product, $h, $w, $l) {
    $product['Skus'][0]['package_height'] = $h;
    $product['Skus'][0]['package_width'] = $w;
    $product['Skus'][0]['package_length'] = $l;
    return $product;
}

function setProductPackageContent($product, $value) {
    $product['Skus'][0]['package_content'] = $value;
    return $product;
}

function setProductVideo($product, $value) {
    $product['Attributes']['video'] = $value;
    return $product;
}

function setProductActive($product, $value) {
    if(intval($value)) {
        $product['Skus'][0]['Status'] = "active";
    } else {
        $product['Skus'][0]['Status'] = "inactive";
    }
    return $product;
}

//######################################
//GET region
//######################################

function getTemplateProduct($sku, $skuid, $status=null) {
    $product = [];
    $product['Attributes'] = [];
    $product['Skus'] = [];
    if(!empty($sku)) {
        $product['Skus'][0]['SellerSku'] = $sku;
    }
    $product['Skus'][0]['SkuId'] = $skuid;
    $product['PrimaryCategory'] = [];

    if(!empty($status)) {
        $product['Skus'][0]['Status'] = $status;
    }

    return $product;
}

function getProductSku($product) {
    $val = val($product['Skus'][0]['SellerSku'], $product['skus'][0]['SellerSku']);
    return $val;
}

function getProductSkuid($product) {
    $val = val($product['Skus'][0]['SkuId'], $product['skus'][0]['SkuId']);
    return $val;
}

function getProductName($product) {
    $val = val($product["Attributes"]["name"], $product["attributes"]["name"]);
    return $val;
}

function getProductSaleProp($product, $key) {
    return $product['Skus'][0]['saleProp'][$key];
}

function getProductSaleProps($product) {
    return $product['Skus'][0]['saleProp'];
}

function getProductItemId($product) {
    return $product['item_id'];
}

function getProductSkuIndex($product, $inputSku) {
    $pos = 0; // default is 0
    foreach($product['skus'] as $skuIndex=>$sku) {
        if ($inputSku == $sku['SellerSku']) {
            $pos = $skuIndex;
        }
    }
    return $pos;
}

/*
    return product with only 1 selected SKU
*/
function getProductWithSingleSku($product, $inputSku) {
    $i = getProductSkuIndex($product, $inputSku);
    $newProduct = $product;
    $newProduct['skus'] = array_slice($product['skus'], $i, 1);
    return $newProduct;
}

/*
    define all product attributes except model, color
*/
function getProductAttributeNames($withoutModelColor=TRUE) {
    $list = array(
        "compatibility_by_model",
        "color_family",
        "Variation",
        "type_screen_guard",
        "smartwear_size"
    );
    if($withoutModelColor) {
        $list = array_slice($list, 2);
    }
    return $list;
}

/*
    return string all attributes except color, model
*/
function getProductAttributes($product, $skuIndex, $withoutModelColor=TRUE) {
    $attributes = getProductAttributeNames($withoutModelColor);
    $values = array();

    foreach ($attributes as $i => $attr) {
        $values[] = $product['skus'][$skuIndex][$attr];
    }
    $values = array_filter($values);
    return implode(",", $values);
}


//######################################
//CHECK region
//######################################

function isProductActive($product, $inputSku=""){
    $i = 0;
    if(!empty($inputSku)) {
        $i = getProductSkuIndex($product, $inputSku);
    }

    return $product['Skus'][$i]['Status'] == "active" || $product['skus'][$i]['Status'] == "active";
}

//######################################
//FIX region
//######################################

function fixProductRemoveSlashFromModel($product) {
    foreach($product['Skus'] as $skuIndex=>$sku) {
        $product['Skus'][$skuIndex]['saleProp']['compatibility_by_model'] = str_replace("/", ",", $product['Skus'][$skuIndex]['compatibility_by_model']);         
    }   
    return $product;
}

function fixProductSetDefaultBrand($product) {
    $product['Attributes']["brand"] = "No Brand";
    return $product;
}

function fixProductSetDefaultColorAndModel($product) {
    foreach($product['Skus'] as $skuIndex=>$sku) {
        $product['Skus'][$skuIndex]['saleProp']['compatibility_by_model'] = "..." . $skuIndex;
        $product['Skus'][$skuIndex]['saleProp']['color_family'] = "...";    
        $product['Skus'][$skuIndex]['Status'] = "inactive";    
    }
    return $product;
}

function fixProductSetRandomModel($product) {
    foreach($product['Skus'] as $skuIndex=>$sku) {
        $product['Skus'][$skuIndex]['saleProp']['compatibility_by_model'] = time() . "." . $skuIndex;
        $product['Skus'][$skuIndex]['Status'] = "inactive";    
    }
    return $product;
}

function fixProductModel($product) {
    foreach($product['Skus'] as $skuIndex=>$sku) {
        $product['Skus'][$skuIndex]['saleProp']['compatibility_by_model'] = '...';   
    }
    return $product;
}

function fixProductRemoveVideoLink($product){
    return setProductVideo($product, "");
}

function fixProductSaleDate($product){
    foreach($product['Skus'] as $skuIndex=>$sku) {
        $product['Skus'][$skuIndex]['special_from_date'] = "2019-01-01";        
    }   
    return $product;
}

//######################################
//OTHERS region
//######################################
function generateProductSku($product){
    return join(".", $product['Skus'][0]['saleProp']);
}


function unsetQuantity($product){
    foreach($product['Skus'] as $skuIndex=>$sku) {
        unset($product['Skus'][$skuIndex]['quantity']);
    }   
    return $product;
}

/*
    return list products, 1 product , 1 sku
*/
function productsWithSingleSku($products) {
    $r = array();
    foreach ($products as $product) {
        $newProduct = $product;

        // raw data, is [skus] not [Skus]
        $count = count($product['skus']);

        for ($i = 0; $i < $count; $i++) {
            $newProduct['skus'] = array_slice($product['skus'], $i, 1);
            $r[] = $newProduct; // add newProduct to array
        }
    }
    return $r;
}

function testFix($product) {
    $product['variation']['Variation1']['name'] = 'compatibility_by_model';
    $product['variation']['Variation1']['label'] = 'Compatibility by Model';
    $product['variation']['variation1'] = $product['variation']['Variation1'];

    $product['variation']['Variation2']['name'] = 'color_family';
    $product['variation']['Variation2']['label'] = 'Color Family';
    $product['variation']['variation2'] = $product['variation']['Variation2'];

    return $product;
}

?>