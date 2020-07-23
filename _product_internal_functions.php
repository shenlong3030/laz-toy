<?php
// To understand product structure, see this link below
// https://open.lazada.com/doc/api.htm?spm=a2o9m.11193487.0.0.3ac413fers8uIL#/api?cid=5&path=/product/create

require_once('src/helper.php');


function prepareProductForUpdating($product) {
    // fix keyName
    $product['Attributes'] = $product['attributes'];
    $product['Skus'] = $product['skus'];
    $product['PrimaryCategory'] = $product['primary_category'];

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

    $product = unsetQuantity($product);
    
    // remove wrong keyName
    unset($product['attributes']);
    unset($product['skus']);
    unset($product['primary_category']);
    
    return $product;
}

function prepareProductForCreating($product) {
    $product = prepareProductForUpdating($product);
    
    // clear all images before creating
    //$product['Skus'][0]['Images'] = array();
    
    // keep skus[0], remove all others
    $product['Skus'] = array_splice($product['Skus'], 0, 1);

    // force active product
    $product['Skus'][0]['Status'] = "active";
    
    $product['Skus'][0]['SellerSku'] = "";

    // remove name_en
    unset($product['attributes']["name_en"]);
    unset($product['attributes']['short_description_en']);
    unset($product['attributes']['description_en']);

    unset($product['Attributes']["name_en"]);
    unset($product['Attributes']['short_description_en']);
    unset($product['Attributes']['description_en']);
    
    return $product;
}

function setProductCategory($product, $category) {
    $product['PrimaryCategory'] = $category;

    $product = setProductColor($product, "...");
    $product = setProductModel($product, "...");

    return $product;
}

// SKU is reference variable,
// SKU will be updated after run this function
function setProductSku($product, &$sku) {
    // convert SKU to UPPERCASE, set SKU
    $sku = strtoupper($sku);
    $sku = make_short_sku($sku);

    $product['Skus'][0]['SellerSku'] = $sku;
    return $product;
}

function setProductAssociatedSku($product, $sku) {
    $product['AssociatedSku'] = $sku;
    return $product;
}

// migrage images before setting
// $fromindex : just setImages after this index
function setProductImages($product, $images, $reset=FALSE, $fromindex = 0) {
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
function setProductPrice($product, $price, $sale_price = 0) {
    // set price
    if(is_numeric($price) && is_numeric($sale_price)) {
        if($sale_price) {
            $product['Skus'][0]['price'] = $price;
            $product['Skus'][0]['special_price'] = $sale_price;
        } else {
            $product['Skus'][0]['price'] = round($price * 1.3 / 100) * 100;
            $product['Skus'][0]['special_price'] = $price;
        }
        
        $product['Skus'][0]['special_from_date'] = "2018-01-01";
        $product['Skus'][0]['special_to_date'] = "2020-12-12";
    }
    return $product;
}

function setProductQuantity($product, $val) {
    $product['Skus'][0]['quantity'] = $val;
    return $product;
}

function setProductColor($product, $color) {
    $product['Skus'][0]['color_family'] = $color;
    return $product;
}

function setProductColorThumbnail($product, $colors, $thumbnails) {
    unset($product['Attributes']);

    foreach ($colors as $key => $color) {
        $thumbnail = $thumbnails[$key];
        foreach($product['Skus'] as $skuIndex=>$sku) {
            if($product['Skus'][$skuIndex]['color_family'] == $color) {
                $product['Skus'][$skuIndex]['color_thumbnail'] = $thumbnail;
            }    
        }
    }
    return $product;
}

function setProductModel($product, $model) {
    $product['Skus'][0]['compatibility_by_model'] = $model;
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

function getProductSkuIndex($product, $inputSku) {
    $pos = -1;
    foreach($product['skus'] as $skuIndex=>$sku) {
        if ($inputSku == $sku['SellerSku']) {
            $pos = $skuIndex;
        }
    }   
    return $pos;
}

function isProductActive($product){
    return $product['Skus'][0]['Status'] == "active" || $product['skus'][0]['Status'] == "active";
}

function getProductItemId($product) {
    return $product['item_id'];
}

function fixProductRemoveSlashFromModel($product) {
    foreach($product['Skus'] as $skuIndex=>$sku) {
        $product['Skus'][$skuIndex]['compatibility_by_model'] = str_replace("/", ",", $product['Skus'][$skuIndex]['compatibility_by_model']);         
    }   
    return $product;
}

function fixProductSetDefaultBrand($product) {
    $product['Attributes']["brand"] = "No Brand";
    return $product;
}

function fixProductSetDefaultColorAndModel($product) {
    foreach($product['Skus'] as $skuIndex=>$sku) {
        $product['Skus'][$skuIndex]['compatibility_by_model'] = "..." . $skuIndex;
        $product['Skus'][$skuIndex]['color_family'] = "...";    
        $product['Skus'][$skuIndex]['Status'] = "inactive";    
    }
    return $product;
}

function fixProductSetRandomModel($product) {
    foreach($product['Skus'] as $skuIndex=>$sku) {
        $product['Skus'][$skuIndex]['compatibility_by_model'] = time() . "." . $skuIndex;
        $product['Skus'][$skuIndex]['Status'] = "inactive";    
    }
    return $product;
}

function fixProductModel($product) {
    foreach($product['Skus'] as $skuIndex=>$sku) {
        $product['Skus'][$skuIndex]['compatibility_by_model'] = '...';   
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

function unsetQuantity($product){
    foreach($product['Skus'] as $skuIndex=>$sku) {
        unset($product['Skus'][$skuIndex]['quantity']);
    }   
    return $product;
}



?>