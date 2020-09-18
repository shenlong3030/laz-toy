<?php
include_once "check_token.php";
//require_once('src/show_errors.php');
require_once('_main_functions.php');



$sku = isset($_REQUEST["sku"]) ? $_REQUEST["sku"] : "";
$itemId = isset($_REQUEST["item_id"]) ? $_REQUEST["item_id"] : "";

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

if($sku) {
    $product = getProduct($accessToken, "", $itemId);

    if($product) {
        $sibling = null;
        if(count($product['skus']) > 1) {
            $sibling = $product; // for displaying all SKUs
        }

        $i = getProductSkuIndex($product, $sku);

        //var_dump($product);
        $images = array_filter($product['skus'][$i]['Images']);
        
        $category = $product['primary_category'];
        $weight = $product['skus'][$i]['package_weight'];
        $h = $product['skus'][$i]['package_height'];
        $w = $product['skus'][$i]['package_width'];
        $l = $product['skus'][$i]['package_length'];
        $content = $product['skus'][$i]['package_content'];
        $qty = $product['skus'][$i]['quantity'];
        $variation = $product['skus'][$i]['_compatible_variation_'];
        
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

        $status = isProductActive($product) ? "checked" : "";
    } else {
        echo "INVALID ID";
    }
}

$addChildLink = "https://$_SERVER[HTTP_HOST]/lazop/addchild.php?sku=$sku&name=$name";
$cloneLink = "https://$_SERVER[HTTP_HOST]/lazop/create.php?sku=$sku";

?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UPDATE</title>
    <link href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" rel="stylesheet">

    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <!-- bxSlider Javascript file -->
    <script src="./js/controls.js"></script>
    <script src="./js/jquery.tablesorter.min.js"></script>
    
    <link rel="stylesheet" type="text/css" href="css/style.css">

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
    <div style="height:500px;overflow:auto">
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
    </form>

    <a style="color:red" href="<?php echo $addChildLink?>" target="_blank">Add Child</a>
    <a style="color:red;padding-left: 10px" href="<?php echo $cloneLink?>" target="_blank">Clone to new product</a>
<hr>

<input id="<?php echo $sku;?>" type="checkbox" data-toggle="toggle" <?php echo $status;?>>

<hr>
    <form action="update.php" method="POST" name="nameForm" target="responseIframe">
    <input type="hidden" id="sku" name="sku" value="<?php echo $sku;?>">
    Name <input type="text" name="name" size="80" value="<?php echo $name;?>" />
    <input type="submit" value="Update name"/>
    </form>
<hr>
    Variations : <?php echo $variation;?>
<hr>
    <form action="update.php" method="POST" name="colorForm" target="responseIframe">
    <input type="hidden" id="sku" name="sku" value="<?php echo $sku;?>">
    color_family <input type="text" name="color_family" value="<?php echo $color;?>" />
    <input type="submit" value="Update color"/>
    </form>
<hr>
    <form action="update_color_thumbnail_gui.php" method="POST" name="colorThumbnailForm" target="_blank">
    <input type="hidden" id="item_id" name="item_id" value="<?php echo $id;?>">
    color_thumbnail <input type="text" name="color_thumbnail" value="<?php echo $color_thumbnail;?>" />
    <input type="submit" value="Update color_thumbnail"/>
    <img src="<?php echo $color_thumbnail;?>" width="30" height="30">
    </form>
<hr>
    <form action="update.php" method="POST" name="modelForm" target="responseIframe">
    <input type="hidden" id="sku" name="sku" value="<?php echo $sku;?>">
    compatibility_by_model <input type="text" name="compatibility_by_model" value="<?php echo $model;?>" />
    <input type="submit" value="Update model"/>
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
        <li>11496 : miếng dán đồng hồ</li>
    </ul>
<hr>
    <form action="update.php" method="POST" name="imageForm" target="responseIframe">
    <input type="hidden" name="sku" value="<?php echo $sku;?>" />
    <h3>Images</h3> <textarea class="nowrap" name="images" rows="6" cols="80"><?php echo implode("\n", $images);?></textarea>
    <input type="submit" value="Update images"/>
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
    <form action="update.php" method="POST" name="pricesForm" target="responseIframe">
    <input type="hidden" name="sku" value="<?php echo $sku;?>" />
    Price <input type="text" name="price" value="<?php echo $price;?>" /> >> 
    <input type="text" name="sale_price" value="<?php echo $sprice;?>" />
    From <input type="text" name="fromdate" value="<?php echo $fromdate;?>" />
    To <input type="text" name="todate" value="<?php echo $todate;?>" /><br>
    <input type="submit" value="Update prices"/>
    </form>
<hr>
    <form action="update.php" method="POST" name="pricesForm" target="responseIframe">
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
    <textarea class="nowrap" name="shortdesc" rows="2" cols="80"></textarea>
    <input type="submit" value="Update short description"/>
    </form>
    
    <?php echo $shortdesc;?>
<hr> 
    <h3>Description:</h3>
    <form action="update.php" method="POST" name="descForm" target="responseIframe">
    <input type="hidden" name="sku" value="<?php echo $sku;?>" />
    <textarea class="nowrap" name="desc" rows="2" cols="80"></textarea>
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
    $("button[name='qtyaction'][value='+500']").click(function() {
      $(this).parent().find('input[name=qty]').val('500'); 
    });
    $("button[name='qtyaction'][value='=0']").click(function() {
      $(this).parent().find('input[name=qty]').val('0'); 
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

    $('input[type=checkbox][data-toggle=toggle]').change(function() {
      var status;
      if(this.checked) {
          status = 'active';
      } else {
          status = 'inactive';
      }
      
      $.post( "update-api.php", { sku: this.id, skustatus: status })
        .done(function( data ) {
          if(data.code) {
              alert(data);
          } 
        });
    });

    // document of tablesorter, see http://tablesorter.com/docs/
    $("#tableProducts").tablesorter();
    // sort column 0
    //$("#tableProducts").tablesorter( {sortList: [[0,0]]} );
</script>
</div>
</body>
</html>