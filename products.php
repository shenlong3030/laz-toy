<?php
include_once "check_token.php";
//include_once "src/show_errors.php";
require_once('_main_functions.php');

?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PRODUCTS</title>
    <link rel="shortcut icon" type="image/x-icon" href="./ico/product.ico" />

    <?php include('src/head.php');?>

    <style>
    .control-bar{
      position: fixed;
      top: 0;
      left: 0;
      overflow: hidden;
      background-color: #FFF;
      width: 100%;
      z-index: 10;
    }
    .mainContent{
      margin-top: 190px;
    }
    </style>
</head>
<?php
$count = 0;

// Pay no attention to this statement.
// It's only needed if timezone in php.ini is not set correctly.
date_default_timezone_set("UTC");
    
$offset = $_GET['offset'] ? $_GET['offset'] : 0;
$limit = $_GET['limit'] ? $_GET['limit'] : 100;
$status = $_GET['status'] ? $_GET['status'] : 'sold-out';

$q = $_GET['q'] ? $_GET['q'] : '';
$byskus = $_GET['byskus'] ? $_GET['byskus'] : 0;

$input = val($_GET['skus']);
$skus = array_filter(explode("\n", str_replace("\r", "", $input)));

$filterName = val($_GET['filterName'], "");
$filterQty = val($_GET['filterQty'], "");

$item_id = $_GET['item_id'] ? $_GET['item_id'] : '';
$nochild = $_GET['nochild'] ? $_GET['nochild'] : 0;
$after = $_GET['after'] ? $_GET['after'] : '';

?>

<body>
  <div class="control-bar">
    <?php include('src/nav.php');?>
    <div class="control-bar-2">
      <iframe id="responseIframe" name="responseIframe" width="100%" height="30"></iframe>
      <?php 
        $currentLink = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; 
        $bareLink = strtok($currentLink, '?');
        $linkSoldout = $bareLink . '?status=sold-out';
        $linkRejected = $bareLink . '?status=rejected';
        $linkAll = $bareLink . '?status=all';
        $linkInactive = $bareLink . '?status=inactive';
        $linkPending = $bareLink . '?status=pending';
        $linkImageMissing = $bareLink . '?status=image-missing';

        $linkAllClass = ($status=="all")?" disabled":"";
        $linkSoldoutClass = ($status=="sold-out")?" disabled":"";
        $linkRejectedClass = ($status=="rejected")?" disabled":"";
        $linkInactiveClass = ($status=="inactive")?" disabled":"";
        $linkPendingClass = ($status=="pending")?" disabled":"";
        $linkImageMissingClass = ($status=="image-missing")?" disabled":"";

        echo "<a tabindex='-1' class='padding $linkAllClass' href='$linkAll'>Tất cả</a>";
        echo "<a tabindex='-1' class='padding $linkSoldoutClass' href='$linkSoldout'>Hết hàng</a>";
        echo "<a tabindex='-1' class='padding $linkRejectedClass' href='$linkRejected'>Bị từ chối</a>";
        echo "<a tabindex='-1' class='padding $linkInactiveClass' href='$linkInactive'>Đang tắt</a>";
        echo "<a tabindex='-1' class='padding $linkPendingClass' href='$linkPending'>Đang chờ duyệt</a>";
        echo "<a tabindex='-1' class='padding $linkImageMissingClass' href='$linkImageMissing'>Thiếu ảnh</a>";
      ?> 
    </div>

    <form id="searchForm" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
    Search <input id="q" class="search text on" type="text" name="q" placeholder="Search by name" size="100" value="<?php echo $_GET['q']; ?>">
    <input type="hidden" name="status" value="<?php echo $_GET['status']; ?>">
    <input id="offset" type="hidden" name="offset" value="<?php echo $offset; ?>">
    <input id="limit" type="hidden" name="limit" value="<?php echo $limit; ?>">
    <input id="nochild" type="hidden" name="nochild" value="<?php echo $nochild; ?>">
    
    <input id="cbshowextracol" type="checkbox" name="cbshowextracol" value="1">Show extra columns
    <input id="cbfulledit" type="checkbox" name="cbfulledit" value="1">Edit mode

    <br>Filter 
    <input type="text" id="filterName" name="filterName" placeholder="Name contain" value="<?php echo $filterName; ?>">
    <input type="text" id="filterQty" name="filterQty" placeholder="Quantity less than" value="<?php echo $filterQty; ?>"> 

    <textarea class="nowrap search skus" name="skus" placeholder="Input SKUs separated by line" rows="20" cols="50"><?php echo implode("\n", $skus);?></textarea><br>
    <input id="cbbyskus" type="checkbox" name="byskus" value="1">Search by SKUs
    <button id="searchBtn" class="padding" type="button">Search</button>
    </form>
    <div class="control-bar-1">
    <span style="padding:5px;">Page</span>
    </div>
  </div>

  <div class="mainContent">
    <button id="copy-sku-btn">Copy SKU to Clipboard</button>
