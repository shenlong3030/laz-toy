<?php
include_once "check_token.php";
//require_once('src/show_errors.php');
require_once('_main_functions.php');


$sku = isset($_REQUEST["sku"]) ? $_REQUEST["sku"] : "";
$itemId = isset($_REQUEST["item_id"]) ? $_REQUEST["item_id"] : "";
//$qname = isset($_REQUEST["qname"]) ? $_REQUEST["qname"] : "";

$category = "";
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
        
        $category = $product['primary_category'];
        $weight = $product['skus'][$i]['package_weight'];
        $h = $product['skus'][$i]['package_height'];
        $w = $product['skus'][$i]['package_width'];
        $l = $product['skus'][$i]['package_length'];
        $content = $product['skus'][$i]['package_content'];
        $qty = $product['skus'][$i]['quantity'];
        $variation = $product['skus'][$i]['Variation'];
        $type_screen_guard = $product['skus'][$i]['type_screen_guard'];

        $color = $product['skus'][$i]['color_family'];
        $color_thumbnail = $product['skus'][$i]['color_thumbnail'];
        $model = $product['skus'][$i]['compatibility_by_model'];
        
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
$cloneLink = "https://$_SERVER[HTTP_HOST]/lazop/create.php?sku=$sku";
$copyLink = "https://$_SERVER[HTTP_HOST]/lazop/copy_product.php?sku=$sku";
$copyToLink = "https://$_SERVER[HTTP_HOST]/lazop/copyinfo.php?sourcesku=$sku";

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
    <a style="color:red;padding-left: 20px" href="<?php echo $copyToLink?>" target="_blank">Copy To</a>
    <a style="color:red;padding-left: 20px" href="<?php echo $cloneLink?>" target="_blank">Clone to new product</a>
<hr>

<input id="<?php echo $sku;?>" type="checkbox" data-toggle="toggle" <?php echo $status;?>>

<hr>
    <form action="update.php" method="POST" name="nameForm" target="responseIframe">
    <input type="hidden" id="sku" name="sku" value="<?php echo $sku;?>">
    Name <input type="text" name="name" size="80" value="<?php echo $name;?>" />
    <input type="submit" value="Update name"/>
    </form>
<hr>
    <form action="update.php" method="POST" name="pricesForm" target="responseIframe">
    <input type="hidden" name="sku" value="<?php echo $sku;?>" />
    Price <input type="text" name="price" value="<?php echo $price;?>" /> >> 
    <input type="text" name="sale_price" value="<?php echo $sprice;?>" />
    From <input type="text" name="fromdate" value="<?php echo $fromdate;?>" />
    To <input type="text" name="todate" value="<?php echo $todate;?>" /><br>
    <input type="submit" value="Update prices"/>
    </form>
<hr>
    <form action="update.php" method="POST" name="variationForm" target="responseIframe">
    <input type="hidden" id="sku" name="sku" value="<?php echo $sku;?>">
    <input type="hidden" id="change-attr" name="change-attr" value="1">
    Variation <input type="text" name="variation" value="<?php echo $variation;?>" />
    type_screen_guard <input type="text" name="type_screen_guard" value="<?php echo $type_screen_guard;?>" />
    compatibility_by_model <input type="text" name="compatibility_by_model" value="<?php echo $model;?>" />
    color_family <input type="text" name="color_family" value="<?php echo $color;?>" />
    <input type="submit" value="Update"/>
    </form>
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
    <form action="update.php" method="POST" name="imageForm" target="responseIframe">
    <input type="hidden" name="sku" value="<?php echo $sku;?>" />
    <h3>Images</h3> <textarea id="imagelinks" class="nowrap" name="images" rows="6" cols="80"><?php echo implode("\n", $images);?></textarea>
    <a title="Editor" href="https://wm.phukiensh.com/wp-admin/upload.php" target="_blank" rel="noopener">Get images</a>
    <a title="Editor" href="https://github.com/shenlong3030/temp/issues/4" target="_blank" rel="noopener">Upload images</a>
    <input type="submit" name="update-image" value="Update images"/>

    <br>
    <input type="button" id="btn_update_children" value="Update all children"/>
    <input type="button" id="btn_copy_single_line" value="Copy images single line"/>
    </form>
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
    <form action="update.php" method="POST" name="qtyForm" target="responseIframe">
    <input type="hidden" name="sku" value="<?php echo $sku;?>" />
    Quantity <input type="text" name="qty" value="<?php echo $qty;?>" />

    <! –– API need qtyaction ––>
    <input type="hidden" name="qtyaction" value="update"/>

    <input type="submit" value="Update quantity"/>
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
    
    <?php echo $shortdesc;?>
<hr> 
    <h3>Description:</h3>
    <form action="update.php" method="POST" name="descForm" target="responseIframe">
    <input type="hidden" name="sku" value="<?php echo $sku;?>" />
    <textarea class="nowrap" name="desc" rows="2" cols="80"><?php echo $desc;?></textarea>
    <a title="Editor" href="https://html-online.com/editor/" target="_blank" rel="noopener">Editor</a>
    <input type="submit" value="Update description"/>
    </form>
    
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

                  if(parseInt(res.code)) {
                    myFrame.prepend(n + data + '<br>'); 
                  } else {
                    myFrame.prepend(n + ' SUCCESS<br>'); 
                  }
               },
               error: function(error){
                  myFrame.prepend(n + ' FAILED<br>'); 
               }
          });
      }
    //##########################################################################################################
    
    $("button[name='qtyaction'][value='+500']").click(function() {
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
    $('input[type=checkbox][data-toggle=toggle]').change(function() {
        var status;
        if(this.checked) {
            status = 'active';
        } else {
            status = 'inactive';
        }

        productUpdateWithAjaxQueue({ sku: this.id, action: "status", skustatus: status});
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

      $('#btn_copy_url').click(function (e) {
        var text = "";
        $("table.main").find("td.url").each(function(){
            text = text + $(this).text() + "\n";
        });
        console.log("copy text : " + text );
        copyToClipBoard(text);
      });

      $('#btn_copy_all').click(function (e) {
        var numberOfInfoColumns = 18;
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