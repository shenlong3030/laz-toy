<?php
include_once "check_token.php";
//include_once "src/show_errors.php";
require_once('_main_functions.php');

$sku = val($_REQUEST['sku']);
$skuid = val($_REQUEST['skuid']);
$skustatus = val($_REQUEST['skustatus'], 'inactive');
$action = val($_REQUEST['action']);
$qty = val($_REQUEST['qty'], 0);
$price = val($_REQUEST['price'], 0);
$sprice = val($_REQUEST['sprice'], 0);


// MASS UPDATE ###################
$skus = val($_REQUEST['skus']);
$skus = explode("\n", $skus);
$skuids = val($_REQUEST['skuids']);
$skuids = explode("\n", $skuids);
// MASS UPDATE ###################

$names = val($_REQUEST['mass_names']);
$names = explode("\n", $names);
// $models = val($_REQUEST['models']);
// $models = explode("\n", $models);
// $colors = val($_REQUEST['colors']);
// $colors = explode("\n", $colors);
$salPropKey1 = val($_REQUEST['salPropKey1']);
$salPropKey2 = val($_REQUEST['salPropKey2']);

$saleprop1s = val($_REQUEST['mass_saleprop1s']);
$saleprop1s = explode("\n", $saleprop1s);
$saleprop1s = array_filter($saleprop1s);

$saleprop2s = val($_REQUEST['mass_saleprop2s']);
$saleprop2s = explode("\n", $saleprop2s);
$saleprop2s = array_filter($saleprop2s);

$prices = val($_REQUEST['mass_prices']);
$prices = explode("\n", $prices);
$massSkuImages = val($_REQUEST['mass_sku_images']);
$massSkuImages = explode("\n", $massSkuImages);
$massSkuMainImages = val($_REQUEST['mass_sku_main_images']);
$massSkuMainImages = explode("\n", $massSkuMainImages);
$massProductImages = val($_REQUEST['mass_product_images']);
$massProductImages = explode("\n", $massProductImages);

$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : "";
$desc = isset($_REQUEST['desc']) ? $_REQUEST['desc'] : "";

$variations = val($_REQUEST['variations'], "");
$variations = array_filter(explode("\n", str_replace("\r", "", $variations))); // split by newline

$variationValues = val($_REQUEST['variationValues'], "");
$variationValues = array_filter(explode("\n", str_replace("\r", "", $variationValues))); // split by newline

$category = val($_REQUEST['category']);
$brand = val($_REQUEST['brand']);

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

$itemId = val($_REQUEST['item_id']);

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
            $fmsg .= "; " . json_encode($response["detail"], JSON_UNESCAPED_UNICODE);
        }
        return $fmsg;
    }
}

function messagesFromMigrateImageResponses($responses) {
    $messages = [];
    foreach ($responses as $dict) {
        $msg = messageFromResponse($dict['response'], "migrate-image", $dict['url']);
        if(substr($msg,0,4) == 'FAIL') {    // only get FAIL message;
            $messages[] = $msg;
        }
    }
}


