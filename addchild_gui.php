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

preg_match('/(.+__.+__)/', $sku, $match);
$initskuprefix = count($match) ? $match[1] : "";
$skuprefix = isset($_POST['skuprefix']) ? $_POST['skuprefix'] : $initskuprefix;
$appendtime = isset($_POST['appendtime']) ? $_POST['appendtime'] : 0;
$newName = isset($_REQUEST['name']) ? $_REQUEST['name'] : "";
$attrNames = getProductAttributeNames(FALSE);

?>

<body>
     <form action="addchild.php" method="POST" target="responseIframe">

Parent SKU: <input type="text" name="sku" size="80" value="<?php echo $sku?>"><br>
Child SKU prefix: <input type="text" name="skuprefix" size="50" value="<?php echo $skuprefix?>"><br> 
New child name: <input type="text" name="name" size="80" value="<?php echo $newName?>"><br>
<br>
Possible Attributes : <?php echo implode(",", $attrNames)?><br><br>
<table>
    <tbody>
        <tr>
            <td>Kiod ID</td>
            <td>
                <select name="attr[]">
                  <option value="color_family">color_family</option>
                  <option value="compatibility_by_model" selected>compatibility_by_model</option>
                  <option value="Variation">Variation</option>
                  <option value="type_screen_guard">type_screen_guard</option>
                  <option value="smartwear_size">smartwear_size</option>
                </select>
            </td>
            <td>
                <select name="attr[]">
                  <option value="color_family" selected>color_family</option>
                  <option value="compatibility_by_model" >compatibility_by_model</option>
                  <option value="Variation">Variation</option>
                  <option value="type_screen_guard">type_screen_guard</option>
                  <option value="smartwear_size">smartwear_size</option>
                </select>
            </td>
            <td>Quantity</td>
            <td>Price</td>
            <td>Image links</td>
        </tr>
        <tr>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="10"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="30"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="30"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="10"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="15"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="80"></textarea></td>
        </tr>
    </tbody>
</table>
<input type="checkbox" name="preview" checked="1" value="1">Preview<br>
<input type="submit"><hr>

<iframe id="responseIframe" name="responseIframe" width="1000" height="1000"></iframe>

<script type="text/javascript">
</script>
</div>
</body>
</html>