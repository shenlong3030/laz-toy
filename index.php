<?php
include_once "check_token.php";
?>

<!DOCTYPE html>
<html>
<head>
  <title>LAZADA Toys</title>
  <link rel="shortcut icon" type="image/x-icon" href="./ico/tool.ico" />
  <script type="text/javascript">
		function getCookie(cname) {
		    var name = cname + "=";
		    var decodedCookie = decodeURIComponent(document.cookie);
		    var ca = decodedCookie.split(';');
		    for(var i = 0; i <ca.length; i++) {
		        var c = ca[i];
		        while (c.charAt(0) == ' ') {
		            c = c.substring(1);
		        }
		        if (c.indexOf(name) == 0) {
		            return c.substring(name.length, c.length);
		        }
		    }
		    return "";
		}
		function deleteCookie( name ) {
		  	document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
		}
		function showToken(){
			prompt("Copy token to clipboard: Ctrl+C, Enter", getCookie("access_token"));
		}
		function logout(){
			deleteCookie("access_token");
		}
	</script>
</head>

<body>
<div style ='font:30px/40px Arial,tahoma,sans-serif;'>
<a target="_blank" href="/lazop/create_voucher.php?">Tạo tên voucher giảm giá</a><br/>

<a target="_blank" href="/lazop/orders.php?needfull=1">Đơn hàng mới</href><br/>
<a target="_blank" href="/lazop/products2.php?status=all">Danh sách sản phẩm</href><br/>
<hr>
<a target="_blank" href="/lazop/>orders.php?needfull=1&shopid=01">Đơn hàng mới SHSHOP01</href><br/>
<hr>
<a onclick="logout()" href="#">Logout</a><br/>
</body>

</html>