if($accessToken) {
    $response = [];
    switch ($action) {
        case 'massQty':
            $response = massUpdateQuantityWithAPI($accessToken, $skuids, $qty);
            //myvar_dump($response);
            //force active product
            $response['mymessage'] = [];
            $response['mymessage'][] = messageFromResponse($response, $action, null);

            if($qty > 0) {
                foreach($skuids as $i => $skuid) {
                    $product = getTemplateProduct(null, $skuid, "active");

                    $retry=3;
                    do{
                        $r = saveProduct($accessToken, $product);
                    } while($r["code"] != "0" && $retry-- > 1);

                    $response['mymessage'][] = messageFromResponse($r, "re-active", $skus[$i]);
                }
            }
            break;

        case 'massPrice':
            $response = massUpdatePriceWithAPI($accessToken, $skuids, $sprice);
            break;

        case 'massUpdate':
            $response['mymessage'] = [];
            $product = null;
            foreach($skus as $i=>$sku) {
                $arr = explode("~", $sku);
                $sku = $arr[0];
                $skuid = $arr[1];
                $itemId = $arr[2];

                if(empty($skuid) || strlen($skuid)<2) {
                    continue;
                }

                if($i==0) {
                    $product = getTemplateProduct(null, $skuid);
                } else {
                    $skuDict = [];
                    $skuDict['SkuId'] = $skuid;
                    array_splice($product['Skus'], 0, 0, [$skuDict]); //insert new skuDict at position 0
                }
                
                if(isset($names[$i])) {
                    $product = setProductName($product, $names[$i]); 
                }

                if(isset($saleprop1s[$i]) && !empty($salPropKey1)) {
                    $product = setProductSaleProp($product, $salPropKey1, $saleprop1s[$i]);
                }
                if(isset($saleprop2s[$i]) && !empty($salPropKey2)) {
                    $product = setProductSaleProp($product, $salPropKey2, $saleprop2s[$i]);
                }
                if(isset($prices[$i])) {
                    $tmp = explode("/", $prices[$i]);
                    $salePrice = $tmp[0];
                    $price = $tmp[1];
                    $product = setProductPrice($product, $price, $salePrice); 
                }

                $originalImages = $massSkuImages[$i];
                migrateImages($accessToken, $originalImages, $cache);
                $product = setProductSKUImages($product, $originalImages); 
                if(isset($massSkuMainImages[$i])) {
                    $images = $massSkuMainImages[$i];
                    migrateImages($accessToken, $images, $cache);
                    $product = setProductSKUImages($product, $images); 
                }
                $response['shen1'][] = $massSkuMainImages;

                if(isset($massProductImages[$i])) {
                    $images = $massProductImages[$i];
                    migrateImages($accessToken, $images, $cache);
                    $product = setProductImages($product, $images); 
                }
            }
            //var_dump($product);

            $retry=3;
            do{
                $r = [];
                $r = saveProduct($accessToken, $product);
                $retry--;
            } while($r["code"] != "0" && $retry > 1);

            $response['mymessage'][] = messageFromResponse($r, $action, $skus[$i]);
            // $response['shen1'] = $salPropKey1;
            // $response['shen2'] = $salPropKey2;

            // if error , check error message
            //var_dump($r);
            break;

        case 'massFixOL':
            // Fix ốp lưng: variation1=color, variation2=model
            $response['mymessage'] = [];
            foreach($skuids as $i=>$skuid) {
                $product = getTemplateProduct(null, $skuid);
            
                // đổi sang categorey cáp sạc để bỏ hết variation, chỉ giữ lại variation1=color
                $product = setProductCategory($product, "11029"); 
                $r = saveProduct($accessToken, $product);

                if($r["code"]=="0") {
                    // đổi lại category ốp lưng
                    $product = setProductCategory($product, "4523"); 
                    $product = setProductModel($product, "..."); 
                    $product = setProductColor($product, "..."); 
                    $r = saveProduct($accessToken, $product);
                } else {
                    // keep and return error response
                }

                $response['mymessage'][] = messageFromResponse($r, $action, $skus[$i]);
            }
            break;

        case 'qty':
            $response = updateQuantityWithAPI($accessToken, $skuid, $qty);
            
            if(noError($response)) {
                // force active product
                if($qty > 0) {
                    $product = getTemplateProduct($sku, $skuid, "active");
                    $r = saveProduct($accessToken, $product);
                    if(!noError($r)) {
                        $response["code"] = $r["code"]; // save error code
                        $response['mymessage'] = failureMessage("re-active", $sku);
                    }
                } 
            }
            break;

        case 'status':
            $product = getTemplateProduct($sku, $skuid, $skustatus);
            $response = saveProduct($accessToken, $product);
            break;

        case 'category':
            $product = getTemplateProduct($sku, $skuid);
            $product = setProductCategory($product, $category);
            // if((string)$category != "4528") {
            //     $product = setProductColor($product, "ccc");
            // }
            $response = saveProduct($accessToken, $product);
            break;

        case 'brand':
            $product = getTemplateProduct($sku, $skuid);
            $product = setProductBrand($product, $brand);
            $response = saveProduct($accessToken, $product);
            break;

        case 'price':
            $response = updatePricesWithAPI($accessToken, $skuid, $price, $sprice);
            break;

        case 'name':
            $product = getTemplateProduct($sku, $skuid);
            $product = setProductName($product, $name);
            $response = saveProduct($accessToken, $product);
            break;

        case 'sku':
            $product = getTemplateProduct($sku, $skuid);
            $response = saveProduct($accessToken, $product);
            break;

        case 'description':
            $product = getTemplateProduct($sku, $skuid);
            $product = setProductShortDescription($product, $desc);
            $product = setProductDescription($product, $desc);
            $response = saveProduct($accessToken, $product);
            break;

        case 'attr':
            $product = getTemplateProduct($sku, $skuid);
            //$product = setProductVariation($product, "variation1", "Variation", true, true);
            //$product = setProductVariation($product, "variation2", "color_family");

            foreach ($variations as $i => $v){
                $product = setProductSaleProp($product, $v, $variationValues[$i]);
            }
            //$product = setProductSaleProp($product, "color_family", "c...");

            $response = saveProduct($accessToken, $product);

            break;

        case 'images':
            $product = getTemplateProduct($sku, $skuid);

            $retry=3;
            do{
                $responses = migrateImages($accessToken, $images, $cache);
                $messages = messagesFromMigrateImageResponses($responses);
                
                if(!empty($messages)) { // migrate FAIL
                    $response['code'] = 111;
                    $response['mymessage'] = $messages;
                    break;
                } else {                // migrate SUCCESS
                    $product = setProductSKUImages($product, $images, TRUE);  
                    $response = saveProduct($accessToken, $product);
                }
            } while($responses["code"] != "0" && $retry-- > 1);
            break;

        case 'pimages':
            $product = getTemplateProduct($sku, $skuid);

            $retry=3;
            do{
                $responses = migrateImages($accessToken, $pimages, $cache);
                $messages = messagesFromMigrateImageResponses($responses);
                
                if(!empty($messages)) { // migrate FAIL
                    $response['code'] = 111;
                    $response['mymessage'] = $messages;
                    break;
                } else {                // migrate SUCCESS
                    $product = setProductImages($product, $pimages, TRUE);  
                    $response = saveProduct($accessToken, $product);
                }
            } while($responses["code"] != "0" && $retry-- > 1);

            break;

        case 'weight':
            $product = getTemplateProduct($sku, $skuid);
            $product = setProductPackageWeight($product, $weight);
            $product = setProductPackageSize($product, $size_h, $size_w, $size_l);
            $product = setProductPackageContent($product, $content);
            $response = saveProduct($accessToken, $product);
            break;

        case 'info': // update multi fields
            $product = getTemplateProduct($sku, $skuid);
            //myvar_dump($info);
            if(isset($info["images"])) {
                $images = $info['images'];
                migrateImages($accessToken, $images, $cache);   // not handle response
                $product = setProductSKUImages($product, $images, TRUE);  
            }
            if(isset($info["sale_price"])) {
                $product = setProductPrice($product, 0, $info['sale_price']);  
            }
            if(isset($info["desc"])) {
                $product = setProductShortDescription($product, $info['shortdesc']);
                $product = setProductDescription($product, $info['desc']);
            }
            if(isset($info["weight"])) {
                $product = setProductPackageWeight($product, $info['weight']);
                $product = setProductPackageSize($product, $info['size_h'], $info['size_w'], $info['size_l']);
                $product = setProductPackageContent($product, $info['content']);
            }

            $response = saveProduct($accessToken, $product);
            break;

        case 'remove_saleprop':
            $response = removeSaleProp($accessToken, $itemId, $variation1);
            break;

        default:
            //myvar_dump("default");
            $response["code"]="XXX";
            $response["mymessage"]="NO ACTION";
            break;
    }

    if(!isset($response['mymessage'])) {
        $response['mymessage'] = messageFromResponse($response, $action, $sku);
    }

    $response['raw_product'] = $product;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}

?>