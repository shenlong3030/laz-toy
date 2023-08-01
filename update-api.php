<?php
include_once "check_token.php";
//include_once "src/show_errors.php";
require_once('_main_functions.php');

$sku = isset($_REQUEST['sku']) ? $_REQUEST['sku'] : 0;
$skustatus = isset($_REQUEST['skustatus']) ? $_REQUEST['skustatus'] : 'inactive';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$qty = isset($_REQUEST['qty']) ? $_REQUEST['qty'] : 0;
$sprice = isset($_REQUEST['sprice']) ? $_REQUEST['sprice'] : 0;

$skus = isset($_REQUEST['skus']) ? $_REQUEST['skus'] : 0;
$skus = explode(",", $skus);

$names = isset($_REQUEST['names']) ? $_REQUEST['names'] : 0;
$names = explode("$", $names);

$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : "";
$desc = isset($_REQUEST['desc']) ? $_REQUEST['desc'] : "";
$variation = isset($_REQUEST['variation']) ? $_REQUEST['variation'] : "";
$type_screen_guard = isset($_REQUEST['type_screen_guard']) ? $_REQUEST['type_screen_guard'] : "";
$compatibility_by_model = isset($_REQUEST['compatibility_by_model']) ? $_REQUEST['compatibility_by_model'] : "";
$color_family = isset($_REQUEST['color_family']) ? $_REQUEST['color_family'] : "";

$category = val($_REQUEST['category']);

$input = val($_REQUEST['images']);
$images = array_filter(explode("\n", str_replace("\r", "", $input))); // split by newline
$temp = array();
foreach($images as $i) {
    $temp = array_merge($temp, preg_split("/\s+/", $i)); // split by space
}
$images = array_filter($temp);

$input = val($_REQUEST['pimages']);
$pimages = array_filter(explode("\n", str_replace("\r", "", $input))); // split by newline
$temp = array();
foreach($pimages as $image) {
    $temp = array_merge($temp, preg_split("/\s+/", $image)); // split by space
}
$pimages = array_filter($temp);

$weight = val($_REQUEST['weight']);
$size_w = val($_REQUEST['size_w']);
$size_h = val($_REQUEST['size_h']);
$size_l = val($_REQUEST['size_l']);
$content = val($_REQUEST['content']);

$info = val($_REQUEST['info']);
$info = json_decode($info, true);


function noError($response) {
    return $response["code"] == "0" && empty($response["detail"]);
}

function successMessage($action, $sku) {
    return "SUCCESS; " . $action . "; " . $sku; 
}

function failureMessage($action, $sku) {
    return "FAIL; " . $action . "; " . $sku; 
}

function messageFromResponse($response, $action, $sku) {
    if(noError($response)) { 
        return successMessage($action, $sku);
    } else {
        $fmsg = failureMessage($action, $sku);
        if(isset($response["detail"])) {
            $fmsg .= "; " . json_encode($response["detail"]);
        }
        return $fmsg;
    }
}

if($accessToken) {
    $response = [];

    switch ($action) {
        case 'massQty':
            $response = massUpdateQuantityWithAPI($accessToken, $skus, $qty);
            //force active product
            $response['message'][] = messageFromResponse($response, $action, $sku);

            if($qty > 0) {
                foreach($skus as $i => $s) {
                    $product = getTemplateProduct($s, "active");
                    $r = saveProduct($accessToken, $product);
                    $response['message'][] = messageFromResponse($r, "re-active", $s);
                }
            }
            break;

        case 'massPrice':
            $response = massUpdatePriceWithAPI($accessToken, $skus, $sprice);
            break;

        case 'massName':
            foreach($skus as $i=>$s) {
                if(empty($s)) {
                    continue;
                }
                $product = getTemplateProduct($s);
                $product = setProductName($product, $names[$i]); 
                $r = saveProduct($accessToken, $product);

                $response['message'][] = messageFromResponse($r, $action, $s);

                // if error , check error message
                // var_dump($r["detail"]["message"]);
            }
            break;

        case 'massFixOL':
            // Fix ốp lưng: variation1=color, variation2=model
            foreach($skus as $s) {
                $product = getTemplateProduct($s);
            
                // đổi sang categorey cáp sạc để bỏ hết variation, chỉ giữ lại variation1=color
                $product = setProductCategory($product, "11029"); 
                $response = saveProduct($accessToken, $product);

                if($response["code"]=="0") {
                    // đổi lại category ốp lưng
                    $product = setProductCategory($product, "4523"); 
                    $product = setProductModel($product, "..."); 
                    $product = setProductColor($product, "..."); 
                    $response = saveProduct($accessToken, $product);
                } else {
                    // keep and return error response
                }

                $response['message'][] = messageFromResponse($r, $action, $s);
            }
            break;

        case 'qty':
            $response = updateQuantityWithAPI($accessToken, $sku, $qty);
            
            if(noError($response)) {
                // force active product
                if($qty > 0) {
                    $product = getTemplateProduct($sku, "active");
                    $r = saveProduct($accessToken, $product);
                    if(!noError($r)) {
                        $response["code"] = $r["code"]; // save error code
                        $response['message'] = failureMessage("re-active", $sku);
                    }
                } 
            }
            break;

        case 'status':
            $product = getTemplateProduct($sku, $skustatus);
            $response = saveProduct($accessToken, $product);
            break;

        case 'category':
            $product = getTemplateProduct($sku);
            $product = setProductCategory($product, $category);
            // if((string)$category != "4528") {
            //     $product = setProductColor($product, "ccc");
            // }
            $response = saveProduct($accessToken, $product);
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

        case 'weight':
            $product = getTemplateProduct($sku);
            $product = setProductPackageWeight($product, $weight);
            $product = setProductPackageSize($product, $size_h, $size_w, $size_l);
            $product = setProductPackageContent($product, $content);
            $response = saveProduct($accessToken, $product);
            break;

        case 'info': // update multi fields
            $product = getTemplateProduct($sku);
            //myvar_dump($info);
            foreach($info as $key => $val) {
                if($key == "images") {
                    $images = $info['images'];
                    $images = migrateImages($accessToken, $val, $cache);
                    $product = setProductSKUImages($product, $images, TRUE);  
                }
                if($key == "price") {
                    $product = setProductPrice($product, 0, $info['sale_price']);  
                }
                if($key == "description") {
                    $product = setProductShortDescription($product, $info['desc']);
                    $product = setProductDescription($product, $info['desc']);
                }
                if($key == "weight") {
                    $product = setProductPackageWeight($product, $info['weight']);
                    $product = setProductPackageSize($product, $info['size_h'], $info['size_w'], $info['size_l']);
                    $product = setProductPackageContent($product, $info['content']);
                }
            }
            $response = saveProduct($accessToken, $product);
            break;

        default:
            $response["code"]="XXX";
            $response["message"]="NO ACTION";
            break;
    }

    if(!isset($response['message'])) {
        $response['message'] = messageFromResponse($response, $action, $sku);
    }

    echo json_encode($response);
}

?>