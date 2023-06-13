<?php
require_once('src/ArrayToXML.php');
require_once('src/helper.php');
require_once('./_migrate_image_functions.php');
require_once('./_product_internal_functions.php');

//####################################################################
// Get client/request region
//####################################################################

function getClient() {
    return new LazopClient($GLOBALS['apiUrl'],$GLOBALS['appKey'],$GLOBALS['appSecret']);
}

function getRequest($path, $method = 'POST') {
    return new LazopRequest($path, $method);
}

//####################################################################
// Get seller info
//####################################################################

// function getSellerInfo($accessToken) {
//     $c = getClient();
//     $request = getRequest('/seller/get','GET');
//     $response = $c->execute($request, $accessToken);
//     $response = json_decode($response, true);

//     $info = array();
//     if($response["code"] == "0") {
//         $info = $response['data'];
//     }
//     return $info;
// }

//####################################################################
// Get orders region
//####################################################################

function getOrderLinkPostfix(){
    $postfix = '/15605';
    if(isset($GLOBALS['info']['name']) && $GLOBALS['info']['name'] == 'SHSHOP 01') {
        $postfix = '/100021640';
    }
    return $postfix;
}

// valid status : pending, canceled, ready_to_ship, delivered, returned, shipped and failed
function getOrders($accessToken, $status = 'pending', $limit = '100', $sortBy = 'created_at', $needOrderItems = null, $sortDirection = 'DESC'){
    $initPageSize = 50;
    $i = 0;
    $all = array();
    do {
        $pageSize = min($initPageSize, $limit - count($all)); // Ex: limit=120, last pageSize=20 (not 50)
        $list = getOrdersByOffset($accessToken, $status, $i, $pageSize, $sortBy, $needOrderItems, $sortDirection);
        $all = array_merge($all, $list);
        $i += $pageSize;
    } while (count($list) == $pageSize && count($all) < $limit);

    return $all;
}

function getOrdersByOffset($accessToken, $status = 'pending', $offset = 0, $limit = 100, $sortBy = 'created_at', $needOrderItems = null, $sortDirection = 'DESC'){
    $c = getClient();
    $request = getRequest('/orders/get','GET');

    $request->addApiParam('created_after','2018-01-01T09:00:00+08:00');
    if($status != 'all') {
    	$request->addApiParam('status',$status);
    }
    $request->addApiParam('sort_direction', $sortDirection);
    //$request->addApiParam('sort_direction','ASC');

    $request->addApiParam('offset',$offset);
    $request->addApiParam('limit', $limit);
    $request->addApiParam('sort_by', $sortBy);
    $response = json_decode($c->execute($request, $accessToken), true);
    //myvar_dump($response);

    $list = array();
    if($response["code"] == "0") {
        $list = $response['data']['orders'];

        if($needOrderItems) {
            $orderIds = array_column($list, "order_id");
            $ordersItems = getOrdersItems($accessToken, $orderIds);

            foreach ($list as $index => $order) {
                $orderId = $order['order_id'];
                $list[$index]['orderItems'] = $ordersItems[$orderId];
            }
        }
    } else {
        myvar_dump($response);
    }

    return $list;
}

function getOrderItemsInfo($data) {
    $info = array();
    $info["name"] = "";
    $info["tracking_code"] = "";
    $info["shipping_provider_type"] = "";
    $info["img"] = "";
    $info["buyer_id"] = "";
    $info["package_id"] = "";
 
    foreach($data as $index=>$item) {
        // extract color or model from 'Variation'
        $variation = make_short_order_variation($item['variation']);
        preg_match('/KV(\d+)/', $item['sku'], $m);
        $kiotid = count($m)==2 ? $m[1] : "";

        $sellersku = $item['sku'];
        $editLink = "https://$_SERVER[HTTP_HOST]/lazop/update_gui.php?sku=$sellersku";
        $editHtml = '<a target="_blank" href="'.$editLink.'" class="fa fa-edit" style="color:red" tabindex="-1"></a>';

        if(!empty($kiotid)) {
            $kiotid = " Kiotviet:" . $kiotid;
        }
        $price = " Giá:" . $item['paid_price'];

        $info["name"] .= '<p class="'.$item['status'].'">'.$item['name'].' '.$variation.$price.$kiotid.$editHtml.'</p>';
        
        if(empty($info["tracking_code"])) {
            $info["tracking_code"] = $item['tracking_code'];
        }
        if(empty($info["shipping_provider_type"])) {
            $info["shipping_provider_type"] = $item['shipping_provider_type'];
        }
        if(empty($info["buyer_id"])) {
            $info["buyer_id"] = $item['buyer_id'];
        }
        if(empty($info["package_id"])) {
            $info["package_id"] = $item['package_id'];
        }

        // show image of all items, include canceled items
        $info["img"] .= '<a target="_blank" href="'.$item['product_main_image'].'"><img border="0" src="'.$item['product_main_image'].'" height="50"></a><br>';
    }
    return $info;
}

// API GetMultipleOrderItems
function getOrdersItems($accessToken, $orderIds){
    $strIds = "[" . implode(",", $orderIds) . "]";
    $c = new LazopClient($GLOBALS['apiUrl'],$GLOBALS['appKey'],$GLOBALS['appSecret']);
    $request = new LazopRequest('/orders/items/get','GET');
    $request->addApiParam('order_ids', $strIds);
    $response = json_decode($c->execute($request, $accessToken), true);
    //var_dump($response);

    $data = $response['data'];
    
    // re-format array, 'order_id' => 'order_items.value'
    $ordersItems = array_column($data, "order_items", "order_id");

    // re-map array, 'order_id' => 'getOrderItemsInfo(order_items.value)'
    $ordersItems =array_map('getOrderItemsInfo', $ordersItems);

    return $ordersItems;
}

