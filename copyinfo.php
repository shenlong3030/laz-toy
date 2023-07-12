<?php
include_once "check_token.php";
require_once('_main_functions.php');
//include_once "src/show_errors.php";



// get code param
$sku = $sourcesku = isset($_REQUEST['sourcesku']) ? $_REQUEST['sourcesku'] : '';
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
    $product = $sibling = null;
    if(empty($itemId)) {
        $product = getProduct($accessToken, $sku, null, $qname);
        $itemId = getProductItemId($product);
        $sibling = getProduct($accessToken, null, $itemId, null);
    } else {
        $product = getProduct($accessToken, null, $itemId, $qname);
        $sibling = $product;
    }
    
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


?>

<!DOCTYPE html>
<html leng="en-AU">
<head><meta http-equiv="Content-Type" content="text/html; charset=shift_jis">
    
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>COPY INFO</title>
    <link rel="shortcut icon" type="image/x-icon" href="./ico/tool.ico" />
    <link href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <!-- bxSlider Javascript file -->
    <script src="./js/controls.js"></script>
    <script src="./js/jquery.tablesorter.min.js"></script>

    <style type="text/css">
        a.menu { padding-left: 4em; }
        td.order { padding-left: 1em; padding-right: 1em;}
        span.count {color: red;}
    </style>
</head>
<body>
    <div name="info">
        <textarea id="images" class="nowrap" name="images" rows="6" cols="80"><?php echo implode("\n", $images);?></textarea><br>
        Price <input type="text" id="price" value="<?php echo $price;?>" /> >> 
        <input type="text" id="sale_price" value="<?php echo $sprice;?>" /><br>
        From <input type="text" id="fromdate" value="<?php echo $fromdate;?>" />
        To <input type="text" id="todate" value="<?php echo $todate;?>" /><br>
        <textarea class="nowrap" id="shortdesc" rows="2" cols="80"><?php echo $shortdesc;?></textarea><br>
        <textarea class="nowrap" id="desc" rows="2" cols="80"><?php echo $desc;?></textarea><br>
        Weight: <input type="text" id="weight" size="5" value="<?php echo $weight;?>"/> kg<br>
        Packing Size: <input type="text" id="size_h" size="10" value="<?php echo $h;?>" /> 
        x <input type="text" id="size_w" size="10" value="<?php echo $w;?>" /> 
        x <input type="text" id="size_l" size="10" value="<?php echo $l;?>" /> cm<br>
        Packing Content: <input type="text" id="content" size="70" value="<?php echo $content;?>" /><br>
    <hr>
    </div>


    <div>
        Copy from SKU <input name="sourcesku" size="80" value="<?php echo $sourcesku; ?>"><br><br>
        Copy options:<br>
        <input type="checkbox" name="chk_images" value="1" >Images <input id="imageindexes" size="15" value="1,2,3,4,5,6,7,8"> Main image is [0]<br>
        <input type="checkbox" name="chk_prices" value="2" >Prices<br>
        <input type="checkbox" name="chk_desc" value="3" >Short Descriptions + Descriptions<br>
        <input type="checkbox" name="chk_weight" value="5" >Size, weight, package content<br>
        <br><br>
        To SKUs<br><textarea id="skus" rows="10" cols="80"><?php echo implode("\n", $skus);?></textarea><br>
        <button id="btn_copy">Copy</button>
    </div>
    <hr>
    <iframe id="responseIframe" name="responseIframe" width="1000" height="1000"></iframe>
</body>

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

      var d = new Date();
      var n = d.toLocaleTimeString();
      ajaxManager.addReq({
           type: 'POST',
           url: 'update-api.php',
           data: params,
           success: function(data){
              //console.log(data);
              var res = JSON.parse(data); // data is string, convert to obj
              
              const randomColor = Math.floor(Math.random()*16777215).toString(16);
              const htmlColor = "#" + randomColor;

              if(parseInt(res.code)) {
                myFrame.prepend('<p style="background-color:' + htmlColor + '">' + n + " " + data + " " + params['sku'] + '</p>');
              } else {
                myFrame.prepend('<p style="background-color:' + htmlColor + '">' + n + ' SUCCESS ' + params['sku'] + '</p>');
              }
           },
           error: function(error){
              myFrame.prepend(n + ' FAILED<br>'); 
           }
      });
    }
    //##########################################################################################################

    $('#btn_copy').click(function (e) {
        var myFrame = $("#responseIframe").contents().find('body'); 
        var d = new Date();
        var n = d.toLocaleTimeString();
        myFrame.prepend('### ' + n + ' COPY INFO ###################################################<hr>');
        
        var flag = 0;
        var info = {};
        if($('input[name=chk_images]').is(':checked')) {
            info['images'] = $('#images').val();
            info['imageindexes'] = $('imageindexes').val();
            flag = 1;
        }
        if($('input[name=chk_prices]').is(':checked')) {
            info['price'] = $('#price').val();
            info['sale_price'] = $('#sale_price').val();
            info['fromdate'] = $('#fromdate').val();
            info['todate'] = $('#todate').val();
            flag = 1;
        }
        if($('input[name=chk_desc]').is(':checked')) {
            info['shortdesc'] = $('#shortdesc').val();
            info['desc'] = $('#desc').val();
            flag = 1;
        }

        if($('input[name=chk_weight]').is(':checked')) {
            info['weight'] = $('#weight').val();
            info['size_h'] = $('#size_h').val();
            info['size_w'] = $('#size_w').val();
            info['size_l'] = $('#size_l').val();
            info['content'] = $('#content').val();
            flag = 1;
        }

        if(flag) {
            var skus = $('#skus').val().split('\n');
            skus = skus.filter(function(e){return e}); //remove empty
            if(skus.length == 0) {
                alert("COPY TO WHRE ?");
            }
            
            for(var i=0; i<skus.length; i++) {
                productUpdateWithAjaxQueue({ sku: skus[i], action: "info", info: JSON.stringify(info)});
            }
        } else {
            alert("COPY WHAT ?");
        }
    });

</script>

</html>

