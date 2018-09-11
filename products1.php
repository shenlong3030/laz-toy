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
    
    <link href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" rel="stylesheet">
    <link rel="shortcut icon" type="image/x-icon" href="./ico/product.ico" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <!-- bxSlider Javascript file -->
    <script src="./js/controls.js"></script>
    <script src="./js/jquery.tablesorter.min.js"></script>
    
    <!-- bootstrap CSS file -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
    
    <!-- bootstrap JS file -->
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    
    <link rel="stylesheet" type="text/css" href="css/style.css">

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
        <iframe id="responseIframe" name="responseIframe" width="600" height="30"></iframe>

    </div>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
    Search <input class="search text on" type="text" name="q" placeholder="Search by name" size="100" value="<?php echo $_GET['q']; ?>">
    <input type="hidden" name="status" value="all">
    <input type="hidden" name="limit" value="100">
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
echo '<table id="myTable" class="tablesorter" border="1" style="width:100%">';
echo '<thead><tr>';
    echo '<th>&#x25BC</th>';
    echo '<th>&#x25BC</th>';
    echo '<th>&#x25BC <b>(<span id="count" style="color:red">0</span>)</b></th>';
    echo '<th>&#x25BC</th>';
    echo '<th>&#x25BC</th>';
    echo '<th>&#x25BC</th>';
    echo '<th>&#x25BC</th>';
    echo '<th>&#x25BC</th>';
echo '</tr></thead>';
echo '<tbody>';

$list = null;
$total = 0;
if($byskus) {
    foreach($skus as $sku) {
        $item = getProduct($accessToken, $sku);
        if($item) {
            $list[] = $item;
        }
    }
} else {
    $list = getProductsPaging($accessToken, $q, $status, $offset, $limit, $total);
}

if(count($list)) {
    printProducts($list);
}
$pagecount = ceil($total/$limit);

echo '</tbody>';
echo '</table><br><hr>';
?>

<!––document of tablesorter, see http://tablesorter.com/docs/-->
<script type="text/javascript">

$(function(){
    
  // sort column 0
  //$('#myTable').tablesorter();
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

        $('#count').text("<?php echo count($list)." of ".$total; ?>");
    }, 1000);
</script>
</div>
</body>
</html>