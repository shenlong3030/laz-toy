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
    <title><?php echo "LAZADA sp"; ?></title>
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

$sku = val($_POST['sku'], "");
$skuprefix = val($_POST['skuprefix']);
$preview = val($_POST['preview']);

$combos = val($_POST["combo"]);
$prices = val($_POST["price"]);

$input = $_POST['col'][0];
$names = array_filter(explode("\n", str_replace("\r", "", $input)));

$input = val($_POST['col'][1]);
$branches = array_filter(explode("\n", str_replace("\r", "", $input)));

$input = val($_POST['col'][2]);
$colors = array_filter(explode("\n", str_replace("\r", "", $input)));

$input = val($_POST['col'][3], "");
$models = array_filter(explode("\n", str_replace("\r", "", $input)));
$lcropmodel = val($_POST['lcropmodel'], 0);

$input = val($_POST['col'][4], "");
$qtys = array_filter(explode("\n", str_replace("\r", "", $input)),'is_numeric');

$input = val($_POST['col'][5]);
$groups = array_filter(explode("\n", str_replace("\r", "", $input)));

$input = val($_POST['col'][6]);
$images = array_filter(explode("\n", str_replace("\r", "", $input)));

$comboimages = val($_POST['comboimage']);

?>

<body>
    <h1>Create products</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
Source SKU: <input type="text" name="sku" size="80" value="<?php echo $sku?>"><br>
New SKU prefix: <input type="text" name="skuprefix" size="80" value="<?php echo $skuprefix?>"><br>

Create options:<br>
    <input type="checkbox" name="combo[]" value="0">Buy 1 get 1 --> 
    Price<input type="text" name="price[]" value="<?php echo val($prices[0]);?>">
    -- Main Image<input type="text" name="comboimage[]" value="<?php echo val($comboimages[0], "");?>"><br>
    
    <input type="checkbox" name="combo[]" value="1" checked="1">Combo 1 --> 
    Price<input type="text" name="price[]" value="<?php echo val($prices[1]);?>">
    -- Main Image<input type="text" name="comboimage[]" value="<?php echo val($comboimages[1], "");?>"><br>
    
    <input type="checkbox" name="combo[]" value="2">Combo 2 --> 
    Price<input type="text" name="price[]" value="<?php echo val($prices[2]);?>">
    -- Main Image<input type="text" name="comboimage[]" value="<?php echo val($comboimages[2], "");?>"><br>
    
    <input type="checkbox" name="combo[]" value="3">Combo 3 --> 
    Price<input type="text" name="price[]" value="<?php echo val($prices[3]);?>">
    -- Main Image<input type="text" name="comboimage[]" value="<?php echo val($comboimages[3], "");?>"><br>
    
    <input type="checkbox" name="combo[]" value="4">Combo 4 --> 
    Price<input type="text" name="price[]" value="<?php echo val($prices[4]);?>">
    -- Main Image<input type="text" name="comboimage[]" value="<?php echo val($comboimages[4], "");?>"><br>
    
    <input type="checkbox" name="combo[]" value="5">Combo 5 --> 
    Price<input type="text" name="price[]" value="<?php echo val($prices[5]);?>">
    -- Main Image<input type="text" name="comboimage[]" value="<?php echo val($comboimages[5], "");?>"><br>
<table>
    <tbody>
        <tr>
            <td>Name</td>
            <td>Branch</td>
            <td>Color</td>
            <td>Model<br>Left crop <input size="3" type="text" name="lcropmodel" value="<?php echo val($lcropmodel);?>">words</td>
            <td>Qty</td>
            <td>Group</td>
            <td>Images</td>
        </tr>
        <tr>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="60"><?php echo implode("\n", $names);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="10"><?php echo implode("\n", $branches);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="10"><?php echo implode("\n", $colors);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="20"><?php echo implode("\n", $models);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="5"><?php echo implode("\n", $qtys);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="5"><?php echo implode("\n", $groups);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="80" style="white-space: nowrap;"><?php echo implode("\n", $images);?></textarea></td>
        </tr>
    </tbody>
</table>
<input type="checkbox" name="preview" checked="1" value="1">Preview<br>
<input type="submit"><hr>


<?php
    
if(empty($sku) || empty($skuprefix)) {
    echo "<h1>Please input source SKU and SKU prefix</h1>";
    exit(1);
}

if(empty($branches)) {
    echo "<h1>Please input branches</h1>";
    exit(1);
}

foreach($combos as $item) {
    if(empty($prices[$item])) {
        echo "<h1>Please input correct price</h1>";
        exit(1);
    }
}

$data = array(
    "names" => $names,
    "branches" => $branches, 
    "colors" => $colors,
    "models" => $models,
    "lcropmodel" => $lcropmodel,
    "qtys" => $qtys, 
    "groups" => $groups,
    "images" => $images,
    );

//var_dump($comboimages);

createProducts($accessToken, $sku, $skuprefix, $data, $combos, $comboimages, $prices, $preview);

?>

<script type="text/javascript">
</script>
</div>
</body>
</html>