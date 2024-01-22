<?php
include_once "check_token.php";
//require_once('src/show_errors.php');
require_once('_main_functions.php');

$skuFull = val($_REQUEST["sku"]);
$arr = explode("~", $skuFull);
$sku = $arr[0];
$skuid = $arr[1];
$itemId = val($_REQUEST["item_id"], $arr[2]);

$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";

$newName = val($_REQUEST["new_name"], "");
$selectedModels = val($_REQUEST["models"], []);

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

$salePropKeys = val($_REQUEST["attr"]);

$input = val($_REQUEST['product_images']);
$productImages = explode("\r\n", $input);

$parentSaleProp1 = val($_REQUEST["parentSaleProp1"]);
$parentSaleProp1 = explode("\r\n", $parentSaleProp1);
$parentSaleProp2 = val($_REQUEST["parentSaleProp2"]);
$parentSaleProp2 = explode("\r\n", $parentSaleProp2);
$parentSaleProp = array_merge($parentSaleProp1, $parentSaleProp2);
$parentSalePropKey1 = val($_REQUEST["parentSalePropKey1"]);
$parentSalePropKey2 = val($_REQUEST["parentSalePropKey2"]);

$inputVariations = val($_REQUEST["variations"], implode("\n", $parentSaleProp2));
$inputVariations = array_filter(explode("\r\n", $inputVariations));

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

            //foreach ($inputVariations as $inputVariation) {
                foreach ($productSkus as $i=>$dict) {
                    $oldSku = $dict['SellerSku']; 
                    // break if not selected for copying
                    if(!in_array($oldSku, $selectedModels)){
                        continue;
                    }

                    $dict['Status'] = "active";
                    $cloneSaleProp = $dict['saleProp'];
                    $dict['saleProp'] = [];

                    // handle selected dropdown saleProp
                    $dict['saleProp'][$salePropKeys[0]] = $cloneSaleProp["compatibility_by_model"];


                    // sku = "AAA__BBBBBBB__CCC.CC"  => postfix = CCC.CC
                    //preg_match('/_([^_]+$)/', $dict['SellerSku'], $match);
                    //$postfix = count($match) ? $match[1] : "";
                    //$newSku = trim($newSkuPrefix) . "_" . trim($postfix);
                    //$newSku = make_short_sku($newSku);
                    //$dict['SellerSku'] = $newSku;

                    //handle input saleProp
                    if(!empty($salePropKeys[1]) && count($inputVariations)>0) {
                        foreach ($inputVariations as $inputVariation) {
                            $dict['saleProp'][$salePropKeys[1]] = trim($inputVariation);
                            $newSku = trim($newSkuPrefix) . generateSkuFromSaleprops($dict['saleProp']);
                            $dict['SellerSku'] = make_short_sku($newSku);
                            $product['Skus'][] = $dict; // dict have 2 saleProp
                        }
                    } else {
                        $newSku = trim($newSkuPrefix) . generateSkuFromSaleprops($dict['saleProp']);
                        $dict['SellerSku'] = make_short_sku($newSku);
                        $product['Skus'][] = $dict; // dict have 1 saleProp
                    }
                }
            //}
            createProduct($accessToken, $product);
        } else {
            $parentProduct = getProduct($accessToken, null, $parentItemId);
            $newName = getProductName($parentProduct);
            $productImages = getProductImages($parentProduct);

            preg_match('/([^_]+_)/', $parentSku, $match);
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
</head>
<body>
    <div class="mainContent">
<hr>
    <h1>Copy all SKU to new product</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
    Source SKU: <input type="text" name="sku" size="70" value="<?php echo $skuFull ?>" style="background:lightgray"/>
<hr>
    Tạo mới<input type="checkbox" id="cb_create_new"><br>
    NEW SKU prefix: <input style="background: lightgreen" type="text" name="new_sku_prefix" size="90" value="<?php echo $newSkuPrefix ?>"/><br/>
    NEW NAME: <input style="background: lightgreen" type="text" name="new_name" size="90" value="<?php echo $productName ?>"/><br/>
    Parent Sku: <input style="background: lightgreen" type="text" name="parent_sku" size="90" value="<?php echo val($parentSkuFull, "") ?>"/><br/>

    Parent Product Images<br><textarea id="productimages" class="nowrap" name="product_images" rows="6" cols="90"><?php echo implode("\n", $productImages);?></textarea>

<hr>

<hr>
    <select class="saleprop_key" name="attr[]">
      <option value="color_family">color_family</option>
      <option value="compatibility_by_model" selected>compatibility_by_model</option>
      <option value="Variation">Variation</option>
      <option value="type_screen_guard">type_screen_guard</option>
      <option value="smartwear_size">smartwear_size</option>
    </select><input type="button" id="btn_uncheck_all_saleprop1" value="Bỏ chọn hết"><br><br/>
    <?php foreach($srcSkuList as $key=>$value):?>
        <input class="cb_saleProp1" type="checkbox" name="models[]" value="<?php echo $value;?>" <?php echo in_array($variationList[$key], $parentSaleProp)?"checked":""?>/>
        <?php echo $variationList[$key]?>
        <?php echo htmlLinkImages($variationImageList[$key]) ?>
        <br>
    <?php endforeach; ?>
        <hr>
    <select class="saleprop_key" name="attr[]">
      <option value="color_family">color_family</option>
      <option value="compatibility_by_model">compatibility_by_model</option>
      <option value="Variation" selected>Variation</option>
      <option value="type_screen_guard">type_screen_guard</option>
      <option value="smartwear_size">smartwear_size</option>
    </select>:<br>
    <textarea class="nowrap" name="variations" rows="9" cols="60"><?php echo implode("\n", $inputVariations);?></textarea><br>
    <hr>

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
<script type="text/javascript">
    $(".saleprop_key").first().val("<?php echo $parentSalePropKey1?>");
    $(".saleprop_key").last().val("<?php echo empty($parentSalePropKey2)?'Variation':$parentSalePropKey2 ?>");

    $("#cb_create_new").change(function() {
        if(this.checked) {
            $("input[name=new_sku_prefix]").val("CL2");
            $("input[name=new_name]").val("Kính cường lực XXX");
            $("input[name=parent_sku]").val("");
        }
    });

    $("#btn_uncheck_all_saleprop1").click(function() {
        $('.cb_saleProp1').prop('checked', false); // uncheck others
    });

</script>
</html>