<?php
include_once "check_token.php";
require_once('_main_functions.php');

//include_once "src/show_errors.php";

?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CREATE PRODUCTS</title>
    <?php include('src/head.php');?>
</head>
<body>

<?php
// Pay no attention to this statement.
// It's only needed if timezone in php.ini is not set correctly.
date_default_timezone_set("UTC");

$preview = val($_POST['preview']);
$makegroup = val($_POST['makegroup']);

$input = $_POST['col'][0];
$parentskus = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][1];
$names = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][2];
$models = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][3];
$colors = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][4];
$qtys = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][5];
$prices = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][6];
$images = explode("\n", str_replace("\r", "", $input));

$resetimages = val($_POST['resetimages']);
    
if(empty($sourceskus) || empty($skuprefixs)) {
    echo "<h1>Please input source SKU and SKU prefix</h1>";
    exit(1);
}

$data = array(
    "parentskus" => $parentskus,
    "sourceskus" => $sourceskus,
    "skuprefixs" => $skuprefixs,
    "names" => $names,
    "groups" => $groups,
    "models" => $models,
    "colors" => $colors,
    "qtys" => $qtys, 
    "prices" => $prices,
    "images" => $images,
    "resetimages" => $resetimages,
    "makegroup" => $makegroup,
    "kiotids" => $kiotids
    );

createProductsFromManySource($accessToken, $data, $preview);

?>

</body>
</html>