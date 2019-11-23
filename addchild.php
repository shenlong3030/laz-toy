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
    <title>ADD CHILD</title>
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

$sku = isset($_REQUEST["sku"]) ? $_REQUEST["sku"] : "";
$preview = isset($_POST['preview']) ? $_POST['preview'] : 0;

preg_match('/(.+__.+__)/', $sku, $match);
$initskuprefix = count($match) ? $match[1] : "";
$skuprefix = isset($_POST['skuprefix']) ? $_POST['skuprefix'] : $initskuprefix;
$appendtime = isset($_POST['appendtime']) ? $_POST['appendtime'] : 0;
$newName = isset($_REQUEST['name']) ? $_REQUEST['name'] : "";

$input = $_POST['col'][0];
$colors = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][1];
$models = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][2];
$qtys = array_filter(explode("\n", str_replace("\r", "", $input)), "strlen");

$input = $_POST['col'][3];
$prices = array_filter(explode("\n", str_replace("\r", "", $input)), "strlen");

$input = $_POST['col'][4];
$images = array_filter(explode("\n", str_replace("\r", "", $input)));
?>

<body>
    <iframe id="responseIframe" name="responseIframe" width="600" height="30"></iframe>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
Parent SKU: <input type="text" name="sku" size="80" value="<?php echo $sku?>"><br>
Child SKU prefix: <input type="text" name="skuprefix" size="50" value="<?php echo $skuprefix?>"> Append time()<input type="checkbox" name="appendtime" checked="1" value="1" <?php if($appendtime) echo "checked=1"?> ><br> 
New child name: <input type="text" name="name" size="80" value="<?php echo $newName?>"><br>

<table>
    <tbody>
        <tr>
            <td>Color</td>
            <td>Model</td>
            <td>Quantity</td>
            <td>Price</td>
            <td>Image links</td>
        </tr>
        <tr>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="30"><?php echo implode("&#13;&#10;", $colors);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="30"><?php echo implode("&#13;&#10;", $models);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="10"><?php echo implode("\n", $qtys);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="15"><?php echo implode("\n", $prices);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="80"><?php echo implode("\n", $images);?></textarea></td>
        </tr>
    </tbody>
</table>
<input type="checkbox" name="preview" checked="1" value="1">Preview<br>
<input type="submit"><hr>
<?php

$inputdata = array(
    "colors" => $colors,
    "models" => $models,
    "images" => $images,
    "appendtime" => $appendtime,
    "skuprefix" => $skuprefix,
    "newname" => $newName,
    "qtys" => $qtys,
    "prices" => $prices
    );

if(count($qtys) == 0) {
    echo "<br><br><h1>Please input Quantity</h1>";
    exit();
}

$dict = addChildProduct($accessToken, $sku, $inputdata, $preview);

?>

<script type="text/javascript">
</script>
</div>
</body>
</html>