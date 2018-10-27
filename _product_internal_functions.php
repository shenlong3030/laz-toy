<?php
// To understand product structure, see this link below
// https://open.lazada.com/doc/api.htm?spm=a2o9m.11193487.0.0.3ac413fers8uIL#/api?cid=5&path=/product/create

require_once('src/helper.php');


function prepareProductForUpdating($product) {
    // fix keyName
    $product['Attributes'] = $product['attributes'];
    $product['Skus'] = $product['skus'];
    $product['PrimaryCategory'] = $product['primary_category'];
    
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
    
    // force active product
    $product['Skus'][0]['Status'] = "active";
    
    return $product;
}

function setProductCategory($product, $category) {
    $product['PrimaryCategory'] = $category;
    return $product;
}

function setProductSku($product, $sku) {
    // convert SKU to UPPERCASE, set SKU
    $newSku = strtoupper($sku);

    $product['Skus'][0]['SellerSku'] = $newSku;
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
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $product['Skus'][0]['Images'][$index + $fromindex] = $url;
        } else {
            myecho("INVALID URL : " + $images[$index], __FUNCTION__);
        }
    }
    return $product;
}

function setProductName($product, $name) {
    // set new product name
    if(!empty($name)) {
        $product['Attributes']["name"] = $name;
    }
    return $product;
}

function setProductBrand($product, $val) {
    if(!empty($val)) {
        $product['Attributes']["brand"] = $val;
    }
    return $product;
}

// just input 1 price
function setProductPrice($product, $price) {
        return setProductPrices($product, $price);
}

// input price and sale price
function setProductPrices($product, $price, $sale_price = 0) {
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

function setProductModel($product, $model) {
    $product['Skus'][0]['compatibility_by_model'] = $model;
    return $product;
}

function setProductShortDescription($product, $value) {
    $product['Attributes']['short_description'] = $value;
    return $product;
}

function setProductDescription($product, $value) {
    $product['Attributes']['description'] = $value;
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

?>