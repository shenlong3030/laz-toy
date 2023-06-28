<?php
include_once "check_token.php";
//require_once('src/show_errors.php');
require_once('_main_functions.php');


$sku = isset($_REQUEST["sku"]) ? $_REQUEST["sku"] : "";
$itemId = isset($_REQUEST["item_id"]) ? $_REQUEST["item_id"] : "";
//$qname = isset($_REQUEST["qname"]) ? $_REQUEST["qname"] : "";

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

if($sku || $itemId) {
    $product = getProduct($accessToken, $sku, $itemId, $qname);
    debug_log($product);

    if($product) {
        if(empty($itemId)) {
            $itemId = getProductItemId($product);
        }
        $sibling = getProduct($accessToken, null, $itemId, null);;

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
        
        $variation = $product['skus'][$i]['saleProp']['Variation'];
        $type_screen_guard = $product['skus'][$i]['saleProp']['type_screen_guard'];

        $color = $product['skus'][$i]['saleProp']['color_family'];
        $color_thumbnail = $product['skus'][$i]['color_thumbnail'];
        $model = $product['skus'][$i]['saleProp']['compatibility_by_model'];
        
        $price = $product['skus'][$i]['price'];
        $sprice = $product['skus'][$i]['special_price'];
        $fromdate = $product['skus'][$i]['special_from_date'];
        $todate = $product['skus'][$i]['special_to_date'];
        
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

$addChildLink = "https://$_SERVER[HTTP_HOST]/lazop/addchild_gui.php?sku=$sku&name=$name";
$cloneLink = "https://$_SERVER[HTTP_HOST]/lazop/copy_product_all_skus.php?sku=$sku";
$copyLink = "https://$_SERVER[HTTP_HOST]/lazop/copy_product.php?sku=$sku";
$copyToLink = "https://$_SERVER[HTTP_HOST]/lazop/copyinfo.php?sourcesku=$sku";
$moveToLink = "https://$_SERVER[HTTP_HOST]/lazop/movechild.php?sku=$sku";
$copyFromCLMau = "https://$_SERVER[HTTP_HOST]/lazop/copy_product_all_skus.php?sku=CL.ALL__MAU.XX__KT&associated_sku=$sku";
$copyFromCLMau2 = "https://$_SERVER[HTTP_HOST]/lazop/copy_product_all_skus2.php?sku=CL.ALL__MAU.XX__KT&associated_sku=$sku";

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
    <iframe id="responseIframe" name="responseIframe" width="600" height="30"></iframe>
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
    <button id="btn_copy_models">Copy models</button>
    <button id="btn_copy_colors">Copy colors</button>
    <?php echo printProducts(array($sibling), false, $sku);?>
    </div>
<?php } ?>

<hr>
    <h1>Update product</h1>
    
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
    Source SKU: <input type="text" name="sku" size="70" value="<?php echo $sku?>"/>
    <input type="submit" value="Reload"/>
    <input id="btn_change_sku" type="button" value="Change"/>
    </form>

    <form id="form_change_sku" class="ex" action="update.php" method="POST" name="skuForm" target="responseIframe">
    <input type="hidden" id="sku" name="sku" value="<?php echo $sku;?>">
    Change SKU to: <input type="text" name="new_sku" size="70" value="<?php echo $sku?>"/>
    <input type="submit" value="Change SKU"/>
    </form>

    <a style="color:red" href="<?php echo $addChildLink?>" target="_blank">Add Child</a>
    <a style="color:red;padding-left: 20px" href="<?php echo $copyLink?>" target="_blank">Copy</a>
    <a style="color:red;padding-left: 20px" href="<?php echo $copyToLink?>" target="_blank">Copy info to</a>
    <a style="color:red;padding-left: 20px" href="<?php echo $cloneLink?>" target="_blank">Copy all SKU</a>
    <a style="color:red;padding-left: 20px" href="<?php echo $moveToLink?>" target="_blank">Move to</a>
    <a style="color:red;padding-left: 20px" href="<?php echo $copyFromCLMau?>" target="_blank">Copy from CL mẫu</a>
    <a style="color:red;padding-left: 20px" href="<?php echo $copyFromCLMau2?>" target="_blank">Copy from CL mẫu 2</a>
<hr>

<input id="<?php echo $sku;?>" type="checkbox" data-toggle="toggle" <?php echo $status;?>>

<hr>
    <div>
    <input type="hidden" id="sku" name="sku" value="<?php echo $sku;?>">
    Name <input type="text" name="name" size="80" value="<?php echo $name;?>" />
    <button id="btn_updatename">Update name</button>
    </div>
<hr>
    <div>
    <input type="hidden" name="sku" value="<?php echo $sku;?>" />
    Price <input type="text" name="price" value="<?php echo $price;?>" /> >> 
    <input type="text" name="sale_price" value="<?php echo $sprice;?>" />
    From <input type="text" name="fromdate" value="<?php echo $fromdate;?>" />
    To <input type="text" name="todate" value="<?php echo $todate;?>" /><br>
    <button id="btn_updateprice">Update Price</button>
    </div>
<hr>
    <div>
    <input type="hidden" id="sku" name="sku" value="<?php echo $sku;?>">
    Variation <input type="text" name="variation" value="<?php echo $variation;?>" />
    type_screen_guard <input type="text" name="type_screen_guard" value="<?php echo $type_screen_guard;?>" />
    compatibility_by_model <input type="text" name="compatibility_by_model" value="<?php echo $model;?>" />
    color_family <input type="text" name="color_family" value="<?php echo $color;?>" />
    <button id="btn_updateattr">Update</button>
    </div>
<hr>
    <form action="update_color_thumbnail_gui.php" method="GET" name="colorThumbnailForm" target="_blank">
    <input type="hidden" id="item_id" name="item_id" value="<?php echo $itemId;?>">
    color_thumbnail <input type="text" name="color_thumbnail" value="<?php echo $color_thumbnail;?>" />
    <input type="submit" value="Update color_thumbnail"/>
    <img src="<?php echo $color_thumbnail;?>" width="30" height="30">
    </form>
<hr>
    <form action="update.php" method="POST" name="brandForm" target="responseIframe">
    <input type="hidden" id="sku" name="sku" value="<?php echo $sku;?>">
    Brand <input type="text" name="brand" value="<?php echo $brand;?>" />
    <input type="submit" value="Update brand"/>
    </form>
<hr>
    <form action="update.php" method="POST" name="categoryForm" target="responseIframe">
    <input type="hidden" id="sku" name="sku" value="<?php echo $sku;?>">
    Primary category <input type="text" name="category" value="<?php echo $category;?>" />
    <input type="submit" value="Update category"/>
    </form>
    <br>
    <ul>
        <li>4528 : miếng dán</li>
        <li>4523 : ốp lưng</li>
        <li>11029 : cáp sạc</li>
        <li>10100418 : miếng dán đồng hồ</li>
    </ul>
<hr>
    <div>
    <input type="hidden" name="sku" value="<?php echo $sku;?>" />
    <h3>Product Images</h3> <textarea id="imagelinks" class="nowrap" name="product_images" rows="6" cols="80"><?php echo implode("\n", $productImages);?></textarea>
    <a title="Editor" href="https://wm.phukiensh.com/wp-admin/upload.php" target="_blank" rel="noopener">Get images</a>
    <a href="https://github.com/shenlong3030/temp/issues/new" target="_blank" rel="noopener">Git</a>
    <button id="btn_updateProductImages">Update product images</button>
    </div>
<?php
    foreach($productImages as $image) {
        echo htmlLinkImage($image);
    }
?>
<hr>
    <div>
    <input type="hidden" name="sku" value="<?php echo $sku;?>" />
    <h3>SKU Images</h3> <textarea id="imagelinks" class="nowrap" name="images" rows="6" cols="80"><?php echo implode("\n", $images);?></textarea>
    <a title="Editor" href="https://wm.phukiensh.com/wp-admin/upload.php" target="_blank" rel="noopener">Get images</a>
    <a href="https://github.com/shenlong3030/temp/issues/new" target="_blank" rel="noopener">Git</a>
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
    <input type="hidden" id="sku" name="sku" value="<?php echo $sku;?>">
    Video <input type="text" name="video" size="70" value="<?php echo $video;?>" />
    <input type="submit" value="Update video link"/>
    </form>

<hr>
    <form action="update.php" method="POST" name="sizeForm" target="responseIframe">
    <input type="hidden" name="sku" value="<?php echo $sku;?>" />

    Weight: <input type="text" name="weight" size="5" value="<?php echo $weight;?>"/> kg<br>
    Packing Size: <input type="text" name="size_h" size="10" value="<?php echo $h;?>" /> 
    x <input type="text" name="size_w" size="10" value="<?php echo $w;?>" /> 
    x <input type="text" name="size_l" size="10" value="<?php echo $l;?>" /> cm<br>
    Packing Content: <input type="text" name="content" size="70" value="<?php echo $content;?>" /><br>
    <input type="submit" value="Update weight, size, content"/>
    </form>
<hr>
    <h3>Short description:</h3>
    <form action="update.php" method="POST" name="shortdescForm" target="responseIframe">
    <input type="hidden" name="sku" value="<?php echo $sku;?>" />
    <textarea class="nowrap" name="shortdesc" rows="2" cols="80"><?php echo $shortdesc;?></textarea>
    <a title="Editor" href="https://html-online.com/editor/" target="_blank" rel="noopener">Editor</a>
    <input type="submit" value="Update short description"/>
    </form>
    
<hr> 
    <div>
    <h3>Description:</h3>
    <input type="hidden" name="sku" value="<?php echo $sku;?>" />
    <textarea class="nowrap" name="desc" rows="2" cols="80"><?php echo $desc;?></textarea>
    <a title="Editor" href="https://html-online.com/editor/" target="_blank" rel="noopener">Editor</a>
    <button id="btn_updatedesc">Update description + short desc</button>
    </div>
    
    <div>
    <?php echo $desc;?>
    </div>

<hr>
    <form action="del.php" method="POST" name="delForm" target="responseIframe">
    <input type="hidden" name="skus" value="<?php echo $sku;?>" />
    <input type="submit" value="Xoá SP này"/>
    </form>
<?php

// Pay no attention to this statement.
// It's only needed if timezone in php.ini is not set correctly.
date_default_timezone_set("UTC");

?>

<script type="text/javascript">

    //######### AJAX QUEUE ######################################################################################
      var ajaxManager = (function() {
         var requests = [];

         return {
            addReq:  function(opt) {
                requests.push(opt);
            },
            removeReq:  function(opt) {
                if( $.inArray(opt, requests) > -1 )
                    requests.splice($.inArray(opt, requests), 1);
            },
            run: function() {
                var self = this,
                    oriSuc;

                if( requests.length ) {
                    oriSuc = requests[0].complete;

                    requests[0].complete = function() {
                         if( typeof(oriSuc) === 'function' ) oriSuc();
                         requests.shift();
                         self.run.apply(self, []);
                    };   

                    $.ajax(requests[0]);
                } else {
                  self.tid = setTimeout(function() {
                     self.run.apply(self, []);
                  }, 1000);
                }
            },
            stop:  function() {
                requests = [];
                clearTimeout(this.tid);
            }
         };
      }());
      ajaxManager.run(); 

      function productUpdateWithAjaxQueue(params) {
          // send response to this iframe
          var myFrame = $("#responseIframe").contents().find('body'); 

          ajaxManager.addReq({
               type: 'POST',
               url: 'update-api.php',
               data: params,
               success: function(data){
                  var res = JSON.parse(data); // data is string, convert to obj
                  var d = new Date();
                  var n = d.toLocaleTimeString();
                  const randomColor = Math.floor(Math.random()*16777215).toString(16);
                  const htmlColor = "#" + randomColor;

                  if(parseInt(res.code)) {
                    myFrame.prepend('<p style="background-color:' + htmlColor + '">' + n + data + '</p>'); 
                  } else {
                    myFrame.prepend('<p style="background-color:' + htmlColor + '">' + n + 'SUCCESS</p>');
                  }
               },
               error: function(error){
                  myFrame.prepend(n + ' FAILED<br>'); 
               }
          });
      }
    //##########################################################################################################
    
    $("button[name='qtyaction'][value='=500']").click(function() {
        $(this).parent().find('input[name=qty]').val('500'); 
        var sku = $(this).parent().find('input[name=sku]').val(); 
        productUpdateWithAjaxQueue({ sku: sku, action: "qty", qty: 500});
    });
    $("button[name='qtyaction'][value='=0']").click(function() {
        $(this).parent().find('input[name=qty]').val('0'); 
        var sku = $(this).parent().find('input[name=sku]').val(); 
        var q = $(this).closest('td').find('.reservedStock').text(); 
        productUpdateWithAjaxQueue({ sku: sku, action: "qty", qty: q?q:0});
    });
    $('input[name=qty]').keypress(function(event){
      var keycode = (event.keyCode ? event.keyCode : event.which);
      if(keycode == '13'){ // press ENTER
          var s = $(this).parent().find('input[name=sku]').val(); 
          var q = $(this).parent().find('input[name=qty]').val(); 
          productUpdateWithAjaxQueue({ sku: s, action: "qty", qty: q});
      }
      event.stopPropagation();
    });

    //### UPDATE NAME AJAX ###
    $('input[name="name"]').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){ // press ENTER
          var s = $(this).parent().find('input[name=sku]').val(); 
          var n = $(this).parent().find('input[name="name"]').val(); 
          productUpdateWithAjaxQueue({ sku: s, action: "name", name: n});
        }
        event.stopPropagation();
    });
    $('#btn_updatename').click(function() {
        var s = $(this).parent().find('input[name=sku]').val(); 
        var n = $(this).parent().find('input[name="name"]').val(); 
        productUpdateWithAjaxQueue({ sku: s, action: "name", name: n});
    });
    //###########################


    //### UPDATE PRICE AJAX ###
    $('input[name="sale_price"]').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){ // press ENTER
          var s = $(this).parent().find('input[name=sku]').val(); 
          var sprice = $(this).parent().find('input[name="sale_price"]').val(); 
          productUpdateWithAjaxQueue({ sku: s, action: "price", sprice: sprice});
        }
        event.stopPropagation();
    });
    $('#btn_updatename').click(function() {
        var s = $(this).parent().find('input[name=sku]').val(); 
        var sprice = $(this).parent().find('input[name="sale_price"]').val(); 
        productUpdateWithAjaxQueue({ sku: s, action: "price", sprice: sprice});
    });
    //###########################


    //### UPDATE ATTRIBUTES ###
    $('#btn_updateattr').click(function() {
        var s = $(this).parent().find('input[name=sku]').val(); 
        var variation = $(this).parent().find('input[name="variation"]').val(); 
        var type_screen_guard = $(this).parent().find('input[name="type_screen_guard"]').val(); 
        var compatibility_by_model = $(this).parent().find('input[name="compatibility_by_model"]').val(); 
        var color_family = $(this).parent().find('input[name="color_family"]').val(); 
        productUpdateWithAjaxQueue({ sku: s, action: "attr", 
            variation: variation, 
            type_screen_guard:type_screen_guard,
            compatibility_by_model:compatibility_by_model,
            color_family:color_family
        });
    });
    //###########################


    //### UPDATE DESCRIPTION ###
    $('#btn_updatedesc').click(function() {
        var s = $(this).parent().find('input[name=sku]').val(); 
        var desc = $(this).parent().find('textarea[name="desc"]').val(); 
        productUpdateWithAjaxQueue({ sku: s, action: "description", desc: desc});
    });
    //###########################


    //### UPDATE IMAGES ###
    $('#btn_updateimages').click(function() {
        var s = $(this).parent().find('input[name=sku]').val(); 
        var images = $(this).parent().find('textarea[name="images"]').val(); 
        productUpdateWithAjaxQueue({ sku: s, action: "images", images: images});
    });

    $('#btn_updateProductImages').click(function() {
        var s = $(this).parent().find('input[name=sku]').val(); 
        var pimages = $(this).parent().find('textarea[name="product_images"]').val(); 
        productUpdateWithAjaxQueue({ sku: s, action: "pimages", pimages: pimages});
    });
    //###########################


    $('input[type=checkbox][data-toggle=toggle]').change(function() {
        var status;
        if(this.checked) {
            status = 'active';
        } else {
            status = 'inactive';
        }

        productUpdateWithAjaxQueue({ sku: this.id, action: "status", skustatus: status});
    });

    $('#btn_del').click(function(e){
        $('a.fa-trash').toggle("hide");
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
            skus = skus + $(this).text() + "%0A";
        });

        var sourcesku = $(this).parent().find('input[name=sku]').val(); 
        var url = "copyinfo.php?sourcesku=" + sourcesku + "&skus=" + skus;
        window.open(url, '_blank');
    });

    $('#btn_copy_single_line').click(function (e) {
        var images = $("#imagelinks").val();
        var arrayOfLines = images.match(/[^\r\n]+/g);
        var singleLine = arrayOfLines.join("\t");
        copyToClipBoard(singleLine);
    });

    $('#btn_copy_sku').click(function (e) {
      var text = "";
      $("#tableProducts").find("td.sku").each(function(){
          text = text + $(this).text() + "\n";
      });
      console.log("copy text : " + text );
      copyToClipBoard(text);
    });

    $('#btn_copy_models').click(function (e) {
      var text = "";
      $("#tableProducts").find("td.model").each(function(){
          text = text + $(this).text() + "\n";
      });
      console.log("copy text : " + text );
      copyToClipBoard(text);
    });

    $('#btn_copy_colors').click(function (e) {
      var text = "";
      $("#tableProducts").find("td.color").each(function(){
          text = text + $(this).text() + "\n";
      });
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
        var numberOfInfoColumns = 17;
        var text = "";
        $("table.main").find("td.info").each(function(index, value){
            text = text + $(this).text();
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