<!DOCTYPE html>
<html leng="en-AU">
<head><meta http-equiv="Content-Type" content="text/html; charset=shift_jis">
    
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VOUCHER</title>
    <link rel="shortcut icon" type="image/x-icon" href="./ico/tool.ico" />
    <link href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <!-- bxSlider Javascript file -->
    <script src="./js/controls.js"></script>
    <script src="./js/jquery.tablesorter.min.js"></script>
</head>
<body>
<div style ='font:30px/40px Arial,tahoma,sans-serif;'>
	<form id="voucherForm">
		Voucher GIẢM GIÁ <input id="percent" name="percent" size="4" value="15"/>%
		<br>
		<br>
		<input type="submit" value="ENTER để tạo"/>
	</form>
	<br>
	<br>
	<div class="mainContent">
	</div>
</div>
</body>

<script type="text/javascript">
$(document).ready(function() {
	$("#voucherForm").submit(function(){
	   	var percent = $("#percent").val();
	    var time = Math.round(+new Date()/1000);
	    var voucher = "SH" + percent + "." + time;
	    prompt("Copy voucher to clipboard: Ctrl+C, Enter", voucher);
	   	
	   	return false; // cancel submit form
	});
});
</script>

</html>





