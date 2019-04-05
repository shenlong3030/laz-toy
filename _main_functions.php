<?php
require_once('src/ArrayToXML.php');
require_once('src/helper.php');
require_once('./_migrate_image_functions.php');
require_once('./_product_internal_functions.php');

function isLazadaImage($url) {
    return preg_match("/slatic.net/i", $url);
}

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
// Get orders region
//####################################################################

function getOrderLinkPostfix(){
    $postfix = '/15605';
    if($GLOBALS['shopid'] == '01') {
        $postfix = '/100021640';
    }
    return $postfix;
}

// valid status : pending, canceled, ready_to_ship, delivered, returned, shipped and failed
function getAllOrders($accessToken, $status = 'pending', $sortBy = 'created_at'){
    $limit = 1000;

    $list = array();
    for($i=0; $i<$limit; $i+=100) {
        $nextlist = getOrders($accessToken, $status, $i, 100, $sortBy);
        $list = array_merge($list, $nextlist);

        if(count($nextlist) < 100) {
            break;
        }
    }
    return $list;
}

function getOrders($accessToken, $status = 'pending', $offset = 0, $limit = 100, $sortBy = 'created_at'){
    $c = getClient();
    $request = getRequest('/orders/get','GET');

    $request->addApiParam('created_after','2018-01-01T09:00:00+08:00');
    if($status != 'all') {
    	$request->addApiParam('status',$status);
    }
    $request->addApiParam('sort_direction','DESC');
    $request->addApiParam('offset',$offset);
    $request->addApiParam('limit', $limit);
    $request->addApiParam('sort_by', $sortBy);
    $response = json_decode($c->execute($request, $accessToken), true);
    //myvar_dump($response);

    $list = array();
    if($response["code"] == "0") {
        $list = $response['data']['orders'];
    } else {
        myvar_dump($response);
    }

    return $list;
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
        // extract color or model from 'Variation'
        $variation = '';
        if(preg_match("(AIS Lava|…)", $item['variation'], $matches)) {
           // do nothing 
           // if model = AIS Lava 
           // or color = …
        } else {
            preg_match('/(.+):(.+)/', $item['variation'], $matches);
            if(count($matches) >= 3) {
                $variation_type = $matches[1];
                $variation_value = $matches[2];
                
                if(preg_match("#(màu)#i", $variation_type, $out)) {
                    $variation = "•• Lựa chọn: ".$variation_value;
                } else {
                    // name must have mix format like this ... / ... / ... /
                    // if(preg_match("(\/)", $item['name'], $out)) {
                    //     $variation = "•• Lựa chọn: ".$variation_value;
                    // } 
                    $variation = "•• Lựa chọn: ".$variation_value;
                }
            }
        }

        $info["ItemName"] .= '<p class="'.$item['status'].'">'.$item['name'].' '.$variation.'</p>';
        $info["TrackingCode"] = $item['tracking_code'] ? $item['tracking_code'] : $info["TrackingCode"];
        
        // show image of all items, include canceled items
        $info["img"] .= '<a target="_blank" href="'.$item['product_main_image'].'"><img border="0" src="'.$item['product_main_image'].'" height="50"></a><br>';
    }
    return $info;
}

function printOrders($token, $orders, $offset = 0, $needFullOrderInfo = 0, $status = "") {
    foreach($orders as $index=>$order) {
        $orderId = $order['order_id'];
        $orderNumber = $order['order_number'];
        $shipping = $order['address_shipping'];
        $address = $shipping['address1'].'<br>,'.$shipping['address2'].','.$shipping['address3'].','.$shipping['address4'].','.$shipping['address5'];
        $cusName = $shipping['first_name'].' '.$shipping['last_name'];
        $cusPhone = preg_replace('/(^84)(\d+)/i', '$2', $shipping['phone']);
        $itemCount = $order['items_count'];
        $orderStatus = $order['statuses'][0];
        $paymentMethod = $order['payment_method'];
        $item = $needFullOrderInfo ? getOrderItems($token, $orderId) : array();
        $price = $order['price'];
        
        echo '<tr class="'.$orderStatus.'">';
        echo '<td class="index">'.($offset+$index+1).'</td>';
        
        
        if($status == 'delivered') {
            echo '<td class="order">'.$orderNumber.'</td>';
        } else {
            echo '<td class="order"><a target="_blank" href="https://sellercenter.lazada.vn/order/detail/'.$orderNumber . getOrderLinkPostfix().'">'.$orderNumber.'</a></td>';
        }
        
        if($needFullOrderInfo) {
            echo '<td>'.$item['TrackingCode'].'</td>';
            echo '<td></td>'; // tracking code link cell
            
            echo '<td></td>'; // $address cell
            
            echo '<td>'.$cusName.'</td>';
            echo '<td><b>'.$cusPhone.'</b></td>';
            echo '<td>'.$item['ItemName'].'</td>';
            echo '<td class="paymentMethod">'.$paymentMethod.'</td>';
            echo '<td>'.$item["img"].'</td>';
        }
        
        $date1 = new DateTime($order["created_at"]);
        $date2 = new DateTime();
        //$date2->modify('+ 7 hour');
        $days  = $date2->diff($date1)->format('%a');
        $hours = $date2->diff($date1)->format('%h');
        $txtNgay = ($days?($days.' ngày '):'');
        $txtGio = ($hours?($hours.' giờ'):'');
        
        echo '<td class="age">'.$txtNgay.$txtGio.'</td>';
        
        $cdate = preg_replace('/(\+0700)/i', '', $order["created_at"]);
        echo '<td><b>'.$cdate.'</b></td>';
        
        //echo '<td>'.$order["updated_at"].'</td>'; 
        echo '<td></td>';
        
        echo '<td>'.$price.'</td>';
        echo '<td>'.$itemCount.'</td>';
        echo '</tr>';
    }
}

