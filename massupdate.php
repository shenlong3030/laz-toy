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
    <title>MASS UPDATE</title>
    <link href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <!-- bxSlider Javascript file -->
    <script src="./js/controls.js"></script>
    <script src="./js/jquery.tablesorter.min.js"></script>
    
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<?php
// Pay no attention to this statement.
// It's only needed if timezone in php.ini is not set correctly.
date_default_timezone_set("UTC");

$input = $_POST['col'][0];
$skus = empty($input) ? array() : explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][1];
$names = empty($input) ? array() : explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][2];
$models = empty($input) ? array() : explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][3];
$colors = empty($input) ? array() : explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][4];
$prices = empty($input) ? array() : explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][5];
$images = empty($input) ? array() : explode("\n", str_replace("\r", "", $input));
$imageindex = val($_POST['imageindex'], 1);

$input = $_POST['col'][6];
$actives = empty($input) ? array() : explode("\n", str_replace("\r", "", $input));

$preview = val($_POST['preview']);

$data = array(
    "names" => $names,
    "models" => $models,
    "colors" => $colors,
    "prices" => $prices,
    "images" => $images,
    "imageindex" => $imageindex,
    "qty" => $qty,
    "actives" => $actives
);

if(!empty($skus)) {
    massUpdateProducts($accessToken, $skus, $data, $preview);  
} else {
    echo "<h3>Please input SKUs and images</h3>";   
}

?>

</body>
</html>


