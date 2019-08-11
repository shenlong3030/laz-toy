<?php
include_once "check_token.php";
require_once('_main_functions.php');
//include_once "src/show_errors.php";

// get code param
$input = isset($_POST['skus']) ? $_POST['skus'] : '';
$skus = array_filter(explode("\n", str_replace("\r", "", $input)));
$options = isset($_POST['options']) ? $_POST['options'] : '';
?>

<!DOCTYPE html>
<html leng="en-AU">
<head><meta http-equiv="Content-Type" content="text/html; charset=shift_jis">
    
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FIX products</title>
    <link rel="shortcut icon" type="image/x-icon" href="./ico/tool.ico" />
    <link href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <!-- bxSlider Javascript file -->
    <script src="./js/controls.js"></script>
    <script src="./js/jquery.tablesorter.min.js"></script>

    <style type="text/css">
        a.menu { padding-left: 4em; }
        td.order { padding-left: 1em; padding-right: 1em;}
        span.count {color: red;}
    </style>
</head>
<body>
    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']?>">
        FIX options:<br>
        <input type="checkbox" name="options[]" value="1">Variation<br>
        <input type="checkbox" name="options[]" value="2">...<br>
        <br><br>
        To SKUs<br><textarea name="skus" rows="10" cols="80"><?php echo implode("\n", $skus);?></textarea><br>
        <input type="submit" value="Submit">
    </form>
    <hr>

<?php

//var_dump($_POST);

if(count($skus)) {
    if(empty($options)) {
        echo "<h1>Please select options</h1>";
    } else {
        fixProducts($accessToken, $skus, $options);
    }
}
?>


</body>
</html>

