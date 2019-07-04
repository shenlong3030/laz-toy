<?php
include_once "check_token.php";
require_once('_main_functions.php');

$token = $GLOBALS["accessToken"];
$info = getSellerInfo($token);

//$info = {"name_company":"HỘ KINH DOANH ELY VÂN","name":"SHSHOP 01","location":"Hồ Chí Minh","seller_id":100021640,"email":"shshop3030.01@gmail.com","short_code":"VN10UTH","cb":false}
?>

<style type="text/css">
	.nav{
      display: inline-block;
      padding-right: 10px;
      float: right;
      color: red;
    }
</style>


<div class="nav" style="width: 100%;padding-bottom: 10px;border-bottom: 1px solid lightgrey">
    <div class="nav logout-link"><a tabindex="-1" onclick="logout()" href="#">Logout</a><br/></div>
    <div class="nav token-link"><a tabindex="-1" onclick="showToken()" href="#">Show token</a></div>
    <div class="nav account"><?php echo $info["name"], " - ", $info["short_code"] , " ", $GLOBALS["shopid"]?></div>
</div>