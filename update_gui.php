<?php
//require_once('src/show_errors.php');
include_once "check_token.php";
require_once('_main_functions.php');


$skuFull = val($_REQUEST["sku"]); //{$sku}~{$skuid}~{$itemId}
$arr = explode("~", $skuFull);
$sku = $arr[0];
$skuid = $arr[1];
$itemId = val($_REQUEST["item_id"], $arr[2]);

$category = "";
$productImages = array();
$images = array();
$weight = "";
$h = "";
$w = "";
$l = "";
$content = "";
$shortdesc = "";
$desc = "";
$qty = "";
$price = "";
$sprice = "";
$fromdate = "";
$todate = "";
$name = "";
$variation = "";
$brand = "";
$color_thumbnail = "";
$type_screen_guard = "";
$variations = array();
$variationValues = array();

if($itemId) {
    $product = getProduct($accessToken, null, $itemId);
    $sibling = $product;
    
    debug_log($product);

    if($product) {
        $i = getProductSkuIndex($product, $sku);
        $images = array_filter($product['skus'][$i]['Images']);

        $productImages = $product['images'];
        
        $category = $product['primary_category'];
        $weight = $product['skus'][$i]['package_weight'];
        $h = $product['skus'][$i]['package_height'];
        $w = $product['skus'][$i]['package_width'];
        $l = $product['skus'][$i]['package_length'];
        $content = $product['skus'][$i]['package_content'];
        $qty = $product['skus'][$i]['quantity'];
        
        // $variation = $product['skus'][$i]['saleProp']['Variation'];
        // $type_screen_guard = $product['skus'][$i]['saleProp']['type_screen_guard'];
        // $color = $product['skus'][$i]['saleProp']['color_family'];
        // $color_thumbnail = $product['skus'][$i]['color_thumbnail'];
        // $model = $product['skus'][$i]['saleProp']['compatibility_by_model'];

        foreach($product['skus'][$i]['saleProp'] as $key => $value)
        {
            $variations[] = $key;
            $variationValues[] = $value;
        }
        
        $price = $product['skus'][$i]['price'];
        $sprice = $product['skus'][$i]['special_price'];
        $fromdate = $product['skus'][$i]['special_from_date'];
        $todate = $product['skus'][$i]['special_to_date'];
        $skuid = $product['skus'][$i]['SkuId'];
        
        $name = $product['attributes']['name'];
        $shortdesc = $product['attributes']['short_description'];
        $desc = $product['attributes']['description'];
        $brand = $product['attributes']['brand'];
        $video = $product['attributes']['video'];

        $status = isProductActive($product, $sku) ? "checked" : "";
    } else {
        echo "INVALID ID";
    }
}

$cloneLink = "https://$_SERVER[HTTP_HOST]/lazop/copy_product_all_skus.php?sku={$sku}~{$skuid}~{$itemId}";
$copyToLink = "https://$_SERVER[HTTP_HOST]/lazop/copyinfo.php?sourcesku=$sku";
$copyFromCLMau = "https://$_SERVER[HTTP_HOST]/lazop/copy_product_all_skus.php?sku=CL.ALL__MAU.XX__KT~~1980685997&parent_sku={$sku}~{$skuid}~{$itemId}";
$copyFromCLMau2 = "https://$_SERVER[HTTP_HOST]/lazop/copy_product_all_skus2.php?sku=CL.ALL__MAU.XX__KT~~1980685997&parent_sku={$sku}~{$skuid}~{$itemId}";
?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UPDATE</title>

    <?php include('src/head.php');?>

    <style>
    .mainContent{
      margin-top: 50px;
    }
    </style>

</head>
<body>
    <div class="floating-bar">
    <iframe id="responseIframe" name="responseIframe" width="900" height="60"></iframe>
    </div>
    
    <div class="mainContent">

