<?php
include_once "check_token.php";
require_once('_main_functions.php');

//include_once "src/show_errors.php";

//var_dump($_POST);

$sku = $_POST['sku'] ? $_POST['sku'] : 0;
$qty = isset($_POST['qty']) ? $_POST['qty'] : '';
$price = $_POST['price'] ? $_POST['price'] : 0;
$sale_price = $_POST['sale_price'] ? $_POST['sale_price'] : 0;
$fromdate = $_POST['fromdate'] ? $_POST['fromdate'] : 0;
$todate = $_POST['todate'] ? $_POST['todate'] : 0;
$name = $_POST['name'] ? $_POST['name'] : '';

$compatibility_by_model = $_POST['compatibility_by_model'] ? $_POST['compatibility_by_model'] : '';
$color_family = $_POST['color_family'] ? $_POST['color_family'] : '';

$category = $_POST['category'] ? $_POST['category'] : 0;
$input = val($_POST['images']);
$images = array_filter(explode("\n", str_replace("\r", "", $input)));

$shortdesc = $_POST['shortdesc'] ? $_POST['shortdesc'] : '';
$desc = $_POST['desc'] ? $_POST['desc'] : '';
$brand = $_POST['brand'] ? $_POST['brand'] : '';


if($accessToken && $sku) {
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

    $product = getProduct($accessToken, $sku);
    $response = null;
    if($product) {
        $product = prepareProductForUpdating($product);

        if ($images) {
            //echo "i1";
            $product = setImagesForProduct($product, $images, $savedimages);
        } elseif (is_numeric($qty)){
            //echo "i2";
            $response = updateQuantity($accessToken, $sku, $qty);
        } elseif ($price) {
            //echo "i3";
            $response = updatePrices($accessToken, $sku, $price, $sale_price);
        } elseif($category) {
            //echo "i4";
            // old source
            $response = setPrimaryCategory($accessToken, $sku, $category);
        } elseif($name) {
            //echo "i5";
            $product = setNameForProduct($product, $name);
        } elseif($color_family) {
            //echo "i6";
            $product = setColorForProduct($product, $color_family);
        } elseif($compatibility_by_model) {
            //echo "i7";
            $product = setModelForProduct($product, $compatibility_by_model);
        } elseif($shortdesc) {
            //echo "i8";
            $product = setShortDescriptionForProduct($product, $shortdesc);
        } elseif($desc) {
            //echo "i9";
            $product = setDescriptionForProduct($product, $desc);
        } elseif($brand) {
            //echo "i9";
            $product = setBrandForProduct($product, $brand);
        }

        //var_dump($price, $sale_price);
        if(!$response) {
            $response = saveProduct($accessToken, $product);
        }
        
        //var_dump($response);
        
    } else {
        echo "<br>INVALID SKU<br>";
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
    $sku = $_POST['sku'] ? $_POST['sku'] : '';
    $qty = $_POST['qty'] ? $_POST['qty'] : '0';
    $price = $_POST['price'] ? $_POST['price'] : '0';
    $sale_price = $_POST['sale_price'] ? $_POST['sale_price'] : '0';
    $name = $_POST['name'] ? $_POST['name'] : '';

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