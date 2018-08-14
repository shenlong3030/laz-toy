<?php
include_once "check_token.php";

$status = $_GET['status'] ? $_GET['status'] : 'pending';

function getOrderLinkPostfix(){
    $postfix = '/15605';
    if($GLOBALS['shopid'] == '01') {
        $postfix = '/100021640';
    }
    return $postfix;
}

function getOrders($accessToken, $status = 'pending', $offset = 0, $limit = 100, $sortBy = 'created_at'){
    $c = new LazopClient($GLOBALS['apiUrl'],$GLOBALS['appKey'],$GLOBALS['appSecret']);
    $request = new LazopRequest('/orders/get','GET');
    $request->addApiParam('created_after','2018-01-01T09:00:00+08:00');
    if($status != 'all') {
    	$request->addApiParam('status',$status);
    }
    $request->addApiParam('sort_direction','DESC');
    $request->addApiParam('offset',$offset);
    $request->addApiParam('limit', $limit);
    $request->addApiParam('sort_by', $sortBy);
    $response = json_decode($c->execute($request, $accessToken), true);
    return $response;
}

function printOrderRows($orders, $offset = 0, $needFullOrderInfo = 0, $status = "") {
    foreach($orders as $index=>$order) {
        $GLOBALS['count'] += 1;
        $orderId = $order['order_id'];
        $orderNumber = $order['order_number'];
        $shipping = $order['address_shipping'];
        $address = $shipping['address1'].'<br>,'.$shipping['address2'].','.$shipping['address3'].','.$shipping['address4'].','.$shipping['address5'];
        $cusName = $shipping['first_name'].' '.$shipping['last_name'];
        $cusPhone = preg_replace('/(^84)(\d+)/i', '$2', $shipping['phone']);
        $itemCount = $order['items_count'];
        $orderStatus = $order['statuses'][0];
        $paymentMethod = $order['payment_method'];
        $item = $needFullOrderInfo ? getOrderItems($_COOKIE["access_token"], $orderId) : array();
        $price = $order['price'];
        
        echo '<tr class="'.$orderStatus.'">';
        echo '<td class="index">'.($offset+$index+1).'</td>';
        echo '<td class="order"><a target="_blank" href="https://sellercenter.lazada.vn/order/detail/'.$orderNumber . getOrderLinkPostfix().'">'.$orderNumber.'</a></td>';
        
        if($needFullOrderInfo) {
            echo '<td>'.$item['TrackingCode'].'</td>';
            echo '<td></td>'; // tracking code link cell
            
            echo '<td></td>'; // $address cell
            
            echo '<td>'.$cusName.'</td>';
            echo '<td><b>'.$cusPhone.'</b></td>';
            echo '<td>'.$item['ItemName'].'</td>';
            echo '<td>'.$paymentMethod.'</td>';
            echo '<td>'.$item["img"].'</td>';
        }
        
        $date1 = new DateTime($order["created_at"]);
        $date2 = new DateTime();
        $date2->modify('+ 7 hour');
        $days  = $date2->diff($date1)->format('%a');
        $hours = $date2->diff($date1)->format('%h');
        $txtNgay = ($days?($days.' ngày '):'');
        $txtGio = ($hours?($hours.' giờ'):'');
        
        echo '<td>'.$txtNgay.$txtGio.'</td>';
        echo '<td><b>'.$order["created_at"].'</b></td>';
        echo '<td>'.$order["updated_at"].'</td>';
        echo '<td>'.$price.'</td>';
        echo '<td>'.$itemCount.'</td>';
        echo '</tr>';
    }
}

function getOrderItems($accessToken, $orderId){
    $c = new LazopClient($GLOBALS['apiUrl'],$GLOBALS['appKey'],$GLOBALS['appSecret']);
    $request = new LazopRequest('/order/items/get','GET');
    $request->addApiParam('order_id', $orderId);
    $response = json_decode($c->execute($request, $accessToken), true);
    //var_dump($response);
    return getOrderItemsInfo($response);
}

function getOrderItemsInfo($dict) {
    $info = array();
    $info["ItemName"] = "";
    $info["TrackingCode"] = "";
    $info["img"] = "";
    foreach($dict['data'] as $index=>$item) {
        // extract color from 'Variation'
        preg_match('/(màu:.+)/', $item['variation'], $out);
        $color = count($out) >= 2 ? $out[1] : '';
        
        if($item['status'] != 'canceled') {
            $info["ItemName"] .= '<p>'.$item['name'].' '.$color.'</p>';
            $info["TrackingCode"] = $item['tracking_code'];
        }
        
        // show image of all items, include canceled items
        $info["img"] .= '<a target="_blank" href="'.$item['product_main_image'].'"><img border="0" src="'.$item['product_main_image'].'" height="50"></a><br>';
    }
    return $info;
}
?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php $status = $_GET['status'] ? $_GET['status'] : 'pending'; echo "LAZ ĐH $status";?></title>
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
        tr.canceled {background-color:darkgray;}
        tr.ready_to_ship td.index {background-color:gold;}
    </style>
