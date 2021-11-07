<?php
include_once "check_token.php";
require_once('_main_functions.php');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$packageId = isset($_REQUEST['package_id']) ? $_REQUEST['package_id'] : '';

if($accessToken && $action) {
    $response = 0;

    switch ($action) {
        case 'repack':
            setRepack($accessToken, $packageId);
            break;
        
        default:
            # code...
            break;
    }
    sleep(1);
    echo json_encode($response);
}

?>