<?php
include_once "check_token.php";
?>

<!DOCTYPE html>
<html>
<head>
  <title>EXTRA</title>
  <link rel="shortcut icon" type="image/x-icon" href="./ico/extra.ico" />

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

<a target="_blank" href="/lazop/orders.php?needfull=1">Đơn hàng mới</a><br/>
<a target="_blank" href="/lazop/products.php?status=all&nochild=1">Danh sách sản phẩm (Không hiện SP con)</a><br/>
<a target="_blank" href="/lazop/products.php?status=all">Danh sách sản phẩm (Hiện SP con)</a><br/>
<hr> 
<a target="_blank" href="/lazop/massclone.php">Mass clone SP</a><br/>
<a target="_blank" href="/lazop/massclone_shop2shop.php">Mass clone SHOP to SHOP</a><br/>
<a target="_blank" href="/lazop/create1_gui.php">Tạo SP từ nhiều source</a><br/>

<a target="_blank" href="/lazop/movechild.php">Move child</a><br/>

<a target="_blank" href="/lazop/update_gui.php">Cập nhật SP</a><br/>
<a target="_blank" href="/lazop/massupdate_gui.php">Mass update</a><br/>
<hr> 
<a target="_blank" href="/lazop/copyinfo.php">Copy thuộc tính SP</a><br/>
<a target="_blank" href="/lazop/del.php">Xoá SP</a><br/>
<hr> 
<a target="_blank" href="/lazop/previewimages.php">Preview images</a><br/>
<a target="_blank" href="/lazop/previewproducts.php">Preview products</a><br/>
<hr>
<a target="_blank" href="/lazop/laz_getstar.php">Get star LAZADA</a><br/>
<a target="_blank" href="/lazop/kiot.php">KIOT LAZADA</a><br/>
<a target="_blank" href="/lazop/fix_product.php">FIX PRODUCTS</a><br/>

<a onclick="showToken()" href="#">Show token</a><br/>
<a onclick="logout()" href="#">Logout</a><br/>
</body>

</html>



