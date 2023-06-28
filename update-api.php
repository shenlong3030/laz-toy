<?php
include_once "check_token.php";
require_once('_main_functions.php');

$sku = isset($_REQUEST['sku']) ? $_REQUEST['sku'] : 0;
$skustatus = isset($_REQUEST['skustatus']) ? $_REQUEST['skustatus'] : 'inactive';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$qty = isset($_REQUEST['qty']) ? $_REQUEST['qty'] : 0;
$sprice = isset($_REQUEST['sprice']) ? $_REQUEST['sprice'] : 0;

$skus = isset($_REQUEST['skus']) ? $_REQUEST['skus'] : 0;
$skus = explode(",", $skus);

$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : "";
$desc = isset($_REQUEST['desc']) ? $_REQUEST['desc'] : "";
$variation = isset($_REQUEST['variation']) ? $_REQUEST['variation'] : "";
$type_screen_guard = isset($_REQUEST['type_screen_guard']) ? $_REQUEST['type_screen_guard'] : "";
$compatibility_by_model = isset($_REQUEST['compatibility_by_model']) ? $_REQUEST['compatibility_by_model'] : "";
$color_family = isset($_REQUEST['color_family']) ? $_REQUEST['color_family'] : "";

$input = val($_REQUEST['images']);
$images = array_filter(explode("\n", str_replace("\r", "", $input))); // split by newline
$temp = array();
foreach($images as $image) {
    $temp = array_merge($temp, preg_split("/\s+/", $image)); // split by space
}
$images = array_filter($temp);

$input = val($_REQUEST['pimages']);
$pimages = array_filter(explode("\n", str_replace("\r", "", $input))); // split by newline
$temp = array();
foreach($pimages as $image) {
    $temp = array_merge($temp, preg_split("/\s+/", $image)); // split by space
}
$pimages = array_filter($temp);


if($accessToken) {
    $response = 0;

    switch ($action) {
        case 'status':
            $product = getTemplateProduct($sku, $skustatus);
            $response = saveProduct($accessToken, $product);
            break;

        case 'qty':
            $response = updateQuantityWithAPI($accessToken, $sku, $qty);
            
            // force active product
            if($qty > 0) {
                $product = getTemplateProduct($sku, "active");
                $r = saveProduct($accessToken, $product);
                if($r["code"]=="0") {
                    // do nothing
                } else {
                    $response["code"] = $r["code"]; // save error code
                    $response['message'] = "RE-ACTIVE FAILED";
                    $response['reactive_failed_skus'][] = $sku;
                }
            }
            break;

        case 'massQty':
            $response = massUpdateQuantityWithAPI($accessToken, $skus, $qty);
            //force active product
            if($qty > 0) {
                foreach($skus as $i => $sku) {
                    $product = getTemplateProduct($sku, "active");
                    $r = saveProduct($accessToken, $product);
                    if($r["code"]=="0") {
                        // do nothing
                    } else {
                        $response["code"] = $r["code"]; // save error code
                        $response['message'] = "RE-ACTIVE FAILED";
                        $response['reactive_failed_skus'][] = $sku;
                    }
                }
            }
            break;

        case 'massPrice':
            $response = massUpdatePriceWithAPI($accessToken, $skus, $sprice);
            break;

        case 'price':
            $response = updatePricesWithAPI($accessToken, $sku, null, $sprice);
            break;

        case 'name':
            $product = getTemplateProduct($sku);
            $product = setProductName($product, $name);
            $response = saveProduct($accessToken, $product);
            break;

        case 'description':
            $product = getTemplateProduct($sku);
            $product = setProductShortDescription($product, $desc);
            $product = setProductDescription($product, $desc);
            $response = saveProduct($accessToken, $product);
            break;

        case 'attr':
            $product = getTemplateProduct($sku);
            $product = setProductVariation($product, $variation);
            $product = setProductModel($product, $compatibility_by_model);
            $product = setProductColor($product, $color_family);
            $product = setProductTypeScreenGuard ($product, $type_screen_guard);
            $response = saveProduct($accessToken, $product);
            break;

        case 'images':
            $product = getTemplateProduct($sku);
            $images = migrateImages($accessToken, $images, $cache);
            $product = setProductSKUImages($product, $images, TRUE);  
            $response = saveProduct($accessToken, $product);
            break;

        case 'pimages':
            $product = getTemplateProduct($sku);
            $pimages = migrateImages($accessToken, $pimages, $cache);
            $product = setProductImages($product, $pimages, TRUE);  
            $response = saveProduct($accessToken, $product);
            break;
        
        default:
            echo "NO ACTION";
            break;
    }

    if($response["code"]=="0") {
        $response["message"]="SUCCESS";
    }

    echo json_encode($response);
}

?>