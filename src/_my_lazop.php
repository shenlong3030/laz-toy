<?php
require_once('src/ArrayToXML.php');
require_once('src/helper.php');

function getClient() {
    return new LazopClient($GLOBALS['apiUrl'],$GLOBALS['appKey'],$GLOBALS['appSecret']);
}

function getRequest($path, $method = 'POST') {
    return new LazopRequest($path, $method);
}

function isLazadaImage($url) {
    return preg_match("/slatic.net/i", $url);
}

//status: all, live, inactive, deleted, image-missing, pending, rejected, sold-out
function getProducts($accessToken, $q = '', $offset = 0, $status = 'sold-out', $skulist = null){
    $c = getClient();
    $request = new LazopRequest('/products/get','GET');
    $request->addApiParam('filter', $status);
    if(strlen($q)) {
        $request->addApiParam('search',$q);
    }
    $request->addApiParam('offset', $offset);
    $request->addApiParam('limit','500');
    //$request->addApiParam('create_after','2010-01-01T00:00:00+0800');
    //$request->addApiParam('update_after','2010-01-01T00:00:00+0800');
    $request->addApiParam('options','1');
    
    // filter by SKU
    if($skulist && count($skulist)) {
        $request->addApiParam('sku_seller_list',json_encode($skulist));
    }
    
    $response = $c->execute($request, $accessToken);
    //echo $response;
    $response = json_decode($response, true);
    
    $products = array();
    if($response["code"] == "0") {
        $products = $response["data"]["products"];
    }
    
    return $products;
}

function createProduct($accessToken, $product) {
    // create XML payload
    $request = array("Product" => $product);
    $xml = new ArrayToXML();
    $payload = $xml->buildXML($request, 'Request');
    
    $c = getClient();
    $request = new LazopRequest('/product/create');
    $request->addApiParam('payload', $payload);
    $res = $c->execute($request, $accessToken);
    var_dump($res);
    return $res;
}

