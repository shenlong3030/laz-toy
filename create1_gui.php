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
    <title>CREATE</title>
    <link href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <!-- bxSlider Javascript file -->
    <script src="./js/controls.js"></script>
    <script src="./js/jquery.tablesorter.min.js"></script>
    
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>
    <h1>Create products</h1>
    <form action="create1.php" method="POST" target="responseIframe">

<table>
    <tbody>
        <tr>
            <td>Parent SKU</td>
            <td>Source SKU</td>
            <td >SKU prefix</td>
            <td bgcolor="yellow">Kiot id</td>
            <td bgcolor="yellow">Name</td>
            <td bgcolor="yellow">Group</td>
            <td>Model</td>
            <td>Color</td>
            <td bgcolor="yellow">Qty</td>
            <td>Price</td>
            <td>Images <input style="padding-left: 10px" type="checkbox" name="resetimages" value="1">Remove all source's images</td>
        </tr>
        <tr>
            <td><textarea class="nowrap" name="col[]" rows="30" cols="20"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="30" cols="20"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="30" cols="20"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="30" cols="10"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="30" cols="60"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="30" cols="20"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="30" cols="10"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="30" cols="10"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="30" cols="5"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="30" cols="5"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="30" cols="80" style="white-space: nowrap;"></textarea></td>
        </tr>
    </tbody>
</table>
<input type="checkbox" name="preview" checked="1" value="1">Preview<br>
<input type="checkbox" name="makegroup" <?php if($makegroup) {echo 'checked="1"';}?> value="1">Make group<br>
<input type="submit"><hr>

<iframe id="responseIframe" name="responseIframe" width="1000" height="1000"></iframe>

</div>
</body>
</html>