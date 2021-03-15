<?php
include_once "check_token.php";
require_once('_main_functions.php');

//include_once "src/show_errors.php";

//var_dump($_REQUEST);

$sku = $_REQUEST['sku'] ? $_REQUEST['sku'] : 0;
$qty = isset($_REQUEST['qty']) ? $_REQUEST['qty'] : '';
$qtyaction = $_REQUEST['qtyaction'];
$price = $_REQUEST['price'] ? $_REQUEST['price'] : 0;
$sale_price = $_REQUEST['sale_price'] ? $_REQUEST['sale_price'] : 0;
$fromdate = $_REQUEST['fromdate'] ? $_REQUEST['fromdate'] : 0;
$todate = $_REQUEST['todate'] ? $_REQUEST['todate'] : 0;
$name = $_REQUEST['name'] ? $_REQUEST['name'] : '';

$compatibility_by_model = $_REQUEST['compatibility_by_model'] ? $_REQUEST['compatibility_by_model'] : '';
$variation = $_REQUEST['variation'] ? $_REQUEST['variation'] : '';
$type_screen_guard = $_REQUEST['type_screen_guard'] ? $_REQUEST['type_screen_guard'] : '';

$category = $_REQUEST['category'] ? $_REQUEST['category'] : 0;
$input = val($_REQUEST['images']);
$images = array_filter(explode("\n", str_replace("\r", "", $input)));

$temp = array();
foreach($images as $image) {
    $temp = array_merge($temp, preg_split("/\s+/", $image));
}
$images = array_filter($temp);


$shortdesc = $_REQUEST['shortdesc'] ? $_REQUEST['shortdesc'] : '';
$desc = $_REQUEST['desc'] ? $_REQUEST['desc'] : '';
$brand = $_REQUEST['brand'] ? $_REQUEST['brand'] : '';

$weight = $_REQUEST['weight'] ? $_REQUEST['weight'] : '';
$size_h = $_REQUEST['size_h'] ? $_REQUEST['size_h'] : '';
$size_w = $_REQUEST['size_w'] ? $_REQUEST['size_w'] : '';
$size_l = $_REQUEST['size_l'] ? $_REQUEST['size_l'] : '';
$content = $_REQUEST['content'] ? $_REQUEST['content'] : '';

$video = $_REQUEST['video'] ? $_REQUEST['video'] : '';

$id = $_REQUEST['item_id'] ? $_REQUEST['item_id'] : '';

$input = $_REQUEST['colors'] ? $_REQUEST['colors'] : '';
$colors = empty($input) ? array() : explode("\n", str_replace("\r", "", $input));

$input = $_REQUEST['color_thumbnails'] ? $_REQUEST['color_thumbnails'] : '';
$color_thumbnails = empty($input) ? array() : explode("\n", str_replace("\r", "", $input));

$newSku = $_REQUEST['new_sku'] ? $_REQUEST['new_sku'] : '';

