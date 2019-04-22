<?php
include_once "check_token.php";
require_once('_main_functions.php');

//include_once "src/show_errors.php";

?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MOVE CHILD</title>
    <link href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <!-- bxSlider Javascript files -->
    <script src="./js/controls.js"></script>
    <script src="./js/jquery.tablesorter.min.js"></script>
    
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<?php
// Pay no attention to this statement.
// It's only needed if timezone in php.ini is not set correctly.
date_default_timezone_set("UTC");

$preview = val($_POST['preview']);

$input = $_POST['col'][0];
$skus = array_filter(explode("\n", str_replace("\r", "", $input)));

$input = $_POST['col'][1];
$newParentSkus = array_filter(explode("\n", str_replace("\r", "", $input)));

?>

<body>
    <h1>MOVE SKU TO NEW PARENT</h1>
    <h2>
        - SP cần move và SP cha phải cùng category ngành hàng<br>
        - SKU phải đúng format trước khi move vào cha mới<br>
        - NewSKU = SKU.Mx (x là số lần đã move)<br>
        - Sau khi move, SKU cha sẽ bị đổi tên theo tên của SKU con, kiểm lại để chắc chắn là SKU con cũ đã bị xoá sau khi move, tránh hiện tượng trùng tên
    </h2>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">

<table>
    <tbody>
        <tr>
            <td>SKU</td>
            <td>New parent SKU</td>
        </tr>
        <tr>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="50"><?php echo implode("\n", $skus);?></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="50"><?php echo implode("\n", $newParentSkus);?></textarea></td>
        </tr>
    </tbody>
</table>
<input type="checkbox" name="preview" checked="1" value="1">Preview<br>
<input type="submit"><hr>


<?php
    
if(empty($skus) || empty($newParentSkus)) {
    echo "<h1>Please input source SKU and SKU prefix</h1>";
    exit(1);
}

$data = array(
    "skus" => $skus,
    "newParentSkus" => $newParentSkus
    );

massMoveChild($accessToken, $data, $preview);

?>

<script type="text/javascript">
</script>
</div>
</body>
</html>