<?php if($sibling) { ?>
    <div style="max-height:500px;overflow:auto">
    <h2>Danh sách các SP cùng nhóm này</h2>
    <button id="btn_copy_all">Copy All Clipboard</button>
    <button id="btn_copy_sku">Copy SKUs</button>
    <button id="btn_copy_url">Copy LAZADA urls</button>
    <button id="btn_del">DEL</button>
    <button id="btn_edit_price">Edit price</button>
    <button id="btn_copy_variation1">Copy variation1</button>
    <button id="btn_copy_variation2">Copy variation2</button>
    
    <div class="fa-trash" style="display:none">
    <br>Remove variation1=<?php echo $variations[0]?><a target="_blank" 
        href="api_update.php?action=remove_saleprop&item_id=<?php echo $itemId?>&variation1=<?php echo $variations[0]?>" 
        class="fa fa-trash" style="color:green;display:none" tabindex="-1"></a>

    <br>Remove variation2=<?php echo $variations[1]?><a target="_blank" 
        href="api_update.php?action=remove_saleprop&item_id=<?php echo $itemId?>&variation1=<?php echo $variations[1]?>" 
        class="fa fa-trash" style="color:green;display:none" tabindex="-1"></a>
    </div>

    <?php echo printProducts(array($sibling), false, $sku);?>
    </div>
<?php } ?>

<hr>
    <h1>Update product</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
    SKU: <input type="text" id="sku" name="sku" size="70" value="<?php echo "{$sku}~{$skuid}~{$itemId}"?>"/><br>
    SKU id: <input type="text" id="skuid" name="skuid" value="<?php echo $skuid;?>"><br>
    Item id <input type="text" id="item_id" name="item_id" value="<?php echo $itemId;?>">
    <input type="submit" value="Reload"/>
    <input id="btn_change_sku" type="button" value="Change SKU"/>
    </form>

    <a id="linkAddChild" style="color:red" href="">Add Child</a>
    <a id="linkMassUpdate" style="color:red;padding-left: 20px" href="">Mass Update</a>
    <a style="color:red;padding-left: 20px" href="<?php echo $copyToLink?>" target="_blank">Copy info to</a>
    <a style="color:red;padding-left: 20px" href="<?php echo $cloneLink?>" target="_blank">Copy to</a>
    <a id="linkMoveTo" style="color:red;padding-left: 20px" href="#">Move to</a>
    <a style="color:red;padding-left: 20px" href="<?php echo $copyFromCLMau?>" target="_blank">Copy from CL mẫu</a>
    <a id="link_copy_from_cl_mau2" style="color:red;padding-left: 20px" href="<?php echo $copyFromCLMau2?>" target="_blank">Copy from CL mẫu 2</a>

    <br><a id="linkMoveProduct" style="color:red;padding-left: 20px" href="#">Move to</a>
    <input id="des_sku" type="text" name="des_sku" size="60"/>
    Keep parent name<input type="checkbox" id="keepParentName" name="keepParentName" checked>
<hr>

<input id="<?php echo $sku;?>" type="checkbox" data-toggle="toggle" <?php echo $status;?>>

<hr>
    <div>
    Name <input id="name" type="text" name="name" size="80" value="<?php echo $name;?>" />
    <button id="btn_updatename">Update name</button>
    </div>
<hr>
    <div>
    Price <input type="text" name="price" value="<?php echo $price;?>" /> >> 
    <input type="text" name="sale_price" value="<?php echo $sprice;?>" />
    From <input type="text" name="fromdate" value="<?php echo $fromdate;?>" />
    To <input type="text" name="todate" value="<?php echo $todate;?>" /><br>
    <button id="btn_updateprice">Update Price</button>
    </div>
<hr>
    <div>
    <table>
    <tbody>
        <tr>
            <td>Variations</td>
            <td>Variation values</td>
        </tr>
        <tr>
            <td><textarea class="nowrap" name="variations" rows="3" cols="20"><?php echo implode("\n", $variations);?></textarea></td>
            <td><textarea class="nowrap" name="variation_values" rows="3" cols="30"><?php echo implode("\n", $variationValues);?></textarea>
            </td>
            <td><button id="btn_updateattr">Update</button></td>
        </tr>
    </tbody>
    </table>
    Valid Variations: color_family, Variation, compatibility_by_model, type_screen_guard<br>
    </div>
