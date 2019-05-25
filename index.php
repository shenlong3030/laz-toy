<?php
include_once "check_token.php";
?>

<!DOCTYPE html>
<html>
<head>
  <title>LAZADA Toys</title>
  <link rel="shortcut icon" type="image/x-icon" href="./ico/tool.ico" />
  <script src="./js/auth.js"></script>
</head>

<body>
<div style ='font:30px/40px Arial,tahoma,sans-serif;'>
<a target="_blank" href="/lazop/create_voucher.php?">Tạo tên voucher giảm giá</a><br/>

<a target="_blank" href="/lazop/orders.php?needfull=1">Đơn hàng mới</href><br/>
<a target="_blank" href="https://docs.google.com/spreadsheets/d/18DLl1yCn3_dzQxn5nLzlWrGEWd7LOQauWKYhHxIfoqw/edit#gid=0">Kiểm tra đơn hàng trùng / thiếu</href><br/>
<a target="_blank" href="/lazop/products.php?status=all&show_child=1">Danh sách sản phẩm</href><br/>
<hr>
<a target="_blank" href="/lazop/>orders.php?needfull=1&shopid=01">Đơn hàng mới SHSHOP01</href><br/>
<hr>
<a onclick="logout()" href="#">Logout</a><br/>
</body>

</html>



