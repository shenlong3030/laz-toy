<?php
include_once "check_token.php";
require_once('_main_functions.php');

$sku = isset($_REQUEST['sku']) ? $_REQUEST['sku'] : 0;
$skustatus = isset($_REQUEST['skustatus']) ? $_REQUEST['skustatus'] : 'inactive';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$qty = isset($_REQUEST['qty']) ? $_REQUEST['qty'] : 0;
$sprice = isset($_REQUEST['sprice']) ? $_REQUEST['sprice'] : 0;

if($accessToken && $sku) {
    $response = 0;

    switch ($action) {
        case 'status':
            $product = getProduct($accessToken, $sku);
            if($product) {
                $product = prepareProductForUpdating($product);

                if ($skustatus) {
                    // force active product
                    $product['Skus'][0]['Status'] = $skustatus;
                    $response = saveProduct($accessToken, $product);
                }
            } else {
                $response = array(
                        "code" => "1",
                        "message" => "Invalid sku"
                    );
            }
            break;

        case 'qty':
            $response = updateQuantityWithAPI($accessToken, $sku, $qty);
            
            // force active product
            if($qty == 500) {
                $product = getProduct($accessToken, $sku);
                if($product) {
                    $product = prepareProductForUpdating($product);
                    $product['Skus'][0]['Status'] = 'active';
                    $response['reactive'] = saveProduct($accessToken, $product);
                }
            }
            break;

        case 'price':
            $response = updatePricesWithAPI($accessToken, $sku, null, $sprice);
            break;
        
        default:
            # code...
            break;
    }
    sleep(1);

    if($response["code"]=="0") {
        $response["message"]="SUCCESS";
    }

    echo json_encode($response);
}

?>