<hr>
    Brand <input type="text" name="brand" value="<?php echo $brand;?>" />
    <button id="btn_update_brand">Update</button>
    </form>
<hr>
    <div>
    Primary category <input type="text" name="category" value="<?php echo $category;?>" />
    <button id="btn_updatecategory">Update</button>
    </div>
    <br>
    <ul>
        <li>4528 : miếng dán</li>
        <li>4523 : ốp lưng</li>
        <li>11029 : cáp sạc</li>
        <li>10100418 : miếng dán đồng hồ</li>
    </ul>
<hr>
    <div>
    <h3>Product Images</h3> <textarea style="background:lightyellow;" id="productimages" class="nowrap" name="product_images" rows="6" cols="80"><?php echo implode("\n", $productImages);?></textarea>
    <a title="Editor" href="https://saohoa.phukiensh.com/wp-admin/upload.php" target="_blank" rel="noopener">saohoa.phukiensh</a> - 
    <a href="https://sellercenter.lazada.vn/apps/product/publish?productId=100886290" target="_blank" rel="noopener">laz</a>
    <button id="btn_updateProductImages">Update product images</button>
    </div>
<?php
    foreach($productImages as $image) {
        echo htmlLinkImage($image);
    }
?>
<hr>
    <div>
    <h3>SKU Images</h3> <textarea id="skuimages" class="nowrap" name="images" rows="6" cols="80"><?php echo implode("\n", $images);?></textarea>
    <a title="Editor" href="https://saohoa.phukiensh.com/wp-admin/upload.php" target="_blank" rel="noopener">saohoa.phukiensh</a> - 
    <a href="https://sellercenter.lazada.vn/apps/product/publish?productId=100886290" target="_blank" rel="noopener">laz</a>
    <button id="btn_updateimages">Update images</button>
    <input type="button" id="btn_update_children" value="Update all children"/>
    <input type="button" id="btn_copy_single_line" value="Copy images single line"/>
    </div>
<?php
    foreach($images as $image) {
        echo htmlLinkImage($image);
    }
?>
<hr>
    <form action="update.php" method="POST" name="videoForm" target="responseIframe">
    <input type="hidden" name="sku" value="<?php echo $sku;?>">
    Video <input type="text" name="video" size="70" value="<?php echo $video;?>" />
    <input type="submit" value="Update video link"/>
    </form>

<hr>
    <div>
    Weight: <input type="text" name="weight" size="5" value="<?php echo $weight;?>"/> kg<br>
    Packing Size: <input type="text" name="size_h" size="10" value="<?php echo $h;?>" /> 
    x <input type="text" name="size_w" size="10" value="<?php echo $w;?>" /> 
    x <input type="text" name="size_l" size="10" value="<?php echo $l;?>" /> cm<br>
    Packing Content: <input type="text" name="content" size="70" value="<?php echo $content;?>" /><br>
    
    <button id="btn_updateweight">Update weight, size, content</button>
    </div>
<hr>
    <h3>Short description:</h3>
    <form action="update.php" method="POST" name="shortdescForm" target="responseIframe">
    <input type="hidden" name="sku" value="<?php echo $sku;?>" />
    <textarea class="nowrap" name="shortdesc" rows="2" cols="80"><?php echo $shortdesc;?></textarea>
    <a title="Editor" href="https://html5-editor.net/" target="_blank" rel="noopener">Editor</a>
    <input type="submit" value="Update short description"/>
    </form>
    
<hr> 
    <div>
    <h3>Description:</h3>
    <textarea class="nowrap" name="desc" rows="2" cols="80"><?php echo $desc;?></textarea>
    <a title="Editor" href="https://html5-editor.net/" target="_blank" rel="noopener">Editor</a>
    <button id="btn_updatedesc">Update description + short desc</button>
    </div>
    
    <div>
    <?php echo $desc;?>
    </div>

