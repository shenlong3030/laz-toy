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
    $str_data = file_get_contents("token.json");
    $data = json_decode($str_data,true);
    $accessToken = $data["access_token"];
    $expireAt = $data["expires_at"];
    $isExpired = time() > $expireAt;
}

if(!$accessToken || $isExpired) {
    // get code param
    $code = isset($_REQUEST['code']) ? $_REQUEST['code'] : null;
    
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
        $data["access_token"] = $accessToken;
        $data["expires_at"] = time()+$expire;

        $refreshToken = $response['refresh_token'];
        $refreshExpire = $response['refresh_expires_in'];
        setcookie("refresh_token",$refreshToken,time()+$refreshExpire);
        $data["refresh_token"] = $refreshToken;
        $data["refresh_expires_at"] = time()+$refreshExpire;

        // write $data to FILE token.json
        $fh = fopen("token.json", 'w')
              or die("Error opening token file");
        fwrite($fh, json_encode($data,JSON_UNESCAPED_UNICODE));
        fclose($fh);
    } else {
        echo "GET TOKEN ERROR";
        //header("Location: ".$GLOBALS['authLink']); 
        exit();
    }
} else {
    // set token from FILE to COOKIE
    setcookie("access_token",$accessToken,$expireAt);
}

?>