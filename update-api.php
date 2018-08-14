<?php
include_once "check_token.php";
require_once('_main_functions.php');

$sku = isset($_POST['sku']) ? $_POST['sku'] : 0;
$skustatus = isset($_POST['skustatus']) ? $_POST['skustatus'] : 'inactive';


if($accessToken && $sku) {
    $response = 0;
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
    echo json_encode($response);
}

?>