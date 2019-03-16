<?php
//####################################################################
// Migrate images functions
// API Documentation:
// https://open.lazada.com/doc/api.htm?spm=a2o9m.11193487.0.0.3ac413fers8uIL#/api?cid=5&path=/image/migrate
//####################################################################

require_once('src/helper.php');

// return migrated image url or ERROR MESSAGE
function migrateImage($accessToken, $imageUrl, $retry = 3) {
    $output = '';
    
    if(!is_url($imageUrl)) {
        return "Invalid URL: " + $imageUrl;
    }
    
    $c = getClient();
    $request = getRequest('/image/migrate');

    $payload = '<?xml version="1.0" encoding="UTF-8"?><Request><Image><Url>'.$imageUrl.'</Url></Image></Request>';
    
    $request->addApiParam('payload',$payload);
    $response = $c->execute($request, $accessToken);
    $response = json_decode($response, true);
    
    if($response['code'] == '0') {
        $output = $response['data']['image']['url'];
        //myecho("", __FUNCTION__);
    } else {
        if($retry) {
            sleep(3);
            $output = migrateImage($accessToken, $imageUrl, $retry-1);
        } else {
            $output = $response['message'];
            myecho("Migrate failed, URL: " . $imageUrl, __FUNCTION__);
        }
    }

    return $output;
}

// return migrated images
function migrateImages($accessToken, $images, &$cache = null) {
    $output = array();

    // convert string to array
    if(is_string($images)) {
        $images = preg_split("/\s+/", $images);
    }

    foreach($images as $url) {
        if(isLazadaImage($url)) {
            $output[] = $url;
        } else {
            if(!isset($cache[sha1($url)])) {
                $cache[sha1($url)] = migrateImage($accessToken, $url);  
                sleep(1);
            }
            
            // only output valid URL
            if(is_url($cache[sha1($url)])) {
                $output[] = $cache[sha1($url)];
            }
        }
    }
    
    return $output;
}

?>