</head>
<body>
<a class=menu href="<?php echo $_SERVER['PHP_SELF'];?>?status=pending&needfull=1&shopid=<?php echo $GLOBALS['shopid']?>">Đơn hàng mới</a>
<?php if($GLOBALS['status']=='pending') echo '<span class="count" id="'.$GLOBALS['status'].'">(0)</span>';?>
<a class=menu href="<?php echo $_SERVER['PHP_SELF'];?>?status=ready_to_ship&needfull=1&shopid=<?php echo $GLOBALS['shopid']?>">Đơn hàng SS giao đi</a>
<?php if($GLOBALS['status']=='ready_to_ship') echo '<span class="count" id="'.$GLOBALS['status'].'">(0)</span>';?>
<a class=menu href="<?php echo $_SERVER['PHP_SELF'];?>?status=shipped&needfull=1&shopid=<?php echo $GLOBALS['shopid']?>">Đơn hàng đang đi phát</a>
<?php if($GLOBALS['status']=='shipped') echo '<span class="count" id="'.$GLOBALS['status'].'">(0)</span>';?>
<a class=menu href="<?php echo $_SERVER['PHP_SELF'];?>?status=canceled&needfull=1&shopid=<?php echo $GLOBALS['shopid']?>">Đơn hàng huỷ</a>
<?php if($GLOBALS['status']=='canceled') echo '<span class="count" id="'.$GLOBALS['status'].'">(0)</span>';?>
<a class=menu href="<?php echo $_SERVER['PHP_SELF'];?>?status=delivered&sortby=updated_at&limit=500&offset=0&shopid=<?php echo $GLOBALS['shopid']?>">Đơn hàng phát thành công</a>
<?php if($GLOBALS['status']=='delivered') echo '<span class="count" id="'.$GLOBALS['status'].'">(0)</span>';?>
<hr>
<?php 
//ini_set('display_startup_errors', 1);
//ini_set('display_errors', 1);
//error_reporting(-1);

$count = 0;

$offset = $_GET['offset'] ? $_GET['offset'] : 0;
$limit = $_GET['limit'] ? $_GET['limit'] : 200;

// pending, canceled, ready_to_ship, delivered, returned, shipped, failed
$status = $_GET['status'] ? $_GET['status'] : 'pending';

// created_at, updated_at
$sortBy = $_GET['sortby'] ? $_GET['sortby'] : 'created_at';

$needFullOrderInfo = $_GET['needfull'] ? $_GET['needfull'] : 1;

echo "<div style ='font:14px/21px Arial,tahoma,sans-serif;'>";
echo '<table border="1"><tbody>';

/*
$arr1 = array ('a'=>1,'b'=>2,'c'=>3,'d'=>4,'e'=>5);
file_put_contents("array.json",json_encode($arr1));
# array.json => {"a":1,"b":2,"c":3,"d":4,"e":5}
$arr2 = json_decode(file_get_contents('array.json'), true);
$arr1 === $arr2 # => true
*/

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

// get canceled orders
$cOrders = array();
$dict = getOrders($accessToken, "canceled", 0, 50, $sortBy);
$cOrders = array_merge($cOrders, $dict['data']['orders']);
$cOrderNumbers = (array_column($cOrders, 'order_number'));

$allOrders = array_merge($pOrders, $rOrders);
$allOrderNumbers = (array_column($allOrders, 'order_number'));
// sort DESC by created_at
usort($allOrders, function($a, $b) {
    return $b['created_at'] <=> $a['created_at'];
});
//var_dump($allOrders);


// load saved orders from JSON file
$savedOrders = json_decode(file_get_contents('orders.json'), true);
$savedOrdersNumbers = (array_column($savedOrders, 'order_number'));


// check if have canceled orders
foreach ($savedOrders as $key->$order) {
    if(in_array($order['order_number'], $cOrderNumbers)) {
        $order['statuses'][0] = 'canceled';
    }
}

// looking and adding new orders
foreach ($allOrders as $key->$order) {
    if(!in_array($order['order_number'], $savedOrdersNumbers)) {
        array_push($savedOrders, $order);
    }
}
// sort DESC by created_at
usort($savedOrders, function($a, $b) {
    return $b['created_at'] <=> $a['created_at'];
});

// print all orders
printOrderRows($allOrders, 0, $needFullOrderInfo, $status);

// save all orders to JSON file
//file_put_contents("orders.json",json_encode($savedOrders));

echo '</tbody></table><br><hr>';
echo "</div>";
?>

<script type="text/javascript">
setTimeout(function(){
    $("#<?php echo $GLOBALS['status'];?>").text("(<?php echo $GLOBALS['count'];?>)");
}, 1000);
</script>
</body>
</html>