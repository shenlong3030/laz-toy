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
    <title>MASS UPDATE</title>
    <link href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <!-- bxSlider Javascript file -->
    <script src="./js/controls.js"></script>
    <script src="./js/jquery.tablesorter.min.js"></script>
    
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>
    <form action="massupdate.php" method="POST" target="responseIframe">
<table>
    <tr>
     <th>SKUs</th>
     <th>Names</th>
     <th>Models</th>
     <th>Colors</th>
     <th>Prices</th>
     <th>Images >> Start index (1-8) <input size="3" type="text" name="imageindex" value="0"></th>
     <th>Actives</th>
    </tr>
    <tbody>
        <tr>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="30"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="50"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="10"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="10"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="10"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="50"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="5"></textarea></td>
        </tr>
    </tbody>
</table>
<br><br>

<input type="checkbox" name="preview" checked="1" value="1">Preview<br>
<input type="submit">
<hr>

<iframe id="responseIframe" name="responseIframe" width="1000" height="1000"></iframe>

</div>
</body>
</html>


