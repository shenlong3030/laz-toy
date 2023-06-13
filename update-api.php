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

        case 'price':
            $response = updatePricesWithAPI($accessToken, $sku, null, $sprice);
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