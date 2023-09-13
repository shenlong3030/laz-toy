<?php
include_once "config.php";
include_once "lazsdk/LazopSdk.php";

function getSellerInfo($accessToken) {
    $c = new LazopClient($GLOBALS['apiUrl'],$GLOBALS['appKey'],$GLOBALS['appSecret']);
    $request = new LazopRequest('/seller/get', 'GET');
    $response = $c->execute($request, $accessToken);
    $response = json_decode($response, true);

    $info = array();
    if($response["code"] == "0") {
        $info = $response['data'];
    }
    return $info;
}

function isValidInfo($info) {
    // if($info["short_code"] == "VN107AK" || $info["short_code"] == "VN10UTH") {
    //     return TRUE;
    // } else {
    //     return FALSE;
    // }
    return true;
}

$logout = isset($_GET['logout']) ? $_GET['logout'] : null;
if($logout) {
    unset($_COOKIE['access_token']);
    setcookie('access_token', '', time() - 3600);
    
    header("Location: ".$GLOBALS['authLink']); 
    exit();
}

// unset($_COOKIE['access_token']);
// setcookie('access_token', '', time() - 3600);

$accessToken = $_COOKIE["access_token"];
$refreshToken = $_COOKIE["refresh_token"];

if(!$accessToken) {
    if($refreshToken) { // refresh token
        $c = new LazopClient($authUrl, $appKey, $appSecret);
        $request = new LazopRequest('/auth/token/refresh');
        $request->addApiParam('refresh_token',$refreshToken);
        $response = json_decode($c->execute($request), true);
    } else {
        // get code param
        $code = isset($_GET['code']) ? $_GET['code'] : null;
        if(!$code) {
            header("Location: ".$GLOBALS['authLink']); 
            exit();
        }
        
        $c = new LazopClient($authUrl, $appKey, $appSecret);
        $request = new LazopRequest("/auth/token/create");
        $request->addApiParam('code', $code);
        $response = json_decode($c->execute($request), true);
    }
    
    if($response['code'] == "0") {
        $accessToken = $response['access_token'];

        $info = getSellerInfo($accessToken);
        if(!isValidInfo($info)) {
            echo "INVALID SELLER ID";
            exit();
        }

        $expire = $response['expires_in'];
        setcookie("access_token",$accessToken,time()+$expire);
        
        $refreshToken = $response['refresh_token'];
        $refreshExpire = $response['refresh_expires_in'];
        setcookie("refresh_token",$refreshToken,time()+$refreshExpire);
    } else {
        echo "GET TOKEN ERROR";
        //header("Location: ".$GLOBALS['authLink']);
        
        // clear cookie access_token , refresh_token
        setcookie("access_token", "", time()-3600);
        setcookie("refresh_token", "", time()-3600); 
        exit();
    }
} else {
    $info = getSellerInfo($accessToken);
    if(!isValidInfo($info)) {
        echo "INVALID SELLER ID";
        // clear cookie access_token , refresh_token
        setcookie("access_token", "", time()-3600);
        setcookie("refresh_token", "", time()-3600);
        exit();
    }
}

?>