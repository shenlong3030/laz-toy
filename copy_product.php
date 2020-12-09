<?php
include_once "check_token.php";
//require_once('src/show_errors.php');
require_once('_main_functions.php');

$sku = isset($_REQUEST["sku"]) ? $_REQUEST["sku"] : "";
$nsku = isset($_REQUEST["nsku"]) ? $_REQUEST["nsku"] : $sku . ".COPY";

$name = isset($_REQUEST["name"]) ? $_REQUEST["name"] : "";
$color = isset($_REQUEST["color_family"]) ? $_REQUEST["color_family"] : "";
$color_thumbnail = isset($_REQUEST["color_thumbnail"]) ? $_REQUEST["color_thumbnail"] : "";
$model = isset($_REQUEST["compatibility_by_model"]) ? $_REQUEST["compatibility_by_model"] : "";
$brand = isset($_REQUEST["brand"]) ? $_REQUEST["brand"] : "";
$category = isset($_REQUEST["category"]) ? $_REQUEST["category"] : "";

$input = $_POST['images'];
$images = array_filter(explode("\n", str_replace("\r", "", $input)));

$video = isset($_REQUEST["video"]) ? $_REQUEST["video"] : "";
$qty = isset($_REQUEST["qty"]) ? $_REQUEST["qty"] : "";
$price = isset($_REQUEST["price"]) ? $_REQUEST["price"] : "";
$sprice = isset($_REQUEST["sale_price"]) ? $_REQUEST["sale_price"] : "";
$fromdate = isset($_REQUEST["fromdate"]) ? $_REQUEST["fromdate"] : "";
$todate = isset($_REQUEST["todate"]) ? $_REQUEST["todate"] : "";

$weight = isset($_REQUEST["weight"]) ? $_REQUEST["weight"] : "";
$h = isset($_REQUEST["size_h"]) ? $_REQUEST["size_h"] : "";
$w = isset($_REQUEST["size_w"]) ? $_REQUEST["size_w"] : "";
$l = isset($_REQUEST["size_l"]) ? $_REQUEST["size_l"] : "";
$content = isset($_REQUEST["content"]) ? $_REQUEST["content"] : "";

$shortdesc = isset($_REQUEST["shortdesc"]) ? $_REQUEST["shortdesc"] : "";
$desc = isset($_REQUEST["desc"]) ? $_REQUEST["desc"] : "";


$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";

if($sku) {
    $product = getProduct($accessToken, $sku);

    $cache = array();
    if($product) {
        if($action) {
            $product = prepareProductForCreating($product);

            $product['PrimaryCategory'] = $category;

            $product['Attributes']['name'] = $name;
            $product['Attributes']['short_description'] = $shortdesc;
            $product['Attributes']['description'] = $desc;
            $product['Attributes']['brand'] = $brand;
            $product['Attributes']['video'] = $video;

            $product['Skus'][0]['package_weight'] = $weight;
            $product['Skus'][0]['package_height'] = $h;
            $product['Skus'][0]['package_width'] = $w;
            $product['Skus'][0]['package_length'] = $l;
            $product['Skus'][0]['package_content'] = $content;
            $product['Skus'][0]['quantity'] = $qty;
            $product['Skus'][0]['_compatible_variation_'] = $variation;
            
            $product['Skus'][0]['color_family'] = $color;
            $product['Skus'][0]['color_thumbnail'] = $color_thumbnail;
            $product['Skus'][0]['compatibility_by_model'] = $model;
            
            $product['Skus'][0]['price'] = $price;
            $product['Skus'][0]['special_price'] = $sprice;
            $product['Skus'][0]['special_from_date'] = $fromdate;
            $product['Skus'][0]['special_to_date'] = $todate;

            $images = migrateImages($accessToken, $images, $cache);
            $product = setProductImages($product, $images, TRUE);

            $nsku = vn_urlencode($nsku);
            $nsku = make_short_sku($nsku);
            $product = setProductSku($product, $nsku);
            createProduct($accessToken, $product);

        } else {
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
        }
    } else {
        echo "INVALID ID";
    }
}

