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
    <title>CREATE</title>
    <link href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <!-- bxSlider Javascript file -->
    <script src="./js/controls.js"></script>
    <script src="./js/jquery.tablesorter.min.js"></script>
    
    <link rel="stylesheet" type="text/css" href="css/style.css">
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
$sourceskus = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][2];
$skuprefixs = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][3];
$names = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][4];
$groups = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][5];
$models = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][6];
$colors = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][7];
$qtys = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][8];
$prices = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][9];
$images = explode("\n", str_replace("\r", "", $input));

$comboimages = val($_POST['comboimage']);
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
    "makegroup" => $makegroup
    );

createProductsFromManySource($accessToken, $data, $preview);

?>

</body>
</html>