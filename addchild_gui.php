<?php
include_once "check_token.php";
//include_once "src/show_errors.php";
require_once('_main_functions.php');

?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ADD CHILD</title>
    <?php include('src/head.php');?>
</head>

<?php
// Pay no attention to this statement.
// It's only needed if timezone in php.ini is not set correctly.
date_default_timezone_set("UTC");

$skuFull = val($_REQUEST["sku"]);
$arr = explode("~", $skuFull);
$sku = $arr[0];
$skuId = $arr[1];
$itemId = $arr[2];

// CL.21D__IP.11.6.1  -->  CL.21D__
preg_match('/(\w+(.\w+)+_+)+/', $sku, $matches);
$skuprefix = val($matches[0], "");

$newName = val($_REQUEST['name']);
$attrNames = getProductAttributeNames(FALSE);

$json = val($_REQUEST['json_product']);
$product = json_decode($json, true);

$salPropKey1 = val($_REQUEST["salPropKey1"]);
$salPropKey2 = val($_REQUEST["salPropKey2"]);

$variation1 = val($product['skus'][0]['saleProp'][$salPropKey1]);
$variation2 = val($product['skus'][0]['saleProp'][$salPropKey2]);
$qty = val($product['skus'][0]['quantity']);
$sprice = val($product['skus'][0]['special_price']);
$image1 = val($product['skus'][0]['Images'][0]);

$example = [$variation1, $variation2, $qty, $sprice, $image1];
$exHeader = ["Variation1","Variation2","Qty","Price","image_url1 image_url2"];

?>

<body>
<form action="addchild.php" method="POST" target="responseIframe">
<textarea name="json_product" rows="1" cols="90"><?php echo json_encode($product,JSON_PRETTY_PRINT);?></textarea>
<br>
Parent SKU: <input type="text" name="sku" size="80" value="<?php echo $skuFull?>"><br>
Parent SKUID: <input type="text" name="skuid" size="80" value="<?php echo $skuId?>"><br>
Child SKU prefix: <input type="text" name="skuprefix" size="50" value="<?php echo $skuprefix?>"><br> 
New child name: <input type="text" name="name" size="80" value="<?php echo $newName?>"><br>
<br>
Possible Attributes : <?php echo implode(",", $attrNames)?><br><br>

Variation1 <select class="saleprop_key" name="saleprop_key1">
  <option value="color_family">color_family</option>
  <option value="compatibility_by_model" selected>compatibility_by_model</option>
  <option value="Variation">Variation</option>
  <option value="type_screen_guard">type_screen_guard</option>
  <option value="smartwear_size">smartwear_size</option>
</select> ; 

Variation2<select class="saleprop_key" name="saleprop_key2">
  <option value="color_family" selected>color_family</option>
  <option value="compatibility_by_model" >compatibility_by_model</option>
  <option value="Variation">Variation</option>
  <option value="type_screen_guard">type_screen_guard</option>
  <option value="smartwear_size">smartwear_size</option>
</select><br><br>

Mass input child: <?php echo implode('⇒', $exHeader)?><br>
<textarea class="nowrap" name="child_lines" rows="10" cols="120"><?php echo implode('⇒', $example)?></textarea><br>

<input type="checkbox" name="preview" checked="1" value="1">Preview<br>
<input type="submit"><hr>

</form>
<iframe id="responseIframe" name="responseIframe" width="1000" height="1000"></iframe>

<script type="text/javascript">
    $(".saleprop_key").first().val("<?php echo $salPropKey1 ?>");
    $(".saleprop_key").last().val("<?php echo $salPropKey2 ?>");
</script>
</div>
</body>
</html>