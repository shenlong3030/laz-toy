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
    <title>MASS CLONE</title>
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

$input = val($_POST['col'][0]);
$srcSkus = array_filter(explode("\n", str_replace("\r", "", $input)));

$input = val($_POST['col'][1]);
$newSkus = array_filter(explode("\n", str_replace("\r", "", $input)));

$delsource = val($_POST['delsource']);
?>

<body>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
        <table>
            <tbody>
                <tr>
                    <td>Source SKUs</td>
                    <td>New SKUs (optional), if leave empty, new sku will be {sourcesku.V{number}}</td>
                </tr>
                <tr>
                    <td><textarea name="col[]" rows="20" cols="50"><?php echo implode("\n", $srcSkus);?></textarea></td>
                    <td><textarea name="col[]" rows="20" cols="50"><?php echo implode("\n", $newSkus);?></textarea></td>
                </tr>
            </tbody>
        </table>
        <input type="checkbox" name="delsource" value="1">Delete source SKUs after cloning<br>
        <br><br>
        <input type="submit"><hr>
    </form>
<?php

if(!empty($srcSkus)) {
    $dict = massCloneProduct($accessToken, $srcSkus, $newSkus, $delsource);
} else {
    echo "<h3>Please input source SKUs</h3>";   
}

?>

<script type="text/javascript">
</script>
</div>
</body>
</html>