$addChildLink = "https://$_SERVER[HTTP_HOST]/lazop/addchild_gui.php?sku=$sku&name=$name";
$cloneLink = "https://$_SERVER[HTTP_HOST]/lazop/create.php?sku=$sku";

?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>COPY PRODUCT</title>
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
    <div class="mainContent">

<hr>
    <h1>Copy product</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
    Source SKU: <input type="text" name="sku" size="70" value="<?php echo $sku?>" style="background:lightgray" readonly/>
<hr>
    NEW SKU: <input type="text" name="nsku" size="70" value="<?php echo $nsku?>"/><br/>
    Name: <input type="text" name="name" size="70" value="<?php echo $name?>"/><br/>
<hr>
    Variations : <?php echo $variation;?>
<hr>
    color_family <input type="text" name="color_family" value="<?php echo $color;?>" />
<hr>
    color_thumbnail <input type="text" name="color_thumbnail" value="<?php echo $color_thumbnail;?>" />
    <img src="<?php echo $color_thumbnail;?>" width="30" height="30">
<hr>
    compatibility_by_model <input type="text" name="compatibility_by_model" value="<?php echo $model;?>" />
<hr>
    Brand <input type="text" name="brand" value="<?php echo $brand;?>" />
<hr>
    Primary category <input type="text" name="category" value="<?php echo $category;?>" />
    <br>
    <ul>
        <li>4528 : miếng dán</li>
        <li>4523 : ốp lưng</li>
        <li>11029 : cáp sạc</li>
        <li>11496 : miếng dán đồng hồ</li>
    </ul>
<hr>
    <h3>Images</h3> <textarea class="nowrap" name="images" rows="6" cols="80"><?php echo implode("\n", $images);?></textarea>
    <a title="Editor" href="https://wm.xamve.com/wp-admin/upload.php" target="_blank" rel="noopener">Get images</a>
    <a title="Editor" href="https://github.com/shenlong3030/temp/issues/4" target="_blank" rel="noopener">Upload images</a>
    <br/>
<?php
    foreach($images as $image) {
        echo htmlLinkImage($image);
    }
?>
<hr>
    Video <input type="text" name="video" size="70" value="<?php echo $video;?>" />

<hr>

    Quantity <input type="text" name="qty" value="<?php echo $qty;?>" />

<hr>

    Price <input type="text" name="price" value="<?php echo $price;?>" />
    <input type="text" name="sale_price" value="<?php echo $sprice;?>" />
    From <input type="text" name="fromdate" value="<?php echo $fromdate;?>" />
    To <input type="text" name="todate" value="<?php echo $todate;?>" /><br>

<hr>
    Weight: <input type="text" name="weight" size="5" value="<?php echo $weight;?>"/> kg<br>
    Packing Size: <input type="text" name="size_h" size="10" value="<?php echo $h;?>" /> 
    x <input type="text" name="size_w" size="10" value="<?php echo $w;?>" /> 
    x <input type="text" name="size_l" size="10" value="<?php echo $l;?>" /> cm<br>
    Packing Content: <input type="text" name="content" size="70" value="<?php echo $content;?>" /><br>

<hr>
    <h3>Short Description:</h3>
    <textarea class="nowrap" name="shortdesc" rows="2" cols="80"><?php echo $shortdesc;?></textarea>
    <a title="Editor" href="https://html-online.com/editor/" target="_blank" rel="noopener">Editor</a>
    <?php echo $shortdesc;?>
<hr> 
    <h3>Description:</h3>
    <textarea class="nowrap" name="desc" rows="2" cols="80"><?php echo $desc;?></textarea>
    <a title="Editor" href="https://html-online.com/editor/" target="_blank" rel="noopener">Editor</a>
    <?php echo $desc;?>
<hr> 
    <input type="hidden" name="action" value="create"/>
    <input type="submit" value="Create"/>
    </form>

<?php

// Pay no attention to this statement.
// It's only needed if timezone in php.ini is not set correctly.
date_default_timezone_set("UTC");

?>

</div>
</body>
</html>