//####################################################################
// Get products region
//####################################################################

//status: all, live, inactive, deleted, image-missing, pending, rejected, sold-out
function getProducts($accessToken, $q = '', $status = 'sold-out', $skulist = null){
    $allProducts = array();
    $limit = 50;
    $offset = 0;
    $totalProducts = 0;
    do {
        $products = getProductsPaging($accessToken, $q, $status, $offset, $limit, $totalProducts, $skulist);
        $skusCount = getSkusCount($products);

        $offset += $skusCount;
        $allProducts = array_merge($allProducts, $products);
    } while(count($products) == $limit);

    return $allProducts;
}

function getSkusCount($products) {
    $skusCount = 0;
    foreach($products as $index=>$product) {
        $skusCount += count($product['skus']);
    }
    return $skusCount;
}

function getProductsPaging($accessToken, $q, $status, $offset, $limit, &$total_products=null, $skulist=null){
    $c = getClient();
    $request = new LazopRequest('/products/get','GET');
    $request->addApiParam('filter', $status);
    if(strlen($q)) {
        $request->addApiParam('search',$q);
    }

    if(count($skulist) > 100) {
        myecho("<h1>ERROR: max number of SKU per request = 100</h1>");
        exit();
    }
    // filter by SKU
    if($skulist && count($skulist)) {
        $request->addApiParam('sku_seller_list',json_encode($skulist));
    }

    $request->addApiParam('offset', (string)$offset);
    $request->addApiParam('limit', (string)$limit);
    //$request->addApiParam('create_after', (string)$create_after);
    //$request->addApiParam('update_after','2010-01-01T00:00:00+0800');
    $request->addApiParam('options','1');

    $response = json_decode($c->execute($request, $accessToken), true);

    $products = array();
    if($response["code"] == "0") {
        $total_products = $response["data"]["total_products"];
        $products = $response["data"]["products"];
    } else {
        myvar_dump($response);
    }
    
    return $products;
}