if($accessToken && ($sku || $id)) {
    $response = 0;
    /*    
    if($category) {
        $response = setPrimaryCategory($accessToken, $sku, $category);
    } elseif ($images) {
        $response = setImages($accessToken, $sku, $images);
    } elseif (is_numeric($qty)){
        $response = setQuantity($accessToken, $sku, $qty);
    } elseif ($price && $sale_price) {
        $response = setPrices($accessToken, $sku, $price, $sale_price);
    } elseif($name) {
        $response = setName($accessToken, $sku, $name);
    } elseif($price) {
        $response = setPrices($accessToken, $price, $sale_price);
        var_dump($response);
    }
    */

    $product = null;
    if($id) {
        $product = getProduct($accessToken, null, $id);
    } else {
        $product = getProduct($accessToken, $sku);
    }

    $response = null;
    if($product) {
        $product = prepareProductForUpdating($product);

        if ($images) {
            // migrate images
            $images = migrateImages($accessToken, $images, $cache);
            $product = setProductImages($product, $images, TRUE);   
        } elseif (!empty($qtyaction)){
            if($qtyaction == "+500") {
                $qty = 500;
            } elseif ($qtyaction == "=0") {
                $qty = 0;
            }
            //echo "i2";
            $response = updateQuantityWithAPI($accessToken, $sku, $qty);
        } elseif ($price || $sale_price) {
            //echo "i3";
            $response = updatePricesWithAPI($accessToken, $sku, $price, $sale_price);
        } elseif($category) {
            //echo "i4";
            $product = setProductBrand($product, "No Brand");
            $product = setProductCategory($product, $category);
        } elseif($name) {
            //echo "i5";
            $product = setProductName($product, $name);
        } elseif($_REQUEST['change-attr']) {
            //echo "i7";
            $product = setProductVariation($product, $variation);
            $product = setProductModel($product, $compatibility_by_model);
            $product = setProductColor($product, $color_family);
            $product = setProductTypeScreenGuard ($product, $type_screen_guard);
        } elseif($shortdesc) {
            //echo "i8";
            $product = setProductShortDescription($product, $shortdesc);
        } elseif($desc) {
            //echo "i9";
            $product = setProductDescription($product, $desc);
        } elseif($brand) {
            //echo "i9";
            $product = setProductBrand($product, $brand);
        } elseif($weight) {
            $product = setProductPackageWeight($product, $weight);
            $product = setProductPackageSize($product, $size_h, $size_w, $size_l);
            $product = setProductPackageContent($product, $content);
        } elseif($video) {
            $product = setProductVideo($product, $video);
        } elseif($color_thumbnails) {
            $product = setProductColorThumbnail($product, $colors, $color_thumbnails);
        } elseif($newSku) {
            $product = setProductSku($product, $newSku);
        }

        if(!$response) {
            $response = saveProduct($accessToken, $product);
        }
        
    } else {
        echo "INVALID SKU<br>";
    }
    
    if($response) {
        $date = new DateTime();
        $date->modify('+ 7 hour');
        $dateStr = date_format($date, 'Y-m-d H:i:s');
        $resCode = $response["code"];
        
        if($resCode == "0") {
            echo '<p style="background-color:lightgreen">'.$dateStr.' SUCCESS</p>';
        } else {
            echo '<p style="background-color:red">'.$dateStr.' ERROR: '.$response["message"].'</p>';
            myvar_dump($response);
        }
    }
}


/*
if($accessToken) {
    // get code param
    $sku = $_REQUEST['sku'] ? $_REQUEST['sku'] : '';
    $qty = $_REQUEST['qty'] ? $_REQUEST['qty'] : '0';
    $price = $_REQUEST['price'] ? $_REQUEST['price'] : '0';
    $sale_price = $_REQUEST['sale_price'] ? $_REQUEST['sale_price'] : '0';
    $name = $_REQUEST['name'] ? $_REQUEST['name'] : '';

    $namePayload = '';
    if($name) {
        $namePayload = '<Attributes><name>'.$name.'</name></Attributes>';
    }
    
    $pricePayload = '';
    if($price && $sale_price) {
        $pricePayload = '<Price>'.$price.'</Price><SalePrice>'.$sale_price.'</SalePrice><SaleStartDate>2018-01-01</SaleStartDate><SaleEndDate>2028-01-01</SaleEndDate>';
    }
    
    $qtyPayload = '<Quantity>'.$qty.'</Quantity>';

    $payload = '<?xml version="1.0" encoding="UTF-8"?><Request><Product>'.$namePayload.'<Skus><Sku><SellerSku>'.$sku.'</SellerSku>'.$qtyPayload.$pricePayload.'</Sku></Skus></Product></Request>';
    
    // log payload
    //echo htmlentities($payload, ENT_COMPAT, 'UTF-8');

    $c = new LazopClient($GLOBALS['apiUrl'],$GLOBALS['appKey'],$GLOBALS['appSecret']);
    
    $api = '/product/price_quantity/update';
    if($name) {
        $api = '/product/update';
    }
    
    $request = new LazopRequest($api);
    $request->addApiParam('payload', $payload);
    $response = $c->execute($request, $accessToken);
    //var_dump($response);
    $response = json_decode($response, true);

    $date = new DateTime();
    $date->modify('+ 7 hour');
    $dateStr = date_format($date, 'Y-m-d H:i:s');
    
    $resCode = $response["code"];
    if($resCode == "0") {
        echo '<p style="background-color:lightgreen">'.$dateStr.' SUCCESS</p>';
    } else {
        echo '<p style="background-color:red">'.$dateStr.' ERROR: '.$response["message"].'</p>';
    }
}

*/
?>