function printOrders($token, $orders, $offset = 0, $status = "") {
    echo '<table border="1">';
    echo '<thead><tr>';
    for($i=0; $i<16; $i++) {
        echo '<th>&#x25BC </th>';
    }
    echo '</tr></thead>';
    echo '<tbody>';

    foreach($orders as $index=>$order) {
        $orderId = $order['order_id'];
        $orderNumber = $order['order_number'];
        $shipping = $order['address_shipping'];
        //$address = $shipping['address1'].'<br>,'.$shipping['address2'].','.$shipping['address3'].','.$shipping['address4'].','.$shipping['address5'];
        $address = $shipping['address3'];
        
        $cusName = $shipping['first_name'].' '.$shipping['last_name'];
        $cusPhone = preg_replace('/(^84)(\d+)/i', '$2', $shipping['phone']);
        $itemCount = $order['items_count'];
        $orderStatus = $order['statuses'][0];
        $paymentMethod = $order['payment_method'];

        $price = $order['price'];
        
        echo '<tr class="'.$orderStatus.'">';
        echo '<td class="order_status have_background">'.substr($orderStatus,0,2).'</td>';
        echo '<td class="order_index">'.($offset+$index+1).'</td>';

        // if($status == 'delivered') {
        //     echo '<td class="order">'.$orderNumber.'</td>';
        // } else {
        //     //https://sellercenter.lazada.vn/order/detail/314699198254809/15605
        //     echo '<td class="order"><a target="_blank" href="https://sellercenter.lazada.vn/apps/order/detail?tradeOrderId='.$orderId.'">'.$orderNumber.'</a></td>';
        // }

        echo '<td class="order"><a target="_blank" href="https://sellercenter.lazada.vn/apps/order/detail?tradeOrderId='.$orderId.'">'.$orderNumber.'</a></td>';

        if(isset($order['orderItems'])) {
            $item = $order['orderItems'];

            // kiem tra hoa toc
            preg_match('/p2p/i', $item['shipping_provider_type'], $m);
            $shipType = count($m) ? '<span style="color:red">Hỏa tốc </span>' : '';

            echo '<td class="order_tracking_code">'.$shipType.$item['tracking_code'].'</td>';
            echo '<td class"order_tracking_link"></td>'; // tracking code link cell
            
            echo "<td class='order_address'>{$address}</td>"; // $address cell
            
            $chatLink = 'https://sellercenter.lazada.vn/apps/im/window?isWindowOpen=true&buyerId=' . $item['buyer_id'];
            $chatHtml = '<a tabIndex="-1" target="_blank" href="'.$chatLink.'" class="message-icon fa fa-comment" style="color:deepskyblue"></a>';

            echo '<td class="order_cus_name">'.$cusName.$chatHtml.'</td>';
            echo '<td class="order_cus_phone"><b>'.$cusPhone.'</b></td>';
            echo '<td class="order_item_names">'.$item['name'].'</td>';

            $repackHtml = "";
            if(!empty($item['package_id'])) {
                $repackLink = "https://$_SERVER[HTTP_HOST]/lazop/order-api.php?action=repack&package_id={$item['package_id']}";
                $repackHtml = '<a tabIndex="-1" target="_blank" href="'.$repackLink.'" class="fa fa-reply" style="color:gray"></a>';
            }
            echo '<td class="order_item_images">'.$item["img"].'</td>';
            echo '<td class="order_paymentMethod">'.$paymentMethod.$repackHtml.'</td>';
        }
        
        $date1 = new DateTime($order["created_at"]);
        $date2 = new DateTime();
        //$date2->modify('+ 7 hour');
        $days  = $date2->diff($date1)->format('%a');
        $hours = $date2->diff($date1)->format('%h');
        $txtNgay = ($days?($days.' ngày '):'');
        $txtGio = ($hours?($hours.' giờ'):'');
        
        echo '<td class="order_age">'.$txtNgay.$txtGio.'</td>';
        
        $cdate = preg_replace('/(\+0700)/i', '', $order["created_at"]);
        echo '<td class="order_c_date"><b>'.$cdate.'</b></td>';
        
        $udate = preg_replace('/(\+0700)/i', '', $order["updated_at"]);
        echo '<td class="order_u_date">'.$udate.'</td>'; 
        //echo '<td></td>';
        
        echo '<td class="order_price">'.$price.'</td>';
        echo '<td class="order_item_count">'.$itemCount.'</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}

function setRepack($accessToken, $packageId){
    $c = getClient();
    $request = new LazopRequest('/order/repack');
    $request->addApiParam('package_id',$packageId);
    var_dump($c->execute($request, $accessToken));
}


//####################################################################
// Get products region
//####################################################################

//status: all, live, inactive, deleted, image-missing, pending, rejected, sold-out
function getProducts($accessToken, $q = '', $options){
    $allProducts = array();
    
    // add limit and offset to options
    $options['limit'] = 10;
    $options['offset'] = 0;

    do {
        $products = getProductsPaging($accessToken, $q, $options);
        $count = count($products);
        //$skusCount = getSkusCount($products);

        $options['offset'] += $count;
        $allProducts = array_merge($allProducts, (array)$products);
        usleep(200000);
    } while($count == $options['limit']);

    return $allProducts;
}

function getSkusCount($products) {
    $skusCount = 0;
    foreach($products as $index=>$product) {
        $skusCount += count($product['skus']);
    }
    return $skusCount;
}

function getProductsPaging($accessToken, $q, $options, &$total_products=null){
    $c = getClient();
    $request = new LazopRequest('/products/get','GET');
    
    if(strlen($q)) {
        $request->addApiParam('search',$q);
    }

    if(isset($options['skulist']) && count($options['skulist'])) {
        if(count($options['skulist']) > 100) {
            myecho("<h1>ERROR: max number of SKU per request = 100</h1>");
            exit();
        }
        $request->addApiParam('sku_seller_list',json_encode($options['skulist']));
    }

    $status = $options['status'] ? $options['status'] : 'all';
    $request->addApiParam('filter', $status);

    if(isset($options['offset'])) {
        $request->addApiParam('offset', (string)$options['offset']);
    }
    if(isset($options['limit'])) {
        $request->addApiParam('limit', (string)$options['limit']);
    }
    if(isset($options['after'])) {
        $request->addApiParam('create_after', (string)$options['after']);
    }
    //$request->addApiParam('create_after', '2010-01-01T00:00:00+0700';
    //$request->addApiParam('update_after','2010-01-01T00:00:00+0700');
    $request->addApiParam('options','1');

    $response = json_decode($c->execute($request, $accessToken), true);

    if($response["code"] == "0") {
        $total_products = $response["data"]['total_products'];
        return $response["data"]['products'];
    } else {
        myvar_dump($response);
        return null;
    }
}


// GET PRODUCT WITH API GetProducts
// $sku : filter by sku , will be ignored if have $item_id
// $item_id : (prioriry) search by $item_id , show all childrent
// $name : search by name, will be ignored if have $item_id

function getProduct($accessToken, $sku, $item_id=null, $name=null){
    $c = getClient();

    $request = null;
    if(empty($name)) {
        $request = new LazopRequest('/product/item/get','GET');
        if(!empty($item_id)) {
            $request->addApiParam('item_id', (string)$item_id);
        }
        if(!empty($sku)) {
            $request->addApiParam('seller_sku', (string)$sku);
        }
    } else {
        $request = new LazopRequest('/products/get','GET');
        $request->addApiParam('filter', 'all');

        if(strlen($name)) {
            $request->addApiParam('search',$name);
        }

        // filter by SKU
        $skulist = [];
        $skulist[] = $sku;
        $request->addApiParam('sku_seller_list',json_encode($skulist));
    }   
    
    $response = json_decode($c->execute($request, $accessToken), true);

    $product = null;
    if($response["code"] == "0") {
        if(empty($name)) {
            $product = $response["data"];
        } else {
            $product = $response["data"]['products'][0];
        }
        debug_log("get product request id: " . $response["request_id"]);
        
        // 30/10/2020
        // current API have bug, sku_seller_list NOT working
        // use this line to fix filter
        if($sku) {
            $product = getProductWithSingleSku($product, $sku);
        }
    } else {
        //myvar_dump($response);
    }

    return $product;
}


// use dictionary to rearrange, key is $product['item_id']
// all skus with same $product['item_id'], must be grouped together
function reArrangeProducts($products) {
    $newProducts = array(); 
    foreach($products as $product) {
        if(!isset($newProducts[$product['item_id']])) {
            $newProducts[$product['item_id']] = $product;
        } else {
            $newProducts[$product['item_id']]['skus'] = array_merge($newProducts[$product['item_id']]['skus'], 
                $product['skus']);
        }
    }
    return $newProducts;
}

function printProducts($products, $nochild=false, $selectedSku=null) {
    // NOTE: 
    // nguyên nhân không sort được : do số lượng TH và số lượng cột không khớp với nhau, 
    // cột ẩn thì phải có TH ẩn đi kèm

    echo '<table id="tableProducts" class="main tablesorter" border="1" style="width:100%">';
    echo '<thead><tr>';
    /* cột 1 */echo '<th class="sku on">&#x25BC SKU (count='.count($products[0]['skus']).')</th>'; //count skus of fist product.

    /* cột 2 */echo '<th>&#x25BC QTY</th>';
    /* cột 3 */echo '<th></th>';  //quantity form

    /* cột 4 */echo '<th class="editmode on">&#x25BC NAME<b>(<span id="count" style="color:red">0</span>)</b></th>';
    /* cột 5 */echo '<th class="editmode name form">&#x25BC NAME Form<b>'; // name form

    /* cột 6 */echo '<th class="model">&#x25BC Model</th>'; 
    /* cột 7 */echo '<th class="color">&#x25BC Color</th>'; 
    /* cột 8 */echo '<th class="attr"></th>'; 
    
    /* cột 9 */echo '<th class="price">&#x25BCPRICE</th>'; // price form, display:none

    echo '<th></th>'; // edit button
    echo '<th></th>'; // active button
    echo '<th class="ex status">&#x25BCstatus</th>';
    echo '<th class="ex item_id">item_id</th>';
    echo '<th class="ex shop_sku">shop_sku</th>';
    echo '<th class="ex primary_category">primary_category</th>';
    echo '<th class="ex link">Url</th>';
    echo '</tr></thead>';

    echo '<tbody>';
    foreach($products as $product) {
        $GLOBALS['count'] += 1;
        
        //var_dump($product);
        $attrs = $product['attributes'];
        $name = $attrs['name'];
        $item_id = $product['item_id'];
        $primary_category = $product['primary_category'];
        $productImages = $product['images'];

        foreach($product['skus'] as $index=>$sku) {
            $price1 = $sku['price'];
            $price2 = $sku['special_price'];
            $qty = $sku['quantity'];
            $reservedStock = $sku['multiWarehouseInventories'][0]['occupyQuantity'];
            $url = $sku['Url'];
            $sellersku = $sku['SellerSku'];
            $shopsku = $sku['ShopSku'];
            $nameLink = '<a target="_blank" tabindex="-1" href="'.$url.'">'.$name.'</a>';
            $imgs = $sku['Images'];
            $color = $sku['saleProp']['color_family'];
            $model = $sku['saleProp']['compatibility_by_model'] ? $sku['saleProp']['compatibility_by_model'] : "";
            
            unset($sku['saleProp']['color_family']);
            unset($sku['saleProp']['compatibility_by_model']);
            $otherAttributes = implode(",", $sku['saleProp']);

            $color_thumbnail = $sku['color_thumbnail'];

            $selection = implode(',', array_filter(array($variation, $type, $color)));

            $variation = $sku['_compatible_variation_'];
            switch ($primary_category) {
                case 4523: // op lung dien thoai
                case 10100418: // CL đồng hồ
                case 4528: // mieng dan DT
                    $variation = $color . " " . $model;
                    break;
                default:
                    // do nothing
                    break;
            }

            $isGrouped = (count($product['skus']) > 1);
            $cssclass = $isGrouped ? 'grouped' : '';
            $cssclass .= ($index == 0) ? ' parent' : ' child';
            $cssclass .= ($selectedSku == $sellersku) ? ' selected' : '';

            $reservedTxt = $reservedStock ? '(<span class="reservedStock">'.$reservedStock.'</span>)' : '';
            $qtyForm = '<div>
            <input name="sku" type="hidden" value="'.$sellersku.'"/>
            <input name="qty" type="text" size="4" value="'.$qty.'"/>
            <button tabindex="-1" style="padding:0px" class="btn btn-primary" type="button" name="qtyaction" value="+500">+500</button>
            <button tabindex="-1" style="padding:0px" class="btn btn-primary" type="button" name="qtyaction" value="=0">=0</button></div>';
            
            $priceForm = '<form action="update.php" method="POST" name="priceForm" target="responseIframe">
                <input name="sku" type="hidden" value="'.$sellersku.'"/>
                <input name="price" type="hidden" size="6" value="'.$price1.'"/>
                <input name="sale_price" type="text" size="6" value="'.$price2.'"/>
                <input type="submit" tabindex="-1" value="↵" hidden/>
                </form>';
            
            $nameForm = '<form action="update.php" method="POST" name="nameForm" target="responseIframe"><input name="sku" type="hidden" value="'.$sellersku.'"/><input name="name" type="text" size="50" value="'.$name.'"/><input type="submit" tabindex="-1" value="↵" hidden/></form>';
            
            echo '<tr class="'. $cssclass .'">';
            //echo '<td class="sku on padding">'. ($isGrouped?"<i class='grouped-icon fa fa-code-fork' style='color:red'></i>":"") .$sellersku.'</td>';

            //$groupLink = "https://$_SERVER[HTTP_HOST]/lazop/products.php?item_id=$item_id&qname=$name";
            $groupLink = "https://$_SERVER[HTTP_HOST]/lazop/products.php?item_id=$item_id";
            $groupHtml = '<a tabIndex="-1" target="_blank" href="'.$groupLink.'" class="grouped-icon fas fa fa-th-list" style="color:red"></a>';
            $editLink = "https://$_SERVER[HTTP_HOST]/lazop/update_gui.php?item_id=$item_id&sku=$sellersku";
            $delLink = "https://$_SERVER[HTTP_HOST]/lazop/del.php?skus=$sellersku";


            $lazEditLink = "https://sellercenter.lazada.vn/apps/product/publish?productId=$item_id";
            $lazEditHtml = '<a target="_blank" href="'.$lazEditLink.'" class="fa fa-edit" style="color:blue" tabindex="-1"></a>';
            
            /* cột 1 */echo '<td class="sku on padding info">'. ($isGrouped?$groupHtml:"") .$sellersku.'</td>';

            /* cột 2 */echo '<td class="info">'.$qty.'</td>';
            /* cột 3 */echo '<td>'.$reservedTxt.$qtyForm.'</td>';
            /* cột 4 */echo '<td class="editmode name on padding info">'.$nameLink.$lazEditHtml.'</td>';
            /* cột 5 */echo '<td class="editmode name form">'.$nameForm.'</td>';
            
            /* cột 6 */echo '<td class="info">'.$model.'</td>';
            /* cột 7 */echo '<td class="info">'.$color.'</td>';
            /* cột 8 */echo '<td class="info">'.$otherAttributes.'</td>';
            
            // visible
            /* cột 9, price*/
            echo '<td>';
            echo '<s>'.$price1.'</s>&nbsp;';
            echo '<span class="price" >'.$price2.'</span>';
            echo '<span class="price form" style="display:none">'.$priceForm.'</span>';
            echo '</td>';

            /* cột 10 */
            // edit button + del button
            echo '<td>
                <a target="_blank" href="'.$editLink.'" class="fa fa-edit" style="color:red" tabindex="-1"></a>
                <a target="_blank" href="'.$delLink.'" class="fa fa-trash" style="color:green;display:none" tabindex="-1"></a>
                </td>';

            // Active toggle button
            /* cột 13 *///bootstrap code (need bootstrap css and bootstraptoggle css + js)
            $status = ($sku['Status'] == "active") ? "checked" : "";
            echo '<td><input id="'. $sellersku .'" type="checkbox" data-toggle="toggle" '. $status .'></td>';

            // print image thumbnail
            $thumbNailElements = array();
            $urlElements = array();

            $imgs = array_merge($productImages, array("https://a.b"), $imgs);
            if(is_array($imgs)) {

                // product_image column
                $thumb = '<a tabindex="-1" target="_blank" href="'.$color_thumbnail.'"><img alt="thumb" src="'.$color_thumbnail.'" height="50"></a>';
                $thumb = empty($color_thumbnail) ? "" : $thumb;
                array_push($thumbNailElements, '<td class="image thumb on">'.$thumb.'</td>');
                array_push($urlElements, '<td class="ex link info">'.$color_thumbnail.'</td>');

                for($i=0; $i<8; $i++){
                    $thumbLink = trim($imgs[$i]);
                    $fullLink = trim(preg_replace('/(-catalog)/i', '', $thumbLink));
                    
                    $thumb = '<a tabindex="-1" target="_blank" href="'.$fullLink.'"><img alt="thumb" src="'.$thumbLink.'" height="50"></a>';
                    $thumb = empty($thumbLink) ? "" : $thumb;

                    array_push($thumbNailElements, '<td class="image thumb on">'.$thumb.'</td>');
                    array_push($urlElements, '<td class="ex link info">'.$fullLink.'</td>');
                }
            }

            // print extra column
            echo '<td class="ex status info">'.$sku['Status'].'</td>';
            echo '<td class="ex item_id">'.$item_id.'</td>';
            echo '<td class="ex shopsku">'.$shopsku.'</td>';
            echo '<td class="ex primary_category">'.$primary_category.'</td>';
            echo '<td class="ex url info">'.$url.'</td>';
            foreach ($urlElements as $e) {
                echo $e;
            }

            // print thumbnails
            foreach ($thumbNailElements as $e) {
                echo $e;
            }


            echo '</tr>';
            if($nochild) {
                break;
            }
        }
    }

    echo '</tbody>';
    echo '</table><br>';
}

// function printProductsWithFilter($products, $filter) { 
//     $filterQty = $filter["filterQty"];
//     $filterNameWords = array_filter(explode(' ', strtolower($filter["filterName"])));


//     // foreach($products as $index=>$product) {
//     //     echo "<br>", count($product['skus']);
//     // }

//     if(count($filterNameWords) || !is_blank($filterQty)) {
//         foreach($products as $index=>$product) {
//             if(count($filterNameWords)) {
//                 $attrs = $product['attributes'];
//                 $name = strtolower($attrs['name']);
//                 $nameWords = array_filter(explode(' ', strtolower($name)));

//                 $matches = array_intersect($nameWords, $filterNameWords);
//                 if(count($matches) == 0)
//                 {
//                     unset($products[$index]); // remove unmatched product
//                     continue;
//                 } 
//             }

//             if(is_numeric($filterQty)) {
//                 foreach($product['skus'] as $index=>$sku) {
//                     $qty = $sku['quantity'];
//                     if($qty > $filterQty) {
//                         echo "shen", $qty;
//                         unset($product['skus'][$index]); // remove unmatched product
//                         continue;
//                     }
//                 }
//             }
//         }
//     }

//     // foreach($products as $index=>$product) {
//     //     echo "<br>", count($product['skus']);
//     // }

//     printProducts($products);
// }

function printProduct($product) {
    echo "<br>";
    echo $product['Skus'][0]['SellerSku'], htmlLinkImages($product['Skus'][0]['Images']);
    echo "<br>";
}



//####################################################################
// Create products region
//####################################################################

function createProduct($accessToken, $product) {
    // create XML payload
    $request = array("Product" => $product);
    $xml = new ArrayToXML();
    $payload = $xml->buildXML($request, 'Request');
    
    $c = getClient();
    $request = new LazopRequest('/product/create');
    $request->addApiParam('payload', $payload);

    $res = json_decode($c->execute($request, $accessToken), true);
    
    debug_log($product);

    if($res["code"] == "0") {
        myecho("success : " . getProductSkusText($product));
        return 1;
    } else {
        myecho("CREATE FAILED: " . $sku);
        var_dump($res);
        return 0;
    }
}

function createProducts($accessToken, $sku, $skuprefix, $data, $combos, $comboimages, $prices, $preview = 1) {
    $product = getProduct($accessToken, $sku);
    
    if($product) {
        $product = prepareProductForCreating($product);

        $backupimages = $product['Skus'][0]['Images'];
        // reset all image
 
        $time = substr(time(), -4);
        $created = array();
        $associatedskus = array();
        $cache = array();
        foreach($data["names"] as $index => $name) {
            //$time++;
            foreach($combos as $combo) {
                if($combo == 0) { // buy 1 get 1
                    $product['Attributes']["name"] = "$name" . " (Mua 1 tặng 1)";
                } else if ($combo == 1) { 
                    $product['Attributes']["name"] = $name;
                } else { // combo 2, 3, 4, 5
                    $product['Attributes']["name"] = "Combo " . $combo . " " . $name;
                }
                
                // SKU = prefix + branch + time
                //$branch = isset($data["branches"][$index]) ? (vn_urlencode($data["branches"][$index])) : "";
                if(substr($skuprefix, -2) != "__") {
                    $skuprefix .= "__";
                }
                $newSku = $skuprefix;

                // set model
                if(isset($data["models"][$index])) {
                    $model = trim($data["models"][$index]);
                    $product['Skus'][0]['compatibility_by_model'] = $model;
                    $newSku = $newSku . vn_urlencode($model) . "__";
                }
                
                // set color
                if(isset($data["colors"][$index])) {
                    $product['Skus'][0]['color_family'] = $data["colors"][$index];
                    $newSku = $newSku . vn_urlencode($data["colors"][$index]);
                }
                
                if($combo == 0) { // buy 1 get 1
                    $newSku = $newSku . "." . "1TANG1";
                } else if ($combo == 1) { 
                    // do nothing
                } else { // combo 2,3,4,5 
                    $newSku = $newSku . "." . "X" . $combo;
                }

                if(substr($newSku, -2) == "__") {
                    $newSku = substr($newSku, 0, -2);
                }
                $newSku .= "." . $time;
                $newSku = make_short_sku($newSku);

                $product = setProductSku($product, $newSku);
                
                // set price
                $price = $prices[$combo];
                $product = setProductPrice($product, $price);
                
                // set quantity
                if(isset($data["qtys"][$index])) {
                    $product = setProductQuantity($product, $data["qtys"][$index]);
                }
                
                // set associated sku
                if(isset($data["groups"][$index])) {
                    $group = $data["groups"][$index];
                    if(!isset($associatedskus[$combo][$group])) {
                        $associatedskus[$combo][$group] = $newSku;
                    }
                    $product['AssociatedSku'] = $associatedskus[$combo][$group];
                }
                
                // set images
                $cbimage = val($comboimages[$combo]);
                $resetimages = $data["resetimages"];
                if(!empty($cbimage)) {
                    // do nothing, already remove cbimage
                } else {
                    if(isset($data["images"][$index])) {
                        // migrate images
                        $images = migrateImages($accessToken, $data["images"][$index], $cache);
                        $product = setProductSKUImages($product, $images, $resetimages);       
                    } else {
                        $product = setProductSKUImages($product, $backupimages, TRUE);   
                    }
                }   

                if(!$preview) {
                    createProduct($accessToken, $product);
                    usleep(300000);
                }
                
                // store to print
                $created["sku"][] = $product['Skus'][0]['SellerSku'];
                $created["name"][] = $product['Attributes']["name"];
                $created["imgs"][] = $product['Skus'][0]['Images'];
            }
        }
        
        foreach($created["sku"] as $index => $sku) {
            echo "<br>", $created["name"][$index], " # ", $sku, htmlLinkImages($created["imgs"][$index]);
        }
    } else {
        myecho("Wrong SKU", __FUNCTION__);
    }
}

function createProductsFromManySource($accessToken, $data, $preview = 1){
    $makegroup = (int)$data["makegroup"];
    $created = array();
    $cache = array();
    $parentCache = array();
    $sourceCache = array();
    $product;
    foreach($data["names"] as $index => $name) {
        $parentSku = isset($data["parentskus"][$index]) ? trim($data["parentskus"][$index]) : 0;
        $sourceSku = isset($data["sourceskus"][$index]) ? trim($data["sourceskus"][$index]) : 0;
        $skuprefix = isset($data["skuprefixs"][$index]) ? trim($data["skuprefixs"][$index]) : 0;
        $group = isset($data["groups"][$index]) ? trim($data["groups"][$index]) : 0;
        $model = isset($data["models"][$index]) ? trim($data["models"][$index]) : "...";
        $color = isset($data["colors"][$index]) ? trim($data["colors"][$index]) : 0;
        $price = isset($data["prices"][$index]) ? $data["prices"][$index] : 0;
        $qty = isset($data["qtys"][$index]) ? $data["qtys"][$index] : 0;
        $image = isset($data["images"][$index]) ? $data["images"][$index] : 0;
        $kiotid = isset($data["kiotids"][$index]) ? trim($data["kiotids"][$index]) : 0;

        if($sourceSku) {
            if(isset($sourceCache[$sourceSku])) {
                $product = $sourceCache[$sourceSku];
            } else {
                $product = getProduct($accessToken, $sourceSku);
                if($product) {
                    $sourceCache[$sourceSku] = $product;
                } else {
                    myecho("SOURCE SKU NOT FOUND: " . $sourceSku);
                }
            }
        } else {
            myecho("NO SOURCE SKU at line: " . ($index+1));
        }

        if(!empty($parentSku)) {
            if(isset($parentCache[$parentSku])) {
                // do nothing
            } else {
                $p = getProduct($accessToken, $parentSku);
                if($p) {
                    $parentCache[$parentSku] = $p;
                } else {
                    myecho("PARENT SKU NOT FOUND: " . $parentSku);
                    $parentSku = "";
                }
            }
        } else {
            //myecho("NO PARENT SKU at line: " . ($index+1));
        }

        if($product) {
            $product = prepareProductForCreating($product);
            $backupimages = $product['Skus'][0]['Images'];

            $product['Attributes']["name"] = $name;

            if($model) {
                $product = setProductModel($product, $model);
            } else {
                unset($product['Skus'][0]['saleProp']['compatibility_by_model']); 
            }

            if($color) {
                $product = setProductColor($product, $color);
            } else {
                unset($product['Skus'][0]['saleProp']['color_family']);
            }
            
            $newSku = generateSku($skuprefix, $group, $model, $color, $kiotid);
            $product = setProductSku($product, $newSku);

            // set price
            if($price) {
                $product = setProductPrice($product, $price);
            }
            
            // set quantity
            if($qty) {
                $product = setProductQuantity($product, $qty);
            }

            // set associated sku ( =parent SKU )
            if(!empty($parentSku)) {
                $product['AssociatedSku'] = $parentSku;
            } else {
                if($makegroup) {
                    // set associated sku
                    $key = md5($group, 1);
                    if(!isset($associatedskus[$key])) {
                        $associatedskus[$key] = $newSku;
                    }
                    $product['AssociatedSku'] = $associatedskus[$key];
                }
            }
            
            // set images
            $resetimages = $data["resetimages"];
            if(strlen($image) > 20) {
                // migrate images
                $images = migrateImages($accessToken, $image, $cache);
                $product = setProductSKUImages($product, $images, $resetimages);       
            } else {
                $product = setProductSKUImages($product, $backupimages, TRUE);   
            }

            if(!$preview) {
                $product = setProductActive($product, 1);
                createProduct($accessToken, $product);
                usleep(300000);
            }
            
            // store to print
            $created["sku"][] = $product['Skus'][0]['SellerSku'];
            $created["name"][] = $product['Attributes']["name"];
            $created["imgs"][] = $product['Skus'][0]['Images'];
        }
    }
    echo "<h2>REVIEW CREATED PRODUCTS</h2>";
    foreach($created["sku"] as $index => $sku) {
        echo "<br>", $created["name"][$index], " # ", $sku, htmlLinkImages($created["imgs"][$index]);
    }
}


//###################################################################
// Update images/prices/quantity
//###################################################################

function updateQuantityWithAPI($accessToken, $sku, $qty) {
    $qtyPayload = '<Quantity>'.$qty.'</Quantity>';
    $payload = '<?xml version="1.0" encoding="UTF-8"?><Request><Product><Skus><Sku><SellerSku>'.$sku.'</SellerSku>'.$qtyPayload.'</Sku></Skus></Product></Request>';
    
    $c = getClient();
    $request = getRequest('/product/price_quantity/update');
    
    $request->addApiParam('payload', $payload);
    $response = $c->execute($request, $accessToken);
    //var_dump($response);
    $response = json_decode($response, true);
    return $response;
}

function massUpdateQuantityWithAPI($accessToken, $skus, $qty) {
    $payload = '<?xml version="1.0" encoding="UTF-8"?><Request><Product><Skus>';
    foreach($skus as $i => $sku) {
        $payload .= '<Sku><SellerSku>'.$sku.'</SellerSku><Quantity>'.$qty.'</Quantity></Sku>';
    }
    $payload .= '</Skus></Product></Request>';
    //echo htmlentities( $payload);

    $c = getClient();
    $request = getRequest('/product/price_quantity/update');
    
    $request->addApiParam('payload', $payload);
    $response = $c->execute($request, $accessToken);
    //var_dump($response);
    $response = json_decode($response, true);
    return $response;
}

function updatePricesWithAPI($accessToken, $sku, $price, $sale_price, $fromdate = "2020-01-01", $todate = "2028-01-01") {
    $pricePayload = '';
    $salePayload = '';
    
    if(empty($price) || intval($sale_price) > intval($price) || intval($sale_price) < intval($price)/2) {
        $price = intval($sale_price * 1.2);
    }

    if($sale_price) {
        $salePayload = '<SalePrice>'.$sale_price.'</SalePrice><SaleStartDate>'.$fromdate.'</SaleStartDate><SaleEndDate>'.$todate.'</SaleEndDate>';
    }

    $pricePayload = '<Price>'.$price.'</Price>' . $salePayload;
    
    
    $payload = '<?xml version="1.0" encoding="UTF-8"?><Request><Product><Skus><Sku><SellerSku>'.$sku.'</SellerSku>'.$pricePayload.'</Sku></Skus></Product></Request>';
    
    $c = getClient();
    $request = getRequest('/product/price_quantity/update');
    
    $request->addApiParam('payload', $payload);
    $response = $c->execute($request, $accessToken);
    //var_dump($response);
    $response = json_decode($response, true);
    return $response;
}

function updateImages($accessToken, $sku, $images, &$savedimages = null) {
    $product = getProduct($accessToken, $sku);
    if($product) {
        // split images
        $images = preg_split("/\s+/", $image);
        $product = prepareProductForUpdating($product);
        
        $product['Skus'][0]['Images'] = array();
        $migratedimgs = migrateImages($accessToken, $images, $savedimages);
        foreach($migratedimgs as $index => $url) {
            $product['Skus'][0]['Images'][] = $url;
        }
        
        //myvar_dump($product);

        $r = array("Product" => $product);
        $xml = new ArrayToXML();
        $payload = $xml->buildXML($r, 'Request');

        $c = getClient();
        $request = getRequest('/images/set');
        $request->addApiParam('payload',$payload);
        $response = $c->execute($request, $accessToken);
        //var_dump($response);
        $response = json_decode($response, true);
        $response['images'] = $product['Skus'][0]['Images'];
        return $response;
    } else {
        $response["code"] = 1;
        $response["message"] = "Invalid SKU: ".$sku;
    }
}

//####################################################################
// Update product (call product/update)
//####################################################################

function setPrimaryCategory($accessToken, $sku, $category) {
    $product = getProduct($accessToken, $sku);
    
    if($product) {
        $product['PrimaryCategory'] = $category;
        $product['Skus'] = $product['skus'];
        unset($product['skus']);
        unset($product['attributes']);
        unset($product['primary_category']);

        $r = array("Product" => $product);
        $xml = new ArrayToXML();
        $payload = $xml->buildXML($r, 'Request');

        $c = getClient();
        $request = getRequest('/product/update');
        $request->addApiParam('payload',$payload);
        $response = $c->execute($request, $accessToken);
        //var_dump($response);
        $response = json_decode($response, true);
        return $response;
    } else {
        $response["code"] = 1;
        $response["message"] = "Invalid SKU: ".$sku;
    }
}

function fixProducts($accessToken, $skus, $options)
{
    $skus = pre_process_skus($skus);

    $success = array();
    $doneList = array();
    foreach($skus as $index => $sku) {
        if(in_array($sku, $doneList)) {
            continue;
        }

        $product = getProduct($accessToken, $sku);
        $item_id = getProductItemId($product);
        $product = getProduct($accessToken, null, $item_id);

        if($product) {
            $product = prepareProductForUpdating($product);

            // option 1
            if(in_array("1", $options)) {
                echo "<br>FIX variation<br>";
                $product = fixProductRemoveSlashFromModel($product);
            } 

            // option 2
            if(in_array("2", $options)) {
                echo "<br>FIX brand<br>";
                $product = fixProductSetDefaultBrand($product);
            }

            // option 3
            if(in_array("3", $options)) {
                echo "<br>FIX color=... model=...<br>";
                $product = fixProductSetDefaultColorAndModel($product);
            }

            // option 4
            if(in_array("4", $options)) {
                echo "<br>Remove video link<br>";
                $product = fixProductRemoveVideoLink($product);
            }
            
            // option 5
            if(in_array("5", $options)) {
                echo "<br>Fix sale date<br>";
                $product = fixProductSaleDate($product);
            }

            // option 6
            if(in_array("6", $options)) {
                echo "<br>FIX model=random<br>";
                $product = fixProductSetRandomModel($product);
            }

            // option 7
            if(in_array("7", $options)) {
                echo "<br>FIX model=...<br>";
                $product = fixProductModel($product);
            }
            
            
            $r = saveProduct($accessToken, $product);
            
            if($r["code"] == "0") {
                $success[] = $product;
                $doneList = array_merge($doneList, array_column($product['Skus'], "SellerSku"));
            } else {
                myvar_dump($r);
            }
        }
    }
    
    foreach($success as $product) {
        printProduct($product);
    }
}

function massUpdateProducts($accessToken, $skus, $data, $preview = 1) {
    $skus = pre_process_skus($skus);
    
    $cache = array();
    $success = array();
    foreach($skus as $index => $sku) {
        $product = getProduct($accessToken, $sku);
        
        if($product) {
            $product = prepareProductForUpdating($product);
            
            if(isset($data["names"][$index])) {
                $val = $data["names"][$index];
                $product = setProductName($product, $val);
            }
            
            if(isset($data["prices"][$index])) {
                $value = $data["prices"][$index];
                $product = setProductPrice($product, $value);
            }

            if(isset($data["models"][$index])) {
                $value = $data["models"][$index];
                $product = setProductModel($product, $value);
            }
            
            if(isset($data["colors"][$index])) {
                $value = $data["colors"][$index];
                $product = setProductColor($product, $value);
            }

            if(isset($data["qty"][$index])) {
                $value = $data["qty"][$index];
                $product = setProductQuantity($product, $value);
            }

            //myvar_dump($product);
            
            if(isset($data["images"][$index])) {
                $imageindex = isset($data["imageindex"]) ? $data["imageindex"] - 1 : 0;
                $imageindex = ($imageindex > 0 && $imageindex < 9) ? $imageindex : 0;
                
                $images = migrateImages($accessToken, $data["images"][$index], $cache);
                $product = setProductSKUImages($product, $images, FALSE, $imageindex);
            }
            
            if(isset($data["actives"][$index])) {
                $value = $data["actives"][$index];
                $product = setProductActive($product, $value);
            }

            if(!intval($preview)) {
                $r = saveProduct($accessToken, $product);
                usleep(50000);
            } else {
                myecho("PREVIEWING ...");
                $r = array(
                    "code" => "0"
                    );
            }
            
            if($r["code"] == "0") {
                $success[] = $product;
            } else {
                echo $sku . " ";
                myvar_dump($r);
            }
        }
    }
    
    foreach($success as $product) {
        printProduct($product);
    }
}

function saveProduct($accessToken, $product) {
    // create XML payload
    $request = array("Product" => $product);
    $xml = new ArrayToXML();
    $payload = $xml->buildXML($request, 'Request');
    //echo htmlentities($payload);
    
    $c = getClient();
    $request = new LazopRequest('/product/update');
    $request->addApiParam('payload', $payload);
    $res = $c->execute($request, $accessToken);
    //var_dump($res);
    $res = json_decode($res, true);

    debug_log("update request id: " . $res["request_id"]);
    
    return $res;
}

//####################################################################
// Clone region
//####################################################################

function addChildProduct($accessToken, $sku, $inputdata, $preview = 1) {
    $sku = pre_process_sku($sku);
    $cloneby = $inputdata['cloneby'];
    $skuprefix = $inputdata['skuprefix'];
    $newName = $inputdata['newname'];
    
    $product = getProduct($accessToken, $sku);

    if($product) {     
        $product = prepareProductForCreating($product);

        // clone original sku dict
        $clonedSku = $product['Skus'][0];
        // remove skus[0]
        unset($product['Skus'][0]);

        $created = array();
        if($cloneby == 'original') {
            //... do nothing
        } else {
            $product = setProductAssociatedSku($product, $sku);
            $kiotids = $inputdata["kiotids"];
            $saleProps = $inputdata["saleProps"];

            // set NAME
            if(!empty($newName)) {
                $product = setProductName($product, $newName);
            }

            $cache = array();
            $time = substr(time(), -4);
            foreach($inputdata["qtys"] as $index => $value) {
                // init new sku dict
                $skuDict = $clonedSku;
                $kiotid = $kiotids[$index] ? $kiotids[$index] : "";

                // set attributes
                $values = array();
                foreach ($saleProps as $prop => $inputList) {
                    if($inputList[$index]) {
                        $skuDict['saleProp'][$prop] = $inputList[$index];
                    }
                    $values[] = $skuDict['saleProp'][$prop];
                }
                
                // set sku
                $newSku = generateSku1($skuprefix, "", $values, $kiotid);
                $skuDict['SellerSku'] = $newSku;

                // set qty
                if(isset($inputdata["qtys"][$index])) {
                    $qty = $inputdata["qtys"][$index];
                    $skuDict['quantity'] = $qty;
                }

                // set price
                if(isset($inputdata["prices"][$index])) {
                    $price = $inputdata["prices"][$index];
                    $skuDict['price'] = round($price * 1.3 / 100) * 100;
                    $skuDict['special_price'] = $price;
                    $skuDict['special_from_date'] = "2020-01-01";
                    $skuDict['special_to_date'] = "2030-12-12";
                }

                // set images
                if(isset($inputdata["images"][$index])) {
                    // migrate images
                    $images = migrateImages($accessToken, $inputdata["images"][$index], $cache);
                    foreach($images as $index => $url) {
                        if (is_url($url)) {
                            $skuDict['Images'][$index] = $url;
                        } else {
                            if(!empty($url)) {
                                myecho("INVALID URL : " + $images[$index], __FUNCTION__);
                            }
                        }
                    }      
                }
                // force active
                $skuDict['Status'] = "active";

                $product['Skus'][] = $skuDict;

                // store to print
                $created["sku"][] = $newSku;
                $created["associatedsku"][] = $sku;
                $created["name"][] = $product['Attributes']["name"];
                $created["img"][] = $skuDict['Images'];
            }

            if(!intval($preview)) {
                createProduct($accessToken, $product);
            } else {
                myecho("PREVIEWING ...");
            }
        }
        
        // print new SKUs
        echo '<hr><h3>New SKUs</h3>';
        foreach($created["sku"] as $index => $sku) {
            echo "<br>",$sku, htmlLinkImages($created["img"][$index]);
        }
    } else {
        echo "<br><br>Wrong sku<br><br>";
    }
}

function massAddChildProduct($accessToken, $data, $preview = 1) {
    
}

function massMoveChild($accessToken, $data, $preview) {
    $created = array();
    $parentCache = array();
    $product;
    foreach($data["skus"] as $index => $sku) {
        $product = getProduct($accessToken, $sku);

        if($product) {
            $product = prepareProductForCreating($product);

            $parentSku = isset($data["newParentSkus"][$index]) ? trim($data["newParentSkus"][$index]) : 0;
            if($parentSku) {
                if(isset($parentCache[$parentSku])) {
                    $parent = $parentCache[$parentSku];
                } else {
                    $parent = getProduct($accessToken, $parentSku);
                }

                if($parent) {
                    // check category of child and parent
                    // to do ...

                    $product = setProductAssociatedSku($product, $parentSku);

                    // add postfix Mx to newSku
                    preg_match('/(.+\.M)(\d+)$/i', $sku, $matches);
                    if(count($matches)) {
                        $newSku = $matches[1].(trim($matches[2])+1);
                    } else {
                        $newSku = trim($sku).'.M1';
                    }
                    $product = setProductSku($product, $newSku);

                    if(!$preview) {
                        if(createProduct($accessToken, $product)) {
                            // disable parent after moving
                            if(isProductActive($parent)) {
                                $parent = setProductActive($parent, 0);
                                $r = saveProduct($accessToken, $parent);
                            }

                            // store to print
                            $created["oldsku"][] = $sku;
                            $created["sku"][] = $product['Skus'][0]['SellerSku'];
                            $created["name"][] = $product['Attributes']["name"];
                            $created["imgs"][] = $product['Skus'][0]['Images'];
                        }
                        
                        usleep(300000);
                    } else {
                        // store to print
                        $created["sku"][] = $product['Skus'][0]['SellerSku'];
                        $created["name"][] = $product['Attributes']["name"];
                        $created["imgs"][] = $product['Skus'][0]['Images'];
                    }

                    // cache
                    $parentCache[$parentSku] = $parent;
                } else {
                    myecho("PARENT SKU NOT FOUND: " . $parentSku);
                }
            } else {
                myecho("PARENT SKU NOT SET");
            }
        } else {
            myecho("SKU NOT FOUND: " . $sku);
        }
    }
    echo "<h2>REVIEW CREATED PRODUCTS</h2>";
    foreach($created["sku"] as $index => $sku) {
        echo "<br>", $created["name"][$index], " # ", $sku, htmlLinkImages($created["imgs"][$index]);
    }

    // XOA HET SKU GOC SAU KHI MOVE
    if(count($created["oldsku"])) {
        //delProducts($accessToken, $created["oldsku"]);

        echo "<h2>NEXT, PLEASE DELETE THESE OLD SKUs</h2>";
        foreach($created["oldsku"] as $index => $sku) {
            echo "<br>", $sku;
        }
    }
}

function massCloneProduct($accessToken, $srcSkus, $newSkus, $delsource = 0) {
    $srcSkus = pre_process_skus($srcSkus);
    $newSkus = pre_process_skus($newSkus);

    $createdSkus = array();
    $needDelSkus = array();
    $options = array(
        'status' => 'all',
        'skulist' => $srcSkus,
    );
    $products = getProducts($accessToken, '', $options);

    foreach($products as $index => $product) {
        $product = prepareProductForCreating($product);

        $associatedSku = "";
        foreach($product['Skus'] as $index=>$dict) {
            $sku = $dict["SellerSku"];

            // GENERATE new SKU
            $newSku = '';
            // set new sku
            if(isset($newSkus[$index])) {
                $newSku = $newSkus[$index];
            } else {
                // add postfix Vx to newSku
                preg_match('/(.+\.v)([1-9][0-9]*)/i', $sku, $matches);
                if(count($matches)) {
                    $newSku = $matches[1].(trim($matches[2])+1);
                } else {
                    $newSku = trim($sku).'.v1';
                }
            }
        
            // SAVE associatedSku
            if($index == 0) {
                $associatedSku = $newSku;
            }

            // create product with only 1 SKU
            $product['Skus'] = array(
                    0 => $dict
                    );
            $product['AssociatedSku'] = $associatedSku;
            $product = setProductSku($product, $newSku);

            $res = createProduct($accessToken, $product);
            if($res["code"] == "0") {
                // SAVE CREATED SKU
                $createdSkus[] = $newSku;

                // SAVE old SKU need to be deleted
                $needDelSkus[] = $sku;
            }
            usleep(100000);
        }
    }
    
    // print new SKUs
    echo '<hr><h3>New SKUs</h3>';
    foreach($createdSkus as $item) {
        echo $item, '<br>';
    }
    
    if($delsource) {
        echo '<hr><h3>Delete source SKUs</h3>';
        foreach($needDelSkus as $item) {
            echo $item, '<br>';
        }
        delProducts($GLOBALS["accessToken"], $needDelSkus);
    }
}

function massCloneToShop($accessToken, $srcSkus, $dest_token, $sku_prefix) {
    $srcSkus = pre_process_skus($srcSkus);
    $createdSkus = array();

    $options = array(
        'status' => 'all',
        'skulist' => $srcSkus,
    );
    $products = getProducts($accessToken, '', $options);

    foreach($products as $index => $product) {
        $product = prepareProductForCreating($product);

        $associatedSku = "";
        foreach($product['Skus'] as $index=>$dict) {
            $sku = $dict["SellerSku"];
            if(empty($sku)) {
                myecho("Can not clone empty SKU");
                continue;
            }

            // GENERATE new SKU
            $newSku = !empty($sku_prefix) ? ($sku_prefix . $sku) : $sku;
        
            // SAVE associatedSku
            if($index == 0) {
                $associatedSku = $newSku;
            }

            // create product with only 1 SKU
            $product['Skus'] = array(
                    0 => $dict
                    );
            $product['AssociatedSku'] = $associatedSku;
            $product = setProductSku($product, $newSku);

            $res = createProduct($dest_token, $product);
            if($res["code"] == "0") {
                // SAVE CREATED SKU
                $createdSkus[] = $newSku;
            } else {
                echo " SORUCE SKU: ";
                echo $sku;
            }
            usleep(100000);
        }
    }
    
    // print new SKUs
    echo '<hr><h3>Cloned SKUs</h3>';
    foreach($createdSkus as $item) {
        echo $item, '<br>';
    }
}

// copy image, desc, short desc, ...
// options[] = 1 : copy images
// options[] = 2 : copy price
// options[] = 3 : copy desc + short desc
// options[] = 4 : copy size, weight
function copyInfoToSkus($accessToken, $sourcesku, $skus, $inputdata) {
    $options = $inputdata["options"];
    $imageindexes = $inputdata["imageindexes"];

    $sourcesku = pre_process_sku($sourcesku);
    $skus = pre_process_skus($skus);

    $srcproduct = getProduct($accessToken, $sourcesku);
    if(!$srcproduct) {
        echo "<h2>Wrong source SKU</h2>";
        return;
    }
    
    // chia thành các mảng nhỏ 20item
    $chunks = array_chunk($skus, 20);
    
    foreach($chunks as $chunk) {
        $options1 = array(
            'status' => 'all',
            'skulist' => $chunk,
        );

        $list = getProducts($accessToken, '', $options1);

        //  1 product , 1 sku , for updating
        // $list = productsWithSingleSku($list);

        foreach($list as $product) {
            debug_log($product);
            
            $product = prepareProductForUpdating($product);

            $size = count($product['Skus']);
            for($i=0; $i<$size; $i++) {
                $dict = &$product['Skus'][$i];
                $sku = $dict["SellerSku"];

                if(!in_array($sku, $skus)){
                    // only do copy for skus in destination list
                    continue;
                }

                // if special_price = 0, cause error
                if(empty($dict['special_price'])) {
                    $dict['special_price'] = $dict['price'] - 1000;
                }

                // copy images
                if(in_array("1", $options)) {
                    foreach ($imageindexes as $index) {
                        $dict['Images'][$index] = $srcproduct['skus'][0]['Images'][$index];
                    }
                }

                // copy prices
                if(in_array("2", $options)) {
                    //echo "<hr>2<hr>";
                    $dict['price'] = $srcproduct['skus'][0]['price'];
                    $dict['special_price'] = $srcproduct['skus'][0]['special_price'];
                    $dict['special_to_date'] = $srcproduct['skus'][0]['special_to_date'];
                    $dict['special_from_date'] = $srcproduct['skus'][0]['special_from_date'];
                }

                // copy size, weight, package content
                if(in_array("4", $options)) {
                    //echo "<hr>4<hr>";
                    $dict['package_width'] = $srcproduct['skus'][0]['package_width'];
                    $dict['package_height'] = $srcproduct['skus'][0]['package_height'];
                    $dict['package_length'] = $srcproduct['skus'][0]['package_length'];
                    $dict['package_weight'] = $srcproduct['skus'][0]['package_weight'];
                    $dict['package_content'] = $srcproduct['skus'][0]['package_content'];
                }
            }
            
            // copy desc
            if(in_array("3", $options)) {
                //echo "<hr>3<hr>";
                $product['Attributes']['short_description'] = $srcproduct['attributes']['short_description'];
            }

            // copy desc
            if(in_array("4", $options)) {
                //echo "<hr>3<hr>";
                if(in_array("4.1", $options)) {
                    // replace duplicated
                    if(strpos($product['Attributes']['description'], $srcproduct['attributes']['description']) !== false){
                        $product['Attributes']['description'] = str_replace($srcproduct['attributes']['description'], "", $product['Attributes']['description']);
                    }
                    $product['Attributes']['description'] .= $srcproduct['attributes']['description'];
                } else {
                    $product['Attributes']['description'] = $srcproduct['attributes']['description'];
                }

            }

            //var_dump($product);
            $response = saveProduct($accessToken, $product);

            if($response["code"] == "0") {
                myecho("success group : " . $product['Skus'][0]["SellerSku"]);
            } else {
                myecho("failed group : " . $product['Skus'][0]["SellerSku"] . " : ");
                var_dump($response);
            }
            usleep(500000);
        }

        // sleep 0.5s
        usleep(500000);
    }
}

//####################################################################
// Delete region
//####################################################################

function delProducts($accessToken, $skus, $deloption=false) {
    $deloption = (int)$deloption;
    $skus = pre_process_skus($skus);
    
    // skus + children
    $allskus = array();

    // children skus
    $childrenskus = array();

    if($deloption > 1) {
        foreach($skus as $sku) {
            $product = getProduct($accessToken, $sku);
            $id = getProductItemId($product);
            $product = getProduct($accessToken, null, $id);

            foreach ($product['skus'] as $key => $value) {
                $sellerSku = $value['SellerSku'];
                $allskus[] = $sellerSku;
            }
        }

        switch ($deloption) {
            case 2: // del skus + children
                $skus = $allskus;
                break;
            case 3: // del children
                $childrenskus = array_diff($allskus, $skus);
                $skus = $childrenskus;
                break;
            default:
                # code...
                break;
        }
    }

    // chia thành các mảng nhỏ 20item
    $chunks = array_chunk($skus, 20);

    $c = getClient();
    foreach($chunks as $chunk) {
        $request = getRequest('/product/remove');
        $request->addApiParam('seller_sku_list', json_encode($chunk));
        $response = $c->execute($request, $accessToken);
        echo "<hr>";
        var_dump($response);
        usleep(500000);
    }
}

//####################################################################
// Test region
//####################################################################

function test($accessToken) {
    $c = getClient();
    $request = getRequest('/products/get', 'GET');
    $request->addApiParam('filter','live');
$request->addApiParam('offset','0');
$request->addApiParam('create_after','2010-07-02T00:00:00+0800');
$request->addApiParam('limit','100');
$request->addApiParam('options','1');

    $response = $c->execute($request, $accessToken);
    var_dump($response);
    $response = json_decode($response, true);
    return $response;
}

?>