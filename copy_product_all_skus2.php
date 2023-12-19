<?php
include_once "check_token.php";
require_once('src/show_errors.php');
require_once('_main_functions.php');

$skuFull = val($_REQUEST["sku"]);
$arr = explode("~", $skuFull);
$sku = $arr[0];
$skuid = $arr[1];
$itemId = val($_REQUEST["item_id"], $arr[2]);

$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";

$newName = val($_REQUEST["new_name"], "");
$selectedModels = val($_REQUEST["models"], []);
$inputVariations = val($_REQUEST["variations"]);
$inputVariations = explode("\r\n", $inputVariations);

$newSkuPrefix = val($_REQUEST["new_sku_prefix"], "");

$parentSkuFull = val($_REQUEST["parent_sku"]);
$arr = explode("~", $parentSkuFull);
$parentSku = $arr[0];
$parentSkuid = $arr[1];
$parentItemId = $arr[2];

$productName;
$srcSkuList;
$variationList;
$variationImageList;

$input = val($_REQUEST['product_images']);
$productImages = explode("\r\n", $input);

if($sku) {
    $product = getProduct($accessToken, null, $itemId);
    if($product) {
        $product = prepareProductForCreating($product, TRUE);
        $product['Images'] = [];
        $productSkus = $product['Skus'];
        $product['Skus'] = [];

        if($action) {
            $product = setProductAssociatedSku($product, $parentSkuid);
            $product = setProductImages($product, $productImages, true);
            $product['Attributes']['name'] = $newName;

            foreach ($inputVariations as $inputVariation) {
                foreach ($productSkus as $i=>$dict) {
                    $oldSku = $dict['SellerSku']; 
                    // break if not selected for copying
                    if(!in_array($oldSku, $selectedModels)){
                        continue;
                    }

                    $dict['saleProp']["Variation"] = $inputVariation;

                    // sku = "AAA__BBBBBBB__CCC.CC"  => postfix = CCC.CC
                    preg_match('/_([^_]+$)/', $dict['SellerSku'], $match);
                    $postfix = count($match) ? $match[1] : "";
                    $newSku = trim($newSkuPrefix) . trim($inputVariation) . "." . trim($postfix);
                    $newSku = strtoupper($newSku);
                    $newSku = make_short_sku($newSku);

                    $dict['SellerSku'] = $newSku;
                    $dict['Status'] = "active";

                    $product['Skus'][] = $dict; // add dict to array
                }
            }
            createProduct($accessToken, $product);
        } else {
            $parentProduct = getProduct($accessToken, null, $parentItemId);
            $newName = getProductName($parentProduct);
            $productImages = getProductImages($parentProduct);

            preg_match('/(.+_)/', $parentSku, $match);
            $newSkuPrefix = count($match) ? $match[1] : "";
        }

        preg_match('/(.+_)/', $sku, $match);
        $skuPrefix = count($match) ? $match[1] : "";
        $newSkuPrefix = $newSkuPrefix ? $newSkuPrefix : $skuPrefix;
        $productName = $newName ? $newName : $product['Attributes']['name'];

        $srcSkuList = array_map(function($productSkus){
            return $productSkus['SellerSku']; 
        }, $productSkus);

        $variationList = array_map(function($productSkus){
            $attributes = getProductAttributeNames(FALSE);
            foreach ($attributes as $i => $attr) {
                $values[] = $productSkus['saleProp'][$attr];
            }
            $values = array_filter($values);
            return implode(",", $values);
        }, $productSkus);

        $variationImageList = array_map(function($productSkus){
            return $productSkus['Images']; 
        }, $productSkus);
    } else {
        echo "INVALID ID";
    }
}

?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>COPY PRODUCT</title>
    <link href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" rel="stylesheet">

    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <!-- bxSlider Javascript file -->
    <script src="./js/controls.js"></script>
    <script src="./js/jquery.tablesorter.min.js"></script>
    
    <link rel="stylesheet" type="text/css" href="css/style.css">

    <?php include('src/head.php');?>

    <style>
    .mainContent{
      margin-top: 50px;
    }
    </style>

</head>
<body>
    <div class="mainContent">

<hr>
    <h1>Copy all SKU to new product</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
    Source SKU: <input type="text" name="sku" size="70" value="<?php echo $skuFull ?>" style="background:lightgray" readonly/>
<hr>
    NEW SKU prefix: <input style="background: lightgreen" type="text" name="new_sku_prefix" size="90" value="<?php echo $newSkuPrefix ?>"/><br/>
    NEW NAME: <input style="background: lightgreen" type="text" name="new_name" size="90" value="<?php echo $productName ?>"/><br/>
    Parent Sku: <input style="background: lightgreen" type="text" name="parent_sku" size="90" value="<?php echo val($parentSkuFull, "") ?>"/><br/>

    Parent Product Images<br><textarea id="productimages" class="nowrap" name="product_images" rows="6" cols="90"><?php echo implode("\n", $productImages);?></textarea>

<hr>
    Input Variations:<br>
    <textarea class="nowrap" name="variations" rows="9" cols="60"><?php echo implode("\n", $inputVariations);?></textarea><br>

    Select compatibility_by_model to copy <br/>

    <?php foreach($srcSkuList as $key=>$value):?>
        <input type="checkbox" name="models[]" value="<?php echo $value;?>"/>
        <?php echo $variationList[$key]?>
        <?php echo htmlLinkImages($variationImageList[$key]) ?>
        <br>
    <?php endforeach; ?>

    <input type="hidden" name="action" value="create"/>
    <input type="submit" value="COPY"/>
    </form>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>

</div>
</body>
</html>