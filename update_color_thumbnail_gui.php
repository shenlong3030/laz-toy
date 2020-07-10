<?php

// Pay no attention to this statement.
// It's only needed if timezone in php.ini is not set correctly.
date_default_timezone_set("UTC");

include_once "check_token.php";
//require_once('src/show_errors.php');
require_once('_main_functions.php');


$id = isset($_REQUEST["item_id"]) ? $_REQUEST["item_id"] : "";
$colors = array();
$colorThumbnails = array();
$name = "";
$defaultColors = array(
    "Đỏ" => "https://vn-live.slatic.net/p/55ad4226db6a2999dab1895459833d14.jpg",
    "Đen" => "https://vn-live.slatic.net/p/89b7e4093c507af559543508ee922cc1.jpg",
    "Vàng" => "https://vn-live.slatic.net/p/0f0c8470b27f4ca6efc08770598ea2e2.jpg",
    "Xanh matcha" => "https://vn-live.slatic.net/p/dc686e7f51bb8f990a0f5c6f8f2400c3.jpg",
    "Tím" => "https://vn-live.slatic.net/p/28a765425981cb1817bec161eb101c8a.jpg",
    "Xanh navy" => "https://vn-live.slatic.net/p/9c14256da7d15e7574c151c1a705eca3.jpg",
    "Xanh bóng đêm" => "https://vn-live.slatic.net/p/9c14256da7d15e7574c151c1a705eca3.jpg",
    "Trắng" => "https://vn-live.slatic.net/p/ad489d3e4167065cd26bb80c8d56b242.jpg"
    );


if($id) {
    $product = getProduct($accessToken, null, $id);
    if($product) {
        $name = $product['attributes']["name"];
        foreach ($product['skus'] as $key => $sku) {
            if(in_array($sku['color_family'], $colors)) {
                continue;
            }
            $colors[] = $sku['color_family'];
            $colorThumbnails[] = $sku['color_thumbnail'];
        }
    }
}

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

    <h1>Update product color thumbnail</h1>
    
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
    Source item_id: <input type="text" name="item_id" size="70" value="<?php echo $id?>"/>
    <input type="submit" value="Reload"/>
    </form>
<hr>
   <h2>Name: <?php echo $name;?></h2>
<hr>
    <form action="update.php" method="POST" name="imageForm" target="responseIframe">
    <input type="hidden" name="item_id" value="<?php echo $id;?>" />

    <table>
    <tr>
     <th>Colors</th>
     <th>Thumbnails</th>
    </tr>
    <tbody>
        <tr>
            <td><textarea id="colorsTxt" class="nowrap" name="colors" rows="10" cols="20"><?php echo implode("\n", $colors);?></textarea></td>
            <td><textarea id="urlsTxt" class="nowrap" name="color_thumbnails" rows="10" cols="100"><?php echo implode("\n", $colorThumbnails);?></textarea></td>
        </tr>
    </tbody>
</table>
    <button id="dcbuton" type="button">Set default colors</button>
    <input type="submit" value="Update colors thumbnails"/>
    </form>
<hr>
<hr>
<?php
    foreach($colorThumbnails as $index => $image) {
        echo "{$colors[$index]}";
        echo htmlLinkImage($image);
        echo "<br>";
    }
?>

<script>
        var defaultColors = {
            "Đỏ" : "https://vn-live.slatic.net/p/55ad4226db6a2999dab1895459833d14.jpg",
            "Đen" : "https://vn-live.slatic.net/p/89b7e4093c507af559543508ee922cc1.jpg",
            "Vàng" : "https://vn-live.slatic.net/p/0f0c8470b27f4ca6efc08770598ea2e2.jpg",
            "Xanh matcha" : "https://vn-live.slatic.net/p/dc686e7f51bb8f990a0f5c6f8f2400c3.jpg",
            "Xanh Matcha" : "https://vn-live.slatic.net/p/dc686e7f51bb8f990a0f5c6f8f2400c3.jpg",
            "Tím" : "https://vn-live.slatic.net/p/28a765425981cb1817bec161eb101c8a.jpg",
            "Xanh navy" : "https://vn-live.slatic.net/p/9c14256da7d15e7574c151c1a705eca3.jpg",
            "Xanh Navy" : "https://vn-live.slatic.net/p/9c14256da7d15e7574c151c1a705eca3.jpg",
            "Xanh bóng đêm" : "https://vn-live.slatic.net/p/e4cde3547c935558227ecc930419df17.jpg",
            "Xanh Bóng Đêm" : "https://vn-live.slatic.net/p/e4cde3547c935558227ecc930419df17.jpg",
            "Trắng" : "https://vn-live.slatic.net/p/ad489d3e4167065cd26bb80c8d56b242.jpg"
        };

        $( "#dcbuton" ).click(function() {
            console.log("ok1");
            var colors = $('#colorsTxt').val().split('\n');
            var urls = [];
            colors.forEach(function(color, index){
                if(color in defaultColors) {
                    urls.push(defaultColors[color]);
                } else {
                    urls.push("");
                } 
            });

            $('#urlsTxt').val(urls.join("\n"));
        });
    </script>

</div>
</body>
</html>