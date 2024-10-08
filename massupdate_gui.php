<?php
//include_once "src/show_errors.php";
include_once "check_token.php";
require_once('_main_functions.php');

$itemId = val($_REQUEST['item_id']);
$skus = val($_REQUEST['skus']);
$skuids = val($_REQUEST['skuids']);
// $models = val($_REQUEST['models']);
// $colors = val($_REQUEST['colors']);
$saleprop1 = val($_REQUEST['saleprop1']);
$saleprop2 = val($_REQUEST['saleprop2']);

$prices = val($_REQUEST['prices']);
$skuImages = val($_REQUEST['sku_images']);

$salPropKey1 = val($_REQUEST["salPropKey1"]);
$salPropKey2 = val($_REQUEST["salPropKey2"]);

?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MASS UPDATE</title>
    
    <?php include('src/head.php');?>
</head>

<body>
    <input type="hidden" id="item_id" name="item_id" value="<?php echo $itemId;?>">
<table>
    <tr>
     <th>SKUs</th>
     <th>SkuIds</th>
     <th>Names</th>
     <th>
        Variation1 <select class="saleprop_key" name="attr[]">
          <option value="color_family">color_family</option>
          <option value="compatibility_by_model" selected>compatibility_by_model</option>
          <option value="Variation">Variation</option>
          <option value="type_screen_guard">type_screen_guard</option>
          <option value="smartwear_size">smartwear_size</option>
        </select>
     </th>
     <th>
        Variation2<select class="saleprop_key" name="attr[]">
          <option value="color_family" selected>color_family</option>
          <option value="compatibility_by_model" >compatibility_by_model</option>
          <option value="Variation">Variation</option>
          <option value="type_screen_guard">type_screen_guard</option>
          <option value="smartwear_size">smartwear_size</option>
        </select>
     </th>
     <th>Prices</th>
     <th>Product Images</th>
     <th>SKU First Images</th>
     <th>SKU Images</th>
     <th>Actives</th>
    </tr>
    <tbody>
        <tr>
            <td><span class="linecount"></span><br><textarea class="nowrap" id="txt_skus" rows="20" cols="60"><?php echo $skus;?></textarea></td>
            <td><span class="linecount"></span><br><textarea class="nowrap" id="txt_skuids" rows="20" cols="9"><?php echo $skuids;?></textarea></td>
            <td><span class="linecount"></span><br><textarea class="nowrap" id="txt_names" rows="20" cols="50"></textarea></td>
            <td><span class="linecount"></span><br><textarea class="nowrap" id="txt_saleprop1s" rows="20" cols="20"><?php echo $saleprop1;?></textarea></td>
            <td><span class="linecount"></span><br><textarea class="nowrap" id="txt_saleprop2s" rows="20" cols="20"><?php echo $saleprop2;?></textarea></td>
            <td><span class="linecount"></span><br><textarea class="nowrap" id="txt_prices" rows="20" cols="10"><?php echo $prices;?></textarea></td>
            <td><span class="linecount"></span><br><textarea class="nowrap" id="txt_product_images" rows="20" cols="20"><?php echo $productImages;?></textarea></td>
            <td><span class="linecount"></span><br><textarea class="nowrap" id="txt_sku_main_images" rows="20" cols="20"></textarea></td>
            <td><span class="linecount"></span><br><textarea class="nowrap" id="txt_sku_images" rows="20" cols="20"><?php echo $skuImages;?></textarea></td>
            <td><span class="linecount"></span><br><textarea class="nowrap" name="col[]" rows="20" cols="5"></textarea></td>
        </tr>
    </tbody>
</table>
<br>
<button id="btn_qty500">Qty =500</button><br>
<button id="btn_updatePrices">Sale prices = </button><input id="sprice" type="text" name="sprice"><br>
<button id="btn_update">Update names,models</button><br>
<br><a target="_blank" 
        href="api_update.php?action=remove_saleprop&item_id=<?php echo $itemId?>&variation1=compatibility_by_model" 
        class="fa fa-trash" style="color:green" tabindex="-1">Remove compatibility_by_model</a>
<br>
<hr>

<iframe id="responseIframe" name="responseIframe" width="1000" height="1000"></iframe>

</div>
</body>