function createProducts($accessToken, $sku, $skuprefix, $data, $combos, $comboimages, $prices, $preview = 1) {
    $skulist = array($sku);
    $products = getProducts($accessToken, '', 0, 'all', $skulist);
    
    if(count($products) == 1) {
        $product = $products[0];
        
        $product['Attributes'] = $product['attributes'];
        $product['Skus'] = $product['skus'];
        $product['PrimaryCategory'] = $product['primary_category'];
        unset($product['attributes']);
        unset($product['skus']);
        unset($product['primary_category']);
        
        $backupimages = $product['Skus'][0]['Images'];
        
        
        $time = time();
        $created = array();
        $associatedskus = array();
        $savedimages = array();
        foreach($data["names"] as $index => $name) {
            $time++;
            foreach($combos as $combo) {
                $price = $prices[$combo];
                $cbimage = val($comboimages[$combo]);
                
                if($combo == 0) { // buy 1 get 1
                    $product['Attributes']["name"] = "$name" . " (Mua 1 tặng 1)";
                } else if ($combo == 1) { 
                    $product['Attributes']["name"] = $name;
                } else { // combo 2, 3, 4, 5
                    $product['Attributes']["name"] = "Combo " . $combo . " " . $name;
                }
                
                // SKU = prefix + branch + time
                $branch = isset($data["branches"][$index]) ? (vn_urlencode($data["branches"][$index])) : "";
                $newSku = $skuprefix . '.' . $branch;
                
                // set color
                if(isset($data["colors"][$index])) {
                    $product['Skus'][0]['color_family'] = $data["colors"][$index];
                    $newSku = $newSku . '.' . vn_urlencode($data["colors"][$index]);
                }
                
                // set model
                if(isset($data["models"][$index])) {
                    $model = $data["models"][$index];
                    $product['Skus'][0]['compatibility_by_model'] = $model;
                    
                    // crop words
                    $leftcrop = $data["lcropmodel"];
                    if($leftcrop && is_numeric($leftcrop)) {
                        $words = explode( " ", $model);
                        array_splice( $words, 0, $leftcrop);
                        $model = implode( " ", $words );
                    }
                    $newSku = $newSku . '.' . vn_urlencode($model);
                }
                
                $newSku = $newSku . '.' . time();
                
                if($combo == 0) { // buy 1 get 1
                    $newSku = $newSku . "." . "1TANG1";
                } else if ($combo == 1) { 
                    // do nothing
                } else { // combo 2,3,4,5 
                    $newSku = $newSku . "." . "X" . $combo;
                }
                
                // convert SKU to UPPERCASE, set SKU
                $newSku = strtoupper($newSku);
                $product['Skus'][0]['SellerSku'] = $newSku;
                
                // set price
                $product['Skus'][0]['price'] = round($price * 1.3 / 100) * 100;
                $product['Skus'][0]['special_price'] = $price;
                $product['Skus'][0]['special_from_date'] = "2018-01-01";
                $product['Skus'][0]['special_to_date'] = "2020-12-12";
                
                // set quantity
                if(isset($data["qtys"][$index])) {
                    $product['Skus'][0]['quantity'] = $data["qtys"][$index];
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
                $product['Skus'][0]['Images'] = array();
                if(!empty($cbimage)) {
                    // migrate image
                    if(!isLazadaImage($cbimage)) {
                        $cbimage = migrateImage($accessToken, $cbimage);
                    }
                    
                    $product['Skus'][0]['Images'][] = $cbimage;
                } else if(isset($data["images"][$index])) {
                    // slit images
                    $images = $data["images"][$index];
                    if(is_string($images)) {
                        $images = array_filter(preg_split("/\s+/", $images));
                        
                        $migratedimgs = migrateImages($accessToken, $images, $savedimages);
                        foreach($migratedimgs as $index => $url) {
                            if (filter_var($url, FILTER_VALIDATE_URL)) {
                                $product['Skus'][0]['Images'][] = $url;
                            } 
                        }
                    }
                } else {
                    $product['Skus'][0]['Images'] = $backupimages;
                }

                //echo "<br>", $combo, " @ ", $cbimage, " @ ", $product['Skus'][0]['Images'][0];

                //echo "<br>",$product['Attributes']["name"]," : ",$newSku, " : ", $product['Skus'][0]['price'], " : ", $product['Skus'][0]['special_price'], " : ", $product['Skus'][0]['quantity'], " : ", $product['Skus'][0]['Images'][0];
            
                if(!$preview) {
                    createProduct($accessToken, $product);
                    usleep(300000);
                }
                
                // store to print
                $created["sku"][] = $newSku;
                $created["name"][] = $product['Attributes']["name"];
                $created["imgs"][] = $product['Skus'][0]['Images'];
            }
        }
        
        foreach($created["sku"] as $index => $sku) {
            echo "<br>",$sku, htmlLinkImages($created["imgs"][$index]);
        }
    } else {
        echo "<br><br>Wrong SKU<br><br>";
    }
}

function cloneProduct($accessToken, $sku, $cloneby, $inputdata, $skuprefix, $newName, $preview = 1) {
    $skulist = array($sku);
    $list = getProducts($accessToken, '', 0, 'all', $skulist);

    if(count($list) == 1) {
        $product = $list[0];
        
        $product['Attributes'] = $product['attributes'];
        $product['Skus'] = $product['skus'];
        $product['PrimaryCategory'] = $product['primary_category'];
        unset($product['attributes']);
        unset($product['skus']);
        unset($product['primary_category']);
        
        $backupimages = $product['Skus'][0]['Images'];

        $newSkus = array();
        $created = array();
        if($cloneby == 'original') {
            // set new sku
            $newSku = '';
            if(!empty($skuprefix)) {
                $newSku = $skuprefix . '.' . time();
            } else {
                preg_match('/(.+\.v)([1-9][0-9]*)/', $sku, $matches);
            
                if(count($matches)) {
                    $newSku = $matches[1].($matches[2]+1);
                } else {
                    $newSku = $sku.'.v1';
                }
            }
            array_push($newSkus, $newSku);
            $product['Skus'][0]['SellerSku'] = $newSku;
            
            // set new product name
            if(!empty($newName)) {
                $product['Attributes']["name"] = $newName;
            }
            
            if(!$preview) {
                createProduct($accessToken, $product);
            }
        } else {
            $product['AssociatedSku'] = $sku;
            
            if($cloneby == 'color') {
                $values = $inputdata["colors"];
            } else {
                $values = $inputdata["models"];
            }
            
            foreach($values as $index => $value) {
                if($cloneby == 'color') {
                    $product['Skus'][0]['color_family'] = $value;
                } else {
                    $product['Skus'][0]['compatibility_by_model'] = $value;
                }
                
                // set new product name
                if(!empty($newName)) {
                    $product['Attributes']["name"] = $newName;
                }
                
                // create newSku and save in array
                $newSku = ( !empty($skuprefix) ? $skuprefix : $sku).'.'.vn_urlencode($value).'.'.time();
                $newSku = strtoupper($newSku);

                $newSkus[] = $newSku;
                $product['Skus'][0]['SellerSku'] = $newSku;
                
                // set images
                if(!empty($inputdata["images"])) {
                    if(isset($inputdata["images"][$index]) && filter_var($inputdata["images"][$index], FILTER_VALIDATE_URL)) {
                        // migrate image
                        if(!isLazadaImage($inputdata["images"][$index])) {
                            $inputdata["images"][$index] = migrateImage($accessToken, $inputdata["images"][$index]);
                        }

                        $product['Skus'][0]['Images'][0] = $inputdata["images"][$index];
                    } else {
                        $product['Skus'][0]['Images'][0] = $backupimages[0];
                    }
                    
                }
                 
                if(!$preview) {
                    createProduct($accessToken, $product);
                }
                
                // store to print
                $created["sku"][] = $newSku;
                $created["associatedsku"][] = $sku;
                $created["name"][] = $product['Attributes']["name"];
                $created["img"][] = $product['Skus'][0]['Images'][0];
                $created["img1"][] = $product['Skus'][0]['Images'][1];
            }
        }
        
        // print new SKUs
        echo '<hr><h3>New SKUs</h3>';
        foreach($created["sku"] as $index => $sku) {
            echo "<br>",$sku, htmlLinkImage($created["img"][$index]), htmlLinkImage($created["img1"][$index]);
        }
    } else {
        echo "<br><br>Wrong sku<br><br>";
    }
}


// return migrated image url or ERROR MESSAGE
function migrateImage($accessToken, $imageUrl, $retry = 3) {
    $output = '';
    
    $c = getClient();
    $request = getRequest('/image/migrate');

    $payload = '<?xml version="1.0" encoding="UTF-8"?><Request><Image><Url>'.$imageUrl.'</Url></Image></Request>';
    
    $request->addApiParam('payload',$payload);
    $response = $c->execute($request, $accessToken);
    $response = json_decode($response, true);
    
    if($response['code'] == '0') {
        $output = $response['data']['image']['url'];
    } else {
        if($retry) {
            sleep(3);
            $output = migrateImage($accessToken, $imageUrl, $retry-1);
        } else {
            $output = $response['message'];
        }
    }

    return $output;
}

// return migrated images url or ERROR MESSAGEs
function migrateImages($accessToken, $images, &$savedimages = null) {
    $output = array();

    foreach($images as $url) {
        if(isLazadaImage($url)) {
            $output[] = $url;
        } else {
            if(!isset($savedimages[$url])) {
                $savedimages[$url] = migrateImage($accessToken, $url);  
                echo "<br>call migrate<br>";
                sleep(1);
            }
            $output[] = $savedimages[$url];
        }
    }
    
    return $output;
}

function setPrimaryCategory($accessToken, $sku, $category) {
    $skulist = array($sku);
    $list = getProducts($accessToken, '', 0, 'all', $skulist);
    
    if(count($list) == 1) {
        $product = $list[0];
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

function setName($accessToken, $sku, $name) {
    $skulist = array($sku);
    $list = getProducts($accessToken, '', 0, 'all', $skulist);
    
    if(count($list) == 1) {
        $namePayload = '';
        if($name) {
            $namePayload = '<Attributes><name>'.$name.'</name></Attributes>';
        }
        
        $payload = '<?xml version="1.0" encoding="UTF-8"?><Request><Product>'.$namePayload.'<Skus><Sku><SellerSku>'.$sku.'</SellerSku></Sku></Skus></Product></Request>';
        
        $c = getClient();
        $request = getRequest('/product/update');
        $request->addApiParam('payload', $payload);
        $response = $c->execute($request, $accessToken);
        //var_dump($response);
        $response = json_decode($response, true);
        return $response;
    } else {
        $response["code"] = 1;
        $response["message"] = "Invalid SKU: ".$sku;
    }
}

function setQuantity($accessToken, $sku, $qty) {
    $skulist = array($sku);
    $list = getProducts($accessToken, '', 0, 'all', $skulist);
    
    if(count($list) == 1) {
        $qtyPayload = '<Quantity>'.$qty.'</Quantity>';
    
        $payload = '<?xml version="1.0" encoding="UTF-8"?><Request><Product><Skus><Sku><SellerSku>'.$sku.'</SellerSku>'.$qtyPayload.'</Sku></Skus></Product></Request>';
        
        $c = getClient();
        $request = getRequest('/product/price_quantity/update');
        $request->addApiParam('payload', $payload);
        $response = $c->execute($request, $accessToken);
        //var_dump($response);
        $response = json_decode($response, true);
        return $response;
    } else {
        $response["code"] = 1;
        $response["message"] = "Invalid SKU: ".$sku;
    }
}

function setPrices($accessToken, $sku, $price, $sale_price, $fromdate = "2018-01-01", $todate = "2028-01-01") {
    $skulist = array($sku);
    $list = getProducts($accessToken, '', 0, 'all', $skulist);
    
    if(count($list) == 1) {
        $pricePayload = '';
        if($price && $sale_price) {
            $pricePayload = '<Price>'.$price.'</Price><SalePrice>'.$sale_price.'</SalePrice><SaleStartDate>'.$fromdate.'</SaleStartDate><SaleEndDate>'.$todate.'</SaleEndDate>';
        }

        $payload = '<?xml version="1.0" encoding="UTF-8"?><Request><Product><Skus><Sku><SellerSku>'.$sku.'</SellerSku>'.$pricePayload.'</Sku></Skus></Product></Request>';
        
        $c = getClient();
        $request = getRequest('/product/price_quantity/update');
        $request->addApiParam('payload', $payload);
        $response = $c->execute($request, $accessToken);
        //var_dump($response);
        $response = json_decode($response, true);
        return $response;
    } else {
        $response["code"] = 1;
        $response["message"] = "Invalid SKU: ".$sku;
    }
}

function setImages($accessToken, $sku, $images, &$savedimages = null) {
    $skulist = array($sku);
    $list = getProducts($accessToken, '', 0, 'all', $skulist);
    
    if(count($list) == 1) {
        $product = $list[0];
        $product['Skus'] = $product['skus'];
        unset($product['skus']);
        unset($product['attributes']);
        unset($product['primary_category']);
        
        $product['Skus'][0]['Images'] = array();
        
        $migratedimgs = migrateImages($accessToken, $images, $savedimages);
        foreach($migratedimgs as $index => $url) {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $product['Skus'][0]['Images'][] = $url;
            } 
        }

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

function massCloneProduct($accessToken, $srcSkus, $newSkus, $delsource = 0) {
    $createdSkus = array();
    foreach($srcSkus as $index => $sku) {
        $skulist = array($sku);
        $list = getProducts($accessToken, '', 0, 'all', $skulist);
        
        if(count($list) == 1) {
            $product = $list[0];
            
            $product['Attributes'] = $product['attributes'];
            $product['Skus'] = $product['skus'];
            $product['PrimaryCategory'] = $product['primary_category'];
            unset($product['attributes']);
            unset($product['skus']);
            unset($product['primary_category']);
            
            $backupimages = $product['Skus'][0]['Images'];
            
            // force active product
            $product['Skus'][0]['Status'] = "active";
 
            $newSku = '';
            // set new sku
            if(isset($newSkus[$index])) {
                $newSku = $newSkus[$index];
            } else {
                preg_match('/(.+\.v)([1-9][0-9]*)/', $sku, $matches);
                if(count($matches)) {
                    $newSku = $matches[1].(trim($matches[2])+1);
                } else {
                    $newSku = trim($sku).'.v1';
                }
                
                $createdSkus[] = $newSku;
                $product['Skus'][0]['SellerSku'] = $newSku;
            }

            createProduct($accessToken, $product);
        } else {
            echo "<br>Wrong sku : ", $sku, "<br>";
        }
    }
    
    // print new SKUs
    echo '<hr><h3>New SKUs</h3>';
    foreach($createdSkus as $item) {
        echo $item, '<br>';
    }
    
    if($delsource) {
        echo '<hr><h3>Delete source SKUs</h3>';
        delProduct($accessToken, $srcSkus);
    }
}

function massUpdateImages($accessToken, $skus, $images) {
    $savedimages = array();
    $success = array();
    foreach($skus as $index => $sku) {
        // split images
        $input = $images[$index];
        $list = preg_split("/\s+/", $input);

        $r = setImages($accessToken, $sku, $list, $savedimages);

        if($r["code"] == 0) {
            $success['skus'][] = $sku;
            $success['images'][] = $r["images"];
        }
        
        echo "<br>shen ";
        var_dump($r);
        echo "<br>";
    }
    
    
    // print new SKUs
    echo '<hr><h3>New SKUs</h3>';
    foreach($success['skus'] as $i => $sku) {
        echo $sku, htmlLinkImages($success['images'][$i]), "<br>";
    }
}

function delProducts($accessToken, $skus) {
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

function getOrderLinkPostfix(){
    $postfix = '/15605';
    if($GLOBALS['shopid'] == '01') {
        $postfix = '/100021640';
    }
    return $postfix;
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
    //var_dump($response);
    return $response;
}

function printOrders($orders, $offset = 0, $needFullOrderInfo = 0, $status = "") {
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
        $item = $needFullOrderInfo ? getOrderItems($_COOKIE["access_token"], $orderId) : array();
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
        
        $cdate = preg_replace('/(\+0700)/i', '', $order["created_at"]);
        echo '<td><b>'.$cdate.'</b></td>';
        
        //echo '<td>'.$order["updated_at"].'</td>'; 
        echo '<td></td>';
        
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
                    if(preg_match("(\/)", $item['name'], $out)) {
                        $variation = "•• Lựa chọn: ".$variation_value;
                    }
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

// copy image, desc, short desc, ...
// options[] = 1 : copy images
// options[] = 2 : copy price
// options[] = 3 : copy desc + short desc
// options[] = 4 : copy size, weight
function copyInfoToSkus($accessToken, $sourcesku, $skus, $options) {
    $list = getProducts($accessToken, '', 0, 'all', array($sourcesku));
    //var_dump($srcdict);
    
    if(count($list) == 0) {
        echo "<h2>Wrong source SKU</h2>";
        return;
    }
    $srcproduct = $list[0];
    
    // chia thành các mảng nhỏ 20item
    $chunks = array_chunk($skus, 20);
    
    foreach($chunks as $chunk) {
        $list = getProducts($accessToken, '', 0, 'all', $chunk);

        foreach($list as $product) {
                // fix key name
                $product['Skus'] = $product['skus'];
                
                // unset old key name
                unset($product['attributes']);
                unset($product['skus']);
                
                // if special_price = 0, cause error
                if(empty($product['Skus'][0]['special_price'])) {
                    $product['Skus'][0]['special_price'] = $product['Skus'][0]['price'] - 1000;
                }
                
                // copy images
                if(in_array(1, $options)) {
                    //echo "<hr>1<hr>";
                    $product['Skus'][0]['Images'] = $srcproduct['skus'][0]['Images'];
                }
                
                // copy prices
                if(in_array(2, $options)) {
                    //echo "<hr>2<hr>";
                    $product['Skus'][0]['price'] = $srcproduct['skus'][0]['price'];
                    $product['Skus'][0]['special_price'] = $srcproduct['skus'][0]['special_price'];
                    $product['Skus'][0]['special_to_date'] = $srcproduct['skus'][0]['special_to_date'];
                    $product['Skus'][0]['special_from_date'] = $srcproduct['skus'][0]['special_from_date'];
                }
                
                // copy desc
                if(in_array(3, $options)) {
                    //echo "<hr>3<hr>";
                    $product['Attributes']['short_description'] = $srcproduct['attributes']['short_description'];
                    $product['Attributes']['description'] = $srcproduct['attributes']['description'];
                }
                
                // copy size, weight, package content
                if(in_array(4, $options)) {
                    //echo "<hr>4<hr>";
                    $product['Skus'][0]['package_width'] = $srcproduct['skus'][0]['package_width'];
                    $product['Skus'][0]['package_height'] = $srcproduct['skus'][0]['package_height'];
                    $product['Skus'][0]['package_length'] = $srcproduct['skus'][0]['package_length'];
                    $product['Skus'][0]['package_weight'] = $srcproduct['skus'][0]['package_weight'];
                    $product['Skus'][0]['package_content'] = $srcproduct['skus'][0]['package_content'];
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
                
                $res = $c->execute($request, $accessToken);
                var_dump($res);
                echo "<br>";
            }

        // sleep 0.5s
        usleep(500000);
    }
}

function printProducts($products, $offset = 0, $options) {
    foreach($products as $index=>$product) {
        $GLOBALS['count'] += 1;
        
        //var_dump($product);
        $attrs = $product['attributes'];
        $name = $attrs['name'];
        
        $sku = $product['skus'][0];
        $price1 = $sku['price'];
        $price2 = $sku['special_price'];
        $qty = $sku['quantity'];
        $reservedStock = $sku['ReservedStock'];
        $url = $sku['Url'];
        $sellersku = $sku['SellerSku'];
        $shopsku = $sku['ShopSku'];
        $nameLink = '<a tabindex="-1" href="'.$url.'">'.$name.'</a>';
        $imgs = $sku['Images'];
        //$variation = $sku['_compatible_variation_'];
        
        echo '<tr>';
        //visible 
        echo '<td class="sku on">'.$sellersku.'</td>';
        // hidden
        echo '<td class="sku off">'.$shopsku.'</td>';
        $reservedTxt = $reservedStock ? '<span style="color:red">('.$reservedStock.' )</span>' : '';
        
        $qtyForm = '<form action="update.php" method="POST" name="qtyForm" target="responseIframe"><input name="sku" type="hidden" value="'.$sellersku.'"/><input name="qty" type="text" size="4" value="'.$qty.'"/><input type="submit" tabindex="-1" value="Submit" /></form>';
        
        $priceForm = '<form action="update.php" method="POST" name="priceForm" target="responseIframe"><input name="sku" type="hidden" value="'.$sellersku.'"/><input name="price" type="text" size="8" value="'.$price1.'"/>--><input name="sale_price" type="text" size="8" value="'.$price2.'"/><input type="submit" tabindex="-1" value="Submit" /></form>';
        
        $nameForm = '<form action="update.php" method="POST" name="nameForm" target="responseIframe"><input name="sku" type="hidden" value="'.$sellersku.'"/><input name="name" type="text" size="80" value="'.$name.'"/><input type="submit" tabindex="-1" value="Submit" /></form>';
        
        echo '<td>'.$reservedTxt.$qtyForm.'</td>';
        echo '<td class="name on">'.$nameLink.'</td>';
        echo '<td class="name">'.$nameForm.'</td>';
        echo '<td></td>';                   // $variation cell
        
        // hidden 
        echo '<td class="price">'.$priceForm.'</td>';
        // visible
        echo '<td class="price on">'.$price1.'</td>';
        echo '<td class="price on">'.$price2.'</td>';
        
        if(is_array($imgs)) {
            for($i=0; $i<8; $i++){
                $imgs[$i] = preg_replace('/(-catalog)/i', '', $imgs[$i]);
                $tmp = $imgs[$i];
                $thumb = '<a tabindex="-1" target="_blank" href="'.$tmp.'"><img border="0" src="'.$tmp.'" height="100"></a>';
                
                if($i == 0) {
                    echo '<td class="image">'.$imgs[$i].'</td>';
                    echo '<td class="image on">'.$thumb.'</td>';
                } else {
                    echo '<td class="image1 link">'.$imgs[$i].'</td>';
                    echo '<td class="image1 thumb">'.$thumb.'</td>';
                }
            }
        }
        //echo '<td>'.($offset + $index + 1).'</td>';
        $color = $sku['Status'] == 'inactive' ? ' style="color:red"' : '';
        echo '<td'.$color.'>'.$sku['Status'].'</td>';
        echo '</tr>';
    }
}

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