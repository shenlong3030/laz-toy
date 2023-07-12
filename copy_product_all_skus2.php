<?php
include_once "check_token.php";
//require_once('src/show_errors.php');
require_once('_main_functions.php');

$sku = isset($_REQUEST["sku"]) ? $_REQUEST["sku"] : "";
$itemId = isset($_REQUEST["item_id"]) ? $_REQUEST["item_id"] : "";
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";

$newName = val($_REQUEST["new_name"], "");
$selectedVariations = val($_REQUEST["variations"], "");
$newSkuPrefix = val($_REQUEST["new_sku_prefix"], "");
$newSkuPrefix = trim($newSkuPrefix);
$associatedSku = val($_REQUEST["associated_sku"], "");

$types = val($_REQUEST['type_screen_guards'], null);
$types = array_filter(explode("\n", str_replace("\r", "", $types)));

$productName;
$srcSkuList;
$variationList;
$variationImageList;

if($sku) {
    $product = getProduct($accessToken, $sku, $itemId);
    if($product) {
        if(empty($itemId)) {
            $itemId = getProductItemId($product);
        }
        // get product with all SKUs
        $product = getProduct($accessToken, null, $itemId);
        $product = prepareProductForCreating($product, TRUE);
        $productSkus = $product['Skus'];
        unset($product['Skus']); // remove all old SKU dict

        if($action) {
            $product = setProductAssociatedSku($product, $associatedSku);
            $product['Attributes']['name'] = $newName;

            foreach($types as $j=>$type) {
                foreach ($productSkus as $i=>$item) {
                    $oldSku = $item['SellerSku']; 
                    // break if not selected for copying
                    if(!in_array($oldSku, $selectedVariations)){
                        continue;
                    }

                    $item['saleProp']['type_screen_guard'] = $type;
                    //$item['saleProp']['compatibility_by_model'] = $model;

                    //var_dump($item['saleProp']);
                    
                    // sku = "AAA__BBBBBBB__CCC.CC"  => postfix = CCC.CC
                    preg_match('/_([^_]+$)/', $item['SellerSku'], $match);
                    $postfix = count($match) ? $match[1] : "";

                    if($newSkuPrefix[-1] != "_") {
                        $newSkuPrefix .= "_";
                    }

                    $newSku = $newSkuPrefix . $type . "." . trim($postfix);
                    $newSku = make_short_sku(strtoupper($newSku));
                    $item['SellerSku'] = $newSku;
                    $item['Status'] = "active";

                    $product['Skus'][] = $item; // add new SKU dict
                }
            }
            
            createProduct($accessToken, $product);
        } else {
            $associatedProduct = getProduct($accessToken, $associatedSku);
            $newName = getProductName($associatedProduct);

            // get newSkuPrefix from associatedSku
            // preg_match('/(.+_)/', $associatedSku, $match);
            // $newSkuPrefix = count($match) ? $match[1] : "";
        }

        //preg_match('/(.+_)/', $sku, $match);
        //$skuPrefix = count($match) ? $match[1] : "";
        //$newSkuPrefix = $newSkuPrefix ? $newSkuPrefix : $skuPrefix;

        $newSkuPrefix = "CL2__";

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

$addChildLink = "https://$_SERVER[HTTP_HOST]/lazop/addchild_gui.php?sku=$sku&name=$name";
$cloneLink = "https://$_SERVER[HTTP_HOST]/lazop/create.php?sku=$sku";

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
    Source SKU: <input type="text" name="sku" size="70" value="<?php echo $sku ?>" style="background:lightgray" readonly/>
<hr>
    NEW SKU prefix: <input style="background: lightgreen" type="text" name="new_sku_prefix" size="70" value="<?php echo $newSkuPrefix ?>"/><br/>
    NEW NAME: <input style="background: lightgreen" type="text" name="new_name" size="70" value="<?php echo $productName ?>"/><br/>
    NEW Associated Sku: <input style="background: lightgreen" type="text" name="associated_sku" size="70" value="<?php echo val($associatedSku, "") ?>"/><br/>
<hr>
    type_screen_guard <br/>
    <textarea class="nowrap" name="type_screen_guards" rows="6" cols="36"><?php echo implode("\n", $models);?></textarea>
<hr>
    select compatibility_by_model <br/>
    <?php foreach($srcSkuList as $key=>$value):?>
        <div>
        <input type="checkbox" name="variations[]" value="<?php echo $value;?>"/>
        <?php echo $variationList[$key]?>
        <a target="_blank" href="#" onclick="return false;" class="fa fa-copy copy-sku-images" style="color:purple;" tabindex="-1"></a>
        <?php echo htmlLinkImages($variationImageList[$key]) ?>
        </div>
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
<?php

// Pay no attention to this statement.
// It's only needed if timezone in php.ini is not set correctly.
date_default_timezone_set("UTC");

?>

</div>
</body>
<script type="text/javascript">
    $('a.copy-sku-images').click(function (e) {
        var imgs = [];
        $(this).parent().find('a').each(function(){
            imgs.push($(this).attr("href"));
        });

        text = imgs.join(" ");
      console.log("copy text : " + text );
      copyToClipBoard(text);
    });

</script>
</html>