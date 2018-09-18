<?php
include_once "config.php";
include_once "lazsdk/LazopSdk.php";


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

if(!$accessToken) {
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
    
    if($response['code'] == "0") {
        $accessToken = $response['access_token'];
        $expire = $response['expires_in'];
        setcookie("access_token",$accessToken,time()+$expire);
        
        $refreshToken = $response['refresh_token'];
        $refreshExpire = $response['refresh_expires_in'];
        setcookie("refresh_token",$refreshToken,time()+$refreshExpire);
    } else {
        echo "GET TOKEN ERROR";
        //header("Location: ".$GLOBALS['authLink']); 
        exit();
    }
} else {
}

?>