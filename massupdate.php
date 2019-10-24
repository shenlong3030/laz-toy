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
$skus = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][1];
$names = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][2];
$models = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][3];
$colors = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][4];
$prices = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][5];
$images = explode("\n", str_replace("\r", "", $input));
$imageindex = val($_POST['imageindex'], 1);

$input = $_POST['col'][6];
$actives = explode("\n", str_replace("\r", "", $input));

$preview = val($_POST['preview']);
?>

<body>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
<table>
    <tr>
     <th>SKUs</th>
     <th>Names</th>
     <th>Models</th>
     <th>Colors</th>
     <th>Prices</th>
     <th>Images >> Start index (1-8) <input size="3" type="text" name="imageindex" value="<?php echo val($imageindex);?>"></th>
     <th>Actives</th>
    </tr>
    <tbody>
        <tr>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="30"><?php echo implode("\n", $skus);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="50"><?php echo implode("\n", $names);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="10"><?php echo implode("\n", $models);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="10"><?php echo implode("\n", $colors);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="10"><?php echo implode("\n", $prices);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="50"><?php echo implode("\n", $images);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="5"><?php echo implode("&#13;&#10;", $actives);?></textarea></td>
        </tr>
    </tbody>
</table>
<br><br>

<input type="checkbox" name="preview" checked="1" value="1">Preview<br>

<input type="submit"><hr>
<?php

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

<script type="text/javascript">
</script>
</div>
</body>
</html>