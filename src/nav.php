<?php
include_once "check_token.php";
require_once('_main_functions.php');

$token = $GLOBALS["accessToken"];
$info = getSellerInfo($token);

//$info = {"name_company":"HỘ KINH DOANH ELY VÂN","name":"SHSHOP 01","location":"Hồ Chí Minh","seller_id":100021640,"email":"shshop3030.01@gmail.com","short_code":"VN10UTH","cb":false}
?>

<div class="nav">
	<div></div>
	<div>
	    <a tabindex="-1" onclick="logout()" href="#">Logout</a>&nbsp;&nbsp;
	    <a tabindex="-1" onclick="showToken()" href="#">Show token</a>&nbsp;&nbsp;
	    <span class="account"><?php echo $info["name"], " - ", $info["short_code"] , " ", $GLOBALS["shopid"]?></span>
    </div>
</div>