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
$parentSku = val($_REQUEST['parent_sku']);

$variations = val($_REQUEST['variations']);
$variations = preg_split("/;/", $variations); // split by ;

$productData = val($_REQUEST['product_data']);
$items = preg_split("/;/", $productData); // split by ;

//name;new sku;model;color;qty;price;image1 image2 ...
$productName = $items[0] ?? "";
$inputSku = $items[1] ?? "";
$variation1Value = !empty($items[2]) ? $items[2] : "variation1";
$variation2Value = !empty($items[3]) ? $items[3] : "variation2";

$sampleSku = !empty($items[4]) ? $items[4] : "sku mau";
$tmp = preg_split("/_/", $sampleSku);
$prefixSku = count($tmp) ? $tmp[0] : "";

$qty = $items[5] ?? 0;
$price = $items[6] ?? 0;

$input = trim($items[7]) ?? "";
$images = empty($input) ? [] : preg_split("/\s+/", $input); // split by space

function noError($response) {
    return $response["code"] == "0" && empty($response["detail"]);
}

function successMessage($requestMethod, $response, $sku, $newName) {
    $msg = "NONE";
    switch ($requestMethod)
    {
        case 'PUT':
            $msg = '<a target=_blank href=https://www.lazada.vn/-i' . $response["data"]["item_id"] . '.html>' . $sku . '</a>' . ';' . $newName . ';<a target="_blank" href=update_gui.php?item_id='.$response["data"]["item_id"].'&sku='.$sku.' style="color:red" tabindex="-1">Update</a>';
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
        $product = getProduct($accessToken, $sampleSku);   // OL khác

        if(empty($product)) {
            $response["code"] = "xxx";
            $response["mymessage"] = "Get product FAIL: ".$sampleSku;
        } else {
            $product = prepareProductForCreating($product);
            if(!empty($parentSku)) {
                $product = setProductAssociatedSku($product, $parentSku);
            }
            
            // "Ốp chống sốc XXX (Trong suốt) ..."
            // replace XXX with real name
            $newName = getProductName($product);
            $newName = str_replace("XXX", $productName, $newName);
            $product = setProductName($product, $newName);

            //$product = setProductModel($product, $compatibility_by_model);
            //$product = setProductColor($product, $color_family);
            $variationValues = array($variation1Value, $variation2Value);
            foreach($variations as $i => $variation) {
                $product = setProductSaleProp($product, $variation, $variationValues[$i]);
            }

            if(!empty($price)) {
                $product = setProductPrice($product, null, $price);
            }
            if(!empty($qty)) {
                $product = setProductQuantity($product, $qty);
            }
            if(!empty($images)) {
                migrateImages($accessToken, $images, $cache);
                $product = setProductSKUImages($product, $images);
                $product = setProductImages($product, array($images[0]), true);
                $shen = 1;
            } else {
                $product = setProductSKUImages($product, array($product['images'][0]));
                $shen = 2;
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
            //$response["raw_product"] = $product;
            //$response["shen"] = $variations;
            //$response["shen1"] = $variationValues;
        }
    }

    if($requestMethod == 'DELETE') {
        $response = delProduct($accessToken, $deleteSku);
        $response["mymessage"] = messageFromResponse($requestMethod, $response, $deleteSku, "");
    }
    
    echo json_encode($response,JSON_UNESCAPED_UNICODE);
}

?>