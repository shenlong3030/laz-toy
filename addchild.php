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

$salePropKey1 = val($_REQUEST['saleprop_key1']);
$salePropKey2 = val($_REQUEST['saleprop_key2']);

$childLines = trim(val($_REQUEST['child_lines']));
$childLines = explode("\r\n", $childLines);

$inputdata = array(
    "kiotids" => $kiotids,
    "skuprefix" => $skuprefix,
    "newname" => $newName,
    "jsonProduct" => $jsonProduct,
    "salePropKey1" => $salePropKey1,
    "salePropKey2" => $salePropKey2,
    "childLines"  => $childLines
    );


echo "<br>MASS CREATING...<br>";
$dict = massAddChildProduct($accessToken, $sku, $inputdata, $preview);

?>

<script type="text/javascript">
</script>
</div>
</body>
</html>