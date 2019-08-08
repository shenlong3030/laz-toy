<?php
include_once "check_token.php";
//require_once('src/show_errors.php');
require_once('_main_functions.php');



$sku = isset($_REQUEST["sku"]) ? $_REQUEST["sku"] : "";

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
    $product = getProduct($accessToken, $sku);

    $sibling = null;
    if($product) {
        $sibling = getProduct($accessToken, "", $product['item_id']);
        if(count($sibling['skus']) < 2) {
            $sibling = null;
        }

        //var_dump($product);
        $images = array_filter($product['skus'][0]['Images']);
        
        $category = $product[primary_category];
        $weight = $product['skus'][0]['package_weight'];
        $h = $product['skus'][0]['package_height'];
        $w = $product['skus'][0]['package_width'];
        $l = $product['skus'][0]['package_length'];
        $content = $product['skus'][0]['package_content'];
        $qty = $product['skus'][0]['quantity'];
        $variation = $product['skus'][0]['_compatible_variation_'];
        
        $color = $product['skus'][0]['color_family'];
        $model = $product['skus'][0]['compatibility_by_model'];
        
        $price = $product['skus'][0]['price'];
        $sprice = $product['skus'][0]['special_price'];
        $fromdate = $product['skus'][0]['special_from_date'];
        $todate = $product['skus'][0]['special_to_date'];
        
        $name = $product['attributes']['name'];
        $shortdesc = $product['attributes']['short_description'];
        $desc = $product['attributes']['description'];
        $brand = $product['attributes']['brand'];
        $video = $product['attributes']['video'];
    }
}

$addChildLink = "http://$_SERVER[HTTP_HOST]/lazop/addchild.php?sku=$sku&name=$name";
$cloneLink = "http://$_SERVER[HTTP_HOST]/lazop/create.php?sku=$sku";

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
    .nav{
      margin: 0;
      padding: 0;
      position: fixed;
      top: 0;
      left: 0;
      overflow: hidden;
      background-color: #FFF;
      width: 100%;
      z-index: 10;
    }
    .mainContent{
      margin-top: 50px;
    }
    </style>

</head>
<body>
    <div class="nav">
    <iframe id="responseIframe" name="responseIframe" width="600" height="30"></iframe>
    </div>
    
    <div class="mainContent">

    <h1>Update product</h1>
    
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
    Source SKU: <input type="text" name="sku" size="70" value="<?php echo $sku?>"/>
    <input type="submit" value="Reload"/>
    </form>

    <a style="color:red" href="<?php echo $addChildLink?>" target="_blank">Add Child</a>
    <a style="color:red;padding-left: 10px" href="<?php echo $cloneLink?>" target="_blank">Clone to new product</a>
<hr>
<?php if($sibling) { ?>
    <h2>Danh sách các SP cùng nhóm này</h2>
    <button id="copy-sku-btn">Copy SKUs to Clipboard</button>
    <?php echo printProducts(array($sibling), false, $sku);?>
<?php } ?>
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

if($sku && !empty($images)) {
    //setImages($accessToken, $sku, $images);
}


?>

<script type="text/javascript">
    $(function(){
        $('#copy-sku-btn').click(function (e) {
          var text = "";
          $("table.main").find("td.sku").each(function(){
              text = text + $(this).text() + "\n";
          });
          console.log("copy text : " + text );
          copyToClipBoard(text);
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