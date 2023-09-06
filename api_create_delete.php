<?php
include_once "check_token.php";
include_once "src/show_errors.php";
require_once('_main_functions.php');

$ocsSKU = "OCS_MAU";
$odtSKU = "ODT_MAU";
$olKhacSKU = "OL_XXX";


// PUT : create new product
// DELETE : delete product
$requestMethod = $_REQUEST['REQUEST_METHOD'];

$deleteSku = val($_REQUEST['delete_sku']);
$parentSku = val($_REQUEST['parent_sku']);

$productData = val($_REQUEST['product_data']);
$items = preg_split("/;/", $productData); // split by ;

//name;new sku;model;color;qty;price;image1 image2 ...
$productName = $items[0] ?? "";
$inputSku = $items[1] ?? "";
$compatibility_by_model = $items[2] ?? "";
$color_family = $items[3] ?? "";
$qty = $items[4] ?? 0;
$price = $items[5] ?? 0;

$input = $items[6] ?? "";
$images = preg_split("/\s+/", $input); // split by space


function noError($response) {
    return $response["code"] == "0" && empty($response["detail"]);
}

function successMessage($requestMethod, $response, $sku, $newName) {
    $msg = "NONE";
    switch ($requestMethod)
    {
        case 'PUT':
            $msg = '<a target=_blank href=https://www.lazada.vn/-i' . $response["data"]["item_id"] . '.html>' . $sku . '</a>' . ';' . $newName;
            break;
        case 'DELETE':
            $msg = 'Deleted; ' . $sku;
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
            $fmsg .= "; " . json_encode($response["detail"]);
        }
        return $fmsg;
    }
}

if($accessToken) {
    $response = 0;
    $newSku = !empty($inputSku) ? $inputSku : $compatibility_by_model;
    $newName;

    if($requestMethod == 'PUT') {
        switch ($color_family) {
            case 'Chống sốc':
                $product = getProduct($accessToken, $ocsSKU);   // OCS
                $product = prepareProductForCreating($product);
                if(!empty($parentSku)) {
                    $product = setProductAssociatedSku($product, $parentSku);
                }

                $newSku = "OCS_" . $newSku;                     // OCS
                $product = setProductSku($product, $newSku);
                
                // "Ốp chống sốc XXX (Trong suốt) ..."
                // replace XXX with real name
                $newName = getProductName($product);
                $newName = str_replace("XXX", $productName, $newName);
                $product = setProductName($product, $newName);

                $product = setProductModel($product, $compatibility_by_model);
                $product = setProductColor($product, $color_family);

                if(!empty($price)) {
                    $product = setProductPrice($product, null, $price);
                }
                if(!empty($qty)) {
                    $product = setProductQuantity($product, $qty);
                }
                if(!empty($images)) {
                    migrateImages($accessToken, $images, $cache);
                    $product = setProductSKUImages($product, $images);
                }

                $response = createProductFromApi($accessToken, $product);
                break;

            case 'Dẻo trong':
                $product = getProduct($accessToken, $odtSKU);   // ODT
                $product = prepareProductForCreating($product);
                if(!empty($parentSku)) {
                    $product = setProductAssociatedSku($product, $parentSku);
                }

                $newSku = "ODT_" . $newSku;                     // ODT
                $product = setProductSku($product, $newSku);
                echo "shen1";
                // "Ốp chống sốc XXX (Trong suốt) ..."
                // replace XXX with real name
                $newName = getProductName($product);
                $newName = str_replace("XXX", $productName, $newName);
                $product = setProductName($product, $newName);

                $product = setProductModel($product, $compatibility_by_model);
                $product = setProductColor($product, $color_family);
                echo "shen2";
                if(!empty($price)) {
                    $product = setProductPrice($product, null, $price);
                }
                if(!empty($qty)) {
                    $product = setProductQuantity($product, $qty);
                }
                if(!empty($images)) {
                    echo "shen3";
                    migrateImages($accessToken, $images, $cache);
                    echo "shen4";
                    $product = setProductSKUImages($product, $images);
                    echo "shen5";
                }

                $response = createProductFromApi($accessToken, $product);
                
                break;

            default:
                $product = getProduct($accessToken, $olKhacSKU);   // OL khác
                $product = prepareProductForCreating($product);
                if(!empty($parentSku)) {
                    $product = setProductAssociatedSku($product, $parentSku);
                }

                $newSku = "OL_" . $newSku;                         // OL khác
                $product = setProductSku($product, $newSku);
                
                // "Ốp chống sốc XXX (Trong suốt) ..."
                // replace XXX with real name
                $newName = getProductName($product);
                $newName = str_replace("XXX", $productName, $newName);
                $product = setProductName($product, $newName);

                $product = setProductModel($product, $compatibility_by_model);
                $product = setProductColor($product, $color_family);

                if(!empty($price)) {
                    $product = setProductPrice($product, null, $price);
                }
                if(!empty($qty)) {
                    $product = setProductQuantity($product, $qty);
                }
                if(!empty($images)) {
                    migrateImages($accessToken, $images, $cache);
                    $product = setProductSKUImages($product, $images);
                }

                $response = createProductFromApi($accessToken, $product);
                break;
        }
        $response["mymessage"] = messageFromResponse($requestMethod, $response, $newSku, $newName);
    }

    if($requestMethod == 'DELETE') {
        $response = delProduct($accessToken, $deleteSku);
        $response["mymessage"] = messageFromResponse($requestMethod, $response, $deleteSku, "");
    }

    
    echo json_encode($response);
}

?>