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

$sku = val($_REQUEST["sku"]);
$skuid = val($_REQUEST["skuid"]);
$preview = val($_POST['preview']);

preg_match('/(.+__.+__)/', $sku, $match);
$initskuprefix = count($match) ? $match[1] : "";
$skuprefix = val($_POST['skuprefix'], $initskuprefix);
$newName = val($_REQUEST['name']);
$jsonProduct = val($_REQUEST['json_product']);

$saleProps = array();
foreach ($_REQUEST['attr'] as $i => $prop) {
    $input = $_POST['col'][$i];
    if(!empty($input)) {
        $lines = explode("\n", str_replace("\r", "", $input)); // lines is array
        $saleProps[$prop] = $lines;
        // $saleProps['compatibility_by_model'] = $lines
    }
}

$selectedSaleProps = val($_REQUEST['attr']);

// move Variation to index 0 --> fix SKU format
// $variationPos = array_search('Variation', $attrList);
// if($variationPos) {
//     repositionArrayElement($attrList, $variationPos, 0);
//     repositionArrayElement($attrValues, $variationPos, 0);
// }

$input = $_POST['col'][2];
$qtys = array_filter(explode("\n", str_replace("\r", "", $input)), "strlen");

$input = $_POST['col'][3];
$prices = array_filter(explode("\n", str_replace("\r", "", $input)), "strlen");

$input = $_POST['col'][4];
$images = array_filter(explode("\n", str_replace("\r", "", $input)));

$childLines = trim(val($_REQUEST['child_lines']));
$childLines = explode("\r\n", $childLines);

$inputdata = array(
    "kiotids" => $kiotids,
    "saleProps" => $saleProps,
    "images" => $images,
    "appendtime" => $appendtime,
    "skuprefix" => $skuprefix,
    "newname" => $newName,
    "qtys" => $qtys,
    "prices" => $prices,
    "jsonProduct" => $jsonProduct,
    "childLines" => $childLines,
    "selectedSaleProps" => $selectedSaleProps,
    );


echo "<br>MASS CREATING...<br>";
$dict = massAddChildProduct($accessToken, $sku, $inputdata, $preview);

?>

<script type="text/javascript">
</script>
</div>
</body>
</html>