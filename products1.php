<?php
include_once "check_token.php";
require_once('_main_functions.php');
//include_once "src/show_errors.php";

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
      margin-top: 150px;
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

?>

<body>
    <div class="nav">
    <div class="control-bar-2">
        <iframe id="responseIframe" name="responseIframe" width="100%" height="30"></iframe>
    </div>
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
    
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
    Search <input class="search text on" type="text" name="q" placeholder="Search by name" size="100" value="<?php echo $_GET['q']; ?>">
    <input type="hidden" name="status" value="<?php echo $_GET['status']; ?>">
    <input type="hidden" name="offset" value="0">
    
    <input id="cbshowthumbnail" type="checkbox" name="showthumbnail" value="1" checked="checked"> I Show thumbnail
    <input id="cbshowshopsku" type="checkbox" name="showshopsku" value="1" checked="1>Show shopsku
    <input id="cbshowall" type="checkbox" name="showall" value="1" checked="1">Show all
    <input id="cbfulledit" type="checkbox" name="fulledit" value="1">Edit mode

    <textarea class="nowrap search skus" name="skus" placeholder="Input SKUs separated by line" rows="20" cols="50"><?php echo implode("\n", $skus);?></textarea><br>
    <input id="cbbyskus" type="checkbox" name="byskus" value="1">Search by SKUs
    <input class="padding" type="submit">
    </form>
    <div class="control-bar-1"><input type="text" id="myNameInput" placeholder="Filter name"><input type="text" id="myQuantityInput" placeholder="Filter quantity"> <span style="padding:5px;">Page</span></div>
    
    </div>

    <div class="mainContent">

<?php
echo '<table id="myTable" class="tablesorter" border="1" style="width:110%">';
echo '<thead><tr>';
    echo '<th class="sku on">&#x25BC SKU</th>'; // display:visible
    echo '<th class="sku">&#x25BC SHOPSKU</th>';    // SHOPSKU display:none
    
    echo '<th>&#x25BC QTY</th>';
    echo '<th>&#x25BC UpdateQty</th>';
    
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
    printProducts($list);
}

echo '</tbody>';
echo '</table><br><hr>';
?>

<!––document of tablesorter, see http://tablesorter.com/docs/-->
<script type="text/javascript">

$(function(){
    
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

function getURLWithNewQueryString(key, newvalue){
    var baseUrl = [location.protocol, '//', location.host, location.pathname].join('');
    /*
     * queryParameters -> handles the query string parameters
     * queryString -> the query string without the fist '?' character
     * re -> the regular expression
     * m -> holds the string matching the regular expression
     */
    var queryParameters = {}, queryString = location.search.substring(1),
        re = /([^&=]+)=([^&]*)/g, m;

    // Creates a map with the query string parameters
    while (m = re.exec(queryString)) {
        queryParameters[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
    }

    // Add new parameters or update existing ones
    queryParameters[key] = newvalue;

    /*
     * Replace the query portion of the URL.
     * jQuery.param() -> create a serialized representation of an array or
     *     object, suitable for use in a URL query string or Ajax request.
     */
    return baseUrl + '?' + $.param(queryParameters);
}

function getQueryStringValue(key){
    /*
     * queryParameters -> handles the query string parameters
     * queryString -> the query string without the fist '?' character
     * re -> the regular expression
     * m -> holds the string matching the regular expression
     */
    var queryParameters = {}, queryString = location.search.substring(1),
        re = /([^&=]+)=([^&]*)/g, m;

    // Creates a map with the query string parameters
    while (m = re.exec(queryString)) {
        queryParameters[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
    }

    // Add new parameters or update existing ones
    return queryParameters[key];
}

function getQueryStringOffsetValue() {
    var value = getQueryStringValue('offset');
    return value ? value : <?php echo $offset;?>;
}

function getQueryStringLimitValue() {
    var value = getQueryStringValue('limit');
    return value ? value : <?php echo $limit;?>;
}

setTimeout(function(){
    var pagecount = <?php echo $pagecount;?>;
    
    for (var i = 0; i < pagecount; i++) {
      var newLink = $("<a />", {
          class : "pagenumber",
          name : "link",
          href : getURLWithNewQueryString("offset", i*getQueryStringLimitValue()),
          text : (i+1)
      });
      if(getQueryStringOffsetValue() == i*getQueryStringLimitValue()) {
        newLink.addClass('disabled');
      }
      $('.control-bar-1').append(newLink);
    }

    var showAllLink = $("<a />", {
          class : "pagenumber",
          name : "link",
          href : getURLWithNewQueryString("limit", "no"),
          text : "ALL"
      });
    $('.control-bar-1').append(showAllLink);

    $('#count').text("<?php echo count($list)." of ".$total; ?>");
}, 1000);
</script>
</div>
</body>
</html>