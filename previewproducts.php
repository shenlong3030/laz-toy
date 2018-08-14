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

$input = val($_POST['col'][0]);
$skus = array_filter(explode("\n", str_replace("\r", "", $input)));
?>

<body>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
<table>
    <tr>
     <th>Names</th>
     <th>Images</th>
    </tr>
    <tbody>
        <tr>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="30"><?php echo implode("\n", $skus);?></textarea></td>
        </tr>
    </tbody>
</table>
<br><br>
<input type="submit"><hr>
</form>
<?php

foreach($names as $i => $name) {
    if(isset($images[$i])) {
        // split images
        $list = preg_split("/\s+/", $images[$i]);
        //var_dump($list);

        echo "<br>";
        echo "$name", htmlLinkImages($list, 150, 150);
        echo "<br>";
    }
}

?>

<script type="text/javascript">
</script>
</div>
</body>
</html>