<hr>
    <textarea id="txt_json" rows="90" cols="180"><?php echo json_encode($product,JSON_PRETTY_PRINT);?></textarea>
<hr>

    <a target="_blank" href="del.php?skus=<?php echo "{$sku}~{$skuid}~{$itemId}"?>" class="fa fa-trash" style="color: green; display: inline-block;" tabindex="-1"></a>
<?php

// Pay no attention to this statement.
// It's only needed if timezone in php.ini is not set correctly.
date_default_timezone_set("UTC");

?>

<script type="text/javascript">

    //######### AJAX QUEUE ######################################################################################
    ajaxManager = getAjaxManager();
    ajaxManager.run(); 
    
    //##########################################################################################################
    
    //### Handle JS action in SKUs list
    $("button[name='btn_child_qty']").click(function() {
        var skuid = $(this).parent().find('input[name=child_skuid]').val();
        var qty = $(this).attr("value");
        var reservedStock = $(this).closest('td').find('.reservedStock').text(); 

        if(qty == 0 && reservedStock){
            qty = reservedStock;
        }

        $(this).parent().find('input[name=child_qty]').val(qty); 

        productUpdateWithAjaxQueue({ skuid: skuid, action: "qty", qty: qty});
    });
    $('input[name=child_qty]').keypress(function(event){
      var keycode = (event.keyCode ? event.keyCode : event.which);
      if(keycode == '13'){ // press ENTER
          var skuid = $(this).parent().find('input[name=child_skuid]').val(); 
          var q = $(this).parent().find('input[name=child_qty]').val(); 
          productUpdateWithAjaxQueue({ skuid: skuid, action: "qty", qty: q});
      }
      //event.stopPropagation();
    });

    $(".child_price_input").keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){ // press ENTER
          var skuid = $(this).parent().find('input[name=child_skuid]').val(); 
          var price = $(this).parent().find('input[name="child_price"]').val(); 
          var sprice = $(this).parent().find('input[name="child_sprice"]').val(); 
          productUpdateWithAjaxQueue({ skuid: skuid, action: "price", price: price, sprice: sprice});
        }
    });

    //###########################

    //### UPDATE BRAND AJAX ###
    $('#btn_update_brand').click(function() {
        var skuid = $('#skuid').val();
        var b = $(this).parent().find('input[name="brand"]').val(); 
        productUpdateWithAjaxQueue({ skuid: skuid, action: "brand", brand: b});
    });
    //###########################

    //### UPDATE CATEGORY AJAX ###
    $('#btn_updatecategory').click(function() {
        var skuid = $('#skuid').val();
        var c = $(this).parent().find('input[name="category"]').val(); 
        productUpdateWithAjaxQueue({ skuid: skuid, action: "category", category: c});
    });
    //###########################

    //### UPDATE SKU AJAX ###
    $('#btn_change_sku').click(function() {
        var skuid = $('#skuid').val();
        var sku = $('#sku').val().split("~")[0];
        console.log(sku);
        productUpdateWithAjaxQueue({ skuid: skuid, action: "sku", sku: sku});
    });

    //### UPDATE NAME AJAX ###
    $('input[name="name"]').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){ // press ENTER
          var skuid = $('#skuid').val();
          var n = $(this).parent().find('input[name="name"]').val(); 
          productUpdateWithAjaxQueue({ skuid: skuid, action: "name", name: n});
        }
        //event.stopPropagation();
    });
    $('#btn_updatename').click(function() {
        var skuid = $('#skuid').val();
        var n = $(this).parent().find('input[name="name"]').val(); 
        productUpdateWithAjaxQueue({ skuid: skuid, action: "name", name: n});
    });
    //###########################


    //### UPDATE PRICE AJAX ###
    $('input[name="sale_price"]').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){ // press ENTER
          var skuid = $('#skuid').val();
          var sprice = $(this).parent().find('input[name="sale_price"]').val(); 
          productUpdateWithAjaxQueue({ skuid: skuid, action: "price", sprice: sprice});
        }
    });
    $('#btn_updateprice').click(function() {
        var skuid = $('#skuid').val();
        var price = $(this).parent().find('input[name="price"]').val(); 
        var sprice = $(this).parent().find('input[name="sale_price"]').val(); 
        productUpdateWithAjaxQueue({ skuid: skuid, action: "price", sprice: sprice, price: price});
    });
    //###########################


    //### UPDATE ATTRIBUTES ###
    $('#btn_updateattr').click(function() {
        var skuid = $('#skuid').val();
        // var variation = $(this).parent().find('input[name="variation"]').val(); 
        // var type_screen_guard = $(this).parent().find('input[name="type_screen_guard"]').val(); 
        // var compatibility_by_model = $(this).parent().find('input[name="compatibility_by_model"]').val(); 
        // var color_family = $(this).parent().find('input[name="color_family"]').val(); 

        var variations = $(this).closest('div').find('textarea[name="variations"]').val(); 
        var variationValues = $(this).closest('div').find('textarea[name="variation_values"]').val(); 

        productUpdateWithAjaxQueue({ skuid: skuid, action: "attr", 
            // variation: variation, 
            // type_screen_guard:type_screen_guard,
            // compatibility_by_model:compatibility_by_model,
            // color_family:color_family
            variations: variations,
            variationValues: variationValues
        });
    });
    //###########################


    //### UPDATE DESCRIPTION ###
    $('#btn_updatedesc').click(function() {
        var skuid = $('#skuid').val();
        var desc = $(this).parent().find('textarea[name="desc"]').val(); 
        productUpdateWithAjaxQueue({ skuid: skuid, action: "description", desc: desc});
    });
    //###########################

    //### UPDATE WEIGHT ###
    $('#btn_updateweight').click(function() {
        var skuid = $('#skuid').val();
        var weight = $('input[name=weight]').val(); 
        var size_w = $('input[name=size_w]').val(); 
        var size_h = $('input[name=size_h]').val(); 
        var size_l = $('input[name=size_l]').val(); 
        var content = $('input[name=content]').val(); 

        productUpdateWithAjaxQueue({ skuid: skuid, action: "weight", 
            weight: weight,
            size_w: size_w,
            size_h: size_h,
            size_l: size_l,
            content: content,
        });
    });
    //###########################


    //### UPDATE IMAGES ###
    $('#btn_updateimages').click(function() {
        var skuid = $('#skuid').val();
        var images = $(this).parent().find('textarea[name="images"]').val(); 
        productUpdateWithAjaxQueue({ skuid: skuid, action: "images", images: images});
    });

    $('#btn_updateProductImages').click(function() {
        var skuid = $('#skuid').val();
        var pimages = $(this).parent().find('textarea[name="product_images"]').val(); 
        productUpdateWithAjaxQueue({ skuid: skuid, action: "pimages", pimages: pimages});
    });
    //###########################

    $("#linkAddChild").click(function(e) {
        e.preventDefault();
        var sku = $('#sku').val();
        var n = $('#name').val(); 
        var url = `addchild_gui.php?sku=${sku}&name=${n}`;
        //window.open(url, '_blank');

        var json = $('#txt_json').val();
        var salPropKey1 = $("#tableProducts").find(".variation1").first().text();
        var salPropKey2 = $("#tableProducts").find(".variation2").first().text();
        var selectedSkuIndex = $("#tableProducts").find("tr.selected").attr("sku-index");

        dopost(url, {json_product: json, salPropKey1: salPropKey1, salPropKey2: salPropKey2, selectedSkuIndex: selectedSkuIndex});        
    });

    $("#linkMassUpdate").click(function(e) {
        e.preventDefault();

        var item_id = $("#item_id").val();
        var skus = "";
          $("#tableProducts").find("td.sku_full").each(function(){
              skus = skus + $(this).text() + "\n";
          });
        var skuids = "";
          $("#tableProducts").find("td.skuid").each(function(){
              skuids = skuids + $(this).text() + "\n";
          });

        var salPropKey1 = $("#tableProducts").find(".variation1").first().text();
        var salPropKey2 = $("#tableProducts").find(".variation2").first().text();

        var saleprop1 = "";
          $("#tableProducts").find("td.saleprop1").each(function(){
              saleprop1 = saleprop1 + $(this).text() + "\n";
          });

          var saleprop2 = "";
          $("#tableProducts").find("td.saleprop2").each(function(){
             saleprop2 = saleprop2 + $(this).text() + "\n";
          });

          var prices = "";
          $("#tableProducts").find("td.price-cell span.price.text").each(function(){
             prices = prices + $(this).text() + "\n";
          });

          // var skuImages = "";
          // $("#tableProducts").find("td.sku-image.first a").each(function(){
          //    skuImages = skuImages + $(this).attr('href') + "\n";
          // });

          var skuImages = "";
          $("#tableProducts tr").each(function(){
            $list = [];
            $(this).find("td.sku-image a").each(function(){
                $list.push($(this).attr('href'));
            });
            skuImages = skuImages + $list.join(" ") + "\n";
          });

        var url = "massupdate_gui.php";
        dopost(url, {
            skus: skus,
            skuids: skuids,
            item_id: item_id,
            saleprop1: saleprop1,
            saleprop2: saleprop2,
            salPropKey1: salPropKey1, 
            salPropKey2: salPropKey2,
            prices: prices,
            sku_images: skuImages
        });        
    }); 

    $("#linkMoveTo").click(function(e) {
        e.preventDefault();
        var text = "";
          $("#tableProducts").find("td.sku_full").each(function(){
              text = text + $(this).text() + "\n";
          });
          console.log("copy text : " + text );

        var url = "movechild.php";
        dopost(url, {
            skus: text
        });   
    }); 

    $("#link_copy_from_cl_mau2").click(function(e) {
        e.preventDefault();
        url = $(this).attr("href");

        var list = [];
        var text = "";
        $("#tableProducts").find("td.saleprop1").each(function(){
          //text = text + $(this).text() + "\n";
            var t = $(this).text().trim();
            if(list.indexOf(t) < 0) {
                list.push(t);
            }
        });
        //list.sort();
        text = list.join("\n");
        listSaleProp1 = text;

        list = [];
        text = "";
        $("#tableProducts").find("td.saleprop2").each(function(){
          //text = text + $(this).text() + "\n";
            var t = $(this).text().trim();
            if(list.indexOf(t) < 0) {
                list.push(t);
            }
        });
        //list.sort();
        text = list.join("\n");
        listSaleProp2 = text;

        var variation1 = $("#tableProducts").find(".variation1").first().text();
        var variation2 = $("#tableProducts").find(".variation2").first().text();

        dopost(url, {parentSaleProp1: listSaleProp1, parentSaleProp2: listSaleProp2, parentSalePropKey1: variation1, parentSalePropKey2: variation2});    
    }); 

    $("#linkMoveProduct").click(function(e) {
        e.preventDefault();
        var desSku = $('#des_sku').val();
        var keepParentName = $("#keepParentName").prop('checked')? 1 : 0;
        var url = `moveproduct.php?des_sku=${desSku}`;
        var json = $('#txt_json').val();
        var selectedSkus = "";
          $("#tableProducts").find("td.sku_full").each(function(){
              selectedSkus = selectedSkus + $(this).text() + "\n";
          });

        dopost(url, {json_product: json, des_sku: desSku, keep_name: keepParentName, selected_skus: selectedSkus});    
    }); 

    $('input[type=checkbox][data-toggle=toggle]').change(function() {
        var status;
        if(this.checked) {
            status = 'active';
        } else {
            status = 'inactive';
        }

        productUpdateWithAjaxQueue({ skuid: this.id, action: "status", skustatus: status});
    });

    $('#btn_del').click(function(e){
        $('.fa-trash').toggle("hide");
    });

    $('#btn_edit_price').click(function(e){
        $('.price').toggle("hide");
    });

    $('#btn_change_sku').click(function (e) {
        $('#form_change_sku').css('display','block');
    });
    
    $('#btn_update_children').click(function (e) {
        var skus = "";
        $("#tableProducts").find("td.sku").each(function(){
            skus = skus + $(this).text() + ",";
        });

        var sourcesku = $('#sku').val(); 
        var url = "copyinfo.php?sourcesku=" + sourcesku + "&skus=" + skus;
        window.open(url, '_blank');
    });

    $('#btn_copy_single_line').click(function (e) {
        var images = $("#skuimages").val();
        var arrayOfLines = images.match(/[^\r\n]+/g);
        var singleLine = arrayOfLines.join("\t");
        copyToClipBoard(singleLine);
        console.log("copy text : " + singleLine );
    });

    $('#btn_copy_sku').click(function (e) {
      var text = "";
      $("#tableProducts").find("td.sku_full").each(function(){
          text = text + $(this).text() + "\n";
      });
      console.log("copy text : " + text );
      copyToClipBoard(text);
    });

    $('#btn_copy_variation1').click(function (e) {
      var list = [];
        var text = "";
        $("#tableProducts").find("td.saleprop1").each(function(){
          //text = text + $(this).text() + "\n";
            var t = $(this).text().trim();
            if(list.indexOf(t) < 0) {
                list.push(t);
            }
        });
        //list.sort();
        text = list.join("\n");
        
      console.log("copy text : " + text );
      copyToClipBoard(text);
    });

    $('#btn_copy_variation2').click(function (e) {
        var list = [];
        var text = "";
        $("#tableProducts").find("td.saleprop2").each(function(){
          //text = text + $(this).text() + "\n";
            var t = $(this).text().trim();
            if(list.indexOf(t) < 0) {
                list.push(t);
            }
        });
        //list.sort();
        text = list.join("\n");
        
      console.log("copy text : " + text );
      copyToClipBoard(text);
    });

    $('a.copy-product-images').click(function (e) {
        var imgs = [];
        $(this).closest("tr").find('td.product-image.thumb a').each(function(){
            imgs.push($(this).attr("href"));
        });

        text = imgs.join(" ");
      console.log("copy text : " + text );
      copyToClipBoard(text);
    });

    $('a.copy-sku-images').click(function (e) {
        var imgs = [];
        $(this).closest("tr").find('td.sku-image.thumb a').each(function(){
            imgs.push($(this).attr("href"));
        });

        text = imgs.join(" ");
      console.log("copy text : " + text );
      copyToClipBoard(text);
    });

      $('#btn_copy_url').click(function (e) {
        var text = "";
        $("table.main").find("td.url").each(function(){
            text = text + $(this).text() + "\n";
        });
        console.log("copy text : " + text );
        copyToClipBoard(text);
      });

      $('#btn_copy_all').click(function (e) {
        var numberOfInfoColumns = 24;
        var text = "";
        $("table.main").find(".info").each(function(index, value){
            var cellText = $(this).text();
            if(cellText) {
                text = text + cellText;
            } else {
                text = text + $(this).find("img").attr("src");
            }
            mod = (index+1)%numberOfInfoColumns;
            if(mod == 0) {
                text += "\n";
            } else {
                text += "\t";
            }
        });
        console.log("copy text : " + text );
        copyToClipBoard(text);
      });

    // document of tablesorter, see http://tablesorter.com/docs/
    $("#tableProducts").tablesorter();
    // sort column 0
    //$("#tableProducts").tablesorter( {sortList: [[0,0]]} );
</script>
</div>
</body>
</html>