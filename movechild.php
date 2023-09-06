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
    <?php include('src/head.php');?>
</head>

<?php
// Pay no attention to this statement.
// It's only needed if timezone in php.ini is not set correctly.
date_default_timezone_set("UTC");

$preview = val($_POST['preview']);
$input = $_POST['col'][0];
$sku = isset($_REQUEST["sku"]) ? $_REQUEST["sku"] : "";
$skus = array($sku);

if(!empty($input)) {
    $skus = array_filter(explode("\n", str_replace("\r", "", $input)));
}

$input = $_POST['col'][1];
$newParentSkus = array_filter(explode("\n", str_replace("\r", "", $input)));

?>

<body>
    <h1>MOVE SKU TO NEW PARENT</h1>
    <h2>
        - SOURCE và DES phải cùng category ngành hàng<br>
        - SOURCE SKU phải đúng format trước khi move vào cha mới<br>
        - NewSKU = SKU.Mx (x là số lần đã move)<br>
        - Sau khi move, DES sẽ bị đổi tên theo tên của SOURCE con, nhớ xoá SOURCE sau khi move, tránh hiện tượng trùng tên
    </h2>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">

    <table>
        <tbody>
            <tr>
                <td>SOURCE SKU</td>
                <td>DES SKU</td>
            </tr>
            <tr>
                <td><textarea id="sourceskus" class="nowrap" name="col[]" rows="20" cols="50"><?php echo implode("\n", $skus);?></textarea></td>
                <td><textarea class="nowrap" name="col[]" rows="20" cols="50"><?php echo implode("\n", $newParentSkus);?></textarea></td>
            </tr>
        </tbody>
    </table>
    <input type="checkbox" name="preview" checked="1" value="1">Preview<br>
    <input type="submit" value="MOVE">
    </form><br><br>

<button id="delbutton">DELETE SOURCE SKUs</button><br>
<iframe id="responseIframe" name="responseIframe" width="1000" height="100"></iframe>

<hr>

<?php
if(empty($skus) || empty($newParentSkus)) {
    echo "<h1>Please input source SKU and SKU prefix</h1>";
} else {
    $data = array(
    "skus" => $skus,
    "newParentSkus" => $newParentSkus
    );

    massMoveChild($accessToken, $data, $preview);
}
?>

<script type="text/javascript">
    //######### AJAX QUEUE ######################################################################################
    ajaxManager = getAjaxManager();
    ajaxManager.run(); 

    //##########################################################################################################


    $("#delbutton").click(function(){
        var myFrame = $("#responseIframe").contents().find('body'); 
        var d = new Date();
        var n = d.toLocaleTimeString();
        myFrame.prepend('### ' + n + ' DELETING ###################################################<hr>');

        var lines = $("#sourceskus").val().split('\n');
        for(let i=0; i<lines.length; i++){
            productDeleteWithAjaxQueue({delete_sku: lines[i]});
        }
    });
</script>
</body>
</html>
