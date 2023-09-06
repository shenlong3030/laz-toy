<?php
//####################################################################
// Migrate images functions
// API Documentation:
// https://open.lazada.com/doc/api.htm?spm=a2o9m.11193487.0.0.3ac413fers8uIL#/api?cid=5&path=/image/migrate
//####################################################################

require_once('src/helper.php');

function isLazadaImage($url) {
    //return false;

    $noMigrateRegex = [
        //"/live.slatic.net/i",   // https://vn.live.slatic.net/p/0257967934799d8e77828d8f8fdcb109.jpg
        //"/test.+slatic.net/i",  // https://sg-test-11.slatic.net/p/0257967934799d8e77828d8f8fdcb109.jpg
    ];

    $flag= false;
    foreach($noMigrateRegex as $regex) {
        $flag = $flag || preg_match($regex, $url);
    }
    return $flag;
}

// return json
// migrated image pass to &$imageUrl param
function migrateImage($accessToken, &$imageUrl, $retry = 3) {
    if(!is_url($imageUrl)) {
        return "Invalid URL: " . $imageUrl;
    }
    
    $c = getClient();
    $request = getRequest('/image/migrate');

    $payload = '<?xml version="1.0" encoding="UTF-8"?><Request><Image><Url>'.$imageUrl.'</Url></Image></Request>';
    
    $request->addApiParam('payload',$payload);
    $response = $c->execute($request, $accessToken);
    $response = json_decode($response, true);

    if($response['code'] == '0') {                      // change value of ref &$imageUrl
        $imageUrl = $response['data']['image']['url'];  
        //myecho("", __FUNCTION__);
    } else {                                            // no change value of ref &$imageUrl
        if($retry) {
            sleep(3 - $retry);
            $response = migrateImage($accessToken, $imageUrl, $retry-1);
        } else {
            //myecho("Migrate failed, URL: " . $imageUrl, __FUNCTION__);
        }
    }
    sleep(1);
    return $response;
}

// return json
// migrated images list pass to &$images param
function migrateImages($accessToken, &$images, &$cache = null) {
    $output = array();
    $finalResponse = [];

    // convert string "image image image" to array [image, image, image]
    if(is_string($images)) {
        $images = preg_split("/\s+/", $images);
    }

    foreach($images as $url) {
        if(isLazadaImage($url)) {
            $output[] = $url;
        } else {
            if(!isset($cache[sha1($url)])) {
                
                // $url is reference param
                // after successful migrate, $url has new value , otherwise $url no change
                $response = migrateImage($accessToken, $url);

                $dict = [];
                $dict['url'] = $url;
                $dict['response'] = $response;

                $finalResponse[] = $dict;
                $cache[sha1($url)] = $url;
            }
            
            // only output valid URL
            if(is_url($cache[sha1($url)])) {
                $output[] = $cache[sha1($url)];
            }
        }
    }
    
    $images = $output; // pass output to reference param
    return $finalResponse;
}

?>