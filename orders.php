<?php
//include_once "src/show_errors.php";
include_once "check_token.php";
require_once('_main_functions.php');



$count = 0;

$offset = $_GET['offset'] ? $_GET['offset'] : 0;
$limit = $_GET['limit'] ? $_GET['limit'] : 0;

// pending, canceled, ready_to_ship, delivered, returned, shipped, failed
$status = $_GET['status'] ? $_GET['status'] : 'pending';

// created_at, updated_at
$sortBy = $_GET['sortby'] ? $_GET['sortby'] : 'created_at';
$needFullOrderInfo = isset($_GET['needfull']) ? $_GET['needfull'] : 1;

?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ORDERS</title>
    <link rel="shortcut icon" type="image/x-icon" href="./ico/cart.ico" />

    <?php include('src/head.php');?>

    <!–– Hide column with CSS 
        column 1 : order_id
        column 5 : tracking_link
        column 6 : address
    ––>
    <style>
        table th:nth-child(5),
        table td:nth-child(5) {
            display: none;
        }
    </style>
</head>
<body>

<?php include('src/nav.php');?>
<hr>

<div class="menu">
<span>
<a href="<?php echo $_SERVER['PHP_SELF'];?>?status=all&needfull=1&shopid=<?php echo $GLOBALS['shopid']?>">Tất cả</a>
<?php if($GLOBALS['status']=='all') echo '<span class="count" id="'.$GLOBALS['status'].'">(0)</span>';?>  
</span> 

<span>
<a href="<?php echo $_SERVER['PHP_SELF'];?>?status=pending&needfull=1">Đơn hàng mới</a>
<span class="count" id="pending"></span>
</span>

<span>
<a href="<?php echo $_SERVER['PHP_SELF'];?>?status=repacked&needfull=1">Tạo lại gói hàng</a>
<span class="count" id="repacked"></span>
</span>

<span>
<a href="<?php echo $_SERVER['PHP_SELF'];?>?status=toship&needfull=1&shopid=<?php echo $GLOBALS['shopid']?>">Đóng gói và SS</a>
<span class="count" id="toship"></span>
</span>

<span>
<a href="<?php echo $_SERVER['PHP_SELF'];?>?status=shipped&needfull=1&shopid=<?php echo $GLOBALS['shopid']?>">Đơn hàng đang đi phát</a>
<span class="count" id="shipped"></span>
</span>

<span>
<a href="<?php echo $_SERVER['PHP_SELF'];?>?status=canceled&needfull=1&shopid=&sortby=updated_at<?php echo $GLOBALS['shopid']?>">Đơn hàng huỷ</a>
<span class="count" id="canceled"></span>
</span>

<span>
<a href="<?php echo $_SERVER['PHP_SELF'];?>?offset=0&limit=100&status=delivered&sortby=updated_at&shopid=&needfull=1<?php echo $GLOBALS['shopid']?>">Đơn hàng phát thành công</a>
<span class="count" id="delivered"></span>
</span>

<span>
<a href="<?php echo $_SERVER['PHP_SELF'];?>?offset=0&limit=100&status=failed&sortby=updated_at&shopid=&needfull=1<?php echo $GLOBALS['shopid']?>">Đơn hàng thất bại</a>
<span class="count" id="failed"></span>
</span>

</div>
<hr>

<?php 

//========================================================================
// navigation
include "nav.php";
//========================================================================

//========================================================================
// print 10 canceled orders
echo "<div class='canceledList' style='font:14px/21px Arial,tahoma,sans-serif; height:400px; overflow:auto;'>";
echo "<p><b>10 đơn hàng bị huỷ gần đây nhất<b></p>";

$token = $GLOBALS["accessToken"];
$list = getOrders($token, 'canceled', 10, 'updated_at', 1);
printOrders($token, $list , 0, $status);
echo "</div>";
//========================================================================

//========================================================================
// print selected order list
echo "<div class='ordersList' style='font:14px/21px Arial,tahoma,sans-serif;'>";


 // status: unpaid, pending, canceled, ready_to_ship, delivered, returned, shipped, failed. 
 // New status: topack, toship

$list = null;

switch ($status) {
    case 'all':
        // get pending orders
        if(empty($limit)) {
            $limit = 500;
        }
        $list1 = getOrders($accessToken, "pending", $limit, $sortBy, $needFullOrderInfo, "ASC");
        $list2 = getOrders($accessToken, "repacked", 30, $sortBy, $needFullOrderInfo, "ASC");
        //$readyOrders = getAllOrders($accessToken, "ready_to_ship", $sortBy, $needFullOrderInfo);
        $list3 = getOrders($accessToken, "toship", $limit, $sortBy, $needFullOrderInfo, "ASC");
        
        $list = array_merge($list1, $list2, $list3);
        break;
    case "pending":
    case "toship":
    case "topack":
        if(empty($limit)) {
            $limit = 500;
        }
        $list = getOrders($accessToken, $status, $limit, $sortBy, $needFullOrderInfo, "ASC");
        break;
    default:
        if(empty($limit)) {
            $limit = 100;
        }
        $list = getOrders($accessToken, $status, $limit, $sortBy, $needFullOrderInfo);
}

// resort merged list
// usort($list, function($a, $b) {
//     return strcmp($b['created_at'] > $a['created_at'])*(-1);
// });

$GLOBALS['count'] = count($list);

echo "<h2>";
switch ($status) {
    case 'pending':
        echo "Đơn hàng mới";
        break;
    case 'ready_to_ship':
        echo "Đơn hàng SS giao đi";
        break;
    case 'shipped':
        echo "Đơn hàng đang đi phát";
        break;
    case 'all':
        echo "Tất cả đơn hàng";
        break;
}
echo "(" . count($list) . ")";
echo "</h2>";

printOrders($token, $list, 0, $status);

echo "</div>";
//========================================================================
?>

<script type="text/javascript">
//========================================================================
// update COUNT
setTimeout(function(){
    var st = "<?php echo $GLOBALS['status'];?>";
    $("#" + st).text("(<?php echo $GLOBALS['count'];?>)");
    $("#" + st).parent().css("background","lightgreen");
}, 1000);

$('table').tablesorter({ sortList: [[0,0]]});

function findDuplicates(arr) {
    var duplicates_list = [];
    var unique_list = [];
    $.each(arr, function(key, value) {
        if ($.inArray(value, unique_list) == -1) {
            unique_list.push(value);
        } else {
            if ($.inArray(value, duplicates_list) == -1) {
                duplicates_list.push(value);
            }
        }
    });
    return duplicates_list;
}

var body = $("body");
var container = $('<div id="dialog"></div>');
container.prependTo(body);
container.dialog({
  position: { my: "right", at: "right"}
});

var $btn1 = $('<input id="btn_check_dup" type="button" value="Check duplicate order"/>');
$btn1.appendTo(container);
$('#btn_check_dup').click(function(e) {
    var text = "";
    var arr = $(".ordersList td.order_cus_name").map(function() {
        var name = $(this).text();
        var phone = $(this).next().text();
        return name + phone;
    }).get();
    var duplicateElements = findDuplicates(arr);
    var msg = '<div id="msg"><br>Result:<br>' + duplicateElements.join("<br>") + '</div>';
    msg = $(msg);
    msg.appendTo(container);
});

//========================================================================
</script>
</body>
</html>