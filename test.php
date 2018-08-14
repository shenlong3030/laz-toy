<?php
include_once "check_token.php";
require_once('_main_functions.php');

//include_once "src/show_errors.php";

function test1() {
    echo __FUNCTION__;
    myecho("asd", __FUNCTION__);
}

test1();

?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo "LAZADA sp"; ?></title>
    
    <!-- bootstrap CSS file -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
    
    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    
    <!-- bxSlider Javascript file -->
    <script src="./js/controls.js"></script>
    <script src="./js/jquery.tablesorter.min.js"></script>
    
    <!-- bootstrap JS file -->
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    
    <input type="checkbox" id="412r12" checked data-toggle="toggle" data-on="Active" data-off="Inactive">


<script type="text/javascript">
    $('input[type=checkbox][data-toggle=toggle]').change(function() {
        if(this.checked) {
            console.log('active');
        } else {
            console.log('inactive');
        }
    });
</script>
</div>
</body>
</html>