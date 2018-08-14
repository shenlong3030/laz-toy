<?php
include_once "check_token.php";
require_once('_main_functions.php');

//include_once "src/show_errors.php";

$count = 0;

$offset = $_GET['offset'] ? $_GET['offset'] : 0;
$limit = $_GET['limit'] ? $_GET['limit'] : 200;

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
    <title><?php $status = $_GET['status'] ? $_GET['status'] : 'pending'; echo "LAZ ĐH $status";?></title>
    <link rel="shortcut icon" type="image/x-icon" href="./ico/cart.ico" />
    <link href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <!-- bxSlider Javascript file -->
    <script src="./js/controls.js"></script>
    <script src="./js/jquery.tablesorter.min.js"></script>

    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
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
$dict = getOrders($GLOBALS['accessToken'], 'canceled', 0, 10, $sortBy);
echo '<br>';
$cOrders = $dict['data']['orders'];
usort($cOrders, function($a, $b) {
    return $b['updated_at'] <=> $a['updated_at'];
});
printOrders($cOrders , 0, $needFullOrderInfo, $status);
echo '</tbody></table></div><hr>';
//========================================================================

//========================================================================
// print selected order list
echo "<div class='contentlist' style='font:14px/21px Arial,tahoma,sans-serif;'>";
echo '<table border="1"><tbody>';

if($status == 'all') {
    // get pending orders
    $pOrders = array();
    for($i=0; $i<$limit; $i+=100) {
        $dict = getOrders($accessToken, "pending", $i, 100, $sortBy);
        $pOrders = array_merge($pOrders, $dict['data']['orders']);
        if($dict['data']['count'] < 100) {
            break; // nothing more to get, stop getOrders
        }
    }
    //var_dump($pOrders);
    
    // get ready_to_ship orders
    $rOrders = array();
    for($i=0; $i<$limit; $i+=100) {
        $dict = getOrders($accessToken, "ready_to_ship", $i, 100, $sortBy);
        $rOrders = array_merge($rOrders, $dict['data']['orders']);
        if($dict['data']['count'] < 100) {
            break; // nothing more to get, stop getOrders
        }
    }
    //var_dump($rOrders);
    
    $allOrders = array_merge($pOrders, $rOrders);
    usort($allOrders, function($a, $b) {
        return $b['created_at'] <=> $a['created_at'];
    });

    $GLOBALS['count'] += count($allOrders);
    printOrders($allOrders, 0, $needFullOrderInfo, $status);
} else {
    $orders = array();
    for($i=0; $i<$limit; $i+=100) {
        $dict = getOrders($accessToken, $status, $i + $offset, 100, $sortBy);
        //var_dump($dict);
        
        $orders = array_merge($orders, $dict['data']['orders']);
        if($dict['data']['count'] < 100) {
            break; // nothing more to get, stop getOrders
        }
    }
    $GLOBALS['count'] += count($orders);
    printOrders($orders, 0, $needFullOrderInfo, $status);
}
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
//========================================================================
</script>
</body>
</html>