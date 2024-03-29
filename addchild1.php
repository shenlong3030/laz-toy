<?php
include_once "check_token.php";
require_once('_main_functions.php');

//include_once "src/show_errors.php";
exit(1);

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

$preview = val($_POST['preview']);

$input = $_POST['col'][0];
$parentskus = explode("\n", str_replace("\r", "", $input));

$input = $_POST['col'][1];
$sourceskus = array_filter(explode("\n", str_replace("\r", "", $input)));

$input = $_POST['col'][2];
$skuprefixs = array_filter(explode("\n", str_replace("\r", "", $input)));

$input = $_POST['col'][3];
$names = array_filter(explode("\n", str_replace("\r", "", $input)));

$input = val($_POST['col'][4], "");
$models = array_filter(explode("\n", str_replace("\r", "", $input)));

$input = val($_POST['col'][5], "");
$colors = array_filter(explode("\n", str_replace("\r", "", $input)));

$input = val($_POST['col'][6]);
$qtys = array_filter(explode("\n", str_replace("\r", "", $input)),'is_numeric');

$input = val($_POST['col'][7]);
$prices = array_filter(explode("\n", str_replace("\r", "", $input)));

$input = val($_POST['col'][8]);
$images = array_filter(explode("\n", str_replace("\r", "", $input)));

$comboimages = val($_POST['comboimage']);
$resetimages = val($_POST['resetimages']);

?>

<body>
    <h1>Create products</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">

<table>
    <tbody>
        <tr>
            <td>Parent SKU</td>
            <td>Source SKU</td>
            <td>SKU prefix</td>
            <td>Name</td>
            <td>Model</td>
            <td>Variation</td>
            <td>Qty</td>
            <td>Prices</td>
            <td>Images <input style="padding-left: 10px" type="checkbox" name="resetimages" value="1" <?php if($resetimages) echo "checked=1";?>>Remove all source's images</td>
        </tr>
        <tr>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="20"><?php echo implode("\n", $parentskus);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="20"><?php echo implode("\n", $sourceskus);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="20"><?php echo implode("\n", $skuprefixs);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="60"><?php echo implode("\n", $names);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="20"><?php echo implode("\n", $models);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="10"><?php echo implode("\n", $colors);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="5"><?php echo implode("\n", $qtys);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="5"><?php echo implode("\n", $prices);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="80" style="white-space: nowrap;"><?php echo implode("\n", $images);?></textarea></td>
        </tr>
    </tbody>
</table>
<input type="checkbox" name="preview" checked="1" value="1">Preview<br>
<input type="submit"><hr>


<?php
    
if(empty($parentskus) || empty($skuprefixs)) {
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
    "variations" => $colors,
    "qtys" => $qtys, 
    "prices" => $prices,
    "images" => $images,
    "resetimages" => $resetimages
    );

massAddChildProduct($accessToken, $data, $preview);

?>

<script type="text/javascript">
</script>
</div>
</body>
</html>