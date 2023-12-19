<?php
//include_once "src/show_errors.php";
include_once "check_token.php";
require_once('_main_functions.php');

$ocsSKU = "OCS_MAU";
$odtSKU = "ODT_MAU";
$olKhacSKU = "OL_XXX";


// PUT : create new product
// DELETE : delete product
$requestMethod = $_REQUEST['REQUEST_METHOD'];

$deleteSku = val($_REQUEST['delete_sku']);
//$parentSku = val($_REQUEST['parent_sku']);

$variations = val($_REQUEST['variations']);
$variations = preg_split("/;/", $variations); // split by ;

$productData = val($_REQUEST['product_data']);
$items = preg_split("/;/", $productData); // split by ;

//"Name;new SKU;Color;Variation;Qty;Price;Pimage;SKUImages;Sku mẫu sku~skuid~itemid;NONE;Parent sku~skuid~itemid"
$productName = val($items[0]);
$inputSku = val($items[1]);
$variation1Value = val($items[2]);
$variation2Value = val($items[3]);

$qty = val($items[4]);
$price = val($items[5]);

$images1 = val($items[6]);
$images2 = val($items[7]);

$sampleSku = val($items[8], "sku mau");
$arr = explode("~", $sampleSku);
$sampleSku = $arr[0];
$sampleSkuid = $arr[1];
$sampleItemid = $arr[2];

$tmp = preg_split("/_/", $sampleSku);
$prefixSku = count($tmp) ? $tmp[0] : "";

$parentSku = val($items[9]);
$arr = explode("~", $parentSku);
$parentSku = $arr[0];
$parentSkuid = $arr[1];
$parentItemid = $arr[2];

$jsonProductDict = json_decode(file_get_contents('jsonProductDict.json'), true);   //keypair sku=>skuid
if(time() - $jsonProductDict['time'] > (60*3)) {   // reset after 1 minute
    $jsonProductDict = [];
}

function noError($response) {
    return $response["code"] == "0" && empty($response["detail"]);
}

function successMessage($requestMethod, $response, $sku, $newName) {
    $msg = "NONE";
    switch ($requestMethod)
    {
        case 'PUT':
            $msg = '<a target=_blank href=https://www.lazada.vn/-i' . $response["data"]["item_id"] . '.html>' . $sku . '</a>' . ';' . $newName . ';<a target="_blank" href=update_gui.php?&sku='.$sku.'~~'.$response["data"]["item_id"].' style="color:red" tabindex="-1">Update</a>';
            break;
        case 'DELETE':
            $msg = 'Deleted;' . $sku;
            break;
        default:
            break;
    }
    return $msg;
}

function failureMessage($requestMethod, $response, $sku, $newName) {
    $msg = "NONE";
    switch ($requestMethod)
    {
        case 'PUT':
            $msg = "Create FAIL; " . $newName; 
            break;
        case 'DELETE':
            $msg = "Delete FAIL; " . $sku; 
            break;
        default:
            break;
    }
    return $msg;
}

function messageFromResponse($requestMethod, $response, $sku, $newName) {
    if(noError($response)) { 
        return successMessage($requestMethod, $response, $sku, $newName);
    } else {
        $fmsg = failureMessage($requestMethod, $response, $sku, $newName);
        if(isset($response["detail"])) {
            $fmsg .= ";" . json_encode($response["detail"], JSON_UNESCAPED_UNICODE);
        }
        return $fmsg;
    }
}

if($accessToken) {
    $response = array();
    $newName;
    if($requestMethod == 'PUT') {
        $product = getProduct($accessToken, $sampleSku, $sampleItemid);   // OL khác

        if(empty($product)) {
            $response["code"] = "xxx";
            $response["mymessage"] = "Get product FAIL: ".$sampleSku;
        } else {
            $product = prepareProductForCreating($product);
            if(!empty($parentSku)) {
                $parent = $jsonProductDict[$parentSku];
                if(empty($parent)) {
                    $parent = getProduct($accessToken, $parentSku, $parentItemid);
                }
                if($parent) {
                    $jsonProductDict[$parentSku] = $parent;
                    $jsonProductDict['time'] = time(); // last update time
                    file_put_contents('jsonProductDict.json', json_encode($jsonProductDict)); //save dict to file

                    $product = setProductAssociatedSku($product, $parent['skus'][0]['SkuId']);
                    $parentImages = $parent['images'];
                    $product = setProductImages($product, $parentImages, true);
                    $parentName = getProductName($parent);
                    $product = setProductName($product, $parentName);
                }
            }
            
            // "Ốp chống sốc XXX (Trong suốt) ..."
            // replace XXX with real name
            $newName = getProductName($product);
            $newName = str_replace("XXX", $productName, $newName);
            $product = setProductName($product, $newName);

            //$product = setProductModel($product, $compatibility_by_model);
            //$product = setProductColor($product, $color_family);
            $variationValues = array($variation1Value, $variation2Value);
            unset($product['Skus'][0]['saleProp']);
            foreach($variations as $i => $variation) {
                if(!empty($variation) && !empty($variationValues[$i])) {
                    $product = setProductSaleProp($product, $variation, $variationValues[$i]);
                }
            }

            if(!empty($price)) {
                $product = setProductPrice($product, null, $price);
            }
            if(!empty($qty)) {
                $product = setProductQuantity($product, $qty);
            }
            if(!empty($images1)) {
                $images1 = preg_split("/\s+/", $images1); // split by space
                migrateImages($accessToken, $images1, $cache);
                $product = setProductImages($product, $images1);
            }
            if(!empty($images2)) {
                $images2 = preg_split("/\s+/", $images2); // split by space
                migrateImages($accessToken, $images2, $cache);
                $product = setProductSKUImages($product, $images2);
            }

            if(str_contains($inputSku, '_')) {
                $newSku = $inputSku;
            } else {
                $newSku = !empty($inputSku) ? $inputSku : generateProductSku($product);
                $newSku = $prefixSku . "_" . $newSku;           
                $newSku = make_short_sku($newSku);
            }
            $product = setProductSku($product, $newSku);
            $response = createProductFromApi($accessToken, $product);
            $response["mymessage"] = messageFromResponse($requestMethod, $response, $newSku, $newName);

            if(noError($response)) { 
                $createdSku = $response['data']['sku_list'][0]['seller_sku'];
                $createdSkuid = $response['data']['sku_list'][0]['sku_id'];

                $product['skus'][0]['SellerSku'] = $createdSku;
                $product['skus'][0]['SkuId'] = $createdSkuid;
                $product['item_id'] = $response['data']['item_id'];
                $product['images'] = $product['Images'];

                $jsonProductDict[$createdSku] = $product;
                $jsonProductDict['time'] = time(); // last update time

                file_put_contents('jsonProductDict.json', json_encode($jsonProductDict)); //save dict to file
            }

            // $response["raw_product"] = $product;
            // $response["shen1"] = $shen1;
            // $response["shen2"] = $shen2;
        }
    }

    if($requestMethod == 'DELETE') {
        $response = delProduct($accessToken, $deleteSku);
        $response["mymessage"] = messageFromResponse($requestMethod, $response, $deleteSku, "");
    }
    
    echo json_encode($response,JSON_UNESCAPED_UNICODE);
}

?>