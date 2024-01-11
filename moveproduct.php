<?php
include_once "check_token.php";
require_once('_main_functions.php');
//include_once "src/show_errors.php";


// Pay no attention to this statement.
// It's only needed if timezone in php.ini is not set correctly.
date_default_timezone_set("UTC");

//keypair itemid=>jsondict
$jsonBackupDelectedProducts = json_decode(file_get_contents('jsonBackupDelectedProducts.json'), true);   
if(time() - $jsonBackupDelectedProducts['time'] > (60*60*24)) {   // reset after 24 hour
    $jsonBackupDelectedProducts = [];
}

$action = val($_REQUEST["action"]);

//#######################################################################################
// HANDLE RESTORE REQUEST
if($action == "restore") {
    $index = val($_REQUEST["index"]);

    // restore deleted product
    $product = $jsonBackupDelectedProducts[$index];
    $product = prepareProduct($product); 
    //myvar_dump($product);
    $response = createProductFromApi($accessToken, $product);
    if($response["code"] == "0"){
        $sku = $response["data"]["sku_list"][0]["seller_sku"];
        $link = '<a target=_blank href=https://www.lazada.vn/-i' . $response["data"]["item_id"] . '.html>' . $sku . '</a>' . ';' . $newName . ';<a target="_blank" href=update_gui.php?&sku='.$sku.'~~'.$response["data"]["item_id"].' style="color:red" tabindex="-1">Update</a>';
        myecho($link);
        myecho("RESTORE THÀNH CÔNG");
    } else {
        myvar_dump($response);
        myecho("RESTORE THẤT BẠI");
    }
    return;
}

//#######################################################################################
// HANDLE MOVE CHILD REQUEST
$jsonProduct = val($_REQUEST['json_product']);
$source = json_decode($jsonProduct, true);
$keepName = val($_REQUEST['keep_name'], "1"); // default: không keep parent name

$desFullSku = val($_REQUEST["des_sku"]);
$arr = explode("~", $desFullSku);
$desSku = $arr[0];
$desSkuid = $arr[1];
$desItemid = $arr[2];

$selectedSkus = val($_REQUEST["selected_skus"]);
$selectedSkus = explode("\r\n", $selectedSkus);

if(empty($source)) {
    echo "INVALID PRODUCT JSON";
    return;
}

if(empty($desSku) || empty($desItemid)) {
    echo "INVALID DESTINATION SKU";
    return;
}

if($action == "move"){
    $parent = getProduct($accessToken, $desSku, $desItemid);
    if(empty($parent)) {
        echo "FAIL to get destination product: {$desSku}~~{$desItemid}";
    } else {
        $parentSkuid = getProductSkuid($parent);
        $parentProductImages = getProductImages($parent);
        $skuCount = count($source["skus"]);


        $delList = [];
        $newProduct = $source;
        $newProduct['skus'] = [];
        for($i=0; $i<$skuCount; $i++) { // every sku
            $soucesSku = getProductSku($source, $i);
            $soucesSkuid = getProductSkuid($source, $i);
            $sourceItemid = getProductItemId($source);
            $sourceFullSku = "{$soucesSku}~{$soucesSkuid}~{$sourceItemid}";

            if(in_array($sourceFullSku, $selectedSkus)){
                $newProduct['skus'][] = $source['skus'][$i];
            }

            // delete source (1 sku)
            $delList[] = $sourceFullSku;
        }

        $jsonBackupDelectedProducts[] = $newProduct;
        delProducts($accessToken, $delList);

        // create new product
        $newProduct = prepareProduct($newProduct); 
        $newProduct = setProductAssociatedSku($newProduct, $parentSkuid);
        if($keepName == "1") {
            $parentName = getProductName($parent);
            $newProduct = setProductName($newProduct, $parentName);
        }
        $newProduct = setProductImages($newProduct, $parentProductImages, true);
        $response = createProductFromApi($accessToken, $newProduct);
        if($response["code"] == "0"){
            myecho('<p style="color:green">MOVE THÀNH CÔNG</p>');
        } else {
            myvar_dump($response);
            myecho('<p style="color:red">MOVE THẤT BẠI</p>');
        }
        echo "<hr>";

        $jsonBackupDelectedProducts["time"] = time();
        file_put_contents('jsonBackupDelectedProducts.json', json_encode($jsonBackupDelectedProducts)); //save dict to file
    }
} else {
    // IF NO ACTION, JUST DISPLAY GUI
}

//#######################################################################################

?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MOVE PRODUCT TO</title>
    <?php include('src/head.php');?>
</head>

<body>
    <hr>
    <div>
        Move list SKUs<br>
        <textarea class="nowrap" id="selected_skus" rows="18" cols="90"><?php echo implode("\n", $selectedSkus)?></textarea><br>
        To
        <input size="90" type="text" id="des_sku" value="<?php echo $desFullSku;?>"><br>
        <textarea style="display:none" id="json_product" rows="3" cols="30"><?php echo $jsonProduct?></textarea><br>
        Keep parent name<input type="checkbox" id="keepParentName" name="keepParentName" checked>
        <button id="btn_move">MOVE</button>
    </div>
    <hr>

    <textarea class="nowrap" id="txt_jsonBackupDelectedProducts" rows="3" cols="30">
        <?php echo json_encode($jsonBackupDelectedProducts)?>
    </textarea>
    <br><a href="https://codebeautify.org/jsonviewer">https://codebeautify.org/jsonviewer</a>
    <hr>
    <?php 
        //#######################################################################################
        // CREATE RESTORE LINKS
        foreach ($jsonBackupDelectedProducts as $i => $product) {
            if($i == "time") {
                continue;
            }
            $link = "<a target=_blank href=moveproduct.php?action=restore&index={$i}>restore ";
            $link.= getProductName($product)." - ".getProductSkus($product);
            $link.="</a>";
            echo "<br>{$link}";
        }
        //#######################################################################################
    ?>
    <hr>
</body>

<script type="text/javascript">
    $("#btn_move").click(function() {
        var selectedSkus = $('#selected_skus').val();
        var desSku = $('#des_sku').val();
        var jsonProduct = $('#json_product').val();
        var url = `#`;
        var keepParentName = $("#keepParentName").prop('checked')? 1 : 0;
        //window.open(url, '_blank');

        var json = $('#txt_json').val();
        dopost(url, {action: "move", json_product: jsonProduct, selected_skus: selectedSkus, des_sku: desSku, keep_name: keepParentName});    
    });
</script>

</html>

