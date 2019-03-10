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

        echo "<a class='padding $linkAllClass' href='$linkAll'>Tất cả</a>";
        echo "<a class='padding $linkSoldoutClass' href='$linkSoldout'>Hết hàng</a>";
        echo "<a class='padding $linkRejectedClass' href='$linkRejected'>Bị từ chối</a>";
        echo "<a class='padding $linkInactiveClass' href='$linkInactive'>Đang tắt</a>";
        echo "<a class='padding $linkPendingClass' href='$linkPending'>Đang chờ duyệt</a>";
        echo "<a class='padding $linkImageMissingClass' href='$linkImageMissing'>Thiếu ảnh</a>";
      ?> 
    </div>

    <form id="searchForm" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
    Search <input id="q" class="search text on" type="text" name="q" placeholder="Search by name" size="100" value="<?php echo $_GET['q']; ?>">
    <input type="hidden" name="status" value="<?php echo $_GET['status']; ?>">
    <input id="offset" type="hidden" name="offset" value="<?php echo $offset; ?>">
    <input id="limit" type="hidden" name="limit" value="<?php echo $limit; ?>">
    
    <input id="cbshowthumbnail" type="checkbox" name="showthumbnail" value="1" checked="checked"> I Show thumbnail
    <input id="cbshowshopsku" type="checkbox" name="showshopsku" value="1" checked="1>Show shopsku
    <input id="cbshowall" type="checkbox" name="showall" value="1" checked="1">Show all
    <input id="cbfulledit" type="checkbox" name="fulledit" value="1">Edit mode

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

<?php
echo '<table id="myTable" class="tablesorter" border="1" style="width:110%">';
echo '<thead><tr>';
    echo '<th class="sku on">&#x25BC SKU</th>'; // display:visible
    echo '<th class="sku">&#x25BC SHOPSKU</th>';    // SHOPSKU display:none
    
    echo '<th>&#x25BC QUANTITY</th>';
    echo '<th>&#x25BC QUANTITY</th>';
    
    echo '<th>&#x25BC NAME<b>(<span id="count" style="color:red">0</span>)</b></th>';
    echo '<th class="name"></th>'; // name form, display:none

    echo '<th class="color"></th>';

    echo '<th class="price"></th>'; // price form, display:none
    echo '<th>&#x25BC</th>';
    echo '<th>&#x25BC</th>';
    
    echo '<th>&#x25BC</th>';
    echo '<th>&#x25BC</th>';
echo '</tr></thead>';
echo '<tbody>';

$list = null;
$total = 0;
$token = $GLOBALS["accessToken"];
if($byskus) {
    $list = getProducts($token, "", $status, $skus);
    $pagecount = 1;
    $total = count($list);
} elseif ($limit == "no") {
    $list = getProducts($token, $q, $status, null);
    $pagecount = 1;
    $total = count($list);
} else {
    $list = getProductsPaging($token, $q, $status, $offset, $limit, $total);
    $pagecount = ceil($total/$limit);
}

if(count($list)) {
    $filter = array(
      "filterName" => $filterName,
      "filterQty" => $filterQty
    );

    $list = reArrangeProducts($list);
    printProducts($list);
}

echo '</tbody>';
echo '</table><br><hr>';
?>

<!––document of tablesorter, see http://tablesorter.com/docs/-->
<script type="text/javascript">

function search() {
    $("#limit").val("");
    $("#offset").val(0);
    $("#searchForm").submit();
}

$(function(){
  $('#q').keypress(function (e) {
    if (e.which == 13) {
      search();
      return false;    //<---- this line is the same as calling e.preventDefault and e.stopPropagation()
    }
  });
  $("#searchBtn").click(function(){
      search();
  });

  // sort column 0
  $('#myTable').tablesorter();
  //$("#myTable").tablesorter( {sortList: [[0,0]]} );

  $('#cbshowshopsku').change(function(){
    $('.sku').toggleClass('on');
  });
  $('#cbfulledit').change(function(){
    $('.price').toggleClass('on');
    $('.name').toggleClass('on');
  });
  $('#cbshowthumbnail').change(function(){
    $('.image').toggleClass('on');
  });
  $('#cbshowall').change(function(){
      // if($('#cbshowthumbnail').is(":checked")) {
      //     $('.image1.thumb').toggleClass('on');
      // } else {
      //     $('.image1.link').toggleClass('on');
      // }
  });
  $('#cbbyskus').change(function(){
    $('.search').toggleClass('on');
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

  $('table').on('click', '.grouped-icon', function(e){
     //$(this).closest('tr').remove();
     $('.grouped.child').toggleClass('hide');
  })


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