function getProduct($accessToken, $sku){
    $skulist = array($sku);
    $list = getProducts($accessToken, '', 'all', $skulist);
    
    if(count($list) == 1) {
        return $list[0];
    } else {
        return null;
    }
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

function printProducts($products) {
    foreach($products as $product) {
        $GLOBALS['count'] += 1;
        
        //var_dump($product);
        $attrs = $product['attributes'];
        $name = $attrs['name'];

        foreach($product['skus'] as $index=>$sku) {
            $price1 = $sku['price'];
            $price2 = $sku['special_price'];
            $qty = $sku['quantity'];
            $reservedStock = $sku['ReservedStock'];
            $url = $sku['Url'];
            $sellersku = $sku['SellerSku'];
            $shopsku = $sku['ShopSku'];
            $nameLink = '<a target="_blank" tabindex="-1" href="'.$url.'">'.$name.'</a>';
            $imgs = $sku['Images'];
            //$variation = $sku['_compatible_variation_'];
            $variation = $sku['_compatible_variation_'];
            //$variation = str_replace('AIS Lava', '', $variation);
            //$variation = str_replace('Not Specified', '', $variation);

            $isGrouped = (count($product['skus']) > 1);
            $cssclass = $isGrouped ? 'grouped' : '';
            $cssclass .= ($index == 0) ? ' parent' : ' child';
            echo '<tr class="'. $cssclass .'">';
            //visible 
            echo '<td class="sku on padding">'. ($isGrouped?"<i class='grouped-icon fa fa-code-fork' style='color:red'></i>":"") .$sellersku.'</td>';
            // hidden
            echo '<td class="sku padding">'.$shopsku.'</td>';
            $reservedTxt = $reservedStock ? '<span style="color:red">('.$reservedStock.' )</span>' : '';
            
            $qtyForm = '<form action="update.php" method="POST" name="qtyForm" target="responseIframe"><input name="sku" type="hidden" value="'.$sellersku.'"/><input name="qty" type="text" size="4" value="'.$qty.'"/><input type="submit" tabindex="-1" value="↵" /></form>';
            
            $priceForm = '<form action="update.php" method="POST" name="priceForm" target="responseIframe"><input name="sku" type="hidden" value="'.$sellersku.'"/><input name="price" type="text" size="8" value="'.$price1.'"/>--><input name="sale_price" type="text" size="8" value="'.$price2.'"/><input type="submit" tabindex="-1" value="Submit" /></form>';
            
            $nameForm = '<form action="update.php" method="POST" name="nameForm" target="responseIframe"><input name="sku" type="hidden" value="'.$sellersku.'"/><input name="name" type="text" size="50" value="'.$name.'"/><input type="submit" tabindex="-1" value="Submit" /></form>';
            
            echo '<td>'.$qty.'</td>';
            echo '<td>'.$reservedTxt.$qtyForm.'</td>';
            echo '<td class="name on padding">'.$nameLink.'</td>';
            echo '<td class="name">'.$nameForm.'</td>';
            echo '<td>'.$variation.'</td>';
            
            // hidden 
            echo '<td class="price">'.$priceForm.'</td>';
            // visible
            echo '<td class="price on">'.$price1.'</td>';
            echo '<td class="price on">'.$price2.'</td>';

            $link = "http://$_SERVER[HTTP_HOST]/lazop/update_gui.php?sku=$sellersku";
            echo '<td><a target="_blank" href="'.$link.'" class="fa fa-edit" style="color:red" tabindex="-1"></a></td>';

            // bootstrap (need bootstrap css and bootstraptoggle css + js)
            $status = ($sku['Status'] == "active") ? "checked" : "";
            echo '<td><input id="'. $sellersku .'" type="checkbox" data-toggle="toggle" '. $status .'></td>';

            if(is_array($imgs)) {
                for($i=0; $i<8; $i++){
                    $thumbLink = trim($imgs[$i]);
                    $fullLink = trim(preg_replace('/(-catalog)/i', '', $thumbLink));
                    
                    $thumb = '<a tabindex="-1" target="_blank" href="'.$fullLink.'"><img alt="thumb" src="'.$thumbLink.'" height="50"></a>';
                    $thumb = empty($thumbLink) ? "" : $thumb;

                    echo '<td class="image thumb on">'.$thumb.'</td>';
                    echo '<td class="image link">'.$fullLink.'</td>';
                }
            }

            echo '</tr>';
        }
    }
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
    $sku = $product['Skus'][0]['SellerSku'];
    if($res["code"] == "0") {
        myecho("success : " . $sku);
    } else {
        myecho("CREATE FAILED: " . $sku);
        var_dump($res);
    }
    
    return $res;
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
                $product = setPriceForProduct($product, $price);
                
                // set quantity
                if(isset($data["qtys"][$index])) {
                    $product = setQtyForProduct($product, $data["qtys"][$index]);
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
                    $product = setImagesForProduct($product, $cbimage, $cache);
                } else {
                    if(isset($data["images"][$index])) {
                        // migrate images
                        $images = migrateImages($accessToken, $data["images"][$index], $cache);
                        $product = setProductImages($product, $images, $resetimages);       
                    } else {
                        $product = setProductImages($product, $backupimages, TRUE);   
                    }
                }   

                // // set images
                // $cbimage = val($comboimages[$combo]);

                // $product['Skus'][0]['Images'] = array();
                // if(isset($data["images"][$index]) && strlen($data["images"][$index]) > 10) {
                //     $imagesStr = $data["images"][$index];
                //     $product = setImagesForProduct($product, $imagesStr, $savedimages);
                // } else {
                //     $product['Skus'][0]['Images'] = $backupimages;
                // }
                
                // if(!empty($cbimage)) {
                //     $product = setImagesForProduct($product, $cbimage, $savedimages);
                // }
                
                //echo "<br>", $combo, " @ ", $cbimage, " @ ", $product['Skus'][0]['Images'][0];
                //echo "<br>",$product['Attributes']["name"]," : ",$newSku, " : ", $product['Skus'][0]['price'], " : ", $product['Skus'][0]['special_price'], " : ", $product['Skus'][0]['quantity'], " : ", $product['Skus'][0]['Images'][0];
            
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

//###################################################################
// Update images/prices/quantity
//###################################################################

function updateQuantity($accessToken, $sku, $qty) {
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

function updatePrices($accessToken, $sku, $price, $sale_price, $fromdate = "2018-01-01", $todate = "2028-01-01") {
    $pricePayload = '';
    $salePayload = '';
    
    if(empty($price)) {
        $price = intval($sale_price) * 1.2;
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

// $fromindex : just setImages after this index
function setImagesForProduct($product, $image, &$savedimages, $fromindex = 0) {
        $images = $image;
        
        // split images
        if(is_string($image)) {
            $images = preg_split("/\s+/", $image);
        }

        $accessToken = $_COOKIE["access_token"];
        $migratedimgs = migrateImages($accessToken, $images, $savedimages);

        foreach($migratedimgs as $index => $url) {
            if (is_url($url)) {
                $product['Skus'][0]['Images'][$index + $fromindex] = $url;
            } else {
                //myvar_dump($url);
                myecho("CAN NOT MIGRATE: " + $images[$index], __FUNCTION__);
            }
        }
        return $product;
}

function setBrandForProduct($product, $val) {
        if(!empty($val)) {
            $product['Attributes']["brand"] = $val;
        }
        return $product;
}

// just input 1 price
function setPriceForProduct($product, $price) {
        return setPricesForProduct($product, $price);
}

// input price and sale price
function setPricesForProduct($product, $price, $sale_price = 0) {
        // set price
        if(is_numeric($price) && is_numeric($sale_price)) {
            if($sale_price) {
                $product['Skus'][0]['price'] = $price;
                $product['Skus'][0]['special_price'] = $sale_price;
            } else {
                $product['Skus'][0]['price'] = round($price * 1.3 / 100) * 100;
                $product['Skus'][0]['special_price'] = $price;
            }
            
            $product['Skus'][0]['special_from_date'] = "2018-01-01";
            $product['Skus'][0]['special_to_date'] = "2020-12-12";
        }
        return $product;
}

function setQtyForProduct($product, $qty) {
    $product['Skus'][0]['quantity'] = $qty;
    return $product;
}

function setCategoryForProduct($product, $category) {
    $product['PrimaryCategory'] = $category;
    return $product;
}

function setColorForProduct($product, $color) {
    $product['Skus'][0]['color_family'] = $color;
    return $product;
}

function setModelForProduct($product, $model) {
    $product['Skus'][0]['compatibility_by_model'] = $model;
    return $product;
}

function setShortDescriptionForProduct($product, $value) {
    $product['Attributes']['short_description'] = $value;
    return $product;
}

function setDescriptionForProduct($product, $value) {
    $product['Attributes']['description'] = $value;
    return $product;
}

function setPackageWeightForProduct($product, $value) {
    $product['Skus'][0]['package_weight'] = $value;
    return $product;
}

function setPackageSizeForProduct($product, $h, $w, $l) {
    $product['Skus'][0]['package_height'] = $h;
    $product['Skus'][0]['package_width'] = $w;
    $product['Skus'][0]['package_length'] = $l;
    return $product;
}

function setPackageContentForProduct($product, $value) {
    $product['Skus'][0]['package_content'] = $value;
    return $product;
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
                // setPriceForProduct
                $value = $data["prices"][$index];
                $product = setPriceForProduct($product, $value);
            }
            
            if(isset($data["colors"][$index])) {
                // setPriceForProduct
                $value = $data["colors"][$index];
                $product = setColorForProduct($product, $value);
            }

            if(isset($data["qty"][$index])) {
                // setPriceForProduct
                $value = $data["qty"][$index];
                $product = setQtyForProduct($product, $value);
            }

            //myvar_dump($product['Attributes']['color_family']);
            
            if(isset($data["images"][$index])) {
                $imageindex = isset($data["imageindex"]) ? $data["imageindex"] - 1 : 0;
                $imageindex = ($imageindex > 0 && $imageindex < 9) ? $imageindex : 0;
                
                $images = migrateImages($accessToken, $data["images"][$index], $cache);
                $product = setProductImages($product, $images, FALSE, $imageindex);
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
    
    $c = getClient();
    $request = new LazopRequest('/product/update');
    $request->addApiParam('payload', $payload);
    $res = $c->execute($request, $accessToken);
    //var_dump($res);
    $res = json_decode($res, true);
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
        $backupimages = $product['Skus'][0]['Images'];

        $created = array();
        if($cloneby == 'original') {
            //... do nothing
        } else {
            $product = setProductAssociatedSku($product, $sku);
            
            $values;
            if($cloneby == 'color') {
                $values = $inputdata["colors"];
            } else {
                $values = $inputdata["models"];
            }
            
            $cache = array();
            $time = substr(time(), -4);
            foreach($values as $index => $value) {
                if($cloneby == 'color') {
                    $product = setProductColor($product, $value);
                } else {
                    $product = setProductModel($product, $value);
                }
                
                // set NAME
                if(!empty($newName)) {
                    $product = setProductName($product, $newName);
                }
                
                // set SKU
                $newSku = ( !empty($skuprefix) ? $skuprefix : $sku);
                if($cloneby == 'color') {
                    // do nothing
                } else {
                    // crop words
                    $model = $value;
                    $leftcrop = $inputdata["lcropmodel"];
                    if($leftcrop && is_numeric($leftcrop)) {
                        $words = explode( " ", $value);
                        array_splice( $words, 0, $leftcrop);
                        $model = implode( " ", $words );
                    }
                    $value = $model;
                }
                if(substr($newSku, -1) != "_" && substr($newSku, -1) != ".") {
                    $newSku = $newSku . '.';
                } 
                $newSku = $newSku . vn_urlencode($value);


                if($inputdata["appendtime"]) {
                    $newSku .= '.' . $time;
                }

                $product = setProductSku($product, $newSku);
                
                if(isset($inputdata["qtys"][$index])) {
                    $qty = $inputdata["qtys"][$index];
                    $product = setProductQuantity($product, $qty);
                }

                if(isset($inputdata["prices"][$index])) {
                    $price = $inputdata["prices"][$index];
                    $product = setProductPrice($product, $price);
                }

                // set images
                if(isset($inputdata["images"][$index])) {
                    // migrate images
                    $images = migrateImages($accessToken, $inputdata["images"][$index], $cache);
                    $product = setProductImages($product, $images, FALSE);       
                } else {
                    $product = setProductImages($product, $backupimages, TRUE);   
                }
                 
                if(!intval($preview)) {
                    createProduct($accessToken, $product);
                } else {
                    myecho("PREVIEWING ...");
                }
                
                // store to print
                $created["sku"][] = $newSku;
                $created["associatedsku"][] = $sku;
                $created["name"][] = $product['Attributes']["name"];
                $created["img"][] = $product['Skus'][0]['Images'];
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

function massCloneProduct($accessToken, $srcSkus, $newSkus, $delsource = 0) {
    $srcSkus = pre_process_skus($srcSkus);
    $newSkus = pre_process_skus($newSkus);

    $createdSkus = array();
    $needDelSkus = array();
    $products = getProducts($accessToken, '', 'all', $srcSkus);

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
    $products = getProducts($accessToken, '', 'all', $srcSkus);

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
        $list = getProducts($accessToken, '', 'all', $chunk);

        foreach($list as $product) {
                $product = prepareProductForUpdating($product);
                
                $size = count($product['Skus']);
                for($i=0; $i<$size; $i++) {
                    $dict = &$product['Skus'][$i];
                    $sku = $dict["SellerSku"];

                    // if special_price = 0, cause error
                    if(empty($dict['special_price'])) {
                        $dict['special_price'] = $dict['price'] - 1000;
                    }

                    // copy images
                    if(in_array("1", $options)) {
                        foreach ($imageindexes as $index) {
                            $t = $index - 1;
                            $dict['Images'][$t] = $srcproduct['skus'][0]['Images'][$t];
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
                    $product['Attributes']['description'] = $srcproduct['attributes']['description'];
                }

                //var_dump($product);
                
                // create XML payload from $product
                $request = array("Product" => $product);
                $xml = new ArrayToXML();
                $payload = $xml->buildXML($request, 'Request');
                
                // log payload
                //echo htmlentities($payload, ENT_COMPAT, 'UTF-8');
                
                $c = new LazopClient($GLOBALS['apiUrl'],$GLOBALS['appKey'],$GLOBALS['appSecret']);
                $request = new LazopRequest('/product/update');
                $request->addApiParam('payload', $payload);
                
                $response = json_decode($c->execute($request, $accessToken), true);
                if($response["code"] == "0") {
                    myecho("success : " . $sku);
                } else {
                    myvar_dump($response);
                }
        }

        // sleep 0.5s
        usleep(500000);
    }
}

//####################################################################
// Delete region
//####################################################################

function delProducts($accessToken, $skus) {
    $skus = pre_process_skus($skus);

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