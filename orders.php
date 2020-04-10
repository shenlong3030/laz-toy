<?php
include_once "check_token.php";
require_once('_main_functions.php');

//include_once "src/show_errors.php";

$count = 0;

$offset = $_GET['offset'] ? $_GET['offset'] : 0;
$limit = $_GET['limit'] ? $_GET['limit'] : 100;

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
</head>
<body>

<?php include('src/nav.php');?>
<hr>

<div class="menu">
<a href="<?php echo $_SERVER['PHP_SELF'];?>?status=all&needfull=1&shopid=<?php echo $GLOBALS['shopid']?>">Tất cả</a>
<?php if($GLOBALS['status']=='all') echo '<span class="count" id="'.$GLOBALS['status'].'">(0)</span>';?>   

<a href="<?php echo $_SERVER['PHP_SELF'];?>?status=pending&needfull=1">Đơn hàng mới</a>
<span class="count" id="pending"></span>

<a href="<?php echo $_SERVER['PHP_SELF'];?>?status=ready_to_ship&needfull=1&shopid=<?php echo $GLOBALS['shopid']?>">Đơn hàng SS giao đi</a>
<span class="count" id="ready_to_ship"></span>

<a href="<?php echo $_SERVER['PHP_SELF'];?>?status=shipped&needfull=1&shopid=<?php echo $GLOBALS['shopid']?>">Đơn hàng đang đi phát</a>
<?php if($GLOBALS['status']=='shipped') echo '<span class="count" id="'.$GLOBALS['status'].'">(0)</span>';?>

<a href="<?php echo $_SERVER['PHP_SELF'];?>?status=canceled&needfull=1&shopid=<?php echo $GLOBALS['shopid']?>">Đơn hàng huỷ</a>
<?php if($GLOBALS['status']=='canceled') echo '<span class="count" id="'.$GLOBALS['status'].'">(0)</span>';?>

<a href="<?php echo $_SERVER['PHP_SELF'];?>?offset=0&limit=500&status=delivered&sortby=updated_at&shopid=&needfull=0<?php echo $GLOBALS['shopid']?>">Đơn hàng phát thành công</a>
<?php if($GLOBALS['status']=='delivered') echo '<span class="count" id="'.$GLOBALS['status'].'">(0)</span>';?>
</div>
<hr>

<?php 

//========================================================================
// navigation
include "nav.php";
//========================================================================

//========================================================================
// print 10 canceled orders
echo "<div class='contentlist' style='font:14px/21px Arial,tahoma,sans-serif; height:400px; overflow:auto;'>";
echo "<p><b>10 đơn hàng bị huỷ gần đây nhất<b></p>";
echo '<table border="1"><tbody>';

$token = $GLOBALS["accessToken"];
$list = getOrders($token, 'canceled', 0, 10, $sortBy, 1);
printOrders($token, $list , 0, $status);

echo '</tbody></table></div><hr>';
//========================================================================

//========================================================================
// print selected order list
echo "<div class='contentlist' style='font:14px/21px Arial,tahoma,sans-serif;'>";
echo '<table border="1"><tbody>';

$list = null;
if($status == 'all') {
    // get pending orders
    $pendingOrders = getAllOrders($accessToken, "pending", $sortBy, $needFullOrderInfo);
    $readyOrders = getAllOrders($accessToken, "ready_to_ship", $sortBy, $needFullOrderInfo);
    
    $list = array_merge($pendingOrders, $readyOrders);
} elseif($status == 'pending' || $status == 'ready_to_ship' || $status == 'shipped') {
    $list = getAllOrders($accessToken, $status, $sortBy, $needFullOrderInfo);
} else {
    $list = array();
    for($i=0; $i<$limit; $i+=100) {
        $nextlist = getOrders($accessToken, $status, $i + $offset, 100, $sortBy, $needFullOrderInfo);
        $list = array_merge($list, $nextlist);

        if(count($nextlist) < 100) {
            break;
        }
    }
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

echo '</tbody></table><br><hr>';
echo "</div>";
//========================================================================
?>

<script type="text/javascript">
//========================================================================
// update COUNT
setTimeout(function(){
    $("#<?php echo $GLOBALS['status'];?>").text("(<?php echo $GLOBALS['count'];?>)");
}, 1000);

$('table').on('click', '.paymentMethod', function(e){
   //$(this).closest('tr').remove();
   $(this).closest('tr').addClass("hide");
})

$('table').on('click', '.age', function(e){
   //$(this).closest('tr').remove();
   $(this).closest('tr').siblings().removeClass("hide");
})

//========================================================================
</script>
</body>
</html>