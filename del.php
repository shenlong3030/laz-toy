<?php
include_once "check_token.php";
require_once('_main_functions.php');

//include_once "src/show_errors.php";

date_default_timezone_set("UTC");

$delchildren = val($_POST['delchildren']);
$input = isset($_POST['skus']) ? $_POST['skus'] : "";
$skus = array_filter(explode("\n", str_replace("\r", "", $input)));

?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DEL</title>
    <link href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <!-- bxSlider Javascript file -->
    <script src="./js/controls.js"></script>
    <script src="./js/jquery.tablesorter.min.js"></script>
    
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <h1>Remove lazada product</h1>
    <iframe id="responseIframe" name="responseIframe" width="600" height="30"></iframe>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
SKUs (separated by line):<br>
    <textarea name="skus" rows="20" cols="80"><?php echo implode("\n", $skus);?></textarea><br><br>
    <input type="checkbox" name="delchildren" value="1">Del children<br>
    <input type="submit"><br><hr>

<?php

// Pay no attention to this statement.
// It's only needed if timezone in php.ini is not set correctly.

if($skus && count($skus)) {
    delProducts($accessToken, $skus, $delchildren);
} else {
    echo "<br><br>Please input SKUs, separated by line<br><br>";   
}
?>

<script type="text/javascript">
</script>
</div>
</body>
</html>