<?php

$list = null;
$total = 0;
$token = $GLOBALS["accessToken"];
$pagecount = 1;

$options = array(
    'status' => $status,
    'offset' => $offset,
    'limit' => $limit,
);
if($after) {
    $options['after'] = $after . "T00:00:00+0700";
}

//$products = getProducts($accessToken, '', $options);

if($item_id) {
    $sp = getProduct($token, "", $item_id);
    printProducts(array($sp));
} else {
    if($byskus) {
        $options['skulist'] = $skus;
        $list = getProducts($token, "", $options);
        $total = count($list);
    } elseif ($limit == "no") {
        $list = getProducts($token, $q, $options);
        $total = count($list);
    } else {
        $list = getProductsPaging($token, $q, $options, $total);
        $pagecount = ceil($total/$limit);
    }

    if(count($list)) {
        $filter = array(
          "filterName" => $filterName,
          "filterQty" => $filterQty
        );

        $list = reArrangeProducts($list);
        
        printProducts($list, $nochild);
    }
}


?>

<script type="text/javascript">

function search() {
    $("#limit").val("");
    $("#offset").val(0);
    $("#searchForm").submit();
}

$(function(){
  $('#copy-sku-btn').click(function (e) {
      var text = "";
      $("#tableProducts").find("td.sku").each(function(){
          text = text + $(this).text() + "\n";
      });
      console.log("copy text : " + text );
      copyToClipBoard(text);
  });

  $('#q').keypress(function (e) {
    if (e.which == 13) {
      search();
      return false;    //<---- this line is the same as calling e.preventDefault and e.stopPropagation()
    }
  });
  $("#searchBtn").click(function(){
      search();
  });

  // document of tablesorter, see http://tablesorter.com/docs/
  // sort column 0
  $('#tableProducts').tablesorter();
  //$("#tableProducts").tablesorter( {sortList: [[0,0]]} );

  // EDIT MODE
  $('#cbfulledit').change(function(){
    $('.editmode').toggleClass('on');
  });

  // SHOW EXTRA COLUMNs
  $('#cbshowextracol').change(function(){
    $('.ex').toggleClass('on');
  });

  // SHOW SEARCH BY LIST
  $('#cbbyskus').change(function(){
    $('.search').toggleClass('on');
  });
  
  $('#btn_copy_url').click(function (e) {
    var text = "";
    $("table.main").find("td.url").each(function(){
        text = text + $(this).text() + "\n";
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

  // $('table').on('click', '.grouped-icon', function(e){
  //    //$(this).closest('tr').remove();
  //    $('.grouped.child').toggleClass('hide');
  // })

});

function loadPage(page) {
    if(page < 0) {
        $("#offset").val(0);
        $("#limit").val("no");
    } else {
        var limit = $("#limit").val();
        $("#offset").val(page*limit);
    }
    $("#searchForm").submit();
}

setTimeout(function(){
    var pagecount = <?php echo $pagecount;?>;
    for (var i = 0; i < pagecount; i++) {
      var newLink = $("<a />", {
          class : "pagenumber",
          name : "link",
          href : "#",
          text : (i+1),
          onclick : "loadPage(" + i + ")"
      });
      if($("#offset").val() == i * $("#limit").val()) {
        newLink.addClass('disabled');
      }
      $('.control-bar-1').append(newLink);
    }

    var page = -1;
    var showAllLink = $("<a />", {
          class : "pagenumber",
          name : "link",
          href : "#",
          text : "ALL",
          onclick : "loadPage(" + page + ")"
      });
    if($("#limit").val() == "no") {
      showAllLink.addClass('disabled');
    }
    $('.control-bar-1').append(showAllLink);

    $('#count').text("<?php echo count($list)." of ".$total; ?>");
}, 1000);
</script>
</div>
</body>
</html>