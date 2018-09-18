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
    <title>SHOP TO SHOP</title>
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

$dest_token = val($_REQUEST['dest_token']);
$sku_prefix = val($_REQUEST['sku_prefix']);

$input = val($_POST['col'][0]);
$srcSkus = array_filter(explode("\n", str_replace("\r", "", $input)));

?>

<body>
<h1>
Clone products from SHOP to SHOP
</h1>
<form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
    Destination shop token: <input type="text" name="dest_token" value="<?php echo $dest_token; ?>"><br>
    SKU prefix: <input type="text" name="sku_prefix" value="SH01"><br>
    <table>
        <tbody>
            <tr>
                <td>Source SKUs</td>
            </tr>
            <tr>
                <td><textarea name="col[]" rows="20" cols="50"><?php echo implode("\n", $srcSkus);?></textarea></td>
            </tr>
        </tbody>
    </table>

    <br><br>

    <input type="submit"><hr>
</form>

<?php
$src_token = $GLOBALS["accessToken"];

if(!empty($srcSkus) && !empty($dest_token) && !empty($sku_prefix)) {
    if($src_token == $dest_token) {
        echo "<h3>Can not execute because SOURCE and DESTINATION are same SHOP</h3>";
        echo "SOURCE token: <br>$src_token<br><br>"; 
        echo "DESTINATION token: <br>$dest_token<br>"; 
    } else {
        $dict = massCloneToShop($accessToken, $srcSkus, $dest_token, $sku_prefix);
    }
} else {
    echo "<h3>Please input destionation token AND sku prefix AND source skus</h3>";   
}

?>

<script type="text/javascript">
</script>
</div>
</body>
</html>