<script type="text/javascript">
    $(".nowrap").on('focusout', function() {
        var lines = $(this).val().split("\n");  
        $(this).prev().prev().text(lines.length);
    });
    //######### AJAX QUEUE ######################################################################################
    ajaxManager = getAjaxManager();
    ajaxManager.run(); 
    
    //##########################################################################################################
    $(".saleprop_key").first().val("<?php echo $salPropKey1 ?>");
    $(".saleprop_key").last().val("<?php echo $salPropKey2 ?>");

    $("#btn_qty500").click(function() {
        var myFrame = $("#responseIframe").contents().find('body'); 
        var d = new Date();
        var n = d.toLocaleTimeString();
        myFrame.prepend('### ' + n + ' QTY=500 ###################################################<hr>');

        var lines = $('#txt_skuids').val().replaceAll("#N/A","").split('\n');
        lines = lines.filter(function(e){return e}); //remove empty

        do {
            var set10 = lines.slice(0, 10); // get 10 left
            lines = lines.slice(10);        // renove 10 left 
            var skuids = set10.join('\n');     // create string sku,sku,sku

            productUpdateWithAjaxQueue({ skuids: skuids, action: "massQty", qty: 500});
        } while(lines.length);
    });

    $("#btn_updatePrices").click(function() {
        var myFrame = $("#responseIframe").contents().find('body'); 
        var d = new Date();
        var n = d.toLocaleTimeString();
        myFrame.prepend('### ' + n + ' UDPATE PRICES ###################################################<hr>');

        var lines = $('#txt_skuids').val().replaceAll("#N/A","").split('\n');
        lines = lines.filter(function(e){return e}); //remove empty
        var sprice = $('#sprice').val();

        do {
            var set10 = lines.slice(0, 10); // get 10 left
            lines = lines.slice(10);        // renove 10 left 
            var skuids = set10.join('\n');     // create string sku,sku,sku

            productUpdateWithAjaxQueue({ skuids: skuids, action: "massPrice", sprice: sprice});
        } while(lines.length);
    });
    
    $("#btn_update").click(function() {
        var myFrame = $("#responseIframe").contents().find('body'); 
        var d = new Date();
        var n = d.toLocaleTimeString();
        myFrame.prepend('### ' + n + ' UDPATE Name, models, colors ###################################################<hr>');

        var lines = $('#txt_skuids').val().split('\n');       
        var skus = $('#txt_skus').val().split('\n');       
        var names = $('#txt_names').val().split('\n');
        var saleprop1s = $('#txt_saleprop1s').val().split('\n');
        var saleprop2s = $('#txt_saleprop2s').val().split('\n');

        var salPropKey1 = $(".saleprop_key").first().val();
        var salPropKey2 = $(".saleprop_key").last().val();

        var prices = $('#txt_prices').val().split('\n');
        var skuMainImages = $('#txt_sku_main_images').val().split('\n');
        var skuImages = $('#txt_sku_images').val().split('\n');
        var pImages = $('#txt_product_images').val().split('\n');

        var chunksize = 10;
        do {
            var set10 = lines.slice(0, chunksize); // get 10 left
            lines = lines.slice(chunksize);        // renove 10 left 
            var skuids = set10.join('\n');     // create string sku,sku,sku

            set10 = skus.slice(0, chunksize); // get 10 left
            skus = skus.slice(chunksize);        // renove 10 left 
            var paramSkus = set10.join('\n');     // create string sku,sku,sku

            set10 = names.slice(0, chunksize); // get 10 left
            names = names.slice(chunksize);        // renove 10 left 
            var paramNames = set10.join('\n');     // create string sku,sku,sku

            set10 = saleprop1s.slice(0, chunksize); // get 10 left
            saleprop1s = saleprop1s.slice(chunksize);        // renove 10 left 
            var paramSaleprop1s = set10.join('\n');     // create string

            set10 = saleprop2s.slice(0, chunksize); // get 10 left
            saleprop2s = saleprop2s.slice(chunksize);        // renove 10 left 
            var paramSaleprop2s = set10.join('\n');     // create string 

            set10 = prices.slice(0, chunksize); // get 10 left
            prices = prices.slice(chunksize);        // renove 10 left 
            var paramPrices = set10.join('\n');     // create string 

            set10 = skuMainImages.slice(0, chunksize); // get 10 left
            skuMainImages = skuMainImages.slice(chunksize);        // renove 10 left 
            var paramSkuMainImages = set10.join('\n');     // create string 

            set10 = skuImages.slice(0, chunksize); // get 10 left
            skuImages = skuImages.slice(chunksize);        // renove 10 left 
            var paramSkuImages = set10.join('\n');     // create string 

            set10 = pImages.slice(0, chunksize); // get 10 left
            pImages = pImages.slice(chunksize);        // renove 10 left 
            var paramProductImages = set10.join('\n');     // create string 

            productUpdateWithAjaxQueue({
                skuids: skuids,
                skus: paramSkus,
                action: "massUpdate", 
                mass_names: paramNames, 
                mass_saleprop1s: paramSaleprop1s, 
                mass_saleprop2s: paramSaleprop2s,
                salPropKey1: salPropKey1,
                salPropKey2: salPropKey2,
                mass_prices: paramPrices,
                mass_sku_images: paramSkuImages,
                mass_sku_main_images: paramSkuMainImages,
                mass_product_images: paramProductImages
            });
        } while(skus.length);
    });


</script>

</html>


