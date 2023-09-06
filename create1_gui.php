<?php
include_once "check_token.php";
include_once "src/show_errors.php";
require_once('_main_functions.php');


?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CREATE PRODUCTS</title>
    <?php include('src/head.php');?>
</head>

<body>
    <h1>Create products</h1>

<table>
    <tbody>
        <tr>
            <td>Parent SKU</td>
            <td>Product data<br><span style="background: lightgreen;"> name;new sku;model;color;qty;price;image1 image2 ...</span></td>

        </tr>
        <tr>
            <td><textarea class="nowrap" id="parent_skus" rows="18" cols="60"></textarea></td>
            <td><textarea class="nowrap" id="product_data" rows="18" cols="60"></textarea></td>

        </tr>
    </tbody>
</table>
<hr>
<button id="btn_fixOL">Fix ốp lưng: variation1=color, variation2=model</button><br>
<button id="btn_create">Create</button><br>
<button id="btn_delParent">Delete parent</button>
<hr>


<iframe id="responseIframe" name="responseIframe" width="1000" height="1000"></iframe>

</div>
</body>

<script type="text/javascript">
    //######### AJAX QUEUE ######################################################################################
    ajaxManager = getAjaxManager();
    ajaxManager.run(); 

    //##########################################################################################################
    
    $("#btn_create").click(function() {
        var myFrame = $("#responseIframe").contents().find('body'); 
        var d = new Date();
        var n = d.toLocaleTimeString();
        myFrame.prepend('### ' + n + ' CREATING ###################################################<hr>');

        var parent_skus = $('#parent_skus').val().split('\n');
        var lines = $('#product_data').val().split('\n');

        //lines = lines.filter(function(e){return e}); //remove empty

        for(let i=0; i<lines.length; i++){
            productCreateWithAjaxQueue({parent_sku: parent_skus[i], product_data:lines[i]});
        }
    });

    $("#btn_fixOL").click(function() {
        var myFrame = $("#responseIframe").contents().find('body'); 
        var d = new Date();
        var n = d.toLocaleTimeString();
        myFrame.prepend('### ' + n + ' FIX OP LUNG ###################################################<hr>');

        var parent_skus = $('#parent_skus').val().split('\n');
        parent_skus = parent_skus.filter(function(e){return e}); //remove empty

        do {
            var set10 = parent_skus.slice(0, 10); // get 10 left
            parent_skus = parent_skus.slice(10);        // renove 10 left 
            var skus = set10.join(',');     // create string sku,sku,sku

            productUpdateWithAjaxQueue({ skus: skus, action: "massFixOL"});
        } while(parent_skus.length);
    });

    $("#btn_delParent").click(function() {
        var myFrame = $("#responseIframe").contents().find('body'); 
        var d = new Date();
        var n = d.toLocaleTimeString();
        myFrame.prepend('### ' + n + ' DELETING ###################################################<hr>');

        var parent_skus = $('#parent_skus').val().split('\n');

        for(let i=0; i<parent_skus.length; i++){
            productDeleteWithAjaxQueue({delete_sku: parent_skus[i]});
        }
    });
</script>

</html>