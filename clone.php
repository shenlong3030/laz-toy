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
    <title>CLONE</title>
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

$sku = isset($_POST['sku']) ? $_POST['sku'] : "";
$cloneby = isset($_POST['cloneby']) ? $_POST['cloneby'] : "";
$preview = isset($_POST['preview']) ? $_POST['preview'] : 0;

$skuprefix = isset($_POST['skuprefix']) ? $_POST['skuprefix'] : "";
$appendtime = isset($_POST['appendtime']) ? $_POST['appendtime'] : 0;
$newName = isset($_POST['name']) ? $_POST['name'] : "";

$input = val($_POST['col'][0]);
$colors = array_filter(explode("\n", str_replace("\r", "", $input)));

$input = val($_POST['col'][1]);
$models = array_filter(explode("\n", str_replace("\r", "", $input)));
$lcropmodel = val($_POST['lcropmodel'], 0);

$input = val($_POST['col'][2]);
$images = array_filter(explode("\n", str_replace("\r", "", $input)));
?>

<body>
    <iframe id="responseIframe" name="responseIframe" width="600" height="30"></iframe>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
Source SKU: <input type="text" name="sku" size="80" value="<?php echo $sku?>"><br>
New SKU prefix: <input type="text" name="skuprefix" size="50" value="<?php echo $skuprefix?>"> Append time()<input type="checkbox" name="appendtime" checked="1" value="1" <?php if($appendtime) echo "checked=1"?> ><br> 
New name: <input type="text" name="name" size="80" value="<?php echo $newName?>"><br>
Clone:<br>
    <input type="radio" name="cloneby" value="original" checked="checked"> Original<br>
    <input type="radio" name="cloneby" value="color"> With colors (associated with source SKU)<br>
    <input type="radio" name="cloneby" value="model"> With models (associated with source SKU)<br>
<table>
    <tbody>
        <tr>
            <td>Colors<br><a target="_blank" href="http://npminhphuc.blogspot.com/2018/08/lazada-colors.html">Color list</a></td>
            <td>Models<br>Left crop <input size="3" type="text" name="lcropmodel" value="<?php echo val($lcropmodel);?>">words
            <br><a target="_blank" href="http://npminhphuc.blogspot.com/2018/08/lazada-model-mobile-phone.html">Model list</a></td>
            <td>Image links</td>
        </tr>
        <tr>
            <td><textarea name="col[]" rows="20" cols="30"><?php echo implode("\n", $colors);?></textarea></td>
            <td><textarea name="col[]" rows="20" cols="30"><?php echo implode("\n", $models);?></textarea></td>
            <td><textarea style="white-space: nowrap;" name="col[]" rows="20" cols="80"><?php echo implode("\n", $images);?></textarea></td>
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
    "cloneby" => $cloneby,
    "lcropmodel" => $lcropmodel
    );

if($cloneby) {
    $dict = cloneProduct($accessToken, $sku, $inputdata, $preview);
} else {
    echo "<br><br>Please select options<br><br>";   
}

?>

<script type="text/javascript">
</script>
</div>
</body>
</html>